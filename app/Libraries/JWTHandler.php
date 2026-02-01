<?php

namespace App\Libraries;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use RuntimeException;

class JWTHandler
{
    private string $key;
    private string $algo = 'HS256';

    public function __construct()
    {
        // Prefer CI4 env() which reads from .env/$_ENV/$_SERVER reliably
        $secret = env('JWT_SECRET');

        // Fallbacks (defensive) in case env() is unavailable in some context
        if (!$secret) {
            $secret = $_ENV['JWT_SECRET'] ?? null;
        }
        if (!$secret) {
            $secret = $_SERVER['JWT_SECRET'] ?? null;
        }
        if (!$secret) {
            $secret = getenv('JWT_SECRET') ?: null;
        }

        if (!$secret || strlen($secret) < 64) {
            throw new RuntimeException(
                'JWT_SECRET is missing or too short. Minimum 64 characters required.'
            );
        }

        $this->key = $secret;
    }

    public function generateToken(array $payload, int $expiry = 3600): string
    {
        $payload['iat'] = time();
        $payload['exp'] = time() + $expiry;

        return JWT::encode($payload, $this->key, $this->algo);
    }

    public function validateToken(string $token): object|false
    {
        try {
            return JWT::decode($token, new Key($this->key, $this->algo));
        } catch (\Throwable $e) {
            log_message('warning', 'JWT validation failed: ' . $e->getMessage());
            return false;
        }
    }
}


// namespace App\Libraries;

// use Firebase\JWT\JWT;
// use Firebase\JWT\Key;
// use RuntimeException;

// class JWTHandler
// {
//     private string $key;
//     private string $algo = 'HS256';

//     public function __construct()
//     {
//         $secret = getenv('JWT_SECRET');

//         if (!$secret || strlen($secret) < 64) {
//             throw new RuntimeException(
//                 'JWT_SECRET is missing or too short. Minimum 64 characters required.'
//             );
//         }

//         $this->key = $secret;
//     }

//     public function generateToken(array $payload, int $expiry = 3600): string
//     {
//         $payload['iat'] = time();
//         $payload['exp'] = time() + $expiry;

//         return JWT::encode($payload, $this->key, $this->algo);
//     }

//     public function validateToken(string $token): object|false
//     {
//         try {
//             return JWT::decode($token, new Key($this->key, $this->algo));
//         } catch (\Throwable $e) {
//             log_message('warning', 'JWT validation failed: ' . $e->getMessage());
//             return false;
//         }
//     }
// }
