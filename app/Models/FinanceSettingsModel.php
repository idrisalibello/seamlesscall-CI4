<?php

namespace App\Models;

use CodeIgniter\Model;

class FinanceSettingsModel extends Model
{
    protected $table      = 'finance_settings';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'key',
        'value',
        'updated_by',
        'updated_at',
        'created_at',
    ];

    public function getValue(string $key, ?string $default = null): ?string
    {
        $row = $this->where('key', $key)->first();
        if (!$row) {
            return $default;
        }
        if (is_object($row)) {
            $row = (array) $row;
        }
        return isset($row['value']) ? (string) $row['value'] : $default;
    }

    /**
     * Upsert a key/value setting.
     */
    public function setValue(string $key, string $value, ?int $updatedBy = null): bool
    {
        $existing = $this->where('key', $key)->first();
        $now = date('Y-m-d H:i:s');

        if ($existing) {
            $id = is_object($existing) ? (int) $existing->id : (int) ($existing['id'] ?? 0);
            if ($id < 1) {
                return false;
            }
            return (bool) $this->update($id, [
                'value'      => $value,
                'updated_by' => $updatedBy,
                'updated_at' => $now,
            ]);
        }

        return (bool) $this->insert([
            'key'        => $key,
            'value'      => $value,
            'updated_by' => $updatedBy,
            'updated_at' => $now,
            'created_at' => $now,
        ]);
    }
}
