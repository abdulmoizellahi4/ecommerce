<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

// Authentication Routes
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();
        return redirect()->intended(route('home'));
    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ])->onlyInput('email');
});

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::post('/register', function (Request $request) {
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'phone' => 'nullable|string|max:20',
        'password' => 'required|string|min:8|confirmed',
        'terms' => 'required|accepted',
    ]);

    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'phone' => $validated['phone'],
        'password' => Hash::make($validated['password']),
        'email_verified_at' => now(),
    ]);

    Auth::login($user);

    return redirect()->route('home');
});

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('home');
})->name('logout');

Route::get('/password/reset', function () {
    return view('auth.forgot-password');
})->name('password.request');

Route::post('/password/email', function () {
    // Password reset logic will be implemented
    return redirect()->back();
})->name('password.email');

Route::get('/password/reset/{token}', function () {
    return view('auth.reset-password');
})->name('password.reset');

Route::post('/password/reset', function () {
    // Password reset logic will be implemented
    return redirect()->route('login');
})->name('password.update');

// Profile Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', function () {
        return view('profile.edit');
    })->name('profile.edit');

    Route::put('/profile', function (Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . auth()->id(),
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
        ]);

        auth()->user()->update($validated);

        return redirect()->route('profile.edit')->with('success', 'Profile updated successfully!');
    })->name('profile.update');

    Route::put('/password', function (Request $request) {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($validated['current_password'], auth()->user()->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        auth()->user()->update([
            'password' => Hash::make($validated['password'])
        ]);

        return redirect()->route('profile.edit')->with('success', 'Password updated successfully!');
    })->name('password.update');
});
