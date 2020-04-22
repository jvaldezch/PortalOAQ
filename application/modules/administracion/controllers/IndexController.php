<?php

class Administracion_IndexController extends Zend_Controller_Action {

    protected $_session;
    protected $_soapClient;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;
    protected $_logger;
    protected $_arch;

    public function init() {
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->view->headLink(array("rel" => "icon shortcut", "href" => "/favicon.png"));
        $this->view->headLink()
                ->appendStylesheet("/js/common/bootstrap/css/bootstrap.min.css")
                ->appendStylesheet("/js/common/bootstrap/datepicker/css/datepicker.css")
                ->appendStylesheet("/css/fontawesome/css/fontawesome-all.min.css")
                ->appendStylesheet("/less/traffic-module.css?" . time());
        $this->view->headScript()
                ->appendFile("/js/common/js.cookie.js")
                ->appendFile("/js/common/jquery-1.9.1.min.js")
                ->appendFile("/js/common/bootstrap/js/bootstrap.min.js")
                ->appendFile("/js/common/bootstrap/datepicker/js/bootstrap-datepicker.js")
                ->appendFile("/js/common/jquery.form.min.js")
                ->appendFile("/js/common/jquery.validate.min.js")
                ->appendFile("/js/common/additional-methods.min.js")
                ->appendFile("/js/common/date.js")
                ->appendFile("/js/common/mensajero.js?" . time())
                ->appendFile("/js/common/principal.js?" . time());
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $this->_logger = Zend_Registry::get("logDb");
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
        $this->view->myHelpers = new Application_View_Helper_MyHelpers();
        $news = new Application_Model_NoticiasInternas();
        $this->view->noticias = $news->obtenerTodos();
        if (APPLICATION_ENV == "development") {
            $this->view->browser_sync = "<script async src='http://{$this->_config->app->browser_sync}/browser-sync/browser-sync-client.js?v=2.26.7'><\/script>";
        }
    }

