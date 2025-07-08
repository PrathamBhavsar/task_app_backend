<?php

namespace Interface\Http;

class JsonResponse
{
    public static function ok($data, int $status = 200): void
    {
        http_response_code($status);
        echo json_encode(["data" => $data]);
        exit;
    }

    public static function error(string $message, int $code = 400): void
    {
        http_response_code($code);
        echo json_encode(["error" => ["message" => $message]]);
        exit;
    }
}
