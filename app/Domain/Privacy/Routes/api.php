<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Privacy\DelegatedProofController;
use App\Http\Controllers\Api\Privacy\PrivacyController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/privacy')->name('api.privacy.')->group(function () {
    // Public endpoint for supported networks
    Route::get('/networks', [PrivacyController::class, 'getNetworks'])->name('networks');

    // Public endpoint for delegated proof types
    Route::get('/delegated-proof-types', [DelegatedProofController::class, 'getSupportedTypes'])
        ->name('delegated-proof-types');

    // Public endpoint for SRS manifest (mobile needs this before auth)
    Route::get('/srs-manifest', [PrivacyController::class, 'getSrsManifest'])->name('srs-manifest');

    // Public endpoint for privacy pool statistics (v3.3.4)
    Route::get('/pool-stats', [PrivacyController::class, 'getPoolStats'])->name('pool-stats');

    // Authenticated endpoints
    Route::middleware(['auth:sanctum', 'check.token.expiration', 'throttle:60,1'])->group(function () {
        Route::get('/merkle-root', [PrivacyController::class, 'getMerkleRoot'])->name('merkle-root');
        Route::post('/merkle-path', [PrivacyController::class, 'getMerklePath'])->middleware('throttle:10,1')->name('merkle-path');
        Route::post('/verify-commitment', [PrivacyController::class, 'verifyCommitment'])->middleware('throttle:10,1')->name('verify-commitment');
        Route::post('/sync', [PrivacyController::class, 'syncTree'])
            ->middleware('transaction.rate_limit:privacy_sync')
            ->name('sync');

        // Delegated Proof Generation (v2.6.0)
        Route::post('/delegated-proof', [DelegatedProofController::class, 'requestProof'])
            ->middleware('transaction.rate_limit:delegated_proof')
            ->name('delegated-proof.request');
        Route::get('/delegated-proof/{jobId}', [DelegatedProofController::class, 'getProofStatus'])
            ->name('delegated-proof.status');
        Route::get('/delegated-proofs', [DelegatedProofController::class, 'listProofJobs'])
            ->name('delegated-proofs.list');
        Route::delete('/delegated-proof/{jobId}', [DelegatedProofController::class, 'cancelProofJob'])
            ->name('delegated-proof.cancel');

        // SRS download tracking for analytics
        Route::post('/srs-downloaded', [PrivacyController::class, 'trackSrsDownload'])->name('srs-downloaded');
    });
});
