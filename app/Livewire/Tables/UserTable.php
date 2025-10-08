<?php

namespace App\Livewire\Tables;

use Livewire\Component;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

class UserTable extends DataTableComponent
{
    protected $model = User::class;

    protected $listeners = [
        'delete-user' => 'delete'
    ];

    public function configure(): void
    {
        $this->setPrimaryKey('id');
        $this->setDefaultSort('created_at', 'desc');

        $this->setTableAttributes([
            'class' => 'w-full divide-y divide-gray-200 dark:divide-none border',
        ]);
        $this->setColumnSelectStatus(false);
        $this->setSearchFieldAttributes([
            'class' => 'p-2 border border-gray-600 dark:border-white rounded-md user-serch-input',
        ]);
        $this->setPerPageFieldAttributes([
            'class' => 'p-2 border border-gray-600 dark:border-white user-per-page-input',
        ]);

        $this->setPaginationWrapperAttributes([
            'class' => 'paginator-class',
        ]);
    }

    public function builder(): Builder
    {
        $query = User::withCount(['conversations', 'queries'])
            ->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'admin');
            });

        if(!auth()->user()->isAdmin()) {
            $query = $query->where('created_by', auth()->id());
        }


        return $query->select('users.*');
    }

    public function columns(): array
    {
        return [
            Column::make('S.No')
                ->label(function ($row) {
                    $currentPage = $this->getPage();
                    $perPage = $this->getPerPage();
                    static $rowIndex = 0;
                    $rowIndex++;

                    return ($currentPage - 1) * $perPage + $rowIndex;
                })
                ->sortable(fn (Builder $query, string $direction) => $query->orderBy('id', $direction))
                ->html(),

            Column::make("User Info")
                ->label(function ($row) {
                    return '<div class="flex items-center space-x-3">
                        <div class="flex-shrink-0 h-10 w-10">
                            <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                <span class="text-sm font-medium text-gray-700">' . 
                                    substr($row->name, 0, 2) . 
                                '</span>
                            </div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-900">' . $row->name . '</div>
                            <div class="text-sm text-gray-500">' . $row->email . '</div>
                        </div>
                    </div>';
                })
                ->searchable(function (Builder $query, $searchTerm) {
                    return $query->where('name', 'like', '%' . $searchTerm . '%')
                                 ->orWhere('email', 'like', '%' . $searchTerm . '%');
                })
                ->sortable(function (Builder $query, string $direction) {
                    return $query->orderBy('name', $direction);
                })
                ->html(),

            Column::make("Email Verified", "email_verified_at")
                ->sortable()
                ->format(function ($value) {
                    if ($value) {
                        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Verified
                        </span>';
                    } else {
                        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                            Unverified
                        </span>';
                    }
                })
                ->html(),

            Column::make("Conversations")
                ->label(function ($row) {
                    return '<div class="text-center">
                        <div class="text-sm font-medium text-gray-900">' . $row->conversations_count . '</div>
                        <div class="text-xs text-gray-500">conversations</div>
                    </div>';
                })
                ->sortable(function (Builder $query, string $direction) {
                    return $query->orderBy('conversations_count', $direction);
                })
                ->html(),

            Column::make("Queries")
                ->label(function ($row) {
                    return '<div class="text-center">
                        <div class="text-sm font-medium text-gray-900">' . $row->queries_count . '</div>
                        <div class="text-xs text-gray-500">queries</div>
                    </div>';
                })
                ->sortable(function (Builder $query, string $direction) {
                    return $query->orderBy('queries_count', $direction);
                })
                ->html(),

            Column::make("Joined", "created_at")
                ->sortable()
                ->format(fn($value) => $value->format('M j, Y')),

            Column::make('Actions')
                ->label(fn ($row) => view('actions.users.index', ['row' => $row])->render())
                ->html(),
        ];
    }

    public function delete($id): void
    {
        try {
            $user = User::findOrFail($id);
            
            // Prevent deleting the current user
            if ($user->id === auth()->id()) {
                $this->dispatch('notify', message: "You cannot delete your own account");
                return;
            }
            
            $user->delete();
            $this->dispatch('notify', message: "User has been deleted");
        } catch(\Exception $e) {
            $this->dispatch('notify', message: $e->getMessage());
        }
    }
}
