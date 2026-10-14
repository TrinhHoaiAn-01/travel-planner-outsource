<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $destination->name }} - Nơi Lưu Trú & Chi Tiết Phòng | Travel Planner</title>
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

        /* 3. Gallery Grid (1 Lớn + 4 Nhỏ) & Controls */
        .gallery-container {
            position: relative;
            display: grid;
            grid-template-columns: 1.15fr 1fr;
            gap: 12px;
            border-radius: 20px;
            overflow: hidden;
            margin: 24px 0 28px;
            height: 420px;
            background: #0F172A;
        }

        .gallery-main-wrap {
            position: relative;
            overflow: hidden;
            height: 100%;
            cursor: pointer;
        }

        .gallery-main-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .gallery-sub-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: 1fr 1fr;
            gap: 12px;
            height: 100%;
        }

        .gallery-sub-item {
            position: relative;
            overflow: hidden;
            height: 100%;
            cursor: pointer;
        }

        .gallery-sub-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
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
            border-radius: 24px;
            padding: 8px 18px;
            font-size: 13px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.18);
            transition: all 0.2s;
            z-index: 10;
        }

        .btn-open-all-photos:hover {
            background: #FFFFFF;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.25);
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
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            flex: 1;
            padding: 16px 0;
            overflow: hidden;
            user-select: none;
        }

        .lightbox-nav-btn {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #FFFFFF;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            font-size: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            flex-shrink: 0;
        }

        .lightbox-nav-btn:hover {
            background: #0066FF;
            border-color: #0066FF;
            transform: scale(1.08);
        }

        .lightbox-image-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            max-width: 82vw;
            height: 64vh;
        }

        .lightbox-image-container img {
            max-width: 100%;
            max-height: 58vh;
            border-radius: 12px;
            object-fit: contain;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
            transition: opacity 0.2s ease;
        }

        .lightbox-caption {
            margin-top: 10px;
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

        /* 6. Related Destinations (Địa Điểm Khám Phá Liên Quan - FR13) */
        .related-destinations-section {
            margin-top: 48px;
            padding-top: 36px;
            border-top: 1px solid #E2E8F0;
        }

        .section-title-wrap {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .btn-view-all-related {
            font-size: 14px;
            font-weight: 600;
            color: #0066FF;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: gap 0.2s;
        }

        .btn-view-all-related:hover {
            gap: 8px;
            text-decoration: underline;
        }

        .related-cards-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .related-dest-card {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 16px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .related-dest-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            border-color: #CBD5E1;
        }

        .related-card-img-wrap {
            position: relative;
            height: 180px;
            overflow: hidden;
            background: #F1F5F9;
        }

        .related-card-img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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
            flex-direction: column;
            gap: 5px;
            z-index: 2;
        }

        .badge-cat-tag {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(4px);
            color: #FFFFFF;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 12px;
        }

        .badge-hotel-tag {
            background: rgba(16, 185, 129, 0.9);
            backdrop-filter: blur(4px);
            color: #FFFFFF;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 12px;
        }

        .fav-form-corner {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 3;
        }

        .btn-fav-small {
            background: rgba(255, 255, 255, 0.9);
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
            transition: all 0.2s;
        }

        .btn-fav-small:hover {
            transform: scale(1.1);
            background: #FFFFFF;
        }

        .related-card-body {
            padding: 16px;
            display: flex;
            flex-direction: column;
            flex: 1;
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
            font-weight: 600;
            color: #475569;
        }

        .related-rating {
            font-weight: 700;
            color: #F59E0B;
        }

        .related-dest-title {
            font-size: 15px;
            font-weight: 700;
            color: #0F172A;
            margin-bottom: 6px;
            line-height: 1.35;
        }

        .related-dest-title a {
            color: inherit;
            text-decoration: none;
            transition: color 0.2s;
        }

        .related-dest-title a:hover {
            color: #0066FF;
        }

        .related-dest-desc {
            font-size: 13px;
            color: #64748B;
            line-height: 1.45;
            margin-bottom: 14px;
            flex: 1;
        }

        .related-card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 12px;
            border-top: 1px solid #F1F5F9;
        }

        .price-label-xs {
            font-size: 11px;
            color: #94A3B8;
            display: block;
        }

        .price-val-sm {
            font-size: 14px;
            font-weight: 800;
            color: #0066FF;
        }

        .btn-related-detail {
            font-size: 12px;
            font-weight: 700;
            color: #0066FF;
            background: #EBF5FF;
            padding: 6px 12px;
            border-radius: 16px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-related-detail:hover {
            background: #0066FF;
            color: #FFFFFF;
        }

        .related-empty-box {
            background: #F8FAFC;
            border: 1px dashed #CBD5E1;
            border-radius: 16px;
            padding: 36px 20px;
            text-align: center;
            color: #64748B;
        }

        @media (max-width: 1024px) {
            .related-cards-grid {
                grid-template-columns: repeat(2, 1fr);
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
            }
            .gallery-main-img {
                height: 280px;
            }
            .gallery-sub-grid {
                height: 200px;
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
            .amenities-grid-3 {
                grid-template-columns: 1fr;
            }
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

        @if(session('success'))
            <div style="background: #ECFDF5; border: 1px solid #10B981; color: #065F46; padding: 14px 20px; border-radius: 12px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px; font-weight: 600; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.1);">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div style="background: #FEF2F2; border: 1px solid #EF4444; color: #991B1B; padding: 14px 20px; border-radius: 12px; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.1);">
                <div style="font-weight: 700; margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <span>Không thể tạo đơn đặt phòng do các lỗi sau:</span>
                </div>
                <ul style="margin-left: 28px; font-size: 14px; line-height: 1.6;">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

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

                <!-- Đặt phòng ngay (Cuộn tới danh sách Room Types) -->
                <a href="#room-types-section" class="btn-action-book-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                    Đặt phòng ngay
                </a>
            </div>
        </div>

        <!-- 3. Thư viện hình ảnh Gallery (1 Lớn + 4 Nhỏ y hệt Hình 13 + Nút Xem tất cả ảnh & Lightbox - FR12) -->
        @php
            $galleryImages = $destination->images->isNotEmpty() ? $destination->images->pluck('image_path')->all() : [];
            $primaryImg = $destination->primaryImage?->image_path ?? ($galleryImages[0] ?? 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?w=1000');
            
            // Đảm bảo đủ tối thiểu 5 ảnh minh họa đẹp mắt
            $defaultSampleImages = [
                'https://images.unsplash.com/photo-1540555700478-4be289fbecef?w=600',
                'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=600',
                'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=600',
                'https://images.unsplash.com/photo-1528127269322-539801943592?w=600',
            ];
            $subImages = array_slice($galleryImages, 1, 4);
            while (count($subImages) < 4) {
                $subImages[] = $defaultSampleImages[count($subImages)];
            }

            // Danh sách toàn bộ ảnh phục vụ Lightbox
            $lightboxPhotos = !empty($galleryImages) ? $galleryImages : array_merge([$primaryImg], $subImages);
        @endphp
        <div class="gallery-container">
            <div class="gallery-main-wrap" onclick="openGalleryLightbox(0)" title="Xem ảnh lớn">
                <img src="{{ $primaryImg }}" alt="{{ $destination->name }}" class="gallery-main-img" id="mainGalleryImg">
            </div>
            <div class="gallery-sub-grid">
                @foreach($subImages as $index => $sImg)
                    <div class="gallery-sub-item" onclick="openGalleryLightbox({{ $index + 1 }})" title="Xem hình ảnh">
                        <img src="{{ $sImg }}" alt="{{ $destination->name }}" class="gallery-sub-img">
                        @if($loop->last && count($galleryImages) > 5)
                            <div class="gallery-more-overlay">
                                <span>+{{ count($galleryImages) - 4 }} ảnh khác</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Nút Mở toàn bộ Gallery Hình ảnh (FR12) -->
            <button type="button" class="btn-open-all-photos" onclick="openGalleryLightbox(0)" id="btnOpenGallery">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                <span>Xem tất cả {{ count($galleryImages) > 0 ? count($galleryImages) : 5 }} hình ảnh</span>
            </button>
        </div>

        <!-- 4. Thanh Highlight Pills (Đánh giá, Giờ mở cửa, Giá vé, Khách sạn) -->
        <div class="highlight-pills-bar">
            <div class="pill-card">
                <span>⭐</span>
                <div>
                    <strong>{{ number_format($destination->rating, 1) }} / 5.0</strong>
                    <span style="color: #64748B; font-weight: 400;">({{ $destination->approvedReviews->count() > 0 ? $destination->approvedReviews->count() : '1.240' }} đánh giá)</span>
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

                <!-- Khối Hiển Thị Nơi Lưu Trú & Danh Sách Room Type -->
                <div class="content-card-box" id="room-types-section">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                        <div>
                            <h2 class="section-headline" style="margin-bottom: 4px;">
                                🏨 Danh Sách Loại Phòng &amp; Chỗ Lưu Trú
                            </h2>
                            <p style="font-size: 14px; color: #64748B;">
                                Khám phá các hạng phòng nghỉ dưỡng tiêu chuẩn cao cấp tại {{ $destination->name }}
                            </p>
                        </div>
                        <span class="badge-hotel-pill">
                            {{ $destination->roomTypes->count() }} hạng phòng có sẵn
                        </span>
                    </div>

                    @if($destination->roomTypes->isNotEmpty())
                        @foreach($destination->roomTypes as $room)
                            <div class="room-type-card" id="roomCard-{{ $room->id }}">
                                <div class="room-header-row">
                                    <h4 class="room-title-name">{{ $room->name }}</h4>
                                    <div class="room-price-val">
                                        {{ number_format($room->price_per_night, 0, ',', '.') }}đ 
                                        <span style="font-size: 13px; color: #64748B; font-weight: 500;">/ đêm</span>
                                    </div>
                                </div>

                                <div class="room-info-tags">
                                    <span class="room-tag">
                                        👥 Sức chứa: {{ $room->capacity }} người lớn
                                    </span>
                                    <span class="room-tag room-tag-green">
                                        ✔ Còn {{ $room->quantity }} phòng trống
                                    </span>
                                    <span class="room-tag">
                                        🛏 Giường King / Twin cao cấp
                                    </span>
                                </div>

                                <p class="room-desc-text">
                                    {{ $room->description ?? 'Không gian nghỉ dưỡng thanh bình, trang bị đầy đủ điều hòa, tivi màn hình phẳng, mini-bar, ban công thoáng đãng và phòng tắm riêng hiện đại.' }}
                                </p>

                                <div class="room-actions-bar">
                                    <button type="button" class="btn-view-room-modal" onclick="openRoomTypeModal({{ $room->id }})">
                                        🔍 Xem chi tiết loại phòng & tiện ích
                                    </button>
                                    <button type="button" class="btn-select-room" 
                                            onclick="selectRoomType('{{ $room->id }}', '{{ addslashes($room->name) }}', '{{ number_format($room->price_per_night, 0, ',', '.') }}đ')">
                                        ✔ Chọn phòng này
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <!-- Nếu địa điểm chưa có Room Types cụ thể trong database, hiển thị trạng thái và phòng gợi ý -->
                        <div style="background: #F1F5F9; border-radius: 12px; padding: 24px; text-align: center;">
                            <p style="color: #475569; font-size: 14px; margin-bottom: 8px;">
                                Địa điểm này hiện đang liên kết với các đối tác khách sạn trong khu vực.
                            </p>
                            <span style="font-size: 13px; color: #0066FF; font-weight: 600;">
                                Liên hệ Hotline để nhận báo giá phòng nghỉ ưu đãi nhất.
                            </span>
                        </div>
                    @endif
                </div>

            </div>

            <!-- Cột Phải: Widget Đặt Phòng Lưu Trú & Thêm vào Trip (Sticky) -->
            <div>
                <div class="booking-widget-card">
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
                                    Đặt phòng lưu trú
                                </button>
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
                <div>
                    <span class="badge-cat-pill" style="margin-bottom: 6px;">Khám phá thêm</span>
                    <h2 class="section-headline" style="margin-bottom: 4px;">📍 Địa Điểm Khám Phá Liên Quan</h2>
                    <p style="font-size: 14px; color: #64748B;">
                        Gợi ý các điểm đến hấp dẫn tương tự tại {{ $destination->city->name }} hoặc cùng danh mục {{ $destination->category->name ?? 'du lịch' }}
                    </p>
                </div>
                @if($destination->city)
                    <a href="{{ route('cities.destinations', $destination->city->slug) }}" class="btn-view-all-related">
                        Xem tất cả tại {{ $destination->city->name }} &rsaquo;
                    </a>
                @endif
            </div>

            @if(isset($relatedDestinations) && $relatedDestinations->isNotEmpty())
                <div class="related-cards-grid">
                    @foreach($relatedDestinations as $rel)
                        <div class="related-dest-card">
                            <div class="related-card-img-wrap">
                                @if($rel->primaryImage)
                                    <img src="{{ $rel->primaryImage->image_path }}" alt="{{ $rel->name }}">
                                @elseif($rel->images->isNotEmpty())
                                    <img src="{{ $rel->images->first()->image_path }}" alt="{{ $rel->name }}">
                                @else
                                    <img src="https://images.unsplash.com/photo-1528127269322-539801943592?w=600" alt="{{ $rel->name }}">
                                @endif

                                <div class="related-badges-stack">
                                    @if($rel->category)
                                        <span class="badge-cat-tag">{{ $rel->category->name }}</span>
                                    @endif
                                    @if($rel->has_hotel)
                                        <span class="badge-hotel-tag">🏨 Có nơi lưu trú</span>
                                    @endif
                                </div>

                                <!-- Nút Lưu yêu thích nhanh -->
                                <form action="{{ route('favorites.toggle', $rel->id) }}" method="POST" class="fav-form-corner">
                                    @csrf
                                    <button type="submit" class="btn-fav-small" title="Lưu yêu thích">🤍</button>
                                </form>
                            </div>

                            <div class="related-card-body">
                                <div class="related-meta-row">
                                    <span class="related-city-name">📍 {{ $rel->city->name }}</span>
                                    <span class="related-rating">⭐ {{ number_format($rel->rating, 1) }}</span>
                                </div>

                                <h4 class="related-dest-title">
                                    <a href="{{ route('destinations.show', $rel->slug) }}">
                                        {{ $rel->name }}
                                    </a>
                                </h4>

                                <p class="related-dest-desc">
                                    {{ \Illuminate\Support\Str::limit($rel->description ?? 'Điểm đến du lịch hấp dẫn với nhiều hoạt động trải nghiệm đặc sắc.', 80) }}
                                </p>

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
                                        Chi tiết &rsaquo;
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
            <button type="button" class="lightbox-nav-btn btn-prev" onclick="changeLightboxImage(-1)" title="Ảnh trước (←)">
                &lsaquo;
            </button>
            <div class="lightbox-image-container">
                <img src="{{ $lightboxPhotos[0] }}" id="lightboxActiveImg" alt="{{ $destination->name }}">
                <p class="lightbox-caption" id="lightboxCaption">
                    {{ $destination->name }} - Khám phá vẻ đẹp ấn tượng
                </p>
            </div>
            <button type="button" class="lightbox-nav-btn btn-next" onclick="changeLightboxImage(1)" title="Ảnh tiếp theo (→)">
                &rsaquo;
            </button>
        </div>
        <div class="lightbox-thumbnails-strip" id="lightboxThumbnailsStrip">
            @foreach($lightboxPhotos as $idx => $pUrl)
                <img src="{{ $pUrl }}" alt="Thumb {{ $idx + 1 }}" 
                     class="lightbox-thumb {{ $idx === 0 ? 'active' : '' }}" 
                     onclick="setLightboxImage({{ $idx }})">
            @endforeach
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

        // Đóng modal & điều hướng Lightbox khi nhấn phím
        document.addEventListener('keydown', function (e) {
            const lightbox = document.getElementById('galleryLightboxModal');
            const isLightboxActive = lightbox && lightbox.classList.contains('active');

            if (e.key === 'Escape') {
                closeRoomTypeModal();
                closeAddToTripModal();
                closeGalleryLightbox();
            } else if (isLightboxActive) {
                if (e.key === 'ArrowLeft') {
                    changeLightboxImage(-1);
                } else if (e.key === 'ArrowRight') {
                    changeLightboxImage(1);
                }
            }
        });
    </script>
</body>
</html>

