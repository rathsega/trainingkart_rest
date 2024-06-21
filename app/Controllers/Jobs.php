<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\JobModel;
use App\Models\AppliedJobModel;

class Jobs extends ResourceController
{
    protected $modelName = 'App\Models\JobModel';
    protected $format    = 'json';

    public function index()
    {
        $jobs = $this->model->findAll();
        return $this->respond($jobs);
    }

    public function getPaginatedJobs($page = 1, $limit = 10)
    {
        /*$jobs = $this->model->getPaginatedJobs($page, $limit);
        return $this->respond($jobs);*/
        $jobModel = new JobModel();

        // Get filters from the request
        $filters = [
            'work_mode' => $this->request->getGet('work_mode'),
            'experience' => $this->request->getGet('experience'),
            'location' => $this->request->getGet('location'),
            'pay_scale' => $this->request->getGet('pay_scale'),
        ];

        // Remove empty filters
        $filters = array_filter($filters, function ($value) {
            return $value !== null && $value !== '';
        });

        $jobs = $jobModel->getPaginatedJobs($page, $limit, $filters);
        return $this->respond($jobs);
    }

    public function show($id = null)
    {
        $job = $this->model->find($id);
        if (!$job) {
            return $this->failNotFound('Job not found');
        }
        return $this->respond($job);
    }

    public function create()
    {
        $data = $this->request->getPost();
        if (!$this->model->insert($data)) {
            return $this->failValidationErrors($this->model->validation->listErrors());
        }
        $job = $this->model->find($this->model->insertID());
        return $this->respondCreated($job);
    }

    public function update($id = null)
    {
        $data = $this->request->getRawInput();
        $job = $this->model->find($id);
        if (!$job) {
            return $this->failNotFound('Job not found');
        }
        $this->model->update($id, $data);
        $job = $this->model->find($id);
        return $this->respond($job);
    }

    public function delete($id = null)
    {
        $job = $this->model->find($id);
        if (!$job) {
            return $this->failNotFound('Job not found');
        }
        $this->model->delete($id);
        return $this->respondDeleted(['id' => $id]);
    }

    public function apply()
    {
        $input = $this->request->getJSON();
        $data = [
            'name' => $input->apply_job_name,
            'email' => $input->apply_job_email,
            'phone' => $input->apply_job_phone,
            'resume' => basename($input->resume_file),
            'job_id' => $input->job_id
        ];

        $model = new AppliedJobModel();
        $model->insert($data);
        return $this->respond(array("message" => "Application Submitted Successfully."), 200);
    }

    public function getFilterData()
    {
        $jobModel = new JobModel();

        $workModes = $jobModel->getUniqueWorkModes();
        $experiences = $jobModel->getUniqueExperiences();
        $locations = $jobModel->getUniqueLocations();
        $payScales = $jobModel->getUniquePayScales();

        return $this->respond([
            'work_modes' => $workModes,
            'experiences' => $experiences,
            'locations' => $locations,
            'pay_scales' => $payScales
        ]);
    }

    function getJobsCount(){
        $jobsModel = model('App\Models\JobModel');

        $data =  $jobsModel->getJobsCount();
        return $this->response->setJSON(array("count"=>$data));
    }

    function getJobsByUsingFilters(){

        $jobModel = new JobModel();
        $input = $this->request->getJSON();

        // Get filter parameters from the request
        $work_mode = (String)$input->work_mode;
        $experience = (String)$input->experience;
        $location = (String)$input->location;
        $pay_scale = (String)$input->pay_scale;
        $perPage = $input->limit;
        $page = $input->page;

        // Initialize the query builder
        $query = $jobModel->asArray();

        // Apply filters
        if ($work_mode) {
            $work_modes = explode(',', $work_mode);
            $query->whereIn('work_mode', $work_modes);
        }

        if ($experience) {
            $experience_ranges = explode(',', $experience);
            $query->groupStart();
            foreach ($experience_ranges as $range) {
                list($min_exp, $max_exp) = explode('-', $range);
                $query->orGroupStart()
                      ->where('min_experience <=', $max_exp)
                      ->where('max_experience >=', $min_exp)
                      ->groupEnd();
            }
            $query->groupEnd();
        }

        if ($location) {
            $locations = explode(',', $location);
            $query->whereIn('location', $locations);
        }

        if ($pay_scale) {
            $pay_scale_ranges = explode(',', $pay_scale);
            $query->groupStart();
            foreach ($pay_scale_ranges as $range) {
                list($min_pay, $max_pay) = explode('-', $range);
                $query->orGroupStart()
                      ->where('min_pay_scale <=', $max_pay)
                      ->where('max_pay_scale >=', $min_pay)
                      ->groupEnd();
            }
            $query->groupEnd();
        }

        // Pagination
        $offset = ($page - 1) * $perPage;
        $jobs = $query->orderBy('created_at', 'DESC')
                      ->limit($perPage, $offset)
                      ->findAll();

        return $this->respond($jobs);
    }
}
