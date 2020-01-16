<?php

class Pedimento_GetController extends Zend_Controller_Action {

    protected $_session;
    protected $_config;
    protected $_appconfig;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        try {
            $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        } catch (Zend_Config_Exception $e) {
        }
        $this->_logger = Zend_Registry::get("logDb");
    }

    public function preDispatch() {
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
    }

}
