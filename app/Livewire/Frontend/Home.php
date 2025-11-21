<?php

namespace App\Livewire\Frontend;

use App\Models\Conversation;
use App\Models\Query;
use App\Services\AIService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

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
            if (! $this->conversationId) {
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
                ->with(['queries' => function ($query) {
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
                'sources' => $query->sources ?? [],
            ];
        }
    }

    private function ensureConversation()
    {
        if (! Auth::check()) {
            return null;
        }

        if (! $this->currentConversation) {
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

            if (! $this->conversationId) {
                // Invalid encrypted ID
                $this->isLoading = false;

                return;
            }

            $this->loadConversation();
        }

        $this->isLoading = false;
    }

    /**
     * Handle user questions and generate AI responses
     *
     * @param  AIService  $ai  The AI service instance
     * @return void
     */
    public function ask(AIService $ai)
    {
        try {
            $this->validate();

            // Rate limiting
            $rateLimitKey = 'ask-question:'.(auth()->id() ?? request()->ip());
            if (RateLimiter::tooManyAttempts($rateLimitKey, 10)) {
                $seconds = RateLimiter::availableIn($rateLimitKey);
                throw new \Exception("Too many attempts. Please try again in {$seconds} seconds.");
            }
            RateLimiter::hit($rateLimitKey);

            // Store question before resetting
            $userQuestion = trim($this->question);
            if (empty($userQuestion)) {
                throw new \Exception('Question cannot be empty.');
            }

            // Add user message to chat
            $this->messages[] = [
                'role' => 'user',
                'content' => $userQuestion,
                'timestamp' => now()->toDateTimeString(),
            ];

            // Reset input
            $this->question = '';
            $this->isLoading = true;

            // Process the question based on selected mode
            try {
                $response = match ($this->searchMode) {
                    'fast' => $ai->fastSearch($userQuestion),
                    'ai' => $ai->answerQuestion($userQuestion),
                    'hybrid' => $this->hybridSearch($ai, $userQuestion),
                    default => $ai->fastSearch($userQuestion)
                };

                if (empty($response['text'])) {
                    throw new \Exception('No response content received from the AI service.');
                }

                // Ensure we have a conversation for authenticated users
                $conversation = null;
                if (auth()->check()) {
                    $conversation = $this->ensureConversation();

                    if ($conversation) {
                        // Save the query and response
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
                }

                // Add assistant's response to messages
                $this->messages[] = [
                    'role' => 'assistant',
                    'content' => $response['text'],
                    'sources' => $response['sources'] ?? [],
                    'timestamp' => now()->toDateTimeString(),
                ];

            } catch (\Exception $e) {
                \Log::error('AI Service Error: '.$e->getMessage(), [
                    'question' => $userQuestion,
                    'mode' => $this->searchMode,
                    'user_id' => auth()->id(),
                    'trace' => $e->getTraceAsString(),
                ]);

                throw new \Exception('Sorry, there was an error processing your request. Please try again later.');
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->addError('question', $e->getMessage());
        } catch (\Exception $e) {
            $this->addError('question', $e->getMessage());

            // Add error message to chat
            $this->messages[] = [
                'role' => 'assistant',
                'content' => 'Sorry, I encountered an error: '.$e->getMessage(),
                'is_error' => true,
                'timestamp' => now()->toDateTimeString(),
            ];
        } finally {
            $this->isLoading = false;
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
        if (! empty($fastResponse['sources']) && count($fastResponse['sources']) > 0) {
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
            'content' => match ($mode) {
                'fast' => '🚀 **Fast Search Mode** - Instant keyword-based search activated',
                'ai' => '🤖 **AI Mode** - Advanced AI analysis activated (slower but more intelligent)',
                'hybrid' => '⚡ **Hybrid Mode** - Fast search with AI fallback activated',
                default => 'Search mode updated'
            },
            'sources' => [],
        ];
    }

    public function newConversation()
    {
        if (! Auth::check()) {
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
        $this->js('window.history.pushState({}, "", "/conversation/'.$conversation->encrypted_id.'")');

        // Refresh sidebar
        $this->dispatch('conversationCreated', $conversation->encrypted_id);

        $this->isLoading = false;
    }

    public function render()
    {
        return view('livewire.frontend.home')
            ->layout('components.layouts.app.frontend', [
                'title' => $this->currentConversation ? $this->currentConversation->title : __('messages.title.home'),
            ]);
    }
}
