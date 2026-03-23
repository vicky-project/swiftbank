<?php

use Illuminate\Support\Facades\Route;
use Modules\SwiftBank\Http\Controllers\SwiftBankController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('swiftbanks', SwiftBankController::class)->names('swiftbank');
});
