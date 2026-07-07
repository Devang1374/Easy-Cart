<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Banner extends Model
{
    protected $fillable = [
        'title',
        'subtitle',

        'button_text',
        'button_link',

        'secondary_button_text',
        'secondary_button_link',

        'desktop_image',
        'mobile_image',
        
        'desktop_image_id',
        'mobile_image_id',

        'background_type',
        'background_color',

        'position',
        'sort_order',

        'is_active',

        'starts_at',
        'expires_at',
        'background_image',
        'background_image_id',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')
                  ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', now());
            });
    }
}
