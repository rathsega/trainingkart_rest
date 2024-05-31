<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class Wishlist extends ResourceController
{
    protected $modelName = 'App\Models\WishlistModel';
    protected $format    = 'json';

    public function create()
    {
        $data = $this->request->getPost();
        $this->model->insert($data);

        return $this->respondCreated(['status' => 'Item added to wishlist']);
    }

    public function delete($id = null)
    {
        $this->model->delete($id);
        return $this->respondDeleted(['status' => 'Item removed from wishlist']);
    }

    public function moveToCart()
    {
        $data = $this->request->getPost();
        // Logic to move item from wishlist to cart
        return $this->respond(['status' => 'Item moved to cart']);
    }
}
