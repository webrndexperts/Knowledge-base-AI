<div class="p-4">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold">Upload Article Pdf</h2>

        <div class="flex items-center justify-center gap-2">
            <flux:link href="{{ route('articles.list') }}" class="flex md:ml-auto px-4 py-2 rounded !text-white !bg-gray-200  hover:!bg-gray-400 cursor-pointer !no-underline">
                <i class="fa-solid fa-arrow-left"></i> {{ __('messages.basic.back') }}
            </flux:link>
        </div>
    </div>

    <div class="w-full">
        <form wire:submit.prevent="submit">
            <flux:input 
                type="file"
                accept="application/pdf"
                wire:model="file"
                class="border rounded p-2 w-full mb-2"
                label="{{ __('messages.basic.upload', ['file_type' => 'PDF']) }}"
            />

            <x-loader-btn target="submit" type='submit' class="bg-blue-500 text-white px-3 py-1 rounded">
                {{ __('messages.basic.submit') }}
            </x-loader-btn>
        </form>
    </div>
</div>
