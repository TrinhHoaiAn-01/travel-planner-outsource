<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoomTypeRequest;
use App\Http\Requests\Admin\UpdateRoomTypeRequest;
use App\Services\Interfaces\RoomTypeManagementServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller Quản trị Loại phòng lưu trú dành cho Quản trị viên (Admin Panel).
 * Thành viên phụ trách: Trần Văn Trọng (22211TT0337) - Mục 10.
 * Tuân thủ chuẩn Controller mỏng (Thin Controller), giao tiếp qua Service Interface.
 */
class RoomTypeController extends Controller
{
    /**
     * Khởi tạo Controller tiêm phụ thuộc RoomTypeManagementServiceInterface.
     *
     * @param RoomTypeManagementServiceInterface $roomTypeService
     */
    public function __construct(
        private readonly RoomTypeManagementServiceInterface $roomTypeService
    ) {
    }

    /**
     * Hiển thị danh sách các loại phòng và bộ lọc tìm kiếm.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->query('search', ''),
            'destination_id' => $request->query('destination_id', ''),
        ];

        $roomTypes = $this->roomTypeService->getRoomTypes($filters, 10);
        $statistics = $this->roomTypeService->getRoomTypeStatistics();
        $destinations = $this->roomTypeService->getDestinationsForSelect();

        return view('admin.room-types.index', [
            'roomTypes' => $roomTypes,
            'filters' => $filters,
            'statistics' => $statistics,
            'destinations' => $destinations,
        ]);
    }

    /**
     * Xử lý thêm mới loại phòng lưu trú.
     *
     * @param StoreRoomTypeRequest $request
     * @return RedirectResponse
     */
    public function store(StoreRoomTypeRequest $request): RedirectResponse
    {
        $this->roomTypeService->createRoomType($request->validated());

        return redirect()
            ->route('admin.room-types.index')
            ->with('success', 'Thêm mới loại phòng lưu trú thành công.');
    }

    /**
     * Xử lý cập nhật thông tin loại phòng.
     *
     * @param UpdateRoomTypeRequest $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(UpdateRoomTypeRequest $request, int $id): RedirectResponse
    {
        $this->roomTypeService->updateRoomType($id, $request->validated());

        return redirect()
            ->route('admin.room-types.index')
            ->with('success', 'Cập nhật thông tin loại phòng thành công.');
    }

    /**
     * Xử lý xóa loại phòng lưu trú khỏi hệ thống.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->roomTypeService->deleteRoomType($id);

        return redirect()
            ->route('admin.room-types.index')
            ->with('success', 'Xóa loại phòng lưu trú thành công.');
    }
}

