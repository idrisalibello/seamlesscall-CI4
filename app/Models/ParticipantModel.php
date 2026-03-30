<?php

namespace App\Models;

use CodeIgniter\Model;

class ParticipantModel extends Model
{
    protected $table = 'conversation_participants';
    protected $allowedFields = ['conversation_id', 'user_id', 'role'];
}