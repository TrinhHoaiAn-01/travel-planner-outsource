<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Travel Planner - Khám phá và Quản lý Chuyến đi Tuyệt vời</title>
  <!-- Bootstrap 5.3.3 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <!-- Bootstrap Icons 1.11.3 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- CSS hệ thống thiết kế chung và giao diện điểm đến -->
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('css/destination.css') }}">
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
            <a class="nav-link nav-link-travelplanner active" href="{{ route('home') }}"><i class="bi bi-house-door"></i> Trang chủ</a>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-link-travelplanner" href="#"><i class="bi bi-geo-alt"></i> Địa điểm</a>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-link-travelplanner" href="{{ route('trips.index') }}"><i class="bi bi-map"></i> Chuyến đi</a>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-link-travelplanner" href="#"><i class="bi bi-heart"></i> Yêu thích</a>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-link-travelplanner" href="#"><i class="bi bi-calendar-check"></i> Booking</a>
          </li>
        </ul>

        <div class="d-flex align-items-center gap-2">
          <!-- Phím tắt Admin nếu người dùng là Admin -->
          @if(Auth::check() && Auth::user()->role === 'admin')
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-dark btn-sm rounded-pill px-3">
              <i class="bi bi-shield-lock me-1"></i> Admin
            </a>
          @endif

          <!-- Khối người dùng hoặc đăng nhập -->
          @auth
            <div class="dropdown">
              <button class="btn user-avatar-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="{{ Auth::user()->avatar ?: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=120&q=80' }}" alt="Avatar" class="user-avatar-img me-1">
                <span class="fw-semibold text-dark small">{{ Auth::user()->name }}</span>
              </button>
              <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2">
                <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Hồ sơ cá nhân</a></li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-heart me-2"></i>Địa điểm yêu thích</a></li>
                <li><a class="dropdown-item" href="{{ route('trips.index') }}"><i class="bi bi-map me-2"></i>Chuyến đi của tôi</a></li>
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

  <main>
    <!-- Khối giới thiệu nổi bật (Hero Section) -->
    <section class="hero-travelplanner">
      <div class="container text-center">
        <div class="badge bg-white text-primary rounded-pill px-3 py-2 fw-bold mb-3 shadow-sm">
          <i class="bi bi-stars me-1 text-warning"></i> Nền tảng du lịch số #1 Việt Nam
        </div>
        <h1 class="display-4 fw-extrabold text-white mb-3">Lên Lịch Trình & Khám Phá <br class="d-none d-md-inline">Những Vùng Đất Mới</h1>
        <p class="lead text-light opacity-90 mx-auto mb-4" style="max-width: 650px;">
          Khám phá hàng ngàn điểm đến ngoạn mục, lưu giữ địa điểm yêu thích và tự động tối ưu hóa ngân sách chuyến đi của bạn.
        </p>

        <!-- Hộp tìm kiếm nổi (Floating Search Box) -->
        <div class="search-box-floating mx-auto text-start" style="max-width: 960px;">
          <form action="{{ route('trips.index') }}" method="GET" class="row g-3 align-items-center">
            <div class="col-md-4">
              <label class="form-label small mb-1"><i class="bi bi-geo-alt-fill me-1"></i> Điểm đến / Chuyến đi</label>
              <input type="text" name="search" class="form-control" placeholder="Ví dụ: Đà Nẵng, Hạ Long...">
            </div>
            <div class="col-md-3">
              <label class="form-label small mb-1"><i class="bi bi-calendar-event me-1"></i> Từ ngày</label>
              <input type="date" name="start_date" class="form-control">
            </div>
            <div class="col-md-3">
              <label class="form-label small mb-1"><i class="bi bi-calendar-check me-1"></i> Đến ngày</label>
              <input type="date" name="end_date" class="form-control">
            </div>
            <div class="col-md-2 d-grid">
              <label class="form-label small mb-1 d-none d-md-block">&nbsp;</label>
              <button type="submit" class="btn btn-primary py-2 fw-bold shadow-sm">
                <i class="bi bi-search me-1"></i> Tìm kiếm
              </button>
            </div>
          </form>
        </div>
      </div>
    </section>

    <!-- Danh mục phổ biến (Popular Categories) -->
    <section class="py-5">
      <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
          <div>
            <h6 class="text-primary fw-bold text-uppercase letter-spacing mb-1">Khám phá theo chủ đề</h6>
            <h2 class="fw-bold mb-0">Danh Mục Phổ Biến</h2>
          </div>
          <a href="#" class="btn btn-outline-primary btn-sm rounded-pill">
            Xem tất cả <i class="bi bi-arrow-right ms-1"></i>
          </a>
        </div>

        <div class="row g-3">
          <!-- Danh mục 1: Biển & Đảo -->
          <div class="col-6 col-md-4 col-lg-2">
            <a href="#" class="category-card-premium" style="background-image: url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=500&q=80');">
              <div class="cat-content">
                <div class="cat-title">Biển & Đảo</div>
                <div class="cat-count">142 điểm đến</div>
              </div>
            </a>
          </div>

          <!-- Danh mục 2: Núi Rừng -->
          <div class="col-6 col-md-4 col-lg-2">
            <a href="#" class="category-card-premium" style="background-image: url('https://images.unsplash.com/photo-1570789210967-2cac24afeb00?auto=format&fit=crop&w=500&q=80');">
              <div class="cat-content">
                <div class="cat-title">Núi Rừng</div>
                <div class="cat-count">89 điểm đến</div>
              </div>
            </a>
          </div>

          <!-- Danh mục 3: Resort & Spa -->
          <div class="col-6 col-md-4 col-lg-2">
            <a href="#" class="category-card-premium" style="background-image: url('https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=500&q=80');">
              <div class="cat-content">
                <div class="cat-title">Resort & Spa</div>
                <div class="cat-count">65 nơi lưu trú</div>
              </div>
            </a>
          </div>

          <!-- Danh mục 4: Di Sản Văn Hóa -->
          <div class="col-6 col-md-4 col-lg-2">
            <a href="#" class="category-card-premium" style="background-image: url('https://images.unsplash.com/photo-1559592413-7cec4d0cae2b?auto=format&fit=crop&w=500&q=80');">
              <div class="cat-content">
                <div class="cat-title">Di Sản Văn Hóa</div>
                <div class="cat-count">110 điểm đến</div>
              </div>
            </a>
          </div>

          <!-- Danh mục 5: Ẩm Thực Đặc Sản -->
          <div class="col-6 col-md-4 col-lg-2">
            <a href="#" class="category-card-premium" style="background-image: url('https://images.unsplash.com/photo-1582878826629-29b7ad1cdc43?auto=format&fit=crop&w=500&q=80');">
              <div class="cat-content">
                <div class="cat-title">Ẩm Thực Đặc Sản</div>
                <div class="cat-count">215 địa điểm</div>
              </div>
            </a>
          </div>

          <!-- Danh mục 6: Cắm Trại -->
          <div class="col-6 col-md-4 col-lg-2">
            <a href="#" class="category-card-premium" style="background-image: url('https://images.unsplash.com/photo-1523987355523-c7b5b0dd90a7?auto=format&fit=crop&w=500&q=80');">
              <div class="cat-content">
                <div class="cat-title">Cắm Trại & Glamping</div>
                <div class="cat-count">45 địa điểm</div>
              </div>
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- Địa điểm nổi bật (Featured Destinations) -->
    <section class="py-5 bg-white">
      <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
          <div>
            <h6 class="text-primary fw-bold text-uppercase letter-spacing mb-1">Điểm đến hàng đầu</h6>
            <h2 class="fw-bold mb-0">Địa Điểm Nổi Bật</h2>
          </div>
          <a href="#" class="btn btn-outline-primary btn-sm rounded-pill">
            Khám phá thêm <i class="bi bi-arrow-right ms-1"></i>
          </a>
        </div>

        <div class="row g-4">
          <!-- Thẻ địa điểm 1 -->
          <div class="col-md-6 col-lg-3">
            <div class="card-custom card-hover h-100 d-flex flex-column">
              <div class="card-img-wrap">
                <span class="badge badge-travelplanner badge-soft-primary badge-floating-top-left">Nghỉ dưỡng</span>
                <button class="btn-favorite-float" title="Yêu thích"><i class="bi bi-heart"></i></button>
                <img src="https://images.unsplash.com/photo-1544644181-1484b3fdfc62?auto=format&fit=crop&w=600&q=80" alt="Bà Nà Hills">
              </div>
              <div class="p-3 d-flex flex-column flex-grow-1">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="text-muted small"><i class="bi bi-geo-alt-fill text-danger me-1"></i>Đà Nẵng</span>
                  <span class="badge bg-warning text-dark"><i class="bi bi-star-fill me-1"></i>4.9</span>
                </div>
                <h5 class="fw-bold mb-2"><a href="#" class="text-dark">Sun World Bà Nà Hills</a></h5>
                <p class="text-muted small mb-3 flex-grow-1">Đường lên tiên cảnh với Cầu Vàng lừng danh thế giới và khí hậu 4 mùa.</p>
                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                  <div>
                    <span class="text-muted small">Từ</span>
                    <div class="fw-bold text-primary fs-5">900.000₫</div>
                  </div>
                  <a href="#" class="btn btn-sm btn-primary rounded-pill px-3">Chi tiết</a>
                </div>
              </div>
            </div>
          </div>

          <!-- Thẻ địa điểm 2 -->
          <div class="col-md-6 col-lg-3">
            <div class="card-custom card-hover h-100 d-flex flex-column">
              <div class="card-img-wrap">
                <span class="badge badge-travelplanner badge-soft-success badge-floating-top-left">Kỳ quan thế giới</span>
                <button class="btn-favorite-float active" title="Yêu thích"><i class="bi bi-heart-fill text-danger"></i></button>
                <img src="https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=600&q=80" alt="Vịnh Hạ Long">
              </div>
              <div class="p-3 d-flex flex-column flex-grow-1">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="text-muted small"><i class="bi bi-geo-alt-fill text-danger me-1"></i>Quảng Ninh</span>
                  <span class="badge bg-warning text-dark"><i class="bi bi-star-fill me-1"></i>4.8</span>
                </div>
                <h5 class="fw-bold mb-2"><a href="#" class="text-dark">Du Thuyền Vịnh Hạ Long</a></h5>
                <p class="text-muted small mb-3 flex-grow-1">Trải nghiệm du thuyền ngắm hoàng hôn và hàng ngàn đảo đá vôi kỳ vĩ.</p>
                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                  <div>
                    <span class="text-muted small">Từ</span>
                    <div class="fw-bold text-primary fs-5">1.500.000₫</div>
                  </div>
                  <a href="#" class="btn btn-sm btn-primary rounded-pill px-3">Chi tiết</a>
                </div>
              </div>
            </div>
          </div>

          <!-- Thẻ địa điểm 3 -->
          <div class="col-md-6 col-lg-3">
            <div class="card-custom card-hover h-100 d-flex flex-column">
              <div class="card-img-wrap">
                <span class="badge badge-travelplanner badge-soft-warning badge-floating-top-left">Resort 5 Sao</span>
                <button class="btn-favorite-float" title="Yêu thích"><i class="bi bi-heart"></i></button>
                <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=600&q=80" alt="Phú Quốc Resort">
              </div>
              <div class="p-3 d-flex flex-column flex-grow-1">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="text-muted small"><i class="bi bi-geo-alt-fill text-danger me-1"></i>Phú Quốc</span>
                  <span class="badge bg-warning text-dark"><i class="bi bi-star-fill me-1"></i>5.0</span>
                </div>
                <h5 class="fw-bold mb-2"><a href="#" class="text-dark">Vinpearl Resort & Spa</a></h5>
                <p class="text-muted small mb-3 flex-grow-1">Thiên đường nghỉ dưỡng bãi biển với hồ bơi vô cực và ẩm thực đẳng cấp quốc tế.</p>
                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                  <div>
                    <span class="text-muted small">Từ</span>
                    <div class="fw-bold text-primary fs-5">2.800.000₫</div>
                  </div>
                  <a href="#" class="btn btn-sm btn-primary rounded-pill px-3">Chi tiết</a>
                </div>
              </div>
            </div>
          </div>

          <!-- Thẻ địa điểm 4 -->
          <div class="col-md-6 col-lg-3">
            <div class="card-custom card-hover h-100 d-flex flex-column">
              <div class="card-img-wrap">
                <span class="badge badge-travelplanner badge-soft-danger badge-floating-top-left">Phố cổ</span>
                <button class="btn-favorite-float" title="Yêu thích"><i class="bi bi-heart"></i></button>
                <img src="https://images.unsplash.com/photo-1559592413-7cec4d0cae2b?auto=format&fit=crop&w=600&q=80" alt="Phố Cổ Hội An">
              </div>
              <div class="p-3 d-flex flex-column flex-grow-1">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="text-muted small"><i class="bi bi-geo-alt-fill text-danger me-1"></i>Quảng Nam</span>
                  <span class="badge bg-warning text-dark"><i class="bi bi-star-fill me-1"></i>4.9</span>
                </div>
                <h5 class="fw-bold mb-2"><a href="#" class="text-dark">Phố Cổ Hội An</a></h5>
                <p class="text-muted small mb-3 flex-grow-1">Thả đèn hoa đăng sông Hoài và ngắm nhìn phố đèn lồng lung linh về đêm.</p>
                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                  <div>
                    <span class="text-muted small">Từ</span>
                    <div class="fw-bold text-primary fs-5">150.000₫</div>
                  </div>
                  <a href="#" class="btn btn-sm btn-primary rounded-pill px-3">Chi tiết</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Khối kêu gọi lên kế hoạch chuyến đi (Callout Banner) -->
    <section class="py-5">
      <div class="container">
        <div class="card-custom p-4 p-md-5 bg-dark text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);">
          <div class="row align-items-center">
            <div class="col-lg-8">
              <span class="badge bg-primary text-white mb-2 px-3 py-2 rounded-pill">Tính năng nổi bật</span>
              <h2 class="display-6 fw-bold text-white mb-3">Lập Kế Hoạch Chuyến Đi & Quản Lý Ngân Sách Thông Minh</h2>
              <p class="text-light opacity-75 mb-4 mb-lg-0">
                Tự do sắp xếp lịch trình từng ngày theo timeline trực quan kéo thả, quản lý chi phí dự tính và thực tế với thanh tiến độ ngân sách thông minh.
              </p>
            </div>
            <div class="col-lg-4 text-lg-end">
              <a href="{{ Route::has('trips.create') ? route('trips.create') : url('/trips/create') }}" class="btn btn-light btn-lg rounded-pill px-4 shadow text-primary fw-bold me-2 mb-2">
                <i class="bi bi-plus-circle-fill me-1 text-primary"></i> Tạo Chuyến Đi Mới
              </a>
              <a href="{{ route('trips.index') }}" class="btn btn-outline-light rounded-pill px-4 mb-2">
                Xem Chuyến Đi Của Tôi
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Điểm nhấn trải nghiệm người dùng -->
    <section class="py-5 bg-white border-top">
      <div class="container">
        <div class="row g-4 text-center">
          <div class="col-md-4">
            <div class="p-3">
              <div class="category-icon mx-auto mb-3" style="width: 64px; height: 64px; font-size: 1.75rem;">
                <i class="bi bi-map-fill"></i>
              </div>
              <h5 class="fw-bold">Lịch Trình Chi Tiết</h5>
              <p class="text-muted small">Tổ chức hoạt động theo từng mốc thời gian trong ngày với thao tác kéo thả mượt mà.</p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="p-3">
              <div class="category-icon mx-auto mb-3" style="width: 64px; height: 64px; font-size: 1.75rem;">
                <i class="bi bi-wallet2"></i>
              </div>
              <h5 class="fw-bold">Kiểm Soát Ngân Sách</h5>
              <p class="text-muted small">Theo dõi chi phí chi tiết, tự động tính toán số dư và cảnh báo khi chi tiêu vượt hạn mức.</p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="p-3">
              <div class="category-icon mx-auto mb-3" style="width: 64px; height: 64px; font-size: 1.75rem;">
                <i class="bi bi-shield-check"></i>
              </div>
              <h5 class="fw-bold">Đặt Phòng Đảm Bảo</h5>
              <p class="text-muted small">Hỗ trợ đặt phòng khách sạn, resort nhanh chóng với mã hóa bảo mật và hóa đơn chi tiết.</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

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
</body>
</html>
