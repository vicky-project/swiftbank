<?php

use Illuminate\Support\Facades\Route;
use Modules\SwiftBank\Http\Controllers\SwiftBankController;

Route::prefix('apps')
->name('apps.')
->group(function () {
  Route::get('swift', [SwiftBankController::class, 'index'])->name('swift');
});