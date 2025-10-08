<!-- Full Screen Chat Interface -->
<div class="h-full flex flex-col bg-gray-50 relative">
    <!-- Loading Overlay -->
    @if($isLoading)
        <div class="absolute inset-0 bg-white/80 backdrop-blur-sm z-50 flex items-center justify-center">
            <div class="flex flex-col items-center space-y-4">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                <p class="text-gray-600 font-medium">Loading conversation...</p>
            </div>
        </div>
    @endif

    <!-- Chat Container - Full Height -->
    <div class="flex-1 flex flex-col overflow-hidden min-h-0">
        <!-- Messages Area - Scrollable -->
        <div class="flex-1 overflow-y-auto px-4 py-6 space-y-4 bg-white" id="chat-container">
            @if(empty($messages))
                <!-- Welcome Message - Centered in Available Space -->
                <div class="flex items-center justify-center h-full min-h-[400px]">
                    <div class="text-center max-w-md mx-auto px-6">
                        <div class="relative mx-auto mb-8">
                            <div class="w-24 h-24 bg-gradient-to-r from-blue-100 to-indigo-100 rounded-3xl flex items-center justify-center mx-auto shadow-lg">
                                <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                            </div>
                            <div class="absolute -top-2 -right-2 w-8 h-8 bg-green-500 rounded-full flex items-center justify-center shadow-lg">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                        </div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-4">AI Knowledge Assistant</h1>
                        <p class="text-gray-600 mb-8 leading-relaxed">
                            Ask me anything about your uploaded documents. I can help you find information, summarize content, and answer questions instantly.
                        </p>
                        <div class="flex flex-wrap justify-center gap-2 text-sm">
                            <span class="px-4 py-2 bg-blue-50 text-blue-700 rounded-full font-medium">Document Analysis</span>
                            <span class="px-4 py-2 bg-green-50 text-green-700 rounded-full font-medium">Quick Answers</span>
                            <span class="px-4 py-2 bg-purple-50 text-purple-700 rounded-full font-medium">Smart Search</span>
                        </div>
                    </div>
                </div>
            @else
                <!-- Messages List -->
                <div class="max-w-4xl mx-auto space-y-6">
            @endif

            @foreach($messages as $message)
                @if($message['role'] === 'system')
                    <!-- System Message -->
                    <div class="flex justify-center mb-6">
                        <div class="px-4 py-2 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800 max-w-md text-center">
                            {!! $message['content'] !!}
                        </div>
                    </div>
                @else
                    <!-- User/Assistant Message -->
                    <div class="flex {{ $message['role'] === 'user' ? 'justify-end' : 'justify-start' }} mb-6">
                        <div class="flex items-start space-x-3 max-w-[85%] md:max-w-[75%]">
                            @if($message['role'] === 'assistant')
                                <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif
                        
                            <div class="flex flex-col">
                                <div class="px-4 py-3 rounded-2xl shadow-sm
                                    {{ $message['role'] === 'user' 
                                        ? 'bg-blue-600 text-white rounded-br-md' 
                                        : 'bg-gray-50 text-gray-800 border border-gray-200 rounded-bl-md' }}">
                                    <div class="prose prose-sm max-w-none {{ $message['role'] === 'user' ? 'prose-invert' : '' }}">
                                        {!! nl2br(e($message['content'])) !!}
                                    </div>
                                </div>

                                @if(!empty($message['sources']))
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <span class="text-xs text-gray-500 font-medium">Sources:</span>
                                        @foreach($message['sources'] as $source)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                                {{ $source['type'] }} #{{ $source['id'] }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            @if($message['role'] === 'user')
                                <div class="w-8 h-8 bg-gray-600 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach

            @if(!empty($messages))
                </div> <!-- Close messages container -->
            @endif
        </div>

        <!-- Bottom Input Area - Fixed at Bottom -->
        <div class="border-t border-gray-200 bg-white shadow-lg">
            <!-- Search Mode Toggle -->
            <div class="px-4 py-3 border-b border-gray-100">
                <div class="max-w-4xl mx-auto flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700">Mode:</span>
                    <div class="flex space-x-2">
                        <button wire:click="setSearchMode('fast')" 
                            class="px-3 py-1 text-xs rounded-full transition-colors duration-200 {{ $searchMode === 'fast' ? 'bg-green-100 text-green-800 font-medium' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            🚀 Fast
                        </button>
                        <button wire:click="setSearchMode('hybrid')" 
                            class="px-3 py-1 text-xs rounded-full transition-colors duration-200 {{ $searchMode === 'hybrid' ? 'bg-blue-100 text-blue-800 font-medium' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            ⚡ Hybrid
                        </button>
                        <button wire:click="setSearchMode('ai')" 
                            class="px-3 py-1 text-xs rounded-full transition-colors duration-200 {{ $searchMode === 'ai' ? 'bg-purple-100 text-purple-800 font-medium' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            🤖 AI
                        </button>
                    </div>
                </div>
            </div>

            <!-- Input Area -->
            <div class="p-4">
                <div class="max-w-4xl mx-auto">
                    <form wire:submit.prevent="ask" class="flex items-end space-x-3">
                        <div class="flex-1">
                            <textarea 
                                wire:model.defer="question"
                                placeholder="Ask me anything about your documents..."
                                rows="1"
                                class="w-full resize-none border border-gray-300 rounded-2xl px-4 py-3 pr-12 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 max-h-32"
                                style="min-height: 44px;"
                                @keydown.enter.prevent="if(!$event.shiftKey) { $wire.ask(); $event.target.value = ''; $event.target.style.height = '44px'; } else { $event.target.style.height = 'auto'; $event.target.style.height = $event.target.scrollHeight + 'px'; }"
                                @input="$event.target.style.height = 'auto'; $event.target.style.height = Math.min($event.target.scrollHeight, 128) + 'px';"
                            ></textarea>
                        </div>
                        
                        <x-loader-btn target="ask" type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white p-3 rounded-2xl font-medium transition-all duration-200 flex items-center justify-center shadow-lg hover:shadow-xl flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                        </x-loader-btn>
                    </form>
                    
                    <div class="mt-2 text-center">
                        <span class="text-xs text-gray-500">Press Enter to send • Shift + Enter for new line</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-scroll to bottom when new messages arrive
        document.addEventListener('livewire:updated', function () {
            const chatContainer = document.getElementById('chat-container');
            if (chatContainer) {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
        });
    </script>
</div>