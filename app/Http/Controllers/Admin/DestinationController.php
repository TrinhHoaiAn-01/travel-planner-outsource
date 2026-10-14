<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDestinationRequest;
use App\Http\Requests\Admin\UpdateDestinationRequest;
use App\Models\Destination;
use App\Services\Interfaces\DestinationManagementServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DestinationController extends Controller
{
    /**
     * Khởi tạo Controller với DestinationManagementServiceInterface thông qua Dependency Injection.
     *
     * @param DestinationManagementServiceInterface $destinationManagementService
     */
    public function __construct(
        private readonly DestinationManagementServiceInterface $destinationManagementService
    ) {
    }

    /**
     * Hiển thị danh sách các địa điểm du lịch theo giao diện Admin Panel.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->query('search', ''),
            'city_id' => $request->query('city_id', 'all'),
            'category_id' => $request->query('category_id', 'all'),
        ];

        $destinations = $this->destinationManagementService->getDestinations($filters, 10);
        $cities = $this->destinationManagementService->getCities();
        $categories = $this->destinationManagementService->getCategories();
        $statistics = $this->destinationManagementService->getDestinationStatistics();

        return view('admin.destinations.index', [
            'destinations' => $destinations,
            'cities' => $cities,
            'categories' => $categories,
            'filters' => $filters,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Hiển thị giao diện tạo mới địa điểm du lịch (hoặc chuyển tiếp sang trang danh sách mở modal).
     *
     * @return View
     */
    public function create(): View
    {
        $cities = $this->destinationManagementService->getCities();
        $categories = $this->destinationManagementService->getCategories();

        return view('admin.destinations.index', [
            'destinations' => $this->destinationManagementService->getDestinations([], 10),
            'cities' => $cities,
            'categories' => $categories,
            'filters' => ['search' => '', 'city_id' => 'all', 'category_id' => 'all'],
            'statistics' => $this->destinationManagementService->getDestinationStatistics(),
            'openCreateModal' => true,
        ]);
    }

    /**
     * Xử lý lưu trữ địa điểm du lịch mới vào cơ sở dữ liệu.
     *
     * @param StoreDestinationRequest $request
     * @return RedirectResponse
     */
    public function store(StoreDestinationRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image_file')) {
            $data['image_file'] = $request->file('image_file');
        }

        $this->destinationManagementService->createDestination($data);

        return redirect()
            ->route('admin.destinations.index')
            ->with('success', 'Thêm mới địa điểm du lịch thành công.');
    }

    /**
     * Xem chi tiết thông tin địa điểm du lịch (hỗ trợ cả JSON cho modal và View cho truy cập trực tiếp).
     *
     * @param Request $request
     * @param mixed $destination
     * @return View|JsonResponse
     */
    public function show(Request $request, mixed $destination): View|JsonResponse
    {
        $destinationModel = $this->resolveDestinationModel($destination);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $destinationModel->id,
                    'code' => sprintf('#DST-%03d', $destinationModel->id),
                    'name' => $destinationModel->name,
                    'slug' => $destinationModel->slug,
                    'city_id' => $destinationModel->city_id,
                    'city_name' => $destinationModel->city?->name,
                    'category_id' => $destinationModel->category_id,
                    'category_name' => $destinationModel->category?->name,
                    'description' => $destinationModel->description,
                    'address' => $destinationModel->address,
                    'entrance_fee' => (float) $destinationModel->entrance_fee,
                    'entrance_fee_formatted' => number_format((float) $destinationModel->entrance_fee, 0, ',', '.') . 'đ',
                    'opening_time' => $destinationModel->opening_time ? substr($destinationModel->opening_time, 0, 5) : '',
                    'closing_time' => $destinationModel->closing_time ? substr($destinationModel->closing_time, 0, 5) : '',
                    'duration' => $destinationModel->duration,
                    'rating' => (float) $destinationModel->rating,
                    'is_featured' => (bool) $destinationModel->is_featured,
                    'has_hotel' => $destinationModel->has_hotel,
                    'primary_image' => $destinationModel->primaryImage?->image_path ?? $destinationModel->images->first()?->image_path,
                    'images' => $destinationModel->images->map(fn ($img) => [
                        'id' => $img->id,
                        'url' => $img->image_path,
                        'is_primary' => (bool) $img->is_primary,
                    ]),
                    'room_types' => $destinationModel->roomTypes->map(fn ($room) => [
                        'id' => $room->id,
                        'name' => $room->name,
                        'price' => number_format((float) $room->price_per_night, 0, ',', '.') . 'đ',
                        'capacity' => $room->capacity,
                    ]),
                ],
            ]);
        }

        return view('admin.destinations.index', [
            'destinations' => $this->destinationManagementService->getDestinations([], 10),
            'cities' => $this->destinationManagementService->getCities(),
            'categories' => $this->destinationManagementService->getCategories(),
            'filters' => ['search' => '', 'city_id' => 'all', 'category_id' => 'all'],
            'statistics' => $this->destinationManagementService->getDestinationStatistics(),
            'viewingDestination' => $destinationModel,
        ]);
    }

    /**
     * Lấy dữ liệu điểm đến để hiển thị form chỉnh sửa qua modal.
     *
     * @param Request $request
     * @param mixed $destination
     * @return JsonResponse|View
     */
    public function edit(Request $request, mixed $destination): JsonResponse|View
    {
        return $this->show($request, $destination);
    }

    /**
     * Cập nhật thông tin điểm đến du lịch.
     *
     * @param UpdateDestinationRequest $request
     * @param mixed $destination
     * @return RedirectResponse
     */
    public function update(UpdateDestinationRequest $request, mixed $destination): RedirectResponse
    {
        $destinationModel = $this->resolveDestinationModel($destination);
        $data = $request->validated();

        if ($request->hasFile('image_file')) {
            $data['image_file'] = $request->file('image_file');
        }

        $this->destinationManagementService->updateDestination($destinationModel->id, $data);

        return redirect()
            ->route('admin.destinations.index')
            ->with('success', "Cập nhật thông tin địa điểm '{$destinationModel->name}' thành công.");
    }

    /**
     * Xử lý xóa một địa điểm du lịch (Tuân thủ BR-17).
     *
     * @param mixed $destination
     * @return RedirectResponse
     */
    public function destroy(mixed $destination): RedirectResponse
    {
        $destinationModel = $this->resolveDestinationModel($destination);

        try {
            $this->destinationManagementService->deleteDestination($destinationModel->id);

            return redirect()
                ->route('admin.destinations.index')
                ->with('success', "Đã xóa địa điểm '{$destinationModel->name}' thành công.");
        } catch (ValidationException $e) {
            $errorMessage = $e->validator->errors()->first('delete') ?? $e->getMessage();

            return redirect()
                ->route('admin.destinations.index')
                ->with('error', $errorMessage);
        }
    }

    /**
     * Tìm bản ghi Destination theo ID hoặc Slug một cách an toàn.
     *
     * @param mixed $destination
     * @return Destination
     */
    private function resolveDestinationModel(mixed $destination): Destination
    {
        if ($destination instanceof Destination) {
            return $destination->loadMissing(['city', 'category', 'images', 'primaryImage', 'roomTypes']);
        }

        if (is_numeric($destination)) {
            return Destination::with(['city', 'category', 'images', 'primaryImage', 'roomTypes'])
                ->findOrFail((int) $destination);
        }

        return Destination::with(['city', 'category', 'images', 'primaryImage', 'roomTypes'])
            ->where('slug', (string) $destination)
            ->firstOrFail();
    }
}
