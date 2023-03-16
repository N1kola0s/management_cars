<?php

use  App\app\Controllers\UserController;
use App\app\Core\Controller;

require '../Controllers/UserController.php';

//creo istanza controller di distance;
$controller_user = new UserController();

//creo istanza controller di user;
$controller_user= new UserController();

//invoco metodo del controller per recuperare dati tabella utenti in base ai parametri inseriti in variabile globale ($_GET);

$data_users = $controller_user->get_data();
//var_dump($data_users);

//da data_users codifico un file json da restituire alla view; 
 header('Content-Type: application/json');
echo(json_encode($data_users));
 


?>