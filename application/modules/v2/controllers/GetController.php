<?php

class V2_GetController extends Zend_Controller_Action {

    protected $_session;
    protected $_appconfig;
    protected $_config;

    public function init() {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
        }
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $contextSwitch = $this->_helper->getHelper("contextSwitch");
        $contextSwitch->addActionContext("verify", "json")
                ->addActionContext("logout", "json")
                ->initContext();
    }

    public function preDispatch() {
        $this->_session = NULL ? $this->_session = new Zend_Session_Namespace("") : $this->_session = new Zend_Session_Namespace($this->_config->app->namespace);
        if ($this->_session->authenticated == true) {
            $this->_session->setExpirationSeconds($this->_appconfig->getParam("session-exp"));
        } else {
            $this->_session = false;
        }
    }

}
