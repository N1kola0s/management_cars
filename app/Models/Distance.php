<?php
//require __DIR__ . '/../../config/db.php';
namespace App\app\Models;
use App\app\Core\Model;



require_once '../Core/Model.php';

class Distance extends Model
{

   protected array $fillable = ['car_license_plate','car_rental_month', 'km', 'email', 'validation', 'created_by'];


   public function __construct(){
        parent::__construct(Distance::class);
   }

    public function check_plates_month($post_data){  
        
        //creazione struttura dati per costruire modulo WHERE per filtro condizioni in lettura dati, nello specifico targa/mese/dato validato; 
        $input_where_data = [
            
            'car_license_plate' => $post_data['car_license_plate'],
            'car_rental_month' => $post_data['car_rental_month'],
            'validation' => 'valid'
        ];
        
        //invoco metodo per costruzione modulo WHERE container con i valori in input specificati;
        $this->where($input_where_data);
        
        //Inizializzazione variabile in cui salvo valori in lettura;
        $check_result = $this->read();

        /* echo "<pre>";
        var_dump($check_result['data']);
        die; */

        //==========REMINDER ======================================================================================================================
        //TEST PERCHE NON MI REGISTRAVA DATI DICENDOMI CHE ESISTEVA GIA UN RECORD CON QUELLA TARGA IN QUEL MESE , AGGIUNTO ['data'] ALLA STRUTTURA
        //=========================================================================================================================================
      
      //CHECK : se la lettura produce un risultato, quindi esiste record validato di una targa in quel mese, ritorna FALSE; 
       if (!empty($check_result['data'])){
            
            return false;

            //altrimenti se non esiste già un record validato con quella targa in quel mese, ritorna TRUE;
        } else {
            
            return true;
        } 
    }


   public function check_id(array $get_data){
    //costruzione struttura ID;
        $input_where_data = [
            'id' => $get_data['id']
        ];
        //invoco metodo per costruzione modulo WHERE con valori in input specificati;
        $this->where($input_where_data);

        //salvo in variabile i valori in lettura;
        $check_result = $this->read();
        
        
        //effettuo CHECK: se la lettura produce un risultato ritorna FALSE;
       if (!empty($check_result)){
            return false;

            //se la lettura NON produce risultati ritorna TRUE;
        } else {
            return true;
        } 


    }



    public function read_by_id($id){

        

        //costruzione struttura ID per il where;
        
        //MODIFICATO PER TEST;


          $input_where_id = [
            'id' => $id
        ];


        $input_where_id = $this->check_id_is_token($id);

        
        
        
        //invoco metodo per costruzione modulo WHERE con valori in input specificati;
        $this->where($input_where_id);
        

       

        //salvo in variabile i valori in lettura;
        $id_result = $this->read();

      //REMINDER ==================================================
      //AGGIUNTO ['data'] PER BUG DOVUTO AL CAMBIO STRUTTURA ARRAY;
      //===========================================================

        return $id_result['data'][0] ?? NULL;

    }

}


?>