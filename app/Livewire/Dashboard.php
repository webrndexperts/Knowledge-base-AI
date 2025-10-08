<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;

class Dashboard extends Component
{
    public function mount()
    {
        if(!auth()->user()->can('dashboard', User::class)) {
            abort(403, __('messages.basic.permission-403'));
        }
    }

    public function render()
    {
        return view('livewire.dashboard')->title(__('messages.title.articles'));
    }
}
