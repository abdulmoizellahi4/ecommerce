<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Product;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Check if this is an admin request
        if ($request->routeIs('admin.*')) {
            return $this->adminIndex($request);
        }

        // Regular user reviews
        $reviews = Review::where('user_id', auth()->id())
                        ->with(['product'])
                        ->latest()
                        ->paginate(10);

        return view('reviews.index', compact('reviews'));
    }

    /**
     * Admin index method
     */
    private function adminIndex(Request $request)
    {
        $query = Review::with(['user', 'product.category']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('comment', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function($userQuery) use ($request) {
                      $userQuery->where('name', 'like', '%' . $request->search . '%');
                  })
                  ->orWhereHas('product', function($productQuery) use ($request) {
                      $productQuery->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        // Filter by rating
        if ($request->has('rating') && $request->rating) {
            $query->where('rating', $request->rating);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $reviews = $query->latest()->paginate(15);

        return view('admin.reviews.index', compact('reviews'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Reviews are typically created from product pages
        return redirect()->route('products.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        // Check if user already reviewed this product
        $existingReview = Review::where('user_id', auth()->id())
                               ->where('product_id', $validated['product_id'])
                               ->first();

        if ($existingReview) {
            return back()->with('error', 'You have already reviewed this product.');
        }

        $validated['user_id'] = auth()->id();
        $validated['status'] = 'pending'; // Default status for new reviews
        Review::create($validated);

        return back()->with('success', 'Review submitted successfully! It will be published after approval.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Review $review)
    {
        $review->load(['user', 'product']);
        
        // Check if this is an admin request
        if (request()->routeIs('admin.*')) {
            return view('admin.reviews.show', compact('review'));
        }
        
        return view('reviews.show', compact('review'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Review $review)
    {
        // Check if this is an admin request
        if (request()->routeIs('admin.*')) {
            return view('admin.reviews.edit', compact('review'));
        }

        // Only the review author or admin can edit
        if (!auth()->user()->is_admin && $review->user_id !== auth()->id()) {
            abort(403, 'You can only edit your own reviews.');
        }

        return view('reviews.edit', compact('review'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Review $review)
    {
        // Check if this is an admin request
        if (request()->routeIs('admin.*')) {
            $validated = $request->validate([
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'required|string|max:1000',
                'status' => 'required|in:pending,approved,rejected',
                'admin_response' => 'nullable|string|max:500',
            ]);

            $review->update($validated);

            return redirect()->route('admin.reviews.index')
                            ->with('success', 'Review updated successfully.');
        }

        // Only the review author or admin can update
        if (!auth()->user()->is_admin && $review->user_id !== auth()->id()) {
            abort(403, 'You can only update your own reviews.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        $review->update($validated);

        return redirect()->route('reviews.show', $review)
                        ->with('success', 'Review updated successfully.');
    }

    /**
     * Approve a review (Admin only)
     */
    public function approve(Review $review)
    {
        $review->update(['status' => 'approved']);

        return redirect()->route('admin.reviews.index')
                        ->with('success', 'Review approved successfully.');
    }

    /**
     * Reject a review (Admin only)
     */
    public function reject(Review $review)
    {
        $review->update(['status' => 'rejected']);

        return redirect()->route('admin.reviews.index')
                        ->with('success', 'Review rejected successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Review $review)
    {
        // Only the review author or admin can delete
        if (!auth()->user()->is_admin && $review->user_id !== auth()->id()) {
            abort(403, 'You can only delete your own reviews.');
        }

        $review->delete();

        // Check if this is an admin request
        if (request()->routeIs('admin.*')) {
            return redirect()->route('admin.reviews.index')
                            ->with('success', 'Review deleted successfully.');
        }

        return redirect()->route('reviews.index')
                        ->with('success', 'Review deleted successfully.');
    }
}