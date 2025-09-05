<?php

namespace App\Http\Controllers;

use App\Services\TwilioService;
use Illuminate\Http\Request;

class TwilioController extends Controller
{
    public function __construct(protected TwilioService $twilio) {}

    public function token(Request $request)
    {
        $result = $this->twilio->getToken($request->user());

        return response()->json($result);
    }
}
