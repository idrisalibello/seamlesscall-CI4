<?php

$routes->group('api/v1/admin', [
    'namespace' => 'App\Modules\Admin\Controllers',
    'filter'    => 'auth',
], function ($routes) {
    // ... existing routes
    $routes->get('provider-applications', 'AdminController::getProviderApplications');
    $routes->post('provider-applications/status', 'AdminController::approveOrRejectProvider');
    $routes->post('users', 'AdminController::createAdmin'); // New route for creating admin users
    $routes->get('customers', 'AdminController::getCustomers');
    $routes->get('providers', 'AdminController::getProviders');
    $routes->get('users/(:num)', 'AdminController::getUserDetails/$1');
    $routes->get('users/(:num)/ledger', 'AdminController::getUserLedger/$1');
    $routes->get('users/(:num)/refunds', 'AdminController::getUserRefunds/$1');
    $routes->get('users/(:num)/activity', 'AdminController::getUserActivityLog/$1');
    $routes->get('providers/(:num)/earnings', 'AdminController::getProviderEarnings/$1');
    $routes->get('providers/(:num)/payouts', 'AdminController::getProviderPayouts/$1');

    // Services GET route (moved before categories resource for correct precedence)
    $routes->get('categories/(:num)/services', 'ServiceController::index/$1');

    // Routes for Categories (moved after specific service route)
    $routes->resource('categories', ['controller' => 'CategoryController']);

    // Services POST, PUT, DELETE routes (these are for individual service management)
    $routes->post('categories/(:num)/services', 'ServiceController::create/$1');
    $routes->put('services/(:num)', 'ServiceController::update/$1');
    $routes->delete('services/(:num)', 'ServiceController::delete/$1');

    // User Management (for Roles & Permissions)
    $routes->get('users', 'AdminController::getUsers');
    $routes->put('users/(:num)', 'AdminController::updateUser/$1');
    $routes->get('users/(:num)/roles', 'AdminController::getUserRoles/$1');
    $routes->put('users/(:num)/roles', 'AdminController::updateUserRoles/$1');

    // Verification Queue
    $routes->get('verification-queue', 'VerificationQueueController::index');
    $routes->get('verification-queue/(:num)', 'VerificationQueueController::show/$1');
    $routes->post('verification-queue/(:num)/approve', 'VerificationQueueController::approve/$1');
    $routes->post('verification-queue/(:num)/reject', 'VerificationQueueController::reject/$1');
    $routes->post('verification-queue/(:num)/escalate', 'VerificationQueueController::escalate/$1');
});

