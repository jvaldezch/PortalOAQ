<?php

class Vucem_DodaController extends Zend_Controller_Action {

    protected $_session;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;

    public function init() {
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->view->headLink(array("rel" => "icon shortcut", "href" => "/favicon.png"));
        $this->view->headLink()
                ->appendStylesheet("/js/common/bootstrap/css/bootstrap.min.css")
                ->appendStylesheet("/js/common/bootstrap/datepicker/css/datepicker.css")
                ->appendStylesheet("/css/DT_bootstrap.css")
                ->appendStylesheet("/less/traffic-module.css?" . time());
        $this->view->headScript()
                ->appendFile("http://code.jquery.com/jquery-1.6.4.js")
                ->appendFile("/js/common/bootstrap/js/bootstrap.min.js")
                ->appendFile("/js/common/bootstrap/datepicker/js/bootstrap-datepicker.js")
                ->appendFile("/js/common/jquery.form.min.js")
                ->appendFile("/js/common/jquery.validate.min.js")
                ->appendFile("/js/common/jquery.dataTables.min.js")
                ->appendFile("/js/common/js.cookie.js")
                ->appendFile("/js/common/jquery.blockUI.js")
                ->appendFile("/js/common/DT_bootstrap.js");
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
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
        $news = new Application_Model_NoticiasInternas();
        $this->view->noticias = $news->obtenerTodos();
    }
    
    public function indexAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " DODA QR";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/js/vucem/doda/index.js?" . time());
    }

}
