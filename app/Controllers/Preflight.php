<?php
namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class Preflight extends BaseController
{
    public function handle($any = null): ResponseInterface
    {
        $response = service('response');
        return $response
            ->setStatusCode(204)
            ->setHeader('Access-Control-Allow-Origin', getenv('CORS_ALLOWED_ORIGINS') ?: '*')
            ->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
            ->setHeader('Access-Control-Allow-Credentials', 'true');
    }
}
