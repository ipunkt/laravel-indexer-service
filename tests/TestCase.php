<?php

namespace Tests;

use Faker\Generator;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Ipunkt\LaravelJsonApi\Contracts\RequestHandlers\ApiRequestHandler;
use Ipunkt\LaravelJsonApi\Testing\Concerns\InteractsWithAuthentication;
use Ipunkt\LaravelJsonApi\Testing\Concerns\PreparesRequestBody;

abstract class TestCase extends BaseTestCase
{
    use PreparesRequestBody;
    use CreatesApplication;
    use InteractsWithAuthentication;

    /**
     * returns headers
     *
     * @return array
     */
    protected function headers() : array
    {
        $headers = [
            'Accept' => ApiRequestHandler::CONTENT_TYPE,
            'Content-Type' => ApiRequestHandler::CONTENT_TYPE,
        ];

        if (!empty(static::$token)) {
            $headers['Authorization'] = 'Token ' . static::$token;
        }

        return $headers;
    }

    protected function faker() : Generator
    {
        return app(Generator::class);
    }
}
