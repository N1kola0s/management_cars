<?php
namespace App\app\Core;
use App\Config\DB;
use Exception;
use mysqli;
//carica la classe per la connessione al DB;
require_once '../../config/db.php';

abstract class Model extends DB
{
    protected $created_by;
    protected $updated_by;
    protected $deleted_date;
    protected $deleted_by;

    //statement container di SELECT
    protected array $select_container = [];
    //statement container del WHERE
    protected array $where_container = [];
    //statement container del SET
    protected array $set_container = [];

    //proprietà per salvare i campi della tabella del db;
    protected array $fillable = [];
    //proprietà per salvare il nome della tabella;
    protected string $table_name;

    protected ?int $limit = null;
    protected ?int $offset = null;

    /**
     * Costruttore della classe
     *
     * @param string $class
     */
    public function __construct($class)
    {   
        //invoca costruttore da classe genitore per stabilire connessionde al DB;
        parent::__construct();

        //ritorna nome della tabella in base alla classe di appartenenza
        $this->table_name = $this->get_table_name($class);
    }

    public function set_limit($limit){
        $this->limit = $limit;
    }

    public function set_offset($offset){
        $this->offset = $offset;
    }

   
    /**
     * Pulisce campi senza corrispondenza con DB.
     *
     * verifica che i campi inseriti nella query corrispondano ai campi esistenti della tabella;
     * 
     * @param array $fields
     * @return array $data Campi in cui c'è corrispondeza
     */
    protected function fill(array $fields) : array {

        $data = [];

        //ciclo dell'array per ottenere il singolo campo della tabella;
        foreach ($this->fillable as $fillable){
            
            #check per passare i valori solo nei campi in cui esiste una corrispondenza;
            if(isset($fields[$fillable])){
                $data[$fillable]= $fields[$fillable];
            }
        }

        //ritorna solo campi con corrispondenza;
        return $data;
    }
    

    /**
     * Restituisce nome della classe.
     * 
     * Metodo per restituire il nome della tabella partendo da quello della classe.
     *
     * @param string $class
     * @return string Nome della tabella
     */
    public function get_table_name($class) : string {

        $classname = strtolower($class) . "s" ;
        $pos = explode("\\", $classname);
        $x = count($pos) - 1;

        return isset($pos[$x]) ? $pos[$x] : throw new Exception("Il nome della classe non è settato", 500);
              
    } 


    /**
     * Aggiunge elementi nella proprietà WHERE_CONTAINER.
     *
     * @param array $data
     * @return array $this->where_container - Dati aggiunti in proprietà where_container.
     */
    public function where(array $data) : array {
        
        //creazione row se presenti più elementi;
        foreach($data as $key => $value){

            //costruzione struttura dati where container; 
            $this->where_container[] = [
                'column' => $key,
                'value' => $value,
                'condition' => '='
            ]; 
        } 
        
        //ritorna array where_container con i dati che rispettano la struttura fissata;
        return $this->where_container;
    }
    
    /**
     * Aggiunge elementi nella proprietà WHERE_CONTAINER per creazione della clausola LIKE
     * 
     * @param array $data
     * @return array $this->where_container - Dati aggiunti in proprietà where_container
     */
    public function like(array $data) : array {

        //creazione row se presenti più elementi
        foreach($data as $key => $value){

            //costruzione struttura dati where container
            $this->where_container[] = [
                "column" => $key,
                "value" => "%" . $value . "%",
                "condition" => 'LIKE'
            ];
        }

        //ritorna array where_container con i dati che rispettano la struttura fisata;
        return $this->where_container;

    }

    //metodo costruzione stringa WHERE;

