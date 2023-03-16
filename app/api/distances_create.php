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


//creo istanza controller di distance
$controller_distance = new DistanceController();

//Se il campo non è vuoto, preparo la data nel formato accettato dal db;

if($_POST["car_rental_month"] !== ''){
    
    $_POST ["car_rental_month"] = $controller_distance->change_date_format($_POST, "car_rental_month", "Y-m-d");
    
}


//invoca metodo di validazione form;
$data = $controller_distance->validate_form();

if($data['success']){
    
    //inserimento nuovo record;
    
    //creazione record;
    $data_distances = $controller_distance->create($_POST); 

    
    //da data_distances codifico un file json da restituire alla view;

    if(!($data_distances)){

       

        $response = [
            "message" => $controller_distance->message,
            "success" => false
        ];
        
    } else {

        $response = [
            "message" => "Invio dati avvenuto con successo",
            "success" => true
        ];
    }

  
    //header('Content-Type: application/json');
    echo json_encode($response); 
    
    
} else {


    header('Content-Type: application/json');
    echo json_encode($data); 

}

