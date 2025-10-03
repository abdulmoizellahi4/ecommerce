@extends('admin.layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row gy-6">
        <!-- Congratulations card -->
        <div class="col-md-12 col-lg-4">
            <div class="card">
                <div class="card-body text-nowrap">
                    <h5 class="card-title mb-0 flex-wrap text-nowrap">Congratulations Admin! 🎉</h5>
                    <p class="mb-2">Manage your e-commerce store</p>
                    <h4 class="text-primary mb-0">${{ number_format(\App\Models\Order::sum('total_amount') ?? 0, 2) }}</h4>
                    <p class="mb-2">Total Revenue 🚀</p>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-primary">View Orders</a>
                </div>
                <img src="{{ asset('assets/img/illustrations/trophy.svg') }}" class="position-absolute bottom-0 end-0 me-5 mb-5" width="83" alt="view sales">
            </div>
        </div>
        <!--/ Congratulations card -->

        <!-- Statistics -->
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="card-title m-0 me-2">Statistics</h5>
                        <div class="dropdown">
                            <button class="btn text-muted p-0" type="button" id="statisticsID" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="ri-more-2-line ri-24px"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="statisticsID">
                                <a class="dropdown-item" href="javascript:void(0);">Refresh</a>
                                <a class="dropdown-item" href="javascript:void(0);">Share</a>
                                <a class="dropdown-item" href="javascript:void(0);">Update</a>
                            </div>
                        </div>
                    </div>
                    <p class="small mb-0"><span class="h6 mb-0">Overview</span> of your store</p>
                </div>
                <div class="card-body pt-lg-10">
                    <div class="row g-6">
                        <div class="col-md-3 col-6">
                            <div class="d-flex align-items-center">
                                <div class="avatar">
                                    <div class="avatar-initial bg-primary rounded shadow-xs">
                                        <i class="ri-shopping-bag-3-line ri-24px"></i>
                                    </div>
                                </div>
                                <div class="ms-3">
                                    <p class="mb-0">Orders</p>
                                    <h5 class="mb-0">{{ \App\Models\Order::count() }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="d-flex align-items-center">
                                <div class="avatar">
                                    <div class="avatar-initial bg-success rounded shadow-xs">
                                        <i class="ri-group-line ri-24px"></i>
                                    </div>
                                </div>
                                <div class="ms-3">
                                    <p class="mb-0">Customers</p>
                                    <h5 class="mb-0">{{ \App\Models\User::where('is_admin', false)->count() }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="d-flex align-items-center">
                                <div class="avatar">
                                    <div class="avatar-initial bg-warning rounded shadow-xs">
                                        <i class="ri-macbook-line ri-24px"></i>
                                    </div>
                                </div>
                                <div class="ms-3">
                                    <p class="mb-0">Products</p>
                                    <h5 class="mb-0">{{ \App\Models\Product::count() }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="d-flex align-items-center">
                                <div class="avatar">
                                    <div class="avatar-initial bg-info rounded shadow-xs">
                                        <i class="ri-money-dollar-circle-line ri-24px"></i>
                                    </div>
                                </div>
                                <div class="ms-3">
                                    <p class="mb-0">Revenue</p>
                                    <h5 class="mb-0">${{ number_format(\App\Models\Order::sum('total_amount') ?? 0, 2) }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/ Statistics -->

        <!-- Recent Orders -->
        <div class="col-xl-8 col-md-6">
            <div class="card overflow-hidden">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Recent Orders</h5>
                    <a href="{{ route('admin.orders.index') }}" class="fw-medium">View all</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th class="text-truncate">Order #</th>
                                <th class="text-truncate">Customer</th>
                                <th class="text-truncate">Status</th>
                                <th class="text-truncate">Total</th>
                                <th class="text-truncate">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(\App\Models\Order::latest()->take(5)->get() as $order)
                                <tr>
                                    <td><a href="{{ route('admin.orders.show', $order->id) }}">{{ $order->order_number }}</a></td>
                                    <td>{{ $order->user->name ?? 'Guest' }}</td>
                                    <td>
                                        <span class="badge rounded-pill
                                            @if($order->status == 'pending') bg-label-warning
                                            @elseif($order->status == 'processing') bg-label-info
                                            @elseif($order->status == 'shipped') bg-label-primary
                                            @elseif($order->status == 'delivered') bg-label-success
                                            @else bg-label-danger @endif">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td>${{ number_format($order->total_amount, 2) }}</td>
                                    <td>{{ $order->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center">No recent orders found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!--/ Recent Orders -->

        <!-- Top Products -->
        <div class="col-xl-4 col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Top Products</h5>
                    <a href="{{ route('admin.products.index') }}" class="fw-medium">View all</a>
                </div>
                <div class="card-body">
                    <ul class="p-0 m-0">
                        @forelse(\App\Models\Product::withCount('orderItems')->orderByDesc('order_items_count')->take(5)->get() as $product)
                            <li class="d-flex mb-4 align-items-center pb-2">
                                <div class="flex-shrink-0 me-4">
                                    <img src="{{ asset('assets/img/avatars/1.png') }}" alt="{{ $product->name }}" class="img-fluid rounded" height="30" width="30">
                                </div>
                                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                    <div class="me-2">
                                        <h6 class="mb-0">{{ $product->name }}</h6>
                                        <p class="mb-0">{{ $product->category->name ?? 'No Category' }}</p>
                                    </div>
                                    <div>
                                        <h6 class="mb-2">${{ number_format($product->current_price, 2) }}</h6>
                                        <small class="text-muted">{{ $product->order_items_count }} sales</small>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="text-center">No top products found.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
        <!--/ Top Products -->

        <!-- Quick Actions -->
        <div class="col-xl-4 col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title m-0 me-2">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                            <i class="ri-add-line me-1"></i> Add New Product
                        </a>
                        <a href="{{ route('admin.categories.create') }}" class="btn btn-success">
                            <i class="ri-folder-add-line me-1"></i> Add New Category
                        </a>
                        <a href="{{ route('admin.coupons.create') }}" class="btn btn-info">
                            <i class="ri-coupon-line me-1"></i> Add New Coupon
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-warning">
                            <i class="ri-user-line me-1"></i> Manage Customers
                        </a>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
                            <i class="ri-file-list-3-line me-1"></i> View Orders
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!--/ Quick Actions -->

        <!-- Recent Customers -->
        <div class="col-xl-8 col-md-6">
            <div class="card overflow-hidden">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Recent Customers</h5>
                    <a href="{{ route('admin.users.index') }}" class="fw-medium">View all</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th class="text-truncate">Name</th>
                                <th class="text-truncate">Email</th>
                                <th class="text-truncate">Orders</th>
                                <th class="text-truncate">Status</th>
                                <th class="text-truncate">Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(\App\Models\User::where('is_admin', false)->latest()->take(5)->get() as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <span class="badge bg-label-primary">{{ $user->orders()->count() }}</span>
                                    </td>
                                    <td>
                                        @if($user->is_active)
                                            <span class="badge bg-label-success">Active</span>
                                        @else
                                            <span class="badge bg-label-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center">No customers found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!--/ Recent Customers -->

        <!-- Categories Overview -->
        <div class="col-xl-6 col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Categories Overview</h5>
                    <a href="{{ route('admin.categories.index') }}" class="fw-medium">View all</a>
                </div>
                <div class="card-body">
                    <ul class="p-0 m-0">
                        @forelse(\App\Models\Category::withCount('products')->orderByDesc('products_count')->take(5)->get() as $category)
                            <li class="d-flex mb-4 align-items-center pb-2">
                                <div class="flex-shrink-0 me-4">
                                    <div class="avatar">
                                        <div class="avatar-initial bg-label-primary rounded">
                                            <i class="ri-folder-line ri-24px"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                    <div class="me-2">
                                        <h6 class="mb-0">{{ $category->name }}</h6>
                                        <p class="mb-0">{{ $category->description ?? 'No description' }}</p>
                                    </div>
                                    <div>
                                        <h6 class="mb-2">{{ $category->products_count }}</h6>
                                        <small class="text-muted">products</small>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="text-center">No categories found.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
        <!--/ Categories Overview -->

        <!-- Recent Reviews -->
        <div class="col-xl-6 col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Recent Reviews</h5>
                    <a href="{{ route('admin.reviews.index') }}" class="fw-medium">View all</a>
                </div>
                <div class="card-body">
                    <ul class="p-0 m-0">
                        @forelse(\App\Models\Review::with(['user', 'product'])->latest()->take(5)->get() as $review)
                            <li class="d-flex mb-4 align-items-center pb-2">
                                <div class="flex-shrink-0 me-4">
                                    <div class="avatar">
                                        <img src="{{ asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle">
                                    </div>
                                </div>
                                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                    <div class="me-2">
                                        <h6 class="mb-0">{{ $review->user->name }}</h6>
                                        <p class="mb-0">{{ Str::limit($review->product->name, 20) }}</p>
                                        <div class="d-flex align-items-center">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="ri-star-fill {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }} ri-12px"></i>
                                            @endfor
                                        </div>
                                    </div>
                                    <div>
                                        <small class="text-muted">{{ $review->created_at->format('M d') }}</small>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="text-center">No reviews found.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
        <!--/ Recent Reviews -->
    </div>
</div>
@endsection