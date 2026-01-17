<?php

$routes->group('api/v1', ['namespace' => 'App\Modules\Auth\Controllers'], function ($routes) {
    // Normal API routes
    $routes->post('register', 'AuthController::register');
    $routes->post('login', 'AuthController::login');
    $routes->post('auth/otp/request', 'AuthController::requestLoginOtp');
    $routes->post('auth/otp/login', 'AuthController::loginWithOtp');
    $routes->post('auth/apply-as-provider', 'AuthController::applyAsProvider', ['filter' => 'auth']);
});


