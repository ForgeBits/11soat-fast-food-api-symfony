<?php

namespace App\Application\Helpers;

use Symfony\Component\HttpFoundation\JsonResponse;

class ApiResponse
{
    public static function success(array $data = [], string $message = 'OK', int $code = 200): JsonResponse
    {
        return new JsonResponse([
            'status'  => 'success',
            'code'    => 200,
            'message' => $message,
            'data'    => $data,
        ]);
    }

    public static function warning(string $message, int $code = 400): JsonResponse
    {
        return new JsonResponse([
            'status'  => 'warning',
            'message' => $message,
            'code'    => $code,
        ]);
    }

    public static function error(string $message, array $errors = [], int $code = 500): JsonResponse
    {
        return new JsonResponse([
            'status'  => 'error',
            'code'    => $code,
            'message' => $message,
            'errors'  => $errors,
        ]);
    }
}
