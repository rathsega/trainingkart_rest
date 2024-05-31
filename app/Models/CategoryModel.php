<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table = 'category'; // Database table name
    protected $primaryKey = 'id'; // Primary key of the table

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['code', 'name', 'parent', 'slug', 'date_added', 'last_modified', 'font_awesome_class', 'thumbnail', 'logo']; // Fields that are allowed to be filled

    protected $useTimestamps = false; // Set to true if you have created_at and updated_at fields


    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;

    public function getCategories()
    {
        // Get the total number of students
        //return $this->findAll();
        $sql = "SELECT c.id, c.name, c.thumbnail, c.slug, COUNT(DISTINCT SUBSTRING_INDEX(courses.user_id, ',', -1)) as instructor_count
                FROM category c
                LEFT JOIN course courses ON FIND_IN_SET(c.id, courses.user_id) where c.parent=0 
                GROUP BY c.id";

        return $this->db->query($sql)->getResult();
    }
}
