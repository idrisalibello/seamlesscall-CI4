<?php

namespace App\Modules\Customers\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use Exception;

/**
 * GET /api/v1/customer/services/popular
 *
 * Returns up to 6 active services ranked by a weighted popularity score:
 *
 *   score = (completed_bookings × 3) + (avg_rating × 10) + (view_count × 0.5)
 *
 * Weights rationale:
 *   - Completed bookings are the strongest trust signal (a customer paid AND the job finished)
 *   - Average rating amplifies quality — multiplied by 10 so a 5-star service with few
 *     bookings can still surface above an average unrated one
 *   - View count is a lighter signal (interest, not commitment) so its weight is halved
 *
 * Falls back gracefully: if a service has no bookings/ratings/views it still appears,
 * just sorted lower. New services are never completely excluded.
 */
class PopularServicesController extends BaseController
{
    use ResponseTrait;

    private function requireAccess(): mixed
    {
        $user = service('request')->auth_payload ?? null;
        if (!$user || !isset($user->id)) {
            return $this->failUnauthorized('Authentication required.');
        }
        return null;
    }

    public function index(): mixed
    {
        if ($resp = $this->requireAccess()) {
            return $resp;
        }

        $limit = max(1, min(12, (int) ($this->request->getGet('limit') ?? 6)));

        try {
            $db = \Config\Database::connect();

            $rows = $db->query("
                SELECT
                    s.id,
                    s.category_id,
                    s.name,
                    s.description,
                    s.status,
                    s.view_count,
                    c.name                                      AS category_name,

                    -- Booking metrics: only count completed jobs
                    COUNT(DISTINCT CASE WHEN j.status = 'completed' THEN j.id END)
                        AS booking_count,

                    -- Rating: average across all jobs for this service
                    ROUND(
                        COALESCE(AVG(pr.rating), 0),
                        1
                    )                                           AS avg_rating,

                    COUNT(DISTINCT pr.id)                       AS rating_count,

                    -- Popularity score (see class docblock for weights)
                    (
                        COUNT(DISTINCT CASE WHEN j.status = 'completed' THEN j.id END) * 3
                        + COALESCE(AVG(pr.rating), 0) * 10
                        + COALESCE(s.view_count, 0) * 0.5
                    )                                           AS popularity_score

                FROM services s
                INNER JOIN categories c ON c.id = s.category_id
                LEFT JOIN jobs j         ON j.service_id = s.id
                LEFT JOIN provider_ratings pr ON pr.job_id = j.id

                WHERE s.status  = 'active'
                  AND c.status  = 'active'

                GROUP BY
                    s.id, s.category_id, s.name, s.description,
                    s.status, s.view_count, c.name

                ORDER BY popularity_score DESC, s.id ASC

                LIMIT ?
            ", [$limit])->getResultArray();

            $data = array_map(function (array $row): array {
                return [
                    'id'             => (int)   $row['id'],
                    'category_id'    => (int)   $row['category_id'],
                    'category_name'  => (string) $row['category_name'],
                    'name'           => (string) $row['name'],
                    'description'    => $row['description'],
                    'status'         => (string) $row['status'],
                    'booking_count'  => (int)   $row['booking_count'],
                    'avg_rating'     => (float) $row['avg_rating'],
                    'rating_count'   => (int)   $row['rating_count'],
                    'view_count'     => (int)   $row['view_count'],
                    'popularity_score' => round((float) $row['popularity_score'], 2),
                ];
            }, $rows);

            return $this->respond(['data' => $data]);

        } catch (Exception $e) {
            log_message('error', '[PopularServicesController::index] ' . $e->getMessage());
            return $this->failServerError('Could not load popular services.');
        }
    }

    /**
     * POST /api/v1/customer/services/{id}/view
     *
     * Increments view_count for a service. Called when a customer opens
     * the service detail screen. Fire-and-forget from the Flutter side.
     */
    public function recordView(int $serviceId): mixed
    {
        if ($resp = $this->requireAccess()) {
            return $resp;
        }

        try {
            $db = \Config\Database::connect();

            // Only increment if the service exists and is active
            $service = $db->table('services')
                ->where('id', $serviceId)
                ->where('status', 'active')
                ->get()
                ->getRowArray();

            if (!$service) {
                return $this->failNotFound('Service not found.');
            }

            $db->table('services')
                ->where('id', $serviceId)
                ->set('view_count', 'view_count + 1', false)
                ->update();

            return $this->respond(['message' => 'View recorded.']);

        } catch (Exception $e) {
            log_message('error', '[PopularServicesController::recordView] ' . $e->getMessage());
            // Non-critical — return 200 so Flutter doesn't need to handle this
            return $this->respond(['message' => 'ok']);
        }
    }
}