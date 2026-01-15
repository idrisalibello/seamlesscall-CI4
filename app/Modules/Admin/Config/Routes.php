<?php

$routes->group('api/v1/admin', [
    'namespace' => 'App\Modules\Admin\Controllers',
    'filter'    => 'auth',
], function ($routes) {
    $routes->get('provider-applications', 'AdminController::getProviderApplications');
    $routes->post('provider-applications/status', 'AdminController::approveOrRejectProvider');
    $routes->post('users', 'AdminController::createAdmin'); // New route for creating admin users
});
