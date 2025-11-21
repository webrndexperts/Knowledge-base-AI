<?php

namespace App\Livewire\Backend;

use App\Models\Conversation;
use Livewire\Component;

class ConversationDetail extends Component
{
    public $conversation;

    public $encryptedId;

    public $userId;

    public function mount($encryptedId)
    {
        $this->encryptedId = $encryptedId;
        $this->loadConversation();
    }

    public function loadConversation()
    {
        $conversationId = Conversation::decryptId($this->encryptedId);

        if (! $conversationId) {
            abort(404, 'Conversation not found');
        }

        $this->conversation = Conversation::with([
            'user',
            'queries' => function ($query) {
                $query->orderBy('created_at');
            },
        ])->find($conversationId);

        if (! $this->conversation) {
            abort(404, 'Conversation not found');
        }

        $this->userId = $this->conversation->user_id;
    }

    public function deleteConversation()
    {
        if ($this->conversation) {
            $this->conversation->delete();
            session()->flash('message', 'Conversation deleted successfully.');

            return redirect()->route('conversations.index');
        }
    }

    public function deleteQuery($queryId)
    {
        $query = $this->conversation->queries()->find($queryId);
        if ($query) {
            $query->delete();
            $this->loadConversation(); // Refresh the conversation
            session()->flash('message', 'Query deleted successfully.');
        }
    }

    public function render()
    {
        return view('livewire.backend.conversation-detail')
            ->title(__('messages.title.articles'));
    }
}
