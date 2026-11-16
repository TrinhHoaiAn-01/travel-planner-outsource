<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $destination->name }} - Nơi Lưu Trú & Chi Tiết Phòng | Travel Planner</title>
    <script>
        if (window.top !== window.self) {
            window.top.location.href = window.location.href;
        }
    </script>
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS & Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #F8FAFC;
            color: #0F172A;
            line-height: 1.5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* 1. Navbar Header */
        .navbar {
            background-color: #FFFFFF;
            border-bottom: 1px solid #E2E8F0;
            padding: 14px 36px;
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
            flex-direction: row;
            margin-bottom: 0;
            padding-left: 0;
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
            background-color: #F1F5F9;
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

        .user-dropdown {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: #0F172A;
            font-weight: 600;
            font-size: 14px;
            padding: 4px 8px;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #0066FF;
            color: #FFF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 13px;
        }

        .btn-login {
            background: #0066FF;
            color: #FFFFFF;
            padding: 8px 20px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.2s;
        }

        /* 2. Main Container */
        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 24px 24px 70px;
            width: 100%;
            flex: 1;
        }

        /* Breadcrumb */
        .breadcrumb-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #64748B;
            margin-bottom: 16px;
        }

        .breadcrumb-wrap a {
            color: #64748B;
            text-decoration: none;
            transition: color 0.2s;
        }

        .breadcrumb-wrap a:hover {
            color: #0066FF;
        }

        /* Top Header Area */
        .detail-top-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 12px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .top-tags-box {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }

        .badge-cat-pill {
            background: #EBF5FF;
            color: #0066FF;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }

        .badge-hotel-pill {
            background: #DCFCE7;
            color: #15803D;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .dest-main-title {
            font-size: 32px;
            font-weight: 800;
            color: #0F172A;
            letter-spacing: -0.5px;
            margin-bottom: 6px;
        }

        .dest-address-text {
            color: #64748B;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .top-action-buttons {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-action-outline {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            border: 1px solid #CBD5E1;
            border-radius: 30px;
            background: #FFFFFF;
            color: #334155;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-action-outline:hover {
            border-color: #94A3B8;
            background: #F8FAFC;
        }

        .btn-action-fav {
            border-color: #FECDD3;
            color: #E11D48;
        }

        .btn-action-fav:hover {
            background: #FFF1F2;
            border-color: #FDA4AF;
        }

        .btn-action-trip {
            border-color: #BAE6FD;
            color: #0284C7;
        }

        .btn-action-trip:hover {
            background: #F0F9FF;
            border-color: #7DD3FC;
        }

        .btn-action-book-primary {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 24px;
            border-radius: 30px;
            background: #0066FF;
            color: #FFFFFF;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: background 0.2s, transform 0.2s;
            box-shadow: 0 4px 12px rgba(0, 102, 255, 0.25);
        }

        .btn-action-book-primary:hover {
            background: #0052CC;
            transform: translateY(-1px);
        }

        /* 3. Gallery Grid (Hỗ trợ linh hoạt từ 1 ảnh đến 5+ ảnh thực tế từ Database) & Controls */
        .gallery-container {
            position: relative;
            display: grid;
            gap: 8px;
            border-radius: 16px;
            overflow: hidden;
            margin: 24px 0 28px;
            height: 440px;
            background: #F1F5F9;
        }

        /* 1 ảnh duy nhất (Hero Full) */
        .gallery-container.gallery-single {
            grid-template-columns: 1fr;
        }

        /* 2 ảnh (50% - 50%) */
        .gallery-container.gallery-two {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        /* 3 ảnh (1 lớn trái + 2 xếp chồng phải) */
        .gallery-container.gallery-three {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .gallery-sub-grid-two {
            grid-template-columns: 1fr !important;
            grid-template-rows: repeat(2, minmax(0, 1fr)) !important;
        }

        /* 4 ảnh (1 lớn trái + 1 trên, 2 dưới phải) */
        .gallery-container.gallery-four {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .gallery-sub-grid-three {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            grid-template-rows: repeat(2, minmax(0, 1fr)) !important;
        }
        .gallery-sub-span-2 {
            grid-column: span 2;
        }

        /* 5 ảnh trở lên (1 lớn trái + 4 lưới 2x2 phải) */
        .gallery-container.gallery-five-plus {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .gallery-main-wrap {
            position: relative;
            overflow: hidden;
            width: 100%;
            height: 100%;
            min-width: 0;
            min-height: 0;
            cursor: pointer;
        }

        .gallery-main-img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
            object-position: center;
            cursor: pointer;
            transition: transform 0.35s ease;
        }

        .gallery-sub-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            grid-template-rows: repeat(2, minmax(0, 1fr));
            gap: 8px;
            width: 100%;
            height: 100%;
            min-width: 0;
            min-height: 0;
        }

        .gallery-sub-item {
            position: relative;
            overflow: hidden;
            width: 100%;
            height: 100%;
            min-width: 0;
            min-height: 0;
            cursor: pointer;
        }

        .gallery-sub-img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
            object-position: center;
            transition: transform 0.35s ease;
        }

        .gallery-main-img:hover, .gallery-sub-img:hover {
            transform: scale(1.03);
        }

        .gallery-more-overlay {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.68);
            backdrop-filter: blur(2px);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FFFFFF;
            font-size: 15px;
            font-weight: 700;
            transition: background 0.2s;
            pointer-events: none;
        }

        .gallery-sub-item:hover .gallery-more-overlay {
            background: rgba(15, 23, 42, 0.82);
        }

        .btn-open-all-photos {
            position: absolute;
            bottom: 16px;
            right: 16px;
            background: rgba(255, 255, 255, 0.96);
            color: #0F172A;
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
            transition: all 0.2s;
            z-index: 10;
        }

        .btn-open-all-photos:hover {
            background: #FFFFFF;
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.16);
            color: #0066FF;
        }

        /* 3.1 Gallery Lightbox Modal (Fullscreen Overlay) */
        .gallery-lightbox-modal {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(8px);
            z-index: 9999;
            display: none;
            flex-direction: column;
            justify-content: space-between;
            padding: 20px 30px;
            color: #FFFFFF;
        }

        .gallery-lightbox-modal.active {
            display: flex;
        }

        .lightbox-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            padding-bottom: 14px;
        }

        .lightbox-title-box h4 {
            font-size: 18px;
            font-weight: 700;
            color: #F8FAFC;
            margin-bottom: 2px;
        }

        .lightbox-counter {
            font-size: 13px;
            color: #94A3B8;
        }

        .lightbox-close-btn {
            background: rgba(255, 255, 255, 0.12);
            border: none;
            color: #FFFFFF;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-size: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .lightbox-close-btn:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: rotate(90deg);
        }

        .lightbox-stage {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 1;
            padding: 16px 0;
            overflow: hidden;
            user-select: none;
            width: 100%;
        }

        .lightbox-nav-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: #FFFFFF;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            font-size: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            z-index: 15;
        }

        .lightbox-nav-btn.btn-prev {
            left: 20px;
        }

        .lightbox-nav-btn.btn-next {
            right: 20px;
        }

        .lightbox-nav-btn:hover {
            background: #0066FF;
            border-color: #0066FF;
            transform: translateY(-50%) scale(1.08);
        }

        .lightbox-image-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            max-width: 86vw;
            max-height: 75vh;
            margin: 0 auto;
            text-align: center;
        }

        .lightbox-image-container img {
            max-width: 100%;
            max-height: 68vh;
            border-radius: 12px;
            object-fit: contain;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
            transition: opacity 0.2s ease;
            margin: 0 auto;
            display: block;
        }

        .lightbox-caption {
            margin-top: 14px;
            font-size: 14px;
            color: #CBD5E1;
            text-align: center;
            min-height: 20px;
        }

        .lightbox-thumbnails-strip {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding-top: 14px;
            border-top: 1px solid rgba(255, 255, 255, 0.12);
            overflow-x: auto;
            max-width: 100%;
        }

        .lightbox-thumb {
            width: 68px;
            height: 48px;
            border-radius: 8px;
            object-fit: cover;
            opacity: 0.5;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.2s;
            flex-shrink: 0;
        }

        .lightbox-thumb:hover {
            opacity: 0.85;
        }

        .lightbox-thumb.active {
            opacity: 1;
            border-color: #0066FF;
            transform: scale(1.05);
        }

        /* 6. Related Destinations (Địa Điểm Khám Phá Liên Quan - Chuẩn nghiệp vụ & Thiết kế tinh tế) */
        .related-destinations-section {
            margin-top: 48px;
            padding-top: 36px;
            border-top: 1px solid #E2E8F0;
        }

        .section-title-wrap {
            margin-bottom: 22px;
        }

        .section-title-wrap .section-headline {
            font-size: 20px;
            font-weight: 700;
            color: #0F172A;
            margin: 0 0 6px 0;
            letter-spacing: -0.01em;
        }

        .section-title-wrap .section-subtitle {
            font-size: 13.5px;
            color: #64748B;
            margin: 0;
            line-height: 1.5;
        }

        .related-cards-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 20px;
            align-items: stretch;
        }

        .related-dest-card {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 100%;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
            position: relative;
        }

        .related-dest-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.08);
            border-color: #CBD5E1;
        }

        .related-card-img-wrap {
            position: relative;
            width: 100%;
            height: 160px;
            overflow: hidden;
            background: #F1F5F9;
        }

        .related-card-img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.3s ease;
        }

        .related-dest-card:hover .related-card-img-wrap img {
            transform: scale(1.04);
        }

        .related-badges-stack {
            position: absolute;
            top: 10px;
            left: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
            z-index: 2;
        }

        .badge-cat-tag {
            background: rgba(15, 23, 42, 0.72);
            backdrop-filter: blur(4px);
            color: #FFFFFF;
            font-size: 11px;
            font-weight: 500;
            padding: 3px 8px;
            border-radius: 6px;
            line-height: 1.2;
        }

        .badge-hotel-tag {
            background: rgba(15, 23, 42, 0.72);
            backdrop-filter: blur(4px);
            color: #FFFFFF;
            font-size: 11px;
            font-weight: 500;
            padding: 3px 8px;
            border-radius: 6px;
            line-height: 1.2;
        }

        .fav-form-corner {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 3;
        }

        .btn-fav-small {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            line-height: 0;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
            transition: all 0.2s;
        }

        .btn-fav-small:hover {
            transform: scale(1.08);
            background: #FFFFFF;
        }

        .related-card-body {
            padding: 14px 16px;
            display: flex;
            flex-direction: column;
            flex: 1;
            justify-content: space-between;
        }

        .related-meta-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            color: #64748B;
            margin-bottom: 6px;
        }

        .related-city-name {
            font-weight: 500;
            color: #64748B;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .related-rating {
            font-weight: 600;
            color: #D97706;
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }

        .related-dest-title {
            font-size: 14.5px;
            font-weight: 600;
            color: #0F172A;
            margin: 0 0 6px 0;
            line-height: 1.35;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .related-dest-title a {
            color: inherit;
            text-decoration: none;
            transition: color 0.15s;
        }

        .related-dest-title a:hover {
            color: #2563EB;
        }

        .related-dest-desc {
            font-size: 12.5px;
            color: #64748B;
            line-height: 1.5;
            margin: 0 0 12px 0;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            min-height: 38px;
        }

        .related-card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 10px;
            border-top: 1px solid #F1F5F9;
            margin-top: auto;
        }

        .price-label-xs {
            font-size: 11px;
            color: #94A3B8;
            display: block;
            line-height: 1.2;
            margin-bottom: 2px;
        }

        .price-val-sm {
            font-size: 13.5px;
            font-weight: 700;
            color: #0F172A;
        }

        .btn-related-detail {
            font-size: 12px;
            font-weight: 600;
            color: #334155;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            padding: 5px 12px;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-related-detail:hover {
            background: #0F172A;
            border-color: #0F172A;
            color: #FFFFFF;
        }

        .related-empty-box {
            background: #F8FAFC;
            border: 1px dashed #CBD5E1;
            border-radius: 12px;
            padding: 36px 20px;
            text-align: center;
            color: #64748B;
        }

        @media (max-width: 1024px) {
            .related-cards-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .related-cards-grid {
                grid-template-columns: 1fr;
            }
        }
        }

        @media (max-width: 640px) {
            .related-cards-grid {
                grid-template-columns: 1fr;
            }
        }


        /* 4. Highlight Pills Bar */
        .highlight-pills-bar {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 35px;
        }

        .pill-card {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            font-weight: 600;
            color: #1E293B;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        }

        .pill-card strong {
            color: #0066FF;
        }

        /* 5. Hai Cột Nội Dung (65% / 35%) */
        .detail-body-grid {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 32px;
            align-items: flex-start;
        }

        .content-card-box {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 20px;
            padding: 32px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        }

        .section-headline {
            font-size: 22px;
            font-weight: 800;
            color: #0F172A;
            margin-bottom: 16px;
            letter-spacing: -0.3px;
        }

        .section-subheadline {
            font-size: 17px;
            font-weight: 700;
            color: #1E293B;
            margin: 24px 0 12px;
        }

        .text-desc {
            font-size: 15px;
            color: #475569;
            line-height: 1.7;
            margin-bottom: 16px;
        }

        .bullet-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 24px;
        }

        .bullet-list li {
            font-size: 14px;
            color: #334155;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            line-height: 1.6;
        }

        .bullet-list li::before {
            content: "•";
            color: #0066FF;
            font-weight: bold;
            font-size: 18px;
            line-height: 1;
        }

        .amenities-grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-top: 14px;
        }

        .amenity-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            font-weight: 500;
            color: #475569;
        }

        .amenity-icon {
            color: #0066FF;
            font-size: 16px;
        }

        /* 6. Khu Vực Room Types */
        .room-type-card {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 16px;
            padding: 22px;
            margin-bottom: 18px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .room-type-card:hover {
            border-color: #0066FF;
            box-shadow: 0 6px 16px rgba(0, 102, 255, 0.08);
            background: #FFFFFF;
        }

        .room-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .room-title-name {
            font-size: 18px;
            font-weight: 700;
            color: #0F172A;
        }

        .room-price-val {
            font-size: 20px;
            font-weight: 800;
            color: #0066FF;
        }

        .room-info-tags {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        .room-tag {
            background: #E2E8F0;
            color: #334155;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .room-tag-green {
            background: #DCFCE7;
            color: #166534;
        }

        .room-desc-text {
            font-size: 13px;
            color: #64748B;
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .room-actions-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 14px;
            border-top: 1px solid #E2E8F0;
        }

        .btn-view-room-modal {
            background: none;
            border: none;
            color: #0066FF;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 0;
        }

        .btn-view-room-modal:hover {
            text-decoration: underline;
        }

        .btn-select-room {
            padding: 8px 18px;
            background: #0066FF;
            color: #FFFFFF;
            border: none;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-select-room:hover {
            background: #0052CC;
        }

        /* 7. Right Sticky Booking Widget */
        .booking-widget-card {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 20px;
            padding: 26px;
            position: sticky;
            top: 90px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .booking-price-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 18px;
            border-bottom: 1px solid #F1F5F9;
        }

        .price-sub-label {
            font-size: 12px;
            color: #64748B;
            font-weight: 500;
            margin-bottom: 2px;
        }

        .price-big-text {
            font-size: 26px;
            font-weight: 800;
            color: #0066FF;
            line-height: 1.2;
        }

        .price-big-text span {
            font-size: 14px;
            color: #64748B;
            font-weight: 500;
        }

        .badge-room-status {
            background: #DCFCE7;
            color: #15803D;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }

        .widget-form-group {
            margin-bottom: 16px;
        }

        .widget-form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }

        .widget-select, .widget-input {
            width: 100%;
            height: 44px;
            padding: 0 12px;
            border: 1px solid #CBD5E1;
            border-radius: 10px;
            background: #FFFFFF;
            font-size: 14px;
            color: #0F172A;
            outline: none;
            transition: all 0.2s;
        }

        .widget-select:focus, .widget-input:focus {
            border-color: #0066FF;
            box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.12);
        }

        .widget-dates-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .btn-widget-primary {
            width: 100%;
            height: 48px;
            background: #0066FF;
            color: #FFFFFF;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background 0.2s, transform 0.2s;
            box-shadow: 0 4px 14px rgba(0, 102, 255, 0.25);
        }

        .btn-widget-primary:hover {
            background: #0052CC;
            transform: translateY(-1px);
        }

        .btn-widget-secondary {
            width: 100%;
            height: 44px;
            background: #FFFFFF;
            color: #0066FF;
            border: 1px solid #BAE6FD;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .btn-widget-secondary:hover {
            background: #F0F9FF;
            border-color: #7DD3FC;
        }

        .widget-footer-note {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 12px;
            color: #059669;
            font-weight: 600;
            margin-top: 16px;
            text-align: center;
        }

        /* 8. Modal Chi Tiết Room Type */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.25s ease;
            padding: 20px;
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal-dialog-box {
            background: #FFFFFF;
            border-radius: 20px;
            width: 100%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            padding: 30px;
            position: relative;
            transform: translateY(20px);
            transition: transform 0.25s ease;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }

        .modal-overlay.active .modal-dialog-box {
            transform: translateY(0);
        }

        .btn-close-modal {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #F1F5F9;
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748B;
            transition: all 0.2s;
        }

        .btn-close-modal:hover {
            background: #E2E8F0;
            color: #0F172A;
        }

        @media (max-width: 992px) {
            .detail-body-grid {
                grid-template-columns: 1fr;
            }
            .booking-widget-card {
                position: static;
                margin-top: 30px;
            }
            .gallery-container {
                grid-template-columns: 1fr;
                height: auto;
                gap: 8px;
            }
            .gallery-main-img {
                height: 320px;
            }
            .gallery-sub-grid {
                height: 240px;
                gap: 8px;
            }
            .highlight-pills-bar {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 640px) {
            .navbar {
                padding: 12px 16px;
            }
            .container {
                padding: 16px 16px 40px;
            }
            .dest-main-title {
                font-size: 24px;
            }
            .highlight-pills-bar {
                grid-template-columns: 1fr;
            }
        }

        /* ================= 8. CSS Quản lý Nhận xét & Đánh giá (Chuẩn Hình 2 Báo Cáo) ================= */
        .review-section-box {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 16px;
            padding: 28px 32px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
            margin-top: 24px;
        }
        .reviews-header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 24px;
        }
        .reviews-headline {
            font-size: 21px;
            font-weight: 800;
            color: #0F172A;
            margin: 0 0 4px 0;
            letter-spacing: -0.3px;
        }
        .reviews-subheadline {
            font-size: 13.5px;
            color: #64748B;
            margin: 0;
        }
        .reviews-rating-header-box {
            text-align: right;
        }
        .rating-display-large {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 4px;
        }
        .star-rating-icon {
            font-size: 18px;
            line-height: 1;
        }
        .rating-score-blue {
            color: #0066FF;
            font-size: 22px;
            font-weight: 800;
            line-height: 1;
        }
        .rating-max-muted {
            color: #64748B;
            font-size: 15px;
            font-weight: 600;
            line-height: 1;
        }
        .rating-count-subtext {
            font-size: 12px;
            color: #64748B;
            margin-top: 4px;
        }
        .review-create-card {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            padding: 22px 24px;
            margin-bottom: 24px;
        }
        .review-create-title {
            font-size: 16px;
            font-weight: 700;
            color: #0F172A;
            margin: 0 0 10px 0;
        }
        .review-input-label {
            display: block;
            font-size: 13px;
            color: #475569;
            font-weight: 500;
            margin-bottom: 6px;
        }
        .review-star-picker-row {
            display: flex;
            align-items: center;
            gap: 4px;
            margin-bottom: 14px;
        }
        .star-interactive-btn {
            border: none;
            background: transparent;
            padding: 0;
            cursor: pointer;
            font-size: 24px;
            line-height: 1;
            color: #CBD5E1;
            transition: transform 0.15s, color 0.15s;
        }
        .star-interactive-btn:hover {
            transform: scale(1.15);
        }
        .star-interactive-btn i.active,
        .star-interactive-btn i.bi-star-fill {
            color: #F59E0B;
        }
        .review-comment-textarea {
            width: 100%;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 13.5px;
            color: #0F172A;
            font-family: inherit;
            resize: vertical;
            outline: none;
            transition: border-color 0.2s;
            min-height: 80px;
            background: #FFFFFF;
        }
        .review-comment-textarea:focus {
            border-color: #0066FF;
        }
        .review-form-bottom-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 14px;
            flex-wrap: wrap;
            gap: 12px;
        }
        .review-user-registered {
            font-size: 13px;
            color: #475569;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .btn-clean-submit {
            background: #0066FF;
            color: #FFFFFF;
            border: none;
            border-radius: 20px;
            padding: 9px 24px;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s;
        }
        .btn-clean-submit:hover {
            background: #0052CC;
            color: #FFFFFF;
        }
        .btn-clean-delete {
            background: transparent;
            border: 1px solid #CBD5E1;
            color: #EF4444;
            border-radius: 20px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-clean-delete:hover {
            background: #FEE2E2;
            border-color: #EF4444;
        }
        .review-feed-card {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            padding: 18px 22px;
            margin-bottom: 16px;
            transition: all 0.2s;
        }
        .review-feed-card:hover {
            border-color: #CBD5E1;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
        }
        .review-feed-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .review-feed-author-box {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .review-feed-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            background: #0066FF;
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            flex-shrink: 0;
        }
        .review-feed-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .review-feed-name {
            font-size: 15px;
            font-weight: 700;
            color: #0F172A;
            display: flex;
            align-items: center;
        }
        .review-feed-meta {
            font-size: 12.5px;
            color: #64748B;
            margin-top: 1px;
        }
        .review-feed-stars {
            display: flex;
            align-items: center;
            gap: 3px;
            font-size: 15px;
        }
        .review-feed-stars i.star-empty {
            color: #E2E8F0;
        }
        .review-feed-stars i.star-filled {
            color: #F59E0B;
        }
        .review-feed-comment {
            font-size: 13.5px;
            color: #334155;
            line-height: 1.65;
            margin: 0;
        }
    </style>
</head>
<body>

    <!-- 1. Thanh điều hướng Navbar -->
    <nav class="navbar">
        <a href="{{ route('home') }}" class="navbar-brand">
            <div class="brand-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon>
                </svg>
            </div>
            TRAVEL PLANNER
        </a>

        <ul class="navbar-nav">
            <li>
                <a href="{{ route('home') }}" class="nav-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg>
                    Trang chủ
                </a>
            </li>
            <li>
                <a href="{{ route('destinations.index') }}" class="nav-link active">
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
                <a href="{{ route('bookings.index') }}" class="nav-link">
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
                    <div class="user-avatar">
                        {{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <span>{{ Auth::user()->name }}</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="btn-login">Đăng nhập</a>
            @endauth
        </div>
    </nav>

    <!-- 2. Nội dung chính: Chi tiết Địa Điểm & Nơi Lưu Trú & Room Types -->
    <main class="container">
        <!-- Breadcrumb -->
        <div class="breadcrumb-wrap">
            <a href="{{ route('home') }}">Trang chủ</a>
            <span>/</span>
            <a href="{{ route('destinations.index') }}">Địa điểm</a>
            <span>/</span>
            <span style="color: #0F172A; font-weight: 600;">{{ $destination->name }}</span>
        </div>

        <!-- Top Header: Huy hiệu & Nút thao tác -->
        <div class="detail-top-row">
            <div>
                <div class="top-tags-box">
                    <span class="badge-cat-pill">
                        {{ $destination->category->name ?? 'Du lịch & Nghỉ dưỡng' }}
                    </span>
                    @if($destination->has_hotel)
                        <span class="badge-hotel-pill">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Có khách sạn lưu trú
                        </span>
                    @endif
                </div>
                <h1 class="dest-main-title">{{ $destination->name }}</h1>
                <p class="dest-address-text">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    {{ $destination->address ?? ($destination->city->name . ', Việt Nam') }}
                </p>
            </div>

            <!-- Action Buttons: Yêu thích, Thêm vào Trip, Đặt phòng ngay -->
            <div class="top-action-buttons">
                <!-- Yêu thích -->
                <form action="{{ route('favorites.toggle', $destination->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-action-outline btn-action-fav" title="Lưu yêu thích">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                        Yêu thích
                    </button>
                </form>

                <!-- Thêm vào Trip -->
                <button type="button" class="btn-action-outline btn-action-trip" onclick="openAddToTripModal()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                    Thêm vào Trip
                </button>

                <!-- Đặt phòng ngay (Mở trang Đặt phòng - Hình 27 Báo cáo) -->
                <a href="{{ route('bookings.create', $destination->id) }}" class="btn-action-book-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                    Đặt phòng ngay
                </a>
            </div>
        </div>

        <!-- 3. Thư viện hình ảnh Gallery (Hiển thị chính xác theo số lượng ảnh thực tế của địa điểm) -->
        @php
            $dbImages = $destination->images->isNotEmpty() ? $destination->images->pluck('image_path')->all() : [];
            $primaryImg = $destination->primaryImage?->image_path;

            $galleryImages = [];
            if ($primaryImg) {
                if (!str_starts_with($primaryImg, 'http') && !str_starts_with($primaryImg, '/')) {
                    $primaryImg = '/' . $primaryImg;
                }
                $galleryImages[] = $primaryImg;
            }
            foreach ($dbImages as $img) {
                if (!empty($img) && !str_starts_with($img, 'http') && !str_starts_with($img, '/')) {
                    $img = '/' . $img;
                }
                if (!in_array($img, $galleryImages)) {
                    $galleryImages[] = $img;
                }
            }

            // Nếu địa điểm chưa có ảnh nào trong cơ sở dữ liệu, dùng 1 ảnh mặc định duy nhất
            if (empty($galleryImages)) {
                $galleryImages = ['https://images.unsplash.com/photo-1506744038136-46273834b3fb?w=1000'];
            }

            $totalPhotos = count($galleryImages);
            $lightboxPhotos = $galleryImages;
        @endphp

        @if($totalPhotos === 1)
            <div class="gallery-container gallery-single">
                <div class="gallery-main-wrap" onclick="openGalleryLightbox(0)" title="Xem ảnh lớn">
                    <img src="{{ $galleryImages[0] }}" alt="{{ $destination->name }}" class="gallery-main-img" id="mainGalleryImg">
                </div>

                <button type="button" class="btn-open-all-photos" onclick="openGalleryLightbox(0)" id="btnOpenGallery">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                    <span>Xem hình ảnh</span>
                </button>
            </div>
        @elseif($totalPhotos === 2)
            <div class="gallery-container gallery-two">
                <div class="gallery-main-wrap" onclick="openGalleryLightbox(0)" title="Xem ảnh">
                    <img src="{{ $galleryImages[0] }}" alt="{{ $destination->name }}" class="gallery-main-img" id="mainGalleryImg">
                </div>
                <div class="gallery-main-wrap" onclick="openGalleryLightbox(1)" title="Xem ảnh">
                    <img src="{{ $galleryImages[1] }}" alt="{{ $destination->name }}" class="gallery-main-img">
                </div>

                <button type="button" class="btn-open-all-photos" onclick="openGalleryLightbox(0)" id="btnOpenGallery">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                    <span>Xem tất cả 2 hình ảnh</span>
                </button>
            </div>
        @elseif($totalPhotos === 3)
            <div class="gallery-container gallery-three">
                <div class="gallery-main-wrap" onclick="openGalleryLightbox(0)" title="Xem ảnh lớn">
                    <img src="{{ $galleryImages[0] }}" alt="{{ $destination->name }}" class="gallery-main-img" id="mainGalleryImg">
                </div>
                <div class="gallery-sub-grid gallery-sub-grid-two">
                    <div class="gallery-sub-item" onclick="openGalleryLightbox(1)" title="Xem hình ảnh">
                        <img src="{{ $galleryImages[1] }}" alt="{{ $destination->name }}" class="gallery-sub-img">
                    </div>
                    <div class="gallery-sub-item" onclick="openGalleryLightbox(2)" title="Xem hình ảnh">
                        <img src="{{ $galleryImages[2] }}" alt="{{ $destination->name }}" class="gallery-sub-img">
                    </div>
                </div>

                <button type="button" class="btn-open-all-photos" onclick="openGalleryLightbox(0)" id="btnOpenGallery">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                    <span>Xem tất cả 3 hình ảnh</span>
                </button>
            </div>
        @elseif($totalPhotos === 4)
            <div class="gallery-container gallery-four">
                <div class="gallery-main-wrap" onclick="openGalleryLightbox(0)" title="Xem ảnh lớn">
                    <img src="{{ $galleryImages[0] }}" alt="{{ $destination->name }}" class="gallery-main-img" id="mainGalleryImg">
                </div>
                <div class="gallery-sub-grid gallery-sub-grid-three">
                    <div class="gallery-sub-item gallery-sub-span-2" onclick="openGalleryLightbox(1)" title="Xem hình ảnh">
                        <img src="{{ $galleryImages[1] }}" alt="{{ $destination->name }}" class="gallery-sub-img">
                    </div>
                    <div class="gallery-sub-item" onclick="openGalleryLightbox(2)" title="Xem hình ảnh">
                        <img src="{{ $galleryImages[2] }}" alt="{{ $destination->name }}" class="gallery-sub-img">
                    </div>
                    <div class="gallery-sub-item" onclick="openGalleryLightbox(3)" title="Xem hình ảnh">
                        <img src="{{ $galleryImages[3] }}" alt="{{ $destination->name }}" class="gallery-sub-img">
                    </div>
                </div>

                <button type="button" class="btn-open-all-photos" onclick="openGalleryLightbox(0)" id="btnOpenGallery">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                    <span>Xem tất cả 4 hình ảnh</span>
                </button>
            </div>
        @else
            <div class="gallery-container gallery-five-plus">
                <div class="gallery-main-wrap" onclick="openGalleryLightbox(0)" title="Xem ảnh lớn">
                    <img src="{{ $galleryImages[0] }}" alt="{{ $destination->name }}" class="gallery-main-img" id="mainGalleryImg">
                </div>
                <div class="gallery-sub-grid">
                    @foreach(array_slice($galleryImages, 1, 4) as $index => $sImg)
                        <div class="gallery-sub-item" onclick="openGalleryLightbox({{ $index + 1 }})" title="Xem hình ảnh">
                            <img src="{{ $sImg }}" alt="{{ $destination->name }}" class="gallery-sub-img">
                            @if($loop->last && $totalPhotos > 5)
                                <div class="gallery-more-overlay">
                                    <span>+{{ $totalPhotos - 5 }} ảnh khác</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <button type="button" class="btn-open-all-photos" onclick="openGalleryLightbox(0)" id="btnOpenGallery">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                    <span>Xem tất cả {{ $totalPhotos }} hình ảnh</span>
                </button>
            </div>
        @endif

        <!-- 4. Thanh Highlight Pills (Đánh giá, Giờ mở cửa, Giá vé, Khách sạn) -->
        <div class="highlight-pills-bar">
            <div class="pill-card">
                <span>⭐</span>
                <div>
                    @if($destination->rating > 0 && $destination->approvedReviews->count() > 0)
                        <strong>{{ number_format($destination->rating, 1) }} / 5.0</strong>
                        <span style="color: #64748B; font-weight: 400;">({{ $destination->approvedReviews->count() }} đánh giá)</span>
                    @else
                        <strong>Chưa có đánh giá</strong>
                        <span style="color: #64748B; font-weight: 400;">(0 đánh giá)</span>
                    @endif
                </div>
            </div>

            <div class="pill-card">
                <span style="color: #0066FF;">⏰</span>
                <div>
                    Mở cửa: <strong>{{ $destination->opening_time ? substr($destination->opening_time, 0, 5) : '07:30' }} - {{ $destination->closing_time ? substr($destination->closing_time, 0, 5) : '21:00' }}</strong> hàng ngày
                </div>
            </div>

            <div class="pill-card">
                <span style="color: #0066FF;">🎟</span>
                <div>
                    Vé tham quan từ: 
                    <strong>
                        {{ $destination->entrance_fee > 0 ? number_format($destination->entrance_fee, 0, ',', '.') . 'đ / người' : 'Miễn phí' }}
                    </strong>
                </div>
            </div>

            <div class="pill-card">
                <span style="color: #0066FF;">🏨</span>
                <div>
                    Khách sạn: 
                    <strong>
                        @if($destination->has_hotel && $destination->roomTypes->isNotEmpty())
                            {{ $destination->roomTypes->first()->name }}
                        @else
                            {{ $destination->name }} Resort
                        @endif
                    </strong>
                </div>
            </div>
        </div>

        <!-- 5. Hai Cột Nội Dung (Cột Trái 65% - Cột Phải 35%) -->
        <div class="detail-body-grid">
            
            <!-- Cột Trái: Giới Thiệu & Danh Sách Room Types -->
            <div>
                <!-- Khối Giới Thiệu Điểm Đến -->
                <div class="content-card-box">
                    <h2 class="section-headline">Giới thiệu điểm đến</h2>
                    <p class="text-desc">
                        {{ $destination->description ?? 'Điểm đến du lịch nghỉ dưỡng hàng đầu, sở hữu cảnh quan thiên nhiên tuyệt mỹ cùng hệ thống dịch vụ tiêu chuẩn quốc tế.' }}
                    </p>

                    <h3 class="section-subheadline">Điểm nổi bật thu hút</h3>
                    <ul class="bullet-list">
                        <li><strong>Cầu Vàng / Điểm Check-in Biểu Tượng:</strong> Biểu tượng du lịch quốc tế với kiến trúc ấn tượng, địa điểm check-in không thể bỏ lỡ.</li>
                        <li><strong>Làng Pháp & Lâu Đài Cổ Kính:</strong> Kiến trúc Gothic nguy nga tráng lệ mang đậm phong cách Châu Âu thế kỷ 19.</li>
                        <li><strong>Khu Vui Chơi Giải Trí:</strong> Hàng trăm trò chơi phiêu lưu và giải trí đẳng cấp cho mọi lứa tuổi.</li>
                        <li><strong>Vườn Hoa Nghệ Thuật:</strong> Các khu vườn hoa rực rỡ sắc màu với phong cách nghệ thuật lãng mạn.</li>
                    </ul>

                    <h3 class="section-subheadline">Thông tin tiện ích & dịch vụ</h3>
                    <div class="amenities-grid-3">
                        <div class="amenity-item">
                            <span class="amenity-icon">📶</span>
                            <span>Wifi miễn phí</span>
                        </div>
                        <div class="amenity-item">
                            <span class="amenity-icon">🅿️</span>
                            <span>Bãi đỗ xe rộng rãi</span>
                        </div>
                        <div class="amenity-item">
                            <span class="amenity-icon">🍽️</span>
                            <span>Khu ẩm thực Buffet</span>
                        </div>
                        <div class="amenity-item">
                            <span class="amenity-icon">💳</span>
                            <span>Hỗ trợ thanh toán thẻ</span>
                        </div>
                        <div class="amenity-item">
                            <span class="amenity-icon">🏥</span>
                            <span>Trạm sơ cứu y tế</span>
                        </div>
                        <div class="amenity-item">
                            <span class="amenity-icon">🛅</span>
                            <span>Phòng gửi hành lý</span>
                        </div>
                    </div>
                </div>



                <!-- ================= Khối Nhận Xét & Đánh Giá Địa Điểm (Chuẩn Hình 2 Báo Cáo) ================= -->
                <div class="review-section-box mt-4" id="destination-reviews-section">
                    
                    <!-- Header Tiêu đề & Tổng điểm đánh giá -->
                    <div class="reviews-header-row">
                        <div>
                            <h2 class="reviews-headline">Đánh giá từ khách du lịch</h2>
                            <p class="reviews-subheadline">Xem nhận xét thực tế từ những người đã trải nghiệm</p>
                        </div>
                        <div class="reviews-rating-header-box">
                            <div class="rating-display-large">
                                <span class="star-rating-icon">⭐</span>
                                @php
                                    $approvedCount = $destination->approvedReviews->count();
                                    $scoreDisplay = $destination->rating > 0 ? number_format($destination->rating, 1) : '5.0';
                                    $countDisplay = number_format($approvedCount, 0, ',', '.');
                                @endphp
                                <span class="rating-score-blue">{{ $scoreDisplay }}</span>
                                <span class="rating-max-muted">/ 5.0</span>
                            </div>
                            <div class="rating-count-subtext">
                                {{ $countDisplay }} đánh giá đã duyệt
                            </div>
                        </div>
                    </div>

                    <!-- Form Viết / Chỉnh sửa đánh giá (Khớp 100% Ảnh 2 Báo Cáo) -->
                    @auth
                        <div class="review-create-card" id="userReviewBox">
                            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                                <h3 class="review-create-title mb-0">Viết đánh giá của bạn</h3>
                                @if($userReview)
                                    <div class="d-flex align-items-center gap-2">
                                        @if($userReview->status === \App\Models\Review::STATUS_PENDING)
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-1 fw-semibold small">
                                                <i class="bi bi-clock-history me-1"></i>Đang chờ duyệt
                                            </span>
                                        @elseif($userReview->status === \App\Models\Review::STATUS_APPROVED)
                                            <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle rounded-pill px-3 py-1 fw-semibold small">
                                                <i class="bi bi-check2-circle me-1"></i>Đã được duyệt
                                            </span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle rounded-pill px-3 py-1 fw-semibold small">
                                                <i class="bi bi-eye-slash me-1"></i>Đã ẩn
                                            </span>
                                        @endif
                                        <a href="{{ Route::has('reviews.my-reviews') ? route('reviews.my-reviews') : url('/my-reviews') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 small fw-medium" title="Xem tất cả bài đánh giá của bạn">
                                            Đánh giá của tôi
                                        </a>
                                    </div>
                                @endif
                            </div>

                            <form action="{{ $userReview ? route('reviews.update', $userReview->id) : route('reviews.store', $destination->id) }}" method="POST" id="mainReviewForm">
                                @csrf
                                @if($userReview)
                                    @method('PUT')
                                @endif

                                <!-- Chọn số sao đánh giá -->
                                <label class="review-input-label">Chọn số sao đánh giá:</label>
                                <div class="review-star-picker-row">
                                    @php
                                        $currentRating = old('rating', $userReview ? $userReview->rating : 5);
                                    @endphp
                                    @for($i = 1; $i <= 5; $i++)
                                        <button type="button" class="star-interactive-btn" onclick="setFormRating({{ $i }})" onmouseover="hoverFormRating({{ $i }})" onmouseout="resetFormRating()" title="{{ $i }} sao">
                                            <i class="bi bi-star-fill {{ $i <= $currentRating ? 'active' : '' }}" id="starIcon-{{ $i }}" style="{{ $i <= $currentRating ? 'color: #F59E0B;' : 'color: #E2E8F0;' }}"></i>
                                        </button>
                                    @endfor
                                    <input type="hidden" name="rating" id="formRatingInput" value="{{ $currentRating }}">
                                    <span class="text-muted small ms-2" id="formRatingText">({{ $currentRating }} sao)</span>
                                </div>
                                @error('rating')
                                    <div class="text-danger small mb-2 d-flex align-items-center gap-1">
                                        <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                                    </div>
                                @enderror

                                <!-- Nội dung đánh giá -->
                                <label class="review-input-label" for="formCommentInput">Nội dung đánh giá:</label>
                                <textarea name="comment" id="formCommentInput" class="review-comment-textarea @error('comment') border-danger @enderror" placeholder="Chia sẻ cảm nhận, lời khuyên và mẹo hay cho chuyến đi..." maxlength="500" oninput="handleCharCounter(this)">{{ old('comment', $userReview ? $userReview->comment : '') }}</textarea>
                                <div class="d-flex justify-content-end mt-1 text-muted" style="font-size: 11px;">
                                    <span id="reviewCharCount">{{ mb_strlen(old('comment', $userReview ? $userReview->comment : '')) }}</span> / 500 ký tự
                                </div>
                                @error('comment')
                                    <div class="text-danger small mt-1 d-flex align-items-center gap-1">
                                        <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                                    </div>
                                @enderror

                                <!-- Hàng dưới: Tên người đăng + Nút hành động -->
                                <div class="review-form-bottom-row">
                                    <div class="review-user-registered">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                        <span>Đăng ký với tên: <strong class="text-dark">{{ auth()->user()->name }}</strong></span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($userReview)
                                            <button type="button" class="btn-clean-delete" onclick="triggerDeleteReview('{{ route('reviews.destroy', $userReview->id) }}')">
                                                <i class="bi bi-trash3 me-1"></i> Xóa đánh giá
                                            </button>
                                        @endif
                                        <button type="submit" class="btn-clean-submit">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                                            <span>{{ $userReview ? 'Cập nhật đánh giá' : 'Gửi đánh giá' }}</span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @else
                        <!-- Khối nhắc đăng nhập sạch sẽ -->
                        <div class="review-create-card d-flex align-items-center justify-content-between flex-wrap gap-3" style="background: #F8FAFC;">
                            <div>
                                <h3 class="review-create-title mb-1">Viết đánh giá của bạn</h3>
                                <p class="reviews-subheadline mb-0">Vui lòng đăng nhập tài khoản để chia sẻ cảm nhận và đánh giá trải nghiệm chuyến đi.</p>
                            </div>
                            <a href="{{ route('login') }}" class="btn-clean-submit text-decoration-none">
                                <span>Đăng nhập để đánh giá</span>
                            </a>
                        </div>
                    @endauth

                    <!-- Danh Sách Các Đánh Giá Thực Tế & Mẫu Khớp Ảnh 2 Báo Cáo -->
                    <div class="reviews-feed-container">
                        @php
                            // Danh sách nhận xét công khai đã được kiểm duyệt (Theo đúng chuẩn DB)
                            $displayReviews = $reviews->filter(function($r) {
                                return $r->status === \App\Models\Review::STATUS_APPROVED;
                            });
                        @endphp

                        @foreach($displayReviews as $item)
                            <div class="review-feed-card" id="reviewCard-{{ $item->id }}">
                                <div class="review-feed-card-header">
                                    <div class="review-feed-author-box">
                                        <div class="review-feed-avatar">
                                            @if($item->user && $item->user->avatar_url)
                                                <img src="{{ $item->user->avatar_url }}" alt="{{ $item->user->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                            @else
                                                <span style="color: #FFFFFF; font-weight: 700; font-size: 15px;">{{ mb_substr($item->user->name ?? 'U', 0, 1) }}</span>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="review-feed-author-name">{{ $item->user->name ?? 'Khách du lịch' }}</span>
                                                @if(auth()->check() && auth()->id() === $item->user_id)
                                                    <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-0 fw-semibold" style="font-size: 11px;">Bạn</span>
                                                @endif
                                                @if($item->status === \App\Models\Review::STATUS_PENDING)
                                                    <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-2 py-0 small fw-medium">
                                                        <i class="bi bi-clock-history me-1"></i>Đang chờ duyệt
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="review-feed-trip-meta">
                                                Đã đi vào {{ $item->created_at ? $item->created_at->format('\T\h\á\n\g m, Y') : 'Tháng 8, 2026' }} • Chuyến đi trải nghiệm
                                            </div>
                                        </div>
                                    </div>
                                    <div class="review-feed-stars">
                                        @for($s = 1; $s <= 5; $s++)
                                            <i class="bi bi-star-fill" style="color: {{ $s <= $item->rating ? '#F59E0B' : '#E2E8F0' }}; font-size: 15px;"></i>
                                        @endfor
                                    </div>
                                </div>
                                <div class="review-feed-comment-text">
                                    {{ $item->comment }}
                                </div>
                            </div>
                        @endforeach

                        @if($displayReviews->isEmpty())
                            <div class="text-center py-5 px-3 bg-light rounded-4 border border-light-subtle">
                                <div class="text-muted mb-2" style="font-size: 32px;">💬</div>
                                <h4 class="h6 fw-bold text-dark mb-1">Chưa có nhận xét nào được hiển thị</h4>
                                <p class="text-muted small mb-0">Hãy là người đầu tiên chia sẻ cảm nhận và đánh giá trải nghiệm tại {{ $destination->name }}!</p>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            <!-- Cột Phải: Widget Đặt Phòng Lưu Trú & Thêm vào Trip (Sticky) -->
            <div>
                <div class="booking-widget-card" id="bookingWidgetBox">
                    <!-- Giá lưu trú từ & Badge Còn phòng -->
                    <div class="booking-price-header">
                        <div>
                            <div class="price-sub-label">Giá lưu trú từ</div>
                            <div class="price-big-text" id="widgetPriceDisplay">
                                @if($destination->has_hotel && $destination->formatted_min_room_price)
                                    {{ $destination->formatted_min_room_price }}
                                @elseif($destination->entrance_fee > 0)
                                    {{ number_format($destination->entrance_fee, 0, ',', '.') }}đ
                                @else
                                    1.850.000đ
                                @endif
                                <span>/ đêm</span>
                            </div>
                        </div>
                        <span class="badge-room-status">Còn phòng</span>
                    </div>

                    <!-- Form Đặt Phòng Nhanh -->
                    <form action="{{ route('bookings.store') }}" method="POST" id="bookingWidgetForm">
                        @csrf
                        <div class="widget-form-group">
                            <label class="widget-form-label" for="widgetRoomTypeSelect">Loại phòng đề xuất</label>
                            <select name="room_type_id" id="widgetRoomTypeSelect" class="widget-select" onchange="updateWidgetPrice(this)" required>
                                @if($destination->roomTypes->isNotEmpty())
                                    @foreach($destination->roomTypes as $r)
                                        <option value="{{ $r->id }}" data-price="{{ number_format($r->price_per_night, 0, ',', '.') }}đ">
                                            {{ $r->name }} ({{ number_format($r->price_per_night, 0, ',', '.') }}đ)
                                        </option>
                                    @endforeach
                                @else
                                    <option value="">Chưa có hạng phòng lưu trú</option>
                                @endif
                            </select>
                        </div>

                        @auth
                            @if(isset($userTrips) && $userTrips->isNotEmpty())
                                <div class="widget-form-group">
                                    <label class="widget-form-label" for="widgetTripSelect">Gắn vào Chuyến đi (tuỳ chọn)</label>
                                    <select name="trip_id" id="widgetTripSelect" class="widget-select">
                                        <option value="">-- Không gắn chuyến đi --</option>
                                        @foreach($userTrips as $tripItem)
                                            <option value="{{ $tripItem->id }}">
                                                {{ $tripItem->title }} ({{ \Carbon\Carbon::parse($tripItem->start_date)->format('d/m') }} - {{ \Carbon\Carbon::parse($tripItem->end_date)->format('d/m/Y') }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        @endauth

                        <div class="widget-form-group">
                            <div class="widget-dates-grid">
                                <div>
                                    <label class="widget-form-label" for="checkInDate">Nhận phòng</label>
                                    <input type="date" name="check_in_date" id="checkInDate" class="widget-input" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}" required>
                                </div>
                                <div>
                                    <label class="widget-form-label" for="checkOutDate">Trả phòng</label>
                                    <input type="date" name="check_out_date" id="checkOutDate" class="widget-input" value="{{ date('Y-m-d', strtotime('+2 days')) }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="widget-form-group">
                            <label class="widget-form-label" for="widgetRoomCount">Số lượng phòng đặt</label>
                            <input type="number" name="room_count" id="widgetRoomCount" class="widget-input" value="1" min="1" max="10" required>
                        </div>

                        <!-- Nút Đặt phòng lưu trú -->
                        @auth
                            @if($destination->roomTypes->isNotEmpty())
                                <button type="submit" class="btn-widget-primary" id="btnBookRoomNow">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                                    Đặt phòng nhanh
                                </button>
                                <a href="{{ route('bookings.create', $destination->id) }}" class="btn-widget-outline" style="display:flex;align-items:center;justify-content:center;gap:8px;margin-top:10px;padding:11px;border:1.5px solid #0066FF;color:#0066FF;border-radius:10px;font-weight:700;text-decoration:none;font-size:14px;background:#f0f7ff;transition:all 0.2s;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                    Mở trang Đặt phòng chi tiết (Hình 27)
                                </a>
                            @else
                                <button type="button" class="btn-widget-primary" style="opacity: 0.6; cursor: not-allowed;" disabled>
                                    Chưa có phòng để đặt
                                </button>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn-widget-primary" style="text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
                                Đăng nhập để đặt phòng
                            </a>
                        @endauth
                    </form>

                    <!-- Nút Thêm vào Lịch trình Trip -->
                    <button type="button" class="btn-widget-secondary" onclick="openAddToTripModal()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                        Thêm vào Lịch trình Trip
                    </button>

                    <!-- Footer Cam Kết -->
                    <div class="widget-footer-note">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        Miễn phí hủy phòng trước 48h
                    </div>
                </div>
            </div>

        </div>

        <!-- 6. Khối Địa Điểm Khám Phá Liên Quan (Related Destinations - FR13) -->
        <div class="related-destinations-section" id="relatedDestinationsSection">
            <div class="section-title-wrap">
                <h2 class="section-headline">Địa Điểm Khám Phá Liên Quan</h2>
                <p class="section-subtitle">
                    Gợi ý các điểm đến hấp dẫn tương tự tại {{ $destination->city->name ?? 'khu vực' }} hoặc cùng danh mục {{ $destination->category->name ?? 'du lịch' }}
                </p>
            </div>

            @if(isset($relatedDestinations) && $relatedDestinations->isNotEmpty())
                <div class="related-cards-grid">
                    @foreach($relatedDestinations as $rel)
                        @php
                            $relImg = $rel->primaryImage?->image_path ?? $rel->images->first()?->image_path;
                            if (!empty($relImg) && !str_starts_with($relImg, 'http') && !str_starts_with($relImg, '/')) {
                                $relImg = '/' . $relImg;
                            }
                            if (empty($relImg)) {
                                $relImg = 'https://images.unsplash.com/photo-1528127269322-539801943592?w=600';
                            }
                        @endphp
                        <div class="related-dest-card">
                            <div class="related-card-img-wrap">
                                <img src="{{ $relImg }}" alt="{{ $rel->name }}" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1528127269322-539801943592?w=600'">

                                <div class="related-badges-stack">
                                    @if($rel->category)
                                        <span class="badge-cat-tag">{{ $rel->category->name }}</span>
                                    @endif
                                    @if($rel->has_hotel)
                                        <span class="badge-hotel-tag">Lưu trú</span>
                                    @endif
                                </div>

                                <!-- Nút Lưu yêu thích nhanh -->
                                <form action="{{ route('favorites.toggle', $rel->id) }}" method="POST" class="fav-form-corner">
                                    @csrf
                                    <button type="submit" class="btn-fav-small" title="Lưu yêu thích">
                                        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="#475569" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: block; pointer-events: none;">
                                            <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>

                            <div class="related-card-body">
                                <div>
                                    <div class="related-meta-row">
                                        <span class="related-city-name">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                            {{ $rel->city->name ?? 'Điểm đến' }}
                                        </span>
                                        <span class="related-rating">
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="#D97706" stroke="#D97706" stroke-width="1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                            {{ number_format($rel->rating, 1) }}
                                        </span>
                                    </div>

                                    <h4 class="related-dest-title">
                                        <a href="{{ route('destinations.show', $rel->slug) }}" title="{{ $rel->name }}">
                                            {{ $rel->name }}
                                        </a>
                                    </h4>

                                    <p class="related-dest-desc">
                                        {{ \Illuminate\Support\Str::limit($rel->description ?? 'Điểm đến du lịch hấp dẫn với nhiều hoạt động trải nghiệm đặc sắc.', 90) }}
                                    </p>
                                </div>

                                <div class="related-card-footer">
                                    <div>
                                        <span class="price-label-xs">Giá từ</span>
                                        <div class="price-val-sm">
                                            @if($rel->has_hotel && $rel->min_room_price)
                                                {{ $rel->formatted_min_room_price }}
                                            @elseif($rel->entrance_fee > 0)
                                                {{ number_format($rel->entrance_fee, 0, ',', '.') }}đ
                                            @else
                                                Miễn phí
                                            @endif
                                        </div>
                                    </div>
                                    <a href="{{ route('destinations.show', $rel->slug) }}" class="btn-related-detail">
                                        Chi tiết
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="related-empty-box">
                    <p style="margin-bottom: 12px; font-weight: 500;">Chưa có địa điểm liên quan nào khác tại khu vực này.</p>
                    <a href="{{ route('destinations.index') }}" class="btn-action-outline" style="display: inline-flex;">
                        Khám phá tất cả địa điểm du lịch
                    </a>
                </div>
            @endif
        </div>
    </main>

    <!-- 8. Modal Chi Tiết Room Type -->
    <div class="modal-overlay" id="roomDetailModal">
        <div class="modal-dialog-box">
            <button type="button" class="btn-close-modal" onclick="closeRoomTypeModal()">&times;</button>
            <div id="modalLoading" style="text-align: center; padding: 30px;">
                <p style="color: #64748B;">Đang tải thông tin loại phòng...</p>
            </div>
            <div id="modalContent" style="display: none;">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                    <span class="badge-cat-pill">Loại phòng</span>
                    <span class="badge-hotel-pill" id="modalRoomCapacity">2 người lớn</span>
                </div>
                <h3 id="modalRoomName" style="font-size: 22px; font-weight: 800; color: #0F172A; margin-bottom: 12px;"></h3>
                
                <div style="font-size: 24px; font-weight: 800; color: #0066FF; margin-bottom: 16px;" id="modalRoomPrice">
                </div>

                <div style="background: #F8FAFC; border-radius: 12px; padding: 14px; margin-bottom: 18px;">
                    <h5 style="font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Mô tả phòng:</h5>
                    <p id="modalRoomDesc" style="font-size: 13px; color: #64748B; line-height: 1.6;"></p>
                </div>

                <h5 style="font-size: 14px; font-weight: 700; color: #0F172A; margin-bottom: 10px;">Tiện ích bao gồm:</h5>
                <ul class="bullet-list" id="modalAmenitiesList">
                </ul>

                <h5 style="font-size: 14px; font-weight: 700; color: #0F172A; margin: 16px 0 8px;">Chính sách phòng:</h5>
                <p style="font-size: 13px; color: #64748B; line-height: 1.5;">
                    • Nhận phòng từ: 14:00 | Trả phòng trước: 12:00 trưa hôm sau.<br>
                    • Hủy phòng miễn phí trước 48 giờ trước ngày nhận phòng.<br>
                    • Yêu cầu xuất trình CMND/CCCD hoặc Hộ chiếu khi nhận phòng.
                </p>

                <div style="margin-top: 24px; display: flex; gap: 12px;">
                    <button type="button" class="btn-action-outline" style="flex: 1;" onclick="closeRoomTypeModal()">Đóng</button>
                    <button type="button" class="btn-action-book-primary" style="flex: 1.5; justify-content: center;" onclick="bookSelectedModalRoom()">
                        Đặt loại phòng này ngay
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 9. Modal Thêm Địa Điểm Vào Trip Của Tôi -->
    <div class="modal-overlay" id="addToTripModal">
        <div class="modal-dialog-box" style="max-width: 480px;">
            <button type="button" class="btn-close-modal" onclick="closeAddToTripModal()">&times;</button>
            <h3 style="font-size: 20px; font-weight: 800; color: #0F172A; margin-bottom: 6px;">
                Thêm Vào Chuyến Đi
            </h3>
            <p style="font-size: 14px; color: #64748B; margin-bottom: 20px;">
                Chọn chuyến đi bạn muốn lên lịch trình cho "{{ $destination->name }}":
            </p>

            @auth
                @if($userTrips->isNotEmpty())
                    <form action="{{ route('favorites.add-to-trip') }}" method="POST">
                        @csrf
                        <input type="hidden" name="destination_id" value="{{ $destination->id }}">
                        
                        <div class="widget-form-group">
                            <label class="widget-form-label" for="selectUserTrip">Chuyến đi của bạn</label>
                            <select name="trip_id" id="selectUserTrip" class="widget-select" required>
                                @foreach($userTrips as $uTrip)
                                    <option value="{{ $uTrip->id }}">
                                        {{ $uTrip->name }} ({{ $uTrip->start_date?->format('d/m/Y') }} - {{ $uTrip->end_date?->format('d/m/Y') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="widget-form-group">
                            <label class="widget-form-label" for="tripDayNumber">Ngày trong lịch trình</label>
                            <input type="number" name="day_number" id="tripDayNumber" class="widget-input" value="1" min="1" max="30">
                        </div>

                        <div class="widget-form-group">
                            <label class="widget-form-label" for="tripItemNote">Ghi chú hoạt động (Tùy chọn)</label>
                            <input type="text" name="note" id="tripItemNote" class="widget-input" placeholder="Ví dụ: Tham quan và nghỉ dưỡng...">
                        </div>

                        <div style="display: flex; gap: 10px; margin-top: 24px;">
                            <button type="button" class="btn-action-outline" style="flex: 1;" onclick="closeAddToTripModal()">Hủy</button>
                            <button type="submit" class="btn-action-book-primary" style="flex: 1.5; justify-content: center;">Thêm vào Trip</button>
                        </div>
                    </form>
                @else
                    <div style="text-align: center; padding: 20px 0;">
                        <p style="font-size: 14px; color: #64748B; margin-bottom: 16px;">
                            Bạn chưa có chuyến đi nào đang lên kế hoạch.
                        </p>
                        <a href="{{ route('trips.create') }}" class="btn-action-book-primary" style="display: inline-flex;">
                            Tạo chuyến đi mới ngay
                        </a>
                    </div>
                @endif
            @else
                <div style="text-align: center; padding: 20px 0;">
                    <p style="font-size: 14px; color: #64748B; margin-bottom: 16px;">
                        Vui lòng đăng nhập để thêm địa điểm vào lịch trình chuyến đi của bạn.
                    </p>
                    <a href="{{ route('login') }}" class="btn-action-book-primary" style="display: inline-flex;">
                        Đăng nhập ngay
                    </a>
                </div>
            @endauth
        </div>
    </div>

    <!-- 10. Gallery Lightbox Modal Xem Ảnh Toàn Màn Hình (FR12) -->
    <div class="gallery-lightbox-modal" id="galleryLightboxModal">
        <div class="lightbox-header">
            <div class="lightbox-title-box">
                <h4>{{ $destination->name }}</h4>
                <span class="lightbox-counter" id="lightboxCounter">Ảnh 1 / {{ count($lightboxPhotos) }}</span>
            </div>
            <button type="button" class="lightbox-close-btn" onclick="closeGalleryLightbox()" title="Đóng (Esc)">&times;</button>
        </div>
        <div class="lightbox-stage">
            @if(count($lightboxPhotos) > 1)
                <button type="button" class="lightbox-nav-btn btn-prev" onclick="changeLightboxImage(-1)" title="Ảnh trước (←)">
                    &lsaquo;
                </button>
            @endif
            <div class="lightbox-image-container">
                <img src="{{ $lightboxPhotos[0] }}" id="lightboxActiveImg" alt="{{ $destination->name }}">
                <p class="lightbox-caption" id="lightboxCaption">
                    {{ $destination->name }} - Khám phá vẻ đẹp ấn tượng
                </p>
            </div>
            @if(count($lightboxPhotos) > 1)
                <button type="button" class="lightbox-nav-btn btn-next" onclick="changeLightboxImage(1)" title="Ảnh tiếp theo (→)">
                    &rsaquo;
                </button>
            @endif
        </div>
        @if(count($lightboxPhotos) > 1)
            <div class="lightbox-thumbnails-strip" id="lightboxThumbnailsStrip">
                @foreach($lightboxPhotos as $idx => $pUrl)
                    <img src="{{ $pUrl }}" alt="Thumb {{ $idx + 1 }}" 
                         class="lightbox-thumb {{ $idx === 0 ? 'active' : '' }}" 
                         onclick="setLightboxImage({{ $idx }})">
                @endforeach
            </div>
        @endif
    </div>

    <!-- Modal Xác Nhận Xóa Đánh Giá (Review Delete Modal - Chuẩn Bootstrap 5) -->
    <div class="modal fade" id="deleteReviewModal" tabindex="-1" aria-labelledby="deleteReviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-danger-subtle text-danger border-0 py-3 px-4">
                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="deleteReviewModalLabel">
                        <i class="bi bi-exclamation-triangle-fill"></i> Xác nhận xóa đánh giá
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="fs-6 text-dark mb-2">Bạn có chắc chắn muốn xóa bài đánh giá này không?</p>
                    <p class="text-muted small mb-0">Sau khi xóa, bài nhận xét sẽ được gỡ hoàn toàn và điểm rating trung bình của địa điểm sẽ được tự động tính toán lại.</p>
                </div>
                <div class="modal-footer border-0 p-4 pt-0 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy bỏ</button>
                    <form id="deleteReviewForm" action="" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger rounded-pill px-4 fw-semibold shadow-sm d-inline-flex align-items-center gap-2">
                            <i class="bi bi-trash3"></i> Xác nhận xóa
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Xử Lý Tương Tác, Gallery Lightbox & AJAX Modal Room Type -->
    <script>
        // ================= Quản lý Gallery Lightbox (FR12) =================
        const galleryPhotos = @json($lightboxPhotos);
        let currentLightboxIndex = 0;

        function openGalleryLightbox(index = 0) {
            currentLightboxIndex = index;
            const modal = document.getElementById('galleryLightboxModal');
            if (modal) {
                modal.classList.add('active');
                updateLightboxView();
                document.body.style.overflow = 'hidden';
            }
        }

        function closeGalleryLightbox() {
            const modal = document.getElementById('galleryLightboxModal');
            if (modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        }

        function changeLightboxImage(step) {
            if (!galleryPhotos || galleryPhotos.length === 0) return;
            currentLightboxIndex += step;
            if (currentLightboxIndex < 0) {
                currentLightboxIndex = galleryPhotos.length - 1;
            } else if (currentLightboxIndex >= galleryPhotos.length) {
                currentLightboxIndex = 0;
            }
            updateLightboxView();
        }

        function setLightboxImage(index) {
            if (index >= 0 && index < galleryPhotos.length) {
                currentLightboxIndex = index;
                updateLightboxView();
            }
        }

        function updateLightboxView() {
            if (!galleryPhotos || galleryPhotos.length === 0) return;
            const activeUrl = galleryPhotos[currentLightboxIndex];
            const activeImg = document.getElementById('lightboxActiveImg');
            const counter = document.getElementById('lightboxCounter');
            
            if (activeImg) {
                activeImg.style.opacity = '0.3';
                setTimeout(() => {
                    activeImg.src = activeUrl;
                    activeImg.style.opacity = '1';
                }, 80);
            }

            if (counter) {
                counter.innerText = `Ảnh ${currentLightboxIndex + 1} / ${galleryPhotos.length}`;
            }

            // Đồng bộ trạng thái active của thumbnails strip
            const thumbs = document.querySelectorAll('.lightbox-thumb');
            thumbs.forEach((thumb, idx) => {
                if (idx === currentLightboxIndex) {
                    thumb.classList.add('active');
                    thumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                } else {
                    thumb.classList.remove('active');
                }
            });
        }

        // ================= Quản lý Room Type Modal & Widget =================
        let currentActiveRoomId = null;

        // Cập nhật giá trên widget khi người dùng chọn loại phòng trong dropdown
        function updateWidgetPrice(selectEl) {
            const selectedOpt = selectEl.options[selectEl.selectedIndex];
            const price = selectedOpt.getAttribute('data-price');
            if (price) {
                document.getElementById('widgetPriceDisplay').innerHTML = price + ' <span>/ đêm</span>';
            }
        }

        // Chọn phòng từ danh sách Room Types ở cột trái
        function selectRoomType(roomId, roomName, roomPrice) {
            const selectEl = document.getElementById('widgetRoomTypeSelect');
            if (selectEl) {
                selectEl.value = roomId;
                document.getElementById('widgetPriceDisplay').innerHTML = roomPrice + ' <span>/ đêm</span>';
            }
            // Cuộn mượt tới widget đặt phòng
            const widget = document.querySelector('.booking-widget-card');
            if (widget) {
                widget.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        // Mở Modal xem chi tiết Room Type
        function openRoomTypeModal(roomTypeId) {
            currentActiveRoomId = roomTypeId;
            const modal = document.getElementById('roomDetailModal');
            const loading = document.getElementById('modalLoading');
            const content = document.getElementById('modalContent');
            
            modal.classList.add('active');
            loading.style.display = 'block';
            content.style.display = 'none';

            fetch(`/room-types/${roomTypeId}`)
                .then(res => res.json())
                .then(json => {
                    if (json.success && json.data) {
                        const data = json.data;
                        document.getElementById('modalRoomName').innerText = data.name;
                        document.getElementById('modalRoomCapacity').innerText = data.capacity_label;
                        document.getElementById('modalRoomPrice').innerText = data.formatted_price + ' / đêm';
                        document.getElementById('modalRoomDesc').innerText = data.description;
                        
                        const amenitiesList = document.getElementById('modalAmenitiesList');
                        amenitiesList.innerHTML = '';
                        if (data.amenities && data.amenities.length > 0) {
                            data.amenities.forEach(item => {
                                const li = document.createElement('li');
                                li.innerText = item;
                                amenitiesList.appendChild(li);
                            });
                        }

                        loading.style.display = 'none';
                        content.style.display = 'block';
                    }
                })
                .catch(err => {
                    loading.innerHTML = '<p style="color:#EF4444;">Không thể tải thông tin phòng. Vui lòng thử lại.</p>';
                });
        }

        function closeRoomTypeModal() {
            document.getElementById('roomDetailModal').classList.remove('active');
        }

        function bookSelectedModalRoom() {
            closeRoomTypeModal();
            if (currentActiveRoomId) {
                const selectEl = document.getElementById('widgetRoomTypeSelect');
                if (selectEl) {
                    selectEl.value = currentActiveRoomId;
                    updateWidgetPrice(selectEl);
                }
            }
            const widget = document.querySelector('.booking-widget-card');
            if (widget) {
                widget.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        // Mở và đóng modal thêm vào Trip
        function openAddToTripModal() {
            document.getElementById('addToTripModal').classList.add('active');
        }

        function closeAddToTripModal() {
            document.getElementById('addToTripModal').classList.remove('active');
        }

        // ================= Quản lý Đánh Giá & Nhận Xét Destination (Khớp 100% Ảnh 2 Báo Cáo) =================
        let currentUserSelectedRating = parseInt(document.getElementById('formRatingInput')?.value || document.getElementById('reviewRatingInput')?.value || 5, 10);
        const ratingDescriptions = {
            1: 'Rất tệ (1 sao)',
            2: 'Tệ (2 sao)',
            3: 'Bình thường (3 sao)',
            4: 'Hài lòng (4 sao)',
            5: 'Tuyệt vời (5 sao)'
        };

        function setFormRating(star) {
            currentUserSelectedRating = star;
            const input = document.getElementById('formRatingInput') || document.getElementById('reviewRatingInput');
            if (input) input.value = star;
            renderFormStars(star);
        }

        function hoverFormRating(star) {
            renderFormStars(star);
        }

        function resetFormRating() {
            renderFormStars(currentUserSelectedRating);
        }

        function renderFormStars(star) {
            for (let i = 1; i <= 5; i++) {
                const starEl = document.getElementById(`starIcon-${i}`);
                if (starEl) {
                    if (i <= star) {
                        starEl.style.color = '#F59E0B';
                        starEl.classList.add('active');
                    } else {
                        starEl.style.color = '#E2E8F0';
                        starEl.classList.remove('active');
                    }
                }
            }
            const txt = document.getElementById('formRatingText');
            if (txt) {
                txt.innerText = `(${star} sao)`;
            }
            const label = document.getElementById('reviewRatingLabel');
            if (label) {
                label.innerText = `(${ratingDescriptions[star] || star + ' sao'})`;
            }
        }

        // Tương thích ngược với các tên hàm cũ
        function selectUserRating(star) { setFormRating(star); }
        function hoverUserRating(star) { hoverFormRating(star); }
        function resetUserRating() { resetFormRating(); }
        function updateStarVisuals(star) { renderFormStars(star); }

        function handleCharCounter(textarea) {
            const counter = document.getElementById('reviewCharCount');
            if (counter) {
                counter.innerText = textarea.value.length;
            }
        }

        function triggerDeleteReview(deleteUrl) {
            const form = document.getElementById('deleteReviewForm');
            const modalEl = document.getElementById('deleteReviewModal');
            if (form && modalEl) {
                form.action = deleteUrl;
                if (window.bootstrap && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance(modalEl).show();
                } else {
                    modalEl.classList.add('show');
                    modalEl.style.display = 'block';
                }
            }
        }

        function closeDeleteReviewModal() {
            const modalEl = document.getElementById('deleteReviewModal');
            if (modalEl && window.bootstrap && bootstrap.Modal) {
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }
        }

        // Đóng modal & điều hướng Lightbox khi nhấn phím
        document.addEventListener('keydown', function (e) {
            const lightbox = document.getElementById('galleryLightboxModal');
            const isLightboxActive = lightbox && lightbox.classList.contains('active');

            if (e.key === 'Escape') {
                closeRoomTypeModal();
                closeAddToTripModal();
                closeGalleryLightbox();
                closeDeleteReviewModal();
            } else if (isLightboxActive) {
                if (e.key === 'ArrowLeft') {
                    changeLightboxImage(-1);
                } else if (e.key === 'ArrowRight') {
                    changeLightboxImage(1);
                }
            }
        });
    </script>
    <!-- Bootstrap 5 JS Bundle cho Modal Đánh giá -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @include('commerce.launcher', ['merchant' => false])
    @include('partials.popup-notification')
</body>
</html>

