@extends('admin.layouts.app')

@section('title', 'Blog Categories')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Admin /</span> Blog Categories
    </h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Blog Categories</h5>
                    <a href="{{ route('admin.blog-categories.create') }}" class="btn btn-primary">
                        <i class="ri-add-line me-1"></i> Add New Category
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

                    @if($blogCategories->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Slug</th>
                                        <th>Blogs Count</th>
                                        <th>Status</th>
                                        <th>Sort Order</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($blogCategories as $category)
                                        <tr>
                                            <td>
                                                @if($category->image)
                                                    <img src="{{ $category->image_url }}" alt="{{ $category->name }}" 
                                                         class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                                @else
                                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                         style="width: 50px; height: 50px;">
                                                        <i class="ri-image-line text-muted"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $category->name }}</div>
                                                @if($category->description)
                                                    <small class="text-muted">{{ Str::limit($category->description, 50) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <code>{{ $category->slug }}</code>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $category->blogs_count }}</span>
                                            </td>
                                            <td>
                                                @if($category->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>{{ $category->sort_order }}</td>
                                            <td>{{ $category->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <div class="dropdown">
                                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                        <i class="ri-more-2-line"></i>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="{{ route('admin.blog-categories.show', $category) }}">
                                                            <i class="ri-eye-line me-1"></i> View
                                                        </a>
                                                        <a class="dropdown-item" href="{{ route('admin.blog-categories.edit', $category) }}">
                                                            <i class="ri-pencil-line me-1"></i> Edit
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <form action="{{ route('admin.blog-categories.destroy', $category) }}" method="POST" 
                                                              onsubmit="return confirm('Are you sure you want to delete this category?')">
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
                    @else
                        <div class="text-center py-5">
                            <i class="ri-folder-open-line" style="font-size: 48px; color: #ccc;"></i>
                            <h5 class="mt-3 text-muted">No Blog Categories Found</h5>
                            <p class="text-muted">Get started by creating your first blog category.</p>
                            <a href="{{ route('admin.blog-categories.create') }}" class="btn btn-primary">
                                <i class="ri-add-line me-1"></i> Create First Category
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

