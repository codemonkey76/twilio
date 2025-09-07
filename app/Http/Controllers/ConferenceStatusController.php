<?php

namespace App\Http\Controllers;

use App\Models\Conference;
use App\Services\TwilioService;
use Illuminate\Http\Request;

class ConferenceStatusController extends Controller
{
    public function __construct(protected TwilioService $twilio) {}

    public function __invoke(Request $request)
    {
        return match ($request->StatusCallbackEvent) {
            'participant-join' => $this->participantJoin($request),
            'conference-end' => $this->conferenceEnd($request),
            'participant-leave' => $this->participantLeave($request),
            default => response()->json(['status' => 'ok']),
        };
    }

    private function participantJoin(Request $request)
    {
        $participantNumber = (int) $request->SequenceNumber;
        $conference = Conference::whereName($request->FriendlyName)->first();

        if ($conference && $participantNumber === 1) {
            $this->twilio->dialSecondLeg($conference);
        }

        return response()->json([
            'status' => 'ok',
        ]);

    }

    private function conferenceEnd(Request $request)
    {
        return response()->json(['status' => 'ok']);
    }

    private function participantLeave(Request $request)
    {
        return response()->json(['status' => 'ok']);
    }
}
