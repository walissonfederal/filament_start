<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;

class ClientObserver
{
    public function creating(Model $model)
    {
        $model->document = str_replace([".","-","/"], ["","",""], $model->document);
    }

    public function created(Model $model): void
    {
        $model->document = str_replace([".","-","/"], ["","",""], $model->document);
    }

    public function updating(Model $model): void
    {
        $model->document = str_replace([".","-","/"], ["","",""], $model->document);
    }

    public function updated(Model $model): void
    {
        $model->document = str_replace([".","-","/"], ["","",""], $model->document);
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
