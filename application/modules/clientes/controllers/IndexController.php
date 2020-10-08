<?php

class Clientes_IndexController extends Zend_Controller_Action
{

    protected $_session;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;

    public function init()
    {
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper('Redirector');
        $this->view->headLink(array("rel" => "icon shortcut", "href" => "/favicon.png"));
        $this->view->headLink()
            ->appendStylesheet("/js/common/bootstrap/css/bootstrap.min.css")
            ->appendStylesheet("/js/common/bootstrap/datepicker/css/datepicker.css")
            ->appendStylesheet("/css/DT_bootstrap.css")
            ->appendStylesheet("/css/fontawesome/css/fontawesome-all.min.css")
            ->appendStylesheet("/less/traffic-module.css?" . time());
        $this->view->headScript()
            ->appendFile("/js/common/js.cookie.js")
            ->appendFile("/js/common/jquery-1.9.1.min.js")
            ->appendFile("/js/common/bootstrap/js/bootstrap.min.js")
            ->appendFile("/js/common/bootstrap/datepicker/js/bootstrap-datepicker.js")
            ->appendFile("/js/common/jquery.form.min.js")
            ->appendFile("/js/common/jquery.validate.min.js")
            ->appendFile("/js/common/jquery.dataTables.min.js")
            ->appendFile("/js/common/DT_bootstrap.js")
            ->appendFile("/js/common/jquery.blockUI.js");
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
    }

    public function preDispatch()
    {
        $this->_session = NULL ? $this->_session = new Zend_Session_Namespace('') : $this->_session = new Zend_Session_Namespace($this->_config->app->namespace);
        if ($this->_session->authenticated == true) {
            $this->_session->setExpirationSeconds($this->_appconfig->getParam('session-exp'));
        } else {
            $this->getResponse()->setRedirect($this->_appconfig->getParam('link-logout'));
        }
        $mapper = new Application_Model_MenuAccesos();
        $this->view->menu = $mapper->obtenerPorRol(6);
        $this->view->username = $this->_session->username;
        $this->view->rol = "cliente";
    }

    public function indexAction()
    {
        $this->view->title = $this->_appconfig->getParam('title') . ' ' . " Clientes";
        $this->view->headMeta()->appendName('description', '');
        $this->view->headScript()
            ->appendFile("/js/common/highcharts/js/highcharts.js")
            ->appendFile("/js/common/highcharts/js/modules/data.js")
            ->appendFile("/js/common/highcharts/js/modules/exporting.js")
            ->appendFile("/js/clientes/index/index.js?" . time());
        $year = $this->_request->getParam('year', (int) date('Y'));
        $this->view->year = $year;
        $this->view->rfc = $this->_session->username;
        $this->view->name = $this->_session->nombre;
        $model = new Application_Model_TipoCambio();
        $tipo = $model->obtener(date('Y-m-d'));
        if ($tipo !== false) {
            $this->view->cambio = $tipo;
        }
        $this->view->year = date("Y");
        $mppr = new Trafico_Model_TraficosMapper();
        $arr = $mppr->traficosDeCliente($this->_session->username, date("Y"));
        if (!empty($arr)) {
            $data = array();
            foreach ($arr as $item) {
                if (isset($item["idAduana"])) {
                    $item["porMes"] = array(
                        1 => $mppr->traficosDeClientePorMes($this->_session->username, date("Y"), 1, $item["idAduana"]),
                        2 => $mppr->traficosDeClientePorMes($this->_session->username, date("Y"), 2, $item["idAduana"]),
                        3 => $mppr->traficosDeClientePorMes($this->_session->username, date("Y"), 3, $item["idAduana"]),
                        4 => $mppr->traficosDeClientePorMes($this->_session->username, date("Y"), 4, $item["idAduana"]),
                        5 => $mppr->traficosDeClientePorMes($this->_session->username, date("Y"), 5, $item["idAduana"]),
                        6 => $mppr->traficosDeClientePorMes($this->_session->username, date("Y"), 6, $item["idAduana"]),
                        7 => $mppr->traficosDeClientePorMes($this->_session->username, date("Y"), 7, $item["idAduana"]),
                        8 => $mppr->traficosDeClientePorMes($this->_session->username, date("Y"), 8, $item["idAduana"]),
                        9 => $mppr->traficosDeClientePorMes($this->_session->username, date("Y"), 9, $item["idAduana"]),
                        10 => $mppr->traficosDeClientePorMes($this->_session->username, date("Y"), 10, $item["idAduana"]),
                        11 => $mppr->traficosDeClientePorMes($this->_session->username, date("Y"), 11, $item["idAduana"]),
                        12 => $mppr->traficosDeClientePorMes($this->_session->username, date("Y"), 12, $item["idAduana"]),
                    );
                    $data[] = $item;
                }
            }
        }
        if (isset($data) && !empty($data)) {
            $this->view->results = $data;
        }
    }

