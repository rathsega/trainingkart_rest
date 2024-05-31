<?php

namespace App\Controllers;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;
use App\Models\TokenModel;

class User extends ResourceController
{
    use ResponseTrait;

    public function getProfile()
    {
        $usersModel = model('App\Models\UserModel');
        $request_data = $this->request;
        $user = $usersModel->select(array("id", "first_name", "last_name", "country", "phone", "email", "skills", "social_links", "biography",  "title",  "is_instructor", "image",  "address", "resume", "education", "work_experience"))->where('email', $request_data->user)->first();
        if($user["image"] && file_exists(WRITEPATH . 'uploads/avatar/' . $user["image"])){
            $mimeType = mime_content_type(WRITEPATH . 'uploads/avatar/' . $user["image"]);
            $user['image'] = base64_encode(file_get_contents(WRITEPATH . 'uploads/avatar/' . $user["image"]));
            $user['mime_type'] = $mimeType;
        }else{
            $user["image"] = "";
            $user["mime_type"] = "";
        }
        
        return $this->respond($user, 200);
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

    public function changePassword(){
        $user_email = $this->request->user;
        $input = $this->request->getJSON();

        $currentPassword = $input->current_password ?? '';
        $newPassword = $input->new_password ?? '';
        $retypeNewPassword = $input->retype_new_password ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($retypeNewPassword)) {
            return $this->fail('All fields are required.');
        }

        if ($newPassword !== $retypeNewPassword) {
            return $this->fail('New password and retype new password do not match.');
        }

        $usersModel = model('App\Models\UserModel');

        $user = $usersModel->where('email', $user_email)->first();

        if (!$user) {
            return $this->failNotFound('User not found.');
        }

        if (sha1($currentPassword) !== $user['password']) {
            return $this->fail('Current password is incorrect.');
        }

        if (sha1($newPassword) === $user['password']) {
            return $this->fail('New password cannot be the same as the current password.');
        }

        $data = [
            'password' => sha1($newPassword)
        ];

        if ($usersModel->updateUser($user["id"], $data)) {
            return $this->respond(['message' => 'Password changed successfully.']);
        } else {
            return $this->fail('Failed to change password.');
        }
    }

    public function uploadAvatar(){
        $file = $this->request->getFile('file');
        if ($file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads/avatar/', $newName);

            $mimeType = mime_content_type(WRITEPATH . 'uploads/avatar/' . $newName);

            $response = [
                'status' => 200,
                'message' => 'Avatar uploaded successfully',
                'newFileName' => $newName,
                'fileName' => $file->getClientName(),
                "image" => base64_encode(file_get_contents(WRITEPATH . 'uploads/avatar/' . $newName)),
                'mime_type'=> $mimeType
            ];
            return $this->respond($response);
        }

        return $this->fail($file->getErrorString());
    }

    public function deleteAvatar()
    {
        $json = $this->request->getJSON();
        $filePath = $json->filePath ? $json->filePath : '/';
        $fileName = basename($filePath);
        $fullPath = WRITEPATH . 'uploads/avatar/' . $fileName;

        if (file_exists($fullPath)) {
            unlink($fullPath);
            return $this->respond(['status' => 200, 'message' => 'Avatar deleted successfully']);
        }

        return $this->failNotFound('File not found');
    }

    public function updateProfile(){
        $user_email = $this->request->user;
        $input = $this->request->getJSON();
        $data = [];
        $data['first_name'] = $input->first_name;
        $data['last_name'] = $input->last_name;
        $data['phone'] = $input->phone;
        $data['education'] = $input->education;
        $data['work_experience'] = $input->work_experience;
        $data['skills'] = $input->skills;
        $data['biography'] = $input->biography;
        $data['social_links'] = json_encode(array("linkedin"=>$input->linkedin));
        if($input->avatar_file_name){
            $data["image"] = $input->avatar_file_name;
        }

        $usersModel = model('App\Models\UserModel');
        $user = $usersModel->where('email', $user_email)->first();
        if ($usersModel->updateUser($user["id"], $data)) {
            return $this->respond(['message' => 'Profile updated successfully.']);
        } else {
            return $this->fail('Failed to update profile.');
        }
    }
}
