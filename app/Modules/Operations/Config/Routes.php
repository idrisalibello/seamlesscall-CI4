 <?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->group('api/v1/operations', [
    'namespace' => 'App\Modules\Operations\Controllers',
    'filter'    => 'auth',
], function ($routes) {
    // Provider specific routes
    $routes->get('provider/jobs/(:num)', 'OperationsController::getProviderJobDetails/$1');
    $routes->put('provider/jobs/(:num)/status', 'OperationsController::updateJobStatus/$1');
    $routes->get('provider/jobs', 'OperationsController::getProviderActiveJobs');

    // Admin specific routes
    $routes->get('admin/jobs', 'OperationsController::getAdminActiveJobs');
    $routes->get('admin/jobs/pending', 'OperationsController::getAdminPendingJobs');
    $routes->get('admin/jobs/(:num)', 'OperationsController::getAdminJobDetails/$1');
    $routes->post('admin/jobs/(:num)/assign', 'OperationsController::assignJob/$1');
    $routes->get('admin/providers/available', 'OperationsController::getAvailableProviders');
});


// use CodeIgniter\Router\RouteCollection;

// /**
//  * @var RouteCollection $routes
//  */

// $routes->group('api/v1/operations', [
//     'namespace' => 'App\Modules\Operations\Controllers',
//     'filter'    => 'auth', // Apply authentication filter
// ], function ($routes) {
//     // Provider specific routes
//     $routes->get('provider/jobs/(:num)', 'OperationsController::getProviderJobDetails/$1');
//     $routes->put('provider/jobs/(:num)/status', 'OperationsController::updateJobStatus/$1');
//     $routes->get('provider/jobs', 'OperationsController::getProviderActiveJobs');



//     // Admin specific routes (for managing all active jobs)

//     $routes->get('admin/jobs/pending', 'OperationsController::getAdminPendingJobs');
//     $routes->get('admin/jobs/(:num)', 'OperationsController::getAdminJobDetails/$1');
//     $routes->post('admin/jobs/(:num)/assign', 'OperationsController::assignJob/$1'); // To assign a provider to a job
//     $routes->get('admin/providers/available', 'OperationsController::getAvailableProviders'); // To get a list of available providers});

// });
// $routes->get('admin/jobs', 'OperationsController::getAdminActiveJobs'); -->