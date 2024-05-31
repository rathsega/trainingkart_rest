<?php

namespace App\Models;

use CodeIgniter\Model;

class CourseModel extends Model
{
    protected $table = 'course'; // Database table name
    protected $primaryKey = 'id'; // Primary key of the table

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['title', 'short_description', 'description', 'outcomes', 'language', 'category_id', 'sub_category_id', 'section', 'requirements', 'price', 'discount_flag', 'discounted_price', 'level', 'user_id', 'thumbnail', 'video_url', 'date_added', 'last_modified', 'course_type', 'is_top_course', 'is_top10_course', 'show_it_in_category', 'is_admin', 'status', 'course_overview_provider', 'meta_keywords', 'meta_description', 'is_free_course', 'multi_instructor', 'enable_drip_content', 'creator', 'faqs', 'expiry_period', 'broucher', 'slug', 'category_slug', 'sub_category_slug', 'course_duration_in_hours', 'course_duration_in_months', 'daily_class_duration_in_hours', 'slug_count', 'order', 'about', 'learn', 'future', 'growth', 'experience', 'template', 'banner_image', 'weekend_track_course_duration_in_months', 'weekend_track_daily_class_duration_in_hours', 'weekend_track_course_price', 'weekend_track_sessions_count', 'week_track_sessions_count', 'number_of_lectures']; // Fields that are allowed to be filled

    protected $useTimestamps = false; // Set to true if you have created_at and updated_at fields
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;

    public function getCourseCount()
    {
        $query = $this->selectCount('id', 'course_count')
            ->get();

        // Get the result row
        $row = $query->getRow();

        // Access the count using the alias
        $courseCount = $row->course_count;

        // Echo the count
        return $courseCount;
    }

    public function getUpcomingCourses_old()
    {
        // Build the query using Query Builder
        $query = $this->orderBy('id', 'desc')
            ->where('status', 'upcoming')
            ->limit(8)
            ->get();

        // Get the result
        return $query->getResult();
    }

    public function getCoursesList($type)
    {
        $db = \Config\Database::connect();

        $sql = "SELECT 
            course.id as course_id, course.last_modified, course.title, course.price, course.thumbnail, course.discount_flag, course.discounted_price, course.course_duration_in_hours, course.number_of_lectures, course.slug,
            ratings_count.number_of_ratings,  one_rating_count, two_rating_count, three_rating_count,  four_rating_count, five_rating_count,
            users.first_name AS instructor_first_name, 
            users.last_name AS instructor_last_name, 
            users.image AS instructor_image 
        FROM 
            course 
        LEFT JOIN 
            ratings_count ON course.id = ratings_count.id 
        LEFT JOIN 
            users ON (FIND_IN_SET(users.id, course.user_id)) 
        WHERE 
            course.status = '" . $type . "' 
        GROUP BY 
            course.id 
        ORDER BY 
            course.id;
        ";

        // Execute the query
        $query = $db->query($sql);

        // Fetch the result rows
        return $query->getResult();
    }

    public function getTopTenLatestCourses()
    {
        $db = \Config\Database::connect();

        $sql = "SELECT 
            course.id as course_id, course.last_modified, course.title, course.price, course.thumbnail, course.discount_flag, course.discounted_price, course.course_duration_in_hours, course.number_of_lectures,  course.slug,
            ratings_count.number_of_ratings,  one_rating_count, two_rating_count, three_rating_count,  four_rating_count, five_rating_count,
            users.first_name AS instructor_first_name, 
            users.last_name AS instructor_last_name, 
            users.image AS instructor_image 
        FROM 
            course 
        LEFT JOIN 
            ratings_count ON course.id = ratings_count.id 
        LEFT JOIN 
            users ON (FIND_IN_SET(users.id, course.user_id)) 
        WHERE 
            course.status = 'active'  and course.is_top10_course = 1  
        GROUP BY 
            course.id 
        ORDER BY 
            course.id LIMIT 10;
        ";

        // Execute the query
        $query = $db->query($sql);

        // Fetch the result rows
        return $query->getResult();
    }

    public function getTopCourses()
    {
        $db = \Config\Database::connect();

        $sql = "SELECT 
            course.id as course_id, course.last_modified, course.title, course.price, course.thumbnail, course.discount_flag, course.discounted_price, course.course_duration_in_hours, course.number_of_lectures,  course.slug,
            ratings_count.number_of_ratings,  one_rating_count, two_rating_count, three_rating_count,  four_rating_count, five_rating_count,
            users.first_name AS instructor_first_name, 
            users.last_name AS instructor_last_name, 
            users.image AS instructor_image 
        FROM 
            course 
        LEFT JOIN 
            ratings_count ON course.id = ratings_count.id 
        LEFT JOIN 
            users ON (FIND_IN_SET(users.id, course.user_id)) 
        WHERE 
            course.status = 'active'  and course.is_top_course = 1  
        GROUP BY 
            course.id 
        ORDER BY 
            course.id LIMIT 8
        ";

        // Execute the query
        $query = $db->query($sql);

        // Fetch the result rows
        return $query->getResult();
    }

    public function getCourseBySlug($slug)
    {
        $db = \Config\Database::connect();

        $sql = "SELECT 
            course.id as course_id, course.*,
            ratings_count.number_of_ratings, ratings_count.number_of_students_enrolled,  ratings_count.one_rating_count, ratings_count.two_rating_count, ratings_count.three_rating_count, ratings_count. four_rating_count, ratings_count.five_rating_count,
            users.first_name AS instructor_first_name, 
            users.last_name AS instructor_last_name, 
            users.image AS instructor_image,            
            users.title AS instructor_designation,
            users.biography as instructor_biography,
            users.skills as instructor_skills,
            category.name AS category_name,
            sub_category.name AS sub_category_name,
            count(ins_course.id) as instructor_course_count,
            sum(ins_course.course_duration_in_hours) as instructor_course_duration,
            sum(ins_course.number_of_lectures) as instructor_number_of_lectures,
            sum(ins_ratings_count.number_of_students_enrolled) as instructor_number_of_students_enrolled 
        FROM 
            course 
        LEFT JOIN 
            ratings_count ON course.id = ratings_count.id 
        LEFT JOIN 
            category as category ON category.id = course.category_id
        LEFT JOIN 
            category as sub_category ON sub_category.id = course.sub_category_id
        LEFT JOIN 
            users ON SUBSTRING_INDEX(course.user_id, ',', -1) = users.id 
        LEFT JOIN 
            course as ins_course ON FIND_IN_SET(SUBSTRING_INDEX(course.user_id, ',', -1), ins_course.user_id)
        LEFT JOIN
            ratings_count as ins_ratings_count on ins_ratings_count.id = ins_course.id
        WHERE 
            course.status = 'active'  and course.slug = '" . $slug . "'
        GROUP BY 
            course.id 
        ";

        // Execute the query
        $query = $db->query($sql);

        // Fetch the result rows
        return $query->getRow();
    }

    public function getHomePageCourses()
    {
        $builder = $this->db->table('course');
        $builder->select("id, title, slug");
        $builder->where('is_top_course', 1)
            ->orWhere('is_top10_course', 1)
            ->orWhere('show_it_in_category', 1)
            ->Where('status', 'active');
        $query = $builder->get();
        return $query->getResult();
    }
}
