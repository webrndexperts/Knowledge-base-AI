<?php

namespace App\Livewire\Admin\Articles;

use App\Models\Article;
use Livewire\Component;

class Index extends Component
{
    public function mount(): void
    {
        // if(!auth()->user()->can('viewAny', Article::class)) {
        //     abort(403, __('messages.basic.permission-403'));
        // }
    }

    public function render()
    {
        return view('livewire.admin.article.articles')->title(__('messages.title.articles'));
    }
}
