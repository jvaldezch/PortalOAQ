<?php

class Comercializacion_IndexController extends Zend_Controller_Action {

    protected $_session;
    protected $_soapClient;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;

    public function init() {
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->view->headLink()
                ->appendStylesheet("/js/common/bootstrap/css/bootstrap.min.css")
                ->appendStylesheet("/css/fontawesome/css/fontawesome-all.min.css")
                ->appendStylesheet("/less/traffic-module.css?" . time());
        $this->view->headLink(array("rel" => "icon shortcut", "href" => "/favicon.png"));
        $this->view->headScript()
                ->appendFile("/js/common/jquery-1.9.1.min.js")
                ->appendFile("/js/common/bootstrap/js/bootstrap.min.js")
                ->appendFile("/js/common/jquery.form.min.js")
                ->appendFile("/js/common/jquery.validate.min.js")
                ->appendFile("/js/common/date.js")
                ->appendFile("/js/common/js.cookie.js")
                ->appendFile("/js/common/jquery.blockUI.js")
                ->appendFile("/js/common/principal.js?" . time());
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $context = stream_context_create(array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
                "allow_self_signed" => true
            )
        ));
        $this->_soapClient = new Zend_Soap_Client($this->_config->app->endpoint, array("stream_context" => $context));
    }

    public function preDispatch() {
        $this->_session = NULL ? $this->_session = new Zend_Session_Namespace("") : $this->_session = new Zend_Session_Namespace($this->_config->app->namespace);
        if ($this->_session->authenticated == true) {
            $session = new OAQ_Session($this->_session, $this->_appconfig);
            $session->actualizar();
            $session->actualizarSesion();
        } else {
            $this->getResponse()->setRedirect($this->_appconfig->getParam("link-logout"));
        }
        $mapper = new Application_Model_MenusMapper();
        $this->view->menu = $mapper->obtenerMenuUsuario($this->_session->role);
        $this->view->username = $this->_session->username;
        $this->view->rol = $this->_session->role;
    }

    public function indexAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Comercialización";
        $this->view->headMeta()->appendName("description", "");
        $sica = new OAQ_Sica();
        $this->view->clientes = $sica->getLastCustomers();
    }

    public function clientesAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Clientes";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/js/comercializacion/index/clientes.js?" . time());
        $clientes = new Comercializacion_Model_ClientesMapper();
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "rfc" => array("StringToUpper"),
            "nombre" => array("StringToUpper"),
            "page" => array("Digits"),
        );
        $v = array(
            "rfc" => array("NotEmpty"),
            "nombre" => array("NotEmpty"),
            "page" => array(new Zend_Validate_Int(), "default" => 1),
        );
        $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($i->isValid("rfc") || $i->isValid("nombre")) {
            if ($i->isValid("rfc")) {
                $result = $clientes->customersByRfc($i->rfc);
            } elseif ($i->isValid("nombre")) {
                $result = $clientes->customersByName($i->nombre);
            }
        } else {
            $result = $clientes->getAllCustomers();
        }
        if (isset($result)) {
            $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array($result));
            $paginator->setItemCountPerPage(20);
            $paginator->setCurrentPageNumber($i->page);
            $this->view->data = $paginator;
        }
    }

    public function datosClienteAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Datos del cliente";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/less/traffic-module.css?" . time());
        $this->view->headScript()
                ->appendFile("/js/common/jquery.form.min.js")
                ->appendFile("/js/common/jquery.validate.min.js")
                ->appendFile("/js/comercializacion/index/datos-cliente.js?" . time());
        $nav = NULL ? $nav = new Zend_Session_Namespace("") : $nav = new Zend_Session_Namespace("Navigation");
        $internal = $this->_getParam("internal", null);
        if (isset($internal)) {
            $customers = new Comercializacion_Model_ClientesMapper();
            $request = $this->getRequest();
            if ($request->isPost()) {
                $update = $request->getPost();
                $customers->updateCustomerDataByRfc($internal, $update["webaccess"], $update["password"], $this->_session->username);
            }
            $data = $customers->customersId($internal);
            $form = new Comercializacion_Form_DatosCliente(array("internal" => $internal));
            if (!empty($data)) {
                $form->populate(array(
                    "rfc" => $data["rfc"],
                    "nombre" => $data["nombre"],
                    "webaccess" => $data["access"],
                    "password" => $data["password"],
                    "sicaid" => $data["sica_id"],
                    "sitaid" => $data["sita_id"],
                    "slamid" => $data["slam_id"],
                ));
            }
            $this->view->form = $form;
        }
    }

}
