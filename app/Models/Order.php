<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'client_id',
        'reference',
        'type',
        'status',
        'total_price',
    ];

    public function client()
    {
        return $this->hasOne(
            Client::class,
            "id",
            "client_id",
        );
    }

    public function products()
    {
        return $this->belongsToMany(
            Product::class,
            'order_product',
            'order_id',
            'product_id',
        )->withPivot([
            'order_id',
            'product_id',
            'observations',
            'quantity',
            'price',
        ]);
    }

    public function services()
    {
        return $this->belongsToMany(
            Service::class,
            'order_service',
            'order_id',
            'service_id',
        )->withPivot([
            'order_id',
            'service_id',
            'observations',
            'quantity',
            'price',
        ]);
    }
}
