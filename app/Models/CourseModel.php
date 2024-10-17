<?php

namespace App\Models;

use CodeIgniter\Model;

class CourseModel extends Model
{
    protected $table = 'course'; // Database table name
    protected $primaryKey = 'id'; // Primary key of the table

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['title', 'short_description', 'description', 'outcomes', 'language', 'category_id', 'sub_category_id', 'section', 'requirements', 'price', 'discount_flag', 'discounted_price', 'level', 'user_id', 'thumbnail', 'video_url', 'date_added', 'last_modified', 'course_type', 'is_top_course', 'is_top10_course', 'show_it_in_category', 'is_admin', 'status', 'course_overview_provider', 'meta_keywords', 'meta_description', 'is_free_course', 'multi_instructor', 'enable_drip_content', 'creator', 'faqs', 'expiry_period', 'broucher', 'slug', 'category_slug', 'sub_category_slug', 'course_duration_in_hours', 'course_duration_in_months', 'daily_class_duration_in_hours', 'slug_count', 'order', 'about', 'learn', 'future', 'growth', 'experience', 'template', 'banner_image', 'weekend_track_course_duration_in_months', 'weekend_track_daily_class_duration_in_hours', 'weekend_track_course_price', 'weekend_track_sessions_count', 'week_track_sessions_count', 'number_of_lectures', "batch_1", "batch_2", "career_growth"]; // Fields that are allowed to be filled

    protected $useTimestamps = false; // Set to true if you have created_at and updated_at fields
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;

    public function getCourseCount()
    {
        // Define a cache key for your query result
        $cacheKey = 'course_count';
        $cacheTime = 3600 * 24; // Cache for a month
        // Check if the result is already cached
        if (!$results = cache()->get($cacheKey)) {
            $query = $this->selectCount('id', 'course_count')
                ->get();

            // Get the result row
            $row = $query->getRow();

            // Access the count using the alias
            $courseCount = $row->course_count;

            // Echo the count
            $results = $courseCount;
            //cache()->save($cacheKey, $results, $cacheTime);
            return $results;
        } else {
            // Fetch the cached result
            return cache()->get($cacheKey);
        }
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
        // Define a cache key for your query result
        $cacheKey = 'courses_list_' . $type;
        $cacheTime = 3600 * 24; // Cache for a month
        // Check if the result is already cached
        if (!$results = cache()->get($cacheKey)) {
            $db = \Config\Database::connect();

            $sql = "SELECT 
            course.id as course_id, course.last_modified, course.title, course.price, course.thumbnail, course.discount_flag, course.discounted_price, course.course_duration_in_hours, course.number_of_lectures, course.slug, course.slug_count, course.category_slug, course.sub_category_slug,
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
            $results = $query->getResult();
            //cache()->save($cacheKey, $results, $cacheTime);
            return $results;
        } else {
            // Fetch the cached result
            return cache()->get($cacheKey);
        }
    }

    public function getTopTenLatestCourses()
    {
        // Define a cache key for your query result
        $cacheKey = 'top_ten_latest_courses';
        $cacheTime = 3600 * 24; // Cache for a month
        // Check if the result is already cached
        if (!$results = cache()->get($cacheKey)) {
            $db = \Config\Database::connect();

            $sql = "SELECT 
            course.id as course_id, course.last_modified, course.title, course.price, course.thumbnail, course.discount_flag, course.discounted_price, course.course_duration_in_hours, course.number_of_lectures,  course.slug, course.slug_count, course.category_slug, course.sub_category_slug,
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
            $results =  $query->getResult(); // Save the result in cache
            //cache()->save($cacheKey, $results, $cacheTime);
            return $results;
        } else {
            // Fetch the cached result
            return cache()->get($cacheKey);
        }
    }

    public function getTopCourses()
    {
        // Define a cache key for your query result
        $cacheKey = 'top_courses';
        $cacheTime = 3600 * 24; // Cache for a month
        // Check if the result is already cached
        if (!$results = cache()->get($cacheKey)) {
            $db = \Config\Database::connect();

            $sql = "SELECT 
            course.id as course_id, course.last_modified, course.title, course.price, course.thumbnail, course.discount_flag, course.discounted_price, course.course_duration_in_hours, course.number_of_lectures,  course.slug, course.slug_count, course.category_slug, course.sub_category_slug,
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
            $results = $query->getResult();
            // Save the result in cache
            //cache()->save($cacheKey, $results, $cacheTime);
            return $results;
        } else {
            // Fetch the cached result
            return cache()->get($cacheKey);
        }
    }

