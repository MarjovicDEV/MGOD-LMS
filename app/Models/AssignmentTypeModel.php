<?php

namespace App\Models;

use CodeIgniter\Model;

class AssignmentTypeModel extends Model
{
    protected $table            = 'assignment_types';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'type_name',
        'type_code',
        'description',
        'default_weight',
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
        'type_name'      => 'required|string|max_length[50]',
        'type_code'      => 'required|string|max_length[20]|is_unique[assignment_types.type_code,id,{id}]',
        'description'    => 'permit_empty|string|max_length[255]',
        'default_weight' => 'permit_empty|decimal',
        'is_active'      => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [
        'type_code' => [
            'required'  => 'Type code is required',
            'is_unique' => 'This type code already exists'
        ]
    ];

    // Callbacks
    protected $allowCallbacks = true;

    /**
     * Get all active assignment types
     */
    public function getActiveTypes()
    {
        return $this->where('is_active', 1)->findAll();
    }

    /**
     * Get type by code
     */
    public function getByCode($code)
    {
        return $this->where('type_code', $code)->first();
    }
}