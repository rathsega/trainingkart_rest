<?php

namespace App\Models;

use CodeIgniter\Model;

class DemoRequestsModel extends Model
{
    protected $table = 'demo_requests'; // Database table name
    protected $primaryKey = 'id'; // Primary key of the table

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['name', 'email', 'phone', 'course', 'date']; // Fields that are allowed to be filled

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

   public function insertDemoRequest(array $data)
    {
        return $this->insert($data);
    }
}
