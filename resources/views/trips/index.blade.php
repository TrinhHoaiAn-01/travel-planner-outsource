<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chuyến đi của tôi - Travel Planner Travel</title>
  <!-- Bootstrap 5.3.3 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <!-- Bootstrap Icons 1.11.3 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- CSS hệ thống thiết kế chung và giao diện Trips -->
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('css/trip.css') }}">
</head>
<body>

  <!-- Thanh điều hướng chính (Main Navigation Bar) -->
  <nav class="navbar navbar-expand-lg navbar-travelplanner">
    <div class="container">
      <a class="navbar-brand navbar-brand-logo" href="{{ route('home') }}">
        <div class="logo-icon"><i class="bi bi-compass"></i></div>
        <span>TRAVEL PLANNER</span>
      </a>
      <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
          <li class="nav-item">
            <a class="nav-link nav-link-travelplanner" href="{{ route('home') }}"><i class="bi bi-house-door"></i> Trang chủ</a>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-link-travelplanner" href="#"><i class="bi bi-geo-alt"></i> Địa điểm</a>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-link-travelplanner active" href="{{ route('trips.index') }}"><i class="bi bi-map"></i> Chuyến đi</a>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-link-travelplanner" href="#"><i class="bi bi-heart"></i> Yêu thích</a>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-link-travelplanner" href="#"><i class="bi bi-calendar-check"></i> Booking</a>
          </li>
        </ul>

        <div class="d-flex align-items-center gap-2">
          @if(Auth::check() && Auth::user()->role === 'admin')
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-dark btn-sm rounded-pill px-3">
              <i class="bi bi-shield-lock me-1"></i> Admin
            </a>
          @endif
          @auth
            <div class="dropdown">
              <button class="btn user-avatar-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="{{ Auth::user()->avatar ?: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=120&q=80' }}" alt="Avatar" class="user-avatar-img me-1">
                <span class="fw-semibold text-dark small">{{ Auth::user()->name }}</span>
              </button>
              <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2">
                <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Hồ sơ cá nhân</a></li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-heart me-2"></i>Địa điểm yêu thích</a></li>
                <li><a class="dropdown-item active" href="{{ route('trips.index') }}"><i class="bi bi-map me-2"></i>Chuyến đi của tôi</a></li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-bag-check me-2"></i>Lịch sử đặt phòng</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger border-0 bg-transparent w-100 text-start">
                      <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
                    </button>
                  </form>
                </li>
              </ul>
            </div>
          @else
            <a href="{{ route('login') }}" class="btn btn-primary btn-sm rounded-pill px-3">Đăng nhập</a>
          @endauth
        </div>
      </div>
    </div>
  </nav>

  <!-- Phần nội dung chính (Main Content) -->
  <main class="py-5">
    <div class="container">
      <!-- Đường dẫn phân cấp (Breadcrumb) -->
      <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
          <li class="breadcrumb-item active" aria-current="page">Chuyến đi của tôi</li>
        </ol>
      </nav>

      <!-- Tiêu đề trang kèm nút hành động -->
      <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
          <h2 class="fw-bold mb-1">Hành Trình & Chuyến Đi</h2>
          <p class="text-muted small mb-0">Quản lý lịch trình, hoạt động từng ngày và theo dõi chi tiêu du lịch của bạn</p>
        </div>
        <div>
          <a href="{{ Route::has('trips.create') ? route('trips.create') : url('/trips/create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Tạo chuyến đi mới
          </a>
        </div>
      </div>

      <!-- Thanh tìm kiếm và bộ lọc thời gian -->
      <div class="card-custom p-3 p-md-4 mb-4 shadow-sm bg-white border">
        <form action="{{ route('trips.index') }}" method="GET" class="row g-3 align-items-center">
          @if(request('status') && request('status') !== 'all')
            <input type="hidden" name="status" value="{{ request('status') }}">
          @endif

          <!-- Tìm theo từ khóa -->
          <div class="col-12 col-md-5">
            <label class="form-label small fw-bold text-muted mb-1"><i class="bi bi-search me-1 text-primary"></i>Tìm kiếm</label>
            <div class="input-group">
              <input type="text" name="search" class="form-control" placeholder="Tên chuyến đi hoặc mô tả..." value="{{ $searchKeyword ?? '' }}">
            </div>
          </div>

          <!-- Từ ngày -->
          <div class="col-6 col-md-3">
            <label class="form-label small fw-bold text-muted mb-1"><i class="bi bi-calendar-event me-1 text-primary"></i>Từ ngày</label>
            <input type="date" name="start_date" class="form-control" value="{{ $startDate ?? '' }}">
          </div>

          <!-- Đến ngày -->
          <div class="col-6 col-md-3">
            <label class="form-label small fw-bold text-muted mb-1"><i class="bi bi-calendar-check me-1 text-primary"></i>Đến ngày</label>
            <input type="date" name="end_date" class="form-control" value="{{ $endDate ?? '' }}">
          </div>

          <!-- Nút tìm kiếm & đặt lại -->
          <div class="col-12 col-md-1 d-flex align-items-end gap-1 mt-md-4 pt-md-2">
            <button type="submit" class="btn btn-primary w-100 px-2" title="Tìm kiếm"><i class="bi bi-search"></i></button>
            @if(!empty($searchKeyword) || !empty($startDate) || !empty($endDate) || (request('status') && request('status') !== 'all'))
              <a href="{{ route('trips.index') }}" class="btn btn-outline-secondary px-2" title="Đặt lại"><i class="bi bi-arrow-counterclockwise"></i></a>
            @endif
          </div>
        </form>
      </div>

      <!-- Các tab lọc theo trạng thái (Filter Tabs) -->
      <ul class="nav nav-pills mb-4 border-bottom pb-3 gap-2">
        <li class="nav-item">
          <a class="nav-link rounded-pill px-3 {{ $currentStatus === 'all' || empty($currentStatus) ? 'active' : '' }}" href="{{ route('trips.index', array_filter(array_merge(request()->query(), ['status' => 'all']))) }}">Tất cả ({{ $counts['all'] ?? 0 }})</a>
        </li>
        <li class="nav-item">
          <a class="nav-link rounded-pill px-3 {{ $currentStatus === 'upcoming' || $currentStatus === 'planned' ? 'active' : '' }}" href="{{ route('trips.index', array_filter(array_merge(request()->query(), ['status' => 'planned']))) }}">Sắp tới ({{ $counts['planned'] ?? 0 }})</a>
        </li>
        <li class="nav-item">
          <a class="nav-link rounded-pill px-3 {{ $currentStatus === 'ongoing' ? 'active' : '' }}" href="{{ route('trips.index', array_filter(array_merge(request()->query(), ['status' => 'ongoing']))) }}">Đang diễn ra ({{ $counts['ongoing'] ?? 0 }})</a>
        </li>
        <li class="nav-item">
          <a class="nav-link rounded-pill px-3 {{ $currentStatus === 'completed' ? 'active' : '' }}" href="{{ route('trips.index', array_filter(array_merge(request()->query(), ['status' => 'completed']))) }}">Đã hoàn thành ({{ $counts['completed'] ?? 0 }})</a>
        </li>
      </ul>

      <!-- Lưới danh sách chuyến đi (Trips List Grid) -->
      <div class="row g-4" id="tripsGrid">
        @forelse($trips as $trip)
        @php
            // Tính số ngày và số đêm thực tế của chuyến đi
            $startDateObj = \Carbon\Carbon::parse($trip->start_date);
            $endDateObj = \Carbon\Carbon::parse($trip->end_date);
            $days = $startDateObj->diffInDays($endDateObj) + 1;
            $nights = max(0, $days - 1);
            $durationText = $days . ' ngày ' . $nights . ' đêm';

            // Phân loại nhãn và màu sắc badge trạng thái
            $statusClass = 'badge-soft-primary';
            $statusLabel = 'Sắp tới';
            $dataStatus = 'upcoming';

            if ($trip->status === 'ongoing' || ($startDateObj->isPast() && $endDateObj->isFuture() && $trip->status !== 'completed')) {
                $statusClass = 'badge-soft-warning';
                $statusLabel = 'Đang diễn ra';
                $dataStatus = 'ongoing';
            } elseif ($trip->status === 'completed') {
                $statusClass = 'badge-soft-secondary';
                $statusLabel = 'Đã hoàn thành';
                $dataStatus = 'completed';
            } elseif ($trip->status === 'draft') {
                $statusClass = 'badge-soft-secondary';
                $statusLabel = 'Bản nháp';
                $dataStatus = 'draft';
            }

            // Tính tổng chi phí thực tế cho chuyến đi đã hoàn thành
            $actualExpenses = $trip->expenses ? $trip->expenses->sum('amount') : 0;

            // Hình ảnh đại diện cho chuyến đi
            $coverImage = $trip->cover_image_url ?? 'https://images.unsplash.com/photo-1544644181-1484b3fdfc62?auto=format&fit=crop&w=600&q=80';
            $lowerName = mb_strtolower($trip->name, 'UTF-8');
            if (str_contains($lowerName, 'hạ long') || str_contains($lowerName, 'ha long')) {
                $coverImage = 'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=600&q=80';
            } elseif (str_contains($lowerName, 'sa pa') || str_contains($lowerName, 'sapa') || str_contains($lowerName, 'fansipan')) {
                $coverImage = 'https://images.unsplash.com/photo-1570789210967-2cac24afeb00?auto=format&fit=crop&w=600&q=80';
            }
        @endphp
        <div class="col-md-6 col-lg-4 trip-item" data-status="{{ $dataStatus }}" id="trip-card-{{ $trip->id }}">
          <div class="card-custom card-hover h-100 d-flex flex-column">
            <!-- Hình ảnh và menu tùy chọn -->
            <div class="card-img-wrap position-relative">
              <img src="{{ $coverImage }}" alt="{{ $trip->name }}">
              <span class="badge badge-travelplanner {{ $statusClass }} badge-floating-top-left">{{ $statusLabel }}</span>
              <div class="dropdown position-absolute top-0 end-0 m-2">
                <button class="btn btn-sm btn-light bg-white rounded-circle shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                  <li><a class="dropdown-item" href="{{ Route::has('trips.edit') ? route('trips.edit', $trip->id) : url('/trips/' . $trip->id . '/edit') }}"><i class="bi bi-pencil me-2"></i>Chỉnh sửa</a></li>
                  <li><a class="dropdown-item" href="{{ url('/trips/' . $trip->id . '/budget') }}"><i class="bi bi-wallet2 me-2"></i>{{ $trip->status === 'completed' ? 'Xem ngân sách' : 'Quản lý ngân sách' }}</a></li>
                  <li><hr class="dropdown-divider"></li>
                  <li>
                    <button type="button" class="dropdown-item text-danger border-0 bg-transparent w-100 text-start" onclick="confirmDeleteTrip({{ $trip->id }}, '{{ addslashes($trip->name) }}')">
                      <i class="bi bi-trash me-2"></i>Xóa chuyến đi
                    </button>
                  </li>
                </ul>
              </div>
            </div>

            <!-- Thân thẻ thông tin chuyến đi -->
            <div class="p-3 d-flex flex-column flex-grow-1">
              <div class="text-muted small mb-1">
                <i class="bi bi-calendar-event me-1 text-primary"></i> {{ $startDateObj->format('d/m/Y') }} - {{ $endDateObj->format('d/m/Y') }} • <strong class="text-dark">{{ $durationText }}</strong>
              </div>
              <h5 class="fw-bold mb-2 fs-6">
                <a href="{{ Route::has('trips.show') ? route('trips.show', $trip->id) : url('/trips/' . $trip->id) }}" class="text-dark">{{ $trip->name }}</a>
              </h5>
              <p class="text-muted small mb-3 flex-grow-1">{{ $trip->description ?: 'Chưa có mô tả cho chuyến đi này.' }}</p>
              
              <!-- Chân thẻ: Thông tin ngân sách / chi phí và các nút hành động -->
              <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                <div>
                  @if($trip->status === 'completed')
                    <span class="text-muted small d-block">Tổng chi thực tế</span>
                    <span class="fw-bold text-primary">{{ number_format($actualExpenses, 0, ',', '.') }}đ</span>
                  @else
                    <span class="text-muted small d-block">Ngân sách dự kiến</span>
                    <span class="fw-bold text-success">{{ number_format($trip->budget, 0, ',', '.') }}đ</span>
                  @endif
                </div>
                <div class="d-flex gap-2">
                  <a href="{{ url('/trips/' . $trip->id . '/budget') }}" class="btn btn-sm btn-outline-secondary rounded-pill" title="Ngân sách"><i class="bi bi-wallet2"></i></a>
                  <a href="{{ Route::has('trips.show') ? route('trips.show', $trip->id) : url('/trips/' . $trip->id) }}" class="btn btn-sm btn-primary rounded-pill px-3">
                    {{ $trip->status === 'completed' ? 'Xem lại' : 'Lịch trình' }}
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
        @empty
        <div class="col-12">
          <div class="empty-state">
            <div class="empty-state-icon"><i class="bi bi-compass"></i></div>
            <h4 class="fw-bold">Chưa có chuyến đi nào</h4>
            <p class="text-muted">Không tìm thấy chuyến đi nào phù hợp với yêu cầu của bạn.</p>
            <a href="{{ Route::has('trips.create') ? route('trips.create') : url('/trips/create') }}" class="btn btn-primary rounded-pill px-4 mt-2">
              <i class="bi bi-plus-lg me-1"></i> Tạo chuyến đi mới
            </a>
          </div>
        </div>
        @endforelse
      </div>

      <!-- Phân trang danh sách chuyến đi -->
      @if($trips->hasPages())
        <div class="d-flex justify-content-center mt-5">
          {{ $trips->links() }}
        </div>
      @endif
    </div>
  </main>

  <!-- Hộp thoại xác nhận xóa chuyến đi (Delete Trip Modal) -->
  <div class="modal fade" id="deleteTripModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form id="deleteTripForm" method="POST" action="">
          @csrf
          @method('DELETE')
          <div class="modal-header">
            <h5 class="modal-title fw-bold text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>Xác nhận xóa chuyến đi</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Bạn có chắc chắn muốn xóa chuyến đi <strong id="tripDeleteName">Khám phá Đà Nẵng</strong>?</p>
            <p class="text-muted small mb-0">Hành động này sẽ xóa toàn bộ lịch trình chi tiết và bảng ngân sách đi kèm của chuyến đi này.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy bỏ</button>
            <button type="submit" class="btn btn-danger px-4" id="btnExecuteDeleteTrip">Xóa chuyến đi</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Chân trang (Footer) -->
  <footer class="footer-travelplanner">
    <div class="container text-center text-md-start">
      <div class="row g-4">
        <div class="col-lg-4 col-md-6">
          <div class="navbar-brand-logo mb-3 text-white">
            <div class="logo-icon"><i class="bi bi-compass"></i></div>
            <span>TRAVEL PLANNER</span>
          </div>
          <p class="small">Hệ thống quản lý và lập kế hoạch chuyến du lịch thông minh, kết nối mọi trải nghiệm khám phá của bạn.</p>
        </div>
        <div class="col-lg-2 col-md-6">
          <h5>Liên kết</h5>
          <a href="{{ route('home') }}">Trang chủ</a>
          <a href="#">Địa điểm</a>
          <a href="{{ route('trips.index') }}">Chuyến đi</a>
          <a href="#">Yêu thích</a>
        </div>
        <div class="col-lg-3 col-md-6">
          <h5>Tài khoản</h5>
          <a href="#">Hồ sơ cá nhân</a>
          <a href="#">Đơn đặt phòng</a>
          @auth
            <a href="#" onclick="event.preventDefault(); document.getElementById('footerLogoutForm').submit();">Đăng xuất</a>
            <form id="footerLogoutForm" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
          @else
            <a href="{{ route('login') }}">Đăng nhập</a>
          @endauth
          @if(Auth::check() && Auth::user()->role === 'admin')
            <a href="{{ route('admin.dashboard') }}">Admin Dashboard</a>
          @endif
        </div>
        <div class="col-lg-3 col-md-6">
          <h5>Hỗ trợ</h5>
          <p class="small mb-1"><i class="bi bi-envelope me-2"></i>support@travelplanner.com</p>
          <p class="small mb-1"><i class="bi bi-telephone me-2"></i>1900 6868</p>
          <p class="small"><i class="bi bi-geo-alt me-2"></i>Đà Nẵng, Việt Nam</p>
        </div>
      </div>
      <div class="footer-bottom text-center">
        <p class="mb-0">&copy; 2026 Travel Planner Travel Platform. All rights reserved.</p>
      </div>
    </div>
  </footer>

  <!-- Bootstrap 5 Bundle JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Hàm hiển thị modal xác nhận xóa chuyến đi
    function confirmDeleteTrip(tripId, tripName) {
      document.getElementById('tripDeleteName').innerText = `"${tripName}"`;
      document.getElementById('deleteTripForm').action = `/trips/${tripId}`;
      const modal = new bootstrap.Modal(document.getElementById('deleteTripModal'));
      modal.show();
    }
  </script>
</body>
</html>
