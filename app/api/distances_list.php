<?php

    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    use  App\app\Controllers\DistanceController;
    use App\app\Core\Controller;

    require '../Controllers/DistanceController.php';


    //creazione istanza controller di distance
    $controller_distance= new DistanceController();

    //controllo setting paginazione
    if(isset($_GET['pagination'])){
        
        //invocazione metodo pagination dal controller applicato sui dati;
        $controller_distance->pagination($_GET['pagination']);
        
    }

    //controllo setting data
    if(isset($_GET['data'])){

       
        //salvataggio in variabile data_distances dei dati restituiti in caso esista una chiave 'data';
        $data_distances = $controller_distance->get_data($_GET['data']);

    } else {
        //salvataggio in variabile dei dati restituiti in caso non esista la chiave 'data';
        $data_distances = $controller_distance->get_data([]);
    }

    

    //codifica di un file json da data_distances da restituire alla view; 
    header('Content-Type: application/json');
    echo(json_encode($data_distances));

?>