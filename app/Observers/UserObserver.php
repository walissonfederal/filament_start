<?php

namespace App\Observers;

use App\Jobs\SendMailRegisterUserJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class UserObserver
{
    public function creating(Model $model)
    {
        //
    }

    public function created(Model $model): void
    {
        if (App::runningInConsole()) {
            return;
        }

        $passwordPlain = Str::random(10);
        $model->update(['password' => bcrypt($passwordPlain)]);
        SendMailRegisterUserJob::dispatch(
            $model,
            $passwordPlain
        );
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
