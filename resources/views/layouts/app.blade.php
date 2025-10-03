<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'E-Commerce Store')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="text-2xl font-bold text-gray-900">
                        <i class="fas fa-store text-blue-600"></i> E-Store
                    </a>
                </div>

                <!-- Search Bar -->
                <div class="flex-1 max-w-lg mx-8">
                    <form action="{{ route('products.search') }}" method="GET" class="relative">
                        <input type="text" name="q" value="{{ request('q') }}" 
                               placeholder="Search products..." 
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </form>
                </div>

                <!-- Navigation -->
                <nav class="flex items-center space-x-4">
                    <!-- Cart -->
                    <a href="{{ route('cart.index') }}" class="relative text-gray-700 hover:text-blue-600">
                        <i class="fas fa-shopping-cart text-xl"></i>
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center" id="cart-count">
                            {{ session('cart_count', 0) }}
                        </span>
                    </a>

                    <!-- Wishlist -->
                    <a href="{{ route('wishlist.index') }}" class="text-gray-700 hover:text-red-600">
                        <i class="fas fa-heart text-xl"></i>
                    </a>

                    <!-- User Menu -->
                    @auth
                        <div class="relative">
                            <button class="flex items-center text-gray-700 hover:text-blue-600" onclick="toggleUserMenu()">
                                <i class="fas fa-user-circle text-xl mr-2"></i>
                                {{ Auth::user()->name }}
                                <i class="fas fa-chevron-down ml-1"></i>
                            </button>
                            
                            <div id="user-menu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                                <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                                </a>
                                <a href="{{ route('orders.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-shopping-bag mr-2"></i> My Orders
                                </a>
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user mr-2"></i> Profile
                                </a>
                                <a href="{{ route('addresses.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-map-marker-alt mr-2"></i> Addresses
                                </a>
                                @if(Auth::user()->is_admin)
                                    <div class="border-t my-1"></div>
                                    <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-cog mr-2"></i> Admin Panel
                                    </a>
                                @endif
                                <div class="border-t my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('login') }}" class="text-gray-700 hover:text-blue-600">Login</a>
                            <span class="text-gray-300">|</span>
                            <a href="{{ route('register') }}" class="text-gray-700 hover:text-blue-600">Register</a>
                        </div>
                    @endauth
                </nav>
            </div>
        </div>

        <!-- Categories Navigation -->
        <div class="bg-gray-100 border-t">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <nav class="flex space-x-8 py-3">
                    <a href="{{ route('home') }}" class="text-gray-700 hover:text-blue-600 font-medium">Home</a>
                    <a href="{{ route('products.index') }}" class="text-gray-700 hover:text-blue-600 font-medium">All Products</a>
                    @foreach(\App\Models\Category::active()->ordered()->get() as $category)
                        <a href="{{ route('products.category', $category->slug) }}" class="text-gray-700 hover:text-blue-600 font-medium">
                            {{ $category->name }}
                        </a>
                    @endforeach
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="min-h-screen">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Company Info -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">
                        <i class="fas fa-store text-blue-400"></i> E-Store
                    </h3>
                    <p class="text-gray-300 mb-4">
                        Your one-stop destination for quality products at great prices. 
                        We're committed to providing excellent customer service and fast delivery.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="{{ route('home') }}" class="text-gray-300 hover:text-white">Home</a></li>
                        <li><a href="{{ route('products.index') }}" class="text-gray-300 hover:text-white">Products</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white">About Us</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white">Contact</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white">FAQ</a></li>
                    </ul>
                </div>

                <!-- Customer Service -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Customer Service</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-300 hover:text-white">Shipping Info</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white">Returns</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white">Size Guide</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white">Track Order</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white">Support</a></li>
                    </ul>
                </div>

                <!-- Newsletter -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Newsletter</h3>
                    <p class="text-gray-300 mb-4">
                        Subscribe to our newsletter for the latest updates and exclusive offers.
                    </p>
                    <form class="flex">
                        <input type="email" placeholder="Your email" 
                               class="flex-1 px-3 py-2 bg-gray-800 border border-gray-700 rounded-l-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-r-lg hover:bg-blue-700">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-8 pt-8 text-center">
                <p class="text-gray-400">
                    &copy; {{ date('Y') }} E-Store. All rights reserved. | 
                    <a href="#" class="hover:text-white">Privacy Policy</a> | 
                    <a href="#" class="hover:text-white">Terms of Service</a>
                </p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        function toggleUserMenu() {
            const menu = document.getElementById('user-menu');
            menu.classList.toggle('hidden');
        }

        // Close user menu when clicking outside
        document.addEventListener('click', function(event) {
            const userMenu = document.getElementById('user-menu');
            const userButton = event.target.closest('button');
            
            if (!userButton && !userMenu.contains(event.target)) {
                userMenu.classList.add('hidden');
            }
        });

        // Update cart count
        function updateCartCount() {
            fetch('{{ route("cart.count") }}')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('cart-count').textContent = data.count;
                });
        }

        // Update cart count on page load
        document.addEventListener('DOMContentLoaded', updateCartCount);
    </script>

    @stack('scripts')
</body>
</html>
