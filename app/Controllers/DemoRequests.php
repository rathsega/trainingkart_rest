<?php

namespace App\Controllers;
// use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;

class DemoRequests extends ResourceController
{
    // use ResponseTrait;

    public function requestDemo()
    {
        $demoRequestsModel = model('App\Models\DemoRequestsModel');

        // Get JSON request data
        $json = $this->request->getJSON();

        // Prepare data for insertion
        $data = [
            'name' => $json->name,
            'email' => $json->email,
            'phone' => $json->phone,
            'course' => $json->course,
            'date' => time()
        ];

        // Insert user data into the database
        $inserted = $demoRequestsModel->insertDemoRequest($data);
        // Return success response
        if($inserted){
            return $this->respondCreated(['message' => 'Thanks for submission.']);
        }else{
            return $this->fail('Failed to submit details.', 500);
        }
    }

    public function checkUserExists($email)
    {
        $usersModel = model('App\Models\UserModel');

        $user = $usersModel->where('email', $email)->first();

        if ($user) {
            // User exists
            return true;
        } else {
            // User does not exist
            return false;
        }
    }
}
