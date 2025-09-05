<?php

use App\Http\Controllers\TwilioController;
use Illuminate\Support\Facades\Route;

Route::get('/twilio/token', [TwilioController::class, 'token'])->name('api.twilio.token');
/**/
/* Route::post('audio/inbound', [TwilioController::class, 'inbound']) */
/*     ->name('api.audio.inbound'); */
/**/
/* Route::post('audio/ringback', [TwilioController::class, 'ringback']) */
/*     ->name('api.audio.ringback'); */
/**/
/* Route::post('calls/outbound/initiate', [TwilioController::class, 'initiateOutbound']) */
/*     ->name('api.calls.outbound.initiate'); */
