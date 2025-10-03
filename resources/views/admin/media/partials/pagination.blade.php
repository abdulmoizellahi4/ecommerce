@if ($paginator->hasPages())
    <div class="wp-media-pagination-wrapper">
        <div class="wp-media-pagination-info">
            <span class="text-muted small">
                Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
            </span>
        </div>
        
        <nav class="wp-media-pagination-nav">
            <ul class="pagination pagination-sm mb-0">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link">
                            <i class="ri-arrow-left-s-line"></i>
                            <span class="d-none d-sm-inline">Previous</span>
                        </span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                            <i class="ri-arrow-left-s-line"></i>
                            <span class="d-none d-sm-inline">Previous</span>
                        </a>
                    </li>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <li class="page-item disabled">
                            <span class="page-link">{{ $element }}</span>
                        </li>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="page-item active">
                                    <span class="page-link">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">
                            <span class="d-none d-sm-inline">Next</span>
                            <i class="ri-arrow-right-s-line"></i>
                        </a>
                    </li>
                @else
                    <li class="page-item disabled">
                        <span class="page-link">
                            <span class="d-none d-sm-inline">Next</span>
                            <i class="ri-arrow-right-s-line"></i>
                        </span>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
@endif
