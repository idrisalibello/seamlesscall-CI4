<?php

namespace App\Modules\Dashboard\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;

class DashboardController extends BaseController
{
    use ResponseTrait;

    public function stats()
    {
        $userModel = new UserModel();
        $customerCount = $userModel->countCustomers();
        $providerCount = $userModel->countProviders();

        return $this->respond([
            'data' => [
                'total_customers' => $customerCount,
                'total_providers' => $providerCount,
            ],
        ]);
    }
}
