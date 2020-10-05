<?php

class Vucem_CatalogoController extends Zend_Controller_Action {

    protected $_session;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;
    protected $_logger;

    public function init() {
        $this->_helper->layout->setLayout("bootstrap-vucem");
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->view->headLink()->appendStylesheet("/css/general/stylesheet.css")
                ->appendStylesheet("/bootstrap/css/bootstrap.min.css")
                ->appendStylesheet("/bootstrap/datepicker/css/datepicker.css")
                ->appendStylesheet($this->_appconfig->getParam("main-css"))
                ->appendStylesheet($this->_appconfig->getParam("bootstrap-css"))
                ->appendStylesheet("/css/DT_bootstrap.css");
        $this->view->headLink(array("rel" => "icon shortcut", "href" => "/favicon.png"));
        $this->view->headScript()->appendFile("/js/jquery-1.9.1.min.js")
                ->appendFile("/bootstrap/js/bootstrap.min.js")
                ->appendFile("/bootstrap/datepicker/js/bootstrap-datepicker.js")
                ->appendFile("/js/jquery.form.js")
                ->appendFile("/js/principal.js")
                ->appendFile("/js/jquery.dataTables.min.js")
                ->appendFile("/js/DT_bootstrap.js")
                ->appendFile("/js/common/mensajero.js?" . time())
                ->appendFile("/js/jquery.blockUI.js");
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $this->_logger = Zend_Registry::get("logDb");
    }

    public function preDispatch() {
        $this->_session = NULL ? $this->_session = new Zend_Session_Namespace("") : $this->_session = new Zend_Session_Namespace($this->_config->app->namespace);
        if ($this->_session->authenticated == true) {
            $session = new OAQ_Session($this->_session, $this->_appconfig);
            $session->actualizar();
            $session->actualizarSesion(Zend_Controller_Front::getInstance()->getRequest()->getRequestUri());
        } else {
            $this->getResponse()->setRedirect($this->_appconfig->getParam("link-logout"));
        }
        $mapper = new Application_Model_MenuAccesos();
        $this->view->menu = $mapper->obtenerPorRol($this->_session->idRol);
        $this->view->username = $this->_session->username;
        $this->view->rol = $this->_session->role;
    }

}
