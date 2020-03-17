   <?php

class Automatizacion_TerminalController extends Zend_Controller_Action {

    protected $_config;
    protected $_appconfig;
    protected $_db;
    protected $_sat;
    protected $_session;

    public function init() {
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $this->_sat = new OAQ_SATValidar();
        $this->_db = Zend_Registry::get("oaqintranet");
    }
    
    public function preDispatch() {
        $this->_session = NULL ? $this->_session = new Zend_Session_Namespace("") : $this->_session = new Zend_Session_Namespace($this->_config->app->namespace);
        if ($this->_session->authenticated == true) {
            $session = new OAQ_Session($this->_session, $this->_appconfig);
            $session->actualizar();
            $session->actualizarSesion();
        }
    }

    public function analizarAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
            );
            $v = array(
                "ids" => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("ids")) {
                $arr = explode(',', $input->ids);
                if (!empty($arr)) {
                    $terminal = new OAQ_TerminalLogistics();
                    $guias = array();
                    foreach ($arr as $item) {
                        $guias[] = $terminal->analizar($item);
                    }
                    $this->_helper->json(array("success" => true, "guias" => $guias));
                }
                $this->_helper->json(array("success" => false));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function borrarRepositorioAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
            );
            $v = array(
                "ids" => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("ids")) {
                $arr = explode(',', $input->ids);
                if (!empty($arr)) {
                    $terminal = new OAQ_TerminalLogistics();
                    $terminal->set_usuario($this->_session->username);
                    foreach ($arr as $id) {
                        $terminal->borrarDeTemporal($id);
                    }
                    $this->_helper->json(array("success" => true));
                }
                $this->_helper->json(array("success" => false));
            }
        } catch (Zend_Exception $e) {
            throw new Zend_Exception($e->getMessage());
        }
    }
    
    public function enviarRepositorioAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
            );
            $v = array(
                "ids" => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("ids")) {
                $arr = explode(',', $input->ids);
                if (!empty($arr)) {
                    $terminal = new OAQ_TerminalLogistics();
                    $terminal->set_usuario($this->_session->username);
                    foreach ($arr as $id) {
                        $terminal->enviarARepositorio($id);
                    }
                    $this->_helper->json(array("success" => true));
                }
                $this->_helper->json(array("success" => false));
            }
        } catch (Zend_Exception $e) {
            throw new Zend_Exception($e->getMessage());
        }
    }
    
    public function arribosAction() {
        try {
            $imap = new OAQ_IMAP($this->appconfig->getParam('arribosServer'), $this->appconfig->getParam('arribosEmail'), $this->appconfig->getParam('arribosPass'), 'INBOX');
            $numMessages = $imap->getNumMessages();
            $directory = $this->_appconfig->getParam("arribosDir") . DIRECTORY_SEPARATOR . date("Y") . DIRECTORY_SEPARATOR . date("m") . DIRECTORY_SEPARATOR . date("d");
            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }
            $emailProcessing = new OAQ_Emails();
            $emailProcessing->set_dir($directory);
            for ($i = 1; $i <= $numMessages; $i++) {
                $header = $imap->getHeader($i);
                if ($header == false) {
                    continue;
                }
                $details = $imap->getDetails($header);
                $uid = $imap->getUid($i);
                if (in_array($details["fromAddr"], array("aviso@tdqro.com", "Mailer-Daemon@vpsmx.oaq.com.mx", "moiseslogistic@yahoo.com", "Maribel.Cuevas@gbrx.com"))) {
                    $imap->deleteMessage($uid);
                    continue;
                }
                if (preg_match('/notificaciones@terminal.com.mx/i', $details["fromAddr"])) {
                    $emailProcessing->findParts($imap, $i);
                    $attachments = $emailProcessing->get_attachments();
                    if (!empty($attachments)) {
                        foreach ($attachments as $attachment) {
                            $emailProcessing->analizarArriboTerminal($directory . DIRECTORY_SEPARATOR . $attachment);
                        }
                    }
                }
                if (preg_match('/proexi@aduanas-mexico.com/i', $details["fromAddr"])) {
                    $emailProcessing->findParts($imap, $i);
                    $attachments = $emailProcessing->get_attachments();
                    if (!empty($attachments)) {
                        foreach ($attachments as $attachment) {
                            $emailProcessing->analizarArchivoProexi($directory . DIRECTORY_SEPARATOR . $attachment, $imap->getImapBody($i));
                        }
                    }
                }
                $datediff = (time() - $details["udate"]) / (60 * 60 * 24);
                if (floor($datediff) > 15) {
                    $imap->deleteMessage($uid);
                }
            }
            $imap->expunge();
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    /**
     * /automatizacion/terminal/terminal
     * 
     */
    public function terminalAction() {
        try {
            $mppr = new Automatizacion_Model_EmailsLeidos();
            $terminal = new OAQ_TerminalLogistics();
            $emailProcessing = new OAQ_Emails();
            $emailProcessing->set_validFrom(array('aviso@terlog.mx', 'aviso@tlog.mx', 'oaqsoporte@gmail.com'));
            $emailProcessing->set_validSubject(array('Facturas Terminal Logistics'));
            
            if (APPLICATION_ENV == "production" || APPLICATION_ENV == "staging") {
                $directory = $this->_appconfig->getParam("terminalDir") . DIRECTORY_SEPARATOR . date("Y") . DIRECTORY_SEPARATOR . date("m") . DIRECTORY_SEPARATOR . date("d");
            } else {
                $directory = "D:\\xampp\\tmp\\terminal" . DIRECTORY_SEPARATOR . date("Y") . DIRECTORY_SEPARATOR . date("m") . DIRECTORY_SEPARATOR . date("d");                
            }
            
            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }
            $emailProcessing->set_dir($directory);
            
            $imap = new OAQ_IMAP($this->appconfig->getParam('terminalServer'), $this->appconfig->getParam('terminalEmail'), $this->appconfig->getParam('terminalPass'), 'INBOX');
            $numMessages = $imap->getNumMessages();
            $unreaded = 0;
            $notvalid = 0;
            $emails = array();
            $j = 0;
            for ($i = 1; $i <= $numMessages; $i++) {
                $header = $imap->getHeader($i);
                if ($header == false) {
                    $unreaded++;
                    continue;
                }
                $fromInfo = $header->from[0];
                $replyInfo = $header->reply_to[0];
                $details = array(
                    "fromAddr" => (isset($fromInfo->mailbox) && isset($fromInfo->host)) ? $fromInfo->mailbox . "@" . $fromInfo->host : "",
                    "fromName" => (isset($fromInfo->personal)) ? $fromInfo->personal : "",
                    "replyAddr" => (isset($replyInfo->mailbox) && isset($replyInfo->host)) ? $replyInfo->mailbox . "@" . $replyInfo->host : "",
                    "replyName" => (isset($replyInfo->personal)) ? $replyInfo->personal : "",
                    "subject" => (isset($header->subject)) ? $header->subject : "",
                    "udate" => (isset($header->udate)) ? $header->udate : ""
                );
                $uid = $imap->getUid($i);
                $subject = imap_mime_header_decode($details["subject"]);
                $temail = array();
                $temail["from"] = $details["fromAddr"];
                if (!$mppr->verificar($uid, date('Y-m-d', strtotime($header->MailDate)))) {
                    $mppr->agregar(array(
                        'idEmail' => $uid,
                        'de' => $details["fromAddr"],
                        'fecha' => date('Y-m-d', strtotime($header->MailDate)),
                        'hora' => date('H:i:s', strtotime($header->MailDate)),
                        'asunto' => $subject[0]->text,
                        'creado' => date('Y-m-d H:i:s'),
                    ));
                    $temail["added"] = true;
                } else {
                    continue;
                }
                if (($res = $emailProcessing->isValid($details["fromAddr"], $subject[0]->text)) === true) {
                    $temail["attachments"] = true;
                    $emailProcessing->findParts($imap, $i);
                    $attachments = $emailProcessing->get_attachments();
                    if (!empty($attachments)) {
                        foreach ($attachments as $item) {
                            $terminal->set_filename($directory . DIRECTORY_SEPARATOR . $item);
                            $terminal->set_idEmail($uid);
                            $terminal->verificarArchivo();
                        }
                    } else {
                        $temail["attachments"] = false;
                    }
                    if (APPLICATION_ENV == "production" || APPLICATION_ENV == "staging") {
                        $output = array();
                        $cmd = "wget --no-check-certificate -O - https://127.0.0.1/automatizacion/terminal/terminal-analizar > /dev/null &";
                        exec($cmd, $output);
                    }
                } else {
                    $temail["error"] = $res;
                    $notvalid++;
                }
                $emails[] = $temail;
                $imap->imapPing();
            }
            $this->_helper->json(array(
                "success" => false,
                "numMessages" => $numMessages,
                "unReaded" => $unreaded,
                "notValid" => $notvalid,
                "emails" => $emails,
            ));
            //$imap->close();
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
    /**
     * /automatizacion/terminal/terminal-gmail
     * 
     */
    public function terminalGmailAction() {
        set_time_limit(0);
        date_default_timezone_set('America/Mexico_City');
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "limit" => "Digits",
            );
            $v = array(
                "limit" => array(new Zend_Validate_Int()),
                "fecha" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/"), "NotEmpty", "default" => date("Y-m-d")),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            
            $mppr = new Automatizacion_Model_EmailsLeidos();
            $terminal = new OAQ_TerminalLogistics();
            $emailProcessing = new OAQ_Emails();
            $emailProcessing->set_validFrom(array('aviso@terlog.mx', 'aviso@tlog.mx'));
            $emailProcessing->set_validSubject(array('Facturas Terminal Logistics'));
            
            if (APPLICATION_ENV == "production") {
                $base_dir = $this->_appconfig->getParam("terminalDir");
            } else {
                $base_dir = "D:\\xampp\\tmp\\terminal";
            }
            
            $fecha = date("d F Y", strtotime($input->fecha));
            
            $imap = new OAQ_Gmail('oaqsoporte@gmail.com', 'T3chn0l0gy');
            
            $emails_to_read = $imap->search('SINCE "' . $fecha . '"');
            
            $unreaded = 0;
            $emails = array();
            
            if (!empty($emails_to_read)) {
                foreach ($emails_to_read as $k => $v) {
                    $header = $imap->getHeader($v);
                    if ($header == false) {
                        $unreaded++;
                        continue;
                    }
                    $fromInfo = $header->from[0];
                    $replyInfo = $header->reply_to[0];
                    
                    $details = array(
                        "fromAddr" => (isset($fromInfo->mailbox) && isset($fromInfo->host)) ? $fromInfo->mailbox . "@" . $fromInfo->host : "",
                        "fromName" => (isset($fromInfo->personal)) ? $fromInfo->personal : "",
                        "replyAddr" => (isset($replyInfo->mailbox) && isset($replyInfo->host)) ? $replyInfo->mailbox . "@" . $replyInfo->host : "",
                        "replyName" => (isset($replyInfo->personal)) ? $replyInfo->personal : "",
                        "subject" => (isset($header->subject)) ? $header->subject : "",
                        "udate" => (isset($header->udate)) ? $header->udate : ""
                    );
                    
                    $uid = $imap->getUid($v);
                    $subject = imap_mime_header_decode($details["subject"]);
                    $temail = array();
                    $temail["from"] = $details["fromAddr"];
                    
                    if (!$mppr->verificar($uid, date('Y-m-d', $header->udate))) {
                        $mppr->agregar(array(
                            'idEmail' => $uid,
                            'toEmail' => 'oaqsoporte@gmail.com',
                            'de' => $details["fromAddr"],
                            'fecha' => date('Y-m-d', $header->udate),
                            'hora' => date('H:i:s', $header->udate),
                            'asunto' => $subject[0]->text,
                            'creado' => date('Y-m-d H:i:s'),
                        ));
                        $temail["added"] = true;
                    } else {
                        continue;
                    }
                    
                    if (($res = $emailProcessing->isValid($details["fromAddr"], $subject[0]->text)) === true) {
                        
                        $directory = $terminal->createDir($base_dir, $header->udate);
                        $emailProcessing->set_dir($directory);
                        
                        $emailProcessing->findPartsGmail($imap, $v);
                        $attachments = $emailProcessing->get_attachments();
                        
                        $temail["subject"] = $subject[0]->text;
                        $temail["attachments"] = count($attachments);
                        
                        if (!empty($attachments)) {
                            foreach ($attachments as $item) {
                                $terminal->set_filename($directory . DIRECTORY_SEPARATOR . $item);
                                $terminal->set_idEmail($uid);
                                $terminal->verificarArchivo(date('Y-m-d', $header->udate));
                            }
                            $emails[] = $temail;
                        }
                    }
                    
                }
            }
            $this->_helper->json(array(
                "success" => true,
                "numMessages" => count($emails_to_read),
                "unReaded" => $unreaded,
                "emails" => $emails,
            ));
            $imap->close();
            
            if (APPLICATION_ENV == "production") {
                $output = array();
                $cmd = "wget --no-check-certificate -O - https://127.0.0.1/automatizacion/terminal/terminal-analizar > /dev/null &";
                exec($cmd, $output);
            }
            
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    /**
     * /automatizacion/terminal/terminal-analizar?limit=10
     * 
     * @throws Exception
     */
    public function terminalAnalizarAction() {
        try {
            $f = array(
                "limit" => array("StringTrim", "StripTags", "Digits"),
                "id" => array("StringTrim", "StripTags", "Digits"),
            );
            $v = array(
                "limit" => array("NotEmpty", new Zend_Validate_Int(), "default" => 100),
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            $terminal = new OAQ_TerminalLogistics();
            $terminal->analizarPendientes($input->limit, $input->id);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

}
