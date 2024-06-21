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
        return $this->where('user_id', $userId)->findAll();
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
