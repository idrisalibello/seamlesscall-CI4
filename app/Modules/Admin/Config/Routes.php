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

    // Routes for Categories (still auth protected)
    $routes->resource('categories', ['controller' => 'CategoryController']);

    // Services GET route - TEMPORARILY UNFILTERED FOR DIAGNOSIS
    $routes->get('categories/(:num)/services', 'ServiceController::index/$1', ['filter' => '']); 

    // Services POST, PUT, DELETE routes (still auth protected)
    $routes->post('categories/(:num)/services', 'ServiceController::create/$1');
    $routes->put('services/(:num)', 'ServiceController::update/$1');
    $routes->delete('services/(:num)', 'ServiceController::delete/$1');
});

// REMOVING TEMPORARY DIAGNOSTIC GROUP
// $routes->group('api/v1/admin', ['namespace' => 'App\Modules\Admin\Controllers'], function ($routes) {
//     $routes->get('categories/(:num)/services', 'ServiceController::index/$1');
// });

