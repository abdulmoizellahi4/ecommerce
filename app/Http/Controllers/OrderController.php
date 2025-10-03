<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;

class OrderController extends Controller
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

        // Regular user orders
        $orders = Order::where('user_id', auth()->id())
                      ->with(['orderItems.product'])
                      ->latest()
                      ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    /**
     * Admin index method
     */
    private function adminIndex(Request $request)
    {
        $query = Order::with(['user', 'orderItems.product']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('order_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function($userQuery) use ($request) {
                      $userQuery->where('name', 'like', '%' . $request->search . '%')
                               ->orWhere('email', 'like', '%' . $request->search . '%');
                  });
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(15);

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // This would typically be handled by checkout process
        return redirect()->route('cart.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // This would typically be handled by checkout process
        return redirect()->route('cart.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        // Check if user can view this order
        if (auth()->user()->is_admin || $order->user_id === auth()->id()) {
            $order->load(['user', 'orderItems.product']);
            return view('orders.show', compact('order'));
        }

        abort(403, 'Unauthorized access to this order.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        // Only admins can edit orders
        if (!auth()->user()->is_admin) {
            abort(403, 'Only administrators can edit orders.');
        }

        $order->load(['user', 'orderItems.product']);
        return view('admin.orders.edit', compact('order'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        // Only admins can update orders
        if (!auth()->user()->is_admin) {
            abort(403, 'Only administrators can update orders.');
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            'notes' => 'nullable|string|max:1000',
        ]);

        $order->update($validated);

        return redirect()->route('admin.orders.index')
                        ->with('success', 'Order updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        // Only admins can delete orders
        if (!auth()->user()->is_admin) {
            abort(403, 'Only administrators can delete orders.');
        }

        $order->delete();

        return redirect()->route('admin.orders.index')
                        ->with('success', 'Order deleted successfully.');
    }
}