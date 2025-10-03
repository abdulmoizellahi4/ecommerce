<style>
    svg{
        width: 20px;
    }
    
    /* Drag and Drop Overlay */
    .wp-media-modal {
        position: relative;
        transition: all 0.3s ease;
    }
    
    .wp-media-modal.drag-over {
        background: rgba(79, 70, 229, 0.05);
    }
    
    .wp-media-drag-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(79, 70, 229, 0.1);
        border: 3px dashed #4f46e5;
        border-radius: 12px;
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        backdrop-filter: blur(2px);
    }
    
    .wp-media-drag-overlay.active {
        display: flex;
    }
    
    .wp-media-drag-content {
        text-align: center;
        color: #4f46e5;
        font-weight: 600;
    }
    
    .wp-media-drag-content i {
        font-size: 3rem;
        margin-bottom: 1rem;
        display: block;
    }
    
    .wp-media-drag-content h5 {
        margin-bottom: 0.5rem;
        color: #4f46e5;
    }
    
    .wp-media-drag-content p {
        color: #6b7280;
        margin-bottom: 0;
    }
</style>
<!-- Media Library Modal -->
<div class="modal fade" id="mediaLibraryModal" tabindex="-1" aria-labelledby="mediaLibraryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-semibold" id="mediaLibraryModalLabel">
                    <i class="ri-folder-image-line me-2"></i>Media Library
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-0" id="mediaLibraryBody">
                <div class="wp-media-modal" data-active-tab="library">
                    <!-- Drag and Drop Overlay -->
                    <div class="wp-media-drag-overlay" id="dragOverlay">
                        <div class="wp-media-drag-content">
                            <i class="ri-upload-cloud-2-line"></i>
                            <h5>Drop images here to upload</h5>
                            <p>Release to add images to your media library</p>
                        </div>
                    </div>
                    <div class="wp-media-header">
                        <div class="wp-media-tabs">
                            <button type="button" class="wp-media-tab active" id="wpMediaLibraryTab" data-tab="library">Media Library</button>
                            <button type="button" class="wp-media-tab" id="wpMediaUploadTab" data-tab="upload">Upload Files</button>
                        </div>
                        <div class="wp-media-toolbar">
                            <span class="wp-media-selection">Selected: <strong id="selectedCount">0</strong></span>
                            <div class="wp-media-search">
                                <i class="ri-search-line"></i>
                                <input type="search" id="mediaSearch" placeholder="Search media items">
                            </div>
                        </div>
                    </div>

                    <div class="wp-media-body">
                        <div class="wp-media-main">
                            <div class="wp-media-section" id="wpMediaUploadSection" hidden>
                                <div class="wp-media-dropzone" id="uploadArea">
                                    <div class="wp-media-dropzone-content">
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

                                <div class="wp-media-help small text-muted">
                                    <p class="mb-2">Maximum upload size: 10 MB. Supported formats: JPEG, PNG, GIF, WEBP, SVG.</p>
                                </div>

                                <div class="wp-media-url-upload">
                                    <h6 class="fw-semibold mb-3">
                                        <i class="ri-link me-2"></i>Add from URL
                                    </h6>
                                    <form id="urlUploadForm" class="row g-2">
                                        <div class="col-12">
                                            <input type="url" class="form-control" id="imageUrl" placeholder="https://example.com/image.jpg">
                                        </div>
                                        <div class="col-12">
                                            <input type="text" class="form-control" id="urlAltText" placeholder="Alt text (optional)">
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-outline-primary w-100">
                                                <i class="ri-download-line me-2"></i>Add Image
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="wp-media-section" id="wpMediaLibrarySection">
                                <div class="wp-media-filters">
                                    <div class="wp-media-date-filter">
                                        <label class="small text-muted mb-1">From</label>
                                        <input type="date" id="dateFrom" class="form-control form-control-sm">
                                    </div>
                                    <div class="wp-media-date-filter">
                                        <label class="small text-muted mb-1">To</label>
                                        <input type="date" id="dateTo" class="form-control form-control-sm">
                                    </div>
                                </div>

                                <div class="wp-media-grid-container" id="mediaGridContainer">
                                    <div class="wp-media-grid" id="mediaGrid">
                                        <!-- Media items injected here -->
                                    </div>
                                    <div class="wp-media-pagination" id="mediaPagination"></div>
                                </div>

                                <div id="mediaLoading" class="wp-media-loading" style="display: none;">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <aside class="wp-media-sidebar">
                            <div class="wp-media-preview-empty" id="wpMediaPreviewEmpty">
                                <i class="ri-image-line"></i>
                                <p>Select an image to see details.</p>
                            </div>

                            <div class="wp-media-preview d-none" id="wpMediaPreviewPanel">
                                <div class="wp-media-preview-thumb">
                                    <img id="wpMediaPreviewImage" src="" alt="Selected media preview">
                                </div>
                                <div class="wp-media-preview-meta">
                                    <div class="wp-media-preview-title" id="wpMediaPreviewTitle"></div>
                                    <div class="wp-media-preview-details" id="wpMediaPreviewDetails"></div>
                                </div>

                                <div class="wp-media-preview-fields">
                                    <label class="form-label form-label-sm">Alt Text</label>
                                    <input type="text" class="form-control form-control-sm" id="wpMediaAltInput">

                                    <label class="form-label form-label-sm mt-3">Description</label>
                                    <textarea class="form-control form-control-sm" rows="3" id="wpMediaDescriptionInput"></textarea>
                                </div>

                                <div class="wp-media-preview-actions">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="wpMediaDeselectBtn">Deselect</button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="wpMediaDeleteBtn">Delete</button>
                                    <button type="button" class="btn btn-sm btn-primary" id="wpMediaUpdateBtn">Save</button>
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="selectMediaBtn" disabled>
                    <i class="ri-check-line me-1"></i>Insert Media
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* General icon sizing for media library */
.wp-media-modal i {
    max-width: 32px;
    max-height: 32px;
}

