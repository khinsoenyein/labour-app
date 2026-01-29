<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CustomerServiceRequestController;
use App\Http\Controllers\Api\StaffServiceRequestController;

Route::middleware(['auth:sanctum'])->group(function () {

    // Customer routes
    Route::middleware('role:customer')->prefix('customer')->group(function () {
        Route::get('service-requests', [CustomerServiceRequestController::class, 'index']);
        Route::post('service-requests', [CustomerServiceRequestController::class, 'store']);
        Route::get('service-requests/{serviceRequest}', [CustomerServiceRequestController::class, 'show']);
    });

    // Staff/Admin routes
    Route::middleware('role:staff,admin')->prefix('staff')->group(function () {
        Route::get('service-requests/pending', [StaffServiceRequestController::class, 'pending']);
        Route::post('service-requests/{serviceRequest}/approve', [StaffServiceRequestController::class, 'approve']);
        Route::post('service-requests/{serviceRequest}/reject', [StaffServiceRequestController::class, 'reject']);
        Route::post('service-requests/{serviceRequest}/request-info', [StaffServiceRequestController::class, 'requestInfo']);
        Route::post('service-requests/{serviceRequest}/change-service-type', [StaffServiceRequestController::class, 'changeServiceType']);
    });

});
