<?php

require __DIR__ . '/../app/Config/Boot/development.php'; // or production.php depending on env
require __DIR__ . '/../app/Config/Autoload.php';
require __DIR__ . '/../vendor/autoload.php';

use App\Modules\Auth\Services\AuthService;

$authService = new AuthService();

// Test with phone
$phone = '+2348036967483';
try {
    $sent = $authService->requestLoginOtp($phone);
    echo $sent ? "OTP sent successfully to $phone\n" : "Failed to send OTP\n";
} catch (Exception $e) {
    echo "Error sending OTP: " . $e->getMessage() . "\n";
}

// Test with email
$email = 'idrisalibello@gmail.com';
try {
    $sent = $authService->requestLoginOtp($email);
    echo $sent ? "OTP sent successfully to $email\n" : "Failed to send OTP\n";
} catch (Exception $e) {
    echo "Error sending OTP: " . $e->getMessage() . "\n";
}
