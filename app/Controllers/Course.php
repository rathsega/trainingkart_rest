<?php

namespace App\Controllers;

//use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;

use App\Models\CourseModel;
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

        // Fetch the course by slug
        $course = $courseModel->getCourseDetailsBySlug($slug);
        if (!$course) {
            return $this->response->setStatusCode(404, 'Course not found');
        }
        $course = (array)$course;



        if ($course["instructor_image"] && file_exists(WRITEPATH . 'uploads/avatar/' . $course["instructor_image"])) {
            $mimeType = mime_content_type(WRITEPATH . 'uploads/avatar/' . $course["instructor_image"]);
            $course['instructor_image'] = base64_encode(file_get_contents(WRITEPATH . 'uploads/avatar/' . $course["instructor_image"]));
            $course['mime_type'] = $mimeType;
        } else {
            $mimeType = mime_content_type(WRITEPATH . 'uploads/avatar/instructor_profile_pic.webp');
            $course['instructor_image'] = base64_encode(file_get_contents(WRITEPATH . 'uploads/avatar/instructor_profile_pic.webp'));
            $course['mime_type'] = $mimeType;
        }



        //Thumbnail
        $course["thumbnail"] = $this->get_course_thumbnail_url($course["course_id"], "course_thumbnail", $course["last_modified"]);

        // Return course details
        return $this->respond($course, 200);
    }

    public function getCourseDetailsOfRequiredData($course_id="", $element="")
    {
        $courseModel = new CourseModel();
        $course = $courseModel->getRequiredData($course_id, $element);
        // Return course details
        return $this->respond($course, 200);
        if ($element == 'career_growth') {
            //Growth
            $companyModel = new CompanyModel();
            $course = $courseModel->getRequiredData($course_id, $element);
            $career_growth = json_decode($course->career_growth);
            foreach ($career_growth as $key => $growth) {
                $growth->company = $growth->company ? $growth->company : [];
                if ($growth->company) {
                    $company_details = $companyModel->getCompaniesByIds($growth->company);
                    $career_growth[$key]->company = $company_details;
                } else {
                    $career_growth[$key]->company = [];
                }
            }
            $course->career_growth = json_encode($career_growth);
            // Return course details
            return $this->respond($course, 200);
        }else {
            //Growth
            $course = $courseModel->getRequiredData($course_id, $element);
            // Return course details
            return $this->respond($course, 200);
        }
    }

    public function getCourseCurriculum($course_id)
    {
        $courseModel = new CourseModel();
        //get course curriculum
        $course_curriculum_result = $courseModel->getCourseCurriculum($course_id);
        // Initialize the result array
        $course_structure = [];

        // Process each row
        foreach ($course_curriculum_result as $row) {
            // Add section if not already present
            if (!isset($course_structure[$row['section_id']])) {
                $course_structure[$row['section_id']] = [
                    'section_id' => $row['section_id'],
                    'title' => $row['section_title'],
                    'order' => $row['section_order'],
                    'chapters' => [],  // To hold chapters if they exist
                    'lessons' => [],   // To hold lessons if no chapters exist
                    'start_date' => $row['start_date'],
                    'end_date' => $row['end_date']
                ];
            }

            // If there's a chapter, add chapter under section
            if (!empty($row['chapter_id'])) {
                if (!isset($course_structure[$row['section_id']]['chapters'][$row['chapter_id']])) {
                    $course_structure[$row['section_id']]['chapters'][$row['chapter_id']] = [
                        'chapter_id' => $row['chapter_id'],
                        'title' => $row['chapter_title'],
                        'order' => $row['chapter_order'],
                        'lessons' => [] // Lessons under this chapter
                    ];
                }

                // Add lesson to chapter if it's linked to a chapter
                if (!empty($row['lesson_id']) && $row['lesson_chapter_id'] == $row['chapter_id']) {
                    $course_structure[$row['section_id']]['chapters'][$row['chapter_id']]['lessons'][] = [
                        'lesson_id' => $row['lesson_id'],
                        'title' => $row['lesson_title'],
                        'duration' => $row['duration'],
                        'video_type' => $row['video_type'],
                        'video_url' => $row['video_url']
                    ];
                }
            }

            // If the lesson belongs directly to a section (no chapter), add it under 'lessons' at the section level
            if (!empty($row['lesson_id']) && empty($row['chapter_id'])) {
                $course_structure[$row['section_id']]['lessons'][] = [
                    'lesson_id' => $row['lesson_id'],
                    'title' => $row['lesson_title'],
                    'duration' => $row['duration'],
                    'video_type' => $row['video_type'],
                    'video_url' => $row['video_url']
                ];
            }
        }

        $course['curriculum'] = $course_structure;
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
                'thumbnail' => $this->get_course_thumbnail_url($row->course_id, "course_thumbnail", $row->last_modified ),
                'slug' => $row->slug,

                'rating_count' => [
                    'average_ratings' => $this->getAverageRating($row),
                    'number_of_ratings' => $row->number_of_ratings
                ],
                'instructor' => [
                    'first_name' => isset($row->instructor_first_name) ? $row->instructor_first_name : null,
                    'last_name' => isset($row->instructor_last_name) ? $row->instructor_last_name : null,
                    'image' => isset($row->instructor_image) ? $row->instructor_image : null,
                ],
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

            if (count($courses) > 0) {
                $result[$category['name']] = $courses;
            }
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
        if (count($topCourse) > 0) {
            $result['Top Courses'] = $topCourse;
        }
        if (count($top10Courses) > 0) {
            $result['Trending Courses'] = $top10Courses;
        }

        return $this->respond($result);
    }

    public function getRelatedCourses($id, $limit, $offset){
        $courseModel = new CourseModel();
        $results = $courseModel->getRelatedCourses($id, $limit, $offset);
        $data = $this->composeCourseModel($results);
        return $this->respond($data);
    }
}
