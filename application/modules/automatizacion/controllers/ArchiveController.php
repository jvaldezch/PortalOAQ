<?php

class Automatizacion_ArchiveController extends Zend_Controller_Action {

    protected $_config;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
    }

    /**
     * /automatizacion/archive/get-invoices?folio=158122
     * /automatizacion/archive/get-invoices?fecha=2015-11-19&rfc=MME921204HZ4
     * 
     */
    public function getInvoicesAction() {
        try {
            ini_set("soap.wsdl_cache_enabled", 0);
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "patente" => "Digits",
                "aduana" => "Digits",
                "folio" => "Digits",
                "year" => "Digits",
                "mes" => "Digits",
                "rfc" => "StringToUpper",
            );
            $v = array(
                "patente" => array(new Zend_Validate_Int()),
                "aduana" => array(new Zend_Validate_Int()),
                "folio" => array("NotEmpty", new Zend_Validate_Int()),
                "year" => array(new Zend_Validate_Int()),
                "mes" => array(new Zend_Validate_Int()),
                "fecha" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/"), "default" => date("Y-m-d")),
                "rfc" => array(new Zend_Validate_Regex("/^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/")),
            );
            $debug = filter_var($this->getRequest()->getParam("debug", null), FILTER_VALIDATE_BOOLEAN);
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            $cuentas = new OAQ_Facturas_CuentasDeGasto();
            if ($input->isValid("rfc")) {
                $result = $cuentas->facturasRfc($input->fecha, $input->patente, $input->aduana, $input->rfc);
            } else {
                $sendEmail = true;
                $result = $cuentas->facturas($input->fecha, $input->patente, $input->aduana);
            }
            if ($input->isValid("folio")) {
                $result = $cuentas->facturaFolio($input->folio);
            }
            if ($input->isValid("rfc") && $input->isValid("year") && $input->isValid("mes")) {
                $result = $cuentas->facturasMes($input->year, $input->mes, $input->rfc);
            }
            if (!$input->isValid("rfc") && $input->isValid("year") && $input->isValid("mes")) {
                $result = $cuentas->facturasMensual($input->year, $input->mes);
            }
            $arr = [];
            foreach ($result as $item) {
                $arr[] = $cuentas->obtenerCuenta($item["FolioID"], $item["Patente"], $item["AduanaID"], $item["Referencia"]);
            }
            if(!empty($arr) && $debug == true) {
                $this->_helper->viewRenderer->setNoRender(false);
                $this->view->invoices = $arr;
            }
            if (!empty($arr) && isset($sendEmail) && $sendEmail == true) {
                $emails = new OAQ_EmailsTraffic();
                if (APPLICATION_ENV == "production") {
                    $emails->addTo("jorge.hdz@oaq.com.mx", "Jorge Hernandez");
                    $emails->addTo("marynl@oaq.com.mx", "Mary Paz");
                    $emails->addTo("griselda.tiburcio@oaq.com.mx", "Griselda Tiburcio");
                    $emails->addTo("dlopez@oaq.com.mx", "David Lopez");
                    $emails->addTo("salvador.cardenas@oaq.com.mx", "Salvador Cardenas");
                    //$emails->addCc("soporte@oaq.com.mx", "Soporte OAQ");
                } else if (APPLICATION_ENV == "staging") {
                    $emails->addTo("soporte@oaq.com.mx", "Soporte OAQ");                                        
                } else {
                    $emails->addTo("soporte@oaq.com.mx", "Soporte OAQ");                                        
                }
                $view = new Zend_View();
                $view->setScriptPath(APPLICATION_PATH . "/../library/Templates/");
                $view->invoices = $arr;
                $emails->contenidoPersonalizado($view->render("facturacion.phtml"));
                $emails->setSubject("Facturación en repositorio {$input->fecha}");
                $emails->send();
            }
        } catch (Zend_Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerFacturaAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $context = stream_context_create(array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
                "allow_self_signed" => true
            )
        ));
        $soap = new Zend_Soap_Client("https://192.168.200.5/casaws/zfsoapsica?wsdl", array('compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_DEFLATE, "stream_context" => $context));
        $tmpDir = "/tmp";
        $folio = $this->_request->getParam("folio", null);
        $invoice = $soap->folioCdfi($folio);
        if (!empty($invoice)) {
            $filename = $tmpDir . DIRECTORY_SEPARATOR . $invoice["filename"];
            file_put_contents($filename, html_entity_decode($invoice["xml"]));
        }
        $array = array(
            '192.168.0.154',
            '192.168.0.179',
        );
        if (in_array($this->kh_getUserIP(), $array)) {
            header("Content-Type:text/xml;charset=utf-8");
            echo html_entity_decode($invoice["xml"]);
        }
    }

    protected function kh_getUserIP() {
        $client = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote = $_SERVER['REMOTE_ADDR'];

        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } else {
            $ip = $remote;
        }
        return $ip;
    }

    protected function _sitawinFileType($filename) {
        if (preg_match('/_IC.pdf$/i', $filename)) {
            return 1;
        } elseif (preg_match('/_IS.pdf$/i', $filename)) {
            return 33;
        } elseif (preg_match('/_AT.txt$/i', $filename) || preg_match('/_AV.txt$/i', $filename)) {
            return 50;
        } elseif (preg_match('/_PE.txt$/i', $filename)) {
            return 51;
        } else {
            return 99;
        }
    }

    protected function _casaFileType($filename, $referencia) {
        if (preg_match('/' . $referencia . '.pdf/i', $filename)) {
            return 1;
        } elseif (preg_match('/_SIMP.pdf$/i', $filename)) {
            return 33;
        } elseif (preg_match('/COVE/i', $filename)) {
            return 22;
        } elseif (preg_match('/E-Document/i', $filename)) {
            return 27;
        } else {
            return 99;
        }
    }

    /**
     * /automatizacion/archive/analizar-expedientes
     * /automatizacion/archive/analizar-expedientes?aduana=3589-640
     * /automatizacion/archive/analizar-expedientes?aduana=3589-646
     * /automatizacion/archive/analizar-expedientes?aduana=3589-370
     * 
     */
    public function analizarExpedientesAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $aduana = $this->_request->getParam("aduana", null);
        if (isset($aduana)) {
            if ($aduana == '3589-646') {
                $base = '/home/expedientes/3589/646/*';
                $folders = array();
                foreach (glob($base, GLOB_ONLYDIR) as $dir) {
                    $folders[] = substr($base, 0, -1) . basename($dir);
                }
            } elseif ($aduana == '3589-640') {
                $base = '/home/expedientes/3589/640/*';
                $folders = array();
                foreach (glob($base, GLOB_ONLYDIR) as $dir) {
                    $folders[] = substr($base, 0, -1) . basename($dir);
                }
            } elseif ($aduana == '3933-810') {
                $base = '/home/expedientes/3933/810/*';
                $folders = array();
                foreach (glob($base, GLOB_ONLYDIR) as $dir) {
                    $folders[] = substr($base, 0, -1) . basename($dir);
                }
                Zend_Debug::dump($folders);
            } elseif ($aduana == '3574-160') {
                $base = '/home/proexi/expedientes/160/*';
                $folders = array();
                foreach (glob($base, GLOB_ONLYDIR) as $dir) {
                    $folders[] = substr($base, 0, -1) . basename($dir);
                    echo "'" . substr($base, 0, -1) . basename($dir) . "',<br>";
                }
                Zend_Debug::dump($folders);
                die();
            } elseif ($aduana == '3589-370') {
                $base = '/home/expedientes/3589/370/*';
                $folders = array();
                foreach (glob($base, GLOB_ONLYDIR) as $dir) {
                    $folders[] = substr($base, 0, -1) . basename($dir);
                }
            } else {
                die("Aduana no disponible.");
            }
        } else {
            die("Debe proporcionar aduana.");
        }
        $misc = new OAQ_Misc();
        $sat = new OAQ_SATValidar();
        $model = new Archivo_Model_RepositorioMapper();
        $docs = new Archivo_Model_DocumentosMapper();
        $context = stream_context_create(array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
                "allow_self_signed" => true
            )
        ));
        $wsData = new Zend_Soap_Client("https://127.0.0.1/webservice/service/data?wsdl", array('compression' => SOAP_COMPRESSION_ACCEPT, "stream_context" => $context));
        if (isset($folders) && !empty($folders) && ($aduana == '3589-640' || $aduana == '3589-646')) {
            echo "<!doctype html><html lang=\"en\"><head><meta charset=\"utf-8\"></head><style>body { font-size: 11px; margin: 0; padding: 0; } table { border-collapse: collapse; border-spacing: 0; } table th, table td { font-size: 11px; font-family: sans-serif; padding: 2px; border: 1px #777 solid; }</style>"
            . "<body><table>"
            . "<tr>"
            . "<th>Patente</th>"
            . "<th>Aduana</th>"
            . "<th>Pedimento</th>"
            . "<th>Referencia</th>"
            . "<th>RFC Cliente</th>"
            . "<th>Archivo</th>"
            . "<th>Tipo Archivo</th>"
            . "<th>Ubicacion</th>"
            . "</tr>";
            foreach ($folders as $folder) {
                $explode = explode('/', $folder);
                $dir = new RecursiveDirectoryIterator($folder);
                $ite = new RecursiveIteratorIterator($dir);
                $files = new RegexIterator($ite, "/^.+\.(txt|pdf)$/i", RecursiveRegexIterator::GET_MATCH);
                if (isset($explode[5])) {
                    $ref = $explode[5];
                    $exp = explode('-', $explode[5]);
                    if (isset($exp[0])) {
                        if (preg_match('/R/i', $explode[5])) {
                            $referencia = preg_replace('!\s+!', '', $exp[0]) . '-R';
                        } else {
                            $referencia = preg_replace('!\s+!', '', $exp[0]);
                        }
                    }
                }
                foreach ($files as $name => $object) {
                    $fe = explode('_', basename($name));
                    if (isset($fe[0])) {
                        $rfcCliente = $fe[0];
                    }
                    if (isset($fe[2])) {
                        $pedimento = $fe[2];
                    }
                    $data[] = array(
                        'rfc_cliente' => $rfcCliente,
                        'patente' => substr($aduana, 0, 4),
                        'aduana' => substr($aduana, 5, 3),
                        'pedimento' => $pedimento,
                        'referencia' => $referencia,
                        'nom_archivo' => basename($name),
                        'ubicacion' => $name,
                        'tipo_archivo' => $this->_sitawinFileType(basename($name)),
                    );
                }
                if (isset($data)) {
                    foreach ($data as $item) {
                        $verificar = $model->verificarArchivo($item["patente"], $item["referencia"], $item["nom_archivo"]);
                        if ($verificar == false) {
                            $path = $misc->crearDirectorio($item["patente"], $item["aduana"], $item["referencia"]);
                            if (!copy($item["ubicacion"], $path . DIRECTORY_SEPARATOR . $item["nom_archivo"])) {
                                die("Error al copiar ...\n");
                            }
                            $added = $model->nuevoArchivo($item["tipo_archivo"], null, $item["patente"], $item["aduana"], null, $item["referencia"], $item["nom_archivo"], $path . DIRECTORY_SEPARATOR . $item["nom_archivo"], 'Auto', $item["rfc_cliente"]);
                            if ($added === true) {
                                echo "<tr>"
                                . "<td>" . $item["patente"] . "</td>"
                                . "<td>" . $item["aduana"] . "</td>"
                                . "<td>" . $item["pedimento"] . "</td>"
                                . "<td>" . $item["referencia"] . "</td>"
                                . "<td>" . $item["rfc_cliente"] . "</td>"
                                . "<td>" . $item["nom_archivo"] . "</td>"
                                . "<td>" . $docs->tipoDocumento($item["tipo_archivo"]) . "</td>"
                                . "<td>" . $item["ubicacion"] . "</td>"
                                . "</tr>";
                            }
                        }
                    }
                    if ($added == true) {
                        $model->actualizarPedimento($item["referencia"], $item["pedimento"], $item["rfc_cliente"]);
                    }
                }
                unset($data);
            }
            echo "</table></body></html>";
        } elseif (isset($folders) && !empty($folders) && $aduana == '3933-810') {
            foreach ($folders as $folder) {
                $explode = explode('/', $folder);
                Zend_Debug::dump($explode);

                $directory = new RecursiveDirectoryIterator($folder);
                $iterator = new RecursiveIteratorIterator($directory);
                $files = new RegexIterator($iterator, '/^.+\.xml/i', RecursiveRegexIterator::GET_MATCH);

                foreach ($files as $name => $object) {
                    //            if (preg_match('/^B.+\.xml/i', basename($name))) {
                    $xml = file_get_contents($name);
                    $array = $sat->satToArray($xml);
                    if (isset($array["Emisor"]["@attributes"]["rfc"])) {
                        $rfcEmisor = $array["Emisor"]["@attributes"]["rfc"];
                    }
                    if (isset($array["Receptor"]["@attributes"]["rfc"])) {
                        $rfcReceptor = $array["Receptor"]["@attributes"]["rfc"];
                    }
                    if ($rfcReceptor == 'BME051004FLA') {
                        if (preg_match('/ALT/i', $explode[5])) {
                            $referencia = explode(' ', $explode[5]);
                            $dir = new RecursiveDirectoryIterator($folder);
                            $ite = new RecursiveIteratorIterator($dir);
                            $arrfiles = new RegexIterator($ite, "/^.+\.(xml|pdf|xls|xlsx|doc|docx)$/i", RecursiveRegexIterator::GET_MATCH);
                            $data = array();
                            foreach ($arrfiles as $nombre => $object) {
                                $newFilename = $misc->formatFilename(basename($nombre));
                                if ($newFilename !== basename($nombre)) {
                                    $pathinfo = pathinfo($nombre);
                                    //                                Zend_Debug::Dump($nombre, "ORIGINAL");
                                    //                                Zend_Debug::Dump($pathinfo["dirname"] . DIRECTORY_SEPARATOR . $newFilename, "NUEVO");
                                    if (!rename($nombre, $pathinfo["dirname"] . DIRECTORY_SEPARATOR . $newFilename)) {
                                        die("No se puede renombrar.");
                                    }
                                    $nombre = $pathinfo["dirname"] . DIRECTORY_SEPARATOR . $newFilename;
                                }
                                if (!preg_match('/^B\d{4}.xml$/i', basename($nombre)) && !preg_match('/^B\d{4}.pdf$/i', basename($nombre))) {
                                    $data[] = array(
                                        'rfc_cliente' => $rfcReceptor,
                                        'patente' => $explode[3],
                                        'aduana' => $explode[4],
                                        'referencia' => $referencia[1],
                                        'nom_archivo' => $misc->formatFilename(basename($nombre)),
                                        'ubicacion' => $nombre,
                                        'tipo_archivo' => 99,
                                    );
                                }
                                if (preg_match('/^B\d{4}.xml$/i', basename($nombre)) || preg_match('/^B\d{4}.pdf$/i', basename($nombre))) {
                                    $data[] = array(
                                        'rfc_cliente' => $rfcReceptor,
                                        'patente' => $explode[3],
                                        'aduana' => $explode[4],
                                        'referencia' => $referencia[1],
                                        'nom_archivo' => $misc->formatFilename(basename($nombre)),
                                        'ubicacion' => $nombre,
                                        'tipo_archivo' => 29,
                                    );
                                }
                            }
                        }
                    }
                    if (isset($data)) {
                        foreach ($data as $item) {
                            $verificar = $model->verificarArchivo($item["patente"], $item["referencia"], $item["nom_archivo"]);
                            if ($verificar == false) {
                                $path = $misc->crearDirectorio($item["patente"], $item["aduana"], $item["referencia"]);
                                if (!copy($item["ubicacion"], $path . DIRECTORY_SEPARATOR . $item["nom_archivo"])) {
                                    die("Error al copiar ...\n");
                                }
                                $model->nuevoArchivo($item["tipo_archivo"], null, $item["patente"], $item["aduana"], null, $item["referencia"], $item["nom_archivo"], $path . DIRECTORY_SEPARATOR . $item["nom_archivo"], 'Auto', $item["rfc_cliente"]);
                            }
                        }
                        continue;
                    }
                } // foreach $files        
            }
        } elseif (isset($folders) && !empty($folders) && $aduana == '3574-160') {
            foreach ($folders as $folder) {
                $explode = explode('/', $folder);
                if (isset($explode[5])) {
                    try {
                        $referencia = $wsData->obtenerPedimento($explode[3], $explode[4], 'MI4-' . $explode[5]);
                    } catch (Exception $e) {
                        if (preg_match('/Unknown error/i', $e->getMessage())) {
                            $error = true;
                        }
                    }
                    if (isset($error) && $error == true) {
                        try {
                            $referencia = $wsData->obtenerPedimento($explode[3], $explode[4], 'ME4-' . $explode[5]);
                        } catch (Exception $e) {
                            if (preg_match('/Unknown error/i', $e->getMessage())) {
                                $error = true;
                            }
                        }
                    }
                    if (isset($error) && $error == true) {
                        try {
                            $referencia = $wsData->obtenerPedimento($explode[3], $explode[4], 'MI3-' . $explode[5]);
                        } catch (Exception $e) {
                            if (preg_match('/Unknown error/i', $e->getMessage())) {
                                $error = true;
                            }
                        }
                    }
                    if (isset($error) && $error == true) {
                        try {
                            $referencia = $wsData->obtenerPedimento($explode[3], $explode[4], 'ME3-' . $explode[5]);
                        } catch (Exception $e) {
                            if (preg_match('/Unknown error/i', $e->getMessage())) {
                                $error = true;
                            }
                        }
                    }
                    if (isset($referencia["trafico"]) && isset($referencia["pedimento"]) && isset($referencia["rfcCliente"])) {
                        Zend_Debug::dump($referencia, "REFERENCIA WS");
                        $dir = new RecursiveDirectoryIterator($folder);
                        $ite = new RecursiveIteratorIterator($dir);
                        $arrfiles = new RegexIterator($ite, "/^.+\.(xml|pdf|xls|xlsx|doc|docx)$/i", RecursiveRegexIterator::GET_MATCH);
                        foreach ($arrfiles as $nombre => $object) {
                            $newFilename = $misc->formatFilename(basename($nombre));
                            if ($newFilename !== basename($nombre)) {
                                $pathinfo = pathinfo($nombre);
                                if (!rename($nombre, $pathinfo["dirname"] . DIRECTORY_SEPARATOR . $newFilename)) {
                                    die("No se puede renombrar.");
                                }
                                $nombre = $pathinfo["dirname"] . DIRECTORY_SEPARATOR . $newFilename;
                            }
                            $data[] = array(
                                'rfc_cliente' => $referencia["rfcCliente"],
                                'patente' => $explode[3],
                                'aduana' => $explode[4],
                                'referencia' => $referencia["trafico"],
                                'pedimento' => $referencia["pedimento"],
                                'nom_archivo' => $misc->formatFilename(basename($nombre)),
                                'ubicacion' => $nombre,
                                'tipo_archivo' => 99,
                            );
                        } // foreach arrfiles
                        if (isset($data)) {
//                            Zend_Debug::dump($data);
                            foreach ($data as $item) {
                                $verificar = $model->verificarArchivo($item["patente"], $item["referencia"], $item["nom_archivo"]);
                                if ($verificar == false) {
                                    $path = $misc->crearDirectorio($item["patente"], $item["aduana"], $item["referencia"]);
                                    if (!copy($item["ubicacion"], $path . DIRECTORY_SEPARATOR . $item["nom_archivo"])) {
                                        die("Error al copiar ...\n");
                                    }
                                    $model->nuevoArchivo($item["tipo_archivo"], null, $item["patente"], $item["aduana"], $item["pedimento"], $item["referencia"], $item["nom_archivo"], $path . DIRECTORY_SEPARATOR . $item["nom_archivo"], 'Auto', $item["rfc_cliente"]);
                                }
                            }
                            continue;
                        }
                    }
                    unset($referencia);
                }
            }
        } elseif (isset($folders) && !empty($folders) && $aduana == '3589-370') {
            $i = 0;
            echo "<!doctype html><html lang=\"en\"><head><meta charset=\"utf-8\"></head><style>body { font-size: 11px; margin: 0; padding: 0; } table { border-collapse: collapse; border-spacing: 0; } table th, table td { font-size: 11px; font-family: sans-serif; padding: 2px; border: 1px #777 solid; }</style>"
            . "<body><table>"
            . "<tr>"
            . "<th>Patente</th>"
            . "<th>Aduana</th>"
            . "<th>Pedimento</th>"
            . "<th>Referencia</th>"
            . "<th>RFC Cliente</th>"
            . "<th>Archivo</th>"
            . "<th>Tipo Archivo</th>"
            . "<th>Ubicacion</th>"
            . "</tr>";
            foreach ($folders as $folder) {
                $explode = explode('/', $folder);
                $ref = explode("_", $explode[5]);
                try {
                    $referencia = $wsData->obtenerPedimento($explode[3], $explode[4], $ref[1]);
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
                if (isset($referencia)) {
                    $directory = new RecursiveDirectoryIterator($folder);
                    $iterator = new RecursiveIteratorIterator($directory);
                    $files = new RegexIterator($iterator, '/^.+\.pdf/i', RecursiveRegexIterator::GET_MATCH);
                    foreach ($files as $name => $object) {
                        $data[] = array(
                            'rfc_cliente' => $referencia["rfcCliente"],
                            'patente' => $explode[3],
                            'aduana' => $explode[4],
                            'referencia' => $ref[1],
                            'pedimento' => $referencia["pedimento"],
                            'nom_archivo' => basename($name),
                            'ubicacion' => $name,
                            'tipo_archivo' => $this->_casaFileType(basename($name), $ref[1]),
                        );
                    }
                }
                $i++;
                if (isset($data)) {
                    foreach ($data as $item) {
                        $verificar = $model->verificarArchivo($item["patente"], $item["referencia"], $item["nom_archivo"]);
                        if ($verificar == false) {
                            $path = $misc->crearDirectorio($item["patente"], $item["aduana"], $item["referencia"]);
                            if (!copy($item["ubicacion"], $path . DIRECTORY_SEPARATOR . $item["nom_archivo"])) {
                                die("Error al copiar ...\n");
                            }
                            $added = $model->nuevoArchivo($item["tipo_archivo"], null, $item["patente"], $item["aduana"], null, $item["referencia"], $item["nom_archivo"], $path . DIRECTORY_SEPARATOR . $item["nom_archivo"], 'Auto', $item["rfc_cliente"]);
                            if ($added === true) {
                                echo "<tr>"
                                . "<td>" . $item["patente"] . "</td>"
                                . "<td>" . $item["aduana"] . "</td>"
                                . "<td>" . $item["pedimento"] . "</td>"
                                . "<td>" . $item["referencia"] . "</td>"
                                . "<td>" . $item["rfc_cliente"] . "</td>"
                                . "<td>" . $item["nom_archivo"] . "</td>"
                                . "<td>" . $docs->tipoDocumento($item["tipo_archivo"]) . "</td>"
                                . "<td>" . $item["ubicacion"] . "</td>"
                                . "</tr>";
                            }
                        }
                    }
                    if ($added == true) {
                        $model->actualizarPedimento($item["referencia"], $item["pedimento"], $item["rfc_cliente"]);
                    }
                }
                unset($data);
//                if($i == 5) {
//                    break;
//                }
            }
            echo "</table></body></html>";
        } else {
            die("No hay carpetas que analizar.");
        }
    }
    
    public function analizarXmlAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

    /**
     * /automatizacion/archive/analizar-terceros?emisor=TLO050804QY7&receptor=TGA960311J79 -- AURIGA
     * /automatizacion/archive/analizar-terceros?emisor=TLO050804QY7&receptor=CIN0309091D3 -- CNH
     * /automatizacion/archive/analizar-terceros?emisor=TLO050804QY7&receptor=CTM990607US8 -- APEX
     * /automatizacion/archive/analizar-terceros?emisor=TLO050804QY7&receptor=WMO1004098Z6 -- WINDSOR
     * /automatizacion/archive/analizar-terceros?emisor=TLO050804QY7&receptor=VEN940203EU6 -- VENTRA
     * /automatizacion/archive/analizar-terceros?emisor=TLO050804QY7&receptor=DCM030212ET4 -- DIEHL
     * /automatizacion/archive/analizar-terceros?emisor=TLO050804QY7&receptor=MQU971209RQ1 -- MAQUINADOS
     * /automatizacion/archive/analizar-terceros?emisor=TLO050804QY7&receptor=GIV021204B1A -- GUARDIAN
     * 
     */
    public function analizarTercerosAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $rfcEmisor = $this->_request->getParam("emisor", null);
        $rfcReceptor = $this->_request->getParam("receptor", null);
        $context = stream_context_create(array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
                "allow_self_signed" => true
            )
        ));
        $wsData = new Zend_Soap_Client("https://127.0.0.1/webservice/service/data?wsdl", array('compression' => SOAP_COMPRESSION_ACCEPT, "stream_context" => $context));
        $sitawin = new OAQ_Sitawin(true, '192.168.0.253', 'sa', 'sqlcointer', 'SITAW3589640', 1433, 'Pdo_Mssql');
        $sitawina = new OAQ_Sitawin(true, '192.168.200.5', 'sa', 'adminOAQ123', 'SITAW3010640', 1433, 'Pdo_Mssql');
        $sat = new OAQ_SATValidar();
        $model = new Archivo_Model_RepositorioMapper();
        $facturas = $model->getInvoicesByRfcTerminal($rfcEmisor, $rfcReceptor, 250);
        foreach ($facturas as $file) {
            if (isset($file["nom_archivo"])) {
                if (preg_match('/.xml$/i', $file["nom_archivo"])) {
                    Zend_Debug::dump($file, "FILE");
                    $array = $sat->satToArray(file_get_contents($file["ubicacion"]));
                    if (isset($array["Addenda"]["operacion"]["patente"]["pedimento"]["@attributes"]["numero"])) {
                        $pedimento = $array["Addenda"]["operacion"]["patente"]["pedimento"]["@attributes"]["numero"] . '<br>';
                        try {
                            $referencia = $wsData->obtenerReferencia(3589, 646, $pedimento);
                        } catch (Exception $e) {
                            Zend_Debug::dump($e->getMessage());
                        }
                        if (isset($referencia["trafico"])) {
                            if (!isset($file["rfc_cliente"]) && !isset($file["referencia"])) {
                                $model->actualizarFolioTerminal($file["folio"], $referencia["rfcCliente"], $referencia["trafico"], "TLO050804QY7", "TGA960311J79");
                            }
                        }
                    } else {
                        if (isset($array["Addenda"]["operacion"]["patente"]["guia"])) {
                            $guia = $array["Addenda"]["operacion"]["patente"]["guia"];
                            $found = $sitawin->buscarGuia($guia);
                            if ($found !== false) {
                                if (isset($found["rfcCliente"]) && isset($found["referencia"])) {
                                    $model->actualizarFolioTerminal($file["folio"], $found["rfcCliente"], $found["referencia"], "TLO050804QY7", "TGA960311J79", $found["pedimento"]);
                                }
                            } else {
                                $found = $sitawina->buscarGuia($guia);
                                if ($found !== false) {
                                    if (isset($found["rfcCliente"]) && isset($found["referencia"])) {
                                        $model->actualizarFolioTerminal($file["folio"], $found["rfcCliente"], $found["referencia"], "TLO050804QY7", "TGA960311J79", $found["pedimento"]);
                                    }
                                }
                            }
                        }
                    }
                }
            }
//            $array =             
        }
    }

    /**
     * /automatizacion/archive/analizar-cuentas?rfcEmisor=OAQ030623UL8&patente=3589&aduana=640&year=2015&mes=1&dia=1
     * /automatizacion/archive/analizar-cuentas?rfcEmisor=OAQ030623UL8&folio=140668
     * /automatizacion/archive/analizar-cuentas?rfcEmisor=OAQ030623UL8&folio=140709
     * /automatizacion/archive/analizar-cuentas?rfcEmisor=OAQ030623UL8&folio=140839
     * 
     */
    public function analizarCuentasAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $rfcEmisor = $this->_request->getParam("rfcEmisor", null);
        $patente = $this->_request->getParam("patente", null);
        $aduana = $this->_request->getParam("aduana", null);
        $year = $this->_request->getParam("year", null);
        $mes = $this->_request->getParam("mes", null);
        $dia = $this->_request->getParam("dia", null);
        $folio = $this->_request->getParam("folio", null);
        $model = new Archivo_Model_RepositorioMapper();
        $cuentas = $model->obtenerCuentas($rfcEmisor, $patente, $aduana, $year, $mes, $dia, $folio);
        $context = stream_context_create(array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
                "allow_self_signed" => true
            )
        ));
        if (isset($cuentas) && !empty($cuentas)) {
            foreach ($cuentas as $item) {
                if ($item["patente"] == 3574 && preg_match('/47/', $item["aduana"])) {
                    $soap = new Zend_Soap_Client("https://proexi.dyndns.org:8445/zfsoapcasa.php?wsdl", array("stream_context" => $context));
                    if (!preg_match('/H$/i', trim($item["referencia"]))) {
                        $wsref = $soap->basicoReferenciaSecundario(3574, 470, $item["referencia"]);
                    } else {
                        $wsref = $soap->basicoReferenciaSecundario(3574, 470, substr($item["referencia"], 0, -1));
                    }
                    if (isset($wsref) && !empty($wsref)) {
                        if ((isset($wsref["pedimento"]) && $wsref["pedimento"] != '') || (isset($wsref["rfcCliente"]) && $wsref["rfcCliente"] != '')) {
                            $model->actualizarPedimento($item["referencia"], $wsref["pedimento"], $wsref["rfcCliente"]);
                        }
                    }
                }
                if ($item["patente"] == 3933 && preg_match('/43/', $item["aduana"])) {
                    $soap = new Zend_Soap_Client("https://162.253.186.242:8443/zfsoapaduanet?wsdl", array("stream_context" => $context));
                    if (!preg_match('/H$/i', trim($item["referencia"]))) {
                        $wsref = $soap->basicoReferencia(3933, 430, $item["referencia"]);
                    } else {
                        $wsref = $soap->basicoReferencia(3933, 430, substr($item["referencia"], 0, -1));
                        Zend_Debug::dump($wsref, "WSREF");
                    }
                    if (isset($wsref) && !empty($wsref)) {
                        if ((isset($wsref["pedimento"]) && $wsref["pedimento"] != '') || (isset($wsref["rfcCliente"]) && $wsref["rfcCliente"] != '')) {
                            $model->actualizarPedimento($item["referencia"], $wsref["pedimento"], $wsref["rfcCliente"]);
                        }
                    }
                }
                if (isset($wsref)) {
                    unset($wsref);
                }
            }
        }
    }

    public function expedientesDigitalizadosSitawinAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $con = new Application_Model_WsWsdl();
        $docs = new Archivo_Model_DocumentosMapper();
        $model = new Archivo_Model_RepositorioMapper();

        $base = '/tmp/cnh/*';
        $folders = array();
        foreach (glob($base, GLOB_ONLYDIR) as $dir) {
            $folders[] = substr($base, 0, -1) . basename($dir);
        }
        $non = array("Q1500661", "Q1500662", "Q1500663", "Q1500664", "Q1500665", "Q1500666", "Q1500667", "Q1500668", "Q1500669", "Q1500670", "Q1500671", "Q1500672", "Q1500673", "Q1500674", "Q1500825", "Q1500845", "Q1500846", "Q1500847", "Q1500848", "Q1500882", "Q1500884", "Q1500885", "Q1500886", "Q1500887", "Q1500888", "Q1500889", "Q1500890", "Q1500891", "Q1500892", "Q1500893", "Q1500883", "Q1501179", "Q1501375", "Q1501378", "Q1501377", "Q1501537", "Q1501538", "Q1501539", "Q1502184", "Q1502219");

        $i = 0;
        $data = array();
        foreach ($folders as $folder) {

            foreach (glob($folder . DIRECTORY_SEPARATOR . '*.pdf') as $filename) {
                $files[] = $filename;
            }

            if (isset($files) && !empty($files)) {
                $dir = explode('/', $folder);
                if (isset($dir[3]) && preg_match('/^Q15/i', $dir[3])) {
                    $ref = explode('-', $dir[3]);
                    if (isset($ref[0]) && preg_match('/Q15/i', $ref[0])) {
                        if (!in_array($ref[0], $non)) {
                            continue;
                        }
                        $referencia = $ref[0];
                        $patente = 3589;
                        $aduana = 640;
                    }
                }
                if ($patente == 3589 && $aduana == 640) {
                    $wsdl = $con->getWsdl($patente, $aduana, 'sitawin');
                }
                try {
                    if (isset($wsdl)) {
                        $soap = new SoapClient($wsdl, array('exceptions' => true, 'trace' => true, 'cache_wsdl' => 0));
                        if ($patente == 3589 && $aduana == 640) {
                            $arr = $soap->basicoReferencia($patente, 640, $referencia);
                            if ($arr == false) {
                                $arr = $soap->basicoReferencia($patente, 646, $referencia);
                            }
                        }
                        if (isset($arr) && $arr !== false && !empty($arr)) {
                            foreach ($files as $file) {
                                if (isset($data[$referencia]['completo']) && isset($data[$referencia]['simplificado'])) {
                                    continue;
                                }
                                if (preg_match('/_IC.pdf/i', basename($file))) {
                                    if (!isset($data[$referencia]['completo'])) {
                                        $data[$referencia]['completo'] = array(
                                            'rfc_cliente' => $arr["rfcCliente"],
                                            'patente' => $patente,
                                            'aduana' => $aduana,
                                            'referencia' => $arr["trafico"],
                                            'pedimento' => $arr["pedimento"],
                                            'nom_archivo' => basename($file),
                                            'ubicacion' => $file,
                                            'tipo_archivo' => 1,
                                        );
                                    }
                                } elseif (preg_match('/_IS.pdf/i', basename($file))) {
                                    if (!isset($data[$referencia]['simplificado'])) {
                                        $data[$referencia]['simplificado'] = array(
                                            'rfc_cliente' => $arr["rfcCliente"],
                                            'patente' => $patente,
                                            'aduana' => $aduana,
                                            'referencia' => $arr["trafico"],
                                            'pedimento' => $arr["pedimento"],
                                            'nom_archivo' => basename($file),
                                            'ubicacion' => $file,
                                            'tipo_archivo' => 33,
                                        );
                                    }
                                }
                            } // foreach files
                        }
                    }
                } catch (Exception $ex) {
                    echo $ex->getMessage();
                }
            }
//            $i++;
//            if ($i == 40) {
//                break;
//            }
        }
        if (isset($data)) {
            echo "<!doctype html><html lang=\"en\"><head><meta charset=\"utf-8\"></head><style>body { font-size: 11px; margin: 0; padding: 0; } table { border-collapse: collapse; border-spacing: 0; } table th, table td { font-size: 11px; font-family: sans-serif; padding: 2px; border: 1px #777 solid; }</style>"
            . "<body><table>"
            . "<tr>"
            . "<th>Patente</th>"
            . "<th>Aduana</th>"
            . "<th>Pedimento</th>"
            . "<th>Referencia</th>"
            . "<th>RFC Cliente</th>"
            . "<th>Archivo</th>"
            . "<th>Tipo Archivo</th>"
            . "<th>Ubicacion</th>"
            . "<th>Existe</th>"
            . "</tr>";
            foreach ($data as $item) {
                if (isset($item["completo"])) {
                    if (!($this->_verificarArchivo($item["completo"]["patente"], $item["completo"]["referencia"], $item["completo"]["nom_archivo"]))) {
                        echo "<tr>"
                        . "<td>" . $item["completo"]["patente"] . "</td>"
                        . "<td>" . $item["completo"]["aduana"] . "</td>"
                        . "<td>" . $item["completo"]["pedimento"] . "</td>"
                        . "<td>" . $item["completo"]["referencia"] . "</td>"
                        . "<td>" . $item["completo"]["rfc_cliente"] . "</td>"
                        . "<td>" . $item["completo"]["nom_archivo"] . "</td>"
                        . "<td>" . $docs->tipoDocumento($item["completo"]["tipo_archivo"]) . "</td>"
                        . "<td>" . $item["completo"]["ubicacion"] . "</td>"
                        . "<td>No existe</td>"
                        . "</tr>";
                        if (($path = $this->_copiar($item["completo"]["patente"], $item["completo"]["aduana"], $item["completo"]["referencia"], $item["completo"]["nom_archivo"], $item["completo"]["ubicacion"]))) {
                            $added = $model->nuevoArchivo($item["completo"]["tipo_archivo"], null, $item["completo"]["patente"], $item["completo"]["aduana"], $item["completo"]['pedimento'], $item["completo"]["referencia"], $item["completo"]["nom_archivo"], $path . DIRECTORY_SEPARATOR . $item["completo"]["nom_archivo"], 'Auto', $item["completo"]["rfc_cliente"]);
                        }
                    }
                }
                if (isset($item["simplificado"])) {
                    if (!($this->_verificarArchivo($item["simplificado"]["patente"], $item["simplificado"]["referencia"], $item["simplificado"]["nom_archivo"]))) {
                        echo "<tr>"
                        . "<td>" . $item["simplificado"]["patente"] . "</td>"
                        . "<td>" . $item["simplificado"]["aduana"] . "</td>"
                        . "<td>" . $item["simplificado"]["pedimento"] . "</td>"
                        . "<td>" . $item["simplificado"]["referencia"] . "</td>"
                        . "<td>" . $item["simplificado"]["rfc_cliente"] . "</td>"
                        . "<td>" . $item["simplificado"]["nom_archivo"] . "</td>"
                        . "<td>" . $docs->tipoDocumento($item["simplificado"]["tipo_archivo"]) . "</td>"
                        . "<td>" . $item["simplificado"]["ubicacion"] . "</td>"
                        . "<td>No existe</td>"
                        . "</tr>";
                        if (($path = $this->_copiar($item["simplificado"]["patente"], $item["simplificado"]["aduana"], $item["simplificado"]["referencia"], $item["simplificado"]["nom_archivo"], $item["simplificado"]["ubicacion"]))) {
                            $added = $model->nuevoArchivo($item["simplificado"]["tipo_archivo"], null, $item["simplificado"]["patente"], $item["simplificado"]["aduana"], $item["simplificado"]['pedimento'], $item["simplificado"]["referencia"], $item["simplificado"]["nom_archivo"], $path . DIRECTORY_SEPARATOR . $item["simplificado"]["nom_archivo"], 'Auto', $item["simplificado"]["rfc_cliente"]);
                        }
                    }
                }
            }
            echo "</table></body></html>";
        }
    }

    public function expedientesDigitalizadosSitawinSimplificadoAction() {
        // https://192.168.0.246/automatizacion/archive/expedientes-digitalizados-sitawin-simplificado
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $con = new Application_Model_WsWsdl();
        $docs = new Archivo_Model_DocumentosMapper();
        $model = new Archivo_Model_RepositorioMapper();

        $base = '/tmp/buss/*';

        $i = 0;
        $data = array();
        foreach (glob($base) as $filename) {
            $dir = explode('/', $filename);
            if (isset($dir[3]) && preg_match('/^5/i', $dir[3])) {
                $ped = explode('_', $dir[3]);
                if (isset($ped[0]) && preg_match('/^5/i', $ped[0])) {
                    if (isset($ped[0]) && preg_match('/^5/i', $ped[0])) {
                        $pedimento = $ped[0];
                        $patente = 3589;
                        $aduana = 640;
                    }
                }
                if ($patente == 3589 && $aduana == 640) {
                    $wsdl = $con->getWsdl($patente, $aduana, 'sitawin');
                }
                try {
                    if (isset($wsdl)) {
                        $soap = new SoapClient($wsdl, array('exceptions' => true, 'trace' => true, 'cache_wsdl' => 0));
                        if ($patente == 3589 && $aduana == 640) {
                            $arr = $soap->basicoPedimento($patente, 640, $pedimento);
                            if ($arr == false) {
                                $arr = $soap->basicoPedimento($patente, 646, $pedimento);
                            }
                            if ($arr !== false) {
                                if (!isset($data[$arr["referencia"]]['completo'])) {
                                    $newname = $arr["referencia"] . '_' . $arr["trafico"] . '_COM' . '.pdf';
                                    $newdir = pathinfo($filename, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . $newname;
                                    $data[$arr["referencia"]]['completo'] = array(
                                        'rfc_cliente' => $arr["rfcCliente"],
                                        'patente' => $patente,
                                        'aduana' => $aduana,
                                        'referencia' => $arr["trafico"],
                                        'pedimento' => $arr["pedimento"],
                                        'nom_archivo' => basename($filename),
                                        'new_nom_archivo' => $newname,
                                        'ubicacion' => $filename,
                                        'new_ubicacion' => $newdir,
                                        'tipo_archivo' => 1,
                                    );
                                }
                                if (file_exists(pathinfo($filename, PATHINFO_DIRNAME) . '/simplificados' . DIRECTORY_SEPARATOR . basename($filename))) {
                                    $snewname = $arr["referencia"] . '_' . $arr["trafico"] . '_SIM' . '.pdf';
                                    $snewdir = pathinfo($filename, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . $snewname;
                                    $data[$arr["referencia"]]['simplificado'] = array(
                                        'rfc_cliente' => $arr["rfcCliente"],
                                        'patente' => $patente,
                                        'aduana' => $aduana,
                                        'referencia' => $arr["trafico"],
                                        'pedimento' => $arr["pedimento"],
                                        'nom_archivo' => basename($filename),
                                        'new_nom_archivo' => $snewname,
                                        'ubicacion' => pathinfo($filename, PATHINFO_DIRNAME) . '/simplificados' . DIRECTORY_SEPARATOR . basename($filename),
                                        'new_ubicacion' => $snewdir,
                                        'tipo_archivo' => 33,
                                    );
                                }
                            }
                        }
                    }
                } catch (Exception $ex) {
                    echo $ex->getMessage();
                }
            }
//            $i++;
//            if ($i == 5) {
//                break;
//            }
        }

        if (isset($data)) {
            echo "<!doctype html><html lang=\"en\"><head><meta charset=\"utf-8\"></head><style>body { font-size: 11px; margin: 0; padding: 0; } table { border-collapse: collapse; border-spacing: 0; } table th, table td { font-size: 11px; font-family: sans-serif; padding: 2px; border: 1px #777 solid; }</style>"
            . "<body><table>"
            . "<tr>"
            . "<th>Patente</th>"
            . "<th>Aduana</th>"
            . "<th>Pedimento</th>"
            . "<th>Referencia</th>"
            . "<th>RFC Cliente</th>"
            . "<th>Archivo</th>"
            . "<th>Tipo Archivo</th>"
            . "</tr>";
            foreach ($data as $item) {
                if (isset($item["completo"])) {
                    if (!($this->_verificarArchivo($item["completo"]["patente"], $item["completo"]["referencia"], $item["completo"]["new_nom_archivo"]))) {
                        echo "<tr>"
                        . "<td>" . $item["completo"]["patente"] . "</td>"
                        . "<td>" . $item["completo"]["aduana"] . "</td>"
                        . "<td>" . $item["completo"]["pedimento"] . "</td>"
                        . "<td>" . $item["completo"]["referencia"] . "</td>"
                        . "<td>" . $item["completo"]["rfc_cliente"] . "</td>"
                        . "<td>" . $item["completo"]["new_nom_archivo"] . "</td>"
                        . "<td>" . $docs->tipoDocumento($item["completo"]["tipo_archivo"]) . "</td>"
                        . "</tr>";
                        if (($path = $this->_copiarNuevoNombre($item["completo"]["patente"], $item["completo"]["aduana"], $item["completo"]["referencia"], $item["completo"]["ubicacion"], $item["completo"]["new_nom_archivo"]))) {
                            $added = $model->nuevoArchivo($item["completo"]["tipo_archivo"], null, $item["completo"]["patente"], $item["completo"]["aduana"], $item["completo"]['pedimento'], $item["completo"]["referencia"], $item["completo"]["new_nom_archivo"], $path . DIRECTORY_SEPARATOR . $item["completo"]["new_nom_archivo"], 'Auto', $item["completo"]["rfc_cliente"]);
                        }
                    }
                }
                if (isset($item["simplificado"])) {
                    if (!($this->_verificarArchivo($item["simplificado"]["patente"], $item["simplificado"]["referencia"], $item["simplificado"]["new_nom_archivo"]))) {
                        echo "<tr>"
                        . "<td>" . $item["simplificado"]["patente"] . "</td>"
                        . "<td>" . $item["simplificado"]["aduana"] . "</td>"
                        . "<td>" . $item["simplificado"]["pedimento"] . "</td>"
                        . "<td>" . $item["simplificado"]["referencia"] . "</td>"
                        . "<td>" . $item["simplificado"]["rfc_cliente"] . "</td>"
                        . "<td>" . $item["simplificado"]["new_nom_archivo"] . "</td>"
                        . "<td>" . $docs->tipoDocumento($item["simplificado"]["tipo_archivo"]) . "</td>"
                        . "</tr>";
                        if (($path = $this->_copiarNuevoNombre($item["simplificado"]["patente"], $item["simplificado"]["aduana"], $item["simplificado"]["referencia"], $item["simplificado"]["ubicacion"], $item["simplificado"]["new_nom_archivo"]))) {
                            $added = $model->nuevoArchivo($item["simplificado"]["tipo_archivo"], null, $item["simplificado"]["patente"], $item["simplificado"]["aduana"], $item["simplificado"]['pedimento'], $item["simplificado"]["referencia"], $item["simplificado"]["new_nom_archivo"], $path . DIRECTORY_SEPARATOR . $item["simplificado"]["new_nom_archivo"], 'Auto', $item["simplificado"]["rfc_cliente"]);
                        }
                    }
                }
            }
            echo "</table></body></html>";
        }
    }

    protected function _verificarArchivo($patente, $referencia, $nomArchivo) {
        $model = new Archivo_Model_RepositorioMapper();

        $verificar = $model->verificarArchivo($patente, $referencia, $nomArchivo);
        if ($verificar == false) {
            return false;
        }
        return true;
    }

    protected function _copiar($patente, $aduana, $referencia, $nomArchivo, $ubicacion) {
        $misc = new OAQ_Misc();
        $path = $misc->crearDirectorio($patente, $aduana, $referencia);
        if (!copy($ubicacion, $path . DIRECTORY_SEPARATOR . $nomArchivo)) {
            die("Error al copiar ...\n");
        }
        return $path;
    }

    protected function _copiarNuevoNombre($patente, $aduana, $referencia, $ubicacion, $nuevoNombre) {
        $misc = new OAQ_Misc();
        $path = $misc->crearDirectorio($patente, $aduana, $referencia);
        if (!copy($ubicacion, $path . DIRECTORY_SEPARATOR . $nuevoNombre)) {
            die("Error al copiar ...\n");
        }
        return $path;
    }

    public function expedientesDigitalizadosAction() {
        // https://192.168.0.246/automatizacion/archive/expedientes-digitalizados
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $misc = new OAQ_Misc();
        $con = new Application_Model_WsWsdl();
        $docs = new Archivo_Model_DocumentosMapper();
        $model = new Archivo_Model_RepositorioMapper();

//        $folder = '/tmp/cnh';
        $folder = '/tmp/windsor';
        
        $directory = new RecursiveDirectoryIterator($folder);
        $iterator = new RecursiveIteratorIterator($directory);
        $files = new RegexIterator($iterator, '/^.+\.pdf/i', RecursiveRegexIterator::GET_MATCH);
        $i = 0;        

        foreach ($files as $name => $object) {
            $ref = explode('-', basename($name));
            if (empty($ref) || !isset($ref[1])) {
                $ref = explode('-', basename($name));
            }
            if (preg_match('/.pdf$/i', $ref[0])) {
                $ref[0] = str_replace('.pdf', '', $ref[0]);
            }
            if (isset($ref[0])) {
                if (preg_match('/^Q13/i', $ref[0]) || preg_match('/^OAQ/i', $ref[0]) || preg_match('/^QRO/i', $ref[0]) || preg_match('/^QM/i', $ref[0]) || preg_match('/^Q15/i', $ref[0])) {
                    $patente = 3589;
                    $aduana = 640;
                }
                if (preg_match('/^14TQ/i', $ref[0])) {
                    $patente = 3589;
                    $aduana = 240;
                    
                }
                if ($patente == 3589 && $aduana == 640) {
                    $wsdl = $con->getWsdl($patente, $aduana, 'sitawin');
                }
                try {
                    if (isset($wsdl)) {
                        $soap = new SoapClient($wsdl, array('exceptions' => true, 'trace' => true, 'cache_wsdl' => 0));
                        if ($patente == 3589 && $aduana == 640) {
                            $arr = $soap->basicoReferencia($patente, 640, $ref[0]);
                            if ($arr == false) {
                                $arr = $soap->basicoReferencia($patente, 646, $ref[0]);
                            }
                        }
                        if (isset($arr) && $arr !== false && !empty($arr)) {
                            $data[] = array(
                                'rfc_cliente' => $arr["rfcCliente"],
                                'patente' => $patente,
                                'aduana' => $aduana,
                                'referencia' => $arr["trafico"],
                                'pedimento' => $arr["pedimento"],
                                'nom_archivo' => basename($name),
                                'ubicacion' => $name,
                                'tipo_archivo' => 17,
                            );
                        }
                    }
                } catch (Exception $ex) {
                    echo $ex->getMessage();
                }
            }
            $i++;
            if ($i == 10) {
                break;
            }
        }
        Zend_Debug::dump($data);
        return true;

//        if (isset($data)) {
//            echo "<!doctype html><html lang=\"en\"><head><meta charset=\"utf-8\"></head><style>body { font-size: 11px; margin: 0; padding: 0; } table { border-collapse: collapse; border-spacing: 0; } table th, table td { font-size: 11px; font-family: sans-serif; padding: 2px; border: 1px #777 solid; }</style>"
//            . "<body><table>"
//            . "<tr>"
//            . "<th>Patente</th>"
//            . "<th>Aduana</th>"
//            . "<th>Pedimento</th>"
//            . "<th>Referencia</th>"
//            . "<th>RFC Cliente</th>"
//            . "<th>Archivo</th>"
//            . "<th>Tipo Archivo</th>"
//            . "<th>Ubicacion</th>"
//            . "</tr>";
//            foreach ($data as $item) {
//                $verificar = $model->verificarArchivo($item["patente"], $item["referencia"], $item["nom_archivo"]);
//                if ($verificar == false) {
//                    $path = $misc->crearDirectorio($item["patente"], $item["aduana"], $item["referencia"]);
//                    if (!copy($item["ubicacion"], $path . DIRECTORY_SEPARATOR . $item["nom_archivo"])) {
//                        die("Error al copiar ...\n");
//                    }
//                    $added = $model->nuevoArchivo($item["tipo_archivo"], $item["patente"], $item["aduana"], $item['pedimento'], $item["referencia"], $item["nom_archivo"], $path . DIRECTORY_SEPARATOR . $item["nom_archivo"], 'Auto', $item["rfc_cliente"]);
//                    if ($added === true) {
//                        echo "<tr>"
//                        . "<td>" . $item["patente"] . "</td>"
//                        . "<td>" . $item["aduana"] . "</td>"
//                        . "<td>" . $item["pedimento"] . "</td>"
//                        . "<td>" . $item["referencia"] . "</td>"
//                        . "<td>" . $item["rfc_cliente"] . "</td>"
//                        . "<td>" . $item["nom_archivo"] . "</td>"
//                        . "<td>" . $docs->tipoDocumento($item["tipo_archivo"]) . "</td>"
//                        . "<td>" . $item["ubicacion"] . "</td>"
//                        . "</tr>";
//                    }
//                } else {
//                    echo "<tr>"
//                    . "<td colspan=\"4\">Archivo ya existe.</td>"
//                    . "<td>" . $item["rfc_cliente"] . "</td>"
//                    . "<td>" . $item["nom_archivo"] . "</td>"
//                    . "<td>" . $docs->tipoDocumento($item["tipo_archivo"]) . "</td>"
//                    . "<td>" . $item["ubicacion"] . "</td>"
//                    . "</tr>";
//                }
////                if($added == true) {
////                    unlink($item["ubicacion"]);
////                }
//            }
//            echo "</table></body></html>";
//        }
    }

    public function analizarFtpAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $misc = new OAQ_Misc();

        $aduana = $this->_request->getParam("aduana", null);
        if (isset($aduana)) {
            if ($aduana == '3574-160') {
                $base = '/home/proexi/expedientes/160/*';
                $folders = array();
                foreach (glob($base, GLOB_ONLYDIR) as $dir) {
                    $folders[] = substr($base, 0, -1) . basename($dir);
                }
            } elseif ($aduana == '3574-470') {
                $base = '/home/proexi/expedientes/470/*';
                $folders = array();
                foreach (glob($base, GLOB_ONLYDIR) as $dir) {
                    $folders[] = substr($base, 0, -1) . basename($dir);
                }
            } else {
                die("Aduana no disponible.");
            }
        } else {
            die("Debe proporcionar aduana.");
        }
        $model = new Archivo_Model_RepositorioMapper();
        echo '<!doctype html><html lang="en"><head><meta charset="utf-8">';
        echo '<style>body { margin:0; padding:0; font-size: 11px; font-family: sans-serif; } table { border-collapse: collapse; } table td, table th { font-size: 11px; font-family: sans-serif; border: 1px #555 solid; } table th { background-color: #f1f1f1; } table tr.none { background-color: red; }</style>';
        echo '<body>';
        echo '<table>';
        echo '<tr><th>Referencia</th><th>RFC Cliente</th><th>Patente</th><th>Aduana</th><th>Pedimento</th><th>Referencia WS</th><th>Tipo Arch.</th><th>Nom. Archivo</th><th>Ubicación</th></tr>';
        if (isset($folders) && !empty($folders)) {
            foreach ($folders as $folder) {
                $explode = explode('/', $folder);
                if (isset($explode[5]) && $this->_validarReferencia($aduana, $explode[5])) {
                    $cdfi = $this->_facturaCorresponsal($aduana, $folder);
                    $referencia = $this->_buscarReferencia($aduana, $explode[5]);
                    $data = array();
                    if (isset($referencia)) {
                        if (isset($referencia["trafico"]) && isset($referencia["pedimento"]) && isset($referencia["rfcCliente"])) {
                            $data = $this->_archivosCorresponsal($aduana, $folder, $explode, $referencia, $cdfi);
                        }
                        unset($referencia);
                        if (isset($data)) {
                            foreach ($data[$aduana] as $k => $files) {
                                foreach ($files as $file) {
                                    echo "<tr>";
                                    echo '<td>' . $k . '</td>';
                                    echo '<td>' . $file["rfc_cliente"] . '</td>';
                                    echo '<td>' . $file["patente"] . '</td>';
                                    echo '<td>' . $file["aduana"] . '</td>';
                                    echo '<td>' . $file["pedimento"] . '</td>';
                                    echo '<td>' . $file["referencia"] . '</td>';
                                    echo '<td>' . $file["tipo_archivo"] . '</td>';
                                    echo '<td>' . $file["nom_archivo"] . '</td>';
                                    echo '<td>' . $file["ubicacion"] . '</td>';
                                    echo '</tr>';
                                    $verificar = $model->verificarArchivo($file["patente"], $file["referencia"], $file["nom_archivo"]);
                                    if ($verificar == false) {
                                        $path = $misc->crearDirectorio($file["patente"], $file["aduana"], $file["referencia"]);
                                        if (!copy($file["ubicacion"], $path . DIRECTORY_SEPARATOR . $file["nom_archivo"])) {
                                            die("Error al copiar ...\n");
                                        }
                                        $model->nuevoArchivo($file["tipo_archivo"], null, $file["patente"], $file["aduana"], $file["pedimento"], $file["referencia"], $file["nom_archivo"], $path . DIRECTORY_SEPARATOR . $file["nom_archivo"], 'Auto', $file["rfc_cliente"]);
                                    }
                                }
                            }
                            unset($data);
                        }
                    }
                }
            }
        }
        echo '</table>';
        echo '</body></html>';
    }

    protected function _validarReferencia($aduana, $folderName) {
        if ($aduana == '3574-160') {
            if (preg_match('/MI/i', $folderName) || preg_match('/ME/i', $folderName) || preg_match('/MR/i', $folderName)) {
                return true;
            } else {
                return false;
            }
        } elseif ($aduana == '3574-470') {
            if (preg_match('/GPI/i', $folderName)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    protected function _buscarReferencia($aduana, $folderName) {
        try {
            $context = stream_context_create(array(
                "ssl" => array(
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                    "allow_self_signed" => true
                )
            ));
            $con = new Application_Model_WsWsdl();
            if ($aduana == '3574-160') {
                if (($wsdl = $con->getWsdl(3574, 160, "casa"))) {
                    $soap = new Zend_Soap_Client($wsdl, array("stream_context" => $context));
                }
                $referencia = $soap->basicoReferencia(3574, 160, $folderName);
            } elseif ($aduana == '3574-470') {
                if (($wsdl = $con->getWsdl(3574, 470, "casa"))) {
                    $soap = new Zend_Soap_Client($wsdl, array("stream_context" => $context));
                }
                $referencia = $soap->basicoReferenciaSecundario(3574, 470, $folderName);
            }
            if ($referencia === false) {
                $referencia["trafico"] = $folderName;
                $referencia["pedimento"] = '';
                $referencia["rfcCliente"] = '';
            }
            return $referencia;
        } catch (Exception $e) {
            if (preg_match('/Unknown error/i', $e->getMessage())) {
                return null;
            }
        }
    }

    protected function _facturaCorresponsal($aduana, $folder) {
        $sat = new OAQ_SATValidar();

        $directory = new RecursiveDirectoryIterator($folder);
        $iterator = new RecursiveIteratorIterator($directory);
        $files = new RegexIterator($iterator, '/^.+\.xml/i', RecursiveRegexIterator::GET_MATCH);
        foreach ($files as $name => $object) {
            $xml = file_get_contents($name);
            $array = $sat->satToArray($xml);
            if (isset($array["Emisor"]["@attributes"]["rfc"])) {
                $rfcEmisor = $array["Emisor"]["@attributes"]["rfc"];
            }
            if (isset($array["Receptor"]["@attributes"]["rfc"])) {
                $rfcReceptor = $array["Receptor"]["@attributes"]["rfc"];
            }
            if ($aduana == '3574-160') {
                if ($rfcReceptor == 'OAQ030623UL8' && $rfcEmisor == 'PIP9903253J8') {
                    return preg_replace(array('/SIGN_/i', '/Factura_/i', '/.xml/i', '/_B/i'), '', basename($name));
                }
            } elseif ($aduana == '3574-470') {
                if ($rfcReceptor == 'OAQ030623UL8' && $rfcEmisor == 'GPR030428C77') {
                    return preg_replace(array('/SIGN_/i', '/Factura_/i', '/.xml/i', '/_B/i'), '', basename($name));
                }
            }
        }
        return null;
    }

    protected function _archivosCorresponsal($aduana, $folder, $explode, $referencia, $cdfi) {
        $misc = new OAQ_Misc();

        $directory = new RecursiveDirectoryIterator($folder);
        $iterator = new RecursiveIteratorIterator($directory);
        $files = new RegexIterator($iterator, "/^.+\.(xml|pdf|xls|xlsx|doc|docx|err|[0-9]{3})$/i", RecursiveRegexIterator::GET_MATCH);
        foreach ($files as $nombre => $object) {
            $newFilename = $misc->formatFilename(basename($nombre));
            if ($newFilename !== basename($nombre)) {
                $pathinfo = pathinfo($nombre);
                if (!rename($nombre, $pathinfo["dirname"] . DIRECTORY_SEPARATOR . $newFilename)) {
                    die("No se puede renombrar.");
                }
                $nombre = $pathinfo["dirname"] . DIRECTORY_SEPARATOR . $newFilename;
            }
            if (preg_match('/^Sign/i', basename($nombre)) && preg_match('/.xml$/i', basename($nombre))) {
                $data[$aduana][$explode[5]][] = array(
                    'rfc_cliente' => $referencia["rfcCliente"],
                    'patente' => substr($aduana, 0, 4),
                    'aduana' => $explode[4],
                    'referencia' => $referencia["trafico"],
                    'pedimento' => $referencia["pedimento"],
                    'nom_archivo' => $misc->formatFilename(basename($nombre)),
                    'ubicacion' => $nombre,
                    'tipo_archivo' => 29,
                );
            } elseif (preg_match('/' . $cdfi . '/i', basename($nombre))) {
                $data[$aduana][$explode[5]][] = array(
                    'rfc_cliente' => $referencia["rfcCliente"],
                    'patente' => substr($aduana, 0, 4),
                    'aduana' => $explode[4],
                    'referencia' => $referencia["trafico"],
                    'pedimento' => $referencia["pedimento"],
                    'nom_archivo' => $misc->formatFilename(basename($nombre)),
                    'ubicacion' => $nombre,
                    'tipo_archivo' => 29,
                );
            } elseif (preg_match('/^M[0-9]{7}.[0-9]{3}/i', basename($nombre))) {
                $data[$aduana][$explode[5]][] = array(
                    'rfc_cliente' => $referencia["rfcCliente"],
                    'patente' => substr($aduana, 0, 4),
                    'aduana' => $explode[4],
                    'referencia' => $referencia["trafico"],
                    'pedimento' => $referencia["pedimento"],
                    'nom_archivo' => $misc->formatFilename(basename($nombre)),
                    'ubicacion' => $nombre,
                    'tipo_archivo' => 50,
                );
            } elseif (preg_match('/^a[0-9]{7}.[0-9]{3}/i', basename($nombre))) {
                $data[$aduana][$explode[5]][] = array(
                    'rfc_cliente' => $referencia["rfcCliente"],
                    'patente' => substr($aduana, 0, 4),
                    'aduana' => $explode[4],
                    'referencia' => $referencia["trafico"],
                    'pedimento' => $referencia["pedimento"],
                    'nom_archivo' => $misc->formatFilename(basename($nombre)),
                    'ubicacion' => $nombre,
                    'tipo_archivo' => 51,
                );
            } elseif (preg_match('/^m[0-9]{7}.err/i', basename($nombre))) {
                $data[$aduana][$explode[5]][] = array(
                    'rfc_cliente' => $referencia["rfcCliente"],
                    'patente' => substr($aduana, 0, 4),
                    'aduana' => $explode[4],
                    'referencia' => $referencia["trafico"],
                    'pedimento' => $referencia["pedimento"],
                    'nom_archivo' => $misc->formatFilename(basename($nombre)),
                    'ubicacion' => $nombre,
                    'tipo_archivo' => 52,
                );
            } else {
                $data[$aduana][$explode[5]][] = array(
                    'rfc_cliente' => $referencia["rfcCliente"],
                    'patente' => substr($aduana, 0, 4),
                    'aduana' => $explode[4],
                    'referencia' => $referencia["trafico"],
                    'pedimento' => $referencia["pedimento"],
                    'nom_archivo' => $misc->formatFilename(basename($nombre)),
                    'ubicacion' => $nombre,
                    'tipo_archivo' => 99,
                );
            }
        } // foreach arrfiles
        if (isset($data) && !empty($data)) {
            return $data;
        }
        return null;
    }

    public function convertirPdfAction() {
        $folder = '/tmp/scrap/';

        $directory = new RecursiveDirectoryIterator($folder);
        $iterator = new RecursiveIteratorIterator($directory);
        $files = new RegexIterator($iterator, '/^.+\.pdf/i', RecursiveRegexIterator::GET_MATCH);

        /* command  /home/jvaldez/pdfconverter/./jpg.sh
         *  /tmp/scrap/./convert.sh
         * #!/bin/bash
          #where $1 is the input filename

          ournum=`gs -q -dNODISPLAY -c "("$1") (r) file runpdfbegin pdfpagecount = quit" $
          echo "Processing $ournum pages"
          counter=1
          while [ $counter -le $ournum ] ; do
          newname=`echo $1 | sed -e s/\.jpg//g`
          reallynewname=$newname-$counter.jpg
          counterplus=$((counter+1))
          # make the individual pdf page
          yes | gs -dBATCH -sOutputFile="$reallynewname" -dFirstPage=$counter -dLastP$
          counter=$counterplus
          done
          convert -density 300 $newname{-}*.jpg -compress zip gray_$1
         */

        foreach ($files as $name => $filename) {
            $jpg = "gs -dNOPAUSE -sDEVICE=jpeg -r200 -sOutputFile=" . $folder . pathinfo(basename($name), PATHINFO_FILENAME) . "-%03d.jpg {$name} quit ";
            echo '<p>' . $jpg . '</p>';
            shell_exec($jpg);
            $pdf = "convert -density 300 " . $folder . pathinfo(basename($name), PATHINFO_FILENAME) . "-*.jpg -compress zip " . pathinfo($name, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . "VU_" . basename($name);
            echo '<p>' . $pdf . '</p>';
            shell_exec($pdf);
            $rm = "rm " . $folder . "*.jpg";
            shell_exec($rm);
            $rm = "rm " . $name;
            shell_exec($rm);
        }
    }

    public function enviarArchivosAgenteAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $server = array(
            'username' => 'ftpoaq',
            'password' => '+oaq2002*',
            'host' => '189.206.78.132',
            'port' => '21',
        );

        $model = new Archivo_Model_RepositorioMapper();
        $files = $model->archivosPatente(3589);
        echo '<!doctype html><html lang="en"><head><meta charset="utf-8">';
        echo '<style>body { margin:0; padding:0; font-size: 11px; font-family: sans-serif; } table { border-collapse: collapse; } table td, table th { font-size: 11px; font-family: sans-serif; border: 1px #555 solid; }</style>';
        echo '<body>';
        echo '<table>';
        echo '<tr><th>RFC</th><th>Patente</th><th>Aduana</th><th>Referenca</th><th>Archivo</th><th>Remote folder</th></tr>';
        $data = array();
        foreach ($files as $item) {
            if (is_null($item["rfc_cliente"])) {
                $item["rfc_cliente"] = $model->buscarRfcPorReferencia(3589, $item["referencia"]);
            }
            $item["remote_folder"] = '/home/samba-share/chiapas' . DIRECTORY_SEPARATOR . $item["patente"] . $item["aduana"] . DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . $item["rfc_cliente"] . DIRECTORY_SEPARATOR . $item["referencia"];
            echo "<tr>"
            . "<td>{$item["rfc_cliente"]}</td>"
            . "<td>{$item["patente"]}</td>"
            . "<td>{$item["aduana"]}</td>"
            . "<td>{$item["referencia"]}</td>"
            . "<td>" . basename($item["ubicacion"]) . "</td>"
            . "<td>" . $item["remote_folder"] . "</td>"
            . "</tr>";
            $data[] = $item;
        }
        echo '</body></html>';
        try {
            if (($conn_id = $this->_connectFtp($server))) {
                foreach ($data as $file) {
                    if ($this->_createRemoteFolder($conn_id, $file["remote_folder"])) {
                        if ((ftp_size($conn_id, $file["remote_folder"] . DIRECTORY_SEPARATOR . basename($file["ubicacion"]))) == -1) {
                            ftp_put($conn_id, $file["remote_folder"] . DIRECTORY_SEPARATOR . basename($file["ubicacion"]), $file["ubicacion"], FTP_BINARY);
                        }
                    } else {
                        continue;
                    }
                }
            }
        } catch (Exception $ex) {
            Zend_Debug::dump($ex->getMessage());
        }
    }

    protected function _connectFtp($server) {
        $conn_id = ftp_connect($server["host"], $server["port"]);
        $login_result = ftp_login($conn_id, $server["username"], $server["password"]);
        if ((!$conn_id) || (!$login_result)) {
            $error = "UNABLE TO CONNECT TO CLIENT FTP \n"
                    . "Url: {$server["host"]}\n"
                    . "User: {$server["username"]}\n"
                    . "Pass: {$server["password"]}\n";
            echo $error;
            return false;
        }
        return $conn_id;
    }

    protected function _createRemoteFolder($conn_id, $remoteFolder) {
        if (!ftp_chdir($conn_id, $remoteFolder)) {
            $this->_makeDir($conn_id, $remoteFolder);
            if (ftp_chdir($conn_id, $remoteFolder)) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    protected function _makeDir($conn_id, $path) {
        $dir = split("/", $path);
        $path = "";
        $ret = true;

        for ($i = 0; $i < count($dir); $i++) {
            $path.="/" . $dir[$i];
            if (!@ftp_chdir($conn_id, $path)) {
                @ftp_chdir($conn_id, "/");
                if (!@ftp_mkdir($conn_id, $path)) {
                    $ret = false;
                    break;
                }
            }
        }
        return $ret;
    }
    
    /**
     * /automatizacion/archive/enviar-archivos-patente?patente=3589&limit=10
     * 
     * @return type
     * @throws Exception
     */
    public function enviarArchivosPatenteAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "patente" => array("Digits"),
                "limit" => array("Digits"),
            );
            $v = array(
                "patente" => array("NotEmpty", new Zend_Validate_Int()),
                "limit" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($i->isValid("patente") && $i->isValid("limit")) {
                $misc = new OAQ_Misc();
                $m = new Archivo_Model_RepositorioMapper();
                $mp = new Archivo_Model_RepositorioEnviar();
                $arr = $mp->noEnviado($i->patente, $i->limit);
                $ftp = new OAQ_Ftp(array(
                    "port" => 21,
                    "host" => "189.206.78.132",
                    "username" => "ftpoaq",
                    "password" => "+oaq2002*",
                ));
                if (true !== ($conn = $ftp->connect())) {
                    $this->_helper->json(array("success" => false, "message" => $conn));
                    return;
                }
                $ftp->setPassive();
                foreach ($arr as $item) {
                    $d = $m->buscarReferenciaRfc($item["referencia"]);
                    if (isset($d) && !empty($d)) {
                        $files = $m->archivosReferenciaAgente($item["referencia"], $i->patente);
                        if (empty($files)) {
                            echo "<p style=\"padding:0; margin:0; font-size: 11px; font-family: sans-serif\">No files found!</p>";
                            continue;
                        }
                        $mp->archivos($item["id"], count($files));
                        if (!($t = $misc->analisisReferencia($item["referencia"]))) {
                            echo "<p style=\"padding:0; margin:0; font-size: 11px; font-family: sans-serif\">Not valid reference pattern found!</p>";
                            continue;
                        }
                        $folder = "/OAQ/" . $d["aduana"] . "/" . $t["year"] . "/" . $item["rfcCliente"] . "/" . $item["referencia"];
                        if ($ftp->makeDirectory($folder)) {
                            if(!$ftp->changeRemoteDirectory($folder)) {
                                echo "<p style=\"padding:0; margin:0; font-size: 11px; font-family: sans-serif\">Unable to change folder on FTP server. {$folder}</p>";
                                continue;
                            }
                            $c = 0;
                            echo "<p style=\"padding:0; margin:0; font-size: 11px; font-family: sans-serif\"><strong>Referencia:</strong> {$item["referencia"]}, <strong>archivos:</strong> " . count($files) . " <br><strong>Folder:</strong> {$folder}</p>";
                            foreach ($files as $f) {
                                if (file_exists($f["ubicacion"])) {
                                    if ($ftp->upload($f["ubicacion"])) {
                                        echo "<p style=\"padding:0; margin:0; font-size: 11px; font-family: sans-serif\">Uploaded {$f["nom_archivo"]}</p>";
                                        $c++;
                                    }
                                } else {
                                    echo "<p style=\"padding:0; margin:0; font-size: 11px; font-family: sans-serif\">File not found {$f["nom_archivo"]}</p>";
                                }
                            } // $files
                            if (count($files) == $c) {
                                $mp->enviado($item["id"]);
                            }
                        } else {
                            echo "<p style=\"padding:0; margin:0; font-size: 11px; font-family: sans-serif\">Unable to create folder on FTP server. {$folder}</p>";
                            continue;
                        }
                    } else {
                        $mp->vacio($item["id"]);
                        continue;
                    }
                } // $arr
                $ftp->disconnect();
                echo "<p style=\"padding:0; margin:0; font-size: 11px; font-family: sans-serif\">Disconnected!</p>";                
            } else {
                throw new Exception("Invalid Input!");
            }
        } catch (Zend_Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    /**
     * /automatizacion/archive/curl?year=2016&month=1
     * 
     * @throws Exception
     */
    public function curlAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "year" => array("Digits"),
            );
            $v = array(
                "year" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($i->isValid("year")) {
                $m = new Archivo_Model_RepositorioEnviar();
                $mapper = new Archivo_Model_RepositorioMapper();
                if ($i->isValid("year") == 2016) {
                    $regex = "^(Q16|16TQ|16ME)";
                }
                $arr = $mapper->referenciasPorPatente(3589, $regex);
                foreach ($arr as $item) {
                    if (null === ($m->buscar($item["patente"], $item["referencia"]))) {
                        $m->agregar($item["patente"], $item["referencia"], $item["rfc_cliente"]);
                    }
                }
            } else {
                throw new Exception("Invalid Input!");
            }
        } catch (Zend_Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

}
