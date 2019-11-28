<?php

class Mobile_MainController extends Zend_Controller_Action {

    protected $_session;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;

    public function init() {
        $this->_helper->layout->setLayout("mobile/default");
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->view->headScript()
                ->appendFile("/js/common/jquery-1.9.1.min.js")
                ->appendFile("/mobile/bootstrap/js/bootstrap.min.js")
                ->appendFile("/mobile/popper.js/dist/umd/popper.min.js")
                ->appendFile("/js/common/jquery.form.min.js")
                ->appendFile("/js/common/jquery.validate.min.js")
                ->appendFile("/js/common/js.cookie.js")
                ->appendFile("/js/common/jquery.blockUI.js");
        $this->view->headLink()
                ->appendStylesheet("/mobile/bootstrap/css/bootstrap.min.css")
                ->appendStylesheet("/mobile/fontawesome/css/all.css")
                ->appendStylesheet("/mobile/common/styles.css");
        $this->view->headLink(array("rel" => "icon shortcut", "href" => "/favicon.png"));
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
    }

    public function preDispatch() {
        
    }

    public function indexAction() {
        $this->view->title = "Login";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/mobile/common/main/index.js?" . time());
    }

    public function inicioAction() {
        $this->_helper->layout->setLayout("mobile/traficos");
        $this->view->title = "Inicio";
        $this->view->headMeta()->appendName("description", "");
        $this->_session = NULL ? $this->_session = new Zend_Session_Namespace("") : $this->_session = new Zend_Session_Namespace("OAQmobile");
        if ($this->_session->authenticated == true) {
            $session = new OAQ_Session($this->_session, $this->_appconfig);
            $session->actualizar();
            $session->actualizarSesion(Zend_Controller_Front::getInstance()->getRequest()->getRequestUri());
        } else {
            $this->getResponse()->setRedirect("/mobile/main/logout");
        }
    }

    public function logoutAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $session = new Zend_Session_Namespace($this->_config->app->namespace);
        try {
            $sess = new OAQ_Session($session, $this->_appconfig);
            $sess->logout($this->getRequest()->getCookie('portalUsername'));
            $session->unsetAll();
            Zend_Session::destroy(true);
            $this->_helper->redirector->gotoUrl("/mobile/main/index?session=Sesión finalizada");
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }

}
