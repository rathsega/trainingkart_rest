<?php

namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\ResponseInterface;

class Blogs extends ResourceController
{


    public function getPaginatedBlogs($page=1,$limit = 10){
        $blogModel = model('App\Models\BlogModel');

        $data =  $blogModel->getPaginatedBlogs($page, $limit);
        return $this->response->setJSON($data);
    }

    public function options()
    {
        return $this->response->setStatusCode(ResponseInterface::HTTP_OK);
    }
}
