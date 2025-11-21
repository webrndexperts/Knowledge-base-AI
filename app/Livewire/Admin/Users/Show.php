<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Livewire\Component;

class Show extends Component
{
    public User $user;

    public function mount(User $user): void
    {
        if (! auth()->user()->can('view', $user)) {
            abort(403, __('messages.basic.permission-403'));
        }

        $this->user = $user->load(['conversations', 'queries']);
    }

    public function deleteUser()
    {
        if (! auth()->user()->can('delete', $this->user)) {
            abort(403, __('messages.basic.permission-403'));
        }

        if ($this->user->id === auth()->id()) {
            session()->flash('error', 'You cannot delete your own account.');

            return;
        }

        try {
            $this->user->delete();
            session()->flash('message', 'User deleted successfully.');

            return redirect()->route('users.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete user: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.users.show')
            ->title(__('messages.title.user_details'));
    }
}
