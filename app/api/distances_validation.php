<?php

    use  App\app\Controllers\DistanceController;

    require_once '../../vendor/autoload.php';
    require '../Controllers/DistanceController.php';

    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    function debug($data){
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        die;
    }


    //$auth_token_id = $_GET['auth_token'];
    $auth_token = $_GET['auth_token'];


    //decodifico i dati passati in json;
    $obj = json_decode(file_get_contents('php://input'));

    //converto in array;
    $data = (array)$obj;

  

    //creo istanza controller di distance
    $controller_distance = new DistanceController();

    //eseguo update dati;

    
    $data_validate = $controller_distance->update_by_token_id($auth_token, $data);
    
    //var_dump($data_validate);

    //stampo json per stampare a schermo i relativi messaggi di update dei dati;
    echo json_encode($data_validate); 




?>