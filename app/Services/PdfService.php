<?php

namespace App\Services;

use Log;
use Smalot\PdfParser\Parser;
use Spatie\PdfToText\Pdf;
use thiagoalessio\TesseractOCR\TesseractOCR;

class PdfService
{
    protected string $pdftotextPath;

    public function __construct()
    {
        $this->pdftotextPath = config('app.pdftotext_path');
    }

    /**
     * Extract text from a PDF file.
     *
     * Workflow:
     * - Try native text extraction using Smalot PDF Parser.
     * - If the document has no extractable text (e.g., scanned images), fall back to OCR.
     *
     * @param  string  $filePath  Path to the PDF file.
     * @return string Extracted text (empty string on failure).
     */
    public function extractText(string $filePath): string
    {
        $text = '';
        try {
            $parser = new Parser;
            $pdf = $parser->parseFile($filePath);

            // First try normal text extraction
            $text = $pdf->getText();

            // If PDF has no extractable text, fall back to OCR
            if (trim($text) === '') {
                $text = $this->extractTextWithOCR($filePath);
            }
        } catch (\Exception $e) {
            Log::info('Error in PdfService->extractText', ['error' => $e->getMessage()]);
        }

        return $text;
    }

    /**
     * Extract native text from a single page using Poppler's pdftotext.
     *
     * @param  string  $pdfPath  Path to the PDF file.
     * @param  int  $pageNum  Page number (1-based index).
     * @return string Extracted text (empty string on failure).
     */
    public function extractNativeText(string $pdfPath, int $pageNum): string
    {
        try {
            $pdfPath = str_replace('/', DIRECTORY_SEPARATOR, $pdfPath);
            $text = (new Pdf($this->pdftotextPath))
                ->setPdf($pdfPath)
                // ->setPage($pageNum)
                ->text();

            return trim($text);
        } catch (\Exception $e) {
            Log::info('Error in PdfService->extractNativeText', ['error' => $e->getMessage()]);

            return '';
        }
    }

    /**
     * Perform OCR on a given Imagick-rendered PDF page.
     *
     * - Converts the page to PNG.
     * - Passes the image to Tesseract OCR.
     * - Cleans up temporary file.
     *
     * @param  \Imagick  $page  A single PDF page rendered via Imagick.
     * @return string Extracted OCR text (trimmed).
     */
    public function extractOCRFromPage(\Imagick $page): string
    {
        try {
            // Save page temporarily
            $tmpFile = tempnam(sys_get_temp_dir(), 'ocr_page_').'.png';
            $page->setImageFormat('png');
            $page->writeImage($tmpFile);

            try {
                $text = (new TesseractOCR($tmpFile))
                    ->run();
            } catch (\Exception $e) {
                Log::info('Error in PdfService->extractOCRFromPage (Tesseract)', ['error' => $e->getMessage()]);
                $text = '';
            }

            @unlink($tmpFile);

            return trim($text);
        } catch (\Exception $e) {
            Log::error('Error in PdfService->extractOCRFromPage (Image write)', ['error' => $e->getMessage()]);

            return '';
        }
    }

    /**
     * Perform OCR on an entire PDF document (all pages).
     *
     * Workflow:
     * - Render each page to a PNG via Imagick.
     * - Pass each image through Tesseract OCR.
     * - Concatenate results with line breaks.
     *
     * @param  string  $filePath  Path to the PDF file.
     * @return string OCR-extracted text from all pages.
     */
    public function extractTextWithOCR(string $filePath): string
    {
        $output = '';

        try {
            $imagick = new \Imagick;
            $imagick->readImage($filePath);

            foreach ($imagick as $i => $page) {
                $page->setImageFormat('png');
                $tmp = storage_path("app/tmp_page_{$i}.png");
                $page->writeImage($tmp);

                $ocr = (new TesseractOCR($tmp))
                    ->run();

                $output .= $ocr."\n";
                unlink($tmp);
            }

        } catch (\Exception $e) {
            Log::info('Error in PdfService->extractTextWithOCR', ['error' => $e->getMessage()]);
        }

        return $output;
    }
}
