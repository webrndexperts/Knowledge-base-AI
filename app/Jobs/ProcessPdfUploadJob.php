<?php

namespace App\Jobs;

use App\Models\Article;
use App\Services\FileProcessingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;

class ProcessPdfUploadJob implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $article;

    protected $fileType;

    /**
     * Create a new job instance.
     */
    public function __construct(Article $article, string $fileType = 'pdf')
    {
        $this->article = $article;
        $this->fileType = $fileType;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $checkService = new FileProcessingService;
            $checkService->check($this->fileType, $this->article);
        } catch (\Throwable $th) {
            \Log::error('Error processing PDF upload job: '.$th->getMessage());
        }
    }
}
