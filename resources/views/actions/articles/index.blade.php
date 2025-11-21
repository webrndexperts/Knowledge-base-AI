<div
    class="flex space-x-2 items-center"
>
    <button
        class="p-1 text-red-500 hover:text-red-800 text-xl delete-article"
        title="Delete Article"
        data-id="{{ $row->id }}" data-name="{{ $row->title }}"
    >
        <x-icon.trash />
    </button>
</div>