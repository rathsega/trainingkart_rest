<?php

namespace App\Models;

use CodeIgniter\Model;

class JobModel extends Model
{
    protected $table = 'jobs';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'title', 'description', 'company_name', 'employment_type', 'location', 'pay_scale',
        'experience', 'qualification', 'required_skills', 'work_mode', 'industry', 'created_at', 'updated_at'
    ];
    protected $useTimestamps = true;

    /*function getPaginatedJobs(int $page, int $perPage = 10)
    {
        $offset = ($page - 1) ? ($page - 1) * $perPage : 0; // Calculate offset
        return $this->asArray() // Ensure we're returning an array
            ->orderBy('created_at', 'DESC')
            ->limit($perPage, $offset) // Apply limit and offset
            ->findAll(); // Fetch the data
    }*/

    public function getPaginatedJobs(int $page = 1, int $perPage = 10, array $filters = [])
    {
        $offset = ($page - 1) * $perPage;

        $builder = $this->asArray()
            ->orderBy('created_at', 'DESC')
            ->limit($perPage, $offset);

        if (isset($filters['work_mode'])) {
            $builder->whereIn('work_mode', explode(',', $filters['work_mode']));
        }

        if (isset($filters['experience'])) {
            $builder->groupStart();
            foreach (explode(',', $filters['experience']) as $experience) {
                $builder->orWhere("FIND_IN_SET('{$experience}', experience) > 0");
            }
            $builder->groupEnd();
        }

        if (isset($filters['location'])) {
            $builder->whereIn('location', explode(',', $filters['location']));
        }

        if (isset($filters['pay_scale'])) {
            $builder->groupStart();
            foreach (explode(',', $filters['pay_scale']) as $payScale) {
                $builder->orWhere("FIND_IN_SET('{$payScale}', pay_scale) > 0");
            }
            $builder->groupEnd();
        }

        return $builder->findAll();
    }

    // Method to get unique work modes
    public function getUniqueWorkModes()
    {
        return $this->select('work_mode, count(id) as count')->groupBy('work_mode')->findAll();
    }

    // Method to get unique experiences
    public function getUniqueExperiences()
    {
        $query = $this->select('
            MIN(min_experience) as min_experience,
            MAX(max_experience) as max_experience,
        ')->get();

        return $query->getRowArray();
    }

    // Method to get unique locations
    public function getUniqueLocations()
    {
        return $this->select('location, count(id) as count')->groupBy('location')->findAll();
    }

    // Method to get unique pay scales
    public function getUniquePayScales()
    {
        $query = $this->select('
            MIN(min_pay_scale) as min_pay_scale,
            MAX(max_pay_scale) as max_pay_scale
        ')->get();

        return $query->getRowArray();
    }

    public function getJobsCount(){
        $query = $this->selectCount('id', 'job_count')
            ->get();

        // Get the result row
        $row = $query->getRow();

        // Access the count using the alias
        return $row->job_count;
    }
}
