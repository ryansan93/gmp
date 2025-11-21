<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// application/libraries/Eloquent.php
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Facades\DB;

class Eloquent
{
    public function __construct()
    {
        $capsule = new Capsule;
        // ... (connection details using $db from CI config) ...
        $capsule->setAsGlobal(); // <-- THIS IS CRITICAL
        $capsule->bootEloquent(); // <-- THIS IS CRITICAL
    }
}