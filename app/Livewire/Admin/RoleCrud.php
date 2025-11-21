<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleCrud extends Component
{
    use WithPagination;

    public $name;

    public $selectedPermissions = [];

    public $perPage = 10;

    public $roleId;

    public $isOpen = false;

    public $search = '';

    public $permissions = [];

    protected $rules = [
        'name' => 'required|string|max:255|unique:roles,name',
        'selectedPermissions' => 'array',
    ];

    public function mount()
    {
        $this->permissions = Permission::all();
    }

    public function render()
    {
        $roles = Role::when($this->search, function ($query) {
                return $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->with('permissions')
            ->whereNot('name', 'admin')
            ->paginate($this->perPage);

        return view('livewire.admin.role-crud', [
            'roles' => $roles,
        ]);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function openModal()
    {
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    private function resetInputFields()
    {
        $this->name = '';
        $this->selectedPermissions = [];
        $this->roleId = null;
    }

    public function store()
    {
        $this->validate();

        $role = Role::updateOrCreate(
            ['id' => $this->roleId],
            ['name' => $this->name, 'guard_name' => 'web']
        );

        $role->syncPermissions($this->selectedPermissions);

        session()->flash('message',
            $this->roleId ? 'Role Updated Successfully.' : 'Role Created Successfully.');

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $this->roleId = $id;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();

        $this->openModal();
    }

    public function delete($id)
    {
        $role = Role::findOrFail($id);

        // Prevent deleting super-admin role
        if ($role->name == 'admin') {
            session()->flash('error', 'Cannot delete admin role.');

            return;
        }

        $role->delete();
        session()->flash('message', 'Role Deleted Successfully.');
    }
}
