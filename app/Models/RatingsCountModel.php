<?php

namespace App\Models;

use CodeIgniter\Model;

class RatingsCountModel extends Model
{
    protected $table = 'ratings_count'; // Database table name
    protected $primaryKey = 'id'; // Primary key of the table

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['number_of_students_enrolled', 'number_of_ratings', 'one_rating_count', 'two_rating_count', 'three_rating_count', 'four_rating_count', 'five_rating_count']; // Fields that are allowed to be filled

    protected $useTimestamps = false; // Set to true if you have created_at and updated_at fields


    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;

    public function getRatingsCount()
    {
        // Get the total number of students
        $totalStudentsQuery = $this->selectSum('number_of_students_enrolled')->get();
        $totalStudentsRow = $totalStudentsQuery->getRow();
        $totalStudents = $totalStudentsRow->number_of_students_enrolled;

        //Get the total number of ratings
        $totalRatingsQuery = $this->selectSum('number_of_ratings')->get();
        $totalRatingsRow = $totalRatingsQuery->getRow();
        $totalRatings = $totalRatingsRow->number_of_ratings;

        // Build the query to calculate the average of the weighted ratings
        $averageQuery = $this->select('SUM(one_rating_count * 1 + two_rating_count * 2 + three_rating_count * 3 + four_rating_count * 4 + five_rating_count * 5) / SUM(number_of_ratings) AS weighted_average')
            ->get();

        // Get the result row
        $averageRow = $averageQuery->getRow();

        // Access the average of the weighted ratings
        $weightedAverage = $averageRow->weighted_average;

        return array("students_count"=> $totalStudents, "ratings_count"=>$totalRatings, "average_ratings"=> round($weightedAverage, 1));
    }
}
