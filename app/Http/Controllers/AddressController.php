<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Address;

class AddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $addresses = Address::where('user_id', auth()->id())
                           ->latest()
                           ->get();

        return view('addresses.index', compact('addresses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('addresses.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:home,work,other',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'is_default' => 'boolean',
        ]);

        $validated['user_id'] = auth()->id();

        // If this is set as default, unset other defaults
        if ($validated['is_default']) {
            Address::where('user_id', auth()->id())
                   ->update(['is_default' => false]);
        }

        Address::create($validated);

        return redirect()->route('addresses.index')
                        ->with('success', 'Address added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Address $address)
    {
        // Ensure user can only view their own addresses
        if ($address->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this address.');
        }

        return view('addresses.show', compact('address'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Address $address)
    {
        // Ensure user can only edit their own addresses
        if ($address->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this address.');
        }

        return view('addresses.edit', compact('address'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Address $address)
    {
        // Ensure user can only update their own addresses
        if ($address->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this address.');
        }

        $validated = $request->validate([
            'type' => 'required|in:home,work,other',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'is_default' => 'boolean',
        ]);

        // If this is set as default, unset other defaults
        if ($validated['is_default']) {
            Address::where('user_id', auth()->id())
                   ->where('id', '!=', $address->id)
                   ->update(['is_default' => false]);
        }

        $address->update($validated);

        return redirect()->route('addresses.index')
                        ->with('success', 'Address updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Address $address)
    {
        // Ensure user can only delete their own addresses
        if ($address->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this address.');
        }

        $address->delete();

        return redirect()->route('addresses.index')
                        ->with('success', 'Address deleted successfully.');
    }
}