<div class="p-4">
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
