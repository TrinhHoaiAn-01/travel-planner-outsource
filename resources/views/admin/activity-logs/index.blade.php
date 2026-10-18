@extends('layouts.admin')

@section('title', 'Nhật ký hoạt động hệ thống - Travel Planner Admin')
@section('page-title', 'Nhật ký hoạt động')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Trang chủ</a>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-current">Nhật ký hoạt động (Activity Logs)</span>
@endsection

@push('styles')
<style>
    .logs-header-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .logs-stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .log-kpi-card {
        background-color: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 18px 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: var(--shadow-sm);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .log-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow);
    }

    .kpi-icon-wrap {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .icon-blue { background-color: #EFF6FF; color: #2563EB; }
    .icon-emerald { background-color: #ECFDF5; color: #059669; }
    .icon-amber { background-color: #FFFBEB; color: #D97706; }
    .icon-purple { background-color: #FAF5FF; color: #7C3AED; }

    .kpi-num {
        font-size: 22px;
        font-weight: 700;
        color: var(--text-dark);
        line-height: 1.2;
    }

    .kpi-label {
        font-size: 13px;
        color: var(--text-muted);
        font-weight: 500;
    }

    /* Filter Panel */
    .filter-panel-card {
        background-color: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 18px 20px;
        margin-bottom: 24px;
        box-shadow: var(--shadow-sm);
    }

    .filter-form-grid {
        display: grid;
        grid-template-columns: 2fr 1.5fr 1fr 1fr auto;
        gap: 12px;
        align-items: flex-end;
    }

    @media (max-width: 1024px) {
        .filter-form-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 640px) {
        .filter-form-grid {
            grid-template-columns: 1fr;
        }
    }

    .form-group-filter {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .filter-label {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-dark);
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .filter-input, .filter-select {
        height: 38px;
        padding: 8px 12px;
        border: 1px solid var(--border);
        border-radius: 8px;
        font-size: 13px;
        color: var(--text-dark);
        background-color: #FFFFFF;
        outline: none;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .filter-input:focus, .filter-select:focus {
        border-color: #2563EB;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .filter-action-btns {
        display: flex;
        gap: 8px;
    }

    .btn-submit-filter {
        height: 38px;
        padding: 0 16px;
        background-color: #2563EB;
        color: #FFFFFF;
        border: none;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: background-color 0.2s ease;
    }

    .btn-submit-filter:hover {
        background-color: #1D4ED8;
    }

    .btn-reset-filter {
        height: 38px;
        padding: 0 12px;
        background-color: #F1F5F9;
        color: #475569;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: background-color 0.2s ease;
    }

    .btn-reset-filter:hover {
        background-color: #E2E8F0;
    }

    /* Table & Badges */
    .logs-table-wrap {
        background-color: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: var(--shadow-sm);
    }

    .logs-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
        text-align: left;
    }

    .logs-table th {
        background-color: #F8FAFC;
        padding: 12px 16px;
        font-weight: 600;
        color: #475569;
        border-bottom: 1px solid var(--border);
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.05em;
    }

    .logs-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #F1F5F9;
        color: var(--text-dark);
        vertical-align: middle;
    }

    .logs-table tr:hover td {
        background-color: #F8FAFC;
    }

    /* Action Badges */
    .action-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.02em;
        white-space: nowrap;
    }

    .badge-login { background-color: #DCFCE7; color: #15803D; }
    .badge-logout { background-color: #F1F5F9; color: #475569; }
    .badge-login_failed { background-color: #FEE2E2; color: #B91C1C; }
    .badge-password_change { background-color: #FEF3C7; color: #B45309; }
    .badge-user_locked { background-color: #FEE2E2; color: #DC2626; }
    .badge-user_unlocked { background-color: #E0E7FF; color: #4338CA; }
    .badge-user_deleted { background-color: #FFE4E6; color: #E11D48; }
    .badge-user_restored { background-color: #CCFBF1; color: #0F766E; }
    .badge-user_created { background-color: #DBEAFE; color: #1D4ED8; }
    .badge-user_updated { background-color: #EDE9FE; color: #6D28D9; }
    .badge-default { background-color: #F1F5F9; color: #334155; }

    .user-actor-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .user-actor-avatar {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background-color: #E2E8F0;
        color: #475569;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 700;
        flex-shrink: 0;
    }

    .ip-address-code {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 12px;
        background-color: #F1F5F9;
        padding: 2px 6px;
        border-radius: 4px;
        color: #334155;
    }

    .btn-view-meta {
        padding: 5px 10px;
        background-color: #F8FAFC;
        border: 1px solid #CBD5E1;
        border-radius: 6px;
        color: #475569;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s ease;
    }

    .btn-view-meta:hover {
        background-color: #EFF6FF;
        border-color: #93C5FD;
        color: #1D4ED8;
    }

    /* Modal Backdrop */
    .modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background-color: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(2px);
        z-index: 999;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .modal-backdrop.active {
        display: flex;
    }

    .modal-box {
        background-color: #FFFFFF;
        border-radius: 12px;
        width: 100%;
        max-width: 550px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        animation: modalFadeIn 0.2s ease-out;
    }

    @keyframes modalFadeIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }

    .modal-header {
        padding: 16px 20px;
        background-color: #F8FAFC;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .modal-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--text-dark);
    }

    .modal-close-btn {
        background: transparent;
        border: none;
        font-size: 20px;
        color: #64748B;
        cursor: pointer;
        padding: 4px;
        line-height: 1;
    }

    .modal-body {
        padding: 20px;
    }

    .meta-json-pre {
        background-color: #0F172A;
        color: #F8FAFC;
        padding: 14px;
        border-radius: 8px;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 12.5px;
        max-height: 300px;
        overflow-y: auto;
        white-space: pre-wrap;
        word-break: break-all;
    }
</style>
@endpush

@section('content')
<div class="logs-header-bar">
    <div>
        <h1 style="font-size: 20px; font-weight: 700; color: var(--text-dark); margin-bottom: 4px;">Nhật ký hoạt động hệ thống</h1>
        <p style="font-size: 13.5px; color: var(--text-muted); margin: 0;">Giám sát thời gian thực các sự kiện xác thực, an ninh và thao tác quản trị trên hệ thống.</p>
    </div>
</div>

<!-- KPI Cards -->
<div class="logs-stat-grid">
    <div class="log-kpi-card">
        <div class="kpi-icon-wrap icon-blue">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
            </svg>
        </div>
        <div>
            <div class="kpi-num">{{ number_format($counts['total'] ?? 0) }}</div>
            <div class="kpi-label">Tổng số nhật ký</div>
        </div>
    </div>

    <div class="log-kpi-card">
        <div class="kpi-icon-wrap icon-emerald">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
        </div>
        <div>
            <div class="kpi-num">{{ number_format($counts['today'] ?? 0) }}</div>
            <div class="kpi-label">Hoạt động hôm nay</div>
        </div>
    </div>

    <div class="log-kpi-card">
        <div class="kpi-icon-wrap icon-amber">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
        </div>
        <div>
            <div class="kpi-num">{{ number_format($counts['auth_events'] ?? 0) }}</div>
            <div class="kpi-label">Sự kiện xác thực</div>
        </div>
    </div>

    <div class="log-kpi-card">
        <div class="kpi-icon-wrap icon-purple">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
            </svg>
        </div>
        <div>
            <div class="kpi-num">{{ number_format($counts['admin_actions'] ?? 0) }}</div>
            <div class="kpi-label">Thao tác quản trị</div>
        </div>
    </div>
</div>

<!-- Bộ lọc tìm kiếm -->
<div class="filter-panel-card">
    <form action="{{ route('admin.activity-logs.index') }}" method="GET" class="filter-form-grid">
        <div class="form-group-filter">
            <label class="filter-label">Tìm kiếm</label>
            <input type="text" name="search" class="filter-input" placeholder="Hành động, IP hoặc tên/email người dùng..." value="{{ request('search') }}">
        </div>

        <div class="form-group-filter">
            <label class="filter-label">Loại hành động</label>
            <select name="action" class="filter-select">
                <option value="">-- Tất cả hành động --</option>
                @foreach($availableActions as $key => $title)
                    <option value="{{ $key }}" {{ request('action') === $key ? 'selected' : '' }}>{{ $title }} ({{ $key }})</option>
                @endforeach
            </select>
        </div>

        <div class="form-group-filter">
            <label class="filter-label">Từ ngày</label>
            <input type="date" name="date_from" class="filter-input" value="{{ request('date_from') }}">
        </div>

        <div class="form-group-filter">
            <label class="filter-label">Đến ngày</label>
            <input type="date" name="date_to" class="filter-input" value="{{ request('date_to') }}">
        </div>

        <div class="filter-action-btns">
            <button type="submit" class="btn-submit-filter">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <span>Lọc</span>
            </button>
            <a href="{{ route('admin.activity-logs.index') }}" class="btn-reset-filter" title="Làm mới bộ lọc">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                    <path d="M3 3v5h5"/>
                </svg>
            </a>
        </div>
    </form>
</div>

<!-- Data Table -->
<div class="logs-table-wrap">
    <table class="logs-table">
        <thead>
            <tr>
                <th style="width: 70px;">ID</th>
                <th style="width: 160px;">Thời gian</th>
                <th>Tác nhân</th>
                <th>Hành động</th>
                <th>Đối tượng</th>
                <th style="width: 130px;">Địa chỉ IP</th>
                <th style="width: 110px; text-align: center;">Chi tiết</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                @php
                    $badgeClass = match($log->action) {
                        'login' => 'badge-login',
                        'logout' => 'badge-logout',
                        'login_failed' => 'badge-login_failed',
                        'password_change' => 'badge-password_change',
                        'user_locked' => 'badge-user_locked',
                        'user_unlocked' => 'badge-user_unlocked',
                        'user_deleted' => 'badge-user_deleted',
                        'user_restored' => 'badge-user_restored',
                        'user_created' => 'badge-user_created',
                        'user_updated' => 'badge-user_updated',
                        default => 'badge-default',
                    };
                @endphp
                <tr>
                    <td style="font-weight: 600; color: #64748B;">#{{ $log->id }}</td>
                    <td style="color: #64748B; font-size: 12.5px;">
                        {{ $log->created_at ? $log->created_at->format('d/m/Y H:i:s') : 'N/A' }}
                    </td>
                    <td>
                        @if($log->user)
                            <div class="user-actor-info">
                                <div class="user-actor-avatar">
                                    {{ strtoupper(substr($log->user->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: var(--text-dark);">{{ $log->user->name }}</div>
                                    <div style="font-size: 11.5px; color: var(--text-muted);">{{ $log->user->email }}</div>
                                </div>
                            </div>
                        @else
                            <span style="color: #94A3B8; font-style: italic;">Hệ thống / Khách</span>
                        @endif
                    </td>
                    <td>
                        <span class="action-badge {{ $badgeClass }}">
                            {{ $availableActions[$log->action] ?? $log->action }}
                        </span>
                    </td>
                    <td>
                        @if($log->subject_type)
                            <span style="font-weight: 500;">{{ class_basename($log->subject_type) }}</span>
                            @if($log->subject_id)
                                <span style="color: #64748B; font-size: 12px;">#{{ $log->subject_id }}</span>
                            @endif
                        @else
                            <span style="color: #94A3B8;">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="ip-address-code">{{ $log->ip_address ?: '127.0.0.1' }}</span>
                    </td>
                    <td style="text-align: center;">
                        <button type="button" class="btn-view-meta" onclick="viewLogMetadata({{ $log->id }})">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="3"/>
                                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                            </svg>
                            <span>Chi tiết</span>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 48px 16px; color: #64748B;">
                        <div style="font-size: 15px; font-weight: 600; margin-bottom: 4px;">Chưa có dữ liệu nhật ký phù hợp</div>
                        <div style="font-size: 13px;">Hãy thử điều chỉnh bộ lọc hoặc từ khóa tìm kiếm.</div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($logs->hasPages())
        <div style="padding: 16px 20px; border-top: 1px solid var(--border); display: flex; justify-content: flex-end;">
            {{ $logs->links() }}
        </div>
    @endif
</div>

<!-- Modal xem chi tiết Metadata -->
<div class="modal-backdrop" id="metaModal">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title" id="modalLogTitle">Chi tiết nhật ký hoạt động</div>
            <button type="button" class="modal-close-btn" onclick="closeMetaModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div style="margin-bottom: 12px; font-size: 13px; color: #475569;" id="modalLogInfo"></div>
            <label style="font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">METADATA (BẢO MẬT & ĐÃ KHỬ KHÓA NHẠY CẢM):</label>
            <pre class="meta-json-pre" id="modalLogMeta">Đang tải...</pre>
        </div>
    </div>
</div>

<script>
    function viewLogMetadata(logId) {
        const modal = document.getElementById('metaModal');
        const modalTitle = document.getElementById('modalLogTitle');
        const modalInfo = document.getElementById('modalLogInfo');
        const modalMeta = document.getElementById('modalLogMeta');

        modalTitle.textContent = `Chi tiết nhật ký #${logId}`;
        modalInfo.textContent = 'Đang tải dữ liệu từ máy chủ...';
        modalMeta.textContent = 'Loading...';
        modal.classList.add('active');

        fetch(`/admin/activity-logs/${logId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.log) {
                const log = data.log;
                modalInfo.innerHTML = `<strong>Tác nhân:</strong> ${log.user_name} (${log.user_email}) | <strong>IP:</strong> ${log.ip_address} | <strong>Thời gian:</strong> ${log.created_at}`;
                modalMeta.textContent = log.metadata ? JSON.stringify(log.metadata, null, 2) : 'Không có thông tin metadata bổ sung.';
            } else {
                modalMeta.textContent = 'Không tìm thấy dữ liệu nhật ký.';
            }
        })
        .catch(err => {
            modalMeta.textContent = 'Lỗi khi tải chi tiết nhật ký: ' + err.message;
        });
    }

    function closeMetaModal() {
        document.getElementById('metaModal').classList.remove('active');
    }

    // Đóng modal khi bấm ra ngoài
    document.getElementById('metaModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeMetaModal();
        }
    });
</script>
@endsection
