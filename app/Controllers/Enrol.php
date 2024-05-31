<?php

namespace App\Controllers;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;

class Enrol extends ResourceController
{
    use ResponseTrait;

    public function getEnrolments(){
        $enrolModel = model('App\Models\EnrolModel');
        $results = $enrolModel->getAllEnrolmentsByUserId($this->request->user_id);
        $enrols = $this->composeCourseModel($results);
        return $this->respond($enrols, 200);
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

    private function getAverageRating($row){
        return $row->number_of_ratings ? ($row->one_rating_count * 1 +  $row->two_rating_count * 2 + $row->three_rating_count * 3 +  $row->four_rating_count * 4 +  $row->five_rating_count * 5)/ $row->number_of_ratings : 0;
    }
}
