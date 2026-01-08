<?php

namespace App\Application\Http\Middleware;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::REQUEST, priority: 8)]
class AuthValidationListener
{
    private string $validateUrl;

    public function __construct()
    {
        $this->validateUrl = getenv('AUTH_API') ?? 'https://default-auth-validate-url.com/auth/validate';
    }

    public function __invoke(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        $path = $request->getPathInfo() ?? '';
        if (!str_starts_with($path, '/api')) {
            return;
        }

        if ($request->getMethod() === 'OPTIONS') {
            return;
        }

        $authHeader = (string) $request->headers->get('Authorization', '');

        $isValid = $this->validateToken($authHeader);
        if (!$isValid) {
            $this->deny($event);
            return;
        }
    }

    private function deny(RequestEvent $event): void
    {
        $event->setResponse(new JsonResponse([
            'success' => false,
            'message' => 'Token expired or invalid',
        ], 401));
    }

    private function validateToken(string $token): bool
    {
        if (\function_exists('curl_init')) {
            $ch = curl_init($this->validateUrl);
            $payload = json_encode(['token' => $token]);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                ],
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_CONNECTTIMEOUT => 2,
                CURLOPT_TIMEOUT => 2,
            ]);
            $responseBody = curl_exec($ch);
            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200 || $responseBody === false) {
                return false;
            }

            $json = json_decode($responseBody, true);
            return is_array($json) && ($json['valid'] ?? ($json['success'] ?? false));
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => json_encode(['token' => $token]),
                'timeout' => 2,
                'ignore_errors' => true,
            ],
        ]);

        $responseBody = @file_get_contents($this->validateUrl, false, $context);
        $statusLine = $http_response_header[0] ?? '';
        if (!preg_match('/\s(\d{3})\s/', $statusLine, $m)) {
            return false;
        }
        $httpCode = (int) $m[1];
        if ($httpCode !== 200 || $responseBody === false) {
            return false;
        }
        $json = json_decode($responseBody, true);
        return is_array($json) && ($json['valid'] ?? ($json['success'] ?? false));
    }
}
