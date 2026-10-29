<?php

namespace App\Services;

use App\Models\City;
use App\Models\Destination;
use Illuminate\Support\Str;

class ChatSuggestionService
{
    public function city(array $history): ?City
    {
        $cities = City::where('is_active', true)->get()->sortByDesc(fn ($city) => mb_strlen($city->name));
        $destinations = null;
        foreach (array_reverse($history) as $item) {
            if (($item['role'] ?? '') !== 'user') {
                continue;
            }
            $text = ' '.$this->normalize($item['content']).' ';
            foreach ($cities as $city) {
                if (str_contains($text, ' '.$this->normalize($city->name).' ')) {
                    return $city;
                }
            }
            // A named destination can supply the city for a subsequent button click.
            $destinations ??= Destination::whereHas('city', fn ($q) => $q->where('is_active', true))
                ->whereHas('category', fn ($q) => $q->where('is_active', true))
                ->with('city')->get(['id', 'name', 'city_id'])
                ->sortByDesc(fn ($destination) => mb_strlen($destination->name));
            $destination = $destinations->first(fn ($destination) => str_contains($text, ' '.$this->normalize($destination->name).' '));
            if ($destination) {
                return $destination->city;
            }
        }

        return null;
    }

    private function normalize(string $text): string
    {
        return trim(preg_replace('/[^a-z0-9]+/', ' ', Str::lower(Str::ascii($text))));
    }

    public function suggestions(array $history): array
    {
        $city = $this->city($history);
        $suffix = $city ? ' ở '.$city->name : '';
        $suggestions = [
            ['action' => 'hotels', 'label' => 'Đặt phòng'.$suffix, 'message' => 'Gợi ý nơi lưu trú'.$suffix],
            ['action' => 'attractions', 'label' => 'Tham quan'.$suffix, 'message' => 'Gợi ý điểm tham quan'.$suffix],
            ['action' => 'explore', 'label' => 'Khám phá điểm đến'.$suffix, 'message' => 'Khám phá điểm đến'.$suffix],
        ];

        // Offer the next category first, using only this visitor's latest request.
        $lastUser = collect($history)->last(fn ($item) => $item['role'] === 'user');
        $text = $this->normalize($lastUser['content'] ?? '');
        if (preg_match('/\b(luu tru|khach san|dat phong|hotel)\b/', $text)) {
            return [$suggestions[1], $suggestions[2], $suggestions[0]];
        }

        return $suggestions;
    }

    public function recommend(string $action, array $history): array
    {
        $city = $this->city($history);
        $query = Destination::query()->with('city')
            ->whereHas('city', fn ($q) => $q->where('is_active', true))
            ->whereHas('category', fn ($q) => $q->where('is_active', true))
            ->withMin('roomTypes', 'price_per_night');
        if ($city) {
            $query->where('city_id', $city->id);
        }
        if ($action === 'hotels') {
            $query->whereHas('roomTypes');
        } elseif ($action === 'attractions') {
            $query->whereDoesntHave('roomTypes');
        }
        $cards = $query->orderByDesc('is_featured')->orderByDesc('rating')->orderBy('id')->limit(3)->get()
            ->map(fn ($destination) => [
                'id' => $destination->id, 'name' => $destination->name, 'city' => $destination->city->name,
                'url' => route('destinations.show', $destination->slug, false),
                'price' => $destination->room_types_min_price_per_night !== null
                    ? 'Giá phòng từ '.number_format((float) $destination->room_types_min_price_per_night, 0, ',', '.').' đ/đêm'
                    : 'Vé tham quan: '.number_format((float) $destination->entrance_fee, 0, ',', '.').' đ',
            ])->all();
        $place = $city ? ' ở '.$city->name : '';
        $answer = $cards ? 'Đây là một số gợi ý từ dữ liệu hiện có của Travel Planner'.$place.'.'
            : 'Chưa có dữ liệu phù hợp'.$place.' trong hệ thống. Bạn có thể chọn thành phố khác.';
        if (! $city) {
            $answer .= ' Bạn muốn đi thành phố nào?';
        }
        if ($action === 'hotels' && $cards) {
            $answer .= ' Mở chi tiết để chọn ngày và kiểm tra phòng; đây là giá niêm yết.';
        }

        return ['answer' => $answer, 'recommendations' => $cards];
    }
}
