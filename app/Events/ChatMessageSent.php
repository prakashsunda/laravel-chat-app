<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Message;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Support\Facades\log;

class ChatMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message->load('sender', 'receiver');
    }

    public function broadcastOn()
    {
        // \Illuminate\Support\Facades\Log::info('Broadcasting message on channel: private-chat.' . $this->message->receiver_id);
        return new PrivateChannel('chat-channel.' . $this->message->receiver_id);
    }

    public function broadcastWith()
    {
        // \Illuminate\Support\Facades\Log::info('Broadcast Data: ', [
        //     'id' => $this->message->id,
        //     'sender_id' => $this->message->sender_id,
        //     'receiver_id' => $this->message->receiver_id,
        //     'message' => $this->message->message,
        // ]);
        return [
            'id' => $this->message->id,
            'sender_id' => $this->message->sender_id,
            'receiver_id' => $this->message->receiver_id,
            'message' => $this->message->message,
            'created_at' => $this->message->created_at->toDateTimeString(),
            'sender' => $this->message->sender ? [
                'id' => $this->message->sender->id,
                'name' => $this->message->sender->name
            ] : null,
            'receiver' => $this->message->receiver ? [
                'id' => $this->message->receiver->id,
                'name' => $this->message->receiver->name
            ] : null
        ];
        
    }
}

