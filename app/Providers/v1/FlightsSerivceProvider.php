<?php

namespace App\Providers\v1;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use App\Services\v1\FlightsService;

class FlightsSerivceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(FlightsService::class, function ($app) {
            return new FlightsService();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('flight_status', function ($attribute, $value, $params, $validator) {
            return $value == 'delayed' || $value == 'on-time';
        });
    }
}
