@extends('admin.layouts.app')

@section('title', 'Customer Reviews')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Admin /</span> Customer Reviews
    </h4>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Reviews List</h5>
            <div class="d-flex gap-2">
                <select class="form-select" id="statusFilter" style="width: auto;">
                    <option value="">All Reviews</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
                <select class="form-select" id="ratingFilter" style="width: auto;">
                    <option value="">All Ratings</option>
                    <option value="5">5 Stars</option>
                    <option value="4">4 Stars</option>
                    <option value="3">3 Stars</option>
                    <option value="2">2 Stars</option>
                    <option value="1">1 Star</option>
                </select>
            </div>
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
                            <th>Customer</th>
                            <th>Product</th>
                            <th>Rating</th>
                            <th>Review</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse ($reviews as $review)
                            <tr>
                                <td><strong>{{ $review->id }}</strong></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3">
                                            <img src="{{ asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle">
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $review->user->name }}</h6>
                                            <small class="text-muted">{{ $review->user->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3">
                                            <img src="{{ asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle">
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ Str::limit($review->product->name, 20) }}</h6>
                                            <small class="text-muted">{{ $review->product->category->name ?? 'No Category' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="ri-star-fill {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }} ri-16px"></i>
                                        @endfor
                                        <span class="ms-1 text-muted">({{ $review->rating }})</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;" title="{{ $review->comment }}">
                                        {{ Str::limit($review->comment, 50) }}
                                    </div>
                                </td>
                                <td>
                                    @if($review->status == 'pending')
                                        <span class="badge bg-label-warning">Pending</span>
                                    @elseif($review->status == 'approved')
                                        <span class="badge bg-label-success">Approved</span>
                                    @else
                                        <span class="badge bg-label-danger">Rejected</span>
                                    @endif
                                </td>
                                <td>{{ $review->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="ri-more-2-line"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="{{ route('admin.reviews.show', $review->id) }}">
                                                <i class="ri-eye-line me-1"></i> View Details
                                            </a>
                                            <a class="dropdown-item" href="{{ route('admin.reviews.edit', $review->id) }}">
                                                <i class="ri-pencil-line me-1"></i> Edit
                                            </a>
                                            @if($review->status == 'pending')
                                                <form action="{{ route('admin.reviews.approve', $review->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item text-success">
                                                        <i class="ri-check-line me-1"></i> Approve
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.reviews.reject', $review->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="ri-close-line me-1"></i> Reject
                                                    </button>
                                                </form>
                                            @endif
                                            <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this review?');">
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
                                <td colspan="8" class="text-center">No reviews found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusFilter = document.getElementById('statusFilter');
    const ratingFilter = document.getElementById('ratingFilter');
    
    statusFilter.addEventListener('change', function() {
        filterTable();
    });
    
    ratingFilter.addEventListener('change', function() {
        filterTable();
    });
    
    function filterTable() {
        const statusValue = statusFilter.value;
        const ratingValue = ratingFilter.value;
        const table = document.getElementById('myTable');
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
        
        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const statusCell = row.cells[5]; // Status column
            const ratingCell = row.cells[3]; // Rating column
            
            let showRow = true;
            
            if (statusValue && !statusCell.textContent.toLowerCase().includes(statusValue)) {
                showRow = false;
            }
            
            if (ratingValue && !ratingCell.textContent.includes(ratingValue)) {
                showRow = false;
            }
            
            row.style.display = showRow ? '' : 'none';
        }
    }
});
</script>
@endsection
