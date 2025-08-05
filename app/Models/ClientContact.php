<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientContact extends Model
{
    protected $table = 'client_contact';

    protected $fillable = [
        'client_id',
        'prefix_international',
        'prefix',
        'number',
        'name',
    ];

    public function client() {
        return $this->hasOne(
            Client::class,
            "id",
            "client_id",
        );
    }
}
