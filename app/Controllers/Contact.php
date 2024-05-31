<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class Contact extends ResourceController
{
    protected $modelName = 'App\Models\ContactModel';
    protected $format    = 'json';

    public function contactus_submitted()
    {
        // Set timezone
        date_default_timezone_set('Asia/Kolkata');

        // Retrieve the JSON input
        $input = $this->request->getJSON();

        // Extract data from the JSON input
        $name = $input->name ?? '';
        $email = $input->email ?? '';
        $phone = $input->phone ?? '';
        $message = $input->message ?? '';
        $course = $input->course ?? '';


        // Prepare the details array
        $details = [
            'first_name' => $name,
            'email' => $email,
            'phone' => $phone,
            'message' => $message,
            'course' => $course,
            'datetime' => time(),
        ];

        // Insert data into the database
        $inserted = $this->model->insert($details);

        if ($inserted) {
            // Assuming you have an email helper or service to send the email
            service('email_service')->sendContactEmail($course, "$name", $email);

            return $this->respond(['message' => 'Thank You For Contacting Us.']);
        } else {
            return $this->fail('Failed to submit contact details.');
        }
    }
}
