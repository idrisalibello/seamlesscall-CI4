<?php

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;
use App\Models\ActivityLogModel;
use App\Models\FinanceSettingsModel;
use CodeIgniter\API\ResponseTrait;

class FinanceCommissionConfigController extends BaseController
{
    use ResponseTrait;

    private const KEY_RATE = 'platform_commission_rate';
    private const DEFAULT_RATE = '0.15';

    /**
     * GET /api/v1/admin/finance/commission-config
     */
    public function show()
    {
        $user = service('request')->auth_payload;
        log_message('debug', 'FinanceCommissionConfigController: payload for show: ' . json_encode($user));
        if (!$user || !isset($user->role) || $user->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }

        $settings = new FinanceSettingsModel();
        $rateStr = $settings->getValue(self::KEY_RATE, self::DEFAULT_RATE);

        // fetch metadata if row exists
        $row = $settings->where('key', self::KEY_RATE)->first();
        if (is_object($row)) {
            $row = (array) $row;
        }

        return $this->respond([
            'data' => [
                'rate'       => (float) $rateStr,
                'updated_at' => $row['updated_at'] ?? null,
                'updated_by' => isset($row['updated_by']) ? (int) $row['updated_by'] : null,
            ],
        ]);
    }

    /**
     * PATCH /api/v1/admin/finance/commission-config
     * Body: { "rate": 0.18 }
     */
    public function update()
    {
        $user = service('request')->auth_payload;
        log_message('debug', 'FinanceCommissionConfigController: payload for update: ' . json_encode($user));
        if (!$user || !isset($user->role) || $user->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }

        $payload = $this->request->getJSON(true);
        $rate = $payload['rate'] ?? null;

        if ($rate === null || $rate === '') {
            return $this->failValidationErrors('rate is required.');
        }

        if (!is_numeric($rate)) {
            return $this->failValidationErrors('rate must be numeric.');
        }

        $rateF = (float) $rate;
        if ($rateF < 0 || $rateF > 1) {
            return $this->failValidationErrors('rate must be between 0 and 1 (e.g., 0.15).');
        }

        // store normalized string with 4dp to reduce float noise
        $rateStr = number_format($rateF, 4, '.', '');

        $settings = new FinanceSettingsModel();
        $ok = $settings->setValue(self::KEY_RATE, $rateStr, isset($user->id) ? (int) $user->id : null);
        if (!$ok) {
            return $this->failServerError('Failed to update commission rate.');
        }

        // log
        $log = new ActivityLogModel();
        $log->insert([
            'user_id'     => isset($user->id) ? (int) $user->id : null,
            'action'      => 'finance.commission_config.update',
            'description' => 'Updated platform commission rate to ' . $rateStr,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        return $this->respond([
            'data' => [
                'rate'       => (float) $rateStr,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => isset($user->id) ? (int) $user->id : null,
            ],
        ]);
    }
}
