<?php

namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\ResponseInterface;

class Blogs extends ResourceController
{


    public function getPaginatedBlogs($page=1,$limit = 10){
        $blogModel = model('App\Models\BlogModel');

        $data =  $blogModel->getPaginatedBlogs((int)$page, (int)$limit);
        return $this->response->setJSON($data);
    }

    public function getPaginatedInterviewQuestions($page=1,$limit = 10){
        $blogModel = model('App\Models\BlogModel');

        $data =  $blogModel->getPaginatedInterviewQuestions($page, $limit);
        return $this->response->setJSON($data);
    }

    
    public function getBlogsCount(){
        $blogsModel = model('App\Models\BlogModel');

        $data =  $blogsModel->getBlogsCount();
        return $this->response->setJSON(array("count"=>$data));
    }
    
    public function getInterviewQuestionsCount(){
        $blogsModel = model('App\Models\BlogModel');

        $data =  $blogsModel->getInterviewQuestionsCount();
        return $this->response->setJSON(array("count"=>$data));
    }


    public function options()
    {
        return $this->response->setStatusCode(ResponseInterface::HTTP_OK);
    }

    public function getUniqueKeywords()
    {
        $blogModel = model('App\Models\BlogModel');
        $keywordsArray = $blogModel->getAllKeywords();

        $allKeywords = [];
        foreach ($keywordsArray as $row) {
            $keywords = explode(',', $row['keywords']);
            $allKeywords = array_merge($allKeywords, array_map('trim', $keywords));
        }

        $uniqueKeywords = array_unique($allKeywords);
        sort($uniqueKeywords); // Optional: sort alphabetically

        // Return or process the unique keywords as needed
        return $this->response->setJSON($uniqueKeywords);
    }

    public function getRecentTagsOfBlogs()
    {
        $blogModel = model('App\Models\BlogModel');
        $tagsArray = $blogModel->getRecentTagsOfBlogs();

        $alltags = [];
        foreach ($tagsArray as $row) {
            $alltags[] = $row['tag'];
        }

        $uniquetags = array_unique($alltags);
        sort($uniquetags); // Optional: sort alphabetically

        // Return or process the unique tags as needed
        return $this->response->setJSON($uniquetags);
    }

    public function getRecentTagsOfInterviews()
    {
        $blogModel = model('App\Models\BlogModel');
        $tagsArray = $blogModel->getRecentTagsOfInterviews();

        $alltags = [];
        foreach ($tagsArray as $row) {
            $alltags[] = $row['tag'];
        }

        $uniquetags = array_unique($alltags);
        sort($uniquetags); // Optional: sort alphabetically

        // Return or process the unique tags as needed
        return $this->response->setJSON($uniquetags);
    }

    public function getCategoriesWithBlogCount()
    {
        $blogModel = model('App\Models\BlogModel');
        $categories = $blogModel->getCategoriesWithBlogCount();

        return $this->response->setJSON($categories);
    }

    public function getCategoriesWithInterviewQuestionCount()
    {
        $blogModel = model('App\Models\BlogModel');
        $categories = $blogModel->getCategoriesWithInterviewQuestionCount();

        return $this->response->setJSON($categories);
    }
}
