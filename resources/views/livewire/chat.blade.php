<div class="flex h-screen bg-gray-100">
    <!-- Sidebar -->
    <div class="w-1/4 bg-white shadow-lg p-4">
        <h3 class="text-lg font-semibold mb-4">Chats</h3>
        <input type="text" placeholder="Search Contact" class="w-full p-2 border rounded mb-4" wire:model.debounce.500ms="search">
        <ul>
            @foreach ($users as $user)
                <li wire:key="{{ $user->id }}" 
                    wire:click="selectUser({{ $user->id }})" 
                    class="cursor-pointer flex items-center justify-between p-3 hover:bg-gray-200 rounded 
                          {{ $selectedUser && $selectedUser->id == $user->id ? 'bg-gray-300' : '' }}">
                    <div class="flex items-center gap-3">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}" class="rounded-full" alt="user1" width="40" height="40">
                        <div>
                            <span class="font-semibold">{{ $user->name }}</span>
                            <p class="text-sm text-gray-500">
                                {{ optional($user->latestMessage)->message ?? 'No messages yet' }}
                            </p>
                        </div>
                    </div>
                    <span class="text-xs text-gray-400">
                        {{ optional($user->latestMessage)->created_at?->diffForHumans() ?? '' }}
                    </span>
                </li>
            @endforeach
        </ul>
    </div>

    <!-- Chat Area -->
    <div class="w-3/4 flex flex-col">
        @if ($selectedUser)
            <div class="p-4 bg-white shadow flex items-center gap-2 border-b">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($selectedUser->name) }}" class="rounded-full" alt="user1" width="40" height="40">
                <h3 class="text-lg font-semibold">{{ $selectedUser->name }}</h3>
            </div>

            <div id="chat-container" class="flex-1 p-4 overflow-y-auto h-96 bg-gray-50">
                @foreach ($messages as $message)
                    <div wire:key="message-{{ $message->id }}" 
                         class="mb-4 flex {{ $message->sender_id == auth()->id() ? 'justify-end' : 'justify-start' }}">
                        @if ($message->sender_id != auth()->id())
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($message->sender->name) }}" class="rounded-full mr-2" alt="user1" width="40" height="40">
                        @endif
                        <div>
                            <p class="text-sm text-gray-500 mb-1">
                                @if ($message->sender_id != auth()->id())
                                    <strong>{{ $message->sender->name }}</strong> · 
                                @endif
                                {{ $message->created_at->diffForHumans() }}
                            </p>
                            <div class="inline-block px-4 py-2 rounded-lg {{ $message->sender_id == auth()->id() ? 'bg-blue-500 text-white' : 'bg-gray-200' }}">
                                {{ $message->message }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="p-4 bg-white shadow flex items-center gap-2 border-t">
                <input type="text" 
                       wire:model.live="newMessage" 
                       wire:keydown.enter="sendMessage" 
                       id="messageInput"
                       placeholder="Type a message..." 
                       class="flex-1 p-2 border rounded">
                <button wire:click="sendMessage" 
                        class="bg-blue-500 text-white px-4 py-2 rounded">Send</button>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const chatContainer = document.getElementById('chat-container');
                    const messageInput = document.getElementById('messageInput');

                    function scrollToBottom() {
                        if (chatContainer) {
                            chatContainer.scrollTop = chatContainer.scrollHeight;
                        }
                    }

                    // Initial scroll
                    scrollToBottom();

                    // Listen for Livewire events
                    Livewire.on('scrollToBottom', () => {
                        setTimeout(() => {
                            scrollToBottom();
                        }, 50);
                    });

                    // Ensure input clears after message is sent
                    Livewire.on('messageSent', () => {
                        if (messageInput) {
                            messageInput.value = ''; // Force clear the input
                        }
                    });
                });
            </script>
        @endif
    </div>
</div>