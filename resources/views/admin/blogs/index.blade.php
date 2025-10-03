@extends('admin.layouts.app')

@section('title', 'Blog Posts')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Admin /</span> Blog Posts
    </h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Blog Posts</h5>
                    <a href="{{ route('admin.blogs.create') }}" class="btn btn-primary">
                        <i class="ri-add-line me-1"></i> Add New Post
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($blogs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Featured Image</th>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Author</th>
                                        <th>Status</th>
                                        <th>Views</th>
                                        <th>Published</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($blogs as $blog)
                                        <tr>
                                            <td>
                                                @if($blog->featured_image)
                                                    <img src="{{ $blog->featured_image_url }}" alt="{{ $blog->title }}" 
                                                         class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                                @else
                                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                         style="width: 50px; height: 50px;">
                                                        <i class="ri-image-line text-muted"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ Str::limit($blog->title, 50) }}</div>
                                                @if($blog->excerpt)
                                                    <small class="text-muted">{{ Str::limit($blog->excerpt, 60) }}</small>
                                                @endif
                                                @if($blog->is_featured)
                                                    <span class="badge bg-warning ms-1">Featured</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $blog->blogCategory->name }}</span>
                                            </td>
                                            <td>{{ $blog->user->name }}</td>
                                            <td>
                                                @if($blog->is_published)
                                                    <span class="badge bg-success">Published</span>
                                                @else
                                                    <span class="badge bg-secondary">Draft</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $blog->views_count }}</span>
                                            </td>
                                            <td>
                                                @if($blog->published_at)
                                                    {{ $blog->published_at->format('M d, Y') }}
                                                @else
                                                    <span class="text-muted">Not published</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                        <i class="ri-more-2-line"></i>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="{{ route('admin.blogs.show', $blog) }}">
                                                            <i class="ri-eye-line me-1"></i> View
                                                        </a>
                                                        <a class="dropdown-item" href="{{ route('admin.blogs.edit', $blog) }}">
                                                            <i class="ri-pencil-line me-1"></i> Edit
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <form action="{{ route('admin.blogs.destroy', $blog) }}" method="POST" 
                                                              onsubmit="return confirm('Are you sure you want to delete this blog post?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="ri-delete-bin-7-line me-1"></i> Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $blogs->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="ri-file-text-line" style="font-size: 48px; color: #ccc;"></i>
                            <h5 class="mt-3 text-muted">No Blog Posts Found</h5>
                            <p class="text-muted">Get started by creating your first blog post.</p>
                            <a href="{{ route('admin.blogs.create') }}" class="btn btn-primary">
                                <i class="ri-add-line me-1"></i> Create First Post
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

