<?php

namespace App\Livewire\Components;

use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ConversationSidebar extends Component
{
    public $conversations;

    public $currentConversationId;

    public $currentEncryptedId;

    public $isOpen = false;

    protected $listeners = ['conversationCreated' => 'refreshConversations'];

    public function mount($currentConversationId = null)
    {
        // If we receive an encrypted ID, decrypt it
        if ($currentConversationId) {
            $decryptedId = Conversation::decryptId($currentConversationId);
            if ($decryptedId) {
                $this->currentConversationId = $decryptedId;
                $this->currentEncryptedId = $currentConversationId;
            }
        }

        $this->loadConversations();
    }

    public function loadConversations()
    {
        if (Auth::check()) {
            $this->conversations = Conversation::where('user_id', Auth::id())
                ->with('latestQuery')
                ->orderBy('updated_at', 'desc')
                ->limit(20)
                ->get();
        } else {
            $this->conversations = collect();
        }
    }

    public function refreshConversations()
    {
        $this->loadConversations();
    }

    public function selectConversation($conversationId)
    {
        $conversation = Conversation::find($conversationId);
        if (! $conversation) {
            return;
        }

        $this->currentConversationId = $conversationId;
        $this->currentEncryptedId = $conversation->encrypted_id;
        $this->dispatch('conversationSelected', $conversation->encrypted_id);

        // Update URL without page refresh
        $this->js('window.history.pushState({}, "", "/conversation/'.$conversation->encrypted_id.'")');
    }

    public function createNewConversation()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $conversation = Conversation::create([
            'user_id' => Auth::id(),
            'title' => 'New Conversation',
            'is_active' => true,
        ]);

        $this->currentConversationId = $conversation->id;
        $this->currentEncryptedId = $conversation->encrypted_id;
        $this->loadConversations();
        $this->dispatch('conversationSelected', $conversation->encrypted_id);

        // Update URL without page refresh
        $this->js('window.history.pushState({}, "", "/conversation/'.$conversation->encrypted_id.'")');
    }

    public function deleteConversation($conversationId)
    {
        $conversation = Conversation::where('user_id', Auth::id())
            ->where('id', $conversationId)
            ->first();

        if ($conversation) {
            $conversation->delete();
            $this->loadConversations();

            // If we deleted the current conversation, switch to home
            if ($this->currentConversationId == $conversationId) {
                $this->currentConversationId = null;
                $this->dispatch('conversationSelected', null);
                $this->js('window.history.pushState({}, "", "/")');
            }
        }
    }

    public function toggleSidebar()
    {
        $this->isOpen = ! $this->isOpen;
    }

    public function render()
    {
        return view('livewire.components.conversation-sidebar');
    }
}
