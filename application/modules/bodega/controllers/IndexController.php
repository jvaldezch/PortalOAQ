<?php

class Bodega_IndexController extends Zend_Controller_Action {

    protected $_session;
    protected $_soapClient;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;
    protected $_rolesEditarTrafico;
    protected $_todosClientes;
    protected $_noTodo;

    public function init() {
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->view->headLink(array("rel" => "icon shortcut", "href" => "/favicon.png"));
        $this->view->headLink()
                ->appendStylesheet("/js/common/bootstrap/css/bootstrap.min.css")
                ->appendStylesheet("/css/fontawesome/css/fontawesome-all.min.css")
                ->appendStylesheet("/less/traffic-module.css?" . time());
        $this->view->headScript()
                ->appendFile("/js/common/jquery-1.12.1.min.js")
                ->appendFile("/js/common/bootstrap/js/bootstrap.min.js")
                ->appendFile("/js/common/jquery.form.min.js")
                ->appendFile("/js/common/jquery.validate.min.js")
                ->appendFile("/js/common/js.cookie.js")
                ->appendFile("/js/common/jquery.blockUI.js")
                ->appendFile("/js/common/mensajero.js?" . time())
                ->appendFile("/js/common/principal.js?" . time());
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $this->_soapClient = new Zend_Soap_Client($this->_config->app->endpoint);
        $contextSwitch = $this->_helper->getHelper("contextSwitch");
        $contextSwitch->addActionContext("json-customers-by-name", "xml")
                ->addActionContext("comments", array("xml", "json"))
                ->initContext();
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
        $mapper = new Application_Model_MenuAccesos();
        $this->view->menu = $mapper->obtenerPorRol($this->_session->idRol);
        $this->view->username = $this->_session->username;
        $this->view->rol = $this->_session->role;
        $this->view->myHelpers = new Application_View_Helper_MyHelpers();
        
        $this->_noTodo = array("corresponsal");
        $this->_rolesEditarTrafico = array("trafico", "super", "trafico_ejecutivo", "gerente");
        $this->_todosClientes = array("trafico", "super", "trafico_ejecutivo");
    }

