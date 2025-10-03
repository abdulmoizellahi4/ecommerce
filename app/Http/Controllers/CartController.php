<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;

class CartController extends Controller
{
    /**
     * Display the cart
     */
    public function index()
    {
        $cartItems = Cart::where('user_id', auth()->id())
                        ->orWhere('session_id', session()->getId())
                        ->with('product')
                        ->get();

        $total = $cartItems->sum(function($item) {
            return $item->quantity * $item->price;
        });

        return view('cart.index', compact('cartItems', 'total'));
    }

    /**
     * Add item to cart
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        // Check if item already exists in cart
        $cartItem = Cart::where('product_id', $product->id)
                       ->where(function($query) {
                           if (auth()->check()) {
                               $query->where('user_id', auth()->id());
                           } else {
                               $query->where('session_id', session()->getId());
                           }
                       })
                       ->first();

        if ($cartItem) {
            $cartItem->quantity += $request->quantity;
            $cartItem->save();
        } else {
            Cart::create([
                'user_id' => auth()->id(),
                'session_id' => auth()->check() ? null : session()->getId(),
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'price' => $product->current_price,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart successfully!',
            'cart_count' => $this->getCartCount()
        ]);
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = Cart::where('id', $id)
                       ->where(function($query) {
                           if (auth()->check()) {
                               $query->where('user_id', auth()->id());
                           } else {
                               $query->where('session_id', session()->getId());
                           }
                       })
                       ->firstOrFail();

        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        return response()->json([
            'success' => true,
            'message' => 'Cart updated successfully!',
            'cart_count' => $this->getCartCount()
        ]);
    }

    /**
     * Remove item from cart
     */
    public function remove($id)
    {
        $cartItem = Cart::where('id', $id)
                       ->where(function($query) {
                           if (auth()->check()) {
                               $query->where('user_id', auth()->id());
                           } else {
                               $query->where('session_id', session()->getId());
                           }
                       })
                       ->firstOrFail();

        $cartItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart!',
            'cart_count' => $this->getCartCount()
        ]);
    }

    /**
     * Get cart count
     */
    public function count()
    {
        return response()->json([
            'count' => $this->getCartCount()
        ]);
    }

    /**
     * Get cart count for current user/session
     */
    private function getCartCount()
    {
        return Cart::where(function($query) {
            if (auth()->check()) {
                $query->where('user_id', auth()->id());
            } else {
                $query->where('session_id', session()->getId());
            }
        })->sum('quantity');
    }
}
