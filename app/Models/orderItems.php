<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class orderItems extends Model
{
    public function order()
    {
        return $this->belongsTo(orderTable::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected $fillable = [
        'order_table_id',
        'product_id',

        'product_name',

        'price',
        'quantity',
        'subtotal',
    ];
}
