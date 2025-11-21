<?php

namespace App\Models;

use App\Models\Articles\ArticlePage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
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

    public function scopeWithRoles($query)
    {
        if (! auth()->user()->isAdmin()) {
            $query->where('user_id', auth()->id());
        }

        return $query;
    }
}
