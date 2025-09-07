<?php

use App\Http\Controllers\CallStatusController;
use App\Http\Controllers\ConferenceJoinController;
use App\Http\Controllers\ConferenceStatusController;
use App\Http\Controllers\TwilioController;
use App\Http\Controllers\VoiceController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/twilio/token', [TwilioController::class, 'token'])->name('api.twilio.token');
});

Route::post('/api/twilio/voice', VoiceController::class)->name('api.twilio.voice');
Route::post('/api/twilio/call/status', CallStatusController::class)->name('api.twilio.call.status');
Route::post('/api/twilio/conference/status', ConferenceStatusController::class)->name('api.twilio.conference.status');
Route::post('/api/twilio/conference/join', ConferenceJoinController::class)->name('api.twilio.conference.join');
/**/
/* Route::post('audio/inbound', [TwilioController::class, 'inbound']) */
/*     ->name('api.audio.inbound'); */
/**/
/* Route::post('audio/ringback', [TwilioController::class, 'ringback']) */
/*     ->name('api.audio.ringback'); */
/**/
/* Route::post('calls/outbound/initiate', [TwilioController::class, 'initiateOutbound']) */
/*     ->name('api.calls.outbound.initiate'); */
