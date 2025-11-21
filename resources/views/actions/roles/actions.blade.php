<div class="flex space-x-2">
    <button 
        wire:click="$dispatch('edit-role', { id: {{ $row->id }} })" 
        class="text-blue-500 hover:text-blue-700"
    >
        <i class="fas fa-edit"></i> Edit
    </button>
    
    @if($row->name !== 'admin')
        <button 
            wire:click="delete({{ $row->id }})" 
            onclick="return confirm('Are you sure you want to delete this role?')"
            class="text-red-500 hover:text-red-700"
        >
            <i class="fas fa-trash"></i> Delete
        </button>
    @endif
</div>
