<?php

namespace App\Models;

use CodeIgniter\Model;

class SemesterModel extends Model
{
    protected $table            = 'semesters';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'semester_name',
        'semester_order',
        'description',
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
        'semester_name'  => 'required|string|max_length[50]|is_unique[semesters.semester_name,id,{id}]',
        'semester_order' => 'required|integer',
        'description'    => 'permit_empty|string|max_length[100]',
        'is_active'      => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [
        'semester_name' => [
            'required'  => 'Semester name is required',
            'is_unique' => 'This semester already exists'
        ]
    ];

    // Callbacks
    protected $allowCallbacks = true;

    /**
     * Get all semesters ordered
     */
    public function getAllOrdered()
    {
        return $this->where('is_active', 1)
                    ->orderBy('semester_order', 'ASC')
                    ->findAll();
    }
}