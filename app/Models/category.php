<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

class category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'image',
        'image_id',
        'is_active',
    ];

    public function product(): HasMany
    {
        return $this->hasMany(product::class);
    }
}
