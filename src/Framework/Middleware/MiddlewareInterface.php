<?php

declare(strict_types=1);

namespace Framework\Middleware;

use Framework\Http\Request;
use Framework\Http\Response;

interface MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response;
}
