<?php

    use App\app\Core\Controller;
    use App\app\Controllers\DistanceController;

    require '../Controllers/DistanceController.php';


    //creo istanza controller di distance
    $controller_distance = new DistanceController();

    //controllo setting paginazione
    if(isset($_GET['pagination'])){


        unset($_GET['pagination']);
        
    }

    //controllo setting data
    if(isset($_GET['data'])){

        //chiamata metodo del controller per recuperare dati della tabella in base ai parametri inseriti in variabile globale ($_GET) in caso esistenza chiave 'data' e stamparli in excel;
        $data_list = $controller_distance->spreadsheet_export($_GET['data']);

    } else {
        //chiamata metodo del controller per recuperare dati della tabella in base ai parametri inseriti in variabile globale ($_GET) in caso esistenza chiave 'data' e stamparli in excel;
        $data_list = $controller_distance->spreadsheet_export([]);
    } 


    header('Content-Type: application/json');
    echo(json_encode($data_list));



?>