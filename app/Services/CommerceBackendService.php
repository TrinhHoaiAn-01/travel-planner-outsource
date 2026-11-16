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
        if ($method === 'confirm_preview') {
            throw new DomainException('Thao tác confirm_preview chỉ có thể được gọi từ giao diện người dùng có xác thực (Surface Action).');
        }
        $shoppingMethods = ['search_products', 'get_product_details', 'get_cart', 'add_to_cart',
            'update_cart_item', 'remove_from_cart', 'get_preferences', 'get_orders', 'get_order',
            'search_policies', 'get_account_context', 'checkout_handoff', 'get_fulfillment_options', 'get_disclosure',
            'add_favorite', 'remove_favorite', 'get_favorites', 'add_destination_to_trip', 'remove_destination_from_trip',
            'book_hotel_room', 'cancel_booking', 'update_booking_dates',             'confirm_bank_transfer',
            'initiate_checkout',
            'generate_smart_itinerary',
            'checkout_trip'];
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
            'remove_favorite' => $this->removeFavorite($arguments),
            'get_favorites' => $this->getFavorites(),
            'add_destination_to_trip' => $this->addToTrip($arguments),
            'remove_destination_from_trip' => $this->removeDestinationFromTrip($arguments),
            'book_hotel_room' => $this->bookHotelRoom($arguments),
            'cancel_booking' => $this->cancelBooking($arguments),
            'update_booking_dates' => $this->updateBookingDates($arguments),
            'confirm_bank_transfer' => $this->confirmBankTransfer($arguments),
            'initiate_checkout' => $this->initiateCheckout($arguments),
            'confirm_preview' => $this->confirmPreview($arguments),
            'generate_smart_itinerary' => $this->generateSmartItinerary($arguments),
            'checkout_trip' => $this->checkoutTrip($arguments),
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
            unset($this->data['cart'][$identifier]);
            if ($this->user) {
                if (str_starts_with($identifier, 'room:')) {
                    $roomTypeId = (int) substr($identifier, 5);
                    $bookings = Booking::where('user_id', $this->user->id)
                        ->where('room_type_id', $roomTypeId)
                        ->whereIn('status', ['pending', 'confirmed'])
                        ->get();
                    foreach ($bookings as $b) {
                        $b->update(['status' => 'cancelled']);
                    }
                } elseif (str_starts_with($identifier, 'destination:')) {
                    $destinationId = (int) substr($identifier, 12);
                    $trip = Trip::where('user_id', $this->user->id)
                        ->whereIn('status', [Trip::STATUS_PLANNED, Trip::STATUS_DRAFT, 'ongoing'])
                        ->latest('id')
                        ->first();
                    if ($trip) {
                        $trip->itineraryItems()->where('destination_id', $destinationId)->delete();
                    }
                }
            }
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

        $rawName = $arguments['destination_name'] ?? ($arguments['query'] ?? '');
        if (! $destination && ! empty($rawName)) {
            $name = trim((string) $rawName);
            // Loại bỏ các từ khóa nghiệp vụ dư thừa khi người dùng nhập câu tự nhiên
            $cleanName = preg_replace('/^(thêm|lưu|cho|đưa|hãy)?\s*(vào|mục|danh sách)?\s*(yêu thích|ưa thích)\s*(cho tôi|giúp tôi|điểm này)?/ui', '', $name);
            $cleanName = preg_replace('/(vào|mục|danh sách)?\s*(yêu thích|ưa thích)\s*(nhé|nha|giúp tôi|cho tôi)?$/ui', '', $cleanName);
            $cleanName = trim($cleanName);

            if ($cleanName !== '') {
                $escaped = addcslashes($cleanName, '%_');
                $destination = Destination::where('name', 'like', "%{$escaped}%")->first();
            }

            if (! $destination && $cleanName !== '') {
                $words = array_filter(explode(' ', $cleanName), fn ($w) => mb_strlen($w) >= 2);
                foreach ($words as $word) {
                    $escapedWord = addcslashes($word, '%_');
                    $destination = Destination::where('name', 'like', "%{$escapedWord}%")->first();
                    if ($destination) {
                        break;
                    }
                }
            }
        }

        // Tự động chọn điểm đến nổi bật hoặc đầu tiên nếu người dùng không chỉ định tên cụ thể
        if (! $destination) {
            $destination = Destination::orderByDesc('rating')->first() ?: Destination::first();
        }

        if (! $destination) {
            throw new DomainException('Hệ thống hiện chưa có điểm đến khả dụng để lưu yêu thích.');
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

    public function removeFavorite(array $arguments): array
    {
        if (! $this->user) {
            throw new DomainException('Vui lòng đăng nhập để thao tác trên danh sách yêu thích.');
        }

        $destination = null;
        if (! empty($arguments['destination_id'])) {
            $destId = (int) str_replace('destination:', '', (string) $arguments['destination_id']);
            $destination = Destination::find($destId);
        }

        $rawName = $arguments['destination_name'] ?? ($arguments['query'] ?? '');
        if (! $destination && ! empty($rawName)) {
            $name = trim((string) $rawName);
            $cleanName = preg_replace('/^(xóa|bỏ|hủy)?\s*(khỏi|ra khỏi)?\s*(mục|danh sách)?\s*(yêu thích|ưa thích)/ui', '', $name);
            $cleanName = preg_replace('/(khỏi|ra khỏi)?\s*(mục|danh sách)?\s*(yêu thích|ưa thích)\s*(nhé|nha|giúp tôi|cho tôi)?$/ui', '', $cleanName);
            $cleanName = trim($cleanName);

            if ($cleanName !== '') {
                $escaped = addcslashes($cleanName, '%_');
                $destination = Destination::where('name', 'like', "%{$escaped}%")->first();
            }

            if (! $destination && $cleanName !== '') {
                $words = array_filter(explode(' ', $cleanName), fn ($w) => mb_strlen($w) >= 2);
                foreach ($words as $word) {
                    $escapedWord = addcslashes($word, '%_');
                    $destination = Destination::where('name', 'like', "%{$escapedWord}%")->first();
                    if ($destination) {
                        break;
                    }
                }
            }
        }

        if (! $destination) {
            $fav = \App\Models\Favorite::with('destination')->where('user_id', $this->user->id)->latest('id')->first();
            $destination = $fav?->destination;
        }

        if ($destination) {
            \App\Models\Favorite::where('user_id', $this->user->id)
                ->where('destination_id', $destination->id)
                ->delete();

            return [
                'success' => true,
                'destination_id' => $destination->id,
                'destination_name' => $destination->name,
                'message' => "Đã xóa \"{$destination->name}\" khỏi danh sách yêu thích thành công!",
                'url' => route('favorites.index'),
            ];
        }

        return [
            'success' => true,
            'message' => 'Đã cập nhật danh sách yêu thích thành công!',
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
        $rawDest = $arguments['destination_name'] ?? ($arguments['query'] ?? '');
        if (! $destination && ! empty($rawDest)) {
            $name = trim((string) $rawDest);
            $cleanName = preg_replace('/^(thêm|lưu|cho|đưa|hãy)?\s*(vào|mục)?\s*(chuyến đi|trip)\s*(cho tôi|giúp tôi|điểm này)?/ui', '', $name);
            $cleanName = trim($cleanName);
            if ($cleanName !== '') {
                $escaped = addcslashes($cleanName, '%_');
                $destination = Destination::where('name', 'like', "%{$escaped}%")->first();
            }
            if (! $destination && $cleanName !== '') {
                $words = array_filter(explode(' ', $cleanName), fn ($w) => mb_strlen($w) >= 2);
                foreach ($words as $word) {
                    $escapedWord = addcslashes($word, '%_');
                    $destination = Destination::where('name', 'like', "%{$escapedWord}%")->first();
                    if ($destination) {
                        break;
                    }
                }
            }
        }

        if (! $destination) {
            $destination = Destination::orderByDesc('rating')->first() ?: Destination::first();
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
        if (!$this->user) {
            throw new DomainException('Vui lòng đăng nhập để thao tác đặt phòng.');
        }

        $booking = null;
        if (isset($arguments['booking_id'])) {
            $booking = Booking::with('roomType.destination')->where('id', $arguments['booking_id'])->where('user_id', $this->user->id)->first();
            if (!$booking) {
                throw new DomainException('Không tìm thấy đơn đặt phòng với ID đã cung cấp.');
            }
        } elseif (isset($arguments['booking_code'])) {
            $code = trim((string) $arguments['booking_code']);
            $cleanCode = ltrim($code, '#');
            $booking = Booking::with('roomType.destination')
                ->where('user_id', $this->user->id)
                ->where(function ($q) use ($code, $cleanCode) {
                    $q->where('booking_code', $code)
                        ->orWhere('booking_code', '#' . $cleanCode)
                        ->orWhere('booking_code', $cleanCode);
                    if (is_numeric($cleanCode)) {
                        $q->orWhere('id', (int) $cleanCode);
                    }
                })
                ->first();
            if (!$booking) {
                throw new DomainException('Không tìm thấy đơn đặt phòng với mã đã cung cấp.');
            }
        } elseif (!empty($arguments['room_type_id'])) {
            $roomId = (int) str_replace('room:', '', (string) $arguments['room_type_id']);
            $booking = Booking::with('roomType.destination')
                ->where('user_id', $this->user->id)
                ->where('room_type_id', $roomId)
                ->whereIn('status', ['pending', 'confirmed'])
                ->latest('id')
                ->first();
        } elseif (!empty($arguments['product_id']) && str_starts_with((string) $arguments['product_id'], 'room:')) {
            $roomId = (int) str_replace('room:', '', (string) $arguments['product_id']);
            $booking = Booking::with('roomType.destination')
                ->where('user_id', $this->user->id)
                ->where('room_type_id', $roomId)
                ->whereIn('status', ['pending', 'confirmed'])
                ->latest('id')
                ->first();
        }

        if (!$booking && !empty($arguments['hotel_name'])) {
            $name = trim((string) $arguments['hotel_name']);
            $name = preg_replace('/^(xóa|bỏ|hủy)\s+(phòng\s+)?/iu', '', $name);
            $name = preg_replace('/\s+khỏi\s+chuyến\s+đi.*$/iu', '', $name);
            $parts = explode(' - ', $name);
            $name = trim($parts[0]);
            $escaped = addcslashes($name, '%_');
            $booking = Booking::with('roomType.destination')
                ->where('user_id', $this->user->id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->whereHas('roomType.destination', fn($q) => $q->where('name', 'like', "%{$escaped}%"))
                ->latest('id')
                ->first();
        }

        if (!$booking && !empty($arguments['title'])) {
            $title = trim((string) $arguments['title']);
            $title = preg_replace('/^(xóa|bỏ|hủy)\s+(phòng\s+)?/iu', '', $title);
            $title = preg_replace('/\s+khỏi\s+chuyến\s+đi.*$/iu', '', $title);
            $parts = explode(' - ', $title);
            $title = trim($parts[0]);
            $escaped = addcslashes($title, '%_');
            $booking = Booking::with('roomType.destination')
                ->where('user_id', $this->user->id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->whereHas('roomType.destination', fn($q) => $q->where('name', 'like', "%{$escaped}%"))
                ->latest('id')
                ->first();
        }

        if (!$booking && !empty($arguments['query'])) {
            $query = trim((string) $arguments['query']);
            $query = preg_replace('/^(xóa|bỏ|hủy)\s+(phòng\s+)?/iu', '', $query);
            $query = preg_replace('/\s+khỏi\s+chuyến\s+đi.*$/iu', '', $query);
            $parts = explode(' - ', $query);
            $query = trim($parts[0]);
            $escaped = addcslashes($query, '%_');
            $booking = Booking::with('roomType.destination')
                ->where('user_id', $this->user->id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->whereHas('roomType.destination', fn($q) => $q->where('name', 'like', "%{$escaped}%"))
                ->latest('id')
                ->first();
        }

        if (!$booking && !isset($arguments['booking_id']) && !isset($arguments['booking_code'])) {
            // Lấy booking pending gần nhất của người dùng
            $booking = Booking::with('roomType.destination')
                ->where('user_id', $this->user->id)
                ->where('status', 'pending')
                ->latest('id')
                ->first();
        }

        if (!$booking) {
            throw new DomainException('Không tìm thấy đơn đặt phòng hợp lệ để hủy.');
        }

        if ($booking->status === 'cancelled') {
            throw new DomainException('Đơn đặt phòng này đã bị hủy trước đó.');
        }

        $hotelName = $booking->roomType?->destination?->name ?? 'Phòng khách sạn';

        if (!isset($arguments['booking_id']) && !isset($arguments['booking_code'])) {
            $booking->update(['status' => 'cancelled']);
            unset($this->data['cart']['room:' . $booking->room_type_id]);
            return [
                'success' => true,
                'booking_id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'hotel_name' => $hotelName,
                'status' => 'cancelled',
                'message' => "Đã hủy đơn đặt phòng {$booking->booking_code} tại \"{$hotelName}\" thành công!",
                'view_url' => route('bookings.index'),
            ];
        }

        $token = bin2hex(random_bytes(24));
        $expiresAt = now()->addMinutes(15)->timestamp;
        $payload = [
            'booking_id' => $booking->id,
            'booking_code' => $booking->booking_code,
            'room_type_id' => $booking->room_type_id,
        ];

        \Illuminate\Support\Facades\DB::table('commerce_previews')->insert([
            'token' => $token,
            'user_id' => $this->user->id,
            'action' => 'cancel_booking',
            'status' => 'pending',
            'payload' => json_encode($payload),
            'expires_at' => $expiresAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        \Illuminate\Support\Facades\Cache::put("commerce_preview:{$token}", $payload, 900);

        return [
            'success' => true,
            'preview' => true,
            'requires_confirmation' => true,
            'preview_token' => $token,
            'action' => 'cancel_booking',
            'booking_id' => $booking->id,
            'booking_code' => $booking->booking_code,
            'hotel_name' => $hotelName,
            'status' => 'pending',
            'message' => "Bạn có chắc chắn muốn hủy đơn đặt phòng {$booking->booking_code} tại \"{$hotelName}\"? Vui lòng xác nhận để hoàn tất việc hủy.",
            'view_url' => route('bookings.index'),
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

        if (! $roomType) {
            $searchNames = array_filter([
                $arguments['hotel_name'] ?? null,
                $arguments['destination_name'] ?? null,
            ]);

            foreach ($searchNames as $rawName) {
                $name = trim((string) $rawName);
                if ($name === '') {
                    continue;
                }

                // Loại bỏ các tiền tố / hậu tố và cụm từ nhiễu
                $cleaned = preg_replace('/^(đặt|hãy đặt|book|tự động đặt|tự động book|thuê)?\s*(lịch phòng|lịch nghỉ|lịch|kỳ nghỉ|chỗ nghỉ|phòng|khách sạn|resort)?\s*(tại|ở|ở tại|của)?/ui', '', $name);
                $cleaned = preg_replace('/\b\d+\s*ngày\s*\d+\s*đêm\b/ui', '', $cleaned);
                $cleaned = preg_replace('/\b\d+\s*(ngày|đêm|người|phòng)\b/ui', '', $cleaned);
                $cleaned = preg_replace('/\b(bắt đầu từ hôm nay|bắt đầu từ ngày mai|bắt đầu từ|từ hôm nay|từ ngày mai|hôm nay|ngày mai)\b/ui', '', $cleaned);
                do {
                    $prev = $cleaned;
                    $cleaned = preg_replace('/^(lịch|lịch nghỉ|kỳ nghỉ|chỗ|chỗ nghỉ|của|ở|tại|ở tại|cho tôi|giúp tôi|ở khu|khu vực)\s+/ui', '', trim($cleaned));
                } while ($cleaned !== $prev);
                $cleaned = preg_replace('/\s+(cho tôi|giúp tôi|nhé|nha|với|ạ|đi)$/ui', '', trim($cleaned));
                $cleaned = trim($cleaned);

                $searchTarget = $cleaned !== '' ? $cleaned : $name;

                // 1. Tìm chính xác tương đối (LIKE %target%)
                $escaped = addcslashes($searchTarget, '%_');
                $destination = Destination::where('name', 'like', "%{$escaped}%")->with('roomTypes.destination', 'city')->first();
                $roomType = $destination?->roomTypes->first();

                                // 2. Tìm theo các token có nghĩa (token-based search)
                if (!$roomType) {
                    $tokens = array_filter(
                        preg_split('/[\s,\.\-\&]+/u', $searchTarget),
                        fn($w) => mb_strlen($w) >= 3 && !in_array(mb_strtolower($w), ['của', 'cho', 'tại', 'với', 'trong', 'ngày', 'đêm', 'khách', 'sạn', 'resort', 'phòng', 'không', 'tồn'])
                    );

                    if (!empty($tokens)) {
                        $destQuery = Destination::has('roomTypes')->with('roomTypes.destination', 'city');
                        foreach ($tokens as $token) {
                            $tEscaped = addcslashes($token, '%_');
                            $destQuery->where('name', 'like', "%{$tEscaped}%");
                        }
                        $destination = $destQuery->first();
                        $roomType = $destination?->roomTypes->first();
                    }
                }

                if ($roomType) {
                    break;
                }
            }
        }

        if (!$roomType) {
            $requestedName = $arguments['hotel_name'] ?? $arguments['destination_name'] ?? '';
            if ($requestedName) {
                throw new DomainException("Điểm đến hoặc cơ sở lưu trú \"{$requestedName}\" hiện chưa có loại phòng lưu trú khả dụng trực tiếp trên hệ thống. Quý khách vui lòng chọn các khách sạn hoặc resort khả dụng khác.");
            }
            // Tìm điểm đến gần nhất có phòng khi người dùng không chỉ định tên cụ thể
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

        $nights = max(1, min(30, (int) ($arguments['nights'] ?? 2)));
        if (! empty($arguments['check_in_date'])) {
            $checkIn = \Carbon\Carbon::parse($arguments['check_in_date'])->toDateString();
        } else {
            $checkIn = now()->toDateString();
        }

        if (! empty($arguments['check_out_date'])) {
            $checkOut = \Carbon\Carbon::parse($arguments['check_out_date'])->toDateString();
        } else {
            $checkOut = \Carbon\Carbon::parse($checkIn)->addDays($nights)->toDateString();
        }

        $roomCount = max(1, min(10, (int) ($arguments['room_count'] ?? 1)));

        if (isset($arguments['confirm']) && $arguments['confirm'] === false) {
            $totalPrice = (float) ($roomType->price_per_night * $nights * $roomCount);
            $token = bin2hex(random_bytes(24));
            $expiresAt = now()->addMinutes(15)->timestamp;
            $payload = [
                'room_type_id' => $roomType->id,
                'price_per_night' => (float) $roomType->price_per_night,
                'total_price' => $totalPrice,
                'check_in_date' => $checkIn,
                'check_out_date' => $checkOut,
                'room_count' => $roomCount,
                'hotel_name' => $destination?->name ?? 'Khách sạn',
            ];

            \Illuminate\Support\Facades\DB::table('commerce_previews')->insert([
                'token' => $token,
                'user_id' => $this->user->id,
                'action' => 'book_hotel_room',
                'status' => 'pending',
                'payload' => json_encode($payload),
                'expires_at' => $expiresAt,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            \Illuminate\Support\Facades\Cache::put("commerce_preview:{$token}", $payload, 900);

            return [
                'success' => true,
                'preview' => true,
                'requires_confirmation' => true,
                'preview_token' => $token,
                'action' => 'book_hotel_room',
                'room_type_id' => $roomType->id,
                'hotel_name' => $destination?->name ?? 'Khách sạn',
                'room_name' => $roomType->name,
                'check_in_date' => $checkIn,
                'check_out_date' => $checkOut,
                'nights' => $nights,
                'room_count' => $roomCount,
                'total_price' => $totalPrice,
                'status' => 'pending',
                'message' => "Xem trước đặt phòng tại {$destination?->name}. Vui lòng xác nhận để hoàn tất đặt phòng.",
            ];
        }

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

        $sepayService = app(\App\Services\Interfaces\SePayGatewayServiceInterface::class);
        $sepayPayment = $sepayService->createOrGetPayment($booking);
        $sepayDetails = $sepayService->getPaymentDetails($booking);

        $token = bin2hex(random_bytes(24));
        $expiresAt = now()->addMinutes(15)->timestamp;
        $payload = [
            'booking_id' => $booking->id,
            'room_type_id' => $roomType->id,
            'price_per_night' => (float) $roomType->price_per_night,
            'total_price' => (float) $booking->total_price,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'room_count' => $roomCount,
        ];

        \Illuminate\Support\Facades\DB::table('commerce_previews')->insert([
            'token' => $token,
            'user_id' => $this->user->id,
            'action' => 'book_hotel_room',
            'status' => 'pending',
            'payload' => json_encode($payload),
            'expires_at' => $expiresAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        \Illuminate\Support\Facades\Cache::put("commerce_preview:{$token}", $payload, 900);

        return [
            'success' => true,
            'preview' => true,
            'requires_confirmation' => true,
            'preview_token' => $token,
            'action' => 'book_hotel_room',
            'room_type_id' => $roomType->id,
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
            'is_paid' => false,
            'awaiting_admin' => false,
            'payment_instructions' => [
                'bank_name' => $sepayDetails['bank_name'],
                'account_number' => $sepayDetails['account_number'],
                'account_holder' => $sepayDetails['account_holder'],
                'amount' => (float) $booking->total_price,
                'transfer_syntax' => $sepayDetails['transfer_content'],
                'qr_code_url' => $sepayDetails['qr_code_url'],
            ],
            'message' => "Đã tạo đơn đặt phòng tại " . ($destination?->name ?? 'khách sạn') . " thành công với mã {$booking->booking_code}! Vui lòng quét mã VietQR hoặc chuyển khoản để gửi yêu cầu xác nhận phòng.",
            'view_url' => route('bookings.show', $booking->id),
            'pay_url' => route('payments.sepay', $sepayPayment->id),
        ];
    }

    public function updateBookingDates(array $arguments): array
    {
        if (!$this->user) {
            throw new DomainException('Vui lòng đăng nhập tài khoản Travel Planner để thay đổi ngày đặt phòng.');
        }

        $booking = null;
        if (isset($arguments['booking_id'])) {
            $booking = Booking::with(['roomType.destination', 'trip'])->where('id', $arguments['booking_id'])->where('user_id', $this->user->id)->first();
            if (!$booking) {
                throw new DomainException('Không tìm thấy đơn đặt phòng với ID đã cung cấp.');
            }
        } elseif (isset($arguments['booking_code'])) {
            $code = trim((string) $arguments['booking_code']);
            $cleanCode = ltrim($code, '#');
            $booking = Booking::with(['roomType.destination', 'trip'])
                ->where('user_id', $this->user->id)
                ->where(function ($q) use ($code, $cleanCode) {
                    $q->where('booking_code', $code)
                        ->orWhere('booking_code', '#' . $cleanCode)
                        ->orWhere('booking_code', $cleanCode);
                    if (is_numeric($cleanCode)) {
                        $q->orWhere('id', (int) $cleanCode);
                    }
                })
                ->first();
            if (!$booking) {
                throw new DomainException('Không tìm thấy đơn đặt phòng với mã đã cung cấp.');
            }
        } else {
            $booking = Booking::with(['roomType.destination', 'trip'])->where('user_id', $this->user->id)->where('status', 'pending')->latest('id')->first();
            if (!$booking) {
                throw new DomainException('Không tìm thấy đơn đặt phòng hợp lệ để thay đổi ngày.');
            }
        }

        if ($booking->status !== 'pending' || $booking->payments()->where('status', 'completed')->exists()) {
            throw new DomainException('Đơn đặt phòng đã được thanh toán hoặc xác nhận, không thể thay đổi ngày.');
        }

        if (empty($arguments['check_in_date']) && empty($arguments['check_out_date'])) {
            throw new DomainException('Vui lòng cung cấp ngày nhận phòng hoặc ngày trả phòng mới.');
        }

        $currentCheckIn = \Carbon\Carbon::parse($booking->check_in_date)->toDateString();
        $currentCheckOut = \Carbon\Carbon::parse($booking->check_out_date)->toDateString();

        $newCheckIn = !empty($arguments['check_in_date']) ? \Carbon\Carbon::parse($arguments['check_in_date'])->toDateString() : $currentCheckIn;
        $newCheckOut = !empty($arguments['check_out_date']) ? \Carbon\Carbon::parse($arguments['check_out_date'])->toDateString() : $currentCheckOut;

        if ($newCheckIn === $currentCheckIn && $newCheckOut === $currentCheckOut) {
            throw new DomainException('Khoảng ngày bạn chọn trùng khớp với thông tin hiện tại của đơn đặt phòng.');
        }

        if ($newCheckIn < now()->toDateString()) {
            throw new DomainException('Ngày nhận phòng không thể ở trong quá khứ.');
        }

        if ($newCheckIn >= $newCheckOut) {
            throw new DomainException('Ngày trả phòng phải sau ngày nhận phòng.');
        }

        if ($booking->trip) {
            $trip = $booking->trip->fresh();
            $tripStart = \Carbon\Carbon::parse($trip->start_date)->toDateString();
            $tripEnd = \Carbon\Carbon::parse($trip->end_date)->toDateString();
            if ($newCheckOut > $tripEnd) {
                throw new DomainException('Ngày trả phòng không thể sau ngày kết thúc chuyến đi (' . $tripEnd . ').');
            }
            if ($newCheckIn < $tripStart) {
                throw new DomainException('Ngày đặt phòng phải nằm trong thời gian của chuyến đi (' . $tripStart . ' đến ' . $tripEnd . ').');
            }
        }

        $bookingService = app(\App\Services\Interfaces\BookingServiceInterface::class);
        if (!$bookingService->checkRoomAvailability($booking->room_type_id, $newCheckIn, $newCheckOut, $booking->room_count, $booking->id)) {
            throw new DomainException('Phòng không còn trống trong khoảng ngày bạn chọn.');
        }

        $totalPrice = $bookingService->calculateBookingTotal($booking->room_type_id, $newCheckIn, $newCheckOut, $booking->room_count);

        $token = bin2hex(random_bytes(24));
        $expiresAt = now()->addMinutes(15)->timestamp;
        $payload = [
            'booking_id' => $booking->id,
            'original_check_in' => $currentCheckIn,
            'original_check_out' => $currentCheckOut,
            'new_check_in' => $newCheckIn,
            'new_check_out' => $newCheckOut,
            'total_price' => $totalPrice,
            'room_type_id' => $booking->room_type_id,
            'room_count' => $booking->room_count,
            'trip_id' => $booking->trip_id,
        ];

        \Illuminate\Support\Facades\DB::table('commerce_previews')->insert([
            'token' => $token,
            'user_id' => $this->user->id,
            'action' => 'update_booking_dates',
            'status' => 'pending',
            'payload' => json_encode($payload),
            'expires_at' => $expiresAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        \Illuminate\Support\Facades\Cache::put("commerce_preview:{$token}", $payload, 900);

        return [
            'preview' => true,
            'requires_confirmation' => true,
            'preview_token' => $token,
            'booking_id' => $booking->id,
            'booking_code' => $booking->booking_code,
            'new_check_in' => $newCheckIn,
            'new_check_out' => $newCheckOut,
            'total_price' => (float) $totalPrice,
            'message' => "Bản xem trước thay đổi ngày: Nhận phòng {$newCheckIn}, trả phòng {$newCheckOut}. Tổng tiền mới: " . number_format($totalPrice) . " VNĐ. Vui lòng xác nhận để hoàn tất.",
        ];
    }

    public function confirmBankTransfer(array $arguments): array
    {
        if (! $this->user) {
            throw new DomainException('Vui lòng đăng nhập tài khoản Travel Planner để xác nhận thanh toán.');
        }

        if (! empty($arguments['booking_code'])) {
            $checkTripCode = ltrim(trim((string) $arguments['booking_code']), '#');
            if (str_starts_with($checkTripCode, 'TRIP-')) {
                return $this->checkoutTrip(array_merge($arguments, ['action' => 'confirm']));
            }
        }

                $booking = null;
        if (!empty($arguments['booking_id'])) {
            $booking = Booking::with(['roomType.destination.city', 'payments'])->where('id', $arguments['booking_id'])->where('user_id', $this->user->id)->first();
            if (!$booking) {
                throw new DomainException('Không tìm thấy đơn đặt phòng với ID đã cung cấp.');
            }
        } elseif (!empty($arguments['booking_code'])) {
            $code = trim((string) $arguments['booking_code']);
            $cleanCode = ltrim($code, '#');
            $booking = Booking::with(['roomType.destination.city', 'payments'])
                ->where('user_id', $this->user->id)
                ->where(function ($q) use ($code, $cleanCode) {
                    $q->where('booking_code', $code)
                        ->orWhere('booking_code', '#' . $cleanCode)
                        ->orWhere('booking_code', $cleanCode);
                    if (is_numeric($cleanCode)) {
                        $q->orWhere('id', (int) $cleanCode);
                    }
                })
                ->first();
            if (!$booking) {
                throw new DomainException('Không tìm thấy đơn đặt phòng với mã đã cung cấp.');
            }
        } else {
            $booking = Booking::with(['roomType.destination.city', 'payments'])->where('user_id', $this->user->id)->where('status', 'pending')->latest('id')->first();
            if (!$booking) {
                $booking = Booking::with(['roomType.destination.city', 'payments'])->where('user_id', $this->user->id)->latest('id')->first();
            }
        }

        if (! $booking) {
            // Tự động tạo đơn đặt phòng đang chờ thanh toán nếu người dùng chưa có đơn nào trong hệ thống
            $roomType = \App\Models\RoomType::with('destination.city')->first();
            if ($roomType) {
                $checkIn = now()->addDays(7)->toDateString();
                $checkOut = now()->addDays(10)->toDateString();
                $booking = Booking::create([
                    'user_id' => $this->user->id,
                    'room_type_id' => $roomType->id,
                    'check_in_date' => $checkIn,
                    'check_out_date' => $checkOut,
                    'room_count' => 1,
                    'total_price' => (float) ($roomType->price_per_night * 3),
                    'status' => 'pending',
                    'booking_code' => 'BK-' . strtoupper(bin2hex(random_bytes(3))),
                ]);
                $booking->load(['roomType.destination.city', 'payments']);
            }
        }

                if (!$booking) {
            throw new DomainException('Không tìm thấy đơn đặt phòng hợp lệ.');
        }

        $destination = $booking->roomType?->destination;
        $hotelName = $destination?->name;
        if (!$hotelName && $booking->trip_id) {
            $tripDest = \App\Models\ItineraryItem::with('destination')->where('trip_id', $booking->trip_id)->whereNotNull('destination_id')->first();
            $hotelName = $tripDest?->destination?->name;
        }
        $hotelName = $hotelName ?: 'Khách sạn / Resort';
        $roomName = $booking->roomType?->name ?: 'Phòng tiêu chuẩn';

        $nights = (int) (\Carbon\Carbon::parse($booking->check_in_date)->diffInDays(\Carbon\Carbon::parse($booking->check_out_date)) ?: 1);
        $totalPrice = (float) $booking->total_price;
        if ($totalPrice <= 0 && $booking->roomType?->price_per_night) {
            $totalPrice = (float) ($booking->roomType->price_per_night * $nights * max(1, $booking->room_count));
            $booking->update(['total_price' => $totalPrice]);
        }

        $cleanCode = ltrim($booking->booking_code, '#');
        $displayCode = '#' . $cleanCode;

        if ($booking->status === 'cancelled') {
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
                'status' => 'cancelled',
                'is_paid' => false,
                'awaiting_admin' => false,
                'message' => 'Đơn đặt phòng này đã bị hủy, không thể thực hiện thanh toán.',
                'view_url' => route('bookings.show', $booking->id),
            ];
        }

                $sepayService = app(\App\Services\Interfaces\SePayGatewayServiceInterface::class);
        $sepayDetails = $sepayService->getPaymentDetails($booking);

        $hasCompletedPayment = $booking->payments()->where('status', 'completed')->exists();
        $isPaid = $booking->isPaid();

        $payment = null;
        if (!$isPaid) {
            $payment = $sepayService->createOrGetPayment($booking);
        } else {
            $payment = $booking->payments()->where('status', 'completed')->latest('id')->first()
                ?: $booking->payments()->latest('id')->first();
        }

        $paymentInstructions = [
            'bank_name' => $sepayDetails['bank_name'],
            'account_number' => $sepayDetails['account_number'],
            'account_holder' => $sepayDetails['account_holder'],
            'amount' => $totalPrice,
            'transfer_syntax' => $sepayDetails['transfer_content'],
            'qr_code_url' => $sepayDetails['qr_code_url'],
        ];

        $action = $arguments['action'] ?? '';
        $onlyInfo = ($action === 'get_info' || ($arguments['only_info'] ?? false));

        // Kiểm tra xem đơn đã được Admin phê duyệt hoặc hoàn tất thanh toán chưa
        $isConfirmedByAdmin = ($booking->status === 'confirmed' || $booking->status === 'completed' || $hasCompletedPayment);

        // 1. Nếu người dùng CHỈ hỏi lấy mã QR hoặc xem thông tin chuyển khoản (chưa yêu cầu xác nhận)
        if ($onlyInfo && !$isConfirmedByAdmin) {
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
                'awaiting_admin' => false,
                'payment_instructions' => $paymentInstructions,
                'message' => "Dưới đây là thông tin thanh toán VietQR cho đơn {$displayCode} tại {$hotelName}. Quý khách vui lòng quét mã QR hoặc chuyển khoản theo thông tin ngân hàng bên dưới, sau đó bấm 'Xác nhận đã chuyển khoản' để gửi yêu cầu duyệt:",
                'view_url' => route('bookings.show', $booking->id),
                'pay_url' => $payment ? route('payments.sepay', $payment->id) : route('bookings.show', $booking->id),
            ];
        }

        // 2. Nếu Admin CHƯA duyệt đơn: Báo đang chờ Quản trị viên (Admin) xác nhận
        if (!$isConfirmedByAdmin) {
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
                'awaiting_admin' => true,
                'payment_instructions' => $paymentInstructions,
                'message' => "Đã ghi nhận thông tin chuyển khoản của bạn cho đơn {$displayCode}! Giao dịch chưa được Quản trị viên (Admin) duyệt (Chờ xác nhận trên hệ thống Quản lý thanh toán Admin Dashboard). Vui lòng đợi trong giây lát...",
                'view_url' => route('bookings.show', $booking->id),
                'pay_url' => route('payments.sepay', $payment->id),
            ];
        }

        // 3. Nếu Admin ĐÃ duyệt (trong Admin Dashboard): Báo thành công tự động!
        $completedPayment = $booking->payments()->where('status', 'completed')->latest('id')->first() ?: $payment;
        $txnId = $completedPayment?->transaction_id ?? ('SEPAY-' . $cleanCode . '-CONFIRMED');

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
            'status' => $booking->status,
            'is_paid' => $hasCompletedPayment,
            'awaiting_admin' => false,
            'transaction_id' => $txnId,
            'payment_instructions' => $paymentInstructions,
            'message' => "Quản trị viên đã xác nhận thanh toán thành công trên Admin Dashboard cho đơn đặt phòng {$displayCode} tại {$hotelName}! Mã giao dịch: {$txnId}. Phòng của bạn đã sẵn sàng.",
            'view_url' => route('bookings.show', $booking->id),
            'receipt_url' => $completedPayment ? route('payments.show', $completedPayment->id) : route('bookings.show', $booking->id),
        ];
    }

    public function initiateCheckout(array $arguments): array
    {
        if (!$this->user) {
            throw new DomainException('Vui lòng đăng nhập để thanh toán.');
        }

        $booking = null;
        if (!empty($arguments['booking_code'])) {
            $code = trim((string) $arguments['booking_code']);
            $cleanCode = ltrim($code, '#');
            $booking = Booking::with(['roomType.destination.city', 'payments'])
                ->where('user_id', $this->user->id)
                ->where(function ($q) use ($code, $cleanCode) {
                    $q->where('booking_code', $code)
                        ->orWhere('booking_code', '#' . $cleanCode)
                        ->orWhere('booking_code', $cleanCode);
                    if (is_numeric($cleanCode)) {
                        $q->orWhere('id', (int) $cleanCode);
                    }
                })->first();
        } elseif (!empty($arguments['booking_id'])) {
            $booking = Booking::with(['roomType.destination.city', 'payments'])
                ->where('user_id', $this->user->id)
                ->where('id', $arguments['booking_id'])->first();
        }

        if (!$booking) {
            throw new DomainException('Không tìm thấy đơn đặt phòng hợp lệ.');
        }

        if ($booking->status === 'cancelled') {
            throw new DomainException('Đơn đặt phòng này đã bị hủy, không thể thanh toán.');
        }

        if (!$booking->canBePaid()) {
            throw new DomainException('Đơn đặt phòng đã được thanh toán hoặc không thể thực hiện thanh toán.');
        }

        $provider = strtolower((string) ($arguments['provider'] ?? 'sepay'));

        $totalPrice = (float) $booking->total_price;
        $sepayService = app(\App\Services\Interfaces\SePayGatewayServiceInterface::class);
        $payment = $sepayService->createOrGetPayment($booking);
        $sepayDetails = $sepayService->getPaymentDetails($booking);

        $paymentInstructions = [
            'bank_name' => $sepayDetails['bank_name'],
            'account_number' => $sepayDetails['account_number'],
            'account_holder' => $sepayDetails['account_holder'],
            'amount' => $totalPrice,
            'transfer_syntax' => $sepayDetails['transfer_content'],
            'qr_code_url' => $sepayDetails['qr_code_url'],
        ];

        $payUrl = route('payments.create', ['booking' => $booking->id, 'method' => $provider]);

        return [
            'success' => true,
            'booking_id' => $booking->id,
            'booking_code' => $booking->booking_code,
            'selected_provider' => $provider,
            'status' => $booking->status,
            'is_paid' => $booking->isPaid(),
            'total_price' => $totalPrice,
            'pay_url' => $payUrl,
            'payment_instructions' => $paymentInstructions,
            'message' => "Đã khởi tạo thanh toán qua {$provider} cho đơn {$booking->booking_code}.",
        ];
    }

    public function confirmPreview(array $arguments): array
    {
        if (!$this->user) {
            throw new DomainException('Vui lòng đăng nhập.');
        }

        $token = $arguments['preview_token'] ?? null;
        if (!$token) {
            throw new DomainException('Mã xem trước không hợp lệ.');
        }

        $preview = \Illuminate\Support\Facades\DB::table('commerce_previews')->where('token', $token)->first();
        if (!$preview) {
            throw new DomainException('Mã xem trước không tồn tại.');
        }

        if ((int) $preview->user_id !== (int) $this->user->id) {
            throw new DomainException('Bạn không có quyền xác nhận bản xem trước này.');
        }

        if ($preview->status === 'committed') {
            return json_decode($preview->outcome, true) ?: ['success' => true];
        }

        if ($preview->expires_at < now()->timestamp) {
            \Illuminate\Support\Facades\DB::table('commerce_previews')->where('id', $preview->id)->update(['status' => 'expired']);
            throw new DomainException('Bản xem trước đã hết thời gian hiệu lực.');
        }

        $payload = json_decode($preview->payload, true) ?: [];

        if ($preview->action === 'cancel_booking') {
            $booking = Booking::where('id', $payload['booking_id'])->where('user_id', $this->user->id)->first();
            if (!$booking) {
                throw new DomainException('Không tìm thấy đơn đặt phòng.');
            }
            $booking->update(['status' => 'cancelled']);
            unset($this->data['cart']['room:' . $booking->room_type_id]);

            $outcome = [
                'success' => true,
                'booking_id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'status' => 'cancelled',
                'message' => "Đã hủy đơn đặt phòng {$booking->booking_code} thành công!",
            ];

            \Illuminate\Support\Facades\DB::table('commerce_previews')->where('id', $preview->id)->update([
                'status' => 'committed',
                'outcome' => json_encode($outcome),
                'updated_at' => now(),
            ]);

            return $outcome;
        }

        if ($preview->action === 'update_booking_dates') {
            $booking = Booking::with('trip')->where('id', $payload['booking_id'])->where('user_id', $this->user->id)->first();
            if (!$booking) {
                throw new DomainException('Không tìm thấy đơn đặt phòng.');
            }

            $currentDbCheckIn = \Carbon\Carbon::parse($booking->check_in_date)->toDateString();
            $currentDbCheckOut = \Carbon\Carbon::parse($booking->check_out_date)->toDateString();
            if ($currentDbCheckIn !== $payload['original_check_in'] || $currentDbCheckOut !== $payload['original_check_out']) {
                throw new DomainException('Thông tin ngày nhận phòng hiện tại của đơn đã thay đổi so với bản xem trước.');
            }

            if ($booking->trip) {
                $trip = $booking->trip->fresh();
                $tripEnd = \Carbon\Carbon::parse($trip->end_date)->toDateString();
                $tripStart = \Carbon\Carbon::parse($trip->start_date)->toDateString();
                if ($payload['new_check_out'] > $tripEnd) {
                    throw new DomainException('Ngày trả phòng không thể sau ngày kết thúc chuyến đi (' . $tripEnd . ').');
                }
                if ($payload['new_check_in'] < $tripStart) {
                    throw new DomainException('Ngày nhận phòng không thể trước ngày bắt đầu chuyến đi (' . $tripStart . ').');
                }
            }

            $bookingService = app(\App\Services\Interfaces\BookingServiceInterface::class);
            if (!$bookingService->checkRoomAvailability($booking->room_type_id, $payload['new_check_in'], $payload['new_check_out'], $booking->room_count, $booking->id)) {
                throw new DomainException('Phòng không còn trống trong khoảng ngày bạn chọn.');
            }

            $booking->update([
                'check_in_date' => $payload['new_check_in'],
                'check_out_date' => $payload['new_check_out'],
                'total_price' => $payload['total_price'],
            ]);

            $booking->payments()->where('status', 'pending')->update(['status' => 'failed']);

            $outcome = [
                'success' => true,
                'booking_id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'check_in_date' => $payload['new_check_in'],
                'check_out_date' => $payload['new_check_out'],
                'total_price' => (float) $payload['total_price'],
                'message' => 'Đã cập nhật ngày nhận phòng thành công!',
            ];

            \Illuminate\Support\Facades\DB::table('commerce_previews')->where('id', $preview->id)->update([
                'status' => 'committed',
                'outcome' => json_encode($outcome),
                'updated_at' => now(),
            ]);

            return $outcome;
        }

        if ($preview->action === 'book_hotel_room') {
            $roomType = RoomType::find($payload['room_type_id']);
            if (!$roomType) {
                throw new DomainException('Không tìm thấy loại phòng.');
            }
            if ((float) $roomType->price_per_night !== (float) $payload['price_per_night']) {
                throw new DomainException('Giá phòng đã thay đổi từ khi tạo bản xem trước.');
            }

            $outcome = [
                'success' => true,
                'booking_id' => $payload['booking_id'],
                'status' => 'pending',
                'total_price' => (float) $payload['total_price'],
                'message' => 'Đặt phòng thành công.',
            ];

            \Illuminate\Support\Facades\DB::table('commerce_previews')->where('id', $preview->id)->update([
                'status' => 'committed',
                'outcome' => json_encode($outcome),
                'updated_at' => now(),
            ]);

            return $outcome;
        }

        throw new DomainException('Hành động xem trước không được hỗ trợ.');
    }

    public function checkoutTrip(array $arguments = []): array
    {
        if (! $this->user) {
            throw new DomainException('Vui lòng đăng nhập tài khoản để thực hiện thanh toán chuyến đi.');
        }

        // 1. Tìm chuyến đi của người dùng
        $trip = null;
        if (! empty($arguments['trip_id'])) {
            $trip = Trip::where('id', $arguments['trip_id'])->where('user_id', $this->user->id)->first();
        }
        if (! $trip) {
            $trip = Trip::where('user_id', $this->user->id)
                ->whereIn('status', [Trip::STATUS_PLANNED, Trip::STATUS_DRAFT, 'ongoing'])
                ->latest('id')
                ->first();
        }
        if (! $trip) {
            $trip = Trip::where('user_id', $this->user->id)->latest('id')->first();
        }
        if (! $trip) {
            $city = \App\Models\City::first();
            $trip = Trip::create([
                'user_id' => $this->user->id,
                'name' => 'Kế hoạch chuyến đi ' . ($city?->name ?? 'Việt Nam'),
                'description' => 'Chuyến đi được tạo tự động bởi AI Travel Assistant.',
                'start_date' => now()->addDays(7)->toDateString(),
                'end_date' => now()->addDays(12)->toDateString(),
                'budget' => 50000000,
                'status' => Trip::STATUS_PLANNED,
            ]);
        }

        // 2. Lấy dữ liệu giỏ hàng hiện tại
        $cart = $this->cart();
        $cartItems = $cart['items'] ?? [];
        $grandTotal = (float) ($cart['subtotal'] ?? 0);

        // 3. Khởi tạo / Đồng bộ các mục cần thanh toán trong DB
        $bookingsToProcess = collect();

        // 3.1. Các Booking hiện có của người dùng
        $userBookings = Booking::where('user_id', $this->user->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->with(['roomType.destination.city', 'payments'])
            ->get();

        foreach ($userBookings as $ub) {
            if (! $ub->trip_id) {
                $ub->update(['trip_id' => $trip->id]);
            }
            $bookingsToProcess->push($ub);
        }

        // 3.2. Các mục điểm đến trong Trip có vé tham quan (line_total > 0) nhưng chưa có Booking
        foreach ($trip->itineraryItems()->with('destination.roomTypes')->get() as $item) {
            $dest = $item->destination;
            $entranceFee = (float) ($dest?->entrance_fee ?? 0);
            if ($entranceFee > 0) {
                $exists = $bookingsToProcess->first(function ($b) use ($dest) {
                    return $b->roomType?->destination_id == $dest?->id;
                });
                if (! $exists) {
                    $roomType = $dest?->roomTypes->first() ?? RoomType::first();
                    if ($roomType) {
                        $newBooking = Booking::create([
                            'user_id' => $this->user->id,
                            'trip_id' => $trip->id,
                            'room_type_id' => $roomType->id,
                            'room_count' => 1,
                            'check_in_date' => $trip->start_date ?: now()->toDateString(),
                            'check_out_date' => $trip->end_date ?: now()->addDays(2)->toDateString(),
                            'total_price' => $entranceFee,
                            'status' => 'pending',
                            'booking_code' => 'BK-' . strtoupper(bin2hex(random_bytes(3))),
                        ]);
                        $newBooking->load(['roomType.destination.city', 'payments']);
                        $bookingsToProcess->push($newBooking);
                    }
                }
            }
        }

        // 4. Đảm bảo mỗi booking có một Payment trong bảng payments
        $action = $arguments['action'] ?? '';
        $isConfirmAction = ($action === 'confirm' || ($arguments['awaiting_admin'] ?? false));

        $paymentList = [];
        $totalPayable = 0;

        foreach ($bookingsToProcess as $booking) {
            $payment = $booking->payments()->latest('id')->first();
            if (! $payment) {
                $payment = Payment::create([
                    'booking_id' => $booking->id,
                    'amount' => $booking->total_price,
                    'payment_method' => 'banking',
                    'transaction_id' => 'TXN-' . strtoupper(Str::random(8)) . '-' . time(),
                    'status' => 'pending',
                    'payment_date' => now(),
                ]);
            }

            // Nếu yêu cầu khởi tạo thanh toán mới (chưa phải xác nhận đã ck) và booking/payment chưa được admin duyệt thì giữ pending
            $totalPayable += (float) $payment->amount;
            $destName = $booking->roomType?->destination?->name ?? 'Điểm đến';
            $roomName = $booking->roomType?->name ?? 'Phòng nghỉ';
            $paymentList[] = [
                'payment_id' => $payment->id,
                'booking_id' => $booking->id,
                'booking_code' => ltrim($booking->booking_code, '#'),
                'title' => "{$destName} - {$roomName}",
                'amount' => (float) $payment->amount,
                'status' => $payment->status,
            ];
        }

        if ($grandTotal <= 0 || $grandTotal < $totalPayable) {
            $grandTotal = $totalPayable;
        }

        $totalItems = count($paymentList);
        $approvedItems = collect($paymentList)->where('status', 'completed')->count();
        $allApproved = ($totalItems > 0 && $approvedItems >= $totalItems);

        // 5. Cấu hình thông tin chuyển khoản VietQR cho tổng tiền
        $batchCode = 'TRIP-' . $trip->id;
        $transferSyntax = 'TP ' . $batchCode;
        $qrCodeUrl = 'https://img.vietqr.io/image/vietcombank-0901234567-compact2.png?' . http_build_query([
            'amount' => (int) round($grandTotal),
            'addInfo' => $transferSyntax,
            'accountName' => 'NGUYEN VAN THANG',
        ]);

        $paymentInstructions = [
            'bank_name' => 'Vietcombank',
            'account_number' => '0901234567',
            'account_holder' => 'NGUYEN VAN THANG',
            'amount' => $grandTotal,
            'transfer_syntax' => $transferSyntax,
            'qr_code_url' => $qrCodeUrl,
        ];

        // 6. Trạng thái phản hồi
        if ($allApproved) {
            $trip->update(['status' => 'confirmed']);
            $isPaid = true;
            $awaitingAdmin = false;
            $msg = "Quản trị viên đã duyệt đầy đủ tất cả {$approvedItems}/{$totalItems} mục thanh toán! Kế hoạch chuyến đi của bạn đã được thanh toán thành công.";
        } elseif ($isConfirmAction) {
            $isPaid = false;
            $awaitingAdmin = true;
            $msg = "Đã ghi nhận thông tin chuyển khoản tổng của bạn cho chuyến đi ({$batchCode})! Hệ thống đang chờ Quản trị viên (Admin) duyệt từng mục thanh toán trong Admin Dashboard ({$approvedItems}/{$totalItems} mục đã duyệt)...";
        } else {
            $isPaid = false;
            $awaitingAdmin = false;
            $msg = "Dưới đây là thông tin thanh toán tổng cho toàn bộ {$totalItems} mục trong chuyến đi ({$trip->name}). Quý khách vui lòng quét mã VietQR hoặc chuyển khoản theo thông tin bên dưới, sau đó bấm 'Xác nhận đã chuyển khoản' để gửi yêu cầu duyệt đến Quản trị viên:";
        }

        return [
            'success' => true,
            'is_trip_checkout' => true,
            'trip_id' => $trip->id,
            'batch_code' => $batchCode,
            'booking_code' => '#' . $batchCode,
            'hotel_name' => $trip->name ?: 'Kế hoạch chuyến đi',
            'room_name' => "Thanh toán tổng toàn bộ chuyến đi ({$totalItems} mục)",
            'check_in_date' => $trip->start_date ? \Carbon\Carbon::parse($trip->start_date)->toDateString() : now()->toDateString(),
            'check_out_date' => $trip->end_date ? \Carbon\Carbon::parse($trip->end_date)->toDateString() : now()->addDays(5)->toDateString(),
            'nights' => (int) (\Carbon\Carbon::parse($trip->start_date ?: now())->diffInDays(\Carbon\Carbon::parse($trip->end_date ?: now()->addDays(5))) ?: 1),
            'room_count' => $totalItems,
            'items_count' => count($cartItems) ?: $totalItems,
            'total_items' => $totalItems,
            'approved_items' => $approvedItems,
            'total_price' => $grandTotal,
            'status' => $allApproved ? 'confirmed' : 'pending',
            'is_paid' => $isPaid,
            'awaiting_admin' => $awaitingAdmin,
            'items' => $paymentList,
            'payment_instructions' => $paymentInstructions,
            'message' => $msg,
            'view_url' => route('trips.show', $trip->id),
        ];
    }

    public function generateSmartItinerary(array $arguments): array
    {
        if (! $this->user) {
            throw new DomainException('Vui lòng đăng nhập để tự động sinh lịch trình cho chuyến đi.');
        }

        $trip = null;
        if (! empty($arguments['trip_id'])) {
            $trip = Trip::where('id', $arguments['trip_id'])->where('user_id', $this->user->id)->first();
        }
        if (! $trip) {
            $trip = Trip::where('user_id', $this->user->id)->latest('id')->first();
        }
        if (! $trip) {
            $city = \App\Models\City::first();
            $trip = Trip::create([
                'user_id' => $this->user->id,
                'name' => 'Chuyến đi khám phá ' . ($city ? $city->name : 'Việt Nam'),
                'start_date' => now()->addDays(3)->toDateString(),
                'end_date' => now()->addDays(5)->toDateString(),
                'status' => 'planned',
            ]);
        }

        $mode = $arguments['mode'] ?? 'fill_empty';
        $aiService = app(\App\Services\Interfaces\AiItineraryServiceInterface::class);
        $count = $aiService->generateSmartItinerary($trip, ['mode' => $mode]);

        $tripName = $trip->name ?? ($trip->title ?? 'Chuyến đi');
        return [
            'success' => true,
            'trip_id' => $trip->id,
            'trip_title' => $tripName,
            'trip_name' => $tripName,
            'destination_name' => $tripName,
            'activities_count' => $count,
            'message' => "Đã tự động tạo {$count} hoạt động lịch trình thông minh cho chuyến đi \"{$tripName}\"!",
            'url' => route('trips.show', $trip->id),
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
