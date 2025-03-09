<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Message;
use App\Events\ChatMessageSent;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

class Chat extends Component
{
    public $users;
    public $selectedUser;
    public $messages = [];
    public $newMessage = ''; // Initialized as empty string
    public $sender_id;
    public $receiver_id;

    public function mount()
    {
        $this->sender_id = Auth::id();

        $this->users = User::where('id', '!=', Auth::id())
            ->with(['latestMessage' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }])
            ->get();

        $lastMessagedUser = Message::where('sender_id', Auth::id())
            ->orWhere('receiver_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastMessagedUser) {
            $this->selectedUser = User::find(
                $lastMessagedUser->sender_id == Auth::id()
                    ? $lastMessagedUser->receiver_id
                    : $lastMessagedUser->sender_id
            );
        } else {
            $this->selectedUser = $this->users->first();
        }

        if ($this->selectedUser) {
            $this->loadMessages();
        }
    }

    public function selectUser($userId)
    {
        $this->selectedUser = User::find($userId);
        $this->messages = [];
        $this->loadMessages();
        $this->dispatch('scrollToBottom');
    }

    public function loadMessages()
    {
        if ($this->selectedUser) {
            $this->messages = Message::where(function ($query) {
                $query->where('sender_id', Auth::id())
                    ->where('receiver_id', $this->selectedUser->id);
            })
                ->orWhere(function ($query) {
                    $query->where('sender_id', $this->selectedUser->id)
                        ->where('receiver_id', Auth::id());
                })
                ->with('sender:id,name', 'receiver:id,name')
                ->orderBy('created_at')
                ->get();
        }
    }

    public function sendMessage()
    {
        try {
            if (!$this->selectedUser || empty(trim($this->newMessage))) return;

            $message = Message::create([
                'sender_id' => Auth::id(),
                'receiver_id' => $this->selectedUser->id,
                'message' => $this->newMessage
            ]);

            broadcast(new ChatMessageSent($message))->toOthers();

            $this->messages[] = $message;
            $this->newMessage = ''; // Reset the input
            $this->dispatch('scrollToBottom');
            $this->dispatch('messageSent'); // Explicitly dispatch an event to confirm reset

            $this->users = User::where('id', '!=', Auth::id())
                ->with(['latestMessage' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                }])
                ->get();
        } catch (\Exception $e) {
            $this->dispatch('error', ['message' => 'Failed to send message']);
        }
    }

    // #[On('echo-private:chat-channel.{sender_id},ChatMessageSent')]
    // public function listenForMessage($event)
    // {

    //     $newMessage = Message::with('sender')->find($event['id']);
    //     if ($newMessage) {
    //         $this->messages[] = $newMessage;

    //         $this->newMessage = '';
    //     }
    // }

    // protected function getListeners()
    // {
    //     return [
    //         "echo.private-chat." . Auth::id() . ",ChatMessageSent" => 'receiveMessage'
    //     ];
    // }

    // #[On('echo-private:chat-channel.{sender_id},ChatMessageSent')]
    // public function listenForMessage($event)
    // {
    //     $newMessage = Message::with('sender')->find($event['id']);
    //     if ($newMessage && $newMessage->receiver_id == $this->selectedUser->id) {
    //         $this->messages[] = $newMessage;
    //         $this->dispatch('scrollToBottom');
    //     }
    // }
    #[On('echo-private:chat-channel.{sender_id},ChatMessageSent')]
    public function listenForMessage($event)
    {
        $newMessage = Message::with('sender')->find($event['id']);

        // Check if the message is intended for the current user
        if ($newMessage && $newMessage->receiver_id == Auth::id()) {
            // If the sender is not the currently selected user, switch to them
            if (!$this->selectedUser || $this->selectedUser->id != $newMessage->sender_id) {
                $this->selectUser($newMessage->sender_id);
            } else {
                // If the sender is already selected, just append the message
                $this->messages[] = $newMessage;
                $this->dispatch('scrollToBottom');
            }
        }
    }
    // protected function getListeners()
    // {
    //     return [
    //         "echo.private-chat." . Auth::id() . ",ChatMessageSent" => 'receiveMessage'
    //     ];
    // }
    protected function getListeners()
    {
        return [
            "echo-private:chat-channel." . Auth::id() . ",ChatMessageSent" => 'listenForMessage'
        ];
    }

    public function render()
    {
        return view('livewire.chat');
    }
}
