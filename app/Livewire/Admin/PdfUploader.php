<?php

namespace App\Livewire\Admin;

use App\Jobs\ProcessPdfUploadJob;
use App\Models\Article;
use App\Models\User;
use App\Services\FileProcessingService;
use Livewire\Component;
use Livewire\WithFileUploads;

class PdfUploader extends Component
{
    use WithFileUploads;

    public $file;

    public $fileSelected = false;

    protected $rules = [
        'file' => 'required|mimes:pdf',
    ];

    public function mount()
    {
        if (! auth()->user()->can('uploadPdf', User::class)) {
            abort(403, __('messages.basic.permission-403'));
        }
    }

    public function updatedFile()
    {
        $this->fileSelected = ! empty($this->file);
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

        try {
            $path = $this->file->store('documents', 'public');

            $article = Article::create([
                'user_id' => auth()->id(),
                'title' => $this->file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $this->file->extension(),
                'meta' => [
                    'size_kb' => $this->file->getSize() / 1024,
                ],
            ]);

            try {
                // ProcessPdfUploadJob::dispatch($article, $this->file->extension());

                $checkService = new FileProcessingService;
                $checkService->check($article, $this->file->extension());

                $this->dispatch('notify', message: __('messages.notify.process.pdf-upload'), type: 'warning');
                $this->file = null;
                $this->fileSelected = false;
            } catch (\Exception $e) {
                $this->dispatch('notify', message: $e->getMessage(), type: 'error');
                \Log::error('Error dispatching PDF upload job: '.$e->getMessage());
            }
        } catch (\Throwable $th) {
            $this->dispatch('notify', message: __('messages.notify.upload.failed'), type: 'error');
            \Log::error('Error uploading PDF: '.$th->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.pdf-uploader');
    }
}
