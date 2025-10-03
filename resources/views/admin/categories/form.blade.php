@php
    $isEdit = isset($category);
    $title = $isEdit ? 'Edit Category' : 'Add New Category';
    $heading = $isEdit ? 'Edit' : 'Add New';
    $cardTitle = $isEdit ? 'Edit Category: ' . $category->name : 'Category Information';
    $formAction = $isEdit ? route('admin.categories.update', $category->id) : route('admin.categories.store');
    $method = $isEdit ? 'PUT' : 'POST';
    $buttonText = $isEdit ? 'Update Category' : 'Create Category';
    $buttonIcon = $isEdit ? 'ri-save-line' : 'ri-save-line';
@endphp

@extends('admin.layouts.app')

@section('title', $title)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Admin / Categories /</span> {{ $heading }}
    </h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">{{ $cardTitle }}</h5>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Back to Categories</a>
                </div>
                <div class="card-body">
                    <form action="{{ $formAction }}" method="POST">
                        @csrf
                        @if($isEdit)
                            @method('PUT')
                        @endif
                        
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" 
                                           value="{{ old('name', $isEdit ? $category->name : '') }}" 
                                           placeholder="Category Name" required>
                                    <label for="name">Category Name *</label>
                                </div>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3 col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control @error('slug') is-invalid @enderror" 
                                           id="slug" name="slug" 
                                           value="{{ old('slug', $isEdit ? $category->slug : '') }}"
                                           placeholder="Slug">
                                    <label for="slug">Slug</label>
                                </div>
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if(!$isEdit)
                                    <small class="text-muted">Leave empty to auto-generate from name</small>
                                @endif
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-floating form-floating-outline">
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="4" 
                                          placeholder="Description">{{ old('description', $isEdit ? $category->description : '') }}</textarea>
                                <label for="description">Description</label>
                            </div>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Category Image Upload -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">Category Image</h6>
                                    <button type="button" class="btn btn-link p-0 fw-semibold text-primary" onclick="openMediaLibrary(event)">
                                        Add Media
                                    </button>
                                </div>
                                
                                @php
                                    $initialMedia = [];
                                    $oldMediaIds = old('media_ids', []);
                                    if ($isEdit && $category->image) {
                                        $initialMedia = [[
                                            'id' => 'existing',
                                            'file_url' => asset('storage/' . $category->image),
                                            'original_name' => basename($category->image),
                                            'file_size_formatted' => 'Existing'
                                        ]];
                                    } elseif (is_array($oldMediaIds) && count($oldMediaIds) > 0) {
                                        $initialMedia = \App\Models\Media::whereIn('id', $oldMediaIds)->get()->map(function ($media) {
                                            $url = \Illuminate\Support\Str::startsWith($media->file_url, ['http://', 'https://'])
                                                ? $media->file_url
                                                : asset(ltrim($media->file_url, '/'));
                                            return [
                                                'id' => $media->id,
                                                'file_url' => $url,
                                                'original_name' => $media->original_name,
                                                'file_size_formatted' => $media->file_size_formatted
                                            ];
                                        })->values();
                                    }
                                @endphp

                                <div class="category-media-picker" data-initial-media='@json($initialMedia)'>
                                    <div class="category-media-dropzone" id="categoryMediaDropzone" role="button" tabindex="0" aria-label="Select category image">
                                        <div class="category-media-empty" id="categoryMediaEmpty">
                                            <i class="ri-upload-cloud-2-line"></i>
                                            <p class="mb-1">Drop image here or click to open media library</p>
                                            <small class="text-muted">Select a category image</small>
                                        </div>

                                        <div class="row g-3 category-media-gallery" id="categoryMediaGallery"></div>
                                    </div>

                                    <input type="hidden" name="image" id="categoryImageInput" value="{{ old('image', $isEdit ? $category->image : '') }}">
                                    <div id="selectedMediaInputs">
                                        @foreach(old('media_ids', []) as $id)
                                            <input type="hidden" name="media_ids[]" value="{{ $id }}">
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control @error('meta_title') is-invalid @enderror" 
                                           id="meta_title" name="meta_title" 
                                           value="{{ old('meta_title', $isEdit ? $category->meta_title : '') }}"
                                           maxlength="60" placeholder="Meta Title">
                                    <label for="meta_title">Meta Title</label>
                                </div>
                                @error('meta_title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Recommended: 50-60 characters</small>
                            </div>
                            <div class="mb-3 col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control @error('meta_keywords') is-invalid @enderror" 
                                           id="meta_keywords" name="meta_keywords" 
                                           value="{{ old('meta_keywords', $isEdit ? $category->meta_keywords : '') }}"
                                           placeholder="Meta Keywords">
                                    <label for="meta_keywords">Meta Keywords</label>
                                </div>
                                @error('meta_keywords')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Separate keywords with commas</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-floating form-floating-outline">
                                <textarea class="form-control @error('meta_description') is-invalid @enderror" 
                                          id="meta_description" name="meta_description" rows="3"
                                          maxlength="160" placeholder="Meta Description">{{ old('meta_description', $isEdit ? $category->meta_description : '') }}</textarea>
                                <label for="meta_description">Meta Description</label>
                            </div>
                            @error('meta_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Recommended: 150-160 characters</small>
                        </div>

                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control @error('og_title') is-invalid @enderror" 
                                           id="og_title" name="og_title" 
                                           value="{{ old('og_title', $isEdit ? $category->og_title : '') }}"
                                           placeholder="Open Graph Title">
                                    <label for="og_title">Open Graph Title</label>
                                </div>
                                @error('og_title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3 col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <textarea class="form-control @error('og_description') is-invalid @enderror" 
                                              id="og_description" name="og_description" rows="2"
                                              placeholder="Open Graph Description">{{ old('og_description', $isEdit ? $category->og_description : '') }}</textarea>
                                    <label for="og_description">Open Graph Description</label>
                                </div>
                                @error('og_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="url" class="form-control @error('og_image') is-invalid @enderror" 
                                           id="og_image" name="og_image" 
                                           value="{{ old('og_image', $isEdit ? $category->og_image : '') }}"
                                           placeholder="Open Graph Image URL">
                                    <label for="og_image">Open Graph Image URL</label>
                                </div>
                                @error('og_image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Recommended: 1200x630 pixels</small>
                            </div>
                            <div class="mb-3 col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="url" class="form-control @error('canonical_url') is-invalid @enderror" 
                                           id="canonical_url" name="canonical_url" 
                                           value="{{ old('canonical_url', $isEdit ? $category->canonical_url : '') }}"
                                           placeholder="Canonical URL">
                                    <label for="canonical_url">Canonical URL</label>
                                </div>
                                @error('canonical_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Prevents duplicate content issues</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <select class="form-select @error('robots') is-invalid @enderror" 
                                            id="robots" name="robots">
                                        <option value="index,follow" {{ old('robots', $isEdit ? $category->robots : 'index,follow') == 'index,follow' ? 'selected' : '' }}>Index, Follow</option>
                                        <option value="index,nofollow" {{ old('robots', $isEdit ? $category->robots : 'index,follow') == 'index,nofollow' ? 'selected' : '' }}>Index, No Follow</option>
                                        <option value="noindex,follow" {{ old('robots', $isEdit ? $category->robots : 'index,follow') == 'noindex,follow' ? 'selected' : '' }}>No Index, Follow</option>
                                        <option value="noindex,nofollow" {{ old('robots', $isEdit ? $category->robots : 'index,follow') == 'noindex,nofollow' ? 'selected' : '' }}>No Index, No Follow</option>
                                    </select>
                                    <label for="robots">Robots Meta</label>
                                </div>
                                @error('robots')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3 col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <textarea class="form-control @error('schema_markup') is-invalid @enderror" 
                                              id="schema_markup" name="schema_markup" rows="3"
                                              placeholder="Schema Markup">{{ old('schema_markup', $isEdit ? $category->schema_markup : '') }}</textarea>
                                    <label for="schema_markup">Schema Markup</label>
                                </div>
                                @error('schema_markup')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">JSON-LD format for rich snippets</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <select class="form-control select2 @error('parent_id') is-invalid @enderror" 
                                            id="parent_id" name="parent_id">
                                        <option value="">-- None --</option>
                                        @if(isset($categories) && $categories)
                                            @foreach($categories as $cat)
                                                @if($isEdit && $cat->id == $category->id)
                                                    @continue
                                                @endif
                                                <option value="{{ $cat->id }}" {{ old('parent_id', $isEdit ? $category->parent_id : '') == $cat->id ? 'selected' : '' }}>
                                                    {{ $cat->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <label for="parent_id">Parent Category</label>
                                </div>
                                @error('parent_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3 col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                           id="sort_order" name="sort_order" 
                                           value="{{ old('sort_order', $isEdit ? $category->sort_order : 0) }}" 
                                           min="0" placeholder="Sort Order">
                                    <label for="sort_order">Sort Order</label>
                                </div>
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                       value="1" {{ old('is_active', $isEdit ? $category->is_active : true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active
                                </label>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="{{ $buttonIcon }} me-1"></i> {{ $buttonText }}
                            </button>
                            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                                <i class="ri-arrow-left-line me-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    const metaTitleInput = document.getElementById('meta_title');
    const metaDescriptionInput = document.getElementById('meta_description');
    const ogTitleInput = document.getElementById('og_title');
    const ogDescriptionInput = document.getElementById('og_description');
    
    // Auto-generate slug from name (only in create mode)
    nameInput.addEventListener('input', function() {
        @if(!$isEdit)
        if (!slugInput.value || slugInput.value === '') {
            const slug = this.value
                .toLowerCase()
                .replace(/[^a-z0-9 -]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim('-');
            slugInput.value = slug;
        }
        @endif
        
        // Auto-generate meta title if empty
        if (!metaTitleInput.value) {
            metaTitleInput.value = this.value;
        }
        
        // Auto-generate OG title if empty
        if (!ogTitleInput.value) {
            ogTitleInput.value = this.value;
        }
    });
    
    // Character counter for meta description
    metaDescriptionInput.addEventListener('input', function() {
        const maxLength = 160;
        const currentLength = this.value.length;
        const remaining = maxLength - currentLength;
        
        // Update character count display
        let counter = this.parentNode.querySelector('.char-counter');
        if (!counter) {
            counter = document.createElement('small');
            counter.className = 'char-counter text-muted';
            this.parentNode.appendChild(counter);
        }
        
        counter.textContent = `${currentLength}/${maxLength} characters`;
        
        if (remaining < 0) {
            counter.className = 'char-counter text-danger';
        } else if (remaining < 20) {
            counter.className = 'char-counter text-warning';
        } else {
            counter.className = 'char-counter text-muted';
        }
    });
    
    // Character counter for meta title
    metaTitleInput.addEventListener('input', function() {
        const maxLength = 60;
        const currentLength = this.value.length;
        const remaining = maxLength - currentLength;
        
        let counter = this.parentNode.querySelector('.char-counter');
        if (!counter) {
            counter = document.createElement('small');
            counter.className = 'char-counter text-muted';
            this.parentNode.appendChild(counter);
        }
        
        counter.textContent = `${currentLength}/${maxLength} characters`;
        
        if (remaining < 0) {
            counter.className = 'char-counter text-danger';
        } else if (remaining < 10) {
            counter.className = 'char-counter text-warning';
        } else {
            counter.className = 'char-counter text-muted';
        }
    });
    
    // Auto-generate OG description from meta description
    metaDescriptionInput.addEventListener('input', function() {
        if (!ogDescriptionInput.value && this.value) {
            ogDescriptionInput.value = this.value;
        }
    });
    
    // Initialize character counters
    metaDescriptionInput.dispatchEvent(new Event('input'));
    metaTitleInput.dispatchEvent(new Event('input'));
});
</script>

<!-- Include Media Library Modal -->
@include('admin.media.partials.media-library-modal')

@push('styles')
<style>
.category-media-picker {
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    padding: 20px;
    background: #fafbff;
}

.category-media-dropzone {
    border: 2px dashed #d5d9ff;
    border-radius: 12px;
    background: #ffffff;
    padding: 32px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
    position: relative;
}

.category-media-dropzone:hover {
    border-color: #6366f1;
    background: #f3f4ff;
}

.category-media-empty i {
    font-size: 32px;
    color: #6366f1;
    display: block;
    margin-bottom: 10px;
}

.category-media-empty p {
    color: #6b7280;
    font-size: 14px;
}

.category-media-gallery {
    width: 100%;
}

.category-media-card {
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

.category-media-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 18px 32px rgba(79, 70, 229, 0.15);
}

.category-media-card .category-media-thumb {
    width: 100%;
    height: 100px;
    background: linear-gradient(180deg, #f3f5ff 0%, #e9ecff 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 14px;
    overflow: hidden;
}

.category-media-card .category-media-thumb img {
    width: 100%;
    height: 100px;
    object-fit: cover;
}

.category-media-card .category-media-meta {
    width: 100%;
    border-top: 1px solid #eceffc;
    padding-top: 12px;
    margin-top: auto;
}

.category-media-card .category-media-name {
    font-weight: 600;
    font-size: 14px;
    color: #121826;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.category-media-card .category-media-size {
    font-size: 12px;
    color: #8c91a7;
    font-style: italic;
    margin-top: 2px;
}

.category-media-card .category-media-actions {
    margin-top: 12px;
}

.category-media-card .category-media-remove {
    font-size: 13px;
    color: #ef4444;
}

.category-media-dropzone.dragover {
    border-color: #4338ca;
    background: rgba(99, 102, 241, 0.08);
}

/* Select2 Custom Styles for Parent Category */
.select2-container {
    width: 100% !important;
}

.select2-container--default .select2-selection--single {
    height: 58px;
    border: 1px solid #e7eaf3;
    border-radius: 0.375rem;
    padding: 0.5rem 0.75rem;
    background-color: transparent;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 46px;
    padding-left: 0;
    padding-right: 20px;
    color: #697a8d;
}

.select2-container--default .select2-selection--single .select2-selection__placeholder {
    color: #697a8d;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 56px;
    right: 10px;
}

.select2-container--default.select2-container--focus .select2-selection--single {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.25);
}

.select2-dropdown {
    border: 1px solid #e7eaf3;
    border-radius: 0.375rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.select2-container--default .select2-search--dropdown .select2-search__field {
    border: 1px solid #e7eaf3;
    border-radius: 0.375rem;
    padding: 0.5rem 0.75rem;
}

.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: var(--bs-primary);
    color: white;
}

/* Adjust floating label for Select2 */
.form-floating-outline .select2-container--default .select2-selection--single {
    height: 58px;
}

.form-floating-outline .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 46px;
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/js/media-library.js') }}"></script>
<script>
// Initialize Select2 for parent category dropdown
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2 on parent category dropdown
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('#parent_id').select2({
            placeholder: 'Search for a parent category...',
            allowClear: true,
            width: '100%',
            dropdownParent: $('.card-body')
        });
    }
    
    initializeCategoryMediaPicker();
});

// Media Library Configuration
window.mediaLibraryConfig = {
    uploadUrl: '{{ route("admin.media.upload") }}',
    libraryUrl: '{{ route("admin.media.library") }}',
    updateUrl: '{{ route("admin.media.update", ":id") }}',
    deleteUrl: '{{ route("admin.media.destroy", ":id") }}',
    uploadUrlUrl: '{{ route("admin.media.upload-url") }}',
    csrf: '{{ csrf_token() }}'
};

let categoryMedia = {
    dropzone: null,
    emptyState: null,
    gallery: null,
    imageInput: null,
    hiddenInputsWrapper: null,
    selectedItems: [],
    eventListenersAdded: false
};

document.addEventListener('DOMContentLoaded', function() {
    initializeCategoryMediaPicker();
});

function initializeCategoryMediaPicker() {
    categoryMedia.dropzone = document.getElementById('categoryMediaDropzone');
    categoryMedia.emptyState = document.getElementById('categoryMediaEmpty');
    categoryMedia.gallery = document.getElementById('categoryMediaGallery');
    categoryMedia.imageInput = document.getElementById('categoryImageInput');
    categoryMedia.hiddenInputsWrapper = document.getElementById('selectedMediaInputs');

    const picker = document.querySelector('.category-media-picker');
    if (picker && picker.dataset.initialMedia) {
        try {
            const initialMedia = JSON.parse(picker.dataset.initialMedia);
            initialMedia.forEach(item => addMediaToGallery(item));
        } catch (error) {
            console.warn('Failed to parse initial media', error);
        }
    }

    if (!categoryMedia.dropzone) return;

    // Only add event listeners once to prevent duplicates
    if (!categoryMedia.eventListenersAdded) {
        categoryMedia.dropzone.addEventListener('click', openMediaLibrary);

        categoryMedia.dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            categoryMedia.dropzone.classList.add('dragover');
        });

        categoryMedia.dropzone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            categoryMedia.dropzone.classList.remove('dragover');
        });

        categoryMedia.dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            categoryMedia.dropzone.classList.remove('dragover');
            
            const files = Array.from(e.dataTransfer.files).filter(file => 
                file.type.startsWith('image/')
            );
            
            if (files.length > 0) {
                // Show image immediately instead of uploading to media library
                showDirectImagePreview(files[0]);
            } else {
                openMediaLibrary();
            }
        });
        
        categoryMedia.eventListenersAdded = true;
    }
}

function showDirectImagePreview(file) {
    // Create a preview URL for the file
    const previewUrl = URL.createObjectURL(file);
    
    // Create a temporary media object for the gallery
    const tempMedia = {
        id: 'temp_' + Date.now(),
        file_url: previewUrl,
        original_name: file.name,
        file_size_formatted: formatFileSize(file.size),
        file: file // Store the actual file object
    };
    
    // Add to gallery immediately
    addMediaToGallery(tempMedia);
    
    // Upload to media library in the background
    uploadToMediaLibrary(file, tempMedia.id);
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function uploadToMediaLibrary(file, tempId) {
    console.log('Starting upload to media library:', file.name, tempId);
    const formData = new FormData();
    formData.append('files[0]', file);

    fetch(window.mediaLibraryConfig.uploadUrl, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': window.mediaLibraryConfig.csrf,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        credentials: 'same-origin',
        body: formData
    })
    .then(async (res) => {
        console.log('Upload response status:', res.status);
        const status = res.status;
        let data = null;
        try {
            data = await res.json();
            console.log('Upload response data:', data);
        } catch (e) {
            console.log('Non-JSON response:', e);
            // Non-JSON response
        }

        if (res.ok && data && (data.success === true || (Array.isArray(data.uploaded) && data.uploaded.length > 0))) {
            console.log('Upload successful, replacing temporary media');
            // Success - replace temporary media with real media library data
            if (data.uploaded && data.uploaded.length > 0) {
                const uploadedMedia = {
                    id: data.uploaded[0].id,
                    file_url: data.uploaded[0].file_url,
                    original_name: data.uploaded[0].original_name,
                    file_size_formatted: data.uploaded[0].file_size_formatted || 'Unknown'
                };
                
                // Replace the temporary media with the real media library data
                replaceTemporaryMedia(tempId, uploadedMedia);
                showNotification(`Image uploaded to media library successfully`, 'success');
            }
        } else {
            console.log('Upload failed:', status, data);
            // Error handling
            if (data && Array.isArray(data.errors) && data.errors.length) {
                const firstErr = data.errors[0]?.error || 'Upload failed';
                console.log('Upload error:', firstErr);
                showNotification(firstErr, 'error');
            } else if (status === 419 || status === 401) {
                console.log('Session expired');
                showNotification('Session expired. Please refresh the page and try again.', 'error');
            } else {
                console.log('Generic upload error:', (data && data.message) || 'Upload failed');
                showNotification((data && data.message) || 'Upload failed', 'error');
            }
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
        showNotification('Upload failed', 'error');
    });
}

function replaceTemporaryMedia(tempId, realMedia) {
    // Find and replace the temporary media item
    const tempIndex = categoryMedia.selectedItems.findIndex(item => item.id === tempId);
    if (tempIndex !== -1) {
        // Clean up the temporary object URL
        const tempItem = categoryMedia.selectedItems[tempIndex];
        if (tempItem.file_url && tempItem.file_url.startsWith('blob:')) {
            URL.revokeObjectURL(tempItem.file_url);
        }
        
        // Replace with real media library data
        categoryMedia.selectedItems[tempIndex] = realMedia;
        renderMediaGallery();
    }
}

function storeDirectFile(file) {
    // Create or update the file input for direct upload
    let fileInput = document.getElementById('directImageFile');
    if (!fileInput) {
        fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.id = 'directImageFile';
        fileInput.name = 'direct_image';
        fileInput.style.display = 'none';
        document.querySelector('form').appendChild(fileInput);
    }
    
    // Create a new FileList with the dropped file
    const dataTransfer = new DataTransfer();
    dataTransfer.items.add(file);
    fileInput.files = dataTransfer.files;
}

function openMediaLibrary(event = null) {
    if (event) event.stopPropagation();
    
    try {
        const modalElement = document.getElementById('mediaLibraryModal');
        if (!modalElement) {
            console.error('Media library modal not found');
            return;
        }
        
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    } catch (error) {
        console.error('Error opening media library:', error);
    }
}


function addMediaToGallery(media) {
    if (!media || categoryMedia.selectedItems.some(item => item.id == media.id)) {
        return;
    }

    // Clear existing items (only one image allowed for category)
    // Clean up any existing object URLs to prevent memory leaks
    categoryMedia.selectedItems.forEach(item => {
        if (item.file_url && item.file_url.startsWith('blob:')) {
            URL.revokeObjectURL(item.file_url);
        }
    });
    
    categoryMedia.selectedItems = [];
    categoryMedia.selectedItems.push(media);
    renderMediaGallery();
}

function removeMediaFromGallery(id) {
    // Find the item to remove and clean up its object URL
    const itemToRemove = categoryMedia.selectedItems.find(item => item.id == id);
    if (itemToRemove && itemToRemove.file_url && itemToRemove.file_url.startsWith('blob:')) {
        URL.revokeObjectURL(itemToRemove.file_url);
    }
    
    categoryMedia.selectedItems = categoryMedia.selectedItems.filter(item => item.id != id);
    
    renderMediaGallery();
}

function renderMediaGallery() {
    if (!categoryMedia.gallery || !categoryMedia.hiddenInputsWrapper) return;

    categoryMedia.gallery.innerHTML = '';
    categoryMedia.hiddenInputsWrapper.innerHTML = '';

    if (categoryMedia.selectedItems.length === 0) {
        categoryMedia.emptyState.classList.remove('d-none');
        if (categoryMedia.imageInput) categoryMedia.imageInput.value = '';
        return;
    }

    categoryMedia.emptyState.classList.add('d-none');

    categoryMedia.selectedItems.forEach((media, index) => {
        const col = document.createElement('div');
        col.className = 'col-lg-4 col-md-6 col-12';

        const card = document.createElement('div');
        card.className = 'category-media-card';
        card.innerHTML = `
            <div class="category-media-thumb">
                <img src="${media.file_url}" alt="${media.original_name}">
            </div>
            <div class="category-media-meta">
                <div class="category-media-name">${media.original_name}</div>
                <div class="category-media-size">${media.file_size_formatted || ''}</div>
            </div>
            <div class="category-media-actions">
                <button type="button" class="btn text-danger p-0 category-media-remove" data-id="${media.id}">Remove</button>
            </div>
        `;

        card.querySelector('.category-media-remove').addEventListener('click', (e) => {
            e.stopPropagation();
            removeMediaFromGallery(media.id);
        });

        col.appendChild(card);
        categoryMedia.gallery.appendChild(col);

        // Store the image path and media ID
        if (categoryMedia.imageInput) {
            if (media.id === 'existing') {
                categoryMedia.imageInput.value = '{{ $isEdit ? $category->image : "" }}';
            } else if (String(media.id).startsWith('temp_')) {
                // For temporary files being uploaded, keep the temp value
                categoryMedia.imageInput.value = 'uploading';
            } else {
                // For media library items, store the file URL
                categoryMedia.imageInput.value = media.file_url;
            }
        }
        
        // Add media ID to hidden inputs for media library items
        if (!String(media.id).startsWith('temp_') && media.id !== 'existing') {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'media_ids[]';
            hiddenInput.value = media.id;
            categoryMedia.hiddenInputsWrapper.appendChild(hiddenInput);
        }
    });
}

function selectMediaFromLibraryItems(items) {
    if (!items || !items.length) return;
    // Only take the first item for category image
    addMediaToGallery(items[0]);
    const modal = bootstrap.Modal.getInstance(document.getElementById('mediaLibraryModal'));
    if (modal) modal.hide();
}

function uploadFilesToMediaLibrary(files) {
    const formData = new FormData();
    files.forEach(file => formData.append('files[]', file));

    // Show loading state
    const dropzone = categoryMedia.dropzone;
    const originalContent = dropzone.innerHTML;
    dropzone.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Uploading...</span>
            </div>
            <p class="mb-0">Uploading ${files.length} image(s)...</p>
        </div>
    `;
    dropzone.style.pointerEvents = 'none';

    fetch(window.mediaLibraryConfig.uploadUrl, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': window.mediaLibraryConfig.csrf,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        credentials: 'same-origin',
        body: formData
    })
    .then(async (res) => {
        const status = res.status;
        let data = null;
        try {
            data = await res.json();
        } catch (e) {
            // Non-JSON response
        }

        if (res.ok && data && (data.success === true || (Array.isArray(data.uploaded) && data.uploaded.length > 0))) {
            // Success - add uploaded images to gallery
            if (data.uploaded && data.uploaded.length > 0) {
                // Only take the first uploaded image for category
                addMediaToGallery({
                    id: data.uploaded[0].id,
                    file_url: data.uploaded[0].file_url,
                    original_name: data.uploaded[0].original_name,
                    file_size_formatted: data.uploaded[0].file_size_formatted || 'Unknown'
                });
                showNotification(`Successfully uploaded image`, 'success');
            }
        } else {
            // Error handling
            if (data && Array.isArray(data.errors) && data.errors.length) {
                const firstErr = data.errors[0]?.error || 'Upload failed';
                showNotification(firstErr, 'error');
            } else if (status === 419 || status === 401) {
                showNotification('Session expired. Please refresh the page and try again.', 'error');
            } else {
                showNotification((data && data.message) || 'Upload failed', 'error');
            }
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
        showNotification('Upload failed', 'error');
    })
    .finally(() => {
        // Restore original content
        dropzone.innerHTML = originalContent;
        dropzone.style.pointerEvents = 'auto';
        
        // Note: Event listeners are already initialized in initializeCategoryMediaPicker()
        // No need to re-add them here to avoid duplicates
    });
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 280px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 4000);
}
</script>
@endpush
@endsection
