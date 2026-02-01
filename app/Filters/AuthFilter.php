<?php

namespace App\Filters;

use App\Libraries\JWTHandler;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Let CORS preflight through
        if ($request->getMethod() === 'options') {
            return null;
        }

        $header = $request->getHeaderLine('Authorization');

        if (!$header || !preg_match('/Bearer\s+(\S+)/', $header, $matches)) {
            return Services::response()
                ->setStatusCode(401)
                ->setJSON(['error' => 'Authorization token missing']);
        }

        $token = $matches[1];

        try {
            $jwt = new JWTHandler();
        } catch (\Throwable $e) {
            // Don’t crash the app with 500 stack traces.
            log_message('error', 'JWT init failed: ' . $e->getMessage());
            return Services::response()
                ->setStatusCode(500)
                ->setJSON(['error' => 'Auth misconfiguration: JWT secret not loaded']);
        }

        $payload = $jwt->validateToken($token);

        if (!$payload) {
            return Services::response()
                ->setStatusCode(401)
                ->setJSON(['error' => 'Invalid or expired token']);
        }

        log_message('debug', 'AuthFilter Payload: ' . json_encode($payload));

        $request->auth_payload = $payload;

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}
