<?php

class V2_UsuariosController extends Zend_Controller_Action {

    protected $_session;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;

    public function init() {
        $this->_helper->layout->setLayout("new-system");
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->view->headLink(array("rel" => "icon shortcut", "href" => "/favicon.png"));
         $this->view->headLink()
                ->appendStylesheet("/v2/css/system.css");
         $this->view->headScript()
                ->appendFile("/v2/js/common/jquery-1.9.1.min.js")
                ->appendFile("/v2/js/common/jquery.form.min.js")
                ->appendFile("/v2/js/common/default.js?" . time());
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
    }

    public function preDispatch() {
        $this->_session = NULL ? $this->_session = new Zend_Session_Namespace("") : $this->_session = new Zend_Session_Namespace($this->_config->app->namespace);
        if ($this->_session->authenticated == true) {
            $this->_session->setExpirationSeconds($this->_appconfig->getParam("session-exp"));
        } else {
            $this->getResponse()->setRedirect($this->_appconfig->getParam("link-logout"));
        }
        $mapper = new Application_Model_MenusMapper();
        $this->view->menu = $mapper->obtenerMenuUsuario($this->_session->role);
        $this->view->username = $this->_session->username;
    }

    public function inicioAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " " . " Usuarios";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript();
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "page" => "Digits",
            "size" => "Digits",
        );
        $v = array(
            "page" => array(new Zend_Validate_Int(), "default" => 1),
            "size" => array(new Zend_Validate_Int(), "default" => 20),
        );
        $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        $model = new V2_Model_Usuarios();
        $rows = $model->fetchAll();
        $pag = new Zend_Paginator(new Zend_Paginator_Adapter_Array($rows));
        $pag->setItemCountPerPage($i->size);
        $pag->setCurrentPageNumber($i->page);
        $this->view->paginator = $pag;
    }

}
