<?php

namespace App\Http\Controllers;

use App\Enums\ConferenceDirection;
use App\Events\InboundCall;
use App\Models\Conference;
use App\Models\User;
use Illuminate\Http\Request;
use Twilio\TwiML\VoiceResponse;

class VoiceController extends Controller
{
    public function __invoke(Request $request)
    {
        $call_type = $request->get('call_type');

        return match ($call_type) {
            'conference' => $this->conference($request->conference_name),
            default => $this->dial($request),
        };
    }

    public function dial(Request $request)
    {
        $user = User::first();

        $conferenceName = "inbound-{$user->id}-".uniqid();

        $conference = Conference::create([
            'name' => $conferenceName,
            'to' => $request->Called,
            'from' => $request->Caller,
            'direction' => ConferenceDirection::Inbound,
            'user_id' => $user->id,
        ]);

        InboundCall::dispatch($conference);

        $response = new VoiceResponse;
        $response->say('Connecting to agent, please wait.');
        $response->dial()->conference($conferenceName, $this->getConferenceOptions());

        return $response;
    }

    public function conference(?string $conference_name)
    {
        $response = new VoiceResponse;

        if (! $conference_name) {
            $response->say('Conference name is required');
            $response->hangup();

            return $response;
        }

        $response->say('Joining conference');
        $response->dial()->conference($conference_name, $this->getConferenceOptions());

        return $response;
    }

    private function getConferenceOptions()
    {
        return [
            'startConferenceOnEnter' => false,
            'endConferenceOnExit' => true,
            'statusCallback' => $this->route('api.twilio.conference.status'),
            'statusCallbackMethod' => 'POST',
            'statusCallbackEvent' => 'start end join leave mute hold modify speaker announcement',
            'waitUrl' => 'http://com.twilio.music.classical.s3.amazonaws.com/BusyStrings.mp3',
            'waitMethod' => 'GET',
            'region' => config('twilio.region'),

        ];
    }

    private function route(string $routeName, array $params = [])
    {
        $baseUrl = rtrim(config('twilio.services.webhook_url'), '/');

        $path = ltrim(route($routeName, $params, false), '/');

        return "{$baseUrl}/{$path}";
    }
}
