<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'Home::index');
$routes->get('testotp/send', 'TestEmail::index');
$routes->post('auth/oauth', 'AuthController::oauth');

$moduleRouteFiles = [
    APPPATH . 'Modules/Auth/Config/Routes.php',
    APPPATH . 'Modules/Dashboard/Config/Routes.php',
    APPPATH . 'Modules/Admin/Config/Routes.php',
    APPPATH . 'Modules/System/Config/Routes.php',
    APPPATH . 'Modules/Operations/Config/Routes.php',
    APPPATH . 'Modules/Customer/Config/Routes.php',
];

foreach ($moduleRouteFiles as $routeFile) {
    if (is_file($routeFile)) {
        require $routeFile;
    }
}