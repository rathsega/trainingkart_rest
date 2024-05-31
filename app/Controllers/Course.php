<?php

namespace App\Controllers;

//use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;

use App\Models\CourseModel;
use App\Models\SectionModel;
use App\Models\ChapterModel;
use App\Models\LessonModel;
use App\Models\EnrolModel;
//use App\Models\UserModel;

class Course extends ResourceController
{
    //use ResponseTrait;

    public function getCourseDetails($slug_1, $slug_2 = "", $slug_3 = "", $slug_4 = "")
    {
        // return $this->response->setStatusCode(404, 'Course not found');
        if($slug_1 && $slug_2 && $slug_3 && $slug_4){
            $slug = $slug_3 ."/".$slug_4;
        }else if($slug_1 && $slug_2 && $slug_3){
            $slug = $slug_3;
        }else if($slug_1 && $slug_2){
            $slug = $slug_1 . "/". $slug_2;
        }else if($slug_1){
            $slug = $slug_1;
        }

        $courseModel = new CourseModel();
        $sectionModel = new SectionModel();
        $chapterModel = new ChapterModel();
        $lessonModel = new LessonModel();
        $enrolModel = new EnrolModel();
        //$userModel = new UserModel();


        // Fetch the course by slug
        $course = $courseModel->getCourseBySlug($slug);
        if (!$course) {
            return $this->response->setStatusCode(404, 'Course not found');
        }
        $course = (array)$course;
        // Fetch sections related to the course
        $sections = $sectionModel->where('course_id', $course['id'])->orderBy('order', 'ASC')->findAll();
        foreach ($sections as &$section) {
            // Fetch chapters related to the section
            $chapters = $chapterModel->where('section_id', $section['id'])->orderBy('order', 'ASC')->findAll();
            foreach ($chapters as &$chapter) {
                // Fetch lessons related to the chapter
                $lessons = $lessonModel->where('chapter_id', $chapter['id'])->orderBy('order', 'ASC')->findAll();
                $chapter['lessons'] = $lessons;
            }
            // Fetch lessons related to the section directly (without chapter)
            $sectionLessons = $lessonModel->where('section_id', $section['id'])->where('chapter_id', null)->orderBy('order', 'ASC')->findAll();
            $section['chapters'] = $chapters;
            $section['lessons'] = $sectionLessons;
        }

        $course['sections'] = $sections;
        //$course["number_of_students_enrolled"] = $enrolModel->getEnrolmentCountByCourse($course['id'])->count;

        if($course["instructor_image"] && file_exists(WRITEPATH . 'uploads/avatar/' . $course["instructor_image"])){
            $mimeType = mime_content_type(WRITEPATH . 'uploads/avatar/' . $course["instructor_image"]);
            $course['instructor_image'] = base64_encode(file_get_contents(WRITEPATH . 'uploads/avatar/' . $course["instructor_image"]));
            $course['mime_type'] = $mimeType;
        }else{
            $mimeType = mime_content_type(WRITEPATH . 'uploads/avatar/instructor_profile_pic.webp');
            $course['instructor_image'] = base64_encode(file_get_contents(WRITEPATH . 'uploads/avatar/instructor_profile_pic.webp'));
            $course['mime_type'] = $mimeType;
        }

        //Thumbnail

        //Fetch instructor details
        /*$instructor = $userModel->getInstructorDetailsByCourseId($course["id"]);
        $course['instructor_details'] = [
            'instructor' => [
                'first_name' => $instructor['first_name'],
                'last_name' => $instructor['last_name'],
                'title' => $instructor['user_title'],
                'biography' => $instructor['biography'],
                'skills' => $instructor['skills'],
                'image' => $instructor['image'],
            ],
            'ratings' => [
                'number_of_students_enrolled' => $instructor['number_of_students_enrolled'],
                'number_of_ratings' => $instructor['number_of_ratings'],
                'ratings' => [
                    'one_rating_count' => $instructor['one_rating_count'],
                    'two_rating_count' => $instructor['two_rating_count'],
                    'three_rating_count' => $instructor['three_rating_count'],
                    'four_rating_count' => $instructor['four_rating_count'],
                    'five_rating_count' => $instructor['five_rating_count'],
                ],
                'enrol_count' => $instructor['enrol_count'],
            ],
        ];*/
        
        // Return course details
        return $this->respond($course, 200);
    }

    public function composeCourseModel($results)
    {
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
                'thumbnail' => $row->thumbnail,
                'rating_count' => [
                    'average_ratings' => $this->getAverageRating($row),
                    'number_of_ratings' => $row->number_of_ratings
                ],
                'expiry_date' => $row->expiry_date
            ];
            $courses[] = $courseDetails;
        }

        // Output the course details
        return $courses;
    }

    private function getAverageRating($row)
    {
        return $row->number_of_ratings ? ($row->one_rating_count * 1 +  $row->two_rating_count * 2 + $row->three_rating_count * 3 +  $row->four_rating_count * 4 +  $row->five_rating_count * 5) / $row->number_of_ratings : 0;
    }

    public function getHomePageCourses(){
        $courseModel = new CourseModel();
        $results = $courseModel->getHomePageCourses();
        return $this->respond($results, 200);
    }
}
