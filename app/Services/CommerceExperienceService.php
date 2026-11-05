<?php

namespace App\Services;

use App\Models\AiConversation;
use App\Models\Booking;
use App\Models\RoomType;
use App\Models\User;
use Carbon\Carbon;
use DomainException;

class CommerceExperienceService
{
    public function handle(string $role, string $action, array $parameters, array $conversation, ?User $user): array
    {
        $backend = new CommerceBackendService($user, $conversation['backend'] ?? []);
        if ($action === 'POST:session') {
            return ['conversation' => $conversation, 'data' => ['session_id' => hash('sha256', $conversation['identity']),
                'name' => $user?->name ?? 'Khách', 'operator' => $role === 'merchant' ? $user->name : null]];
        }

        if ($action === 'GET:history') {
            $items = [];
            if ($user) {
                $conv = AiConversation::where('user_id', $user->id)->latest('id')->first();
                if ($conv) {
                    $turn = 1;
                    $dbMsgs = $conv->messages()->orderBy('id', 'asc')->get();
                    foreach ($dbMsgs as $m) {
                        if ($m->role === 'user') {
                            $items[] = [
                                'kind' => 'user',
                                'text' => $m->content,
                            ];
                        } elseif ($m->role === 'assistant') {
                            $items[] = [
                                'kind' => 'assistant',
                                'turn' => $turn++,
                                'segments' => [['type' => 'text', 'text' => $m->content]],
                                'suggestions' => [],
                                'pending' => false,
                                'tools' => [],
                                'modelProvider' => config('commerce.openai_api_key') ? 'openai' : 'gemini',
                            ];
                        }
                    }
                }
            }
            if (empty($items)) {
                $sessHistory = session('commerce_chat_history', []);
                if (! empty($sessHistory)) {
                    $items = $sessHistory;
                } else {
                    $cached = \Illuminate\Support\Facades\Cache::get('chat_history:'.($user ? "user_{$user->id}" : 'session_'.session()->getId()), []);
                    if (! empty($cached)) {
                        $items = $cached;
                    }
                }
            }
            $data = ['items' => $items];
        } elseif ($action === 'DELETE:history') {
            if ($user) {
                $convs = \App\Models\AiConversation::where('user_id', $user->id)->get();
                foreach ($convs as $conv) {
                    $conv->messages()->delete();
                    $conv->delete();
                }
            }
            session()->forget('commerce_chat_history');
            session()->forget('commerce_shopping');
            \Illuminate\Support\Facades\Cache::forget('chat_history:'.($user ? "user_{$user->id}" : 'session_'.session()->getId()));
            $conversation = ['identity' => $role.':'.($user?->id ?? 'guest')];
            $data = ['ok' => true, 'items' => []];
        } elseif ($action === 'GET:memory') {
            $facts = $conversation['memory'] ?? [];
            if ($user) {
                $cached = \Illuminate\Support\Facades\Cache::get("user_memory:{$user->id}", []);
                $heldKeys = array_column($facts, 'key');
                foreach ($cached as $item) {
                    if (! in_array($item['key'] ?? '', $heldKeys, true)) {
                        $facts[] = $item;
                    }
                }
            } else {
                $guestKey = 'guest_memory:'.($parameters['session_id'] ?? session()->getId());
                $cached = \Illuminate\Support\Facades\Cache::get($guestKey, session('commerce_memory', []));
                $heldKeys = array_column($facts, 'key');
                foreach ($cached as $item) {
                    if (! in_array($item['key'] ?? '', $heldKeys, true)) {
                        $facts[] = $item;
                    }
                }
            }
            $data = ['facts' => array_values($facts)];
        } elseif (in_array($action, ['PATCH:memory', 'DELETE:memory'], true)) {
            $key = (string) ($parameters['key'] ?? '');
            $facts = $conversation['memory'] ?? [];
            if (empty($facts)) {
                if ($user) {
                    $facts = \Illuminate\Support\Facades\Cache::get("user_memory:{$user->id}", []);
                } else {
                    $guestKey = 'guest_memory:'.($parameters['session_id'] ?? session()->getId());
                    $facts = \Illuminate\Support\Facades\Cache::get($guestKey, session('commerce_memory', []));
                }
            }
            $index = array_search($key, array_column($facts, 'key'), true);
            if ($index === false) {
                throw new DomainException('Không tìm thấy bộ nhớ cần sửa.');
            }
            if ($action === 'DELETE:memory') {
                unset($facts[$index]);
                $data = ['ok' => true];
            } else {
                $validated = validator($parameters, ['value' => ['required', 'string', 'max:200']])->validate();
                if (preg_match('/sk-(?:proj-|ant-)|password|api[_ ]?key|\b\d{10,16}\b|[\w.+-]+@[\w.-]+\.[a-z]{2,}/i', $validated['value'])) {
                    throw new DomainException('Không lưu khóa, thông tin đăng nhập hoặc định danh riêng tư vào bộ nhớ.');
                }
                $facts[$index]['value'] = strip_tags($validated['value']);
                $facts[$index]['updated_at'] = now()->toIso8601String();
                $data = ['fact' => $facts[$index]];
            }
            $conversation['memory'] = array_values($facts);
            if ($user) {
                \Illuminate\Support\Facades\Cache::forever("user_memory:{$user->id}", $conversation['memory']);
            } else {
                $guestKey = 'guest_memory:'.($parameters['session_id'] ?? session()->getId());
                \Illuminate\Support\Facades\Cache::forever($guestKey, $conversation['memory']);
                session(['commerce_memory' => $conversation['memory']]);
            }
        } elseif (preg_match('~^POST:changes/([^/]+)/(apply|discard)$~', $action, $match)) {
            abort_unless($role === 'merchant', 403);
            $change = $match[2] === 'apply' ? $backend->approveChange($match[1]) : $backend->execute('discard_change', ['change_id' => $match[1]]);
            if (isset($conversation['wire'])) {
                $wire = json_decode($conversation['wire']);
                $wire->state->seen_changes->{$match[1]} = $change;
                $conversation['wire'] = json_encode($wire, JSON_THROW_ON_ERROR);
            }
            $data = ['change' => $change];
        } else {
            $data = $role === 'merchant' ? $this->merchant($action, $parameters, $backend) : $this->shopping($action, $parameters, $backend);
        }
        $conversation['backend'] = $backend->state();
        return ['conversation' => $conversation, 'data' => $data];
    }

