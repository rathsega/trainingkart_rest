<?php

namespace App\Models;

use CodeIgniter\Model;

class EnrolModel extends Model
{
    protected $table = 'enrol'; // Database table name
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

   public function getAllEnrolmentsByUserId($user_id){
        $db = \Config\Database::connect();

        $sql = "SELECT 
            course.id as course_id, course.last_modified, course.title, course.price, course.thumbnail, course.discount_flag, course.discounted_price, course.course_duration_in_hours, course.number_of_lectures, 
            ratings_count.number_of_ratings,  one_rating_count, two_rating_count, three_rating_count,  four_rating_count, five_rating_count,
            enrol.date_added, enrol.expiry_date
        FROM 
            enrol 
        LEFT JOIN
            course ON course.id = enrol.course_id
        LEFT JOIN 
            ratings_count ON course.id = ratings_count.id 
        WHERE 
            enrol.user_id = $user_id
        GROUP BY 
            course.id 
        ORDER BY 
            enrol.id
        ";

        // Execute the query
        $query = $db->query($sql);

        // Fetch the result rows
        return $query->getResult();   
   }

   public function getEnrolmentCountByCourse($course_id){
    $db = \Config\Database::connect();
    $sql = "select count(id) as count from enrol where course_id=".$course_id;
    // Execute the query
    $query = $db->query($sql);

    // Fetch the result rows
    return $query->getRow();  
   }  
}
