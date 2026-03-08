<?php

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;
use App\Modules\Admin\Models\PromotionModel;
use CodeIgniter\API\ResponseTrait;

class PromotionFeedController extends BaseController
{
    use ResponseTrait;

    private PromotionModel $model;

    public function __construct()
    {
        $this->model = new PromotionModel();
    }

    private function decodeRow(array $row): array
    {
        foreach (['id', 'category_id', 'service_id', 'provider_id', 'usage_limit'] as $key) {
            $row[$key] = isset($row[$key]) && $row[$key] !== null ? (int) $row[$key] : null;
        }

        $row['discount_value'] = isset($row['discount_value']) ? (float) $row['discount_value'] : 0.0;

        $targetLabel = 'All services';

        if (($row['promotion_type'] ?? '') === 'service') {
            if (!empty($row['service_name'])) {
                $targetLabel = $row['service_name'];
            } elseif (!empty($row['category_name'])) {
                $targetLabel = $row['category_name'] . ' (All services)';
            } else {
                $targetLabel = 'Service';
            }
        } elseif (($row['promotion_type'] ?? '') === 'provider') {
            $targetLabel = !empty($row['provider_name']) ? $row['provider_name'] : 'Provider';
        } elseif (($row['promotion_type'] ?? '') === 'coupon') {
            $targetLabel = !empty($row['code']) ? $row['code'] : 'Coupon';
        }

        $row['target_label'] = $targetLabel;

        return $row;
    }

    public function index()
    {
        $now = date('Y-m-d H:i:s');

        $rows = $this->model
            ->select(
                'promotions.*,
                 categories.name as category_name,
                 services.name as service_name,
                 users.name as provider_name'
            )
            ->join('categories', 'categories.id = promotions.category_id', 'left')
            ->join('services', 'services.id = promotions.service_id', 'left')
            ->join('users', 'users.id = promotions.provider_id', 'left')
            ->where('promotions.status', 'active')
            ->groupStart()
                ->where('promotions.start_date IS NULL', null, false)
                ->orWhere('promotions.start_date <=', $now)
            ->groupEnd()
            ->groupStart()
                ->where('promotions.end_date IS NULL', null, false)
                ->orWhere('promotions.end_date >=', $now)
            ->groupEnd()
            ->orderBy('promotions.created_at', 'DESC')
            ->findAll(12);

        $data = array_map(fn(array $row) => $this->decodeRow($row), $rows);

        return $this->respond([
            'data' => $data,
        ]);
    }
}