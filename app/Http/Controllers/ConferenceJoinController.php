<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\TwiML\VoiceResponse;

class ConferenceJoinController extends Controller
{
    public function __invoke(Request $request)
    {
        $conferenceName = $request->conference_name;
        /* $to = $request->to; */

        $response = new VoiceResponse;
        $response->say('Joining conference');
        $response->dial()->conference($conferenceName);

        return $response;
    }
    //
}
