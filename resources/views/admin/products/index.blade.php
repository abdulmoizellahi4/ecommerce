@extends('admin.layouts.app')

@section('title', 'Products Management')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Admin /</span> Products
    </h4>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Products List</h5>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                <i class="ri-add-line me-1"></i> Add New Product
            </a>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="typeFilter">
                        <option value="">All Types</option>
                        <option value="simple">Simple Products</option>
                        <option value="variable">Variable Products</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search products...">
                </div>
            </div>

            @if($products->count() > 0)
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover" id="myTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Variations</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse ($products as $product)
                                <tr>
                                    <td><strong>{{ $product->id }}</strong></td>
                                    <td>
                                        @if($product->images && count($product->images) > 0)
                                            <img src="{{ asset('storage/' . $product->images[0]) }}" alt="{{ $product->name }}" class="img-fluid rounded" style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <div class="avatar avatar-sm">
                                                <span class="avatar-initial bg-label-secondary rounded">
                                                    <i class="ri-image-line"></i>
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <h6 class="mb-0">{{ $product->name }}</h6>
                                                <small class="text-muted">{{ $product->sku }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($product->product_type === 'variable')
                                            <span class="badge bg-label-info">
                                                <i class="ri-settings-3-line me-1"></i>Variable
                                            </span>
                                        @else
                                            <span class="badge bg-label-primary">
                                                <i class="ri-box-3-line me-1"></i>Simple
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ $product->category->name }}</td>
                                    <td>
                                        @if($product->isVariable())
                                            <div>
                                                <span class="fw-medium">{{ $product->price_range }}</span>
                                                <br><small class="text-muted">{{ $product->variation_count }} variations</small>
                                            </div>
                                        @else
                                            <div>
                                                <span class="fw-medium">${{ number_format($product->current_price, 2) }}</span>
                                                @if($product->sale_price)
                                                    <br><small class="text-success">Sale: ${{ number_format($product->sale_price, 2) }}</small>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($product->isVariable())
                                            <span class="badge bg-label-info">{{ $product->productVariations->sum('stock_quantity') }}</span>
                                        @else
                                            <span class="badge bg-label-{{ $product->in_stock ? 'success' : 'danger' }}">
                                                {{ $product->stock_quantity }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($product->isVariable())
                                            <span class="badge bg-label-secondary">{{ $product->variation_count }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($product->is_active)
                                            <span class="badge bg-label-success">Active</span>
                                        @else
                                            <span class="badge bg-label-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="ri-more-2-line"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="{{ route('products.show', $product->slug) }}" target="_blank">
                                                    <i class="ri-eye-line me-1"></i> View
                                                </a>
                                                <a class="dropdown-item" href="{{ route('admin.products.edit', $product->id) }}">
                                                    <i class="ri-pencil-line me-1"></i> Edit
                                                </a>
                                                @if($product->isVariable())
                                                    <a class="dropdown-item" href="#" onclick="manageVariations({{ $product->id }})">
                                                        <i class="ri-settings-3-line me-1"></i> Manage Variations
                                                    </a>
                                                @endif
                                                <div class="dropdown-divider"></div>
                                                <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="ri-delete-bin-line me-1"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center">No products found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $products->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="ri-box-3-line ri-48px text-muted mb-3"></i>
                    <h5 class="text-muted">No products found</h5>
                    <p class="text-muted mb-4">Start by adding your first product to the store.</p>
                    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                        <i class="ri-add-line me-2"></i>Add First Product
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Variations Management Modal -->
<div class="modal fade" id="variationsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manage Variations</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="variationsContent">
                    <!-- Variations will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

<script>
function manageVariations(productId) {
    // Load variations for the product
    fetch(`/admin/products/${productId}/variations`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('variationsContent').innerHTML = data.html;
            new bootstrap.Modal(document.getElementById('variationsModal')).show();
        })
        .catch(error => {
            console.error('Error loading variations:', error);
            alert('Error loading variations');
        });
}

// Table filtering
document.addEventListener('DOMContentLoaded', function() {
    const statusFilter = document.getElementById('statusFilter');
    const typeFilter = document.getElementById('typeFilter');
    const searchInput = document.getElementById('searchInput');
    
    function filterTable() {
        const statusValue = statusFilter.value.toLowerCase();
        const typeValue = typeFilter.value.toLowerCase();
        const searchValue = searchInput.value.toLowerCase();
        const table = document.getElementById('myTable');
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
        
        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const statusCell = row.cells[8]; // Status column
            const typeCell = row.cells[3]; // Type column
            const nameCell = row.cells[2]; // Name column
            
            let showRow = true;
            
            if (statusValue && !statusCell.textContent.toLowerCase().includes(statusValue)) {
                showRow = false;
            }
            
            if (typeValue && !typeCell.textContent.toLowerCase().includes(typeValue)) {
                showRow = false;
            }
            
            if (searchValue && !nameCell.textContent.toLowerCase().includes(searchValue)) {
                showRow = false;
            }
            
            row.style.display = showRow ? '' : 'none';
        }
    }
    
    statusFilter.addEventListener('change', filterTable);
    typeFilter.addEventListener('change', filterTable);
    searchInput.addEventListener('input', filterTable);
});
</script>

@push('styles')
<style>
.table-responsive {
    border-radius: 0.5rem;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #697a8d;
    background-color: #f5f5f9;
}

.table td {
    vertical-align: middle;
}

.avatar-sm {
    width: 2.5rem;
    height: 2.5rem;
    object-fit: cover;
}

.btn-outline-secondary {
    border-color: #e7eaf3;
    color: #697a8d;
}

.btn-outline-secondary:hover {
    background-color: #f5f5f9;
    border-color: #e7eaf3;
    color: #697a8d;
}
</style>
@endpush
