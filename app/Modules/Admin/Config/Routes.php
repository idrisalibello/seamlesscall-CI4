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

    // Provider Performance, Ratings, and Disputes
    $routes->get('providers/performance', 'ProviderPerformanceController::getOverallProviderPerformance');
    $routes->get('providers/(:num)/performance', 'ProviderPerformanceController::getProviderPerformance/$1');
    $routes->get('providers/(:num)/ratings', 'ProviderPerformanceController::getProviderRatings/$1');
    $routes->get('providers/(:num)/disputes', 'ProviderPerformanceController::getProviderDisputes/$1');

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

    //$routes->get('providers/(:num)/ratings', 'ProviderPerformanceController::ratings/$1');
    $routes->get('providers/performance', 'ProviderPerformanceController::getOverallProviderPerformance');
    $routes->get('providers/(:num)/performance', 'ProviderPerformanceController::getProviderPerformance/$1');

    $routes->get('providers/(:num)/ratings', 'ProviderPerformanceController::getProviderRatings/$1');
    $routes->get('providers/(:num)/disputes', 'ProviderPerformanceController::getProviderDisputes/$1');

    $routes->get('finance/earnings', 'AdminController::getEarningsOverview');

    // Finance -> Provider Payouts (global, filter-driven)
    $routes->get('finance/payouts', 'FinancePayoutsController::index');
    $routes->get('finance/payouts/summary', 'FinancePayoutsController::summary');
    $routes->patch('finance/payouts/(:num)/mark-paid', 'FinancePayoutsController::markPaid/$1');
    $routes->patch('finance/payouts/(:num)/mark-failed', 'FinancePayoutsController::markFailed/$1');


    // Finance -> Platform Commissions (derived) + Config
    $routes->get('finance/commission-config', 'FinanceCommissionConfigController::show');
    $routes->patch('finance/commission-config', 'FinanceCommissionConfigController::update');
    $routes->get('finance/commissions', 'FinanceCommissionsController::index');
    $routes->get('finance/commissions/summary', 'FinanceCommissionsController::summary');

    $routes->match(['patch', 'post'], 'finance/commissions/(:num)/confirm', 'FinanceCommissionsController::confirm/$1');

    // Refunds (global, finance)
    $routes->get('finance/refunds', 'FinanceRefundsController::index');
    $routes->get('finance/refunds/summary', 'FinanceRefundsController::summary');
    $routes->patch('finance/refunds/(:num)/status', 'FinanceRefundsController::updateStatus/$1');

    // Disputes (global, finance)
    $routes->get('finance/disputes', 'FinanceDisputesController::index');
    $routes->get('finance/disputes/summary', 'FinanceDisputesController::summary');
    $routes->patch('finance/disputes/(:num)/status', 'FinanceDisputesController::updateStatus/$1');

    // Backward-compat: PeopleRepository expects this
    $routes->post('refunds/(:num)/status', 'FinanceRefundsController::updateStatus/$1');

    // ✅ Finance -> Ledger (global, filter-driven)
    $routes->get('finance/ledger', 'FinanceLedgerController::index');
    $routes->get('finance/ledger/summary', 'FinanceLedgerController::summary');

    // Pricing Rules (Configurations -> Pricing)
    $routes->get('pricing-rules', 'PricingRulesController::index');
    $routes->get('pricing-rules/summary', 'PricingRulesController::summary');
    $routes->get('pricing-rules/(:num)', 'PricingRulesController::show/$1');
    $routes->post('pricing-rules', 'PricingRulesController::create');
    $routes->put('pricing-rules/(:num)', 'PricingRulesController::update/$1');
    $routes->patch('pricing-rules/(:num)/status', 'PricingRulesController::updateStatus/$1');
    $routes->delete('pricing-rules/(:num)', 'PricingRulesController::delete/$1');

    // Configurations -> Pricing (Controlled Flex Pricing)
    $routes->get('pricing/profiles', 'PricingController::profiles');
    $routes->get('pricing/summary', 'PricingController::summary');
    $routes->get('pricing/profiles/(:num)', 'PricingController::profile/$1');
    $routes->post('pricing/profiles', 'PricingController::createProfile');
    $routes->put('pricing/profiles/(:num)', 'PricingController::updateProfile/$1');
    $routes->patch('pricing/profiles/(:num)/status', 'PricingController::updateProfileStatus/$1');
    $routes->delete('pricing/profiles/(:num)', 'PricingController::deleteProfile/$1');

    $routes->get('pricing/profiles/(:num)/adjustments', 'PricingController::listAdjustments/$1');
    $routes->post('pricing/profiles/(:num)/adjustments', 'PricingController::createAdjustment/$1');
    $routes->put('pricing/adjustments/(:num)', 'PricingController::updateAdjustment/$1');
    $routes->patch('pricing/adjustments/(:num)/status', 'PricingController::updateAdjustmentStatus/$1');
    $routes->delete('pricing/adjustments/(:num)', 'PricingController::deleteAdjustment/$1');

    $routes->group('api/v1/admin', ['namespace' => 'App\Modules\Admin\Controllers'], function ($routes) {

        $routes->get('coverage', 'CoverageController::index');
        $routes->get('coverage/(:num)', 'CoverageController::show/$1');
        $routes->post('coverage', 'CoverageController::create');
        $routes->put('coverage/(:num)', 'CoverageController::update/$1');
        $routes->patch('coverage/(:num)/status', 'CoverageController::changeStatus/$1');
        $routes->delete('coverage/(:num)', 'CoverageController::delete/$1');
    });

    $routes->get('coverages', 'CoverageController::index');
    $routes->post('coverages', 'CoverageController::create');
    $routes->put('coverages/(:num)', 'CoverageController::update/$1');
    $routes->delete('coverages/(:num)', 'CoverageController::delete/$1');
});
