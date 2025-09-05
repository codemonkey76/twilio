<?php

namespace App\Services;

use App\Models\User;
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\VoiceGrant;
use Twilio\Rest\Client;

class TwilioService
{
    public string $accountSid;

    public string $authToken;

    public string $appSid;

    public string $phoneNumber;

    public string $apiKey;

    public string $apiSecret;

    public string $region;

    public string $edge;

    public string $webhookUrl;

    public Client $client;

    public function __construct()
    {
        $this->accountSid = config('twilio.account_sid');
        $this->authToken = config('twilio.auth_token');
        $this->appSid = config('twilio.app_sid');
        $this->phoneNumber = config('twilio.phone_number');
        $this->apiKey = config('twilio.api_key');
        $this->apiSecret = config('twilio.api_secret');
        $this->region = config('twilio.region');
        $this->edge = config('twilio.edge');
        $this->webhookUrl = config('twilio.webhook_url');

        if (! $this->accountSid || ! $this->authToken) {
            throw new \Exception('Twilio configuration is incomplete. Please ensure TWILIO_ACCOUNT_SID and TWILIO_AUTH_TOKEN are set in your .env file.');
        }

        if (! $this->appSid) {
            throw new \Exception('Twilio Voice SDK configuration is missing. Please ensure TWILIO_APP_SID is set in your .env file.');
        }

        $this->client = new Client($this->accountSid, $this->authToken, null, $this->region);
        $this->client->setEdge($this->edge);
    }

    public function getToken(User $user): array
    {
        $identity = "user_{$user->id}";
        $expiry = 3600; // 1 hour

        $token = new AccessToken(
            $this->accountSid,
            $this->apiKey,
            $this->apiSecret,
            $expiry,
            $identity,
            $this->region
        );

        $voiceGrant = new VoiceGrant;
        $voiceGrant->setOutgoingApplicationSid($this->appSid);
        $voiceGrant->setIncomingAllow(true);
        $token->addGrant($voiceGrant);

        return [
            'token' => $token->toJwt(),
            'identity' => $identity,
            'expiry' => $expiry,
        ];
    }
}
