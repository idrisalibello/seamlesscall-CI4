<?php

namespace App\Modules\Admin\Models;

use CodeIgniter\Model;
use CodeIgniter\I18n\Time;

class VerificationCaseModel extends Model
{
    protected $table            = 'verification_cases';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id', 'status', 'decision_reason', 'escalation_reason',
        'decided_by', 'decided_at', 'created_at', 'updated_at'
    ];

    // Dates
    protected $useTimestamps = false; // Timestamps are handled manually
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Creates a pending verification case if one does not already exist
     * for the given user_id with 'pending' or 'escalated' status.
     *
     * @param int $userId The ID of the user.
     * @return bool True if a new row was inserted, false otherwise.
     */
    public function createPendingIfNotExists(int $userId): bool
    {
        // 1) Query verification_cases for this user_id where status IN ('pending','escalated').
        $existingCase = $this->where('user_id', $userId)
                             ->whereIn('status', ['pending', 'escalated'])
                             ->first();

        // 2) If a record exists → return false (do nothing).
        if ($existingCase) {
            return false;
        }

        // 3) If none exists → insert a new row:
        //    - user_id = $userId
        //    - status = 'pending'
        //    - created_at = now()
        $now = Time::now()->toDateTimeString();
        $data = [
            'user_id'    => $userId,
            'status'     => 'pending',
            'created_at' => $now,
            'updated_at' => $now, // Manually set updated_at as well
        ];

        $this->insert($data);

        // 4) Return true if a row was inserted.
        return $this->db->affectedRows() > 0;
    }
}