<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Planner - Lên Lịch Trình & Khám Phá Những Vùng Đất Mới</title>
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #FFFFFF;
            color: #0F172A;
            line-height: 1.5;
        }

        /* 1. Navbar Header */
        .navbar {
            background-color: #FFFFFF;
            border-bottom: 1px solid #F1F5F9;
            padding: 12px 36px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #0F172A;
            font-weight: 800;
            font-size: 19px;
            letter-spacing: -0.5px;
        }

        .brand-icon {
            width: 34px;
            height: 34px;
            background: #0066FF;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FFFFFF;
        }

        .navbar-nav {
            display: flex;
            align-items: center;
            gap: 12px;
            list-style: none;
        }

        .nav-link {
            text-decoration: none;
            color: #475569;
            font-weight: 500;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 20px;
            transition: all 0.2s;
        }

        .nav-link.active {
            background-color: #EBF5FF;
            color: #0066FF;
            font-weight: 600;
        }

        .nav-link:hover:not(.active) {
            color: #0066FF;
            background-color: #F8FAFC;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-admin {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            border-radius: 20px;
            border: 1px solid #0F172A;
            background: #FFFFFF;
            color: #0F172A;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-admin:hover {
            background: #F8FAFC;
        }

        .user-dropdown {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: #0F172A;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            padding: 4px 10px;
            border-radius: 20px;
            transition: all 0.15s ease;
        }

        .user-dropdown:hover {
            background-color: #F1F5F9;
            color: #0066FF;
        }

        .user-avatar-img {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            object-fit: cover;
            background: #E2E8F0;
        }

        /* 2. Hero Section */
        .hero-section {
            position: relative;
            background-image: linear-gradient(rgba(15, 23, 42, 0.45), rgba(15, 23, 42, 0.65)), url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?w=1600&q=80');
            background-size: cover;
            background-position: center 35%;
            color: #FFFFFF;
            padding: 70px 20px 85px;
            text-align: center;
        }

        .badge-pill-hero {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #FFFFFF;
            color: #0066FF;
            font-size: 13px;
            font-weight: 700;
            padding: 6px 18px;
            border-radius: 30px;
            margin-bottom: 22px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .hero-title {
            font-size: 46px;
            font-weight: 800;
            letter-spacing: -1px;
            line-height: 1.25;
            margin-bottom: 14px;
        }

        .hero-subtitle {
            font-size: 16px;
            color: #E2E8F0;
            max-width: 680px;
            margin: 0 auto 36px;
            line-height: 1.6;
        }

        /* Floating Search Card */
        .search-card-wrapper {
            max-width: 960px;
            margin: 0 auto;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 20px;
            padding: 20px 24px;
        }

        .search-grid {
            display: grid;
            grid-template-columns: 1.8fr 1.3fr 1.3fr auto;
            gap: 16px;
            align-items: flex-end;
            text-align: left;
        }

        .search-field label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 600;
            color: #F8FAFC;
            margin-bottom: 8px;
        }

        .search-input-box {
            background: #FFFFFF;
            border-radius: 10px;
            padding: 10px 14px;
            display: flex;
            align-items: center;
        }

        .search-input-box input, .search-input-box select {
            border: none;
            outline: none;
            width: 100%;
            font-family: inherit;
            font-size: 14px;
            color: #0F172A;
            background: transparent;
        }

        .btn-search-hero {
            background: #0066FF;
            color: #FFFFFF;
            border: none;
            padding: 12px 26px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            height: 44px;
            transition: background 0.2s;
        }

        .btn-search-hero:hover {
            background: #0052CC;
        }

        /* 3. Common Sections */
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 50px 24px 70px;
        }

        .section-header-wrap {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .sub-tag-blue {
            color: #0066FF;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 4px;
            display: block;
        }

        .sec-title-main {
            font-size: 26px;
            font-weight: 800;
            color: #0F172A;
            letter-spacing: -0.5px;
        }

        .btn-pill-outline {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 20px;
            border-radius: 30px;
            border: 1.5px solid #0066FF;
            color: #0066FF;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-pill-outline:hover {
            background: #0066FF;
            color: #FFFFFF;
        }

        /* 4. Categories Section (6 Columns) */
        .categories-grid-6 {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 14px;
            margin-bottom: 60px;
        }

        .cat-card-img {
            position: relative;
            height: 140px;
            border-radius: 14px;
            overflow: hidden;
            text-decoration: none;
            color: #FFFFFF;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 12px;
            background-size: cover;
            background-position: center;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .cat-card-img::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.1) 0%, rgba(15, 23, 42, 0.85) 100%);
        }

        .cat-card-img:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.15);
        }

        .cat-card-content {
            position: relative;
            z-index: 2;
        }

        .cat-card-title {
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 2px;
        }

        .cat-card-count {
            font-size: 11px;
            color: #CBD5E1;
        }

        /* 5. Featured Destinations Section (4 Columns) */
        .destinations-grid-4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .dest-card-box {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 16px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .dest-card-box:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08);
        }

        .dest-img-header {
            position: relative;
            height: 180px;
            background: #E2E8F0;
        }

        .dest-img-header img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .tag-dest-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            padding: 4px 10px;
            border-radius: 14px;
            font-size: 11px;
            font-weight: 700;
        }

        .tag-blue-light { background: #EBF5FF; color: #0066FF; }
        .tag-teal-light { background: #E6FFFA; color: #0D9488; }
        .tag-gold-light { background: #FEF3C7; color: #B45309; }
        .tag-red-light { background: #FEE2E2; color: #DC2626; }

        .btn-heart-fav {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 32px;
            height: 32px;
            background: #FFFFFF;
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            font-size: 15px;
        }

        .dest-body-content {
            padding: 16px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .dest-meta-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 12px;
        }

        .dest-city-text {
            color: #EF4444;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 3px;
        }

        .dest-rating-pill {
            background: #FACC15;
            color: #713F12;
            padding: 2px 7px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 11px;
            display: flex;
            align-items: center;
            gap: 2px;
        }

        .dest-card-name {
            font-size: 16px;
            font-weight: 700;
            color: #0F172A;
            margin-bottom: 6px;
            line-height: 1.3;
        }

        .dest-card-desc {
            font-size: 12.5px;
            color: #64748B;
            margin-bottom: 16px;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex-grow: 1;
        }

        .dest-card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top: 1px solid #F1F5F9;
            padding-top: 12px;
        }

        .price-label-small {
            font-size: 11px;
            color: #64748B;
            display: block;
        }

        .price-val-blue {
            font-size: 16px;
            font-weight: 800;
            color: #0066FF;
        }

        .btn-detail-blue {
            background: #0066FF;
            color: #FFFFFF;
            padding: 7px 18px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
            text-decoration: none;
            transition: background 0.2s;
        }

        .btn-detail-blue:hover {
            background: #0052CC;
        }
    </style>
</head>
<body>

    <!-- 1. Navigation Header -->
    <header class="navbar">
        <a href="{{ route('home') }}" class="navbar-brand">
            <div class="brand-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon></svg>
            </div>
            TRAVEL PLANNER
        </a>

        <ul class="navbar-nav">
            <li>
                <a href="{{ route('home') }}" class="nav-link active">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg>
                    Trang chủ
                </a>
            </li>
            <li>
                <a href="#destinations" class="nav-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    Địa điểm
                </a>
            </li>
            <li>
                <a href="{{ route('trips.index') }}" class="nav-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                    Chuyến đi
                </a>
            </li>
            <li>
                <a href="{{ route('favorites.index') }}" class="nav-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                    Yêu thích
                </a>
            </li>
            <li>
                <a href="#" class="nav-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect><line x1="16" x2="16" y1="2" y2="6"></line><line x1="8" x2="8" y1="2" y2="6"></line><line x1="3" x2="21" y1="10" y2="10"></line></svg>
                    Booking
                </a>
            </li>
        </ul>

        <div class="nav-actions">
            @auth
                @if(Auth::user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}" class="btn-admin">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        Admin
                    </a>
                @endif
                <a href="{{ route('profile.show') }}" class="user-dropdown" title="Hồ sơ cá nhân">
                    <img src="{{ Auth::user()->avatar ?: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100' }}" class="user-avatar-img" alt="Avatar">
                    <span>{{ Auth::user()->name }}</span>
                    <span>▾</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="btn-admin">Đăng nhập</a>
                <a href="{{ route('register') }}" class="btn-detail-blue">Đăng ký</a>
            @endauth
        </div>
    </header>

    <!-- 2. Hero Banner -->
    <section class="hero-section">
        <div class="badge-pill-hero">
            ⭐ Nền tảng du lịch số #1 Việt Nam
        </div>
        <h1 class="hero-title">
            Lên Lịch Trình & Khám Phá<br>Những Vùng Đất Mới
        </h1>
        <p class="hero-subtitle">
            Khám phá hàng ngàn điểm đến ngoạn mục, lưu giữ địa điểm yêu thích và tự động tối ưu hóa ngân sách chuyến đi của bạn.
        </p>

        <!-- Floating Search Box -->
        <div class="search-card-wrapper">
            <div class="search-grid">
                <div class="search-field">
                    <label>📍 Điểm đến / Thành phố</label>
                    <div class="search-input-box">
                        <input type="text" placeholder="Ví dụ: Đà Nẵng, Phú Quốc...">
                    </div>
                </div>

                <div class="search-field">
                    <label>🗂️ Danh mục</label>
                    <div class="search-input-box">
                        <select>
                            <option value="">Tất cả danh mục</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="search-field">
                    <label>💵 Mức ngân sách</label>
                    <div class="search-input-box">
                        <select>
                            <option value="">Tất cả mức giá</option>
                            <option value="1">Dưới 500.000đ</option>
                            <option value="2">500.000đ - 1.500.000đ</option>
                            <option value="3">Trên 1.500.000đ</option>
                        </select>
                    </div>
                </div>

                <button class="btn-search-hero">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    Tìm kiếm
                </button>
            </div>
        </div>
    </section>

    <!-- Main Content Container -->
    <div class="main-container">

        <!-- 3. Section: Danh Mục Phổ Biến (Grid 6 cột) -->
        <section style="margin-bottom: 50px;">
            <div class="section-header-wrap">
                <div>
                    <span class="sub-tag-blue">KHÁM PHÁ THEO CHỦ ĐỀ</span>
                    <h2 class="sec-title-main">Danh Mục Phổ Biến</h2>
                </div>
                <a href="#destinations" class="btn-pill-outline">Xem tất cả &rarr;</a>
            </div>

            <div class="categories-grid-6">
                <!-- 1. Biển & Đảo -->
                <a href="#destinations" class="cat-card-img" style="background-image: url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=500&q=80');">
                    <div class="cat-card-content">
                        <div class="cat-card-title">Biển & Đảo</div>
                        <div class="cat-card-count">142 điểm đến</div>
                    </div>
                </a>

                <!-- 2. Núi Rừng -->
                <a href="#destinations" class="cat-card-img" style="background-image: url('https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=500&q=80');">
                    <div class="cat-card-content">
                        <div class="cat-card-title">Núi Rừng</div>
                        <div class="cat-card-count">89 điểm đến</div>
                    </div>
                </a>

                <!-- 3. Resort & Spa -->
                <a href="#destinations" class="cat-card-img" style="background-image: url('https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=500&q=80');">
                    <div class="cat-card-content">
                        <div class="cat-card-title">Resort & Spa</div>
                        <div class="cat-card-count">65 nơi lưu trú</div>
                    </div>
                </a>

                <!-- 4. Di Sản Văn Hóa -->
                <a href="#destinations" class="cat-card-img" style="background-image: url('https://images.unsplash.com/photo-1528127269322-539801943592?w=500&q=80');">
                    <div class="cat-card-content">
                        <div class="cat-card-title">Di Sản Văn Hóa</div>
                        <div class="cat-card-count">110 điểm đến</div>
                    </div>
                </a>

                <!-- 5. Ẩm Thực Đặc Sản -->
                <a href="#destinations" class="cat-card-img" style="background-image: url('https://images.unsplash.com/photo-1582878826629-29b7ad1cdc43?w=500&q=80');">
                    <div class="cat-card-content">
                        <div class="cat-card-title">Ẩm Thực Đặc Sản</div>
                        <div class="cat-card-count">215 địa điểm</div>
                    </div>
                </a>

                <!-- 6. Cắm Trại & Glamping -->
                <a href="#destinations" class="cat-card-img" style="background-image: url('https://images.unsplash.com/photo-1510312305653-8ed496efae75?w=500&q=80');">
                    <div class="cat-card-content">
                        <div class="cat-card-title">Cắm Trại & Glamping</div>
                        <div class="cat-card-count">45 địa điểm</div>
                    </div>
                </a>
            </div>
        </section>

               <!-- 4. Section: Địa Điểm Nổi Bật (Render động từ Database) -->
        <section id="destinations">
            <div class="section-header-wrap">
                <div>
                    <span class="sub-tag-blue">ĐIỂM ĐẾN HÀNG ĐẦU</span>
                    <h2 class="sec-title-main">Địa Điểm Nổi Bật</h2>
                </div>
                <a href="#destinations" class="btn-pill-outline">Khám phá thêm &rarr;</a>
            </div>

            <div class="destinations-grid-4">
                @forelse($featuredDestinations as $destination)
                    <div class="dest-card-box">
                        <div class="dest-img-header">
                            @if($destination->primaryImage)
                                <img src="{{ $destination->primaryImage->image_path }}" alt="{{ $destination->name }}">
                            @else
                                <img src="https://images.unsplash.com/photo-1528127269322-539801943592?w=600" alt="{{ $destination->name }}">
                            @endif
                            <span class="tag-dest-badge tag-blue-light">
                                {{ $destination->category->name ?? 'Du lịch' }}
                            </span>
                            <form action="{{ route('favorites.toggle', $destination->id) }}" method="POST" style="position: absolute; top: 12px; right: 12px; z-index: 5;">
                                @csrf
                                <button type="submit" class="btn-heart-fav" title="Lưu yêu thích">🤍</button>
                            </form>
                        </div>
                        <div class="dest-body-content">
                            <div class="dest-meta-row">
                                <span class="dest-city-text">📍 {{ $destination->city->name ?? 'Việt Nam' }}</span>
                                <span class="dest-rating-pill">⭐ {{ number_format($destination->rating, 1) }}</span>
                            </div>
                            <h3 class="dest-card-name">{{ $destination->name }}</h3>
                            <p class="dest-card-desc">{{ $destination->description }}</p>
                            <div class="dest-card-footer">
                                <div>
                                    <span class="price-label-small">Từ</span>
                                    <div class="price-val-blue">
                                        {{ $destination->entrance_fee > 0 ? number_format($destination->entrance_fee, 0, ',', '.') . 'đ' : 'Miễn phí' }}
                                    </div>
                                </div>
                                <a href="#" class="btn-detail-blue">Chi tiết</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <p style="grid-column: 1/-1; text-align: center; color: #64748B; padding: 40px 0;">
                        Hiện chưa có địa điểm nổi bật nào.
                    </p>
                @endforelse
            </div>
        </section>

    </div>

</body>
</html>
