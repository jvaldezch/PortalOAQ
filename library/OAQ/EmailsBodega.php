<?php

require_once "PHPMailerAutoload.php";

/**
 * Envio de emails que usa plantilla que se leen basadas en DOM parsing
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class OAQ_EmailsBodega {

    protected $mail;
    protected $view;
    protected $subject;
    protected $to;
    protected $cc;
    protected $bcc;

    function setSubject($subject) {
        if (APPLICATION_ENV == "production") {
            $this->mail->Subject = $subject;
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
            $this->mail->Host = "mail.oaq.com.mx";
            $this->mail->SMTPAuth = true;
            $this->mail->Username = "arribosynotificaciones@oaq.com.mx";
            $this->mail->Password = '4rrib0$yn0tifica#$';
            $this->mail->Port = 26;
            $this->mail->From = "arribosynotificaciones@oaq.com.mx";
            $this->mail->FromName = "Notificaciones OAQ";
            $this->mail->isHTML(true);
        } catch (Exception $ex) {
            throw new Exception("Exception found", (string) $ex->getMessage(), "localhost", "RabbitMQ");
        }
    }

    public function addAttachment($filename, $name = null) {
        if ($name) {
            $this->mail->AddAttachment($filename, $name, 'base64', 'application/pdf');
        } else {
            $this->mail->AddAttachment($filename, basename($filename), 'base64', 'application/pdf');
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
            $this->mail->Send();
            return true;
        } catch (Exception $ex) {
            throw new Exception("Exception found", (string) $ex->getMessage(), "localhost", "RabbitMQ");
        }
    }

    public function notificacion($comentario) {
        try {
            $this->view->message = $comentario;
            $this->mail->Body = $this->view->render("notificacion.phtml");
        } catch (Exception $ex) {
            throw new Exception("Exception found", (string) $ex->getMessage(), "localhost", "RabbitMQ");
        }
    }

}
