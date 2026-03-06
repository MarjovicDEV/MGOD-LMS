<?php

namespace App\Models;

use CodeIgniter\Model;

class LessonModel extends Model
{
    protected $table            = 'lessons';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'course_offering_id',
        'title',
        'description',
        'content',
        'lesson_order',
        'is_published',
        'published_at'
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
        'content'            => 'permit_empty|string',
        'lesson_order'       => 'permit_empty|integer',
        'is_published'       => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [
        'course_offering_id' => [
            'required' => 'Course offering is required'
        ],
        'title' => [
            'required' => 'Lesson title is required'
        ]
    ];

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['setPublishedDate'];
    protected $beforeUpdate   = ['setPublishedDate'];

    /**
     * Set published_at date when is_published changes to 1
     */
    protected function setPublishedDate(array $data)
    {
        if (isset($data['data']['is_published']) && $data['data']['is_published'] == 1) {
            if (!isset($data['data']['published_at']) || empty($data['data']['published_at'])) {
                $data['data']['published_at'] = date('Y-m-d H:i:s');
            }
        }
        return $data;
    }

    /**
     * Get all lessons for a course offering
     */
    public function getOfferingLessons($offeringId, $publishedOnly = false)
    {
        $builder = $this->where('course_offering_id', $offeringId);
        
        if ($publishedOnly) {
            $builder->where('is_published', 1);
        }
        
        return $builder->orderBy('lesson_order', 'ASC')
                       ->orderBy('created_at', 'ASC')
                       ->findAll();
    }

    /**
     * Get published lessons for students
     */
    public function getPublishedLessons($offeringId)
    {
        return $this->getOfferingLessons($offeringId, true);
    }

    /**
     * Get lesson with course details
     */
    public function getLessonWithDetails($lessonId)
    {
        return $this->select('
                lessons.*,
                co.section,
                c.course_code,
                c.title as course_title
            ')
            ->join('course_offerings co', 'co.id = lessons.course_offering_id')
            ->join('courses c', 'c.id = co.course_id')
            ->find($lessonId);
    }

    /**
     * Publish a lesson
     */
    public function publishLesson($lessonId)
    {
        return $this->update($lessonId, [
            'is_published' => 1,
            'published_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Unpublish a lesson
     */
    public function unpublishLesson($lessonId)
    {
        return $this->update($lessonId, [
            'is_published' => 0,
            'published_at' => null
        ]);
    }

    /**
     * Reorder lessons
     */
    public function reorderLessons($offeringId, $lessonOrders)
    {
        $this->db->transStart();
        
        foreach ($lessonOrders as $lessonId => $order) {
            $this->update($lessonId, ['lesson_order' => $order]);
        }
        
        $this->db->transComplete();
        
        return $this->db->transStatus();
    }

    /**
     * Get next lesson order number
     */
    public function getNextOrder($offeringId)
    {
        $lastLesson = $this->where('course_offering_id', $offeringId)
                          ->orderBy('lesson_order', 'DESC')
                          ->first();
        
        return $lastLesson ? $lastLesson['lesson_order'] + 1 : 1;
    }

    /**
     * Get previous lesson
     */
    public function getPreviousLesson($offeringId, $currentOrder)
    {
        return $this->where('course_offering_id', $offeringId)
                    ->where('lesson_order <', $currentOrder)
                    ->where('is_published', 1)
                    ->orderBy('lesson_order', 'DESC')
                    ->first();
    }

    /**
     * Get next lesson
     */
    public function getNextLesson($offeringId, $currentOrder)
    {
        return $this->where('course_offering_id', $offeringId)
                    ->where('lesson_order >', $currentOrder)
                    ->where('is_published', 1)
                    ->orderBy('lesson_order', 'ASC')
                    ->first();
    }

    /**
     * Count lessons in offering
     */
    public function countLessons($offeringId, $publishedOnly = false)
    {
        $builder = $this->where('course_offering_id', $offeringId);
        
        if ($publishedOnly) {
            $builder->where('is_published', 1);
        }
        
        return $builder->countAllResults();
    }

    /**
     * Search lessons
     */
    public function searchLessons($offeringId, $keyword)
    {
        return $this->where('course_offering_id', $offeringId)
                    ->groupStart()
                        ->like('title', $keyword)
                        ->orLike('description', $keyword)
                        ->orLike('content', $keyword)
                    ->groupEnd()
                    ->where('is_published', 1)
                    ->orderBy('lesson_order', 'ASC')
                    ->findAll();
    }
}