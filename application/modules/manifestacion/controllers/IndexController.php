<?php

class Manifestacion_IndexController extends Zend_Controller_Action
{

    protected $_session;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;

    public function init()
    {
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->view->headLink(array("rel" => "icon shortcut", "href" => "/favicon.png"));
        $this->view->headLink()
            ->appendStylesheet("/js/common/bootstrap/css/bootstrap.min.css")
            ->appendStylesheet("/less/traffic-module.css?" . time())
            ->appendStylesheet("/css/fontawesome/css/fontawesome-all.min.css")
            ->appendStylesheet("/css/jquery.timepicker.css")
            ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
            ->appendStylesheet("/easyui/themes/default/easyui.css")
            ->appendStylesheet("/easyui/themes/icon.css")
            ->appendStylesheet("/js/common/bootstrap/bootstrap-datepicker/css/datepicker.css");
        $this->view->headScript()
            ->appendFile("/js/common/jquery-1.9.1.min.js")
            ->appendFile("/js/common/bootstrap/js/bootstrap.min.js")
            ->appendFile("/js/common/jquery.form.min.js")
            ->appendFile("/js/common/jquery.validate.min.js")
            ->appendFile("/js/common/js.cookie.js")
            ->appendFile("/js/common/jquery.blockUI.js")
            ->appendFile("/js/common/jquery.number.min.js")
            ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
            ->appendFile("/easyui/jquery.easyui.min.js")
            ->appendFile("/easyui/jquery.edatagrid.js")
            ->appendFile("/easyui/datagrid-filter.js")
            ->appendFile("/easyui/locale/easyui-lang-es.js")
            ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js")
            ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js")
            ->appendFile("/js/common/jquery.timepicker.min.js")
            ->appendFile("/js/common/loadingoverlay.min.js")
            ->appendFile("/js/common/principal.js?" . time());
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $this->_soapClient = new Zend_Soap_Client($this->_config->app->endpoint);
        $contextSwitch = $this->_helper->getHelper("contextSwitch");
        $contextSwitch->addActionContext("json-customers-by-name", "xml")
            ->addActionContext("comments", array("xml", "json"))
            ->initContext();
    }

    public function preDispatch()
    {
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

        $this->_todosClientes = array("trafico", "super", "trafico_ejecutivo", "gerente");
    }

    public function indexAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " M.V.";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
            ->appendFile("/js/manifestacion/index/index.js?" . time());
    }

    public function nuevaAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Nueva M.V.";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
            ->appendFile("/js/manifestacion/index/nueva.js?" . time());

        $ctms = new Trafico_Model_TraficoUsuAduanasMapper();
        $cts = new Trafico_Model_ClientesMapper();

        if (in_array($this->_session->role, $this->_todosClientes)) {
            $customs = $ctms->aduanasDeUsuario();
            $customers = $cts->obtenerTodos();
        } else {
            $customs = $ctms->aduanasDeUsuario($this->_session->id);
            $customers = null;
        }
        if (!empty($customs)) {
            $form = new Manifestacion_Form_NuevaManifestacion(array("aduanas" => $customs, "clientes" => $customers));
        } else {
            $form = new Manifestacion_Form_NuevaManifestacion();
        }
        $this->view->form = $form;
    }

    public function editarAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Editar M.V.";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
            ->appendFile("/js/manifestacion/index/editar.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits"),
        );
        $v = array(
            "id" => array("NotEmpty", new Zend_Validate_Int()),
        );
        $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($i->isValid("id")) {
            $man = new Manifestacion_Trafico();
            $row = $man->datos($i->id);
            $this->view->row = $row;
        } else {
            $this->getResponse()->setRedirect("/manifestacion/index/index");
        }
    }
}
