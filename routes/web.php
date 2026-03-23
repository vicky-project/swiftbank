<?php

use Illuminate\Support\Facades\Route;
use Modules\SwiftBank\Http\Controllers\SwiftBankController;

Route::prefix('apps')
->name('apps.')
->middleware(['web', 'telegram.miniapp'])
->group(function () {
  Route::get('', [SwiftBankController::class, 'index'])->names('swift');
  Route::get('/country/{countryCode}', [SwiftBankController::class, 'show'])->name('swift.show');
});