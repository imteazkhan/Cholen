<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Test route
Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});

// Test Admin Routes (no auth required)
Route::prefix('test-admin')->group(function () {
    Route::get('/users', [App\Http\Controllers\Api\TestAdminController::class, 'getAllUsers']);
    Route::get('/drivers', [App\Http\Controllers\Api\TestAdminController::class, 'getAllDrivers']);
    Route::post('/drivers', [App\Http\Controllers\Api\TestAdminController::class, 'createDriver']);
    Route::put('/drivers/{id}/status', [App\Http\Controllers\Api\TestAdminController::class, 'updateDriverStatus']);
    Route::delete('/drivers/{id}', [App\Http\Controllers\Api\TestAdminController::class, 'deleteDriver']);
    Route::delete('/users/{id}', [App\Http\Controllers\Api\TestAdminController::class, 'deleteUser']);
    Route::put('/users/{id}', [App\Http\Controllers\Api\TestAdminController::class, 'updateUser']);
    Route::get('/rides', [App\Http\Controllers\Api\TestAdminController::class, 'getAllRides']);
    Route::put('/rides/{id}/status', [App\Http\Controllers\Api\TestAdminController::class, 'updateRideStatus']);
    Route::get('/analytics', [App\Http\Controllers\Api\TestAdminController::class, 'getDashboardAnalytics']);
    Route::get('/settings', [App\Http\Controllers\Api\TestAdminController::class, 'getSystemSettings']);
    Route::put('/settings', [App\Http\Controllers\Api\TestAdminController::class, 'updateSystemSettings']);
    Route::get('/test', [App\Http\Controllers\Api\TestAdminController::class, 'test']);
});

// Vue Frontend Authentication Routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/users', [AuthController::class, 'getAllUsers']); // Admin only
    });
});

// Admin Routes
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::get('/users', [App\Http\Controllers\Api\AdminController::class, 'getAllUsers']);
    Route::get('/drivers', [App\Http\Controllers\Api\AdminController::class, 'getAllDrivers']);
    Route::post('/drivers', [App\Http\Controllers\Api\AdminController::class, 'createDriver']);
    Route::put('/drivers/{id}/status', [App\Http\Controllers\Api\AdminController::class, 'updateDriverStatus']);
    Route::delete('/drivers/{id}', [App\Http\Controllers\Api\AdminController::class, 'deleteDriver']);
    Route::delete('/users/{id}', [App\Http\Controllers\Api\AdminController::class, 'deleteUser']);
    Route::put('/users/{id}', [App\Http\Controllers\Api\AdminController::class, 'updateUser']);
    Route::get('/analytics', [App\Http\Controllers\Api\AdminController::class, 'getDashboardAnalytics']);
    Route::get('/rides', [App\Http\Controllers\Api\AdminController::class, 'getAllRides']);
    Route::put('/rides/{id}/status', [App\Http\Controllers\Api\AdminController::class, 'updateRideStatus']);
    Route::get('/settings', [App\Http\Controllers\Api\AdminController::class, 'getSystemSettings']);
    Route::put('/settings', [App\Http\Controllers\Api\AdminController::class, 'updateSystemSettings']);
    Route::get('/test', [App\Http\Controllers\Api\AdminController::class, 'test']);
});

// Ride Management Routes
Route::middleware('auth:sanctum')->prefix('rides')->group(function () {
    // User routes
    Route::post('/book', [App\Http\Controllers\Api\RideController::class, 'bookRide']);
    Route::get('/my-rides', [App\Http\Controllers\Api\RideController::class, 'getUserRides']);
    
    // Driver routes
    Route::get('/available', [App\Http\Controllers\Api\RideController::class, 'getAvailableRides']);
    Route::post('/{ride}/accept', [App\Http\Controllers\Api\RideController::class, 'acceptRide']);
    Route::get('/my-driver-rides', [App\Http\Controllers\Api\RideController::class, 'getDriverRides']);
    
    // Common routes
    Route::put('/{ride}/status', [App\Http\Controllers\Api\RideController::class, 'updateRideStatus']);
    Route::delete('/{ride}/cancel', [App\Http\Controllers\Api\RideController::class, 'cancelRide']);
    
    // Payment routes
    Route::post('/{ride}/payment', [App\Http\Controllers\Api\RideController::class, 'processPayment']);
});

// Payment Routes
Route::middleware('auth:sanctum')->prefix('payment')->group(function () {
    Route::post('/initialize', [App\Http\Controllers\Api\PaymentController::class, 'initializePayment']);
    Route::post('/cash', [App\Http\Controllers\Api\PaymentController::class, 'processCashPayment']);
    Route::get('/status/{transaction_id}', [App\Http\Controllers\Api\PaymentController::class, 'getPaymentStatus']);
});

// Payment Callback Routes (No auth required for SSLCommerz callbacks)
Route::prefix('payment')->name('payment.')->group(function () {
    Route::post('/success', [App\Http\Controllers\Api\PaymentController::class, 'paymentSuccess'])->name('success');
    Route::post('/fail', [App\Http\Controllers\Api\PaymentController::class, 'paymentFail'])->name('fail');
    Route::post('/cancel', [App\Http\Controllers\Api\PaymentController::class, 'paymentCancel'])->name('cancel');
    Route::post('/ipn', [App\Http\Controllers\Api\PaymentController::class, 'paymentIPN'])->name('ipn');
});

// Legacy Phone-based Authentication (for mobile app)
Route::get('/login', function () {
    return response()->json(['message' => 'GET login route working']);
});

Route::post('/login', [LoginController::class, 'submit']);
Route::post('/login/verify', [LoginController::class, 'verify']);

Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::get('/driver', [DriverController::class, 'show']);
        Route::post('/driver', [DriverController::class, 'update']);

        Route::get('/user', function(Request $request){
            return $request->user();
        })->middleware('auth:sanctum');
});
