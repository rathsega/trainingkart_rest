<?php

namespace App\Controllers;

//use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;

use App\Models\CourseModel;
use App\Models\SectionModel;
use App\Models\ChapterModel;
use App\Models\LessonModel;
use App\Models\EnrolModel;
use App\Models\CompanyModel;
use App\Models\CategoryModel;
//use App\Models\UserModel;

class Course extends ResourceController
{
    //use ResponseTrait;

    public function getCourseDetails($slug_1, $slug_2 = "", $slug_3 = "", $slug_4 = "")
    {
        // return $this->response->setStatusCode(404, 'Course not found');
        if ($slug_1 && $slug_2 && $slug_3 && $slug_4) {
            $slug = $slug_3 . "/" . $slug_4;
        } else if ($slug_1 && $slug_2 && $slug_3) {
            $slug = $slug_3;
        } else if ($slug_1 && $slug_2) {
            $slug = $slug_1 . "/" . $slug_2;
        } else if ($slug_1) {
            $slug = $slug_1;
        }

        $courseModel = new CourseModel();
        $sectionModel = new SectionModel();
        $chapterModel = new ChapterModel();
        $lessonModel = new LessonModel();
        $enrolModel = new EnrolModel();
        $companyModel = new CompanyModel();
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

        if ($course["instructor_image"] && file_exists(WRITEPATH . 'uploads/avatar/' . $course["instructor_image"])) {
            $mimeType = mime_content_type(WRITEPATH . 'uploads/avatar/' . $course["instructor_image"]);
            $course['instructor_image'] = base64_encode(file_get_contents(WRITEPATH . 'uploads/avatar/' . $course["instructor_image"]));
            $course['mime_type'] = $mimeType;
        } else {
            $mimeType = mime_content_type(WRITEPATH . 'uploads/avatar/instructor_profile_pic.webp');
            $course['instructor_image'] = base64_encode(file_get_contents(WRITEPATH . 'uploads/avatar/instructor_profile_pic.webp'));
            $course['mime_type'] = $mimeType;
        }

        //Growth
        $career_growth = json_decode($course["career_growth"]);
        foreach ($career_growth as $key => $growth) {
            $growth->company = $growth->company ? $growth->company : [];
            if ($growth->company) {
                $company_details = $companyModel->getCompaniesByIds($growth->company);
                $career_growth[$key]->company = $company_details;
            } else {
                $career_growth[$key]->company = [];
            }
        }
        $course["career_growth"] = json_encode($career_growth);

        //Thumbnail
        $course["thumbnail"] = $this->get_course_thumbnail_url($course["course_id"], "course_thumbnail", $course["last_modified"]);
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

    public function getHomePageCourses()
    {
        $courseModel = new CourseModel();
        $results = $courseModel->getHomePageCourses();
        return $this->respond($results, 200);
    }

    public function getCourseById()
    {
        $courseModel = new CourseModel();
        $request = $this->request->getJSON();
        $results = $courseModel->getCourseById($request->courseIds);
        return $this->respond($results, 200);
    }

    public function getAllCourses()
    {

        // Get JSON request data
        $request = $this->request->getJSON();

        $selected_category_id = [];
        $selected_price = "all";
        $selected_level = "";
        $selected_sort_by = "desc";
        $selected_order_by = "id";
        $selected_offset = "0";
        $selected_limit = "10";

        $search_string = "";

        // Get the category ids
        if (isset($request?->category) & count($request->category) > 0) {
            $selected_category_id = $request->category;
        }

        if (isset($request?->price)) {
            $selected_price = $request->price;
        }

        if (isset($request?->level)) {
            $selected_level = $request->level;
        }

        if (isset($request->title)) {
            $search_string = $request->title;
        }

        if (isset($request?->sort_by)) {
            $selected_sort_by = $request->sort_by;
        }

        if (isset($request?->page)) {
            $selected_offset = ($request->page - 1) * 10;
        }

        if (isset($request?->limit)) {
            $selected_limit = $request->limit;
        }

        $courseModel = new CourseModel();
        $results = $courseModel->getAllCourses($search_string, $selected_category_id, $selected_price, $selected_level, $selected_sort_by, $selected_limit, $selected_offset);
        $data = $this->composeCourseModel($results["data"]);
        // return $this->response->setJSON(array("data"=>$data, "type"=>$type));
        return $this->respond(array("data" => $data, "count" => $results["total_count"]), 200);
        // return $this->respond($courseModel->getLastQuery()->getQuery(), 200);

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
                'slug' => $row->slug,

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

    private function getAverageRating($row)
    {
        return $row->number_of_ratings ? ($row->one_rating_count * 1 +  $row->two_rating_count * 2 + $row->three_rating_count * 3 +  $row->four_rating_count * 4 +  $row->five_rating_count * 5) / $row->number_of_ratings : 0;
    }

    public function get_course_thumbnail_url($course_id, $type, $last_modified)
    {
        // Course media placeholder is coming from the theme config file. Which has all the placehoder for different images. Choose like course type.
        $course_media_placeholders = "assets/frontend/default-new/img/course_thumbnail_placeholder.jpg";
        return 'uploads/thumbnails/course_thumbnails/optimized/' . $type . '_' . 'default-new' . '_' . $course_id . $last_modified . '.jpg';
        if (file_exists(WRITEPATH . 'uploads/thumbnails/course_thumbnails/optimized/' . $type . '_' . 'default-new' . '_' . $course_id . $last_modified . '.webp')) {
            return 'uploads/thumbnails/course_thumbnails/optimized/' . $type . '_' . 'default-new' . '_' . $course_id . $last_modified . '.webp';
        } elseif (file_exists(WRITEPATH . 'uploads/thumbnails/course_thumbnails/optimized/' . $type . '_' . 'default-new' . '_' . $course_id . $last_modified . '.jpg')) {
            return 'uploads/thumbnails/course_thumbnails/optimized/' . $type . '_' . 'default-new' . '_' . $course_id . $last_modified . '.jpg';
        } elseif (file_exists(WRITEPATH . 'uploads/thumbnails/course_thumbnails/' . $type . '_' . 'default-new' . '_' . $course_id . $last_modified . '.jpg')) {

            //resizeImage
            //resizeImage(WRITEPATH . 'uploads/thumbnails/course_thumbnails/' . $type . '_' . 'default-new' . '_' . $course_id.$last_modified . '.jpg', WRITEPATH . 'uploads/thumbnails/course_thumbnails/optimized/', 400);

            return 'uploads/thumbnails/course_thumbnails/' . $type . '_' . 'default-new' . '_' . $course_id . $last_modified . '.jpg';
        } else {
            return $course_media_placeholders;
        }
    }

    public function getFooterCourses()
    {
        $courseModel = new CourseModel();
        $categoryModel = new CategoryModel();

        // Fetch all categories
        $categories = $categoryModel->findAll();

        $result = [];

        foreach ($categories as $category) {
            // Fetch courses by category
            $courses = $courseModel->select('id, title, slug, category_slug, sub_category_slug')->where('category_id', $category['id'])
                ->where('status', 'active')
                ->groupStart()
                ->orWhere('is_top_course', 1)
                ->orWhere('is_top10_course', 1)
                ->orWhere('show_it_in_category', 1)
                ->groupEnd()
                ->findAll();


            $result[$category['name']] = $courses;
        }

        $topCourse = $courseModel->select('id, title, slug, category_slug, sub_category_slug')
            ->where('status', 'active')
            ->where('is_top_course', 1)
            ->first();

        // Get top 10 courses
        $top10Courses = $courseModel->select('id, title, slug, category_slug, sub_category_slug')
            ->where('status', 'active')
            ->where('is_top10_course', 1)
            ->findAll(10);
        $result['Top Courses'] = $topCourse;
        $result['Top 10 Courses'] = $top10Courses;

        return $this->respond($result);
    }
}
