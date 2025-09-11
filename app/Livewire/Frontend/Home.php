<?php

namespace App\Livewire\Frontend;

use App\Models\Query;
use App\Services\AIService;
use Illuminate\Http\Request;
use Livewire\Component;

class Home extends Component
{
    public $question = '';

    public $messages = [];

    protected $rules = [
        'question' => 'required|string|min:2',
    ];

    public function ask(Request $request, AIService $ai)
    {

        $this->validate();

        $userQuestion = $this->question;
        $this->messages[] = ['role' => 'user', 'content' => $this->question];

        // Reset input
        $this->question = '';

        // Call AIService
        $response = $ai->answerQuestion($this->question);

        $query = Query::create([
            'user_id' => auth()->id(),
            'question' => $this->question,
            'answer' => $response['text'],
            'sources' => $response['sources'],
        ]);

        $this->messages[] = [
            'role' => 'assistant',
            'content' => $response['text'],
            'sources' => $response['sources'] ?? [],
        ];
    }

    public function render()
    {
        return view('livewire.frontend.home')
            ->layout('components.layouts.app.frontend', ['title' => __('messages.title.home')]);
    }
}
