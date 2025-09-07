<?php

namespace App\Events;

use App\Models\Conference;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InboundCall implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Conference $conference) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("calls.{$this->conference->user_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'inbound.call';
    }
}
