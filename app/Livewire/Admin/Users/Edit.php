<?php

namespace App\Livewire\Admin\Users;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class Edit extends Component
{
    public User $user;
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $email_verified = false;

    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $this->user->id],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'email_verified' => ['boolean'],
        ];
    }

    protected $messages = [
        'name.required' => 'The name field is required.',
        'email.required' => 'The email field is required.',
        'email.unique' => 'This email is already taken.',
        'password.confirmed' => 'The password confirmation does not match.',
    ];

    public function mount(User $user): void
    {
        if(!auth()->user()->can('update', $user)) {
            abort(403, __('messages.basic.permission-403'));
        }

        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->email_verified = !is_null($user->email_verified_at);
    }

    public function save()
    {
        $this->validate();

        try {
            $updateData = [
                'name' => $this->name,
                'email' => $this->email,
                'email_verified_at' => $this->email_verified ? now() : null,
            ];

            // Only update password if provided
            if (!empty($this->password)) {
                $updateData['password'] = Hash::make($this->password);
            }

            $this->user->update($updateData);

            session()->flash('message', 'User updated successfully.');
            return redirect()->route('users.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update user: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.users.edit')
            ->title(__('messages.title.edit_user'));
    }
}
