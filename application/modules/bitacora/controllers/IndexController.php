<?php

class Bitacora_IndexController extends Zend_Controller_Action {

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
                ->appendStylesheet("/css/fontawesome/css/fontawesome-all.min.css")
                ->appendStylesheet("/less/traffic-module.css?" . time());
        $this->view->headScript()
                ->appendFile("/js/common/jquery-1.9.1.min.js")
                ->appendFile("/js/common/bootstrap/js/bootstrap.min.js")
                ->appendFile("/js/common/jquery.form.min.js")
                ->appendFile("/js/common/jquery.validate.min.js")
                ->appendFile("/js/common/js.cookie.js")
                ->appendFile("/js/common/jquery.blockUI.js")
                ->appendFile("/js/common/mensajero.js?" . time())
                ->appendFile("/js/common/principal.js?" . time());
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
        $this->view->myHelpers = new Application_View_Helper_MyHelpers();
    }

    public function indexAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Bitacora";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/easyui/themes/metro/easyui.css")
                ->appendStylesheet("/easyui/themes/icon.css")
                ->appendStylesheet("/easyui/themes/color.css")
                ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
                ->appendStylesheet("/js/common/contentxmenu/jquery.contextMenu.min.css");
        $this->view->headScript()
                ->appendFile("/easyui/jquery.easyui.min.js")
                ->appendFile("/easyui/jquery.edatagrid.js")
                ->appendFile("/easyui/datagrid-filter.js")
                ->appendFile("/easyui/locale/easyui-lang-es.js")
                ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
                ->appendFile("/js/bitacora/index/index.js?" . time());
//        $session = new OAQ_Session($this->_session, $this->_appconfig);
//        if (($res = $session->revisarUri("/bitacora/index/index"))) {
//            if ($res == $this->_session->username) {
//                
//            } else {
//                echo '<script> $.alert({title: "Advertencia", type: "orange", content: "No es posible editar por el momento la bitacora ya el usuario <strong>' . strtoupper($res) . '</strong> está haciendo uso de la misma.", boxWidth: "250px", useBootstrap: false, buttons: { ok: function () { window.history.go(-1); } } });</script> ';
//                $this->view->headScript()->appendFile("/js/bitacora/index/index-bloquear.js?" . time());
//            }
//        }
    }
    
    public function tramitadoresAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Tramitadores";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/easyui/themes/metro/easyui.css")
                ->appendStylesheet("/easyui/themes/icon.css")
                ->appendStylesheet("/easyui/themes/color.css")
                ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
                ->appendStylesheet("/js/common/contentxmenu/jquery.contextMenu.min.css");
        $this->view->headScript()
                ->appendFile("/easyui/jquery.easyui.min.js")
                ->appendFile("/easyui/jquery.edatagrid.js")
                ->appendFile("/easyui/datagrid-filter.js")
                ->appendFile("/easyui/locale/easyui-lang-es.js")
                ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
                ->appendFile("/js/bitacora/index/tramitadores.js?" . time());
    }

}
