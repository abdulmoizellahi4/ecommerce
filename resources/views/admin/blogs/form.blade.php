@php
    $isEdit = isset($blog);
    $title = $isEdit ? 'Edit Blog Post' : 'Add New Blog Post';
    $heading = $isEdit ? 'Edit' : 'Add New';
    $cardTitle = $isEdit ? 'Edit Blog Post: ' . $blog->title : 'Blog Post Information';
    $formAction = $isEdit ? route('admin.blogs.update', $blog->id) : route('admin.blogs.store');
    $method = $isEdit ? 'PUT' : 'POST';
    $buttonText = $isEdit ? 'Update Post' : 'Create Post';
    $buttonIcon = $isEdit ? 'ri-save-line' : 'ri-save-line';
@endphp

@extends('admin.layouts.app')

@section('title', $title)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Admin / Blog Posts /</span> {{ $heading }}
    </h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">{{ $cardTitle }}</h5>
                    <a href="{{ route('admin.blogs.index') }}" class="btn btn-secondary">Back to Posts</a>
                </div>
                <div class="card-body">
                    <form action="{{ $formAction }}" method="POST">
                        @csrf
                        @if($isEdit)
                            @method('PUT')
                        @endif
                        
                        <div class="row">
                            <div class="mb-3 col-md-8">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                           id="title" name="title" 
                                           value="{{ old('title', $isEdit ? $blog->title : '') }}" 
                                           placeholder="Blog Title" required>
                                    <label for="title">Blog Title *</label>
                                </div>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3 col-md-4">
                                <div class="form-floating form-floating-outline">
                                    <select class="form-select @error('blog_category_id') is-invalid @enderror" 
                                            id="blog_category_id" name="blog_category_id" required>
                                        <option value="">Select Category</option>
                                        @foreach($blogCategories as $category)
                                            <option value="{{ $category->id }}" 
                                                    {{ old('blog_category_id', $isEdit ? $blog->blog_category_id : '') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label for="blog_category_id">Category *</label>
                                </div>
                                @error('blog_category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control @error('slug') is-invalid @enderror" 
                                       id="slug" name="slug" 
                                       value="{{ old('slug', $isEdit ? $blog->slug : '') }}"
                                       placeholder="Slug">
                                <label for="slug">Slug</label>
                            </div>
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if(!$isEdit)
                                <small class="text-muted">Leave empty to auto-generate from title</small>
                            @endif
                        </div>

                        <div class="mb-3">
                            <div class="form-floating form-floating-outline">
                                <textarea class="form-control @error('excerpt') is-invalid @enderror" 
                                          id="excerpt" name="excerpt" rows="3" 
                                          placeholder="Excerpt">{{ old('excerpt', $isEdit ? $blog->excerpt : '') }}</textarea>
                                <label for="excerpt">Excerpt</label>
                            </div>
                            @error('excerpt')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Brief description of the blog post (optional)</small>
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Content *</label>
                            <textarea class="form-control @error('content') is-invalid @enderror" 
                                      id="content" name="content" rows="15" 
                                      placeholder="Write your blog post content here..." required>{{ old('content', $isEdit ? $blog->content : '') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Blog Images Upload -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">Blog Images</h6>
                                    <button type="button" class="btn btn-link p-0 fw-semibold text-primary" onclick="openMediaLibrary(event)">
                                        Add Media
                                    </button>
                                </div>
                                
                                @php
                                    $initialMedia = [];
                                    $oldMediaIds = old('media_ids', []);
                                    if ($isEdit && $blog->gallery_images) {
                                        $galleryImages = is_array($blog->gallery_images) ? $blog->gallery_images : json_decode($blog->gallery_images, true);
                                        if ($galleryImages) {
                                            foreach ($galleryImages as $index => $image) {
                                                $initialMedia[] = [
                                                    'id' => 'existing_' . $index,
                                                    'file_url' => asset('storage/' . $image),
                                                    'original_name' => basename($image),
                                                    'file_size_formatted' => 'Existing'
                                                ];
                                            }
                                        }
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

                                <div class="blog-media-picker" data-initial-media='@json($initialMedia)'>
                                    <div class="blog-media-dropzone" id="blogMediaDropzone" role="button" tabindex="0" aria-label="Select blog images">
                                        <div class="blog-media-empty" id="blogMediaEmpty">
                                            <i class="ri-upload-cloud-2-line"></i>
                                            <p class="mb-1">Drop images here or click to open media library</p>
                                            <small class="text-muted">Select images for your blog post</small>
                                        </div>

                                        <div class="row g-3 blog-media-gallery" id="blogMediaGallery"></div>
                                    </div>

                                    <input type="hidden" name="featured_image" id="blogFeaturedImageInput" value="{{ old('featured_image', $isEdit ? $blog->featured_image : '') }}">
                                    <input type="hidden" name="gallery_images" id="blogGalleryImagesInput" value="{{ old('gallery_images', $isEdit ? json_encode($blog->gallery_images) : '') }}">
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
                                           value="{{ old('meta_title', $isEdit ? $blog->meta_title : '') }}"
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
                                           value="{{ old('meta_keywords', $isEdit ? $blog->meta_keywords : '') }}"
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
                                          maxlength="160" placeholder="Meta Description">{{ old('meta_description', $isEdit ? $blog->meta_description : '') }}</textarea>
                                <label for="meta_description">Meta Description</label>
                            </div>
                            @error('meta_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Recommended: 150-160 characters</small>
                        </div>

                        <div class="row">
                            <div class="mb-3 col-md-4">
                                <div class="form-floating form-floating-outline">
                                    <input type="datetime-local" class="form-control @error('published_at') is-invalid @enderror" 
                                           id="published_at" name="published_at" 
                                           value="{{ old('published_at', $isEdit && $blog->published_at ? $blog->published_at->format('Y-m-d\TH:i') : '') }}">
                                    <label for="published_at">Publish Date</label>
                                </div>
                                @error('published_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3 col-md-4">
                                <div class="form-floating form-floating-outline">
                                    <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                           id="sort_order" name="sort_order" 
                                           value="{{ old('sort_order', $isEdit ? $blog->sort_order : 0) }}" 
                                           min="0" placeholder="Sort Order">
                                    <label for="sort_order">Sort Order</label>
                                </div>
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3 col-md-4">
                                <label class="form-label">Options</label>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="is_published" name="is_published" 
                                           value="1" {{ old('is_published', $isEdit ? $blog->is_published : false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_published">
                                        Published
                                    </label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" 
                                           value="1" {{ old('is_featured', $isEdit ? $blog->is_featured : false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_featured">
                                        Featured
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="{{ $buttonIcon }} me-1"></i> {{ $buttonText }}
                            </button>
                            <a href="{{ route('admin.blogs.index') }}" class="btn btn-outline-secondary">
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
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');
    const metaTitleInput = document.getElementById('meta_title');
    const metaDescriptionInput = document.getElementById('meta_description');
    
    // Auto-generate slug from title (only in create mode)
    titleInput.addEventListener('input', function() {
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
    
    // Initialize character counters
    metaDescriptionInput.dispatchEvent(new Event('input'));
    metaTitleInput.dispatchEvent(new Event('input'));
});
</script>

<!-- Include Media Library Modal -->
@include('admin.media.partials.media-library-modal')

@push('styles')
<style>
.blog-media-picker {
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    padding: 20px;
    background: #fafbff;
}

.blog-media-dropzone {
    border: 2px dashed #d5d9ff;
    border-radius: 12px;
    background: #ffffff;
    padding: 32px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
    position: relative;
}

.blog-media-dropzone:hover {
    border-color: #6366f1;
    background: #f3f4ff;
}

.blog-media-empty i {
    font-size: 32px;
    color: #6366f1;
    display: block;
    margin-bottom: 10px;
}

.blog-media-empty p {
    color: #6b7280;
    font-size: 14px;
}

.blog-media-gallery {
    width: 100%;
}

.blog-media-card {
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

.blog-media-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 18px 32px rgba(79, 70, 229, 0.15);
}

.blog-media-card .blog-media-thumb {
    width: 100%;
    height: 100px;
    background: linear-gradient(180deg, #f3f5ff 0%, #e9ecff 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 14px;
    overflow: hidden;
}

.blog-media-card .blog-media-thumb img {
    width: 100%;
    height: 100px;
    object-fit: cover;
}

.blog-media-card .blog-media-meta {
    width: 100%;
    border-top: 1px solid #eceffc;
    padding-top: 12px;
    margin-top: auto;
}

.blog-media-card .blog-media-name {
    font-weight: 600;
    font-size: 14px;
    color: #121826;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.blog-media-card .blog-media-size {
    font-size: 12px;
    color: #8c91a7;
    font-style: italic;
    margin-top: 2px;
}

.blog-media-card .blog-media-actions {
    margin-top: 12px;
}

.blog-media-card .blog-media-remove {
    font-size: 13px;
    color: #ef4444;
}

.blog-media-dropzone.dragover {
    border-color: #4338ca;
    background: rgba(99, 102, 241, 0.08);
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/js/media-library.js') }}"></script>
<script>
// Media Library Configuration
window.mediaLibraryConfig = {
    uploadUrl: '{{ route("admin.media.upload") }}',
    libraryUrl: '{{ route("admin.media.library") }}',
    updateUrl: '{{ route("admin.media.update", ":id") }}',
    deleteUrl: '{{ route("admin.media.destroy", ":id") }}',
    uploadUrlUrl: '{{ route("admin.media.upload-url") }}',
    csrf: '{{ csrf_token() }}'
};

let blogMedia = {
    dropzone: null,
    emptyState: null,
    gallery: null,
    featuredImageInput: null,
    galleryImagesInput: null,
    hiddenInputsWrapper: null,
    selectedItems: [],
    eventListenersAdded: false
};

document.addEventListener('DOMContentLoaded', function() {
    initializeBlogMediaPicker();
});

function initializeBlogMediaPicker() {
    blogMedia.dropzone = document.getElementById('blogMediaDropzone');
    blogMedia.emptyState = document.getElementById('blogMediaEmpty');
    blogMedia.gallery = document.getElementById('blogMediaGallery');
    blogMedia.featuredImageInput = document.getElementById('blogFeaturedImageInput');
    blogMedia.galleryImagesInput = document.getElementById('blogGalleryImagesInput');
    blogMedia.hiddenInputsWrapper = document.getElementById('selectedMediaInputs');

    const picker = document.querySelector('.blog-media-picker');
    if (picker && picker.dataset.initialMedia) {
        try {
            const initialMedia = JSON.parse(picker.dataset.initialMedia);
            initialMedia.forEach(item => addMediaToGallery(item));
        } catch (error) {
            console.warn('Failed to parse initial media', error);
        }
    }

    if (!blogMedia.dropzone) return;

    // Only add event listeners once to prevent duplicates
    if (!blogMedia.eventListenersAdded) {
        blogMedia.dropzone.addEventListener('click', openMediaLibrary);

        blogMedia.dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            blogMedia.dropzone.classList.add('dragover');
        });

        blogMedia.dropzone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            blogMedia.dropzone.classList.remove('dragover');
        });

        blogMedia.dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            blogMedia.dropzone.classList.remove('dragover');
            
            const files = Array.from(e.dataTransfer.files).filter(file => 
                file.type.startsWith('image/')
            );
            
            if (files.length > 0) {
                // Show images immediately instead of uploading to media library
                files.forEach(file => showDirectImagePreview(file));
            } else {
                openMediaLibrary();
            }
        });
        
        blogMedia.eventListenersAdded = true;
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
    const tempIndex = blogMedia.selectedItems.findIndex(item => item.id === tempId);
    if (tempIndex !== -1) {
        // Clean up the temporary object URL
        const tempItem = blogMedia.selectedItems[tempIndex];
        if (tempItem.file_url && tempItem.file_url.startsWith('blob:')) {
            URL.revokeObjectURL(tempItem.file_url);
        }
        
        // Replace with real media library data
        blogMedia.selectedItems[tempIndex] = realMedia;
        renderMediaGallery();
    }
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
    if (!media || blogMedia.selectedItems.some(item => item.id == media.id)) {
        return;
    }

    // Clean up any existing object URLs to prevent memory leaks
    blogMedia.selectedItems.forEach(item => {
        if (item.file_url && item.file_url.startsWith('blob:')) {
            URL.revokeObjectURL(item.file_url);
        }
    });

    blogMedia.selectedItems.push(media);
    renderMediaGallery();
}

function removeMediaFromGallery(id) {
    // Find the item to remove and clean up its object URL
    const itemToRemove = blogMedia.selectedItems.find(item => item.id == id);
    if (itemToRemove && itemToRemove.file_url && itemToRemove.file_url.startsWith('blob:')) {
        URL.revokeObjectURL(itemToRemove.file_url);
    }
    
    blogMedia.selectedItems = blogMedia.selectedItems.filter(item => item.id != id);
    renderMediaGallery();
}

function renderMediaGallery() {
    if (!blogMedia.gallery || !blogMedia.hiddenInputsWrapper) return;

    blogMedia.gallery.innerHTML = '';
    blogMedia.hiddenInputsWrapper.innerHTML = '';

    if (blogMedia.selectedItems.length === 0) {
        blogMedia.emptyState.classList.remove('d-none');
        if (blogMedia.featuredImageInput) blogMedia.featuredImageInput.value = '';
        if (blogMedia.galleryImagesInput) blogMedia.galleryImagesInput.value = '';
        return;
    }

    blogMedia.emptyState.classList.add('d-none');

    blogMedia.selectedItems.forEach((media, index) => {
        const col = document.createElement('div');
        col.className = 'col-lg-4 col-md-6 col-12';

        const card = document.createElement('div');
        card.className = 'blog-media-card';
        card.innerHTML = `
            <div class="blog-media-thumb">
                <img src="${media.file_url}" alt="${media.original_name}">
            </div>
            <div class="blog-media-meta">
                <div class="blog-media-name">${media.original_name}</div>
                <div class="blog-media-size">${media.file_size_formatted || ''}</div>
            </div>
            <div class="blog-media-actions">
                <button type="button" class="btn text-danger p-0 blog-media-remove" data-id="${media.id}">Remove</button>
            </div>
        `;

        card.querySelector('.blog-media-remove').addEventListener('click', (e) => {
            e.stopPropagation();
            removeMediaFromGallery(media.id);
        });

        col.appendChild(card);
        blogMedia.gallery.appendChild(col);

        // Set featured image (first image)
        if (index === 0 && blogMedia.featuredImageInput) {
            if (String(media.id).startsWith('temp_')) {
                // For temporary files being uploaded, keep the temp value
                blogMedia.featuredImageInput.value = 'uploading';
            } else if (String(media.id).startsWith('existing_')) {
                // For existing images
                blogMedia.featuredImageInput.value = '{{ $isEdit ? $blog->featured_image : "" }}';
            } else {
                // For media library items, store the file URL
                blogMedia.featuredImageInput.value = media.file_url;
            }
        }
        
        // Add media ID to hidden inputs for media library items only
        if (!String(media.id).startsWith('temp_') && !String(media.id).startsWith('existing_')) {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'media_ids[]';
            hiddenInput.value = media.id;
            blogMedia.hiddenInputsWrapper.appendChild(hiddenInput);
        }
    });

    // Update gallery images JSON
    if (blogMedia.galleryImagesInput) {
        const galleryImages = blogMedia.selectedItems
            .filter(item => !String(item.id).startsWith('temp_'))
            .map(item => {
                if (String(item.id).startsWith('existing_')) {
                    return '{{ $isEdit ? $blog->featured_image : "" }}';
                }
                return item.file_url;
            });
        blogMedia.galleryImagesInput.value = JSON.stringify(galleryImages);
    }
}

function selectMediaFromLibraryItems(items) {
    if (!items || !items.length) return;
    items.forEach(item => addMediaToGallery(item));
    const modal = bootstrap.Modal.getInstance(document.getElementById('mediaLibraryModal'));
    if (modal) modal.hide();
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

