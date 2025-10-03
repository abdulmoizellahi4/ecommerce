@extends('layouts.app')

@section('title', 'My Wishlist - E-Commerce Store')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">My Wishlist</h1>
        <p class="text-gray-600">Save your favorite products for later.</p>
    </div>

    @if(Auth::user()->wishlists()->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach(Auth::user()->wishlists()->with('product.category')->get() as $wishlistItem)
                <div class="bg-white rounded-lg shadow-sm hover:shadow-lg transition duration-300 overflow-hidden">
                    <a href="{{ route('products.show', $wishlistItem->product->slug) }}">
                        @if($wishlistItem->product->images && count($wishlistItem->product->images) > 0)
                            <img src="{{ asset('storage/' . $wishlistItem->product->images[0]) }}" 
                                 alt="{{ $wishlistItem->product->name }}" 
                                 class="w-full h-48 object-cover">
                        @else
                            <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                <i class="fas fa-image text-gray-400 text-4xl"></i>
                            </div>
                        @endif
                    </a>
                    
                    <div class="p-4">
                        <a href="{{ route('products.show', $wishlistItem->product->slug) }}">
                            <h3 class="font-semibold text-gray-900 mb-2 hover:text-blue-600">{{ $wishlistItem->product->name }}</h3>
                        </a>
                        
                        <p class="text-sm text-gray-600 mb-2">{{ $wishlistItem->product->category->name }}</p>
                        
                        <div class="flex items-center mb-2">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= $wishlistItem->product->average_rating ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                            @endfor
                            <span class="ml-2 text-sm text-gray-600">({{ $wishlistItem->product->review_count }})</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                @if($wishlistItem->product->sale_price)
                                    <span class="text-lg font-bold text-red-600">${{ number_format($wishlistItem->product->sale_price, 2) }}</span>
                                    <span class="text-sm text-gray-500 line-through">${{ number_format($wishlistItem->product->price, 2) }}</span>
                                @else
                                    <span class="text-lg font-bold text-gray-900">${{ number_format($wishlistItem->product->price, 2) }}</span>
                                @endif
                            </div>
                            
                            <div class="flex space-x-2">
                                <button onclick="addToCart({{ $wishlistItem->product->id }})" 
                                        class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 transition duration-300">
                                    <i class="fas fa-cart-plus"></i>
                                </button>
                                <button onclick="removeFromWishlist({{ $wishlistItem->id }})" 
                                        class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 transition duration-300">
                                    <i class="fas fa-heart-broken"></i>
                                </button>
                            </div>
                        </div>
                        
                        @if($wishlistItem->product->discount_percentage > 0)
                            <div class="mt-2">
                                <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded">{{ $wishlistItem->product->discount_percentage }}% OFF</span>
                            </div>
                        @endif
                        
                        <div class="mt-2 text-xs text-gray-500">
                            Added {{ $wishlistItem->created_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <i class="fas fa-heart text-gray-400 text-6xl mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Your wishlist is empty</h3>
            <p class="text-gray-600 mb-6">Save products you love to your wishlist and they'll appear here.</p>
            <a href="{{ route('products.index') }}" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition duration-300">
                Start Shopping
            </a>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function addToCart(productId) {
    fetch('{{ route("cart.add") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount();
            showNotification('Product added to cart!', 'success');
        } else {
            showNotification('Error adding product to cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error adding product to cart', 'error');
    });
}

function removeFromWishlist(wishlistId) {
    if (confirm('Are you sure you want to remove this item from your wishlist?')) {
        fetch(`{{ route("wishlist.remove", ":id") }}`.replace(':id', wishlistId), {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                showNotification('Error removing item from wishlist', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error removing item from wishlist', 'error');
        });
    }
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>
@endpush
