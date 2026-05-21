<?php

namespace App\Modules\Customers\Controllers;

use App\Controllers\BaseController;
use App\Modules\Admin\Models\PromotionModel;
use CodeIgniter\API\ResponseTrait;
use Exception;

/**
 * Handles promotion validation and usage recording for customers.
 *
 * Flow:
 *   1. Customer sees a promo card → taps "Use" or enters a coupon code
 *   2. POST /api/v1/customer/promotions/validate  →  check eligibility + return discount
 *   3. Customer confirms booking on BookingSummaryScreen
 *   4. POST /api/v1/customer/payments/initialize  →  applies discount, records usage
 *      (usage is recorded in CustomerController::initializeInspectionPayment)
 */
class PromotionValidationController extends BaseController
{
    use ResponseTrait;

    private PromotionModel $promoModel;

    public function __construct()
    {
        $this->promoModel = new PromotionModel();
    }

    private function requireCustomerAccess(): mixed
    {
        $user = service('request')->auth_payload ?? null;
        if (!$user || !isset($user->id)) {
            return $this->failUnauthorized('Authentication required.');
        }
        if (!in_array($user->role ?? '', ['Customer', 'Admin'], true)) {
            return $this->failForbidden('Customers only.');
        }
        return null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/v1/customer/promotions/validate
    //
    // Body (one of):
    //   { "promotion_id": 11, "service_id": 3, "amount": 2500 }
    //   { "code": "SAVE20",   "service_id": 3, "amount": 2500 }
    //
    // Returns:
    //   { valid: true,  discount_type, discount_value, promotion_id,
    //     original_amount, discount_applied, final_amount, message }
    //   { valid: false, message }
    // ─────────────────────────────────────────────────────────────────────────
    public function validatePromotion(): mixed
    {
        if ($resp = $this->requireCustomerAccess()) {
            return $resp;
        }

        $user  = service('request')->auth_payload;
        $input = (array) ($this->request->getJSON(true) ?? []);

        $serviceId = isset($input['service_id']) ? (int) $input['service_id'] : null;
        $amount    = isset($input['amount'])     ? (float) $input['amount']   : 0.0;

        // Resolve the promotion row
        $promo = null;

        if (!empty($input['promotion_id'])) {
            $promo = $this->promoModel->find((int) $input['promotion_id']);
        } elseif (!empty($input['code'])) {
            $code  = strtoupper(trim((string) $input['code']));
            $promo = $this->promoModel
                ->where('code', $code)
                ->where('promotion_type', 'coupon')
                ->first();
        }

        if (!$promo) {
            return $this->respond(['valid' => false, 'message' => 'Promotion not found.']);
        }

        // ── Eligibility checks ────────────────────────────────────────────────

        if ($promo['status'] !== 'active') {
            return $this->respond(['valid' => false, 'message' => 'This promotion is no longer active.']);
        }

        $now = time();

        if (!empty($promo['start_date']) && strtotime($promo['start_date']) > $now) {
            return $this->respond(['valid' => false, 'message' => 'This promotion has not started yet.']);
        }

        if (!empty($promo['end_date']) && strtotime($promo['end_date']) < $now) {
            return $this->respond(['valid' => false, 'message' => 'This promotion has expired.']);
        }

        // ── Service/category scope check ──────────────────────────────────────

        if ($promo['promotion_type'] === 'service' && $serviceId !== null) {
            // If scoped to a specific service
            if (!empty($promo['service_id']) && (int) $promo['service_id'] !== $serviceId) {
                return $this->respond([
                    'valid'   => false,
                    'message' => 'This promotion does not apply to the selected service.',
                ]);
            }

            // If scoped to a category, check the service belongs to it
            if (empty($promo['service_id']) && !empty($promo['category_id'])) {
                $db      = \Config\Database::connect();
                $service = $db->table('services')
                    ->where('id', $serviceId)
                    ->where('category_id', (int) $promo['category_id'])
                    ->get()
                    ->getRowArray();

                if (!$service) {
                    return $this->respond([
                        'valid'   => false,
                        'message' => 'This promotion does not apply to the selected service category.',
                    ]);
                }
            }
        }

        // ── Usage limit check ─────────────────────────────────────────────────

        if (!empty($promo['usage_limit'])) {
            $db        = \Config\Database::connect();
            $usedCount = $db->table('promotion_usages')
                ->where('promotion_id', (int) $promo['id'])
                ->countAllResults();

            if ($usedCount >= (int) $promo['usage_limit']) {
                return $this->respond([
                    'valid'   => false,
                    'message' => 'This promotion has reached its usage limit.',
                ]);
            }
        }

        // ── Already used by this customer? ────────────────────────────────────
        // Coupons: one use per customer. Global/service: allow repeat use.
        if ($promo['promotion_type'] === 'coupon') {
            $db       = \Config\Database::connect();
            $prevUse  = $db->table('promotion_usages')
                ->where('promotion_id', (int) $promo['id'])
                ->where('customer_id', (int) $user->id)
                ->countAllResults();

            if ($prevUse > 0) {
                return $this->respond([
                    'valid'   => false,
                    'message' => 'You have already used this coupon.',
                ]);
            }
        }

        // ── Calculate discount ────────────────────────────────────────────────

        [$discountApplied, $finalAmount] = $this->calculateDiscount(
            $promo['discount_type'],
            (float) $promo['discount_value'],
            $amount
        );

        return $this->respond([
            'valid'            => true,
            'promotion_id'     => (int) $promo['id'],
            'promotion_type'   => $promo['promotion_type'],
            'discount_type'    => $promo['discount_type'],
            'discount_value'   => (float) $promo['discount_value'],
            'original_amount'  => $amount,
            'discount_applied' => $discountApplied,
            'final_amount'     => $finalAmount,
            'message'          => 'Promotion applied successfully.',
        ]);
    }

    /**
     * Calculate discount amount and final amount.
     * Ensures final amount is never negative.
     *
     * @return array{float, float}  [discount_applied, final_amount]
     */
    public static function calculateDiscount(
        string $discountType,
        float  $discountValue,
        float  $originalAmount
    ): array {
        if ($discountType === 'percent') {
            $discount = round($originalAmount * ($discountValue / 100), 2);
        } else {
            $discount = min($discountValue, $originalAmount);
        }

        $discount = max(0.0, $discount);
        $final    = max(0.0, round($originalAmount - $discount, 2));

        return [$discount, $final];
    }
}