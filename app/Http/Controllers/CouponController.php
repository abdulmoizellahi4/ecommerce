<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Coupon;

class CouponController extends Controller
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

        // Regular users don't need to see all coupons
        return redirect()->route('home');
    }

    /**
     * Admin index method
     */
    private function adminIndex(Request $request)
    {
        $query = Coupon::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('code', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            if ($request->status === 'active') {
                $query->where('is_active', true)
                      ->where(function($q) {
                          $q->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                      });
            } elseif ($request->status === 'expired') {
                $query->where('expires_at', '<', now());
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $coupons = $query->latest()->paginate(15);

        return view('admin.coupons.index', compact('coupons'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.coupons.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons',
            'description' => 'nullable|string|max:255',
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0',
            'minimum_amount' => 'nullable|numeric|min:0',
            'maximum_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date|after:now',
            'is_active' => 'boolean',
        ]);

        Coupon::create($validated);

        return redirect()->route('admin.coupons.index')
                        ->with('success', 'Coupon created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Coupon $coupon)
    {
        return view('admin.coupons.show', compact('coupon'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Coupon $coupon)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code,' . $coupon->id,
            'description' => 'nullable|string|max:255',
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0',
            'minimum_amount' => 'nullable|numeric|min:0',
            'maximum_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        $coupon->update($validated);

        return redirect()->route('admin.coupons.index')
                        ->with('success', 'Coupon updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return redirect()->route('admin.coupons.index')
                        ->with('success', 'Coupon deleted successfully.');
    }

    /**
     * Validate coupon code
     */
    public function validate(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);

        $coupon = Coupon::where('code', $request->code)
                        ->where('is_active', true)
                        ->where(function($q) {
                            $q->whereNull('expires_at')
                              ->orWhere('expires_at', '>', now());
                        })
                        ->first();

        if (!$coupon) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid or expired coupon code.'
            ]);
        }

        // Check minimum amount
        if ($coupon->minimum_amount && $request->amount < $coupon->minimum_amount) {
            return response()->json([
                'valid' => false,
                'message' => 'Minimum order amount not met.'
            ]);
        }

        // Check usage limit
        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            return response()->json([
                'valid' => false,
                'message' => 'Coupon usage limit exceeded.'
            ]);
        }

        // Calculate discount
        $discount = $coupon->type === 'percentage' 
            ? ($request->amount * $coupon->value / 100)
            : $coupon->value;

        // Apply maximum discount limit
        if ($coupon->maximum_discount && $discount > $coupon->maximum_discount) {
            $discount = $coupon->maximum_discount;
        }

        return response()->json([
            'valid' => true,
            'discount' => $discount,
            'coupon' => $coupon
        ]);
    }
}