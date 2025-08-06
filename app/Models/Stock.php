<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $fillable = [
        'product_id',
        'observations',
        'quantity',
        'price',
        'entry_date',
        'type_transaction',
    ];

    public function product()
    {
        return $this->hasOne(
            Product::class,
            'id',
            'product_id',
        );
    }
}
