@extends('layouts.app')

@section('title', 'Home - E-Commerce Store')

@section('content')
<!-- Hero Section -->
<section class="bg-gradient-to-r from-blue-600 to-purple-600 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="text-center">
            <h1 class="text-4xl md:text-6xl font-bold mb-6">
                Welcome to E-Store
            </h1>
            <p class="text-xl md:text-2xl mb-8 text-blue-100">
                Discover amazing products at unbeatable prices
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('products.index') }}" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition duration-300">
                    Shop Now
                </a>
                <a href="#featured" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transition duration-300">
                    View Featured
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Shop by Category</h2>
            <p class="text-gray-600">Explore our wide range of product categories</p>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
            @foreach(\App\Models\Category::active()->ordered()->take(6)->get() as $category)
                <a href="{{ route('products.category', $category->slug) }}" class="group">
                    <div class="bg-gray-100 rounded-lg p-6 text-center hover:bg-gray-200 transition duration-300">
                        @if($category->image)
                            <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="w-16 h-16 mx-auto mb-4 object-cover rounded-lg">
                        @else
                            <div class="w-16 h-16 mx-auto mb-4 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-box text-blue-600 text-2xl"></i>
                            </div>
                        @endif
                        <h3 class="font-semibold text-gray-900 group-hover:text-blue-600">{{ $category->name }}</h3>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section id="featured" class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Featured Products</h2>
            <p class="text-gray-600">Handpicked products just for you</p>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach(\App\Models\Product::active()->featured()->inStock()->take(8)->get() as $product)
                <div class="bg-white rounded-lg shadow-sm hover:shadow-lg transition duration-300 overflow-hidden">
                    <a href="{{ route('products.show', $product->slug) }}">
                        @if($product->images && count($product->images) > 0)
                            <img src="{{ asset('storage/' . $product->images[0]) }}" alt="{{ $product->name }}" class="w-full h-48 object-cover">
                        @else
                            <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                <i class="fas fa-image text-gray-400 text-4xl"></i>
                            </div>
                        @endif
                    </a>
                    
                    <div class="p-4">
                        <a href="{{ route('products.show', $product->slug) }}">
                            <h3 class="font-semibold text-gray-900 mb-2 hover:text-blue-600">{{ $product->name }}</h3>
                        </a>
                        
                        <div class="flex items-center mb-2">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= $product->average_rating ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                            @endfor
                            <span class="ml-2 text-sm text-gray-600">({{ $product->review_count }})</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                @if($product->sale_price)
                                    <span class="text-lg font-bold text-red-600">${{ number_format($product->sale_price, 2) }}</span>
                                    <span class="text-sm text-gray-500 line-through">${{ number_format($product->price, 2) }}</span>
                                @else
                                    <span class="text-lg font-bold text-gray-900">${{ number_format($product->price, 2) }}</span>
                                @endif
                            </div>
                            
                            <button onclick="addToCart({{ $product->id }})" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 transition duration-300">
                                <i class="fas fa-cart-plus"></i>
                            </button>
                        </div>
                        
                        @if($product->discount_percentage > 0)
                            <div class="mt-2">
                                <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded">{{ $product->discount_percentage }}% OFF</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="text-center mt-8">
            <a href="{{ route('products.index') }}" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition duration-300">
                View All Products
            </a>
        </div>
    </div>
</section>

<!-- Latest Products Section -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Latest Products</h2>
            <p class="text-gray-600">Check out our newest arrivals</p>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach(\App\Models\Product::active()->inStock()->latest()->take(8)->get() as $product)
                <div class="bg-white rounded-lg shadow-sm hover:shadow-lg transition duration-300 overflow-hidden">
                    <a href="{{ route('products.show', $product->slug) }}">
                        @if($product->images && count($product->images) > 0)
                            <img src="{{ asset('storage/' . $product->images[0]) }}" alt="{{ $product->name }}" class="w-full h-48 object-cover">
                        @else
                            <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                <i class="fas fa-image text-gray-400 text-4xl"></i>
                            </div>
                        @endif
                    </a>
                    
                    <div class="p-4">
                        <a href="{{ route('products.show', $product->slug) }}">
                            <h3 class="font-semibold text-gray-900 mb-2 hover:text-blue-600">{{ $product->name }}</h3>
                        </a>
                        
                        <div class="flex items-center mb-2">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= $product->average_rating ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                            @endfor
                            <span class="ml-2 text-sm text-gray-600">({{ $product->review_count }})</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                @if($product->sale_price)
                                    <span class="text-lg font-bold text-red-600">${{ number_format($product->sale_price, 2) }}</span>
                                    <span class="text-sm text-gray-500 line-through">${{ number_format($product->price, 2) }}</span>
                                @else
                                    <span class="text-lg font-bold text-gray-900">${{ number_format($product->price, 2) }}</span>
                                @endif
                            </div>
                            
                            <button onclick="addToCart({{ $product->id }})" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 transition duration-300">
                                <i class="fas fa-cart-plus"></i>
                            </button>
                        </div>
                        
                        @if($product->discount_percentage > 0)
                            <div class="mt-2">
                                <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded">{{ $product->discount_percentage }}% OFF</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="py-16 bg-blue-600 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold mb-4">Stay Updated</h2>
        <p class="text-xl mb-8 text-blue-100">
            Subscribe to our newsletter for the latest updates and exclusive offers
        </p>
        
        <form class="max-w-md mx-auto flex">
            <input type="email" placeholder="Your email address" 
                   class="flex-1 px-4 py-3 rounded-l-lg text-gray-900 focus:ring-2 focus:ring-blue-300 focus:outline-none">
            <button type="submit" class="bg-white text-blue-600 px-6 py-3 rounded-r-lg font-semibold hover:bg-gray-100 transition duration-300">
                Subscribe
            </button>
        </form>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">What Our Customers Say</h2>
            <p class="text-gray-600">Real reviews from real customers</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <h4 class="font-semibold text-gray-900">John Doe</h4>
                        <div class="flex text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
                <p class="text-gray-600">
                    "Excellent service and fast delivery. The products are exactly as described and the quality is outstanding!"
                </p>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <h4 class="font-semibold text-gray-900">Jane Smith</h4>
                        <div class="flex text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
                <p class="text-gray-600">
                    "Great prices and amazing customer support. I've been shopping here for months and never disappointed!"
                </p>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-purple-600"></i>
                    </div>
                    <div class="ml-4">
                        <h4 class="font-semibold text-gray-900">Mike Johnson</h4>
                        <div class="flex text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
                <p class="text-gray-600">
                    "The website is easy to navigate and the checkout process is smooth. Highly recommended!"
                </p>
            </div>
        </div>
    </div>
</section>
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
            // Update cart count
            updateCartCount();
            
            // Show success message
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

function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Remove notification after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>
@endpush
