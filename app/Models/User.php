<?php
namespace App\app\Models;
use App\app\Core\Model;
require_once '../Core/Model.php';

class User extends Model
{

    public $fillable = ['email', 'password'];


   public function __construct()
   {
    
    parent::__construct(User::class, $_GET);
      
   }


}



?>