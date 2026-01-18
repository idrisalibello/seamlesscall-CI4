<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use App\Libraries\JWTHandler;
use Config\Services;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // ✅ Let CORS preflight through unchallenged
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

        $jwt = new JWTHandler();
        $payload = $jwt->validateToken($token);

        if (!$payload) {
            return Services::response()
                ->setStatusCode(401)
                ->setJSON(['error' => 'Invalid or expired token']);
        }

        log_message('debug', 'AuthFilter Payload: ' . json_encode($payload));

        // Attach user payload to the request object
        $request->auth_payload = $payload;

        return null;
    }

    public function after(
        RequestInterface $request,
        ResponseInterface $response,
        $arguments = null
    ) {
        return $response;
    }
}
