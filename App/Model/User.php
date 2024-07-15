<?php

namespace App\Model;

use Lib\Model;

class User extends Model
{

    protected $table = 'users';
    public function __construct()
    {
        parent::__construct();
    }

    
}
