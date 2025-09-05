<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AudioController extends Controller
{
    public function ringback(Request $_request)
    {
        return response()->json([
            'url' => Storage::disk('s3')
                ->temporaryUrl('ringtone/phone-ringtone-penthouse-357397.mp3', now()->addHours(8)),
        ]);

    }

    public function inbound(Request $_request)
    {
        return response()->json([
            'url' => Storage::disk('s3')
                ->temporaryUrl('ringtone/phone-ringtone-ultra-273555.mp3', now()->addHours(8)),
        ]);

    }
}
