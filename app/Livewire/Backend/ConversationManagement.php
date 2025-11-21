<?php

namespace App\Livewire\Backend;

use App\Models\User;
use Livewire\Component;

class ConversationManagement extends Component
{
    public $userId = null;

    public $user = null;

    public function mount($userId = null)
    {
        if (! auth()->user()->can('conversations', User::class)) {
            abort(403, __('messages.basic.permission-403'));
        }

        $this->userId = $userId;

        if ($this->userId) {
            $this->user = User::with('conversations')->findOrFail($this->userId);
        }
    }

    public function render()
    {
        return view('livewire.backend.conversation-management')
            ->title(__('messages.title.articles'));
    }
}
