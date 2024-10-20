<?php

namespace App\Models;

use CodeIgniter\Model;

class WishlistModel extends Model
{
    protected $table      = 'wishlist';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'course_id', 'datetime'];


    function getExistedWishListItems($user_id){
        $query = $this->orderBy('id', 'desc')
            ->where('user_id', $user_id)
            ->get();

        // Get the result
        return $query->getResult();
    }

    function deleteAllWishlistItemsByUserId($user_id){
        return $this->where('user_id', $user_id)->delete();
    }

    function insertBatchRecords($data){
        return $this->insertBatch($data);
    }

    public function getWishlistByUserId($userId)
    {
        $db = \Config\Database::connect();

        $sql = "SELECT w.id,
            course.id as course_id, course.title, course.price, course.thumbnail, course.last_modified, course.discount_flag, course.discounted_price, course.is_free_course, course.slug,
            users.first_name AS instructor_first_name, 
            users.last_name AS instructor_last_name, 
            users.image AS instructor_image 
        FROM 
            course 
        RIGHT JOIN 
            wishlist as w ON course.id = w.course_id 
        RIGHT JOIN 
            users ON (FIND_IN_SET(users.id, course.user_id)) 
        WHERE 
            course.status = 'active' and w.user_id = $userId 
        GROUP BY
            w.id
        ORDER BY 
            w.id desc;
        ";

        // Execute the query
        $query = $db->query($sql);

        // Fetch the result rows
        return $query->getResult();
    }
    
    public function insertBatchIfNotExists($data)
    {
        $builder = $this->builder();
        foreach ($data as $row) {
            $builder->set($row)
                    ->where('course_id', $row['course_id'])
                    ->where('user_id', $row['user_id'])
                    ->where('datetime', $row['datetime'])
                    ->insert(null, false);
        }
    }
}
