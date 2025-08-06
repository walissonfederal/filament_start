<?php

namespace App\Models;

use App\Models\Scopes\TransactionScope;
use App\Observers\TransactionObserver;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes, Filterable;

    protected $table = 'transactions';

    protected $fillable = [
        'creator_id',
        'order_id',
        'name',
        'necessary',
        'type',
        'value',
        'monthly',
        'date_initial_monthly',
        'date_finish_monthly',
        'due_day',
        'payment_day',
        'paid',
        'current_month',
        'recipient',
    ];

    protected static function boot()
    {
        parent::boot();

        self::observe(TransactionObserver::class);

        self::addGlobalScopes([new TransactionScope()]);
    }

    public function creator()
    {
        return $this->hasOne(
            User::class,
            "id",
            "creator_id",
        );
    }

    public function order()
    {
        return $this->hasOne(
            Order::class,
            "id",
            "order_id",
        );
    }
}
