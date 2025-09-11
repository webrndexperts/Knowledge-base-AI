<?php

namespace App\Models\Articles;

use Illuminate\Database\Eloquent\Model;

class ArticleImage extends Model
{
    protected $fillable = [
        'article_page_id',
        'image_path',
        'ocr_text',
    ];

    public function page()
    {
        return $this->belongsTo(ArticlePage::class);
    }

    public function embeddings()
    {
        return $this->morphMany(Embedding::class, 'embeddable');
    }
}