.wp-media-modal .ri-check-line {
    font-size: 16px;
}

.wp-media-modal .ri-image-line {
    font-size: 24px;
}

.wp-media-modal .ri-upload-cloud-2-line {
    font-size: 28px;
}

/* Product Media Card Styling for Media Library */
.wp-media-modal .product-media-card {
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
    position: relative;
}

.wp-media-modal .product-media-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 18px 32px rgba(79, 70, 229, 0.15);
}

.wp-media-modal .product-media-card .product-media-thumb {
    width: 100%;
    height: 100px;
    background: linear-gradient(180deg, #f3f5ff 0%, #e9ecff 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 14px;
    overflow: hidden;
}

.wp-media-modal .product-media-card .product-media-thumb img {
    width: 100%;
    height: 100px;
    object-fit: cover;
}

.wp-media-modal .product-media-card .product-media-meta {
    width: 100%;
    border-top: 1px solid #eceffc;
    padding-top: 12px;
    margin-top: auto;
}

.wp-media-modal .product-media-card .product-media-name {
    font-weight: 600;
    font-size: 14px;
    color: #121826;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.wp-media-modal .product-media-card .product-media-size {
    font-size: 12px;
    color: #8c91a7;
    font-style: italic;
    margin-top: 2px;
}

/* Selection indicator for media cards */
.wp-media-modal .product-media-card .wp-media-item-check {
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

.wp-media-modal .product-media-card.selected .wp-media-item-check {
    opacity: 1;
}

/* Professional Pagination Styling */
.wp-media-pagination-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 0;
    border-top: 1px solid #e5e7eb;
    margin-top: 16px;
}

.wp-media-pagination-info {
    color: #6b7280;
    font-size: 14px;
}

.wp-media-pagination-nav .pagination {
    margin: 0;
    gap: 4px;
}

.wp-media-pagination-nav .page-link {
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 14px;
    font-weight: 500;
    color: #374151;
    background: #ffffff;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 4px;
    min-width: 40px;
    justify-content: center;
}

.wp-media-pagination-nav .page-link:hover {
    background: #f3f4f6;
    border-color: #9ca3af;
    color: #111827;
    transform: translateY(-1px);
}

.wp-media-pagination-nav .page-item.active .page-link {
    background: #4f46e5;
    border-color: #4f46e5;
    color: #ffffff;
    box-shadow: 0 2px 4px rgba(79, 70, 229, 0.2);
}

.wp-media-pagination-nav .page-item.disabled .page-link {
    background: #f9fafb;
    border-color: #e5e7eb;
    color: #9ca3af;
    cursor: not-allowed;
}

.wp-media-pagination-nav .page-item.disabled .page-link:hover {
    background: #f9fafb;
    border-color: #e5e7eb;
    color: #9ca3af;
    transform: none;
}

.wp-media-pagination-nav .page-link i {
    font-size: 16px;
}

/* Responsive pagination */
@media (max-width: 576px) {
    .wp-media-pagination-wrapper {
        flex-direction: column;
        gap: 12px;
        align-items: center;
    }
    
    .wp-media-pagination-nav .page-link {
        padding: 6px 10px;
        font-size: 13px;
    }
}

.wp-media-modal {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.wp-media-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 24px 16px;
    border-bottom: 1px solid #eef2ff;
}

.wp-media-tabs {
    display: flex;
    gap: 8px;
}

.wp-media-tab {
    border: none;
    background: transparent;
    border-radius: 6px;
    padding: 8px 16px;
    font-weight: 600;
    color: #4b5563;
    transition: background-color 0.2s ease;
}

.wp-media-tab.active {
    background: #eef2ff;
    color: #4338ca;
}

.wp-media-toolbar {
    display: flex;
    align-items: center;
    gap: 16px;
}

.wp-media-selection {
    font-size: 13px;
    color: #6b7280;
}

.wp-media-search {
    position: relative;
    display: flex;
    align-items: center;
    background: #f4f5f7;
    border-radius: 6px;
    padding: 4px 10px;
}

.wp-media-search i {
    font-size: 16px;
    color: #9ca3af;
}

.wp-media-search input {
    border: none;
    background: transparent;
    margin-left: 8px;
    font-size: 14px;
    min-width: 220px;
}

.wp-media-search input:focus {
    outline: none;
}

.wp-media-body {
    display: flex;
    min-height: 520px;
}

.wp-media-main {
    flex: 1;
    padding: 24px;
    border-right: 1px solid #eef2ff;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.wp-media-section {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.wp-media-dropzone {
    border: 2px dashed #c7d2fe;
    border-radius: 12px;
    background: #f5f7ff;
    padding: 48px 16px;
    text-align: center;
    transition: border-color 0.3s ease, background-color 0.3s ease;
}

.wp-media-dropzone.dragover {
    border-color: #4f46e5;
    background: rgba(79, 70, 229, 0.08);
}

.wp-media-dropzone-content i {
    font-size: 28px;
    color: #4338ca;
}

.wp-media-progress {
    margin-top: 20px;
}

.wp-media-help {
    margin-top: 16px;
}

.wp-media-url-upload {
    margin-top: 24px;
    border-top: 1px solid #eef2ff;
    padding-top: 16px;
}

.wp-media-filters {
    display: flex;
    gap: 16px;
    margin-bottom: 16px;
}

.wp-media-grid-container {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.wp-media-grid {
    flex: 1;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 16px;
    overflow-y: auto;
    padding-right: 8px;
}

.wp-media-item {
    position: relative;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    background: #ffffff;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    height: 190px;
}

.wp-media-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 22px rgba(15, 23, 42, 0.12);
}

.wp-media-item.selected {
    border-color: #4338ca;
    box-shadow: 0 14px 28px rgba(67, 56, 202, 0.25);
}

.wp-media-item-thumb {
    background: #f6f7fb;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 18px 12px;
    min-height: 110px;
    position: relative;
}

.wp-media-item-thumb img {
    max-width: 100%;
    max-height: 90px;
    object-fit: contain;
    border-radius: 4px;
    background: #ffffff;
}

.wp-media-item-thumb img[src=""],
.wp-media-item-thumb img:not([src]) {
    display: none;
}

.wp-media-item-thumb::after {
    content: "📷";
    font-size: 18px;
    color: #9ca3af;
    position: absolute;
    display: none;
}

.wp-media-item-thumb.has-error::after {
    content: "📷";
    font-size: 18px;
    color: #9ca3af;
    position: absolute;
    display: block;
}

.wp-media-item-info {
    padding: 12px 14px 14px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    flex: 1;
}

.wp-media-item-title {
    font-size: 13px;
    font-weight: 600;
    color: #1f2937;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.wp-media-item-meta {
    font-size: 12px;
    color: #6b7280;
}

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

.wp-media-pagination {
    margin-top: 16px;
    padding-top: 12px;
    border-top: 1px solid #eef2ff;
}

.wp-media-pagination nav ul {
    justify-content: flex-start;
}

.wp-media-loading {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 120px;
}

.wp-media-sidebar {
    width: 280px;
    padding: 24px;
    background: #f9fafb;
}

.wp-media-preview-empty {
    text-align: center;
    color: #9ca3af;
    border: 2px dashed #d1d5db;
    border-radius: 12px;
    padding: 32px 16px;
}

.wp-media-preview-empty i {
    font-size: 24px;
    display: block;
    margin-bottom: 12px;
}

.wp-media-preview {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.wp-media-preview-thumb {
    border-radius: 12px;
    background: #ffffff;
    box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.05);
    padding: 16px;
    display: flex;
    justify-content: center;
    align-items: center;
}

.wp-media-preview-thumb img {
    max-width: 100%;
    max-height: 220px;
    object-fit: contain;
}

.wp-media-preview-title {
    font-weight: 600;
    color: #111827;
    font-size: 14px;
}

.wp-media-preview-details {
    font-size: 12px;
    color: #6b7280;
    line-height: 1.6;
}

.wp-media-preview-fields .form-label-sm {
    font-size: 12px;
    font-weight: 600;
    color: #4b5563;
}

.wp-media-preview-actions {
    display: flex;
    justify-content: space-between;
    gap: 8px;
}

.wp-media-preview-actions .btn {
    flex: 1;
}

[data-active-tab="upload"] .wp-media-search,
[data-active-tab="upload"] .wp-media-filters {
    display: none;
}

@media (max-width: 991px) {
    .wp-media-body {
        flex-direction: column;
    }

    .wp-media-sidebar {
        width: 100%;
        border-top: 1px solid #eef2ff;
    }

    .wp-media-main {
        border-right: none;
    }
}
</style>

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
