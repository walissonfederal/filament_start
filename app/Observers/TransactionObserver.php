<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;

class TransactionObserver
{
    public $updates = [];

    public function __construct()
    {
        $this->updates = request()->all()["components"][0]["updates"];
    }

    public function creating(Model $model)
    {
        $model->creator_id = auth()->id();
    }

    public function saved(Model $model)
    {
        //
    }

    public function saving(Model $model)
    {
        if (!isset($day) && $model->paid) {
            $model->payment_day = now()->format("d");
        }

        $day = $this->updates["mountedTableActionsData.0.payment_day"] ?? null;

        if ($day) {
            $model->payment_day = $day;
        }

        if ($model->paid === false) {
            $model->payment_day = null;
        }
    }

    public function created(Model $model): void
    {
        //
    }

    public function updated(Model $model): void
    {
        //
    }

    public function deleted(Model $model): void
    {
        //
    }

    public function restored(Model $model): void
    {
        //
    }

    public function forceDeleted(Model $model): void
    {
        //
    }

    public function deleting(Model $model): void
    {
        //
    }
}
