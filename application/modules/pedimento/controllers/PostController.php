<?php

class Pedimento_PostController extends Zend_Controller_Action
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
        try {
            $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        } catch (Zend_Config_Exception $e) {
        }
        try {
            $this->_firephp = Zend_Registry::get("firephp");
        } catch (Zend_Exception $e) {
        }
    }

    public function preDispatch()
    {
        try {
            $this->_session = NULL ? $this->_session = new Zend_Session_Namespace("") :
                $this->_session = new Zend_Session_Namespace($this->_config->app->namespace);
        } catch (Zend_Session_Exception $e) {
        }
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

    public function actualizarAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idPedimento" => array("Digits"),
                );
                $v = array(
                    "idPedimento" => array("NotEmpty", new Zend_Validate_Int()),
                    "name" => array("NotEmpty"),
                    "value" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idPedimento")) {
                    $pedimento = new OAQ_TraficoPedimento(array("id" => $input->idPedimento));
                    $arr = array(
                        "$input->name" => $input->value,
                        "actualizado" => date("Y-m-d H:i:s")
                    );
                    if (($pedimento->actualizar($arr))) {
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
