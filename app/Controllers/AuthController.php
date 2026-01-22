<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Libraries\JWTHandler;
use Google_Client;
use Google_Service_Oauth2;
use CodeIgniter\API\ResponseTrait;

// Import the models needed for permissions
use App\Modules\System\Models\UserRoleModel;

class AuthController extends BaseController
{
    use ResponseTrait;

    /**
     * Get all unique permissions for a given user.
     *
     * @param int $userId
     * @return array
     */
    private function _getUserPermissions(int $userId): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('user_roles');
        $builder->select('p.permission_name')
                ->join('role_permissions as rp', 'rp.role_id = user_roles.role_id')
                ->join('permissions as p', 'p.id = rp.permission_id')
                ->where('user_roles.user_id', $userId);
        
        $query = $builder->get();
        $result = $query->getResultArray();
        
        // Return a flat, unique array of permission names
        return array_unique(array_column($result, 'permission_name'));
    }

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
            
            // Assuming UserModel methods return arrays
            $user = $userModel->asArray()->where('google_id', $payload['sub'])->first();

            if (!$user) {
                log_message('info', 'User not found by google_id. Searching by email: ' . $payload['email']);
                $user = $userModel->asArray()->where('email', $payload['email'])->first();
            }

            if (!$user) {
                log_message('info', 'User not found. Creating a new user.');
                $newUserData = [
                    'name' => $payload['name'],
                    'email' => $payload['email'],
                    'phone' => '', // Phone is required in the DB, but not provided by Google
                    'password' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT), // Create a random, unusable password
                    'google_id' => $payload['sub'],
                    'role' => 'Customer',
                ];
                $userId = $userModel->insert($newUserData);
                $user = $userModel->asArray()->find($userId);
                log_message('info', 'New user created with ID: ' . $userId);
            } else {
                log_message('info', 'User found with ID: ' . $user['id']);
                if (empty($user['google_id'])) {
                    log_message('info', 'Updating user with google_id.');
                    $userModel->update($user['id'], [
                        'google_id' => $payload['sub'],
                    ]);
                }
            }
            
            log_message('info', 'Generating JWT for user ID: ' . $user['id']);
            
            // Get user's granular permissions
            $permissions = $this->_getUserPermissions((int)$user['id']);
            log_message('info', 'Permissions for user ' . $user['id'] . ': ' . json_encode($permissions));

            $jwtHandler = new JWTHandler();
            $jwtPayload = [
                'id' => (int)$user['id'],
                'email' => $user['email'],
                'role' => $user['role'],
                'permissions' => $permissions, // Add permissions to the JWT payload
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