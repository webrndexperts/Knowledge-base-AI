<?php

namespace App\Models\Articles;

use App\Models\Article as MainArticle;
use Illuminate\Database\Eloquent\Model;

class ArticlePage extends Model
{
    protected $fillable = [
        'article_id',
        'page_number',
        'native_text',
        'ocr_text',
    ];

    public function article()
    {
        return $this->belongsTo(MainArticle::class);
    }

    public function images()
    {
        return $this->hasMany(ArticleImage::class);
    }

    public function embeddings()
    {
        return $this->morphMany(Embedding::class, 'embeddable');
    }
}