    /**
     * Costruttore della stringa Where.
     * 
     * Il metodo costruisce la stringa where da inserire nella query.
     *
     * @return string 
     */
    public function where_string_builder() : string {

        $where_string = ""; 
            
        //check, in caso where_container[] non sia vuoto;
        if(!empty($this->where_container)){ 
            
            //dichiarazione stringa e assegnazione valore in modo che inizi con istruzione 'WHERE';
            $where_string = 'WHERE '; 

            //dichiarazione variabile e assegnazione valore per lunghezza array;
            $size = count($this->where_container); 
            
            //ciclo degli elementi presenti in where_container[];
            for($i = 0 ; $i < $size; $i++){ 
                
                //dichiarazione e assegnazione variabile per singola riga; 
                $row = $this->where_container[$i]; 

                //"$row['column'] $row['condition'] $row['value']"

                //aggiunta stringa con formattazione valori 
                $where_string .= sprintf(" %s %s '%s'", $row['column'], $row['condition'], $row['value']); 
                

                //controlla che il blocco di codice venga eseguito fino al penultimo elemento in array;
                if($i != $size - 1){
                    //aggiunta AND
                    $where_string .= ' AND'; 
                }
                
            }
            
        }

        //ritorna la stringa WHERE;
        return $where_string;

    }

    
    /**
     * Restituisce struttura ID;
     * 
     * Metodo che verifica se l'ID è un token o un intero, costruendo struttura per aggiungere dati nella query;
     *
     * @param string|int $id
     * @return array $data_id Struttura ID da aggiungere nella query per esecuzione successiva di uno statement.
     */
    public function check_id_is_token(int|string $id) : array {
        if(is_int($id)){
            $data_id = [
                'id' => $id
            ];  
        } else {
            $data_id = [
                'sha1(id)' => $id
            ];  
        }
        
        return $data_id;
    }
    
    
    /**
     * Restituisce i container del modulo where,set,select vuoti.
     * 
     * Pulisce i rispettivi contenitori eliminando gli elementi aggiunti in precedenza.
     *
     * @return void 
     */
    protected function refresh_container() : void {
        $this->where_container = [];
        $this->set_container = [];
        $this->select_container = [];
        $this->offset = null;
        $this->limit = null;
    }
    
    
    /**
     * Crea ed esegue lo statement INSERT nel DB.
     *
     * @param array $data
     * @return int|null $insert_id  ID dell'ultimo record nel DB.
     */
    public function create(array $data) : ?int {   
        //esempio statement: INSERT INTO 'nome_tabella' ('colonna1','colonna2','colonna3'..) VALUES ('valore1', 'valore2','valore3'..);
        
        //PREPARAZIONE DATI:
        
        //passo i dati nel metodo fill per controllo campi;
        $data = $this->fill($data);
        //recupero la lista delle colonne dalle chiavi dell'array;
        $colsList = array_keys($data);
        //recupero i valori da data;
        $colsValue = array_values($data);
        //assegno ad una variabile la lista delle colonne separandole con una virgola per la query;
        $columns = implode(',', $colsList);

        //$placeholder_columns = ":" . implode(', :', $colsList);

        //recupero un placeholder per ogni colonna esistente;
        $placeholder_columns = rtrim(str_repeat('?,',count($data)), ',');  
        
        
        //CREAZIONE CUSTOM INSERT STATEMENT:

        
        //$stmt = $this->db->prepare("INSERT INTO" . " " . $this->get_table_name() . "($columns)" . " " . "VALUES" . "($placeholder_columns)");

        //formatto la query con il metodo sprintf;
        $query= sprintf("INSERT INTO %s (%s) VALUES (%s)", $this->table_name, $columns, $placeholder_columns);

        
        //PREPARAZIONE ED ESECUZIONE INSERT:
        
        //preparazione per il server della query;
        $stmt = $this->db->prepare($query);
        //bind dei valori;
        $stmt->bind_param('ssdsd', ...$colsValue);
        
        $this->refresh_container();

        //check dello statement, se l'esecuzione è false ritorno null;
        if($stmt->execute() === false){        

            return null;

        } else {
            //ritorno l'id dell'utente che ha creato il record
            $insert_id = $this->db->insert_id;            
            return $insert_id;
        }
    }


