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

    /**
     * Process a PDF file:
     * - Iterates through all pages with Imagick.
     * - Extracts native text (pdftotext) and OCR text (Tesseract).
     * - Stores each page in ArticlePage.
     * - Generates embeddings for the combined text.
     * - Extracts images embedded within each page.
     *
     * @return void
     */
    private function processPdf(Article $article, PdfService $pdfService, AIService $ai)
    {
        try {
            $imagick = new Imagick;
            $imagick->readImage($this->filePath);

            foreach ($imagick as $i => $page) {
                $pageNum = $i + 1;

                // Extract text from page (native text or OCR fallback)
                $nativeText = $pdfService->extractNativeText($this->filePath, $pageNum);
                $ocrText = $pdfService->extractOCRFromPage($page);

                // Save page
                $articlePage = ArticlePage::create([
                    'article_id' => $article->id,
                    'page_number' => $pageNum,
                    'native_text' => $nativeText,
                    'ocr_text' => $ocrText,
                ]);

                // Generate embedding for combined text
                $combinedText = $nativeText."\n".$ocrText;
                if (trim($combinedText) !== '') {
                    // $embedding = $ai->generateEmbedding($combinedText);
                    // Embedding::create([
                    //     'embeddable_id' => $articlePage->id,
                    //     'embeddable_type' => ArticlePage::class,
                    //     'embedding' => $embedding,
                    // ]);
                }

                // Extract embedded images inside page
                $this->extractImagesFromPage($page, $articlePage, $ai);
            }
        } catch (\Exception $e) {
            Log::info('Error in FileProcessingService->processPdf', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Process a standalone image file:
     * - Runs OCR with Tesseract.
     * - Saves result in ArticlePage.
     * - Generates embeddings for recognized text.
     *
     * @param  string  $path  Relative storage path of the image.
     * @return void
     */
    private function processImage(Article $article, string $path, AIService $ai)
    {
        try {
            // $ocr = (new TesseractOCR(storage_path("app/$path")))->run();
            $ocr = (new TesseractOCR(Storage::disk('public')->path("images/app/{$path}")))->run();

            $page = ArticlePage::create([
                'article_id' => $article->id,
                'page_number' => 1,
                'ocr_text' => $ocr,
            ]);

            // Generate embedding
            if (trim($ocr) !== '') {
                // $embedding = $ai->generateEmbedding($ocr);
                // Embedding::create([
                //     'embeddable_id' => $page->id,
                //     'embeddable_type' => ArticlePage::class,
                //     'embedding' => $embedding,
                // ]);
            }
        } catch (\Exception $e) {
            Log::info('Error in FileProcessingService->processImage', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Extract embedded images from a PDF page:
     * - Converts the page to PNG.
     * - Runs OCR on the image.
     * - Saves result in ArticleImage.
     * - Generates embeddings for recognized text.
     *
     * @return void
     */
    private function extractImagesFromPage(\Imagick $page, ArticlePage $articlePage, AIService $ai)
    {
        try {
            // Render each page as PNG to extract embedded images
            $folder = "images/{$articlePage->id}";
            $fileName = "tmp_page_{$articlePage->page_number}.png";

            Storage::disk('public')->makeDirectory($folder);

            $page->setImageFormat('png');
            $tmpPath = storage_path("app/{$fileName}");
            $path = storage_path("app/public/{$folder}/{$fileName}");

            $page->writeImage($tmpPath);
            $page->writeImage($path);

            // Run OCR on extracted image
            // $ocr = (new TesseractOCR($tmpPath))->run();
            $ocr = (new TesseractOCR($path))->run();

            if (trim($ocr) !== '') {
                $image = ArticleImage::create([
                    'article_page_id' => $articlePage->id,
                    'image_path' => "{$folder}/{$fileName}",
                    'ocr_text' => $ocr,
                ]);

                // $embedding = $ai->generateEmbedding($ocr);
                // Embedding::create([
                //     'embeddable_id' => $image->id,
                //     'embeddable_type' => ArticleImage::class,
                //     'embedding' => $embedding,
                // ]);
            }

            // unlink($tmpPath);
        } catch (\Exception $e) {
            Log::info('Error in FileProcessingService->extractImagesFromPage', ['error' => $e->getMessage()]);
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
    public function check($file, $article)
    {
        try {
            $pdfService = new PdfService;
            $ai = new AIService;
            $this->filePath = Storage::disk('public')->path($article->file_path);

            if ($file->extension() === 'pdf') {
                $this->processPdf($article, $pdfService, $ai);
            } else {
                $this->processImage($article, $this->filePath, $ai);
            }
        } catch (\Exception $e) {
            Log::info('Error in FileProcessingService->check', ['error' => $e->getMessage()]);
        }
    }
}