    private function shopping(string $action, array $parameters, CommerceBackendService $backend): array
    {
        return match ($action) {
            'GET:products' => ['products' => array_merge($backend->execute('search_products', ['query' => '', 'limit' => 20]),
                RoomType::limit(50)->get()->map(fn ($room) => $backend->execute('get_product_details', ['product_id' => 'room:'.$room->id]))->all())],
            'GET:cart' => $this->cart($backend),
            'DELETE:cart' => $this->removeCartItem($parameters, $backend),
            'GET:orders' => ['orders' => $backend->execute('get_orders', ['limit' => 20])],
            default => abort(404),
        };
    }

    private function removeCartItem(array $parameters, CommerceBackendService $backend): array
    {
        $productId = (string) ($parameters['product_id'] ?? '');
        if ($productId) {
            $backend->execute('remove_from_cart', $parameters);
        }
        return $this->cart($backend);
    }

    private function cart(CommerceBackendService $backend): array
    {
        $cart = $backend->execute('get_cart', []);
        $cart['items'] = array_map(fn ($item) => array_merge($item, ['line_total' => $item['price'] * $item['quantity']]), $cart['items']);
        $cart['subtotal'] = array_sum(array_column($cart['items'], 'line_total'));
        $cart['item_count'] = array_sum(array_column($cart['items'], 'quantity'));
        return $cart;
    }

