<?php

class Usuarios_IndexController extends Zend_Controller_Action {

    protected $_session;
    protected $_soapClient;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;
    protected $_key;

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
        $mapper = new Application_Model_MenuAccesos();
        $this->view->menu = $mapper->obtenerPorRol($this->_session->idRol);
        $this->view->username = $this->_session->username;
        $this->view->rol = $this->_session->role;
        $this->_key = NULL ? $this->_key = new Zend_Session_Namespace("") : $this->_key = new Zend_Session_Namespace("UploadKey");
    }

    public function usuariosAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Usuarios del sistema";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/css/mobile-style.css?" . time());
        if ($this->_session->role != "super") {
            throw new Zend_Controller_Action_Exception("Forbidden", 403);
        }
        $users = new Usuarios_Model_UsuariosMapper();
        $usuarios = $users->getUsers();
        $this->view->paginator = $usuarios;
    }
    
    public function actividadesAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Actividades de usuarios";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
                ->appendStylesheet("/easyui/themes/material/easyui.css")
                ->appendStylesheet("/js/common/toast/jquery.toast.min.css");
        $this->view->headScript()
                ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
                ->appendFile("/easyui/jquery.easyui.min.js")
                ->appendFile("/js/common/toast/jquery.toast.min.js?")
                ->appendFile("/js/usuarios/index/actividades.js?" . time());
        if ($this->_session->role != "super") {
            throw new Zend_Controller_Action_Exception("Forbidden", 403);
        }
    }
    
    public function dashboardAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Dashboard RabbitMQ";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
                ->appendStylesheet("/js/common/toast/jquery.toast.min.css");
        $this->view->headScript()
                ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
                ->appendFile("/js/common/toast/jquery.toast.min.js?")
                ->appendFile("/js/usuarios/index/dashboard.js?" . time());
        if ($this->_session->role != "super") {
            throw new Zend_Controller_Action_Exception("Forbidden", 403);
        }
        if (APPLICATION_ENV == "production") {
            $rabbit = new OAQ_Workers_Queues();
            if (($arr = $rabbit->queues())) {
                $this->view->queues = $arr;
            }
        }
    }

    public function usuariosEnLineaAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Usuarios en línea";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
                ->appendStylesheet("/js/common/toast/jquery.toast.min.css");
        $this->view->headScript()
                ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
                ->appendFile("/js/common/toast/jquery.toast.min.js?")
                ->appendFile("/js/usuarios/index/usuarios-en-linea.js?" . time());
        if ($this->_session->role != "super") {
            throw new Zend_Controller_Action_Exception("Forbidden", 403);
        }
        $mapper = new Application_Model_UsuarioSesiones();
        $offline = $mapper->usuariosNoActivos();
        if(count($offline)) {
            foreach ($offline as $item) {
                $mapper->borrar($item["id"]);
            }
        }
        $arr = $mapper->obtenerTodos();
        $this->view->paginator = $arr;
    }

    public function agregarUsuarioAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Agregar nuevo usuario";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/js/usuarios/index/agregar-usuario.js?" . time());
        if ($this->_session->role != "super") {
            throw new Zend_Controller_Action_Exception("Forbidden", 403);
        }
        $form = new Usuarios_Form_NuevoUsuario();
        $this->view->form = $form;
    }

    public function editarUsuarioAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Editar usuario";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
                ->appendStylesheet("/js/common/toast/jquery.toast.min.css")
                ->appendStylesheet("/css/mobile-style.css?" . time());
        $this->view->headScript()
                ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
                ->appendFile("/js/common/toast/jquery.toast.min.js?")
                ->appendFile("/js/common/jquery.dataTables.min.js")
                ->appendFile("/js/usuarios/index/editar-usuario-datos.js?" . time())
                ->appendFile("/js/usuarios/index/editar-usuario-vucem.js?" . time())
                ->appendFile("/js/usuarios/index/editar-usuario-trafico.js?" . time())
                ->appendFile("/js/usuarios/index/editar-usuario-validador.js?" . time())
                ->appendFile("/js/usuarios/index/editar-usuario-expediente.js?" . time())
                ->appendFile("/js/usuarios/index/editar-usuario-inhouse.js?" . time())
                ->appendFile("/js/usuarios/index/editar-usuario-proveedor.js?" . time())
                ->appendFile("/js/usuarios/index/editar-usuario-bodega.js?" . time())
                ->appendFile("/js/usuarios/index/editar-usuario.js?" . time());
        if ($this->_session->role != "super") {
            throw new Zend_Controller_Action_Exception("Forbidden", 403);
        }
        try {
            $flt = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $vld = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($flt, $vld, $this->_request->getParams());
            if ($input->isValid("id")) {
                $this->view->idUsuario = $input->id;
                // DATOS
                $form = new Usuarios_Form_NuevoUsuario(array("edit" => true));
                $usuarios = new Usuarios_Model_UsuariosMapper();
                $usuario = $usuarios->obtenerUsuario($input->id);
                $form->populate($usuario);
                $form->usuario->setAttrib("readonly", "true");
                if (isset($usuario["patenteUsuario"])) {
                    $adus = new Trafico_Model_TraficoAduanasMapper();
                    foreach ($adus->obtenerAduanas($usuario["patenteUsuario"]) as $item) {
                        $form->aduanaUsuario->addMultiOption($item["aduana"], $item["aduana"] . " - " . $item["nombre"]);
                    }
                }
                $this->view->form = $form;
                // VUCEM
                $perm = new Vucem_Model_VucemPermisosMapper();
                $fiel = new Usuarios_Form_AsignarFiel(array("id" => $input->id));
                if (($permisos = $perm->obtenerPermisos($input->id))) {
                    $this->view->permisos = $permisos;
                }
                $this->view->fiel = $fiel;
                // TRAFICO
                $mapper = new Trafico_Model_ClientesMapper();
                $arr = $mapper->obtenerTodos();
                $html = new V2_Html();
                $html->select("traffic-select-large", "idCliente");
                $html->addSelectOption("", "---", true);
                foreach ($arr as $k => $v) {
                    $html->addSelectOption($k, htmlentities($v));
                }
                $this->view->clientes = $html->getHtml();
                // REPOSITORIO
                $repositorio = new Usuarios_Form_Aduanas();
                $this->view->repositorio = $repositorio;
                // INHOUSE
                if($usuario["nombreRol"] == "inhouse" || $usuario["nombreRol"] == "proveedor") {
                    $docs = new Archivo_Model_DocumentosMapper();
                    $this->view->inhouse = true;
                    $this->view->clientes = $html->getHtml();
                    $this->view->proveedor = true;                    
                    $this->view->documentos = $docs->obtener();
                }
                
                $mppr = new Bodega_Model_Bodegas();
                
                $bodegas = new Usuarios_Form_Bodegas(array("bodegas" => $mppr->obtenerTodos()));
                $this->view->bodegas = $bodegas;
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function aduanasTraficoAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $idCliente = $this->_getParam("idCliente", null);
        if (isset($idCliente)) {
            $model = new Trafico_Model_TraficoCliAduanasMapper();
            $aduanas = $model->clienteAduanas($idCliente);
            if (isset($aduanas) && !empty($aduanas)) {
                $html = "<select style=\"width: 150px\" class=\"focused\" id=\"idAduana\" name=\"idAduana\">";
                $html .= "<option label=\"-- Seleccionar --\" value=\"\">-- Seleccionar --</option>";
                foreach ($aduanas as $k => $aduana) {
                    $html .= "<option value=\"" . $k . "\">" . $aduana["patente"] . "-" . $aduana["aduana"] . "</option>";
                }
                $html .= "</select>";
                echo Zend_Json::encode(array("success" => true, "html" => $html));
                return false;
            } else {
                $html = "<select disabled=\"disabled\" style=\"width: 150px\" class=\"focused\" id=\"idAduana\" name=\"idAduana\"><option label=\"-- Seleccionar --\" value=\"\">-- Seleccionar --</option></select>";
                echo Zend_Json::encode(array("success" => false, "html" => $html));
                return false;
            }
        }
    }

    public function menusAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " " . " Editar Menus";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/js/usuarios/index/menus.js?" . time());
        $mapper = new Application_Model_RolesMapper();
        $this->view->roles = $mapper->todos();
        $mapper = new Application_Model_MenusMapper();
        $modules = $mapper->obtenerModulos();
        $controllers = $mapper->obtenerControladores();
        $this->view->modules = $modules;
        $this->view->controllers = $controllers;
    }

    public function misDatosAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " " . " Editar usuario";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/js/usuarios/index/mis-datos.js?" . time());
        $users = new Usuarios_Model_UsuariosMapper();
        $form = new Usuarios_Form_MisDatos();
        $arr = $users->obtenerDatosUsuario($this->_session->id, $this->_session->username);
        $form->populate($arr);
        $this->view->form = $form;
    }

    public function fielAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " " . " FIEL (VU)";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/js/usuarios/index/fiel.js?" . time());
        if ($this->_session->role != "super") {
            throw new Zend_Controller_Action_Exception("Forbidden", 403);
        }
        $mapper = new Vucem_Model_VucemFirmanteMapper();
        $arr = $mapper->listadoFirmantes();
        $this->view->data = $arr;
    }

    public function nuevaFielAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " " . " Nueva FIEL (VU)";
        $this->view->headMeta()->appendName("description", "");        
        if (isset($this->_key->rfc)) {
            $this->view->rfc = $this->_key->rfc;
        }
        if (isset($this->_key->nombre)) {
            $this->view->nombre = $this->_key->nombre;
        }
        if (isset($this->_key->patente)) {
            $this->view->patente = $this->_key->patente;
        }
        if (isset($this->_key->aduana)) {
            $this->view->aduana = $this->_key->aduana;
        }
        if (isset($this->_key->pwdvu)) {
            $this->view->pwdvu = $this->_key->pwdvu;
        }
        if (isset($this->_key->pwdws)) {
            $this->view->pwdws = $this->_key->pwdws;
        }
        if (isset($this->_key->figura)) {
            $this->view->figura = $this->_key->figura;
        }
        $request = $this->getRequest();
        if ($request->isPost()) {
            if (isset($this->_key->ready) && $this->_key->ready === true) {
                $fiel = new Vucem_Model_VucemFirmanteMapper();
                $added = $fiel->addNew($this->_key->figura, $this->_key->patente, $this->_key->aduana, $this->_key->nombre, $this->_key->rfc, $this->_key->cerFile, $this->_key->keyFile, $this->_key->reqFile, $this->_key->pemFile, $this->_key->spemFile, $this->_key->pwdvu, $this->_key->pwdvu, $this->_key->pwdws, $this->_key->pwdvu, $this->_session->username);
                if ($added == true) {
                    $this->_key->unsetAll();
                    return $this->_redirector->gotoSimple("fiel", "index", "usuarios");
                }
            }
        }
    }

    public function editarFielAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " " . " Editar FIEL (VU)";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
                ->appendStylesheet("/js/common/highlight/styles/github.css")
                ->appendStylesheet("/css/jqModal.css");
        $this->view->headScript()
                ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
                ->appendFile("/js/common/additional-methods.min.js")
                ->appendFile("/js/common/jqModal.js")
                ->appendFile("/js/common/highlight/highlight.pack.js")
                ->appendFile("/js/usuarios/index/editar-fiel.js?" . time());
        if ($this->_session->role == "super") {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $v = array(
                "id" => new Zend_Validate_Int(),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $mapper = new Vucem_Model_VucemFirmanteMapper();
                $arr = $mapper->obtenerDetalleFirmanteId($input->id);
                $this->view->data = $arr;
                $this->view->idFiel = $input->id;
                $sat = new OAQ_SATValidar();
                $fechas = $sat->fechasDeCertificado($arr["cer"]);
                if (!empty($fechas)) {
                    $mapper->actualizarFechasVencimiento($input->id, $fechas["valido_desde"], $fechas["valido_hasta"]);
                    $this->view->validoDesde = $fechas["valido_desde"];
                    $this->view->validoHasta = $fechas["valido_hasta"];
                }
            }
        }
    }

    public function firmaFielAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            } elseif ($this->_session->role !== "super") {
                throw new Zend_Controller_Request_Exception("Operation not allowed!");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost();

                $tbl = new Vucem_Model_VucemFirmanteMapper();
                $rfc = $tbl->obtenerDetalleFirmanteId($post["idFiel"]);

                $cadena = $post["cadena"];
                if ($rfc["cer"] != "") {
                    $signature = "";
                    $pkeyid = openssl_get_privatekey(base64_decode($rfc["spem"]), $rfc["spem_pswd"]);
                    if (isset($rfc["sha"]) && $rfc["sha"] == "sha256") {
                        openssl_sign($cadena, $signature, $pkeyid, OPENSSL_ALGO_SHA256);
                    } else {
                        openssl_sign($cadena, $signature, $pkeyid);
                    }
                }
                if (isset($signature) && $signature != false) {
                    echo Zend_Json::encode(array("success" => true, "firma" => base64_encode($signature)));
                    return false;
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerFielAction() {
        $this->_helper->layout()->disableLayout();
        $vucemFir = new Vucem_Model_VucemFirmanteMapper();
        $perm = new Vucem_Model_VucemPermisosMapper();
        $fiel = $vucemFir->fielDisponible();
        $id = $this->_getParam("id");
        $this->view->userId = $id;
        if (($permisos = $perm->obtenerPermisos($id))) {
            $this->view->permisos = $permisos;
        }
        foreach ($fiel as $k => $item) {
            foreach ($permisos as $l => $prm) {
                if ($prm["rfc"] == $item["rfc"] && $prm["patente"] == $item["patente"] && $prm["aduana"] == $item["aduana"] && $prm["tipo"] == $item["tipo"])
                    unset($fiel[$k]);
            }
        }
        $this->view->fiel = $fiel;
    }

    public function asignarFielAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $perm = new Vucem_Model_VucemPermisosMapper();
        $idUsuario = $this->_getParam("id");
        $idFirmante = $this->_getParam("idfirmante");
        $rfc = $this->_getParam("rfc");
        $patente = $this->_getParam("patente");
        $aduana = $this->_getParam("aduana");
        if (!($perm->verificarPermiso($idUsuario, $rfc, $idFirmante, $patente, $aduana))) {
            $added = $perm->agregarNuevoPermiso($idUsuario, $rfc, $idFirmante, $patente, $aduana);
            if ($added) {
                echo Zend_Json::encode(array("success" => true));
                return false;
            } else {
                echo Zend_Json::encode(array("success" => false));
                return false;
            }
        }
    }

    public function removerFielAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $perm = new Vucem_Model_VucemPermisosMapper();
        $idUsuario = $this->_getParam("id");
        $idFirmante = $this->_getParam("idfirmante");
        $rfc = $this->_getParam("rfc");
        $patente = $this->_getParam("patente");
        $aduana = $this->_getParam("aduana");
        if (($perm->verificarPermiso($idUsuario, $rfc, $idFirmante, $patente, $aduana))) {
            $added = $perm->removerPermiso($idUsuario, $rfc, $idFirmante, $patente, $aduana);
            if ($added) {
                echo Zend_Json::encode(array("success" => true));
                return false;
            } else {
                echo Zend_Json::encode(array("success" => false));
                return false;
            }
        }
    }

    public function downloadFileAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $type = $this->_request->getParam("type", null);
        $patente = $this->_request->getParam("patente", null);
        $aduana = $this->_request->getParam("aduana", null);
        $rfc = $this->_request->getParam("rfc", null);
        $firm = new Vucem_Model_VucemFirmanteMapper();
        $rfc = $firm->archivosFirmante($rfc, null, $patente, $aduana);
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Pragma: public");
        header("Content-Disposition: attachment; filename={$rfc[$type]}");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        switch ($type) {
            case "key_nom":
                echo base64_decode($rfc["key"]);
                break;
            case "certificado_nom":
                echo base64_decode($rfc["cer"]);
                break;
            case "spem_nom":
                echo base64_decode($rfc["spem"]);
                break;
            case "req_nom":
                echo base64_decode($rfc["req"]);
                break;
            case "pem_nom":
                echo base64_decode($rfc["pem"]);
                break;
        }
    }

    public function cerFileUploadAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $rfc = strtoupper(filter_input(INPUT_POST, "rfc", FILTER_SANITIZE_SPECIAL_CHARS));
        $folder = "/tmp" . DIRECTORY_SEPARATOR . $rfc;
        if (!file_exists($folder)) {
            mkdir($folder);
        }
        $adapter = new Zend_File_Transfer_Adapter_Http();
        $adapter->setDestination($folder);
        if (!$adapter->receive()) {
            echo "<span class=\"error\">Error al subir</span>";
            return false;
        }
        $this->_key->cerFile = $adapter->getFileName();
        echo "<span style=\"color:green\">OK</span>";
        return false;
    }

    public function uploadNewKeyAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $input = array();
        $input["pwd-vu"] = filter_input(INPUT_POST, "pwd-vu", FILTER_SANITIZE_SPECIAL_CHARS);
        $input["rfc"] = strtoupper(filter_input(INPUT_POST, "rfc", FILTER_SANITIZE_SPECIAL_CHARS));
        $input["pwd-ws"] = filter_input(INPUT_POST, "ws", FILTER_SANITIZE_SPECIAL_CHARS);
        $input["patente"] = filter_input(INPUT_POST, "patente", FILTER_SANITIZE_SPECIAL_CHARS);
        $input["aduana"] = filter_input(INPUT_POST, "aduana", FILTER_SANITIZE_SPECIAL_CHARS);
        $input["nombre"] = filter_input(INPUT_POST, "nombre", FILTER_SANITIZE_SPECIAL_CHARS);
        $input["figura"] = filter_input(INPUT_POST, "figura", FILTER_SANITIZE_SPECIAL_CHARS);
        $firmante = new Vucem_Model_VucemFirmanteMapper();
        $verify = $firmante->verificarFirmante("prod", $input["figura"], $input["rfc"], $input["patente"], $input["aduana"]);
        if ($verify === true) {
            $array = array(
                "res" => false,
                "rfc" => "<span class=\"error\">El RFC ya existe en la base de datos</span>",
            );
            echo Zend_Json_Encoder::encode($array);
            return false;
        } else {
            $this->_key->pwdvu = $input["pwd-vu"];
            $this->_key->pwdws = $input["pwd-ws"];
            $this->_key->rfc = $input["rfc"];
            $this->_key->patente = $input["patente"];
            $this->_key->aduana = $input["aduana"];
            $this->_key->nombre = $input["nombre"];
            $this->_key->figura = $input["figura"];
            if (file_exists($this->_key->cerFile) && file_exists($this->_key->keyFile) && file_exists($this->_key->pemFile) && file_exists($this->_key->spemFile)) {
                $this->_key->ready = true;
                echo Zend_Json_Encoder::encode(array("res" => true));
                return false;
            } else {
                echo Zend_Json_Encoder::encode(array("res" => false, "rfc" => "<span class=\"error\">Debe subir todos los archivos</span>"));
                return false;
            }
        }
    }

    public function fileUploadAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $pwd = filter_input(INPUT_POST, "pwd-vu", FILTER_SANITIZE_SPECIAL_CHARS);
        $rfc = strtoupper(filter_input(INPUT_POST, "rfc", FILTER_SANITIZE_SPECIAL_CHARS));
        $folder = "/tmp" . DIRECTORY_SEPARATOR . $rfc;
        if (!file_exists($folder)) {
            mkdir($folder);
        }
        $adapter = new Zend_File_Transfer_Adapter_Http();
        $adapter->setDestination($folder);
        if (!$adapter->receive()) {
            $messages = $adapter->getMessages();
            echo implode("\n", $messages);
        }
        $keyname = $adapter->getFileName();
        $pemname = "/tmp" . DIRECTORY_SEPARATOR . $rfc . DIRECTORY_SEPARATOR . $rfc . ".pem";
        $spemname = "/tmp" . DIRECTORY_SEPARATOR . $rfc . DIRECTORY_SEPARATOR . $rfc . "_secure.pem";
        exec("openssl pkcs8 -inform DER -outform PEM -in {$keyname} -out {$pemname} -passin pass:\"{$pwd}\"");
        $filesize = filesize($pemname);
        if ($filesize > 0) {
            exec("openssl rsa -in {$pemname} -des3 -out {$spemname} -passout pass:\"{$pwd}\"");
            $pfilesize = filesize($spemname);
            if ($pfilesize > 0) {
                $this->_key->keyFile = $keyname;
                $this->_key->pemFile = $pemname;
                $this->_key->spemFile = $spemname;
                echo "<span style=\"color:green\">OK</span>";
                return false;
            }
        }
        echo "<span class=\"error\">Error de contraseña</span>";
        return false;
    }

    public function wsPassAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $ws = filter_input(INPUT_POST, "ws", FILTER_SANITIZE_SPECIAL_CHARS);
        $rfc = strtoupper(filter_input(INPUT_POST, "rfc", FILTER_SANITIZE_SPECIAL_CHARS));

        $doc = new DOMDocument("1.0", "utf-8");
        $doc->formatOutput = true;
        $root = $doc->createElementNS("http://schemas.xmlsoap.org/soap/envelope/", "soapenv:Envelope");
        $doc->appendChild($root);
        $doc->createElementNS("http://www.ventanillaunica.gob.mx/cove/ws/oxml/", "p:x", "test");
        $root->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:oxml", "http://www.ventanillaunica.gob.mx/cove/ws/oxml/");

        $header = $doc->createElement("soapenv:Header");
        $root->appendChild($header);
        $security = $doc->createElementNS("http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd", "wsse:Security");
        $usernameToken = $doc->createElement("wsse:UsernameToken");
        $usernameToken->appendChild($doc->createElement("wsse:Username", $rfc));
        $password = $doc->createElement("wsse:Password", $ws);
        $password->setAttribute("Type", "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText");

        $usernameToken->appendChild($password);
        $security->appendChild($usernameToken);
        $header->appendChild($security);

        $body = $doc->createElement("soapenv:Body");
        $root->appendChild($body);

        $service = $doc->createElementNS("http://www.ventanillaunica.gob.mx/cove/ws/oxml/", "oxml:solicitarRecibirCoveServicio");
        $body->appendChild($service);

        $vucem = new OAQ_Vucem();
        $response = $vucem->enviarCoveVucem($doc->saveXML(), "https://www.ventanillaunica.gob.mx/ventanilla/RecibirCoveService");
        $array = $vucem->vucemXmlToArray($response);

        unset($array["Header"]);
        if (isset($array["Body"]["solicitarRecibirCoveServicioResponse"])) {
            if (isset($array["Body"]["solicitarRecibirCoveServicioResponse"]["mensajeInformativo"])) {
                if (preg_match("/No se recibieron/i", $array["Body"]["solicitarRecibirCoveServicioResponse"]["mensajeInformativo"])) {
                    echo "<span style=\"color:green\">OK</span>";
                    return false;
                }
            }
        } elseif (isset($array["Body"]["Fault"])) {
            if (isset($array["Body"]["Fault"]["faultcode"])) {
                if (preg_match("/FailedAuthentication/i", $array["Body"]["Fault"]["faultcode"])) {
                    echo "<span class=\"error\">Password Error</span>";
                    return false;
                }
                echo "<span class=\"error\">Unknown Error</span>";
                return false;
            }
        }
    }

    public function deleteUserAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
        }
        $id = $this->getRequest()->getParam("id", null);
        $users = new Usuarios_Model_UsuariosMapper();
        if ($id && $this->_session->role == "super") {
            $removed = $users->deleleUser($id);
            if ($removed === true) {
                echo Zend_Json_Encoder::encode(array("success" => true));
                return false;
            } else {
                echo Zend_Json_Encoder::encode(array("success" => false));
                return false;
            }
        } else {
            echo Zend_Json_Encoder::encode(array("success" => false));
            return false;
        }
    }

    public function addUserAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
        }
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data && $this->_session->role == "super") {
                $com = new Application_Model_CustomsMapper();
                $empresa = $com->getCompanyName($data["empresa"]);
                if ($data["confirm_password"] === $data["password"]) {
                    $users = new Usuarios_Model_UsuariosMapper();
                    if (!$users->verifyUser($data["usuario"])) {
                        $added = $users->addNewUser($data["nombre"], $data["email"], $data["usuario"], $data["patente_usuario"], $data["aduana_usuario"], $data["empresa"], $data["password"], $data["rol"], $empresa, $data["departamento"], $data["sispedimentos"]);
                        if ($added !== false) {
                            echo Zend_Json_Encoder::encode(array("success" => true, "id" => $added));
                            return false;
                        }
                    } else {
                        echo Zend_Json_Encoder::encode(array("success" => false, "message" => "El usuario ya existe."));
                        return false;
                    }
                } else {
                    echo Zend_Json_Encoder::encode(array("success" => false, "message" => "El password no coincide."));
                    return false;
                }
            } else {
                echo Zend_Json_Encoder::encode(array("success" => false));
                return false;
            }
        }
    }

    public function prefijosAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " " . " Prefijos";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/js/usuarios/index/prefijos.js?" . time());
        try {
            if ($this->_session->role != "super") {
                throw new Zend_Controller_Action_Exception("Forbidden", 403);
            }
            $mapper = new Archivo_Model_DocumentosMapper();
            $arr = $mapper->getAll();
            $this->view->data = $arr;
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function alertasAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " " . " Alertas";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/js/common/bootstrap/datepicker/css/datepicker.css");
        $this->view->headScript()
                ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js")
                ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js")
                ->appendFile("/js/usuarios/index/alertas.js?" . time());
        try {
            if ($this->_session->role != "super") {
                throw new Zend_Controller_Action_Exception("Forbidden", 403);
            }
            $news = new Application_Model_NoticiasInternas();
            $this->view->arr = $news->obtener();
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function aplicacionesAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " " . " Aplicaciones";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/js/common/bootstrap/datepicker/css/datepicker.css");
        $this->view->headScript()
                ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js")
                ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js")
                ->appendFile("/js/common/additional-methods.min.js")
                ->appendFile("/js/usuarios/index/aplicaciones.js?" . time());
        try {
            if ($this->_session->role != "super") {
                throw new Zend_Controller_Action_Exception("Forbidden", 403);
            }
            $mppr = new Webservice_Model_AppVersion();
            $this->view->aplicaciones = $mppr->todasVersiones();
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function comunicadosAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " " . " Comunicados";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/js/usuarios/index/comunicados.js?" . time());
        try {
            if ($this->_session->role != "super") {
                throw new Zend_Controller_Action_Exception("Forbidden", 403);
            }
            $mapper = new Usuarios_Model_UsuariosMapper();
            $this->view->usuarios = $mapper->obtenerUsuariosEmails();
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function analizarSellosAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);        
        $mapper = new Vucem_Model_VucemFirmanteMapper();
        $arr = $mapper->certificados();
        if(isset($arr) && !empty($arr)) {
            foreach ($arr as $cer) {
                $filename = "/tmp" . DIRECTORY_SEPARATOR . $cer["nombre"];
                file_put_contents($filename, base64_decode($cer["contenido"]));
                exec("openssl x509 -inform DER -in {$filename} -dates -noout", $output);
                $data = array();
                if(isset($output[0])) {
                    $exp = explode("=", $output[0]);
                    if(isset($exp[1])) {
                        $data["valido_desde"] = date("Y-m-d H:i:s", strtotime($exp[1]));
                    }
                }
                if(isset($output[1])) {
                    $exp = explode("=", $output[1]);
                    if(isset($exp[1])) {
                        $data["valido_hasta"] = date("Y-m-d H:i:s", strtotime($exp[1]));
                    }
                }
                if(isset($data) && !empty($data)) {
                    $updated = $mapper->update($cer["id"], $data);
                    if($updated) {
                        unlink($filename);
                    }
                }
                unset($output);
            }
        }
    }

}
