<?php

namespace App\Models;

use CodeIgniter\Model;

class ContactModel extends Model
{
    protected $table = 'contactus'; // Set the correct table name
    protected $primaryKey = 'id'; // Set the correct primary key
    protected $allowedFields = [
        'first_name', 'last_name', 'email', 'phone',
        'message', 'course', 'city', 'datetime'
    ];
}
