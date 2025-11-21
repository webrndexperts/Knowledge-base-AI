<?php

namespace App\Services;

use App\Models\Articles\ArticleImage;
use App\Models\Articles\ArticlePage;
use App\Models\Articles\Embedding;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class AIService
{
    protected $maxTokens = 1500;

    protected $contextTokens = 0;

    protected $context = '';

    /**
     * Generate a vector embedding for a given text using OpenAI.
     *
     * @param  string  $text  Input text.
     * @return array<float> Embedding vector values (empty array on failure).
     */
    public function generateEmbedding(string $text): array
    {
        $values = [];

        // Log::info('Text in AIService->generateEmbedding', ['text' => $text]);

        try {
            $result = OpenAI::embeddings()->create([
                'model' => 'text-embedding-3-small',
                'input' => $text,
            ]);

            $values = $result['data'][0]['embedding'];
        } catch (\Exception $e) {
            Log::info('Error in AIService->generateEmbedding', ['error' => $e->getMessage()]);
        }

        return $values;
    }

    /**
     * Fast text-based search without AI processing.
     * Uses database LIKE queries for instant results.
     *
     * @param  string  $question  The user's search query.
     * @return array<string,mixed> [
     *                             'text'    => string Search results,
     *                             'sources' => array<int,array{id:int,type:string}> List of matched sources
     *                             ]
     */
    public function fastSearch(string $question): array
    {
        try {
            // Extract keywords from the question
            $keywords = $this->extractKeywords($question);

            if (empty($keywords)) {
                return ['text' => 'Please provide more specific search terms.', 'sources' => []];
            }

            $sources = [];
            $results = [];

            // Search in ArticlePages
            $pageQuery = ArticlePage::query();

            if (Auth::check()) {
                $pageQuery->whereHas('article', function ($q) {
                    $q->where('user_id', Auth::id());
                });
            }

            foreach ($keywords as $keyword) {
                $pageQuery->where(function ($q) use ($keyword) {
                    $q->where('native_text', 'ILIKE', "%{$keyword}%")
                        ->orWhere('ocr_text', 'ILIKE', "%{$keyword}%");
                });
            }

            $pages = $pageQuery->limit(5)->get();

            foreach ($pages as $page) {
                $sources[] = ['id' => $page->id, 'type' => 'ArticlePage'];
                $text = trim(($page->native_text ?? '').' '.($page->ocr_text ?? ''));
                if (! empty($text)) {
                    $results[] = $this->highlightKeywords($text, $keywords);
                }
            }

            // Search in ArticleImages
            $imageQuery = ArticleImage::query();

            if (Auth::check()) {
                $imageQuery->whereHas('page.article', function ($q) {
                    $q->where('user_id', Auth::id());
                });
            }

            foreach ($keywords as $keyword) {
                $imageQuery->where('ocr_text', 'ILIKE', "%{$keyword}%");
            }

            $images = $imageQuery->limit(3)->get();

            foreach ($images as $image) {
                $sources[] = ['id' => $image->id, 'type' => 'ArticleImage'];
                $text = trim($image->ocr_text ?? '');
                if (! empty($text)) {
                    $results[] = $this->highlightKeywords($text, $keywords);
                }
            }

            if (empty($results)) {
                return ['text' => 'No matching content found. Try different search terms or upload relevant documents.', 'sources' => []];
            }

            // Format results
            $responseText = "**Search Results:**\n\n";
            foreach (array_slice($results, 0, 3) as $index => $result) {
                $responseText .= '**Result '.($index + 1).":**\n";
                $responseText .= $result."\n\n";
            }

            if (count($results) > 3) {
                $responseText .= '*Found '.count($results).' total matches. Showing top 3 results.*';
            }

            return [
                'text' => $responseText,
                'sources' => array_slice($sources, 0, 8), // Limit sources
            ];

        } catch (\Exception $e) {
            Log::error('Error in AIService->fastSearch', [
                'error' => $e->getMessage(),
                'question' => $question,
            ]);

            return ['text' => 'Search error occurred. Please try again.', 'sources' => []];
        }
    }

    /**
     * Extract meaningful keywords from a search query.
     *
     * @return array<string>
     */
    private function extractKeywords(string $question): array
    {
        // Remove common stop words
        $stopWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'can', 'what', 'where', 'when', 'why', 'how', 'who', 'which'];

        // Clean and split the question
        $words = preg_split('/\s+/', strtolower(trim($question)));
        $keywords = [];

        foreach ($words as $word) {
            // Remove punctuation and keep only words with 3+ characters
            $word = preg_replace('/[^\w]/', '', $word);
            if (strlen($word) >= 3 && ! in_array($word, $stopWords)) {
                $keywords[] = $word;
            }
        }

        return array_unique($keywords);
    }

    /**
     * Highlight keywords in text for better readability.
     *
     * @param  array<string>  $keywords
     */
    private function highlightKeywords(string $text, array $keywords): string
    {
        // Limit text length for readability
        if (strlen($text) > 300) {
            // Try to find a good excerpt around the first keyword
            foreach ($keywords as $keyword) {
                $pos = stripos($text, $keyword);
                if ($pos !== false) {
                    $start = max(0, $pos - 100);
                    $text = '...'.substr($text, $start, 300).'...';
                    break;
                }
            }
            if (strlen($text) > 300) {
                $text = substr($text, 0, 300).'...';
            }
        }

        // Simple highlighting (you can enhance this)
        foreach ($keywords as $keyword) {
            $text = preg_replace('/('.preg_quote($keyword, '/').')/i', '**$1**', $text);
        }

        return $text;
    }

    /**
     * Answer a user's question using retrieval-augmented generation (RAG).
     *
     * Workflow:
     *  1. Generate an embedding for the question.
     *  2. Search the database for the most relevant embeddings using pgvector.
     *  3. Collect context text from matching ArticlePages and ArticleImages.
     *  4. Send the question + context to GPT for an answer.
     *
     * @param  string  $question  The user's natural language question.
     * @return array<string,mixed> [
     *                             'text'    => string Answer generated by GPT,
     *                             'sources' => array<int,array{id:int,type:string}> List of matched sources
     *                             ]
     */
    public function answerQuestion(string $question): array
    {
        $values = ['text' => '', 'sources' => []];

        // Validate OpenAI API key
        if (empty(config('openai.api_key'))) {
            Log::error('OpenAI API key is not configured');

            return ['text' => 'AI service is not configured. Please contact the administrator.', 'sources' => []];
        }

        try {
            // Step 1: Generate embedding for the question
            $qEmbedding = $this->generateEmbedding($question);

            if (empty($qEmbedding)) {
                Log::error('Failed to generate embedding for question');

                return ['text' => 'Unable to process your question. Please try again.', 'sources' => []];
            }

            $qEmbeddingStr = '['.implode(',', $qEmbedding).']';

            // Step 2: Build optimized query with pgvector operator (<->)
            $query = Embedding::selectRaw(
                'id, embeddable_id, embeddable_type, embedding <-> ? as distance',
                [$qEmbeddingStr]
            )->orderBy('distance', 'asc');

            if (Auth::check()) {
                $user = Auth::user();

                // Optimized filter: Load embeddings with article relationship
                $query->whereHasMorph(
                    'embeddable',
                    [ArticlePage::class, ArticleImage::class],
                    function ($q) use ($user) {
                        // For ArticlePage, check article.user_id directly
                        if ($q->getModel() instanceof ArticlePage) {
                            $q->whereHas('article', function ($qa) use ($user) {
                                $qa->where('user_id', $user->id);
                            });
                        }
                        // For ArticleImage, check through page->article
                        if ($q->getModel() instanceof ArticleImage) {
                            $q->whereHas('page.article', function ($qa) use ($user) {
                                $qa->where('user_id', $user->id);
                            });
                        }
                    }
                );
            }

            $results = $query->limit(5)->with('embeddable')->get();

            // Check if we have any results
            if ($results->isEmpty()) {
                return ['text' => 'No relevant information found. Please upload documents related to your question.', 'sources' => []];
            }

            // Step 3: Collect sources + context
            $sources = [];
            $context = '';

            foreach ($results as $embedding) {
                $embeddable = $embedding->embeddable;

                if ($embeddable) {
                    $sources[] = [
                        'id' => $embedding->embeddable_id,
                        'type' => class_basename($embedding->embeddable_type),
                    ];

                    if ($embeddable instanceof ArticlePage) {
                        $context .= "\n".trim(($embeddable->native_text ?? '').' '.($embeddable->ocr_text ?? ''));
                    } elseif ($embeddable instanceof ArticleImage) {
                        $context .= "\n".trim($embeddable->ocr_text ?? '');
                    }
                }
            }

            // Trim context to avoid token limits
            $context = trim($context);
            if (empty($context)) {
                return ['text' => 'No text content found in the uploaded documents.', 'sources' => []];
            }

            // Step 4: Ask GPT with timeout and error handling
            $completion = OpenAI::chat()->create([
                'model' => config('openai.model', 'gpt-4o-mini'),
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful assistant. Answer questions based only on the provided context. If the context does not contain relevant information, say so.'],
                    ['role' => 'user', 'content' => "Question: {$question}\n\nContext:\n{$context}"],
                ],
                'max_tokens' => 500,
                'temperature' => 0.7,
            ]);

            $answer = $completion['choices'][0]['message']['content'] ?? '';

            $values = [
                'text' => $answer ?: 'Unable to generate a response.',
                'sources' => $sources,
            ];
        } catch (\OpenAI\Exceptions\ErrorException $e) {
            Log::error('OpenAI API Error in answerQuestion', [
                'error' => $e->getMessage(),
                'question' => $question,
            ]);

            return ['text' => 'AI service is temporarily unavailable. Please try again later.', 'sources' => []];
        } catch (\Exception $e) {
            Log::error('Error in AIService->answerQuestion', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ['text' => 'An error occurred while processing your question.', 'sources' => []];
        }

        return $values;
    }
}
