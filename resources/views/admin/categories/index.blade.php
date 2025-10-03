@extends('admin.layouts.app')

@section('title', 'Categories Management')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Admin /</span> Categories
    </h4>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Categories List</h5>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                <i class="ri-add-line me-1"></i> Add New Category
            </a>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            <div class="table-responsive text-nowrap">
                <table class="table table-hover" id="myTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Products Count</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse ($categories as $category)
                            <tr>
                                <td><strong>{{ $category->id }}</strong></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-initial rounded bg-label-primary">
                                                <i class="ri-folder-line"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $category->name }}</h6>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ Str::limit($category->description, 50) }}</td>
                                <td>
                                    <span class="badge bg-label-info">{{ $category->products_count ?? 0 }}</span>
                                </td>
                                <td>
                                    @if($category->is_active)
                                        <span class="badge bg-label-success">Active</span>
                                    @else
                                        <span class="badge bg-label-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $category->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="ri-more-2-line"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="{{ route('admin.categories.show', $category->id) }}">
                                                <i class="ri-eye-line me-1"></i> View
                                            </a>
                                            <a class="dropdown-item" href="{{ route('admin.categories.edit', $category->id) }}">
                                                <i class="ri-pencil-line me-1"></i> Edit
                                            </a>
                                            <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item">
                                                    <i class="ri-delete-bin-line me-1"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No categories found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
