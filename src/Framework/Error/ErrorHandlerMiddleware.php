<?php

declare(strict_types=1);

namespace Framework\Error;

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Middleware\MiddlewareInterface;
use Framework\Middleware\RequestHandler;

class ErrorHandlerMiddleware implements MiddlewareInterface
{
    private ErrorHandler $errorHandler;

    public function __construct(ErrorHandler $errorHandler)
    {
        $this->errorHandler = $errorHandler;
    }

    /**
     * Process the request and catch any exceptions
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        try {
            return $handler->handle($request);
        } catch (\Throwable $e) {
            return $this->errorHandler->handle($e, $request);
        }
    }
}
