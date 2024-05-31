<?php

namespace App\Models;

use CodeIgniter\Model;

class ManualPaymentsModel extends Model
{
    protected $table = 'manual_payments'; // Database table name
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

   public function getManualPaymentsByUserId($userId)
    {
        return $this->select('e.user_id, e.course_id, c.title, e.course_fee, e.id, e.date_added, mp.amount, mp.datetime')
                    ->from('manual_payments as mp')
                    ->join('enrol as e', 'e.id = mp.enrolment_id', 'left')
                    ->join('course as c', 'c.id = e.course_id', 'left')
                    ->where('e.user_id', $userId)
                    ->groupBy('e.id')
                    ->findAll();
    }

}
