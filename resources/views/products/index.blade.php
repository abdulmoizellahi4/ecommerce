@extends('layouts.app')

@section('title', 'Products - E-Commerce Store')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            @if(isset($category))
                {{ $category->name }}
            @elseif(isset($query))
                Search Results for "{{ $query }}"
            @else
                All Products
            @endif
        </h1>
        
        @if(isset($category) && $category->description)
            <p class="text-gray-600">{{ $category->description }}</p>
        @endif
    </div>

    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar Filters -->
        <div class="lg:w-1/4">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold mb-4">Filters</h3>
                
                <!-- Categories -->
                <div class="mb-6">
                    <h4 class="font-medium text-gray-900 mb-3">Categories</h4>
                    <ul class="space-y-2">
                        <li>
                            <a href="{{ route('products.index') }}" 
                               class="text-gray-600 hover:text-blue-600 {{ !isset($category) ? 'text-blue-600 font-medium' : '' }}">
                                All Categories
                            </a>
                        </li>
                        @foreach($categories as $cat)
                            <li>
                                <a href="{{ route('products.category', $cat->slug) }}" 
                                   class="text-gray-600 hover:text-blue-600 {{ isset($category) && $category->id == $cat->id ? 'text-blue-600 font-medium' : '' }}">
                                    {{ $cat->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Price Range -->
                <div class="mb-6">
                    <h4 class="font-medium text-gray-900 mb-3">Price Range</h4>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio" name="price" value="0-50" class="mr-2">
                            <span class="text-gray-600">Under $50</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="price" value="50-100" class="mr-2">
                            <span class="text-gray-600">$50 - $100</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="price" value="100-500" class="mr-2">
                            <span class="text-gray-600">$100 - $500</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="price" value="500+" class="mr-2">
                            <span class="text-gray-600">Over $500</span>
                        </label>
                    </div>
                </div>

                <!-- Availability -->
                <div class="mb-6">
                    <h4 class="font-medium text-gray-900 mb-3">Availability</h4>
                    <label class="flex items-center">
                        <input type="checkbox" name="in_stock" checked class="mr-2">
                        <span class="text-gray-600">In Stock Only</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="lg:w-3/4">
            <!-- Sort and View Options -->
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600">{{ $products->total() }} products found</span>
                </div>
                
                <div class="flex items-center space-x-4">
                    <select class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="name">Sort by Name</option>
                        <option value="price">Sort by Price</option>
                        <option value="newest">Sort by Newest</option>
                        <option value="popular">Sort by Popularity</option>
                    </select>
                </div>
            </div>

            <!-- Products Grid -->
            @if($products->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($products as $product)
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
                                
                                <p class="text-sm text-gray-600 mb-2">{{ $product->category->name }}</p>
                                
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

                <!-- Pagination -->
                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-search text-gray-400 text-6xl mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No products found</h3>
                    <p class="text-gray-600 mb-6">
                        @if(isset($query))
                            We couldn't find any products matching "{{ $query }}"
                        @else
                            No products are available in this category
                        @endif
                    </p>
                    <a href="{{ route('products.index') }}" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition duration-300">
                        View All Products
                    </a>
                </div>
            @endif
        </div>
    </div>
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
