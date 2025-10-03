<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\AddressController;

// Home route
Route::get('/', [HomeController::class, 'index'])->name('home');

// Product routes
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');
Route::get('/products/category/{slug}', [ProductController::class, 'category'])->name('products.category');
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');

// Cart routes
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::put('/cart/update/{id}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');

// Wishlist routes
Route::middleware('auth')->group(function () {
    Route::get('/wishlist', function () {
        return view('wishlist.index');
    })->name('wishlist.index');
    
    Route::post('/wishlist/add', [WishlistController::class, 'add'])->name('wishlist.add');
    Route::delete('/wishlist/remove/{id}', [WishlistController::class, 'remove'])->name('wishlist.remove');
});

// Review routes
Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');

// Coupon routes
Route::post('/coupons/validate', [CouponController::class, 'validate'])->name('coupons.validate');

// Address routes
Route::resource('addresses', AddressController::class);

// Order routes
Route::middleware('auth')->group(function () {
    Route::get('/orders', function () {
        return view('orders.index');
    })->name('orders.index');
    
    Route::get('/orders/{order}', function ($order) {
        $order = \App\Models\Order::where('id', $order)
                                  ->where('user_id', auth()->id())
                                  ->with(['orderItems.product'])
                                  ->firstOrFail();
        return view('orders.show', compact('order'));
    })->name('orders.show');
});

// Dashboard route
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

// Profile routes
Route::get('/profile', function () {
    return view('profile.edit');
})->middleware('auth')->name('profile.edit');

// Admin routes
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
    
    Route::resource('products', ProductController::class)->names([
        'index' => 'admin.products.index',
        'create' => 'admin.products.create',
        'store' => 'admin.products.store',
        'show' => 'admin.products.show',
        'edit' => 'admin.products.edit',
        'update' => 'admin.products.update',
        'destroy' => 'admin.products.destroy'
    ]);
    
    Route::resource('categories', \App\Http\Controllers\CategoryController::class)->names([
        'index' => 'admin.categories.index',
        'create' => 'admin.categories.create',
        'store' => 'admin.categories.store',
        'show' => 'admin.categories.show',
        'edit' => 'admin.categories.edit',
        'update' => 'admin.categories.update',
        'destroy' => 'admin.categories.destroy'
    ]);
    
    Route::resource('orders', OrderController::class)->names([
        'index' => 'admin.orders.index',
        'create' => 'admin.orders.create',
        'store' => 'admin.orders.store',
        'show' => 'admin.orders.show',
        'edit' => 'admin.orders.edit',
        'update' => 'admin.orders.update',
        'destroy' => 'admin.orders.destroy'
    ]);
    
    Route::resource('users', \App\Http\Controllers\UserController::class)->names([
        'index' => 'admin.users.index',
        'create' => 'admin.users.create',
        'store' => 'admin.users.store',
        'show' => 'admin.users.show',
        'edit' => 'admin.users.edit',
        'update' => 'admin.users.update',
        'destroy' => 'admin.users.destroy'
    ]);
    
    Route::resource('coupons', CouponController::class)->names([
        'index' => 'admin.coupons.index',
        'create' => 'admin.coupons.create',
        'store' => 'admin.coupons.store',
        'show' => 'admin.coupons.show',
        'edit' => 'admin.coupons.edit',
        'update' => 'admin.coupons.update',
        'destroy' => 'admin.coupons.destroy'
    ]);
    
    Route::resource('reviews', ReviewController::class)->names([
        'index' => 'admin.reviews.index',
        'create' => 'admin.reviews.create',
        'store' => 'admin.reviews.store',
        'show' => 'admin.reviews.show',
        'edit' => 'admin.reviews.edit',
        'update' => 'admin.reviews.update',
        'destroy' => 'admin.reviews.destroy'
    ]);
    
    // Additional review routes
    Route::post('reviews/{review}/approve', [ReviewController::class, 'approve'])->name('admin.reviews.approve');
    Route::post('reviews/{review}/reject', [ReviewController::class, 'reject'])->name('admin.reviews.reject');
    
    // Attributes management
    Route::resource('attributes', \App\Http\Controllers\AttributeController::class)->names([
        'index' => 'admin.attributes.index',
        'create' => 'admin.attributes.create',
        'store' => 'admin.attributes.store',
        'show' => 'admin.attributes.show',
        'edit' => 'admin.attributes.edit',
        'update' => 'admin.attributes.update',
        'destroy' => 'admin.attributes.destroy'
    ]);
    
    // Additional attribute routes
    Route::get('attributes/{attribute}/values', [\App\Http\Controllers\AttributeController::class, 'getValues'])->name('admin.attributes.values');
    Route::post('attributes/{attribute}/values', [\App\Http\Controllers\AttributeController::class, 'storeValue'])->name('admin.attributes.values.store');
    Route::put('attribute-values/{attributeValue}', [\App\Http\Controllers\AttributeController::class, 'updateValue'])->name('admin.attribute-values.update');
    Route::delete('attribute-values/{attributeValue}', [\App\Http\Controllers\AttributeController::class, 'destroyValue'])->name('admin.attribute-values.destroy');
    
// Media library routes
Route::get('media', [\App\Http\Controllers\MediaController::class, 'index'])->name('admin.media.index');
Route::get('media/create', [\App\Http\Controllers\MediaController::class, 'create'])->name('admin.media.create');
    Route::post('media/upload', [\App\Http\Controllers\MediaController::class, 'upload'])->name('admin.media.upload');
    Route::get('media/library', [\App\Http\Controllers\MediaController::class, 'library'])->name('admin.media.library');
    Route::put('media/{media}', [\App\Http\Controllers\MediaController::class, 'update'])->name('admin.media.update');
    Route::delete('media/{media}', [\App\Http\Controllers\MediaController::class, 'destroy'])->name('admin.media.destroy');
    Route::post('media/upload-url', [\App\Http\Controllers\MediaController::class, 'uploadFromUrl'])->name('admin.media.upload-url');

// Blog Categories routes
Route::resource('blog-categories', \App\Http\Controllers\BlogCategoryController::class)->names([
    'index' => 'admin.blog-categories.index',
    'create' => 'admin.blog-categories.create',
    'store' => 'admin.blog-categories.store',
    'show' => 'admin.blog-categories.show',
    'edit' => 'admin.blog-categories.edit',
    'update' => 'admin.blog-categories.update',
    'destroy' => 'admin.blog-categories.destroy',
]);

// Blog routes
Route::resource('blogs', \App\Http\Controllers\BlogController::class)->names([
    'index' => 'admin.blogs.index',
    'create' => 'admin.blogs.create',
    'store' => 'admin.blogs.store',
    'show' => 'admin.blogs.show',
    'edit' => 'admin.blogs.edit',
    'update' => 'admin.blogs.update',
    'destroy' => 'admin.blogs.destroy',
]);
});

// Authentication routes
require __DIR__.'/auth.php';