    /**
     * Costruisce ed esegue stmt SELECT nel DB.
     * 
     * Costruisce la query per eseguire lo statement, partendo dalla stringa del select e dagli elementi presenti nella proprietà where_container.
     *
     * @return array $result Dati restituiti dal SELECT.
     */
    public function read() : ?array {
        //check su variabile che contiene lista colonne inserite nel SELECT;

        //in caso sia vuoto aggiunta '*' altrimenti aggiunta stringa singoli elementi separati da virgola;
        $select_string = empty($this->select_container) ? '*' : implode(",",$this->select_container); 
    
        //verifica su array contenitore delle clausole WHERE; 
        
        $where_string = $this->where_string_builder();

        //creazione limit string se sono settate le proprietà limit e offset
        if(is_null($this->limit)){

            $limit_string = '';

        } else {

            $limit_string = sprintf("LIMIT %d, %d", $this->offset, $this->limit);

        }

        //return di tutti i dati selezionati come array associativo;
        
        //COSTRUZIONE ED ESECUZIONE QUERY PER RITORNO NUMERO ROWS;

        //SELECT COUNT(*) FROM table_name
        $query_counter = sprintf("SELECT COUNT(*) as T FROM %s %s", $this->table_name, $where_string);

        $stmt_counter = $this->db->query($query_counter);

        $result['count_rows'] = $stmt_counter->fetch_assoc()["T"];

        //QUERY PER RITORNO DATI IN LETTURA CON MODULI SELECT, WHERE, LIMIT;

        //creazione del SELECT custom statement;
        $query = sprintf("SELECT %s FROM %s %s %s", $select_string, $this->table_name, $where_string, $limit_string);
        
        //var_dump($query);
    

        //esecuzione dello statement;
        $stmt = $this->db->query($query); 
        
        $this->refresh_container();
        
        //return di tutti i dati selezionati come array associativo;
        $result['data'] = $stmt->fetch_all(MYSQLI_ASSOC);
        
         /* echo "<pre>";
         var_dump($result); */
        
        return $result;
        
        
    }


    /**
     * Costruisce la query ed esegue lo statement UPDATE verso il DB partendo dall'ID.
     * 
     * Metodo che costruisce query ed esegue lo statement partendo da ID. 
     *
     * @param string|int $id Il paramentro ID può essere un valore criptato o un numero intero;
     * @param array $data Dati con campi e valori da modificare;
     * @return bool Esecuzione dello statement - TRUE esecuzione riuscita, FALSE esecuzione fallita.
     */
    public function update_by_id($id, array $data) {   
        //esempio stmt specifico;
        //UPDATE distances SET validation = 'valid' WHERE id = '120';
        
        //esempio stmt generico;
        //UPDATE table_name SET column1 = value1, column2 = value2, ... WHERE condition; 
        
        
        //passo i dati nel metodo fill per controllo campi;
        
        $data = $this->fill($data);

        //inizializzazione stringa del modulo SET;
        $set_string = "";
        
        //inizializzazione e assegnazione di un contatore per il ciclo;
        $counter = 0;
        
        //inizializzazione variabile con lunghezza array dei dati per il SET;
        $num_col = count($data);
        
        //costruzione stringa modulo SET; 
        foreach($data as $col => $value){
            $counter = $counter + 1;
            
            //$set_string .= "$col" . "=" . "$value";
            $set_string .= sprintf(" %s = '%s'", $col, $value); 
            
            //controlla che il blocco di codice venga eseguito fino al penultimo elemento in array;
            if($counter != $num_col){
                //aggiunta separatore
                $set_string .= ', '; 
            }
        }
        
        
        //dichiarazione variabile per la stringa del 'Where' ; 
        //$where_string = '';
        
        //assegnazione clausola con campo Id;
        $data_id = $this->check_id_is_token($id);
        
        //aggiunta del campo ID nel where_container;
        $this->where($data_id);  
        
        //costruzione della stringa Where in base al Where Container;
        
        $where_string = $this->where_string_builder();
        
        //creazione dell UPDATE custom statement;
        $query = sprintf("UPDATE %s SET %s %s ;", $this->table_name, $set_string, $where_string);
        
        //preparazione dello stmt;
        $stmt = $this->db->prepare($query); 
        
        $this->refresh_container();

        //esecuzione e check dello statement, ritorno valori booleani;
        return $stmt->execute();

    }

    
    public function delete(int $id) {
        
    }

}

?>