@extends('admin.layouts.app')

@section('title', 'Edit Order - Admin Dashboard')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Update Order Status</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.orders.update', $order->id) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Order Number</label>
                                <input type="text" class="form-control" value="{{ $order->order_number }}" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Customer</label>
                                <input type="text" class="form-control" value="{{ $order->user->name }}" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Order Date</label>
                                <input type="text" class="form-control" value="{{ $order->created_at->format('M d, Y H:i') }}" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Total Amount</label>
                                <input type="text" class="form-control" value="${{ number_format($order->total_amount, 2) }}" readonly>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Order Status *</label>
                                <select class="form-control @error('status') is-invalid @enderror" 
                                        id="status" name="status" required>
                                    <option value="pending" {{ old('status', $order->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="processing" {{ old('status', $order->status) == 'processing' ? 'selected' : '' }}>Processing</option>
                                    <option value="shipped" {{ old('status', $order->status) == 'shipped' ? 'selected' : '' }}>Shipped</option>
                                    <option value="delivered" {{ old('status', $order->status) == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                    <option value="cancelled" {{ old('status', $order->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Admin Notes</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="4" placeholder="Add any notes about this order...">{{ old('notes', $order->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="mt-4">
                        <h6 class="mb-3">Order Items</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->orderItems as $item)
                                        <tr>
                                            <td>
                                                <div>
                                                    <h6 class="mb-0">{{ $item->product->name }}</h6>
                                                    <small class="text-muted">{{ $item->product->sku }}</small>
                                                </div>
                                            </td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>${{ number_format($item->price, 2) }}</td>
                                            <td>${{ number_format($item->quantity * $item->price, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3">Total</th>
                                        <th>${{ number_format($order->total_amount, 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.form-control {
    border: 1px solid #e7eaf3;
    border-radius: 0.375rem;
    padding: 0.5rem 0.75rem;
}

.form-control:focus {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.25);
}

.form-control[readonly] {
    background-color: #f5f5f9;
    color: #697a8d;
}

.form-label {
    font-weight: 500;
    color: #697a8d;
    margin-bottom: 0.5rem;
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

.is-invalid {
    border-color: var(--bs-danger);
}

.invalid-feedback {
    display: block;
    color: var(--bs-danger);
    font-size: 0.875rem;
    margin-top: 0.25rem;
}
</style>
@endpush
