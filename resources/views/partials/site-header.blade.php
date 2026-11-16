<style>
    .tp-header{position:sticky;top:0;z-index:100;background:#fff;border-bottom:1px solid #e2e8f0;font-family:Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}
    .tp-header *{box-sizing:border-box}.tp-header-inner{max-width:1280px;margin:0 auto;padding:12px 24px;min-height:60px;display:flex;align-items:center;justify-content:space-between;gap:18px}
    .tp-brand{display:flex;align-items:center;gap:10px;text-decoration:none;color:#0f172a;font-size:19px;font-weight:800;letter-spacing:-.5px;white-space:nowrap}
    .tp-header .brand-icon{width:36px;height:36px;flex:0 0 36px;background:linear-gradient(135deg,#06f 0%,#0052cc 100%);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;box-shadow:0 4px 10px rgba(0,102,255,.25)}
    .tp-menu{display:flex;align-items:center;gap:8px;list-style:none;margin:0;padding:0}.tp-menu-link{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:9999px;text-decoration:none;font-size:14px;font-weight:500;color:#64748b;white-space:nowrap;transition:all .2s ease}
    .tp-menu-link:hover{color:#06f;background:#f1f5f9}.tp-menu-link.active{background:#ebf5ff;color:#06f;font-weight:700}.tp-actions{display:flex;align-items:center;gap:12px;white-space:nowrap}
    .tp-admin,.tp-login{display:inline-flex;align-items:center;gap:6px;padding:6px 16px;border:1px solid #cbd5e1;border-radius:9999px;background:#fff;color:#0f172a;font-size:13.5px;font-weight:600;text-decoration:none;transition:all .2s ease}.tp-admin:hover,.tp-login:hover{border-color:#06f;color:#06f}
    .tp-user{position:relative}.tp-user-button{display:flex;align-items:center;gap:10px;padding:4px 12px 4px 4px;border:1px solid #e2e8f0;border-radius:9999px;background:#f1f5f9;color:#0f172a;cursor:pointer;font-size:14px;font-weight:600;transition:all .2s ease}.tp-user-button:hover{background:#e2e8f0}.tp-avatar{width:32px;height:32px;border-radius:50%;object-fit:cover;background:#dbeafe}.tp-user-chevron{font-size:11px}
    .tp-dropdown{display:none;position:absolute;right:0;top:calc(100% + 8px);min-width:200px;padding:6px;background:#fff;border:1px solid #e2e8f0;border-radius:14px;box-shadow:0 10px 25px rgba(0,0,0,.1);z-index:1200}.tp-dropdown.show{display:block}
    .tp-dropdown form{margin:0}.tp-dropdown a,.tp-dropdown button{display:flex;align-items:center;gap:8px;width:100%;padding:10px 14px;border:0;border-radius:8px;background:transparent;color:#0f172a;text-align:left;text-decoration:none;font-family:inherit;font-size:13.5px;font-weight:500;cursor:pointer}.tp-dropdown a:hover,.tp-dropdown button:hover{background:#ebf5ff;color:#06f}.tp-dropdown hr{margin:6px 0;border:0;border-top:1px solid #e2e8f0}.tp-logout{color:#dc2626!important}
    .tp-mobile-toggle{display:none;border:1px solid #e2e8f0;border-radius:10px;background:#fff;padding:8px 10px;color:#0f172a;font-size:18px;cursor:pointer}
    @media(max-width:960px){.tp-header-inner{flex-wrap:wrap;padding:10px 18px}.tp-mobile-toggle{display:block;margin-left:auto}.tp-menu{display:none;order:3;width:100%;flex-direction:column;align-items:stretch;gap:2px;padding-top:6px}.tp-menu.open{display:flex}.tp-menu-link{padding:10px 12px}.tp-actions{margin-left:auto}.tp-brand{font-size:17px}}
    @media(max-width:540px){.tp-header-inner{gap:10px;padding:9px 12px}.tp-actions{order:2}.tp-user-button{padding-right:8px}.tp-user-name{display:none}.tp-admin{padding:6px 10px}.tp-brand{font-size:15px;gap:7px}.tp-header .brand-icon{width:32px;height:32px;flex-basis:32px}}
</style>
<header class="tp-header">
    <div class="tp-header-inner">
        <a href="{{ route('home') }}" class="tp-brand">
            <div class="brand-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon></svg></div>
            <span>TRAVEL PLANNER</span>
        </a>
        <button class="tp-mobile-toggle" type="button" aria-label="Mở điều hướng" aria-expanded="false" onclick="toggleSiteNav(event)"><i class="bi bi-list"></i></button>
        <ul class="tp-menu" id="tpMainMenu">
            <li><a href="{{ route('home') }}" class="tp-menu-link {{ request()->routeIs('home') ? 'active' : '' }}"><i class="bi bi-house"></i> Trang chủ</a></li>
            <li><a href="{{ route('destinations.index') }}" class="tp-menu-link {{ request()->routeIs('destinations.*') ? 'active' : '' }}"><i class="bi bi-geo-alt"></i> Địa điểm</a></li>
            <li><a href="{{ route('trips.index') }}" class="tp-menu-link {{ request()->routeIs('trips.*', 'itinerary.*') ? 'active' : '' }}"><i class="bi bi-map"></i> Chuyến đi</a></li>
            <li><a href="{{ route('favorites.index') }}" class="tp-menu-link {{ request()->routeIs('favorites.*') ? 'active' : '' }}"><i class="bi bi-suit-heart"></i> Yêu thích</a></li>
            <li><a href="{{ route('bookings.index') }}" class="tp-menu-link {{ request()->routeIs('bookings.*', 'payments.*') ? 'active' : '' }}"><i class="bi bi-calendar-event"></i> Booking</a></li>
        </ul>
        <div class="tp-actions">
            @if(auth()->check() && auth()->user()->isAdmin())<a href="{{ route('admin.dashboard') }}" class="tp-admin"><i class="bi bi-shield-check"></i> Admin</a>@endif
            @auth
                <div class="tp-user">
                    <button type="button" class="tp-user-button" aria-haspopup="true" aria-expanded="false" onclick="toggleSiteUserMenu(event)"><img src="{{ auth()->user()->avatar ?: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100' }}" alt="{{ auth()->user()->name }}" class="tp-avatar"><span class="tp-user-name">{{ auth()->user()->name }}</span><i class="bi bi-chevron-down tp-user-chevron"></i></button>
                    <div class="tp-dropdown" id="tpUserDropdown">
                        <a href="{{ route('profile.show') }}"><i class="bi bi-person"></i> Hồ sơ cá nhân</a>
                        <a href="{{ route('trips.index') }}"><i class="bi bi-briefcase"></i> Chuyến đi của tôi</a>
                        <a href="{{ route('favorites.index') }}"><i class="bi bi-heart"></i> Danh sách yêu thích</a>
                        @if(Route::has('reviews.my-reviews'))<a href="{{ route('reviews.my-reviews') }}"><i class="bi bi-star"></i> Đánh giá của tôi</a>@endif
                        <hr><form action="{{ route('logout') }}" method="POST">@csrf<button type="submit" class="tp-logout"><i class="bi bi-box-arrow-right"></i> Đăng xuất</button></form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="tp-login">Đăng nhập</a>
            @endauth
        </div>
    </div>
</header>
<script>
    function toggleSiteUserMenu(event){event.stopPropagation();const menu=document.getElementById('tpUserDropdown');const open=menu.classList.toggle('show');event.currentTarget.setAttribute('aria-expanded',String(open))}
    function toggleSiteNav(event){const menu=document.getElementById('tpMainMenu');const open=menu.classList.toggle('open');event.currentTarget.setAttribute('aria-expanded',String(open))}
    document.addEventListener('click',function(event){const menu=document.getElementById('tpUserDropdown');if(menu&&!event.target.closest('.tp-user')){menu.classList.remove('show');document.querySelector('.tp-user-button')?.setAttribute('aria-expanded','false')}});
    document.addEventListener('keydown',function(event){if(event.key==='Escape'){document.getElementById('tpUserDropdown')?.classList.remove('show');document.querySelector('.tp-user-button')?.setAttribute('aria-expanded','false')}});
</script>
