<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNamedIndexesToGradebookTables extends Migration
{
    public function up()
    {
        $this->addMissingNamedIndexes('gradebook_entries', [
            'idx_enrollment_period' => ['enrollment_id', 'grading_period_id'],
            'idx_enrollment_status' => ['enrollment_id', 'grade_status'],
        ]);

        $this->addMissingNamedIndexes('grade_history', [
            'idx_entry_id'   => ['gradebook_entry_id'],
            'idx_changed_by' => ['changed_by'],
            'idx_changed_at' => ['changed_at'],
        ]);
    }

    public function down()
    {
        $this->dropNamedIndexesIfPresent('gradebook_entries', [
            'idx_enrollment_period',
            'idx_enrollment_status',
        ]);

        $this->dropNamedIndexesIfPresent('grade_history', [
            'idx_entry_id',
            'idx_changed_by',
            'idx_changed_at',
        ]);
    }

    /**
     * @param array<string, list<string>> $indexes
     */
    private function addMissingNamedIndexes(string $table, array $indexes): void
    {
        $indexData = $this->db->getIndexData($table);
        $hasPending = false;

        foreach ($indexes as $indexName => $columns) {
            $hasNamedIndex      = $this->hasNamedIndex($indexData, $indexName);
            $hasEquivalentIndex = $this->hasEquivalentIndex($indexData, $columns);

            if (! $hasEquivalentIndex || ! $hasNamedIndex) {
                $this->forge->addKey($columns, false, false, $indexName);
                $indexData[$indexName] = (object) [
                    'name'   => $indexName,
                    'type'   => 'INDEX',
                    'fields' => $columns,
                ];
                $hasPending = true;
            }
        }

        if ($hasPending) {
            $this->forge->processIndexes($table);
        }
    }

    /**
     * @param list<string> $indexes
     */
    private function dropNamedIndexesIfPresent(string $table, array $indexes): void
    {
        $indexData = $this->db->getIndexData($table);

        foreach ($indexes as $indexName) {
            if ($this->hasNamedIndex($indexData, $indexName)) {
                $this->forge->dropKey($table, $indexName);
            }
        }
    }

    /**
     * @param array<string, object> $indexData
     */
    private function hasNamedIndex(array $indexData, string $indexName): bool
    {
        $required = strtolower($indexName);

        foreach ($indexData as $name => $index) {
            $candidate = is_string($name) ? $name : ($index->name ?? '');
            if (strtolower($candidate) === $required) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, object> $indexData
     * @param list<string>          $columns
     */
    private function hasEquivalentIndex(array $indexData, array $columns): bool
    {
        $required = array_map('strtolower', $columns);

        foreach ($indexData as $index) {
            $fields = array_map('strtolower', $index->fields ?? []);
            if ($fields === $required) {
                return true;
            }
        }

        return false;
    }
}
