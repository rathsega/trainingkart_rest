<?php

namespace App\Models;

use CodeIgniter\Model;

class BlogModel extends Model
{
    protected $table = 'blogs'; // Database table name
    protected $primaryKey = 'id'; // Primary key of the table

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
                     ->limit($perPage, $offset) // Apply limit and offset
                     ->findAll(); // Fetch the data
    }
}
