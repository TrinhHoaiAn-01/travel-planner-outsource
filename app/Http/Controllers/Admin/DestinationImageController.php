<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDestinationImageRequest;
use App\Services\Interfaces\DestinationImageServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DestinationImageController extends Controller
{
    /**
     * Khởi tạo Controller với DestinationImageServiceInterface qua Dependency Injection.
     *
     * @param DestinationImageServiceInterface $destinationImageService
     */
    public function __construct(
        private readonly DestinationImageServiceInterface $destinationImageService
    ) {
    }

    /**
     * Hiển thị thư viện hình ảnh theo địa điểm du lịch đã chọn.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $destinations = $this->destinationImageService->getDestinationsList();

        // Ưu tiên chọn địa điểm theo tham số request, nếu không mặc định lấy địa điểm đầu tiên (hoặc Bà Nà Hills)
        $selectedDestinationId = $request->query('destination_id');

        if (! $selectedDestinationId && $destinations->isNotEmpty()) {
            // Ưu tiên chọn Sun World Bà Nà Hills nếu có trong CSDL để khớp 100% ảnh thiết kế
            $baNaHills = $destinations->first(fn ($d) => str_contains(mb_strtolower($d->name), 'bà nà hills'));
            $selectedDestinationId = $baNaHills ? $baNaHills->id : $destinations->first()->id;
        }

        $selectedDestination = $destinations->firstWhere('id', (int) $selectedDestinationId);
        $images = $this->destinationImageService->getImagesByDestination((int) $selectedDestinationId);

        return view('admin.destination-images.index', [
            'destinations' => $destinations,
            'selectedDestinationId' => (int) $selectedDestinationId,
            'selectedDestination' => $selectedDestination,
            'images' => $images,
        ]);
    }

    /**
     * Xử lý tải lên hoặc thêm hình ảnh mới vào thư viện.
     *
     * @param StoreDestinationImageRequest $request
     * @return RedirectResponse
     */
    public function store(StoreDestinationImageRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image_file')) {
            $data['image_file'] = $request->file('image_file');
        }

        try {
            $this->destinationImageService->uploadImage($data);

            return redirect()
                ->route('admin.destination-images.index', ['destination_id' => $data['destination_id']])
                ->with('success', 'Tải lên hình ảnh mới thành công.');
        } catch (ValidationException $e) {
            $errorMessage = $e->validator->errors()->first() ?? $e->getMessage();

            return redirect()
                ->route('admin.destination-images.index', ['destination_id' => $data['destination_id']])
                ->with('error', $errorMessage);
        }
    }

    /**
     * Đặt hình ảnh làm ảnh đại diện chính của địa điểm.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function setPrimary(int $id): RedirectResponse
    {
        $this->destinationImageService->setPrimaryImage($id);

        return redirect()
            ->back()
            ->with('success', 'Đã cập nhật ảnh đại diện chính thành công.');
    }

    /**
     * Xóa một hình ảnh khỏi thư viện.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->destinationImageService->deleteImage($id);

        return redirect()
            ->back()
            ->with('success', 'Đã xóa hình ảnh khỏi thư viện thành công.');
    }
}
