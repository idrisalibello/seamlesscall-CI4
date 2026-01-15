<?php

$routes->group('api/v1', ['namespace' => 'App\Modules\Auth\Controllers'], function ($routes) {
    // Registration & Login
    $routes->post('register', 'AuthController::register');
    $routes->post('login', 'AuthController::login');

    // OTP Authentication Flow
    $routes->post('auth/otp/request', 'AuthController::requestLoginOtp');
    $routes->post('auth/otp/login', 'AuthController::loginWithOtp');

    // Provider application
    $routes->post('auth/apply-as-provider', 'AuthController::applyAsProvider', ['filter' => 'auth']);
});


