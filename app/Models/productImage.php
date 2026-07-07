<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class productImage extends Model
{
    public function product()
    {
        return $this->belongsTo(product::class);
    }

    protected $fillable = [
        'image',
        'image_id',
        'sort_order'
    ];
}
