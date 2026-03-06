<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table            = 'roles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'role_name',
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
        'role_name'   => 'required|string|max_length[50]|is_unique[roles.role_name,id,{id}]',
        'description' => 'permit_empty|string|max_length[255]',
        'is_active'   => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [
        'role_name' => [
            'required'  => 'Role name is required',
            'is_unique' => 'This role name already exists'
        ]
    ];

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];

    /**
     * Get all active roles
     */
    public function getActiveRoles()
    {
        return $this->where('is_active', 1)->findAll();
    }

    /**
     * Get role by name
     */
    public function getRoleByName($roleName)
    {
        return $this->where('role_name', $roleName)->first();
    }

    /**
     * Check if role exists
     */
    public function roleExists($roleId)
    {
        return $this->find($roleId) !== null;
    }
}
