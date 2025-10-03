@extends('admin.layouts.app')

@section('title', 'Blog Category: ' . $blogCategory->name)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Admin / Blog Categories /</span> {{ $blogCategory->name }}
    </h4>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Category Details</h5>
                    <div>
                        <a href="{{ route('admin.blog-categories.edit', $blogCategory) }}" class="btn btn-primary me-2">
                            <i class="ri-pencil-line me-1"></i> Edit
                        </a>
                        <a href="{{ route('admin.blog-categories.index') }}" class="btn btn-secondary">
                            <i class="ri-arrow-left-line me-1"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            @if($blogCategory->image)
                                <img src="{{ $blogCategory->image_url }}" alt="{{ $blogCategory->name }}" 
                                     class="img-fluid rounded mb-3">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center mb-3" 
                                     style="height: 200px;">
                                    <i class="ri-image-line text-muted" style="font-size: 48px;"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <h4 class="mb-3">{{ $blogCategory->name }}</h4>
                            
                            <div class="mb-3">
                                <strong>Slug:</strong> 
                                <code>{{ $blogCategory->slug }}</code>
                            </div>
                            
                            @if($blogCategory->description)
                                <div class="mb-3">
                                    <strong>Description:</strong>
                                    <p class="mt-1">{{ $blogCategory->description }}</p>
                                </div>
                            @endif
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Status:</strong>
                                    @if($blogCategory->is_active)
                                        <span class="badge bg-success ms-2">Active</span>
                                    @else
                                        <span class="badge bg-secondary ms-2">Inactive</span>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <strong>Sort Order:</strong>
                                    <span class="ms-2">{{ $blogCategory->sort_order }}</span>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Created:</strong>
                                    <span class="ms-2">{{ $blogCategory->created_at->format('M d, Y H:i') }}</span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Updated:</strong>
                                    <span class="ms-2">{{ $blogCategory->updated_at->format('M d, Y H:i') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($blogCategory->meta_title || $blogCategory->meta_description || $blogCategory->meta_keywords)
                        <hr>
                        <h6 class="mb-3">SEO Information</h6>
                        <div class="row">
                            @if($blogCategory->meta_title)
                                <div class="col-md-12 mb-2">
                                    <strong>Meta Title:</strong>
                                    <p class="mt-1">{{ $blogCategory->meta_title }}</p>
                                </div>
                            @endif
                            
                            @if($blogCategory->meta_description)
                                <div class="col-md-12 mb-2">
                                    <strong>Meta Description:</strong>
                                    <p class="mt-1">{{ $blogCategory->meta_description }}</p>
                                </div>
                            @endif
                            
                            @if($blogCategory->meta_keywords)
                                <div class="col-md-12 mb-2">
                                    <strong>Meta Keywords:</strong>
                                    <p class="mt-1">{{ $blogCategory->meta_keywords }}</p>
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
                    <h5 class="mb-0">Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <i class="ri-file-text-line text-primary" style="font-size: 24px;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fw-semibold">Total Blogs</div>
                            <div class="text-muted">{{ $blogCategory->blogs_count }}</div>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <i class="ri-eye-line text-info" style="font-size: 24px;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fw-semibold">Total Views</div>
                            <div class="text-muted">{{ $blogCategory->blogs->sum('views_count') }}</div>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="ri-calendar-line text-success" style="font-size: 24px;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fw-semibold">Last Blog</div>
                            <div class="text-muted">
                                @if($blogCategory->blogs->count() > 0)
                                    {{ $blogCategory->blogs->sortByDesc('created_at')->first()->created_at->format('M d, Y') }}
                                @else
                                    No blogs yet
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @if($blogCategory->blogs->count() > 0)
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Recent Blogs</h5>
                        <a href="{{ route('admin.blogs.index', ['category' => $blogCategory->id]) }}" class="btn btn-sm btn-outline-primary">
                            View All
                        </a>
                    </div>
                    <div class="card-body">
                        @foreach($blogCategory->blogs->take(5) as $blog)
                            <div class="d-flex align-items-center mb-3">
                                @if($blog->featured_image)
                                    <img src="{{ $blog->featured_image_url }}" alt="{{ $blog->title }}" 
                                         class="rounded me-3" style="width: 40px; height: 40px; object-fit: cover;">
                                @else
                                    <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" 
                                         style="width: 40px; height: 40px;">
                                        <i class="ri-image-line text-muted"></i>
                                    </div>
                                @endif
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">
                                        <a href="{{ route('admin.blogs.show', $blog) }}" class="text-decoration-none">
                                            {{ Str::limit($blog->title, 30) }}
                                        </a>
                                    </div>
                                    <small class="text-muted">{{ $blog->created_at->format('M d, Y') }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

