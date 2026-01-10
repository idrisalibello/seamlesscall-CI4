<?php
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use App\Libraries\JWTHandler;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $jwt = new JWTHandler();
        $header = $request->getHeaderLine('Authorization');
        if (!$header || !preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            return service('response')->setStatusCode(401)->setJSON(['error'=>'No token provided']);
        }

        $token = $matches[1];
        $payload = $jwt->validateToken($token);
        if (!$payload) {
            return service('response')->setStatusCode(401)->setJSON(['error'=>'Invalid token']);
        }

        // Pass user info to request
        $request->user = $payload;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // no-op
    }
}
