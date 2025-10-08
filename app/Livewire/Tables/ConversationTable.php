<?php

namespace App\Livewire\Tables;

use Livewire\Component;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

class ConversationTable extends DataTableComponent
{
    protected $model = Conversation::class;
    public $userId = null;

    protected $listeners = [
        'delete-conversation' => 'delete'
    ];

    public function configure(): void
    {
        $this->setPrimaryKey('id');
        $this->setDefaultSort('updated_at', 'desc');

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
        $query = Conversation::with(['user', 'queries'])
            ->withCount('queries')
            ->select('conversations.*');
            
        if ($this->userId) {
            $query->where('user_id', $this->userId);
        }
        
        return $query;
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

            Column::make("User Name")
                ->label(function ($row) {
                    $user = $row->user;
                    return '<div class="flex items-center space-x-3">
                        <div class="flex-shrink-0 h-8 w-8">
                            <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                <span class="text-xs font-medium text-gray-700">' . 
                                    substr($user->name, 0, 2) . 
                                '</span>
                            </div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-900">' . $user->name . '</div>
                            <div class="text-xs text-gray-500">' . $user->email . '</div>
                        </div>
                    </div>';
                })
                ->searchable(function (Builder $query, $searchTerm) {
                    return $query->whereHas('user', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', '%' . $searchTerm . '%')
                          ->orWhere('email', 'like', '%' . $searchTerm . '%');
                    });
                })
                ->sortable(function (Builder $query, string $direction) {
                    return $query->join('users', 'conversations.user_id', '=', 'users.id')
                                 ->orderBy('users.name', $direction);
                })
                ->html(),

            Column::make("Conversation Title", "title")
                ->searchable()
                ->sortable()
                ->format(function ($value, $row) {
                    return '<div class="flex flex-col">
                        <div class="text-sm font-medium text-gray-900">' . 
                            \Illuminate\Support\Str::limit($value, 40) . 
                        '</div>
                        <div class="text-xs text-gray-500">ID: ' . $row->id . '</div>
                    </div>';
                })
                ->html(),

            Column::make("Total Queries")
                ->label(function ($row) {
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">' . 
                        $row->queries_count . ' queries</span>';
                })
                ->sortable(function (Builder $query, string $direction) {
                    return $query->orderBy('queries_count', $direction);
                })
                ->html(),

            Column::make("User Status")
                ->label(function ($row) {
                    $user = $row->user;
                    $isActive = $user->email_verified_at !== null;
                    $statusClass = $isActive ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                    $statusText = $isActive ? 'Active' : 'Inactive';
                    
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . 
                        $statusClass . '">' . $statusText . '</span>';
                })
                ->sortable(function (Builder $query, string $direction) {
                    return $query->join('users', 'conversations.user_id', '=', 'users.id')
                                 ->orderBy('users.email_verified_at', $direction);
                })
                ->html(),

            Column::make("Last Activity", "updated_at")
                ->sortable()
                ->format(fn($value) => $value->diffForHumans()),

            Column::make('Actions')
                ->label(fn ($row) => view('actions.conversations.index', ['row' => $row])->render())
                ->html(),
        ];
    }

    public function delete($id): void
    {
        try {
            $conversation = Conversation::findOrFail($id);
            $conversation->delete();
            $this->dispatch('notify', message: "Conversation has been deleted");
        } catch(\Exception $e) {
            $this->dispatch('notify', message: $e->getMessage());
        }
    }
}
