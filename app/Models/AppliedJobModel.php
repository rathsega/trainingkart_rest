<?php

namespace App\Models;

use CodeIgniter\Model;

class AppliedJobModel extends Model
{
    protected $table = 'applied_jobs';
    protected $primaryKey = 'id';

    protected $allowedFields = ['name', 'email', 'phone', 'resume', 'job_id', 'created_at'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
