<?php

namespace Ipunkt\LaravelIndexer\Http\Middleware;

use Closure;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Ipunkt\LaravelJsonApi\Exceptions\JsonApiError;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class VerifyFixedToken
{
    /**
     * @var ResponseFactory
     */
    private $response;

    public function __construct(ResponseFactory $response)
    {
        $this->response = $response;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $secureToken = config('json-api.routes.secure-route.token', 'vf2AdKzPBqgNne5YA7yfRwWZnj5R43fa');
        try {
            $requestToken = $this->getRequestToken($request);

            if ($requestToken !== $secureToken) {
                throw new BadRequestHttpException('Token invalid');
            }
        } catch (HttpException $e) {
            $jsonApiError = new JsonApiError($e->getMessage(), $e->getCode());
            $jsonApiError->setStatusCode($e->getStatusCode());

            return $this->response->json(['errors' => [$jsonApiError]], $jsonApiError->getStatusCode());
        }

        return $next($request);
    }

    /**
     * returns request token
     *
     * @param Request $request
     * @return string
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    private function getRequestToken(Request $request) : string
    {
        $token = $request->header('Authorization');

        if (!starts_with($token, 'Token ')) {
            throw new BadRequestHttpException('Token invalid');
        }

        return Str::substr($token, 6);
    }
}
