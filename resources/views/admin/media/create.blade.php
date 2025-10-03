@extends('admin.layouts.app')

@section('title', 'Add Media')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="py-3 mb-0">
            <span class="text-muted fw-light">Admin / Media /</span> Add
        </h4>
        <a href="{{ route('admin.media.index') }}" class="btn btn-outline-secondary">
            <i class="ri-arrow-left-line me-2"></i>Back to Library
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row g-4">
                <div class="col-lg-6">
                    <h5 class="mb-3">Upload Files</h5>
                    <div class="wp-media-dropzone" id="uploadArea">
                        <div class="wp-media-dropzone-content text-center p-5">
                            <i class="ri-upload-cloud-2-line"></i>
                            <h6 class="mt-2 mb-1">Drop files to upload</h6>
                            <p class="text-muted small mb-0">or</p>
                            <button type="button" class="btn btn-primary btn-sm mt-2" id="wpMediaUploadBrowse">
                                Select Files
                            </button>
                            <input type="file" id="mediaUploadInput" multiple accept="image/*" hidden>
                        </div>
                    </div>

                    <div id="uploadProgress" class="wp-media-progress" style="display: none;">
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                        </div>
                        <small class="text-muted">Uploading...</small>
                    </div>

                    <div class="wp-media-help small text-muted mt-3">
                        <p class="mb-2">Maximum upload size: 10 MB. Supported formats: JPEG, PNG, GIF, WEBP, SVG.</p>
                    </div>
                </div>

                <div class="col-lg-6">
                    <h5 class="mb-3">Add From URL</h5>
                    <form id="urlUploadForm" class="row g-2">
                        <div class="col-12">
                            <input type="url" class="form-control" id="imageUrl" placeholder="https://example.com/image.jpg">
                        </div>
                        <div class="col-12">
                            <input type="text" class="form-control" id="urlAltText" placeholder="Alt text (optional)">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="ri-download-line me-2"></i>Add Image
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Recent Media</h5>
            <a href="{{ route('admin.media.index') }}" class="btn btn-sm btn-light">Open Library</a>
        </div>
        <div class="card-body">
            <div class="wp-media-grid" id="mediaGrid">
                @foreach($media as $item)
                    @php
                        $imageUrl = \Illuminate\Support\Str::startsWith($item->file_url, ['http://', 'https://'])
                            ? $item->file_url
                            : asset(ltrim($item->file_url, '/'));
                    @endphp
                    <div class="wp-media-item" data-media-id="{{ $item->id }}">
                        <div class="wp-media-item-thumb">
                            <img src="{{ $imageUrl }}" alt="{{ $item->alt_text ?? $item->original_name }}" loading="lazy">
                        </div>
                        <div class="wp-media-item-info">
                            <div class="wp-media-item-title" title="{{ $item->original_name }}">{{ $item->original_name }}</div>
                            <div class="wp-media-item-meta d-flex justify-content-between">
                                <span>{{ $item->file_size_formatted }}</span>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteMediaItem({{ $item->id }}, this)">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div id="mediaPagination" class="mt-3">
                {{ $media->links('admin.media.partials.pagination') }}
            </div>
        </div>
    </div>
</div>

<script>
window.mediaLibraryConfig = {
    uploadUrl: '{{ route('admin.media.upload') }}',
    libraryUrl: '{{ route('admin.media.library') }}',
    uploadFromUrl: '{{ route('admin.media.upload-url') }}',
    updateUrl: '{{ route('admin.media.update', ':id') }}',
    deleteUrl: '{{ route('admin.media.destroy', ':id') }}',
    csrf: '{{ csrf_token() }}'
};
</script>
<script src="{{ asset('assets/js/media-library.js') }}"></script>
<script>
// Bind upload handlers on this standalone page too
document.addEventListener('DOMContentLoaded', function() {
    if (typeof attachGlobalListeners === 'function') {
        attachGlobalListeners();
    }
});

function deleteMediaItem(id, btn) {
    if (!confirm('Delete this image? This cannot be undone.')) return;
    const card = btn.closest('.wp-media-item');
    btn.disabled = true;
    fetch(`{{ route('admin.media.destroy', ':id') }}`.replace(':id', id), {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            if (card) card.remove();
            showMediaNotification('Media deleted successfully', 'success');
        } else {
            showMediaNotification(data.message || 'Failed to delete media', 'error');
            btn.disabled = false;
        }
    })
    .catch(() => {
        showMediaNotification('Failed to delete media', 'error');
        btn.disabled = false;
    });
}
</script>
@endsection

@push('styles')
<style>
/* Page layout */
.wp-media-dropzone {
    border: 2px dashed #c7d2fe;
    border-radius: 12px;
    background: #f5f7ff;
    padding: 40px 16px;
    text-align: center;
    transition: border-color .2s ease, background-color .2s ease;
}
.wp-media-dropzone.dragover { border-color: #4f46e5; background: rgba(79,70,229,.08); }
.wp-media-dropzone-content i { display:block; font-size:28px; color:#4338ca; }
.wp-media-progress { margin-top: 16px; }

/* Grid like WordPress */
.wp-media-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 16px;
}
.wp-media-item {
    position: relative;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    background: #fff;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    min-height: 190px;
}
.wp-media-item-thumb {
    background: #f6f7fb;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px 12px;
    min-height: 110px;
}
.wp-media-item-thumb img { max-width:100%; max-height:90px; object-fit:contain; border-radius:4px; background:#fff; }
.wp-media-item-info { padding: 10px 12px 12px; }
.wp-media-item-title { font-size:13px; font-weight:600; color:#1f2937; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.wp-media-item-meta { font-size:12px; color:#6b7280; }
.wp-media-item-check { position:absolute; top:10px; right:10px; width:26px; height:26px; border-radius:50%; background:rgba(67,56,202,.9); color:#fff; display:flex; align-items:center; justify-content:center; font-size:15px; opacity:0; transition:opacity .2s; }
.wp-media-item.selected .wp-media-item-check { opacity:1; }

/* Tidy recent media section */
.card-body > .wp-media-grid { max-height: 520px; overflow: auto; }
</style>
@endpush


