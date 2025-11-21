<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Articles\ArticleImage;
use App\Models\Articles\ArticlePage;
use App\Models\Articles\Embedding;
use Illuminate\Support\Facades\Storage;
use Imagick;
use Log;
use thiagoalessio\TesseractOCR\TesseractOCR;

class FileProcessingService
{
    protected $filePath;

    protected $imagePath;

    protected $ghostscriptAvailable = null;

    /**
     * Process a PDF file using Poppler utilities
     */
    private function processPdf(Article $article, PdfService $pdfService, AIService $ai)
    {
        try {
            // Extract text using pdftotext
            $text = $pdfService->extractNativeText($this->filePath, 1);
            
            if (empty(trim($text))) {
                Log::warning('No text extracted from PDF, trying OCR fallback', [
                    'article_id' => $article->id,
                ]);
                $text = $pdfService->extractTextWithOCR($this->filePath);
            }

            // Split text into pages using form feed character or by approximate page length
            $pages = str_split($text, 1500);
            
            foreach ($pages as $pageNum => $pageText) {
                $articlePage = ArticlePage::create([
                    'article_id' => $article->id,
                    'page_number' => $pageNum + 1,
                    'native_text' => $pageText,
                    'ocr_text' => '', // Will be filled if OCR is needed
                ]);

                // Generate embedding for the page
                $this->generateAndStoreEmbedding($articlePage, $pageText, $ai);

                // Extract and process images from this page using pdfimages
                $this->extractAndProcessImages($article, $articlePage, $ai, $pageNum + 1);
            }

        } catch (\Exception $e) {
            Log::error('Error in FileProcessingService->processPdf', [
                'error' => $e->getMessage(),
                'article_id' => $article->id,
                'file_path' => $this->filePath,
            ]);
            throw $e;
        }
    }

    /**
     * Extract and process images using pdfimages
     */
    private function extractAndProcessImages(Article $article, ArticlePage $articlePage, AIService $ai, int $pageNum): void
    {
        try {
            $pdfPath = $this->filePath;
            $outputDir = storage_path('app/public/pdf_images/' . pathinfo($pdfPath, PATHINFO_FILENAME) . '/page_' . $pageNum);

            if (!file_exists($outputDir)) {
                mkdir($outputDir, 0777, true);
            }

            // Use Poppler pdfimages command
            // The -j flag keeps images in their original format (JPEG, JPX, etc.)
            // The -f and -l flags specify first and last page to extract images from
            $pdfimages = 'pdfimages'; // Poppler binary (works if PATH is set)
            $command = sprintf('"%s" -j -f %d -l %d "%s" "%s"', $pdfimages, $pageNum, $pageNum, $pdfPath, $outputDir . '/image');
            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                \Log::error('There is no image in there: ' . implode("\n", $output));
                rmdir($outputDir);
            } else {

                // Collect results (jpg, png, jp2)
                $images = collect(glob($outputDir . '/*.{jpg,png,jp2}', GLOB_BRACE))
                    ->map(fn($img) => str_replace(storage_path('app/public/'), '', $img))
                    ->values()
                    ->toArray();
                
                if (empty($images)) {
                    // Delete the directory if no images were found
                    rmdir($outputDir);
                } else {
                    foreach ($images as $imageFile) {
                        $this->processImageFile($articlePage, $imageFile, $ai);
                    }
                }
            }

        } catch (\Exception $e) {
            Log::error('Error in extractAndProcessImages', [
                'error' => $e->getMessage(),
                'article_page_id' => $articlePage->id,
                'page_number' => $pageNum
            ]);
        }
    }

    /**
     * Process a single image file with OCR
     */
    private function processImageFile(ArticlePage $articlePage, string $imagePath, AIService $ai): void
    {
        try {
            $absolutePath = Storage::disk('public')->path($imagePath);

            if (!file_exists($absolutePath)) {
                Log::error('Image file missing at absolute path', [
                    'absolute_path' => $absolutePath,
                    'image_path' => $imagePath,
                ]);
                return;
            }
            
            // Run OCR on the image
            $ocrText = (new TesseractOCR($absolutePath))->run();

            if (empty(trim($ocrText))) {
                return;
            }

            // Create image record
            $image = ArticleImage::create([
                'article_page_id' => $articlePage->id,
                'image_path' => $imagePath,
                'ocr_text' => $ocrText,
            ]);

            // Generate and store embedding
            $this->generateAndStoreEmbedding($image, $ocrText, $ai, ArticleImage::class);

        } catch (\Exception $e) {
            Log::error('Error processing image file', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'image_path' => $imagePath
            ]);
        }
    }

    /**
     * Generate and store embedding for a model
     */
    private function generateAndStoreEmbedding($model, string $text, AIService $ai, string $type = null): void
    {
        try {
            $embedding = $ai->generateEmbedding($text);
            
            Embedding::create([
                'embeddable_id' => $model->id,
                'embeddable_type' => $type ?: get_class($model),
                'embedding' => $embedding,
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating embedding', [
                'error' => $e->getMessage(),
                'model_id' => $model->id,
                'model_type' => $type ?: get_class($model)
            ]);
            throw $e;
        }
    }

    /**
     * Entry point: check file type and delegate processing.
     * - If PDF → processPdf().
     * - Else (image) → processImage().
     *
     * @param  \Illuminate\Http\UploadedFile  $file  Uploaded file instance.
     * @param  Article  $article  Article model linked to the file.
     * @return void
     */
    public function check($article, $file = 'pdf')
    {
        try {
            $pdfService = new PdfService;
            $ai = new AIService;
            $this->filePath = Storage::disk('public')->path($article->file_path);

            \Log::info($this->filePath);

            if ($file === 'pdf') {
                $this->processPdf($article, $pdfService, $ai);
            } else {
                $this->processImage($article, $this->filePath, $ai);
            }
        } catch (\Exception $e) {
            Log::info('Error in FileProcessingService->check', ['error' => $e->getMessage()]);
        }
    }
}
