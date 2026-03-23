<?php

use Illuminate\Support\Facades\Route;
use Modules\SwiftBank\Http\Controllers\SwiftBankController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('swiftbanks', SwiftBankController::class)->names('swiftbank');
});
