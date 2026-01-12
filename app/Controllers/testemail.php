<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class TestEmail extends Controller
{
    public function index()
    {
        $email = \Config\Services::email();
        $email->setFrom('seamlesscallapp@gmail.com', 'SeamlessCall');
        $email->setTo('idrisalibello@gmail.com'); // replace with your test email
        $email->setSubject('Test OTP Email');
        $email->setMessage('This is a test OTP email from SeamlessCall.');

        if ($email->send()) {
            echo "Email sent successfully!";
        } else {
            echo "Email failed:<br>";
            echo $email->printDebugger(['headers', 'subject', 'body']);
        }
    }
}
