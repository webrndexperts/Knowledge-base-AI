<?php

namespace App\Livewire\Admin;

use App\Models\Article;
use App\Models\User;
use App\Services\FileProcessingService;
use Livewire\Component;
use Livewire\WithFileUploads;

class PdfUploader extends Component
{
    use WithFileUploads;

    public $file;

    protected $rules = [
        'file' => 'required|mimes:pdf',
    ];

    public function mount()
    {
        if (! auth()->user()->can('uploadPdf', User::class)) {
            abort(403, __('messages.basic.permission-403'));
        }
    }

    /**
     * Handle PDF upload and processing.
     *
     * - Validates the uploaded file.
     * - Stores the file in the "public/documents" directory.
     * - Creates an Article record linked to the file.
     * - Runs the file through FileProcessingService.
     * - Dispatches browser events for success/error notifications.
     */
    public function submit(): void
    {
        $this->validate();

        $path = $this->file->store('documents', 'public');

        $article = Article::create([
            'title' => $this->file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $this->file->extension(),
        ]);

        try {
            $checkService = new FileProcessingService;
            $checkService->check($this->file, $article);

            $this->dispatch('notify', message: __('messages.notify.success.pdf-upload'));
            $this->file = null;
        } catch (\Exception $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.admin.pdf-uploader');
    }
}
