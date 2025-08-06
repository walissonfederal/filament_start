<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $appends = ['total_product'];

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

    public function getTotalProductAttribute(): ?float
    {
        if (is_null($this->price) || is_null($this->quantity)) {
            return null;
        }

        return $this->price * $this->quantity;
    }
}
