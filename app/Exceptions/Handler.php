<?php

namespace Ipunkt\LaravelIndexer\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Ipunkt\LaravelJsonApi\Contracts\RequestHandlers\ApiRequestHandler;
use Ipunkt\LaravelJsonApi\Exceptions\JsonApiError;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        if (app()->bound('sentry') && $this->shouldReport($exception)) {
            app('sentry')->captureException($exception);
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($request->expectsJson() || $request->headers->contains('accept', ApiRequestHandler::CONTENT_TYPE)) {
            $error = new JsonApiError($exception->getMessage());

            if ($exception->getCode() > 100) {
                $error->setCode($exception->getCode());
            }

            if ($exception instanceof ModelNotFoundException || $exception instanceof NotFoundHttpException) {
                $error->setStatusCode(404)
                    ->setTitle('Resource not found');
            }

            if ($exception instanceof AuthorizationException) {
                $error->setStatusCode(403)
                    ->setTitle('Access forbidden');
            }

            if ($exception instanceof ValidationException) {
                $validationErrors = collect();
                foreach ($exception->validator->errors()->keys() as $key) {
                    $validationErrors->push([
                        'pointer' => $key,
                        'message' => str_replace('attributes.', '', $exception->validator->errors()->first($key)),
                    ]);
                }
                $error->setSource($validationErrors);
            }

            if ($exception instanceof HttpExceptionInterface) {
                $error->setStatusCode($exception->getStatusCode());
            }

            if (app()->environment('local')) {
                $error->setException($exception);
            }

            return response()->json(['errors' => [$error]], $error->getStatusCode());
        }

        return parent::render($request, $exception);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Illuminate\Auth\AuthenticationException $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson() || $request->headers->contains('accept', ApiRequestHandler::CONTENT_TYPE)) {
            $error = new JsonApiError('Unauthenticated');
            $error->setStatusCode(401);

            return response()->json(['errors' => [$error]], $error->getStatusCode());
        }

        return redirect()->guest(route('login'));
    }
}
