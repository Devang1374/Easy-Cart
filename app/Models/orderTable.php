<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class orderTable extends Model
{
    public function items()
    {
        return $this->hasMany(orderItems::class, 'order_table_id');
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $fillable = [
        'user_id',
        'order_number',

        'first_name',
        'last_name',

        'email',
        'phone',

        'address',
        'city',
        'state',
        'pincode',

        'total_amount',
        'status',

        'cf_payment_id',
        'pyment',
        'cashfree_order_id',

        'coupon_id',
        'coupon_code',
        'discount_amount',

        'return_requested',
        'return_status',
        'return_reason',
        'return_admin_note',
        'return_requested_at',
        'return_completed_at',
    ];

    protected $casts = [
        'return_requested'    => 'boolean',
    
        'return_requested_at' => 'datetime',
        'return_completed_at' => 'datetime',
    
        'created_at'          => 'datetime',
        'updated_at'          => 'datetime',
    ];
}
