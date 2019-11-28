<?php

class Archivo_ExpedienteController extends Zend_Controller_Action {

    protected $_session;
    protected $_soapClient;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;
    protected $_arch;

    public function init() {
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->view->headLink(array("rel" => "icon shortcut", "href" => "/favicon.png"));
        $this->view->headLink()
                ->appendStylesheet("/js/common/bootstrap/css/bootstrap.min.css")
                ->appendStylesheet("/js/common/bootstrap/datepicker/css/datepicker.css")
                ->appendStylesheet("/less/traffic-module.css?" . time());
        $this->view->headScript()
                ->appendFile("/js/common/jquery-1.9.1.min.js")
                ->appendFile("/js/common/bootstrap/js/bootstrap.min.js")
                ->appendFile("/js/common/bootstrap/datepicker/js/bootstrap-datepicker.js")
                ->appendFile("/js/common/jquery.form.min.js")
                ->appendFile("/js/common/jquery.validate.min.js")
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
            $session->actualizarSesion(Zend_Controller_Front::getInstance()->getRequest()->getRequestUri());
        } else {
            $this->getResponse()->setRedirect($this->_appconfig->getParam("link-logout"));
        }
        $mapper = new Application_Model_MenuAccesos();
        $this->view->menu = $mapper->obtenerPorRol($this->_session->idRol);
        $this->view->username = $this->_session->username;
        $this->view->rol = $this->_session->role;
        $this->_arch = NULL ? $this->_arch = new Zend_Session_Namespace("") : $this->_arch = new Zend_Session_Namespace("Navigation");
        $this->_arch->setExpirationSeconds($this->_appconfig->getParam("session-exp"));
        $news = new Application_Model_NoticiasInternas();
        $this->view->noticias = $news->obtenerTodos();
    }

    public function indexAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Expediente Digital";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/easyui/themes/metro/easyui.css")
                ->appendStylesheet("/easyui/themes/icon.css")
                ->appendStylesheet("/easyui/themes/color.css")
                ->appendStylesheet("/js/common/contentxmenu/jquery.contextMenu.min.css");
        $this->view->headScript()
                ->appendFile("/easyui/jquery.easyui.min.js")
                ->appendFile("/easyui/jquery.edatagrid.js")
                ->appendFile("/easyui/datagrid-filter.js")
                ->appendFile("/easyui/locale/easyui-lang-es.js")
                ->appendFile("/js/archivo/expediente/index.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits"),
        );
        $v = array(
            "id" => array("NotEmpty", new Zend_Validate_Int()),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id")) {
            $this->view->id = $input->id;
        }
    }

    public function archivosDeValidacionAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Expediente Digital";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/easyui/themes/metro/easyui.css")
                ->appendStylesheet("/easyui/themes/icon.css")
                ->appendStylesheet("/easyui/themes/color.css")
                ->appendStylesheet("/js/common/contentxmenu/jquery.contextMenu.min.css");
        $this->view->headScript()
                ->appendFile("/easyui/jquery.easyui.min.js")
                ->appendFile("/easyui/jquery.edatagrid.js")
                ->appendFile("/easyui/datagrid-filter.js")
                ->appendFile("/easyui/locale/easyui-lang-es.js")
                ->appendFile("/js/archivo/expediente/archivos-de-validacion.js?" . time());
    }

    public function obtenerArchivosAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
                "tipoArchivo" => array("Digits"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
                "tipoArchivo" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $request->getPost());
            if ($input->isValid("id") && !$input->isValid("tipoArchivo")) {
                $arr = array(
                    array("id" => 1, "parentId" => 0, "name" => "Pedimentos", "tipoArchivo" => null, "usuario" => null, "creado" => null, "state" => "closed"),
                    array("id" => 2, "parentId" => 0, "name" => "Facturas Originales", "tipoArchivo" => null, "usuario" => null, "creado" => null, "state" => "closed"),
                    array("id" => 3, "parentId" => 0, "name" => "COVES", "tipoArchivo" => null, "usuario" => null, "creado" => null, "state" => "closed"),
                    array("id" => 4, "parentId" => 0, "name" => "Edocuments", "tipoArchivo" => null, "usuario" => null, "creado" => null, "state" => "closed"),
                    array("id" => 5, "parentId" => 0, "name" => "Cuentas de Gastos", "tipoArchivo" => null, "usuario" => null, "creado" => null, "state" => "closed"),
                    array("id" => 6, "parentId" => 0, "name" => "HC y MV", "tipoArchivo" => null, "usuario" => null, "creado" => null, "state" => "closed"),
                    array("id" => 7, "parentId" => 0, "name" => "Facturas de terceros", "tipoArchivo" => null, "usuario" => null, "creado" => null, "state" => "closed"),
                    array("id" => 99, "parentId" => 0, "name" => "No identificados", "tipoArchivo" => null, "usuario" => null, "creado" => null, "state" => "closed"),
                );
                $this->_helper->json($arr);
            } elseif ($input->isValid("id") && $input->isValid("tipoArchivo")) {
                $referencias = new OAQ_Referencias();
                $res = $referencias->restricciones($this->_session->id, $this->_session->role);
                $index = new Archivo_Model_RepositorioIndex();
                $arr = $index->datos($input->id, $res["idsAduana"], $res["rfcs"]);
                if (!empty($arr)) {
                    $mppr = new Archivo_Model_RepositorioMapper();
                    if ($input->tipoArchivo == 1) {
                        $tiposDeArchivo = array(33, 23);
                    }
                    if ($input->tipoArchivo == 2) {
                        $tiposDeArchivo = array(34);
                    }
                    if ($input->tipoArchivo == 3) {
                        $tiposDeArchivo = array(21, 22);
                    }
                    if ($input->tipoArchivo == 4) {
                        $tiposDeArchivo = array(27, 56);
                    }
                    if ($input->tipoArchivo == 5) {
                        
                    }
                    if ($input->tipoArchivo == 6) {
                        $tiposDeArchivo = array(10, 11);
                    }
                    if ($input->tipoArchivo == 7) {
                        $tiposDeArchivo = array(40);
                    }
                    if ($input->tipoArchivo == 99) {
                        $tiposDeArchivo = array(99);
                    }
                    if (!empty($tiposDeArchivo)) {
                        $files = $mppr->obtenerTiposDeArchivos($arr["referencia"], $arr["patente"], $arr["aduana"], $tiposDeArchivo);
                        $array = array();
                        foreach ($files as $item) {
                            $array[] = array(
                                "id" => $item["id"],
                                "parentId" => $input->tipoArchivo,
                                "name" => $item["nom_archivo"],
                                "nombreArchivo" => $item["nombre"], // enviado para render de tipo archivo
                                "tipoArchivo" => $item["tipo_archivo"],
                                "subTipoArchivo" => $item["sub_tipo_archivo"],
                                "usuario" => $item["usuario"],
                                "creado" => $item["creado"],
                                "editor" => '<a onclick="javascript:borrar(' . $item["id"] . ');"><img src="/images/icons/basura.png" /></a>',
                                "state" => "open",
                            );
                        }
                        $this->_helper->json($array);
                    } else {
                        $this->_helper->json(array());
                    }
                }
            }
        }
    }

    public function tiposDeArchivosAction() {
        $mppr = new Archivo_Model_DocumentosMapper();
        $this->_helper->json($mppr->getAll());
    }

    public function terminalAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Terminal";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/easyui/themes/metro/easyui.css")
                ->appendStylesheet("/easyui/themes/icon.css")
                ->appendStylesheet("/easyui/themes/color.css")
                ->appendStylesheet("/js/common/highlight/styles/github.css");
        $this->view->headScript()
                ->appendFile("/easyui/jquery.easyui.min.js")
                ->appendFile("/easyui/jquery.edatagrid.js")
                ->appendFile("/easyui/datagrid-filter.js")
                ->appendFile("/easyui/locale/easyui-lang-es.js")
                ->appendFile("/js/common/highlight/highlight.pack.js")
                ->appendFile("/js/common/pdfobject.min.js")
                ->appendFile("/js/archivo/expediente/terminal.js?" . time());
    }

}
