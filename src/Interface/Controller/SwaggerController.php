<?php

declare(strict_types=1);

namespace Interface\Controller;

use Framework\Http\Request;
use Framework\Http\Response;

/**
 * Swagger UI Controller
 * Serves OpenAPI documentation
 */
class SwaggerController
{
    /**
     * Serve Swagger UI HTML page
     */
    public function ui(Request $request): Response
    {
        $html = $this->getSwaggerUIHtml();
        
        return new Response($html, 200, [
            'Content-Type' => 'text/html; charset=utf-8'
        ]);
    }

    /**
     * Serve OpenAPI JSON specification from static file
     */
    public function json(Request $request): Response
    {
        $projectRoot = dirname(__DIR__, 3);
        $openApiFile = $projectRoot . '/public/openapi.json';
        
        if (!file_exists($openApiFile)) {
            return new Response(
                ['error' => 'OpenAPI specification file not found'],
                404,
                ['Content-Type' => 'application/json']
            );
        }
        
        $content = file_get_contents($openApiFile);
        
        return new Response(
            $content,
            200,
            ['Content-Type' => 'application/json']
        );
    }

    private function getSwaggerUIHtml(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task App API Documentation</title>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
    <style>
        html { box-sizing: border-box; overflow-y: scroll; }
        *, *:before, *:after { box-sizing: inherit; }
        body { margin: 0; background: #fafafa; }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            const ui = SwaggerUIBundle({
                url: window.location.origin + "/swagger/json",
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout"
            });
            window.ui = ui;
        };
    </script>
</body>
</html>
HTML;
    }
}
