<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketsUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public string $message = 'Tickets updated') {}

    public function broadcastOn(): array
    {
        return [new Channel('tickets')];
    }

    public function broadcastAs(): string
    {
        return 'TicketsUpdated';
    }

    public function broadcastWith(): array
    {
        return ['message' => $this->message];
    }
}
