@extends('admin.layouts.app')

@section('title', 'Media Library')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="py-3 mb-0">
            <span class="text-muted fw-light">Admin /</span> Media Library
        </h4>
        <a href="{{ route('admin.media.create') }}" class="btn btn-primary">
            <i class="ri-add-line me-2"></i>Add Media
        </a>
    </div>

    <!-- Search and Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="ri-search-line"></i>
                        </span>
                        <input type="text" class="form-control" id="searchInput" placeholder="Search images..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" id="dateFromInput" placeholder="From Date" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" id="dateToInput" placeholder="To Date" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-secondary w-100" onclick="clearFilters()">
                        <i class="ri-refresh-line me-2"></i>Clear
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Media Table -->
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="small text-muted"><span id="selectedCount">0</span> selected</div>
                <div class="d-flex gap-2">
                    <button type="button" id="bulkDeleteBtn" class="btn btn-sm btn-danger" disabled>
                        <i class="ri-delete-bin-line me-1"></i>Delete Selected
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle" id="mediaTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px"><input type="checkbox" id="selectAll"></th>
                            <th style="width:70px">Preview</th>
                            <th>Name</th>
                            <th style="width:120px">Size</th>
                            <th style="width:140px">Type</th>
                            <th style="width:160px">Uploaded</th>
                            <th style="width:120px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($media as $item)
                            @php
                                $imageUrl = \Illuminate\Support\Str::startsWith($item->file_url, ['http://', 'https://'])
                                    ? $item->file_url
                                    : asset(ltrim($item->file_url, '/'));
                            @endphp
                            <tr data-id="{{ $item->id }}">
                                <td><input type="checkbox" class="row-check" value="{{ $item->id }}"></td>
                                <td>
                                    <img src="{{ $imageUrl }}" alt="{{ $item->alt_text ?? $item->original_name }}" style="height:50px;width:50px;object-fit:cover;border-radius:8px" onerror="this.style.display='none'">
                                </td>
                                <td>
                                    <div class="fw-semibold text-truncate" style="max-width:320px" title="{{ $item->original_name }}">{{ $item->original_name }}</div>
                                    @if($item->alt_text)
                                        <small class="text-muted">Alt: {{ \Illuminate\Support\Str::limit($item->alt_text, 60) }}</small>
                                    @endif
                                </td>
                                <td>{{ $item->file_size_formatted }}</td>
                                <td>{{ $item->mime_type }}</td>
                                <td>{{ $item->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteOne({{ $item->id }}, this)">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-3">
                {{ $media->links('admin.media.partials.pagination') }}
            </div>
        </div>
    </div>
</div>

<!-- Include Media Library Modal -->
@include('admin.media.partials.media-library-modal')

<!-- Media Preview Modal -->
<div class="modal fade" id="mediaPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Image Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="previewImage" src="" alt="" class="img-fluid">
                <div class="mt-3">
                    <h6 id="previewTitle"></h6>
                    <p class="text-muted" id="previewInfo"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Media Edit Modal -->
<div class="modal fade" id="mediaEditModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Media</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="mediaEditForm">
                    <div class="mb-3">
                        <label for="editAltText" class="form-label">Alt Text</label>
                        <input type="text" class="form-control" id="editAltText">
                    </div>
                    <div class="mb-3">
                        <label for="editDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editDescription" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveMediaEdit()">Save Changes</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Product Media Card Styling for Media Library */
.product-media-card {
    background: #ffffff;
    border: 1px solid #eceffc;
    border-radius: 18px;
    box-shadow: 0 12px 24px rgba(15, 23, 42, 0.06);
    padding: 18px 0px;
    display: flex;
    flex-direction: column;
    align-items: center;
    min-height: 180px;
    text-align: center;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.product-media-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 18px 32px rgba(79, 70, 229, 0.15);
}

.product-media-card .product-media-thumb {
    width: 100%;
    height: 100px;
    background: linear-gradient(180deg, #f3f5ff 0%, #e9ecff 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 14px;
    overflow: hidden;
}

.product-media-card .product-media-thumb img {
    width: 100%;
    height: 100px;
    object-fit: cover;
}

.product-media-card .product-media-meta {
    width: 100%;
    border-top: 1px solid #eceffc;
    padding-top: 12px;
    margin-top: auto;
}

.product-media-card .product-media-name {
    font-weight: 600;
    font-size: 14px;
    color: #121826;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.product-media-card .product-media-size {
    font-size: 12px;
    color: #8c91a7;
    font-style: italic;
    margin-top: 2px;
}

/* Selection indicator */
.wp-media-item-check {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: rgba(67, 56, 202, 0.9);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    opacity: 0;
    transition: opacity 0.2s ease;
}

.wp-media-item.selected .wp-media-item-check {
    opacity: 1;
}
</style>
@endpush

@push('scripts')
<script>
let currentMediaId = null;
let selectedMedia = new Set();

document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const dateFromInput = document.getElementById('dateFromInput');
    const dateToInput = document.getElementById('dateToInput');
    
    function performSearch() {
        const params = new URLSearchParams();
        if (searchInput.value) params.append('search', searchInput.value);
        if (dateFromInput.value) params.append('date_from', dateFromInput.value);
        if (dateToInput.value) params.append('date_to', dateToInput.value);
        
        window.location.href = '{{ route("admin.media.index") }}?' + params.toString();
    }
    
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            performSearch();
        }
    });
    
    dateFromInput.addEventListener('change', performSearch);
    dateToInput.addEventListener('change', performSearch);
});

function clearFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('dateFromInput').value = '';
    document.getElementById('dateToInput').value = '';
    window.location.href = '{{ route("admin.media.index") }}';
}

function previewMedia(mediaId) {
    // This would be implemented to show image preview
    console.log('Preview media:', mediaId);
}

function editMedia(mediaId) {
    currentMediaId = mediaId;
    // This would be implemented to edit media details
    console.log('Edit media:', mediaId);
}

function saveMediaEdit() {
    // This would be implemented to save media edits
    console.log('Save media edit:', currentMediaId);
}

// Bulk table interactions
document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('mediaTable');
    if (!table) return;

    const selectAll = document.getElementById('selectAll');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const selectedCountEl = document.getElementById('selectedCount');

    function updateBulkState() {
        const checked = table.querySelectorAll('.row-check:checked');
        const count = checked.length;
        selectedCountEl.textContent = count;
        bulkDeleteBtn.disabled = count === 0;
    }

    table.addEventListener('change', (e) => {
        if (e.target.classList.contains('row-check')) updateBulkState();
    });

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            table.querySelectorAll('.row-check').forEach(cb => cb.checked = selectAll.checked);
            updateBulkState();
        });
    }

    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function() {
            const ids = Array.from(table.querySelectorAll('.row-check:checked')).map(cb => cb.value);
            if (!ids.length) return;
            if (!confirm(`Delete ${ids.length} selected item(s)?`)) return;

            bulkDeleteBtn.disabled = true;

            // Perform sequential deletes to reuse existing endpoint
            const promises = ids.map(id => fetch(`{{ route('admin.media.destroy', ':id') }}`.replace(':id', id), {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
            }).then(r => r.json()).then(d => ({ id, ok: d.success })));

            Promise.all(promises).then(results => {
                let removed = 0;
                results.forEach(res => {
                    if (res.ok) {
                        const row = table.querySelector(`tr[data-id="${res.id}"]`);
                        if (row) row.remove();
                        removed++;
                    }
                });
                showMediaNotification(`${removed} item(s) deleted`, 'success');
                updateBulkState();
                bulkDeleteBtn.disabled = false;
            }).catch(() => {
                showMediaNotification('Failed to delete selected items', 'error');
                bulkDeleteBtn.disabled = false;
            });
        });
    }
});

function deleteOne(id, btn) {
    if (!confirm('Delete this image?')) return;
    btn.disabled = true;
    const row = btn.closest('tr');
    fetch(`{{ route('admin.media.destroy', ':id') }}`.replace(':id', id), {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            if (row) row.remove();
            showMediaNotification('Media deleted successfully', 'success');
        } else {
            showMediaNotification(data.message || 'Failed to delete media', 'error');
            btn.disabled = false;
        }
    })
    .catch(() => { showMediaNotification('Failed to delete media', 'error'); btn.disabled = false; });
}
</script>
@endpush
