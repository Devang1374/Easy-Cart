<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'title',
        'comment',
        'rating',
        'review',
        'is_approved',
        'helpful_count'
    ];

    protected $casts = [
        'is_approved' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $with = [
        'user',
    ];

    public function images()
    {
        return $this->hasMany(ReviewImage::class);
    }
}
