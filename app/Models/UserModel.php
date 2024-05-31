<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users'; // Database table name
    protected $primaryKey = 'id'; // Primary key of the table

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['first_name', 'last_name', 'email', 'phone', 'password', 'status','is_instructor','social_links', 'role_id', 'skills', 'payment_keys', 'resume', "education", "work_experience", "biography", "image"]; // Fields that are allowed to be filled

    protected $useTimestamps = false; // Set to true if you have created_at and updated_at fields
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        'first_name' => 'required',
        'last_name' => 'required',
        'email' => 'required|valid_email',
        'phone' => 'required',
        // Add more validation rules as needed
    ];

    protected $messages = [
        'password' => [
            'validatePasswordStrength' => 'The password must contain at least one uppercase letter, one lowercase letter, one digit, and one special character.'
        ],
        'confirm_password' => [
            'matches' => 'The password and confirm password fields do not match.'
        ],
        'email' => [
            'required'=>'Email is required',
        ],
        'first_name' => [
            'required'=>'First name is required',
        ],
        'last_name' => [
            'required'=>'Last name is required',
        ]
    ];

    protected $validationMessages = [];
    protected $skipValidation     = false;

    /**
     * Get a user by email.
     *
     * @param string $email
     * @return array
     */
    public function getUserByEmail($email)
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Insert a new user into the database.
     *
     * @param array $data
     * @return bool|int
     */
    public function createUser(array $data)
    {
        return $this->insert($data);
    }

    /**
     * Update user details based on the user ID.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateUser($id, array $data)
    {
        return $this->update($id, $data);
    }

    /**
     * Delete a user from the database.
     *
     * @param int $id
     * @return bool
     */
    public function deleteUser($id)
    {
        return $this->delete($id);
    }

    public function validateUser($email, $password = "")
    {
        if($password){
            return $this->where('email', $email)->where('password', sha1($password))->first();
        }else{
            return $this->where('email', $email)->first();
        }
    }

    public function getUsersCount()
    {
        $query = $this->selectCount('id', 'students_count')
            ->where('role_id !=', 1)
            ->get();

        // Get the result row
        $students = $query->getRow();

        // Access the count using the alias
        $studentsCount = $students->students_count;

        $query = $this->selectCount('id', 'instructor_count')
            ->where('is_instructor', 1)
            ->where('show_in_home_page', 1)
            ->get();

        // Get the result row
        $instructors = $query->getRow();

        // Access the count using the alias
        $instructorsCount = $instructors->instructor_count;

        // Echo the count
        return array("students" => $studentsCount, "instructors" => $instructorsCount);
    }

    function getAllInstructors()
    {
        $db = \Config\Database::connect();
        $sql = "SELECT
        users.id AS instructor_id,
        users.first_name,
        users.last_name,
        users.image,
        COUNT(enrol.id) AS num_enrollments
    FROM
        users
    JOIN
        (
            SELECT
                course.id,
                SUBSTRING_INDEX(course.user_id, ',', -1) AS user_id,
                course.multi_instructor
            FROM
                course
            WHERE
                course.multi_instructor = 1
        ) AS course_filtered ON course_filtered.user_id = users.id
    LEFT JOIN
        enrol ON enrol.course_id = course_filtered.id
    WHERE users.status = '1'
    GROUP BY
        users.id, users.first_name, users.last_name";
        // Execute the query
        $query = $db->query($sql);

        // Fetch the result rows
        return $query->getResult();
    }

    function getInstructorDetailsByCourseId($course_id){
        $db = \Config\Database::connect();
        $query = $db->query("
            SELECT 
                c.id as course_id,
                u.first_name,
                u.last_name,
                u.title as user_title,
                u.biography,
                u.skills,
                u.image,
                rc.number_of_students_enrolled,
                rc.number_of_ratings,
                rc.one_rating_count,
                rc.two_rating_count,
                rc.three_rating_count,
                rc.four_rating_count,
                rc.five_rating_count,
                COUNT(e.id) as enrol_count
            FROM course c
            LEFT JOIN users u ON u.id = CASE WHEN LOCATE(',', c.user_id) > 0 THEN SUBSTRING_INDEX(c.user_id, ',', -1) ELSE c.user_id END
            LEFT JOIN ratings_count rc ON rc.id = c.id
            LEFT JOIN enrol e ON e.course_id = c.id
            WHERE c.id = ?
            GROUP BY c.id, u.id, rc.id
        ", [$course_id]);

        return $query->getRowArray();
    }
}
