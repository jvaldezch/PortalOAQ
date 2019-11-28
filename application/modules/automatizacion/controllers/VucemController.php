<?php

class Automatizacion_VucemController extends Zend_Controller_Action {

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
    protected $_appconfig;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_logger = Zend_Registry::get("logDb");
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $config = array("auth" => "login",
            "username" => $this->_config->app->infra->email,
            "password" => $this->_config->app->infra->pass,
            "port" => 26);
        $this->_transportSupport = new Zend_Mail_Transport_Smtp($this->_config->app->infra->smtp, $config);
    }

    protected function mailStorage($tipo) {
        try {
            if ($tipo == "notificaciones") {
                $this->_emailStorage = new Zend_Mail_Storage_Imap(array(
                    "host" => $this->_config->app->notificaciones->smtp,
                    "user" => $this->_config->app->notificaciones->email,
                    "password" => $this->_config->app->notificaciones->pass,
                ));
            } else if ($tipo == "pedimentos") {
                $this->_emailStorage = new Zend_Mail_Storage_Imap(array(
                    "host" => $this->_config->app->pedimento->smtp,
                    "user" => $this->_config->app->pedimento->email,
                    "password" => $this->_config->app->pedimento->pass,
                ));
            } else if ($tipo == "facturas") {
                $this->_emailStorage = new Zend_Mail_Storage_Imap(array(
                    "host" => $this->_config->app->facturas->smtp,
                    "user" => $this->_config->app->facturas->email,
                    "password" => $this->_config->app->facturas->pass,
                ));
            } else if ($tipo == "cobranza") {
                $this->_emailStorage = new Zend_Mail_Storage_Imap(array(
                    "host" => "mail.oaq.com.mx",
                    "user" => "cobranza@oaq.com.mx",
                    "password" => "Cobr4nz#0",
                ));
            }
        } catch (Exception $e) {
            echo "<p><b>IMAP storage exception:</b> {$e->getMessage()}</p>";
        }
    }

    public function indexAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

    protected $_excelFile = "D:\COVES\JOHNSON_COVES_2013.xlsx";
    protected $_directory = "D:\COVES\JOHNSON";

    public function excelCovesAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        require_once "PHPExcel/IOFactory.php";
        $objPHPExcel = PHPExcel_IOFactory::load($this->_excelFile);
        $vucem = new OAQ_Vucem();
        $firmante = new Vucem_Model_VucemFirmanteMapper();
        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
            $worksheetTitle = $worksheet->getTitle();
            if (preg_match("/COVE/i", $worksheetTitle)) {
                $highestRow = $worksheet->getHighestRow(); // e.g. 10
                $highestColumn = $worksheet->getHighestColumn(); // e.g "F"
                $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
                $nrColumns = ord($highestColumn) - 64;
                if (preg_match("/COVE/i", $worksheetTitle)) {
                    $coves = array();
                    $tmpCove = array();
                    for ($row = 2; $row <= $highestRow; ++$row) {
                        for ($col = 0; $col < $highestColumnIndex; ++$col) {
                            $cell = $worksheet->getCellByColumnAndRow($col, $row);
                            if ($worksheet->getCellByColumnAndRow($col, 1)->getValue() != "") {
                                if ($cell->getFormattedValue() != "") {
                                    $tmpCove[$worksheet->getCellByColumnAndRow($col, 1)->getValue()] = $cell->getFormattedValue();
                                } else {
                                    continue;
                                }
                            }
                        }
                        if (!empty($tmpCove)) {
                            array_push($coves, $tmpCove);
                            unset($tmpCove);
                        }
                    }
                }
            } else {
                break;
            }
        }
        foreach ($coves as $item) {
            $rfc = $firmante->obtenerDetalleFirmante($item["RFCConsulta"], null, 3589, 640);
            $pkeyid = openssl_get_privatekey(base64_decode($rfc["spem"]), $rfc["spem_pswd"]);
            $cadena = "|{$rfc["rfc"]}|{$item["COVE"]}|";
            $signature = "";
            if (isset($rfc["sha"]) && $rfc["sha"] == "sha256") {
                openssl_sign($cadena, $signature, $pkeyid, OPENSSL_ALGO_SHA256);
            } else {
                openssl_sign($cadena, $signature, $pkeyid);
            }
            $xmlEnvio = $vucem->consultaCove($rfc["rfc"], $rfc["ws_pswd"], $rfc["cer"], $cadena, base64_encode($signature), $item["COVE"]);
            $xml = $vucem->vucemServicio($xmlEnvio, "https://www.ventanillaunica.gob.mx/ventanilla/ConsultarEdocumentService");
            $coveXml = $this->_directory . DIRECTORY_SEPARATOR . $item["COVE"] . ".xml";
            if (!file_exists($coveXml)) {
                file_put_contents($coveXml, $xml);
            }
            unset($xml);
            unset($coveXml);
        }
        unset($item);
        foreach ($coves as $item) {
            $covePdf = $this->_directory . DIRECTORY_SEPARATOR . $item["COVE"] . ".pdf";
            $coveXml = $this->_directory . DIRECTORY_SEPARATOR . $item["COVE"] . ".xml";
            if (!file_exists($covePdf) && file_exists($coveXml)) {
                $uri = "{$this->_config->app->url}/automatizacion/vucem/save-cove-pdf?cove={$item["COVE"]}";
                $ch = curl_init($uri);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                $response = curl_exec($ch);
                curl_close($ch);
            }
            unset($coveXml);
            unset($covePdf);
        }
    }

    public function renderCoveHtmlAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(false);
        $cove = $this->_request->getParam("cove", null);
        if ($cove) {
            $vucem = new OAQ_Vucem();
            if (file_exists($this->_directory . DIRECTORY_SEPARATOR . $cove . ".xml")) {
                $xml = file_get_contents($this->_directory . DIRECTORY_SEPARATOR . $cove . ".xml");
                $data = $vucem->vucemXmlToArray($xml);
                unset($data["Header"]);
                $this->view->cove = $cove;
                $this->view->data = $data["Body"]["ConsultarEdocumentResponse"]["response"]["resultadoBusqueda"]["cove"];
            }
        } else {
            return false;
        }
    }

    public function saveCovePdfAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        define("DOMPDF_ENABLE_REMOTE", true);
        require_once "dompdf/dompdf_config.inc.php";
        $cove = $this->_request->getParam("cove", null);
        $uri = "{$this->_config->app->url}/automatizacion/vucem/render-cove-html?cove={$cove}";
        $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $response = curl_exec($ch);
        curl_close($ch);
        $dompdf = new DOMPDF();
        $dompdf->set_paper("letter", "portrait");
        $dompdf->load_html($response);
        $dompdf->set_base_path($_SERVER["DOCUMENT_ROOT"]);
        $dompdf->render();
        $output = $dompdf->output();
        file_put_contents($this->_directory . DIRECTORY_SEPARATOR . $cove . ".pdf", $output);
    }

    public function renderCoveToHtmlAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(false);
        $vucemSol = new Vucem_Model_VucemSolicitudesMapper();
        $vucem = new OAQ_Vucem();
        $id = $this->_request->getParam("id");
        $xml = $vucemSol->obtenerSolicitudPorId($id);
        $this->view->fechas = array(
            "enviado" => $xml["enviado"],
            "actualizado" => $xml["actualizado"]
        );
        $xmlArray = $vucem->xmlStrToArray($xml["xml"]);
        unset($xmlArray["Header"]);
        if ($xml["cove"] != "" && $xml["cove"] != null) {
            $this->view->cove = $xml["cove"];
        }
        $this->view->pedimento = $xml["pedimento"];
        $this->view->referencia = $xml["referencia"];
        $this->view->id = $id;
        $this->view->data = $xmlArray["Body"]["solicitarRecibirCoveServicio"]["comprobantes"];
        $this->view->url = $this->_config->app->url;
    }

    public function convertCoveToPdfAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        error_reporting(E_ALL);
        ini_set("display_errors", 1);
        try {
            define("DOMPDF_ENABLE_REMOTE", true);
            require_once "dompdf/dompdf_config.inc.php";
            $id = $this->_request->getParam("id");
            $strCookie = "PHPSESSID=" . $_COOKIE["PHPSESSID"] . "; path=/";
            session_write_close();
            $uri = "{$this->_config->app->url}/vucem/data/render-cove-to-html?id={$id}";
            $ch = curl_init($uri);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_COOKIESESSION, true);
            curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie-name");
            curl_setopt($ch, CURLOPT_COOKIE, $strCookie);
            curl_setopt($ch, CURLOPT_COOKIEFILE, "/tmp/session/");
            $response = curl_exec($ch);
            curl_close($ch);
            $vucemSol = new Vucem_Model_VucemSolicitudesMapper();
            $cove = $vucemSol->obtenerNombreCove($id);
            if ($cove["cove"] != null && $cove["cove"] != "") {
                $filename = $cove["cove"];
            } else {
                $filename = "Operacion_" . $cove["solicitud"];
            }
            $dompdf = new DOMPDF();
            $dompdf->set_paper("letter", "portrait");
            $dompdf->load_html($response);
            $dompdf->set_base_path($_SERVER["DOCUMENT_ROOT"]);
            $dompdf->render();
            $dompdf->stream($filename . ".pdf");
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function guardarCovePdfAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $session = null ? $session = new Zend_Session_Namespace("") : $session = new Zend_Session_Namespace($this->_config->app->namespace);
        $arch = new Archivo_Model_RepositorioMapper();
        $sol = new Vucem_Model_VucemSolicitudesMapper();
        $cove = $this->_request->getParam("cove");
        $info = $sol->searchCove($cove);
        if ($info):
            $misc = new OAQ_Misc();
            $dir = $misc->crearExpedienteDir($this->_appconfig->getParam("expdest"), null, $info["patente"], $info["aduana"], $info["referencia"]);
            if ($dir) {
                $filenamePdf = $dir . DIRECTORY_SEPARATOR . $cove . ".pdf";
                define("DOMPDF_ENABLE_REMOTE", true);
                require_once "dompdf/dompdf_config.inc.php";
                $vucem = new OAQ_Vucem();
                $xmlArray = $vucem->xmlStrToArray($info["xml"]);
                unset($xmlArray["Header"]);
                if ($info["cove"] != "" && $info["cove"] != null) {
                    $this->view->cove = $info["cove"];
                }
                $this->view->pedimento = $info["pedimento"];
                $this->view->referencia = $info["referencia"];
                $this->view->data = $xmlArray["Body"]["solicitarRecibirCoveServicio"]["comprobantes"];
                $this->view->url = $this->_config->app->url;
                $html = $this->view->render("vucem/guardar-cove-pdf.phtml");
                $dompdf = new DOMPDF();
                $dompdf->set_paper("letter", "portrait");
                $dompdf->load_html($html);
                $dompdf->set_base_path($_SERVER["DOCUMENT_ROOT"]);
                $dompdf->render();
                $output = $dompdf->output();
                file_put_contents($filenamePdf, $output);
                if (file_exists($filenamePdf)) {
                    if (!($arch->checkIfFileExists($info["referencia"], $info["patente"], $info["aduana"], $cove . ".pdf"))) {
                        $arch->addNewFile(22, null, $info["referencia"], $info["patente"], $info["aduana"], $cove . ".pdf", $filenamePdf, (isset($session)) ? $session->username : null);
                    }
                }
                $filenameXml = $dir . DIRECTORY_SEPARATOR . $cove . ".xml";
                if (!file_exists($filenameXml)) {
                    file_put_contents($filenameXml, $info["xml"]);
                    if (file_exists($filenameXml)) {
                        if (!($arch->checkIfFileExists($info["referencia"], $info["patente"], $info["aduana"], $cove . ".xml"))) {
                            $arch->addNewFile(21, null, $info["referencia"], $info["patente"], $info["aduana"], $cove . ".xml", $filenameXml, (isset($session)) ? $session->username : null);
                        }
                    }
                }
            }
        endif;
    }

    public function guardarEdocAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $session = null ? $session = new Zend_Session_Namespace("") : $session = new Zend_Session_Namespace($this->_config->app->namespace);
        $uuid = $this->_request->getParam("uuid");
        $solicitud = $this->_request->getParam("solicitud");
        $vucemEdoc = new Vucem_Model_VucemEdocMapper();
        if (isset($session)) {
            if ($session->role == "super" || $session->role == "trafico_operaciones") {
                $data = $vucemEdoc->obtenerEdocPorUuid($uuid, $solicitud);
            } else {
                $data = $vucemEdoc->obtenerEdocPorUuid($uuid, $solicitud, $session->username);
            }
        } else {
            $data = $vucemEdoc->obtenerEdocPorUuid($uuid, $solicitud);
        }
        $misc = new OAQ_Misc();
        if ($data["patente"] && $data["aduana"] && $data["referencia"]) {
            $dir = $misc->crearExpedienteDir($this->_appconfig->getParam("expdest"), date("Y"), $data["patente"], $data["aduana"], $data["referencia"]);
            if ($dir) {
                $arch = new Archivo_Model_RepositorioMapper();
                $filename = $dir . DIRECTORY_SEPARATOR . "EDOC_" . $data["edoc"] . ".pdf";
                if (!file_exists($filename)) {
                    define("DOMPDF_ENABLE_REMOTE", true);
                    require_once "dompdf/dompdf_config.inc.php";
                    $vucemEdoc = new Vucem_Model_VucemEdocMapper();
                    if (isset($session)) {
                        if ($session->role == "super" || $session->role == "trafico_operaciones") {
                            $data = $vucemEdoc->obtenerEdocPorUuid($uuid, $solicitud);
                        } else {
                            $data = $vucemEdoc->obtenerEdocPorUuid($uuid, $solicitud, (isset($session)) ? $session->username : null);
                        }
                    } else {
                        $data = $vucemEdoc->obtenerEdocPorUuid($uuid, $solicitud);
                    }
                    $this->view->data = $data;
                    $this->view->id = $uuid;
                    $this->view->solicitud = $solicitud;
                    $this->view->url = $this->_config->app->url;

                    $html = $this->view->render("vucem/convert-edoc-to-pdf.phtml");
                    $dompdf = new DOMPDF();
                    $dompdf->set_paper("letter", "portrait");
                    $dompdf->load_html($html);
                    $dompdf->set_base_path($_SERVER["DOCUMENT_ROOT"]);
                    $dompdf->render();
                    $output = $dompdf->output();
                    file_put_contents($filename, $output);
                    if (file_exists($filename)) {
                        if (!($arch->checkIfFileExists($data["referencia"], $data["patente"], $data["aduana"], "EDOC_" . $data["edoc"] . ".pdf"))) {
                            // 27 Acuse del E-Document                            
                            $arch->addNewFile(27,  $data["subTipoArchivo"], $data["referencia"], $data["patente"], $data["aduana"], "EDOC_" . $data["edoc"] . ".pdf", $filename, (isset($session)) ? $session->username : null, $data["edoc"], null, $data["pedimento"]);
                            $file = $vucemEdoc->obtenerEdocDigitalizado($uuid);
                            $digitalizado = $dir . DIRECTORY_SEPARATOR . $file["nomArchivo"];
                            if (!file_exists($digitalizado)) {
                                file_put_contents($digitalizado, base64_decode($file["archivo"]));
                            }
                            $arch->addNewFile($file["tipoDoc"], $file["subTipoArchivo"], $data["referencia"], $data["patente"], $data["aduana"], $file["nomArchivo"], $digitalizado, (isset($session)) ? $session->username : null, $data["edoc"], null, $data["pedimento"]);
                        }
                    }
                }
            }
        } // isset     
    }

    /**
     * /automatizacion/vucem/imprimir-acuse-cove?id=16322
     * /automatizacion/vucem/imprimir-acuse-cove?id=16322&debug=true
     * 
     * @return boolean
     */
    public function imprimirAcuseCoveAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        require "tcpdf/acusecovevu.php";
        $id = $this->_request->getParam("id", null);
        $debug = $this->_request->getParam("debug", null);
        if (isset($id)) {
            $vucemSol = new Vucem_Model_VucemSolicitudesMapper();
            $data = $vucemSol->obtenerSolicitudPorId($id);
            if (isset($debug) && $debug == true) {
                $vucem = new OAQ_Vucem();
                $array = $vucem->xmlStrToArray($data["xml"]);
                Zend_Debug::dump($array["Body"]["solicitarRecibirCoveServicio"]["comprobantes"], "DATA");
                return false;
            }
            $pdf = new AcuseCoveVU($data, "P", "pt", "LETTER");
            $pdf->Create();
            $pdf->Output("ACUSE_" . $data["cove"] . ".pdf", "I");
        }
    }

    /**
     * /automatizacion/vucem/imprimir-acuse-edocument?uuid=1c7d1dd8-dab0-573e-b93b-f05696bc1ab1&solicitud=28093335
     * /automatizacion/vucem/imprimir-acuse-edocument?uuid=1c7d1dd8-dab0-573e-b93b-f05696bc1ab1&solicitud=28093335&debug=true
     * 
     * @return boolean
     */
    public function imprimirAcuseEdocumentAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "debug" => "StringToLower",
                "solicitud" => "Digits",
                "uuid" => "StringToLower",
            );
            $v = array(
                "debug" => array("NotEmpty", new Zend_Validate_InArray(array("true"))),
                "solicitud" => array("NotEmpty", new Zend_Validate_Int()),
                "uuid" => array(new Zend_Validate_Regex("/^\{?[a-z0-9]{8}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{12}\}?$/")),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("solicitud") && $input->isValid("uuid")) {
                require "tcpdf/acuseedocvu.php";
                $vucemEdoc = new Vucem_Model_VucemEdocMapper();
                $arr = $vucemEdoc->obtenerEdocPorUuid($input->uuid, $input->solicitud);
                if (isset($input->debug) && $input->debug == true) {
                    Zend_Debug::dump($arr);
                    return false;
                }
                $print = new EdocumentVU($arr, "P", "pt", "LETTER");
                $print->Create();
                $print->Output("ACUSE_" . $arr["edoc"] . ".pdf", "I");
            } else {
                throw new Exception("Invalid input: " . Zend_Debug::dump($input->getErrors(), null, true));
            }
        } catch (Zend_Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
    public function printEdocumentAccuseAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "debug" => "StringToLower",
                "id" => "Digits",
            );
            $v = array(
                "debug" => array("NotEmpty", new Zend_Validate_InArray(array("true"))),
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                require "tcpdf/acuseedocvu.php";
                $vucemEdoc = new Vucem_Model_VucemEdocMapper();
                $arr = $vucemEdoc->obtener($input->id);
                $arr["titulo"] = "EDOC_" . $arr["tipoDoc"] . "_ACUSE_" . $arr["edoc"];
                $print = new EdocumentVU($arr, "P", "pt", "LETTER");
                $print->Create();
                $print->Output($arr["titulo"] . ".pdf", "I");
            } else {
                throw new Exception("Invalid input: " . Zend_Debug::dump($input->getErrors(), null, true));
            }
        } catch (Zend_Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function imprimirEdocumentAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "debug" => "StringToLower",
                "id" => "Digits",
            );
            $v = array(
                "debug" => array("NotEmpty", new Zend_Validate_InArray(array("true"))),
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                require "tcpdf/acuseedocvu.php";
                $vucemEdoc = new Vucem_Model_VucemEdocMapper();
                $arr = $vucemEdoc->obtener($input->id);
                if (isset($input->debug) && $input->debug == true) {
                    Zend_Debug::dump($arr);
                    return false;
                }
                $print = new EdocumentVU($arr, "P", "pt", "LETTER");
                $print->Create();
                $print->Output("ACUSE_" . $arr["edoc"] . ".pdf", "I");
            } else {
                throw new Exception("Invalid input: " . Zend_Debug::dump($input->getErrors(), null, true));
            }
        } catch (Zend_Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    /*
     * /automatizacion/vucem/imprimir-detalle-cove?id=16322
     * /automatizacion/vucem/imprimir-detalle-cove?id=16322&debug=true
     * 
     */
    public function imprimirDetalleCoveAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            require "tcpdf/acusevu.php";
            $id = $this->_request->getParam("id", null);
            $cove = $this->_request->getParam("cove", null);
            $debug = $this->_request->getParam("debug", null);
            if (isset($id) || isset($cove)) {
                $vucemSol = new Vucem_Model_VucemSolicitudesMapper();
                if ($id != null && $id != "") {
                    $data = $vucemSol->obtenerSolicitudPorId($id);
                }
                if ($cove != null && $cove != "") {
                    $data = $vucemSol->obtenerSolicitudPorCove($cove);
                }
                if (isset($debug) && $debug == true) {
                    $vucem = new OAQ_Vucem();
                    $array = $vucem->xmlStrToArray($data["xml"]);
                    Zend_Debug::dump($array["Body"]["solicitarRecibirCoveServicio"]["comprobantes"], "DATA");
                    return false;
                }
                $pdf = new DetalleCoveVU($data, "P", "pt", "LETTER");
                $pdf->Create();
                $pdf->Output("DETALLE_" . $data["cove"] . ".pdf", "I");
            } else {
                return false;
            }            
        } catch (Zend_Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function printEdocAction() {
        $uuid = $this->_request->getParam("uuid");
        $solicitud = $this->_request->getParam("solicitud");
        $download = $this->_request->getParam("download", null);
        $save = $this->_request->getParam("save", null);
        $view = $this->_request->getParam("view", null);
        $debug = $this->_request->getParam("debug", null);
        $vucem = new OAQ_VucemEnh();
        $vucem->printEdoc($uuid, $solicitud, $download, $view, $save, $debug);
    }

    public function printCoveAction() {
        $id = $this->_request->getParam("id");
        $download = $this->_request->getParam("download", null);
        $save = $this->_request->getParam("save", null);
        $view = $this->_request->getParam("view", null);
        $debug = $this->_request->getParam("debug", null);
        ini_set("memory_limit", -1);
        $vucem = new OAQ_VucemEnh();
        $vucem->printCove($id, $download, $view, $save, $debug);
    }

    public function printInvoiceAction() {
        $uuid = $this->_request->getParam("uuid");
        $download = $this->_request->getParam("download", null);
        $save = $this->_request->getParam("save", null);
        $view = $this->_request->getParam("view", null);
        ini_set("memory_limit", -1);
        $vucem = new OAQ_VucemEnh();
        $vucem->printInvoice($uuid, $download, $view, $save);
    }

    public function identificadorDesc($iden) {
        $misc = new OAQ_Misc();
        return $misc->identificadorDesc($iden);
    }

    public function number($value) {
        return number_format($value, 3, ".", ",");
    }

    public function number4($value) {
        return number_format($value, 4, ".", ",");
    }

    public function number6($value) {
        return number_format($value, 6, ".", ",");
    }

    /**
     * /automatizacion/vucem/pedimento-vucem?patente=3589&aduana=640&pedimento=4011651
     * /automatizacion/vucem/pedimento-vucem?patente=3589&aduana=640&pedimento=5001190
     * su - www-data -c "php /var/www/workers/pedimentos_worker.php"
     * 
     */
    public function pedimentoVucemAction() {
        $patente = $this->_request->getParam("patente", null);
        $aduana = $this->_request->getParam("aduana", null);
        $pedimento = $this->_request->getParam("pedimento", null);
        $data = array(
            "rfc" => "MALL640523749",
            "pass" => "o/31djAUs/3beePPfEmK7UZmynJC9z/4BwuDcEKgBrIQ/dMBuW2UHLoOWvnA0fst",
            "patente" => $patente,
            "aduana" => $aduana,
            "pedimento" => $pedimento,
        );
        $client = new GearmanClient();
        $client->addServer("127.0.0.1", 4730);
        $client->addTaskBackground("pedimentows", serialize($data));
        $client->runTasks();
    }

    /**
     * /automatizacion/vucem/print-pedimento?patente=3589&aduana=640&pedimento=5001190
     * 
     * @return boolean
     */
    public function printPedimentoAction() {
        $patente = $this->_request->getParam("patente", null);
        $aduana = $this->_request->getParam("aduana", null);
        $pedimento = $this->_request->getParam("pedimento", null);
        $vucem = new OAQ_VucemEnh();
        $model = new Vucem_Model_VucemPedimentosMapper();
        $xml = $model->obtenerXml($patente, $aduana, $pedimento);
        if (isset($xml) && $xml != "") {
            $array = $vucem->xmlStrToArray(file_get_contents($xml));
            return false;
            if (isset($array) && !empty($array)) {
                $vucem->printPedimento();
            }
        }
    }

    /**
     * /automatizacion/vucem/print-pedimento-sitawin?patente=3589&aduana=646&pedimento=6001372
     * /automatizacion/vucem/print-pedimento-sitawin?patente=3589&aduana=646&pedimento=6002389
     * /automatizacion/vucem/print-pedimento-sitawin?patente=3589&aduana=646&pedimento=6001372&debug=true
     * 
     * @return boolean
     */
    public function printPedimentoSitawinAction() {
        $filters = array(
            "*" => array("StringTrim", "StripTags"),
            "patente" => array("Digits"),
            "aduana" => array("Digits"),
            "pedimento" => array("Digits"),
            "debug" => new Zend_Filter_Boolean(array(
                "type" => array(
                    Zend_Filter_Boolean::INTEGER,
                    Zend_Filter_Boolean::ZERO,
                )
                    )),
        );
        $validators = array(
            "patente" => array("NotEmpty", new Zend_Validate_Int()),
            "aduana" => array("NotEmpty", new Zend_Validate_Int()),
            "pedimento" => array("NotEmpty", new Zend_Validate_Int()),
            "debug" => new Zend_Validate_InArray(array(true, false))
        );
        $input = new Zend_Filter_Input($filters, $validators, $this->_request->getParams());
        if ($input->isValid("patente") && $input->isValid("aduana") && $input->isValid("pedimento")) {
            $misc = new OAQ_Misc();
            foreach (array(640, 646, 240, 645) as $k => $v) {
                $db = $misc->sitawin($input->patente, $v);
                if (isset($db)) {
                    $pedimento = $db->wsDetallePedimento($input->pedimento, $input->aduana);
                    if (isset($pedimento) && $pedimento !== false) {
                        break;
                    }
                }
            }
            if (!isset($db)) {
                die("No hay sistema de pedimento.");
            }
            if (isset($pedimento)) {
                $info = $db->pedimentoDatosBasicos($input->pedimento);
                if (isset($info) && !empty($info)) {
                    $data = $db->pedimentoCompleto($input->pedimento);
                    if (isset($input->debug) && $input->debug == true) {
                        // Zend_Debug::Dump($info);
                        // Zend_Debug::Dump($data, "DATA");
                        Zend_Debug::Dump($data["liquidacion"], "LIQUIDACION");
                        Zend_Debug::Dump($data["pago"], "PAGO");
                        return false;
                    }
                    $print = new OAQ_Print();
                    if (APPLICATION_ENV == "production") {
                        if (!file_exists("/tmp/archivos")) {
                            mkdir("/tmp/archivos", 0777);
                        }
                        $print->set_dir("/tmp/archivos");
                    } else {
                        if (!file_exists("C:\\wamp64\\tmp\\pedimentos")) {
                            mkdir("C:\\wamp64\\tmp\\pedimentos", 0777);
                        }
                        $print->set_dir("C:\\wamp64\\tmp\\pedimentos");                        
                    }
                    $print->set_data($data);
                    $print->printPedimentoSitawin();
                } else {
                    throw new Exception("No results!");
                }
            } else {
                throw new Exception("No information found!");
            }
        } else {
            throw new Exception("Invalid input!");
        }
        return;
    }

    /**
     * /automatizacion/vucem/print-pedimento-simplificado-sitawin?patente=3589&aduana=640&pedimento=5001190
     * /automatizacion/vucem/print-pedimento-simplificado-sitawin?patente=3589&aduana=640&pedimento=6002389
     * /automatizacion/vucem/print-pedimento-simplificado-sitawin?patente=3589&aduana=646&pedimento=6001372&debug=true
     * 
     * @return boolean
     */
    public function printPedimentoSimplificadoSitawinAction() {
        $filters = array(
            "*" => array("StringTrim", "StripTags"),
            "patente" => array("Digits"),
            "aduana" => array("Digits"),
            "pedimento" => array("Digits"),
            "debug" => new Zend_Filter_Boolean(array(
                "type" => array(
                    Zend_Filter_Boolean::INTEGER,
                    Zend_Filter_Boolean::ZERO,
                )
            )),
        );
        $validators = array(
            "patente" => array("NotEmpty", new Zend_Validate_Int(), new Zend_Validate_InArray(array("3589"))),
            "aduana" => array("NotEmpty", new Zend_Validate_Int()),
            "pedimento" => array("NotEmpty", new Zend_Validate_Int()),
            "debug" => new Zend_Validate_InArray(array(true, false))
        );
        $input = new Zend_Filter_Input($filters, $validators, $this->_request->getParams());
        if ($input->isValid("patente") && $input->isValid("aduana") && $input->isValid("pedimento")) {
            $misc = new OAQ_Misc();
            foreach (array(640, 240) as $k => $v) {
                $db = $misc->sitawin($input->patente, $v);
                if(isset($db)) {
                    $pedimento = $db->wsDetallePedimento($input->pedimento, $input->aduana);
                    if (isset($pedimento) && $pedimento !== false) {
                        break;
                    }
                }
            }
            if (isset($pedimento)) {
                $info = $db->pedimentoDatosBasicos($input->pedimento);
                if (isset($info) && !empty($info)) {
                    $data = $db->pedimentoSimplicado($input->pedimento);
                    if (isset($input->debug) && $input->debug == true) {
                        Zend_Debug::Dump($info, "INFO");
                        Zend_Debug::Dump($data, "DATA");
                        return false;
                    }
                    $print = new OAQ_Print();
                    if (APPLICATION_ENV == "production") {
                        if (!file_exists("/tmp/archivos")) {
                            mkdir("/tmp/archivos", 0777);
                        }
                        $print->set_dir("/tmp/archivos");
                    } else {
                        if (!file_exists("C:\\wamp64\\tmp\\pedimentos")) {
                            mkdir("C:\\wamp64\\tmp\\pedimentos", 0777);
                        }
                        $print->set_dir("C:\\wamp64\\tmp\\pedimentos");                        
                    }
                    $print->set_data($data);
                    $print->printPedimentoSimplificadoSitawin();
                } else {
                    throw new Exception("No results!");
                }
            } else {
                throw new Exception("No information found!");
            }
        } else {
            throw new Exception("Invalid input!");
        }
        return;
    }

    /**
     * /automatizacion/vucem/print-pedimento-aduanet?patente=3589&aduana=640&pedimento=5001190
     * 
     * @return boolean
     */
    public function printPedimentoAduanetAction() {
        $patente = $this->_request->getParam("patente", null);
        $aduana = $this->_request->getParam("aduana", null);
        $pedimento = $this->_request->getParam("pedimento", null);
        $debug = $this->_request->getParam("debug", null);
        if ($aduana == 240 && $patente == 3589) {
            $db = new OAQ_AduanetM3(true, "localhost", "root", "mysql11!", "SAAIWEB", 3306);
        }
        try {
            if (isset($db)) {
                $info = $db->pedimentoDatosBasicos($pedimento);
                if (isset($info) && !empty($info)) {
                    $data = $db->pedimentoCompleto($pedimento);
                    if (isset($debug) && $debug == true) {
                        Zend_Debug::Dump($info);
                        Zend_Debug::Dump($data);
                        return false;
                    }
                    $vucem = new OAQ_VucemEnh();
                    $vucem->printPedimentoAduanet($data);
                }
            }
        } catch (Exception $ex) {
            Zend_Debug::Dump($ex->getMessage());
        }
    }
    
    public function guardarCoveAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $flt = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                );
                $vld = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($flt, $vld, $request->getPost());
                if ($input->isValid("id")) {
                    $vucem = new OAQ_VucemEnh();
                    $vucem->printCove($input->id, null, null, true);
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function guardarEdocumentAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $flt = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                    "solicitud" => array("Digits"),
                );
                $vld = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "solicitud" => array("NotEmpty", new Zend_Validate_Int())
                );
                $input = new Zend_Filter_Input($flt, $vld, $request->getPost());
                if ($input->isValid("solicitud") && $input->isValid("id")) {
                    $session = null ? $session = new Zend_Session_Namespace("") : $session = new Zend_Session_Namespace($this->_config->app->namespace);                    
                    $misc = new OAQ_Misc();
                    $sello = new Vucem_Model_VucemFirmanteMapper();
                    $mapper = new Vucem_Model_VucemEdocMapper();
                    $index = new Vucem_Model_VucemEdocIndex();
                    $arr = $mapper->obtenerEdocument($input->id, $input->solicitud);
                    $vucemFiles = new OAQ_VucemArchivos(array(
                        "id" => $input->id,
                        "solicitud" => $input->solicitud,
                        "dir" => $misc->nuevoDirectorio($this->_appconfig->getParam("expdest"), $arr["patente"], $arr["aduana"], $arr["referencia"]),
                        "data" => $arr,
                        "sello" => $sello->obtenerDetalleFirmante($arr["rfc"]),
                        "username" => isset($this->_session->username) ? $this->_session->username : "Auto",
                    ));
                    if($vucemFiles->guardarEdoc()) {
                        $mapper->saved($arr["id"]);
                        $index->saved($arr["id"]);
                        $this->_helper->json(array("success" => true, "id" => $arr["id"]));
                    }
                    $this->_helper->json(array("success" => false, "message" => "No se pudo guardar el Edocument."));
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Zend_Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function respuestaAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $flt = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                    "solicitud" => array("Digits"),
                );
                $vld = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "solicitud" => array("NotEmpty", new Zend_Validate_Int())
                );
                $input = new Zend_Filter_Input($flt, $vld, $request->getPost());
                if ($input->isValid("solicitud") && $input->isValid("id")) {
                    $this->_helper->json(array("success" => true, "id" => $input->id, "solicitud" => $input->solicitud));
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function proformaAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        require "tcpdf/proforma.php";
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "uuid" => new Zend_Filter_StringToLower(),
            );
            $v = array(
                "uuid" => array(new Zend_Validate_Regex("/^[-_a-zA-Z0-9.\/]+$/"), "presence" => "required"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_getAllParams());
            if ($input->isValid("uuid")) {
                $mapper = new Vucem_Model_VucemTmpFacturasMapper();
                if ($mapper->find($input->uuid)) {
                    $inv = $mapper->getInvoiceData($input->uuid);
                    $pdf = new Proforma($inv, "P", "pt", "LETTER");
                    $pdf->Create();
                    $pdf->Output("FACTURA_" . $inv["Patente"] . "_" . $inv["Aduana"] . "_" . $inv["Pedimento"] . ".pdf", "I");
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function proformaCoveAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        require "tcpdf/proforma.php";
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "uuid" => new Zend_Filter_StringToLower(),
            );
            $v = array(
                "uuid" => array(new Zend_Validate_Regex("/^[-_a-zA-Z0-9.\/]+$/"), "presence" => "required"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_getAllParams());
            if ($input->isValid("uuid")) {
                $mapper = new Vucem_Model_VucemFacturasMapper();
                if ($mapper->find($input->uuid)) {
                    $inv = $mapper->getInvoiceData($input->uuid);
                    $pdf = new Proforma($inv, "P", "pt", "LETTER");
                    $pdf->Create();
                    $pdf->Output("FACTURA_" . $inv["Patente"] . "_" . $inv["Aduana"] . "_" . $inv["Pedimento"] . ".pdf", "I");
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    /**
     * /automatizacion/vucem/listen-edocs
     */
    public function listenEdocsAction() {
        if (APPLICATION_ENV === "development") {
            set_time_limit(180);
        }
        $worker = new OAQ_Workers_EdocReceiver();
        $worker->listenEdocs();
    }

    /**
     * /automatizacion/vucem/seleccionar-factura?patente=3589&aduana=640&pedimento=7010658&numFactura=0155166945
     */
    public function seleccionarFacturaAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "patente" => "Digits",
                "aduana" => "Digits",
                "pedimento" => array("Digits"),
            );
            $v = array(
                "patente" => array("NotEmpty", new Zend_Validate_Int()),
                "aduana" => array("NotEmpty", new Zend_Validate_Int()),
                "pedimento" => array("NotEmpty", new Zend_Validate_Int()),
                "numFactura" => "NotEmpty",
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("patente") && $input->isValid("pedimento") && $input->isValid("aduana")) {
                $misc = new OAQ_Misc();
                $sita = $misc->sitawinTrafico($input->patente, $input->aduana);
                $arr = $sita->buscarPedimento($input->pedimento);
                if (isset($arr["consolidado"]) && $arr["consolidado"] == true) {
                    $factura = $sita->seleccionarFacturaImportacion($arr["referencia"], $input->pedimento, $input->numFactura, $arr["tipoCambio"], true);
                } else {
                    $factura = $sita->seleccionarFacturaImportacion($arr["referencia"], $input->pedimento, $input->numFactura, $arr["tipoCambio"]);                    
                }
                $this->_helper->json($factura);
            }
        } catch (Exception $e) {
            
        }
    }

    public function consumeRestAction() {
        /*$client = new Zend_Rest_Client('http://localhost:3000', array('timeout' => 30));
        $options['patente'] = '3589';
        $options['aduana'] = '240';
        $options['pedimento'] = '8000393';
        $response = $client->restPost('/aduanet/basico', $options);

        if ($response->getBody()) {
            header('Content-Type: application/json');
            echo $response->getBody();
        }*/
        $client = new Zend_Rest_Client('http://162.253.186.242:3001', array('timeout' => 30));
        $options['patente'] = '3589';
        $options['aduana'] = '240';
        $options['pedimento'] = '8000393';
        $response = $client->restPost('/aduanet/basico', $options);

        if ($response->getBody()) {
            header('Content-Type: application/json');
            echo $response->getBody();
        }
    }
        
}
