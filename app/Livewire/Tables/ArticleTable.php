<?php

namespace App\Livewire\Tables;

use App\Models\Article;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

class ArticleTable extends DataTableComponent
{
    protected $model = Article::class;

    protected $listeners = [
        'delete-article' => 'delete',
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
        return Article::withRoles()->select('id');
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

            Column::make('Title', 'title')->searchable()->sortable(),

            Column::make('Created', 'created_at')
                ->sortable()
                ->format(fn ($value) => $value->diffForHumans()),

            Column::make('Actions')
                ->label(fn ($row) => view('actions.articles.index', ['row' => $row])->render())
                ->html(),
        ];
    }

    public function delete($id): void
    {
        try {
            $article = Article::findOrFail($id);
            $article->delete();
            $this->dispatch('notify', message: 'Article has been deleted');
        } catch (\Exception $e) {
            $this->dispatch('notify', message: $e->getMessage());
        }

    }
}
