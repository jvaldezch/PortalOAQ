<?php

class Manifestacion_PostController extends Zend_Controller_Action
{
    protected $_session;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;
    protected $_firephp;

    public function init()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $this->_logger = Zend_Registry::get("logDb");
        $this->_firephp = Zend_Registry::get("firephp");
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

    public function actualizarAction()
    {
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
                    "id" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty())
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("id")) {
                    $this->_helper->json(array("success" => true));
                } else {
                    throw new Exception("Invalid input.");
                }
            } else {
                throw new Exception("Invalid request type.");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function nuevaAction()
    {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idAduana" => "Digits",
                    "idCliente" => "Digits",
                    "tipoOperacion" => "StringToUpper",
                    "cvePedimento" => "StringToUpper",
                    "referencia" => "StringToUpper",
                    "pedimento" => "Digits",
                );
                $v = array(
                    "idAduana" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty()),
                    "idCliente" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty()),
                    "tipoOperacion" => new Zend_Validate_NotEmpty(),
                    "cvePedimento" => new Zend_Validate_NotEmpty(),
                    "referencia" => new Zend_Validate_NotEmpty(),
                    "pedimento" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty()),
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("idAduana") && $i->isValid("idCliente")) {
                    $man = new Manifestacion_Trafico();
                    $id =  $man->nueva($i->idAduana, $i->idCliente, $i->tipoOperacion, $i->cvePedimento, $i->pedimento, $i->referencia);
                    if ($id) {
                        $this->_helper->json(array("success" => true, "id" => $id));
                    } else {
                        throw new Exception("No pudo agregarse.");
                    }
                } else {
                    throw new Exception("Invalid input.");
                }
            } else {
                throw new Exception("Invalid request type.");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
}
