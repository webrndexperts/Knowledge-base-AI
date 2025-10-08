<div class="relative">
    <!-- Mobile Sidebar Toggle -->
    <button 
        wire:click="toggleSidebar"
        class="lg:hidden fixed top-20 left-4 z-50 bg-white shadow-lg rounded-lg p-2 border border-gray-200"
    >
        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>

    <!-- Sidebar Overlay (Mobile) -->
    <div 
        class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-40 transition-opacity duration-300 {{ $isOpen ? 'opacity-100' : 'opacity-0 pointer-events-none' }}"
        wire:click="toggleSidebar"
    ></div>

    <!-- Sidebar -->
    <div class="fixed left-0 top-16 h-[calc(100vh-4rem)] w-80 bg-white border-r border-gray-200 shadow-lg z-40 transform transition-transform duration-300 lg:translate-x-0 {{ $isOpen ? 'translate-x-0' : '-translate-x-full' }} flex flex-col">
        <!-- Sidebar Header -->
        <div class="p-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-800">Conversations</h2>
                <button 
                    wire:click="createNewConversation"
                    wire:loading.attr="disabled"
                    wire:target="createNewConversation"
                    class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white text-sm font-medium rounded-lg transition-colors duration-200"
                >
                    <div wire:loading.remove wire:target="createNewConversation">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </div>
                    <div wire:loading wire:target="createNewConversation">
                        <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-1"></div>
                    </div>
                    New
                </button>
            </div>
        </div>

        <!-- Conversations List -->
        <div class="flex-1 overflow-y-auto">
            @auth
                @if($conversations->count() > 0)
                    <div class="p-2">
                        @foreach($conversations as $conversation)
                            <div 
                                wire:click="selectConversation({{ $conversation->id }})"
                                wire:loading.class="opacity-50 pointer-events-none"
                                wire:target="selectConversation({{ $conversation->id }})"
                                class="group relative flex items-center p-3 rounded-lg cursor-pointer transition-colors duration-200 mb-1 {{ $currentConversationId == $conversation->id ? 'bg-blue-50 border border-blue-200' : 'hover:bg-gray-50' }}"
                            >
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-2 h-2 bg-green-500 rounded-full {{ $conversation->is_active ? '' : 'opacity-50' }}"></div>
                                        <h3 class="text-sm font-medium text-gray-800 truncate flex items-center">
                                            {{ $conversation->title }}

                                            <div wire:loading wire:target="selectConversation({{ $conversation->id }})">
                                                <div class="ml-2 animate-spin rounded-full h-4 w-4 border-b-2 border-current mr-1"></div>
                                            </div>
                                        </h3>
                                    </div>
                                    @if($conversation->latestQuery)
                                        <p class="text-xs text-gray-500 mt-1 truncate">
                                            {{ Str::limit($conversation->latestQuery->question, 60) }}
                                        </p>
                                        <p class="text-xs text-gray-400 mt-1">
                                            {{ $conversation->latestQuery->created_at->diffForHumans() }}
                                        </p>
                                    @else
                                        <p class="text-xs text-gray-400 mt-1">No messages yet</p>
                                    @endif
                                </div>

                                <!-- Delete Button -->
                                <button 
                                    wire:click.stop="deleteConversation({{ $conversation->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="deleteConversation({{ $conversation->id }})"
                                    class="opacity-0 group-hover:opacity-100 p-1 text-gray-400 hover:text-red-500 disabled:opacity-50 transition-all duration-200"
                                    onclick="return confirm('Are you sure you want to delete this conversation?')"
                                >
                                    <div wire:loading wire:target="deleteConversation({{ $conversation->id }})">
                                        <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-current"></div>
                                    </div>

                                    <div wire:loading.remove wire:target="deleteConversation({{ $conversation->id }})">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </div>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-6 text-center">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                        <h3 class="text-sm font-medium text-gray-800 mb-2">No conversations yet</h3>
                        <p class="text-xs text-gray-500 mb-4">Start a new conversation to see your chat history here.</p>
                        <button 
                            wire:click="createNewConversation"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Start Chatting
                        </button>
                    </div>
                @endif
            @else
                <div class="p-6 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-gray-800 mb-2">Please log in</h3>
                    <p class="text-xs text-gray-500 mb-4">Log in to save and view your conversation history.</p>
                    <a 
                        href="{{ route('login') }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200"
                    >
                        Log In
                    </a>
                </div>
            @endauth
        </div>
    </div>
</div>
