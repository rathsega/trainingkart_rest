<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class FileController extends ResourceController
{
    public function upload()
    {
        $file = $this->request->getFile('file');
        if ($file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads', $newName);

            $response = [
                'status' => 200,
                'message' => 'File uploaded successfully',
                'filePath' => base_url('writable/uploads/' . $newName),
                'fileName' => $file->getClientName()
            ];
            return $this->respond($response);
        }

        return $this->fail($file->getErrorString());
    }

    public function deleteFile()
    {
        $json = $this->request->getJSON();
        $filePath = $json->filePath ? $json->filePath : '/';
        $fileName = basename($filePath);
        $fullPath = WRITEPATH . 'uploads/' . $fileName;

        if (file_exists($fullPath)) {
            unlink($fullPath);
            return $this->respond(['status' => 200, 'message' => 'File deleted successfully']);
        }

        return $this->failNotFound('File not found');
    }
}
