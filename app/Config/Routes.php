<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('testotp/send', 'TestEmail::index');
$routes->post('auth/oauth', 'AuthController::oauth');


require APPPATH.'Modules/Auth/Config/Routes.php';
require APPPATH.'Modules/Dashboard/Config/Routes.php';
require APPPATH.'Modules/Admin/Config/Routes.php';
require APPPATH.'Modules/System/Config/Routes.php';
require APPPATH.'Modules/Operations/Config/Routes.php';

