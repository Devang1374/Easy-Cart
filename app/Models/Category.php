<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;
//commit
class category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'image',
        'image_id',
        'image',
        'is_active',
    ];

    public function product(): HasMany
    {
        return $this->hasMany(product::class);
    }
}
