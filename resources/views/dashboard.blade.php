@extends('layouts.app')

@section('title', 'Dashboard - E-Commerce Store')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Welcome Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            Welcome back, {{ Auth::user()->name }}!
        </h1>
        <p class="text-gray-600">Here's what's happening with your account today.</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Orders -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-shopping-bag text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Orders</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ Auth::user()->orders()->count() }}</p>
                </div>
            </div>
        </div>

        <!-- Pending Orders -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-clock text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Pending Orders</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ Auth::user()->orders()->pending()->count() }}</p>
                </div>
            </div>
        </div>

        <!-- Wishlist Items -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <i class="fas fa-heart text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Wishlist Items</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ Auth::user()->wishlists()->count() }}</p>
                </div>
            </div>
        </div>

        <!-- Reviews -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-star text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Reviews Written</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ Auth::user()->reviews()->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Orders -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Recent Orders</h3>
            </div>
            <div class="p-6">
                @if(Auth::user()->orders()->count() > 0)
                    <div class="space-y-4">
                        @foreach(Auth::user()->orders()->latest()->take(5)->get() as $order)
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-900">Order #{{ $order->order_number }}</p>
                                    <p class="text-sm text-gray-600">{{ $order->created_at->format('M d, Y') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium text-gray-900">${{ number_format($order->total_amount, 2) }}</p>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($order->status === 'processing') bg-blue-100 text-blue-800
                                        @elseif($order->status === 'shipped') bg-purple-100 text-purple-800
                                        @elseif($order->status === 'delivered') bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800
                                        @endif">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('orders.index') }}" class="text-blue-600 hover:text-blue-500 font-medium">
                            View all orders →
                        </a>
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-shopping-bag text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-600 mb-4">You haven't placed any orders yet.</p>
                        <a href="{{ route('products.index') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                            Start Shopping
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <a href="{{ route('products.index') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-300">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                            <i class="fas fa-shopping-cart text-xl"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Browse Products</p>
                            <p class="text-sm text-gray-600">Shop our latest collection</p>
                        </div>
                    </a>

                    <a href="{{ route('wishlist.index') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-300">
                        <div class="p-3 rounded-full bg-red-100 text-red-600 mr-4">
                            <i class="fas fa-heart text-xl"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">My Wishlist</p>
                            <p class="text-sm text-gray-600">View saved items</p>
                        </div>
                    </a>

                    <a href="{{ route('addresses.index') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-300">
                        <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                            <i class="fas fa-map-marker-alt text-xl"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Manage Addresses</p>
                            <p class="text-sm text-gray-600">Update shipping info</p>
                        </div>
                    </a>

                    <a href="{{ route('profile.edit') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-300">
                        <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                            <i class="fas fa-user text-xl"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Edit Profile</p>
                            <p class="text-sm text-gray-600">Update your information</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="mt-8 bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Recent Activity</h3>
        </div>
        <div class="p-6">
            @if(Auth::user()->orders()->count() > 0 || Auth::user()->reviews()->count() > 0)
                <div class="space-y-4">
                    @foreach(Auth::user()->orders()->latest()->take(3)->get() as $order)
                        <div class="flex items-center">
                            <div class="p-2 rounded-full bg-blue-100 text-blue-600 mr-4">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">
                                    Order #{{ $order->order_number }} was {{ $order->status }}
                                </p>
                                <p class="text-sm text-gray-600">{{ $order->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @endforeach

                    @foreach(Auth::user()->reviews()->latest()->take(2)->get() as $review)
                        <div class="flex items-center">
                            <div class="p-2 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">
                                    You reviewed "{{ $review->product->name }}"
                                </p>
                                <p class="text-sm text-gray-600">{{ $review->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-history text-gray-400 text-4xl mb-4"></i>
                    <p class="text-gray-600">No recent activity to show.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
