<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewImage extends Model
{
    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    protected $fillable = [
        'image',
        'image_id'
    ];
}
