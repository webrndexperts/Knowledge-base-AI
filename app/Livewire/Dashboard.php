<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;

class Dashboard extends Component
{
    public function mount()
    {
        if (! auth()->user()->can('dashboard', User::class)) {
            return redirect()->route('home');
        }
    }

    public function render()
    {
        return view('livewire.dashboard')->title(__('messages.title.articles'));
    }
}
