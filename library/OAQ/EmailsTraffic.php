<?php

require_once "PHPMailerAutoload.php";

/**
 * Envio de emails que usa plantilla que se leen basadas en DOM parsing
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class OAQ_EmailsTraffic {

    protected $mail;
    protected $view;
    protected $subject;
    protected $to;
    protected $cc;
    protected $bcc;
    protected $log;
    protected $appconfig;

    function setSubject($subject) {
        if(APPLICATION_ENV == "production") {
            $this->mail->Subject = $subject;
        } else if (APPLICATION_ENV == "staging") {
            $this->mail->Subject = "[STAGE] " . $subject;            
        } else {
            $this->mail->Subject = "[DEV] " . $subject;            
        }
    }

    function setTo($to) {
        $this->to = $to;
    }

    function setCc($cc) {
        $this->cc = $cc;
    }

    function setBcc($bcc) {
        $this->bcc = $bcc;
    }

    function __construct() {
        try {
            $this->appconfig = new Application_Model_ConfigMapper();
            $this->log = new Application_Model_LogMapper();
            $this->view = new Zend_View();
            $this->view->setScriptPath(__DIR__ . "/../Templates/");
            $this->mail = new PHPMailer;
            $this->mail->SMTPOptions = array(
                "ssl" => array(
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                    "allow_self_signed" => true
                )
            );
            $this->mail->CharSet = "UTF-8";
            $this->mail->AddReplyTo("no-responder@oaq.com.mx", "No responder");
            $this->mail->MailerDebug = true;
            $this->mail->isSMTP();
            $this->mail->SMTPSecure = 'tls';
            $this->mail->Host = $this->appconfig->getParam('arribosServer');
            $this->mail->SMTPAuth = true;
            $this->mail->Username = $this->appconfig->getParam('arribosEmail');
            $this->mail->Password = $this->appconfig->getParam('arribosPass');
            $this->mail->Port = 26;
            $this->mail->From = $this->appconfig->getParam('arribosEmail');
            $this->mail->FromName = "Notificaciones OAQ";
            $this->mail->isHTML(true);
        } catch (Exception $ex) {
            $this->log->logEntry("Exception found", (string) $ex->getMessage(), "localhost", "RabbitMQ");
        }
    }

    public function addAttachment($filename, $name = null) {
        if ($name) {
            $this->mail->AddAttachment($filename, $name,  'base64', 'application/pdf');
        } else {
            $this->mail->AddAttachment($filename, basename($filename),  'base64', 'application/pdf');
        }
    }
    
    public function addTo($email, $nombre) {
        $this->mail->addAddress($email, $nombre);
    }
    
    public function addCc($email, $nombre = null) {
        $this->mail->addCC($email, $nombre);
    }
    
    public function addBcc($email, $nombre = null) {
        $this->mail->addBCC($email, $nombre);
    }

    public function send() {
        try {
            if ($this->mail->Send()) {
                return true;
            }
            return null;
        } catch (Exception $ex) {
            $this->log->logEntry("Exception found", (string) $ex->getMessage(), "localhost", "RabbitMQ");
        }
    }

    public function avisoComentario($comentario) {
        try {
            $this->view->message = $comentario;
            $this->mail->Body = $this->view->render("aviso-comentario.phtml");
        } catch (Exception $ex) {
            $this->log->logEntry("Exception found", (string) $ex->getMessage(), "localhost", "RabbitMQ");
        }
    }
    
    public function avisoDeposito($comentario) {
        try {
            $this->view->message = $comentario;
            $this->mail->Body = $this->view->render("aviso-deposito.phtml");
        } catch (Exception $ex) {
            $this->log->logEntry("Exception found", (string) $ex->getMessage(), "localhost", "RabbitMQ");
        }
    }
    
    public function nuevaSolicitud($comentario) {
        try {
            $this->view->message = $comentario;
            $this->mail->Body = $this->view->render("nueva-solicitud.phtml");
        } catch (Exception $ex) {
            $this->log->logEntry("Exception found", (string) $ex->getMessage(), "localhost", "RabbitMQ");
        }
    }
    
    public function contenidoPersonalizado($html) {
        $this->mail->Body = $html;
    }
    
    public function pagoPedimento($patente, $aduana, $pedimento, $referencia) {
        try {
            $this->view->message = "SE NOTIFICA QUE SE HA REALIZADO EL PAGO DEL PEDIMENTO:";
            $this->view->patente = $patente;
            $this->view->aduana = $aduana;
            $this->view->pedimento = $pedimento;
            $this->view->referencia = $referencia;
            $this->mail->Body = $this->view->render("pago-pedimento.phtml");
        } catch (Exception $ex) {
            $this->log->logEntry("Exception found", (string) $ex->getMessage(), "localhost", "RabbitMQ");
        }
    }

}
