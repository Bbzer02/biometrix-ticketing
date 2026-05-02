<?php

namespace App\Events;

use App\Models\HelpMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HelpMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $payload;

    public function __construct(HelpMessage $message)
    {
        $this->payload = [
            'id'          => $message->id,
            'body'        => $message->body,
            'sender_id'   => $message->sender_id,
            'sender'      => $message->sender?->name ?? 'Admin',
            'created_at'  => $message->created_at?->format('M j, Y g:i A'),
            'recipient_id'=> $message->recipient_id,
        ];
    }

    /**
     * Broadcast on IT staff channel, and on recipient's private channel if targeted.
     */
    public function broadcastOn(): array|Channel
    {
        $channels = [new PrivateChannel('it-staff')];

        // Also notify the specific recipient if set
        if ($this->payload['recipient_id']) {
            $channels[] = new PrivateChannel('user.' . $this->payload['recipient_id']);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'HelpMessageSent';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
