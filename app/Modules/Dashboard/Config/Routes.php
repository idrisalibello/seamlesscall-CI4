<?php

$routes->group('api/v1/dashboard', [
    'namespace' => 'App\Modules\Dashboard\Controllers',
    'filter'    => 'auth',
], function ($routes) {
    $routes->get('stats', 'DashboardController::stats');
});
