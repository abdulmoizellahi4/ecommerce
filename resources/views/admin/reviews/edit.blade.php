@extends('admin.layouts.app')

@section('title', 'Edit Review')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Admin / Reviews /</span> Edit
    </h4>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Edit Review</h5>
                    <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary">Back to Reviews</a>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.reviews.update', $review->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Customer</label>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-3">
                                        <img src="{{ asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle">
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $review->user->name }}</h6>
                                        <small class="text-muted">{{ $review->user->email }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Product</label>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-3">
                                        <img src="{{ asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle">
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $review->product->name }}</h6>
                                        <small class="text-muted">{{ $review->product->category->name ?? 'No Category' }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="rating" class="form-label">Rating <span class="text-danger">*</span></label>
                            <div class="rating-input">
                                @for($i = 1; $i <= 5; $i++)
                                    <input type="radio" id="star{{ $i }}" name="rating" value="{{ $i }}" 
                                           {{ old('rating', $review->rating) == $i ? 'checked' : '' }}>
                                    <label for="star{{ $i }}" class="star-label">
                                        <i class="ri-star-fill"></i>
                                    </label>
                                @endfor
                            </div>
                            @error('rating')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="comment" class="form-label">Review Comment</label>
                            <textarea class="form-control @error('comment') is-invalid @enderror" 
                                      id="comment" name="comment" rows="4">{{ old('comment', $review->comment) }}</textarea>
                            @error('comment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                <option value="pending" {{ old('status', $review->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ old('status', $review->status) == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ old('status', $review->status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="admin_response" class="form-label">Admin Response (Optional)</label>
                            <textarea class="form-control @error('admin_response') is-invalid @enderror" 
                                      id="admin_response" name="admin_response" rows="3" 
                                      placeholder="Add a response to this review...">{{ old('admin_response', $review->admin_response) }}</textarea>
                            @error('admin_response')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="ri-save-line me-1"></i> Update Review
                            </button>
                            <a href="{{ route('admin.reviews.index') }}" class="btn btn-outline-secondary">
                                <i class="ri-arrow-left-line me-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Review Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Original Rating</label>
                        <div class="d-flex align-items-center">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="ri-star-fill {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }} ri-20px"></i>
                            @endfor
                            <span class="ms-2">{{ $review->rating }}/5</span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Review Date</label>
                        <p class="mb-0">{{ $review->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Last Updated</label>
                        <p class="mb-0">{{ $review->updated_at->format('M d, Y H:i') }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Current Status</label>
                        <div>
                            @if($review->status == 'pending')
                                <span class="badge bg-label-warning">Pending</span>
                            @elseif($review->status == 'approved')
                                <span class="badge bg-label-success">Approved</span>
                            @else
                                <span class="badge bg-label-danger">Rejected</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.rating-input {
    display: flex;
    gap: 5px;
}

.rating-input input[type="radio"] {
    display: none;
}

.rating-input .star-label {
    font-size: 24px;
    color: #ddd;
    cursor: pointer;
    transition: color 0.2s;
}

.rating-input .star-label:hover,
.rating-input input[type="radio"]:checked ~ .star-label,
.rating-input input[type="radio"]:checked ~ .star-label ~ .star-label {
    color: #ffc107;
}

.rating-input input[type="radio"]:checked ~ .star-label {
    color: #ffc107;
}
</style>
@endsection
