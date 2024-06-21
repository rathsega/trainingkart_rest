<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\WishlistModel;

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

    public function manageWhenUserLogin(){
        /*$request_data = $this->request->getJSON();
        //get existed wishlist items
        $backend_wishlist = $this->model->getExistedWishListItems($request_data->user_id);
        $backend_wishlist_ids = [];
        foreach($backend_wishlist as $item){
            $backend_wishlist_ids[] = (int)$item->course_id;
        }
        log_message('info', 'CORS Filter is running before1.' . json_encode(array("backend_wishlist" => $backend_wishlist)));
        $frontend_wishlist = $request_data->courseIds;
        log_message('info', 'CORS Filter is running before1.' . json_encode(array("frontend_wishlist" => $frontend_wishlist)));

        $wishlist_items = array_values(array_unique(array_merge($frontend_wishlist, $backend_wishlist_ids)));
        log_message('info', 'CORS Filter is running before1.' . json_encode(array("wishlist_items" => $wishlist_items)));

        $this->model->deleteAllWishlistItemsByUserId($request_data->user_id);

        $data =[];
        foreach($wishlist_items as $item){
            $data[] = array('user_id'=>$request_data->user_id, 'course_id'=> $item);
        }
        log_message('info', 'CORS Filter is running before1.' . json_encode(array("batch" => $data)));
        $inserted = $this->model->insertBatchRecords($data);
        log_message('info', 'CORS Filter is running before1.' . json_encode(array("inserted" => $inserted)));

        if($inserted){
            return $this->respond($wishlist_items, 200);
        }*/

        $courseIds = $this->request->getPost('courseIds'); // Assuming the course_ids are passed as a POST parameter
        $userId = $this->request->getPost('user_id'); // Assuming the user_id is passed as a POST parameter

        // Load the model
        $wishlistModel = new WishlistModel();
        
        // Get existing wishlist entries for the user
        $existingWishlist = $wishlistModel->getWishlistByUserId($userId);

        // Extract course IDs from existing wishlist
        $existingCourseIds = array_column($existingWishlist, 'course_id');

        // Calculate the unique course IDs that need to be inserted
        $uniqueCourseIdsToInsert = array_diff($courseIds, $existingCourseIds);

        // Prepare the data for batch insert
        $dataToInsert = [];
        foreach ($uniqueCourseIdsToInsert as $courseId) {
            $dataToInsert[] = [
                'course_id' => $courseId,
                'user_id' => $userId,
                'datetime' => date('Y-m-d H:i:s')
            ];
        }

        // Insert the unique courses into the wishlist table
        if (!empty($dataToInsert)) {
            $wishlistModel->insertBatch($dataToInsert);
        }

        // Return the updated wishlist course IDs
        $updatedWishlist = $wishlistModel->getWishlistByUserId($userId);
        $updatedCourseIds = array_column($updatedWishlist, 'course_id');

        return $this->respond(['course_ids' => $updatedCourseIds]);

    }
}
