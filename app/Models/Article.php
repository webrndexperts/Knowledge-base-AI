<?php

namespace App\Models;

use App\Models\Articles\ArticlePage;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = [
        'title',
        'file_path',
        'file_type',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function pages()
    {
        return $this->hasMany(ArticlePage::class);
    }
}