    private function merchant(string $action, array $parameters, CommerceBackendService $backend): array
    {
        if ($action === 'GET:overview') {
            return [
                'snapshot' => $backend->execute('get_business_snapshot', []),
                'needs_attention' => ['inventory' => $backend->execute('get_inventory_alerts', []),
                    'order_issues' => $backend->execute('get_order_issues', []), 'pending_changes' => $backend->pendingChanges()],
                'recent_orders' => Booking::latest()->limit(15)->get()->map(fn ($booking) => [
                    'order_id' => $booking->booking_code, 'status' => $booking->status, 'placed_at' => $booking->created_at->toIso8601String(),
                    'total' => (float) $booking->total_price, 'items' => $booking->room_count,
                ])->all(),
                'recent_changes' => array_values(array_reverse($backend->state()['changes'] ?? [])),
                'today' => $this->today(),
            ];
        }
        if ($action === 'GET:occupancy') {
            return $this->occupancy();
        }
        if ($action === 'GET:listings') {
            $listings = $backend->execute('search_listings', ['query' => (string) ($parameters['query'] ?? ''), 'limit' => 20]);
            return ['listings' => $listings, 'total' => count($listings)];
        }
        if (str_starts_with($action, 'GET:listings/')) {
            $identifier = substr($action, strlen('GET:listings/'));
            $listing = $backend->execute('get_listing', ['listing_id' => $identifier]);
            abort_unless($listing, 404);
            return ['listing' => $listing, 'pricing' => $backend->execute('get_pricing_context', ['listing_id' => $identifier])];
        }
        if ($action === 'GET:alerts') {
            return ['inventory' => $backend->execute('get_inventory_alerts', []), 'order_issues' => $backend->execute('get_order_issues', [])];
        }
        abort(404);
    }

    private function today(): array
    {
        $result = [];
        foreach (['arrivals' => 'check_in_date', 'departures' => 'check_out_date', 'new_bookings' => 'created_at'] as $name => $column) {
            $query = Booking::whereDate($column, today())->where('status', '!=', 'cancelled');
            $result[$name] = ['count' => (clone $query)->count(), 'properties' => $query->with('roomType.destination')->limit(10)->get()
                ->map(fn ($booking) => $booking->roomType?->destination?->name ?? '')->filter()->unique()->values()->all()];
        }
        return $result;
    }

    public function occupancy(): array
    {
        $from = today()->startOfWeek();
        $to = $from->copy()->addWeeks(8);
        $properties = RoomType::with('destination')->limit(30)->get()->map(function ($room) use ($from) {
            $weeks = [];
            for ($index = 0; $index < 8; $index++) {
                $start = $from->copy()->addWeeks($index);
                $nights = 0;
                $bookings = $room->bookings()->whereIn('status', ['pending', 'confirmed', 'completed'])
                    ->where('check_in_date', '<', $start->copy()->addWeek())->where('check_out_date', '>', $start)->get();
                foreach ($bookings as $booking) {
                    $begin = $booking->check_in_date->max($start);
                    $end = $booking->check_out_date->min($start->copy()->addWeek());
                    $nights += max(0, $begin->diffInDays($end, false)) * $booking->room_count;
                }
                $weeks[] = ['week_start' => $start->toDateString(), 'nightly_rate' => (float) $room->price_per_night,
                    'occupancy_pct' => $room->quantity > 0 ? min(100, round($nights / ($room->quantity * 7) * 100, 1)) : null];
            }
            return ['listing_id' => 'room:'.$room->id, 'title' => $room->destination->name.' · '.$room->name,
                'rooms' => $room->quantity, 'base_nightly_rate' => (float) $room->price_per_night, 'currency' => 'VND', 'weeks' => $weeks];
        })->all();
        return ['window' => ['from' => $from->toDateString(), 'to' => $to->toDateString()], 'properties' => $properties, 'staged_windows' => []];
    }
}
