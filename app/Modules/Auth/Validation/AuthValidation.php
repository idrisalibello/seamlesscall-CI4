<?php
namespace App\Modules\Auth\Validation;

class AuthValidation
{
    public $register = [
        'name'=>'required|min_length[3]',
        'email'=>'required|valid_email|is_unique[users.email]',
        'phone'=>'required|is_unique[users.phone]',
        'password'=>'required|min_length[8]',
        'role'=>'required|in_list[Admin,Provider,Customer]'
    ];

    public $login = [
        'email_or_phone'=>'required',
        'password'=>'required'
    ];
}
