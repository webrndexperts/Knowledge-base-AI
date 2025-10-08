<?php

namespace App\Models\Articles;

use Illuminate\Database\Eloquent\Model;
use App\Models\Article;
use App\Models\Articles\ArticlePage;
use App\Models\Articles\Embedding;

class ArticleImage extends Model
{
    protected $fillable = [
        'article_page_id',
        'image_path',
        'ocr_text',
    ];

    public function page()
    {
        return $this->belongsTo(ArticlePage::class, 'article_page_id');
    }

    public function article()
    {
        return $this->hasOneThrough(
            Article::class,
            ArticlePage::class,
            'id',
            'id',
            'article_page_id',
            'article_id'
        );
    }

    public function embeddings()
    {
        return $this->morphMany(Embedding::class, 'embeddable');
    }
}
