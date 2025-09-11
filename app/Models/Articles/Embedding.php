<?php

namespace App\Models\Articles;

use Illuminate\Database\Eloquent\Model;

class Embedding extends Model
{
    protected $fillable = [
        'embeddable_id',
        'embeddable_type',
        'embedding',
    ];

    protected $casts = [
        'embedding' => 'array',
    ];

    public function embeddable()
    {
        return $this->morphTo();
    }
}
