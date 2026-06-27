<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAddress extends Model
{
        protected $fillable = [
        'user_id',
        'title',
        'first_name',
        'last_name',
        'phone',
        'address',
        'city',
        'state',
        'pincode',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
