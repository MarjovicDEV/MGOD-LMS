<?php

namespace App\Models;

use CodeIgniter\Model;

class YearLevelModel extends Model
{
    protected $table            = 'year_levels';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'year_level_name',
        'year_level_order',
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
        'year_level_name'  => 'required|string|max_length[50]|is_unique[year_levels.year_level_name,id,{id}]',
        'year_level_order' => 'required|integer',
        'description'      => 'permit_empty|string|max_length[100]',
        'is_active'        => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [
        'year_level_name' => [
            'required'  => 'Year level name is required',
            'is_unique' => 'This year level already exists'
        ]
    ];

    // Callbacks
    protected $allowCallbacks = true;

    /**
     * Get all year levels ordered
     */
    public function getAllOrdered()
    {
        return $this->where('is_active', 1)
                    ->orderBy('year_level_order', 'ASC')
                    ->findAll();
    }

    /**
     * Get year level by order
     */
    public function getByOrder($order)
    {
        return $this->where('year_level_order', $order)->first();
    }
}