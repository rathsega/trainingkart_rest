<?php

namespace App\Models;

use CodeIgniter\Model;

class BlogModel extends Model
{
    protected $table = 'blogs'; // Database table name
    protected $primaryKey = 'blog_id'; // Primary key of the table

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    //protected $allowedFields = ['first_name', 'last_name', 'email', 'phone', 'password', 'status', 'role_id']; // Fields that are allowed to be filled

    protected $useTimestamps = false; // Set to true if you have created_at and updated_at fields
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;

   public function getAll(){
     return $this->getAll();
   }

   public function getPaginatedBlogs(int $page, int $perPage = 10)
    {
        $offset = ($page - 1) ? ($page - 1) * $perPage : 0; // Calculate offset
        return $this->asArray() // Ensure we're returning an array
        ->where('(is_interview_question IS NULL OR  is_interview_question = 0 OR is_interview_question = FALSE)')
        ->orderBy('added_date', 'DESC')
                     ->limit($perPage, $offset) // Apply limit and offset
                     ->findAll(); // Fetch the data
    }

    public function getPaginatedInterviewQuestions(int $page, int $perPage = 10)
    {
        $offset = ($page - 1) ? ($page - 1) * $perPage : 0; // Calculate offset
        return $this->asArray() // Ensure we're returning an array
        ->where('(is_interview_question = 1 OR is_interview_question = TRUE)')
        ->orderBy('added_date', 'DESC')
                     ->limit($perPage, $offset) // Apply limit and offset
                     ->findAll(); // Fetch the data
    }

    public function getBlogsCount()
    {
        $query = $this->selectCount('blog_id', 'blog_count')
        ->where('(is_interview_question IS NULL OR  is_interview_question = 0 OR is_interview_question = FALSE)')
            ->get();

        // Get the result row
        $row = $query->getRow();

        // Access the count using the alias
        $blogCount = $row->blog_count;

        // Echo the count
        return $blogCount;
    }

    public function getInterviewQuestionsCount()
    {
        $query = $this->selectCount('blog_id', 'blog_count')
        ->where('(is_interview_question = 1 OR is_interview_question = TRUE)')
            ->get();

        // Get the result row
        $row = $query->getRow();

        // Access the count using the alias
        $blogCount = $row->blog_count;

        // Echo the count
        return $blogCount;
    }

    public function getAllKeywords()
    {
        $builder = $this->builder();
        $builder->select('keywords')
        ->where('(is_interview_question IS NULL OR  is_interview_question = 0 OR is_interview_question = FALSE)')
        ->orderBy('added_date', 'DESC') // Assuming 'created_at' is the timestamp column
                ->limit(2);
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function getRecentTagsOfBlogs()
    {
        $builder = $this->builder();
        $builder->select('tag')
        ->where('(is_interview_question IS NULL OR  is_interview_question = 0 OR is_interview_question = FALSE)')
        ->orderBy('added_date', 'DESC') // Assuming 'created_at' is the timestamp column
                ->limit(6);
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function getRecentTagsOfInterviews()
    {
        $builder = $this->builder();
        $builder->select('tag')
        ->where('(is_interview_question = 1 OR is_interview_question = TRUE)')
        ->orderBy('added_date', 'DESC') // Assuming 'created_at' is the timestamp column
                ->limit(6);
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function getCategoriesWithBlogCount()
    {
        $builder = $this->db->table('blog_category bc');
        $builder->select('bc.blog_category_id, bc.title, COUNT(b.blog_id) as blog_count')
                ->join('blogs b', 'bc.blog_category_id = b.blog_category_id', 'left')
                ->where('(b.is_interview_question IS NULL OR  b.is_interview_question = 0 OR b.is_interview_question = FALSE)')
                ->groupBy('bc.blog_category_id, bc.title');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function getCategoriesWithInterviewQuestionCount()
    {
        $builder = $this->db->table('blog_category bc');
        $builder->select('bc.blog_category_id, bc.title, COUNT(b.blog_id) as blog_count')
                ->join('blogs b', 'bc.blog_category_id = b.blog_category_id', 'left')
                ->where('(b.is_interview_question = 1 OR b.is_interview_question = TRUE)')
                ->groupBy('bc.blog_category_id, bc.title');
        $query = $builder->get();
        return $query->getResultArray();
    }

}
