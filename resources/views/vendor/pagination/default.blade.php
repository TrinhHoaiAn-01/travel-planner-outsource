@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Phân trang" class="custom-pagination-nav">
        <style>
            .custom-pagination-nav {
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0;
                padding: 0;
            }

            .custom-pagination-list {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                list-style: none;
                margin: 0;
                padding: 0;
            }

            .custom-pagination-list .page-item {
                display: inline-block;
                margin: 0;
                padding: 0;
            }

            .custom-pagination-list .page-link {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 34px;
                height: 34px;
                padding: 0 8px;
                font-size: 13px;
                font-weight: 600;
                color: #475569;
                background-color: #FFFFFF;
                border: 1px solid #E2E8F0;
                border-radius: 8px;
                text-decoration: none;
                transition: all 0.15s ease;
                box-sizing: border-box;
                line-height: 1;
            }

            .custom-pagination-list .page-link svg {
                width: 14px;
                height: 14px;
                display: inline-block;
                vertical-align: middle;
            }

            .custom-pagination-list .page-item:not(.disabled):not(.active) .page-link:hover {
                background-color: #F8FAFC;
                border-color: #CBD5E1;
                color: #0F172A;
            }

            .custom-pagination-list .page-item.active .page-link {
                background-color: #2563EB;
                border-color: #2563EB;
                color: #FFFFFF;
                font-weight: 700;
                box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
            }

            .custom-pagination-list .page-item.disabled .page-link {
                color: #CBD5E1;
                background-color: #F8FAFC;
                border-color: #F1F5F9;
                cursor: not-allowed;
                opacity: 0.7;
            }

            .custom-pagination-list .page-link.dots {
                border-color: transparent;
                background-color: transparent;
                cursor: default;
                color: #94A3B8;
            }
        </style>

        <ul class="custom-pagination-list">
            {{-- Nút trang trước (Previous) --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true" aria-label="Trang trước">
                    <span class="page-link" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Trang trước">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </a>
                </li>
            @endif

            {{-- Các phần tử trang (Số trang & dấu ...) --}}
            @foreach ($elements as $element)
                {{-- Dấu "..." (Ellipsis) --}}
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link dots">{{ $element }}</span></li>
                @endif

                {{-- Mảng liên kết các số trang --}}
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

            {{-- Nút trang kế tiếp (Next) --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Trang sau">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true" aria-label="Trang sau">
                    <span class="page-link" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif
