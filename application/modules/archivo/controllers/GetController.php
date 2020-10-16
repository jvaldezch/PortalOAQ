<?php

class Archivo_GetController extends Zend_Controller_Action
{

    protected $_session;
    protected $_config;
    protected $_appconfig;

    public function init()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
    }

    public function preDispatch()
    {
        $this->_session = NULL ? $this->_session = new Zend_Session_Namespace("") : $this->_session = new Zend_Session_Namespace($this->_config->app->namespace);
        if ($this->_session->authenticated == true) {
            $session = new OAQ_Session($this->_session, $this->_appconfig);
            $session->actualizar();
            $session->actualizarSesion();
        } else {
            $this->getResponse()->setRedirect($this->_appconfig->getParam("link-logout"));
        }
    }

    public function estatusFtpAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $mppr = new Application_Model_LogMapper();
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");
                $arr = $mppr->estatus($input->id);
                if (!empty($arr)) {
                    $view->results = $arr;
                }
                $this->_helper->json(array("success" => true, "html" => $view->render("estatus-ftp.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Zend_Exception $e) {
            throw new Zend_Exception($e->getMessage());
        }
    }

    public function permalinkAction()
    {
        require_once 'random_compat/psalm-autoload.php';
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $mppr = new Archivo_Model_RepositorioPermalinks();
                if (!($validar = $mppr->verificar($input->id))) {
                    $validar = base64_encode(random_bytes(32));
                    $mppr->agregar($input->id, $validar, $this->_session->username);
                }
                if (APPLICATION_ENV == "production") {
                    $uri = "https://oaq.dnsalias.net/clientes/expediente?code=" . urlencode($validar);
                } else if (APPLICATION_ENV == "staging") {
                    $uri = "http://192.168.0.191/clientes/expediente?code=" . urlencode($validar);
                } else {
                    $uri = "http://localhost:8090/clientes/expediente?code=" . urlencode($validar);
                }
                echo "<p>El permalink sirve para enviarle este expediente a un cliente sin tener que enviar todos los archivos.</p><br>"
                    . "<input id=\"permalinkUri\" value=\"{$uri}\" class=\"traffic-input-large\" style=\"width: 650px !important\" />"
                    . "<script>$('#permalinkUri').focus(function() { $(this).select(); });</script>";
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Zend_Exception $e) {
            throw new Zend_Exception($e->getMessage());
        }
    }

    public function descargarArchivoAction()
    {
        try {
            $f = array(
                "id" => array("Digits", "StringTrim", "StripTags"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $mppr = new Archivo_Model_RepositorioMapper();
                $arr = $mppr->getFileById($input->id);
                $tmpDir = $this->_appconfig->getParam("tmpDir");
                if (is_readable($arr["ubicacion"]) && file_exists($arr["ubicacion"])) {
                    $sha = sha1_file($arr["ubicacion"]);
                    copy($arr["ubicacion"], $tmpDir . DIRECTORY_SEPARATOR . $sha);
                    $basename = basename($arr["ubicacion"]);
                }
                if ((int) $arr["tipo_archivo"] == 22 && preg_match("/.xml$/i", $arr["nom_archivo"])) {
                    $misc = new OAQ_Misc();
                    $xml = file_get_contents($arr["ubicacion"], $tmpDir . DIRECTORY_SEPARATOR . $sha);
                    $cleanXml = $misc->removeSecurityHeaders($xml);
                    header("Content-Type: application/octet-stream");
                    header("Content-Transfer-Encoding: Binary");
                    header("Content-Length: " . strlen($cleanXml));
                    header("Content-disposition: attachment; filename=\"" . $basename . "\"");
                    echo $cleanXml;
                } elseif ((int) $arr["tipo_archivo"] == 56 && preg_match("/.xml$/i", $arr["nom_archivo"])) {
                    header("Content-Type: application/octet-stream");
                    header("Content-Transfer-Encoding: Binary");
                    header("Content-Length: " . filesize($tmpDir . DIRECTORY_SEPARATOR . $sha));
                    header("Content-disposition: attachment; filename=\"" . $basename . "\"");
                    readfile($tmpDir . DIRECTORY_SEPARATOR . $sha);
                } else {
                    header("Content-Type: application/octet-stream");
                    header("Content-Transfer-Encoding: Binary");
                    header("Content-Length: " . filesize($tmpDir . DIRECTORY_SEPARATOR . $sha));
                    header("Content-disposition: attachment; filename=\"" . $basename . "\"");
                    readfile($tmpDir . DIRECTORY_SEPARATOR . $sha);
                }
                unlink($tmpDir . DIRECTORY_SEPARATOR . $sha);
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function descargarCarpetaAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $misc = new OAQ_Misc();
                $exp = new OAQ_Expediente_Descarga();
                $index = new Archivo_Model_RepositorioIndex();
                $arr = $index->datos($input->id);
                $model = new Archivo_Model_RepositorioMapper();
                $referencias = new OAQ_Referencias();
                $res = $referencias->restricciones($this->_session->id, $this->_session->role);
                if (!in_array($this->_session->role, array("inhouse", "cliente", "proveedor"))) {
                    $files = $model->getFilesByReferenceUsers($arr["referencia"], $arr["patente"], $arr["aduana"]);
                } else if (in_array($this->_session->role, array("proveedor"))) {
                    $files = $model->obtener($arr["referencia"], $arr["patente"], $arr["aduana"], json_decode($res["documentos"]));
                } else if (in_array($this->_session->role, array("inhouse"))) {
                    $files = $model->obtener($arr["referencia"], $arr["patente"], $arr["aduana"], json_decode($res["documentos"]));
                } else if (in_array($this->_session->role, array("cliente"))) {
                    $files = $model->getFilesByReferenceCustomers($arr["referencia"], $arr["patente"], $arr["aduana"]);
                }
                $complementos = $model->complementosReferencia($arr["referencia"]);

                if (count($files)) {
                    $zipName = $exp->zipFilename($arr["patente"], $arr["aduana"], $arr["pedimento"], $misc->limpiarNombreReferencia($arr["referencia"]), $arr["rfcCliente"]);
                    $zipDir = "D:\\Tmp\zips";
                    if (APPLICATION_ENV === "production" || APPLICATION_ENV === "staging") {
                        $zipDir = "/tmp/zips";
                    }
                    if (!file_exists($zipDir)) {
                        mkdir($zipDir, 0777, true);
                    }
                    $zipFilename = $zipDir . DIRECTORY_SEPARATOR . $zipName;
                    if (file_exists($zipFilename)) {
                        unlink($zipFilename);
                    }
                    $zip = new ZipArchive();
                    if ($zip->open($zipFilename, ZIPARCHIVE::CREATE) !== TRUE) {
                        return null;
                    }
                    foreach ($files as $file) {
                        if (file_exists($file["ubicacion"])) {
                            $tmpfile = $zipDir . DIRECTORY_SEPARATOR . sha1($file["ubicacion"]);
                            copy($file["ubicacion"], $tmpfile);
                            if (($zip->addFile($tmpfile, $exp->filename($arr["patente"], $arr["aduana"], $arr["pedimento"], basename($file["ubicacion"]), $file["tipo_archivo"]))) === true) {
                                $added[] = $tmpfile;
                            }
                            unset($tmpfile);
                        }
                    }
                    if (!empty($complementos)) {
                        foreach ($complementos as $file) {
                            if (file_exists($file["ubicacion"])) {
                                $tmpfile = $zipDir . DIRECTORY_SEPARATOR . sha1($file["ubicacion"]);
                                copy($file["ubicacion"], $tmpfile);
                                if (($zip->addFile($tmpfile, $exp->filename($arr["patente"], $arr["aduana"], $arr["pedimento"], basename($file["ubicacion"]), $file["tipo_archivo"]))) === true) {
                                    $added[] = $tmpfile;
                                }
                                unset($tmpfile);
                            }
                        }
                    }

                    $val = new OAQ_ArchivosValidacion();
                    if (isset($arr["pedimento"])) {
                        $arch_val = $val->archivosDePedimento($arr["patente"], $arr["aduana"], $arr["pedimento"]);
                        if (!empty($arch_val)) {
                            $mppr_val = new Automatizacion_Model_ArchivosValidacionMapper();
                            foreach ($arch_val as $a_val) {
                                if ($a_val['idArchivoValidacion']) {
                                    $file_val = $mppr_val->fileContent($a_val['idArchivoValidacion']);
                                    if ($file_val) {
                                        $zip->addFromString($a_val['archivoNombre'], base64_decode($file_val["contenido"]));
                                    }
                                }
                            }
                        }
                    }

                    if (($zip->close()) === TRUE) {
                        $closed = true;
                    }
                    if ($closed === true) {
                        foreach ($added as $tmp) {
                            unlink($tmp);
                        }
                    }
                    if (file_exists($zipFilename)) {
                        if (!is_file($zipFilename)) {
                            header($this->getRequest()->getServer('SERVER_PROTOCOL') . " 404 Not Found");
                            echo "File not found";
                        } else if (!is_readable($zipFilename)) {
                            header($this->getRequest()->getServer('SERVER_PROTOCOL') . " 403 Forbidden");
                            echo "File not readable";
                        }
                        header($this->getRequest()->getServer('SERVER_PROTOCOL') . " 200 OK");
                        header("Content-Type: application/zip");
                        header("Content-Transfer-Encoding: Binary");
                        header("Content-Length: " . filesize($zipFilename));
                        header("Content-Disposition: attachment; filename=\"" . basename($zipFilename) . "\"");
                        readfile($zipFilename);
                        unlink($zipFilename);
                        return false;
                    }
                } else {
                    throw new Exception("No files!");
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Zend_Exception $e) {
            throw new Zend_Exception($e->getMessage());
        }
    }

    public function ayudaDocumentosAction()
    {
        try {
            $this->_helper->viewRenderer->setNoRender(false);
            $mapper = new Archivo_Model_RepositorioPrefijos();
            $arr = $mapper->todos();
            $this->view->documentos = $arr;
        } catch (Zend_Exception $e) {
            throw new Zend_Exception($e->getMessage());
        }
    }

    public function imprimirPrefijosAction()
    {
        try {
            $mapper = new Archivo_Model_RepositorioPrefijos();
            $arr = array(
                "empresa" => "ORGANIZACIÓN ADUANAL DE QUERÉTARO S.C.",
                "documentos" => $mapper->todos(),
            );
            $print = new OAQ_PrintPrefijos($arr, "P", "pt", "LETTER");
            if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN") {
                $diretory = "d:\\Tmp\\php_archivos";
            } else {
                $diretory = "/tmp/archivos";
            }
            if (!file_exists($diretory)) {
                mkdir($diretory, 0777);
            }
            $print->set_dir($diretory);
            $print->set_filename("PREFIJOS.pdf");
            $print->printPrefijos();
            $print->Output($print->get_filename(), "I");
        } catch (Zend_Exception $e) {
            throw new Zend_Exception($e->getMessage());
        }
    }

    public function descargarArchivoTerminalAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $terminal = new OAQ_TerminalLogistics();
                $terminal->descargar($input->id, $this->getRequest()->getServer('SERVER_PROTOCOL'));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Zend_Exception $e) {
            throw new Zend_Exception($e->getMessage());
        }
    }

    public function pdfFacturaTerminalAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $terminal = new OAQ_TerminalLogistics();
                $terminal->verPdf($input->id);
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Zend_Exception $e) {
            throw new Zend_Exception($e->getMessage());
        }
    }

    public function xmlFacturaTerminalAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $terminal = new OAQ_TerminalLogistics();
                $terminal->verXml($input->id);
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Zend_Exception $e) {
            throw new Zend_Exception($e->getMessage());
        }
    }

    public function verArchivoAction()
    {
        try {
            $f = array(
                "id" => array("Digits", "StringTrim", "StripTags"),
                "view" => "StringToLower",
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
                "view" => array("NotEmpty", new Zend_Validate_InArray(array(true, false)))
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                if (boolval($input->view) === true) {
                    $mppr = new Archivo_Model_RepositorioMapper();
                    $arr = $mppr->getFileById($input->id);
                    if (is_readable($arr["ubicacion"]) && file_exists($arr["ubicacion"])) {
                        $sha = sha1_file($arr["ubicacion"]);
                        $basename = basename($arr["ubicacion"]);
                        $tmpDir = $this->_appconfig->getParam("tmpDir");
                        if (copy($arr["ubicacion"], $tmpDir . DIRECTORY_SEPARATOR . $sha)) {
                            if (file_exists($tmpDir . DIRECTORY_SEPARATOR . $sha)) {
                                header("Content-Type: application/octet-stream");
                                header("Content-Transfer-Encoding: Binary");
                                header("Content-Length: " . filesize($tmpDir . DIRECTORY_SEPARATOR . $sha));
                                header("Content-disposition: attachment; filename=\"" . $basename . "\"");
                                readfile($tmpDir . DIRECTORY_SEPARATOR . $sha);
                                unlink($tmpDir . DIRECTORY_SEPARATOR . $sha);
                            }
                        }
                    }
                } else {
                    $view = new Zend_View();
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                    $view->id = $input->id;
                    echo $view->render("ver-archivo.phtml");
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function verArchivoClienteAction()
    {
        try {
            $f = array(
                "id" => array("Digits", "StringTrim", "StripTags"),
                "view" => "StringToLower",
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
                "view" => array("NotEmpty", new Zend_Validate_InArray(array(true, false)))
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                if (boolval($input->view) === true) {
                    $mppr = new Archivo_Model_RepositorioFiscalMapper();
                    $arr = $mppr->buscar($input->id);
                    if (file_exists($arr["ubicacion"] . DIRECTORY_SEPARATOR . $arr["nombreArchivo"])) {
                        header("Cache-Control: public");
                        header("Content-Description: File Transfer");
                        header("Content-Disposition: attachment; filename=" . $arr["nombreArchivo"] . "");
                        header("Content-length: " . filesize($arr["ubicacion"] . DIRECTORY_SEPARATOR . $arr["nombreArchivo"]));
                        header("Content-Transfer-Encoding: binary");
                        header("Content-Type: binary/octet-stream");
                        readfile($arr["ubicacion"] . DIRECTORY_SEPARATOR . $arr["nombreArchivo"]);
                    }
                } else {
                    $view = new Zend_View();
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                    $view->id = $input->id;
                    echo $view->render("ver-archivo-cliente.phtml");
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function descargaArchivoClienteAction()
    {
        try {
            $f = array(
                "id" => array("Digits", "StringTrim", "StripTags"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $mppr = new Archivo_Model_RepositorioFiscalMapper();
                $arr = $mppr->buscar($input->id);
                if (file_exists($arr["ubicacion"] . DIRECTORY_SEPARATOR . $arr["nombreArchivo"])) {
                    header("Cache-Control: public");
                    header("Content-Description: File Transfer");
                    header("Content-Disposition: attachment; filename=" . $arr["nombreArchivo"] . "");
                    header("Content-length: " . filesize($arr["ubicacion"] . DIRECTORY_SEPARATOR . $arr["nombreArchivo"]));
                    header("Content-Transfer-Encoding: binary");
                    header("Content-Type: binary/octet-stream");
                    readfile($arr["ubicacion"] . DIRECTORY_SEPARATOR . $arr["nombreArchivo"]);
                } else {
                    throw new Exception("File does not exist!");
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function descargarFacturasTerminalAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
            );
            $v = array(
                "fechaInicio" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
                "fechaFin" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            $terminal = new OAQ_TerminalLogistics();
            $terminal->todas(null, null, null, $input->fechaInicio, $input->fechaFin);
            $files = $terminal->get_rows();
            if (!empty($files)) {
                $zipFilename = "TERMINAL_" . $input->fechaInicio . "_" . $input->fechaFin . ".zip";
                if (($zipFilename = $terminal->crearZip($zipFilename, $files))) {
                    header($this->getRequest()->getServer('SERVER_PROTOCOL') . " 200 OK");
                    header("Content-Type: application/zip");
                    header("Content-Transfer-Encoding: Binary");
                    header("Content-Length: " . filesize($zipFilename));
                    header("Content-Disposition: attachment; filename=\"" . basename($zipFilename) . "\"");
                    readfile($zipFilename);
                    unlink($zipFilename);
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function reporteFacturasTerminalAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
            );
            $v = array(
                "fechaInicio" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
                "fechaFin" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
                "switchData" => "NotEmpty",
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            $noData = filter_var($input->switchData, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $terminal = new OAQ_TerminalLogistics();
            $terminal->reporte($input->fechaInicio, $input->fechaFin, $noData);
            $arr = $terminal->get_rows();
            $reportes = new OAQ_ExcelReportes();
            $reportes->reportesTrafico(60, $arr);
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function descargarFtpAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $f = array(
                "id" => array("StringTrim", "StripTags", "Digits"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($i->isValid("id")) {
                $ftp = new OAQ_Archivos_FtpDescarga($i->id, $this->_appconfig->getParam("ftpfolder"));
                if (($link = $ftp->obtenerLink())) {
                    echo $link;
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function mvhcEstatusObtenerAction()
    {
        try {
            $f = array(
                "id" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags(), new Zend_Filter_Digits()),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $mppr = new Archivo_Model_RepositorioIndex();
                if (($arr = $mppr->datos($input->id))) {
                    $this->_helper->json(array(
                        "success" => true,
                        "mvhcCliente" => $arr["mvhcCliente"],
                        "mvhcFirmado" => $arr["mvhcFirmada"],
                        "mvhcEnviada" => $arr["mvhcEnviada"],
                        "numGuia" => $arr["numGuia"],
                    ));
                } else {
                    throw new Exception("No data found!");
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Zend_Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function recargarDirectorioAction()
    {
        try {
            $f = array(
                "id" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags(), new Zend_Filter_Digits()),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $mppr = new Archivo_Model_RepositorioIndex();
                if (($arr = $mppr->datos($input->id))) {
                    $mdl = new Archivo_Model_RepositorioMapper();

                    $misc = new OAQ_Misc();
                    if (APPLICATION_ENV == "production") {
                        $misc->set_baseDir($this->_appconfig->getParam("expdest"));
                    } else {
                        $misc->set_baseDir("D:\\xampp\\tmp\\expedientes");
                    }
                    if (($path = $misc->directorioExpedienteDigital($arr["patente"], $arr["aduana"], $arr["referencia"]))) {

                        if (file_exists($path)) {
                            $files = scandir($path);
                            if (!empty($files)) {
                                $files = array_diff($files, array('..', '.'));
                                $archivos = array();
                                foreach ($files as $item) {
                                    if (file_exists($path . DIRECTORY_SEPARATOR . $item)) {
                                        $archivos[] = $path . DIRECTORY_SEPARATOR . $item;
                                    }
                                }
                                if (!empty($archivos)) {
                                    foreach ($archivos as $item) {
                                        $tipoArchivo = $misc->tipoArchivo(basename($item));

                                        $ext = pathinfo($item, PATHINFO_EXTENSION);
                                        if (preg_match('/msg/i', $ext)) {
                                            $tipoArchivo = 2001;
                                        }

                                        if (!($mdl->verificarArchivo($arr["patente"], $arr["referencia"], basename($item)))) {
                                            $mdl->nuevoArchivo($tipoArchivo, null, $arr["patente"], $arr["aduana"], $arr["pedimento"], $arr["referencia"], basename($item), $item, $this->_session->username, $arr["rfcCliente"]);
                                        }
                                    }
                                }
                                $this->_helper->json(array("success" => true, "id" => $input->id));
                            }
                        }
                        $this->_helper->json(array("success" => true, "directory" => $path));
                    }
                } else {
                    throw new Exception("No data found!");
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Zend_Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function enviarEmailAction()
    {
        try {
            $f = array(
                "id" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags(), new Zend_Filter_Digits()),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");

                $referencias = new OAQ_Referencias();
                $res = $referencias->restricciones($this->_session->id, $this->_session->role);
                $mapper = new Archivo_Model_RepositorioIndex();
                $arr = $mapper->datos($input->id, $res["idsAduana"], $res["rfcs"]);

                $repo = new Archivo_Model_RepositorioMapper();
                $files = $repo->getFilesByReferenceUsers($arr["referencia"], $arr["patente"], $arr["aduana"], array(23, 32, 33));
                if (!empty($files)) {
                    $view->archivos = $files;
                }

                if (isset($arr["rfcCliente"])) {
                    $cust = new Trafico_Model_ClientesMapper();
                    $cli = $cust->buscarRfc($arr["rfcCliente"]);
                    if (!empty($cli)) {
                        $cont = new Trafico_Model_ContactosCliMapper();
                        $contacts = $cont->obtenerTodos($cli["id"]);
                        (!empty($contacts)) ? $view->contacts = $contacts : null;
                    }
                }

                $view->id = $input->id;
                echo $view->render("enviar-email.phtml");
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Zend_Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function descargarArchivoTemporalAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
            );
            $v = array(
                "id" => array(new Zend_Validate_Int()),
            );
            $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($i->isValid("id")) {
                $mppr = new Archivo_Model_RepositorioTemporalMapper();
                $arr = $mppr->get($i->id);
                if (file_exists($arr["ubicacion"])) {
                    header("Cache-Control: public");
                    header("Content-Description: File Transfer");
                    header("Content-Disposition: attachment; filename=" . urlencode($arr["nombreArchivo"]) . "");
                    header("Content-length: " . filesize($arr["ubicacion"]));
                    header("Content-Transfer-Encoding: binary");
                    header("Content-Type: binary/octet-stream");
                    readfile($arr["ubicacion"]);
                } else {
                    throw new Exception("File does not exist!");
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Zend_Exception($ex->getMessage());
        }
    }

    public function actualizarTraficoAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {

                $index = new Archivo_Model_RepositorioIndex();
                $arr = $index->datos($input->id);

                if (isset($arr['idTrafico'])) {
                    $trafico = new OAQ_Trafico(array("idTrafico" => $arr['idTrafico'], "idUsuario" => $this->_session->id));
                    $array = array(
                        "idRepositorio" => $input->id,
                        "revisionAdministracion" => $arr['revisionAdministracion'],
                        "revisionOperaciones" => $arr['revisionOperaciones'],
                        "completo" => $arr['completo'],
                    );
                    $trafico->actualizar($array);

                    $this->_helper->json(array("success" => true));
                }

                $this->_helper->json(array("success" => false));
            } else {
                $this->_helper->json(array("success" => false, "message" => "Invalid input!"));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function buscarTraficoAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {

                $index = new Archivo_Model_RepositorioIndex();
                $arr = $index->datos($input->id);

                $trafico = new Trafico_Model_TraficosMapper();

                if (($row = $trafico->search($arr['patente'], $arr['aduana'], $arr['referencia']))) {

                    $traffic = new OAQ_Trafico(array("idTrafico" => $row["id"], "idUsuario" => $this->_session->id));

                    $index->update($input->id, array("idTrafico" => $row["id"], "pedimento" => $row["pedimento"], "rfcCliente" => $row["rfcCliente"]));

                    $array = array(
                        "idRepositorio" => $arr['id'],
                        "revisionAdministracion" => $arr['revisionAdministracion'],
                        "revisionOperaciones" => $arr['revisionOperaciones'],
                        "completo" => $arr['completo'],
                    );
                    $traffic->actualizar($array);

                    $this->_helper->json(array("success" => true));
                }

                $this->_helper->json(array("success" => false));
            } else {
                $this->_helper->json(array("success" => false, "message" => "Invalid input!"));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function reporteCuentasDeGastosAction()
    {
        try {

            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "page" => array("Digits"),
                "rows" => array("Digits"),
                "rfc" => array("StringToUpper"),
                "nombre" => array("StringToUpper"),
            );
            $v = array(
                "page" => array(new Zend_Validate_Int(), "default" => 1),
                "rows" => array(new Zend_Validate_Int(), "default" => 20),
                "fechaIni" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/"), "default" => date("Y-m-") . "01"),
                "fechaFin" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/"), "default" => date("Y-m-d")),
                "rfcCliente" => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("page") && $input->isValid("rows")) {

                $mppr = new Archivo_Model_CuentasGastosMapper();
                $select = $mppr->searchSql($input->fechaIni, $input->fechaFin, $input->rfcCliente);

                $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
                $paginator->setCurrentPageNumber($input->page);
                $paginator->setItemCountPerPage($input->rows);

                $arr = array(
                    "total" => $paginator->getTotalItemCount(),
                    "rows" => iterator_to_array($paginator),
                    "paginator" => $paginator->getPages(),
                );

                $this->_helper->json($arr);
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function descargarCuentasDeGastosAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "page" => array("Digits"),
                "rows" => array("Digits"),
                "rfcCliente" => array("StringToUpper"),
            );
            $v = array(
                "page" => array(new Zend_Validate_Int(), "default" => 1),
                "rows" => array(new Zend_Validate_Int(), "default" => 20),
                "fechaIni" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/"), "default" => date("Y-m-") . "01"),
                "fechaFin" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/"), "default" => date("Y-m-d")),
                "rfcCliente" => array("NotEmpty"),
                "ids" => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("page") && $input->isValid("rows")) {

                if (APPLICATION_ENV == "production") {
                    $tmpDir = "/tmp/zipcuentas";
                    if (!file_exists($tmpDir)) {
                        mkdir($tmpDir, 0777, true);
                    }
                } else {
                    $tmpDir = "D:\\xampp\\tmp\\zipcuentas";
                    if (!file_exists($tmpDir)) {
                        mkdir($tmpDir);
                    }
                }

                $mppr = new Archivo_Model_CuentasGastosMapper();
                $arr = $mppr->getXmlPaths($input->fechaIni, $input->fechaFin, $input->rfcCliente, $input->ids);

                if (!empty($arr)) {
                    $misc = new OAQ_Misc();
                    $zipName = "CUENTASDEGASTOS_" . date("Ymd_Hms", time()) . ".zip";
                    $zipFilename = $tmpDir . DIRECTORY_SEPARATOR . $zipName;

                    $created = $misc->createZipFile($arr, $zipFilename);
                    if ($created == true) {

                        header("Content-type: application/zip");
                        header('Content-Disposition: attachment; filename="' . basename($zipFilename) . '"');
                        header("Cache-Control: no-store, no-cache, must-revalidate");
                        header("Pragma: no-cache");
                        header("Content-length: " . filesize(realpath($zipFilename)));
                        header("Expires: 0");
                        readfile(realpath($zipFilename));
                        unlink($zipFilename);
                    }
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function imprimirChecklistAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
                "idTrafico" => array("Digits"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());

            $index = new Archivo_Model_RepositorioIndex();
            if ($input->isValid("idTrafico")) {
                $idRepo = $index->buscarPorTrafico($input->idTrafico);

                $arr = $index->datos($idRepo);

                $check = new Archivo_Model_ChecklistReferencias();

                $checklist = $check->buscar($arr['patente'], $arr['aduana'], $arr['referencia']);

                $checkjson = json_decode($checklist->checklist, true);

                $log = new Archivo_Model_ChecklistReferenciasBitacora();
                $logs = $log->obtener($arr["patente"], $arr["aduana"], $arr["pedimento"], $arr["referencia"]);

                $data = array(
                    "patente" => $arr['patente'],
                    "aduana" => $arr['aduana'],
                    "referencia" => $arr['referencia'],
                    "pedimento" => str_pad($arr["pedimento"], 7, '0', STR_PAD_LEFT),
                    "bitacora" => (isset($logs) && !empty($logs)) ? $logs : null,
                    "checklist" => $checkjson,
                    "revisionAdministracion" => $arr['revisionAdministracion'],
                    "revisionOperaciones" => $arr['revisionOperaciones'],
                    "completo" => $arr['completo'],
                    "observaciones" => $checklist->observaciones,
                );

                $print = new OAQ_Imprimir_Checklist($data, "P", "pt", "LETTER");
                $print->checklist();
                $print->set_filename("CHECKLIST_{$arr["referencia"]}.pdf");
                $print->Output($print->get_filename(), "I");
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function vistaPreviaAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idTrafico" => array("Digits"),
            );
            $v = array(
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idTrafico")) {

                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");

                $traffic = new OAQ_Trafico(array("idTrafico" => $input->idTrafico));
                $view->results = $traffic->archivosDeExpediente(true);

                $this->_helper->json(array("success" => true, "html" => $view->render("vista-previa.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
}
