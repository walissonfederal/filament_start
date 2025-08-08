<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrap();

        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '-1');
        ini_set('max_input_vars', '-1');
        ini_set('upload_max_filesize', '256M');
        ini_set('client_max_body_size ', '256M');
        ini_set('upload_max_size', '256M');
        ini_set('post_max_size', '256M');
        ini_set('max_execution_time', '600');

        date_default_timezone_set("America/Sao_Paulo");

        if (env("APP_ENV") == "local") {
            setlocale(LC_ALL, "pt_BR", "pt_BR.utf-8", "pt_BR.utf-8", "portuguese");
        }
        if (env("APP_ENV") == "production") {
            setlocale(LC_TIME, 'pt_BR.utf8');
        }
        \Carbon\Carbon::setLocale('pt_BR');

        Schema::defaultStringLength(191);

        if (env('APP_AMBIENT') == 'production') {
            URL::forceScheme('https');
        }

        Model::unguard();

        Model::preventLazyLoading();
    }
}
