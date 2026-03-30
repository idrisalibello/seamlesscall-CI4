<?php

namespace App\Models;

use CodeIgniter\Model;

class ConversationModel extends Model
{
    protected $table = 'conversations';
    protected $allowedFields = ['type', 'job_id', 'created_at', 'updated_at'];
    protected $useTimestamps = true;
}