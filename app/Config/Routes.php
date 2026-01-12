<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('testotp/send', 'TestEmail::index');

require APPPATH.'Modules/Auth/Config/Routes.php';

