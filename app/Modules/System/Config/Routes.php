<?php

namespace App\Modules\System\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->group('api/v1/system', ['namespace' => 'App\Modules\System\Controllers', 'filter' => 'auth'], static function (RouteCollection $routes) {
    // Roles
    $routes->get('roles', 'SystemController::getRoles');
    $routes->post('roles', 'SystemController::createRole');

    // Permissions
    $routes->get('permissions', 'SystemController::getPermissions');
    $routes->get('roles/(:num)/permissions', 'SystemController::getRolePermissions/$1');
    $routes->put('roles/(:num)/permissions', 'SystemController::updateRolePermissions/$1');
});
