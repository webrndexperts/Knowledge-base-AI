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
        $query = User::with(['creator', 'conversations'])
            ->withRoles()
            ->withoutAdmin();
            // ->withCount(['conversations', 'queries']);

        return $query->select('users.*');
    }

    public function columns(): array
    {
        $columns = [
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
                    return view('actions.users.info', ['row' => $row])->render();
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
                    return view('actions.users.verified', ['value' => $value])->render();
                })
                ->html(),

            Column::make("Conversations")
                ->label(function ($row) {
                    return '<div class="text-center">
                        <div class="text-sm font-medium text-gray-900">' . count($row->conversations) . '</div>
                        <div class="text-xs text-gray-500">conversations</div>
                    </div>';
                })
                ->sortable(function (Builder $query, string $direction) {
                    return $query->orderBy('conversations_count', $direction);
                })
                ->html(),

            // Column::make("Queries")
            //     ->label(function ($row) {
            //         return '<div class="text-center">
            //             <div class="text-sm font-medium text-gray-900">' . $row->queries_count . '</div>
            //             <div class="text-xs text-gray-500">queries</div>
            //         </div>';
            //     })
            //     ->sortable(function (Builder $query, string $direction) {
            //         return $query->orderBy('queries_count', $direction);
            //     })
            //     ->html(),
        ];

        // Only show "Created By" column for admin users
        if(auth()->user()->isAdmin()) {
            $columns[] = Column::make("Created By")
                ->label(function ($row) {
                    if ($row->creator) {
                        return '<div class="text-sm">
                            <div class="font-medium text-gray-900 dark:text-white">' . $row->creator->name . '</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">' . $row->creator->email . '</div>
                        </div>';
                    } else {
                        return '<span class="text-sm text-gray-500 dark:text-gray-400">System</span>';
                    }
                })
                ->searchable(function (Builder $query, $searchTerm) {
                    return $query->whereHas('creator', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', '%' . $searchTerm . '%')
                            ->orWhere('email', 'like', '%' . $searchTerm . '%');
                    });
                })
                ->sortable(function (Builder $query, string $direction) {
                    return $query->leftJoin('users as creators', 'users.created_by', '=', 'creators.id')
                        ->orderBy('creators.name', $direction);
                })
                ->html();
        }

        $columns[] = Column::make("Joined", "created_at")
            ->sortable()
            ->format(fn($value) => $value->format('M j, Y'));

        $columns[] = Column::make('Actions')
            ->label(fn ($row) => view('actions.users.index', ['row' => $row])->render())
            ->html();

        return $columns;
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
