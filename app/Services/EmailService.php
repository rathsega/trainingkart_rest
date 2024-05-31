<?php

namespace App\Services;

class EmailService
{
    public function sendContactEmail($course, $fullName, $to)
    {
        // Implement email sending logic here
        // For example, using CodeIgniter's Email library
        $email = \Config\Services::email();

        $email->setTo($to);
        $email->setSubject('Contact Us Form Submission');
        $email->setMessage("Course: $course\nName: $fullName\nEmail: $to");

        return $email->send();
    }
}
