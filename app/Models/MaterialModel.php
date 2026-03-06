<?php

namespace App\Models;

use CodeIgniter\Model;

class MaterialModel extends Model
{
    protected $table            = 'materials';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'course_offering_id',
        'title',
        'description',
        'file_name',
        'file_path',
        'file_size',
        'file_type',
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
        'course_offering_id' => 'required|integer',
        'title'              => 'required|string|max_length[255]',
        'description'        => 'permit_empty|string',
        'file_name'          => 'required|string|max_length[255]',
        'file_path'          => 'required|string|max_length[255]',
        'file_size'          => 'permit_empty|integer',
        'file_type'          => 'permit_empty|string|max_length[50]',
        'is_active'          => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [
        'course_offering_id' => [
            'required' => 'Course offering is required'
        ],
        'title' => [
            'required' => 'Material title is required'
        ],
        'file_name' => [
            'required' => 'File name is required'
        ],
        'file_path' => [
            'required' => 'File path is required'
        ]
    ];

    // Callbacks
    protected $allowCallbacks = true;

    /**
     * Get all materials for a course offering
     */
    public function getOfferingMaterials($offeringId)
    {
        return $this->where('course_offering_id', $offeringId)
                    ->where('is_active', 1)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get material with course details
     */
    public function getMaterialWithDetails($materialId)
    {
        return $this->select('
                materials.*,
                co.section,
                c.course_code,
                c.title as course_title
            ')
            ->join('course_offerings co', 'co.id = materials.course_offering_id')
            ->join('courses c', 'c.id = co.course_id')
            ->find($materialId);
    }

    /**
     * Get materials by file type
     */
    public function getMaterialsByType($offeringId, $fileType)
    {
        return $this->where('course_offering_id', $offeringId)
                    ->where('file_type', $fileType)
                    ->where('is_active', 1)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Search materials
     */
    public function searchMaterials($offeringId, $keyword)
    {
        return $this->where('course_offering_id', $offeringId)
                    ->groupStart()
                        ->like('title', $keyword)
                        ->orLike('description', $keyword)
                        ->orLike('file_name', $keyword)
                    ->groupEnd()
                    ->where('is_active', 1)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get total storage used by course offering
     */
    public function getTotalStorageUsed($offeringId)
    {
        $result = $this->selectSum('file_size')
                      ->where('course_offering_id', $offeringId)
                      ->where('is_active', 1)
                      ->get()
                      ->getRow();
        
        return $result->file_size ?? 0;
    }

    /**
     * Delete material and file
     */
    public function deleteMaterial($materialId)
    {
        $material = $this->find($materialId);
        
        if ($material && file_exists($material['file_path'])) {
            unlink($material['file_path']);
        }
        
        return $this->delete($materialId);
    }

    /**
     * Format file size
     */
    public function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }    /**
     * Get materials count by offering
     */
    public function countMaterials($offeringId)
    {
        return $this->where('course_offering_id', $offeringId)
                    ->where('is_active', 1)
                    ->countAllResults();
    }

    /**
     * Get materials by course_offering_id (for backward compatibility with controller)
     */
    public function getMaterialsByCourse($offeringId)
    {
        return $this->getOfferingMaterials($offeringId);
    }    /**
     * Insert material (for backward compatibility with controller)
     */
    public function insertMaterial($data)
    {
        // Map course_id to course_offering_id if needed
        if (isset($data['course_id']) && !isset($data['course_offering_id'])) {
            $data['course_offering_id'] = $data['course_id'];
            unset($data['course_id']);
        }

        // Add default values if not set
        if (!isset($data['title'])) {
            $data['title'] = $data['file_name'] ?? 'Untitled';
        }
        if (!isset($data['file_size']) && isset($data['file_path'])) {
            $fullPath = WRITEPATH . $data['file_path'];
            $data['file_size'] = file_exists($fullPath) ? filesize($fullPath) : 0;
        }
        if (!isset($data['file_type'])) {
            $extension = pathinfo($data['file_name'] ?? '', PATHINFO_EXTENSION);
            $data['file_type'] = $extension;
        }
        if (!isset($data['is_active'])) {
            $data['is_active'] = 1;
        }

        return $this->insert($data) ? $this->getInsertID() : false;
    }
}
