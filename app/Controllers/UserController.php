<?php
namespace App\app\Controllers;
use App\app\Models\User;

require_once '../Models/User.php';

class UserController 
{
    //proprietà classe
    protected $model;

    public function __construct()
    {
        $this->model = new User();
    }

    //ritorno i dati dal metodo read() del modello;
    public function get_data() {
        return $this->model->read();
    }
        
}

?>