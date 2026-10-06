<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách địa điểm yêu thích - Travel Planner</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #0284c7;
            --primary-dark: #0369a1;
            --primary-light: #e0f2fe;
            --accent: #f59e0b;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --bg-light: #f8fafc;
            --white: #ffffff;
            --border-color: #e2e8f0;
            --danger: #ef4444;
            --success: #10b981;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            --radius-md: 12px;
            --radius-lg: 16px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: var(--bg-light); color: var(--text-dark); min-height: 100vh; display: flex; flex-direction: column; }

        /* Header */
        header { background: var(--white); border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 50; }
        .header-container { max-width: 1280px; margin: 0 auto; padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; }
        .logo { display: flex; align-items: center; gap: 0.5rem; font-size: 1.25rem; font-weight: 800; color: var(--primary); text-decoration: none; }
        .nav-links { display: flex; gap: 1.5rem; list-style: none; align-items: center; }
        .nav-links a { text-decoration: none; color: var(--text-muted); font-weight: 600; font-size: 0.95rem; transition: color 0.2s; }
        .nav-links a:hover, .nav-links a.active { color: var(--primary); }

        /* Main Container */
        .container { max-width: 1280px; margin: 2rem auto; padding: 0 1.5rem; flex: 1; width: 100%; }
        .page-header { margin-bottom: 2rem; }
        .page-title { font-size: 1.875rem; font-weight: 800; color: var(--text-dark); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.75rem; }
        .page-title i { color: var(--danger); }
        .page-subtitle { color: var(--text-muted); font-size: 1rem; }

        /* Alert Toast */
        .alert-success { background: #dcfce7; color: #166534; padding: 1rem 1.25rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; border-left: 4px solid var(--success); display: flex; align-items: center; gap: 0.5rem; }

        /* Grid Cards */
        .destinations-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; }
        .card { background: var(--white); border-radius: var(--radius-lg); overflow: hidden; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); transition: all 0.2s ease; display: flex; flex-direction: column; }
        .card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
        .card-img-wrapper { position: relative; height: 180px; overflow: hidden; }
        .card-img { width: 100%; height: 100%; object-fit: cover; }
        .badge-category { position: absolute; top: 12px; left: 12px; background: rgba(255,255,255,0.92); backdrop-filter: blur(4px); padding: 0.25rem 0.6rem; border-radius: 20px; font-size: 0.75rem; font-weight: 700; color: var(--primary-dark); }
        .btn-remove-fav { position: absolute; top: 12px; right: 12px; width: 36px; height: 36px; border-radius: 50%; background: var(--white); border: none; display: flex; align-items: center; justify-content: center; color: var(--danger); cursor: pointer; box-shadow: var(--shadow-md); transition: transform 0.2s; }
        .btn-remove-fav:hover { transform: scale(1.1); background: #fee2e2; }
        
        .card-body { padding: 1.25rem; flex: 1; display: flex; flex-direction: column; }
        .card-location { font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.25rem; }
        .card-title { font-size: 1.1rem; font-weight: 700; color: var(--text-dark); margin-bottom: 0.5rem; }
        .card-rating { display: flex; align-items: center; gap: 0.25rem; color: var(--accent); font-size: 0.85rem; font-weight: 700; margin-bottom: 1rem; }
        .card-rating span { color: var(--text-muted); font-weight: 400; }
        .card-footer-box { margin-top: auto; padding-top: 1rem; border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; }
        .price-label { font-size: 0.75rem; color: var(--text-muted); }
        .price-value { font-size: 1rem; font-weight: 800; color: var(--primary); }
        
        .btn-add-trip { background: var(--primary-light); color: var(--primary-dark); border: none; padding: 0.5rem 0.9rem; border-radius: var(--radius-md); font-size: 0.85rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 0.4rem; transition: all 0.2s; }
        .btn-add-trip:hover { background: var(--primary); color: var(--white); }

        /* Empty State */
        .empty-state { text-align: center; padding: 4rem 1rem; background: var(--white); border-radius: var(--radius-lg); border: 1px dashed var(--border-color); }
        .empty-icon { font-size: 3.5rem; color: #cbd5e1; margin-bottom: 1rem; }
        .empty-title { font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem; }
        .empty-desc { color: var(--text-muted); margin-bottom: 1.5rem; }
        .btn-primary { background: var(--primary); color: var(--white); text-decoration: none; padding: 0.75rem 1.5rem; border-radius: var(--radius-md); font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem; }

        /* Footer */
        footer { background: var(--white); border-top: 1px solid var(--border-color); padding: 1.5rem 0; margin-top: 3rem; text-align: center; color: var(--text-muted); font-size: 0.875rem; }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <a href="{{ route('home') }}" class="logo">
                <i class="fa-solid fa-compass"></i> Travel Planner
            </a>
            <ul class="nav-links">
                <li><a href="{{ route('home') }}">Trang chủ</a></li>
                <li><a href="{{ route('favorites.index') }}" class="active"><i class="fa-solid fa-heart" style="color:var(--danger);"></i> Yêu thích</a></li>
                <li><a href="{{ route('trips.index') }}">Chuyến đi của tôi</a></li>
            </ul>
        </div>
    </header>

    <main class="container">
        <div class="page-header">
            <h1 class="page-title"><i class="fa-solid fa-heart"></i> Danh sách địa điểm yêu thích</h1>
            <p class="page-subtitle">Quản lý các điểm đến bạn đã lưu và dễ dàng lên kế hoạch cho chuyến đi</p>
        </div>

        @if(session('success'))
            <div class="alert-success">
                <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
            </div>
        @endif

        @if($favorites->count() > 0)
            <div class="destinations-grid">
                @foreach($favorites as $fav)
                    @php $dest = $fav->destination; @endphp
                    @if($dest)
                        <div class="card">
                            <div class="card-img-wrapper">
                                <img src="{{ $dest->primaryImage->image_url ?? 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e' }}" 
                                     alt="{{ $dest->name }}" class="card-img">
                                @if($dest->category)
                                    <span class="badge-category">{{ $dest->category->name }}</span>
                                @endif
                                <form action="{{ route('favorites.toggle', $dest->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn-remove-fav" title="Xóa khỏi yêu thích">
                                        <i class="fa-solid fa-heart"></i>
                                    </button>
                                </form>
                            </div>
                            <div class="card-body">
                                <div class="card-location">
                                    <i class="fa-solid fa-location-dot"></i> {{ $dest->city->name ?? 'Việt Nam' }}
                                </div>
                                <h3 class="card-title">{{ $dest->name }}</h3>
                                <div class="card-rating">
                                    <i class="fa-solid fa-star"></i> {{ number_format($dest->rating, 1) }}
                                    <span>({{ $dest->reviews_count ?? 0 }} đánh giá)</span>
                                </div>
                                <div class="card-footer-box">
                                    <div>
                                        <div class="price-label">Giá vé ước tính</div>
                                        <div class="price-value">
                                            {{ $dest->entrance_fee > 0 ? number_format($dest->entrance_fee, 0, ',', '.') . ' đ' : 'Miễn phí' }}
                                        </div>
                                    </div>
                                    <a href="{{ route('trips.index') }}" class="btn-add-trip">
                                        <i class="fa-solid fa-plus"></i> Thêm vào Trip
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            <div style="margin-top: 2rem;">
                {{ $favorites->links() }}
            </div>
        @else
            <div class="empty-state">
                <div class="empty-icon"><i class="fa-regular fa-heart"></i></div>
                <h2 class="empty-title">Bạn chưa có địa điểm yêu thích nào</h2>
                <p class="empty-desc">Hãy khám phá các điểm đến hấp dẫn và bấm biểu tượng trái tim để lưu lại nhé!</p>
                <a href="{{ route('home') }}" class="btn-primary">
                    <i class="fa-solid fa-compass"></i> Khám phá địa điểm ngay
                </a>
            </div>
        @endif
    </main>

    <footer>
        <p>&copy; {{ date('Y') }} Travel Planner. Hệ thống gợi ý và lên lịch trình du lịch thông minh.</p>
    </footer>
</body>
</html>
