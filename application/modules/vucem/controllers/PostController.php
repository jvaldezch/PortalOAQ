<?php

class Vucem_PostController extends Zend_Controller_Action {

    protected $_session;
    protected $_svucem;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;
    protected $_logger;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $this->_logger = Zend_Registry::get("logDb");
        $contextSwitch = $this->_helper->getHelper("contextSwitch");
        $contextSwitch->addActionContext("guardar-edocument", "json")
                ->initContext();
    }

    public function preDispatch() {
        $this->_session = NULL ? $this->_session = new Zend_Session_Namespace("") : $this->_session = new Zend_Session_Namespace($this->_config->app->namespace);
        if ($this->_session->authenticated == true) {
            $session = new OAQ_Session($this->_session, $this->_appconfig);
            $session->actualizar();
            $session->actualizarSesion();
        } else {
            $this->getResponse()->setRedirect($this->_appconfig->getParam("link-logout"));
        }
        $this->_svucem = NULL ? $this->_svucem = new Zend_Session_Namespace("") : $this->_svucem = new Zend_Session_Namespace("OAQVucem");
        $this->_svucem->setExpirationSeconds($this->_appconfig->getParam("session-exp"));
    }

    public function guardarEdocumentAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                    "solicitud" => array("Digits"),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "solicitud" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id") && $input->isValid("solicitud")) {
                    $misc = new OAQ_Misc();
                    $sello = new Vucem_Model_VucemFirmanteMapper();
                    $mapper = new Vucem_Model_VucemEdocMapper();
                    $arr = $mapper->obtenerEdocument($input->id, $input->solicitud);
                    $vucemFiles = new OAQ_VucemArchivos(array(
                        "id" => $input->id,
                        "solicitud" => $input->solicitud,
                        "dir" => $misc->nuevoDirectorio($this->_appconfig->getParam("expdest"), $arr["patente"], $arr["aduana"], $arr["referencia"]),
                        "data" => $arr,
                        "sello" => $sello->obtenerDetalleFirmante($arr["rfc"]),
                        "username" => $this->_session->username,
                    ));
                    if($vucemFiles->guardarEdoc()) {
                        $mapper->saved($arr["id"]);
                        $this->_helper->json(array("success" => true, "id" => $arr["id"], "message" => "El EDocument fue guardado exitosamente."));
                    }
                    $this->_helper->json(array("success" => false, "message" => "No se pudo guardar el Edocument."));
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $e) {
            $this->_helper->json(array("success" => false, "message" => $e->getMessage()));
        }
    }
    
    public function revisarParaEnviarAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $this->_svucem = NULL ? $this->_svucem = new Zend_Session_Namespace("") : $this->_svucem = new Zend_Session_Namespace("OAQVucem");
            if (isset($this->_svucem->edfiles)) {
                if (count($this->_svucem->edfiles) > 0) {
                    $client = new GearmanClient();
                    $client->addServer("127.0.0.1", 4730);
                    if(APPLICATION_ENV === "production") {
                        $email = $this->_appconfig->getParam("vucem-email");
                    } else {
                        $email = "soporte@oaq.com.mx";
                    }
                    foreach ($this->_svucem->edfiles as $k => $item) {
                        $file = array(
                            "patente" => $item["patente"],
                            "aduana" => $item["aduana"],
                            "referencia" => $item["referencia"],
                            "pedimento" => $item["pedimento"],
                            "rfc" => $item["rfc"],
                            "firmante" => $item["firmante"],
                            "name" => basename($item["name"]),
                            "filename" => $item["name"],
                            "type" => mime_content_type($item["name"]),
                            "size" => filesize($item["name"]),
                            "tipoArchivo" => $item["tipoArchivo"],
                            "subTipoArchivo" => $item["subTipoArchivo"],
                            "username" => $this->_session->username,
                            "uuid" => $k,
                            "email" => $email,
                            "urlvucem" => $this->_config->app->vucem . "DigitalizarDocumentoService",
                        );
                        $client->addTaskBackground("edoc_enviaredocs", serialize($file));
                    }
                    $client->runTasks();
                    $this->_helper->json(array("success" => true));
                } else {
                    $this->_helper->json(array("success" => false, "message" => "No hay archivos para enviar."));
                }
            } else {
                $this->_helper->json(array("success" => false, "message" => "No ha cargado archivos."));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    /**
     * /vucem/data/background-edoc?solicitud=27098470
     * su - www-data -c 'php /var/www/workers/edocs_worker.php'
     * LOOK FORWARD http://blog.andyburton.co.uk/index.php/tag/gearmanmanager/
     *
     */
    public function backgroundEdocAction() {
        try {
            $username = $this->_request->getParam("username", null);
            $solicitud = $this->_request->getParam("solicitud", null);
            $maxProcess = 1;
            $misc = new OAQ_Misc();
            $gm = new Application_Model_GearmanMapper();
            $workerName = "edocs_worker.php";
            $workerPath = $gm->getProcessPath($workerName);
            if (isset($workerPath)) {
                if (file_exists($workerPath)) {
                    $process = new Archivo_Model_PidMapper();
                    for ($i = 0; $i < $maxProcess; $i++) {
                        if (!($pids = $process->checkRunnigProcess($workerName))) {
                            $newPid = shell_exec(sprintf('%s > /dev/null 2>&1 & echo $!', "php " . $workerPath));
                            $process->addNewProcess($newPid, $workerName, "php " . $workerPath);
                        } else {
                            foreach ($pids as $k => $p) {
                                if (!$misc->isRunning($p['pid'])) {
                                    echo "{$p['pid']} is not runnig.\n";
                                    $process->deleteProcess($p['pid']);
                                    unset($pids[$k]);
                                } else {
                                    echo "{$p['pid']} is runnig.\n";
                                }
                            }
                            if (count($pids) < $maxProcess) {
                                $newPid = shell_exec(sprintf('%s > /dev/null 2>&1 & echo $!', "php " . $workerPath));
                                $process->addNewProcess($newPid, $workerName, "php " . $workerPath);
                            }
                        }
                    }
                } else {
                    return true;
                }
            } else {
                return true;
            }
            $model = new Vucem_Model_VucemEdocMapper();
            if (isset($username)) {
                $solicitudes = $model->obtenerSinRespuestaEdoc($username);
            } else {
                $solicitudes = $model->obtenerSinRespuestaEdoc($this->_session->username);
            }
            if (isset($solicitud)) {
                $solicitudes = $model->obtenerSinRespuestaEdoc(null, null, $solicitud);
            }
            if (!empty($solicitudes)) {
                $client = new GearmanClient();
                $client->addServer("127.0.0.1", 4730);
                foreach ($solicitudes as $item) {
                    $data = array(
                        "id" => $item["id"],
                        "uuid" => $item["uuid"],
                        "solicitud" => $item["solicitud"],
                        "patente" => $item["patente"],
                        "aduana" => $item["aduana"],
                        "referencia" => $item["referencia"],
                        "rfc" => $item["rfc"],
                        "nomArchivo" => $item["nomArchivo"],
                        "urlvucem" => $this->_config->app->vucem . "DigitalizarDocumentoService",
                        "logo" => $this->_appconfig->getParam("tcpdf-logo"),
                        "addr1" => $this->_appconfig->getParam("footer-edoc-addr1"),
                        "addr2" => $this->_appconfig->getParam("footer-edoc-addr2"),
                        "directory" => $this->_appconfig->getParam("expdest"),
                        "username" => $this->_session->username,
                    );
                    $client->addTaskBackground("edoc_revisaredocs", serialize($data));
                }
                $client->runTasks();
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function subirArchivoDigitalizarAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "patente" => "Digits",
                    "aduana" => "Digits",
                    "pedimento" => "Digits",
                    "tipo" => "Digits",
                    "subTipo" => "Digits",
                    "referencia" => "StringToUpper",
                    "firmante" => "StringToUpper",
                    "rfc" => "StringToUpper",
                );
                $v = array(
                    "patente" => array("NotEmpty", new Zend_Validate_Int()),
                    "aduana" => array("NotEmpty", new Zend_Validate_Int()),
                    "pedimento" => array("NotEmpty", new Zend_Validate_Int()),
                    "tipo" => array("NotEmpty", new Zend_Validate_Int()),
                    "subTipo" => array("NotEmpty", new Zend_Validate_Int()),
                    "referencia" => array("Notempty", new Zend_Validate_Regex("/^[-_a-zA-Z0-9.]+$/")),
                    "firmante" => array("NotEmpty", new Zend_Validate_Regex("/^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/")),
                    "rfc" => array("NotEmpty", new Zend_Validate_Regex("/^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/")),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if (!$input->isValid()) {
                    throw new Exception("Invalid input!");
                }
                $this->_svucem = NULL ? $this->_svucem = new Zend_Session_Namespace('') : $this->_svucem = new Zend_Session_Namespace("OAQVucem");
                $adapter = new Zend_File_Transfer_Adapter_Http();
                $adapter->setDestination($this->_svucem->edtmp)
                        ->addValidator("Extension", false, "pdf");
                $this->_svucem->edReferencia = $input->referencia;
                $this->_svucem->edAduana = $input->aduana;
                $this->_svucem->edPedimento = $input->pedimento;
                $this->_svucem->edPatente = $input->patente;
                $this->_svucem->edFirmante = $input->firmante;
                if ($adapter->isValid()) {
                    $misc = new OAQ_Misc();
                    $info = $adapter->getFileInfo();
                    if (!$adapter->receive()) {
                        throw new Exception("Nothing upladed!");
                    }
                    $temp = explode(".", $info["file"]["name"]);
                    rename($this->_svucem->edtmp . DIRECTORY_SEPARATOR . $info["file"]["name"], $this->_svucem->edtmp . DIRECTORY_SEPARATOR . $misc->formatURL($temp[0]) . '.pdf');
                    if (file_exists($this->_svucem->edtmp . DIRECTORY_SEPARATOR . $misc->formatURL($temp[0]) . ".pdf")) {
                        $hash = sha1_file($this->_svucem->edtmp . DIRECTORY_SEPARATOR . $misc->formatURL($temp[0]) . ".pdf");
                        $uuid = $misc->getUuid($hash . microtime());
                        $this->_svucem->edfiles[$uuid] = array(
                            "name" => $this->_svucem->edtmp . DIRECTORY_SEPARATOR . $misc->formatURL($temp[0]) . ".pdf",
                            "firmante" => $input->firmante,
                            "patente" => $input->patente,
                            "aduana" => $input->aduana,
                            "referencia" => $input->referencia,
                            "pedimento" => $input->pedimento,
                            "rfc" => $input->rfc,
                            "tipoArchivo" => $input->tipo,
                            "subTipoArchivo" => $input->subTipo,
                        );
                        $this->_helper->json(array("success" => true));
                    } else {
                        $this->_helper->json(array("success" => false, "message" => "Ocurrio un error al subir el archivo."));
                    }
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function subirArchivoAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "patente" => "Digits",
                    "aduana" => "Digits",
                    "pedimento" => "Digits",
                    "tipo" => "Digits",
                    "subTipo" => "Digits",
                    "referencia" => "StringToUpper",
                    "firmante" => "StringToUpper",
                    "rfc" => "StringToUpper",
                );
                $v = array(
                    "patente" => array("NotEmpty", new Zend_Validate_Int()),
                    "aduana" => array("NotEmpty", new Zend_Validate_Int()),
                    "pedimento" => array("NotEmpty", new Zend_Validate_Int()),
                    "tipo" => array("NotEmpty", new Zend_Validate_Int()),
                    "subTipo" => array("NotEmpty", new Zend_Validate_Int()),
                    "referencia" => array("Notempty", new Zend_Validate_Regex("/^[-_a-zA-Z0-9.]+$/")),
                    "firmante" => array("NotEmpty", new Zend_Validate_Regex("/^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/")),
                    "rfc" => array("NotEmpty", new Zend_Validate_Regex("/^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/")),
                    "directory" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if (!$input->isValid()) {
                    throw new Exception("Invalid input!");
                }
                if (!file_exists($input->directory)) {
                    mkdir($input->directory, 0777, true);
                }
                $adapter = new Zend_File_Transfer_Adapter_Http();
                $adapter->setDestination($input->directory)
                        ->addValidator("Extension", false, "pdf");
                if ($adapter->isValid()) {
                    $process = new OAQ_Archivos_Procesar();
                    $mppr = new Vucem_Model_VucemTmpEdocsMapper();
                    $misc = new OAQ_Misc();
                    $info = $adapter->getFileInfo();
                    if (!$adapter->receive()) {
                        throw new Exception("Nothing upladed!");
                    }
                    $temp = explode(".", $info["file"]["name"]);
                    $filename = $input->directory . DIRECTORY_SEPARATOR . $misc->formatURL($temp[0]) . ".pdf";
                    rename($input->directory . DIRECTORY_SEPARATOR . $info["file"]["name"], $filename);
                    if (file_exists($filename)) {
                        if (!$mppr->verify($input->patente, $input->referencia, basename($filename))) {
                            $id = $mppr->agregar($input->tipo, $input->subTipo, $input->firmante, $input->patente, $input->aduana, $input->pedimento, $input->referencia, $filename, filesize($filename), sha1_file($filename), $input->rfc, $this->_session->username);
                            if ($id) {
                                $res = $process->analizarArchivo($id);
                                if ($res["success"] == false) {
                                    $mppr->update($id, array('error' => 1, 'mensajeError' => $res["message"]));
                                }
                            }
                        }
                        $this->_helper->json(array("success" => true));
                    } else {
                        $this->_helper->json(array("success" => false, "message" => "Ocurrio un error al subir el archivo."));                        
                    }
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function borrarEdocumentAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array("id" => array("StringTrim", "StripTags", "Digits"));
                $v = array("id" => array("NotEmpty", new Zend_Validate_Int()));
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("id")) {
                    $mppr = new Vucem_Model_VucemTmpEdocsMapper();
                    $arr = $mppr->obtenerArchivo($input->id);
                    if (!empty($arr)) {
                        if (file_exists($arr["nomArchivo"])) {
                            unlink($arr["nomArchivo"]);
                        }
                        if ($mppr->eliminar($input->id)) {
                            $this->_helper->json(array("success" => true));
                        } else {
                            throw new Exception("No se pudo borrar.");
                        }
                    }
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function enviarAPedimentoAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => "Digits",
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("id")) {
                    $index = new Vucem_Model_VucemEdocIndex();
                    $model = new Vucem_Model_VucemEdocMapper();
                    $edoc = $model->obtener($input->id);
                    if (isset($edoc) && !empty($edoc)) {
                        $misc = new OAQ_Misc();
                        $sita = $misc->sitawinTrafico($edoc["patente"], $edoc["aduana"]);
                        if (!isset($sita)) {
                            $this->_helper->json(array("success" => false, "message" => "No existe sistema de pedimentos configurado."));
                        }
                        $referencia = $sita->infoPedimentoBasicaReferencia($edoc["referencia"]);
                        if (isset($referencia) && !empty($referencia)) {
                            $verificar = $sita->verificarEdoc($edoc["referencia"], $edoc["edoc"]);
                            if (!$verificar) {
                                $folio = $sita->folioEdoc($edoc["referencia"]);
                                $nuevoFolio = 1;
                                if (is_int($folio)) {
                                    $nuevoFolio = $folio;
                                }
                                if (APPLICATION_ENV === "production") {
                                    if ($sita->actualizarEdocEnPedimento($edoc["referencia"], $nuevoFolio, $edoc["edoc"])) {
                                        $index->enPedimento($input->id);
                                        $this->_helper->json(array("success" => true, "message" => "EDocument actualizado."));
                                    }
                                    $this->_helper->json(array("success" => false, "message" => "No se pudo actualizar."));
                                } else {
                                    $this->_helper->json(array("success" => true, "message" => "Folio {$nuevoFolio}, no es posible actualizar en modo desarrollo."));
                                }
                            } else {
                                $index->enPedimento($input->id);
                                $this->_helper->json(array("success" => true, "message" => "EDocument existe en pedimento."));
                            }
                        } else {
                            $this->_helper->json(array("success" => false, "message" => "La referencia no existe en la BD."));
                        }
                    } else {
                        $this->_helper->json(array("success" => true, "message" => "No edoc."));
                    }
                } else {
                    throw new Exception("Invalid input!");
                }
                $this->_helper->json(array("success" => false));
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function procesarEdocumentAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array("id" => array("StringTrim", "StripTags", "Digits"));
                $v = array("id" => array("NotEmpty", new Zend_Validate_Int()));
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("id")) {
                    $proc = new OAQ_Archivos_Procesar();
                    $mppr = new Vucem_Model_VucemTmpEdocsMapper();
                    if (($arr = $proc->procesarEdocument($input->id))) {
                        if (file_exists($arr["filename"])) {
                            $file = array(
                                "nomArchivo" => $arr["filename"],
                                "size" => filesize($arr["filename"]),
                                "hash" => sha1_file($arr["filename"]),
                                "error" => null,
                                "mensajeError" => null
                            );
                            $mppr->update($input->id, $file);
                            $this->_helper->json(array("success" => true, "id" => $input->id, "result" => $file));
                        }
                    } else {
                        $this->_helper->json(array("success" => false));
                    }
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function enviarEdocumentsAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array("ids" => array("StringTrim", "StripTags", "Digits"));
                $v = array("ids" => array("NotEmpty", new Zend_Validate_Int()));
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("ids")) {
                    $sender = new OAQ_Workers_EdocSender();
                    $mppr = new Vucem_Model_VucemTmpEdocsMapper();
                    foreach ($input->ids as $id) {
                        $sender->edocs($id);
                        $mppr->estatus($id, 1);
                    }
                    $this->_helper->json(array("success" => true));
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function subirPlantillaAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $upload = new Zend_File_Transfer_Adapter_Http();
                $upload->addValidator("Count", false, array("min" => 1, "max" => 1))
                        ->addValidator("Size", false, array("min" => "1", "max" => "20MB"))
                        ->addValidator("Extension", false, array("extension" => "xls,xlsx", "case" => false));
                $upload->setDestination($this->_appconfig->getParam("tmpDir"));
                $files = $upload->getFileInfo();
                foreach ($files as $fieldname => $fileinfo) {
                    if (($upload->isUploaded($fieldname)) && ($upload->isValid($fieldname))) {
                        $ext = strtolower(pathinfo($fileinfo['name'], PATHINFO_EXTENSION));
                        $sha = sha1_file($fileinfo['tmp_name']);
                        $filename = $sha . '.' . $ext;
                        $upload->addFilter('Rename', $filename, $fieldname);
                        $upload->receive($fieldname);
                    }
                    if (file_exists($this->_appconfig->getParam("tmpDir") . DIRECTORY_SEPARATOR . $filename)) {
                        $plantilla = new OAQ_Archivos_PlantillaCoves($this->_appconfig->getParam("tmpDir") . DIRECTORY_SEPARATOR . $filename);
                        $plantilla->set_solicitante($this->_svucem->solicitante);
                        $plantilla->set_tipoFigura($this->_svucem->tipoFigura);
                        $plantilla->set_patente($this->_svucem->patente);
                        $plantilla->set_aduana($this->_svucem->aduana);
                        $plantilla->set_usuario($this->_session->username);
                        if ($plantilla->analizar() == true) {
                            $this->_helper->json(array("success" => true));
                        }
                    }
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function buscarPedimentoAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "sistema" => "StringToLower",
                    "aduana" => "Digits",
                    "pedimento" => "Digits",
                );
                $v = array(
                    "sistema" => "NotEmpty",
                    "aduana" => array("NotEmpty", new Zend_Validate_Int()),
                    "pedimento" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("sistema") && $input->isValid("aduana") && $input->isValid("pedimento")) {
                    if (APPLICATION_ENV == "development" && (int) $input->aduana == 640) {
                        $client = new Zend_Rest_Client('http://localhost:3000', array('timeout' => 30));
                    }
                    if (APPLICATION_ENV == "development" && (int) $input->aduana == 240) {
                        $client = new Zend_Rest_Client('http://localhost:3000', array('timeout' => 30));
                    }
                    if (APPLICATION_ENV == "production" && (int) $input->aduana == 240) {
                        $client = new Zend_Rest_Client('http://162.253.186.242:3001', array('timeout' => 30));
                    }
                    if (APPLICATION_ENV == "production" && (int) $input->aduana == 640) {
                        $client = new Zend_Rest_Client('http://192.168.200.5:3001', array('timeout' => 30));
                    }
                    if (isset($client)) {

                        $options['patente'] = '3589';
                        $options['aduana'] = $input->aduana;
                        $options['pedimento'] = $input->pedimento;
                        $response = $client->restPost("/{$input->sistema}/buscar-pedimento", $options);
                        if ($response->getBody()) {

                            $row = json_decode($response->getBody(), true);

                            if (!empty($row["response"][0])) {

                                $pedimento = $row["response"][0];

                                $response = $client->restPost("/{$input->sistema}/encabezado-facturas", $options);
                                if ($response->getBody()) {
                                    $invoices = json_decode($response->getBody(), true);
                                    if (!empty($invoices["response"])) {
                                        $pedimento["facturas"] = $invoices["response"];
                                    }
                                }
                                $this->_helper->json(array("success" => true, "row" => $pedimento));
                            }

                        }

                    }
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function seleccionarFacturasAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "sistema" => "StringToLower",
                    "aduana" => "Digits",
                    "pedimento" => "Digits",
                );
                $v = array(
                    "facturas" => "NotEmpty",
                    "sistema" => "NotEmpty",
                    "aduana" => array("NotEmpty", new Zend_Validate_Int()),
                    "pedimento" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("sistema") && $input->isValid("aduana") && $input->isValid("pedimento") && $input->isValid("facturas")) {
                    $facturas = explode("|", $input->facturas);
                    if (!empty($facturas)) {

                        foreach ($facturas as $factura) {
                            if ($input->sistema == "casa") {
                                $sis = new Sistemas_Casa();
                                
                            }
                        }


                        $this->_helper->json(array("success" => true));
                    }
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

}
