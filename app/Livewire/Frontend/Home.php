<?php

namespace App\Livewire\Frontend;

use App\Models\Query;
use App\Models\Conversation;
use App\Services\AIService;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Home extends Component
{
    public $question = '';
    public $messages = [];
    public $searchMode = 'fast'; // 'fast', 'ai', or 'hybrid'
    public $currentConversation = null;
    public $conversationId = null;
    public $encryptedId = null;
    public $isLoading = false;

    protected $rules = [
        'question' => 'required|string|min:2',
    ];

    protected $listeners = ['conversationSelected' => 'switchConversation'];

    public function mount($encryptedId = null)
    {
        $this->encryptedId = $encryptedId;
        
        if ($encryptedId && Auth::check()) {
            $this->conversationId = Conversation::decryptId($encryptedId);
            if (!$this->conversationId) {
                // Invalid encrypted ID, redirect to home
                return redirect()->route('home');
            }
        }
        
        $this->loadConversation();
    }

    public function loadConversation()
    {
        if ($this->conversationId && Auth::check()) {
            $this->currentConversation = Conversation::where('user_id', Auth::id())
                ->where('id', $this->conversationId)
                ->with(['queries' => function($query) {
                    $query->orderBy('created_at');
                }])
                ->first();

            if ($this->currentConversation) {
                $this->loadMessagesFromConversation();
            }
        } else {
            $this->currentConversation = null;
            $this->messages = [];
        }
    }

    private function loadMessagesFromConversation()
    {
        $this->messages = [];
        
        foreach ($this->currentConversation->queries as $query) {
            $this->messages[] = ['role' => 'user', 'content' => $query->question];
            $this->messages[] = [
                'role' => 'assistant', 
                'content' => $query->answer,
                'sources' => $query->sources ?? []
            ];
        }
    }

    private function ensureConversation()
    {
        if (!Auth::check()) {
            return null;
        }

        if (!$this->currentConversation) {
            $this->currentConversation = Conversation::create([
                'user_id' => Auth::id(),
                'title' => 'New Conversation',
                'is_active' => true,
            ]);
            $this->conversationId = $this->currentConversation->id;
        }

        return $this->currentConversation;
    }

    public function switchConversation($encryptedId)
    {
        $this->isLoading = true;
        
        if ($encryptedId === null) {
            // Going back to home - clear current conversation
            $this->conversationId = null;
            $this->encryptedId = null;
            $this->currentConversation = null;
            $this->messages = [];
        } else {
            $this->encryptedId = $encryptedId;
            $this->conversationId = Conversation::decryptId($encryptedId);
            
            if (!$this->conversationId) {
                // Invalid encrypted ID
                $this->isLoading = false;
                return;
            }
            
            $this->loadConversation();
        }
        
        $this->isLoading = false;
    }

    public function ask(AIService $ai)
    {
        $this->validate();

        // Store question before resetting
        $userQuestion = $this->question;
        $this->messages[] = ['role' => 'user', 'content' => $userQuestion];

        // Reset input
        $this->question = '';

        // Choose search method based on mode
        $response = match($this->searchMode) {
            'fast' => $ai->fastSearch($userQuestion),
            'ai' => $ai->answerQuestion($userQuestion),
            'hybrid' => $this->hybridSearch($ai, $userQuestion),
            default => $ai->fastSearch($userQuestion)
        };

        // Only save if we got a valid response
        if (!empty($response['text'])) {
            // Ensure we have a conversation
            $conversation = $this->ensureConversation();
            
            if ($conversation) {
                $query = Query::create([
                    'user_id' => auth()->id(),
                    'conversation_id' => $conversation->id,
                    'question' => $userQuestion,
                    'answer' => $response['text'],
                    'sources' => $response['sources'] ?? [],
                ]);

                // Update conversation title if it's the first query
                $conversation->ensureTitle();
                
                // Update conversation timestamp
                $conversation->touch();
                
                // Dispatch event to refresh sidebar
                $this->dispatch('conversationCreated', $conversation->id);
            }

            $this->messages[] = [
                'role' => 'assistant',
                'content' => $response['text'],
                'sources' => $response['sources'] ?? [],
            ];
        } else {
            $this->messages[] = [
                'role' => 'assistant',
                'content' => 'Sorry, I could not find an answer to your question. Please try rephrasing or upload relevant documents.',
                'sources' => [],
            ];
        }
    }

    /**
     * Hybrid search: Try fast search first, fallback to AI if no results
     */
    private function hybridSearch(AIService $ai, string $question): array
    {
        // First try fast search
        $fastResponse = $ai->fastSearch($question);
        
        // If fast search found results, return them
        if (!empty($fastResponse['sources']) && count($fastResponse['sources']) > 0) {
            return $fastResponse;
        }
        
        // If no fast results, try AI search
        return $ai->answerQuestion($question);
    }

    public function setSearchMode($mode)
    {
        $this->searchMode = $mode;
        $this->messages[] = [
            'role' => 'system',
            'content' => match($mode) {
                'fast' => '🚀 **Fast Search Mode** - Instant keyword-based search activated',
                'ai' => '🤖 **AI Mode** - Advanced AI analysis activated (slower but more intelligent)',
                'hybrid' => '⚡ **Hybrid Mode** - Fast search with AI fallback activated',
                default => 'Search mode updated'
            },
            'sources' => []
        ];
    }

    public function newConversation()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $this->isLoading = true;
        
        $conversation = Conversation::create([
            'user_id' => Auth::id(),
            'title' => 'New Conversation',
            'is_active' => true,
        ]);

        $this->conversationId = $conversation->id;
        $this->encryptedId = $conversation->encrypted_id;
        $this->currentConversation = $conversation;
        $this->messages = [];
        
        // Update URL without page refresh
        $this->js('window.history.pushState({}, "", "/conversation/' . $conversation->encrypted_id . '")');
        
        // Refresh sidebar
        $this->dispatch('conversationCreated', $conversation->encrypted_id);
        
        $this->isLoading = false;
    }

    public function render()
    {
        return view('livewire.frontend.home')
            ->layout('components.layouts.app.frontend', [
                'title' => $this->currentConversation ? $this->currentConversation->title : __('messages.title.home')
            ]);
    }
}
