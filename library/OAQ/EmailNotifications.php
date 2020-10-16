<?php

/**
 * Envio de emails que usa plantilla que se leen basadas en DOM parsing
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class OAQ_EmailNotifications
{

    protected $_smtp;
    protected $_user;
    protected $_name;
    protected $_pass;
    protected $_html;
    protected $_config;
    protected $_transport;
    protected $_email;

    public function __construct($email = null, $username = null, $name = null)
    {
        try {
            if (isset($email) && isset($username) && isset($name)) {
                $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
                if (!$email && !$username) {
                    $this->_smtp = "mail.oaq.com.mx";
                    $this->_user = "cobranza@oaq.com.mx";
                    $this->_pass = "Cobr4nz#0";
                } else if ($email && $username) {
                    $emails = new Application_Model_UserEmailsMapper();
                    $userEmail = $emails->getUserEmailCredentials($username, $email);
                    $this->_smtp = $userEmail['smtp'];
                    $this->_user = $email;
                    $this->_pass = $userEmail['password'];
                    $this->_name = $name;
                    $this->_name = $email;
                    unset($email);
                    unset($userEmail);
                }
                $config = array('auth' => 'login',
                    'username' => $this->_user,
                    'password' => $this->_pass,
                    'port' => 26);
                $this->_transport = new Zend_Mail_Transport_Smtp($this->_smtp, $config);
            }
        } catch (Exception $e) {
            throw new Exception("Exception found while creating email transport" . $e->getMessage());
        }
    }

    public function sendForgotPasswordEmail($username, $password, $email)
    {
        try {
            require_once 'simple_html_dom.php';
            $this->_html = file_get_html(APPLICATION_PATH . '/../library/OAQ/Templates/password.html');
            $this->_html->find('span[id=username]', 0)->innertext = $username;
            $this->_html->find('span[id=password]', 0)->innertext = $password;
            $this->sendEmail($email, 'Portal OAQ', 'Su contraseña de portal');
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function sendUpdatedPasswordEmail($username, $password, $email)
    {
        try {
            require_once 'simple_html_dom.php';
            $this->_html = file_get_html(APPLICATION_PATH . '/../library/OAQ/Templates/passwordUpdate.html');
            $this->_html->find('span[id=username]', 0)->innertext = $username;
            $this->_html->find('span[id=password]', 0)->innertext = $password;
            $this->sendEmail($email, 'Portal OAQ', 'Su contraseña de portal ha sido actualizada');
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    protected function sendEmail($email, $from, $subject)
    {
        try {
            if (APPLICATION_ENV == 'development') {
                $subject = "[DEV] " . $subject;
            }
            $mailSend = new Zend_Mail("UTF-8");
            $mailSend->setBodyHtml($this->_html)
                ->setFrom($this->_user, $from)
                ->addTo($email)
                ->setReplyTo("no-responder@" . "localhost")
                ->setSubject($subject);
            $mailSend->send($this->_transport);
        } catch (Exception $e) {
            throw new Exception("Exception found while sending email" . $e->getMessage());
        }
    }

    protected function sendEmailAndAttach($email, $name, $from, $subject, $filepath, $filename)
    {
        try {
            if (APPLICATION_ENV == 'development') {
                $subject = "[DEV] " . $subject;
            }
            $mailSend = new Zend_Mail('UTF-8');
            $mailSend->setBodyHtml($this->_html)
                ->setFrom($this->_user, isset($this->_name) ? $this->_name : $from)
                ->addTo($email, $name)
                ->addBcc($this->_user)
                ->setReplyTo('no-responder@oaq.com.mx')
                ->setSubject($subject);
            $attach = new Zend_Mime_Part(file_get_contents($filepath));
            $attach->type = 'application/octet-stream';
            $attach->disposition = Zend_Mime::DISPOSITION_INLINE;
            $attach->encoding = Zend_Mime::ENCODING_BASE64;
            $attach->filename = $filename;
            $mailSend->addAttachment($attach);
            $mailSend->send($this->_transport);
        } catch (Zend_Mail_Exception $e) {
            throw new Exception("Exception found while sending email at " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function sendEmailWithAttachment($emails, $filename, $filepath, $subject, $template)
    {
        try {
            require_once 'simple_html_dom.php';
            $this->_html = file_get_html(APPLICATION_PATH . '/../library/OAQ/Templates/' . $template);
            foreach ($emails as $item) {
                $this->_html->find('span[id=name]', 0)->innertext = $item['nombre'];
                $this->sendEmailAndAttach($item['email'], $item['nombre'], 'Programación de cobranza', $subject, $filepath, $filename);
            }
        } catch (Exception $e) {
            throw new Exception("Exception found while sending email at " . __METHOD__ . ": " . $e->getMessage() . $e->getLine());
        }
    }

    public function sendInfraEmail($subject, $body)
    {
        try {
            if (APPLICATION_ENV == "development") {
                $subject = "[DEV] " . $subject;
            }
            $mailSend = new Zend_Mail('UTF-8');
            $mailSend->setBodyHtml($body)
                ->setFrom($this->_config->app->infra->email)
                ->addTo("sistemas@oaq.com.mx", "Vianey Noya")
                ->addCc("jvaldezch@gmail.com", "Jaime E. Valdez")
                ->setReplyTo('no-responder@oaq.com.mx')
                ->setSubject($subject);
            $config = array('auth' => 'login',
                'username' => $this->_config->app->infra->email,
                'password' => $this->_config->app->infra->pass,
                'port' => 26);
            $this->_transport = new Zend_Mail_Transport_Smtp($this->_config->app->infra->smtp, $config);
            $mailSend->send($this->_transport);
        } catch (Exception $e) {
            throw new Exception('<b>Exception found while sending email at ' . __METHOD__ . '</b>' . $e->getMessage() . $e->getLine());
        }
    }

    public function nuevaNotificacion($idAduana, $pedimento, $referencia, $de, $para, $mensaje, $tipo, $idTrafico = null)
    {
        $ns = new Trafico_Model_NotificacionesMapper();
        $id = $ns->agregar(array(
            "idAduana" => $idAduana,
            "idTrafico" => $idTrafico,
            "contenido" => $mensaje,
            "pedimento" => $pedimento,
            "referencia" => $referencia,
            "de" => $de,
            "para" => $para,
            "tipo" => $tipo,
            "estatus" => null,
            "creado" => date("Y-m-d H:i:s"),
        ));
        if ($id) {
            $sender = new OAQ_WorkerSender("emails");
            $sender->enviarEmail($id);
            $misc = new OAQ_Misc();
            $misc->execCurl("enviar-email");
            return $id;
        }
        return null;
    }

}
