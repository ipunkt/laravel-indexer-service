<?php

namespace Ipunkt\LaravelIndexer\Providers;

use Illuminate\Support\ServiceProvider;
use Ipunkt\LaravelJsonApi\Resources\ResourceDefinition;
use Ipunkt\LaravelJsonApi\Resources\ResourceManager;
use Ipunkt\LaravelIndexer\Http\Api\Items\ItemsRequestHandler;

class ApiResourceServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @param ResourceManager $resourceManager
     * @return void
     */
    public function boot(ResourceManager $resourceManager)
    {
        $resourceManager->version(1)
            ->define('items', function (ResourceDefinition $resource) {
                $resource->setRequestHandler(ItemsRequestHandler::class)
                    ->addMiddleware('fixed.token');
            });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
