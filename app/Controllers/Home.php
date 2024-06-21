<?php

namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;

class Home extends ResourceController
{

    protected $modelName = 'App\Models\UserModel';
    protected $format    = 'json';


    public function index1(): string
    {
        return view('welcome_message');
    }
    public function index()
    {
        return $this->respond($this->model->findAll(), 200);
    }

    public function getUsersCount(){
        $userModel = model('App\Models\UserModel');

        $data =  $userModel->getUsersCount();
        return $this->response->setJSON(array("count"=>$data));
    }

    public function getRatingsCount(){
        $ratingsCountModel = model('App\Models\RatingsCountModel');

        $data =  $ratingsCountModel->getRatingsCount();
        return $this->response->setJSON(array("count"=>$data));
    }

    public function getCourseCount(){
        $courseModel = model('App\Models\CourseModel');

        $data =  $courseModel->getCourseCount();
        return $this->response->setJSON(array("count"=>$data));
    }

    public function getCategories(){
        $categoryModel = model('App\Models\CategoryModel');

        $data =  $categoryModel->getCategories();
        return $this->response->setJSON(array("data"=>$data));
    }

    public function getCoursesList($type){
        $courseModel = model('App\Models\CourseModel');
        
        if($type == 'active' || $type == "upcoming"){
            $results =  $courseModel->getCoursesList($type);
        }else if($type == 'top_10'){
            $results =  $courseModel->getTopTenLatestCourses();
        }else if($type == 'top'){
            $results =  $courseModel->getTopCourses();
        }
        $data = $this->composeCourseModel($results);
        return $this->response->setJSON(array("data"=>$data, "type"=>$type));
    }

    public function composeCourseModel($results){
        $courses = [];
        // Process the results
        foreach ($results as $row) {
            // Construct the result object
            $courseDetails = (object) [
                'id' => $row->course_id,
                'last_modified' => $row->last_modified,
                'title' => $row->title,
                'price' => $row->price,
                'discount_flag' => $row->discount_flag,
                'discounted_price' => $row->discounted_price,
                'course_duration_in_hours' => $row->course_duration_in_hours,
                'number_of_lectures' => $row->number_of_lectures,
                'thumbnail' => $course["thumbnail"] = $this->get_course_thumbnail_url($row->course_id, "course_thumbnail", $row->last_modified ),
                'slug' => $row->slug,
                'instructor' => [
                    'first_name' => $row->instructor_first_name,
                    'last_name' => $row->instructor_last_name,
                    'image' => $row->instructor_image,
                ],
                'rating_count' => [
                    'average_ratings' => $this->getAverageRating($row),
                    'number_of_ratings' => $row->number_of_ratings
                ]
            ];
            $courses[] = $courseDetails;
        }

        // Output the course details
        return $courses;
    }

    private function getAverageRating($row){
        return $row->number_of_ratings ? ($row->one_rating_count * 1 +  $row->two_rating_count * 2 + $row->three_rating_count * 3 +  $row->four_rating_count * 4 +  $row->five_rating_count * 5)/ $row->number_of_ratings : 0;
    }

    public function getAllInstructors(){
        $userModel = model('App\Models\UserModel');
        $data =  $userModel->getAllInstructors();
        return $this->response->setJSON(array("data"=>$data));
    }
    public function get_course_thumbnail_url($course_id, $type, $last_modified)
    {
        // Course media placeholder is coming from the theme config file. Which has all the placehoder for different images. Choose like course type.
        $course_media_placeholders = "assets/frontend/default-new/img/course_thumbnail_placeholder.jpg";
        return 'uploads/thumbnails/course_thumbnails/optimized/' . $type . '_' . 'default-new' . '_' . $course_id.$last_modified . '.jpg';
        if (file_exists(WRITEPATH . 'uploads/thumbnails/course_thumbnails/optimized/' . $type . '_' . 'default-new' . '_' . $course_id.$last_modified . '.webp')) {
            return 'uploads/thumbnails/course_thumbnails/optimized/' . $type . '_' . 'default-new' . '_' . $course_id.$last_modified . '.webp';
        } elseif (file_exists(WRITEPATH . 'uploads/thumbnails/course_thumbnails/optimized/' . $type . '_' . 'default-new' . '_' . $course_id.$last_modified . '.jpg')) {
            return 'uploads/thumbnails/course_thumbnails/optimized/' . $type . '_' . 'default-new' . '_' . $course_id.$last_modified . '.jpg';
        } elseif(file_exists(WRITEPATH . 'uploads/thumbnails/course_thumbnails/' . $type . '_' . 'default-new' . '_' . $course_id.$last_modified . '.jpg')) {

            //resizeImage
            //resizeImage(WRITEPATH . 'uploads/thumbnails/course_thumbnails/' . $type . '_' . 'default-new' . '_' . $course_id.$last_modified . '.jpg', WRITEPATH . 'uploads/thumbnails/course_thumbnails/optimized/', 400);

            return 'uploads/thumbnails/course_thumbnails/' . $type . '_' . 'default-new' . '_' . $course_id.$last_modified . '.jpg';
        }else{
            return $course_media_placeholders;
        }
    }
}
