<?php

// app/Controllers/AuthController.php
namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\TokenModel;
use App\Models\PasswordResetModel;
use CodeIgniter\I18n\Time;

class Auth extends ResourceController
{
    use ResponseTrait;

    public function authenticate()
    {
        $json = $this->request->getJSON();
        $email = $json->email;
        $password = $json->password;
        if (trim($password) == "" || trim($email) == "") {
            return $this->failUnauthorized('Email and Password are required');
        }

        // Your authentication logic here...
        $data = $this->validateUser($email, $password);
        if ($data) {

            // Invalidate any existing tokens for this user
            $tokenModel = new TokenModel();
            $existingToken = $tokenModel->where('user_id', $data['id'])
                ->where('expiry_date >', date('Y-m-d H:i:s'))
                ->orderBy('expiry_date', 'DESC')
                ->first();

            if ($existingToken) {
                // Update expiry date of existing token
                $tokenModel->update($existingToken['id'], [
                    'expiry_date' => date('Y-m-d H:i:s', strtotime('-1 minutes'))
                ]);
                $token = $existingToken['token'];
            }

            // Generate JWT token
            $token = $this->generateJWTToken($email, $data['id']);

            // Insert token into database
            $tokenModel = new TokenModel();
            $tokenModel->insert([
                'user_id' => $data['id'], // Assuming you have a user ID
                'token' => $token,
                'expiry_date' => date('Y-m-d H:i:s', strtotime('+30 minutes'))
            ]);

            // Return token
            return $this->respondCreated(['token' => $token, 'user' => [
                'id' => $data['id'],
                'email' => $data['email'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
            ]]);
        } else {
            return $this->failUnauthorized('Invalid email or password');
        }
    }

    private function generateJWTToken($email, $user_id)
    {
        $key = jwt_secret; // Change this to your secret key
        $payload = [
            'email' => $email,
            'user_id' => $user_id,
            'exp' => strtotime('+30 minutes')
        ];
        return JWT::encode($payload, $key, 'HS256');
    }

    public function validateUser($email, $password = "")
    {
        $userModel = model('App\Models\UserModel');
        $data =  $userModel->validateUser($email, $password);
        return $data;
    }

    public function validateToken()
    {
        $key = jwt_secret; // Change this to your secret key
        $authHeader = $this->request->getHeaderLine('Authorization');

        if (!$authHeader) {
            return $this->failUnauthorized('Missing Authorization Header');
        }

        $token = $authHeader;
        try {
            //$decoded = JWT::decode($token, new Key($key, 'HS256'));

            // Get token from database
            $tokenModel = new TokenModel();
            $dbToken = $tokenModel->where('token', $token)->first();
            if (!$dbToken) {
                return $this->failUnauthorized('Session has expired');
            }

            // Check if token is expired
            $currentDate = time();
            $expiryDate = strtotime($dbToken['expiry_date']);
            if ($currentDate > $expiryDate) {
                return $this->failUnauthorized('Session has expired');
            }

            // Update expiry date to next 30 minutes
            $newExpiry = $currentDate + (30 * 60);
            $tokenModel->update($dbToken['id'], ['expiry_date' => date('Y-m-d H:i:s', $newExpiry)]);

            return $this->respond(['message' => 'Token is valid']);
        } catch (\Exception $e) {
            return $this->failUnauthorized('Invalid token: ' . $e->getMessage());
        }
    }

    public function logout()
    {
        $header = $this->request->getHeaderLine("Authorization");
        $token = null;

        if (!empty($header)) {
            $token = $header;
        }

        if (!$token) {
            return $this->respond(['message' => 'No token provided.'], 401);
        }

        // Invalidate any existing tokens for this user
        $tokenModel = new TokenModel();
        $existingToken = $tokenModel->where('token', $token)
            ->where('expiry_date >', date('Y-m-d H:i:s'))
            ->orderBy('expiry_date', 'DESC')
            ->first();

        if ($existingToken) {
            // Update expiry date of existing token
            $tokenModel->update($existingToken['id'], [
                'expiry_date' => date('Y-m-d H:i:s', strtotime('-1 minutes'))
            ]);
            $token = $existingToken['token'];
        }

        return $this->respond(['message' => 'Successfully logged out.'], 200);
    }

    public function sendResetLink()
    {
        $host = $this->request->getServer('HTTP_HOST');
        $json = $this->request->getJSON();
        $email = $json->email;

        // Your authentication logic here...
        $data = $this->validateUser($email);
        if ($data) {

            $token = bin2hex(random_bytes(50));
            $passwordResetModel = new PasswordResetModel();
            $passwordResetModel->insert([
                'email' => $email,
                'token' => $token,
                'created_at' => Time::now()
            ]);

            $resetLink =  $host . "/reset-password.php?token=$token";
            $message = "Click here to reset your password: $resetLink";

            // Send the email
            $email = \Config\Services::email();
            $email->setTo($data['email']);
            $email->setSubject('Password Reset Request');
            $email->setMessage($message);
            $email->send();
            return $this->respond(['message' => 'Reset password link was sent to your email.'], 200);
        } else {
            return $this->fail('No user found with that email address.');
        }
    }

    public function updatePassword()
    {
        $request = $this->request->getJSON();
        $token = $request->token;
        $password = $request->password;
        $reenter_password = $request->reenter_password;

        if(trim($password) && trim($reenter_password)){
            if(trim($password) != trim($reenter_password)){
                return $this->fail('Password and Re-enter password did not matched.');
            }
        }else{
            return $this->fail('Password and Re-enter password are required.');
        }
        $passwordResetModel = new PasswordResetModel();
        $record = $passwordResetModel->where('token', $token)->first();

        if (!$record) {
            return $this->fail('Invalid request.');
        }

        $userModel = model('App\Models\UserModel');
        $userModel->where('email', $record['email'])->set(['password' => sha1($password)])->update();

        // Delete the token after use
        $passwordResetModel->where('token', $token)->delete();

        return $this->respond(['message' => 'Password updated successfully.'], 200);
    }
}