    public function indexAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Panel principal";
        $this->view->headMeta()->appendName("description", "");
    }

    public function cuentaDeGastosAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Reporte de cuenta de gastos";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/js/administracion/index/cuenta-de-gastos.js?" . time());
        $search = NULL ? $search = new Zend_Session_Namespace("") : $search = new Zend_Session_Namespace("OAQCtaGastos");
        $form = new Administracion_Form_CtaGastos();
        $this->view->form = $form;
    }

    public function pronosticoDeCobranzaAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Pronóstico de cobranza";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/js/common/bootstrap/css/bootstrap.min.css")
                ->appendStylesheet("/js/common/bootstrap/bootstrap-datepicker/css/datepicker.css");
        $this->view->headScript()
                ->appendFile("/js/common/bootstrap/js/bootstrap.min.js")
                ->appendFile("/js/common/jquery.validate.min.js")
                ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js")
                ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js")
                ->appendFile("/js/administracion/index/pronostico-de-cobranza.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "page" => array("Digits"),
            "size" => array("Digits"),
            "sum" => array("Digits"),
            "desglose" => array("Digits"),
            "rfc" => array("StringToUpper"),
            "nombre" => array("StringToUpper"),
        );
        $v = array(
            "page" => array(new Zend_Validate_Int(), "default" => 1),
            "size" => array(new Zend_Validate_Int(), "default" => 20),
            "sum" => array("NotEmpty", new Zend_Validate_Int()),
            "desglose" => array("NotEmpty", new Zend_Validate_Int()),
            "nombre" => "NotEmpty",
            "rfc" => array("NotEmpty", new Zend_Validate_Regex("/^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/")),
            "fechaIni" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/"), "default" => date("Y-m-d")),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        $form = new Administracion_Form_Pronostico();
        $form->populate(array(
            "rfc" => $input->rfc,
            "nombre" => $input->nombre,
            "fechaIni" => $input->fechaIni,
            "sum" => $input->sum,
            "desglose" => $input->desglose,
        ));
        if ($input->isValid("fechaIni") && $input->isValid("sum") && $input->isValid("desglose")) {
            $sica = new OAQ_SicaDb("192.168.200.5", "sa", "adminOAQ123", "SICA", 1433, "SqlSrv");
            if ((int) $input->desglose == 0) {
                $result = $sica->pronosticoCobranza($input->rfc, $input->fechaIni, $input->sum);
            } elseif ((int) $input->desglose == 1) {
                $result = $sica->pronosticoCobranzaDesglose($input->rfc, $input->fechaIni, $input->sum);
            }
        }
        $this->view->desglose = $input->desglose;
        $this->view->sum = $input->sum;
        if (isset($result) && !empty($result)) {
            $this->view->params = http_build_query($input->getEscaped());
            $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array($result));
            $paginator->setItemCountPerPage($input->size);
            $paginator->setCurrentPageNumber($input->page);
            $this->view->paginator = $paginator;
        }
        $this->view->form = $form;
    }
    
    public function excelPronosticoDeCobranzaAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "page" => array("Digits"),
            "size" => array("Digits"),
            "sum" => array("Digits"),
            "desglose" => array("Digits"),
            "rfc" => array("StringToUpper"),
            "nombre" => array("StringToUpper"),
        );
        $v = array(
            "page" => array(new Zend_Validate_Int(), "default" => 1),
            "size" => array(new Zend_Validate_Int(), "default" => 20),
            "sum" => array("NotEmpty", new Zend_Validate_Int()),
            "desglose" => array("NotEmpty", new Zend_Validate_Int()),
            "nombre" => "NotEmpty",
            "rfc" => array("NotEmpty", new Zend_Validate_Regex("/^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/")),
            "fechaIni" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/"), "default" => date("Y-m-d")),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("fechaIni") && $input->isValid("sum") && $input->isValid("desglose")) {
            $sica = new OAQ_SicaDb("192.168.200.5", "sa", "adminOAQ123", "SICA", 1433, "SqlSrv");
            if ((int) $input->desglose == 0) {
                $result = $sica->pronosticoCobranza($input->rfc, $input->fechaIni, $input->sum);
            } elseif ((int) $input->desglose == 1) {
                $result = $sica->pronosticoCobranzaDesglose($input->rfc, $input->fechaIni, $input->sum);
            }
        }
        $excel = new OAQ_ExcelReportes();
        $excel->setData($result);
        if ((int) $input->desglose == 0 && (int) $input->sum == 0) {
            $excel->setTitles(["CLIENTE", "PLAZO (DÍAS)", "RETRASO (DÍAS)", "RELACIÓN DE CTA.", "FACTURA", "FECHA FACTURA", "FECHA ACUSE", "FECHA PRONÓSTICO", "REFERENCIA", "TOTAL"]);            
        } elseif ((int) $input->desglose == 0 && (int) $input->sum == 1) {
            $excel->setTitles(["CLIENTE", "TOTAL"]);            
        }
        $excel->setFilename("PRONOSTICOCROB_" . date("Ymd") . ".xlsx");
        $excel->layoutClientes();
    }

    public function excelCuentaDeGastosAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $search = NULL ? $search = new Zend_Session_Namespace("") : $search = new Zend_Session_Namespace("OAQCtaGastos");
        if ($search->desglose == "0") {
            $headers = array(
                "Fecha" => "fecha_factura",
                "Folio" => "factura",
                "Referencia" => "referencia",
                "Aduana" => "aduana",
                "Patente" => "patente",
                "Pedimento" => "pedimento",
                "IE" => "ie",
                "Cve. Doc" => "regimen",
                "Fecha Pedimento" => "fecha_pedimento",
                "Factura Pedimento" => "ref_factura",
                "Bultos/Piezas" => "bultos",
                "Valor Aduana" => "valor_aduana",
                "Maniobras" => "maniobras",
                "Almacenajes" => "almacenaje",
                "Demoras" => "demoras",
                "Flete aereo" => "fleteaereo",
                "Flete marítimo" => "fletemaritimo",
                "Fletes acarreos" => "fletesacarreos",
                "Gastos complementarios" => "gastos_complementarios",
                "Gastos alijadores" => "gastos_alijadores",
                "Impuestos Aduanales" => "impuestos_aduanales",
                "Revalidación" => "revalidacion",
                "Rectificación" => "rectificaciones",
                "Honorarios" => "honorarios",
                "Sub Total" => "subtotal",
                "IVA" => "iva",
                "Anticipo" => "anticipo",
                "Total" => "total",
            );
        } else if ($search->desglose == "1") {
            $headers = array(
                "Fecha" => "fecha_factura",
                "Folio" => "factura",
                "Referencia" => "referencia",
                "Aduana" => "aduana",
                "Patente" => "patente",
                "Pedimento" => "pedimento",
                "IE" => "ie",
                "Cve. Doc" => "regimen",
                "Fecha Pedimento" => "fecha_pedimento",
                "Factura Pedimento" => "ref_factura",
                "Bultos/Piezas" => "bultos",
                "Valor Aduana" => "valor_aduana",
                "Maniobras Subtotal" => "subtotal_maniobras",
                "Maniobras IVA" => "iva_maniobras",
                "Maniobras Total" => "maniobras",
                "Almacenaje Subtotal" => "subtotal_almacenaje",
                "Almacenaje IVA" => "iva_almacenaje",
                "Almacenaje Total" => "almacenaje",
                "Demoras Subtotal" => "subtotal_demoras",
                "Demoras IVA" => "iva_demoras",
                "Demoras Total" => "demoras",
                "Flete Aéreo Subtotal" => "subtotal_fleteaereo",
                "Flete Aéreo IVA" => "iva_fleteaereo",
                "Flete Aéreo Total" => "fleteaereo",
                "Flete Marítimo Subtotal" => "subtotal_fletemaritimo",
                "Flete Marítimo IVA" => "iva_fletemaritimo",
                "Flete Marítimo Total" => "fletemaritimo",
                "Flete Terrestre Subtotal" => "subtotal_fleteterrestre",
                "Flete Terrestre IVA" => "iva_fleteterrestre",
                "Flete Terrestre Total" => "fleteterrestre",
                "Flete y Acarreos Subtotal" => "subtotal_fletesacarreos",
                "Flete y Acarreos IVA" => "iva_fletesacarreos",
                "Flete y Acarreos Total" => "fletesacarreos",
                "Gastos complementarios" => "gastos_complementarios",
                "Gastos alijadores" => "gastos_alijadores",
                "Impuestos Aduanales" => "impuestos_aduanales",
                "Revalidación" => "revalidacion",
                "Rectificación" => "rectificaciones",
                "Honorarios" => "honorarios",
                "Sub Total" => "subtotal",
                "IVA" => "iva",
                "Anticipo" => "anticipo",
                "Total" => "total",
            );
        }
        $sica = new OAQ_Sica;
        $misc = new OAQ_Misc();
        $clienteId = $sica->getCustomerId(strtoupper($search->rfc));
        $result = $sica->getInvoices((int) $clienteId, $search->fechaIni, $search->fechaFin);
        if (!($result = $misc->checkCache("ctagastos"))) {
            $result = $sica->getInvoices((int) $clienteId, $search->fechaIni, $search->fechaFin);
        }
        $customerInfo = $sica->getCustomerName((int) $clienteId);
        $reports = new OAQ_ExcelExport();
        $reports->createSimpleReport($headers, $result, "ctagastos", "Cuenta de gastos", $search->fechaIni, $search->fechaFin, "Reporte de cuenta de gastos", $search->rfc, $customerInfo["nombre"]);
    }

    public function excelPedimentosPagadosAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $search = NULL ? $search = new Zend_Session_Namespace("") : $search = new Zend_Session_Namespace("OAQCtaGastos");
        $headers = array(
            "RFC" => "RFCCTE",
            "Nombre" => "NOMCLI",
            "Ene" => "Ene",
            "Feb" => "Feb",
            "Mar" => "Mar",
            "Abr" => "Abr",
            "May" => "May",
            "Jun" => "Jun",
            "Ago" => "Ago",
            "Sep" => "Sep",
            "Oct" => "Oct",
            "Nov" => "Nov",
            "Dic" => "Dic",
        );
        $misc = new OAQ_Misc();
        $result = $misc->checkCache("pedimentosPag_" . $this->_session->username);
        $reports = new OAQ_ExcelExport();
        $reports->createSimpleReport($headers, $result, "pedimentospag", "Pedimentos Pagados", date("Y-m-d"), null, "Pedimentos Pagados", $search->rfc, "Organización Aduanal de Querétaro, S.C.");
    }

    public function excelIngresosCorresponsalesAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $search = NULL ? $search = new Zend_Session_Namespace("") : $search = new Zend_Session_Namespace("OAQCtaGastos");
        $headers = array(
            "Tipo" => "Tipo",
            "Cuenta" => "CuentaID",
            "Ene" => "Ene",
            "Feb" => "Feb",
            "Mar" => "Mar",
            "Abr" => "Abr",
            "May" => "May",
            "Jun" => "Jun",
            "Ago" => "Ago",
            "Sep" => "Sep",
            "Oct" => "Oct",
            "Nov" => "Nov",
            "Dic" => "Dic",
        );
        $misc = new OAQ_Misc();
        $result = $misc->checkCache("ingresosCorr_" . $this->_session->username);
        $reports = new OAQ_ExcelExport();
        $reports->createSimpleReport($headers, $result, "ingresoscorr", "Ingresos de Corresponsales", date("Y-m-d"), null, "", "", $search->ingCorrNombre);
    }   

    public function enviarEmailAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(false);
        $search = NULL ? $search = new Zend_Session_Namespace("") : $search = new Zend_Session_Namespace("OAQCobranza");
        if ($search->sum == true) {
            $emails[] = array(
                "nombre" => "David Lopez",
                "email" => "david.lopez@oaq.com.mx",
            );
            $this->view->emails = $emails;
            $search->emails = $emails;
        } else if (isset($search->rfc) && $search->rfc != "") {
            $emails[] = array(
                "nombre" => "Jaime",
                "email" => "ti.jvaldez@oaq.com.mx",
            );
            $emails[] = array(
                "nombre" => "Soporte",
                "email" => "soporte@oaq.com.mx",
            );
            $this->view->emails = $emails;
            $search->emails = $emails;
        } else {
            $this->view->emails = null;
        }
    }

    public function sendEmailAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $search = NULL ? $search = new Zend_Session_Namespace("") : $search = new Zend_Session_Namespace("OAQCobranza");
        $headers = array(
            "Cliente" => "cliente",
            "Plazo" => "plazo",
            "Vencimiento" => "vencimiento",
            "Relación de Cta." => "relacionid",
            "Factura" => "folioid",
            "Fecha Factura" => "fechafactura",
            "Fecha Acuse" => "fecha_acuse",
            "Referencia" => "referencia",
            "Total" => "total",
        );

        $sum = null;
        if ((int) $search->sum == 1) {
            $this->view->sum = true;
            $sum = true;
            $headers = array(
                "Cliente" => "cliente",
                "Total" => "total",
            );
        }
        $this->_logger->logEntry(
                $this->_request->getModuleName() . ":" . $this->_request->getControllerName() . ":" . $this->_request->getActionName(), "ENVIO EMAIL {$search->fechaIni} : {$search->rfc}", $_SERVER["REMOTE_ADDR"], $this->_session->username);
        $sica = new OAQ_Sica;
        $misc = new OAQ_Misc();
        if (!($data = $misc->checkCache("cobranza"))) {
            $data = $sica->getCheckupCorrespondents();
        }
        $clienteId = $sica->getCustomerId(strtoupper($search->rfc));
        $customerInfo = $sica->getCustomerName((int) $clienteId);

        $reports = new OAQ_ExcelExport();
        $reports->createSimpleReport($headers, $data, "cobranza", "Pronóstico de cobranza", $search->fechaIni, null, "Reporte de cuenta de gastos", $search->rfc, $customerInfo["nombre"], true);
    }

    public function clientesAction() {
        $this->_helper->layout()->disableLayout();
        $sica = new OAQ_Sica();
        $this->view->customers = $sica->loadCustomers();
    }

    public function enviosPorComprobarAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Envíos por comprobar";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/js/common/bootstrap/css/bootstrap.min.css")
                ->appendStylesheet("/js/common/bootstrap/bootstrap-datepicker/css/datepicker.css");
        $this->view->headLink(array("rel" => "icon shortcut", "href" => "/favicon.png"));
        $this->view->headScript()
                ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js")
                ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js")
                ->appendFile("/js/administracion/index/envios-por-comprobar.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "page" => array("Digits"),
            "size" => array("Digits"),
            "opcion" => array("Digits"),
        );
        $v = array(
            "page" => array(new Zend_Validate_Int(), "default" => 1),
            "size" => array(new Zend_Validate_Int(), "default" => 20),
            "opcion" => array("NotEmpty", new Zend_Validate_Int(), "default" => 0),
            "fechaIni" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/"), "default" => date("Y-m-d")),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        $form = new Administracion_Form_EnvioCorresponsales();
        $sica = new OAQ_SicaDb("192.168.200.5", "sa", "adminOAQ123", "SICA", 1433, "SqlSrv");
        if ($input->isValid("opcion") && $input->isValid("fechaIni")) {
            $result = $sica->enviosPorComprobar($input->opcion);
        }
        $form->populate(array(
            "opcion" => $input->opcion,
            "fechaIni" => $input->fechaIni,
        ));
        if (isset($result)) {
            $this->view->params = http_build_query($input->getEscaped());
            $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array($result));
            $paginator->setItemCountPerPage($input->size);
            $paginator->setCurrentPageNumber($input->page);
            $this->view->paginator = $paginator;
        }
        $this->view->form = $form;
    }
    
    public function tiemposDeComprobacionAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Tiempos de comprobación";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/js/common/bootstrap/css/bootstrap.min.css")
                ->appendStylesheet("/js/common/bootstrap/bootstrap-datepicker/css/datepicker.css");
        $this->view->headScript()
                ->appendFile("/js/common/bootstrap/js/bootstrap.min.js")
                ->appendFile("/js/common/jquery.validate.min.js")
                ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js")
                ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js")
                ->appendFile("/js/administracion/index/tiempos-de-comprobacion.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "page" => array("Digits"),
            "size" => array("Digits"),
            "rfc" => array("StringToUpper"),
            "nombre" => array("StringToUpper"),
        );
        $v = array(
            "page" => array(new Zend_Validate_Int(), "default" => 1),
            "size" => array(new Zend_Validate_Int(), "default" => 20),
            "rfc" => array("NotEmpty", new Zend_Validate_Regex("/^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/")),
            "nombre" => "NotEmpty",
            "fechaIni" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/"), "default" => date("Y-m-d")),
            "fechaFin" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/"), "default" => date("Y-m-d")),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        $form = new Administracion_Form_TiemposComprobacion();
        $form->populate(array(
            "fechaIni" => $input->fechaIni,
            "fechaFin" => $input->fechaFin,
            "rfc" => $input->rfc,
            "nombre" => $input->nombre,
        ));
        $this->view->form = $form;
        if ($input->isValid("rfc") && $input->isValid("fechaIni") && $input->isValid("fechaFin")) {
            $sica = new OAQ_SicaDb("192.168.200.5", "sa", "adminOAQ123", "SICA", 1433, "SqlSrv");
            $clientes = new Trafico_Model_ClientesMapper();
            $cuentaID = $clientes->sistema($input->rfc, $input->nombre, "sica");
            $result = $sica->tiemposDeComprobacion($input->rfc, "1104" . str_pad($cuentaID["identificador"], 4, "0", STR_PAD_LEFT) . "00000000", $input->fechaIni, $input->fechaFin);
            if (isset($result) && !empty($result)) {
                $this->view->params = http_build_query($input->getEscaped());
                $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array($result));
                $paginator->setItemCountPerPage($input->size);
                $paginator->setCurrentPageNumber($input->size);
                $this->view->paginator = $paginator;
                $this->view->resultados = count($result);
            }
        }
    }

    public function excelTiemposDeComprobacionAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "rfc" => array("StringToUpper"),
            "nombre" => array("StringToUpper"),
        );
        $v = array(
            "rfc" => array("NotEmpty", new Zend_Validate_Regex("/^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/")),
            "nombre" => "NotEmpty",
            "fechaIni" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
            "fechaFin" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("rfc") && $input->isValid("fechaIni") && $input->isValid("fechaFin")) {
            $sica = new OAQ_SicaDb("192.168.200.5", "sa", "adminOAQ123", "SICA", 1433, "SqlSrv");
            $clientes = new Trafico_Model_ClientesMapper();
            $cuentaID = $clientes->sistema($input->rfc, $input->nombre, "sica");
            $result = $sica->tiemposDeComprobacion($input->rfc, "1104" . str_pad($cuentaID["identificador"], 4, "0", STR_PAD_LEFT) . "00000000", $input->fechaIni, $input->fechaFin);
            $excel = new OAQ_ExcelReportes();
            $excel->setTitles(["CUENTA", "REFERENCIA", "REGIMEN", "FECHA FACTURA", "FECHA ACUSE", "FECHA PAGO", "PLAZO", "F.PAGO - F.FACTURA", "F.ACUSE - F.FACTURA"]);
            $excel->setData($result);
            $excel->setFilename("TIEMPOCOMP_" . date("Ymd") . ".xlsx");
            $excel->layoutClientes();
        }
    }

    public function pedimentosPagadosAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Pedimentos pagados";
        $this->view->headMeta()->appendName("description", "");

        $search = NULL ? $search = new Zend_Session_Namespace("") : $search = new Zend_Session_Namespace("OAQCtaGastos");
        $misc = new OAQ_Misc();
        $form = new Administracion_Form_PedimentosPagados();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($form->isValid($data)) {
                if ($data["bootstrap"]["aduana"] == "640") {
                    $sitawin = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITAW3010640", 1433, "Pdo_Mssql");
                } elseif ($data["bootstrap"]["aduana"] == "646") {
                    $sitawin = new OAQ_Sitawin(true, "192.168.0.253", "sa", "sqlcointer", "SITAW3589640", 1433, "Pdo_Mssql");
                }
                $pedimentos = $sitawin->pedimentosPorCorresponsal($data["bootstrap"]["year"]);
                $misc->saveCache("pedimentosPag_" . $this->_session->username, $pedimentos);
                if (isset($pedimentos)) {
                    $this->view->data = $pedimentos;
                }
            }
        }
        $this->view->form = $form;
    }

    public function consolidarFacturasAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Consolidar facturas";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/js/jquery.form.min.js");
        $search = NULL ? $search = new Zend_Session_Namespace("") : $search = new Zend_Session_Namespace("OAQCtaGastos");
        if (!isset($search->uploadDir)) {
            $search->uploadDir = "/tmp" . DIRECTORY_SEPARATOR . md5(time());
        }
    }

    public function verArchivosAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Ver archivos poliza";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/css/nuevo-estilo.css")
                ->appendStylesheet("/less/traffic-module.css?" . time());
        $this->view->headScript()
                ->appendFile("/js/jquery.form.min.js");
        $filters = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => "Digits",
        );
        $validators = array(
            "id" => array("Digits"),
        );
        $input = new Zend_Filter_Input($filters, $validators, $this->_request->getParams());
        if ($input->isValid()) {
            $mapper = new Administracion_Model_RepositorioContaMapper();
            $file = $mapper->getAll($input->id);
            $form = new Administracion_Form_VerArchivos();
            $form->populate(array(
                "tipoPoliza" => $file[0]["tipoPoliza"],
                "tipoArchivo" => $file[0]["tipoArchivo"],
                "fecha" => (isset($file[0]["fecha"]) && $file[0]["fecha"] != "") ? date("Y-m-d", strtotime($file[0]["fecha"])) : null,
            ));
            $this->view->form = $form;
            $this->view->data = $file;
        }
    }

    public function repositorioAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Repositorio Administracion";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/bootstrap/bootstrap-datepicker/css/datepicker.css")
                ->appendStylesheet("/css/nuevo-estilo.css")
                ->appendStylesheet("/less/traffic-module.css?" . time());
        $this->view->headScript()
                ->appendFile("/js/jquery.form.min.js")
                ->appendFile("/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js")
                ->appendFile("/bootstrap/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js")
                ->appendFile("/js/administracion/index/repositorio.js?" . time());
        $filters = array(
            "*" => array("StringTrim", "StripTags"),
        );
        $input = new Zend_Filter_Input($filters, null, $this->_request->getParams());
        if ($input->isValid()) {
            if (isset($input->poliza)) {
                $this->view->poliza = $input->poliza;
            }
            if (isset($input->fechaIni)) {
                $this->view->fechaIni = $input->fechaIni;
            } else {
                $this->view->fechaIni = date("Y-m-" . "01");
            }
            if (isset($input->fechaFin)) {
                $this->view->fechaFin = $input->fechaFin;
            } else {
                $this->view->fechaFin = date("Y-m-d");
            }
        }
        $page = $this->_request->getParam("page", 1);
        $mapper = new Administracion_Model_RepositorioContaMapper();
        $result = $mapper->fetchAll();
        if (isset($result) && !empty($result)) {
            $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array($result));
            $paginator->setItemCountPerPage(25);
            $paginator->setCurrentPageNumber($page);
            $this->view->paginator = $paginator;
        }
    }

    public function crearRepositorioAdminAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Crear repositorio poliza";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/bootstrap/bootstrap-datepicker/css/datepicker.css")
                ->appendStylesheet("/css/nuevo-estilo.css")
                ->appendStylesheet("/less/traffic-module.css?" . time());
        $this->view->headScript()
                ->appendFile("/js/jquery.form.min.js")
                ->appendFile("/js/jquery.form.min.js")
                ->appendFile("/js/jquery.validate.min.js")
                ->appendFile("/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js")
                ->appendFile("/bootstrap/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js")
                ->appendFile("/js/administracion/index/crear-repositorio-admin.js?" . time());
        $form = new Administracion_Form_CrearPoliza();
        $this->_arch = NULL ? $this->_arch = new Zend_Session_Namespace("") : $this->_arch = new Zend_Session_Namespace("Navigation");
        $this->_arch->setExpirationSeconds($this->_appconfig->getParam("session-exp"));
        $archive = new Archivo_Model_RepositorioContaMapper();
        $data = $this->_request->getParams();
        $form->populate($data);
        if (isset($this->_arch)) {
            $this->_arch->unsetAll();
        }
        if (isset($data["poliza"]) && isset($data["tipo"])) {
            if ($form->isValid($data)) {
                $found = $archive->buscarPoliza($data["poliza"], $data["tipo"]);
                if ($found === false) {
                    $this->_arch->poliza = strtoupper($data["poliza"]);
                    $this->_arch->tipo = strtoupper($data["tipo"]);
                    $this->getResponse()->setRedirect("/administracion/index/ver-archivos-poliza");
                } else {
                    $this->_arch->poliza = strtoupper($data["poliza"]);
                    $this->_arch->tipo = strtoupper($data["tipo"]);
                    $this->getResponse()->setRedirect("/administracion/index/ver-archivos-poliza");
                }
            }
        } else {
            if (isset($data["tipo"]) && $data["tipo"] == "") {
                $form->getElement("tipo")->addError("Debe proporcionar el tipo de poliza");
            }
            if (isset($data["poliza"]) && $data["poliza"] == "") {
                $form->getElement("poliza")->addError("Debe proporcionar el número de poliza");
            }
        }
        $this->view->form = $form;
    }

    public function verArchivosPolizaAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Archivos archivos de poliza";
        $this->view->headLink()
                ->appendStylesheet("/css/nuevo-estilo.css")
                ->appendStylesheet("/less/traffic-module.css?" . time());
        $this->view->headScript()
                ->appendFile("/js/jquery.validate.min.js");
        $this->_arch = NULL ? $this->_arch = new Zend_Session_Namespace("") : $this->_arch = new Zend_Session_Namespace("Navigation");
        if (isset($this->_arch->poliza) && isset($this->_arch->tipo)) {
            $this->view->poliza = $this->_arch->poliza;
        } else {
            $data = $this->_request->getParams();
            if (isset($data["poliza"]) && isset($data["tipo"])) {
                $this->_arch->poliza = $data["poliza"];
                $this->_arch->tipo = $data["tipo"];
                $this->view->poliza = $this->_arch->poliza;
            }
        }
    }

    public function cartaPorteAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Carta Porte";
        $this->view->headLink()
                ->appendStylesheet("/less/traffic-module.css?" . time());
        $this->view->headScript()
                ->appendFile("/js/common/additional-methods.min.js")
                ->appendFile("/js/common/jquery-ui-1.9.2.min.js")
                ->appendFile("/js/common/jquery.selectBoxIt.min.js")
                ->appendFile("/js/common/jquery.blockUI.js")
                ->appendFile("/js/common/rich_calendar.js")
                ->appendFile("/js/common/rc_lang_en.js")
                ->appendFile("/js/common/domready.js")
                ->appendFile("/js/common/calendar.js")
                ->appendFile("/js/common/jquery.form.js");
        $this->view->action = $this->getRequest()->getActionName();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $this->view->data = $data;
            $model = new Administracion_Model_CartasPorteMapper();
            if (isset($data) && !empty($data)) {
                $explode = explode("/", $data["fecha"]);
                $data["fecha"] = $explode[2] . "-" . $explode[1] . "-" . $explode[0] . " " . date("H:i:s");
                $inserted = $model->nuevaCarta($data, $this->_session->username);
                if ($inserted == true) {
                    $this->getResponse()->setRedirect("/administracion/index/cartas-porte");
                }
            }
        }
    }

    public function cartasPorteAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Cartas Porte";
        $this->view->headLink()
                ->appendStylesheet("/css/jquery.selectBoxIt.css");
        $this->view->headScript()
                ->appendFile("/js/common/jquery-ui-1.9.2.min.js")
                ->appendFile("/js/common/jquery.selectBoxIt.min.js")
                ->appendFile("/js/common/jquery.blockUI.js");
        $model = new Administracion_Model_CartasPorteMapper();
        $page = $this->_request->getParam("page", 1);
        $result = $model->obtenerCartas();
        if (isset($result) && !empty($result)) {
            $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array($result));
            $paginator->setItemCountPerPage(25);
            $paginator->setCurrentPageNumber($page);
            $this->view->paginator = $paginator;
        }
    }

    public function editarCartaPorteAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Editar Carta Porte";
        $this->view->headLink()
                ->appendStylesheet("/css/jquery.selectBoxIt.css")
                ->appendStylesheet("/css/rich_calendar.css");
        $this->view->headScript()
                ->appendFile("/js/common/jquery-ui-1.9.2.min.js")
                ->appendFile("/js/common/jquery.selectBoxIt.min.js")
                ->appendFile("/js/common/jquery.blockUI.js")
                ->appendFile("/js/common/rich_calendar.js")
                ->appendFile("/js/common/rc_lang_en.js")
                ->appendFile("/js/common/domready.js")
                ->appendFile("/js/common/calendar.js")
                ->appendFile("/js/administracion/index/carta-porte.js?" . time());
        $this->_helper->viewRenderer("carta-porte");
        $this->view->action = $this->getRequest()->getActionName();
        $folio = $this->_request->getParam("folio", 1);
        if (isset($folio)) {
            $model = new Administracion_Model_CartasPorteMapper();
            $data = $model->obtenerFolio($folio);
            $this->view->data = $data;
        }
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $explode = explode("/", $data["fecha"]);
            $data["fecha"] = $explode[2] . "-" . $explode[1] . "-" . $explode[0] . " " . date("H:i:s");
            $this->view->data = $data;
            if (isset($data) && !empty($data)) {
                if (isset($data["facturado"]) && $data["facturado"] == "on") {
                    $data["facturado"] = 1;
                } else {
                    $data["facturado"] = null;
                }
                if (isset($data["factura"]) && $data["factura"] == "") {
                    $data["factura"] = null;
                }
                $updated = $model->actualizarCarta($data["folio"], $data, $this->_session->username);
                if ($updated == true) {
                    $this->getResponse()->setRedirect("/administracion/index/cartas-porte");
                }
            }
        }
    }
    
    public function solicitudesAnticipoAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Solicitudes de Anticipo";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
                ->appendStylesheet("/js/common/toast/jquery.toast.min.css")
                ->appendStylesheet("/js/common/bootstrap/datepicker/css/datepicker.css")
                ->appendStylesheet("/css/jqModal.css");
        $this->view->headScript()
                ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
                ->appendFile("/js/common/toast/jquery.toast.min.js?")
                ->appendFile("/js/common/jqModal.js")
                ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js")
                ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js")
                ->appendFile("/js/administracion/index/solicitudes-anticipo.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "page" => "Digits",
            "size" => "Digits",
            "aduanas" => "Digits",
            "complementos" => "StringToLower",
            "depositado" => "StringToLower",
            "warning" => "StringToLower",
            "buscar" => "StringToUpper",
        );
        $v = array(
            "aduanas" => array(new Zend_Validate_Int()),
            "page" => array(new Zend_Validate_Int(), "default" => 1),
            "size" => array(new Zend_Validate_Int(), "default" => 20),
            "complementos" => array(new Zend_Validate_InArray(array("true"))),
            "depositado" => array(new Zend_Validate_InArray(array("true"))),
            "warning" => array(new Zend_Validate_InArray(array("true"))),
            "buscar" => array(new Zend_Validate_Regex("/^[-_a-zA-Z0-9.]+$/")),
        );
        $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        $model = new Trafico_Model_TraficoSolicitudesMapper();
        $form = new Trafico_Form_BuscarSolicitud();
        if($i->isValid("buscar")) {
            $form->populate(array("buscar" => $i->buscar));
        }
        $mapper = new Trafico_Model_TraficoAduanasMapper();
        $this->view->filters = $mapper->obtenerTodas();
        if(in_array($this->_session->role, array("super", "super_admon"))) {
            $rows = $model->solicitudesSupervision(isset($i->buscar) ? $i->buscar : null, $i->complementos, $i->warning, $i->aduanas);
            $this->view->multiple = true;
        } elseif(in_array($this->_session->role, array("administracion"))) {
            $rows = $model->solicitudesEnTramite(isset($i->buscar) ? $i->buscar : null, $i->complementos, $i->depositado, $i->warning, $i->aduanas);            
        }
        if(isset($rows["total"])) {
            $this->view->total = $rows["total"];
            unset($rows["total"]);            
        }
        if (isset($rows) && !empty($rows)) {
            $pag = new Zend_Paginator(new Zend_Paginator_Adapter_Array($rows));
            $pag->setItemCountPerPage($i->size);
            $pag->setCurrentPageNumber($i->page);
            $this->view->paginator = $pag;
        }
        $this->view->form = $form;
        $forms = new Administracion_Form_SolicitudTrafico();
        $this->view->forms = $forms;
    }
    
    public function verSolicitudAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Solicitud de anticipo";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
                ->appendStylesheet("/css/jqModal.css");
        $this->view->headScript()
                ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
                ->appendFile("/js/common/jqModal.js")
                ->appendFile("/js/common/jquery.fileDownload.js")
                ->appendFile("/js/administracion/index/ver-solicitud.js?" . time());
        $id = $this->_getParam("id", null);
        if (in_array($this->_session->role, array("administracion", "super", "super_admon"))) {
            $this->view->edit = true;
        }
        $this->view->rol = $this->_session->role;
        $table = new Application_Model_UsuariosAduanasMapper();
        $aduanas = $table->aduanasUsuario($this->_session->id);
        if (isset($aduanas) && !empty($aduanas)) {
            if (isset($id)) {
                $request = new Trafico_Model_TraficoSolicitudesMapper();
                if ($aduanas["patente"][0] != "0" && $aduanas["aduana"][0] != "0") {
                    $header = $request->obtener($id, $aduanas["patente"], $aduanas["aduana"]);
                } else {
                    $header = $request->obtener($id);
                }
            }
        }
        if (isset($id) && isset($header) && $header !== false) {
            $tbl = new Trafico_Model_ClientesMapper();
            $comments = new Trafico_Model_TraficoSolComentarioMapper();
            $datosCliente = $tbl->datosCliente($header["idCliente"]);
            $data["header"] = $header;
            $this->view->data = $data;
            $form = new Administracion_Form_SolicitudTrafico();
            if (isset($header["tramite"]) && $header["tramite"] == 1 && !isset($header["deposito"])) {
                $proceso = 1;
            } elseif (isset($header["tramite"]) && $header["tramite"] == 1 && isset($header["deposito"]) && $header["deposito"] == 1) {
                $proceso = 2;
            }
            $solicitud = new OAQ_SolicitudesAnticipo($id);
            $form->populate(array(
                "idSolicitud" => $id,
                "aduana" => $data["header"]["aduana"],
                "patente" => $data["header"]["patente"],
                "pedimento" => str_pad($data["header"]["pedimento"], 7, '0', STR_PAD_LEFT),
                "referencia" => $data["header"]["referencia"],
                "esquema" => isset($header["esquema"]) ? $header["esquema"] : $datosCliente["esquema"],
                "proceso" => $solicitud->proceso($header["autorizada"], $header["autorizadaHsbc"], $header["autorizadaBanamex"]),
            ));
            if($solicitud->proceso($header["autorizada"]) == 2) {
                $form->esquema->setAttribs(array("readonly" => "true"));
            }
            if($solicitud->proceso($header["autorizada"]) == 3) {
                $form->esquema->setAttribs(array("disabled" => "disabled"));
                $form->proceso->setAttribs(array("disabled" => "disabled"));
                $this->view->disabled = true;
            }
            $this->view->form = $form;
            $this->view->comentarios = $comments->obtenerTodos($id);
            $log = new Trafico_Model_BitacoraMapper();
            $this->view->bitacora = $log->obtener($header["patente"], $header["aduana"], $header["pedimento"], $header["referencia"]);
        }
    }
    
    public function distanciaAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Distancia";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/css/jqModal.css");
        $this->view->headScript()
                ->appendFile("/js/common/jqModal.js")
                ->appendFile("/js/common/jquery.fileDownload.js")
                ->appendFile("/js/administracion/index/distancia.js?" . time());
        $form = new Administracion_Form_Distancia();
        $this->view->form = $form;
    }
    
    public function ingresosCorresponsalesAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Ingresos de corresponsales";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/less/traffic-module.css?" . time());
        $this->view->headScript()
                ->appendFile("/js/common/jquery.form.min.js")
                ->appendFile("/js/common/jquery.validate.min.js")
                ->appendFile("/js/common/highcharts/js/highcharts.js")
                ->appendFile("/js/common/highcharts/js/modules/data.js")
                ->appendFile("/js/common/highcharts/js/modules/exporting.js")
                ->appendFile("/js/administracion/index/ingresos-corresponsales.js?" . time());
        $search = NULL ? $search = new Zend_Session_Namespace("") : $search = new Zend_Session_Namespace("OAQCtaGastos");
        $form = new Administracion_Form_IngresosCorresponsales();
        $this->view->form = $form;
    }
    
    public function reportesAdministracionAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Reportes Administración";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/easyui/themes/default/easyui.css")
                ->appendStylesheet("/easyui/themes/icon.css");
        $this->view->headScript()
                ->appendFile("/easyui/jquery.easyui.min.js")
                ->appendFile("/easyui/jquery.edatagrid.js")
                ->appendFile("/easyui/datagrid-filter.js")
                ->appendFile("/easyui/locale/easyui-lang-es.js")
                ->appendFile("/js/common/highcharts/js/highcharts.js")
                ->appendFile("/js/common/highcharts/js/modules/data.js")
                ->appendFile("/js/common/highcharts/js/modules/exporting.js")
                ->appendFile("/js/administracion/index/reportes-administracion.js?" . time());
    }
    
    public function reporteTraficoAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Reporte de trafico";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/easyui/themes/default/easyui.css")
                ->appendStylesheet("/easyui/themes/icon.css");
        $this->view->headScript()
                ->appendFile("/easyui/jquery.easyui.min.js")
                ->appendFile("/easyui/jquery.edatagrid.js")
                ->appendFile("/easyui/datagrid-filter.js")
                ->appendFile("/easyui/locale/easyui-lang-es.js")
                ->appendFile("/js/common/highcharts/js/highcharts.js")
                ->appendFile("/js/common/highcharts/js/modules/data.js")
                ->appendFile("/js/common/highcharts/js/modules/exporting.js")
                ->appendFile("/js/administracion/index/reporte-trafico.js?" . time());
    }
    
    public function verFolioAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Ver folio";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/easyui/themes/default/easyui.css")
                ->appendStylesheet("/easyui/themes/icon.css");
        $this->view->headScript()
                ->appendFile("/easyui/jquery.easyui.min.js")
                ->appendFile("/easyui/jquery.edatagrid.js")
                ->appendFile("/easyui/datagrid-filter.js")
                ->appendFile("/easyui/locale/easyui-lang-es.js")
                ->appendFile("/js/common/highcharts/js/highcharts.js")
                ->appendFile("/js/common/highcharts/js/modules/data.js")
                ->appendFile("/js/common/highcharts/js/modules/exporting.js")
                ->appendFile("/js/administracion/index/ver-folio.js?" . time());
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
            
            if(isset($arr[0]['id'])) {
                
                $arrc = $mapper->conceptos($arr[0]['id']);
                if (!empty($arrc)) {
                    $arr[0]['conceptos'] = $arrc;
                } else {
                    $arr[0]['conceptos'] = null;
                }
                
            }
            $this->view->invoice = $arr[0];
        }
    }
    
}
