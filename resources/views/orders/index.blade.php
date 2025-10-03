@extends('layouts.app')

@section('title', 'My Orders - E-Commerce Store')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">My Orders</h1>
        <p class="text-gray-600">Track and manage your order history.</p>
    </div>

    @if(Auth::user()->orders()->count() > 0)
        <div class="space-y-6">
            @foreach(Auth::user()->orders()->latest()->get() as $order)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <!-- Order Header -->
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Order #{{ $order->order_number }}</h3>
                                <p class="text-sm text-gray-600">Placed on {{ $order->created_at->format('M d, Y \a\t g:i A') }}</p>
                            </div>
                            <div class="mt-2 sm:mt-0">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
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
                    </div>

                    <!-- Order Items -->
                    <div class="px-6 py-4">
                        <div class="space-y-4">
                            @foreach($order->orderItems as $item)
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        @if($item->product->images && count($item->product->images) > 0)
                                            <img src="{{ asset('storage/' . $item->product->images[0]) }}" 
                                                 alt="{{ $item->product_name }}" 
                                                 class="h-16 w-16 object-cover rounded-lg">
                                        @else
                                            <div class="h-16 w-16 bg-gray-200 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-image text-gray-400"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-medium text-gray-900 truncate">{{ $item->product_name }}</h4>
                                        <p class="text-sm text-gray-600">SKU: {{ $item->product_sku }}</p>
                                        <p class="text-sm text-gray-600">Quantity: {{ $item->quantity }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-900">${{ number_format($item->price, 2) }}</p>
                                        <p class="text-sm text-gray-600">Total: ${{ number_format($item->total, 2) }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        <div class="flex justify-between items-center">
                            <div class="text-sm text-gray-600">
                                <p>Subtotal: ${{ number_format($order->subtotal, 2) }}</p>
                                @if($order->tax_amount > 0)
                                    <p>Tax: ${{ number_format($order->tax_amount, 2) }}</p>
                                @endif
                                @if($order->shipping_amount > 0)
                                    <p>Shipping: ${{ number_format($order->shipping_amount, 2) }}</p>
                                @endif
                                @if($order->discount_amount > 0)
                                    <p>Discount: -${{ number_format($order->discount_amount, 2) }}</p>
                                @endif
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-semibold text-gray-900">Total: ${{ number_format($order->total_amount, 2) }}</p>
                                <p class="text-sm text-gray-600">{{ $order->payment_method ?? 'Payment method not specified' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Order Actions -->
                    <div class="px-6 py-4 border-t border-gray-200">
                        <div class="flex justify-between items-center">
                            <div class="text-sm text-gray-600">
                                @if($order->status === 'delivered')
                                    <p>Delivered on {{ $order->updated_at->format('M d, Y') }}</p>
                                @elseif($order->status === 'shipped')
                                    <p>Shipped on {{ $order->updated_at->format('M d, Y') }}</p>
                                @elseif($order->status === 'processing')
                                    <p>Processing since {{ $order->updated_at->format('M d, Y') }}</p>
                                @else
                                    <p>Order placed on {{ $order->created_at->format('M d, Y') }}</p>
                                @endif
                            </div>
                            <div class="flex space-x-2">
                                <a href="{{ route('orders.show', $order->id) }}" 
                                   class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300 text-sm">
                                    View Details
                                </a>
                                @if($order->status === 'delivered')
                                    <button class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-300 text-sm">
                                        Leave Review
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <i class="fas fa-shopping-bag text-gray-400 text-6xl mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No orders yet</h3>
            <p class="text-gray-600 mb-6">You haven't placed any orders yet. Start shopping to see your orders here.</p>
            <a href="{{ route('products.index') }}" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition duration-300">
                Start Shopping
            </a>
        </div>
    @endif
</div>
@endsection
