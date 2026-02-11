<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ModuleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Module Management API Routes
|--------------------------------------------------------------------------
|
| Routes for the FinAegis module management system.
| Provides endpoints for listing, inspecting, enabling/disabling,
| and verifying health of domain modules.
|
*/

Route::prefix('v2/modules')->name('api.modules.')->group(function () {
    Route::middleware(['auth:sanctum', 'check.token.expiration'])->group(function () {
        Route::get('/', [ModuleController::class, 'index'])->name('index');
        Route::get('/health', [ModuleController::class, 'health'])->name('health');
        Route::get('/{name}', [ModuleController::class, 'show'])->name('show');

        Route::middleware('is_admin')->group(function () {
            Route::post('/{name}/enable', [ModuleController::class, 'enable'])->name('enable');
            Route::post('/{name}/disable', [ModuleController::class, 'disable'])->name('disable');
            Route::post('/{name}/verify', [ModuleController::class, 'verify'])->name('verify');
        });
    });
});
