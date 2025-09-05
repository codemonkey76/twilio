<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InitiateOutboundCall implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public User $user) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("calls.{$this->user->id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'outbound.call';
    }

    public function broadcastWith(): array
    {
        return [
            'conference' => [
                'name' => 'outbound-'.uniqid(),
                'from' => '+61 123 456 789',
                'to' => '0400 588 588',
            ],
        ];
    }
}
