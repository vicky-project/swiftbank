<?php

use Illuminate\Support\Facades\Route;
use Modules\SwiftBank\Http\Controllers\SwiftBankController;

Route::middleware(['auth:sanctum'])->prefix('swift')->name('swift.')->group(function () {
  Route::get('countries', [SwiftBankController::class, 'countries'])->name('countries');
  Route::get('banks/{countryCode}', [SwiftBankController::class, 'banks'])->name('banks');
});