<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\WishlistModel;

class Wishlist extends ResourceController
{
    protected $modelName = 'App\Models\WishlistModel';
    protected $format    = 'json';

    public function add()
    {
        $userId = $this->request->user_id;
        $postData = $this->request->getJSON();

        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['message' => 'User not logged in']);
        }

        $data = [
            'user_id' => $userId,
            'course_id' => $postData->product_id
        ];

        $this->model->insert($data);

        return $this->respondCreated(['message' => 'Product added to wishlist']);
    }

    public function getWishlist()
    {
        $userId = $this->request->user_id;

        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['message' => 'User not logged in']);
        }

        $model = new WishlistModel();
        $wishlist = $model->getWishlistByUserId($userId);
        foreach($wishlist as $key => $row){
            $wishlist[$key]->thumbnail = $this->get_course_thumbnail_url($row->course_id, "course_thumbnail", $row->last_modified );
        }

        return $this->respond($wishlist);
    }

    public function remove($id)
    {
        $userId = $this->request->user_id;

        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['message' => 'User not logged in']);
        }

        $model = new WishlistModel();
        $model->delete($id);

        return $this->respondDeleted(['message' => 'Product removed from wishlist']);
    }

    public function get_course_thumbnail_url($course_id, $type, $last_modified)
    {
        // Course media placeholder is coming from the theme config file. Which has all the placehoder for different images. Choose like course type.
        $course_media_placeholders = "assets/frontend/default-new/img/course_thumbnail_placeholder.jpg";
        return 'uploads/thumbnails/course_thumbnails/optimized/' . $type . '_' . 'default-new' . '_' . $course_id.$last_modified . '.jpg';
        if (file_exists(WRITEPATH . 'uploads/thumbnails/course_thumbnails/optimized/' . $type . '_' . 'default-new' . '_' . $course_id.$last_modified . '.webp')) {
            return 'uploads/thumbnails/course_thumbnails/optimized/' . $type . '_' . 'default-new' . '_' . $course_id.$last_modified . '.webp';
        } elseif (file_exists(WRITEPATH . 'uploads/thumbnails/course_thumbnails/optimized/' . $type . '_' . 'default-new' . '_' . $course_id.$last_modified . '.jpg')) {
            return 'uploads/thumbnails/course_thumbnails/optimized/' . $type . '_' . 'default-new' . '_' . $course_id.$last_modified . '.jpg';
        } elseif(file_exists(WRITEPATH . 'uploads/thumbnails/course_thumbnails/' . $type . '_' . 'default-new' . '_' . $course_id.$last_modified . '.jpg')) {

            //resizeImage
            //resizeImage(WRITEPATH . 'uploads/thumbnails/course_thumbnails/' . $type . '_' . 'default-new' . '_' . $course_id.$last_modified . '.jpg', WRITEPATH . 'uploads/thumbnails/course_thumbnails/optimized/', 400);

            return 'uploads/thumbnails/course_thumbnails/' . $type . '_' . 'default-new' . '_' . $course_id.$last_modified . '.jpg';
        }else{
            return $course_media_placeholders;
        }
    }
}
