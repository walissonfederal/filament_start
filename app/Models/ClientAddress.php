<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientAddress extends Model
{
    protected $table = 'client_address';

    protected $fillable = [
        'client_id',
        'zipcode',
        'street',
        'state',
        'city',
        'district',
        'complement',
        'number',
        'main',
    ];

    public function client() {
        return $this->hasOne(
            Client::class,
            "id",
            "client_id",
        );
    }
}
