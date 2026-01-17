<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class Cors implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Preflight requests are now handled by the server (.htaccess).
        // This filter will no longer intercept OPTIONS requests.
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Headers are now exclusively handled by the root .htaccess file.
        // This method no longer adds headers to prevent duplication.
        return $response;
    }
}