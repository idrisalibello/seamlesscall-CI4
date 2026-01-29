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
        $case = $this->verificationCaseModel
            ->select('
                verification_cases.*,
                users.name,
                users.email,
                users.phone,
                users.kyc_status,
                users.provider_status,
                users.is_provider
            ')
            ->join('users', 'users.id = verification_cases.user_id') // Join on user_id
            ->find($id);

        if (!$case) {
            return $this->response->setStatusCode(404)->setJSON(['message' => 'Verification case not found.']);
        }

        return $this->response->setJSON($case);
    }

    /**
     * Approves a verification case.
     *
     * @param int $id The ID of the verification case.
     */
    public function approve($id)
    {
        $case = $this->verificationCaseModel->find($id);

        if (!$case) {
            return $this->response->setStatusCode(404)->setJSON(['message' => 'Verification case not found.']);
        }

        $adminUserId = $this->getAdminUserId();
        $now = Time::now()->toDateTimeString();

        // Update verification_cases
        $this->verificationCaseModel->update($id, [
            'status'     => 'verified',
            'decided_by' => $adminUserId,
            'decided_at' => $now,
            'updated_at' => $now,
        ]);

        // Update users.kyc_status
        $this->userModel->update($case['user_id'], [
            'kyc_status' => 'Verified'
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
        $case = $this->verificationCaseModel->find($id);

        if (!$case) {
            return $this->response->setStatusCode(404)->setJSON(['message' => 'Verification case not found.']);
        }

        $reason = $this->request->getVar('reason');
        if (empty($reason)) {
            return $this->response->setStatusCode(400)->setJSON(['message' => 'Rejection reason is required.']);
        }

        $adminUserId = $this->getAdminUserId();
        $now = Time::now()->toDateTimeString();

        // Update verification_cases
        $this->verificationCaseModel->update($id, [
            'status'          => 'rejected',
            'decision_reason' => $reason,
            'decided_by'      => $adminUserId,
            'decided_at'      => $now,
            'updated_at'      => $now,
        ]);

        // Update users.kyc_status
        $this->userModel->update($case['user_id'], [
            'kyc_status' => 'Rejected'
        ]);

        return $this->response->setJSON(['message' => 'Verification case rejected successfully.']);
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
