<?php

class Webservice_AjaxController extends Zend_Controller_Action {

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
            $this->_session->setExpirationSeconds($this->_appconfig->getParam("session-exp"));
        } else {
            $this->getResponse()->setRedirect($this->_appconfig->getParam("link-logout"));
        }
    }

    public function agregarWebServiceAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "rfc" => array("StringToUpper"),
                    "value" => array("Digits"),
                );
                $v = array(
                    "rfc" => array(new Zend_Validate_Regex("/^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/")),
                    "value" => array(new Zend_Validate_Int()),
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("rfc") && $i->isValid("value")) {
                    $row = new Webservice_Model_Table_WsTokens(array("rfc" => $i->rfc));
                    $table = new Webservice_Model_WsTokens();
                    $table->find($row);
                    if (null === ($row->getId())) {
                        $row->setRfc($i->rfc);
                        $row->setActivo(1);
                        $row->setToken(sha1("dss78454" . $i->rfc . "oaq2013*"));
                        $row->setCreado(date("Y-m-d H:i:s"));
                        $table->save($row);
                        $this->_helper->json(array("success" => true));
                    } else {
                        $row->setActivo($i->value);
                        $row->setModificado(date("Y-m-d H:i:s"));
                        $table->save($row);
                        $this->_helper->json(array("success" => true));
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
    
    public function estatusWebServiceAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "rfc" => array("StringToUpper"),
                );
                $v = array(
                    "rfc" => array(new Zend_Validate_Regex("/^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/")),
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("rfc")) {
                    $row = new Webservice_Model_Table_WsTokens(array("rfc" => $i->rfc));
                    $table = new Webservice_Model_WsTokens();
                    $table->find($row);
                    if (null === ($row->getId())) {
                        $this->_helper->json(array("success" => false));
                    } else {
                        $this->_helper->json(array("success" => true, "value" => $row->getActivo()));                        
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
    
    public function estatusActivoClienteAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "rfc" => array("StringToUpper"),
                );
                $v = array(
                    "rfc" => array(new Zend_Validate_Regex("/^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/")),
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("rfc")) {
                    $mapper = new Trafico_Model_ClientesMapper();
                    $arr = $mapper->buscarRfc($i->rfc);
                    if(isset($arr["activo"]) && $arr["activo"] == 1) {
                        $this->_helper->json(array("success" => true));
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
    
    public function activarClienteAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "value" => array("Digits"),
                    "rfc" => array("StringToUpper"),
                );
                $v = array(
                    "value" => new Zend_Validate_Int(),
                    "rfc" => new Zend_Validate_Regex("/^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/"),
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("rfc") && $i->isValid("value")) {
                    $mapper = new Trafico_Model_ClientesMapper();
                    if($mapper->cambiarEstatus($i->value, $i->rfc)) {
                        $this->_helper->json(array("success" => true));                        
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

}
