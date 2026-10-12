<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_id',
        'ref',
        'product_name',
        'variant',
        'qty',
        'amount',
        'customer_name',
        'customer_email',
        'customer_phone',
        'payment_method',
        'status',
        'accounts',
        'note',
        'qr_url',
    ];

    protected $casts = [
        'accounts' => 'array',
    ];
}
