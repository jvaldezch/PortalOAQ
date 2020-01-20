<?php

class Rrhh_IndexController extends Zend_Controller_Action {

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
                ->appendStylesheet("/css/fontawesome/css/fontawesome-all.min.css")
                ->appendStylesheet("/less/traffic-module.css?" . time());
        $this->view->headScript()
                ->appendFile("/js/common/jquery-1.9.1.min.js")
                ->appendFile("/js/common/bootstrap/js/bootstrap.min.js")
                ->appendFile("/js/common/bootstrap/datepicker/js/bootstrap-datepicker.js")
                ->appendFile("/js/common/jquery.form.min.js")
                ->appendFile("/js/common/jquery.validate.min.js")
                ->appendFile("/js/common/jquery.dataTables.min.js")
                ->appendFile("/js/common/js.cookie.js")
                ->appendFile("/js/common/jquery.blockUI.js")
                ->appendFile("/js/common/DT_bootstrap.js")
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
        $this->view->rol = $this->_session->role;
        $news = new Application_Model_NoticiasInternas();
        $this->view->noticias = $news->obtenerTodos();
    }
    
    public function indexAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " " . " Empleados";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/js/common/DT_bootstrap.js")
                ->appendFile("/js/rrhh/index/index.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits"),
            "filter" => array("Digits"),
        );
        $v = array(
            "id" => array("NotEmpty", new Zend_Validate_Int()),
            "filter" => array("NotEmpty", new Zend_Validate_Int()),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        ($input->isValid("id")) ? $this->view->id = $input->id : null;
        ($input->isValid("filter")) ? $this->view->filter = $input->filter : null;
        $companies = new Application_Model_UsuariosEmpresas();
        $com = $companies->empresasDeUsuario($this->_session->id);
        $arr = $companies->selectEmpresasDeUsuario($this->_session->id);
        if (isset($com) && !empty($com)) {
            $this->view->empresas = $com;
        }
        if (isset($com) && !empty($com)) {
            $mapper = new Rrhh_Model_Empleados();
            $usuarios = $mapper->obtenerTodos($com, $input->id, $input->filter);
            $this->view->paginator = $usuarios;
            $this->view->empresas = $arr;
        }
    }

    public function informacionEmpleadoAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Información de empleado";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/js/common/toast/jquery.toast.min.css")
                ->appendStylesheet("/fullcalendar/fullcalendar.min.css")
                ->appendStylesheet("/fullcalendar/fullcalendar.min.css")
                ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
                ->appendStylesheet("/js/common/bootstrap/datepicker/css/datepicker.css");
        $this->view->headScript()
                ->appendFile("/js/common/toast/jquery.toast.min.js")
                ->appendFile("/fullcalendar/lib/moment.min.js")
                ->appendFile("/fullcalendar/fullcalendar.min.js")
                ->appendFile("/fullcalendar/locale/es.js")
                ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
                ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js")
                ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js")
                ->appendFile("/js/rrhh/index/informacion-empleado.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits"),
        );
        $v = array(
            "id" => array("NotEmpty", new Zend_Validate_Int()),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id")) {
            $companies = new Application_Model_UsuariosEmpresas();
            $com = $companies->selectEmpresasDeUsuario($this->_session->id);
            if (isset($com) && !empty($com)) {
                $this->view->empresas = $com;
            }
            $fotos = new Rrhh_Model_EmpleadoFotos();
            $bancos = new Rrhh_Model_EmpleadosBancos();
            $this->view->bancos = $bancos->obtenerOpciones();
            $edo = new Rrhh_Model_EmpleadosEstadoCivil();
            $this->view->estadoCivil = $edo->obtenerOpciones();
            $grupo = new Rrhh_Model_EmpleadosGrupoSanguineo();
            $this->view->grupoSanguineo = $grupo->obtenerOpciones();
            $esco = new Rrhh_Model_EmpleadosEscolaridad();
            $this->view->escolaridad = $esco->obtenerOpciones();
            $this->view->idEmpleado = $input->id;
            $this->view->foto = $fotos->obtener($input->id);
            $mppr = new Rrhh_Model_Empleados();
            $arr = $mppr->obtener($input->id);
            $this->view->estatus = (int) $arr["estatus"];
            $this->view->doctos = $arr["documentacion"];
            $this->view->capacit = $arr["capacitacion"];
        }
    }

    public function cambiarPerfilAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Cambiar perfil";
        $this->view->headMeta()->appendName("description", "");
    }
    
    public function altaEmpleadoAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Alta de empleado";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/js/rrhh/index/alta-empleado.js?" . time());
        $companies = new Application_Model_UsuariosEmpresas();
        $com = $companies->selectEmpresasDeUsuario($this->_session->id);
        if (isset($com) && !empty($com)) {
            $this->view->empresas = $com;
        }
    }
    
    public function empresasAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Empresas";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
                ->appendStylesheet("/js/common/toast/jquery.toast.min.css");
        $this->view->headScript()
                ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
                ->appendFile("/js/common/toast/jquery.toast.min.js?")
                ->appendFile("/js/rrhh/index/empresas.js?" . time());
        $mppr = new Application_Model_UsuariosEmpresas();
        $arr = $mppr->selectEmpresasDeUsuario($this->_session->id);
        if (isset($arr) && !empty($arr)) {
            $this->view->results = $arr;
        }
    }

    public function buzonAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Buzón de quejas y denuncias";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
            ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
            ->appendStylesheet("/js/common/toast/jquery.toast.min.css");
        $this->view->headScript()
            ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
            ->appendFile("/js/common/toast/jquery.toast.min.js?")
            ->appendFile("/js/rrhh/index/buzon.js?" . time());

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

        $mppr = new Principal_Model_OaqTeEscucha();

        $rows = $mppr->obtenerTodos();
        if (isset($rows) && !empty($rows)) {
            $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array($rows));
            $paginator->setItemCountPerPage(25);
            $paginator->setCurrentPageNumber($i->page);
            $this->view->paginator = $paginator;
        }
    }

    public function verQuejaDenunciaAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Queja o denuncia";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
            ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
            ->appendStylesheet("/js/common/toast/jquery.toast.min.css");
        $this->view->headScript()
            ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
            ->appendFile("/js/common/toast/jquery.toast.min.js?")
            ->appendFile("/js/rrhh/index/ver-queja-denuncia.js?" . time());

        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => "Digits",
        );
        $v = array(
            "id" => array(new Zend_Validate_Int(), "NotEmpty"),
        );
        $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());

        $mppr = new Principal_Model_OaqTeEscucha();
    }

}
