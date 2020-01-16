<?php

class Principal_IndexController extends Zend_Controller_Action {

    protected $_session;
    protected $_soapClient;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;

    public function init() {
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->view->headLink()
                ->appendStylesheet("/js/common/bootstrap/css/bootstrap.min.css")
                ->appendStylesheet("/css/fontawesome/css/fontawesome-all.min.css")
                ->appendStylesheet("/less/traffic-module.css?" . time());
        $this->view->headLink(array("rel" => "icon shortcut", "href" => "/favicon.png"));
        $this->view->headScript()
                ->appendFile("/js/common/jquery-1.9.1.min.js")
                ->appendFile("/js/common/bootstrap/js/bootstrap.min.js")
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
            $session->actualizarSesion(Zend_Controller_Front::getInstance()->getRequest()->getRequestUri());
        } else {
            $this->getResponse()->setRedirect($this->_appconfig->getParam("link-logout"));
        }
        $model = new Comercializacion_Model_ClientesMapper();
        if ($this->_session->role == "cliente") {
            $model->updateAccess($this->_session->username);
            return $this->_redirector->gotoSimple("index", "index", "clientes");
        }
        $mapper = new Application_Model_MenuAccesos();
        if ($this->_session->rol != "cliente") {
            $this->view->menu = $mapper->obtenerPorRol($this->_session->idRol);
        } else {
            $this->view->menu = $mapper->obtenerPorRol(6);
        }
        $this->view->rol = $this->_session->role;
        $this->view->username = $this->_session->username;
        $news = new Application_Model_NoticiasInternas();
        $this->view->noticias = $news->obtenerTodos();
    }

    public function misActividadesAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Mis actividades";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
                ->appendStylesheet("/easyui/themes/material/easyui.css")
                ->appendStylesheet("/js/common/toast/jquery.toast.min.css")
                ->appendStylesheet("/less/traffic-module.css?" . time());
        $this->view->headScript()
                ->appendFile("/tinymce/tiny_mce/tiny_mce.js")
                ->appendFile("/js/common/jquery.form.min.js")
                ->appendFile("/js/common/jquery.validate.min.js")
                ->appendFile("/easyui/jquery.easyui.min.js")
                ->appendFile("/easyui/jquery.edatagrid.js")
                ->appendFile("/easyui/datagrid-filter.js")
                ->appendFile("/easyui/locale/easyui-lang-es.js")
                ->appendFile("/js/common/toast/jquery.toast.min.js?")
                ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
                ->appendFile("/js/principal/index/mis-actividades.js?" . time());
        $mppr = new Principal_Model_UsuariosActividades();
        $arr = $mppr->obtenerPorFecha($this->_session->id, date("Y-m-d"));
        if (!empty($arr)) {
            $this->view->actividades = $arr;
        }
        $emp = new Rrhh_Model_Empleados();
        $usr = $emp->obtenerPorUsuario($this->_session->id);
        if (isset($usr["idEmpresa"])) {
            $mpp = new Rrhh_Model_EmpresaDepartamentos();
            $dpts = $mpp->obtener($usr["idEmpresa"]);
            $this->view->empresas = $dpts;
            $cust = new Trafico_Model_ClientesMapper();
            $cts = $cust->obtenerPorEmpresa($usr["idEmpresa"]);
            $this->view->clientes = $cts;
            $mppa = new Rrhh_Model_EmpresaDeptoActividades();
            $acts = $mppa->obtener($usr["idPuesto"]);
            $this->view->misActividades = $acts;
        }
    }
    
    public function misDatosAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Mis datos";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/css/nuevo-estilo.css")
                ->appendStylesheet("/js/common/bootstrap/css/bootstrap.min.css")
                ->appendStylesheet("/css/DT_bootstrap.css")
                ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
                ->appendStylesheet("/less/traffic-module.css?" . time());
        $this->view->headScript()
                ->appendFile("/js/common/jquery.form.min.js")
                ->appendFile("/js/common/jquery.validate.min.js")
                ->appendFile("/js/common/bootstrap/js/bootstrap.min.js")
                ->appendFile("/js/common/jquery.dataTables.min.js")
                ->appendFile("/js/common/DT_bootstrap.js")
                ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
                ->appendFile("/js/principal/index/mis-datos.js?" . time());        
        $mapper = new Usuarios_Model_UsuariosMapper();            
        $form = new Principal_Form_MisDatos(array("edit" => true));
        $arr = $mapper->obtenerUsuario($this->_session->id);
        if (isset($arr) && !empty($arr)) {
            $dir = $mapper->obtenerDirectorio($this->_session->id);
            $arr["telefono"] = $dir["telefono"];
            $arr["extension"] = $dir["extension"];
            $form->populate($arr);
            $form->usuario->setAttrib("readonly", "true");
            $form->email->setAttrib("readonly", "true");
            $form->empresa->setAttrib("disabled", "true");
            $form->nombre->setAttrib("readonly", "true");
            $form->departamento->setAttrib("disabled", "true");
            $mppr = new Principal_Model_UsuariosTipoSolicitud();
            $this->view->tipoSolicitudes = $mppr->obtener();
        }
        $this->view->form = $form;
        $directorio = $mapper->directorio();
        $this->view->directorio = $directorio;
    }

    public function misDocumentosAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Mis documentos";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/css/nuevo-estilo.css")
                ->appendStylesheet("/js/common/bootstrap/css/bootstrap.min.css")
                ->appendStylesheet("/css/DT_bootstrap.css")
                ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
                ->appendStylesheet("/less/traffic-module.css?" . time());
        $this->view->headScript()
                ->appendFile("/js/common/loadingoverlay.min.js")
                ->appendFile("/js/common/jquery.form.min.js")
                ->appendFile("/js/common/jquery.validate.min.js")
                ->appendFile("/js/common/bootstrap/js/bootstrap.min.js")
                ->appendFile("/js/common/jquery.dataTables.min.js")
                ->appendFile("/js/common/DT_bootstrap.js")
                ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
                ->appendFile("/js/principal/index/mis-documentos.js?" . time());
    }

    public function indexAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " " . "Panel principal";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/css/nuevo-estilo.css")
                ->appendStylesheet("/less/traffic-module.css?" . time())
                ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
                ->appendStylesheet("/css/mobile-style.css?" . time());
        $this->view->headScript()
                ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js");
        $model = new Application_Model_TipoCambio();
        $tipo = $model->obtener(date("Y-m-d"));
        if ($tipo !== false) {
            $this->view->cambio = $tipo;
        }
        $this->view->role = $this->_session->role;
    }

    public function oeaAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " " . "OEA";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/js/principal/index/oea.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
        );
        $v = array(
            "directorio" => "NotEmpty",
        );
        $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        $mapper = new Rrhh_Model_OeaRelCarpetas();
        $arr = $mapper->obtener($i->directorio);
        $this->view->carpetas = $arr;
        $parent = $mapper->obtenerParent($i->directorio);
        if (isset($parent) && $parent["previo"] !== "") {
            $this->view->parent = $parent;
        }
        $mappera = new Rrhh_Model_OeaArchivos();
        $arra = $mappera->obtenerTodos($i->directorio);
        $this->view->archivos = $arra;
    }


    public function oaqTeEscuchaAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " " . "te escucha";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
            ->appendFile("/js/common/jquery-1.9.1.min.js")
            ->appendFile("/js/common/bootstrap/js/bootstrap.min.js")
            ->appendFile("/js/common/jquery.form.min.js")
            ->appendFile("/js/common/jquery.validate.min.js")
            ->appendFile("/js/common/js.cookie.js")
            ->appendFile("/js/principal/index/oaq-te-escucha.js?" . time());
    }
    
    public function isoAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " " . "SGC 2015";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/js/principal/index/iso.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
        );
        $v = array(
            "directorio" => "NotEmpty",
        );
        $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        $mapper = new Rrhh_Model_IsoRelCarpetas();
        $arr = $mapper->obtener($i->directorio);
        $this->view->carpetas = $arr;
        $parent = $mapper->obtenerParent($i->directorio);
        if (isset($parent) && $parent["previo"] !== "") {
            $this->view->parent = $parent;
        }
        $mappera = new Rrhh_Model_IsoArchivos();
        $arra = $mappera->obtenerTodos($i->directorio);
        $this->view->archivos = $arra;
        $this->view->directorio = $i->directorio;
        /*if ($i->isValid("directorio")) {
            $dr = $this->_buscarParentArray($i->directorio);
            $this->view->navigator = $dr;
            if ($i->directorio == 'be12223f8d48eaef222c432cfc3b5590b908d198') {
                $this->view->videos = array(
                    "/videos/punto4.mp4" => "Video 1: Introducción, Contacto de la organización",
                    "/videos/punto5.mp4" => "Video 2: Liderazgo.",
                    "/videos/punto6.mp4" => "Video 3: Planificacón, da <a href=\"https://docs.google.com/forms/d/e/1FAIpQLScnZQ3NCiJMlmEWrl0yl8_6sMwoFrTmsM9spRQ_slJtoRrzgA/viewform?usp=sf_link\" target=\"_blank\">clic aqui</a> para contestar al cuestionario.",
                );
            }
        }*/
        if ($i->isValid("directorio")) {
            $dr = $this->_buscarParentArray($i->directorio);
            $this->view->navigator = $dr;
            if ($i->directorio == 'be12223f8d48eaef222c432cfc3b5590b908d198') {
                $arr_browsers = ["Firefox", "Chrome", "Safari", "Opera",
                    "MSIE", "Trident", "Edge"];
                $agent = $_SERVER['HTTP_USER_AGENT'];
                $user_browser = '';
                foreach ($arr_browsers as $browser) {
                    if (strpos($agent, $browser) !== false) {
                        $user_browser = $browser;
                        break;
                    }
                }
                switch ($user_browser) {
                    case 'MSIE':
                        $user_browser = 'Internet Explorer';
                        break;
                    case 'Trident':
                        $user_browser = 'Internet Explorer';
                        break;
                    case 'Edge':
                        $user_browser = 'Internet Explorer';
                        break;
                }
                $browser = $user_browser;
                if ($browser == 'Chrome') {
                    $compatible = true;
                } else {
                    $compatible = false;
                }
                // echo "You are using " . $user_browser . " browser";
                $this->view->videos = array(
                     array("url" => "/videos/Capacitacion_CASA.mp4", "titulo" => "Capacitación sistema de pedimentos CASA", "compatible" => $compatible),
                );
            }
        }
    }

    protected function _buscarParent($directorio) {
        $rel = new Rrhh_Model_IsoRelCarpetas();
        $p = $rel->obtenerParent($directorio);
        if ($p["previo"]) {
            return $this->_buscarParent($p["previo"]) . DIRECTORY_SEPARATOR . $p["previo"];
        }
        return;
    }

    protected function _buscarParentArray($directorio) {
        $rel = new Rrhh_Model_IsoRelCarpetas();
        $p = $rel->obtenerParentArray($directorio);
        if ($p["previo"]) {
            return array_merge_recursive($this->_buscarParentArray($p["previo"]), array("directorio" => $directorio, "nombreCarpeta" => $p["nombreCarpeta"]));
        }
        $folders = new Rrhh_Model_IsoCarpetas();
        $p = $folders->obtener($directorio);
        return array("directorio" => $p["carpeta"], "nombreCarpeta" => $p["nombreCarpeta"]);
    }

}
