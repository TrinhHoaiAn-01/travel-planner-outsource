<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\City;
use App\Models\Trip;
use App\Models\Category;
use App\Models\CommerceCampaign;
use App\Models\CommercePromotion;
use App\Models\Destination;
use App\Models\Payment;
use App\Models\RoomType;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CommerceBackendService
{
    public function __construct(private ?User $user, private array $data = []) {}

    public function state(): array
    {
        return $this->data;
    }

    public function pendingChanges(): array
    {
        return array_values(array_filter($this->data['changes'] ?? [], fn ($change) => $change['status'] === 'staged'));
    }

    public function execute(string $method, array $arguments): mixed
    {
        if ($this->user && ! $this->user->is_active) {
            throw new DomainException('Tài khoản đã bị khóa.');
        }
        $shoppingMethods = ['search_products', 'get_product_details', 'get_cart', 'add_to_cart',
            'update_cart_item', 'remove_from_cart', 'get_preferences', 'get_orders', 'get_order',
            'search_policies', 'get_account_context', 'checkout_handoff', 'get_fulfillment_options', 'get_disclosure',
            'add_favorite', 'get_favorites', 'add_destination_to_trip', 'remove_destination_from_trip', 'book_hotel_room', 'cancel_booking', 'update_booking_dates', 'confirm_bank_transfer'];
        if (! in_array($method, $shoppingMethods, true)) {
            $this->requireAdmin();
        }

        return match ($method) {
            'search_products' => $this->searchProducts($arguments),
            'get_product_details' => $this->product((string) $arguments['product_id']),
            'get_cart' => $this->cart(),
            'add_to_cart', 'update_cart_item', 'remove_from_cart' => $this->changeCart($method, $arguments),
            'get_preferences' => ['user_id' => (string) ($this->user?->id ?? 'guest'), 'display_name' => $this->user?->name],
            'get_account_context' => $this->accountContext(),
            'get_orders' => $this->orders((int) ($arguments['limit'] ?? 5)),
            'get_order' => $this->order((string) $arguments['order_id']),
            'search_policies' => $this->websiteInformation(),
            'checkout_handoff' => $this->checkoutLinks(),
            'get_fulfillment_options' => [['method' => 'pickup', 'eta' => 'Nhận dịch vụ tại điểm đến sau khi xác nhận ngày đặt phòng', 'fee' => 0]],
            'get_disclosure' => $this->disclosure((string) $arguments['product_id']),
            'add_favorite' => $this->addFavorite($arguments),
            'get_favorites' => $this->getFavorites(),
            'add_destination_to_trip' => $this->addToTrip($arguments),
            'remove_destination_from_trip' => $this->removeDestinationFromTrip($arguments),
            'book_hotel_room' => $this->bookHotelRoom($arguments),
            'cancel_booking' => $this->cancelBooking($arguments),
            'update_booking_dates' => $this->updateBookingDates($arguments),
            'confirm_bank_transfer' => $this->confirmBankTransfer($arguments),
            'search_listings' => $this->searchListings($arguments),
            'get_listing' => $this->listing((string) $arguments['listing_id']),
            'get_business_snapshot' => $this->snapshot($arguments),
            'query_metrics' => $this->metricSeries($arguments),
            'get_inventory_alerts' => $this->inventoryAlerts(),
            'get_order_issues' => $this->orderIssues(),
            'get_pricing_context' => $this->pricingContext((string) $arguments['listing_id']),
            'get_campaign_performance' => $this->campaigns($arguments),
            'stage_listing_update', 'stage_price_update', 'stage_inventory_action' => $this->stageChange($method, $arguments),
            'stage_promotion', 'stage_campaign' => $this->stageBusinessChange($method, $arguments),
            'propose_price_update' => $this->stageChange('stage_price_update', isset($arguments['listing_id']) ? ['items' => [$arguments]] : $arguments),
            'propose_pause_listing' => $this->stageChange('stage_inventory_action', isset($arguments['listing_id']) ? ['items' => [['listing_id' => $arguments['listing_id'], 'action' => 'pause']]] : $arguments),
            'propose_promotion' => $this->stageBusinessChange('stage_promotion', isset($arguments['name']) ? ['promotion' => $arguments] : $arguments),
            'propose_campaign' => $this->stageBusinessChange('stage_campaign', isset($arguments['name']) ? ['campaign' => $arguments] : $arguments),
            'get_pending_changes' => $this->pendingChanges(),
            'apply_change' => throw new DomainException('Chỉ áp dụng qua nút Duyệt trên website; lời nhắn trong chat không phải phê duyệt.'),
            'discard_change' => $this->discardChange((string) $arguments['change_id']),
            'get_analysis_schema' => 'bookings(id, room_type_id, room_count, check_in_date, check_out_date, total_price, status, created_at); rooms(id, destination_id, name, quantity, price_per_night); payments(id, booking_id, amount, status, created_at)',
            'analysis_data' => $this->analysisData(),
            'get_occupancy' => (new CommerceExperienceService)->occupancy(),
            'get_merchant_context' => ['website' => $this->websiteInformation(), 'limitations' => [
                ['source' => 'campaigns', 'note' => 'Chiến dịch lưu bản nháp trên website; chưa kết nối nền tảng quảng cáo nên spend/revenue là không xác định.'],
                ['source' => 'analytics', 'note' => 'Phân tích trên bản sao tối đa 2000 dòng/bảng; không có số liệu truy cập.'],
            ]],
            default => throw new DomainException('Công cụ không được hỗ trợ.'),
        };
    }

    private function requireAdmin(): void
    {
        if (! $this->user || $this->user->role !== 'admin' || ! $this->user->is_active) {
            throw new DomainException('Chỉ quản trị viên đang hoạt động được dùng công cụ quản trị.');
        }
    }

    private function searchProducts(array $arguments): array
    {
        $filters = $arguments['filters'] ?? [];
        $query = trim((string) ($arguments['query'] ?? ''));
        $limit = max(1, min(20, (int) ($arguments['limit'] ?? 8)));
        $destinations = Destination::query()->where('name', 'NOT LIKE', '%#%')->with(['city', 'category', 'roomTypes']);
        if ($query !== '') {
            $identifiers = [];
            try {
                $response = Http::timeout(5)->acceptJson()->get(config('commerce.solr_url').'/select', [
                    'q' => $query, 'defType' => 'edismax', 'qf' => 'name^4 city^3 category^2 description address',
                    'q.op' => 'OR', 'mm' => '1',
                    'fl' => 'id', 'rows' => $limit, 'wt' => 'json',
                ]);
                if (! $response->successful()) {
                    return [];
                }
                $identifiers = array_map(function ($id) {
                    return (int) str_replace(['dest:', 'destination:'], '', (string) $id);
                }, array_column($response->json('response.docs', []), 'id'));
            } catch (\Throwable $e) {
                return [];
            }
            if (empty($identifiers)) {
                return [];
            }
            $destinations->whereIn('id', $identifiers);
        }
        if (! empty($filters['category'])) {
            if (! Category::where('name', $filters['category'])->exists()) {
                throw new DomainException('Danh mục không tồn tại. Hãy gọi lại không truyền filters.category; chỉ dùng query của khách.');
            }
            $destinations->whereHas('category', fn ($builder) => $builder->where('name', $filters['category']));
        }
        if (isset($filters['min_rating'])) {
            $destinations->where('rating', '>=', $filters['min_rating']);
        }
        $products = [];
        $query = $destinations->orderByRaw("CASE WHEN name LIKE '%#%' THEN 1 ELSE 0 END ASC")->orderByDesc('rating')->limit($limit);
        foreach ($query->get() as $destination) {
            $product = $this->destinationProduct($destination);
            if (isset($filters['min_price']) && $product['price'] < $filters['min_price']) {
                continue;
            }
            if (isset($filters['max_price']) && $product['price'] > $filters['max_price']) {
                continue;
            }
            $products[] = $product;
        }
        if (($filters['sort'] ?? '') === 'price_asc') {
            usort($products, fn ($left, $right) => $left['price'] <=> $right['price']);
        } elseif (($filters['sort'] ?? '') === 'price_desc') {
            usort($products, fn ($left, $right) => $right['price'] <=> $left['price']);
        }

        return $products;
    }

    private function destinationProduct(Destination $destination): array
    {
        return [
            'product_id' => 'destination:'.$destination->id, 'title' => $destination->name,
            'price' => (float) $destination->entrance_fee, 'currency' => 'VND',
            'rating' => $destination->rating === null ? null : (float) $destination->rating, 'in_stock' => ! $destination->commerce_paused,
            'category' => $destination->category?->name, 'short_description' => Str::limit(strip_tags($destination->description ?? ''), 500),
            'long_description' => Str::limit(strip_tags($destination->description ?? ''), 5000),
            'review_highlights' => $destination->approvedReviews()->latest()->limit(5)->get(['comment'])
                ->map(fn ($review) => Str::limit(strip_tags($review->comment ?? ''), 300))->all(),
            'attributes' => ['city' => $destination->city?->name ?? '', 'address' => $destination->address ?? '',
                'url' => url('/destinations/'.$destination->slug), 'price_basis' => 'entrance_fee; phòng có giá riêng'],
            'variants' => $destination->roomTypes->map(fn ($room) => $this->roomProduct($room))->all(),
        ];
    }

    private function roomProduct(RoomType $room): array
    {
        return ['product_id' => 'room:'.$room->id, 'title' => $room->name,
            'price' => (float) $room->price_per_night, 'currency' => 'VND', 'in_stock' => $room->quantity > 0 && ! $room->commerce_paused && ! $room->destination->commerce_paused,
            'short_description' => Str::limit(strip_tags($room->description ?? ''), 500),
            'attributes' => ['capacity' => (string) $room->capacity, 'quantity' => (string) $room->quantity,
                'price_basis' => 'mỗi phòng mỗi đêm; kiểm tra phòng trống theo ngày tại trang đặt phòng',
                'url' => url('/destinations/'.$room->destination->slug)]];
    }

    private function product(string $identifier): ?array
    {
        [$kind, $id] = array_pad(explode(':', $identifier, 2), 2, null);
        if ($kind === 'destination' && ctype_digit($id ?? '')) {
            $destination = Destination::with(['city', 'category', 'roomTypes'])->find($id);

            return $destination ? $this->destinationProduct($destination) : null;
        }
        if ($kind === 'room' && ctype_digit($id ?? '')) {
            $room = RoomType::find($id);

            return $room ? $this->roomProduct($room) : null;
        }

        return null;
    }

    private function cart(): array
    {
        $items = [];
        $existingKeys = [];

        // Nếu người dùng đã đăng nhập, lấy các mục trong Chuyến đi thực tế (Trip) và các Đặt phòng (Booking)
        if ($this->user) {
            $trip = Trip::where('user_id', $this->user->id)
                ->whereIn('status', [Trip::STATUS_PLANNED, Trip::STATUS_DRAFT, 'ongoing'])
                ->latest('id')
                ->first();

            if ($trip) {
                foreach ($trip->itineraryItems()->with(['destination.city'])->get() as $item) {
                    $pId = 'destination:' . $item->destination_id;
                    $existingKeys[$pId] = true;
                    $price = (float) ($item->destination?->entrance_fee ?? 0);
                    $items[] = [
                        'product_id' => $pId,
                        'title' => $item->destination?->name ?? $item->custom_title ?? 'Điểm tham quan',
                        'price' => $price,
                        'quantity' => 1,
                        'line_total' => $price,
                    ];
                }
            }

            // Thêm các đơn đặt phòng của người dùng
            $bookings = Booking::where('user_id', $this->user->id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->with(['roomType.destination.city'])
                ->latest('id')
                ->get();

            foreach ($bookings as $booking) {
                $pId = 'room:' . $booking->room_type_id;
                $existingKeys[$pId] = true;
                $nights = (int) ($booking->nights ?? \Carbon\Carbon::parse($booking->check_in_date)->diffInDays(\Carbon\Carbon::parse($booking->check_out_date)) ?: 1);
                $pricePerNight = (float) ($booking->roomType?->price_per_night ?? ($booking->total_price / max(1, $nights)));
                $title = ($booking->roomType?->destination?->name ?? 'Khách sạn') . ' - ' . ($booking->roomType?->name ?? 'Phòng');
                $items[] = [
                    'product_id' => $pId,
                    'title' => $title,
                    'price' => $pricePerNight,
                    'quantity' => $nights,
                    'line_total' => (float) $booking->total_price,
                ];
            }
        }

        // Bổ sung các mục giỏ hàng session nếu chưa có trong danh sách
        foreach ($this->data['cart'] ?? [] as $identifier => $quantity) {
            if (isset($existingKeys[$identifier])) {
                continue;
            }
            $product = $this->product($identifier);
            if ($product) {
                $lineTotal = (float) ($product['price'] * $quantity);
                $items[] = [
                    'product_id' => $identifier,
                    'title' => $product['title'],
                    'price' => (float) $product['price'],
                    'quantity' => $quantity,
                    'line_total' => $lineTotal,
                ];
            }
        }

        $subtotal = (float) array_sum(array_column($items, 'line_total'));

        return [
            'items' => $items,
            'currency' => 'VND',
            'subtotal' => $subtotal,
        ];
    }

    private function changeCart(string $method, array $arguments): array
    {
        $identifier = (string) ($arguments['product_id'] ?? '');

        if ($method === 'remove_from_cart') {
            if (str_starts_with($identifier, 'destination:')) {
                $this->removeDestinationFromTrip(['product_id' => $identifier] + $arguments);
            } elseif (str_starts_with($identifier, 'room:')) {
                $this->cancelBooking(['product_id' => $identifier] + $arguments);
            } else {
                $this->removeDestinationFromTrip(['destination_name' => $identifier] + $arguments);
                $this->cancelBooking(['hotel_name' => $identifier] + $arguments);
            }
            unset($this->data['cart'][$identifier]);
            return $this->cart();
        }

        $product = $this->product($identifier);
        if (! $product || ! ($product['in_stock'] ?? true)) {
            throw new DomainException('Sản phẩm không tồn tại hoặc không khả dụng.');
        }
        $quantity = max(1, min(24, (int) $arguments['quantity']));
        $this->data['cart'][$identifier] = min(24, $quantity + ($method === 'add_to_cart' ? ($this->data['cart'][$identifier] ?? 0) : 0));

        return $this->cart();
    }

    private function checkoutLinks(): array
    {
        $links = [];
        foreach (array_keys($this->data['cart'] ?? []) as $identifier) {
            $product = $this->product($identifier);
            if ($product) {
                $links[] = ['url' => $product['attributes']['url'], 'label' => 'Xem và đặt: '.$product['title']];
            }
        }

        return $links;
    }

    private function accountContext(): array
    {
        if (! $this->user) {
            return ['signed_in' => false, 'website' => $this->websiteInformation()];
        }

        return ['signed_in' => true, 'user_id' => $this->user->id, 'website' => $this->websiteInformation(),
            'favorites' => $this->user->favoriteDestinations()->limit(10)->get(['destinations.id', 'destinations.name'])->toArray(),
            'trips' => $this->user->trips()->latest()->limit(10)
                ->get(['id', 'name', 'start_date', 'end_date', 'budget', 'status'])->map(function ($trip) {
                    return array_merge($trip->toArray(), [
                        'itinerary' => $trip->itineraryItems()->orderBy('day_number')->limit(30)
                            ->get(['destination_id', 'custom_title', 'day_number', 'start_time', 'end_time', 'note'])->toArray(),
                        'total_expenses' => (float) $trip->expenses()->sum('amount'),
                        'recent_expenses' => $trip->expenses()->latest()->limit(10)
                            ->get(['category', 'description', 'amount', 'expense_date'])->toArray(),
                    ]);
                })->all()];
    }

    private function orders(int $limit): array
    {
        if (! $this->user) {
            return [];
        }

        return Booking::where('user_id', $this->user->id)->with('roomType')->latest()->limit(max(1, min(20, $limit)))
            ->get()->map(fn ($booking) => $this->bookingOrder($booking))->all();
    }

    private function order(string $identifier): ?array
    {
        if (! $this->user) {
            return null;
        }
        $booking = Booking::where('user_id', $this->user->id)->where('booking_code', $identifier)->with('roomType')->first();

        return $booking ? $this->bookingOrder($booking) : null;
    }

    private function bookingOrder(Booking $booking): array
    {
        return ['order_id' => $booking->booking_code, 'status' => $booking->status === 'cancelled' ? 'cancelled' : 'processing',
            'placed_at' => $booking->created_at->toIso8601String(), 'total' => (float) $booking->total_price, 'currency' => 'VND',
            'estimated_delivery' => $booking->check_in_date->toDateString().' → '.$booking->check_out_date->toDateString().' ('.$booking->status.')',
            'items' => [['product_id' => 'room:'.$booking->room_type_id, 'title' => $booking->roomType?->name ?? 'Phòng',
                'quantity' => $booking->room_count, 'price' => (float) ($booking->roomType?->price_per_night ?? 0)]]];
    }

    private function websiteInformation(): array
    {
        return [
            ['policy_id' => 'website', 'title' => 'Chức năng Travel Planner', 'content' => 'Website hỗ trợ khám phá điểm đến, yêu thích, chuyến đi, lịch trình theo ngày, ngân sách, chi phí, đánh giá, đặt phòng và thanh toán. Các dữ liệu riêng tư yêu cầu đăng nhập. Địa điểm: '.url('/').'; chuyến đi: '.url('/trips').'; đặt phòng: '.url('/bookings').'.'],
            ['policy_id' => 'booking', 'title' => 'Đặt phòng và giá', 'content' => 'Giá phòng trong danh mục là giá mỗi đêm bằng VND. Giỏ chat chỉ là kế hoạch, không giữ phòng hoặc thu tiền. Khách chọn ngày, số phòng và xác nhận tại trang đặt phòng. Tồn phòng thực tế được kiểm tra bởi dịch vụ đặt phòng. Không suy diễn chính sách hủy hoặc hoàn tiền khi chưa có dữ liệu.'],
        ];
    }

    private function searchListings(array $arguments): array
    {
        $keyword = trim((string) ($arguments['query'] ?? ''));
        if ($keyword !== '') {
            $query = Destination::query()->where('name', 'NOT LIKE', '%#%');
            app(\App\Services\Interfaces\SearchServiceInterface::class)->apply($query, 'destination', $keyword);

            return $query->orderByDesc('rating')->limit(max(1, min(100, (int) ($arguments['limit'] ?? 20))))
                ->get()->map(fn ($destination) => $this->listing('destination:'.$destination->id))->all();
        }
        $products = $this->searchProducts($arguments);

        return array_map(fn ($product) => $this->listing($product['product_id']), $products);
    }

    private function listing(string $identifier): ?array
    {
        $product = $this->product($identifier);
        if (! $product) {
            return null;
        }

        return ['listing_id' => $identifier, 'title' => $product['title'], 'price' => $product['price'], 'currency' => 'VND',
            'stock' => (int) ($product['attributes']['quantity'] ?? 0), 'status' => ($product['in_stock'] ?? true) ? 'active' : 'paused',
            'category' => $product['category'] ?? null, 'attributes' => $product['attributes'],
            'short_description' => $product['short_description'] ?? '', 'long_description' => $product['long_description'] ?? '',
            'variants' => array_map(fn ($variant) => $this->listing($variant['product_id']), $product['variants'] ?? [])];
    }

    private function snapshot(array $arguments): array
    {
        $bookings = Booking::where('created_at', '>=', now()->subDays(30));
        $orders = (clone $bookings)->count();
        $sales = (float) Payment::where('status', 'completed')->where('created_at', '>=', now()->subDays(30))->sum('amount');

        return ['period' => '30 ngày gần nhất', 'sales' => $sales, 'orders' => $orders, 'currency' => 'VND',
            'average_order_value' => $orders ? (float) (clone $bookings)->avg('total_price') : null,
            'note' => 'Doanh thu = thanh toán completed; số đơn = booking tạo trong 30 ngày; không có dữ liệu traffic.',
            'alerts' => ['pending_changes' => count($this->pendingChanges()),
                'low_stock' => RoomType::where('quantity', '<=', 2)->count(),
                'order_issues' => Booking::where('status', 'pending')->where('created_at', '<', now()->subDay())->count()]];
    }

    private function metricSeries(array $arguments): array
    {
        $metric = $arguments['metric'] ?? 'orders';
        if (! in_array($metric, ['orders', 'sales', 'revenue'], true)) {
            return ['metric' => $metric, 'points' => [], 'note' => 'Chỉ hỗ trợ orders, sales, revenue theo ngày trong 30 ngày gần nhất.'];
        }
        $query = $metric === 'orders' ? Booking::query() : Payment::where('status', 'completed');
        $expression = $metric === 'orders' ? 'COUNT(*)' : 'SUM(amount)';
        $points = $query->where('created_at', '>=', now()->subDays(30))
            ->selectRaw("DATE(created_at) as date, {$expression} as value")->groupByRaw('DATE(created_at)')->orderBy('date')->get()
            ->map(fn ($row) => ['date' => $row->date, 'value' => (float) $row->value])->all();

        return ['metric' => $metric, 'granularity' => 'day', 'period' => '30 ngày gần nhất', 'points' => $points];
    }

    private function inventoryAlerts(): array
    {
        return RoomType::where('quantity', '<=', 2)->limit(20)->get()->map(fn ($room) => [
            'listing_id' => 'room:'.$room->id, 'kind' => 'low_stock', 'title' => $room->name,
            'summary' => 'Tổng quỹ phòng: '.$room->quantity.'; chưa trừ đặt phòng theo ngày.', 'stock' => $room->quantity,
        ])->all();
    }

    private function orderIssues(): array
    {
        return Booking::where('status', 'pending')->where('created_at', '<', now()->subDay())->latest()->limit(20)->get()
            ->map(fn ($booking) => ['order_id' => $booking->booking_code, 'kind' => 'delayed',
                'issue_id' => 'pending:'.$booking->id,
                'summary' => 'Đặt phòng còn pending sau hơn 24 giờ.', 'opened_at' => $booking->created_at->toIso8601String()])->all();
    }

    private function pricingContext(string $identifier): ?array
    {
        $listing = $this->listing($identifier);

        return $listing ? ['listing_id' => $identifier, 'current_price' => $listing['price'], 'currency' => 'VND',
            'max_price_delta_pct' => 20, 'variants' => array_map(fn ($variant) => $this->pricingContext($variant['listing_id']), $listing['variants'])] : null;
    }

    private function stageChange(string $method, array $arguments): array
    {
        $this->requireAdmin();
        if (count($this->pendingChanges()) >= 20) {
            throw new DomainException('Tối đa 20 đề xuất chờ duyệt mỗi phiên.');
        }
        $items = [];
        if ($method === 'stage_listing_update') {
            $identifier = (string) $arguments['listing_id'];
            $model = $this->editableModel($identifier);
            foreach ($arguments['fields'] ?? [] as $field => $value) {
                $column = match ($field) {
                    'title' => 'name', 'long_description', 'short_description' => 'description', default => null
                };
                if (! $column || ! is_string($value) || mb_strlen($value) > ($column === 'name' ? 255 : 5000)) {
                    throw new DomainException('Chỉ hỗ trợ sửa title (255 ký tự) hoặc mô tả (5000 ký tự).');
                }
                $items[] = ['target' => $identifier, 'field' => $column, 'before' => $model->$column, 'after' => strip_tags($value)];
            }
        } else {
            foreach (array_slice($arguments['items'] ?? [], 0, 20) as $item) {
                $identifier = (string) $item['listing_id'];
                $model = $this->editableModel($identifier);
                if ($method === 'stage_price_update') {
                    $column = $model instanceof RoomType ? 'price_per_night' : 'entrance_fee';
                    $price = (float) ($item['new_price'] ?? 0);
                    $current = (float) $model->$column;
                    if ($price <= 0 || $current <= 0 || abs($price - $current) / $current > 0.20) {
                        throw new DomainException('Thay đổi giá phải dương và trong phạm vi 20% giá hiện tại.');
                    }
                    $items[] = ['target' => $identifier, 'field' => $column, 'before' => $model->$column, 'after' => $price];
                } else {
                    if (in_array($item['action'] ?? '', ['pause', 'activate'], true)) {
                        $items[] = ['target' => $identifier, 'field' => 'commerce_paused', 'before' => $model->commerce_paused, 'after' => $item['action'] === 'pause'];
                        continue;
                    }
                    if (! $model instanceof RoomType || ($item['action'] ?? '') !== 'restock') {
                        throw new DomainException('Chỉ tăng quỹ phòng cho room; điểm đến hỗ trợ pause/activate.');
                    }
                    $quantity = (int) ($item['quantity'] ?? 0);
                    if ($quantity < 1 || $quantity > 100) {
                        throw new DomainException('Chỉ thêm từ 1 đến 100 phòng mỗi đề xuất.');
                    }
                    $items[] = ['target' => $identifier, 'field' => 'quantity', 'before' => $model->quantity, 'after' => $model->quantity + $quantity];
                }
            }
        }
        if (! $items) {
            throw new DomainException('Đề xuất không có thay đổi.');
        }
        $change = ['change_id' => (string) Str::uuid(), 'kind' => substr($method, 6), 'status' => 'pending',
            'summary' => 'Đề xuất '.count($items).' thay đổi; cần quản trị viên duyệt.', 'items' => $items,
            'created_at' => now()->toIso8601String(), 'created_by' => (string) $this->user->id,
            'created_by_kind' => 'agent', 'currency' => 'VND'];
        $this->data['changes'][$change['change_id']] = $change;

        return $change;
    }

    private function editableModel(string $identifier): Destination|RoomType
    {
        [$kind, $id] = array_pad(explode(':', $identifier, 2), 2, null);
        $model = ctype_digit($id ?? '') ? match ($kind) {
            'destination', 'dest' => Destination::find($id), 'room' => RoomType::find($id), default => null,
        } : null;
        if (! $model) {
            throw new DomainException('Không tìm thấy dữ liệu cần sửa.');
        }

        return $model;
    }

    public function approveChange(string $identifier): array
    {
        $this->requireAdmin();
        $change = $this->data['changes'][$identifier] ?? null;
        if (! $change || ! in_array($change['status'], ['staged', 'pending'], true) || $change['created_by'] !== (string) $this->user->id) {
            throw new DomainException('Đề xuất không tồn tại hoặc đã được xử lý.');
        }
        DB::transaction(function () use ($change) {
            if (isset($change['_operation'])) {
                $this->applyBusinessChange($change);
            }
            foreach ($change['items'] as $item) {
                if (isset($change['_operation'])) {
                    break;
                }
                $model = $this->editableModel($item['target']);
                $model = $model->newQuery()->lockForUpdate()->findOrFail($model->id);
                if ($item['field'] === 'commerce_paused') {
                    if ((bool) $model->{$item['field']} !== (bool) $item['before']) {
                        throw new DomainException('Dữ liệu đã thay đổi từ lúc đề xuất. Hãy tạo đề xuất mới.');
                    }
                } elseif ((string) $model->{$item['field']} !== (string) $item['before']) {
                    throw new DomainException('Dữ liệu đã thay đổi từ lúc đề xuất. Hãy tạo đề xuất mới.');
                }
                $model->{$item['field']} = $item['after'];
                $model->save();
            }
            app(ActivityLogService::class)->logActivity($this->user->id, 'commerce.change.approved', null, null,
                ['change_id' => $change['change_id'], 'kind' => $change['kind'], 'items' => $change['items']]);
        });
        $change['status'] = 'applied';
        $change['applied_at'] = now()->toIso8601String();
        $change['applied_by'] = (string) $this->user->id;
        $this->data['changes'][$identifier] = $change;

        return $change;
    }

    private function disclosure(string $identifier): ?array
    {
        $product = $this->product($identifier);
        if (! $product) {
            return null;
        }
        return ['title' => 'Thông tin dịch vụ', 'product_id' => $identifier, 'rows' => [
            ['label' => 'Giá', 'value' => number_format($product['price'], 0, ',', '.').' VND'],
            ['label' => 'Đơn vị', 'value' => str_starts_with($identifier, 'room:') ? 'Mỗi phòng mỗi đêm' : 'Vé vào cổng; phòng có giá riêng'],
            ['label' => 'Xác nhận', 'value' => 'Ngày, số lượng và tổng tiền được kiểm tra tại trang đặt phòng.'],
        ], 'sources' => [$product['attributes']['url']], 'footnotes' => ['Giỏ trợ lý chỉ là kế hoạch; chưa giữ phòng và chưa thu tiền.']];
    }

    private function campaigns(array $arguments): array
    {
        $query = CommerceCampaign::query();
        if (! empty($arguments['campaign_id'])) {
            $query->whereKey($arguments['campaign_id']);
        }
        return $query->latest()->limit(30)->get()->map(fn ($campaign) => [
            'campaign_id' => $campaign->id, 'name' => $campaign->name, 'status' => $campaign->status,
            'budget' => (float) $campaign->budget, 'currency' => 'VND', 'spend' => null, 'revenue' => null,
            'objective' => $campaign->details['objective'] ?? null, 'starts' => $campaign->details['starts'] ?? null,
            'ends' => $campaign->details['ends'] ?? null, 'channel' => 'Website draft',
        ])->all();
    }

    private function stageBusinessChange(string $method, array $arguments): array
    {
        $this->requireAdmin();
        if (count($this->pendingChanges()) >= 20) {
            throw new DomainException('Tối đa 20 đề xuất chờ duyệt mỗi phiên.');
        }
        $promotion = $method === 'stage_promotion';
        $payload = $arguments[$promotion ? 'promotion' : 'campaign'] ?? [];
        $rules = ['name' => ['required', 'string', 'max:80']];
        if ($promotion) {
            $rules += ['listing_ids' => ['required', 'array', 'min:1', 'max:25'], 'listing_ids.*' => ['required', 'string'],
                'discount_pct' => ['required', 'numeric', 'between:-20,50'], 'starts' => ['required', 'date_format:Y-m-d'],
                'ends' => ['required', 'date_format:Y-m-d', 'after_or_equal:starts'], 'nights' => ['nullable', 'array'],
                'nights.*' => ['in:mon,tue,wed,thu,fri,sat,sun']];
        } else {
            $rules += ['campaign_id' => ['nullable', 'uuid'], 'budget' => ['nullable', 'numeric', 'min:0', 'max:1000000000'],
                'objective' => ['nullable', 'string', 'max:200'], 'audience' => ['nullable', 'string', 'max:300'],
                'copy_text' => ['nullable', 'string', 'max:600'], 'starts' => ['nullable', 'date_format:Y-m-d'],
                'ends' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:starts']];
        }
        $validation = validator($payload, $rules);
        if ($validation->fails()) {
            throw new DomainException('Đề xuất không hợp lệ: '.$validation->errors()->first());
        }
        $payload = $validation->validated();
        if ($promotion) {
            foreach ($payload['listing_ids'] as $identifier) {
                if (! $this->editableModel($identifier) instanceof RoomType) {
                    throw new DomainException('Khung giá theo ngày chỉ áp dụng cho các loại phòng room:id.');
                }
            }
        }
        $identifier = $payload['campaign_id'] ?? (string) Str::uuid();
        $before = ! $promotion && isset($payload['campaign_id']) ? CommerceCampaign::findOrFail($identifier)->toArray() : null;
        $items = [];
        foreach ($payload as $field => $value) {
            if ($field !== 'campaign_id') {
                $items[] = ['target' => $identifier, 'field' => $field, 'before' => $before[$field] ?? null, 'after' => $value];
            }
        }
        $change = ['change_id' => (string) Str::uuid(), 'kind' => $promotion ? 'promotion' : 'campaign', 'status' => 'staged',
            'summary' => ($promotion ? 'Khung giá: ' : 'Bản nháp chiến dịch: ').$payload['name'], 'items' => $items,
            'created_at' => now()->toIso8601String(), 'created_by' => (string) $this->user->id,
            'created_by_kind' => 'agent', 'currency' => 'VND', '_operation' => ['id' => $identifier, 'payload' => $payload, 'before' => $before],
            'guardrail_notes' => $promotion ? ['Chỉ áp dụng sau khi duyệt, theo đêm trong khoảng ngày đã chọn.'] : ['Chỉ lưu bản nháp; không tự chạy quảng cáo hoặc chi ngân sách.']];
        $this->data['changes'][$change['change_id']] = $change;
        return $change;
    }

    private function applyBusinessChange(array $change): void
    {
        $operation = $change['_operation'];
        $payload = $operation['payload'];
        if ($change['kind'] === 'promotion') {
            CommercePromotion::create(array_merge($payload, ['id' => $operation['id'], 'created_by' => $this->user->id]));
            return;
        }
        $campaign = CommerceCampaign::lockForUpdate()->find($operation['id']);
        if ($campaign && $campaign->toArray() !== $operation['before']) {
            throw new DomainException('Chiến dịch đã thay đổi. Hãy tạo đề xuất mới.');
        }
        if (! $campaign && $operation['before']) {
            throw new DomainException('Chiến dịch không còn tồn tại.');
        }
        $campaign ??= new CommerceCampaign(['id' => $operation['id'], 'created_by' => $this->user->id]);
        $campaign->name = $payload['name'];
        $campaign->budget = $payload['budget'] ?? $campaign->budget ?? 0;
        $campaign->details = array_merge($campaign->details ?? [], $payload);
        $campaign->save();
    }

    private function analysisData(): array
    {
        return ['bookings' => Booking::orderByDesc('id')->limit(2000)->get(['id', 'room_type_id', 'room_count', 'check_in_date', 'check_out_date', 'total_price', 'status', 'created_at'])->toArray(),
            'rooms' => RoomType::orderBy('id')->limit(2000)->get(['id', 'destination_id', 'name', 'quantity', 'price_per_night'])->toArray(),
            'payments' => Payment::orderByDesc('id')->limit(2000)->get(['id', 'booking_id', 'amount', 'status', 'created_at'])->toArray()];
    }

    
    public function addFavorite(array $arguments): array
    {
        if (! $this->user) {
            throw new DomainException('Vui lòng đăng nhập để lưu điểm đến vào danh sách yêu thích.');
        }

        $destination = null;
        if (! empty($arguments['destination_id'])) {
            $destId = (int) str_replace('destination:', '', (string) $arguments['destination_id']);
            $destination = Destination::find($destId);
        }
        if (! $destination && ! empty($arguments['destination_name'])) {
            $name = trim((string) $arguments['destination_name']);
            $escaped = addcslashes($name, '%_');
            $destination = Destination::where('name', 'like', "%{$escaped}%")->first();
        }
        if (! $destination && ! empty($arguments['query'])) {
            $query = trim((string) $arguments['query']);
            $escaped = addcslashes($query, '%_');
            $destination = Destination::where('name', 'like', "%{$escaped}%")->first();
        }

        if (! $destination) {
            throw new DomainException('Không tìm thấy điểm đến yêu cầu.');
        }

        \App\Models\Favorite::firstOrCreate([
            'user_id' => $this->user->id,
            'destination_id' => $destination->id,
        ]);

        return [
            'success' => true,
            'destination_id' => $destination->id,
            'destination_name' => $destination->name,
            'message' => "Đã thêm \"{$destination->name}\" vào danh sách yêu thích của bạn thành công!",
            'url' => route('favorites.index'),
        ];
    }

    public function getFavorites(): array
    {
        if (! $this->user) {
            return [];
        }

        return \App\Models\Favorite::where('user_id', $this->user->id)
            ->with(['destination.city'])
            ->get()
            ->map(fn ($fav) => [
                'destination_id' => $fav->destination_id,
                'name' => $fav->destination?->name,
                'city' => $fav->destination?->city?->name,
                'entrance_fee' => (float) ($fav->destination?->entrance_fee ?? 0),
            ])
            ->toArray();
    }

    public function addToTrip(array $arguments): array
    {
        if (! $this->user) {
            throw new DomainException('Vui lòng đăng nhập để thêm điểm đến vào chuyến đi.');
        }

        $destination = null;
        if (! empty($arguments['destination_id'])) {
            $destId = (int) str_replace('destination:', '', (string) $arguments['destination_id']);
            $destination = Destination::find($destId);
        }
        if (! $destination && ! empty($arguments['destination_name'])) {
            $name = trim((string) $arguments['destination_name']);
            $escaped = addcslashes($name, '%_');
            $destination = Destination::where('name', 'like', "%{$escaped}%")->first();
        }
        if (! $destination && ! empty($arguments['query'])) {
            $query = trim((string) $arguments['query']);
            $escaped = addcslashes($query, '%_');
            $destination = Destination::where('name', 'like', "%{$escaped}%")->first();
        }

        if (! $destination) {
            throw new DomainException('Không tìm thấy điểm đến yêu cầu.');
        }

        $trip = null;
        if (! empty($arguments['trip_id'])) {
            $trip = \App\Models\Trip::where('id', $arguments['trip_id'])
                ->where('user_id', $this->user->id)
                ->first();
        }

        if (! $trip) {
            $trip = \App\Models\Trip::where('user_id', $this->user->id)
                ->whereIn('status', [\App\Models\Trip::STATUS_PLANNED, \App\Models\Trip::STATUS_DRAFT, 'ongoing'])
                ->latest('id')
                ->first();
        }

        if (! $trip) {
            $cityName = $destination->city?->name ?? 'Việt Nam';
            $startDate = now()->addDays(7)->startOfDay();
            $trip = \App\Models\Trip::create([
                'user_id' => $this->user->id,
                'name' => "Chuyến đi {$cityName}",
                'description' => "Lịch trình khám phá cùng Trợ lý AI",
                'start_date' => $startDate->toDateString(),
                'end_date' => $startDate->copy()->addDays(3)->toDateString(),
                'budget' => 5000000,
                'status' => \App\Models\Trip::STATUS_PLANNED,
            ]);
        }

        $dayNumber = max(1, (int) ($arguments['day_number'] ?? 1));
        $favoriteService = app(\App\Services\Interfaces\FavoriteServiceInterface::class);
        $result = $favoriteService->addDestinationToTrip(
            $this->user,
            $trip->id,
            $destination->id,
            $dayNumber,
            $arguments['note'] ?? null
        );

        if (! ($result['success'] ?? false)) {
            throw new DomainException($result['message'] ?? 'Không thể thêm điểm đến vào chuyến đi.');
        }

        return [
            'success' => true,
            'trip_id' => $trip->id,
            'trip_name' => $trip->name,
            'destination_id' => $destination->id,
            'destination_name' => $destination->name,
            'day_number' => $dayNumber,
            'message' => "Đã thêm \"{$destination->name}\" vào ngày {$dayNumber} của chuyến đi \"{$trip->name}\" thành công!",
            'url' => route('trips.show', $trip->id),
        ];
    }

    public function removeDestinationFromTrip(array $arguments): array
    {
        if (! $this->user) {
            throw new DomainException('Vui lòng đăng nhập để thao tác trên chuyến đi.');
        }

        $destId = null;
        if (! empty($arguments['destination_id'])) {
            $destId = (int) str_replace('destination:', '', (string) $arguments['destination_id']);
        } elseif (! empty($arguments['product_id']) && str_starts_with((string) $arguments['product_id'], 'destination:')) {
            $destId = (int) str_replace('destination:', '', (string) $arguments['product_id']);
        }

        $destination = null;
        if ($destId) {
            $destination = Destination::find($destId);
        }

        if (! $destination && ! empty($arguments['destination_name'])) {
            $name = trim((string) $arguments['destination_name']);
            $name = preg_replace('/^(xóa|bỏ)\s+/iu', '', $name);
            $name = preg_replace('/\s+khỏi\s+chuyến\s+đi.*$/iu', '', $name);
            $name = trim($name);
            $escaped = addcslashes($name, '%_');
            $destination = Destination::where('name', 'like', "%{$escaped}%")->first();
        }

        if (! $destination && ! empty($arguments['title'])) {
            $title = trim((string) $arguments['title']);
            $title = preg_replace('/^(xóa|bỏ)\s+/iu', '', $title);
            $title = preg_replace('/\s+khỏi\s+chuyến\s+đi.*$/iu', '', $title);
            $title = trim($title);
            $escaped = addcslashes($title, '%_');
            $destination = Destination::where('name', 'like', "%{$escaped}%")->first();
        }

        if (! $destination && ! empty($arguments['query'])) {
            $query = trim((string) $arguments['query']);
            $query = preg_replace('/^(xóa|bỏ)\s+/iu', '', $query);
            $query = preg_replace('/\s+khỏi\s+chuyến\s+đi.*$/iu', '', $query);
            $query = trim($query);
            $escaped = addcslashes($query, '%_');
            $destination = Destination::where('name', 'like', "%{$escaped}%")->first();
        }

        $tripIds = Trip::where('user_id', $this->user->id)->pluck('id');

        if ($destination) {
            \App\Models\ItineraryItem::whereIn('trip_id', $tripIds)
                ->where('destination_id', $destination->id)
                ->delete();
            unset($this->data['cart']['destination:' . $destination->id]);
            $title = $destination->name;
        } else {
            $searchTitle = trim((string) ($arguments['destination_name'] ?? $arguments['title'] ?? $arguments['query'] ?? ''));
            $searchTitle = preg_replace('/^(xóa|bỏ)\s+/iu', '', $searchTitle);
            $searchTitle = preg_replace('/\s+khỏi\s+chuyến\s+đi.*$/iu', '', $searchTitle);
            $searchTitle = trim($searchTitle);
            if ($searchTitle) {
                \App\Models\ItineraryItem::whereIn('trip_id', $tripIds)
                    ->where('custom_title', 'like', "%{$searchTitle}%")
                    ->delete();
            }
            $title = $searchTitle ?: 'Điểm đến';
        }

        return [
            'success' => true,
            'message' => "Đã xóa \"{$title}\" khỏi kế hoạch chuyến đi thành công!",
        ];
    }

    public function cancelBooking(array $arguments): array
    {
        if (! $this->user) {
            throw new DomainException('Vui lòng đăng nhập để thao tác đặt phòng.');
        }

        $booking = null;
        if (! empty($arguments['booking_id'])) {
            $booking = Booking::with('roomType.destination')->where('id', $arguments['booking_id'])->where('user_id', $this->user->id)->first();
        } elseif (! empty($arguments['booking_code'])) {
            $code = trim((string) $arguments['booking_code']);
            $cleanCode = ltrim($code, '#');
            $booking = Booking::with('roomType.destination')
                ->where('user_id', $this->user->id)
                ->where(function ($q) use ($code, $cleanCode) {
                    $q->where('booking_code', $code)
                      ->orWhere('booking_code', '#' . $cleanCode)
                      ->orWhere('booking_code', $cleanCode);
                })
                ->first();
        } elseif (! empty($arguments['room_type_id'])) {
            $roomId = (int) str_replace('room:', '', (string) $arguments['room_type_id']);
            $booking = Booking::with('roomType.destination')
                ->where('user_id', $this->user->id)
                ->where('room_type_id', $roomId)
                ->whereIn('status', ['pending', 'confirmed'])
                ->latest('id')
                ->first();
        } elseif (! empty($arguments['product_id']) && str_starts_with((string) $arguments['product_id'], 'room:')) {
            $roomId = (int) str_replace('room:', '', (string) $arguments['product_id']);
            $booking = Booking::with('roomType.destination')
                ->where('user_id', $this->user->id)
                ->where('room_type_id', $roomId)
                ->whereIn('status', ['pending', 'confirmed'])
                ->latest('id')
                ->first();
        }

        if (! $booking && ! empty($arguments['hotel_name'])) {
            $name = trim((string) $arguments['hotel_name']);
            $name = preg_replace('/^(xóa|bỏ|hủy)\s+(phòng\s+)?/iu', '', $name);
            $name = preg_replace('/\s+khỏi\s+chuyến\s+đi.*$/iu', '', $name);
            $parts = explode(' - ', $name);
            $name = trim($parts[0]);
            $escaped = addcslashes($name, '%_');
            $booking = Booking::with('roomType.destination')
                ->where('user_id', $this->user->id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->whereHas('roomType.destination', fn ($q) => $q->where('name', 'like', "%{$escaped}%"))
                ->latest('id')
                ->first();
        }

        if (! $booking && ! empty($arguments['title'])) {
            $title = trim((string) $arguments['title']);
            $title = preg_replace('/^(xóa|bỏ|hủy)\s+(phòng\s+)?/iu', '', $title);
            $title = preg_replace('/\s+khỏi\s+chuyến\s+đi.*$/iu', '', $title);
            $parts = explode(' - ', $title);
            $title = trim($parts[0]);
            $escaped = addcslashes($title, '%_');
            $booking = Booking::with('roomType.destination')
                ->where('user_id', $this->user->id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->whereHas('roomType.destination', fn ($q) => $q->where('name', 'like', "%{$escaped}%"))
                ->latest('id')
                ->first();
        }

        if (! $booking && ! empty($arguments['query'])) {
            $query = trim((string) $arguments['query']);
            $query = preg_replace('/^(xóa|bỏ|hủy)\s+(phòng\s+)?/iu', '', $query);
            $query = preg_replace('/\s+khỏi\s+chuyến\s+đi.*$/iu', '', $query);
            $parts = explode(' - ', $query);
            $query = trim($parts[0]);
            $escaped = addcslashes($query, '%_');
            $booking = Booking::with('roomType.destination')
                ->where('user_id', $this->user->id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->whereHas('roomType.destination', fn ($q) => $q->where('name', 'like', "%{$escaped}%"))
                ->latest('id')
                ->first();
        }

        if (! $booking) {
            // Lấy booking pending gần nhất của người dùng
            $booking = Booking::with('roomType.destination')
                ->where('user_id', $this->user->id)
                ->where('status', 'pending')
                ->latest('id')
                ->first();
        }

        if ($booking) {
            $hotelName = $booking->roomType?->destination?->name ?? 'Phòng khách sạn';
            $booking->update(['status' => 'cancelled']);
            unset($this->data['cart']['room:' . $booking->room_type_id]);
            return [
                'success' => true,
                'booking_id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'message' => "Đã xóa phòng \"{$hotelName}\" khỏi kế hoạch chuyến đi thành công!",
            ];
        }

        // Nếu booking vừa bị hủy (ví dụ qua API ngay trước đó), tìm booking đã cancelled gần đây nhất để báo thành công
        $recentCancelled = Booking::with('roomType.destination')
            ->where('user_id', $this->user->id)
            ->where('status', 'cancelled')
            ->latest('updated_at')
            ->first();
        if ($recentCancelled) {
            $hotelName = $recentCancelled->roomType?->destination?->name ?? 'Phòng khách sạn';
            return [
                'success' => true,
                'booking_id' => $recentCancelled->id,
                'message' => "Đã xóa phòng \"{$hotelName}\" khỏi kế hoạch chuyến đi thành công!",
            ];
        }

        return [
            'success' => true,
            'message' => 'Đã xóa mục này khỏi kế hoạch chuyến đi thành công!',
        ];
    }

    public function bookHotelRoom(array $arguments): array
    {
        if (! $this->user) {
            throw new DomainException('Vui lòng đăng nhập để thực hiện đặt phòng.');
        }

        $roomType = null;
        $roomId = ! empty($arguments['room_type_id']) ? $arguments['room_type_id'] : ($arguments['room_id'] ?? null);
        if (! empty($roomId)) {
            $parsedId = (int) str_replace('room:', '', (string) $roomId);
            $roomType = RoomType::with('destination')->find($parsedId);
        }

        if (! $roomType && ! empty($arguments['destination_name'])) {
            $name = trim((string) $arguments['destination_name']);
            $escaped = addcslashes($name, '%_');
            $destination = Destination::where('name', 'like', "%{$escaped}%")->with('roomTypes.destination', 'city')->first();
            $roomType = $destination?->roomTypes->first();
            if (! $roomType && $destination) {
                $roomType = RoomType::whereHas('destination', fn ($q) => $q->where('city_id', $destination->city_id))->with('destination.city')->first();
            }
        }

        if (! $roomType && ! empty($arguments['hotel_name'])) {
            $name = trim((string) $arguments['hotel_name']);
            $escaped = addcslashes($name, '%_');
            $destination = Destination::where('name', 'like', "%{$escaped}%")->with('roomTypes.destination', 'city')->first();
            $roomType = $destination?->roomTypes->first();
            if (! $roomType && $destination) {
                $roomType = RoomType::whereHas('destination', fn ($q) => $q->where('city_id', $destination->city_id))->with('destination.city')->first();
            }
        }

        if (! $roomType) {
            $requestedName = $arguments['hotel_name'] ?? $arguments['destination_name'] ?? '';
            if ($requestedName) {
                throw new DomainException("Điểm đến hoặc cơ sở lưu trú \"{$requestedName}\" hiện chưa có loại phòng lưu trú khả dụng trực tiếp trên hệ thống. Quý khách vui lòng chọn các khách sạn hoặc resort khả dụng khác.");
            }
            // Tìm điểm đến gần nhất có phòng
            $destination = Destination::has('roomTypes')->with('roomTypes.destination', 'city')->first();
            $roomType = $destination?->roomTypes->first();
        }

        if (! $roomType) {
            throw new DomainException('Không tìm thấy phòng khách sạn khả dụng cho điểm đến yêu cầu.');
        }

        if ($roomType->commerce_paused || $roomType->destination?->commerce_paused) {
            throw new DomainException('Phòng hoặc điểm đến này hiện đang tạm dừng nhận đặt phòng.');
        }

        $destination = $roomType->destination;

        $nights = max(1, min(30, (int) ($arguments['nights'] ?? 3)));
        if (! empty($arguments['check_in_date'])) {
            $checkIn = \Carbon\Carbon::parse($arguments['check_in_date'])->toDateString();
        } else {
            $checkIn = now()->addDay()->toDateString();
        }

        if (! empty($arguments['check_out_date'])) {
            $checkOut = \Carbon\Carbon::parse($arguments['check_out_date'])->toDateString();
        } else {
            $checkOut = \Carbon\Carbon::parse($checkIn)->addDays($nights)->toDateString();
        }

        $roomCount = max(1, min(10, (int) ($arguments['room_count'] ?? 1)));

        $bookingService = app(\App\Services\Interfaces\BookingServiceInterface::class);
        try {
            $booking = $bookingService->createBooking([
                'room_type_id' => $roomType->id,
                'check_in_date' => $checkIn,
                'check_out_date' => $checkOut,
                'room_count' => $roomCount,
                'trip_id' => $arguments['trip_id'] ?? null,
            ], $this->user->id);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw new DomainException($e->getMessage(), 0, $e);
        }

        $syntax = 'TP ' . str_replace('#', '', $booking->booking_code);

        return [
            'success' => true,
            'booking_id' => $booking->id,
            'booking_code' => $booking->booking_code,
            'hotel_name' => $destination?->name ?? 'Khách sạn',
            'room_name' => $roomType->name,
            'check_in_date' => $booking->check_in_date,
            'check_out_date' => $booking->check_out_date,
            'nights' => (int) \Carbon\Carbon::parse($booking->check_in_date)->diffInDays(\Carbon\Carbon::parse($booking->check_out_date)),
            'room_count' => $booking->room_count,
            'total_price' => (float) $booking->total_price,
            'status' => $booking->status,
            'payment_instructions' => [
                'bank_name' => 'Vietcombank',
                'account_number' => '9876543210',
                'account_holder' => 'CONG TY TRAVEL PLANNER',
                'amount' => (float) $booking->total_price,
                'transfer_syntax' => $syntax,
            ],
            'message' => "Đã tạo đơn đặt phòng thành công với mã {$booking->booking_code}! Vui lòng chuyển khoản để xác nhận phòng.",
            'view_url' => route('bookings.show', $booking->id),
            'pay_url' => route('payments.create', $booking->id),
        ];
    }

    public function updateBookingDates(array $arguments): array
    {
        if (! $this->user) {
            throw new DomainException('Vui lòng đăng nhập tài khoản Travel Planner để thay đổi ngày đặt phòng.');
        }

        $booking = null;
        if (! empty($arguments['booking_id'])) {
            $booking = Booking::where('id', $arguments['booking_id'])->where('user_id', $this->user->id)->first();
        } elseif (! empty($arguments['booking_code'])) {
            $booking = Booking::where('booking_code', $arguments['booking_code'])->where('user_id', $this->user->id)->first();
        }

        if (! $booking) {
            $booking = Booking::where('user_id', $this->user->id)->where('status', 'pending')->latest('id')->first();
        }

        if (! $booking) {
            throw new DomainException('Không tìm thấy đơn đặt phòng hợp lệ để thay đổi ngày.');
        }

        $checkIn = ! empty($arguments['check_in_date']) ? \Carbon\Carbon::parse($arguments['check_in_date'])->toDateString() : $booking->check_in_date;
        $nights = max(1, (int) ($arguments['nights'] ?? 3));
        $checkOut = ! empty($arguments['check_out_date']) ? \Carbon\Carbon::parse($arguments['check_out_date'])->toDateString() : \Carbon\Carbon::parse($checkIn)->addDays($nights)->toDateString();

        $bookingService = app(\App\Services\Interfaces\BookingServiceInterface::class);
        if (! $bookingService->checkRoomAvailability($booking->room_type_id, $checkIn, $checkOut, $booking->room_count)) {
            throw new DomainException('Phòng không còn trống trong khoảng ngày bạn chọn.');
        }

        $totalPrice = $bookingService->calculateBookingTotal($booking->room_type_id, $checkIn, $checkOut, $booking->room_count);
        $booking->update([
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'total_price' => $totalPrice,
        ]);

        return [
            'success' => true,
            'booking_id' => $booking->id,
            'booking_code' => $booking->booking_code,
            'check_in_date' => $booking->check_in_date,
            'check_out_date' => $booking->check_out_date,
            'total_price' => (float) $booking->total_price,
            'message' => "Đã cập nhật ngày nhận phòng: {$booking->check_in_date} và ngày trả phòng: {$booking->check_out_date}. Tổng tiền mới: " . number_format($totalPrice) . " VNĐ.",
        ];
    }

    public function confirmBankTransfer(array $arguments): array
    {
        if (! $this->user) {
            throw new DomainException('Vui lòng đăng nhập tài khoản Travel Planner để xác nhận thanh toán.');
        }

        $booking = null;
        if (! empty($arguments['booking_id'])) {
            $booking = Booking::with(['roomType.destination.city', 'payments'])->where('id', $arguments['booking_id'])->where('user_id', $this->user->id)->first();
        } elseif (! empty($arguments['booking_code'])) {
            $code = trim((string) $arguments['booking_code']);
            $cleanCode = ltrim($code, '#');
            $booking = Booking::with(['roomType.destination.city', 'payments'])
                ->where('user_id', $this->user->id)
                ->where(function ($q) use ($code, $cleanCode) {
                    $q->where('booking_code', $code)
                      ->orWhere('booking_code', '#' . $cleanCode)
                      ->orWhere('booking_code', $cleanCode);
                })
                ->first();
        }

        if (! $booking) {
            $booking = Booking::with(['roomType.destination.city', 'payments'])->where('user_id', $this->user->id)->where('status', 'pending')->latest('id')->first();
        }

        if (! $booking) {
            $booking = Booking::with(['roomType.destination.city', 'payments'])->where('user_id', $this->user->id)->latest('id')->first();
        }

        if (! $booking) {
            throw new DomainException('Không tìm thấy đơn đặt phòng hợp lệ.');
        }

        // BẮT BUỘC KIỂM TRA TRONG ADMIN: Đơn đã được Quản trị viên duyệt / áp dụng thanh toán chưa
        $isApproved = ($booking->status === 'confirmed' || $booking->status === 'completed')
            || $booking->payments()->where('status', 'completed')->exists();

        $destination = $booking->roomType?->destination;
        $hotelName = $destination?->name;
        if (! $hotelName && $booking->trip_id) {
            $tripDest = \App\Models\ItineraryItem::with('destination')->where('trip_id', $booking->trip_id)->whereNotNull('destination_id')->first();
            $hotelName = $tripDest?->destination?->name;
        }
        $hotelName = $hotelName ?: 'Khách sạn / Resort';
        $roomName = $booking->roomType?->name ?: 'Phòng tiêu chuẩn';

        $nights = (int) (\Carbon\Carbon::parse($booking->check_in_date)->diffInDays(\Carbon\Carbon::parse($booking->check_out_date)) ?: 1);
        $totalPrice = (float) $booking->total_price;
        if ($totalPrice <= 0 && $booking->roomType?->price_per_night) {
            $totalPrice = (float) ($booking->roomType->price_per_night * $nights * max(1, $booking->room_count));
        }

        $cleanCode = ltrim($booking->booking_code, '#');
        $displayCode = '#' . $cleanCode;
        $syntax = 'TP ' . $cleanCode;

        $paymentInstructions = [
            'bank_name' => 'Vietcombank',
            'account_number' => '9876543210',
            'account_holder' => 'CONG TY TRAVEL PLANNER',
            'amount' => $totalPrice,
            'transfer_syntax' => $syntax,
        ];

        if ($isApproved) {
            return [
                'success' => true,
                'booking_id' => $booking->id,
                'booking_code' => $displayCode,
                'hotel_name' => $hotelName,
                'room_name' => $roomName,
                'check_in_date' => $booking->check_in_date,
                'check_out_date' => $booking->check_out_date,
                'nights' => $nights,
                'room_count' => $booking->room_count,
                'total_price' => $totalPrice,
                'status' => 'confirmed',
                'is_paid' => true,
                'payment_instructions' => $paymentInstructions,
                'message' => "Hệ thống và Quản trị viên đã xác nhận thanh toán thành công cho đơn {$displayCode}! Phòng của bạn đã sẵn sàng.",
                'view_url' => route('bookings.show', $booking->id),
            ];
        }

        // NẾU CHƯA ĐƯỢC ADMIN DUYỆT: Giữ nguyên trạng thái pending, báo khách thanh toán trước khi xác nhận
        return [
            'success' => true,
            'booking_id' => $booking->id,
            'booking_code' => $displayCode,
            'hotel_name' => $hotelName,
            'room_name' => $roomName,
            'check_in_date' => $booking->check_in_date,
            'check_out_date' => $booking->check_out_date,
            'nights' => $nights,
            'room_count' => $booking->room_count,
            'total_price' => $totalPrice,
            'status' => 'pending',
            'is_paid' => false,
            'payment_instructions' => $paymentInstructions,
            'message' => "Đơn đặt phòng {$displayCode} hiện đang ở trạng thái Chờ thanh toán và chưa được Quản trị viên (Admin) duyệt/áp dụng thanh toán. Quý khách vui lòng chuyển khoản theo thông tin bên dưới trước khi xác nhận, hoặc đợi Quản trị viên kiểm tra và duyệt giao dịch.",
            'view_url' => route('bookings.show', $booking->id),
            'pay_url' => route('payments.create', $booking->id),
        ];
    }

    private function discardChange(string $identifier): array
    {
        if (! isset($this->data['changes'][$identifier]) || $this->data['changes'][$identifier]['status'] !== 'staged') {
            throw new DomainException('Không tìm thấy đề xuất chờ duyệt.');
        }
        $change = &$this->data['changes'][$identifier];
        $change['status'] = 'discarded';
        $change['discarded_at'] = now()->toIso8601String();
        $change['discarded_by'] = (string) $this->user->id;
        $change['discarded_by_kind'] = 'agent';

        return $change;
    }
}
