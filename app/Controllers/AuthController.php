<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Libraries\JWTHandler;
use Google_Client;
use Google_Service_Oauth2;
use CodeIgniter\API\ResponseTrait;

class AuthController extends BaseController
{
    use ResponseTrait;

    public function oauth()
    {
        log_message('info', 'OAuth endpoint hit.');

        $provider = $this->request->getJsonVar('provider');
        $token = $this->request->getJsonVar('token');

        log_message('info', 'Provider: ' . $provider);

        if ($provider !== 'google') {
            log_message('warning', 'OAuth attempt with unsupported provider: ' . $provider);
            return $this->failValidationError('Only Google login is supported.');
        }

        if (empty($token)) {
            log_message('warning', 'OAuth attempt with empty token.');
            return $this->failValidationError('Token is required.');
        }

        $clientId = getenv('GOOGLE_CLIENT_ID');
        if (empty($clientId)) {
            log_message('error', 'GOOGLE_CLIENT_ID is not configured.');
            return $this->failServerError('Google Client ID is not configured.');
        }

        try {
            log_message('info', 'Verifying Google ID token.');
            $client = new Google_Client(['client_id' => $clientId]);
            $payload = $client->verifyIdToken($token);

            if (!$payload) {
                log_message('error', 'Invalid Google token received.');
                return $this->failUnauthorized('Invalid Google token.');
            }

            log_message('info', 'Google token verified. Payload: ' . json_encode($payload));

            $userModel = new UserModel();

            log_message('info', 'Searching for user by provider_id: ' . $payload['sub']);
            $user = $userModel->findUserByProviderId($payload['sub']);

            if (!$user) {
                log_message('info', 'User not found by provider_id. Searching by email: ' . $payload['email']);
                $user = $userModel->findUserByEmail($payload['email']);
            }

            if (!$user) {
                log_message('info', 'User not found. Creating a new user.');
                $newUserData = [
                    'name' => $payload['name'],
                    'email' => $payload['email'],
                    'provider' => 'google',
                    'provider_id' => $payload['sub'],
                    'role' => 'Customer',
                ];
                $userId = $userModel->createUser($newUserData);
                $user = $userModel->find($userId);
                log_message('info', 'New user created with ID: ' . $userId);
            } else {
                log_message('info', 'User found with ID: ' . $user['id']);
                if (empty($user['provider']) || empty($user['provider_id'])) {
                    log_message('info', 'Updating user with provider info.');
                    $userModel->update($user['id'], [
                        'provider' => 'google',
                        'provider_id' => $payload['sub'],
                    ]);
                }
            }
            
            log_message('info', 'Generating JWT for user ID: ' . $user['id']);
            $jwtHandler = new JWTHandler();
            $jwtPayload = [
                'id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role'],
            ];
            $jwt = $jwtHandler->generateToken($jwtPayload);
            log_message('info', 'JWT generated successfully.');

            return $this->respond([
                'status' => 'success',
                'data' => [
                    'token' => $jwt,
                    'user' => $user,
                ]
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'OAuth error: ' . $e->getMessage() . '\n' . $e->getTraceAsString());
            return $this->failServerError('An unexpected error occurred.');
        }
    }
}
