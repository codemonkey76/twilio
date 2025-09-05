<?php

use App\Http\Controllers\AudioController;
use Illuminate\Support\Facades\Route;

Route::get('/api/audio/inbound', [AudioController::class, 'inbound'])->name('api.audio.inbound');
Route::get('/api/audio/ringback', [AudioController::class, 'ringback'])->name('api.audio.ringback');