    public function getCourseBySlug($slug)
    {
        // Define a cache key for your query result
        $cacheKey = 'course_by_slug_' . str_replace('/', '_', $slug);
        $cacheTime = 3600 * 24; // Cache for a month
        // Check if the result is already cached
        if (!$results = cache()->get($cacheKey)) {
            $db = \Config\Database::connect();

            $sql = "SELECT 
            course.id as course_id, course.*,
            ratings_count.number_of_ratings, ratings_count.number_of_students_enrolled,  ratings_count.one_rating_count, ratings_count.two_rating_count, ratings_count.three_rating_count, ratings_count. four_rating_count, ratings_count.five_rating_count, course.daily_class_duration_in_hours, 
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
            $results =   $query->getRow();
            // Save the result in cache
            //cache()->save($cacheKey, $results, $cacheTime);
            return $results;
        } else {
            // Fetch the cached result
            return cache()->get($cacheKey);
        }
    }

    public function getCourseDetailsBySlug($slug)
    {
        // Define a cache key for your query result
        $cacheKey = 'course_details_by_slug_' . str_replace('/', '_', $slug);
        $cacheTime = 3600 * 24; // Cache for a month
        // Check if the result is already cached
        if (!$results = cache()->get($cacheKey)) {
            $db = \Config\Database::connect();

            $sql = "SELECT 
                    course.id as course_id, course.title, course.short_description, course.description, course.outcomes, course.status, course.batch_1, course.batch_2, course.experience, course.banner_image, course.language, course.price, course.discount_flag, course.discounted_price, course.level, course.video_url, course.thumbnail, course.meta_keywords, course.meta_description, course.number_of_lectures, course.course_duration_in_hours, course.is_free_course, course.last_modified, course.is_top_course, course.is_top10_course, course.show_it_in_category, course.broucher,  course.daily_class_duration_in_hours, 
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
            $results =   $query->getRow();
            // Save the result in cache
            //cache()->save($cacheKey, $results, $cacheTime);
            return $results;
        } else {
            // Fetch the cached result
            return cache()->get($cacheKey);
        }
    }

    public function getRequiredData($course_id, $element)
    {
        // Define a cache key for your query result
        $cacheKey = 'course_' . $course_id . '_' . $element;
        $cacheTime = 3600 * 24; // Cache for a month
        // Check if the result is already cached
        if (!$results = cache()->get($cacheKey)) {
            $builder = $this->db->table('course');
            $builder->select("id, $element");
            $builder->where('id', $course_id)
                ->Where('status', 'active');
            $query = $builder->get();
            $results =  $query->getRow();
            // Save the result in cache
            //cache()->save($cacheKey, $results, $cacheTime);
            return $results;
        } else {
            // Fetch the cached result
            return cache()->get($cacheKey);
        }
    }

    public function getHomePageCourses()
    {
        // Define a cache key for your query result
        $cacheKey = 'home_page_courses';
        $cacheTime = 3600 * 24; // Cache for a month
        // Check if the result is already cached
        if (!$results = cache()->get($cacheKey)) {
            $builder = $this->db->table('course');
            $builder->select("id, title, slug");
            $builder->where('is_top_course', 1)
                ->orWhere('is_top10_course', 1)
                ->orWhere('show_it_in_category', 1)
                ->Where('status', 'active');
            $query = $builder->get();
            $results =  $query->getResult();
            // Save the result in cache
            //cache()->save($cacheKey, $results, $cacheTime);
            return $results;
        } else {
            // Fetch the cached result
            return cache()->get($cacheKey);
        }
    }

    public function getCourseById($ids)
    {
        $query = $this->orderBy('id', 'desc')
            ->whereIn('id', $ids)
            ->get();

        // Get the result
        return $query->getResult();
    }

    public function getAllCourses($title, $category_id, $price, $level, $sort_by, $limit, $offset)
    {
        // Start building the query
        $sql = "SELECT *, course.id as course_id,  ratings_count.number_of_ratings,  one_rating_count, two_rating_count, three_rating_count,  four_rating_count, five_rating_count FROM " . $this->table . " LEFT JOIN 
        ratings_count ON course.id = ratings_count.id  WHERE status = 'active' AND show_it_in_category = 1";
        $bindings = [];

        // Apply filters based on provided parameters
        if (!empty($title)) {
            $sql .= " AND title LIKE ?";
            $bindings[] = '%' . $title . '%';
        }

        if (!empty($category_id) && count($category_id) > 0) {
            $sql .= " AND category_id in ?";
            $bindings[] = $category_id;
        }

        if (!empty($price)) {
            if ($price === 'free') {
                $sql .= " AND is_free_course = ?";
                $bindings[] = 1;
            } elseif ($price === 'paid') {
                $sql .= " AND is_free_course = ?";
                $bindings[] = 0;
            }
        }

        if (!empty($level)) {
            $sql .= " AND level = ?";
            $bindings[] = $level;
        }

        if (!empty($sort_by)) {
            // Assuming sort_by value is something like 'title asc' or 'title desc'
            $sql .= " ORDER BY course.order " . $sort_by;
        }

        /*if (!empty($limit)) {
            $limit = intval($limit);
            $offset = !empty($offset) ? intval($offset) : 0;
            $sql .= " LIMIT ? OFFSET ?";
            $bindings[] = $limit;
            $bindings[] = $offset;
        }*/

        // Execute the query
        $query = $this->db->query($sql, $bindings);

        // Get the total count of matched records
        $totalCountQuery = $this->db->query("SELECT FOUND_ROWS() AS total_count");
        $totalCount = $totalCountQuery->getRow()->total_count;

        if (!empty($limit)) {
            $limit = intval($limit);
            $offset = !empty($offset) ? intval($offset) : 0;
            $sql .= " LIMIT ? OFFSET ?";
            $bindings[] = $limit;
            $bindings[] = $offset;
        }

        // Execute the query
        $query = $this->db->query($sql, $bindings);
        return [
            'total_count' => $totalCount,
            'data' => $query->getResult()
        ];
    }

