@extends('admin.layouts.app')

@section('title', 'Blog Post: ' . $blog->title)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Admin / Blog Posts /</span> {{ Str::limit($blog->title, 50) }}
    </h4>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Blog Post Details</h5>
                    <div>
                        <a href="{{ route('admin.blogs.edit', $blog) }}" class="btn btn-primary me-2">
                            <i class="ri-pencil-line me-1"></i> Edit
                        </a>
                        <a href="{{ route('admin.blogs.index') }}" class="btn btn-secondary">
                            <i class="ri-arrow-left-line me-1"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <h2 class="mb-3">{{ $blog->title }}</h2>
                            
                            <div class="d-flex align-items-center mb-3">
                                <span class="badge bg-primary me-2">{{ $blog->blogCategory->name }}</span>
                                <span class="badge bg-info me-2">{{ $blog->views_count }} views</span>
                                @if($blog->is_featured)
                                    <span class="badge bg-warning me-2">Featured</span>
                                @endif
                                @if($blog->is_published)
                                    <span class="badge bg-success">Published</span>
                                @else
                                    <span class="badge bg-secondary">Draft</span>
                                @endif
                            </div>
                            
                            <div class="text-muted mb-3">
                                <i class="ri-user-line me-1"></i> By {{ $blog->user->name }}
                                <i class="ri-calendar-line me-1 ms-3"></i> {{ $blog->created_at->format('M d, Y H:i') }}
                                @if($blog->published_at)
                                    <i class="ri-time-line me-1 ms-3"></i> Published {{ $blog->published_at->format('M d, Y H:i') }}
                                @endif
                            </div>
                            
                            @if($blog->excerpt)
                                <div class="alert alert-light">
                                    <strong>Excerpt:</strong> {{ $blog->excerpt }}
                                </div>
                            @endif
                        </div>
                        <div class="col-md-4">
                            @if($blog->featured_image)
                                <img src="{{ $blog->featured_image_url }}" alt="{{ $blog->title }}" 
                                     class="img-fluid rounded mb-3">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center mb-3" 
                                     style="height: 200px;">
                                    <i class="ri-image-line text-muted" style="font-size: 48px;"></i>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-4">
                        <h6 class="mb-3">Content</h6>
                        <div class="blog-content">
                            {!! nl2br(e($blog->content)) !!}
                        </div>
                    </div>
                    
                    @if($blog->gallery_images && count($blog->gallery_images) > 0)
                        <hr>
                        <h6 class="mb-3">Gallery Images</h6>
                        <div class="row g-3">
                            @foreach($blog->gallery_images as $image)
                                <div class="col-md-4">
                                    <img src="{{ asset('storage/' . $image) }}" alt="Gallery Image" 
                                         class="img-fluid rounded">
                                </div>
                            @endforeach
                        </div>
                    @endif
                    
                    @if($blog->meta_title || $blog->meta_description || $blog->meta_keywords)
                        <hr>
                        <h6 class="mb-3">SEO Information</h6>
                        <div class="row">
                            @if($blog->meta_title)
                                <div class="col-md-12 mb-2">
                                    <strong>Meta Title:</strong>
                                    <p class="mt-1">{{ $blog->meta_title }}</p>
                                </div>
                            @endif
                            
                            @if($blog->meta_description)
                                <div class="col-md-12 mb-2">
                                    <strong>Meta Description:</strong>
                                    <p class="mt-1">{{ $blog->meta_description }}</p>
                                </div>
                            @endif
                            
                            @if($blog->meta_keywords)
                                <div class="col-md-12 mb-2">
                                    <strong>Meta Keywords:</strong>
                                    <p class="mt-1">{{ $blog->meta_keywords }}</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Post Information</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <i class="ri-link text-primary" style="font-size: 24px;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fw-semibold">Slug</div>
                            <div class="text-muted">
                                <code>{{ $blog->slug }}</code>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <i class="ri-eye-line text-info" style="font-size: 24px;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fw-semibold">Views</div>
                            <div class="text-muted">{{ $blog->views_count }}</div>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <i class="ri-time-line text-success" style="font-size: 24px;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fw-semibold">Reading Time</div>
                            <div class="text-muted">{{ $blog->reading_time }} min read</div>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <i class="ri-sort-asc text-warning" style="font-size: 24px;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fw-semibold">Sort Order</div>
                            <div class="text-muted">{{ $blog->sort_order }}</div>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="ri-calendar-line text-secondary" style="font-size: 24px;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fw-semibold">Last Updated</div>
                            <div class="text-muted">{{ $blog->updated_at->format('M d, Y H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.blogs.edit', $blog) }}" class="btn btn-primary">
                            <i class="ri-pencil-line me-1"></i> Edit Post
                        </a>
                        
                        @if($blog->is_published)
                            <button class="btn btn-warning" onclick="togglePublishStatus({{ $blog->id }}, false)">
                                <i class="ri-eye-off-line me-1"></i> Unpublish
                            </button>
                        @else
                            <button class="btn btn-success" onclick="togglePublishStatus({{ $blog->id }}, true)">
                                <i class="ri-eye-line me-1"></i> Publish
                            </button>
                        @endif
                        
                        @if($blog->is_featured)
                            <button class="btn btn-outline-warning" onclick="toggleFeaturedStatus({{ $blog->id }}, false)">
                                <i class="ri-star-line me-1"></i> Remove from Featured
                            </button>
                        @else
                            <button class="btn btn-outline-warning" onclick="toggleFeaturedStatus({{ $blog->id }}, true)">
                                <i class="ri-star-fill me-1"></i> Mark as Featured
                            </button>
                        @endif
                        
                        <hr>
                        
                        <form action="{{ route('admin.blogs.destroy', $blog) }}" method="POST" 
                              onsubmit="return confirm('Are you sure you want to delete this blog post? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="ri-delete-bin-7-line me-1"></i> Delete Post
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePublishStatus(blogId, publish) {
    const action = publish ? 'publish' : 'unpublish';
    const message = publish ? 'Are you sure you want to publish this blog post?' : 'Are you sure you want to unpublish this blog post?';
    
    if (confirm(message)) {
        // Create a form to submit the request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/blogs/${blogId}/toggle-publish`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'PATCH';
        
        const publishField = document.createElement('input');
        publishField.type = 'hidden';
        publishField.name = 'is_published';
        publishField.value = publish ? '1' : '0';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        form.appendChild(publishField);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function toggleFeaturedStatus(blogId, featured) {
    const action = featured ? 'feature' : 'unfeature';
    const message = featured ? 'Are you sure you want to mark this blog post as featured?' : 'Are you sure you want to remove this blog post from featured?';
    
    if (confirm(message)) {
        // Create a form to submit the request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/blogs/${blogId}/toggle-featured`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'PATCH';
        
        const featuredField = document.createElement('input');
        featuredField.type = 'hidden';
        featuredField.name = 'is_featured';
        featuredField.value = featured ? '1' : '0';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        form.appendChild(featuredField);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<style>
.blog-content {
    line-height: 1.6;
    font-size: 16px;
}

.blog-content p {
    margin-bottom: 1rem;
}

.blog-content h1, .blog-content h2, .blog-content h3, 
.blog-content h4, .blog-content h5, .blog-content h6 {
    margin-top: 2rem;
    margin-bottom: 1rem;
    font-weight: 600;
}

.blog-content ul, .blog-content ol {
    margin-bottom: 1rem;
    padding-left: 2rem;
}

.blog-content blockquote {
    border-left: 4px solid #6366f1;
    padding-left: 1rem;
    margin: 1rem 0;
    font-style: italic;
    color: #6b7280;
}
</style>
@endsection

