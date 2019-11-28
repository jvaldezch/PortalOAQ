<?php

class Administracion_AjaxController extends Zend_Controller_Action {

    protected $_session;
    protected $_appconfig;
    protected $_config;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $contextSwitch = $this->_helper->getHelper("contextSwitch");
        $contextSwitch->addActionContext("ingresos-corresponsal", "json")
                ->initContext();
    }

    public function preDispatch() {
        $this->_session = NULL ? $this->_session = new Zend_Session_Namespace("") : $this->_session = new Zend_Session_Namespace($this->_config->app->namespace);
        if ($this->_session->authenticated == true) {
            $session = new OAQ_Session($this->_session, $this->_appconfig);
            $session->actualizar();
            $session->actualizarSesion();
        } else {
            $this->_session = false;
        }
    }

    public function ingresosCorresponsalAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "year" => array("Digits"),
                    "corresponsal" => array("Digits"),
                );
                $v = array(
                    "year" => array("NotEmpty", new Zend_Validate_Int()),
                    "corresponsal" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("corresponsal") && $input->isValid("year")) {
                    $view = new Zend_View();
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/ajax/");
                    $view->setHelperPath(realpath(dirname(__FILE__)) . "/../helpers/");
                    $table = new Administracion_Model_Table_CorresponsalesCuentas();
                    $table->setId($input->corresponsal);
                    $mapper = new Administracion_Model_CorresponsalesCuentas();
                    $mapper->find($table);
                    if (null !== ($table->getId())) {
                        $sica = new OAQ_Sica();
                        $array = $sica->ingresosCorresponsal($table->getIngresos(), $table->getCostos(), $input->year, "s");
                        $view->array = $array;
                        $view->nombre = $table->getNombre();
                        $in = $sica->ingresos($table->getIngresos(), $input->year);
                        $co = $sica->egresos($table->getCostos(), $input->year);
                        if (isset($in) && !empty($in)) {
                            $view->ingresos = $in;
                        }
                        if (isset($co) && !empty($co)) {
                            $view->egresos = $co;
                        }
                        $this->_helper->json(array("success" => true, "html" => $view->render("ingresos-corresponsal.phtml"), "ingresos" => $in["valores"], "egresos" => $co["valores"]));
                    }
                    $this->_helper->json(array("success" => false));
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type");
            }
            $this->_helper->json(array("success" => false));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function actualizarSolicitudAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idSolicitud" => array("Digits"),
                    "esquema" => array("Digits"),
                    "proceso" => array("Digits"),
                );
                $v = array(
                    "idSolicitud" => array("NotEmpty", new Zend_Validate_Int()),
                    "esquema" => array("NotEmpty", new Zend_Validate_Int()),
                    "proceso" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idSolicitud") && $input->isValid("esquema") && $input->isValid("proceso")) {
                    
                    $solicitud = new OAQ_SolicitudesAnticipo($input->idSolicitud);
                    
                    $solicitud->set_username($this->_session->username);
                    $solicitud->set_usernameId($this->_session->id);
                    $solicitud->set_esquema($input->esquema);
                    $solicitud->set_process($input->proceso);
                    
                    if ($input->proceso == 2 && $solicitud->get_process() !== 3) {
                        $solicitud->enviarTramite($input->esquema);
                        $this->_helper->json(array("success" => true));
                    }
                    if ($input->proceso == 3 && $solicitud->get_process() !== 3) {
                        if(($solicitud->enviarDeposito($input->esquema))) {
                            $this->_helper->json(array("success" => true));
                        } else {
                            throw new Exception("Unable to update!");
                        }
                    }
                    
                    if ($input->proceso == 5 && $solicitud->get_process() !== 3) {
                        // hsbc
                        $solicitud->autorizarBanamex($input->idSolicitud, $input->esquema);
                        $this->_helper->json(array("success" => false, "message" => "Solicitud autorizada por HSBC."));  
                    }
                    
                    if ($input->proceso == 6 && $solicitud->get_process() !== 3) {
                        // banamex
                        $solicitud->autorizarHsbc($input->idSolicitud, $input->esquema);
                        $this->_helper->json(array("success" => false, "message" => "Solicitud autorizada por Banamex."));  
                    }
                    $this->_helper->json(array("success" => false, "No se pudo procesar la solicitud."));
                    
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

    public function archivosSolicitudAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $mapper = new Administracion_Model_RepositorioContaMapper();
                $m = new Trafico_Model_TraficoSolRepoMapper();
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idSolicitud" => "Digits",
                );
                $v = array(
                    "idSolicitud" => array("Alnum", array("stringLength", array("min" => 1, "max" => 9999999))),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if (!$input->isValid("idSolicitud")) {
                    $this->_helper->json(array("success" => false, "errors" => "Invalid input!"));
                }
                $rows = $mapper->archivosSolicitud($input->idSolicitud);
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/ajax/");
                if (!isset($rows)) {
                    $rows = $m->buscarPorSolicitud($input->idSolicitud);
                }
                $view->data = $rows;
                $this->_helper->json(array("success" => true, "html" => $view->render("archivos-solicitud.phtml")));
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function borrarArchivoAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => "Digits",
                    "request" => "StringToLower",
                );
                $v = array(
                    "id" => array("Alnum", array("stringLength", array("min" => 1, "max" => 9999999))),
                    "request" => new Zend_Validate_InArray(array("delete")),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if (!($input->isValid("id") && $input->isValid("request"))) {
                    throw new Exception("Invalid input");
                }
                if ($input->request == "delete") {
                    $mppr = new Administracion_Model_RepositorioContaMapper();
                    if (($mppr->borrar($input->id))) {                        
                        $this->_helper->json(array("success" => true));
                    }                    
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function subirArchivosSolicitudAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $misc = new OAQ_Misc();
                $mppr = new Administracion_Model_RepositorioContaMapper();
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idSolicitud" => "Digits",
                    "patente" => "Digits",
                    "aduana" => "Digits",
                    "pedimento" => "Digits",
                    "referencia" => "StringToUpper",
                );
                $v = array(
                    "idSolicitud" => array("Alnum", array("stringLength", array("min" => 1, "max" => 9999999))),
                    "aduana" => array("Alnum", array("stringLength", array("min" => 3, "max" => 3))),
                    "patente" => array("Alnum", array("stringLength", array("min" => 4, "max" => 4))),
                    "pedimento" => array("Alnum", array("stringLength", array("min" => 7, "max" => 7))),
                    "referencia" => array(new Zend_Validate_Regex("/^[-_a-zA-Z0-9.]+$/"), "presence" => "required"),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if (!$input->isValid("idSolicitud")) {
                    $this->_helper->json(array("success" => false, "errors" => "Invalid input!"));
                }
                $upload = new Zend_File_Transfer_Adapter_Http();
                $upload->addValidator("Count", false, array("min" => 1, "max" => 15))
                        ->addValidator("Size", false, array("min" => "1", "max" => "20MB"))
                        ->addValidator("Extension", false, array("extension" => "pdf,xml,xls,xlsx,doc,docx,zip,bmp,tif,jpg", "case" => false));
                
                if (APPLICATION_ENV == "production") {
                    $directory = "/home/samba-share/expedientes/transferencias";
                } else {
                    $directory = "D:\\xampp\\tmp\\transferencias";
                }
                $fecha = date('Y-m-d');
                
                $path = $directory . DIRECTORY_SEPARATOR . substr($fecha, 0, 4) . DIRECTORY_SEPARATOR . substr($fecha, 5, 2) . DIRECTORY_SEPARATOR . substr($fecha, 8, 2);
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
                $upload->setDestination($path);
                
                if ($input->patente != "" && $input->aduana != "" && $input->pedimento != "" && $input->referencia != "") {
                    $solicitud = new OAQ_SolicitudesAnticipo($input->idSolicitud);
                    $header = $solicitud->get_header();
                    if (!empty($header)) {
                        if (isset($header["rfcCliente"])) {
                            $referencias = new OAQ_Referencias();
                            $referencias->crearRepositorio($input->patente, $input->aduana, $input->referencia, $this->_session->username, $header["rfcCliente"], $input->pedimento);
                        }
                    }
                }

                $files = $upload->getFileInfo();
                
                foreach ($files as $fieldname => $fileinfo) {
                    
                    if (($upload->isUploaded($fieldname)) && ($upload->isValid($fieldname))) {
                        $filename = $misc->formatFilename($fileinfo["name"], false);
                        $upload->receive($fieldname);
                        
                        if (($misc->renombrarArchivo($path, $fileinfo["name"], $filename))) {                            
                            $hash = sha1_file($path . DIRECTORY_SEPARATOR . $filename);
                            $arr = array(
                                "idSolicitud" => $input->idSolicitud,
                                "aduana" => $input->aduana,
                                "patente" => $input->patente,
                                "pedimento" => $input->pedimento,
                                "referencia" => $input->referencia,
                                "nombreArchivo" => $filename,
                                "ubicacion" => $path,
                                "hash" => $hash,
                                "creado" => date("Y/m/d H:i:s"),
                                "usuario" => $this->_session->username,
                            );
                            if (!($mppr->buscarArchivo($filename, $hash))) {
                                if(($mppr->agregar($arr))) {
                                    $this->_helper->json(array("success" => true));
                                }
                            } else {
                                if(($mppr->agregar($arr))) {
                                    $this->_helper->json(array("success" => true));
                                }
                            }
                            $this->_helper->json(array("success" => false));
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
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function cancelarSolicitudAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idSolicitud" => "Digits",
                    "mensaje" => array("StringToUpper"),
                );
                $v = array(
                    "idSolicitud" => array("NotEmpty", array("stringLength", array("min" => 1, "max" => 9999999))),
                    "mensaje" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idSolicitud") && $input->isValid("mensaje")) {
                    $solicitud = new OAQ_SolicitudesAnticipo($input->idSolicitud);
                    $solicitud->set_username($this->_session->username);
                    $solicitud->set_usernameId($this->_session->id);
                    $solicitud->set_process(4);

                    $this->_helper->json(array("success" => false, "errors" => "Invalid input!"));
                }
                $this->_helper->json(array("success" => true));
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    /*public function informacionSolicitudesAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                );
                $v = array(
                    "ids" => array("NotEmpty"),
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("ids")) {
                    $m = new Trafico_Model_TraficoSolicitudesMapper();
                    $array = array();
                    foreach ($i->ids as $id) {
                        $d = $m->obtener($id);
                        $array[] = array(
                            "patente" => $d["patente"],
                            "aduana" => $d["aduana"],
                            "referencia" => $d["referencia"],
                            "pedimento" => $d["pedimento"],
                            "total" => $d["subtotal"] - $d["anticipo"],
                        );
                    }
                    $this->_helper->json(array("success" => true, "data" => $array));
                } else {
                    $this->_helper->json(array("success" => false, "message" => "Not valid!"));
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }*/

    public function multiplesDepositosAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                );
                $v = array(
                    "ids" => array("NotEmpty"),
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("ids")) {
                    
                    $misc = new OAQ_Misc();
                    
                    //$mppr = new Administracion_Model_RepositorioContaMapper();
                    
                    $mppr_sol = new Trafico_Model_TraficoSolRepoMapper();
                    $mppr_repo = new Administracion_Model_RepositorioContaMapper();
                    $mppr = new Trafico_Model_TraficoSolicitudesMapper();
                    
                    $upload = new Zend_File_Transfer_Adapter_Http();
                    $upload->addValidator("Count", false, array("min" => 1, "max" => 15))
                            ->addValidator("Size", false, array("min" => "1", "max" => "20MB"))
                            ->addValidator("Extension", false, array("extension" => "pdf,xml,xls,xlsx,doc,docx,zip,bmp,tif,jpg,png", "case" => false));
                    
                    $arr = explode(",", $i->ids);
                    
                    $s = $mppr->obtener($arr[1]);
                    if (($path = $misc->crearDirectorio($s["patente"], $s["aduana"], $s["referencia"]))) {
                        $upload->setDestination($path);
                    }
                    
                    $files = $upload->getFileInfo();
                    foreach ($files as $fieldname => $fileinfo) {
                        
                        if (($upload->isUploaded($fieldname)) && ($upload->isValid($fieldname))) {
                            $filename = $misc->formatFilename($fileinfo["name"], false);
                            $upload->receive($fieldname);
                            if (($misc->renombrarArchivo($path, $fileinfo["name"], $filename))) {                                
                                $row = array(
                                    "idSolicitud" => $s["id"],
                                    "idTrafico" => $s["idTrafico"],
                                    "idCliente" => $s["idCliente"],
                                    "patente" => $s["patente"],
                                    "aduana" => $s["aduana"],
                                    "pedimento" => $s["pedimento"],
                                    "referencia" => $s["referencia"],
                                    "nombreArchivo" => $filename,
                                    "ubicacion" => $path,
                                    "hash" => sha1_file($path . DIRECTORY_SEPARATOR . $filename),
                                    "creado" => date("Y/m/d H:i:s"),
                                    "usuario" => $this->_session->username,
                                );
                                
                                if (($mppr_repo->agregar($row))) {
                                    foreach ($arr as $id) {
                                        $sol = new OAQ_SolicitudesAnticipo($id);
                                        $sol->set_username($this->_session->username);
                                        /*$sol->set_username($this->_session->username);
                                        $sol->set_usernameId($this->_session->id);
                                        $sol->set_process(3);
                                        $sol->enviarDepositoMultiple();*/
                                        $array = array(
                                            "autorizada" => 3,
                                            "deposito" => 1,
                                            "depositado" => date("Y-m-d H:i:s"),
                                        );
                                        $sol->enviarDepositoMultiple($array);
                                        unset($sol);
                                    }
                                }
                                $this->_helper->json(array("success" => true));
                            }
                        } else {
                            $this->_helper->json(array("success" => false, "message" => "Not file uploaded!"));
                        }
                    }
                    $this->_helper->json(array("success" => true));
                } else {
                    $this->_helper->json(array("success" => false, "message" => "Invalid input!"));
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function obtenerMunicipiosAction() {
        try {
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                );
                $v = array(
                    "id" => array("NotEmpty"),
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("id")) {
                    $mapper = new Application_Model_InegiMunicipios();
                    $arr = $mapper->obtenerTodos($i->id);
                    $this->_helper->json(array("success" => true, "json" => $arr));
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function obtenerLocalidadesAction() {
        try {
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                );
                $v = array(
                    "id" => array("NotEmpty"),
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("id")) {
                    $mapper = new Application_Model_InegiLocalidades();
                    $arr = $mapper->obtenerTodos($i->id);
                    $this->_helper->json(array("success" => true, "json" => $arr));
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function datosLocalidadAction() {
        try {
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                );
                $v = array(
                    "id" => array("NotEmpty"),
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("id")) {
                    $mapper = new Application_Model_InegiLocalidades();
                    $arr = $mapper->datos($i->id);
                    $this->_helper->json(array("success" => true, "json" => $arr));
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function obtenerDistanciaAction() {
        try {
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                );
                $v = array(
                    "origen" => array("NotEmpty"),
                    "destino" => array("NotEmpty"),
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("origen") && $i->isValid("destino")) {
                    $o = $i->origen;
                    $d = $i->destino;
                    $key = "AIzaSyD0tBtLiyXoblArAav01Uunm2Ti8wgs0Js";
                    $fullurl = "https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins={$o}&destinations={$d}&key=" . $key;
                    $string = file_get_contents($fullurl); // get json content
                    $json = json_decode($string, true);
                    $this->_helper->json(array("success" => true, "distance" => $json["rows"][0]["elements"][0]["distance"], "time" => $json["rows"][0]["elements"][0]["duration"]));
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function actualizarFacturaAction() {
        try {
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                    "idTrafico" => array("Digits"),
                    "pagada" => array("Digits"),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                    "pagada" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("id") && $i->isValid("idTrafico")) {
                    
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
