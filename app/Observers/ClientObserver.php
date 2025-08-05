<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;

class ClientObserver
{
    public function creating(Model $model)
    {
        //
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
