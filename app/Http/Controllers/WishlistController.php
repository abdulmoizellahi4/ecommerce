<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wishlist;
use App\Models\Product;

class WishlistController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $wishlistItems = Wishlist::where('user_id', auth()->id())
                                 ->with('product.category')
                                 ->latest()
                                 ->get();

        return view('wishlist.index', compact('wishlistItems'));
    }

    /**
     * Add product to wishlist
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $product = Product::findOrFail($request->product_id);

        // Check if already in wishlist
        $existingItem = Wishlist::where('user_id', auth()->id())
                               ->where('product_id', $product->id)
                               ->first();

        if ($existingItem) {
            return response()->json([
                'success' => false,
                'message' => 'Product is already in your wishlist.'
            ]);
        }

        Wishlist::create([
            'user_id' => auth()->id(),
            'product_id' => $product->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product added to wishlist successfully!'
        ]);
    }

    /**
     * Remove product from wishlist
     */
    public function remove($id)
    {
        $wishlistItem = Wishlist::where('id', $id)
                               ->where('user_id', auth()->id())
                               ->firstOrFail();

        $wishlistItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product removed from wishlist!'
        ]);
    }
}