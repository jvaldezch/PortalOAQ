<?php

class Trafico_IndexController extends Zend_Controller_Action
{

    protected $_session;
    protected $_soapClient;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;
    protected $_rolesEditarTrafico;
    protected $_todosClientes;
    protected $_noTodo;

    public function init()
    {
        $this->_appconfig = new Application_Model_ConfigMapper();
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
        $this->_noTodo = array("corresponsal");
        $this->_rolesEditarTrafico = array("trafico", "super", "trafico_ejecutivo", "gerente");
        $this->_todosClientes = array("trafico", "super", "trafico_ejecutivo", "gerente");
        $news = new Application_Model_NoticiasInternas();
        $this->view->noticias = $news->obtenerTodos();
        if (APPLICATION_ENV == "development") {
            $this->view->browser_sync = "<script async src='http://{$this->_config->app->browser_sync}/browser-sync/browser-sync-client.js?v=2.26.7'><\/script>";
        }
    }

    public function reporteDeOperacionesAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Reporte de operaciones";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()->appendStylesheet("/extjs/resources/css/ext-all.css")
            ->appendStylesheet("/extjs/resources/css/ext-all-neptune-rtl.css")
            ->appendStylesheet("/css/principal.css");
        $this->view->headScript()->appendFile("/extjs/bootstrap.js")
            ->appendFile("/js/ext_operaciones.js");
        $users = new Usuarios_Model_UsuariosMapper();
        $company = $users->getUserCompanyRelatedInfo($this->_session->id);
        $this->view->aduana = $company["aduana"];
        $this->view->patente = $company["patente"];
    }

    public function editarClienteAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Editar cliente";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
            ->appendFile("/js/trafico/index/editar-cliente.js?" . time());
        $gets = $this->_request->getParams();
        if (isset($gets)) {
            $filters = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => "Digits",
            );
            $validators = array(
                "id" => array(new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($filters, $validators, $gets);
            if ($input->isValid()) {
                $form = new Trafico_Form_NuevoCliente();
                $mapper = new Trafico_Model_ClientesMapper();
                $row = $mapper->datosCliente($input->id);
                if (count($row)) {
                    $form->populate(array(
                        "id" => $row["id"],
                        "rfc" => $row["rfc"],
                        "nombre" => $row["nombre"],
                    ));
                    $form->rfc->setAttrib("readonly", "readonly");
                }
                $this->view->form = $form;
                $mppr = new Application_Model_UsuariosEmpresas();
                $arr = $mppr->empresas();
                if (isset($arr) && !empty($arr)) {
                    $this->view->empresas = $arr;
                    $this->view->idEmpresa = $row["idEmpresa"];
                }
            }
        }
    }

    public function excelConsultaTraficoAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $search = NULL ? $search = new Zend_Session_Namespace("") : $search = new Zend_Session_Namespace("TraficoOAQ");
        $headers = array(
            "Aduana" => "aduana",
            "Referencia" => "referencia",
            "Cliente" => "cliente",
            "BL/Guía" => "bl_guia",
            "Fecha entrada" => "fecha_entrada",
            "Proveedor" => "proveedor",
            "Fletera" => "fletera",
            "Tipo de embarque" => "embarque",
            "Estatus" => "estatus",
            "F.Sol.Anticipo" => "fecha_sol",
            "Importe Sol." => "importe_sol",
            "F.Anticipo" => "fecha_anti",
            "F.Cruce" => "fecha_cruce",
            "F.Prog." => "fecha_prog",
            "Días" => "fecha_entrada",
            "Bultos" => "bultos",
            "Sección" => "seccion",
        );
        $embarques = new Dashboard_Model_EmbarquesMonitorMapper();
        $traficos = $embarques->getAllShipmentAndStatus($search->aduana);
        $reports = new OAQ_ExcelExport();
        $reports->traficReport($headers, $traficos, "traficos", "Trafico de aduana", $search->aduana, "Trafico de aduana");
    }

    public function editOperationAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $ops = new Trafico_Model_OperacionesTraficoMapper();
            if ($data["oper"] == "edit") {
                $ops->editOperation($data["id"], $data["patente"], $data["aduana"], $data["referencia"], $data["bl"], $data["fechaeta"], $data["fechasolicitud"], $data["montosol"], $data["fechaenvio"], $data["fechaliberacion"], $data["observaciones"], $this->_session->username);
            } elseif ($data["oper"] == "add") {
                $ops->addSimpleOperation($data["patente"], $data["aduana"], $data["referencia"], $data["nombrecli"], $data["bl"], $data["fechaeta"], $data["fechasolicitud"], $data["montosol"], $data["fechaenvio"], $data["fechaliberacion"], $data["observaciones"], $this->_session->username);
            }
        }
    }

    public function getCustomersAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $cus = new Application_Model_CustomersMapper();
        $all = $cus->getCustomers();
        if ($all) {
            $result = "<select id=\"nombrecli\" class=\"FormElement ui-widget-content ui-corner-all\" role=\"select\" name=\"nombrecli\" size=\"1\">
                <option value=\"None\">None</option>";
            foreach ($all as $cus) {
                $result .= "<option role=\"option\" value=\"" . str_replace('"', "", $cus["nombre"]) . "\">" . str_replace('"', "", $cus["nombre"]) . "</option>";
            }
            echo $result;
        }
    }

    public function ultimasSolicitudesAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Últimas solicitudes";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
            ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
            ->appendStylesheet("/js/common/bootstrap/datepicker/css/datepicker.css")
            ->appendStylesheet("/css/jqModal.css");
        $this->view->headScript()
            ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
            ->appendFile("/js/common/jqModal.js")
            ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js")
            ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js")
            ->appendFile("/js/trafico/index/ultimas-solicitudes.js?" . time());

        $referencias = new OAQ_Referencias();
        $res = $referencias->restricciones($this->_session->id, $this->_session->role);
        $page = $this->_request->getParam("page", null);
        $pageSize = $this->_request->getParam("size", 20);

        $complemento = $this->_request->getParam("complementos", null);
        $pendiente = $this->_request->getParam("pendiente", null);
        $aduana = $this->_request->getParam("aduanas", null);
        $depositado = $this->_request->getParam("depositado", null);
        $warning = $this->_request->getParam("warning", null);
        $idCliente = $this->_request->getParam("idCliente", null);

        if (isset($aduana) && is_int((int) $aduana)) {
            $adu = $aduana;
            $this->view->idAduana = $aduana;
        } else {
            $adu = false;
        }
        if (isset($complemento) && $complemento == true) {
            $comp = true;
        } else {
            $comp = false;
        }
        if (isset($pendiente) && $pendiente == true) {
            $pend = true;
        } else {
            $pend = false;
        }
        if (isset($depositado) && $depositado == true) {
            $dep = true;
        } else {
            $dep = false;
        }
        if (isset($warning) && $warning == true) {
            $war = true;
        } else {
            $war = false;
        }
        if (!in_array($this->_session->role, $this->_rolesEditarTrafico)) {
            $this->view->error = true;
        }
        if (in_array($this->_session->role, $this->_noTodo)) {
            $this->view->corresponsal = true;
        }
        $model = new Trafico_Model_TraficoSolicitudesMapper();
        $form = new Trafico_Form_BuscarSolicitud();
        $gets = $this->_request->getParams();
        if (isset($gets)) {
            $filters = array(
                "*" => array("StringTrim", "StripTags"),
                "buscar" => "StringToUpper",
                "idCliente" => "Digits",
                "aduanas" => "Digits",
            );
            $validators = array(
                "buscar" => array(new Zend_Validate_Regex("/^[-_a-zA-Z0-9.]+$/")),
                "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                "aduanas" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($filters, $validators, $gets);
            if ($input->isValid("buscar")) {
                $search = $input->getEscaped("buscar");
            }
            $form->populate($input->getEscaped());
        }
        if (!empty($res["idsAduana"])) {

            $mapper = new Trafico_Model_TraficoAduanasMapper();
            $this->view->filters = $mapper->obtenerTodas($res["idsAduana"]);
            // $data = $model->obtenerSolicitudesTrafico($res["idsAduana"], isset($search) ? $search : null, $comp, $adu, $pend, $dep, $war, $idCliente);
            // if (isset($data) && !empty($data)) {
            //     $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array($data));
            //     $paginator->setItemCountPerPage($pageSize);
            //     $paginator->setCurrentPageNumber($page);
            //     $this->view->paginator = $paginator;
            // }
            $select = $model->obtenerSolicitudesTraficoSelect($res["idsAduana"], isset($search) ? $search : null, $comp, $adu, $pend, $dep, $war, $idCliente);            
            $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
            $paginator->setItemCountPerPage($pageSize);
            $paginator->setCurrentPageNumber($page);
            $this->view->paginator = $paginator;
            
        }
        $this->view->form = $form;
    }

    public function crearNuevaSolicitudAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Crear nueva solicitud de anticipo";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
            ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css");
        $this->view->headScript()
            ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
            ->appendFile("/js/trafico/index/crear-nueva-solicitud.js?" . time());
        if (!in_array($this->_session->role, $this->_rolesEditarTrafico)) {
            $this->view->error = "Usted no tiene permisos para consultar esta página.";
        } else {
            $customs = new Application_Model_UsuariosAduanasMapper();
            if (in_array($this->_session->role, $this->_todosClientes)) {
                $model = new Trafico_Model_ClientesMapper();
                $customers = $model->obtenerTodos();
                $m = new Trafico_Model_TraficoAduanasMapper();
                $aduanas = $m->aduanas();
            } else {
                $model = new Trafico_Model_TraficoUsuClientesMapper();
                $customers = $model->obtenerClientes($this->_session->id);
                $aduanas = $customs->aduanasDeUsuario($this->_session->id);
            }
            $form = new Trafico_Form_CrearSolicitud(array("clientes" => $customers, "aduanas" => $aduanas));
            $this->view->form = $form;
        }
    }

    public function solicitudesCorresponsalAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Solicitudes de corresponsal";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
            ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css");
        $this->view->headScript()
            ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
            ->appendFile("/js/trafico/index/solicitudes-corresponsal.js?" . time());
        $mdl = new Application_Model_UsuariosAduanasMapper();
        $aduanas = $mdl->aduanasUsuario($this->_session->id);
        $tbl = new Trafico_Model_TraficoCliAduanasMapper();

        $mppr = new Trafico_Model_TraficoUsuAduanasMapper();
        if (in_array($this->_session->role, $this->_todosClientes)) {
            $customs = $mppr->aduanasDeUsuario();
        } else {
            $customs = $mppr->aduanasDeUsuario($this->_session->id);
        }

        $tipoOperacion = array(
            "TOCE.IMP" => "Importación",
            "TOCE.EXP" => "Exportación",
        );
        if (count($aduanas)) {
            $cli = $tbl->clientesPorAduana($aduanas["patente"], $aduanas["aduana"]);
            $adu = $mdl->aduanasDeUsuario($this->_session->id);
            $form = new Trafico_Form_CrearSolicitud(array("clientes" => $cli, "aduanas" => $adu, "operacion" => $tipoOperacion));
            $this->view->form = $form;
        } else if (count($customs)) {
            $adu['-'] = "---";
            foreach ($customs as $c) {
                $adu[$c['id']] = $c['patente'] . '-' . $c['aduana'] . ' ' . $c["nombre"];
            }
            $cli['-'] = "---";
            $form = new Trafico_Form_CrearSolicitud(array("clientes" => $cli, "aduanas" => $adu, "operacion" => $tipoOperacion));
            $this->view->form = $form;
        } else {
            $this->view->error = "No tiene aduanas asignadas";
        }
    }

    public function editarSolicitudAnticipoAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Editar solicitud anticipo";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
            ->appendFile("/js/trafico/index/editar-solicitud-anticipo.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits"),
        );
        $v = array(
            "id" => array("NotEmpty", new Zend_Validate_Int()),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id")) {
            $mapper = new Trafico_Model_TraficoSolicitudesMapper();
            $arr = $mapper->obtener($input->id);
            if ($arr["autorizada"] === null) {
                if (in_array($this->_session->role, $this->_todosClientes)) {
                    $model = new Trafico_Model_ClientesMapper();
                    $customers = $model->obtenerTodos();
                } else {
                    $model = new Trafico_Model_TraficoUsuClientesMapper();
                    $customers = $model->obtenerClientes($this->_session->id);
                }
                $customs = new Application_Model_UsuariosAduanasMapper();
                if (in_array($this->_session->role, $this->_todosClientes)) {
                    $custom = $customs->aduanasDeUsuario();
                } else {
                    $custom = $customs->aduanasDeUsuario($this->_session->id);
                }
                $form = new Trafico_Form_CrearSolicitud(array("clientes" => $customers, "aduanas" => $custom));
                $form->populate([
                    "aduana" => $arr["idAduana"],
                    "pedimento" => $arr["pedimento"],
                    "referencia" => $arr["referencia"],
                    "operacion" => $arr["tipoOperacion"],
                    "cliente" => $arr["idCliente"],
                ]);
                $form->operacion->setAttrib("disabled", null);
                $form->aduana->setAttrib("readonly", null);
                $form->operacion->addMultiOption("TOCE.IMP", "Importación");
                $form->operacion->addMultiOption("TOCE.EXP", "Exportación");
                $this->view->id = $input->id;
                $this->view->form = $form;
            }
        }
    }

    public function editarSolicitudAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Editar solicitud";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
            ->appendStylesheet("/css/jquery.timepicker.css")
            ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
            ->appendStylesheet("/js/common/bootstrap/bootstrap-datepicker/css/datepicker.css");
        $this->view->headScript()
            ->appendFile("/js/common/jquery.number.min.js")
            ->appendFile("/js/common/zebra_dialog.js")
            ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
            ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js")
            ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js")
            ->appendFile("/js/common/jquery.timepicker.min.js")
            ->appendFile("/js/trafico/index/editar-solicitud.js?" . time());
        if (in_array($this->_session->role, $this->_noTodo)) {
            $this->view->corresponsal = true;
        }
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits"),
            "aduana" => array("Digits"),
        );
        $v = array(
            "id" => array("NotEmpty", new Zend_Validate_Int()),
            "aduana" => array("NotEmpty", new Zend_Validate_Int()),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        $tbl = new Trafico_Model_TraficoSolicitudesMapper();
        if ($input->isValid("id")) {
            $data = $tbl->obtener($input->id);
            if ($data["autorizada"] === null) {
                $tbl = new Trafico_Model_ClientesMapper();
                $datosCliente = $tbl->datosCliente($data["idCliente"]);
                if (isset($datosCliente) && !empty($datosCliente)) {
                    $this->view->datosCliente = $datosCliente;
                }
                $this->view->data = $data;
                $model = new Trafico_Model_TraficoConceptosMapper();
                if (($model->verificarConceptos($data["idAduana"]))) {
                    $conceptos = $model->obtenerConValor($data["idAduana"], $input->id);
                } else {
                    $this->view->warningConceptos = true;
                    $conceptos = $model->obtenerConValor(2, $input->id);
                }
                if (isset($conceptos)) {
                    $this->view->concepts = $conceptos;
                }
                $this->view->aduana = $input->aduana;
                $this->view->id = $input->id;
                $table = new Trafico_Model_TraficoSolDetalleMapper();
                $detalle = $table->obtener($input->id);
                if (isset($detalle) && !empty($detalle)) {
                    $this->view->detalle = $detalle;
                }
                $solcon = new Trafico_Model_TraficoSolConceptoMapper();
                $anticipo = $solcon->anticipo($input->id);
                $subtotal = $solcon->subtotal($input->id);
                $all = $solcon->obtenerTodos($input->id);
                if (!empty($all)) {
                    $this->view->puedeEnviar = true;
                }
                if (isset($anticipo) && $anticipo > 0) {
                    $this->view->anticipo = number_format($anticipo, 2);
                    $this->view->subtotal = number_format($subtotal, 2);
                    $this->view->total = number_format(($subtotal - $anticipo), 2);
                } elseif (isset($data["complemento"])) {
                    $anticipo = $subtotal;
                    $this->view->anticipo = number_format($anticipo, 2);
                    $this->view->subtotal = number_format($subtotal, 2);
                    $this->view->total = number_format(($subtotal - $anticipo), 2);
                } else {
                    $this->view->anticipo = number_format(0, 2);
                    $this->view->subtotal = number_format(0, 2);
                    $this->view->total = number_format($subtotal, 2);
                }
                if (isset($subtotal) && $subtotal > 0) {
                    $this->view->subtotal = number_format($subtotal, 2);
                }
                $form = new Trafico_Form_EditarSolicitud(array("idAduana" => $input->aduana));
                $form->populate(array(
                    "banco" => $detalle["banco"],
                    "almacen" => $detalle["almacen"]
                ));
                $this->view->form = $form;
            }
        }
    }

    public function verSolicitudAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Ver solicitud";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
            ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
            ->appendStylesheet("/css/jqModal.css");
        $this->view->headScript()
            ->appendFile("/js/common/jqModal.js")
            ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
            ->appendFile("/js/trafico/index/ver-solicitud.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits"),
        );
        $v = array(
            "id" => array("NotEmpty", new Zend_Validate_Int()),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if (in_array($this->_session->role, array("super", "gerente", "trafico_ejecutivo", "trafico"))) {
            $this->view->edit = true;
        }
        $this->view->rol = $this->_session->role;
        $table = new Application_Model_UsuariosAduanasMapper();
        $aduanas = $table->aduanasUsuario($this->_session->id);
        if (isset($aduanas) && !empty($aduanas)) {
            if ($input->isValid("id")) {
                $request = new Trafico_Model_TraficoSolicitudesMapper();
                if ($aduanas["patente"][0] != "0" && $aduanas["aduana"][0] != "0") {
                    $header = $request->obtener($input->id, $aduanas["patente"], $aduanas["aduana"]);
                } else {
                    $header = $request->obtener($input->id);
                }
            }
        } else {
            $this->view->error = "No tiene aduanas asignadas.";
        }
        if ($input->isValid("id") && isset($header) && $header !== false) {
            $comments = new Trafico_Model_TraficoSolComentarioMapper();
            $data["header"] = $header;
            $this->view->data = $data;
            $solicitud = new OAQ_SolicitudesAnticipo($input->id);

            $procesos = array(
                "" => "---",
                "1" => "SOLICITAR AUTORIZACIÓN",
                "2" => "EN TESORERÍA",
                "4" => "CANCELADO",
                "5" => "AUTORIZADO HSBC",
                "6" => "AUTORIZADO BANAMEX",
                "10" => "COTIZACIÓN"
            );

            $form = new Trafico_Form_SolicitudTrafico(array("procesos" => $procesos));

            if ($header["tramite"] == 1 && $header["autorizada"] == 2) {
            }
            if ($header["deposito"] == 1) {
                $form->proceso->setMultiOptions(array(
                    3 => "DEPOSITADO",
                    5 => "AUTORIZADO HSBC",
                    6 => "AUTORIZADO BANAMEX",
                ));
            }
            $form->populate(array(
                "idSolicitud" => $input->id,
                "esquema" => $header["esquema"],
                "proceso" => $solicitud->proceso($header["autorizada"], $header["autorizadaHsbc"], $header["autorizadaBanamex"]),
            ));
            if ($solicitud->proceso($header["autorizada"]) == 2) {
            }
            if ($solicitud->proceso($header["autorizada"]) == 3 && $header["deposito"] == 1) {
                $form->esquema->setAttribs(array("disable" => "disable"));
                $form->proceso->setAttribs(array("disable" => "disable"));
                $this->view->disabled = true;
            }
            $this->view->form = $form;
            $this->view->comentarios = $comments->obtenerTodos($input->id);
            $log = new Trafico_Model_BitacoraMapper();
            $this->view->bitacora = $log->obtener($header["patente"], $header["aduana"], $header["pedimento"], $header["referencia"]);
        }
    }

    protected function _arrayValue($value, $array)
    {
        if (isset($array[$value])) {
            return $array[$value];
        }
        return 0;
    }

    public function nuevaSolicitudAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Nueva solicitud de anticipo";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()->appendStylesheet("/css/jquery.selectBoxIt.css")
            ->appendStylesheet("/css/rich_calendar.css")
            ->appendStylesheet("/css/nuevo-estilo.css");
        $this->view->headScript()->appendFile("/js/jquery.form.min.js")
            ->appendFile("/js/jquery.validate.min.js")
            ->appendFile("/js/additional-methods.min.js")
            ->appendFile("/js/jquery-ui-1.9.2.min.js")
            ->appendFile("/js/jquery.selectBoxIt.min.js")
            ->appendFile("/js/rich_calendar.js")
            ->appendFile("/js/rc_lang_en.js")
            ->appendFile("/js/domready.js")
            ->appendFile("/js/calendar.js");
        $id = $this->_getParam("id", null);
        $model = new Trafico_Model_TraficosMapper();
        if (isset($id) && $id != "") {
            $basico = $model->obtenerPorId($id);
            $this->view->basico = $basico;
        }
    }

    public function crearTraficoMultipleAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Crear múltiples tráfico";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
            ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
            ->appendStylesheet("/js/common/bootstrap/datepicker/css/datepicker.css");
        $this->view->headScript()
            ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
            ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js")
            ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js")
            ->appendFile("/js/common/typeahead.min.js")
            ->appendFile("/js/trafico/index/crear-trafico-multiple.js?" . time());
        $mppr = new Trafico_Model_TraficoUsuAduanasMapper();
        if (in_array($this->_session->role, $this->_todosClientes)) {
            $customs = $mppr->aduanasDeUsuario(null, 1);
        } else {
            $customs = $mppr->aduanasDeUsuario($this->_session->id, 1);
        }
        $form = new Trafico_Form_CrearTraficoNew(array("aduanas" => $customs));
        $form->populate(array(
            "cantidad" => 1
        ));
        $this->view->form = $form;
    }

    public function crearTraficoAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Crear tráfico";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
            ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
            ->appendStylesheet("/js/common/bootstrap/datepicker/css/datepicker.css");
        $this->view->headScript()
            ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
            ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js")
            ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js")
            ->appendFile("/js/common/typeahead.min.js")
            ->appendFile("/js/common/loadingoverlay.min.js")
            ->appendFile("/js/trafico/index/crear-trafico.js?" . time());
        $mppr = new Trafico_Model_TraficoUsuAduanasMapper();
        if (in_array($this->_session->role, $this->_todosClientes)) {
            $customs = $mppr->aduanasDeUsuario();
        } else {
            $customs = $mppr->aduanasDeUsuario($this->_session->id);
        }
        if (!empty($customs)) {
            $form = new Trafico_Form_CrearTraficoNew(array("aduanas" => $customs));
        } else {
            $form = new Trafico_Form_CrearTraficoNew();
        }
        $this->view->form = $form;
    }

    public function editarTraficoAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Editar tráfico";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
            ->appendStylesheet("/css/fakeLoader.css")
            ->appendStylesheet("/easyui/themes/default/easyui.css")
            ->appendStylesheet("/easyui/themes/icon.css")
            ->appendStylesheet("/css/jqModal.css")
            ->appendStylesheet("/css/jquery.qtip.min.css")
            ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
            ->appendStylesheet("/js/common/modal/magnific-popup.css")
            ->appendStylesheet("/js/common/highlight/styles/monokai.css")
            ->appendStylesheet("/js/common/bootstrap/datepicker/css/datepicker.css")
            ->appendStylesheet("/js/common/contentxmenu/jquery.contextMenu.min.css")
            ->appendStylesheet("/js/common/bootstrap-datetimepicker-master/css/bootstrap-datetimepicker.min.css")
            ->appendStylesheet("/css/jquery.timepicker.css")
            ->appendStylesheet("/js/common/toast/jquery.toast.min.css");
        $this->view->headScript()
            ->appendFile("/js/common/jquery.timepicker.min.js")
            ->appendFile("/js/common/fakeLoader.min.js")
            ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
            ->appendFile("/js/common/jqModal.js")
            ->appendFile("/js/common/jquery.qtip.min.js")
            ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js")
            ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js")
            ->appendFile("/js/common/contentxmenu/jquery.contextMenu.min.js")
            ->appendFile("/js/common/bootstrap-datetimepicker-master/js/bootstrap-datetimepicker.min.js")
            ->appendFile("/js/common/bootstrap-datetimepicker-master/js/locales/bootstrap-datetimepicker.es.js")
            ->appendFile("/js/common/toast/jquery.toast.min.js?")
            ->appendFile("/easyui/jquery.easyui.min.js")
            ->appendFile("/easyui/jquery.edatagrid.js")
            ->appendFile("/easyui/datagrid-filter.js")
            ->appendFile("/easyui/locale/easyui-lang-es.js")
            ->appendFile("/js/trafico/index/editar-trafico.js?" . time())
            ->appendFile("/js/common/jquery.slidereveal.min.js")
            ->appendFile("/js/common/loadingoverlay.min.js")
            ->appendFile("/js/common/mensajero.js?" . time());
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
            if ($this->_session->role == "inhouse") {
            }
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

            $mpr = new Trafico_Model_TipoCarga();
            $tc = $mpr->obtener($basico['tipoAduana']);
            if (!empty($tc)) {
                $this->view->tipoCargas = $tc;
            } else {
            }
            $this->view->basico = $basico;

            $dates = new Trafico_Model_TraficoFechasMapper();
            $fechas = $dates->obtenerFechas($input->id);
            $this->view->fechas = $fechas;
            $mm = new Trafico_Model_CliSello();
            $sello = $mm->obtener($basico["idCliente"]);
            if (isset($sello) && $sello !== false) {
                $this->view->sello = $sello;
            }
            $obs = new Trafico_Model_TraficoOtrosMapper();
            $otros = $obs->obtener($input->id);
            $this->view->otros = $otros;
            $repo = new Archivo_Model_RepositorioMapper();
            if (!($repo->buscarTipoArchivo($basico["patente"], $basico["aduana"], $basico["referencia"], 32))) {
                $this->view->printUrl = "/automatizacion/vucem/print-pedimento-sitawin?patente={$basico["patente"]}&aduana={$basico["aduana"]}&pedimento=" . $basico["pedimento"];
            }
            if (!($repo->buscarTipoArchivo($basico["patente"], $basico["aduana"], $basico["referencia"], 33))) {
                $this->view->printSimpUrl = "/automatizacion/vucem/print-pedimento-simplificado-sitawin?patente={$basico["patente"]}&aduana={$basico["aduana"]}&pedimento=" . $basico["pedimento"];
            }
            $tiposFechas = new Trafico_Model_TraficoFechasAduanaMapper();
            $json = $tiposFechas->obtener($basico["tipoAduana"], $basico["ie"], $basico["cvePedimento"]);
            $arrFechas = json_decode($json["fechas"], true);
            $this->view->tiposFechas = $arrFechas;

            $pmppr = new Trafico_Model_ClientesPlantas();
            $plantas = $pmppr->obtener($basico['idCliente']);
            if (!empty(($plantas))) {
                $this->view->plantas = $plantas;
            } else {
                $this->view->plantas = null;
            }
        }
    }

    public function modificarTraficoAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Modificar trafico";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
            ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
            ->appendStylesheet("/css/jqModal.css");
        $this->view->headScript()
            ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
            ->appendFile("/js/trafico/index/modificar-trafico.js?" . time());
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

            if ($row["pagado"] === null || $this->_session->role == "super") {
                $mapper = new Trafico_Model_TraficoUsuAduanasMapper();
                if (in_array($this->_session->role, $this->_todosClientes)) {
                    $customs = $mapper->aduanasDeUsuario();
                } else {
                    $customs = $mapper->aduanasDeUsuario($this->_session->id);
                }

                $pmppr = new Trafico_Model_ClientesPlantas();
                $plantas = $pmppr->obtener($row['idCliente']);
                //                if (!empty(($plantas))) {
                //                    $this->view->plantas = $plantas;
                //                } else {
                //                    $this->view->plantas = null;
                //                }

                $form = new Trafico_Form_CrearTraficoNew(array("aduanas" => $customs, "aduana" => $row["idAduana"]));
                $form->populate(array(
                    "aduana" => $row["idAduana"],
                    "cliente" => $row["idCliente"],
                    "pedimento" => $row["pedimento"],
                    "referencia" => $row["referencia"],
                    "tipoCambio" => $row["tipoCambio"],
                    "operacion" => $row["ie"],
                    "cvePedimento" => $row["cvePedimento"],
                    "consolidado" => $row["consolidado"],
                    "rectificacion" => $row["rectificacion"],
                    "contenedorCaja" => $row["contenedorCaja"],
                    "nombreBuque" => $row["nombreBuque"],
                    "idRepositorio" => $row["idRepositorio"],
                ));
                if (isset($row["rfcSociedad"])) {
                    $this->view->rfcSociedad = $row["rfcSociedad"];
                }
                $form->cliente->setAttrib("disabled", null);
                $this->view->form = $form;
            }
            $this->view->idTrafico = $input->id;
        }
    }

    public function traficoAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Trafico";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
            ->appendStylesheet("/css/jquery.qtip.min.css")
            ->appendStylesheet("/js/common/bootstrap/datepicker/css/datepicker.css")
            ->appendStylesheet("/css/jquery.timepicker.css")
            ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
            ->appendStylesheet("/css/jqModal.css");
        $this->view->headScript()
            ->appendFile("/js/common/jqModal.js")
            ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
            ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js")
            ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js")
            ->appendFile("/js/common/jquery.qtip.min.js")
            ->appendFile("/js/trafico/index/trafico.js?" . time())
            ->appendFile("/js/common/mensajero.js?" . time());
        $request = new Zend_Controller_Request_Http();
        $all = filter_var($request->getCookie("allOperations"), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $pagadas = filter_var($request->getCookie("pagadas"), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $liberadas = filter_var($request->getCookie("liberadas"), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $impos = filter_var($request->getCookie("impos"), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $expos = filter_var($request->getCookie("expos"), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "page" => array("Digits"),
            "size" => array("Digits"),
            "buscar" => array("StringToUpper"),
            "idAduana" => array("Digits"),
            "cvePedimento" => array("StringToUpper"),
        );
        $v = array(
            "page" => array(new Zend_Validate_Int(), "default" => 1),
            "size" => array(new Zend_Validate_Int(), "default" => 20),
            "buscar" => "NotEmpty",
            "cvePedimento" => "NotEmpty",
            "idAduana" => array("NotEmpty", new Zend_Validate_Int()),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        $model = new Trafico_Model_TraficosMapper();
        $form = new Trafico_Form_BuscarSolicitud();
        $mapper = new Trafico_Model_TraficoAduanasMapper();
        $referencias = new OAQ_Referencias();
        $res = $referencias->restricciones($this->_session->id, $this->_session->role);
        if (!in_array($this->_session->role, array("inhouse"))) {
            if (!empty($res["idsAduana"])) {
                if (in_array($this->_session->role, array("super", "trafico_operaciones", "trafico", "trafico_aero", "trafico_ejecutivo", "gerente"))) {
                    $this->view->filters = $mapper->obtenerTodas($res["idsAduana"]);
                    $this->view->idAduana = ($input->isValid("idAduana")) ? $input->idAduana : null;
                }
                $form->populate(array(
                    "buscar" => $input->isValid("buscar") ? $input->buscar : null,
                    "cvePedimento" => $input->isValid("cvePedimento") ? $input->cvePedimento : null,
                ));
                $arr = $model->obtenerTraficos($input->buscar, ($input->isValid("idAduana")) ? $input->idAduana : $res["idsAduana"], ($all === false) ? $this->_session->id : null, $pagadas, $liberadas, null, $impos, $expos, null, null, $input->cvePedimento);
            }
        } else { // inhouse
            $this->view->filters = $mapper->obtenerTodas();
            $arr = $model->obtenerTraficos($input->buscar, ($input->isValid("idAduana")) ? $input->idAduana : null, null, $pagadas, $liberadas, $res["rfcs"], $impos, $expos, null, null, $input->cvePedimento);
        }
        $this->view->form = $form;
        if (isset($arr) && !empty($arr)) {
            $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array($arr));
            $paginator->setItemCountPerPage($input->size);
            $paginator->setCurrentPageNumber($input->page);
            $this->view->paginator = $paginator;
        }
    }

    public function clientesAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Clientes";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
            ->appendStylesheet("/css/jqModal.css");
        $this->view->headScript()
            ->appendFile("/js/common/jqModal.js")
            ->appendFile("/js/trafico/index/clientes.js?" . time());
        $request = new Zend_Controller_Request_Http();
        $filter = $request->getCookie("filter");
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "page" => "Digits",
            "size" => "Digits",
            "busqueda" => array("StringToUpper"),
        );
        $v = array(
            "page" => array(new Zend_Validate_Int(), "default" => 1),
            "size" => array(new Zend_Validate_Int(), "default" => 20),
            "busqueda" => "NotEmpty",
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        $model = new Trafico_Model_ClientesMapper();
        $alerts = new Trafico_Model_ClientesAlertas();
        $this->view->alertas = $alerts->ultimaActividad();
        if ($input->isValid("busqueda")) {
            $data = $model->busqueda(html_entity_decode($input->busqueda));
            $this->view->busqueda = $input->busqueda;
        } else {
            $data = $model->obtener(false, $filter);
        }
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array($data));
        $paginator->setItemCountPerPage($input->size);
        $paginator->setCurrentPageNumber($input->page);
        $this->view->paginator = $paginator;
    }

    public function datosClienteAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Datos cliente";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
            ->appendStylesheet("/css/jqModal.css")
            ->appendStylesheet("/easyui/themes/default/easyui.css")
            ->appendStylesheet("/easyui/themes/icon.css")
            ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
            ->appendStylesheet("/js/common/toast/jquery.toast.min.css")
            ->appendStylesheet("/js/common/bootstrap/datepicker/css/datepicker.css");
        $this->view->headScript()
            ->appendFile("/js/common/typeahead.min.js")
            ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
            ->appendFile("/js/common/toast/jquery.toast.min.js?")
            ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js")
            ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js")
            ->appendFile("/js/common/jquery.dataTables.min.js")
            ->appendFile("/easyui/jquery.easyui.min.js")
            ->appendFile("/easyui/jquery.edatagrid.js")
            ->appendFile("/easyui/datagrid-filter.js")
            ->appendFile("/easyui/locale/easyui-lang-es.js")
            ->appendFile("/js/common/jqModal.js")
            ->appendFile("/js/common/loadingoverlay.min.js")
            ->appendFile("/js/trafico/index/datos-cliente.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits"),
        );
        $v = array(
            "id" => array("NotEmpty", new Zend_Validate_Int()),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id")) {
            $model = new Trafico_Model_ClientesMapper();
            $this->view->id = $input->id;

            $data = $model->datosCliente($input->id);
            $this->view->data = $data;

            $adu = new Trafico_Model_TraficoCliAduanasMapper();
            $aduanas = $adu->clienteAduanas($input->id);
            $this->view->aduanas = $aduanas;

            $tbl = new Trafico_Model_ContactosCliMapper();
            $this->view->contactos = $tbl->obtenerTodos($input->id);

            $mdl = new Vucem_Model_VucemClientesMapper();
            $address = $mdl->datosCliente($data["rfc"]);
            $this->view->token = sha1("dss78454" . $data["rfc"] . "oaq2013*");
            $this->view->direccion = $address;
            $formAddress = new Trafico_Form_DireccionCliente();
            $formAddress->populate(array(
                "id" => $address["id"],
                "idCliente" => $input->id,
                "rfcCliente" => $data["rfc"],
                "cvecte" => $address["cvecte"],
                "razon_soc" => $address["razon_soc"],
                "calle" => $address["calle"],
                "numext" => $address["numext"],
                "numint" => $address["numint"],
                "colonia" => $address["colonia"],
                "localidad" => $address["localidad"],
                "cp" => $address["cp"],
                "municipio" => $address["municipio"],
                "estado" => $address["estado"],
                "pais" => $address["pais"],
            ));
            $this->view->address = $formAddress;
            $dom = new Trafico_Model_ClientesDom();
            if (!($dom->verificar($input->id))) {
                $dom->agregarPersonalizado($input->id, 1, $data["rfc"], $address["razon_soc"], $address["calle"], $address["numext"], $address["numint"], $address["colonia"], $address["localidad"], $address["municipio"], null, $address["cp"], $address["pais"]);
            }
            $rfc = new Trafico_Model_RfcConsultaMapper();
            $r = $rfc->obtener($input->id);
            if (isset($r) && !empty($r)) {
                $this->view->rfcConsulta = $r;
            }
            $formc = new Trafico_Form_EditarCliente(array("idCliente" => $input->id));
            $this->view->form = $formc;
            $this->view->backUrl = "/trafico/index/clientes";
            $tipos = new Trafico_Model_TipoCliente();
            $this->view->tipoCliente = $tipos->obtenerTodos();
            $esquemas = new Trafico_Model_EsquemaFondos();
            $this->view->esquemaFondos = $esquemas->obtenerTodos(true);
            $formAccess = new Comercializacion_Form_DatosCliente();
            $formAccess->populate(array(
                "sicaId" => $model->sica($input->id),
                "password" => $model->accesoPortal($input->id),
                "webaccess" => ($model->accesoPortal($input->id)) ? 1 : 0,
                "dashboard" => $model->accesoDashboard($input->id),
            ));
            if ($model->accesoDashboard($input->id)) {
                $urlDashboard = "http://localhost:8090/dashboard/main?code=" . $model->accesoDashboard($input->id);
                if (APPLICATION_ENV == "production") {
                    $urlDashboard = "https://192.168.200.11/dashboard/main?code=" . $model->accesoDashboard($input->id);
                } else if (APPLICATION_ENV == "staging") {
                    $urlDashboard = "http://192.168.0.191/dashboard/main?code=" . $model->accesoDashboard($input->id);
                }
                $this->view->dashboard = $urlDashboard;
            }
            $tarifas = new Trafico_Model_Tarifas();
            $this->view->tarifas = $tarifas->obtenerTarifasCliente($input->id);
            $this->view->formAccess = $formAccess;

            $alerts = new Trafico_Model_ClientesAlertas();
            $this->view->alertas = $alerts->ultimaActividad($input->id);

            $mppr = new Trafico_Model_ClientesPlantas();
            $arr = $mppr->obtener($input->id);
            $this->view->plantas = $arr;
        }
    }

    public function oficinasAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Oficinas y corresponsales";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
            ->appendStylesheet("/css/DT_bootstrap.css");
        $this->view->headScript()
            ->appendFile("/js/common/jquery.dataTables.min.js")
            ->appendFile("/js/common/DT_bootstrap.js")
            ->appendFile("/js/trafico/index/oficinas.js?" . time());
        $model = new Trafico_Model_TraficoAduanasMapper();
        $data = $model->obtener();
        $this->view->data = $data;
    }

    public function datosOficinaAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Datos oficina";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
            ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
            ->appendStylesheet("/js/common/toast/jquery.toast.min.css");
        $this->view->headScript()
            ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
            ->appendFile("/js/common/toast/jquery.toast.min.js?")
            ->appendFile("/js/common/js.cookie.js")
            ->appendFile("/js/trafico/index/datos-oficina.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits"),
        );
        $v = array(
            "id" => array("NotEmpty", new Zend_Validate_Int()),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id")) {
            $this->view->idAduana = $input->id;
            $mapper = new Trafico_Model_TraficoAduanasMapper();
            $arr = $mapper->obtenerAduana($input->id);
            if ((int) $arr["tipoAduana"] == 1) {
                $this->view->nombreNavieras = "Líneas aéreas";
            }
            if (isset($arr) && !empty($arr)) {
                $this->view->data = $arr;
            }
            $form = new Trafico_Form_EditarOficina(array("idAduana" => $input->id));
            $this->view->form = $form;
            $this->view->backUrl = "/trafico/index/oficinas";
            $bancos = new Rrhh_Model_EmpleadosBancos();
            $this->view->bancos = $bancos->obtenerOpciones();
            $dates = new Trafico_Model_TraficoFechasAduanaMapper();
            $fechas = $dates->obtenerPorAduana($arr["tipoAduana"]);
            $this->view->tiposFechas = $fechas;
        } else {
            throw new Exception("Invalid input!");
        }
    }

    public function layoutAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Layout example";
        $this->view->headMeta()->appendName("description", "");
    }

    public function mejoraAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Nuevo cliente";
        $this->view->headMeta()->appendName("description", "");
    }

    public function nuevoClienteAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Nuevo cliente";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
            ->appendFile("/js/trafico/index/nuevo-cliente.js?" . time());
        $form = new Trafico_Form_NuevoCliente();
        $this->view->form = $form;
        $mppr = new Application_Model_UsuariosEmpresas();
        $arr = $mppr->empresas();
        if (isset($arr) && !empty($arr)) {
            $this->view->empresas = $arr;
        }
    }

    public function editarOficinaAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Editar oficina";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
            ->appendStylesheet("/js/common/toast/jquery.toast.min.css")
            ->appendStylesheet("/css/jquery.qtip.min.css");
        $this->view->headScript()
            ->appendFile("/js/common/toast/jquery.toast.min.js?")
            ->appendFile("/js/common/typeahead.min.js")
            ->appendFile("/js/common/jquery.qtip.min.js")
            ->appendFile("/js/trafico/index/nueva-oficina.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits"),
        );
        $v = array(
            "id" => array("NotEmpty", new Zend_Validate_Int()),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id")) {
            $mapper = new Trafico_Model_TraficoAduanasMapper();
            $arr = $mapper->aduana($input->id);
            $form = new Trafico_Form_NuevaOficina();
            $form->populate(array(
                "patente" => $arr["patente"],
                "aduana" => $arr["aduana"],
                "tipoAduana" => $arr["tipoAduana"],
                "corresponsal" => $arr["corresponsal"],
            ));
            $this->view->form = $form;
            $this->view->id = $input->id;
            $this->render("nueva-oficina");
        }
    }

    public function nuevaOficinaAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Nueva oficina";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
            ->appendStylesheet("/js/common/toast/jquery.toast.min.css")
            ->appendStylesheet("/css/jquery.qtip.min.css");
        $this->view->headScript()
            ->appendFile("/js/common/toast/jquery.toast.min.js?")
            ->appendFile("/js/common/typeahead.min.js")
            ->appendFile("/js/common/jquery.qtip.min.js")
            ->appendFile("/js/trafico/index/nueva-oficina.js?" . time());
        $form = new Trafico_Form_NuevaOficina();
        $this->view->form = $form;
    }

    public function agentesAduanalesAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Agentes Aduanales";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
            ->appendStylesheet("/css/DT_bootstrap.css")
            ->appendStylesheet("/css/jquery.qtip.min.css");
        $this->view->headScript()
            ->appendFile("/js/common/typeahead.min.js")
            ->appendFile("/js/common/jquery.qtip.min.js")
            ->appendFile("/js/common/jquery.dataTables.min.js")
            ->appendFile("/js/common/DT_bootstrap.js")
            ->appendFile("/js/trafico/index/agentes-aduanales.js?" . time());
        $mapper = new Trafico_Model_Agentes();
        $this->view->data = $mapper->todos();
    }

    public function editarAgenteAduanalAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Editar Agente Aduanal";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
            ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
            ->appendStylesheet("/js/common/toast/jquery.toast.min.css")
            ->appendStylesheet("/css/jquery.qtip.min.css");
        $this->view->headScript()
            ->appendFile("/js/common/toast/jquery.toast.min.js?")
            ->appendFile("/js/common/typeahead.min.js")
            ->appendFile("/js/common/jquery.qtip.min.js")
            ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
            ->appendFile("/js/trafico/index/editar-agente-aduanal.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits"),
        );
        $v = array(
            "id" => array("NotEmpty", new Zend_Validate_Int()),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id")) {
            $mapper = new Trafico_Model_Agentes();
            $arr = $mapper->obtener($input->id);
            $form = new Trafico_Form_NuevoAgente();
            $form->populate(array(
                "patente" => $arr["patente"],
                "rfc" => $arr["rfc"],
                "nombre" => $arr["nombre"],
            ));
            $this->view->idAgente = $input->id;
            $this->view->form = $form;
        }
    }

    public function agregarAgenteAduanalAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Agregar Agente Aduanal";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
            ->appendStylesheet("/css/jquery.qtip.min.css");
        $this->view->headScript()
            ->appendFile("/js/common/typeahead.min.js")
            ->appendFile("/js/common/jquery.qtip.min.js")
            ->appendFile("/js/trafico/index/agregar-agente-aduanal.js?" . time());
        $form = new Trafico_Form_NuevoAgente();
        $this->view->form = $form;
    }

    public function catalogosAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Catalogos";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
            ->appendFile("/js/trafico/index/catalogos.js?" . time());
    }

    public function reportesEnhAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Reportes";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
            ->appendStylesheet("/easyui/themes/default/easyui.css")
            ->appendStylesheet("/easyui/themes/icon.css");
        $this->view->headScript()
            ->appendFile("/easyui/jquery.easyui.min.js")
            ->appendFile("/easyui/jquery.edatagrid.js")
            ->appendFile("/easyui/datagrid-filter.js")
            ->appendFile("/easyui/locale/easyui-lang-es.js")
            ->appendFile("/js/trafico/index/reportes-enh.js?" . time());
        if ($this->_session->role == "super") {
            $this->view->menur = array(
                "1" => "Traficos",
                "2" => "Candados",
                "3" => "Rojos",
                "4" => "Traficos Incompletos",
                "5" => "Traficos Aéreos",
                "6" => "Traficos Marítimos",
                "7" => "Traficos Operaciones Especiales",
                "8" => "Traficos Terrestre",
                "10" => "Detalle de cuenta por pagar (Marron)",
                "11" => "Detalle de cuenta por pagar (Asociación)",
                "12" => "Reporte de tiempos facturación vs pedimento (SICA)",
                "13" => "Inventario de tráficos",
                "14" => "Reporte facturación",
                "70" => "Reporte COVE",
                "71" => "Reporte EDocuments",
                "72" => "Reporte Indicadores",
                "73" => "Reporte Repositorio Estatus MV / HC",
                "74" => "Reporte pendientes de facturar",
                "75" => "Reporte tráfico y facturación",
                "76" => "Reporte entrega de expedientes",
                "77" => "Sellos de agentes",
                "78" => "Sellos de clientes",
            );
        } else if ($this->_session->role == "super_admon") {
            $this->view->menur = array(
                "1" => "Traficos",
                "2" => "Candados",
                "3" => "Rojos",
                "10" => "Detalle de cuenta por pagar (Marron)",
                "11" => "Detalle de cuenta por pagar (Asociación)",
                "12" => "Reporte de tiempos facturación vs pedimento (SICA)",
                "13" => "Inventario de tráficos",
                "14" => "Reporte facturación",
                "74" => "Reporte pendientes de facturar",
                "73" => "Reporte Repositorio Estatus MV / HC",
                "75" => "Reporte tráfico y facturación",
                "77" => "Sellos de agentes",
                "78" => "Sellos de clientes",
            );
        } else if ($this->_session->role == "trafico_ejecutivo" || $this->_session->role == "trafico") {
            $this->view->menur = array(
                "1" => "Traficos",
                "2" => "Candados",
                "3" => "Rojos",
                "4" => "Traficos Incompletos",
                "5" => "Traficos Aéreos",
                "6" => "Traficos Marítimos",
                "7" => "Traficos Operaciones Especiales",
                "8" => "Traficos Terrestre",
                "70" => "Reporte COVE",
                "71" => "Reporte EDocuments",
                "73" => "Reporte Repositorio Estatus MV / HC",
                "76" => "Reporte entrega de expedientes",
                "77" => "Sellos de agentes",
                "78" => "Sellos de clientes",
            );
        } else if ($this->_session->role == "gerente") {
            $this->view->menur = array(
                "1" => "Traficos",
                "2" => "Candados",
                "3" => "Rojos",
                "4" => "Traficos Incompletos",
                "5" => "Traficos Aéreos",
                "6" => "Traficos Marítimos",
                "7" => "Traficos Operaciones Especiales",
                "8" => "Traficos Terrestre",
                "70" => "Reporte COVE",
                "71" => "Reporte EDocuments",
                "72" => "Reporte Indicadores",
                "73" => "Reporte Repositorio Estatus MV / HC",
                "74" => "Reporte pendientes de facturar",
                "75" => "Reporte tráfico y facturación",
                "76" => "Reporte entrega de expedientes",
                "77" => "Sellos de agentes",
                "78" => "Sellos de clientes",
            );
        } else {
            $this->view->menur = array(
                "1" => "Traficos",
                "2" => "Candados",
                "3" => "Rojos",
                "4" => "Traficos Incompletos",
                "5" => "Traficos Aéreos",
                "6" => "Traficos Marítimos",
                "7" => "Traficos Operaciones Especiales",
                "8" => "Traficos Terrestre",
                "13" => "Inventario de tráficos",
                "73" => "Reporte Repositorio Estatus MV / HC",
                "74" => "Reporte pendientes de facturar",
                "76" => "Reporte entrega de expedientes",
                "77" => "Sellos de agentes",
                "78" => "Sellos de clientes",
            );
        }
    }

    public function verFolioAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Ver folio";
        $this->view->headMeta()->appendName("description", "");
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits"),
        );
        $v = array(
            "id" => array("NotEmpty", new Zend_Validate_Int()),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id")) {
            $mppr = new Automatizacion_Model_RptCuentas();
            $mapper = new Automatizacion_Model_RptCuentaConceptos();
            $arr = $mppr->folio($input->id);

            $arrc = $mapper->conceptos($arr['id']);
            if (!empty($arrc)) {
                $arr['conceptos'] = $arrc;
            } else {
                $arr['conceptos'] = null;
            }
            $this->view->invoice = $arr;
        }
    }

    public function nuevaTarifaAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Nueva Tarifa";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
            ->appendFile("/js/trafico/index/nueva-tarifa.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits"),
            "idCliente" => array("Digits"),
        );
        $v = array(
            "id" => array("NotEmpty", new Zend_Validate_Int()),
            "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id")) {
            $this->view->idTarifa = $input->id;
        }
        $customers = new Trafico_Model_ClientesMapper();
        $clientes = $customers->obtenerTodos();
        $mapper = new Trafico_Model_TraficoAduanasMapper();
        $arrAereas = $mapper->obtenerActivas(null, 2); // 1 ops esp, 2 aerea, 3 maritima, 4 terrestre, 5 ferro
        $arrMaritimas = $mapper->obtenerActivas(null, 3);
        $arrTerrestre = $mapper->obtenerActivas(null, 4);
        $arrEspeciales = $mapper->obtenerActivas(null, 1);
        $conceptos = new Trafico_Model_TarifaConceptos();
        $arrConceptos = $conceptos->obtenerTodos(1);
        $arrOtros = $conceptos->obtenerTodos(2);
        $vigencias = new Trafico_Model_TarifaVigencias();
        $arrVigencias = $vigencias->obtenerTodos();
        $form = new Trafico_Form_Tarifa(array("idCliente" => $clientes, "vigencias" => $arrVigencias, "aereas" => $arrAereas, "maritimas" => $arrMaritimas, "terrestres" => $arrTerrestre, "especiales" => $arrEspeciales, "conceptos" => $arrConceptos, "otros" => $arrOtros));
        if ($input->isValid("idCliente")) {
            $form->idCliente->setValue($input->idCliente);
        }
        $this->view->form = $form;
        $notes = new Trafico_Model_TarifaNotasGenerales();
        $this->view->notas = $notes->obtenerTodos();
    }

    public function traficosV2Action()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Tráficos V2";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
            ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
            ->appendStylesheet("/easyui/themes/default/easyui.css")
            ->appendStylesheet("/easyui/themes/icon.css")
            ->appendStylesheet("/js/common/popmodal/popModal.min.css")
            ->appendStylesheet("/js/common/bootstrap/datepicker/css/datepicker.css")
            ->appendStylesheet("/js/common/contentxmenu/jquery.contextMenu.min.css");
        $this->view->headScript()
            ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
            ->appendFile("/easyui/jquery.easyui.min.js")
            ->appendFile("/easyui/jquery.edatagrid.js")
            ->appendFile("/easyui/datagrid-filter.js")
            ->appendFile("/easyui/locale/easyui-lang-es.js")
            ->appendFile("/fullcalendar/lib/moment.min.js")
            ->appendFile("/js/common/popmodal/popModal.js")
            ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js")
            ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js")
            ->appendFile("/js/common/contentxmenu/jquery.contextMenu.min.js")
            ->appendFile("/js/common/mensajero.js?" . time());
        if ($this->_session->role == "inhouse") {
            $this->view->headScript()->appendFile("/js/trafico/index/traficos-inhouse.js?" . time());
        } else {
            $this->view->headScript()->appendFile("/js/trafico/index/traficos-v2.js?" . time());
        }
    }

    public function traficosAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Tráficos";
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
            ->appendFile("/js/common/mensajero.js?" . time());
        if ($this->_session->role == "inhouse") {
            $this->view->headScript()->appendFile("/js/trafico/index/trafico-common.js?" . time())
                ->appendFile("/js/trafico/index/traficos-inhouse.js?" . time());
        } else {
            $this->view->headScript()->appendFile("/js/trafico/index/trafico-common.js?" . time());
            $this->view->headScript()->appendFile("/js/trafico/index/traficos.js?" . time());
        }
    }

    public function traficoAereoAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Tráfico Aéreo";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
            ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
            ->appendStylesheet("/easyui/themes/default/easyui.css")
            ->appendStylesheet("/easyui/themes/icon.css");
        $this->view->headScript()
            ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
            ->appendFile("/easyui/jquery.easyui.min.js")
            ->appendFile("/easyui/jquery.edatagrid.js")
            ->appendFile("/easyui/datagrid-filter.js")
            ->appendFile("/easyui/locale/easyui-lang-es.js")
            ->appendFile("/fullcalendar/lib/moment.min.js")
            ->appendFile("/js/common/mensajero.js?" . time());
        if ($this->_session->role == "inhouse") {
            $this->view->headScript()->appendFile("/js/trafico/index/traficos-inhouse.js?" . time());
        } else {
            $this->view->headScript()->appendFile("/js/trafico/index/trafico-aereo.js?" . time());
        }
    }

    public function traficoTerrestreAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Tráfico Terrestre";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
            ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
            ->appendStylesheet("/easyui/themes/default/easyui.css")
            ->appendStylesheet("/easyui/themes/icon.css");
        $this->view->headScript()
            ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
            ->appendFile("/easyui/jquery.easyui.min.js")
            ->appendFile("/easyui/jquery.edatagrid.js")
            ->appendFile("/easyui/datagrid-filter.js")
            ->appendFile("/easyui/locale/easyui-lang-es.js")
            ->appendFile("/fullcalendar/lib/moment.min.js")
            ->appendFile("/js/common/mensajero.js?" . time());
        if ($this->_session->role == "inhouse") {
            $this->view->headScript()->appendFile("/js/trafico/index/traficos-inhouse.js?" . time());
        } else {
            $this->view->headScript()->appendFile("/js/trafico/index/trafico-terrestre.js?" . time());
        }
    }

    public function traficoMaritimoAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Tráfico Marítimo";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
            ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
            ->appendStylesheet("/easyui/themes/default/easyui.css")
            ->appendStylesheet("/easyui/themes/icon.css");
        $this->view->headScript()
            ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
            ->appendFile("/easyui/jquery.easyui.min.js")
            ->appendFile("/easyui/jquery.edatagrid.js")
            ->appendFile("/easyui/datagrid-filter.js")
            ->appendFile("/easyui/locale/easyui-lang-es.js")
            ->appendFile("/fullcalendar/lib/moment.min.js")
            ->appendFile("/js/common/mensajero.js?" . time());
        if ($this->_session->role == "inhouse") {
            $this->view->headScript()->appendFile("/js/trafico/index/traficos-inhouse.js?" . time());
        } else {
            $this->view->headScript()->appendFile("/js/trafico/index/trafico-maritimo.js?" . time());
        }
    }

    public function traficoOpsEspecialesAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Tráfico Operaciones Especiales";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
            ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
            ->appendStylesheet("/easyui/themes/default/easyui.css")
            ->appendStylesheet("/easyui/themes/icon.css");
        $this->view->headScript()
            ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
            ->appendFile("/easyui/jquery.easyui.min.js")
            ->appendFile("/easyui/jquery.edatagrid.js")
            ->appendFile("/easyui/datagrid-filter.js")
            ->appendFile("/easyui/locale/easyui-lang-es.js")
            ->appendFile("/fullcalendar/lib/moment.min.js")
            ->appendFile("/js/common/mensajero.js?" . time());
        if ($this->_session->role == "inhouse") {
            $this->view->headScript()->appendFile("/js/trafico/index/traficos-inhouse.js?" . time());
        } else {
            $this->view->headScript()->appendFile("/js/trafico/index/trafico-ops-especiales.js?" . time());
        }
    }

    public function editarTarifaAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Editar Tarifa";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
            ->appendFile("/js/trafico/index/nueva-tarifa.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits"),
        );
        $v = array(
            "id" => array("NotEmpty", new Zend_Validate_Int()),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id")) {
            $customers = new Trafico_Model_ClientesMapper();
            $clientes = $customers->obtenerTodos();
            $mapper = new Trafico_Model_TraficoAduanasMapper();
            $arrAereas = $mapper->obtenerActivas(null, 2); // 1 ops esp, 2 aerea, 3 maritima, 4 terrestre, 5 ferro
            $arrMaritimas = $mapper->obtenerActivas(null, 3);
            $arrTerrestre = $mapper->obtenerActivas(null, 4);
            $arrEspeciales = $mapper->obtenerActivas(null, 1);
            $conceptos = new Trafico_Model_TarifaConceptos();
            $arrConceptos = $conceptos->obtenerTodos(1);
            $arrOtros = $conceptos->obtenerTodos(2);
            $vigencias = new Trafico_Model_TarifaVigencias();
            $arrVigencias = $vigencias->obtenerTodos();
            $form = new Trafico_Form_Tarifa(array("id" => $input->id, "idCliente" => $clientes, "vigencias" => $arrVigencias, "aereas" => $arrAereas, "maritimas" => $arrMaritimas, "terrestres" => $arrTerrestre, "especiales" => $arrEspeciales, "conceptos" => $arrConceptos, "otros" => $arrOtros));
            $this->view->form = $form;
            $notes = new Trafico_Model_TarifaNotasGenerales();
            $this->view->notas = $notes->obtenerTodos();
            $this->render("nueva-tarifa");
        }
    }

    public function graficasAction()
    {
        setlocale(LC_TIME, 'es_ES','es_ES.UTF-8');

        $this->view->title = $this->_appconfig->getParam("title") . " Gráficas";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
            ->appendFile("/js/common/highcharts/js/highcharts.js")
            ->appendFile("/js/common/highcharts/js/modules/data.js")
            ->appendFile("/js/common/highcharts/js/modules/exporting.js")
            ->appendFile("/js/trafico/index/graficas.js?" . time());

        $months = array(
            1 => "Enero",
            2 => "Febrero",
            3 => "Marzo",
            4 => "Abril",
            5 => "Mayo",
            6 => "Junio",
            7 => "Julio",
            8 => "Agosto",
            9 => "Septiembre",
            10 => "Octubre",
            11 => "Noviembre",
            12 => "Diciembre",
        );

        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "year" => array("Digits"),
            "month" => array("Digits"),
            "idCliente" => array("Digits"),
            "idAduana" => array("Digits"),
        );
        $v = array(
            "year" => array("NotEmpty", new Zend_Validate_Int(), "default" => date("Y")),
            "month" => array("NotEmpty", new Zend_Validate_Int(), "default" => date("m")),
            "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
            "idAduana" => array("NotEmpty", new Zend_Validate_Int()),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());

        $this->view->year = $input->isValid("year") ? $input->year : (int) date("Y");
        $this->view->month = $input->isValid("month") ? $months[$input->month] : $months[(int) date("m")];

        $mapper = new Trafico_Model_TraficosReportes();

        $year = (int) date("Y");
        if ($input->isValid("year")) {
            $year = $input->year;
        }

        $month = (int) date("m");
        if ($input->isValid("month")) {
            $month = $input->month;
        }
        $this->view->month = $input->month;

        $this->view->idCliente = $input->isValid('idCliente') ? $input->idCliente : null;
        $this->view->idAduana = $input->isValid('idAduana') ? $input->idAduana : null;

        $arr = $mapper->obtenerPagadosGrafica($year, $input->idCliente, $input->idAduana);
        foreach ($arr as $value) {
            $arrp[] = (int) $value;
        }

        $arr_p = $mapper->obtenerPagadosGrafica($year - 1, $input->idCliente, $input->idAduana);
        foreach ($arr_p as $value) {
            $arrp_p[] = (int) $value;
        }

        $this->view->pagados = $arrp;
        $this->view->pagados_p = $arrp_p;

        if (isset($arr) && !empty($arr)) {
            $graph = array(
                "name" => "Pedimentos " . $input->year,
                "colorByPoint" => "true",
                "data" => array(
                    (int) $arr["Ene"] ? (int) $arr["Ene"] : null,
                    (int) $arr["Feb"] ? (int) $arr["Feb"] : null,
                    (int) $arr["Mar"] ? (int) $arr["Mar"] : null,
                    (int) $arr["Abr"] ? (int) $arr["Abr"] : null,
                    (int) $arr["May"] ? (int) $arr["May"] : null,
                    (int) $arr["Jun"] ? (int) $arr["Jun"] : null,
                    (int) $arr["Jul"] ? (int) $arr["Jul"] : null,
                    (int) $arr["Ago"] ? (int) $arr["Ago"] : null,
                    (int) $arr["Sep"] ? (int) $arr["Sep"] : null,
                    (int) $arr["Oct"] ? (int) $arr["Oct"] : null,
                    (int) $arr["Nov"] ? (int) $arr["Nov"] : null,
                    (int) $arr["Dic"] ? (int) $arr["Dic"] : null
                )
            );
            $this->view->arr = json_encode($graph);
        }

        if (isset($arr_p) && !empty($arr_p)) {
            $graph_p = array(
                "name" => "Pedimentos 2019",
                "colorByPoint" => "true",
                "data" => array(
                    (int) $arr_p["Ene"] ? (int) $arr_p["Ene"] : null,
                    (int) $arr_p["Feb"] ? (int) $arr_p["Feb"] : null,
                    (int) $arr_p["Mar"] ? (int) $arr_p["Mar"] : null,
                    (int) $arr_p["Abr"] ? (int) $arr_p["Abr"] : null,
                    (int) $arr_p["May"] ? (int) $arr_p["May"] : null,
                    (int) $arr_p["Jun"] ? (int) $arr_p["Jun"] : null,
                    (int) $arr_p["Jul"] ? (int) $arr_p["Jul"] : null,
                    (int) $arr_p["Ago"] ? (int) $arr_p["Ago"] : null,
                    (int) $arr_p["Sep"] ? (int) $arr_p["Sep"] : null,
                    (int) $arr_p["Oct"] ? (int) $arr_p["Oct"] : null,
                    (int) $arr_p["Nov"] ? (int) $arr_p["Nov"] : null,
                    (int) $arr_p["Dic"] ? (int) $arr_p["Dic"] : null
                )
            );
            $this->view->arr_p = json_encode($graph_p);
        }


        $yes = date('Y-m-d', strtotime('-1 day', strtotime(date("Y-m-d"))));
        $arr_lda = $mapper->obtenerLiberadosPorFecha($yes, $input->idCliente, $input->idAduana);
        $this->view->arr_lda = $arr_lda;

        $oyb = date('Y-m-d', strtotime('-2 year', strtotime(date("Y-m-d"))));
        $oybt = date('Y-m-d', strtotime('+6 months', strtotime(date("Y-m-d"))));

        $arr_oyb = $mapper->obtenerNoLiberadosPorFecha($oyb, $input->idCliente, $input->idAduana, $oybt);
        $this->view->arr_oyb = $arr_oyb;

        $arrl = $mapper->obtenerLiberadosGrafica($year, $input->idCliente, $input->idAduana);
        $arrlib = [];
        foreach ($arrl as $value) {
            $arrlib[] = (int) $value;
        }
        $this->view->liberados = $arrlib;

        $arrl_p = $mapper->obtenerLiberadosGrafica($year - 1, $input->idCliente, $input->idAduana);
        $arrlib_p = [];
        foreach ($arrl_p as $value) {
            $arrlib_p[] = (int) $value;
        }
        $this->view->liberados_p = $arrlib_p;

        $arra = $mapper->obtenerPorAduanaGrafica($year, $month);
        $this->view->porAduana = $arra["data"];

        $rojos_arra = $mapper->obtenerRojosPorAduanaGrafica($year, $month);
        $this->view->rojoPorAduana = $rojos_arra;

        $arra_p = $mapper->obtenerPorAduanaGrafica($year - 1, $month);
        $this->view->porAduanaP = $arra_p["data"];

        $this->view->porAduanaEtiquetas = array_unique(array_merge($arra["labels"], $arra_p["labels"]), SORT_REGULAR);

        $comp = $mapper->obtenerLiberadosVsCompleto($year, $month);
        $this->view->sinc = $comp;

        $mppr = new Trafico_Model_ClientesMapper();
        $this->view->clientes = $mppr->obtenerClientes();

        $mpprc = new Trafico_Model_TraficoAduanasMapper();
        $this->view->aduanas = $mpprc->obtenerTodas();

        $inc_mppr = new Operaciones_Model_Incidencias();
        $in = $inc_mppr->reporte($year, $input->idCliente, $input->idAduana);

        $graph_inc = array(
            "data" => array(
                (int) $in["Ene"] ? (int) $in["Ene"] : null,
                (int) $in["Feb"] ? (int) $in["Feb"] : null,
                (int) $in["Mar"] ? (int) $in["Mar"] : null,
                (int) $in["Abr"] ? (int) $in["Abr"] : null,
                (int) $in["May"] ? (int) $in["May"] : null,
                (int) $in["Jun"] ? (int) $in["Jun"] : null,
                (int) $in["Jul"] ? (int) $in["Jul"] : null,
                (int) $in["Ago"] ? (int) $in["Ago"] : null,
                (int) $in["Sep"] ? (int) $in["Sep"] : null,
                (int) $in["Oct"] ? (int) $in["Oct"] : null,
                (int) $in["Nov"] ? (int) $in["Nov"] : null,
                (int) $in["Dic"] ? (int) $in["Dic"] : null
            )
        );
        $this->view->incidencias = json_encode($graph_inc);

        $inc_adu = $inc_mppr->obtenerIncidenciasPorAduanaGrafica($year);
        $this->view->incidencias_aduana = $inc_adu;

        $indicadores = $mapper->indicadores($year, $month);
        $this->view->indicadores = $indicadores;
    }

    public function verTraficoAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Ver tráfico";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
            ->appendStylesheet("/css/fakeLoader.css")
            ->appendStylesheet("/easyui/themes/default/easyui.css")
            ->appendStylesheet("/easyui/themes/icon.css")
            ->appendStylesheet("/css/jquery.qtip.min.css")
            ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
            ->appendStylesheet("/js/common/highlight/styles/monokai.css");
        $this->view->headScript()
            ->appendFile("/js/common/fakeLoader.min.js")
            ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
            ->appendFile("/js/common/jquery.qtip.min.js")
            ->appendFile("/easyui/jquery.easyui.min.js")
            ->appendFile("/easyui/jquery.edatagrid.js")
            ->appendFile("/easyui/datagrid-filter.js")
            ->appendFile("/easyui/locale/easyui-lang-es.js")
            ->appendFile("/js/common/jquery.slidereveal.min.js")
            ->appendFile("/js/common/loadingoverlay.min.js")
            ->appendFile("/js/trafico/index/ver-trafico.js?" . time())
            ->appendFile("/js/common/mensajero.js?" . time());
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
            $model = new Trafico_Model_TraficosMapper();
            $basico = $model->obtenerPorId($input->id);
            $this->view->basico = $basico;
        }
    }

    public function editarTraficosAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Editar tráficos";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
            ->appendStylesheet("/css/fakeLoader.css")
            ->appendStylesheet("/easyui/themes/default/easyui.css")
            ->appendStylesheet("/easyui/themes/icon.css")
            ->appendStylesheet("/css/jqModal.css")
            ->appendStylesheet("/css/jquery.qtip.min.css")
            ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
            ->appendStylesheet("/js/common/modal/magnific-popup.css")
            ->appendStylesheet("/js/common/highlight/styles/monokai.css")
            ->appendStylesheet("/js/common/bootstrap/datepicker/css/datepicker.css")
            ->appendStylesheet("/js/common/contentxmenu/jquery.contextMenu.min.css")
            ->appendStylesheet("/css/jquery.timepicker.css")
            ->appendStylesheet("/js/common/toast/jquery.toast.min.css");
        $this->view->headScript()
            ->appendFile("/js/common/jquery.timepicker.min.js")
            ->appendFile("/js/common/fakeLoader.min.js")
            ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
            ->appendFile("/js/common/jqModal.js")
            ->appendFile("/js/common/jquery.qtip.min.js")
            ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js")
            ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js")
            ->appendFile("/js/common/contentxmenu/jquery.contextMenu.min.js")
            ->appendFile("/js/common/toast/jquery.toast.min.js?")
            ->appendFile("/easyui/jquery.easyui.min.js")
            ->appendFile("/easyui/jquery.edatagrid.js")
            ->appendFile("/easyui/datagrid-filter.js")
            ->appendFile("/easyui/locale/easyui-lang-es.js")
            ->appendFile("/js/trafico/index/editar-traficos.js?" . time())
            ->appendFile("/js/common/jquery.slidereveal.min.js")
            ->appendFile("/js/common/loadingoverlay.min.js");
    }
}
