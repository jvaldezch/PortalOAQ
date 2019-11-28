<?php

class Archivo_DataController extends Zend_Controller_Action {

    protected $_session;
    protected $_config;
    protected $_appconfig;
    protected $_arch;
    protected $_logger;

    public function init() {
        $this->_helper->layout->setLayout('bootstrap');
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_logger = Zend_Registry::get("logDb");
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
    }

    public function preDispatch() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_session = NULL ? $this->_session = new Zend_Session_Namespace('') : $this->_session = new Zend_Session_Namespace($this->_config->app->namespace);
        if ($this->_session->authenticated == true) {
            $session = new OAQ_Session($this->_session, $this->_appconfig);
            $session->actualizar();
            $session->actualizarSesion();
        } else {
            $this->getResponse()->setRedirect($this->_appconfig->getParam('link-logout'));
        }
        $this->_arch = NULL ? $this->_arch = new Zend_Session_Namespace('') : $this->_arch = new Zend_Session_Namespace('Navigation');
        $this->_arch->setExpirationSeconds($this->_appconfig->getParam('session-exp'));
    }

    public function referenceFilesAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(false);
        $this->view->headLink()
                ->appendStylesheet('/less/traffic-module.css');
        $this->view->headScript()
                ->appendFile("/js/common/jquery-1.9.1.min.js");
        $gets = $this->_request->getParams();
        $model = new Archivo_Model_RepositorioMapper();
        $files = $model->getFilesByReferenceUsers($gets["referencia"], $gets["patente"], $gets["aduana"]);
        $this->view->files = $files;
    }

    /**
     * http://www.johnboyproductions.com/php-upload-progress-bar/
     */
    public function uploadFilesAction() {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $request = $this->getRequest();
            if ($request->isPost()) {
                $filters = array(
                    "*" => array("StringTrim", "StripTags"),
                    "patente" => "Digits",
                    "aduana" => "Digits",
                    "pedimento" => "Digits",
                    "referencia" => "StringToUpper",
                    "rfc_cliente" => "StringToUpper",
                );
                $validators = array(
                    "aduana" => array("Alnum", array("stringLength", array("min" => 3, "max" => 3))),
                    "patente" => array("Alnum", array("stringLength", array("min" => 4, "max" => 4))),
                    "pedimento" => array("Alnum", array("stringLength", array("min" => 7, "max" => 7))),
                    "referencia" => array(new Zend_Validate_Regex("/^[-_a-zA-Z0-9.]+$/"), "presence" => "required"),
                    "rfc_cliente" => array(new Zend_Validate_Regex("/^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/")),
                );
                $input = new Zend_Filter_Input($filters, $validators, $request->getPost());
                if (!$input->isValid()) {
                    $this->_helper->json(array("success" => false, "errors" => "Invalid input!"));
                }
            }
            $misc = new OAQ_Misc();
            $misc->set_baseDir($this->_appconfig->getParam("expdest"));
            $model = new Archivo_Model_RepositorioMapper();
            $upload = new Zend_File_Transfer_Adapter_Http();
            $upload->addValidator("Count", false, array("min" => 1, "max" => 15))
                    ->addValidator("Size", false, array("min" => "1", "max" => "20MB"))
                    ->addValidator("Extension", false, array("extension" => "pdf,xml,xls,xlsx,doc,docx,zip,bmp,tif,jpg", "case" => false));
            if (($path = $misc->nuevoDirectorioExpediente($input->patente, $input->aduana, $this->_trim($input->referencia)))) {
                $upload->setDestination($path);
            }
            $files = $upload->getFileInfo();
            foreach ($files as $fieldname => $fileinfo) {
                if (($upload->isUploaded($fieldname)) && ($upload->isValid($fieldname))) {
                    $tipoArchivo = $misc->tipoArchivo(basename($fileinfo["name"]));
                    
                    if ($tipoArchivo == 99) {
                        unlink($fileinfo["name"]);
                        continue;
                    }
                    
                    $filename = $misc->formatFilename($fileinfo["name"], false);
                    $verificar = $model->verificarArchivo($input->patente, $this->_trim($input->referencia), $filename);
                    if ($verificar == false) {
                        $upload->receive($fieldname);
                        if (($this->_renombrarArchivo($path, $fileinfo["name"], $filename))) {
                            if (file_exists($path . DIRECTORY_SEPARATOR . $filename)) {
                                $model->nuevoArchivo($tipoArchivo, null, $input->patente, $input->aduana, $input->pedimento, $this->_trim($input->referencia), $filename, $path . DIRECTORY_SEPARATOR . $filename, $this->_session->username, $input->rfc_cliente);
                            }
                        }
                    } else {
                        $errors[] = array(
                            "filename" => $fileinfo["name"],
                            "errors" => array("errors" => "El archivo ya existe."),
                        );
                    }
                } else {
                    $error = $upload->getErrors();
                    $errors[] = array(
                        "filename" => $fileinfo["name"],
                        "errors" => $error,
                    );
                }
            }
            if (isset($errors)) {
                $this->_helper->json(array("success" => false, "errors" => $errors));
            } else {
                $this->_helper->json(array("success" => true));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    protected function _trim($value) {
        return preg_replace('/\s+/', '', strtoupper(trim($value)));
    }

    public function removeFileAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $model = new Archivo_Model_RepositorioMapper();
            $info = $model->getFileInfo($data["id"]);
            $model->removeFileById($data["id"]);
            if (file_exists($info["ubicacion"])) {
                unlink($info["ubicacion"]);
            } else {
                $this->_helper->json(array("success" => false, "message" => "El archivo no existe."));
            }
        }
    }

    protected function _tipoArchivo($basename) {
        switch (true) {
            case preg_match('/^PED_/i', $basename):
                return 1;
            case preg_match('/^SOL_/i', $basename):
                return 31;
            case preg_match('/^PF_/i', $basename):
                return 1;
            case preg_match('/^PS_/i', $basename):
                return 33;
            case preg_match('/^FO_/i', $basename):
                return 34;
            case preg_match('/^CO_/i', $basename):
                return 35;
            case preg_match('/^MV_/i', $basename):
                return 10;
            case preg_match('/^HC_/i', $basename):
                return 11;
            case preg_match('/^RRNS_/i', $basename):
                return 36;
            case preg_match('/^OD_/i', $basename):
                return 37;
            case preg_match('/^CV_/i', $basename):
                return 22;
            case preg_match('/^ED_/i', $basename):
                return 27;
            case preg_match('/^CI_/i', $basename):
                return 4;
            case preg_match('/^EC_/i', $basename):
                return 17;
            case preg_match('/^PL_/i', $basename):
                return 38;
            case preg_match('/^BL_/i', $basename):
                return 12;
            case preg_match('/^FT_/i', $basename):
                return 40;
            case preg_match('/^FC_/i', $basename):
                return 29;
            case preg_match('/^NOM_/i', $basename):
                return 18;
            default:
                return 99;
        }
    }

    protected function _crearDirectorio($patente, $aduana, $referencia) {
        $folder = '/home/samba-share/expedientes' . DIRECTORY_SEPARATOR . $patente;
        if (!file_exists('/home/samba-share/expedientes' . DIRECTORY_SEPARATOR . $patente)) {
            mkdir('/home/samba-share/expedientes' . DIRECTORY_SEPARATOR . $patente);
        }
        if (!file_exists('/home/samba-share/expedientes' . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana)) {
            mkdir('/home/samba-share/expedientes' . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana);
        }
        if (!file_exists('/home/samba-share/expedientes' . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana . DIRECTORY_SEPARATOR . $referencia)) {
            mkdir('/home/samba-share/expedientes' . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana . DIRECTORY_SEPARATOR . $referencia);
        }
        $folder = '/home/samba-share/expedientes' . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana . DIRECTORY_SEPARATOR . $referencia;
        if (file_exists($folder)) {
            return $folder;
        } else {
            return false;
        }
    }

    protected function _renombrarArchivo($path, $sourceFile, $newFile) {
        if (!rename($path . DIRECTORY_SEPARATOR . $sourceFile, $path . DIRECTORY_SEPARATOR . $newFile)) {
            return false;
        }
        return true;
    }

    public function descargaCarpetaAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $gets = $this->_request->getParams();
        $model = new Archivo_Model_RepositorioMapper();
        $files = $model->getFilesByReferenceUsers($gets["referencia"], $gets["patente"], $gets["aduana"]);
        $zipName = $gets["patente"] . '_' . $gets["referencia"] . '.zip';
        if (!file_exists('/tmp/zips')) {
            mkdir('/tmp/zips', 0777, true);
        }
        $zipFilename = '/tmp/zips' . DIRECTORY_SEPARATOR . $zipName;
        if (file_exists($zipFilename)) {
            unlink($zipFilename);
        }
        if (!empty($files)) {
            $zip = new ZipArchive();
            if ($zip->open($zipFilename, ZIPARCHIVE::CREATE) !== TRUE) {
                return null;
            }
            foreach ($files as $file) {
                if (file_exists($file["ubicacion"])) {
                    $tmpfile = '/tmp/zips' . DIRECTORY_SEPARATOR . sha1($file["ubicacion"]);
                    copy($file["ubicacion"], $tmpfile);
                    if (($zip->addFile($tmpfile, basename($file["ubicacion"]))) === true) {
                        $added[] = $tmpfile;
                    }
                    unset($tmpfile);
                }
            }
            if (($zip->close()) === TRUE) {
                $closed = true;
            }
        }
        if ($closed === true) {
            foreach ($added as $tmp) {
                unlink($tmp);
            }
        }
        if (file_exists($zipFilename)) {
            if (!is_file($zipFilename)) {
                header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
                echo 'File not found';
            } else if (!is_readable($zipFilename)) {
                header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
                echo 'File not readable';
            }
            header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
            header("Content-Type: application/zip");
            header("Content-Transfer-Encoding: Binary");
            header("Content-Length: " . filesize($zipFilename));
            header("Content-Disposition: attachment; filename=\"" . basename($zipFilename) . "\"");
            readfile($zipFilename);
            unlink($zipFilename);
            return false;
        }
    }

    public function analizarFacturaAction() {
        $sat = new OAQ_SATValidar();
        $repo = new Archivo_Model_RepositorioMapper();
        $id = $this->getRequest()->getParam('id', null);
        $type = $this->getRequest()->getParam('type', null);
        if ($type == 29) {
            $file = $repo->obtenerInfo($id);
            $filename = $repo->getFilePathById($id);
            $basename = basename($filename);
            if (preg_match('/.xml$/i', $basename)) {
                $xmlArray = $sat->satToArray(html_entity_decode(file_get_contents($filename)));
                $emisor = $sat->obtenerGenerales($xmlArray["Emisor"]);
                $receptor = $sat->obtenerGenerales($xmlArray["Receptor"]);
                $complemento = $sat->obtenerComplemento($xmlArray["Complemento"]);
                $data = array(
                    'emisor_rfc' => $emisor["rfc"],
                    'emisor_nombre' => $emisor["razonSocial"],
                    'receptor_rfc' => $receptor["rfc"],
                    'receptor_nombre' => $receptor["razonSocial"],
                    'folio' => $xmlArray["@attributes"]["folio"],
                    'fecha' => date('Y-m-d H:i:s', strtotime($xmlArray["@attributes"]["fecha"])),
                    'uuid' => $complemento["uuid"],
                );
                unset($xmlArray);
                $updated = $repo->actualizarFactura($id, $data);
                if ($updated) {
                    if (($idd = $repo->searchFileByName($file["patente"], $file["aduana"], pathinfo($basename, PATHINFO_FILENAME) . '.pdf'))) {
                        $repo->actualizarFactura($idd, $data);
                    }
                }
            }
        }
    }

    public function actualizarReferenciaAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    '*' => array('StringTrim', 'StripTags'),
                    'patente' => 'Digits',
                    'aduana' => 'Digits',
                    'pedimento' => 'Digits',
                    'rfc' => 'StringToUpper',
                    'referencia' => 'StringToUpper',
                );
                $v = array(
                    '*' => 'NotEmpty',
                    'patente' => array('Alnum', array('stringLength', array('min' => 4, 'max' => 4))),
                    'pedimento' => array('Alnum', array('stringLength', array('min' => 7, 'max' => 7))),
                    'referencia' => array('Alnum', array('stringLength', array('min' => 5, 'max' => 25))),
                    'rfc' => array('Alnum', array('stringLength', array('min' => 8, 'max' => 25))),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid()) {
                    $tbl = new Archivo_Model_RepositorioMapper();
                    $updated = $tbl->actualizarDatosReferencia($input->patente, $input->referencia, $input->rfc, $input->pedimento, $this->_session->username);
                    if ($updated === true) {
                        $this->_helper->json(array("success" => true, "message" => "Se actualizo"));
                    }
                    $this->_helper->json(array("success" => true, "message" => "No hubo cambios."));
                } else {
                    
                }
                $this->_helper->json(array("success" => false, "message" => "Datos insuficientes."));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function checklistAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idTrafico" => "Digits",
                "patente" => "Digits",
                "aduana" => "Digits",
                "pedimento" => "Digits",
                "referencia" => "StringToUpper",
            );
            $v = array(
                "*" => "NotEmpty",
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                "patente" => array("NotEmpty", new Zend_Validate_Int()),
                "aduana" => array("NotEmpty", new Zend_Validate_Int()),
                "pedimento" => array("NotEmpty", new Zend_Validate_Int()),
                "referencia" => array("NotEmpty", new Zend_Validate_Regex("/[-a-zA-Z0-9\d]/")),
            );
            $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($i->isValid()) {
                $row = new Archivo_Model_Table_ChecklistReferencias();
                $table = new Archivo_Model_ChecklistReferencias();
                $row->setPatente($i->patente);
                $row->setAduana($i->aduana);
                $row->setReferencia($i->referencia);
                $row->setPedimento($i->pedimento);
                $table->find($row);
                $view = new Zend_View();
                $model = new Trafico_Model_TraficoAduanasMapper();
                $idAduana = $model->idAduana($i->patente, $i->aduana);
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/data/");
                $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");
                if (null !== ($row->getId())) {                    
                    $view->data = json_decode($row->getChecklist());
                    $view->observaciones = $row->getObservaciones();
                    $view->completo = $row->getCompleto();
                    $view->revOp = $row->getRevisionOperaciones();
                    $view->revAdm = $row->getRevisionAdministracion();
                }
                if (isset($idAduana)) {
                    $checklist = new OAQ_Checklist();
                    if($row->getCreado()) {
                        $view->preguntas = $checklist->obtenerChecklist($this->_session->role, $row->getCreado());
                    } else {
                        $view->preguntas = $checklist->obtenerChecklist($this->_session->role, date("Y-m-d"));                        
                    }
                }
                if ($i->isValid("idTrafico")) {
                    $view->idTrafico = $i->idTrafico;
                }
                $view->aduana = $i->aduana;
                $view->patente = $i->patente;
                $view->pedimento = $i->pedimento;
                $view->referencia = $i->referencia;
                $r = new Archivo_Model_ChecklistRoles();
                if($r->rolChecklist($this->_session->role) == "admin") {
                    $view->admin = true;
                } elseif($r->rolChecklist($this->_session->role) == "operacion") {
                    $view->operacion = true;
                } elseif($r->rolChecklist($this->_session->role) == "administracion") {
                    $view->administracion = true;                    
                }
                $repo = new Archivo_Model_RepositorioMapper();
                $tipos = $repo->obtenerTiposArchivosReferencia($i->referencia);
                if(isset($tipos)) {
                    $view->tipos = $tipos;
                }
                echo $view->render("checklist.phtml");
            } else {
                throw new Exception("Invalid input!" . Zend_Debug::dump($i->getErrors(), true));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function descargaArchivoAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
            );
            $input = new Zend_Filter_Input($f, null, $this->_request->getParams());
            if ($input->isValid("filename")) {
                $filename = "/tmp" . DIRECTORY_SEPARATOR . $input->filename . ".zip";
                if (file_exists($filename)) {
                    header("Cache-Control: public");
                    header("Content-Description: File Transfer");
                    header("Content-Disposition: attachment; filename=" . pathinfo($filename, PATHINFO_BASENAME) . "");
                    header("Content-length: " . filesize($filename));
                    header("Content-Transfer-Encoding: binary");
                    header("Content-Type: binary/octet-stream");
                    readfile($filename);
                    unlink($filename);
                } else {
                    throw new Exception("File doesn't exists!");
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function layoutExpedientesAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
            );
            $v = array(
                "layout" => array(new Zend_Validate_Int()),
                "rfc" => array(new Zend_Validate_Regex("/^[-_a-zA-Z0-9.]+$/")),
                "fechaInicio" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
                "fechaFin" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("rfc") && $input->isValid("layout") && $input->isValid("fechaInicio") && $input->isValid("fechaFin")) {
                $mapper = new Archivo_Model_RepositorioMapper();
                $arr = $mapper->referenciasCliente($input->fechaInicio, $input->fechaFin, $input->rfc);
                $view = new Zend_View();
                $view->headLink()->appendStylesheet("/less/traffic-module.css?" . time());
                $view->setScriptPath(realpath(dirname(__FILE__)) . '/../views/scripts/data/');
                $view->setHelperPath(realpath(dirname(__FILE__)) . '/../views/helpers/');
                $view->arr = $arr;
                echo $view->render("layout-expedientes.phtml");
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function descargaArchivoValidacionAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int())
            );
            $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($i->isValid("id")) {
                $mapper = new Automatizacion_Model_ArchivosValidacionMapper();
                $file = $mapper->fileContent($i->id);
                if (isset($file) && !empty($file)) {
                    header("Content-Type: text/plain");
                    header('Content-Disposition: attachment; filename="' . $file["archivoNombre"] . '"');
                    echo base64_decode($file["contenido"]);
                }
                return;
            } else {
                throw new Exception("Invalid Input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function imprimirChecklistAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "patente" => "Digits",
                "aduana" => "Digits",
                "referencia" => "StringToUpper",
                "download" => "StringToLower",
            );
            $v = array(
                "patente" => array(new Zend_Validate_Int()),
                "aduana" => array(new Zend_Validate_Int()),
                "referencia" => "NotEmpty",
                "download" => array("NotEmpty", new Zend_Validate_InArray(array(true, false)))
            );
            $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($i->isValid("patente") && $i->isValid("aduana") && $i->isValid("referencia") && $i->isValid("download")) {
                $aduanas = new Trafico_Model_TraficoAduanasMapper();
                $clientes = new Trafico_Model_ClientesMapper();
                $repo = new Archivo_Model_RepositorioMapper();
                $info = $repo->obtenerInformacionReferencia($i->patente, $i->referencia);
                if (isset($info)) {
                    $cliente = $clientes->buscarRfc($info["rfc_cliente"]);
                    $aduana = $aduanas->infoAduana($i->patente, $i->aduana);
                }
                $row = new Archivo_Model_Table_ChecklistReferencias();
                $table = new Archivo_Model_ChecklistReferencias();
                $row->setPatente($i->patente);
                $row->setAduana($i->aduana);
                $row->setReferencia($i->referencia);
                $data = array(
                    "referencia" => $i->referencia,
                    "pedimento" => $info["pedimento"],
                    "empresa" => "ORGANIZACIÓN ADUANAL DE QUERÉTARO S.C.",
                    "oficina" => "[{$i->patente}-{$i->aduana}] " . strtoupper($aduana["nombre"]),
                    "nombreCliente" => $cliente["nombre"],
                );
                $table->find($row);
                $mapper = new Archivo_Model_Checklist();
                $data["revision"] = json_decode($row->getRevision(), true);
                $data["observaciones"] = $row->getObservaciones();
                if (null !== ($row->getId())) {
                    $data["checklist"] = json_decode($row->getChecklist());
                } else {
                    $data["checklist"] = null;
                }
                $checklist = new OAQ_Checklist();
                $rows = $checklist->obtenerChecklist(null, $row->getCreado());
                $data["preguntas"] = $rows;
                $print = new OAQ_PrintChecklist($data, "P", "pt", "LETTER");
                if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN") {
                    $diretory = "d:/Tmp/php_archivos";
                } else {
                    $diretory = "/tmp/archivos";
                }
                if (!file_exists($diretory)) {
                    mkdir($diretory, 0777);
                }
                $print->set_dir($diretory);
                $print->set_filename("CHECKLIST_" . $i->referencia . ".pdf");
                $print->printChecklist();
                if (filter_var($i->download, FILTER_VALIDATE_BOOLEAN) == false) {
                    $print->Output($print->get_filename(), "I");
                } else {
                    $print->Output($print->get_filename(), "F");
                }
            } else {
                throw new Exception("Invalid Input!");
            }
        } catch (Zend_Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

}
