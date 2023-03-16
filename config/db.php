<?php
    namespace App\Config;
    use mysqli;
    use Exception;
    //carica file config;
    require __DIR__ . '/config.php';

    class DB 
    {
        protected $db;

        function __construct()
        {
            //inizializzo variabile e salvataggio oggetto della connessione al database;
            $connection = new mysqli (DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                //check presenza connessione o errori
                if($connection && $connection->connect_error){
                    //dichiarazione variabile per salvataggio dell'errore;
                    $error = $connection->connect_error;
                    //sollevo eccezione in caso di errore della connessione;
                    throw new Exception("Errore, la connessione con il Database è fallita $error", 500);
                }
            //assegnazione valore alla proprietà della classe;
            $this->db = $connection;
        }

        function __destruct()
        {
            //chiusura della connessione;
            $this->db->close();
        }

        public function get_connection(){
            return $this->db;
        }

    }


?>