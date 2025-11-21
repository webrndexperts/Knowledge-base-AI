<?php

namespace App\Livewire\Tables;

use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\ButtonGroupColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\LinkColumn;
use Spatie\Permission\Models\Role;

class RoleTable extends DataTableComponent
{
    protected $model = Role::class;

    protected $listeners = [
        'delete-role' => 'delete',
        'refresh' => '$refresh',
    ];

    public function configure(): void
    {
        $this->setPrimaryKey('id');
        $this->setDefaultSort('id', 'asc');

        $this->setTableAttributes([
            'class' => 'w-full divide-y divide-gray-200 dark:divide-none border',
        ]);

        $this->setColumnSelectStatus(false);
        $this->setSearchFieldAttributes([
            'class' => 'p-2 border border-gray-600 dark:border-white rounded-md user-serch-input',
            'placeholder' => 'Search roles...',
        ]);

        $this->setPerPageFieldAttributes([
            'class' => 'p-2 border border-gray-600 dark:border-white user-per-page-input',
        ]);

        $this->setPaginationWrapperAttributes([
            'class' => 'px-4 py-2 border-t border-gray-200 dark:border-gray-700',
        ]);
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')
                ->sortable()
                ->searchable()
                ->format(
                    fn ($value, $row, Column $column) => $value
                )->html(),

            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Permissions')
                ->label(
                    fn ($row, Column $column) => $row->permissions->map(function ($permission) {
                        return '<span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 mr-1 mb-1">'
                               .$permission->name.
                               '</span>';
                    })->implode('')
                )->html(),

            ButtonGroupColumn::make('Actions')
                ->attributes(function ($row) {
                    return [
                        'class' => 'space-x-2',
                    ];
                })
                ->buttons([
                    LinkColumn::make('Edit')
                        ->title(fn ($row) => 'Edit')
                        ->location(fn ($row) => '#')
                        ->attributes(function ($row) {
                            return [
                                'wire:click' => "$\$dispatch('edit-role', {id: {$row->id}})",
                                'class' => 'text-blue-500 hover:text-blue-700',
                            ];
                        }),
                    LinkColumn::make('Delete')
                        ->title(fn ($row) => 'Delete')
                        ->location(fn ($row) => '#')
                        ->attributes(function ($row) {
                            return [
                                'wire:click' => "$\$dispatch('delete-role', {id: {$row->id}})",
                                'class' => 'text-red-500 hover:text-red-700',
                                'onclick' => 'return confirm(\'Are you sure?\')',
                            ];
                        }),
                ]),
        ];
    }

    public function delete($id)
    {
        $role = Role::findOrFail($id);

        // Prevent deleting super-admin role
        if ($role->name === 'super-admin') {
            session()->flash('error', 'Cannot delete super-admin role.');

            return;
        }

        $role->delete();
        session()->flash('message', 'Role deleted successfully.');
        $this->emit('refresh');
    }

    public function builder(): Builder
    {
        return Role::query()->with('permissions');
    }
}
