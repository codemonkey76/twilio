<?php

namespace App\Http\Controllers;

use App\Enums\ConferenceDirection;
use App\Events\InitiateOutboundCall;
use App\Models\Conference;
use App\Models\Did;
use Illuminate\Http\Request;

class CallController extends Controller
{
    public function outbound(Request $request)
    {
        $conferenceName = "outbound-{$request->user()->id}-".uniqid();
        $did = Did::find($request->did_id);

        $conference = Conference::create([
            'name' => $conferenceName,
            'to' => $request->to,
            'from' => $did->number,
            'direction' => ConferenceDirection::Outbound,
            'user_id' => $request->user()->id,
        ]);

        InitiateOutboundCall::dispatch($conference);

        return response()->json(['status' => 'ok']);
    }
}
