<?php

class Automatizacion_EmailController extends Zend_Controller_Action {

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
    protected $_logger;
    protected $_viewsFolder = null;
    protected $_appconfig;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_logger = Zend_Registry::get("logDb");
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
        $config = array(
            'auth' => 'login',
            'username' => $this->_config->app->infra->email,
            'password' => $this->_config->app->infra->pass,
            'ssl' => 'tls',
            'port' => 26
        );
        $this->_transportSupport = new Zend_Mail_Transport_Smtp($this->_config->app->infra->smtp, $config);
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
            } else if ($tipo == 'cobranza') {
                $this->_emailStorage = new Zend_Mail_Storage_Imap(array(
                    'host' => 'mail.oaq.com.mx',
                    'user' => 'cobranza@oaq.com.mx',
                    'password' => 'Cobr4nz#0',
                ));
            }
        } catch (Exception $e) {
            echo "<p><b>IMAP storage exception " . __METHOD__ . " :</b> <br><b>Exception found:</b>{$e->getMessage()}, <br><b>Line:</b> {$e->getLine()}, <br><b>File:</b> {$e->getFile()}, <br><b>Trace:</b> {$e->getTraceAsString()}</p>";
        }
    }

    /**
     * https://192.168.0.246/automatizacion/email/email-facturas?rfc=MME921204HZ4&fecha=2014-08-11
     * https://192.168.0.246/automatizacion/email/email-facturas?rfc=JMM931208JY9&fecha=2014-08-11
     * https://192.168.0.246/automatizacion/email/email-facturas?rfc=MME921204HZ4&fecha=2014-08-11&debug=true
     * 
     */
    public function emailFacturasAction() {
        $misc = new OAQ_Misc();
        $repo = new Archivo_Model_RepositorioMapper();
        $rfc = $this->getRequest()->getParam('rfc', null);
        $date = $this->getRequest()->getParam('fecha', null);
        $debug = $this->getRequest()->getParam('debug', null);
        if (!isset($date)) {
            $date = date('Y-m-d');
        }
        if (!isset($rfc)) {
            $rfcs = array(
                'MME921204HZ4',
                'JMM931208JY9',
                'BAP060906LEA',
                'SME751021B90',
                'FDQ7904066U0',
            );
        } else {
            $rfcs = array(
                $rfc,
            );
        }
        $sica = new OAQ_Sica();
        foreach ($rfcs as $rfc) {
            $sica = new OAQ_Sica();
            $facturas = $sica->facturacionDelDia($rfc, $date);
            if ($rfc && $date) {
                $tmpArchivos = $repo->getInvoicesByRfcAndDate('OAQ030623UL8', $rfc, $date);
            }
            $tmpDir = "/tmp/redcofidi" . DIRECTORY_SEPARATOR . date('Ymd');
            if (!file_exists($tmpDir)) {
                mkdir($tmpDir, 0777, true);
            }
            if (isset($tmpArchivos) && !empty($tmpArchivos) && $rfc && $date) {
                foreach ($tmpArchivos as $k => $item) {
                    if (file_exists($item["xml"]["ubicacion"])) {
                        if (preg_match('/.xml$/i', $item["xml"]["ubicacion"])) {
                            copy($item["xml"]["ubicacion"], $tmpDir . DIRECTORY_SEPARATOR . basename($item["xml"]["ubicacion"]));
                            $newFiles[] = $tmpDir . DIRECTORY_SEPARATOR . basename($item["xml"]["ubicacion"]);
                        }
                        if (preg_match('/.pdf$/i', $item["pdf"]["ubicacion"])) {
                            $misc->createZipCofidi($item["pdf"]["ubicacion"], $tmpDir . DIRECTORY_SEPARATOR . substr(basename($item["pdf"]["nom_archivo"]), 0, -4) . '.zip');
                            $newFiles[] = $tmpDir . DIRECTORY_SEPARATOR . substr(basename($item["pdf"]["ubicacion"]), 0, -4) . '.zip';
                        }
                    }
                }
                $zipName = 'Facturas_' . date('Y-m-d', time()) . '_' . $misc->alphaID(time()) . '.zip';
                $zipFilename = realpath($tmpDir) . DIRECTORY_SEPARATOR . $zipName;
                $created = $misc->createZip($newFiles, $zipFilename);
                if ($created) {
                    Zend_Mail::setDefaultReplyTo('cobranza@oaq.com.mx');
                    $this->mailStorage('cobranza');
                    $mail = new Zend_Mail('UTF-8');
                    if (!isset($debug)) {
                        $mail->addTo('red.cofidi.inbox@ateb.com.mx');
                        $mail->addCc('cobranza@oaq.com.mx');
                        $mail->addCc('jorge.hdz@oaq.mx');
                        $mail->addBcc("ti.jvaldez@oaq.com.mx");
                        $mail->addBcc("sistemas@oaq.com.mx");
                    } else {
                        $mail->addTo('ti.jvaldez@oaq.com.mx');
                    }
                    $mail->setFrom('cobranza@oaq.com.mx');
                    if ($rfc == 'SME751021B90') {
                        $mail->setSubject(
                                'REDCOFIDI|DIVISION:0000000043|CODIGO:'
                        );
                    } else {
                        $mail->setSubject(
                                'REDCOFIDI|DIVISION:|CODIGO:'
                        );
                    }
                    $class = ' style="font-size: 11px; font-family: sans-serif; border: 1px #999 solid; padding: 2px 3px;"';
                    $classg = ' style="font-size: 11px; font-family: sans-serif; border: 1px #999 solid; padding: 2px 3px; background: #D6FFC4;"';
                    $classr = ' style="font-size: 11px; font-family: sans-serif; border: 1px #999 solid; padding: 2px 3px; background: #FFDBD8;"';
                    $enviado = "<table style=\"border-collapse:collapse;\">";
                    $enviado .= "<tr>";
                    $enviado .= "<th {$class}>Referencia</th>";
                    $enviado .= "<th {$class}>Patente</th>";
                    $enviado .= "<th {$class}>Aduana</th>";
                    $enviado .= "<th {$class}>Pedimento</th>";
                    $enviado .= "<th {$class}>Folio</th>";
                    $enviado .= "<th {$class}>Archivo</th>";
                    $enviado .= "<th {$class}>Usuario</th>";
                    $enviado .= "</tr>";
                    $enviadoplain = "";
                    $i = 1;
                    foreach ($facturas as $item) {
                        $enviado .= "<tr>";
                        $enviado .= "<td rowspan=\"2\" {$class}>{$item["referencia"]}</td>";
                        $enviado .= "<td rowspan=\"2\" {$class}>{$item["patente"]}</td>";
                        $enviado .= "<td rowspan=\"2\" {$class}>{$item["aduana"]}</td>";
                        $enviado .= "<td rowspan=\"2\" {$class}>{$item["pedimento"]}</td>";
                        $enviado .= "<td rowspan=\"2\" {$class}>{$item["cuentaDeGastos"]}</td>";
                        if (isset($tmpArchivos[$item["cuentaDeGastos"]])) {
                            $enviado .= "<td {$classg}>" . basename($tmpArchivos[$item["cuentaDeGastos"]]["xml"]["ubicacion"]) . "</td>";
                        }
                        $enviado .= "<td rowspan=\"2\" {$class}>{$item["usuario"]}</td>";
                        $enviado .= "</tr>";
                        $enviado .= "<tr>";
                        if (isset($tmpArchivos[$item["cuentaDeGastos"]]) && isset($tmpArchivos[$item["cuentaDeGastos"]]["pdf"]["ubicacion"])) {
                            $enviado .= "<td {$classr}>" . basename($tmpArchivos[$item["cuentaDeGastos"]]["pdf"]["ubicacion"]) . "</td>";
                        } else {
                            $enviado .= "<td>&nbsp;</td>";
                        }
                        $enviado .= "</tr>";
                    }
                    $enviado .= "</table>";
                    $mail->setBodyText("Facturas del día: {$date} \nRFC: {$rfc} \nArchivos adjuntados: \n{$enviadoplain}\n\n   -- Email generado de forma automática, no responder. --");
                    $mail->setBodyHtml("<p style=\"margin: 0; padding: 0; font-size:12px; font-family: sans-serif;\">Facturas del día: {$date}</p><p style=\"margin: 0; padding: 0; font-size:12px; font-family: sans-serif;\">RFC: <strong>{$rfc}</strong></p><p style=\"margin: 0; padding: 0; font-size:12px; font-family: sans-serif;\">Archivos adjuntados:</p>{$enviado}<p style=\"margin: 0; padding: 0; font-size:12px; font-family: sans-serif; margin-top: 5px;\">-- Email generado de forma automática, no responder. --</p>");
                    $zipAttach = file_get_contents($zipFilename);
                    $attach = $mail->createAttachment($zipAttach);
                    $attach->type = 'application/octet-stream';
                    $attach->disposition = Zend_Mime::DISPOSITION_INLINE;
                    $attach->encoding = Zend_Mime::ENCODING_BASE64;
                    $attach->filename = $zipName;
                    $sent = $mail->send($this->_transportSupport);
                    if ($sent) {
                        foreach ($newFiles as $rmv) {
                            if (file_exists($rmv)) {
                                unlink($rmv);
                            }
                        }
                        unlink($zipFilename);
                    }
                }
            } else {
                Zend_Mail::setDefaultReplyTo('cobranza@oaq.com.mx');
                $this->mailStorage('cobranza');
                $mail = new Zend_Mail('UTF-8');
                $mail->addTo('cobranza@oaq.com.mx');
                $mail->addCc('ti.jvaldez@oaq.com.mx');
                $mail->addCc('sistemas@oaq.com.mx');
                $mail->setFrom('cobranza@oaq.com.mx');
                $mail->setSubject(
                        'REDCOFIDI|DIVISION:|CODIGO:'
                );
                $mail->setBodyText("Facturas del día: {$date} \nRFC: {$rfc} \nNo hubo facturas que enviar a COFIDI.\n\n   -- Email generado de forma automática, no responder. --");
                $sent = $mail->send($this->_transportSupport);
            }
        }
    }

    public function notificacionesTerminalAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
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
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        $sat = new OAQ_SATValidar();
        $shipments = new Automatizacion_Model_EmbarquesTerminalMapper();
        $imap = new OAQ_IMAP($this->_config->app->notificaciones->smtp, $this->_config->app->notificaciones->email, $this->_config->app->notificaciones->pass, 'INBOX');

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

            if (preg_match('/notificaciones@terminal.com.mx/i', $details["fromAddr"]) && $header->Unseen == "U") {
                echo "<ul>";
                echo "<li><strong>Uid:</strong> " . $uid . "</li>";
                echo "<li><strong>Id:</strong> " . $i . "</li>";
                echo "<li><strong>From:</strong> " . $details["fromAddr"] . "</li>";
                echo "<li><strong>Subject:</strong> " . $details["subject"] . "</li>";
                echo "<li><strong>Date:</strong> " . $details["subject"] . "</li>";
                echo "<li><strong>Flag:</strong> ";
                echo ($header->Unseen == "U") ? "unreadMsg" : "readMsg" . '</li>';

                if (isset($header->date)) {
                    $fecha = date('Y-m-d H:i:s', strtotime($header->date));
                } elseif (isset($header->Date)) {
                    $fecha = date('Y-m-d H:i:s', strtotime($header->Date));
                }
                foreach ($attachments as $k => $attach) {
                    if (preg_match('/.xml/i', $attach["name"])) {
                        echo "<li><strong>Attachtment:</strong> " . $attach["name"] . "</li>";
                        $xml = quoted_printable_decode($imap->downloadAttachment($uid, $attach["partNum"], $attach["enc"]));
                        $array = $sat->satToArray($xml);
                        $noti = $array["Entrada"]["@attributes"];
                        if (!$shipments->checkDoorTag($noti["GuiaHouse"])) {
                            $aerolinea = '';
                            if (preg_match("/TERMINAL LOGISTICS TRANSPORTE FISCALIZADO/i", $noti['Aerolinea'])) {
                                $aerolinea = 'FEDEX';
                            } else if (preg_match("/DHL EXPRESS MEXICO, S.A. DE C.V./i", $noti['Aerolinea'])) {
                                $aerolinea = 'DHL';
                            } else {
                                $aerolinea = $noti['Aerolinea'];
                            }
                            $data = array(
                                'aduana' => 646,
                                'guia_house' => $noti['GuiaHouse'],
                                'guia_master' => $noti['GuiaMaster'],
                                'aerolinea' => $aerolinea,
                                'bultos' => (int) $noti['Bultos'],
                                'cliente' => $noti['Cliente'],
                                'descripcion' => $noti['Descripcion'],
                                'destinatario' => (isset($noti['Destinatario'])) ? $noti['Destinatario'] : null,
                                'embalaje' => $noti['Embalaje'],
                                'dir_destinatario' => (isset($noti['DireccionDestinatario'])) ? $noti['DireccionDestinatario'] : null,
                                'estado_mercancia' => $noti['EstadoMercancia'],
                                'fecha_abandono' => $noti['FechaAbandono'],
                                'fecha_entrada' => date('Y-m-d H:i:s', strtotime($noti['FechaEntrada'])),
                                'fecha_notificacion' => $fecha,
                                'num_vuelo' => $noti['NumVuelo'],
                                'peso_kg' => (float) $noti['Peso'],
                                'peso_lbs' => null,
                                'creado' => date('Y-m-d H:i:s'),
                            );
                            $shipments->addNewShipment($data);
                            if ($imap->copyMessage($i, "INBOX.Historic")) {
                                $imap->deleteMessage($uid);
                            }
                        }
                    }
                }
                echo "</ul>";
            } elseif (preg_match('/aviso@tdqro.com/i', $details["fromAddr"])) {
                $imap->deleteMessage($uid);
            }
        }
        // $imap->expunge();
    }

    /**
     * /automatizacion/email/facturas-terminal
     * SCRIPT: /var/www/oaqintranet/cron/./terminalDescargar.sh 
     * 
     */
    public function facturasTerminalAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        try {
            $filters = array(
                'fecha' => array('StringTrim', 'StripTags'),
            );
            $validators = array(
                'fecha' => array(new Zend_Validate_NotEmpty(), new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
            );
            $input = new Zend_Filter_Input($filters, $validators);
            $input->setData($this->_request->getParams());
            if ($input->isValid('fecha') === true) {
                $db = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589640", 1433, "Pdo_Mssql");
                $sat = new OAQ_SATValidar();
                $repo = new Archivo_Model_Repositorio();
                $des = $this->_appconfig->getParam("invoices");
                $tmp = $this->_appconfig->getParam("attachments");
                if (file_exists($des)) {
                    if (!file_exists($tmp)) {
                        if (!file_exists($tmp)) {
                            mkdir($tmp, 0777, true);
                        }
                    }
                    $emailsMapper = new Automatizacion_Model_EmailsLeidos();
                    $efrom = new OAQ_Emails();
                    $efrom->set_validFrom(array('notificaciones@terminal.com.mx'));
                    $efrom->set_validSubject(array('Facturas Terminal'));
                    $efrom->set_dir($des);
                    $view = new Zend_View();
                    $view->setScriptPath(realpath(dirname(__FILE__)) . '/../views/scripts/data/');
                    $imap = new OAQ_IMAP($this->_appconfig->getParam('factprovServer'), $this->_appconfig->getParam('factprovEmail'), $this->_appconfig->getParam('factprovPass'), 'INBOX');
                    /* $folders = $imap->getFolders(); */
                    $numMessages = $imap->getNumMessages();
                    $emails = array();
                    $j = 0;
                    for ($i = 1; $i <= $numMessages; $i++) {
                        $header = $imap->getHeader($i);
                        if ($header == false) {
                            continue;
                        } 
                        if($input->fecha != date('Y-m-d', strtotime($header->MailDate))) {
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
                        if (($efrom->isValid($details["fromAddr"], $subject[0]->text)) === true) {
                            $tblEmail = new Automatizacion_Model_Table_EmailsLeidos();
                            $tblEmail->setIdEmail($i);
                            $tblEmail->setUuidEmail($uid);
                            $tblEmail->setDe($details["fromAddr"]);
                            $tblEmail->setFecha(date('Y-m-d', strtotime($header->MailDate)));
                            $tblEmail->setHora(date('H:i:s', strtotime($header->MailDate)));
                            $emailsMapper->find($tblEmail);
                            if(null !== ($tblEmail->getId())) {
                                continue;
                            }
                            $tblEmail->setAsunto($subject[0]->text);
                            $efrom->findParts($imap, $i);
                            $emails[] = array(
                                'uid' => $uid,
                                'fecha' => $header->Date,
                                'id' => $i,
                                'from' => $details["fromAddr"],
                                'subject' => $subject[0]->text,
                                'seen' => (($header->Unseen == "U") ? "unReadMsg" : "readMsg"),
                                'deleted' => (($header->Deleted == "D") ? "Deleted" : "UnDeleted"),
                                'attachments' => ($efrom->get_attachments()) ? $efrom->get_attachments() : null
                            );
                            $emailsMapper->save($tblEmail);
                            $j++;
                            if ($j == 1) {
                                break;
                            }
                        }
                        $imap->imapPing();
                        unset($tblEmail);
                    } // for                    
                    if (isset($emails) && !empty($emails)) {
                        foreach ($emails as $k => $item) {
                            if (isset($item["attachments"]) && !empty($item["attachments"])) {
                                foreach ($item["attachments"] as $attach) {
                                    if (preg_match('/.xml$/i', $attach)) {
                                        if (file_exists($des . DIRECTORY_SEPARATOR . $attach)) {
                                            $sat->analizarArchivoXml($des . DIRECTORY_SEPARATOR . $attach);
                                            $data = $sat->get_data();
                                            if (isset($data["observaciones"]) && $data["observaciones"] != '') {
                                                $guia = $db->searchTrackingNumber($data["observaciones"]);
                                            }
                                            $emails[$k]["xml"][] = array(
                                                'guia' => ($guia["guia"]) ? $guia["guia"] :  $data["observaciones"],
                                                'rfcCliente' => ($guia["rfcCliente"]) ? $guia["rfcCliente"] :  null,
                                                'referencia' => ($guia["referencia"]) ? $guia["referencia"] :  null,
                                                'pedimento' => ($guia["pedimento"]) ? ((int) $guia["pedimento"] != 0) ? $guia["pedimento"] : null :  null,
                                                'archivo' => $attach,
                                                'uuid' => $data["uuid"],
                                                'emisor' => $data["emisor_rfc"],
                                                'receptor' => $data["receptor_rfc"],
                                            );
                                            $table = new Archivo_Model_Table_Repositorio($data);
                                            $repo->find($table);
                                            if (null !== ($table->getId())) {
                                                $table->setTipo_archivo(3);
                                                $table->setAduana(640);
                                                $table->setPatente(3589);
                                                $table->setUsuario('Auto');
                                                $table->setCreado(date('Y-m-d H:i:s'));
                                                $table->setNom_archivo($attach);
                                                $table->setUbicacion($des . DIRECTORY_SEPARATOR . $attach);
                                                if (isset($guia)) {
                                                    $table->setObservaciones($guia["guia"]);
                                                    $table->setRfc_cliente($guia["rfcCliente"]);
                                                    if ((int) $guia["pedimento"] != 0) {
                                                        $table->setPedimento($guia["pedimento"]);
                                                    }
                                                    $table->setReferencia($guia["referencia"]);
                                                }
                                                if (file_exists($des . DIRECTORY_SEPARATOR . substr($attach, 0, -4) . '.pdf')) {
                                                    $tablepdf = clone $table;
                                                    $tablepdf->setNom_archivo(substr($attach, 0, -4) . '.pdf');
                                                    $tablepdf->setUbicacion($des . DIRECTORY_SEPARATOR . substr($attach, 0, -4) . '.pdf');
                                                }
                                                $repo->save($table);
                                                if (isset($tablepdf)) {
                                                    $repo->save($tablepdf);
                                                }
                                            } // if found
                                        }
                                    }
                                    if (isset($table)) {
                                        unset($table);
                                    }
                                    if (isset($tablepdf)) {
                                        unset($tablepdf);
                                    }
                                } // foreach attachment
                            }
                        }
                    } // emails
//                    Zend_Debug::dump($emails);
                    $view->emails = $emails;
                    echo $view->render('imap.phtml');
                } else {
                    throw new Exception("Folder don't exists {$des}!");
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Zend_Exception($ex->getMessage());
        }
        return;
    }

    /**
     * /automatizacion/email/facturas-terminal-analizar
     */
    public function facturasTerminalAnalizarAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $db = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589640", 1433, "Pdo_Mssql");
            $sat = new OAQ_SATValidar();
            $repo = new Archivo_Model_Repositorio();
            $fecha = date('Y-m-d');
            $array = $repo->facturasTerminalSinRfc($fecha);
            if (isset($array) && !empty($array)) {
                foreach ($array as $uuid) {
                    $table = new Archivo_Model_Table_Repositorio();
                    $table->setUuid($uuid);
                    $repo->findUuid($table);
                    if (null !== ($table->getId())) {
                        if (file_exists($table->getUbicacion())) {
                            $sat->analizarArchivoXml($table->getUbicacion());
                            $data = $sat->get_data();
                            if (isset($data["observaciones"]) && $data["observaciones"] != '') {
                                $guia = $db->searchTrackingNumber($data["observaciones"]);
                                if (!isset($guia)) {
                                    $guia = $db->searchTrackingNumberConsolidado($data["observaciones"]);
                                }
                                if (isset($guia) && !empty($guia)) {
                                    $upd = array(
                                        'referencia' => $guia["referencia"],
                                        'rfc_cliente' => $guia["rfcCliente"],
                                        'observaciones' => $guia["guia"],
                                        'pedimento' => ((int) $guia["pedimento"] != 0) ? $guia["pedimento"] : null,
                                    );
                                    $repo->update($uuid, $upd);
                                    Zend_Debug::dump($uuid);
                                }
                            }
                        }
                    }
                }
            }
        } catch (Exception $ex) {
            throw new Zend_Exception($ex->getMessage());
        }
    }

    /**
     * /automatizacion/email/facturas-terminal-revision
     */
    public function facturasTerminalRevisionAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $filters = array(
                'fecha' => array('StringTrim', 'StripTags'),
            );
            $validators = array(
                'fecha' => array(new Zend_Validate_NotEmpty(), new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
            );
            $input = new Zend_Filter_Input($filters, $validators);
            $input->setData($this->_request->getParams());
            $db = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589640", 1433, "Pdo_Mssql");
            $sat = new OAQ_SATValidar();
            $repo = new Archivo_Model_Repositorio();
            if ($input->isValid("fecha")) {
                $fecha = $input->fecha;
            } else {
                $fecha = date('Y-m-d');
            }
            $array = $repo->facturasTerminalSinDatos($fecha);
            $des = $this->_appconfig->getParam("invoices");
            if (isset($array) && !empty($array)) {
                foreach ($array as $item) {
                    if (file_exists($item["ubicacion"])) {
                        $sat->analizarArchivoXml($item["ubicacion"]);
                        $data = $sat->get_data();
                    } elseif (file_exists($des . DIRECTORY_SEPARATOR . $item["nom_archivo"])) {
                        $sat->analizarArchivoXml($des . DIRECTORY_SEPARATOR . $item["nom_archivo"]);
                        $data = $sat->get_data();
                    }
                    if (isset($data) && !empty($data)) {
                        if (isset($data["observaciones"]) && $data["observaciones"] != '') {
                            $guia = $db->searchTrackingNumber($data["observaciones"]);
                            if (!isset($guia)) {
                                $guia = $db->searchTrackingNumberConsolidado($data["observaciones"]);
                            }
                        }
                        $table = new Archivo_Model_Table_Repositorio();
                        $table->setId($item["id"]);
                        $repo->get($table);
                        if (null !== ($table->getId())) {
                            $table->setTipo_archivo(40);
                            $table->setUuid($data["uuid"]);
                            $table->setFolio($data["folio"]);
                            $table->setFecha(date('Y-m-d H:i:s', strtotime($data["fecha"])));
                            $table->setEmisor_rfc($data["emisor_rfc"]);
                            $table->setEmisor_nombre($data["emisor_nombre"]);
                            $table->setReceptor_rfc($data["receptor_rfc"]);
                            $table->setReceptor_nombre($data["receptor_nombre"]);
                            $table->setModificado(date('Y-m-d H:i:s'));
                            $table->setModificadoPor("Auto");
                            if (isset($guia) && !empty($guia)) {
                                $table->setReferencia($guia["referencia"]);
                                $table->setRfc_cliente($guia["rfcCliente"]);
                                $table->setObservaciones($guia["guia"]);
                                if(null == ($table->getPedimento())) {
                                    $table->setPedimento(((int) $guia["pedimento"] != 0) ? $guia["pedimento"] : null);
                                }
                            }
                            $repo->save($table);
                            if (($arr = $repo->getPdf(substr($item["nom_archivo"], 0, -4)))) {
                                $tablepdf = clone $table;
                                $tablepdf->setId($arr["id"]);
                                $tablepdf->setNom_archivo($arr["nom_archivo"]);
                                $tablepdf->setUbicacion($arr["ubicacion"]);
                                $repo->save($tablepdf);
                            }
                        }
                    }
                    unset($table);
                    unset($tablepdf);
                }
            }
        } catch (Exception $ex) {
            throw new Zend_Exception($ex->getMessage());
        }
    }

    /**
     * /automatizacion/email/enviar-facturas-terminal?fecha=2014-06-10&debug=true
     * /automatizacion/email/enviar-facturas-terminal?fecha=2014-05-20&debug=true <----- FALLA
     * SCRIPT /var/www/oaqintranet/cron/./terminalEnviar.sh 
     * 
     */
    public function enviarFacturasTerminalAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        echo "<style>
                body {
                  font-size: 12px;
                  font-family: sans-serif;
                  margin:0;
                  padding:0;
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
                table {
                  font-size: 12px;
                  font-family: sans-serif;
                  border-collapse:collapse;
                }
                table td,
                table th {
                  border: 1px #999 solid;
                  padding: 2px 5px;
                }
            </style>";

        $misc = new OAQ_Misc();
        $sica = new OAQ_Sica();
        $repo = new Archivo_Model_RepositorioMapper();

        $date = $this->getRequest()->getParam('fecha', null);
        $mes = $this->getRequest()->getParam('mes', null);
        $year = $this->getRequest()->getParam('year', null);
        $pedimento = $this->getRequest()->getParam('pedimento', null);
        $debug = $this->getRequest()->getParam('debug', null);
        $rfcc = $this->getRequest()->getParam('rfc', null);

        if (!isset($date) && !(isset($mes) && isset($year))) {
            $date = date('Y-m-d');
        }
        if (isset($debug) && $debug == true) {
            $rfcs = array(
                1 => array(
                    'rfc' => 'GCA0107267Y9',
                    'emails' => array('ti.jvaldez@oaq.com.mx'),
                ),
            );
        } else {
            $rfcs = array(
                1 => array(
                    'rfc' => 'GCA0107267Y9',
                    'emails' => array('matias.martinez@cargoquin.com'),
//                    'bcc' => array('soporte@oaq.com.mx'),
                ),
                2 => array(
                    'rfc' => 'GIV021204B1A',
                    'emails' => array('myong@guardian.com'),
//                    'bcc' => array('soporte@oaq.com.mx'),
                ),
                3 => array(
                    'rfc' => 'CTM990607US8',
                ),
                4 => array(
                    'rfc' => 'TEM670628A19',
                ),
                5 => array(
                    'rfc' => 'CIN0309091D3',
                ),
                6 => array(
                    'rfc' => 'JMM931208JY9',
                ),
                7 => array(
                    'rfc' => 'VEN940203EU6',
                ),
                8 => array(
                    'rfc' => 'DAL870401MGA',
                ),
                9 => array(
                    'rfc' => 'WMO1004098Z6',
                ),
            );
        }
        foreach ($rfcs as $k => $rfc) {
            if (isset($rfcc)) {
                if ($rfcc !== $rfc["rfc"]) {
                    continue;
                }
            }
            if (!isset($rfc["emails"])) {
                continue;
            }
            $archivos = array();
            $tmpArchivos = array();
            if (isset($date)) {
                $ctaTerminal = $sica->facturasTerminal($rfc['rfc'], $date, $pedimento);
            } elseif (isset($mes) && isset($year)) {
                $ctaTerminal = $sica->facturasTerminalMes($rfc['rfc'], $year, $mes);
            }
//            Zend_Debug::dump($ctaTerminal); 
//            Zend_Debug::dump($rfcs); 
//            die();
            $html = '<h1 style="margin:0;padding:0;">SICA</h1>'
                    . '<table>'
                    . '<tr>'
                    . '<th>#</th>'
                    . '<th>nombre</th>'
                    . '<th>rfc</th>'
                    . '<th>cuentaDeGastos</th>'
                    . '<th>patente</th>'
                    . '<th>pedimento</th>'
                    . '<th>referencia</th>'
                    . '<th>uuid</th>'
                    . '<th>facturaProveedor</th>'
                    . '<th>fecha</th>'
                    . '</tr>';
            foreach ($ctaTerminal as $k => $t) {
                $html .= '<tr>'
                        . '<td>' . $k . '</td>'
                        . '<td>' . $t["nombre"] . '</td>'
                        . '<td>' . $t["rfc"] . '</td>'
                        . '<td>' . $t["cuentaDeGastos"] . '</td>'
                        . '<td>' . $t["patente"] . '</td>'
                        . '<td>' . $t["pedimento"] . '</td>'
                        . '<td>' . $t["referencia"] . '</td>'
                        . '<td>' . $t["uuid"] . '</td>'
                        . '<td>' . $t["facturaProveedor"] . '</td>'
                        . '<td>' . $t["fecha"] . '</td>'
                        . '</tr>';
            }
            $html .= '</table>';
            echo $html;
            unset($html);

            foreach ($ctaTerminal as $cta) {
                if ((isset($cta["uuid"]) && $cta["uuid"] != '') && !preg_match('/^TL/i', $cta["uuid"])) {
                    $invoices = $repo->getInvoicesByRfcAndInvoiceByUuid('TLO050804QY7', $cta["uuid"]);
                    if (count($invoices) == 1) {
                        if (isset($cta["facturaProveedor"]) && $cta["facturaProveedor"] != '') {
                            $invoices = $repo->getInvoicesByRfcAndInvoice('TLO050804QY7', $cta["facturaProveedor"]);
                        }
                    }
                } elseif (isset($cta["facturaProveedor"]) && $cta["facturaProveedor"] != '') {
                    $invoices = $repo->getInvoicesByRfcAndInvoice('TLO050804QY7', $cta["facturaProveedor"]);
                } else {
                    continue;
                }
                if ($invoices && !empty($invoices)) {
                    foreach ($invoices as $invoice) {
                        $invoice["cuentaDeGastos"] = $cta["cuentaDeGastos"];
                        $invoice["patente"] = $cta["patente"];
                        $invoice["pedimento"] = $cta["pedimento"];
                        $invoice["referencia"] = $cta["referencia"];
                        $invoice["facturaProveedor"] = $cta["facturaProveedor"];
                        $tmpArchivos[] = $invoice;
                    }
                }
                unset($invoices);
                unset($invoice);
            }
//            Zend_Debug::dump($tmpArchivos); 
//            die();
            $model = new Archivo_Model_RepositorioMapper();
            $html = '<h1 style="margin:0;padding:0;">REPOSITORIO</h1>'
                    . '<table>'
                    . '<tr>'
                    . '<th>#</th>'
                    . '<th>id</th>'
                    . '<th>folio</th>'
                    . '<th>uuid</th>'
                    . '<th>nom_archivo</th>'
                    . '<th>cuentaDeGastos</th>'
                    . '<th>patente</th>'
                    . '<th>pedimento</th>'
                    . '<th>referencia</th>'
                    . '<th>facturaProveedor</th>'
                    . '</tr>';
            foreach ($tmpArchivos as $k => $t) {
                $html .= '<tr>'
                        . '<td>' . $k . '</td>'
                        . '<td>' . $t["id"] . '</td>'
                        . '<td>' . $t["folio"] . '</td>'
                        . '<td>' . $t["uuid"] . '</td>'
                        . '<td>' . $t["ubicacion"] . '</td>'
                        . '<td>' . $t["cuentaDeGastos"] . '</td>'
                        . '<td>' . $t["patente"] . '</td>'
                        . '<td>' . $t["pedimento"] . '</td>'
                        . '<td>' . $t["referencia"] . '</td>'
                        . '<td>' . $t["facturaProveedor"] . '</td>'
                        . '</tr>';
                if (isset($t["referencia"]) && $t["referencia"] != '' && isset($t["patente"]) && $t["patente"] != '') {
                    $model->actualizarFacturaTerminal($t["folio"], 42, "TLO050804QY7", $t["patente"], $t["referencia"]);
                }
            }
            $html .= '</table>';
            echo $html;
            unset($html);
            unset($ctaTerminal);
            $newFiles = array();
            foreach ($tmpArchivos as $f) {
                if (preg_match('/.xml$/i', $f["nom_archivo"])) {
                    $archivos[] = array(
                        "cuentaDeGastos" => $f["cuentaDeGastos"],
                        "patente" => $f["patente"],
                        "pedimento" => $f["pedimento"],
                        "referencia" => $f["referencia"],
                        "facturaProveedor" => $f["folio"],
                        'nom_archivo' => pathinfo($f["ubicacion"], PATHINFO_BASENAME),
                        'ubicacion' => $f["ubicacion"],
                    );
                    $newFiles[] = $f["ubicacion"];
                }
                if (preg_match('/.pdf$/i', $f["nom_archivo"])) {
                    $archivos[] = array(
                        "cuentaDeGastos" => $f["cuentaDeGastos"],
                        "patente" => $f["patente"],
                        "pedimento" => $f["pedimento"],
                        "referencia" => $f["referencia"],
                        "facturaProveedor" => $f["folio"],
                        'nom_archivo' => pathinfo($f["ubicacion"], PATHINFO_BASENAME),
                        'ubicacion' => $f["ubicacion"],
                    );
                    $newFiles[] = $f["ubicacion"];
                }
            }
            unset($tmpArchivos);
            Zend_Mail::setDefaultReplyTo('cobranza@oaq.com.mx');
            $this->mailStorage('cobranza');
            $mail = new Zend_Mail('UTF-8');
            if (is_array($rfc["emails"])) {
                foreach ($rfc["emails"] as $e) {
                    $mail->addTo($e);
                }
            }
            if (isset($rfc["bcc"])) {
                foreach ($rfc["bcc"] as $e) {
                    $mail->addBcc($e);
                }
            }
            $mail->setFrom('cobranza@oaq.com.mx');
            if (!isset($pedimento)) {
                $mail->setSubject(
                        'Facturas Terminal ' . $rfc["rfc"] . " con fecha " . $date
                );
            } else {
                $mail->setSubject(
                        'Facturas Terminal Pedimento ' . $pedimento
                );
            }
            if (isset($archivos) && !empty($archivos)) {
                if (!isset($pedimento) && isset($date)) {
                    $zipName = 'FacturasTerminal_' . date('Ymd', strtotime($date)) . '_' . $misc->alphaID(time()) . '.zip';
                } elseif (isset($pedimento)) {
                    $zipName = 'FacturasTerminal_' . $pedimento . '_' . $misc->alphaID(time()) . '.zip';
                }
                $zipFilename = '/tmp' . DIRECTORY_SEPARATOR . $zipName;
                $zip = new ZipArchive();
                if ($zip->open($zipFilename, ZIPARCHIVE::CREATE) === TRUE) {
                    foreach ($newFiles as $file) {
                        if (file_exists($file) && is_readable($file)) {
                            $zip->addFile($file, basename($file));
                        } else {
                            echo "Archivo {$file} no existe <br />";
                        }
                    }
                    $zip->close();
                } else {
                    echo "No se puede crear el archivo Zip {$zipFilename}<br />";
                    return false;
                }
                if (!file_exists($zipFilename)) {
                    echo "Archivo Zip {$zipFilename} no existe<br />";
                    return false;
                }
                $privacy = "<p class=MsoNormal><b><span style='font-size:7.0pt;font-family:\"Arial\",\"sans-serif\";
                  mso-fareast-font-family:\"Times New Roman\";color:#777777'>AVISO:</span></b><span
                  style='font-size:7.0pt;font-family:\"Arial\",\"sans-serif\";mso-fareast-font-family:
                  \"Times New Roman\";color:#777777'> Este mensaje (incluyendo cualquier archivo
                  anexo) es confidencial y personal. En caso de que usted lo reciba por error
                  favor de notificar a soporte@oaq.mx regresando este correo electrónico y
                  eliminando este mensaje de su sistema. Cualquier uso o difusión en forma
                  total o parcial del mensaje así como la distribución o copia de este
                  comunicado está estrictamente prohibida. Favor de tomar en cuenta que los
                  e-mails son susceptibles de cambio.<br>
                  <br>
                  <em><b><span style='font-family:\"Arial\",\"sans-serif\"'>DISCLAIMER:</span></b></em><em><span
                  style='font-family:\"Arial\",\"sans-serif\"'> This message (including any
                  attachments) is confidential and may be privileged. If you have received it
                  by mistake please notify soporte@oaq.mx by returning this e-mail and delete
                  this message from your system. Any unauthorized use or dissemination of this
                  message in whole or in part and distribution or copying of this communication
                  is strictly prohibited. Please note that e-mails are susceptible to change.</span></em><o:p></o:p></span></p>";
                $p = ' style="font-family: sans-serif; font-size: 12px; margin:0; padding:0"';
                $th = ' style="font-family: sans-serif; font-size: 12px; margin:0; padding: 2px 5px; border: 1px #777 solid; background: #e5e5e5"';
                $td = ' style="font-family: sans-serif; font-size: 12px; margin:0; padding: 2px 5px; border: 1px #777 solid; text-align: center"';
                $enviado = "<table style=\"border-collapse:collapse;\">";
                $enviado .= "<tr>";
                $enviado .= "<th{$th}>Cuenta de Gastos</th>";
                $enviado .= "<th{$th}>Patente</th>";
                $enviado .= "<th{$th}>Pedimento</th>";
                $enviado .= "<th{$th}>Referencia</th>";
                $enviado .= "<th{$th}>Factura</th>";
                $enviado .= "<th{$th}>Nombre de Archivo</th>";
                $enviado .= "</tr>";
                foreach ($archivos as $a) {
                    $enviado .= "<tr>";
                    $enviado .= "<td{$td}>" . $a["cuentaDeGastos"] . "</td>";
                    $enviado .= "<td{$td}>" . $a["patente"] . "</td>";
                    $enviado .= "<td{$td}>" . $a["pedimento"] . "</td>";
                    $enviado .= "<td{$td}>" . $a["referencia"] . "</td>";
                    $enviado .= "<td{$td}>" . $a["facturaProveedor"] . "</td>";
                    $enviado .= "<td{$td}>" . $a["nom_archivo"] . "</td>";
                    $enviado .= "</tr>";
                }
                $enviado .= "</table>";
                echo $enviado;
                $mail->setBodyHtml("<p{$p}>Facturas del día: {$date}</p>
                        <p{$p}>RFC: {$rfc["rfc"]}</p>
                        <p{$p}>{$enviado}</p>
                        <br>
                        <p{$p}>-- Email generado de forma automática, no responder. --</p>
                        {$privacy}");

                $zipAttach = file_get_contents($zipFilename);
                $attach = $mail->createAttachment($zipAttach);
                $attach->type = 'application/octet-stream';
                $attach->disposition = Zend_Mime::DISPOSITION_INLINE;
                $attach->encoding = Zend_Mime::ENCODING_BASE64;
                $attach->filename = $zipName;
                $sent = $mail->send($this->_transportSupport);
            }
        }
    }

    /**
     * /automatizacion/email/enviar-cofidi?factura=12934
     * /automatizacion/email/enviar-cofidi?factura=12934&debug=true&email=ti.jvaldez@oaq.com.mx
     * 
     * @return boolean
     */
    public function enviarCofidiAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $context = stream_context_create(array(
                "ssl" => array(
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                    "allow_self_signed" => true
                )
            ));
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "factura" => "Digits",
            );
            $v = array(
                "factura" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($i->isValid("factura")) {
                $sat = new OAQ_SATValidar();
                $soap = new Zend_Soap_Client("https://192.168.200.5/casaws/zfsoapsica?wsdl", array(
                    "compression" => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_DEFLATE, 
                    "stream_context" => $context)
                        );
                $invoice = $soap->folioCdfi($i->factura);
                $tmpDir = "/tmp/cofidi";
                if (!file_exists($tmpDir)) {
                    mkdir($tmpDir, 0777, true);
                }
                if (!empty($invoice)) {
                    $filename = $tmpDir . DIRECTORY_SEPARATOR . $invoice["filename"];
                    file_put_contents($filename, html_entity_decode($invoice["xml"]));
                    $xmlArray = $sat->satToArray(html_entity_decode($invoice["xml"]));
                    if (isset($xmlArray["Receptor"]["@attributes"]["rfc"])) {
                        $receptor = $xmlArray["Receptor"]["@attributes"]["rfc"];
                    }
                } else {
                    $error = "ERROR: El archivo XML no existe en el servidor.";
                }
                $pdf = $soap->searchPdf($i->factura);
                if (!empty($pdf)) {
                    $pdfFilename = $tmpDir . DIRECTORY_SEPARATOR . $pdf["filename"];
                    if (file_exists($pdfFilename)) {
                        unlink($pdfFilename);
                    }
                    if (!file_exists($pdfFilename)) {
                        file_put_contents($pdfFilename, base64_decode($pdf["content"]));
                    }
                } else {
                    $error = "ERROR: El archivo PDF no existe.";
                }
                if (!isset($error)) {
                    $mapp = new Automatizacion_Model_CofidiEmailsMapper();
                    $ccs = $mapp->findAll(0);
                    $mapper = new Automatizacion_Model_CofidiMapper();
                    $cofidi = $mapper->obtenerCofidi($receptor);
                    if (isset($xmlArray["Complemento"]["TimbreFiscalDigital"]["@attributes"]["UUID"])) {
                        $uuid = $xmlArray["Complemento"]["TimbreFiscalDigital"]["@attributes"]["UUID"];
                    } else {
                        $uuid = "n/d";
                    }
                    if (!empty($invoice) && !empty($pdf) && isset($cofidi) && $cofidi != false && file_exists($filename) && file_exists($pdfFilename)) {
                        $e = new OAQ_Emails();
                        $e->cofidi();
                        if (file_exists($filename)) {
                            $e->addAttachment(file_get_contents($filename), basename($filename));
                        }
                        if (file_exists($pdfFilename)) {
                            $e->addAttachment(file_get_contents($pdfFilename), basename($pdfFilename));
                        }
                        $e->codifiSetup($cofidi["asunto"], $ccs, $uuid);
                        $e->send();
                        $this->_helper->json(array("success" => true));
                    }
                } else {
                    $this->_helper->json(array("success" => false, "error" => $error));
                }
            } else {
                throw new Exception("Invalid Input!");
            }
        } catch (Zend_Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    /**
     * /automatizacion/email/enviar-cdfi-cofidi?debug=true
     * /automatizacion/email/enviar-cdfi-cofidi?debug=true&fecha=2014-12-10
     * /automatizacion/email/enviar-cdfi-cofidi?rfc=GAM950228IZ5&fecha=2016-12-01
     * /automatizacion/email/enviar-cdfi-cofidi?rfc=MME921204HZ4&fecha=2015-01-01
     * 
     * su - www-data -c 'php /var/www/portalprod/workers/emailc_worker.php'
     * 
     */
    public function enviarCdfiCofidiAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "debug" => "StringToLower",
                "folio" => "Digits",
                "rfc" => "StringToUpper",
            );
            $v = array(
                "debug" => array("NotEmpty", new Zend_Validate_InArray(array("true"))),
                "folio" => array("NotEmpty", new Zend_Validate_Int()),
                "fecha" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
                "rfc" => array(new Zend_Validate_Regex("/^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/")),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if (!$input->isValid("fecha")) {
                $input->setData(array("fecha" => date("Y-m-d")));
            }
            if (!$input->isValid("debug")) {
                $gm = new Application_Model_GearmanMapper();
                $workerName = "emailc_worker.php";
                $workerPath = $gm->getProcessPath($workerName);
                if (APPLICATION_ENV === "production") {
                    exec("php {$workerPath} > /dev/null &");
                }
            }
            $sica = new OAQ_Sica();
            $model = new Automatizacion_Model_CofidiMapper();
            if (!$input->isValid("folio")) {
                $customers = $model->clientes($input->rfc);
                foreach ($customers as $customerData) {
                    $facturas = $sica->facturacionDelDia($customerData["rfc"], $input->fecha);
                    $this->_gearmanFacturas($facturas, $customerData, $input->debug);
                }
            } else {
                $factura = $sica->obtenerFolio($input->folio);
                if (isset($factura) && !empty($factura)) {
                    $customerData = $model->obtenerCliente($factura[0]["rfc"]);
                    $this->_gearmanFacturas($factura, $customerData, $input->debug);
                }
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    protected function _gearmanFacturas($array, $customerData, $debug = null, $fecha = null) {
        $client = new GearmanClient();
        $client->addServer("127.0.0.1", 4730);
        if (isset($array) && !empty($array)) {
            foreach ($array as $invoice) {
                if ($customerData["tipo"] == "cofidi") {
                    $array = array(
                        "rfc" => $invoice["rfc"],
                        "razonSocial" => $customerData["razonSocial"],
                        "cuentaDeGastos" => $invoice["cuentaDeGastos"],
                        "patente" => $invoice["patente"],
                        "aduana" => $invoice["aduana"],
                        "pedimento" => $invoice["pedimento"],
                        "referencia" => $invoice["referencia"],
                        "usuario" => $invoice["usuario"],
                        "asunto" => $customerData["asunto"],
                        "debug" => $debug,
                        "fecha" => $fecha,
                    );
                    $client->addTaskBackground("cofidienviar", serialize($array));
                }
                if ($customerData["tipo"] == "ftp") {
                    $array = array(
                        "rfc" => $invoice["rfc"],
                        "razonSocial" => $customerData["razonSocial"],
                        "cuentaDeGastos" => $invoice["cuentaDeGastos"],
                        "patente" => $invoice["patente"],
                        "aduana" => $invoice["aduana"],
                        "pedimento" => $invoice["pedimento"],
                        "referencia" => $invoice["referencia"],
                        "usuario" => $invoice["usuario"],
                        "host" => $customerData["host"],
                        "username" => $customerData["username"],
                        "password" => $customerData["password"],
                        "puerto" => $customerData["puerto"],
                        "carpeta" => $customerData["carpeta"],
                        "debug" => (isset($debug) && $debug == "true") ? true : false,
                        "fecha" => $fecha,
                    );
                    $client->addTaskBackground("ftpenviar", serialize($array));
                }
            }
        }
        $client->runTasks();
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

    /**
     * su - www-data -c 'php /var/www/workers/trafico_worker.php'
     * /automatizacion/email/descarga-pedimentos
     * 
     */
    public function descargaPedimentosAction() {
        $client = new GearmanClient();
        $client->addServer('127.0.0.1', 4730);
        $aduana = $this->_request->getParam('aduana', null);
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

        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        if (isset($aduana) && $aduana == '3589-640') {
            $imap = new OAQ_IMAP($this->_appconfig->getParam('pedimentosServer'), $this->_appconfig->getParam('pedimentosEmail'), $this->_appconfig->getParam('pedimentosPass'), 'INBOX');
        } elseif (isset($aduana) && $aduana == '3574-160') {
            $imap = new OAQ_IMAP($this->_appconfig->getParam('arribosServer'), $this->_appconfig->getParam('arribosEmail'), $this->_appconfig->getParam('arribosPass'), 'INBOX');
        } else {
            die("Debe especificar una aduana.");
        }
        $folders = $imap->getFolders();
        echo "<ul>";
        echo "<li><strong>Folders</strong></li>";
        foreach ($folders as $folder) {
            echo '<li>' . imap_utf7_decode($folder) . '</li>';
        }
        echo "</ul>";

        $path = '/tmp/pedimentos';
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

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
            echo "<ul>";
            echo "<li><strong>Uid:</strong> " . $uid . "</li>";
            echo "<li><strong>Id:</strong> " . $i . "</li>";
            echo "<li><strong>From:</strong> " . $details["fromAddr"] . "</li>";
            echo "<li><strong>Subject:</strong> " . $details["subject"] . "</li>";
            echo "<li><strong>Flag: " . (($header->Unseen == "U") ? "unReadMsg" : "readMsg") . "</strong> ";
            echo "<li><strong>Flag: " . (($header->Deleted == "D") ? "Deleted" : "UnDeleted") . "</strong> ";
            echo "</ul>";

            if (preg_match('/Acuse de pago/i', $details["subject"])) {
                $mailStruct = $imap->getStructure($i);
                $flattenedParts = $imap->flattenParts($mailStruct->parts);
                foreach ($flattenedParts as $partNumber => $part) {
                    switch ($part->type) {
                        case 0:
                            $message = $imap->getPart($i, $partNumber, $part->encoding);
//                            Zend_Debug::dump($message);
                            $content = preg_split('/\r\n|\r|\n/', $message);
                            $data = array();
                            foreach ($content as $line) {
                                if (preg_match('/pedimento:/i', $line)) {
                                    $data["pedimento"] = $this->_explodeDelimiter($line, ':', 1);
                                }
                                if (preg_match('/aduana:/i', $line)) {
                                    $data["aduana"] = $this->_explodeDelimiter($line, ':', 1);
                                }
                                if (preg_match('/patente:/i', $line)) {
                                    $data["patente"] = $this->_explodeDelimiter($line, ':', 1);
                                }
                                if (preg_match('/referencia:/i', $line)) {
                                    $data["referencia"] = $this->_explodeDelimiter($line, ':', 1);
                                }
                            }
//                            Zend_Debug::Dump($data);
                            break;
                        case 1:
                            // multi-part headers, can ignore
                            break;
                        case 2:
                            // attached message headers, can ignore
                            break;
                        case 3: // application
                        case 4: // audio
                        case 5: // image
                        case 6: // video
                        case 7: // other
                            $filename = $imap->getFilenameFromPart($part);
//                            Zend_Debug::dump($filename);
                            if ($filename) {
                                // it's an attachment
                                $attachment = $imap->getPart($i, $partNumber, $part->encoding);
                            } else {
                                // don't know what it is
                            }
                            break;
                    }
                } // foreach part
                if (isset($data) && !empty($data) && isset($filename) && isset($attachment)) {
                    if (preg_match('/.pdf$/i', $filename)) {
                        if (!file_exists($path . DIRECTORY_SEPARATOR . $filename)) {
                            $fh = fopen($path . DIRECTORY_SEPARATOR . $filename, 'w');
                            fwrite($fh, $attachment);
                            fclose($fh);
                        }
                        $process = array(
                            'patente' => $data["patente"],
                            'aduana' => $data["aduana"],
                            'pedimento' => $data["pedimento"],
                            'filename' => $path . DIRECTORY_SEPARATOR . $filename
                        );
                        $client->addTaskBackground("pdfpedimento", serialize($process));
                        $client->runTasks();
                    }
                }
//                if($imap->copyMessage($i,"INBOX.Historic")) {
////                    $imap->deleteMessage($uid);
//                }
            } elseif (preg_match('/del Pago de Pedimento/i', $details["subject"])) {
                $mailStruct = $imap->getStructure($i);
                $flattenedParts = $imap->flattenParts($mailStruct->parts);
                foreach ($flattenedParts as $partNumber => $part) {
                    switch ($part->type) {
                        case 0:
                            $message = $imap->getPart($i, $partNumber, $part->encoding);
                            $content = preg_split('/\r\n|\r|\n/', $message);
//                            Zend_Debug::dump($content);
                            $data = array();
                            foreach ($content as $line) {
                                if (preg_match('/^pedimento/i', trim($line))) {
                                    $data["pedimento"] = $this->_explodeDelimiter($line, ':', 1);
                                }
                                if (preg_match('/^aduana/i', trim($line))) {
                                    $data["aduana"] = $this->_explodeDelimiter($line, ':', 1);
                                }
                                if (preg_match('/^patente/i', trim($line))) {
                                    $data["patente"] = $this->_explodeDelimiter($line, ':', 1);
                                }
                                if (preg_match('/^referencia/i', trim($line))) {
                                    $data["referencia"] = $this->_explodeDelimiter($line, ':', 1);
                                }
                                if (preg_match('/^RFC Importador/i', trim($line))) {
                                    $data["rfcCliente"] = $this->_explodeDelimiter($line, ':', 1);
                                }
                            }
//                            Zend_Debug::Dump($data);
                            break;
                        case 1:
                            // multi-part headers, can ignore
                            break;
                        case 2:
                            // attached message headers, can ignore
                            break;
                        case 3: // application
                        case 4: // audio
                        case 5: // image
                        case 6: // video
                        case 7: // other
                            $attachmentName = $imap->getFilenameFromPart($part);
//                            Zend_Debug::dump($filename);
                            if ($attachmentName) {
                                $attachment = $imap->getPart($i, $partNumber, $part->encoding);
                            } else {
                                // don't know what it is
                            }
                            break;
                    }
                } // foreach part
//                Zend_Debug::dump($data);
                if (isset($data) && !empty($data) && isset($attachmentName) && isset($attachment)) {
                    if (preg_match('/.zip$/i', $attachmentName)) {
                        $filename = $data["aduana"] . '-' . $data["patente"] . '-' . $data["pedimento"] . '.zip';
                        $unzipPath = $path . DIRECTORY_SEPARATOR . $data["aduana"] . '-' . $data["patente"] . '-' . $data["pedimento"];
                        if (!file_exists($path . DIRECTORY_SEPARATOR . $filename)) {
                            $fh = fopen($path . DIRECTORY_SEPARATOR . $filename, 'w');
                            fwrite($fh, $attachment);
                            fclose($fh);
                            $zip = new ZipArchive;
                            $res = $zip->open($path . DIRECTORY_SEPARATOR . $filename);
                            if ($res === TRUE) {
                                if (!file_exists($unzipPath)) {
                                    mkdir($unzipPath, 0777, true);
                                }
                                $zip->extractTo($unzipPath);
                                $zip->close();
//                                $files = scandir($unzipPath);
                                $directory = new RecursiveDirectoryIterator($unzipPath);
                                $iterator = new RecursiveIteratorIterator($directory);
                                $files = new RegexIterator($iterator, '/^.+\.pdf$/i', RecursiveRegexIterator::GET_MATCH);
                            }
                        }
                        foreach ($files as $name => $object) {
                            $process = array(
                                'patente' => $data["patente"],
                                'aduana' => $data["aduana"],
                                'pedimento' => $data["pedimento"],
                                'filename' => $name
                            );
                            $client->addTaskBackground("pdfpedimento", serialize($process));
                        }
                        $client->runTasks();
                    }
                }
//                if($imap->copyMessage($i,"INBOX.Historic")) {
////                    $imap->deleteMessage($uid);
//                }
            } else {
//                $imap->deleteMessage($uid);
            }
            if ($i == 150) {
                break;
            }
            unset($data);
        }
//        $imap->expunge();
    }

    protected function _removeEmails($rmvIds, $aduana) {
        if (isset($aduana)) {
            if ($aduana == '3589-640') {
                $imap = new Zend_Mail_Storage_Imap(array(
                    'host' => $this->_appconfig->getParam('pedimentosServer'),
                    'user' => $this->_appconfig->getParam('pedimentosEmail'),
                    'password' => $this->_appconfig->getParam('pedimentosPass'),
                ));
            }
        } else {
            die("No ha especificado la aduana.");
        }
        $erase = true;
        if ($erase == true) {
            try {
                if (!empty($rmvIds)):
                    if ($rmvIds):
                        $idx = 0;
                        foreach ($rmvIds as $id):
                            if (is_int($id)) {
                                try {
                                    $removed = $imap->removeMessage($id - $idx);
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

    protected function _moveEmailsToFolder($mvIds, $aduana, $folder) {
        try {
            if (isset($aduana)) {
                if ($aduana == '3589-640') {
                    $imap = new Zend_Mail_Storage_Imap(array(
                        'host' => $this->_appconfig->getParam('pedimentosServer'),
                        'user' => $this->_appconfig->getParam('pedimentosEmail'),
                        'password' => $this->_appconfig->getParam('pedimentosPass'),
                    ));
                }
            } else {
                die("No ha especificado la aduana.");
            }
            if (!empty($mvIds)):
                if ($mvIds):
                    $idx = 0;
                    foreach ($mvIds as $id):
                        if (is_int($id["id"])) {
                            try {
                                if ($this->folderExists($imap, $folder)) {
                                    $imap->copyMessage($id["id"] - $idx, $folder);
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
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function _explodeDelimiter($string, $delimiter, $position) {
        $array = explode($delimiter, trim($string));
        return preg_replace('/\s+/', '', trim($array[$position]));
    }

    protected function _filename($string, $delimiter, $position) {
        $array = explode($delimiter, $string);
        $attach = str_replace('"', '', (explode('=', trim($array[$position]))));
        return $attach[$position];
    }

    public function removerSpamAction() {
        $aduana = $this->_request->getParam('aduana', null);
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

        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        if (isset($aduana) && $aduana == '3589-640') {
            $imap = new OAQ_IMAP($this->_appconfig->getParam('pedimentosServer'), $this->_appconfig->getParam('pedimentosEmail'), $this->_appconfig->getParam('pedimentosPass'), 'INBOX');
        } elseif (isset($aduana) && $aduana == '3574-160') {
            $imap = new OAQ_IMAP($this->_appconfig->getParam('arribosServer'), $this->_appconfig->getParam('arribosEmail'), $this->_appconfig->getParam('arribosPass'), 'INBOX');
        } else {
            die("Debe especificar una aduana.");
        }
        $numMessages = $imap->getNumMessages();
        for ($i = 1; $i <= $numMessages; $i++) {
            $header = $imap->getHeader($i);
            if ($header == false) {
                continue;
            }
            $fromInfo = $header->from[0];
            $replyInfo = $header->reply_to[0];
            $uid = $imap->getUid($i);
            $details = array(
                "fromAddr" => (isset($fromInfo->mailbox) && isset($fromInfo->host)) ? $fromInfo->mailbox . "@" . $fromInfo->host : "",
                "fromName" => (isset($fromInfo->personal)) ? $fromInfo->personal : "",
                "replyAddr" => (isset($replyInfo->mailbox) && isset($replyInfo->host)) ? $replyInfo->mailbox . "@" . $replyInfo->host : "",
                "replyName" => (isset($replyInfo->personal)) ? $replyInfo->personal : "",
                "subject" => (isset($header->subject)) ? $header->subject : "",
                "udate" => (isset($header->udate)) ? $header->udate : ""
            );
            echo "<ul>";
            echo "<li><strong>Uid:</strong> " . $uid . "</li>";
            echo "<li><strong>Id:</strong> " . $i . "</li>";
            echo "<li><strong>From:</strong> " . $details["fromAddr"] . "</li>";
            echo "<li><strong>Subject:</strong> " . $details["subject"] . "</li>";
            echo "<li><strong>Flag: " . (($header->Unseen == "U") ? "unReadMsg" : "readMsg") . "</strong> ";
            echo "<li><strong>Flag: " . (($header->Deleted == "D") ? "Deleted" : "UnDeleted") . "</strong> ";
            echo "</ul>";
            $uid = $imap->getUid($i);
            if (preg_match('/Arribo de Embarque/i', $details["subject"])) {
                $imap->deleteMessage($uid);
            } elseif (preg_match('/IMPORTACION INTRANSIT/i', $details["subject"])) {
                $imap->deleteMessage($uid);
            } elseif (preg_match('/n_del_pago_de_pedimento/i', $details["subject"])) {
                $imap->deleteMessage($uid);
            } elseif (preg_match('/Informe del pago de pedimento/i', $details["subject"])) {
                $imap->deleteMessage($uid);
            } elseif (preg_match('/Informe de resultado de previo/i', $details["subject"])) {
                $imap->deleteMessage($uid);
            } elseif (preg_match('/Resultado del Previo/i', $details["subject"])) {
                $imap->deleteMessage($uid);
            } elseif (preg_match('/registro en el sistema del BL/i', $details["subject"])) {
                $imap->deleteMessage($uid);
            } elseif (preg_match('/copia pedimentos/i', $details["subject"])) {
                $imap->deleteMessage($uid);
            } elseif (preg_match('/Reporte de Inventario/i', $details["subject"])) {
                $imap->deleteMessage($uid);
            } elseif (preg_match('/ Relacion de Cruce/i', $details["subject"])) {
                $imap->deleteMessage($uid);
            }
            if ($i == 750) {
                break;
            }
        }
        $imap->expunge();
    }

    public function sicaEmailAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        require_once 'PHPMailerAutoload.php';
        $mail = new PHPMailer;
        $mail->CharSet = "UTF-8";
        $mail->MailerDebug = true;

        $mail->isSMTP();
        $mail->Host = "mail.oaq.com.mx";
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';
        $mail->Username = "facturacion@oaq.mx";
        $mail->Password = "Facturacio1";
        $mail->Port = 26;

        $mail->From = "facturacion@oaq.mx";
        $mail->FromName = 'Facturación OAQ';
        $mail->addAddress("sistemas@oaq.com.mx", "Vianey");     // Add a recipient
        $mail->addAddress("ti.jvaldez@oaq.com.mx", "Jaime Valdez");     // Add a recipient
        $mail->addAddress("jvaldezch@gmail.com", "Jaime Valdez");     // Add a recipient
        $mail->addAddress("lhernandez@op-cbs.com", "Liliana Hernandez");     // Add a recipient
        $mail->addReplyTo("no-reply@oaq.com.mx", 'No responder');

        $mail->isHTML(true);                                  // Set email format to HTML

        $mail->Subject = 'SICA';
        $mail->Body = "-- " . sha1_file('/home/samba-share/expedientes/FacturacionElectronica/SIGN_Factura_150317.pdf') . " --";
        $mail->AddAttachment('/home/samba-share/expedientes/FacturacionElectronica/SIGN_Factura_150317.pdf');
        $mail->AddAttachment('/home/samba-share/expedientes/FacturacionElectronica/SIGN_Factura_150317.xml');
        $mail->Send();
    }

    /**
     * /automatizacion/email/notificacion-trafico?id=3641
     * 
     */
    public function notificacionTraficoAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_viewsFolder = realpath(dirname(__FILE__)) . "/../views/scripts/index/";
        $id = $this->_request->getParam("id", null);
        $debug = $this->_request->getParam("debug", null);
        $tbl = new Trafico_Model_NotificacionesMapper();
        if (isset($id) && $id != "") {
            if ($tbl->noEnviada($id)) {
                $data = $tbl->obtener($id);
                $users = new Usuarios_Model_UsuariosMapper();
                if (isset($data["para"])) {
                    $to = $users->getEmailById($data["para"]);
                }
                $mdl = new Trafico_Model_ContactosMapper();
                if ($data["tipo"] == "notificacion-comentario") {
                    $subject = "[" . $data["patente"] . "-" . $data["aduana"] . "] Se agrego comentario ref: " . $data["referencia"] . " ped. " . $data["pedimento"];
                    $html = $this->_commentTemplate($data["contenido"]);
                    $contacts = $mdl->avisoComentario($data["idAduana"]);
                    if (isset($contacts) && !empty($contacts)) {
                        $ccs = $contacts;
                    }
                } elseif ($data["tipo"] == "nueva-solicitud") {
                    $subject = "[" . $data["patente"] . "-" . $data["aduana"] . "] Solicitud de anticipo ref: " . $data["referencia"] . " ped. " . $data["pedimento"];
                    $html = $this->_commentTemplate($data["contenido"]);
                    $contacts = $mdl->avisoCreacion($data["idAduana"]);
                    if (isset($contacts) && !empty($contacts)) {
                        $ccs = $contacts;
                    }
                } elseif ($data["tipo"] == "deposito-solicitud") {
                    $subject = "[" . $data["patente"] . "-" . $data["aduana"] . "] Deposito de solicitud de anticipo ref: " . $data["referencia"] . " ped. " . $data["pedimento"];
                    $html = $this->_commentTemplate($data["contenido"]);
                    $contacts = $mdl->avisoDeposito($data["idAduana"]);
                    if (isset($contacts) && !empty($contacts)) {
                        $ccs = $contacts;
                    }
                } elseif ($data["tipo"] == "pago") {
                    $mdl = new Trafico_Model_TraficosMapper();
                    $traffic = new Trafico_Model_Table_Traficos();
                    $traffic->setId($data["idTrafico"]);
                    $mdl->find($traffic);
                    if (null !== ($traffic->getId())) {
                        $conCli = new Trafico_Model_ContactosCliMapper();
                        $view = new Zend_View();
                        $view->setScriptPath($this->_viewsFolder);
                        $view->message = "SE NOTIFICA QUE SE HA REALIZADO EL PAGO DEL PEDIMENTO:";
                        $view->patente = $traffic->getPatente();
                        $view->aduana = $traffic->getAduana();
                        $view->pedimento = $traffic->getPedimento();
                        $view->referencia = $traffic->getReferencia();
                        $html = $view->render("traffic.phtml");
                        $ccs = $conCli->avisoPago($traffic->getIdCliente());
                        $subject = "[" . $traffic->getPatente() . "-" . $traffic->getAduana() . "] Pago de pedimento: " . $traffic->getAduana() . "-" . $traffic->getPatente() . "-" . $traffic->getPedimento();
                        if (isset($ccs) && !empty($ccs)) {
                            $this->_send($subject, $html, "Trafico OAQ", $ccs);
                        }
                    }
                    return;
                }
                if (APPLICATION_ENV === "development") {
                    $subject = "DEV " . $subject;
                    $ccs = array(
                        1 => array(
                            "email" => "soporte@oaq.com.mx",
                            "nombre" => "Soporte OAQ"
                        )
                    );
                }
                if (isset($subject) && isset($html)) {
                    $this->_sendEmail($subject, $html, "Trafico OAQ", $to, isset($ccs) ? $ccs : null);
                }
            } else {
                throw new Exception("Empty data!");
            }
        } else {
            throw new Exception("Invalid data!");
        }
    }

    protected function _commentTemplate($mensaje) {
        $view = new Zend_View();
        $view->setScriptPath($this->_viewsFolder);
        $view->message = $mensaje;
        return $view->render("nuevo-comentario.phtml");
    }

    protected function _sendEmail($subject, $html, $fromName, $to, $ccs = null) {
        require_once "PHPMailerAutoload.php";
        $mail = new PHPMailer;
        $mail->CharSet = "UTF-8";
        $mail->MailerDebug = true;
        $mail->isSMTP();
        $mail->SMTPOptions = array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
                "allow_self_signed" => true
            )
        );
        $mail->Host = $this->_appconfig->getParam('arribosServer');
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';
        $mail->Username = $this->_appconfig->getParam('arribosEmail');
        $mail->Password = $this->_appconfig->getParam('arribosPass');
        $mail->Port = 26;
        $mail->From = $this->_appconfig->getParam('arribosEmail');
        $mail->FromName = $fromName;
        $mail->addAddress($to["email"], ucwords($to["nombre"]));
        if (APPLICATION_ENV === "production") {
            $mail->addCC("everardo.martinez@oaq.com.mx", "Everardo Martinez");
        }
        $mail->addReplyTo("no-responder@oaq.com.mx");
        if (isset($ccs) && is_array($ccs)) {
            foreach ($ccs as $item) {
                $mail->addCC($item["email"], $item["nombre"]);
            }
        }
        $mail->addBCC("soporte@oaq.com.mx", "Soporte OAQ");
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $html;
        $mail->Send();
    }
    
    protected function _send($subject, $html, $fromName, $ccs = null) {
        require_once "PHPMailerAutoload.php";
        $mail = new PHPMailer;
        $mail->CharSet = "UTF-8";
        $mail->MailerDebug = true;
        $mail->isSMTP();
        $mail->SMTPOptions = array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
                "allow_self_signed" => true
            )
        );
        $mail->Host = $this->_appconfig->getParam('arribosServer');
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';
        $mail->Username = $this->_appconfig->getParam('arribosEmail');
        $mail->Password = $this->_appconfig->getParam('arribosPass');
        $mail->Port = 26;
        $mail->From = $this->_appconfig->getParam('arribosEmail');
        $mail->FromName = $fromName;
        if (isset($ccs) && is_array($ccs)) {
            foreach ($ccs as $item) {
                $mail->addAddress($item["email"], $item["nombre"]);
            }
        }
        $mail->addCC("everardo.martinez@oaq.com.mx", "Everardo Martinez");
        $mail->addCC("renatta.colin@oaq.com.mx", "Renatta Colin");
        $mail->addBCC("soporte@oaq.com.mx", "Soporte OAQ");
        $mail->addReplyTo('no-responder@oaq.com.mx');
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $html;
        $mail->Send();
    }

}
