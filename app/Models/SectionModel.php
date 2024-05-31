<?php

namespace App\Models;

use CodeIgniter\Model;

class SectionModel extends Model
{
    protected $table = 'section';
    protected $primaryKey = 'id';
    protected $allowedFields = ['title', 'course_id', 'order', 'start_date', 'end_date', 'restricted_by'];
}
