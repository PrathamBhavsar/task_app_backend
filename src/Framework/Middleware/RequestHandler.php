<?php

declare(strict_types=1);

namespace Framework\Middleware;

use Framework\Http\Request;
use Framework\Http\Response;

interface RequestHandler
{
    public function handle(Request $request): Response;
}
