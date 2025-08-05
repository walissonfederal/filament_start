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

    public function client() {
        return $this->hasOne(
            Client::class,
            "id",
            "client_id",
        );
    }
}
