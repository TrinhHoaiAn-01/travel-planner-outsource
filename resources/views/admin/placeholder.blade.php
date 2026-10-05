@extends('layouts.admin')

@section('title', $pageTitle ?? 'Quản lý')
@section('breadcrumb', $pageTitle ?? 'Quản lý')

@section('content')
<div class="dashboard-card" style="padding: 40px; text-align: center;">
    <div style="width: 56px; height: 56px; background-color: #EFF6FF; color: #2563EB; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect width="18" height="18" x="3" y="3" rx="2"/>
            <path d="M9 17V7h4a3 3 0 0 1 0 6H9"/>
        </svg>
    </div>
    <h2 style="font-size: 20px; font-weight: 700; color: var(--text-dark); margin-bottom: 8px;">
        {{ $pageTitle ?? 'Chức năng Quản lý' }}
    </h2>
    <p style="font-size: 14px; color: var(--text-muted); max-width: 500px; margin: 0 auto 24px auto;">
        Mô-đun đang trong kế hoạch triển khai theo từng giai đoạn của Đồ án. Vui lòng gửi ảnh giao diện để hoàn thiện chức năng tương ứng!
    </p>
    <a href="{{ route('admin.dashboard') }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 20px; background-color: var(--primary); color: #FFFFFF; text-decoration: none; border-radius: 8px; font-size: 13.5px; font-weight: 600;">
        ← Quay lại Dashboard
    </a>
</div>
@endsection
