<?php

namespace App\Modules\Chat\Models;

use CodeIgniter\Model;

class ChatMessageModel extends Model
{
    protected $table            = 'chat_messages';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'customer_id',
        'sender_role',
        'sender_id',
        'body',
        'message_type',
        'attachment_url',
        'attachment_name',
        'is_read',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Skip model-level validation — the controller validates input before insert.
    // Keeping these rules active caused silent insert failures in CI4 4.x when PHP
    // integer values were passed and the internal cast produced unexpected results.
    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = true;

    /**
     * Return paginated messages for a customer in chronological order.
     *
     * The JOIN uses sender_id only for 'agent' rows. For 'customer' rows
     * sender_id is NULL, so the LEFT JOIN returns NULL for sender_name — correct.
     * We add the customer's own name as a fallback using a CASE expression so
     * Flutter always has a displayable name without extra round-trips.
     */
    public function getForCustomer(int $customerId, int $limit = 50, int $offset = 0): array
    {
        return $this->db->table('chat_messages')
            ->select('
                chat_messages.*,
                CASE
                    WHEN chat_messages.sender_role = \'agent\' THEN agents.name
                    ELSE customers.name
                END AS sender_name
            ')
            ->join('users AS agents',   'agents.id   = chat_messages.sender_id',   'left')
            ->join('users AS customers', 'customers.id = chat_messages.customer_id', 'left')
            ->where('chat_messages.customer_id', $customerId)
            ->orderBy('chat_messages.created_at', 'ASC')
            ->orderBy('chat_messages.id', 'ASC')     // tie-break for same-second messages
            ->limit($limit, $offset)
            ->get()
            ->getResultArray();
    }

    /**
     * Mark all unread agent messages for a customer as read.
     */
    public function markReadForCustomer(int $customerId): void
    {
        $this->db->table('chat_messages')
            ->where('customer_id', $customerId)
            ->where('sender_role', 'agent')
            ->where('is_read', 0)
            ->update(['is_read' => 1]);
    }

    /**
     * Count unread messages (from agent) for a customer — used for badge counts.
     */
    public function countUnreadForCustomer(int $customerId): int
    {
        return (int) $this->db->table('chat_messages')
            ->where('customer_id', $customerId)
            ->where('sender_role', 'agent')
            ->where('is_read', 0)
            ->countAllResults();
    }
}