<?php

namespace App\Models;

use CodeIgniter\Model;

class AcademicYearModel extends Model
{
    protected $table            = 'academic_years';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'year_code',
        'year_name',
        'start_date',
        'end_date',
        'is_current',
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
        'year_code'  => 'required|string|max_length[20]|is_unique[academic_years.year_code,id,{id}]',
        'year_name'  => 'required|string|max_length[50]',
        'start_date' => 'required|valid_date',
        'end_date'   => 'required|valid_date',
        'is_current' => 'permit_empty|in_list[0,1]',
        'is_active'  => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [
        'year_code' => [
            'required'  => 'Year code is required',
            'is_unique' => 'This year code already exists'
        ]
    ];

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeUpdate   = ['ensureOnlyOneCurrent'];

    /**
     * Get current academic year
     */
    public function getCurrentYear()
    {
        return $this->where('is_current', 1)->first();
    }

    /**
     * Set academic year as current (ensures only one is current)
     */
    public function setAsCurrent($yearId)
    {
        $this->db->transStart();
        
        // Set all to not current
        $this->set('is_current', 0)->update();
        
        // Set specified year as current
        $this->update($yearId, ['is_current' => 1]);
        
        $this->db->transComplete();
        
        return $this->db->transStatus();
    }

    /**
     * Ensure only one academic year is current
     */
    protected function ensureOnlyOneCurrent(array $data)
    {
        if (isset($data['data']['is_current']) && $data['data']['is_current'] == 1) {
            $this->where('id !=', $data['id'][0] ?? 0)
                 ->set('is_current', 0)
                 ->update();
        }
        return $data;
    }

    /**
     * Get all active years ordered
     */
    public function getActiveYears()
    {
        return $this->where('is_active', 1)
                    ->orderBy('start_date', 'DESC')
                    ->findAll();
    }
}