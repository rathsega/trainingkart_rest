<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table = 'payment'; // Database table name
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

   public function getPaymentsByUserId($userId)
    {
        return $this->select('p.user_id, p.course_id, c.title, p.date_added, p.amount')
                    ->from('payment as p')
                    ->join('course as c', 'c.id = p.course_id', 'left')
                    ->where('p.user_id', $userId)
                    ->groupBy('p.id')
                    ->findAll();
    }

}
