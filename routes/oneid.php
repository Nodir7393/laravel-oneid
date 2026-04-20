<?php

use Illuminate\Support\Facades\Route;
use Nodir\OneId\Http\Controllers\OneIdController;

$prefix = config('oneid.routes.prefix', 'api');
$middleware = config('oneid.routes.middleware', ['web']);

// OneID auth routes (web middleware — session kerak)
Route::middleware($middleware)->prefix($prefix)->group(function () {
    Route::get('auth/oneid/url',      [OneIdController::class, 'url']);
    Route::get('auth/oneid/redirect', [OneIdController::class, 'redirect'])->name('oneid.redirect');
    Route::get('auth/oneid/callback', [OneIdController::class, 'callback'])->name('oneid.callback');
    Route::get('auth/status',         [OneIdController::class, 'status']);

    // Backward compatibility (egov prefix ham ishlaydi)
    Route::get('auth/egov/url',      [OneIdController::class, 'url']);
    Route::get('auth/egov/redirect', [OneIdController::class, 'redirect'])->name('egov.redirect');
    Route::get('auth/egov/callback', [OneIdController::class, 'callback'])->name('egov.callback');
});

// JWT himoyalangan routes
Route::middleware(['jwt.auth'])->prefix($prefix)->group(function () {
    Route::get('me',            [OneIdController::class, 'me']);
    Route::post('auth/logout',  [OneIdController::class, 'logout']);
});
