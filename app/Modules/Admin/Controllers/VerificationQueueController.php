<?php

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;
use App\Modules\Admin\Models\VerificationCaseModel;
use App\Models\UserModel;
use CodeIgniter\I18n\Time;

class VerificationQueueController extends BaseController
{
    protected $verificationCaseModel;
    protected $userModel;

    public function __construct()
    {
        $this->verificationCaseModel = new VerificationCaseModel();
        $this->userModel = new UserModel();
    }

    private function getAdminUserId()
    {
        // This is a placeholder. In a real application, you would get the
        // authenticated admin user's ID from a session, JWT, or similar.
        // Assuming $this->request->user->id holds the current user's ID if authenticated.
        return $this->request->user->id ?? 1; // Default to 1 for testing if not available
    }

    /**
     * Returns a JSON list of verification_cases where status='pending'.
     * Includes case details and relevant user summary information.
     */
    public function index()
    {
        $pendingCases = $this->userModel
            ->select('
            users.id,
            users.id AS provider_id,
            users.name,
            users.email,
            users.phone,
            users.kyc_status,
            users.provider_status,
            users.is_provider,
            users.created_at
        ')
            ->where('users.kyc_status', 'Pending')
            ->where('users.provider_status', 'approved')
            ->findAll();

        return $this->response->setJSON($pendingCases);
    }


    /**
     * Returns JSON detail for a specific verification case + joined user summary.
     *
     * @param int $id The ID of the verification case.
     */
    public function show($id)
    {
        $userId = (int) $id;

        $user = $this->userModel
            ->select('users.id,users.id AS provider_id, users.name, users.email, users.phone, users.kyc_status,users.decision_reason, users.provider_status, users.is_provider')
            ->where('users.id', $userId)
            ->where('users.is_provider', 1)
            ->first();

        if (!$user) {
            return $this->response->setStatusCode(404)->setJSON(['message' => 'Verification case not found.']);
        }

        return $this->response->setJSON($user);
    }


    /**
     * Approves a verification case.
     *
     * @param int $id The ID of the verification case.
     */
    public function approve($id)
    {
        $userId = (int) $id;

        $user = $this->userModel->find($userId);
        if (!$user) {
            return $this->response->setStatusCode(404)->setJSON(['message' => 'Verification case not found.']);
        }
        $payload = $this->request->getJSON(true) ?? [];


        $this->userModel->update($userId, [
            'kyc_status' => 'Verified',
        ]);

        return $this->response->setJSON(['message' => 'Verification case approved successfully.']);
    }


    /**
     * Rejects a verification case.
     *
     * @param int $id The ID of the verification case.
     */
    public function reject($id)
    {
        $userId = (int) $id;

        $user = $this->userModel->find($userId);
        if (!$user) {
            return $this->response->setStatusCode(404)->setJSON(['message' => 'Verification case not found.']);
        }

        $payload = $this->request->getJSON(true) ?? [];

        $reason = $payload['reason']
            ?? $payload['decision_reason']
            ?? $this->request->getVar('reason')
            ?? $this->request->getVar('decision_reason');

        $reason = is_string($reason) ? trim($reason) : '';

        if ($reason === '') {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['message' => 'Rejection reason is required.']);
        }


        $this->userModel->update($userId, [
            'kyc_status' => 'Rejected',
            'decision_reason' => 'Reason is: ' . $reason
        ]);

        return $this->response->setJSON(['message' => 'Verification case rejected.']);
    }

    /**
     * Escalates a verification case.
     *
     * @param int $id The ID of the verification case.
     */
    public function escalate($id)
    {
        $case = $this->verificationCaseModel->find($id);

        if (!$case) {
            return $this->response->setStatusCode(404)->setJSON(['message' => 'Verification case not found.']);
        }

        $reason = $this->request->getVar('reason');
        if (empty($reason)) {
            return $this->response->setStatusCode(400)->setJSON(['message' => 'Escalation reason is required.']);
        }

        $adminUserId = $this->getAdminUserId();
        $now = Time::now()->toDateTimeString();

        // Update verification_cases
        $this->verificationCaseModel->update($id, [
            'status'            => 'escalated',
            'escalation_reason' => $reason,
            'decided_by'        => $adminUserId,
            'decided_at'        => $now,
            'updated_at'        => $now,
        ]);

        // DO NOT update users.kyc_status for escalation

        return $this->response->setJSON(['message' => 'Verification case escalated successfully.']);
    }
}
