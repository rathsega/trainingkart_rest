<?php

namespace App\Controllers;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;

class Register extends ResourceController
{
    use ResponseTrait;

    public function register()
    {
        $userModel = model('App\Models\UserModel');

        // Get JSON request data
        $json = $this->request->getJSON();

        if (!$this->validate($userModel->validationRules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        //check is user alredy exists
        $users_existed = $this->checkUserExists($json->email);
        if($users_existed){
            return $this->fail('User already exists.', 409); // 409 Conflict
        }

        //

        // Handle file upload for resume
        $resume = $this->request->getFile('resume');
        if ($resume && $resume->isValid() && !$resume->hasMoved()) {
            $newName = $resume->getRandomName();
            $resume->move('./uploads', $newName);
            $data['resume'] = $newName;
        }

        $hashedPassword = sha1($json->password);

        // Prepare data for insertion
        $data = [
            'first_name' => $json->first_name,
            'last_name' => $json->last_name,
            'email' => $json->email,
            'phone' => $json->phone,
            'password' => $hashedPassword,
            'social_links' => json_encode(array('facebook'=>"", "twitter"=>"","linkedin"=>$json->linkedin)),
            'is_instructor' =>  isset($json->instructor) && $json->instructor ? 1 : 0,
            'role_id' => 2,
            'status'=>1,
            'resume'=>basename($json->resume_file),
            'skills'=> json_encode([]),
            'payment_keys'=>json_encode([])
        ];

        // Insert user data into the database
        $inserted = $userModel->insert($data);
        // Return success response
        if($inserted){
            return $this->respondCreated(['message' => 'User registered successfully']);
        }else{
            return $this->fail('Failed to create user.', 500);
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
