<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterServicePricingProfilesAddVarianceFields extends Migration
{
    private function hasTable(string $table): bool
    {
        $q = $this->db->query("SHOW TABLES LIKE ?", [$table]);
        return $q->getNumRows() > 0;
    }

    private function hasColumn(string $table, string $column): bool
    {
        $q = $this->db->query("SHOW COLUMNS FROM `{$table}` LIKE ?", [$column]);
        return $q->getNumRows() > 0;
    }

    public function up()
    {
        if (!$this->hasTable('service_pricing_profiles')) {
            return;
        }

        $fieldsToAdd = [];

        if (!$this->hasColumn('service_pricing_profiles', 'band_is_per_unit')) {
            $fieldsToAdd['band_is_per_unit'] = [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ];
        }

        if (!$this->hasColumn('service_pricing_profiles', 'unit_label')) {
            $fieldsToAdd['unit_label'] = [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ];
        }

        if (!$this->hasColumn('service_pricing_profiles', 'warn_variance_percent')) {
            $fieldsToAdd['warn_variance_percent'] = [
                'type'       => 'INT',
                'constraint' => 3,
                'default'    => 25,
            ];
        }

        if (!$this->hasColumn('service_pricing_profiles', 'critical_variance_percent')) {
            $fieldsToAdd['critical_variance_percent'] = [
                'type'       => 'INT',
                'constraint' => 3,
                'default'    => 60,
            ];
        }

        if (!$this->hasColumn('service_pricing_profiles', 'require_reason_over_critical')) {
            $fieldsToAdd['require_reason_over_critical'] = [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ];
        }

        if (!empty($fieldsToAdd)) {
            $this->forge->addColumn('service_pricing_profiles', $fieldsToAdd);
        }
    }

    public function down()
    {
        if (!$this->hasTable('service_pricing_profiles')) {
            return;
        }

        foreach (
            [
                'band_is_per_unit',
                'unit_label',
                'warn_variance_percent',
                'critical_variance_percent',
                'require_reason_over_critical',
            ] as $col
        ) {
            if ($this->hasColumn('service_pricing_profiles', $col)) {
                $this->forge->dropColumn('service_pricing_profiles', $col);
            }
        }
    }
}
