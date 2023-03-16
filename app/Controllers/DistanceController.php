<?php
    namespace App\app\Controllers;
    use App\app\Models\Distance;
    use App\app\Core\Mail;
    use Symfony\Component\Validator\Constraints\Date;
    use Symfony\Component\Validator\Constraints\Email;
    use Symfony\Component\Validator\Constraints\Length;
    use Symfony\Component\Validator\Constraints\LessThanOrEqual;
    use Symfony\Component\Validator\Validation;
    use Symfony\Component\Validator\Constraints\NotBlank;
    use Symfony\Component\Validator\Constraints\Positive;
    use Symfony\Component\Validator\Constraints\Regex;
    use Symfony\Component\Validator\Constraints\Type;
    use Exception;
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

    require_once '../Models/Distance.php';
    require_once '../Core/Mail.php';
    //Autoload dependencies
    require_once '../../vendor/autoload.php';
    //require  '../../../config/config_mail.php';

    class DistanceController
    {
        public $message;
        protected $model;
        //proprietà con campi della tabella distances;
        protected $form_fields = ['car_license_plate', 'car_rental_month', 'km', 'email'];
        protected $mail;


        public function __construct()
        {
            //istanza del modello distance;
            $this->model = new Distance();

            //istanza della classe per libreria mail;
            $this->mail= new Mail();
        }

        /**
         * Setta le proprietà della classe utili alla paginazione dei dati in lettura
         * 
         */
        public function pagination($data){

            //verifica che la pagina corrente sia successiva a quella impostata di default
            if($data['currentPage'] == 1){

                //impostazione proprietà per limit e offset
                $this->model->set_limit($data['perPage']);
                $this->model->set_offset(0);

            } else {
                
                //impostazione proprietà per limit e offset
                $this->model->set_limit($data['perPage']);
                $this->model->set_offset(($data['currentPage'] - 1) * $data['perPage']);
            }
        }


        /**
         * Recupera dati dal Database e crea foglio di lavoro.
         * 
         * Invoca il metodo del modello di riferimento per recuperare i dati dal database, crea il foglio di lavoro nell'estensione .xlsx e restituisce il percorso in locale del file;
         * 
         * @return array associativo avente come valore la stringa del percorso in locale del file.
         */
        public function spreadsheet_export(array $data) : array {


            $data_list = $this->get_data($data);            

            //creazione oggetto new Spreadsheet
            $spreadsheet = new Spreadsheet();

            //recupero del foglio di lavoro attivo
            $sheet = $spreadsheet->getActiveSheet();

            //settaggui dekke celle
            $sheet->setCellValue('A1', 'Targa');
            $sheet->setCellValue('B1', 'Mese_Anno');
            $sheet->setCellValue('C1', 'Distanza_percorsa');
            $sheet->setCellValue('D1', 'Email');
            $sheet->setCellValue('E1', 'Validato');


            //estrazione singolo dato ed assegnazione el valore alla cella;

            $count = 2;
            foreach($data_list['data'] as $datum){

                //conversione data da stampare in cella foglio di lavoro;
                $input_date = $datum['car_rental_month'];
                $input_date = strtotime($input_date);
                $datum['car_rental_month'] = date("m-Y", $input_date);

                //conversione valore validazione da stampare in cella foglio di lavoro;
                if($datum['validation'] == 'valid'){
                    //è valido;
                    $datum['validation'] = 'Sì';
                }else{
                    //non è valido;
                    $datum['validation'] = 'No';
                }

                $sheet->setCellValue('A'.$count, $datum['car_license_plate']);
                $sheet->setCellValue('B'.$count, $datum['car_rental_month']);
                $sheet->setCellValue('C'.$count, $datum['km']);
                $sheet->setCellValue('D'.$count, $datum['email']);
                $sheet->setCellValue('E'.$count, $datum['validation']);
                $count++;
            }

            //scrittura del nuovo file .xlsx
            $writer = new Xlsx($spreadsheet);

            //salvataggio del nuovo file .xlsx
            $writer->save("Export/dati_gestione_flotte.xlsx");

            //ritorno del percorso del file
            
            //VUE_APP_URL_BASE "http://localhost:8000/app/api/distances_download.php"
            return ["path" => "/Export/dati_gestione_flotte.xlsx"];

        }


        /**
        * Recupera dati dal Database;
        *
        * Invoca il metodo del modello di riferimento, recuperando i dati in base alla query;
        *
        * @return array Dati restituiti dalla lettura nel DB.
        */
        public function get_data($data) {
            /* echo "<pre>";
            print_r($data);
            die; */
            //CHECK se in data è presente la targa dove applicare il LIKE metodo altrimenti utilizzo il WHERE;

            if(!empty($data['car_license_plate'])){

                $this->model->like(['car_license_plate' => $data['car_license_plate']]);

                unset($data['car_license_plate']);
                $this->model->where($data);

                //invoco il metodo read() dal modello Distance;
                return $this->model->read();

            } else {

                //ritorno i valori delle variabili per la costruizione dello stmt del select in virtù delle modifiche fatte nel metodo WHERE nel modello;
                $this->model->where($data);
                //invoco il metodo read() dal modello Distance;
                return $this->model->read();
            }

        }

        /**
        * Scrittura dati nel Database
        *
        * Invoca il metodo del modello per inserimento nuovo record nel database e invia email per conferma/validazione dei dati registrati;
        *
        * @param array Dati recuperati attraverso il metodo;
        *
        * @return string|bool id criptato del nuovo record nel database o FALSE;
        */
        public function create(array $data) : string|bool {
            

         
            //se NON esiste un record VALIDATO con quella targa in quel mese (in quel caso check_plates_month ritornava TRUE);
            if($this->model->check_plates_month($data)){


                //check creazione record
                //se non è null viene effettuata la creazione del record che ritornerà l'ID;
                if(!is_null($distances_id = $this->model->create($data))){


                    //creazione token dell'ID;
                    $auth_token_id = sha1($distances_id);
        
                    //viene effettuato l'invio della mail di conferma all'indirizzo con il token di quell'ID;
                    $confirmation_data = $this->data_confirmation_email($auth_token_id, $data['email']);

                    //check su invio email conferma
                    if(!is_array($confirmation_data)){

                        //se non è un array ritorna il valore dell'ID;
                        return $distances_id;

                    } else {
                        //altrimenti ritorna un messaggio di errore;
                        $this->message =  "Processo di invio email di conferma fallito";

                        return FALSE;

                    }

                    //ritorno valore dell'ID;
                    //return $distances_id;

                }else{

                    $this->message = "Processo di registrazione dei dati fallito";

                    //altrimenti ritorna messaggio di errore di creazione;
                    return FALSE;

                };

            } else {

                $this->message = "Impossibile inviare i dati. La targa inserita già è stata confermata per questo mese.";
                //se il controllo targa/mese restituisce un risultato ( ossia esiste già un record VALIDATO con quella targa in quel mese), ritorna FALSE;
                return FALSE;
            }
        }

        /**
         * Esecuzione dello statement UPDATE
         *
         * @param string|int $id
         * @param array $data Campi con valori da modificare.
         * @return array $validate_data Dati di status e messaggio relativo di modifica.
         */
        public function update_by_token_id(string|int $id, array $data) : array {

            
            //verifico se esiste record con con quel ID;
            $data_from_id = $this->model->read_by_id($id);
            


            //se NON esiste record con quell'ID
            if(is_null($data_from_id)){

                //var_dump("non esiste record con questo ID");


                //se non esiste ritorno status FALSE;
                $validate_update = [
                    "message" => "Errore, non esiste una registrazione con il tuo identificativo.",
                    "success" => false
                ];

                return $validate_update;

            } else {

                //se esiste record con quell'ID, verifico se è già VALIDATO:

                //invoco il metodo check plates, se esistesse record valido ritornerebbe FALSE:
                if($this->model->check_plates_month($data_from_id)){

                    //quindi se fosse vero, NON esisterebbe un record VALIDATO (targa/mese);

                    //inizializzo variabile in cui salvo esecuzione update del campo validation, ritorna TRUE se viene effettuato con successo
                    $validate_update = $this->model->update_by_id($id, $data);

                    //messaggio di update in caso di validazione effettuata con successo;
                    $validate_update = [
                        "message" => "La registrazione dei dati inseriti è avvenuta con successo.",
                        "success" => TRUE
                    ];

                    return $validate_update;

                } else {
                    //se esiste un record già VALIDATO;

                    //esiste record già validato con quel mese/targa;
                    $validate_update = [
                        "message" => "Attenzione, i dati non sono stati confermati. Esiste già una registrazione valida in questo mese con la targa inserita.",
                        "success" => false
                    ];

                    return $validate_update;

                }


            }

        }



        /**
        * Modifica il formato di una data.
        *
        * Prepara la data nel formato accettato dal database: da "mm-yyyy" in "yyyy-mm-01" ;
        *
        * @param array $data Dati recuperati attraverso il metodo;
        *
        * @param string $columns Campo di riferimento della data;
        *
        * @param string $format Formato della data desiderato in uscita;
        *
        * @return string Valore Data;
        */
        public function change_date_format(array $data, string $columns, string $format) : string {


            //recupero i singoli valori (mese,anno) dalla relativa stringa nei dati in ingresso
            $first_date =  explode(" ", $data[$columns]);
            //inizializzo variabile con il nome del mese
            $month_name = strtolower($first_date[0]);
            //inizializzo variabile con l'anno
            $year = $first_date[1];


            //associo i nomi dei mesi dell'anno alle rispettive chiavi numeriche
            $months = [
                1 => "gennaio",
                2 => "febbraio",
                3 => "marzo",
                4 => "aprile",
                5 => "maggio",
                6 => "giugno",
                7 => "luglio",
                8 => "agosto",
                9 =>  "settembre",
                10 => "ottobre",
                11 => "novembre",
                12 => "dicembre"
            ];


            //cerco nell'array il valore il nome del mese e ritorno la prima chiave corrispondente in caso di successo
            $month_num = array_search($month_name, $months);

            //stringa della data in input
            $input_date = sprintf("01-%s-%s", $month_num, $year);
            //$input_date = "01-". "$month_num-" . "$year";

            //analizza la stringa in ingresso in un timestamp unix;
            $input_date = strtotime($input_date);

            //restituisco in uscita una data nel formato adatto alla registrazione nel DB;
            return date($format,$input_date);
        }

        /**
        * Regole di validazione.
        *
        * Contiene le regole di validazione per inserimento dati nei campi del form;
        *
        * @return array nome campi form => regole di validazione;
        */
        private function get_validation_rules() : array {
            return [
                "car_license_plate" => [
                    new NotBlank(
                        [
                            "normalizer" => "trim",
                            "message" => "Attenzione, il formato della targa non è valido. (es. AA159BB)"
                        ]
                    ),
                    new Type(
                        [
                            "type" => "alnum",
                            "message" => "Attenzione, il formato della targa non è valido. (es. AA159BB)"
                        ]
                    ),
                    new Regex(
                        [
                            "pattern" => "/[A-Za-z]{2}[0-9]{3}[A-Za-z]{2}/",
                            "message" => "Attenzione, il formato della targa non è valido. (es. AA159BB)"
                        ]
                    ),
                    new Length([
                        "min" => 7,
                        "max" => 7,
                        "exactMessage" => "Attenzione, il formato della targa non è valido. (es. AA159BB)"
                    ])

                ],
                "car_rental_month" => [
                    new NotBlank(
                        [
                            "normalizer" => "trim",
                            "message" => "Attenzione, inserire una data valida. (es. gennaio 2022)"
                        ]
                    ),
                    new Date(
                        [
                        "message" => "Attenzione, inserire una data valida. (es. gennaio 2022)"
                        ]
                    )
                ],
                "km" => [
                    new NotBlank(
                        [
                            "normalizer" => "trim",
                            "message" => "Attenzione, inserisci un numero di chilometri valido. (es. min '1', max '50000')"
                        ]
                    ),
                    new Type(
                        [
                            "type" => "digit",
                            "message" => "Attenzione, inserisci un numero di chilometri valido. (es. min '1', max '50000')"
                        ]
                    ),
                    new Positive(
                        [
                            "message" =>"Attenzione, inserisci un numero di chilometri valido. (es. min '1', max '50000')"
                        ]
                    ),
                    new LessThanOrEqual(
                        [
                            "value" => 50000,
                            "message" => "Attenzione, inserisci un numero di chilometri valido. (es. min '1', max '50000')"
                        ]),
                ],
                "email" =>[
                    new NotBlank(
                        [
                            "normalizer" =>"trim",
                            "message" => "Attenzione, inserire un'email valida. (es. nomeutente@dominio.it)"
                        ]
                    ),
                    new Email(
                        [
                            "message" => "Attenzione, inserire un'email valida. (es. nomeutente@dominio.it)"
                        ]
                    )
                ]
            ];
        }

        /**
         * Validazione dei dati inseriti nel form
         *
         * @return array $val_data Dati di Status e Messaggio di errore.
         */
        public function validate_form() : array {
            //invoco metodo per la validazione dalla classe di symphony;
            $validator = Validation::createValidator();

            //dichiaro variabile con array composto da elementi di status e messaggio di successo della validazione;
            $val_data = [
                "success" => true,
                "message" => "validazione avvenuta con successo."
            ];

            //invoco il metodo per le regole di validazioni;
            $validation_rules =  $this->get_validation_rules();

            //imposto ciclo per selezionare il singolo campo del form;
            foreach($this->form_fields as $field){

                //check tra i campi del form e la chiave presente nell'array delle regole di validazione;
                if(!array_key_exists($field, $validation_rules)){

                    $val_data['success'] = false;

                    //["message" => "I campi inseriti non sono presenti nel form."];
                    $val_data['message'] = "La validazione non è stata effettuata su tutti i campi del form.";


                    return $val_data;
                }

            }


            //seleziono i singoli valori presenti nell'array delle regole di validazione per verifica e restituzione messaggi di errore;
            foreach($validation_rules as $field => $value){

                $violations = $validator->validate($_POST[$field], $value);

                if(0 !== count($violations)){
                    //mostra se ci sono degli errori;

                    $val_data['success'] = false;

                    $val_data['message']= $violations[0]->getMessage();

                    return $val_data;

                }

            }

            return $val_data;
        }

        /**
         * Invia email di coferma dei dati inseriti nel form.
         *
         * Elaborazione del messaggio e invio della mail per confermare i dati inseriti.
         *
         * @param string $auth_token_id Il token del ID.
         * @param string $recipient_address Email inserita nel form.
         * @return bool Invio della mail.
         */
        public function data_confirmation_email($auth_token_id, $recipient_address) {

            $URL_SERVER = URL_SERVER_VALIDATION;

            $email_subject = "Conferma di avvenuto inserimento del percorso km";

            $btn_send = "

                <body>

                    <section>

                        <div style='padding-bottom: 15px;'>

                            Gentile utente,

                        </div>

                        <div style='padding-bottom: 15px;'>
                        

                            Le confermiamo la ricezione dei dati inseriti. Per confermarli, le chiediamo di cliccare sul seguente link:
                            
                            <a style='padding: 20px 0px 20px;' href='{$URL_SERVER}?auth_token={$auth_token_id}' >
                                CONFERMA DATI
                            </a>

                        </div>

                        <div>
                            Buona giornata.
                        </div>

                    </section>

                </body>
            ";

            return $this->mail->send_mail($recipient_address, $email_subject, $btn_send);

        }

    }



?>