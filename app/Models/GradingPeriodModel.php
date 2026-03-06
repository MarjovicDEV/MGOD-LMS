<?php

namespace App\Models;

use CodeIgniter\Model;

class GradingPeriodModel extends Model
{
    protected $table            = 'grading_periods';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'term_id',
        'period_name',
        'period_order',
        'weight_percentage',
        'start_date',
        'end_date',
        'is_active'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'term_id'           => 'required|integer',
        'period_name'       => 'required|string|max_length[50]',
        'period_order'      => 'required|integer',
        'weight_percentage' => 'required|decimal',
        'start_date'        => 'permit_empty|valid_date',
        'end_date'          => 'permit_empty|valid_date',
        'is_active'         => 'permit_empty|in_list[0,1]'
    ];

    // Callbacks
    protected $allowCallbacks = true;

    /**
     * Get grading periods for a term
     */
    public function getPeriodsByTerm($termId)
    {
        return $this->where('term_id', $termId)
                    ->where('is_active', 1)
                    ->orderBy('period_order', 'ASC')
                    ->findAll();
    }

    /**
     * Get current grading period for a term
     */
    public function getCurrentPeriod($termId)
    {
        $now = date('Y-m-d');
        
        return $this->where('term_id', $termId)
                    ->where('start_date <=', $now)
                    ->where('end_date >=', $now)
                    ->where('is_active', 1)
                    ->first();
    }

    /**
     * Validate that total weight = 100% for a term
     */
    public function validateTermWeights($termId)
    {
        $periods = $this->where('term_id', $termId)
                       ->where('is_active', 1)
                       ->findAll();
        
        $totalWeight = array_sum(array_column($periods, 'weight_percentage'));
        
        return abs($totalWeight - 100) < 0.01; // Allow for floating point precision
    }
}