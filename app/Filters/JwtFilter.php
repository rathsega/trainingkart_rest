<?php
// app/Filters/JwtFilter.php
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\TokenModel;

class JwtFilter implements FilterInterface
{


    public function before(RequestInterface $request, $arguments = null)
    {
        $key = jwt_secret; // Change this to your secret key
        $authHeader = $request->getHeaderLine('Authorization');

        if (!$authHeader) {
            return $this->unauthorizedResponse('Missing Authorization Header');
        }

        $token = $authHeader;
        try {
            
            $decoded = JWT::decode($token, new Key($key, 'HS256'));

            // Check token expiry from the database
            $tokenModel = new TokenModel();
            $dbToken = $tokenModel->where('token', $token)->first();

            if (!$dbToken || strtotime($dbToken['expiry_date']) < time()) {
                return $this->unauthorizedResponse('Session has expired.');
            }

            // Extend token validity by 30 minutes
            $newExpiry = date('Y-m-d H:i:s', strtotime('+30 minutes'));
            $tokenModel->update($dbToken['id'], ['expiry_date' => $newExpiry]);

            // Attach user info to the request for further use
            $request->user = $decoded->email;
            $request->user_id = $decoded->user_id;
        } catch (\Exception $e) {
            return $this->unauthorizedResponse($e->getMessage());
        }

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed after the request
    }

    private function unauthorizedResponse($message)
    {
        $response = service('response');
        $response->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        $response->setBody(json_encode(['status' => 'error', 'message' => $message]));
        $response->setHeader('Content-Type', 'application/json');
        return $response;
    }
}
