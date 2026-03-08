<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\BookingController;
use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DeityController;
use App\Http\Controllers\Web\DevoteeController;
use App\Http\Controllers\Web\ItemController;
use App\Http\Controllers\Web\MemberController;
use App\Http\Controllers\Web\PanchangController;
use App\Http\Controllers\Web\PoojaController;
use App\Http\Controllers\Web\PurchaseController;
use App\Http\Controllers\Web\RoleController;
use App\Http\Controllers\Web\SupplierController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/', function () {
        return redirect('/login');
    });
});

// Authenticated routes
Route::middleware(['auth:web'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Temple Profile
    Route::get('/temple/profile', [DashboardController::class, 'templeProfile'])->name('temple.profile');
    Route::put('/temple/profile', [DashboardController::class, 'updateTempleProfile'])->name('temple.profile.update');

    // Members
    Route::get('/members', [MemberController::class, 'index'])->name('members.index');
    Route::get('/members/create', [MemberController::class, 'create'])->name('members.create');
    Route::post('/members', [MemberController::class, 'store'])->name('members.store');
    Route::get('/members/{id}/edit', [MemberController::class, 'edit'])->name('members.edit');
    Route::put('/members/{id}', [MemberController::class, 'update'])->name('members.update');
    Route::delete('/members/{id}', [MemberController::class, 'destroy'])->name('members.destroy');

    // Roles
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{id}/edit', [RoleController::class, 'edit'])->name('roles.edit');
    Route::put('/roles/{id}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->name('roles.destroy');

    // Devotees
    Route::get('/devotees', [DevoteeController::class, 'index'])->name('devotees.index');
    Route::get('/devotees/create', [DevoteeController::class, 'create'])->name('devotees.create');
    Route::post('/devotees', [DevoteeController::class, 'store'])->name('devotees.store');
    Route::get('/devotees/{id}', [DevoteeController::class, 'show'])->name('devotees.show');
    Route::get('/devotees/{id}/edit', [DevoteeController::class, 'edit'])->name('devotees.edit');
    Route::put('/devotees/{id}', [DevoteeController::class, 'update'])->name('devotees.update');
    Route::delete('/devotees/{id}', [DevoteeController::class, 'destroy'])->name('devotees.destroy');

    // Panchang
    Route::get('/panchang', [PanchangController::class, 'index'])->name('panchang.index');
    Route::post('/panchang/fetch', [PanchangController::class, 'fetch'])->name('panchang.fetch');
    Route::get('/panchang/{date}', [PanchangController::class, 'show'])->name('panchang.show');

    // Poojas
    Route::get('/poojas', [PoojaController::class, 'index'])->name('poojas.index');
    Route::get('/poojas/create', [PoojaController::class, 'create'])->name('poojas.create');
    Route::post('/poojas', [PoojaController::class, 'store'])->name('poojas.store');
    Route::get('/poojas/{id}/edit', [PoojaController::class, 'edit'])->name('poojas.edit');
    Route::put('/poojas/{id}', [PoojaController::class, 'update'])->name('poojas.update');
    Route::delete('/poojas/{id}', [PoojaController::class, 'destroy'])->name('poojas.destroy');

    // Deities
    Route::get('/deities', [DeityController::class, 'index'])->name('deities.index');
    Route::get('/deities/create', [DeityController::class, 'create'])->name('deities.create');
    Route::post('/deities', [DeityController::class, 'store'])->name('deities.store');
    Route::get('/deities/{id}/edit', [DeityController::class, 'edit'])->name('deities.edit');
    Route::put('/deities/{id}', [DeityController::class, 'update'])->name('deities.update');
    Route::delete('/deities/{id}', [DeityController::class, 'destroy'])->name('deities.destroy');

    // Bookings
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{id}', [BookingController::class, 'show'])->name('bookings.show');
    Route::get('/bookings/{id}/edit', [BookingController::class, 'edit'])->name('bookings.edit');
    Route::put('/bookings/{id}', [BookingController::class, 'update'])->name('bookings.update');
    Route::patch('/bookings/{id}/status', [BookingController::class, 'updateStatus'])->name('bookings.status');
    Route::post('/bookings/{id}/payment', [BookingController::class, 'recordPayment'])->name('bookings.payment');
    Route::post('/bookings/{id}/refund', [BookingController::class, 'processRefund'])->name('bookings.refund');
    Route::delete('/bookings/{id}', [BookingController::class, 'destroy'])->name('bookings.destroy');

    // Items
    Route::get('/items', [ItemController::class, 'index'])->name('items.index');
    Route::get('/items/create', [ItemController::class, 'create'])->name('items.create');
    Route::post('/items', [ItemController::class, 'store'])->name('items.store');
    Route::get('/items/{id}/edit', [ItemController::class, 'edit'])->name('items.edit');
    Route::put('/items/{id}', [ItemController::class, 'update'])->name('items.update');
    Route::delete('/items/{id}', [ItemController::class, 'destroy'])->name('items.destroy');

    // Categories
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{id}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Suppliers
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::get('/suppliers/{id}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
    Route::put('/suppliers/{id}', [SupplierController::class, 'update'])->name('suppliers.update');
    Route::delete('/suppliers/{id}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');

    // Purchases
    Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
    Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');
    Route::get('/purchases/{id}/edit', [PurchaseController::class, 'edit'])->name('purchases.edit');
    Route::put('/purchases/{id}', [PurchaseController::class, 'update'])->name('purchases.update');
    Route::delete('/purchases/{id}', [PurchaseController::class, 'destroy'])->name('purchases.destroy');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Fallback for SPA routes (handle client-side routing)
Route::get('/{any}', function () {
    return inertia('Dashboard');
})->where('any', '.*')->middleware('auth:web');
