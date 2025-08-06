<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderService extends Model
{
    protected $table = 'order_service';

    protected $fillable = [
        'order_id',
        'service_id',
        'observations',
        'quantity',
        'price',
    ];
}
