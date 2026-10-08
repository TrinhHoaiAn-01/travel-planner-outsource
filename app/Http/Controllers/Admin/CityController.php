<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCityRequest;
use App\Http\Requests\Admin\UpdateCityRequest;
use App\Services\Interfaces\CityManagementServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CityController extends Controller
{
    /**
     * Khởi tạo Controller với CityManagementServiceInterface thông qua Dependency Injection.
     *
     * @param CityManagementServiceInterface $cityService
     */
    public function __construct(
        private readonly CityManagementServiceInterface $cityService
    ) {
    }

    /**
     * Hiển thị danh sách các tỉnh / thành phố theo giao diện Admin Panel.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->query('search', ''),
            'region' => $request->query('region', 'all'),
            'status' => $request->query('status', 'all'),
        ];

        $cities = $this->cityService->getCities($filters, 10);
        $statistics = $this->cityService->getCityStatistics();

        return view('admin.cities.index', [
            'cities' => $cities,
            'filters' => $filters,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Xử lý thêm mới một tỉnh / thành phố từ Modal form.
     *
     * @param StoreCityRequest $request
     * @return RedirectResponse
     */
    public function store(StoreCityRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image_file')) {
            $data['image_file'] = $request->file('image_file');
        }

        $this->cityService->createCity($data);

        return redirect()
            ->route('admin.cities.index')
            ->with('success', 'Thêm mới tỉnh / thành phố thành công.');
    }

    /**
     * Xử lý cập nhật thông tin tỉnh / thành phố.
     *
     * @param UpdateCityRequest $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(UpdateCityRequest $request, int $id): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image_file')) {
            $data['image_file'] = $request->file('image_file');
        }

        $this->cityService->updateCity($id, $data);

        return redirect()
            ->route('admin.cities.index')
            ->with('success', 'Cập nhật tỉnh / thành phố thành công.');
    }

    /**
     * Xử lý xóa một tỉnh / thành phố (Tuân thủ quy tắc BR-17).
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $this->cityService->deleteCity($id);

            return redirect()
                ->route('admin.cities.index')
                ->with('success', 'Xóa tỉnh / thành phố thành công.');
        } catch (ValidationException $e) {
            $errorMessage = $e->validator->errors()->first('delete') ?? $e->getMessage();

            return redirect()
                ->route('admin.cities.index')
                ->with('error', $errorMessage);
        }
    }
}
