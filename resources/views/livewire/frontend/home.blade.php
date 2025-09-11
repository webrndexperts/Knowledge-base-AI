<div class="flex flex-col h-[80vh] max-w-3xl mx-auto bg-white rounded-2xl shadow p-4">
    <!-- Chat Window -->
    <div class="flex-1 overflow-y-auto space-y-4 mb-4">
        @foreach($messages as $message)
            <div class="flex {{ $message['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[70%] px-4 py-2 rounded-2xl shadow
                    {{ $message['role'] === 'user' ? 'bg-green-500 text-white rounded-br-none' : 'bg-gray-100 text-gray-900 rounded-bl-none' }}">
                    
                    <p class="whitespace-pre-wrap">{{ $message['content'] }}</p>

                    @if(!empty($message['sources']))
                        <div class="mt-2 text-xs text-gray-500">
                            Sources:
                            @foreach($message['sources'] as $source)
                                <span class="px-2 py-0.5 bg-gray-200 rounded">
                                    {{ $source['type'] }} #{{ $source['id'] }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Input Box -->
    <form wire:submit.prevent="ask" class="flex items-center space-x-2">
        <input type="text" wire:model.defer="question"
            placeholder="Ask me anything..."
            class="flex-1 border rounded-2xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" />

        <x-loader-btn target="ask" type='submit' class="bg-blue-500 text-white px-3 py-1 rounded">
            {{ __('messages.basic.submit') }}
        </x-loader-btn>
    </form>
</div>
