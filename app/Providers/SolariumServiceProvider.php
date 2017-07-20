<?php

namespace Ipunkt\LaravelIndexer\Providers;

use Illuminate\Support\ServiceProvider;
use Solarium\Client;

class SolariumServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(Client::class, function () {
            return new Client(config('solarium'));
        });
    }

    public function provides()
    {
        return [Client::class];
    }
}
