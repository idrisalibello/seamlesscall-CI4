<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Email extends BaseConfig
{
    public string $fromEmail  = 'seamlesscallapp@gmail.com';
    public string $fromName   = 'SeamlessCall';
    public string $protocol   = 'smtp';
    public string $SMTPHost   = 'smtp.gmail.com';
    public string $SMTPUser   = 'seamlesscallapp@gmail.com';
    public string $SMTPPass   = 'nkrbtiehzzpxdedc'; //remove spaces!
    public int    $SMTPPort   = 587;
    public string $SMTPCrypto = 'tls';
    public int    $SMTPTimeout = 30;
    public bool   $wordWrap   = true;
    public string $mailType   = 'html';
    public string $charset    = 'UTF-8';
    public string $newline    = "\r\n";
}
