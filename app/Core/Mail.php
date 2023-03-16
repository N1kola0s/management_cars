<?php
namespace App\app\Core;
use Exception;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\PHPMailer;

//carica file config dell'email;
require __DIR__ . '../../../config/config_mail.php';
//carica Composer's autoloader;
require '../../vendor/autoload.php';

class Mail
{

    protected $mail;
    protected $sender_address = "noreply-dev@servizi.digital";
    protected $sender_name = "Gestione Flotte";

    public function __construct(){

        $this->mail = new PHPMailer(true);
        $this->mail->isSMTP();
        $this->mail->SMTPAuth   = true;
        //$this->mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $this->mail->Host = EMAIL_HOST;
        $this->mail->Username = EMAIL_USERNAME;
        $this->mail->Password = EMAIL_PASSWORD;
        $this->mail->Port = EMAIL_PORT;
        $this->sender_address = EMAIL_FROM;
    }


    /**
     * Metodo Invio della mail
     *
     * @param string $recipient_address Email del destinatario.
     * @param string $subject Oggetto dell'Email.
     * @param string $body Corpo della Email.
     * @return bool|void Invio Email
     */
    public function send_mail(string $recipient_address, string $subject, string $body) {
        try{
            $this->mail->setFrom($this->sender_address, $this->sender_name);
            $this->mail->addAddress($recipient_address);
            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;

            //metodo invio della mail;
            if(!$this->mail->send()){
                //eccezione in caso di errore invio email;
                throw new Exception("Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}");

            }

        } catch (Exception $e) {
            $arr ['success'] = FALSE;
            $arr['message'] = $e->getMessage();
            
        }

    }

}




?>