@extends('admin.layouts.app')

@section('title', 'Orders Management - Admin Dashboard')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Orders Management</h5>
                <div class="d-flex gap-2">
                    <select class="form-control" onchange="filterByStatus(this.value)">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
            <div class="card-body">
                @if($orders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                    <tr>
                                        <td>
                                            <div>
                                                <h6 class="mb-0">{{ $order->order_number }}</h6>
                                                <small class="text-muted">{{ $order->id }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <h6 class="mb-0">{{ $order->user->name }}</h6>
                                                <small class="text-muted">{{ $order->user->email }}</small>
                                            </div>
                                        </td>
                                        <td>{{ $order->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <span class="badge bg-label-{{ $order->status === 'pending' ? 'warning' : ($order->status === 'delivered' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'info')) }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-medium">${{ number_format($order->total_amount, 2) }}</span>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="{{ route('admin.orders.show', $order->id) }}">
                                                        <i class="ri-eye-line me-2"></i>View Details
                                                    </a></li>
                                                    <li><a class="dropdown-item" href="{{ route('admin.orders.edit', $order->id) }}">
                                                        <i class="ri-edit-line me-2"></i>Update Status
                                                    </a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form method="POST" action="{{ route('admin.orders.destroy', $order->id) }}" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this order?')">
                                                                <i class="ri-delete-bin-line me-2"></i>Delete
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $orders->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="ri-shopping-bag-3-line ri-48px text-muted mb-3"></i>
                        <h5 class="text-muted">No orders found</h5>
                        <p class="text-muted mb-4">Orders will appear here once customers start placing them.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

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

.btn-outline-secondary {
    border-color: #e7eaf3;
    color: #697a8d;
}

.btn-outline-secondary:hover {
    background-color: #f5f5f9;
    border-color: #e7eaf3;
    color: #697a8d;
}

.form-control {
    border: 1px solid #e7eaf3;
    border-radius: 0.375rem;
    padding: 0.5rem 0.75rem;
}
</style>
@endpush

@push('scripts')
<script>
function filterByStatus(status) {
    const url = new URL(window.location);
    if (status) {
        url.searchParams.set('status', status);
    } else {
        url.searchParams.delete('status');
    }
    window.location.href = url.toString();
}
</script>
@endpush
