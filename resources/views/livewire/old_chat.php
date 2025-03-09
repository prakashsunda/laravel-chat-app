<div class="flex h-screen bg-gray-100">
    <!-- Sidebar -->
    <div class="w-1/4 bg-white shadow-lg p-4">
        <h3 class="text-lg font-semibold mb-4">Chats</h3>
        <input type="text" placeholder="Search Contact" class="w-full p-2 border rounded mb-4">
        <ul>
            @foreach ($users as $index => $user)
                <li wire:click="selectUser({{ $user->id }})" 
                    class="cursor-pointer flex items-center justify-between p-3 hover:bg-gray-200 rounded">
                    <div class="flex items-center gap-3">
                        <img src="https://bootstrapdemos.adminmart.com/modernize/dist/assets/images/profile/user-1.jpg" class="rounded-full" alt="user1" width="40" height="40" >
                        <div>
                            <span class="font-semibold">{{ $user->name }}</span>
                            <p class="text-sm text-gray-500">Last message preview...</p>
                        </div>
                    </div>
                    <span class="text-xs text-gray-400">
                        {{ optional($user->latestMessage)->created_at?->diffForHumans() ?? 'No messages' }}
                    </span>
                </li>
                @if ($index === 0)
                    @php $this->selectUser($user->id); @endphp
                @endif
            @endforeach
        </ul>
    </div>
    
    <!-- Chat Area -->
    <div class="w-3/4 flex flex-col">
        @if ($selectedUser)
            <div class="p-4 bg-white shadow flex items-center gap-2 border-b">
                <img src="https://bootstrapdemos.adminmart.com/modernize/dist/assets/images/profile/user-1.jpg" class="rounded-full" alt="user1" width="40" height="40" >
                <h3 class="text-lg font-semibold">{{ $selectedUser->name }}</h3>
            </div>

            <div class="flex-1 p-4 overflow-y-auto h-96 bg-gray-50">
                @foreach ($messages as $message)
                    <div class="mb-4 flex {{ $message->sender_id == auth()->id() ? 'justify-end' : 'justify-start' }}">
                        @if ($message->sender_id != auth()->id())
                            <img src="https://bootstrapdemos.adminmart.com/modernize/dist/assets/images/profile/user-1.jpg" class="rounded-full mr-2" alt="user1" width="40" height="40" >
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
                <input type="text" wire:model="newMessage" wire:keydown.enter="sendMessage" id="messageInput"
                    placeholder="Type a message..." class="flex-1 p-2 border rounded">
                <button wire:click="sendMessage" class="bg-blue-500 text-white px-4 py-2 rounded">Send</button>
            </div>
        @endif
    </div>
</div>
