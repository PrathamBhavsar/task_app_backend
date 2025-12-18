<?php

declare(strict_types=1);

namespace Framework\Middleware;

use Framework\Http\Request;
use Framework\Http\Response;

class MiddlewarePipeline implements RequestHandler
{
    private array $middleware = [];
    private ?RequestHandler $finalHandler = null;

    public function pipe(MiddlewareInterface $middleware): self
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    public function then(RequestHandler $handler): self
    {
        $this->finalHandler = $handler;
        return $this;
    }

    public function handle(Request $request): Response
    {
        return $this->process($request, $this->finalHandler ?? new class implements RequestHandler {
            public function handle(Request $request): Response
            {
                return new Response(['message' => 'No handler defined'], 500);
            }
        });
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        if (empty($this->middleware)) {
            return $handler->handle($request);
        }

        $middleware = array_shift($this->middleware);
        
        return $middleware->process($request, $this);
    }
}
