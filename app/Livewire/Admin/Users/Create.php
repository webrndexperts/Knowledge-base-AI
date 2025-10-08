<?php

namespace App\Livewire\Admin\Users;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class Create extends Component
{
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $email_verified = false;

    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'email_verified' => ['boolean'],
        ];
    }

    protected $messages = [
        'name.required' => 'The name field is required.',
        'email.required' => 'The email field is required.',
        'email.unique' => 'This email is already taken.',
        'password.required' => 'The password field is required.',
        'password.confirmed' => 'The password confirmation does not match.',
    ];

    public function mount(): void
    {
        if(!auth()->user()->can('create', User::class)) {
            abort(403, __('messages.basic.permission-403'));
        }
    }

    public function save()
    {
        $this->validate();

        try {
            $user = User::create([
                'name' => $this->name,
                'created_by' => auth()->id(),
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'email_verified_at' => $this->email_verified ? now() : null,
            ]);

            session()->flash('message', 'User created successfully.');
            return redirect()->route('users.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to create user: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.users.create')
            ->title(__('messages.title.create_user'));
    }
}
