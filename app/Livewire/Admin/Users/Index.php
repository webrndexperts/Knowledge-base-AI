<?php

namespace App\Livewire\Admin\Users;

use Livewire\Component;
use App\Models\User;

class Index extends Component
{
    public function mount(): void
    {
        if(!auth()->user()->can('viewAny', User::class)) {
            abort(403, __('messages.basic.permission-403'));
        }
    }

    public function render()
    {
        return view('livewire.admin.users.index')
            ->title(__('messages.title.users'));
    }
}
