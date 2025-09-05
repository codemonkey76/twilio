<?php

namespace App\Http\Controllers;

use App\Events\InitiateOutboundCall;
use Illuminate\Http\Request;

class CallController extends Controller
{
    public function outbound(Request $request)
    {
        InitiateOutboundCall::dispatch($request->user());

        return response()->json(['status' => 'ok']);
    }
}
