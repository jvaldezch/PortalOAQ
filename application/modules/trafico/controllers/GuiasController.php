<?php

class Trafico_GuiasController extends Zend_Controller_Action {

    protected $_session;
    protected $_config;
    protected $_appconfig;
    protected $_firephp;

    public function init() {
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->view->headLink(array("rel" => "icon shortcut", "href" => "/favicon.png"));
        $this->view->headLink()
            ->appendStylesheet("/js/common/bootstrap/css/bootstrap.min.css")
            ->appendStylesheet("/less/traffic-module.css?" . time())
            ->appendStylesheet("/css/fontawesome/css/fontawesome-all.min.css");
        $this->view->headScript()
            ->appendFile("/js/common/jquery-1.9.1.min.js")
            ->appendFile("/js/common/bootstrap/js/bootstrap.min.js")
            ->appendFile("/js/common/jquery.form.min.js")
            ->appendFile("/js/common/jquery.validate.min.js")
            ->appendFile("/js/common/js.cookie.js")
            ->appendFile("/js/common/jquery.blockUI.js")
            ->appendFile("/js/common/principal.js?" . time());
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $this->_firephp = Zend_Registry::get("firephp");
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
        $this->view->myHelpers = new Application_View_Helper_MyHelpers();
    }

    public function indexAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Guías";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
            ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
            ->appendStylesheet("/easyui/themes/default/easyui.css")
            ->appendStylesheet("/easyui/themes/icon.css")
            ->appendStylesheet("/css/mobile-style.css?" . time());
        $this->view->headScript()
            ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
            ->appendFile("/easyui/jquery.easyui.min.js")
            ->appendFile("/easyui/jquery.edatagrid.js")
            ->appendFile("/easyui/datagrid-filter.js")
            ->appendFile("/easyui/locale/easyui-lang-es.js")
            ->appendFile("/fullcalendar/lib/moment.min.js")
            ->appendFile("/js/trafico/guias/index.js?" . time());

        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "page" => array("Digits"),
            "size" => array("Digits"),
            "search" => array("StringToUpper"),
        );
        $v = array(
            "page" => array(new Zend_Validate_Int(), "default" => 1),
            "size" => array(new Zend_Validate_Int(), "default" => 20),
            "search" => "NotEmpty",
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());

        $mppr = new Trafico_Model_TraficoGuiasMapper();

        if ($input->isValid("search")) {

            $search = preg_replace('/\s+/', '', $input->search);

            $result = $mppr->buscar($search);

            if (isset($result) && !empty($result)) {
                $this->view->params = http_build_query($input->getEscaped());
                $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array($result));
                $paginator->setItemCountPerPage($input->size);
                $paginator->setCurrentPageNumber($input->page);
                $this->view->paginator = $paginator;
            }

            $this->view->search = $search;
        } else {
            $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($mppr->obtenerTodas()));
            $paginator->setItemCountPerPage($input->size);
            $paginator->setCurrentPageNumber($input->page);
            $this->view->paginator = $paginator;
        }
    }

}
