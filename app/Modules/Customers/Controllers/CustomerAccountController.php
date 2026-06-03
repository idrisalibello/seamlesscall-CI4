<?php

namespace App\Modules\Customers\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;

class CustomerAccountController extends BaseController
{
    use ResponseTrait;

    protected UserModel $users;

    public function __construct()
    {
        $this->users = new UserModel();
    }

    private function authUser(): ?object
    {
        $payload = $this->authPayload();

        if (!$payload || empty($payload->id)) {
            return null;
        }

        return $this->users->asObject()->find((int) $payload->id);
    }

    private function requestValue(string $key): ?string
    {
        $json = $this->request->getJSON(true);
        $value = $this->request->getPost($key);

        if ($value === null && is_array($json) && array_key_exists($key, $json)) {
            $value = $json[$key];
        }

        if ($value === null) {
            $value = $this->request->getVar($key);
        }

        return $value === null ? null : (string) $value;
    }

    private function userPayload(object $user): array
    {
        $payload = $this->authPayload();

        return [
            'id'          => (int) $user->id,
            'name'        => (string) ($user->name ?? ''),
            'email'       => (string) ($user->email ?? ''),
            'phone'       => (string) ($user->phone ?? ''),
            'role'        => $user->role ?? null,
            'status'      => $user->status ?? null,
            'permissions' => is_array($payload->permissions ?? null)
                ? array_values($payload->permissions)
                : [],
        ];
    }

    public function profile()
    {
        $user = $this->authUser();

        if (!$user) {
            return $this->failUnauthorized('Unauthorized');
        }

        return $this->respond([
            'status' => 'success',
            'data'   => $this->userPayload($user),
        ]);
    }

    public function updateProfile()
    {
        $user = $this->authUser();

        if (!$user) {
            return $this->failUnauthorized('Unauthorized');
        }

        $name = trim((string) $this->requestValue('name'));

        $rules = [
            'name' => 'required|min_length[2]|max_length[120]',
        ];

        if (!$this->validateData(['name' => $name], $rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $this->users->update($user->id, [
            'name' => $name,
        ]);

        $updated = $this->users->asObject()->find($user->id);

        return $this->respond([
            'status'  => 'success',
            'message' => 'Profile updated successfully',
            'data'    => $this->userPayload($updated),
        ]);
    }

    public function changePassword()
    {
        $user = $this->authUser();

        if (!$user) {
            return $this->failUnauthorized('Unauthorized');
        }

        $data = [
            'current_password' => (string) $this->requestValue('current_password'),
            'new_password'     => (string) $this->requestValue('new_password'),
            'confirm_password' => (string) $this->requestValue('confirm_password'),
        ];

        $rules = [
            'current_password' => 'required',
            'new_password'     => 'required|min_length[8]',
            'confirm_password' => 'required|matches[new_password]',
        ];

        if (!$this->validateData($data, $rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        if (!password_verify($data['current_password'], (string) $user->password)) {
            return $this->fail('Current password is incorrect', 400);
        }

        $this->users->update($user->id, [
            'password' => password_hash($data['new_password'], PASSWORD_DEFAULT),
        ]);

        return $this->respond([
            'status'  => 'success',
            'message' => 'Password changed successfully',
        ]);
    }
}