    public function indexAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Bodega";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
                ->appendStylesheet("/easyui/themes/default/easyui.css")
                ->appendStylesheet("/easyui/themes/icon.css")
                ->appendStylesheet("/js/common/bootstrap/datepicker/css/datepicker.css");
        $this->view->headScript()
                ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
                ->appendFile("/easyui/jquery.easyui.min.js")
                ->appendFile("/easyui/jquery.edatagrid.js")
                ->appendFile("/easyui/datagrid-filter.js")
                ->appendFile("/easyui/locale/easyui-lang-es.js")
                ->appendFile("/fullcalendar/lib/moment.min.js")
                ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js")
                ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js")
                ->appendFile("/js/common/typeahead.min.js")
                ->appendFile("/js/bodega/index/index.js?" . time());
    }

    public function nuevaEntradaAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Nueva entrada";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/js/common/jquery-datetimepicker/jquery.datetimepicker.min.css")
                ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css");
        $this->view->headScript()
                ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js")
                ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js")
                ->appendFile("/js/common/typeahead.min.js")
                ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
                ->appendFile("/js/bodega/index/nueva-entrada.js?" . time());
        $mapper = new Trafico_Model_ClientesMapper();
        $arr = $mapper->obtenerTodos();
        $form = new Bodega_Form_NuevaEntrada(array("clientes" => $arr));
        $this->view->form = $form;
    }
    
    public function crearTraficoAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Entrada de bodega";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()                
                ->appendStylesheet("/js/common/jquery-datetimepicker/jquery.datetimepicker.min.css")
                ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css");
        $this->view->headScript()
                ->appendFile("/js/common/moment.min.js")
                ->appendFile("/js/common/jquery-datetimepicker/jquery.datetimepicker.full.min.js")
                ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
                ->appendFile("/js/common/typeahead.min.js")
                ->appendFile("/js/common/loadingoverlay.min.js")
                ->appendFile("/js/bodega/index/crear-trafico.js?" . time());
        $mapper = new Trafico_Model_ClientesMapper();
        $mppr = new Bodega_Model_Bodegas();
        
        $form = new Bodega_Form_CrearTrafico(array("clientes" => $mapper->obtenerTodos(), "bodegas" => $mppr->obtenerTodos()));
        $this->view->form = $form;
    }

    public function editarEntradaAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Editar entrada";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/js/common/jquery-datetimepicker/jquery.datetimepicker.min.css")
                ->appendStylesheet("/js/common/contentxmenu/jquery.contextMenu.min.css")
                ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
                ->appendStylesheet("/css/jquery.qtip.min.css")
                ->appendStylesheet("/js/common/toast/jquery.toast.min.css");
        $this->view->headScript()
                ->appendFile("/js/common/moment.min.js")
                ->appendFile("//cdnjs.cloudflare.com/ajax/libs/numeral.js/2.0.6/numeral.min.js")
                ->appendFile("/js/common/jquery-datetimepicker/jquery.datetimepicker.full.min.js")
                ->appendFile("/js/common/contentxmenu/jquery.contextMenu.min.js")
                ->appendFile("/js/common/jquery.qtip.min.js")
                ->appendFile("/js/common/typeahead.min.js")             
                ->appendFile("/js/common/jquery.slidereveal.min.js")
                ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
                ->appendFile("/js/common/loadingoverlay.min.js")
                ->appendFile("/js/common/toast/jquery.toast.min.js?")
                ->appendFile("/js/bodega/index/editar-entrada.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits"),
        );
        $v = array(
            "id" => array("NotEmpty", new Zend_Validate_Int()),
            "active" => array("NotEmpty"),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id")) {

            if ($this->_session->role == "super") {
                $this->view->edit = true;
            } else {
                $this->view->edit = false;
            }
            
            $model = new Trafico_Model_TraficosMapper();
            if ($input->isValid("active")) {
                $this->view->active = $input->active;
            }
            
            $this->view->idTrafico = $input->id;
            $basico = $model->obtenerPorId($input->id);
            if ((int) $basico["estatus"] == 4) {
                $this->view->deleted = true;
                return;
            }
            $this->view->basico = $basico;
            
            $mapper = new Trafico_Model_ClientesMapper();
            $mppr = new Bodega_Model_Bodegas();

            $form = new Bodega_Form_CrearTrafico(array("clientes" => $mapper->obtenerTodos(), "bodegas" => $mppr->obtenerTodos()));
            $form->populate(array(
                "idBodega" => $basico["idBodega"]
            ));
            $this->view->form = $form;

            $mppr = new Vucem_Model_VucemMonedasMapper();
            $this->view->divisas = $mppr->obtenerMonedas();
        } else {
            throw new Exception("Invalid input!");
        }
    }
    
    public function modificarEntradaAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Modificar entrada";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
                ->appendStylesheet("/css/jqModal.css");
        $this->view->headScript()
                ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
                ->appendFile("/js/bodega/index/modificar-entrada.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits"),
        );
        $v = array(
            "id" => array("NotEmpty", new Zend_Validate_Int()),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id")) {
            $model = new Trafico_Model_TraficosMapper();
            $row = $model->obtenerPorId($input->id);
            if ($row["pagado"] === null) {
                $mapper = new Trafico_Model_ClientesMapper();
                $mppr = new Bodega_Model_Bodegas();
        
                $form = new Bodega_Form_CrearTrafico(array("clientes" => $mapper->obtenerTodos(), "bodegas" => $mppr->obtenerTodos()));
                $form->populate(array(
                    "idBodega" => $row["idBodega"],
                    "idCliente" => $row["idCliente"],
                    "referencia" => $row["referencia"],
                    "proveedor" => $row["proveedores"],
                    "blGuia" => $row["blGuia"],
                    "contenedorCaja" => $row["contenedorCaja"],
                    "lineaTransporte" => $row["lineaTransporte"],
                    "bultos" => $row["bultos"],
                ));
                if(isset($row["rfcSociedad"])) {
                    $this->view->rfcSociedad = $row["rfcSociedad"];                    
                }
                $form->idCliente->setAttrib("disabled", null);
                $this->view->form = $form;
            }
            $this->view->idTrafico = $input->id;
        }
    }

}
