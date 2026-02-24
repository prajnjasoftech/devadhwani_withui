<?php

use App\Http\Controllers\Api\AuthController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DevoteeController;
use App\Http\Controllers\Api\DonationController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\PanchangController;
use App\Http\Controllers\Api\PendingPoojaController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\TempleController;
use App\Http\Controllers\Api\TemplePoojaBookingController;
use App\Http\Controllers\Api\TemplePoojaController;
use App\Http\Controllers\Api\TempleReportController;
use App\Http\Controllers\Api\Tenant\DonationController as TenantDonationController;
use App\Http\Controllers\Api\Tenant\EventController as TenantEventController;
use App\Http\Controllers\Api\UsageController;
use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return response()->json(['message' => 'API is working!']);
});
// Panchang
Route::get('/panchang/listDate/{date}', [PanchangController::class, 'listDate']);
Route::get('/panchang/list/{id}', [PanchangController::class, 'list']);
Route::get('/panchang/list', [PanchangController::class, 'list']);
Route::get('/panchang', [PanchangController::class, 'show']);
Route::get('/panchangYear', [PanchangController::class, 'yearlyRecord']);
// Authentication routes with rate limiting
Route::middleware('throttle:otp')->post('/send-otp', [AuthController::class, 'sendOtp']);
Route::middleware('throttle:otp-verify')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/temples', [TempleController::class, 'index']);
    Route::post('/temples/{id}', [TempleController::class, 'update']);

    Route::post('/logout', [AuthController::class, 'logout']);

    // Donations CRUD
    Route::prefix('donations')->group(function () {
        Route::get('/', [DonationController::class, 'index']);
        Route::post('/', [DonationController::class, 'store']);
        Route::get('/{id}', [DonationController::class, 'show']);
        Route::put('/{id}', [DonationController::class, 'update']);
        Route::delete('/{id}', [DonationController::class, 'destroy']);
        Route::get('/trashed/list', [DonationController::class, 'trashed']);
        Route::post('/{id}/restore', [DonationController::class, 'restore']);
        Route::delete('/{id}/force-delete', [DonationController::class, 'forceDelete']);
    });

    // Events CRUD
    Route::prefix('events')->group(function () {
        Route::get('/', [EventController::class, 'index']);
        Route::post('/', [EventController::class, 'store']);
        Route::get('/{id}', [EventController::class, 'show']);
        Route::put('/{id}', [EventController::class, 'update']);
        Route::delete('/{id}', [EventController::class, 'destroy']);
        Route::get('/trashed/list', [EventController::class, 'trashed']);
        Route::post('/{id}/restore', [EventController::class, 'restore']);
        Route::delete('/{id}/force-delete', [EventController::class, 'forceDelete']);
    });

    Route::get('/roles', [RoleController::class, 'index']);
    Route::post('/roles', [RoleController::class, 'store']);
    Route::get('/roles/{id}', [RoleController::class, 'show']);
    Route::put('/roles/{id}', [RoleController::class, 'update']);
    Route::delete('/roles/{id}', [RoleController::class, 'destroy']);

    // soft delete helpers
    Route::get('/roles-trashed', [RoleController::class, 'trashed']);
    Route::post('/roles/{id}/restore', [RoleController::class, 'restore']);
    Route::delete('/roles/{id}/force-delete', [RoleController::class, 'forceDelete']);

    Route::get('/members', [MemberController::class, 'index']);
    Route::post('/members', [MemberController::class, 'store']);
    Route::get('/members/{id}', [MemberController::class, 'show']);
    Route::put('/members/{id}', [MemberController::class, 'update']);
    Route::delete('/members/{id}', [MemberController::class, 'destroy']);
    Route::get('/members-trashed', [MemberController::class, 'trashed']);
    Route::post('/members/{id}/restore', [MemberController::class, 'restore']);
    Route::delete('/members/{id}/force-delete', [MemberController::class, 'forceDelete']);

    Route::prefix('devotees')->group(function () {
        Route::get('/', [DevoteeController::class, 'index']);
        Route::post('/', [DevoteeController::class, 'store']);
        Route::get('/{id}', [DevoteeController::class, 'show']);
        Route::put('/{id}', [DevoteeController::class, 'update']);
        Route::delete('/{id}', [DevoteeController::class, 'destroy']);
        Route::post('/{id}/restore', [DevoteeController::class, 'restore']);
        Route::delete('/{id}/force', [DevoteeController::class, 'forceDelete']);
    });

    Route::prefix('bookings')->group(function () {
        Route::get('/', [TemplePoojaBookingController::class, 'index']);
        Route::post('/', [TemplePoojaBookingController::class, 'store']);
        Route::get('/{id}', [TemplePoojaBookingController::class, 'show']);
        Route::put('/{id}', [TemplePoojaBookingController::class, 'update']);
        Route::delete('/{id}', [TemplePoojaBookingController::class, 'destroy']);
        Route::get('/trashed', [TemplePoojaBookingController::class, 'trashed']);
        Route::post('/{id}/restore', [TemplePoojaBookingController::class, 'restore']);
        Route::delete('/{id}/force-delete', [TemplePoojaBookingController::class, 'forceDelete']);
    });
    Route::prefix('transaction-summary')->group(function () {
        Route::get('/', [TempleReportController::class, 'index']);
    });

    Route::get('/pending-pooja-summary', [PendingPoojaController::class, 'index']);

    Route::prefix('temple-poojas')->group(function () {
        Route::get('/', [TemplePoojaController::class, 'index']);
        Route::post('/', [TemplePoojaController::class, 'store']);
        Route::get('/{id}', [TemplePoojaController::class, 'show']);
        Route::put('/{id}', [TemplePoojaController::class, 'update']);
        Route::delete('/{id}', [TemplePoojaController::class, 'destroy']);

        // Restore & Force Delete
        Route::post('/{id}/restore', [TemplePoojaController::class, 'restore']);
        Route::delete('/{id}/force', [TemplePoojaController::class, 'forceDelete']);
        Route::get('/trashed', [TemplePoojaController::class, 'trashed']);
    });

    Route::prefix('purchases')->group(function () {
        Route::get('/', [PurchaseController::class, 'index']);
        Route::post('/', [PurchaseController::class, 'store']);
        Route::get('/{id}', [PurchaseController::class, 'show']);
        Route::put('/{id}', [PurchaseController::class, 'update']);
        Route::delete('/{id}', [PurchaseController::class, 'destroy']);
        Route::get('/trashed/list', [PurchaseController::class, 'trashed']);
        Route::post('/{id}/restore', [PurchaseController::class, 'restore']);
        Route::delete('/{id}/force-delete', [PurchaseController::class, 'forceDelete']);
    });

    Route::prefix('suppliers')->group(function () {
        Route::get('/', [SupplierController::class, 'index']);
        Route::post('/', [SupplierController::class, 'store']);
        Route::get('/{id}', [SupplierController::class, 'show']);
        Route::put('/{id}', [SupplierController::class, 'update']);
        Route::delete('/{id}', [SupplierController::class, 'destroy']);
        Route::get('/trashed/list', [SupplierController::class, 'trashed']);
        Route::post('/{id}/restore', [SupplierController::class, 'restore']);
        Route::delete('/{id}/force-delete', [SupplierController::class, 'forceDelete']);
    });

    Route::prefix('items')->group(function () {
        Route::get('/', [ItemController::class, 'index']);
        Route::post('/', [ItemController::class, 'store']);
        Route::get('/{id}', [ItemController::class, 'show']);
        Route::put('/{id}', [ItemController::class, 'update']);
        Route::delete('/{id}', [ItemController::class, 'destroy']);
        Route::get('/trashed/list', [ItemController::class, 'trashed']);
        Route::post('/{id}/restore', [ItemController::class, 'restore']);
        Route::delete('/{id}/force-delete', [ItemController::class, 'forceDelete']);
    });

    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::post('/', [CategoryController::class, 'store']);
        Route::get('/{id}', [CategoryController::class, 'show']);
        Route::put('/{id}', [CategoryController::class, 'update']);
        Route::delete('/{id}', [CategoryController::class, 'destroy']);
        Route::get('/trashed/list', [CategoryController::class, 'trashed']);
        Route::post('/{id}/restore', [CategoryController::class, 'restore']);
        Route::delete('/{id}/force-delete', [CategoryController::class, 'forceDelete']);
    });

    // Usages CRUD
    Route::prefix('usages')->group(function () {
        Route::get('/', [UsageController::class, 'index']);
        Route::post('/', [UsageController::class, 'store']);
        Route::get('/{id}', [UsageController::class, 'show']);
        Route::put('/{id}', [UsageController::class, 'update']);
        Route::delete('/{id}', [UsageController::class, 'destroy']);

        Route::get('/trashed/list', [UsageController::class, 'trashed']);
        Route::post('/{id}/restore', [UsageController::class, 'restore']);
        Route::delete('/{id}/force-delete', [UsageController::class, 'forceDelete']);
    });
});

Route::middleware(['auth:sanctum', 'temple.db'])->prefix('{temple}')->group(function () {
    Route::get('/tenant-members', [MemberController::class, 'index']);
    Route::post('/tenant-members', [MemberController::class, 'store']);
    Route::get('/tenant-members/{id}', [MemberController::class, 'show']);
    Route::put('/tenant-members/{id}', [MemberController::class, 'update']);
    Route::delete('/tenant-members/{id}', [MemberController::class, 'destroy']);
    Route::get('/tenant-members-trashed', [MemberController::class, 'trashed']);
    Route::post('/tenant-members/{id}/restore', [MemberController::class, 'restore']);
    Route::delete('/tenant-members/{id}/force-delete', [MemberController::class, 'forceDelete']);
    Route::get('/donations', [TenantDonationController::class, 'index']);
    Route::post('/donations', [TenantDonationController::class, 'store']);

    Route::get('/events', [TenantEventController::class, 'index']);
    Route::post('/events', [TenantEventController::class, 'store']);

    Route::post('/logout', [AuthController::class, 'logout']);
});