    public function archivosExpedienteAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Expediente";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
            ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css");
        $this->view->headScript()
            ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
            ->appendFile("/js/clientes/index/archivos-expediente.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => "Digits",
        );
        $v = array(
            "id" => array("NotEmpty", new Zend_Validate_Int()),
        );
        $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($i->isValid("id")) {
            $mppr = new Clientes_Model_Repositorio();
            $arr = $mppr->datos($i->id, $this->_session->username);
            $form = new Archivo_Form_ArchivosExpediente();
            $form->populate(array(
                "patente" => $arr["patente"],
                "aduana" => $arr["aduana"],
                "pedimento" => $arr["pedimento"],
                "referencia" => $arr["referencia"],
            ));
            if (isset($arr["idTrafico"]) && $arr["idTrafico"] !== null) {
                $this->view->idTrafico = $arr["idTrafico"];
            }
            $form->patente->setAttrib("disabled", "disabled");
            $form->aduana->setAttrib("disabled", "disabled");
            $form->pedimento->setAttrib("disabled", "disabled");
            $form->referencia->setAttrib("disabled", "disabled");
            $this->view->form = $form;
            $this->view->id = $i->id;
            $files = $mppr->archivosCliente($arr["referencia"], $arr["patente"], $arr["aduana"]);
            $this->view->cantidad = count($files);
            if (isset($files) && !empty($files)) {
                $this->view->files = $files;
            }
            $complementos = $mppr->complementosReferencia($arr["referencia"]);
            if (!empty($complementos)) {
                $this->view->complementos = $complementos;
            }

            $val = new OAQ_ArchivosValidacion();
            if (isset($arr["pedimento"])) {
                $this->view->validacion = $val->archivosDePedimento($arr["patente"], $arr["aduana"], $arr["pedimento"]);
            }

            if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN") {
            } else {
                $directory = $this->_appconfig->getParam("expdest") . DIRECTORY_SEPARATOR . $arr["patente"] . DIRECTORY_SEPARATOR . $arr["aduana"] . DIRECTORY_SEPARATOR . $arr["referencia"];
                if (file_exists($directory)) {
                    $salida = shell_exec('du -h ' . $directory);
                    $array = explode(" ", preg_replace('/\s+/', ' ', trim($salida)));
                    if (isset($array[0])) {
                        $quantity = substr($array[0], 0, -1);
                        $measure = substr($array[0], -1);
                        if (isset($quantity) && isset($measure)) {
                            if ((int) $quantity > 25 && strtoupper($measure) == "M") {
                                if ($measure == "M") {
                                    $measure = "Mb";
                                } elseif ($measure == "K") {
                                    $measure = "Kb";
                                }
                                $this->view->downloadZip = array(
                                    "size" => $quantity . " " . $measure,
                                    "message" => "Tamaño del expediente: "
                                );
                            }
                        }
                    }
                }
            }
        } else {
            throw new Exception("Invalid input!");
        }
    }

    public function expedienteAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Expediente Digital";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
            ->appendFile("/js/clientes/index/expediente.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "page" => "Digits",
            "size" => "Digits",
            "pedimento" => "Digits",
            "referencia" => "StringToUpper",
        );
        $v = array(
            "page" => array(new Zend_Validate_Int(), "default" => 1),
            "size" => array(new Zend_Validate_Int(), "default" => 25),
            "pedimento" => array("NotEmpty", new Zend_Validate_Int()),
            "referencia" => array("NotEmpty", new Zend_Validate_Regex("/^[-_a-zA-Z0-9.]+$/")),
        );
        $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        $form = new Archivo_Form_Referencias();
        $mppr = new Clientes_Model_Repositorio();
        $rows = $mppr->referencias($this->_session->username, $i->pedimento, $i->referencia);
        if (isset($rows) && !empty($rows)) {
            $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array($rows));
            $paginator->setItemCountPerPage($i->size);
            $paginator->setCurrentPageNumber($i->page);
            $this->view->paginator = $paginator;
        }
        $form->populate(array(
            "referencia" => $i->referencia,
            "pedimento" => $i->pedimento,
        ));
        $this->view->form = $form;
    }

    public function cuentaDeGastosAction()
    {
        $this->view->title = $this->_appconfig->getParam('title') . ' ' . " Cuenta de gastos";
        $this->view->headMeta()->appendName('description', '');
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
            ->appendFile("/js/clientes/index/cuenta-de-gastos.js?" . time());
    }

    public function excelCuentaDeGastosAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $search = NULL ? $search = new Zend_Session_Namespace('') : $search = new Zend_Session_Namespace('OAQCtaGastos');

        if ($search->desglose == '0') {
            $headers = array(
                'Fecha' => 'fecha_factura',
                'Folio' => 'factura',
                'Referencia' => 'referencia',
                'Aduana' => 'aduana',
                'Patente' => 'patente',
                'Pedimento' => 'pedimento',
                'IE' => 'ie',
                'Cve. Doc' => 'regimen',
                'Fecha Pedimento' => 'fecha_pedimento',
                'Factura Pedimento' => 'ref_factura',
                'Bultos/Piezas' => 'bultos',
                'Valor Aduana' => 'valor_aduana',
                'Maniobras' => 'maniobras',
                'Almacenajes' => 'almacenaje',
                'Demoras' => 'demoras',
                'Flete aereo' => 'fleteaereo',
                'Flete marítimo' => 'fletemaritimo',
                'Fletes acarreos' => 'fletesacarreos',
                'Gastos complementarios' => 'gastos_complementarios',
                'Gastos alijadores' => 'gastos_alijadores',
                'Impuestos Aduanales' => 'impuestos_aduanales',
                'Revalidación' => 'revalidacion',
                'Rectificación' => 'rectificaciones',
                'Honorarios' => 'honorarios',
                'Sub Total' => 'subtotal',
                'IVA' => 'iva',
                'Anticipo' => 'anticipo',
                'Total' => 'total',
            );
        } else if ($search->desglose == '1') {
            $headers = array(
                'Fecha' => 'fecha_factura',
                'Folio' => 'factura',
                'Referencia' => 'referencia',
                'Aduana' => 'aduana',
                'Patente' => 'patente',
                'Pedimento' => 'pedimento',
                'IE' => 'ie',
                'Cve. Doc' => 'regimen',
                'Fecha Pedimento' => 'fecha_pedimento',
                'Factura Pedimento' => 'ref_factura',
                'Bultos/Piezas' => 'bultos',
                'Valor Aduana' => 'valor_aduana',
                'Maniobras Subtotal' => 'subtotal_maniobras',
                'Maniobras IVA' => 'iva_maniobras',
                'Maniobras Total' => 'maniobras',
                'Almacenaje Subtotal' => 'subtotal_almacenaje',
                'Almacenaje IVA' => 'iva_almacenaje',
                'Almacenaje Total' => 'almacenaje',
                'Demoras Subtotal' => 'subtotal_demoras',
                'Demoras IVA' => 'iva_demoras',
                'Demoras Total' => 'demoras',
                'Flete Aéreo Subtotal' => 'subtotal_fleteaereo',
                'Flete Aéreo IVA' => 'iva_fleteaereo',
                'Flete Aéreo Total' => 'fleteaereo',
                'Flete Marítimo Subtotal' => 'subtotal_fletemaritimo',
                'Flete Marítimo IVA' => 'iva_fletemaritimo',
                'Flete Marítimo Total' => 'fletemaritimo',
                'Flete Terrestre Subtotal' => 'subtotal_fleteterrestre',
                'Flete Terrestre IVA' => 'iva_fleteterrestre',
                'Flete Terrestre Total' => 'fleteterrestre',
                'Flete y Acarreos Subtotal' => 'subtotal_fletesacarreos',
                'Flete y Acarreos IVA' => 'iva_fletesacarreos',
                'Flete y Acarreos Total' => 'fletesacarreos',
                'Gastos complementarios' => 'gastos_complementarios',
                'Gastos alijadores' => 'gastos_alijadores',
                'Impuestos Aduanales' => 'impuestos_aduanales',
                'Revalidación' => 'revalidacion',
                'Rectificación' => 'rectificaciones',
                'Honorarios' => 'honorarios',
                'Sub Total' => 'subtotal',
                'IVA' => 'iva',
                'Anticipo' => 'anticipo',
                'Total' => 'total',
            );
        }
        $sica = new OAQ_Sica;
        $misc = new OAQ_Misc();
        $clienteId = $sica->getCustomerId(strtoupper($search->rfc));
        $result = $sica->getInvoices((int) $clienteId, $search->fechaIni, $search->fechaFin);
        if (!($result = $misc->checkCache('ctagastos' . $this->_session->rfc . date('Ymd')))) {
            $result = $sica->getInvoices((int) $clienteId, $search->fechaIni, $search->fechaFin);
        }
        $customerInfo = $sica->getCustomerName((int) $clienteId);

        $reports = new OAQ_ExcelExport();
        $reports->createSimpleReport($headers, $result, 'ctagastos', 'Cuenta de gastos', $search->fechaIni, $search->fechaFin, 'Reporte de cuenta de gastos', $search->rfc, $customerInfo['nombre']);
    }

    public function archivosXmlAction()
    {
        $this->view->title = $this->_appconfig->getParam('title') . ' ' . " Archivos XML de cuenta de gastos";
        $this->view->headMeta()->appendName('description', '');
        $this->view->headScript()
            ->appendFile("/js/clientes/index/archivos-xml.js?" . time());
        $search = NULL ? $search = new Zend_Session_Namespace('') : $search = new Zend_Session_Namespace('OAQCtaGastos');
        $page = $this->_request->getParam('page', 1);
        $archive = new Archivo_Model_CuentasGastosMapper();
        $form = new Clientes_Form_CtaGastos(array('desglose' => false));
        $form->populate(array(
            'rfc' => $this->_session->rfc,
            'fechaIni' => (isset($search->arch_fini)) ? $search->arch_fini : date('Y-m-d'),
            'fechaFin' => (isset($search->arch_ffin)) ? $search->arch_fini : date('Y-m-d'),
        ));
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $search->arch_rfc = $this->_session->rfc;
            $search->arch_fini = $data["fechaIni"];
            $search->arch_ffin = $data["fechaFin"];

            $form->populate(array(
                'rfc' => $this->_session->rfc,
                'fechaIni' => $search->arch_fini,
                'fechaFin' => $search->arch_ffin,
            ));
            $this->view->fechaIni = $search->arch_fini;
            $this->view->fechaFin = $search->arch_ffin;

            $result = $archive->getByRfc($this->_session->rfc, $search->arch_fini, $search->arch_ffin);
            $searched = true;
        } else {
            if (isset($search->arch_rfc) && $search->arch_rfc != null) {
                $form->populate(array(
                    'rfc' => $search->arch_rfc,
                    'fechaIni' => $search->arch_fini,
                    'fechaFin' => $search->arch_ffin,
                ));
                $result = $archive->getByRfc($this->_session->rfc, $search->arch_fini, $search->arch_ffin);
                $searched = true;
            }
        }
        if (isset($result) && isset($searched) && !empty($result)) {
            $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array($result));
            $paginator->setItemCountPerPage(30);
            $paginator->setCurrentPageNumber($page);
            $this->view->paginator = $paginator;
        } elseif (empty($result) && isset($searched)) {
            $this->view->searched = true;
        }
        $this->view->form = $form;
    }

    public function excelPedimentosPagadosAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $search = NULL ? $search = new Zend_Session_Namespace('') : $search = new Zend_Session_Namespace('OAQPedimentos');

        $headers = array(
            'Patente' => 'patente',
            'Aduana' => 'aduana',
            'Referencia' => 'referencia',
            'Pedimento' => 'pedimento',
            'Fecha de Pago' => 'fecha_pago',
            'Cve. Doc.' => 'cve_doc',
            'IMP-EXP' => 'ie',
            'Firma validación' => 'firma_validacion',
            'Firma banco' => 'firma_banco',
        );

        $misc = new OAQ_Misc();
        $sis = new Usuarios_Model_SisPedimentosMapper();
        $db = $sis->getMySystemData($search->aduana);
        if ($db["nombre"] == 'sitawin') {
            if (!($result = $misc->checkCache('rptpedimentospag' . $this->_session->rfc . date('Ymd')))) {
                $sitawin = new OAQ_Sitawin(true, $db["direccion_ip"], $db["usuario"], $db["pwd"], $db["dbname"], $db["puerto"]);
                $result = $sitawin->obtenerPedimentos($this->_session->rfc, $search->fechaIni, $search->fechaFin);
                $misc->saveCache('rptpedimentospag' . $this->_session->rfc . date('Ymd'), $result);
            }
        }
        $reports = new OAQ_ExcelExport();
        $reports->createSimpleReport($headers, $result, 'pedimentospag', 'Pedimentos Pagados', $search->fechaIni, $search->fechaFin, 'Reporte de pedimentos pagados', $this->_session->rfc, $this->_session->nombre);
    }

    public function reporteAnexo24Action()
    {
        $this->view->title = $this->_appconfig->getParam('title') . ' ' . " Reporte de Anexo 24";
        $this->view->headMeta()->appendName('description', '');
        $this->view->headScript()
            ->appendFile("/js/clientes/index/reporte-anexo-24.js?" . time());
        $this->view->rfc = $this->_session->username;
        $custs = new Trafico_Model_ClientesMapper();
        $cust  = $custs->buscarRfc($this->_session->username);
        $this->view->idCliente = $cust["id"];
        $mppr = new Trafico_Model_TraficoAduanasMapper();
        $arr = $mppr->obtenerReporteo();
        $array = array();
        foreach ($arr as $value) {
            $array[$value["id"]] = array(
                "patente" => $value["patente"],
                "aduana" => $value["aduana"],
                "nombre" => $value["nombre"]
            );
        }
        $this->view->arr = $array;
    }

    public function reporteIvaAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Reporte I.V.A.";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
            ->appendFile("/js/clientes/index/reporte-iva.js?" . time());
        $form = new Clientes_Form_ReporteIva();
        $this->view->form = $form;
    }

    public function reporteCovesAction()
    {
        $this->view->title = $this->_appconfig->getParam("title") . " Reporte de COVES";
        $this->view->headMeta()->appendName("description", "");
        //$this->view->headLink()->setContainer(new Zend_View_Helper_Placeholder_Container());
        //$this->view->headScript()->setContainer(new Zend_View_Helper_Placeholder_Container());
        $this->view->headLink()
            ->appendStylesheet("/js/common/bootstrap/css/bootstrap.min.css")
            ->appendStylesheet("/css/DT_bootstrap.css")
            ->appendStylesheet("/less/traffic-module.css?" . time());
        $this->view->headScript()
            ->appendFile("/js/common/jquery-1.9.1.min.js")
            ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js")
            ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js")
            ->appendFile("/js/common/jquery.dataTables.min.js")
            ->appendFile("/js/common/DT_bootstrap.js")
            ->appendFile("/js/clientes/index/reporte-coves.js?" . time());
        $coves = new Clientes_Model_CovesMapper();
        $solicitudes = $coves->getCoves($this->_session->rfc);
        $this->view->result = $solicitudes;
        $this->view->rfc = $this->_session->rfc;
    }

    public function consultarCoveEnviadoAction()
    {
        $this->view->title = $this->_appconfig->getParam('title') . ' ' . " Consulta COVE solicitado";
        $this->view->headMeta()->appendName('description', '');
        $vucemSol = new Clientes_Model_CovesMapper();
        $vucemFact = new Clientes_Model_FacturasMapper();
        $vucem = new OAQ_Vucem();
        $id = $this->_request->getParam('id', null);

        if (($xml = $vucemSol->obtenerSolicitudPorId($id, $this->_session->rfc))) {
            $fact = $vucemFact->verificarFactura($xml["solicitud"], $this->_session->rfc);
            if ($fact) {
                $xmlArray = $vucem->vucemXmlToArray($xml["xml"]);
                unset($xmlArray["Header"]);
                if ($xml["cove"] != '' && $xml["cove"] != null) {
                    $this->view->cove = $xml["cove"];
                }
                $this->view->id = $id;
                $this->view->estatus = $xml["estatus"];
                if (isset($xmlArray["Body"]["solicitarRecibirCoveServicio"])) {
                    $this->view->relfact = false;
                    $this->view->data = $xmlArray["Body"]["solicitarRecibirCoveServicio"]["comprobantes"];
                } elseif (isset($xmlArray["Body"]["solicitarRecibirRelacionFacturasIAServicio"])) {
                    $this->view->relfact = true;
                    $this->view->data = $xmlArray["Body"]["solicitarRecibirRelacionFacturasIAServicio"]["comprobantes"];
                }
            }
        }
    }

    public function archivosM3Action()
    {
        $this->view->title = $this->_appconfig->getParam('title') . ' ' . " Archivos M3";
        $this->view->headMeta()->appendName('description', '');
        $this->view->headScript()
            ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js")
            ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js")
            ->appendFile("/js/clientes/index/archivos-m3.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "page" => "Digits",
            "size" => "Digits",
        );
        $v = array(
            "page" => array(new Zend_Validate_Int(), "default" => 1),
            "size" => array(new Zend_Validate_Int(), "default" => 25),
            "fechaIni" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/"), "default" => date("Y-m-" . "01")),
            "fechaFin" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/"), "default" => date("Y-m-d")),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        $form = new Archivo_Form_ArchivosM3();
        $mapper = new Automatizacion_Model_ArchivosValidacionPedimentosMapper();
        $result = $mapper->pedimentosPagadosRango(trim(strtoupper($this->_session->username)), $input->fechaIni, $input->fechaFin);
        $form->populate(array(
            'fechaIni' => $input->fechaIni,
            'fechaFin' => $input->fechaFin,
        ));
        $this->view->form = $form;
        if (isset($result) && !empty($result)) {
            $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array($result));
            $paginator->setItemCountPerPage($input->size);
            $paginator->setCurrentPageNumber($input->page);
            $this->view->paginator = $paginator;
            $this->view->fechaIni = $input->fechaIni;
            $this->view->fechaFin = $input->fechaFin;
        }
    }

    public function landingAction()
    {
        $this->_helper->layout->setLayout("gentelella/default");
        $this->view->title = $this->_appconfig->getParam("title") . " Bienvenido";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()->setContainer(
            new Zend_View_Helper_Placeholder_Container()
        );
        $this->view->headScript()->exchangeArray(array());
        $this->view->headLink()
            ->appendStylesheet("/gentelella/vendors/bootstrap/dist/css/bootstrap.min.css")
            ->appendStylesheet("/gentelella/vendors/font-awesome/css/font-awesome.min.css")
            ->appendStylesheet("/gentelella/vendors/nprogress/nprogress.css")
            ->appendStylesheet("/gentelella/vendors/iCheck/skins/flat/green.css")
            ->appendStylesheet("/gentelella/vendors/bootstrap-progressbar/css/bootstrap-progressbar-3.3.4.min.css")
            ->appendStylesheet("/gentelella/vendors/jqvmap/dist/jqvmap.min.css")
            ->appendStylesheet("/gentelella/vendors/bootstrap-daterangepicker/daterangepicker.css")
            ->appendStylesheet("/gentelella/build/css/custom.min.css");
        $this->view->headScript()
            ->appendFile("/gentelella/vendors/jquery/dist/jquery.min.js")
            ->appendFile("/gentelella/vendors/bootstrap/dist/js/bootstrap.min.js")
            ->appendFile("/gentelella/vendors/fastclick/lib/fastclick.js")
            ->appendFile("/gentelella/vendors/nprogress/nprogress.js")
            ->appendFile("/gentelella/vendors/Chart.js/dist/Chart.min.js")
            ->appendFile("/gentelella/vendors/gauge.js/dist/gauge.min.js")
            ->appendFile("/gentelella/vendors/bootstrap-progressbar/bootstrap-progressbar.min.js")
            ->appendFile("/gentelella/vendors/iCheck/icheck.min.js")
            ->appendFile("/gentelella/vendors/skycons/skycons.js")
            ->appendFile("/gentelella/vendors/Flot/jquery.flot.js")
            ->appendFile("/gentelella/vendors/Flot/jquery.flot.pie.js")
            ->appendFile("/gentelella/vendors/Flot/jquery.flot.time.js")
            ->appendFile("/gentelella/vendors/Flot/jquery.flot.stack.js")
            ->appendFile("/gentelella/vendors/Flot/jquery.flot.resize.js")
            ->appendFile("/gentelella/vendors/flot.orderbars/js/jquery.flot.orderBars.js")
            ->appendFile("/gentelella/vendors/flot-spline/js/jquery.flot.spline.min.js")
            ->appendFile("/gentelella/vendors/flot.curvedlines/curvedLines.js")
            ->appendFile("/gentelella/vendors/DateJS/build/date.js")
            ->appendFile("/gentelella/vendors/jqvmap/dist/jquery.vmap.js")
            ->appendFile("/gentelella/vendors/jqvmap/dist/maps/jquery.vmap.world.js")
            ->appendFile("/gentelella/vendors/jqvmap/examples/js/jquery.vmap.sampledata.js")
            ->appendFile("/gentelella/vendors/moment/min/moment.min.js")
            ->appendFile("/gentelella/vendors/bootstrap-daterangepicker/daterangepicker.js")
            ->appendFile("/gentelella/build/js/custom.js?" . time());
    }

    public function traficoAction()
    {
        $this->view->title = $this->_appconfig->getParam('title') . ' ' . " Traficos";
        $this->view->headMeta()->appendName('description', '');
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
            ->appendFile("/js/clientes/index/trafico.js?" . time());
    }

    public function verTraficoAction()
    {
        $this->view->title = $this->_appconfig->getParam('title') . ' ' . " Trafico";
        $this->view->headMeta()->appendName('description', '');
        $this->view->headLink()
            ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
            ->appendStylesheet("/css/jquery.qtip.min.css")
            ->appendStylesheet("/js/common/toast/jquery.toast.min.css")
            ->appendStylesheet("/easyui/themes/default/easyui.css")
            ->appendStylesheet("/easyui/themes/icon.css");
        $this->view->headScript()
            ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
            ->appendFile("/easyui/jquery.easyui.min.js")
            ->appendFile("/easyui/jquery.edatagrid.js")
            ->appendFile("/easyui/datagrid-filter.js")
            ->appendFile("/easyui/locale/easyui-lang-es.js")
            ->appendFile("/fullcalendar/lib/moment.min.js")
            ->appendFile("/js/common/jquery.qtip.min.js")
            ->appendFile("/js/common/toast/jquery.toast.min.js?")
            ->appendFile("/js/common/loadingoverlay.min.js")
            ->appendFile("/js/clientes/index/ver-trafico.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits"),
        );
        $v = array(
            "id" => array(new Zend_Validate_Int(), "NotEmpty"),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id")) {
            $traficos = new OAQ_Trafico(array("idTrafico" => $input->id));
            $arr = $traficos->obtenerDatos();
            $this->view->id_trafico = $input->id;
            $this->view->basico = $arr;
        }
    }

    public function traficosAction()
    {
        $this->_helper->layout->setLayout("gentelella/default");
        $this->view->title = $this->_appconfig->getParam("title") . " Traficos";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()->setContainer(
            new Zend_View_Helper_Placeholder_Container()
        );
        $this->view->headScript()->exchangeArray(array());
        $this->view->headLink()
            ->appendStylesheet("/gentelella/vendors/bootstrap/dist/css/bootstrap.min.css")
            ->appendStylesheet("/gentelella/vendors/font-awesome/css/font-awesome.min.css")
            ->appendStylesheet("/gentelella/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css")
            ->appendStylesheet("/gentelella/vendors/datatables.net-buttons-bs/css/buttons.bootstrap.min.css")
            ->appendStylesheet("/gentelella/vendors/datatables.net-fixedheader-bs/css/fixedHeader.bootstrap.min.css")
            ->appendStylesheet("/gentelella/vendors/datatables.net-responsive-bs/css/responsive.bootstrap.min.css")
            ->appendStylesheet("/gentelella/vendors/datatables.net-scroller-bs/css/scroller.bootstrap.min.css")
            ->appendStylesheet("/gentelella/vendors/bootstrap-daterangepicker/daterangepicker.css")
            ->appendStylesheet("/gentelella/build/css/custom.min.css");
        $this->view->headScript()
            ->appendFile("/gentelella/vendors/jquery/dist/jquery.min.js")
            ->appendFile("/gentelella/vendors/bootstrap/dist/js/bootstrap.min.js")
            ->appendFile("/gentelella/vendors/fastclick/lib/fastclick.js")
            ->appendFile("/gentelella/vendors/nprogress/nprogress.js")
            ->appendFile("/gentelella/vendors/iCheck/icheck.min.js")
            ->appendFile("/gentelella/vendors/datatables.net/js/jquery.dataTables.min.js")
            ->appendFile("/gentelella/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js")
            ->appendFile("/gentelella/vendors/datatables.net-buttons/js/dataTables.buttons.min.js")
            ->appendFile("/gentelella/vendors/datatables.net-buttons-bs/js/buttons.bootstrap.min.js")
            ->appendFile("/gentelella/vendors/datatables.net-buttons/js/buttons.flash.min.js")
            ->appendFile("/gentelella/vendors/datatables.net-buttons/js/buttons.html5.min.js")
            ->appendFile("/gentelella/vendors/datatables.net-buttons/js/buttons.print.min.js")
            ->appendFile("/gentelella/vendors/datatables.net-fixedheader/js/dataTables.fixedHeader.min.js")
            ->appendFile("/gentelella/vendors/datatables.net-keytable/js/dataTables.keyTable.min.js")
            ->appendFile("/gentelella/vendors/datatables.net-responsive/js/dataTables.responsive.min.js")
            ->appendFile("/gentelella/vendors/datatables.net-responsive-bs/js/responsive.bootstrap.js")
            ->appendFile("/gentelella/vendors/datatables.net-scroller/js/dataTables.scroller.min.js")
            ->appendFile("/gentelella/vendors/jszip/dist/jszip.min.js")
            ->appendFile("/gentelella/vendors/pdfmake/build/pdfmake.min.js")
            ->appendFile("/gentelella/vendors/pdfmake/build/vfs_fonts.js")
            ->appendFile("/gentelella/vendors/moment/min/moment.min.js")
            ->appendFile("/gentelella/vendors/bootstrap-daterangepicker/daterangepicker.js")
            ->appendFile("/gentelella/build/js/custom.js?" . time());
        $request = new Zend_Controller_Request_Http();
        $impos = filter_var($request->getCookie("impos"), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $expos = filter_var($request->getCookie("expos"), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "page" => array("Digits"),
            "size" => array("Digits"),
            "buscar" => array("StringToUpper"),
            "idAduana" => array("Digits"),
        );
        $v = array(
            "page" => array(new Zend_Validate_Int(), "default" => 1),
            "size" => array(new Zend_Validate_Int(), "default" => 20),
            "buscar" => "NotEmpty",
            "idAduana" => array("NotEmpty", new Zend_Validate_Int()),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        $model = new Trafico_Model_TraficosMapper();
        $arr = $model->obtenerTraficosClientes();
        if (isset($arr) && !empty($arr)) {
            $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array($arr));
            $paginator->setItemCountPerPage($input->size);
            $paginator->setCurrentPageNumber($input->page);
            $this->view->paginator = $paginator;
        }
    }

    public function expedientesAction()
    {
        $this->_helper->layout->setLayout("gentelella/default");
        $this->view->title = $this->_appconfig->getParam("title") . " Traficos";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()->setContainer(
            new Zend_View_Helper_Placeholder_Container()
        );
        $this->view->headScript()->exchangeArray(array());
        $this->view->headLink()
            ->appendStylesheet("/gentelella/vendors/bootstrap/dist/css/bootstrap.min.css")
            ->appendStylesheet("/gentelella/vendors/font-awesome/css/font-awesome.min.css")
            ->appendStylesheet("/gentelella/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css")
            ->appendStylesheet("/gentelella/vendors/datatables.net-buttons-bs/css/buttons.bootstrap.min.css")
            ->appendStylesheet("/gentelella/vendors/datatables.net-fixedheader-bs/css/fixedHeader.bootstrap.min.css")
            ->appendStylesheet("/gentelella/vendors/datatables.net-responsive-bs/css/responsive.bootstrap.min.css")
            ->appendStylesheet("/gentelella/vendors/datatables.net-scroller-bs/css/scroller.bootstrap.min.css")
            ->appendStylesheet("/gentelella/vendors/bootstrap-daterangepicker/daterangepicker.css")
            ->appendStylesheet("/gentelella/build/css/custom.min.css");
        $this->view->headScript()
            ->appendFile("/gentelella/vendors/jquery/dist/jquery.min.js")
            ->appendFile("/gentelella/vendors/bootstrap/dist/js/bootstrap.min.js")
            ->appendFile("/gentelella/vendors/fastclick/lib/fastclick.js")
            ->appendFile("/gentelella/vendors/nprogress/nprogress.js")
            ->appendFile("/gentelella/vendors/iCheck/icheck.min.js")
            ->appendFile("/gentelella/vendors/datatables.net/js/jquery.dataTables.min.js")
            ->appendFile("/gentelella/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js")
            ->appendFile("/gentelella/vendors/datatables.net-buttons/js/dataTables.buttons.min.js")
            ->appendFile("/gentelella/vendors/datatables.net-buttons-bs/js/buttons.bootstrap.min.js")
            ->appendFile("/gentelella/vendors/datatables.net-buttons/js/buttons.flash.min.js")
            ->appendFile("/gentelella/vendors/datatables.net-buttons/js/buttons.html5.min.js")
            ->appendFile("/gentelella/vendors/datatables.net-buttons/js/buttons.print.min.js")
            ->appendFile("/gentelella/vendors/datatables.net-fixedheader/js/dataTables.fixedHeader.min.js")
            ->appendFile("/gentelella/vendors/datatables.net-keytable/js/dataTables.keyTable.min.js")
            ->appendFile("/gentelella/vendors/datatables.net-responsive/js/dataTables.responsive.min.js")
            ->appendFile("/gentelella/vendors/datatables.net-responsive-bs/js/responsive.bootstrap.js")
            ->appendFile("/gentelella/vendors/datatables.net-scroller/js/dataTables.scroller.min.js")
            ->appendFile("/gentelella/vendors/jszip/dist/jszip.min.js")
            ->appendFile("/gentelella/vendors/pdfmake/build/pdfmake.min.js")
            ->appendFile("/gentelella/vendors/pdfmake/build/vfs_fonts.js")
            ->appendFile("/gentelella/vendors/moment/min/moment.min.js")
            ->appendFile("/gentelella/vendors/bootstrap-daterangepicker/daterangepicker.js")
            ->appendFile("/gentelella/build/js/custom.js?" . time());
        $request = new Zend_Controller_Request_Http();
        $filtro = filter_var($request->getCookie("filtro"), FILTER_VALIDATE_INT);
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "page" => "Digits",
            "size" => "Digits",
            "aduana" => "Digits",
            "patente" => "Digits",
            "pedimento" => "Digits",
            "referencia" => "StringToUpper",
        );
        $v = array(
            "aduana" => array(new Zend_Validate_Int()),
            "patente" => array(new Zend_Validate_Int()),
            "pedimento" => array(new Zend_Validate_Int()),
            "page" => array(new Zend_Validate_Int(), "default" => 1),
            "size" => array(new Zend_Validate_Int(), "default" => 25),
            "referencia" => array(new Zend_Validate_Regex("/^[-_a-zA-Z0-9.]+$/")),
        );
        $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        $form = new Archivo_Form_Referencias();
        $repo = new Archivo_Model_RepositorioIndex();
        if ($i->isValid("referencia") || $i->isValid("pedimento") || $i->isValid("patente") || $i->isValid("aduana")) {
            $search = array($i->patente, $i->aduana, $i->pedimento, $i->referencia);
            $select = $repo->paginatorSelect($filtro, $search);
        } else {
            $select = $repo->paginatorSelect($filtro);
        }
        if (isset($select) && !empty($select)) {
            $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
            $paginator->setCurrentPageNumber($i->page);
            $paginator->setItemCountPerPage($i->size);
            $this->view->paginator = $paginator;
        }
        $form->populate(array(
            "patente" => $i->patente,
            "aduana" => $i->aduana,
            "pedimento" => $i->pedimento,
            "referencia" => $i->referencia,
        ));
        $this->view->rol = $this->_session->role;
        $this->view->form = $form;
    }

    public function catalogoDePartesAction()
    {
        $this->view->title = $this->_appconfig->getParam('title') . ' ' . " Consulta del catalogo";
        $this->view->headMeta()->appendName('description', '');
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
            ->appendFile("/js/common/contentxmenu/jquery.contextMenu.min.js")
            ->appendFile("/js/clientes/index/catalogo-de-partes.js?" . time());

        $mppr = new Clientes_Model_Clientes();
        $idCliente = $mppr->obtenerId($this->_session->username);
        if ($idCliente) {
            $this->view->idCliente = $idCliente;
        }
    }
}
