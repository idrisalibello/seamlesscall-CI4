<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->group('api/v1/customer', [
    'namespace' => 'App\Modules\Customers\Controllers',
    'filter'    => 'auth',
], static function (RouteCollection $routes) {
    $routes->get('categories', 'CustomerController::getCategories');
    $routes->get('categories/(:num)/services', 'CustomerController::getServicesByCategory/$1');
    $routes->get('services/(:num)', 'CustomerController::getServiceDetails/$1');

    $routes->post('bookings', 'CustomerController::createBooking');
    $routes->get('bookings', 'CustomerController::getMyBookings');
    $routes->get('bookings/(:num)', 'CustomerController::getBookingDetails/$1');

    $routes->post('payments/initialize', 'CustomerController::initializeInspectionPayment');
    $routes->get('payments/verify/(:segment)', 'CustomerController::verifyPayment/$1');
});

$routes->post('api/v1/paystack/webhook', 'CustomerController::paystackWebhook', [
    'namespace' => 'App\Modules\Customers\Controllers',
]);