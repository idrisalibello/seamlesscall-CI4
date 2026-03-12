<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
    }

    /**
     * Returns the decoded JWT payload attached by AuthFilter.
     */
    protected function authPayload(): ?object
    {
        return $this->request->auth_payload ?? null;
    }

    /**
     * Returns the current user's role from JWT payload.
     */
    protected function authRole(): ?string
    {
        $payload = $this->authPayload();
        return $payload->role ?? null;
    }

    /**
     * Returns the current user's permissions from JWT payload.
     *
     * @return array<int, string>
     */
    protected function authPermissions(): array
    {
        $payload = $this->authPayload();
        $permissions = $payload->permissions ?? [];

        if (is_array($permissions)) {
            return array_values(array_unique(array_map('strval', $permissions)));
        }

        return [];
    }

    /**
     * Transitional bypass:
     * If a user is Admin but has no granular permissions embedded yet,
     * allow access to avoid locking out legacy admin accounts.
     */
    protected function hasPermission(string $permission): bool
    {
        $role = $this->authRole();
        $permissions = $this->authPermissions();

        if ($role === 'Admin' && empty($permissions)) {
            return true;
        }

        return in_array($permission, $permissions, true);
    }

    /**
     * Returns a 403 JSON response if permission is missing, otherwise null.
     */
    protected function requirePermission(string $permission): ?ResponseInterface
    {
        if ($this->hasPermission($permission)) {
            return null;
        }

        return service('response')
            ->setStatusCode(403)
            ->setJSON([
                'status'   => 403,
                'error'    => 'Forbidden',
                'messages' => [
                    'error' => 'Missing required permission: ' . $permission,
                ],
            ]);
    }
}