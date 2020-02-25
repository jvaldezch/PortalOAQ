<?php

class Dashboard_PostController extends Zend_Controller_Action {

    protected $_session;
    protected $_config;
    protected $_appconfig;
    protected $_firephp;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_firephp = Zend_Registry::get("firephp");
    }

    public function preDispatch() {
        
    }

    public function subirArchivosAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                    "tipo_documento" => array("Digits"),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "tipo_documento" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if (!$input->isValid('id') || !$input->isValid('tipo_documento')) {
                    throw new Exception("Invalid input!");
                }
                $upload = new Zend_File_Transfer_Adapter_Http();
                $upload->addValidator("Count", false, array("min" => 1, "max" => 15))
                        ->addValidator("Size", false, array("min" => "1", "max" => "20MB"))
                        ->addValidator("Extension", false, array("extension" => "pdf,xml,xls,xlsx,doc,docx,zip,bmp,tif,jpg,jpeg", "case" => false));
                
                $misc = new OAQ_Misc();                
                $trafico = new OAQ_Trafico(array("idTrafico" => $input->id));
                $mppr = new Archivo_Model_RepositorioMapper();
                
                if (APPLICATION_ENV == "production") {
                    $misc->set_baseDir($this->_appconfig->getParam("expdest"));
                } else {
                    $misc->set_baseDir("D:\\wamp64\\tmp\\dropzone");
                }
                if (($path = $misc->directorioExpedienteDigital($trafico->getPatente(), $trafico->getAduana(), $trafico->getReferencia()))) {
                    $upload->setDestination($path);
                }
                $upload->setDestination($path);
                $files = $upload->getFileInfo();
                
                switch ($input->tipo_documento) {
                    case 18:
                        $prefix = 'NOM_';
                        break;
                    case 34:
                        $prefix = 'FACT_';
                        break;
                    case 38:
                        $prefix = 'PL_';
                        break;
                    case 55:
                        $prefix = '317_';
                        break;
                    case 60:
                        $prefix = 'BL_';
                        break;
                    case 61:
                        $prefix = 'REV_';
                        break;
                    default:
                        $prefix = '';
                        break;
                }
                foreach ($files as $fieldname => $fileinfo) {
                    if (($upload->isUploaded($fieldname)) && ($upload->isValid($fieldname))) {
                        $filename = $misc->formatFilename($prefix . $fileinfo["name"], false);
                        $upload->receive($fieldname);
                        if (($misc->renombrarArchivo($path, $fileinfo["name"], $filename))) {
                            $verificar = $mppr->verificarArchivo($trafico->getPatente(), $trafico->getAduana(), $trafico->getReferencia(), $filename);
                            if ($verificar == false) {
                                $mppr->nuevoArchivo($input->tipo_documento, null, $trafico->getPatente(), $trafico->getAduana(), $trafico->getPedimento(), $trafico->getReferencia(), $filename, $path . DIRECTORY_SEPARATOR . $filename, 'Ansell', $trafico->getRfcCliente());
                            }
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
    
    public function loginAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "ulogin" => array("StringToLower"),
                );
                $v = array(
                    "ulogin" => array("NotEmpty"),
                    "plogin" => array("NotEmpty"),
                    "code" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("ulogin") && $input->isValid("plogin") && $input->isValid("code")) {  
                    
                    $auth_model = new OAQ_Auth();
                    $auth = $auth_model->challengeCredentials($input->ulogin, $input->plogin);
                    if ($auth["auth"] === true) {

                        if ($auth['rol'] !== 'cliente') {
                            throw new Exception("RFC invalid!");
                        }
                        
                        $mapper = new Dashboard_Model_ClientesDbs();
                        $arr = $mapper->buscarIdentificador($input->code);
                        
                        Zend_Session::regenerateId();
                        $this->_session = new Zend_Session_Namespace("OAQDashboard");
                        if ($this->_session->isLocked()) {
                            $this->_session->unLock();
                            $this->_session->setExpirationSeconds(3600);
                        }
                        $this->_session->code = $input->code;
                        $this->_session->rfcCliente = $arr["rfc"];
                        $this->_session->nomCliente = $arr["nombre"];
                        $this->_session->lock();
                        
                        $this->_helper->json(array("success" => true));


                    } else {
                        if (isset($auth["username"])) {
                            $this->_helper->json(array("success" => false, "username" => $auth["username"]));
                        }
                        if (isset($auth["password"])) {
                            $this->_helper->json(array("success" => false, "password" => $auth["password"]));
                        }
                    }

                    /*if ($input->ulogin == 'ansell' && $input->plogin == 'ansell') {
                        
                        $mapper = new Dashboard_Model_ClientesDbs();
                        $arr = $mapper->buscarIdentificador($input->code);
                        
                        Zend_Session::regenerateId();
                        $this->_session = new Zend_Session_Namespace("OAQDashboard");
                        if ($this->_session->isLocked()) {
                            $this->_session->unLock();
                            $this->_session->setExpirationSeconds(3600);
                        }
                        $this->_session->code = $input->code;
                        $this->_session->rfcCliente = $arr["rfc"];
                        $this->_session->nomCliente = $arr["nombre"];
                        $this->_session->lock();
                        
                        $this->_helper->json(array("success" => true));
                    } else {
                        throw new Exception("Usuario o contraseña no válidos.");
                    }*/
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Error Processing Request", 1);
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function agregarComentarioAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idTrafico" => array("Digits"),
                    "message" => array("StringToUpper"),
                );
                $v = array(
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                    "message" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("idTrafico")) {
                    $traficos = new OAQ_Trafico(array("idTrafico" => $input->idTrafico, "idUsuario" => 999));
                    
                    if (($traficos->agregarComentario(utf8_decode($input->message)))) {
                        $this->_helper->json(array("success" => true));                        
                    } else {
                        $this->_helper->json(array("success" => false));
                    }
                    
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Error Processing Request", 1);
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

}