    public function getCourseCurriculum($course_id)
    {
        $db = \Config\Database::connect();

        // Define a cache key for your query result
        $cacheKey = 'course_curriculum_' . $course_id;
        $cacheTime = 3600 * 24; // Cache for a month
        // Check if the result is already cached
        if (!$results = cache()->get($cacheKey)) {
            $builder = $db->table('section');
            $builder->select('
            section.id AS section_id,
            section.title AS section_title,
            section.course_id,
            section.order AS section_order,
            section.start_date,
            section.end_date,
            section.restricted_by,
            chapters.id AS chapter_id,
            chapters.title AS chapter_title,
            chapters.order AS chapter_order,
            lesson.id AS lesson_id,
            lesson.title AS lesson_title,
            lesson.duration,
            lesson.section_id AS lesson_section_id,
            lesson.chapter_id AS lesson_chapter_id,
            lesson.video_type,
            lesson.video_url
        ');

            // Add the joins
            $builder->join('chapters', 'chapters.section_id = section.id', 'left');
            $builder->join('lesson', 'lesson.section_id = section.id OR lesson.chapter_id = chapters.id', 'left');

            // Add the where condition
            $builder->where('section.course_id', $course_id);

            // Order the results
            $builder->orderBy('section.order', 'ASC');
            $builder->orderBy('chapters.order', 'ASC');
            $builder->orderBy('lesson.order', 'ASC');

            // Execute the query and get the results
            $query = $builder->get();
            $results =  $query->getResultArray();
            // Save the result in cache
            //cache()->save($cacheKey, $results, $cacheTime);
            return $results;
        } else {
            // Fetch the cached result
            return cache()->get($cacheKey);
        }
    }

    public function getRelatedCourses($id, $limit, $offset)
    {
        // Define a cache key for your query result
        $cacheKey = 'related_courses';
        $cacheTime = 3600 * 24; // Cache for a month
        // Check if the result is already cached
        if (!$results = cache()->get($cacheKey)) {
            $db = \Config\Database::connect();

            $sql = "SELECT 
    course.id AS course_id, 
    course.last_modified, 
    course.title, 
    course.price, 
    course.thumbnail, 
    course.discount_flag, 
    course.discounted_price, 
    course.course_duration_in_hours, 
    course.number_of_lectures, 
    course.slug, 
    course.slug_count, 
    course.category_slug, 
    course.sub_category_slug,
    ratings_count.number_of_ratings,  
    ratings_count.one_rating_count, 
    ratings_count.two_rating_count, 
    ratings_count.three_rating_count,  
    ratings_count.four_rating_count, 
    ratings_count.five_rating_count,
    users.first_name AS instructor_first_name, 
    users.last_name AS instructor_last_name, 
    users.image AS instructor_image 
FROM 
    course 
LEFT JOIN 
    ratings_count ON course.id = ratings_count.id 
LEFT JOIN 
                    users ON SUBSTRING_INDEX(course.user_id, ',', -1) = users.id 
WHERE 
    course.status = 'active'  
    AND category_id = (SELECT category_id FROM course WHERE id = $id) 
    AND sub_category_id = (SELECT sub_category_id FROM course WHERE id = $id) 
    AND (course.is_top_course = 1 OR course.is_top10_course = 1 OR course.show_it_in_category = 1) 
    AND course.id != $id
GROUP BY 
    course.id 
ORDER BY 
    course.id 
LIMIT  $offset, $limit

                  ";
            $query = $this->db->query($sql);
            // Return the results
            return $query->getResult();
        } else {
            // Fetch the cached result
            return cache()->get($cacheKey);
        }
    }
}
