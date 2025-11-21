<div class="p-4">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold">Upload Article Pdf</h2>

        <div class="flex items-center justify-center gap-2">
            <x-back-btn url="articles.list" />
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

            <x-loader-btn 
                target="submit" 
                type='submit' 
                class="bg-blue-500 text-white px-3 py-1 rounded disabled:opacity-50 disabled:cursor-not-allowed"
                :disabled="!$fileSelected"
            >
                {{ __('messages.basic.submit') }}
            </x-loader-btn>
        </form>
    </div>
</div>
