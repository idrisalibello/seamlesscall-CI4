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

    // Popular services — ranked by bookings, ratings, views
    $routes->get('services/popular', 'PopularServicesController::index');
    // View tracking — called when customer opens a service detail screen
    $routes->post('services/(:num)/view', 'PopularServicesController::recordView/$1');

    $routes->post('bookings', 'CustomerController::createBooking');
    $routes->get('bookings', 'CustomerController::getMyBookings');
    $routes->get('bookings/(:num)', 'CustomerController::getBookingDetails/$1');

    $routes->post('payments/initialize', 'CustomerController::initializeInspectionPayment');
    $routes->get('payments/verify/(:segment)', 'CustomerController::verifyPayment/$1');

    // Promotion validation — called from booking summary screen
    $routes->post('promotions/validate', 'PromotionValidationController::validate');
});

$routes->post('api/v1/paystack/webhook', 'CustomerController::paystackWebhook', [
    'namespace' => 'App\Modules\Customers\Controllers',
]);
$routes->group('api/v1/customer/chat', [
    'namespace' => 'App\Modules\Customers\Controllers',
    'filter'    => 'auth',
], static function (RouteCollection $routes) {
 
    // Load message history (paginated)
    // GET /api/v1/customer/chat/messages?limit=50&offset=0
    $routes->get('messages', 'ChatController::getMessages');
 
    // Send a text message
    // POST /api/v1/customer/chat/messages  { body: "..." }
    // Admin also supplies: { body: "...", customer_id: 42 }
    $routes->post('messages', 'ChatController::sendMessage');
 
    // Upload a file/image attachment (multipart)
    // POST /api/v1/customer/chat/attachments  (field: "file")
    $routes->post('attachments', 'ChatController::uploadAttachment');
 
    // Unread message count — for home screen badge
    // GET /api/v1/customer/chat/unread-count
    $routes->get('unread-count', 'ChatController::unreadCount');
});

$routes->group('api/v1/customer', [
    'namespace' => 'App\Modules\Customers\Controllers',
    'filter'    => 'auth',
], static function (RouteCollection $routes) {

    $routes->get('account/profile', 'CustomerAccountController::profile');

    $routes->post('account/update-profile', 'CustomerAccountController::updateProfile');

    $routes->post('account/change-password', 'CustomerAccountController::changePassword');
});
