<?php

class Operaciones_PostController extends Zend_Controller_Action {

    protected $_session;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
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
    }

    public function subirArchivoAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idDirectorio" => "Digits",
                    "patente" => "Digits",
                    "aduana" => "Digits",
                );
                $v = array(
                    "idDirectorio" => array("NotEmpty", new Zend_Validate_Int()),
                    "patente" => array("NotEmpty", new Zend_Validate_Int()),
                    "aduana" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idDirectorio") && $input->isValid("patente") && $input->isValid("aduana")) {
                    $upload = new Zend_File_Transfer_Adapter_Http();
                    $upload->addValidator("Count", false, array("min" => 1, "max" => 50))
                            ->addValidator("Size", false, array("min" => "1", "max" => "30MB"));
                    $mppr = new Application_Model_DirectoriosValidacion();
                    $arr = $mppr->get($input->patente, $input->aduana);
                    if (!empty($arr)) {
                        $upload->setDestination($arr["directorio"]);
                        if (file_exists($arr["directorio"])) {
                            $files = $upload->getFileInfo();
                            foreach ($files as $fieldname => $fileinfo) {
                                if (($upload->isUploaded($fieldname)) && ($upload->isValid($fieldname))) {
                                    $upload->receive($fieldname);
                                }
                            }
                            $this->_helper->json(array("success" => true));
                        } else {
                            throw new Exception("Directory does not exists!");
                        }
                    } else {
                        throw new Exception("No directory.");
                    }
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
//                        $plantilla->set_solicitante($this->_svucem->solicitante);
//                        $plantilla->set_tipoFigura($this->_svucem->tipoFigura);
//                        $plantilla->set_patente($this->_svucem->patente);
//                        $plantilla->set_aduana($this->_svucem->aduana);
//                        $plantilla->set_usuario($this->_session->username);
//                        if ($plantilla->analizar() == true) {
//                            $this->_helper->json(array("success" => true));
//                        }
                    }
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
        
    public function subirFacturasAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idCliente" => "Digits",
                );
                $v = array(
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idCliente")) {
                    $upload = new Zend_File_Transfer_Adapter_Http();
                    $upload->addValidator("Count", false, array("min" => 1, "max" => 1))
                            ->addValidator("Size", false, array("min" => "1", "max" => "20MB"))
                            ->addValidator("Extension", false, array("extension" => "xls,xlsx", "case" => false));
                    
                    if (APPLICATION_ENV ==  "production") {
                        $dest_directory = $this->_appconfig->getParam("tmpDir");
                    } else {
                        $dest_directory = "D:\\xampp\\tmp\\layout";
                    }
                    $upload->setDestination($dest_directory);

                    $files = $upload->getFileInfo();
                    foreach ($files as $fieldname => $fileinfo) {
                        
                        if (($upload->isUploaded($fieldname)) && ($upload->isValid($fieldname))) {
                            $ext = strtolower(pathinfo($fileinfo['name'], PATHINFO_EXTENSION));
                            $sha = sha1_file($fileinfo['tmp_name']);
                            $filename = $sha . '.' . $ext;
                            $upload->addFilter('Rename', $filename, $fieldname);
                            $upload->receive($fieldname);
                        }    
                        
                        if (file_exists($dest_directory . DIRECTORY_SEPARATOR . $filename)) {
                            
                            $plantilla = new OAQ_Archivos_PlantillaBos($dest_directory . DIRECTORY_SEPARATOR . $filename);
                            
                            if (($plantilla->analizar($input->idCliente))) {
                                $this->_helper->json(array("success" => true));
                            } else {
                                throw new Exception("Something happened while processing!");                                
                            }
                            
                        } else {
                            throw new Exception("File not uploaded [" . $dest_directory . DIRECTORY_SEPARATOR . $filename . "]");
                        }
                    }
                    $this->_helper->json(array("success" => false));
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
    
    public function borrarCartaInstruccionAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => "Digits",
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id")) {                    
                    $this->_helper->json(array("success" => true));
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

    public function agregarDeliveryAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => "Digits",
                    "deliveryNumber" => "StringToUpper",
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "deliveryNumber" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id") && $input->isValid("deliveryNumber")) {

                    $mppr = new Operaciones_Model_CartaInstruccionesPartes();
                    $parts = new Operaciones_Model_CartaPartes();

                    if (($arr = $mppr->buscar($input->deliveryNumber))) {

                        $rows = $mppr->obtener($input->deliveryNumber);
                        if (!empty($rows)) {
                            foreach ($rows as $item) {
                                if (!($parts->verificar($input->id, $item["id"]))) {
                                    $row = array(
                                        "idCarta" => $input->id,
                                        "idDelivery" => $item["id"],
                                        "deliveryNumber" => $input->deliveryNumber,
                                        "creado" => date("Y-m-d H:i:s"),
                                    );
                                    $parts->agregar($row);
                                }
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
        } catch (Exception $e) {
            $this->_helper->json(array("success" => false, "message" => $e->getMessage()));
        }
    }

    public function guardarCartaInstruccionesAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => "Digits",
                    "sello" => "StringToUpper",
                    "dirigida" => "StringToUpper",
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "sello" => array("NotEmpty"),
                    "dirigida" => array("NotEmpty"),
                    "fecha" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id")) {
                    $mppr = new Operaciones_Model_CartaInstrucciones();
                    $arr = array(
                        "sello" => $input->sello,
                        "dirigida" => $input->dirigida,
                        "fecha" => $input->fecha,
                        "modificado" => date("Y-m-d H:i:s"),
                        "modificadoPor" => $this->_session->username,
                    );
                    if (($mppr->actualizar($input->id, $arr))) {
                        $this->_helper->json(array("success" => true));
                    }
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

    public function nuevaCartaInstruccionesAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idCliente" => "Digits",
                    "numCarta" => "StringToUpper",
                    "sello" => "StringToUpper",
                    "dirigida" => "StringToUpper",
                    "tipoOperacion" => "StringToUpper",
                );
                $v = array(
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                    "numCarta" => array("NotEmpty"),
                    "sello" => array("NotEmpty"),
                    "dirigida" => array("NotEmpty"),
                    "fecha" => array("NotEmpty"),
                    "tipoOperacion" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idCliente")) {
                    $mppr = new Operaciones_Model_CartaInstrucciones();
                    $arr = array(
                        "idCliente" => $input->idCliente,
                        "numCarta" => $input->numCarta,
                        "sello" => $input->sello,
                        "dirigida" => $input->dirigida,
                        "fecha" => $input->fecha,
                        "tipoOperacion" => $input->tipoOperacion,
                        "creado" => date("Y-m-d H:i:s"),
                        "creadoPor" => $this->_session->username,
                    );
                    if (!($mppr->verificar($input->idCliente, $input->numCarta))) {
                        if (($mppr->agregar($arr))) {
                            $this->_helper->json(array("success" => true));
                        }
                    } else {
                        throw new Exception("El número de carta ya existe.");
                    }
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
    
    public function agregarRetornableAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idCarta" => "Digits",
                );
                $v = array(
                    "idCarta" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idCarta")) {
                    $this->_helper->json(array("success" => true));
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

}
