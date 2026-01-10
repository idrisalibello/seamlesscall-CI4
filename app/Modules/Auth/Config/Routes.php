<?php

$routes->group('api/v1', ['namespace' => 'App\Modules\Auth\Controllers'], function ($routes) {
    $routes->options('register', fn() => response()->setStatusCode(200));
    $routes->post('register', 'AuthController::register');

    $routes->options('login', fn() => response()->setStatusCode(200));
    $routes->post('login', 'AuthController::login');
});

$routes->group('auth', ['namespace' => 'App\Modules\Auth\Controllers'], function($routes) {

    // Public-facing provider application
    $routes->post('provider/apply', 'AuthController::applyProvider');

    // Admin-only routes (apply auth filter)
    $routes->group('admin', ['filter' => 'auth'], function($routes) {
        // Approve pending provider
        $routes->post('provider/approve/(:num)', 'AuthController::approveProvider/$1');

        // Create provider directly
        $routes->post('provider/create', 'AuthController::createProvider');

        // Create admin
        $routes->post('admin/create', 'AuthController::createAdmin');
    });

});

$routes->post('auth/request-email-otp', 'AuthController::requestEmailOtp');
$routes->post('auth/verify-otp', 'AuthController::verifyOtp');


