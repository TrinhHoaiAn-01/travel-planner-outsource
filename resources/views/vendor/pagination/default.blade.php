@if ($paginator->hasPages())
    <style>
        .custom-pagination-nav {
            display: inline-flex;
            align-items: center;
        }

        .custom-pagination-list {
            display: flex;
            align-items: center;
            gap: 6px;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .custom-pagination-list .page-item {
            display: inline-flex;
        }

        .custom-pagination-list .page-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            min-width: 36px;
            height: 36px;
            padding: 0 10px;
            font-size: 13.5px;
            font-weight: 500;
            color: #334155;
            background-color: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.2s ease;
            user-select: none;
            box-sizing: border-box;
        }

        .custom-pagination-list .page-link svg {
            width: 14px;
            height: 14px;
            flex-shrink: 0;
        }

        .custom-pagination-list .page-item:not(.disabled):not(.active) .page-link:hover {
            color: #2563EB;
            border-color: #BFDBFE;
            background-color: #EFF6FF;
            transform: translateY(-1px);
        }

        .custom-pagination-list .page-item.active .page-link {
            color: #FFFFFF;
            background-color: #2563EB;
            border-color: #2563EB;
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);
            cursor: default;
        }

        .custom-pagination-list .page-item.disabled .page-link {
            color: #94A3B8;
            background-color: #F8FAFC;
            border-color: #F1F5F9;
            cursor: not-allowed;
            opacity: 0.8;
        }

        .custom-pagination-list .page-link.dots {
            border: none;
            background: transparent;
            color: #94A3B8;
            cursor: default;
            padding: 0 4px;
            min-width: auto;
        }
    </style>

    <nav role="navigation" aria-label="Phân trang" class="custom-pagination-nav">
        <ul class="custom-pagination-list">
            {{-- Nút Trang Trước --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true" aria-label="Trang trước">
                    <span class="page-link" aria-hidden="true">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                        <span>Trước</span>
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Trang trước">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                        <span>Trước</span>
                    </a>
                </li>
            @endif

            {{-- Các phần tử số trang --}}
            @foreach ($elements as $element)
                {{-- Dấu ba chấm phân cách --}}
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link dots">{{ $element }}</span></li>
                @endif

                {{-- Mảng liên kết trang --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Nút Trang Tiếp Theo --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Trang tiếp theo">
                        <span>Sau</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true" aria-label="Trang tiếp theo">
                    <span class="page-link" aria-hidden="true">
                        <span>Sau</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif
