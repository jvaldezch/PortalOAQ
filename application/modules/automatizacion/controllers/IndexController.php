<?php

class Automatizacion_IndexController extends Zend_Controller_Action {

    protected $_config;
    protected $_emailsNotif;
    protected $_emailsPedimentos;
    protected $_emailStorage;
    protected $_transportSupport;
    protected $_log;
    protected $_emailExceptions;
    protected $_notifMapper;
    protected $_pedMapper;
    protected $_db;
    protected $_conn;
    protected $_localDir;
    protected $_remoteDir;

    public function init() {
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
        $config = array('auth' => 'login',
            'username' => $this->_config->app->infra->email,
            'password' => $this->_config->app->infra->pass,
            'port' => 26);
        $this->_transportSupport = new Zend_Mail_Transport_Smtp($this->_config->app->infra->smtp, $config);
        ini_set("soap.wsdl_cache_enabled", 0);
    }

    protected function mailStorage($tipo) {
        try {
            if ($tipo == 'notificaciones') {
                $this->_emailStorage = new Zend_Mail_Storage_Imap(array(
                    'host' => $this->_config->app->notificaciones->smtp,
                    'user' => $this->_config->app->notificaciones->email,
                    'password' => $this->_config->app->notificaciones->pass,
                ));
            } else if ($tipo == 'pedimentos') {
                $this->_emailStorage = new Zend_Mail_Storage_Imap(array(
                    'host' => $this->_config->app->pedimento->smtp,
                    'user' => $this->_config->app->pedimento->email,
                    'password' => $this->_config->app->pedimento->pass,
                ));
            } else if ($tipo == 'facturas') {
                $this->_emailStorage = new Zend_Mail_Storage_Imap(array(
                    'host' => $this->_config->app->facturas->smtp,
                    'user' => $this->_config->app->facturas->email,
                    'password' => $this->_config->app->facturas->pass,
                ));
            } else if ($tipo == 'infraestructura') {
                $this->_emailStorage = new Zend_Mail_Storage_Imap(array(
                    'host' => $this->_config->app->infra->smtp,
                    'user' => $this->_config->app->infra->email,
                    'password' => $this->_config->app->infra->pass,
                ));
                return $this->_config->app->infra->email;
            }
        } catch (Exception $e) {
            echo "<p><b>IMAP storage exception:</b> {$e->getMessage()}</p>";
        }
    }

    public function indexAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $domtree = new DOMDocument('1.0', 'UTF-8');
        $xmlRoot = $domtree->appendChild($domtree->createElement("emails"));

        $erased = $domtree->createElement("borrados");
        $erased->setAttribute('cantidad', 5);
        $erasedEmails = $xmlRoot->appendChild($erased);

        $emails = $domtree->createElement("email");
        $emails->setAttribute('id', 0);
        $emailList = $erasedEmails->appendChild($emails);
        $emailList->appendChild($domtree->createElement('subject', 'any'));
        $emailList->appendChild($domtree->createElement('from', 'any@any'));
        $output = $domtree->saveXML();
        Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoRender(true);
        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_response->setHeader('Content-Type', 'text/xml; charset=utf-8')
                ->setBody($output);
    }

    public function spamAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $storages = array(
            'notificaciones',
        );
        try {
            foreach ($storages as $storage):
                $this->mailStorage($storage);
                $del = array();
                $readed = 0;
                foreach ($this->_emailStorage as $msgId => $message) {
                    if ($message->hasFlag(Zend_Mail_Storage::FLAG_DELETED)) {
                        $del[] = array(
                            'id' => $msgId,
                            'email' => $message->from,
                            'subject' => 'Item marked for deletion',
                        );
                        continue;
                    }

                    switch ($message->from) {
                        case preg_match('/notificaciones@terminal.com.mx/i', $message->from):
                            $del[] = array(
                                'id' => $msgId,
                                'email' => $message->from,
                                'subject' => (String) $message->subject,
                            );
                            break;
                        case preg_match('/pedimentos.pagados@grupoproexi.com/i', $message->from):
                            $del[] = array(
                                'id' => $msgId,
                                'email' => $message->from,
                                'subject' => (String) $message->subject,
                            );
                            break;
                        case preg_match('/avisos.vrz@grupoproexi.com/i', $message->from):
                            $del[] = array(
                                'id' => $msgId,
                                'email' => $message->from,
                                'subject' => (String) $message->subject,
                            );
                            break;
                        case preg_match('/sistemas@coinsar.com.mx/i', $message->from):
                            $del[] = array(
                                'id' => $msgId,
                                'email' => $message->from,
                                'subject' => (String) $message->subject,
                            );
                            break;
                        case preg_match('/lruiz@oaq.com.mx/i', $message->from):
                            $del[] = array(
                                'id' => $msgId,
                                'email' => $message->from,
                                'subject' => (String) $message->subject,
                            );
                            break;
                        case preg_match('/impo01@tamex.com.mx/i', $message->from):
                            $del[] = array(
                                'id' => $msgId,
                                'email' => $message->from,
                                'subject' => (String) $message->subject,
                            );
                            break;
                        case preg_match('/trafico@oaq.com.mx/i', $message->from):
                            $del[] = array(
                                'id' => $msgId,
                                'email' => $message->from,
                                'subject' => (String) $message->subject,
                            );
                            break;
                        case preg_match('/trafico2@oaq.com.mx/i', $message->from):
                            $del[] = array(
                                'id' => $msgId,
                                'email' => $message->from,
                                'subject' => (String) $message->subject,
                            );
                            break;
                        case preg_match('/aviso@tdqro.com/i', $message->from):
                            $del[] = array(
                                'id' => $msgId,
                                'email' => $message->from,
                                'subject' => (String) $message->subject,
                            );
                            break;
                    }
                    $readed++;
                    if ($readed == 150) {
                        break;
                    }
                }
            endforeach;

            $delete = array();
            foreach ($del as $borrar) {
                $delete[$borrar['id']] = $borrar['id'];
            }
            if (!empty($delete)) {
                $this->removeEmails($delete, 'notificaciones');
            }

            $domtree = new DOMDocument('1.0', 'UTF-8');
            $xmlRoot = $domtree->appendChild($domtree->createElement("emails"));

            $erased = $domtree->createElement("borrados");
            $erased->setAttribute('cantidad', count($del));
            $erased->setAttribute('contenedor', $storage);
            $erasedEmails = $xmlRoot->appendChild($erased);

            foreach ($del as $correo) {
                $emails = $domtree->createElement("email");
                $emails->setAttribute('id', $correo['id']);
                $emailList = $erasedEmails->appendChild($emails);
                $emailList->appendChild($domtree->createElement('subject', utf8_encode($correo['subject'])));
                $emailList->appendChild($domtree->createElement('from', $correo['email']));
            }
            $output = $domtree->saveXML();
            Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoRender(true);
            Zend_Layout::getMvcInstance()->disableLayout();
            $this->_response->setHeader('Content-Type', 'text/xml; charset=utf-8')
                    ->setBody($output);
        } catch (Exception $e) {
            echo "<p><b>SPAM removing exception:</b> {$e->getMessage()}</p>";
        }
    }

    protected function removeEmails($rmvIds) {
        $erase = true;
        if ($erase == true) {
            try {
                if (!empty($rmvIds)):
                    if ($rmvIds):
                        $idx = 0;
                        foreach ($rmvIds as $id):
                            if (is_int($id)) {
                                try {
                                    $this->_emailStorage->removeMessage($id - $idx);
                                    $idx++;
                                } catch (Zend_Mail_Storage_Exception $e) {
                                    $this->_log->errorLog($e->getMessage(), 'cron');
                                }
                            }
                            usleep(500000); // 0.5 seconds
                        endforeach;
                    endif;
                endif;
            } catch (Exception $e) {
                $this->_log->errorLog('Error removing mails ' . $e->getMessage(), 'cron');
            }
        }
    }

    protected function moveEmailsToFolder($mvIds, $folder) {
        try {
            if (!empty($mvIds)):
                if ($mvIds):
                    $idx = 0;
                    foreach ($mvIds as $id):
                        if (is_int($id["id"])) {
                            try {
                                if ($this->folderExists($this->_emailStorage, $folder)) {
                                    $this->_emailStorage->moveMessage($id["id"] - $idx, $folder);
                                }
                                $idx++;
                            } catch (Zend_Mail_Storage_Exception $e) {
                                echo "<b>Exception on " . __METHOD__ . "</b> " . $e->getMessage() . Zend_Debug::dump($folder) . Zend_debug::dump($id) . Zend_Debug::dump($this->_emailStorage);
                                die();
                            }
                        }
                        usleep(500000); // 0.5 seconds
                    endforeach;
                endif;
            endif;
        } catch (Exception $e) {
            echo "<b>Exception on " . __METHOD__ . "</b> " . $e->getMessage();
            die();
        }
    }

    protected function folderExists(Zend_Mail_Storage_Imap $imapObj, $folder) {
        try {
            $imapObj->selectFolder($folder);
        } catch (Zend_Mail_Storage_Exception $e) {
            return false;
        }
        return true;
    }

    public function facturasAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        // https://187.188.159.44/automatizacion/index/facturas
        echo "<style>
                body {
                  font-size: 12px;
                  font-family: sans-serif;
                }
                ul {
                  margin: 0;
                  padding: 0;
                  margin-bottom: 10px;
                  border: 1px #999 solid;
                  background: #f4f4f4;
                  padding:5px;
                }
                ul li {
                  list-style: none;
                  margin: 0;
                  padding: 0;
                }
            </style>";

        $imap = new OAQ_IMAP($this->_config->app->facturas->smtp, $this->_config->app->facturas->email, $this->_config->app->facturas->pass, 'INBOX');
        $sat = new OAQ_SATValidar();
        $repo = new Archivo_Model_RepositorioMapper();

        $folders = $imap->getFolders();
        echo "<ul>";
        echo "<li><strong>Folders</strong></li>";
        foreach ($folders as $folder) {
            echo '<li>' . imap_utf7_decode($folder) . '</li>';
        }
        echo "</ul>";

        $numMessages = $imap->getNumMessages();
        for ($i = 1; $i <= $numMessages; $i++) {
            $header = $imap->getHeader($i);
            if ($header == false) {
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
            $mailStruct = $imap->getStructure($i);
            $attachments = $imap->getAttachments($i, $mailStruct, "");

            echo "<ul>";
            echo "<li><strong>Uid:</strong> " . $uid . "</li>";
            echo "<li><strong>Id:</strong> " . $i . "</li>";
            echo "<li><strong>From:</strong> " . $details["fromAddr"] . "</li>";
            echo "<li><strong>Subject:</strong> " . $details["subject"] . "</li>";
            echo "<li><strong>Flag:</strong> ";
            echo ($header->Unseen == "U") ? "unreadMsg" : "readMsg" . '</li>';
            if (!empty($attachments)) {
                foreach ($attachments as $k => $attach) {
                    echo "<li><strong>PartNum</strong> " . $attach["partNum"] . " <strong>Encode</strong> " . $attach["enc"] . "    <strong>Attachment: </strong> " . $attach["name"] . "</li>";
                    if (preg_match('/notificaciones@terminal.com.mx/i', $details["fromAddr"]) || preg_match('/ana.arteaga@terminal.com.mx/i', $details["fromAddr"]) || preg_match('/guadalupe.munoz@terminal.com.mx/i', $details["fromAddr"])) {
                        if (preg_match('/.xml/i', $attach["name"])) {
                            $xml = $imap->downloadAttachment($uid, $attach["partNum"], $attach["enc"]);
                            $array = $sat->satToArray($xml);

                            if (!($repo->verificarFacturaProveedor($array["Emisor"]["@attributes"]["rfc"], $array["@attributes"]["folio"]))) {
                                foreach ($attachments as $k => $att) {
                                    $pdf = substr($attach["name"], 0, -4) . '.pdf';
                                    if (preg_match('/' . $pdf . '/i', $att["name"])) {
                                        $pdfBin = $imap->downloadAttachment($uid, $att["partNum"], $att["enc"]);
                                    }
                                }
                                file_put_contents($this->_appconfig->getParam('backup') . DIRECTORY_SEPARATOR . 'terminal_xml' . DIRECTORY_SEPARATOR . $attach["name"], $xml);
                                if (isset($pdfBin)) {
                                    file_put_contents($this->_appconfig->getParam('backup') . DIRECTORY_SEPARATOR . 'terminal_xml' . DIRECTORY_SEPARATOR . substr($attach["name"], 0, -4) . ".pdf", $pdfBin);
                                }
                                if (file_exists($this->_appconfig->getParam('backup') . DIRECTORY_SEPARATOR . 'terminal_xml' . DIRECTORY_SEPARATOR . $attach["name"])) {
                                    $added = $repo->addNewInvoice(
                                            3, null, $array["@attributes"]["folio"], date('Y-m-d H:i:s', strtotime($array["@attributes"]["fecha"])), $array["Emisor"]["@attributes"]["rfc"], $array["Emisor"]["@attributes"]["nombre"], $array["Receptor"]["@attributes"]["rfc"], $array["Receptor"]["@attributes"]["nombre"], $attach["name"], $this->_appconfig->getParam('backup') . DIRECTORY_SEPARATOR . 'terminal_xml', $details["fromAddr"]);
                                    if ($added && isset($pdfBin)) {
                                        $added = $repo->addNewInvoice(
                                                3, null, $array["@attributes"]["folio"], date('Y-m-d H:i:s', strtotime($array["@attributes"]["fecha"])), $array["Emisor"]["@attributes"]["rfc"], $array["Emisor"]["@attributes"]["nombre"], $array["Receptor"]["@attributes"]["rfc"], $array["Receptor"]["@attributes"]["nombre"], substr($attach["name"], 0, -4) . ".pdf", $this->_appconfig->getParam('backup') . DIRECTORY_SEPARATOR . 'terminal_xml', $details["fromAddr"]);
                                    }
                                }
                            }
                        }
                    }
                    if (preg_match('/mbenitez@oaq.com.mx/i', $details["fromAddr"])
                            //|| preg_match('/marynl@oaq.mx/i', $details["fromAddr"])
                            //|| preg_match('/portal.consumidor@edxinbox.com/i', $details["fromAddr"])
                            || preg_match('/facturaelectronica@cmr.ws/i', $details["fromAddr"])) {
                        if (preg_match('/.xml/i', $attach["name"])) {
                            $xml = $imap->downloadAttachment($uid, $attach["partNum"], $attach["enc"]);
                            $array = $sat->satToArray($xml);

                            if (!($repo->verificarFacturaProveedor($array["Emisor"]["@attributes"]["rfc"], $array["@attributes"]["folio"]))) {
                                foreach ($attachments as $k => $att) {
                                    $pdf = substr($attach["name"], 0, -4) . '.pdf';
                                    if (preg_match('/' . $pdf . '/i', $att["name"])) {
                                        $pdfBin = $imap->downloadAttachment($uid, $att["partNum"], $att["enc"]);
                                    }
                                }
                                file_put_contents($this->_appconfig->getParam('backup') . DIRECTORY_SEPARATOR . 'facturas' . DIRECTORY_SEPARATOR . 'otras' . DIRECTORY_SEPARATOR . $attach["name"], $xml);
                                if (isset($pdfBin)) {
                                    file_put_contents($this->_appconfig->getParam('backup') . DIRECTORY_SEPARATOR . 'facturas' . DIRECTORY_SEPARATOR . 'otras' . DIRECTORY_SEPARATOR . substr($attach["name"], 0, -4) . ".pdf", $pdfBin);
                                }
                                if (file_exists($this->_appconfig->getParam('backup') . DIRECTORY_SEPARATOR . 'facturas' . DIRECTORY_SEPARATOR . 'otras' . DIRECTORY_SEPARATOR . $attach["name"])) {
                                    $added = $repo->addNewInvoice(
                                            3, null, $array["@attributes"]["folio"], date('Y-m-d H:i:s', strtotime($array["@attributes"]["fecha"])), $array["Emisor"]["@attributes"]["rfc"], $array["Emisor"]["@attributes"]["nombre"], $array["Receptor"]["@attributes"]["rfc"], $array["Receptor"]["@attributes"]["nombre"], $attach["name"], $this->_appconfig->getParam('backup') . DIRECTORY_SEPARATOR . 'facturas' . DIRECTORY_SEPARATOR . 'otras', $details["fromAddr"]);
                                    if ($added && isset($pdfBin)) {
                                        $added = $repo->addNewInvoice(
                                                3, null, $array["@attributes"]["folio"], date('Y-m-d H:i:s', strtotime($array["@attributes"]["fecha"])), $array["Emisor"]["@attributes"]["rfc"], $array["Emisor"]["@attributes"]["nombre"], $array["Receptor"]["@attributes"]["rfc"], $array["Receptor"]["@attributes"]["nombre"], substr($attach["name"], 0, -4) . ".pdf", $this->_appconfig->getParam('backup') . DIRECTORY_SEPARATOR . 'facturas' . DIRECTORY_SEPARATOR . 'otras', $details["fromAddr"]);
                                    }
                                }
                            }
                        }
                    }

                    if (preg_match('/autoexpress.dophe@gmail.com/i', $details["fromAddr"])) {
                        if (preg_match('/.xml/i', $attach["name"])) {
                            $xml = $imap->downloadAttachment($uid, $attach["partNum"], $attach["enc"]);
                            $array = $sat->satToArray($xml);

                            if (!($repo->verificarFacturaProveedor($array["Emisor"]["@attributes"]["rfc"], $array["@attributes"]["folio"]))) {
                                foreach ($attachments as $k => $att) {
                                    $pdf = substr($attach["name"], 0, -4) . '.pdf';
                                    if (preg_match('/' . $pdf . '/i', $att["name"])) {
                                        $pdfBin = $imap->downloadAttachment($uid, $att["partNum"], $att["enc"]);
                                    }
                                }
                                file_put_contents($this->_appconfig->getParam('backup') . DIRECTORY_SEPARATOR . 'facturas' . DIRECTORY_SEPARATOR . 'dophe' . DIRECTORY_SEPARATOR . $attach["name"], $xml);
                                if (isset($pdfBin)) {
                                    file_put_contents($this->_appconfig->getParam('backup') . DIRECTORY_SEPARATOR . 'facturas' . DIRECTORY_SEPARATOR . 'dophe' . DIRECTORY_SEPARATOR . substr($attach["name"], 0, -4) . ".pdf", $pdfBin);
                                }
                                if (file_exists($this->_appconfig->getParam('backup') . DIRECTORY_SEPARATOR . 'facturas' . DIRECTORY_SEPARATOR . 'dophe' . DIRECTORY_SEPARATOR . $attach["name"])) {
                                    $added = $repo->addNewInvoice(
                                            3, null, $array["@attributes"]["folio"], date('Y-m-d H:i:s', strtotime($array["@attributes"]["fecha"])), $array["Emisor"]["@attributes"]["rfc"], $array["Emisor"]["@attributes"]["nombre"], $array["Receptor"]["@attributes"]["rfc"], $array["Receptor"]["@attributes"]["nombre"], $attach["name"], $this->_appconfig->getParam('backup') . DIRECTORY_SEPARATOR . 'facturas' . DIRECTORY_SEPARATOR . 'dophe', $details["fromAddr"]);
                                    if ($added && isset($pdfBin)) {
                                        $added = $repo->addNewInvoice(
                                                3, null, $array["@attributes"]["folio"], date('Y-m-d H:i:s', strtotime($array["@attributes"]["fecha"])), $array["Emisor"]["@attributes"]["rfc"], $array["Emisor"]["@attributes"]["nombre"], $array["Receptor"]["@attributes"]["rfc"], $array["Receptor"]["@attributes"]["nombre"], substr($attach["name"], 0, -4) . ".pdf", $this->_appconfig->getParam('backup') . DIRECTORY_SEPARATOR . 'facturas' . DIRECTORY_SEPARATOR . 'dophe', $details["fromAddr"]);
                                    }
                                }
                            }
                        }
                    }
                }
            }
            echo "</ul>";

            if (APPLICATION_ENV == 'production') {
                if (preg_match('/notificaciones@terminal.com.mx/i', $details["fromAddr"]) || preg_match('/ana.arteaga@terminal.com.mx/i', $details["fromAddr"]) || preg_match('/guadalupe.munoz@terminal.com.mx/i', $details["fromAddr"])) {
                    if ($imap->copyMessage($i, "INBOX.Historic")) {
                        $imap->deleteMessage($uid);
                    }
                }
                if (preg_match('/autoexpress.dophe@gmail.com/i', $details["fromAddr"])) {
                    if ($imap->copyMessage($i, "INBOX.Historic")) {
                        $imap->deleteMessage($uid);
                    }
                }
                if (preg_match('/marynl@oaq.mx/i', $details["fromAddr"])) {
                    if ($imap->copyMessage($i, "INBOX.Historic")) {
                        $imap->deleteMessage($uid);
                    }
                }
            }
        }
    }

    public function clientesSicaAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $sica = new OAQ_Sica();
        $customers = $sica->getAllSicaCustomer();
        $cust = new Automatizacion_Model_ClientesMapper();
        foreach ($customers as $customer):
            $exists = $cust->verifyCustomer($customer['cliente_rfc']);
            if (!$exists) {
                $cust->addNewSicaCustomer($customer['cliente_rfc'], $customer['cliente_nombre'], $customer['email'], $customer['sica_id'], $customer['sica_num_interno']);
            }
        endforeach;
    }

//    public function xmlFacturasAction() {
//        $this->_helper->layout()->disableLayout();
//        $this->_helper->viewRenderer->setNoRender(true);
//    }

    public function notificacionesTerminalAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        try {
            $shipments = new Automatizacion_Model_EmbarquesTerminalMapper();
            $storages = array(
                'notificaciones',
            );
            $backupFolder = $this->_appconfig->getParam('backup') . DIRECTORY_SEPARATOR . "terminal_xml";
            if (!file_exists($backupFolder)) {
                mkdir($backupFolder);
            }
            foreach ($storages as $storage) {
                $this->mailStorage($storage);
                foreach ($this->_emailStorage as $msgId => $message) {
                    if ($message->hasFlag(Zend_Mail_Storage::FLAG_SEEN)) {
                        continue;
                    }
                    if (preg_match("/notificaciones@terminal.com.mx/i", (String) $message->from)) {

                        Zend_Debug::dump($message);
                        $fecha = date('Y-m-d H:i:s', strtotime(str_replace(' -0500 (GMT-05:00)', '', $message->date)));

                        if ($message->isMultipart()) {

                            foreach (new RecursiveIteratorIterator($this->_emailStorage->getMessage($msgId)) as $part) {

                                if (preg_match("/octet-stream/i", (String) $part->contentType)) {
                                    $contentType = explode(" ", $part->contentType);
                                    $filename = explode("=", $contentType[1]);
                                    $filestream = $backupFolder . DIRECTORY_SEPARATOR . $filename[1];
                                    $content = $part->getContent();
                                    if (!file_exists($filestream)) {
                                        $fh = fopen($filestream, 'w');
                                        fwrite($fh, $content);
                                        fclose($fh);
                                    }
                                    $xml = simplexml_load_string($content);
                                    $xmlArray = @json_decode(@json_encode($xml), 1);
                                    if (!$shipments->checkDoorTag($xmlArray['Entrada']['@attributes']["GuiaHouse"])) {
                                        $aerolinea = '';
                                        if (preg_match("/TERMINAL LOGISTICS TRANSPORTE FISCALIZADO/i", $xmlArray['Entrada']['@attributes']['Aerolinea'])) {
                                            $aerolinea = 'FEDEX';
                                        } else if (preg_match("/DHL EXPRESS MEXICO, S.A. DE C.V./i", $xmlArray['Entrada']['@attributes']['Aerolinea'])) {
                                            $aerolinea = 'DHL';
                                        } else {
                                            $aerolinea = $xmlArray['Entrada']['@attributes']['Aerolinea'];
                                        }

                                        $data = array(
                                            'aduana' => 646,
                                            'guia_house' => $xmlArray['Entrada']['@attributes']['GuiaHouse'],
                                            'guia_master' => $xmlArray['Entrada']['@attributes']['GuiaMaster'],
                                            'aerolinea' => $aerolinea,
                                            'bultos' => (int) $xmlArray['Entrada']['@attributes']['Bultos'],
                                            'cliente' => $xmlArray['Entrada']['@attributes']['Cliente'],
                                            'descripcion' => $xmlArray['Entrada']['@attributes']['Descripcion'],
                                            'destinatario' => (isset($xmlArray['Entrada']['@attributes']['Destinatario'])) ? $xmlArray['Entrada']['@attributes']['Destinatario'] : null,
                                            'embalaje' => $xmlArray['Entrada']['@attributes']['Embalaje'],
                                            'dir_destinatario' => (isset($xmlArray['Entrada']['@attributes']['DireccionDestinatario'])) ? $xmlArray['Entrada']['@attributes']['DireccionDestinatario'] : null,
                                            'estado_mercancia' => $xmlArray['Entrada']['@attributes']['EstadoMercancia'],
                                            'fecha_abandono' => $xmlArray['Entrada']['@attributes']['FechaAbandono'],
                                            'fecha_entrada' => date('Y-m-d H:i:s', strtotime($xmlArray['Entrada']['@attributes']['FechaEntrada'])),
                                            'fecha_notificacion' => $fecha,
                                            'num_vuelo' => $xmlArray['Entrada']['@attributes']['NumVuelo'],
                                            'peso_kg' => (float) $xmlArray['Entrada']['@attributes']['Peso'],
                                            'peso_lbs' => null,
                                            'creado' => date('Y-m-d H:i:s'),
                                        );
                                        $shipments->addNewShipment($data);
                                    }
                                }
                            }
                        } // if message multipart
                    }
                } // foreach message
            } // foreach storage
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function vucemPedimentosAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $misc = new OAQ_Misc();
        $firmantes = new Vucem_Model_VucemFirmanteMapper();
        $customs = new Vucem_Model_VucemPedimentosAduanasMapper();
        $ped = new Vucem_Model_VucemPedimentosMapper();
        $vucem = new OAQ_Vucem();
        $rfc = $firmantes->obtenerFirmantes('production');
        $getFecha = $this->_request->getParam("fecha");

        foreach ($rfc as $item) {
            $detalle = $firmantes->obtenerDetalleFirmante($item["rfc"], 'prod');
            $aduanas = $customs->obtenerAduanas($item["id"]);

            foreach ($aduanas as $adu) {
                if (!isset($getFecha)) {
                    $fecha = date('Y-m-d', strtotime("-1 days", time()));
                } else {
                    $fecha = $getFecha;
                }
                $xml = $vucem->xmlListadoPedimentos($item["rfc"], $detalle["ws_pswd"], $fecha, $adu["patente"], $adu["aduana"]);
                $response = $vucem->vucemPedimento('ListarPedimentosService', $xml);

                $misc->saveFile($this->_appconfig->getParam('xmldir') . DIRECTORY_SEPARATOR . "pedimentos" . DIRECTORY_SEPARATOR . "solicitud", "SOL_" . $item["rfc"] . "_" . $adu["patente"] . "-" . $adu["aduana"] . "_" . date("Ymd-His") . ".xml", $misc->xmlIdent($response));

                $pedimentos = $vucem->vucemXmlToArray($response);
                unset($pedimentos["Header"]);
                if ($pedimentos["Body"]["consultarPedimentosRespuesta"]["tieneError"] == 'false') {

                    if (isset($pedimentos["Body"]["consultarPedimentosRespuesta"]["pedimento"][0]["numeroDocumentoAgente"])) {
                        foreach ($pedimentos["Body"]["consultarPedimentosRespuesta"]["pedimento"] as $pd) {
                            if (!($ped->verificarPedimento($adu["patente"], $adu["aduana"], $pd["numeroDocumentoAgente"]))) {
                                $add = $ped->nuevoPedimento(0, $adu["patente"], $adu["aduana"], $pd["numeroDocumentoAgente"], $fecha, 0, $item["rfc"]);
                            }
                        }
                    } else if (isset($pedimentos["Body"]["consultarPedimentosRespuesta"]["pedimento"]["numeroDocumentoAgente"])) {
                        if (!($ped->verificarPedimento($adu["patente"], $adu["aduana"], $pedimentos["Body"]["consultarPedimentosRespuesta"]["pedimento"]["numeroDocumentoAgente"]))) {
                            $add = $ped->nuevoPedimento(0, $adu["patente"], $adu["aduana"], $pedimentos["Body"]["consultarPedimentosRespuesta"]["pedimento"]["numeroDocumentoAgente"], $fecha, 0, $item["rfc"]);
                        }
                    }
                }
            }
        }
    }

    public function vucemActualizarPedimentosAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $misc = new OAQ_Misc();
        $firmantes = new Vucem_Model_VucemFirmanteMapper();
        $customs = new Vucem_Model_VucemPedimentosAduanasMapper();
        $ped = new Vucem_Model_VucemPedimentosMapper();
        $vucem = new OAQ_Vucem();
        $rfc = $firmantes->obtenerFirmantes('production');

        foreach ($rfc as $item) {
            $detalle = $firmantes->obtenerDetalleFirmante($item["rfc"], 'prod');
            $aduanas = $customs->obtenerAduanas($item["id"]);
            foreach ($aduanas as $adu) {
                $peds = $ped->obtenerPedimentosNoXml($item["rfc"], $adu["patente"], $adu["aduana"]);
                if ($peds) {
                    foreach ($peds as $pd) {
                        $xml = $vucem->solicitudPedimentoCompleto($item["rfc"], $detalle["ws_pswd"], $adu["patente"], $adu["aduana"], $pd["pedimento"]);
                        $response = $vucem->vucemPedimento('ConsultarPedimentoCompletoService', $xml);
                        $dir = $this->_appconfig->getParam('xmldir');
                        $dir2 = DIRECTORY_SEPARATOR . "pedimentos" . DIRECTORY_SEPARATOR . "pedimento";
                        $filename = $item["rfc"] . "_" . $adu["patente"] . "-" . $adu["aduana"] . "-" . $pd["pedimento"] . "_PED_" . date("Ymd-His") . ".xml";
                        $sol = $item["rfc"] . "_" . $adu["patente"] . "-" . $adu["aduana"] . "-" . $pd["pedimento"] . "_SOL_" . date("Ymd-His") . ".xml";
                        $misc->saveFile($dir . $dir2, $filename, $misc->xmlIdent($response));
                        $misc->saveFile($dir . $dir2, $sol, $misc->xmlIdent($xml));

                        $completo = $vucem->vucemXmlToArray($response);
                        unset($completo["Header"]);
                        if ($completo["Body"]["consultarPedimentoCompletoRespuesta"]["tieneError"] == 'false') {
                            if (isset($completo["Body"]["consultarPedimentoCompletoRespuesta"]["pedimento"]["encabezado"]["rfcAgenteAduanalSocFactura"])) {
                                $rfcSoc = $completo["Body"]["consultarPedimentoCompletoRespuesta"]["pedimento"]["encabezado"]["rfcAgenteAduanalSocFactura"];
                                $status = 1;
                            }
                            if (isset($completo["Body"]["consultarPedimentoCompletoRespuesta"]["pedimento"]["previoConsolidado"]["rfcAgenteAduanalSociedadFactura"])) {
                                $rfcSoc = $completo["Body"]["consultarPedimentoCompletoRespuesta"]["pedimento"]["previoConsolidado"]["rfcAgenteAduanalSociedadFactura"];
                                $status = 2; // consolidado/remesas                                
                            }
                            $update = $ped->actualizarPedimento($dir2 . DIRECTORY_SEPARATOR . $filename, $adu["patente"], $adu["aduana"], $pd["pedimento"], 0, $completo["Body"]["consultarPedimentoCompletoRespuesta"]["numeroOperacion"], $rfcSoc, $status);
                        } else if ($completo["Body"]["consultarPedimentoCompletoRespuesta"]["tieneError"] == 'true') {
                            $update = $ped->actualizarPedimento($dir2 . DIRECTORY_SEPARATOR . $filename, $adu["patente"], $adu["aduana"], $pd["pedimento"], 0, null, null, -1);
                        }
                        unset($filename);
                        unset($sol);
                    }
                }
            }
        }
    }

    /**
     * /automatizacion/index/obtener-archivos-m3
     * /automatizacion/index/obtener-archivos-m3?aduana=240
     * 
     */
    public function obtenerArchivosM3Action() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $aduana = $this->_request->getParam('aduana', null);
        $year = $this->_request->getParam('year', null);
        $val = new OAQ_ArchivosM3('/home/samba-share/expedientes/m3');
        if (!isset($year)) {
            $year = date('Y');
        }
        if (!isset($aduana)) {
            $directories = array(
                array(
                    'patente' => 3589,
                    'aduana' => 640,
                    'directory' => "/home/samba-share/archivos_640/saai/" . $year
                ),
                array(
                    'patente' => 3589,
                    'aduana' => 240,
                    'directory' => "/home/samba-share/archivos_240/saai/" . $year
                ),
            );
        } else {
            if ($aduana == 240) {
                $directories = array(
                    array(
                        'patente' => 3589,
                        'aduana' => 240,
                        'directory' => "/home/samba-share/archivos_240/saai/" . $year
                    ),
                );
            }
        }
        foreach ($directories as $dir) {
            $val->obtenerArchivosM3($dir["aduana"], $dir["directory"]);
            $val->firmasValidacion($dir["aduana"], $dir["directory"]);
            $val->pagosRealizados($dir["aduana"], $dir["directory"]);
            $val->archivosE($dir["aduana"], $dir["directory"]);
            $val->otrosArchivos($dir["aduana"], $dir["directory"]);
            $val->archivosK($dir["patente"], $dir["aduana"], $dir["directory"]);
        }
    }

    /**
     * /automatizacion/index/archivos-validacion?debug=true&year=2018
     * 
     * @return boolean
     */
    public function archivosValidacionAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $flt = array(
            "*" => array("StringTrim", "StripTags"),
            "patente" => array("Digits"),
            "aduana" => array("Digits"),
            "year" => array("Digits"),
            "debug" => array("StringToLower"),
            "plain" => array("StringToLower"),
            "dia" => array("StringToLower"),
        );
        $vdr = array(
            "aduana" => array(new Zend_Validate_Int()),
            "patente" => array(new Zend_Validate_Int()),
            "dia" => array(new Zend_Validate_Int(), "default" => (int) date("z") + 1),
            "year" => array(new Zend_Validate_Int(), "default" => date("Y")),
            "debug" => new Zend_Validate_InArray(array("true", "false")),
            "plain" => new Zend_Validate_InArray(array("true", "false")),
        );
        $input = new Zend_Filter_Input($flt, $vdr, $this->_request->getParams());
        $mapperDir = new Automatizacion_Model_ArchivosValidacionDirectorios();
        $view = new Zend_View();
        $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/index/");
        if (!$input->isValid("aduana") && !$input->isValid("patente")) {
            $arr = $mapperDir->fetchAll();
            if (isset($arr) && is_array($arr)) {
                $mapper = new Automatizacion_Model_ArchivosValidacionMapper();
                foreach ($arr as $dir) {
                    $archivos = new OAQ_ArchivosValidacion();
                    if ($dir["yearPrefix"] == 1) {
                        $archivos->set_dir($dir["directorio"] . DIRECTORY_SEPARATOR . $input->year);
                    } else {
                        $archivos->set_dir($dir["directorio"]);
                    }
                    $archivos->set_aduana($dir["aduana"]);
                    $archivos->set_patente($dir["patente"]);
                    $archivos->obtenerTodos();
                    $files = $archivos->get_data();
                    if ($input->isValid("debug")) {
                        $view->data = $files;
                        if ($dir["yearPrefix"] == 1) {
                            $view->directory = $dir["directorio"] . DIRECTORY_SEPARATOR . $input->year;
                        } else {
                            $view->directory = $dir["directorio"];
                        }
                        if ($input->isValid("plain")) {
                            echo $view->render("validacion-plain.phtml");
                        } else {
                            echo $view->render("validacion.phtml");
                        }
                    }
                    if (isset($files) && !empty($files)) {
                        foreach ($files as $file) {
                            $table = new Automatizacion_Model_Table_ArchivosValidacion($file);
                            $mapper->save($table);
                            $juliano = str_pad(((int) date("z", strtotime($table->getCreado())) + 1), 3, "0", STR_PAD_LEFT);
                            if (isset($dir["salida"])) {
                                $outdir = $dir["salida"] . DIRECTORY_SEPARATOR . $input->year . DIRECTORY_SEPARATOR . $dir["patente"] . DIRECTORY_SEPARATOR . $dir["aduana"] . DIRECTORY_SEPARATOR . $juliano;
                                $archivos->copiarArchivo($dir["directorio"], $outdir, $table->getArchivoNombre());
                            }
                        }
                    }
                    unset($files);
                } // foreach $arr as $dir
                $arrayp = $mapper->notAnalized("pagado");
                $pagos = new Automatizacion_Model_ArchivosValidacionPagosMapper();
                if (isset($arrayp) && !empty($arrayp)) {
                    foreach ($arrayp as $item) {
                        $archivos->set_contenido(base64_decode($item["contenido"]));
                        $archivos->analizaArchivoPago();
                        $data = $archivos->get_data();
                        if (isset($data) && !empty($data)) {
                            foreach ($data as $p) {
                                $pago = new Automatizacion_Model_Table_ArchivosValidacionPagos($p);
                                $pago->setIdArchivoValidacion($item["id"]);
                                $pagos->find($pago);
                                if (null === ($pago->getId())) {
                                    $pagos->save($pago);
                                }
                            }
                            $mapper->setAnalized($item["id"]);
                        }
                    }
                }
                $arrayv = $mapper->notAnalized("validacion");
                $signatures = new Automatizacion_Model_ArchivosValidacionFirmasMapper();
                if (isset($arrayv) && !empty($arrayv)) {
                    foreach ($arrayv as $item) {
                        $archivos->set_contenido(base64_decode($item["contenido"]));
                        $archivos->set_patente($item["patente"]);
                        $archivos->analizaArchivoFirmas();
                        $data = $archivos->get_data();
                        if (isset($data) && !empty($data)) {
                            foreach ($data as $f) {
                                $sign = new Automatizacion_Model_Table_ArchivosValidacionFirmas($f);
                                $sign->setIdArchivoValidacion($item["id"]);
                                $signatures->find($sign);
                                if (null === ($sign->getId())) {
                                    $signatures->save($sign);
                                }
                            }
                            $mapper->setAnalized($item["id"]);
                        }
                    }
                }
                $arraym = $mapper->notAnalized("m3");
                $documents = new Automatizacion_Model_ArchivosValidacionPedimentosMapper();
                if (isset($arraym) && !empty($arraym)) {
                    foreach ($arraym as $item) {
                        $archivos->set_contenido(base64_decode($item["contenido"]));
                        $archivos->set_patente($item["patente"]);
                        $archivos->analizaArchivoPedimento();
                        $data = $archivos->get_data();
                        if (isset($data) && !empty($data)) {
                            foreach ($data as $m) {
                                if (!isset($m["archivoNombre"]) || !isset($m["patente"]) || !isset($m["aduana"]) || !isset($m["pedimento"])) {
                                    continue;
                                }
                                $ped = new Automatizacion_Model_Table_ArchivosValidacionPedimentos($m);
                                $ped->setIdArchivoValidacion($item["id"]);
                                $documents->find($ped);
                                if (null === ($ped->getId())) {
                                    $documents->save($ped);
                                }
                            }
                            $mapper->setAnalized($item["id"]);
                        }
                    }
                }
            } else {
                return false;
            }
        } elseif ($input->isValid("patente") && $input->isValid("aduana") && $input->isValid("year")) {
            $dir = $mapperDir->obtener($input->patente, $input->aduana);
            if (isset($dir)) {
                if ($dir["yearPrefix"] == 1) {
                    $archivos->set_dir($dir["directorio"] . DIRECTORY_SEPARATOR . $input->year);
                } else {
                    $archivos->set_dir($dir["directorio"]);
                }
            }
            $archivos->set_aduana($dir["aduana"]);
            $archivos->set_patente($dir["patente"]);
            $archivos->obtenerTodos();
            $files = $archivos->get_data();
            if ($input->isValid("debug")) {
                $view->data = $files;
                if ($dir["yearPrefix"] == 1) {
                    $view->directory = $dir["directorio"] . DIRECTORY_SEPARATOR . $input->year;
                } else {
                    $view->directory = $dir["directorio"];
                }
                if ($input->isValid("plain")) {
                    echo $view->render("validacion-plain.phtml");
                } else {
                    echo $view->render("validacion.phtml");
                }
            }
            $mapper = new Automatizacion_Model_ArchivosValidacionMapper();
            if (isset($files) && !empty($files)) {
                foreach ($files as $file) {
                    $table = new Automatizacion_Model_Table_ArchivosValidacion($file);
                    $mapper->save($table);
                    $juliano = str_pad(((int) date("z", strtotime($table->getCreado())) + 1), 3, "0", STR_PAD_LEFT);
                    if (isset($dir["salida"])) {
                        $outdir = $dir["salida"] . DIRECTORY_SEPARATOR . $input->year . DIRECTORY_SEPARATOR . $dir["patente"] . DIRECTORY_SEPARATOR . $dir["aduana"] . DIRECTORY_SEPARATOR . $juliano;
                        $archivos->copiarArchivo($dir["directorio"], $outdir, $table->getArchivoNombre());
                    }
                }
            }
            unset($files);
            $arrayp = $mapper->notAnalized("pagado");
            $pagos = new Automatizacion_Model_ArchivosValidacionPagosMapper();
            if (isset($arrayp) && !empty($arrayp)) {
                foreach ($arrayp as $item) {
                    $archivos->set_contenido(base64_decode($item["contenido"]));
                    $archivos->analizaArchivoPago();
                    $data = $archivos->get_data();
                    if (isset($data) && !empty($data)) {
                        foreach ($data as $p) {
                            $pago = new Automatizacion_Model_Table_ArchivosValidacionPagos($p);
                            $pago->setIdArchivoValidacion($item["id"]);
                            $pagos->find($pago);
                            if (null === ($pago->getId())) {
                                $pagos->save($pago);
                            }
                        }
                        $mapper->setAnalized($item["id"]);
                    }
                }
            }
            $arrayv = $mapper->notAnalized("validacion");
            $signatures = new Automatizacion_Model_ArchivosValidacionFirmasMapper();
            if (isset($arrayv) && !empty($arrayv)) {
                foreach ($arrayv as $item) {
                    $archivos->set_contenido(base64_decode($item["contenido"]));
                    $archivos->set_patente($item["patente"]);
                    $archivos->analizaArchivoFirmas();
                    $data = $archivos->get_data();
                    if (isset($data) && !empty($data)) {
                        foreach ($data as $f) {
                            $sign = new Automatizacion_Model_Table_ArchivosValidacionFirmas($f);
                            $sign->setIdArchivoValidacion($item["id"]);
                            $signatures->find($sign);
                            if (null === ($sign->getId())) {
                                $signatures->save($sign);
                            }
                        }
                        $mapper->setAnalized($item["id"]);
                    }
                }
            }
            $arraym = $mapper->notAnalized("m3");
            $documents = new Automatizacion_Model_ArchivosValidacionPedimentosMapper();
            if (isset($arraym) && !empty($arraym)) {
                foreach ($arraym as $item) {
                    $archivos->set_contenido(base64_decode($item["contenido"]));
                    $archivos->set_patente($item["patente"]);
                    $archivos->analizaArchivoPedimento();
                    $data = $archivos->get_data();
                    if (isset($data) && !empty($data)) {
                        foreach ($data as $m) {
                            if (!isset($m["archivoNombre"]) || !isset($m["patente"]) || !isset($m["aduana"]) || !isset($m["pedimento"])) {
                                continue;
                            }
                            $ped = new Automatizacion_Model_Table_ArchivosValidacionPedimentos($m);
                            $ped->setIdArchivoValidacion($item["id"]);
                            $documents->find($ped);
                            if (null === ($ped->getId())) {
                                $documents->save($ped);
                            }
                        }
                        $mapper->setAnalized($item["id"]);
                    }
                }
            }
        }
        return;
    }
    
    /**
     * /automatizacion/index/ver-archivo-validacion?id=41400
     * 
     * @throws Exception
     */
    public function verArchivoValidacionAction() {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $flt = array(
                "id" => array("StringTrim", "StripTags", "Digits"),
            );
            $vld = array(
                "id" => array("NotEmpty",new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($flt, $vld, $this->_request->getParams());
            if($input->isValid("id")) {
                $mapper = new Automatizacion_Model_ArchivosValidacionMapper();
                $arr = $mapper->fileContent($input->id);
                Zend_Debug::dump($arr);
                Zend_Debug::dump(base64_decode($arr["contenido"]));
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    /**
     * /automatizacion/index/analizar-archivos-validacion
     * 
     */
    public function analizarArchivosValidacionAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "patente" => array("Digits"),
            "aduana" => array("Digits"),
            "debug" => array("StringToLower"),
        );
        $v = array(
            "aduana" => array(new Zend_Validate_Int()),
            "patente" => array(new Zend_Validate_Int()),
            "debug" => new Zend_Validate_InArray(array("true", "false")),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        $mapper = new Automatizacion_Model_ArchivosValidacionMapper();
        $archivos = new OAQ_ArchivosValidacion();
        $arrayp = $mapper->notAnalized("pagado");
        $pagos = new Automatizacion_Model_ArchivosValidacionPagosMapper();
        if (isset($arrayp) && !empty($arrayp)) {
            foreach ($arrayp as $item) {
                Zend_Debug::dump(array(
                    "id" => $item["id"],
                    "archivo" => $item["archivo"],
                    "contenido" => base64_decode($item["contenido"])
                ));
                $archivos->set_contenido(base64_decode($item["contenido"]));
                $archivos->analizaArchivoPago();
                $data = $archivos->get_data();
                if (isset($data) && !empty($data)) {
                    foreach ($data as $p) {
                        $pago = new Automatizacion_Model_Table_ArchivosValidacionPagos($p);
                        $pago->setIdArchivoValidacion($item["id"]);
                        $pagos->find($pago);
                        if (null == ($pago->getId())) {
                            $pagos->save($pago);
                        } else {
                            $pagos->update($pago);
                        }
                    }
                    $mapper->setAnalized($item["id"]);
                }
            }
        }
        $array = $mapper->notAnalized("m3");
        $documents = new Automatizacion_Model_ArchivosValidacionPedimentosMapper();
        if (isset($array) && !empty($array)) {
            foreach ($array as $item) {
                $archivos->set_contenido(base64_decode($item["contenido"]));
                $archivos->set_patente($item["patente"]);
                $archivos->analizaArchivoPedimento();
                $data = $archivos->get_data();
                if (isset($data) && !empty($data)) {
                    foreach ($data as $m) {
                        if (!isset($m["archivoNombre"]) || !isset($m["patente"]) || !isset($m["aduana"]) || !isset($m["pedimento"])) {
                            continue;
                        }
                        $ped = new Automatizacion_Model_Table_ArchivosValidacionPedimentos($m);
                        $ped->setIdArchivoValidacion($item["id"]);
                        $documents->find($ped);
                        if (null == ($ped->getId())) {
                            $documents->save($ped);
                        } else {
                            $documents->update($ped);
                        }
                    }
                    $mapper->setAnalized($item["id"]);
                }
            }
        }
    }

    /**
     * /automatizacion/index/analizar-archivos-m3
     * 
     */
    public function analizarArchivosM3Action() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $val = new OAQ_ArchivosM3();
        $val->analizarArchivosM3();
        $val->analizarPrevalidacion();
        $val->analizarPagados();
    }

    protected function sendErrorEmail($mailStorage, $body) {
        $from = $this->mailStorage($mailStorage);
        $mail = new Zend_Mail("UTF-8");
        $mail->addTo("soporte@oaq.com.mx");
        $mail->setFrom($from);
        $mail->setSubject("Exception found " . __METHOD__);
        $mail->setBodyText($body);
        $mail->send($this->_transportSupport);
    }

    /**
     * /automatizacion/index/send-to-ftp?year=2016&mes=1&rfc=CCO0309098N8
     * /automatizacion/index/send-to-ftp?year=2016&mes=1&rfc=CTM990607US8
     * /automatizacion/index/send-to-ftp?rfc=JMM931208JY9&patente=3589&fecha=2014-03-14
     * /automatizacion/index/send-to-ftp?rfc=JMM931208JY9&patente=3589&year=2016&mes=1
     * /automatizacion/index/send-to-ftp?patente=3589&fecha=2014-03-14
     * /automatizacion/index/send-to-ftp?rfc=DCM030212ET4&patente=3589&year=2014&mes=4
     * /automatizacion/index/send-to-ftp?rfc=CIN0309091D3&patente=3589&year=2014$mes=8
     * /automatizacion/index/send-to-ftp?rfc=MQU971209RQ1&patente=3589&year=2014&mes=8  MEI DE QUERETARO.
     * /automatizacion/index/send-to-ftp?year=2015&mes=1&rfc=ARB820712U77,CCO030908FU8,CCO0309098N8,CIN0309091D3,CME950209J18,CME930831D89,SME751021B90,RHM720412B61,FDQ7904066U0,DAM980101SR0
     * /automatizacion/index/send-to-ftp?year=2016&mes=1&patente=3574&rfc=JMM931208JY9
     * 
     * SCRIPT /var/www/oaqintranet/cron/enviarM3ftp.sh
     * 
     */
    public function sendToFtpAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $flt = array(
            "*" => array("StringTrim", "StripTags"),
            "patente" => array("Digits"),
            "year" => array("Digits"),
            "mes" => array("Digits"),
            "rfc" => array("StringToUpper"),
        );
        $vld = array(
            "patente" => array("Digits", new Zend_Validate_Int(), new Zend_Validate_InArray(array(3589, 3574, 3933))),
            "year" => array("Digits", new Zend_Validate_Int(), array("Between", 2015, 2025)),
            "mes" => array("Digits", new Zend_Validate_Int(), array("Between", 1, 12)),
            "rfc" => array("Alnum", new Zend_Validate_Alnum(), array("StringLength", 12, 15)),
            "fecha" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
        );
        $input = new Zend_Filter_Input($flt, $vld, $this->_request->getParams());
        if ($input->isValid()) {
            if (!$input->isValid("patente")) {
                $input->patente = 3589;
            }
            if (preg_match("/,/i", $input->rfc)) {
                $array = preg_split("/,/i", $input->rfc);
            } elseif (isset($input->rfc)) {
                $array[] = $input->rfc;
            }
            $view = new Zend_View();
            $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/index/");
            $view->data = $input->getEscaped();
            $ftp = new Automatizacion_Model_FtpMapper();
            $mapper = new Automatizacion_Model_ArchivosValidacionPedimentosMapper();
            $servers = $ftp->getByType("m3", $array);
            if (APPLICATION_ENV == 'production') {
                $directory = "/tmp/ftptmp";
            } else {
                $directory = "D:\\wamp64\\tmp";
            }
            foreach ($servers as $server) {
                $arr = $mapper->pedimentosPagados(trim(strtoupper($server["rfc"])), $input->year, $input->mes, $input->fecha, $input->patente);
                if (!($this->_connectFtp($server))) {
                    continue;
                } else {
                    if (isset($arr) && !empty($arr)) {
                        if (isset($input->fecha)) {
                            $this->_localDir = $directory . DIRECTORY_SEPARATOR . date("Ymd", strtotime($input->fecha)) . "_m3_" . trim(strtoupper($server["rfc"]));
                        } else {
                            $this->_localDir = $directory . DIRECTORY_SEPARATOR . date("Ymd") . "_m3_" . trim(strtoupper($server["rfc"]));
                        }
                        if (!file_exists($this->_localDir)) {
                            mkdir($this->_localDir, 0777, true);
                        }
                        $this->_enviarPagados($arr, $input->debug);
                    }
                }
                $view->arr = $arr;
                unset($arr);
            }
            echo $view->render("send-to-ftp.php");
        } else {
            throw new Exception("Invalid input!");
        }
        return;
    }

    protected function _connectFtp($server) {
        try {
            $this->_conn = ftp_connect($server["url"], $server["port"]);
            $login_result = ftp_login($this->_conn, $server["user"], $server["password"]);
            if ((!$this->_conn) || (!$login_result)) {
                $error = "UNABLE TO CONNECT TO CLIENT FTP {$server["rfc"]}\n"
                        . "Url: {$server["url"]}\n"
                        . "User: {$server["user"]}\n"
                        . "Pass: {$server["password"]}\n";
                echo $error;
                $this->sendErrorEmail("infraestructura", $error);
                return false;
            }
            if (isset($server["remoteFolder"]) && !preg_match("\/", $server["remoteFolder"])) {
                $this->_remoteDir = $server["remoteFolder"];
            } else {
                $this->_remoteDir = "/";
            }
            ftp_pasv($this->_conn, true);
            return true;
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    protected function _enviarPagados($arr, $debug = false) {
        try {
            foreach ($arr as $item) {
                ftp_chdir($this->_conn, $this->_remoteDir);
                if (isset($item["m3"]) && !empty($item["m3"])) {
                    if ($this->_saveLocalFile($item["m3"])) {
                        $this->_uploadFile($item["m3"]);
                    }
                }
                if (isset($item["pagoe"]) && !empty($item["pagoe"])) {
                    if ($this->_saveLocalFile($item["pagoe"])) {
                        $this->_uploadFile($item["pagoe"]);
                    }
                }                
                if (isset($item["pago"]) && !empty($item["pago"])) {
                    if ($this->_saveLocalFile($item["pago"])) {
                        $this->_uploadFile($item["pago"]);
                    }
                }
                if (isset($item["firma"]) && !empty($item["firma"])) {
                    if ($this->_saveLocalFile($item["firma"])) {
                        $this->_uploadFile($item["firma"]);
                    }
                }
            }
            ftp_close($this->_conn);
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    protected function _uploadFile($value) {
        try {
            ftp_put($this->_conn, $value["archivoNombre"], $this->_localDir . DIRECTORY_SEPARATOR . $value["archivoNombre"], FTP_ASCII);
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    protected function _saveLocalFile($value) {
        try {
            file_put_contents($this->_localDir . DIRECTORY_SEPARATOR . $value["archivoNombre"], base64_decode($value["contenido"]));
            if (file_exists($this->_localDir . DIRECTORY_SEPARATOR . $value["archivoNombre"])) {
                return true;
            }
            return false;
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    /**
     * /automatizacion/index/send-cove-xml?patente=3589&fecha=2014-05-06
     * SCRIPT /var/www/oaqintranet/cron/enviarCoveftp.sh
     * 
     */
    public function sendCoveXmlAction() {
        ini_set('display_errors', 1);
        error_reporting(E_ALL | E_STRICT);

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $fecha = $this->_request->getParam('fecha', null);
        $rfc = $this->_request->getParam('rfc', null);

        $ftp = new Automatizacion_Model_FtpMapper();
        $servers = $ftp->getByType('cove', $rfc);
        foreach ($servers as $server):
            $cove = new Vucem_Model_VucemSolicitudesMapper();
            $coves = $cove->getXmlForFtp($server["rfc"], $fecha);

            Zend_Debug::dump($coves);
            die();
            if ($coves == null || empty($coves)) {
                continue;
            }
            $conn_id = ftp_connect($server["url"], $server["port"]);
            $login_result = ftp_login($conn_id, $server["user"], $server["password"]);
            if ((!$conn_id) || (!$login_result)) {
                echo "Ftp-connect failed!";
                die;
            }
            $tmpFolder = "/tmp/ftptmp" . DIRECTORY_SEPARATOR . $server["rfc"] . '_cove_' . date('Ymd');
            if (!file_exists($tmpFolder)) {
                mkdir($tmpFolder, 0777, true);
            }
            foreach ($coves as $item) {
                file_put_contents($tmpFolder . DIRECTORY_SEPARATOR . $item["cove"] . '.xml', $item["xml"]);
                ftp_chdir($conn_id, $server["remoteFolder"]);
                $uploaded = ftp_put($conn_id, $item["cove"] . '.xml', $tmpFolder . DIRECTORY_SEPARATOR . $item["cove"] . '.xml', FTP_BINARY);
                if ($uploaded) {
//                    unlink($tmpFolder . DIRECTORY_SEPARATOR . $item["cove"] . '.xml');
                }
            }
            ftp_close($conn_id);
        endforeach;
    }

    public function descargarM3PedimentoAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $gets = $this->_request->getParams();
        $val = new OAQ_ArchivosM3();
        $content = $val->contenidoPedimento($gets["archivo"], $gets["patente"], $gets["aduana"], $gets["pedimento"]);
        if ($content) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $content["archivoNombre"] . '"'); //<<< Note the " " surrounding the file name
            header('Content-Transfer-Encoding: binary');
            header('Connection: Keep-Alive');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            echo base64_decode($content["contenido"]);
        }
    }

    public function descargarValidacionAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $gets = $this->_request->getParams();
        $val = new OAQ_ArchivosM3();
        $content = $val->contenidoValidacion($gets["archivo"]);
        if ($content) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $content["archivoNombre"] . '"'); //<<< Note the " " surrounding the file name
            header('Content-Transfer-Encoding: binary');
            header('Connection: Keep-Alive');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            echo base64_decode($content["contenido"]);
        }
    }

    public function descargarPagoPedimentoAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $gets = $this->_request->getParams();
        $val = new OAQ_ArchivosM3();

        $content = $val->contenidoPago($gets["archivo"], $gets["patente"], $gets["aduana"], $gets["pedimento"]);
        if ($content) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $content["archivoNombre"] . '"'); //<<< Note the " " surrounding the file name
            header('Content-Transfer-Encoding: binary');
            header('Connection: Keep-Alive');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            echo base64_decode($content["contenido"]);
        }
    }

    public function descargarArchivoValidacionAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $gets = $this->_request->getParams();
        $val = new OAQ_ArchivosM3();

        $content = $val->contenidoValidacion($gets["archivo"], $gets["patente"], $gets["aduana"], $gets["pedimento"]);
        if ($content) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $content["archivoNombre"] . '"'); //<<< Note the " " surrounding the file name
            header('Content-Transfer-Encoding: binary');
            header('Connection: Keep-Alive');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            echo base64_decode($content["contenido"]);
        }
    }

    public function descargarResultadosAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $gets = $this->_request->getParams();
        $val = new OAQ_ArchivosM3();

        $content = $val->contenidoResultado($gets["archivo"], $gets["patente"], $gets["aduana"], $gets["pedimento"]);
        if ($content) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $content["archivoNombre"] . '"'); //<<< Note the " " surrounding the file name
            header('Content-Transfer-Encoding: binary');
            header('Connection: Keep-Alive');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            echo base64_decode($content["contenido"]);
        }
    }

    public function verArchivoPedimentoAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $gets = $this->_request->getParams();
        $val = new OAQ_ArchivosM3();
        $content = $val->extraerPedimento($gets["archivo"], $gets["patente"], $gets["aduana"], $gets["pedimento"]);
        $datosPago = $val->datosPago($gets["patente"], $gets["aduana"], $gets["pedimento"]);

        if ($content) {

            $domtree = new DOMDocument('1.0', 'UTF-8');
            $root = $domtree->createElement("operaciones");
            $xmlRoot = $domtree->appendChild($root);

            $_501 = explode("|", $content['501'][0]);
            $_800 = explode("|", $content['800']);

            $pedimento = $domtree->createElement("pedimento");
            $addData = $xmlRoot->appendChild($pedimento);
            $addData->appendChild($domtree->createElement("numero", $gets["pedimento"]));
            $addData->appendChild($domtree->createElement("patente", $gets["patente"]));
            $addData->appendChild($domtree->createElement("regimen", utf8_encode($_501[5])));
            $addData->appendChild($domtree->createElement("rfcEnt", utf8_encode($_501[8])));
            $addData->appendChild($domtree->createElement("noOperacion", $datosPago["firmaBanco"]));
            $addData->appendChild($domtree->createElement("firmaDig", utf8_encode($_800[3])));
            $addData->appendChild($domtree->createElement("noSerie", utf8_encode($_800[4])));
            $addData->appendChild($domtree->createElement("fechaPago", $datosPago["fechaPago"]));
            $registro = $addData->appendChild($domtree->createElement("registro"));
            $registro->appendChild($domtree->createElement("noRegistro", '500'));
            $registro->appendChild($domtree->createElement("valor", utf8_encode($content['500'])));
            $registro = $addData->appendChild($domtree->createElement("registro"));
            $registro->appendChild($domtree->createElement("noRegistro", '501'));
            $registro->appendChild($domtree->createElement("valor", utf8_encode($content['501'][0])));

            $registros = array('502', '503', '504', '505', '506', '507', '508', '509', '510', '511', '512', '513', '514', '516', '520', '551', '552', '553', '554', '555', '556', '557', '558', '560', '601', '701', '702', '301', '302', '351', '352', '353', '355', '358');
            foreach ($registros as $regkey) {
                if (isset($content[trim($regkey)]) && is_array($content[trim($regkey)])) {
                    foreach ($content[trim($regkey)] as $reg) {
                        $registro = $addData->appendChild($domtree->createElement("registro"));
                        $registro->appendChild($domtree->createElement("noRegistro", trim($regkey)));
                        $registro->appendChild($domtree->createElement("valor", htmlentities(trim($reg), ENT_QUOTES, 'UTF-8')));
//                        $registro->appendChild($domtree->createElement("valor", utf8_encode(trim($reg))) );
//                        $registro->appendChild($domtree->createElement("valor", html_entity_decode(trim($reg))));
                    }
                }
            }
            if (isset($content['800'])) {
                $registro = $addData->appendChild($domtree->createElement("registro"));
                $registro->appendChild($domtree->createElement("noRegistro", '800'));
                $registro->appendChild($domtree->createElement("valor", utf8_encode($content['800'])));
            }
            $output = $domtree->saveXML();
            Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoRender(true);
            Zend_Layout::getMvcInstance()->disableLayout();
            $this->_response->setHeader('Content-Type', 'text/xml; charset=utf-8')
                    ->setBody($output);
        }
    }

    public function printDocumentAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $arch = new Archivo_Model_RepositorioMapper();

        $printer = $this->_request->getParam('printer', null);
        $document = $this->_request->getParam('document', null);

        if ($printer == 1) {
            $port = "Canon iR5570 20.65";
        } elseif ($printer == 2) {
            $port = "Canon iR5570 30.02";
        }
        if (isset($document)) {
            $path = $arch->searchCovePdf($document);
        }

        if (!empty($path)) {

            $out = shell_exec('java -jar /home/samba-share/pdfbox-app-1.8.4.jar PrintPDF -silentPrint -printerName "' . $port . '" ' . $path["ubicacion"]);

            echo Zend_Json_Encoder::encode(array('success' => true));
        } else {
            echo Zend_Json_Encoder::encode(array('success' => false));
        }
    }

    /**
     * /automatizacion/index/reporte-prasad?rfc=JMM931208JY9&patente=3589&aduana=640&year=2014&month=4
     * /automatizacion/index/reporte-prasad?rfc=JMM931208JY9&patente=3589&aduana=646&year=2014&month=4
     * 
     */
    public function reportePrasadAction() {

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $rfc = $this->_request->getParam('rfc', null);
        $patente = $this->_request->getParam('patente', null);
        $aduana = $this->_request->getParam('aduana', null);
        $year = $this->_request->getParam('year', null);
        $month = $this->_request->getParam('month', null);

        if (isset($aduana) && isset($patente) && isset($rfc) && isset($year)) {
            if ($aduana == 640) {
                $sita = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITAW3010640", 1433, "Pdo_Mssql");
            } else if ($aduana == 646) {
                $sita = new OAQ_Sitawin(true, '192.168.0.253', 'sa', 'sqlcointer', 'SITAW3589640', 1433, 'Pdo_Mssql');
            }
            if (isset($sita)) {
                $peds = $sita->reportePrasad($rfc, $year, $month);
            }
            if (isset($peds) && !empty($peds)) {
                $html = '<style>'
                        . 'body {'
                        . 'font-family: sans-serif;'
                        . 'font-size: 12px;'
                        . 'margin:0;'
                        . 'padding:0;'
                        . '}'
                        . 'table {'
                        . 'font-size: 12px;'
                        . 'border-collapse:collapse;'
                        . '}'
                        . 'table td,'
                        . 'table th {'
                        . 'border: 1px #aaa solid;'
                        . 'padding: 2px;'
                        . '}'
                        . 'table th {'
                        . 'background-color: #f3f3f3;'
                        . '}'
                        . '</style>'
                        . '<table>'
                        . '<tr>'
                        . '<th>Referencia</th>'
                        . '<th>CvePedimento</th>'
                        . '<th>ReferenciaAA</th>'
                        . '<th>ClaveProyecto</th>'
                        . '<th>Factura</th>'
                        . '<th>OrdenCompra</th>'
                        . '<th>ClaveCliente</th>'
                        . '<th>NumFactura</th>'
                        . '<th>NumeroParte</th>'
                        . '<th>PiasOrigen</th>'
                        . '<th>Secuencial</th>'
                        . '<th>Fraccion</th>'
                        . '<th>UMC</th>'
                        . '<th>CantUMC</th>'
                        . '<th>PrecioUnitario</th>'
                        . '<th>PatenteOrig</th>'
                        . '<th>PedimentoOrig</th>'
                        . '<th>AduanaOrig</th>'
                        . '</tr>';

                foreach ($peds as $item) {
                    $html .= '<tr>'
                            . "<td>{$item["Referencia"]}</td>"
                            . "<td>{$item["CvePedimento"]}</td>"
                            . "<td>{$item["ReferenciaAA"]}</td>"
                            . "<td>{$item["ClaveProyecto"]}</td>"
                            . "<td>{$item["Factura"]}</td>"
                            . "<td>{$item["OrdenCompra"]}</td>"
                            . "<td>{$item["ClaveCliente"]}</td>"
                            . "<td>{$item["NumFactura"]}</td>"
                            . "<td>{$item["NumeroParte"]}</td>"
                            . "<td>{$item["PiasOrigen"]}</td>"
                            . "<td>{$item["Secuencial"]}</td>"
                            . "<td>{$item["Fraccion"]}</td>"
                            . "<td>{$item["UMC"]}</td>"
                            . "<td>{$item["CantUMC"]}</td>"
                            . "<td style=\"text-align: right\">" . number_format($item["PrecioUnitario"], 4, '.', ',') . "</td>"
                            . "<td>{$item["PatenteOrig"]}</td>"
                            . "<td>{$item["PedimentoOrig"]}</td>"
                            . "<td>{$item["AduanaOrig"]}</td>"
                            . '</tr>';
                }
                $html .= '</table>';
                echo $html;
            }
        }
    }

    public function anexo24ParcialAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $patente = filter_input(INPUT_GET, 'patente', FILTER_SANITIZE_SPECIAL_CHARS);
        $year = filter_input(INPUT_GET, 'year', FILTER_SANITIZE_SPECIAL_CHARS);
        $rfc = filter_input(INPUT_GET, 'rfc', FILTER_SANITIZE_SPECIAL_CHARS);
        $mes = filter_input(INPUT_GET, 'mes', FILTER_SANITIZE_SPECIAL_CHARS);

        if (isset($anexo)) {
            $reporte = $anexo->anexo24Parcial($rfc, $patente, $year, $mes);
            echo "<style>body {margin:0;padding:0; font-family:sans-serif;}"
            . "table {border-collapse:collapse; }"
            . "table th, table td {font-size: 12px; border: 1px #555 solid; padding: 2px 5px;}"
            . "table th {background: #f1f1f1;}"
            . "</style>";
            echo "<table>";
            echo "<tr>";
            echo "<th>Referencia</th>";
            echo "<th>ReferenciaAA</th>";
            echo "<th>ClaveProyecto</th>";
            echo "<th>Factura</th>";
            echo "<th>OrdenCompra</th>";
            echo "<th>ClaveCliente</th>";
            echo "<th>NumFactura</th>";
            echo "<th>NumeroParte</th>";
            echo "<th>PaisOrigen</th>";
            echo "<th>Secuencial</th>";
            echo "<th>Fraccion</th>";
            echo "<th>UMC</th>";
            echo "<th>CantUMC</th>";
            echo "<th>PrecioUnitario</th>";
            echo "<th>Total</th>";
            echo "</tr>";
            foreach ($reporte as $item) {
                echo "<tr>";
                echo "<td>{$item["Trafico"]}</td>";
                echo "<td>&nbsp;</td>";
                echo "<td>&nbsp;</td>";
                echo "<td>{$item["Factura"]}</td>";
                echo "<td>&nbsp;</td>";
                echo "<td>&nbsp;</td>";
                echo "<td>{$item["NumFactura"]}</td>";
                echo "<td>{$item["NumParte"]}</td>";
                echo "<td>{$item["PaisOrigen"]}</td>";
                echo "<td>{$item["Secuencia"]}</td>";
                echo "<td>{$item["Fraccion"]}</td>";
                echo "<td>{$item["UMC"]}</td>";
                echo "<td>{$item["CantUMC"]}</td>";
                echo "<td>{$item["PrecioUnitario"]}</td>";
                echo "<td>{$item["Total"]}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }

    public function actualizarRepositorioAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $patente = $this->_request->getParam('patente', null);
        $aduana = $this->_request->getParam('aduana', null);
        $referencia = $this->_request->getParam('referencia', null);

        $model = new Archivo_Model_RepositorioMapper();
        $result = $model->referenciasNoRfc($patente, $aduana, 50, $referencia, 2);

        $client = new GearmanClient();
        $client->addServer('127.0.0.1', 4730);

        if (!empty($result)) {
            foreach ($result as $item) {
                $array = array(
                    'referencia' => $item["referencia"],
                    'patente' => $item["patente"],
                    'aduana' => $item["aduana"],
                );
                $client->addTaskBackground("repositorio", serialize($array));
                $client->runTasks();
            }
        }
        Zend_Debug::Dump($result);
    }

    /**
     * /automatizacion/index/analizar-repositorio
     * /automatizacion/index/analizar-repositorio?referencia=Q1408727
     * 
     */
    public function analizarRepositorioAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $referencia = $this->_request->getParam('referencia', null);
        $db = Zend_Db_Table::getDefaultAdapter();
        $select = $db->select();
        $arch = new Archivo_Model_RepositorioMapper();
        $result = $arch->referenciasSinRfc(null, null, null, $referencia);
        $context = stream_context_create(array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
                "allow_self_signed" => true
            )
        ));
        foreach ($result as $item) {
            if (isset($item["patente"]) && isset($item["aduana"])) {
                $select = $db->select();
                $select->from('ws_wsdl', array('wsdl', 'patente', 'aduana'))
                        ->where('patente = ?', $item["patente"])
                        ->where('aduana LIKE ?', substr($item["aduana"], 0, 2) . '%')
                        ->where('habilitado = 1');
                $servers = $db->fetchAll($select);
                if ($servers) {
                    foreach ($servers as $server) {
                        $soap = new Zend_Soap_Client($server["wsdl"], array("stream_context" => $context));
                        if (preg_match('/64/', $item["aduana"])) {
                            $rfc = $soap->buscarClienteReferencia($item["referencia"], $item["patente"], 640);
                            if ($rfc === false) {
                                $rfc = $soap->buscarClienteReferencia($item["referencia"], $item["patente"], 646);
                            }
                        } else {
                            $rfc = $soap->buscarClienteReferencia($item["referencia"], $item["patente"], $item["aduana"]);
                        }
                        if (isset($rfc) && !empty($rfc)) {
                            if (isset($rfc["rfc"])) {
                                $updated = $arch->actualizarRfcCliente($item["id"], $rfc["rfc"], $rfc['pedimento']);
                            } elseif (isset($rfc[0]["rfc"])) {
                                $updated = $arch->actualizarRfcCliente($item["id"], $rfc[0]["rfc"], $rfc[0]['pedimento']);
                            }
                        }
                    }
                }
                unset($select);
            }
        }
    }

    /**
     * https://192.168.0.246/automatizacion/index/analizar-repositorio-facturas
     * https://192.168.0.246/automatizacion/index/analizar-repositorio-facturas?referencia=Q1408727
     * 
     */
    public function analizarRepositorioFacturasAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $referencia = $this->_request->getParam('referencia', null);
        $db = Zend_Db_Table::getDefaultAdapter();
        $select = $db->select();
        $arch = new Archivo_Model_RepositorioMapper();
        $result = $arch->facturasSinRfc(null, null, null, $referencia);
        $context = stream_context_create(array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
                "allow_self_signed" => true
            )
        ));
        foreach ($result as $item) {
            if (isset($item["patente"]) && isset($item["aduana"])) {
                $select = $db->select();
                $select->from('ws_wsdl', array('wsdl', 'patente', 'aduana'))
                        ->where('patente = ?', $item["patente"])
                        ->where('aduana LIKE ?', substr($item["aduana"], 0, 2) . '%')
                        ->where('habilitado = 1');
                $servers = $db->fetchAll($select);
                if ($servers) {
                    foreach ($servers as $server) {
                        $soap = new Zend_Soap_Client($server["wsdl"], array("stream_context" => $context));
                        if (preg_match('/64/', $item["aduana"])) {
                            $rfc = $soap->buscarClienteReferencia($item["referencia"], $item["patente"], 640);
                            if ($rfc === false) {
                                $rfc = $soap->buscarClienteReferencia($item["referencia"], $item["patente"], 646);
                            }
                        } else {
                            $rfc = $soap->buscarClienteReferencia($item["referencia"], $item["patente"], $item["aduana"]);
                        }
                        if (isset($rfc) && !empty($rfc)) {
                            if (isset($rfc["rfc"])) {
                                $updated = $arch->actualizarRfcCliente($item["id"], $rfc["rfc"], $rfc['pedimento']);
                            } elseif (isset($rfc[0]["rfc"])) {
                                $updated = $arch->actualizarRfcCliente($item["id"], $rfc[0]["rfc"], $rfc[0]['pedimento']);
                            }
                        }
                    }
                }
                unset($select);
            }
        }
    }

    /**
     * 
     * /automatizacion/index/envio-expedientes?rfc=GCO980828GY0
     * /automatizacion/index/envio-expedientes?rfc=GCO980828GY0&referencia=Q1403621
     * /automatizacion/index/envio-expedientes?rfc=CTM990607US8
     * /automatizacion/index/envio-expedientes?rfc=MQU971209RQ1
     * /automatizacion/index/envio-expedientes?rfc=JMM931208JY9
     * /automatizacion/index/envio-expedientes?rfc=MME921204HZ4
     * php /var/www/workers/ftp_worker.php
     * 
     */
    public function envioExpedientesAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "referencia" => array("StringToUpper"),
                "rfc" => array("StringToUpper"),
            );
            $v = array(
                "referencia" => array(new Zend_Validate_Regex("/^[-_a-zA-Z0-9.]+$/")),
                "rfc" => array(new Zend_Validate_Regex("/^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}$/")),
            );
            $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($i->isValid("rfc")) {
                $misc = new OAQ_Misc();
                $ftp = new Automatizacion_Model_FtpMapper();
                $repo = new Archivo_Model_RepositorioMapper();
                $servers = $ftp->getByType("expedientes", $i->rfc);
                if (empty($servers)) {
                    $this->_helper->json(array("success" => false, "message" => "No hay servidor FTP para RFC: {$i->rfc}"));
                    return;
                } else {
                    $ftp = new OAQ_Ftp(array(
                        "host" => $servers[0]["url"],
                        "port" => $servers[0]["port"],
                        "username" => $servers[0]["user"],
                        "password" => $servers[0]["password"],
                    ));
                    if (true !== ($conn = $ftp->connect())) {
                        $this->_helper->json(array("success" => false, "message" => $conn));
                        return;
                    }
                    $ftp->disconnect();
                }
                $client = new GearmanClient();
                $client->addServer("127.0.0.1", 4730);
                foreach ($servers as $server) {
                    $refNoEnviadas = $repo->referenciasParaEnviar($server["rfc"], $i->isValid("referencia") ? $i->referencia : null);
                    if (empty($refNoEnviadas)) {
                        continue;
                    }
                    foreach ($refNoEnviadas as $referencia) {
                        $noEnviado = $repo->archivosDeReferencia($referencia["referencia"], null, true);
                        if (!empty($noEnviado)) {
                            foreach ($noEnviado as $notSent) {
                                $array = array(
                                    "idRepo" => $notSent["id"],
                                    "idFtp" => $server["id"],
                                    "rfc" => $i->rfc,
                                    "patente" => $referencia["patente"],
                                    "aduana" => $referencia["aduana"],
                                    "pedimento" => $referencia["pedimento"],
                                    "referencia" => $referencia["referencia"],
                                );
                                $client->addTaskBackground("enviar", serialize($array));
                            }
                            $client->runTasks();
                        }
                    }
                    if (!empty($refNoEnviadas)) {
                        $arr = array();
                        foreach ($refNoEnviadas as $item) {
                            $arr[] = array(
                                "patente" => $item["patente"],
                                "aduana" => $item["aduana"],
                                "pedimento" => $item["pedimento"],
                                "referencia" => $item["referencia"],
                                "rfc_cliente" => $item["rfc_cliente"],
                            );
                        }
                        if ($this->getRequest()->isXmlHttpRequest()) {
                            $run = $misc->newBackgroundWorker("ftp_worker", 1);
                            $this->_helper->json(array("success" => true, "data" => $arr));                            
                        }
                    } else {
                        $this->_helper->json(array("success" => false, "message" => "No se encontraron archivos para enviar"));
                    }
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Zend_Exception $ex) {
            throw new Exception($ex->getMessage());
        }
        return;
    }

    protected function isRunning($pid) {
        try {
            $result = shell_exec(sprintf('ps %d', $pid));
            if (count(preg_split("/\n/", $result)) > 2) {
                return true;
            }
        } catch (Exception $e) {
            
        }
        return false;
    }

    public function descargaRegistrosAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $conn_id = ftp_connect('200.76.60.132', 21);
        $login_result = ftp_login($conn_id, 'reg3589', 'qwe234');
        if ((!$conn_id) || (!$login_result)) {
            echo "UNABLE TO CONNECT TO CLIENT FTP\n";
        } else {
            $files = ftp_nlist($conn_id, '/');
        }
        if (empty($files)) {
            return false;
        }
        $model = new Operaciones_Model_ArchivosRegistro001Mapper();
        $tmpFolder = "/tmp/ftpregistros" . DIRECTORY_SEPARATOR . date('Ymd');
        if (!file_exists($tmpFolder)) {
            mkdir($tmpFolder, 0777, true);
        }
        foreach ($files as $remotefile) {
            if (!($model->verify(3589, basename($remotefile)))) {
                $locafile = $tmpFolder . DIRECTORY_SEPARATOR . basename($remotefile);
                if (!ftp_get($conn_id, $locafile, $remotefile, FTP_BINARY)) {
                    echo "Cannot download {$locafile}\n";
                }
                $contenido = file_get_contents($locafile);
                $model->addNew(3589, basename($remotefile), $contenido, date('Y-m-d H:i:s'));
            }
        }
    }

    /**
     * /automatizacion/index/print-carta-porte
     * http://www.ibm.com/developerworks/library/os-tcpdf/        
     */
    public function printCartaPorteAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        require 'tcpdf/cartaporte.php';
        require 'tcpdf/tcpdf_barcodes_2d.php';
        $folio = $this->_request->getParam('folio', 1);
        if (isset($folio)) {
            $model = new Administracion_Model_CartasPorteMapper();
            $data = $model->obtenerFolio($folio);
            $data["colors"]["line"] = array(5, 5, 5);
            $pdf = new CartaPorte($data, 'P', 'pt', 'LETTER');
            $pdf->CreateLetter();
            $pdf->Output('CARTA_PORTE_' . $data["id"] . '.pdf', 'I');
        }
    }

    /**
     * /automatizacion/index/descarga-validacion?id=23
     * su - www-data -c 'php /var/www/workers/ftp_worker.php'
     */
    public function descargaValidacionAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $client = new GearmanClient();
        $client->addServer('127.0.0.1', 4730);
        $id = $this->_request->getParam("id", null);
        if (isset($id)) {
            $data = array(
                'id' => $id,
            );
            $client->addTaskBackground("validador", serialize($data));
            $client->runTasks();
        }
        Zend_Debug::dump($id);
    }

    public function tipoDeCambioAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $model = new Application_Model_TipoCambio();
            $soap = new SAT_Banxico();
            $xml = $soap->tipoDeCambioXml();
            $res = $soap->consultaTipoCambio($xml);
            $match = $soap->insideTags("bm:DataSet", html_entity_decode($res));
            $cambios = $soap->tagsToArray($match);
            $data = array();
            if ($cambios !== false) {
                if (isset($cambios["Series"])) {
                    foreach ($cambios["Series"] as $item) {
                        $tmp = array(
                            'nombre' => preg_replace("/[[:blank:]]+/", " ", utf8_decode($item["@attributes"]["TITULO"])),
                            'serie' => $item["@attributes"]["IDSERIE"],
                            'unidad' => $item["@attributes"]["BANXICO_UNIT_TYPE"],
                            'fecha' => date('Y-m-d H:i:s', strtotime($item["Obs"]["@attributes"]["TIME_PERIOD"])),
                            'valor' => (float) $item["Obs"]["@attributes"]["OBS_VALUE"],
                        );
                        if (($model->verificar($tmp["serie"], $tmp["fecha"])) === false && (int) $tmp["valor"] !== 0) {
                            $model->agregar($tmp);
                        }
                        $data[] = $tmp;
                    }
                }
            }
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

}
