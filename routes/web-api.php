<?php

use App\Http\Controllers\AudioController;
use App\Http\Controllers\CallController;
use App\Http\Resources\DidResource;
use App\Models\Did;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {

    Route::get('/api/audio/inbound', [AudioController::class, 'inbound'])->name('api.audio.inbound');
    Route::get('/api/audio/ringback', [AudioController::class, 'ringback'])->name('api.audio.ringback');
    Route::get('/dids', function () {
        $dids = DidResource::collection(Did::all());

        return response()->json([
            'dids' => $dids,
        ]);
    })->name('dids.index');

    Route::post('/api/calls/outbound', [CallController::class, 'outbound'])->name('api.calls.outbound.initiate');
});
