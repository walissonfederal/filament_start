<?php

namespace App\Models;

use App\Observers\ClientObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected static function boot()
    {
        parent::boot();

        static::observe(ClientObserver::class);
    }

    protected $fillable = [
        'name',
        'document',
        'email',
        'people_type',
    ];

    public function contacts() {
        return $this->hasMany(
            ClientContact::class,
            "client_id",
            "id",
        );
    }

    public function addresses() {
        return $this->hasMany(
            ClientAddress::class,
            "client_id",
            "id",
        );
    }

    public function orders() {
        return $this->hasMany(
            Order::class,
            "client_id",
            "id",
        );
    }
}
