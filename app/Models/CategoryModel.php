<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table            = 'categories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'category_name',
        'category_code',
        'description',
        'parent_id',
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
        'category_name' => 'required|string|max_length[100]',
        'category_code' => 'required|string|max_length[20]|is_unique[categories.category_code,id,{id}]',
        'description'   => 'permit_empty|string',
        'parent_id'     => 'permit_empty|integer',
        'is_active'     => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [
        'category_code' => [
            'required'  => 'Category code is required',
            'is_unique' => 'This category code already exists'
        ]
    ];

    // Callbacks
    protected $allowCallbacks = true;

    /**
     * Get all parent categories (no parent_id)
     */
    public function getParentCategories()
    {
        return $this->where('parent_id', null)
                    ->where('is_active', 1)
                    ->findAll();
    }

    /**
     * Get subcategories of a parent
     */
    public function getSubcategories($parentId)
    {
        return $this->where('parent_id', $parentId)
                    ->where('is_active', 1)
                    ->findAll();
    }

    /**
     * Get category hierarchy (tree structure)
     */
    public function getCategoryTree()
    {
        $parents = $this->getParentCategories();
        
        foreach ($parents as &$parent) {
            $parent['subcategories'] = $this->getSubcategories($parent['id']);
        }
        
        return $parents;
    }
}