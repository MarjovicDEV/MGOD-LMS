<?php

namespace App\Models;

use CodeIgniter\Model;

class TermModel extends Model
{
    protected $table            = 'terms';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'academic_year_id',
        'semester_id',
        'term_name',
        'start_date',
        'end_date',
        'enrollment_start',
        'enrollment_end',
        'is_current',
        'is_active'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';    // Validation
    protected $validationRules = [
        'academic_year_id' => 'required|integer',
        'semester_id'      => 'required|integer',
        'term_name'        => 'required|string|max_length[100]',
        'start_date'       => 'permit_empty|valid_date',
        'end_date'         => 'permit_empty|valid_date',
        'enrollment_start' => 'permit_empty|valid_date',
        'enrollment_end'   => 'permit_empty|valid_date',
        'is_current'       => 'permit_empty|in_list[0,1]',
        'is_active'        => 'permit_empty|in_list[0,1]'
    ];

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeUpdate   = ['ensureOnlyOneCurrent'];

    /**
     * Get current term
     */
    public function getCurrentTerm()
    {
        return $this->where('is_current', 1)->first();
    }    /**
     * Get term with academic year and semester details
     */
    public function getTermWithDetails($termId)
    {
        return $this->select('terms.*, academic_years.year_name, academic_years.year_code, semesters.semester_name')
                    ->join('academic_years', 'academic_years.id = terms.academic_year_id', 'left')
                    ->join('semesters', 'semesters.id = terms.semester_id', 'left')
                    ->find($termId);
    }

    /**
     * Get all terms with details
     */
    public function getAllWithDetails()
    {
        return $this->select('terms.*, academic_years.year_name, semesters.semester_name')
                    ->join('academic_years', 'academic_years.id = terms.academic_year_id')
                    ->join('semesters', 'semesters.id = terms.semester_id')
                    ->where('terms.is_active', 1)
                    ->orderBy('terms.start_date', 'DESC')
                    ->findAll();
    }

    /**
     * Check if enrollment is open for a term
     */
    public function isEnrollmentOpen($termId)
    {
        $term = $this->find($termId);
        if (!$term) return false;
        
        $now = date('Y-m-d');
        return ($now >= $term['enrollment_start'] && $now <= $term['enrollment_end']);
    }    /**
     * Set term as current
     */
    public function setAsCurrent($termId)
    {
        $this->db->transStart();
        
        // Set all to not current using query builder
        $this->db->table($this->table)
                 ->set('is_current', 0)
                 ->where('is_current', 1)
                 ->update();
        
        // Set specified term as current
        $this->update($termId, ['is_current' => 1]);
        
        $this->db->transComplete();
        
        return $this->db->transStatus();
    }

    /**
     * Ensure only one term is current
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
}
