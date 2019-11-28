<?php

class Operaciones_IndexController extends Zend_Controller_Action {

    protected $_session;
    protected $_soapClient;
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
                ->appendStylesheet("/css/fontawesome/css/fontawesome-all.min.css")
                ->appendStylesheet("/less/traffic-module.css?" . time());
        $this->view->headScript()
                ->appendFile("/js/common/jquery-1.9.1.min.js")
                ->appendFile("/js/common/bootstrap/js/bootstrap.min.js")
                ->appendFile("/js/common/jquery.form.min.js")
                ->appendFile("/js/common/jquery.validate.min.js")
                ->appendFile("/js/common/additional-methods.min.js")
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
    }

    public function indexAction() {
        $this->view->title = $this->_appconfig->getParam('title') . ' ' . " Panel operaciones";
        $this->view->headMeta()->appendName('description', '');
    }

    public function anexo24Action() {        
        $this->view->title = $this->_appconfig->getParam('title') . ' ' . " Anexo 24";
        $this->view->headMeta()->appendName('description', '');
        $this->view->headLink()
                ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css");
        $this->view->headScript()
                ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
                ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js")
                ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js")
                ->appendFile("/js/operaciones/index/anexo-24.js?" . time());
        $inh = new Usuarios_Model_UsuarioInhouse();
        if(!in_array($this->_session->role, array("inhouse"))) {
            $form = new Operaciones_Form_Anexo24Enh();
        } else {
            $form = new Operaciones_Form_Anexo24Enh(array("rfcs" => $inh->obtenerRfcClientes($this->_session->id)));            
        }
        $this->view->form = $form;
    }

    public function excelAnexo24Action() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $search = NULL ? $search = new Zend_Session_Namespace('') : $search = new Zend_Session_Namespace('OAQXml');
        $headers = array(
            "Pedimento" => 'Pedimento',
            "Patente" => 'Patente',
            "Aduana" => 'Aduana',
            "Regimen" => 'Regimen',
            "IE" => 'IE',
            "ClienteRFC" => 'ClienteRFC',
            "NombrePro" => 'NombrePro',
            "TaxID" => 'TaxID',
            "Referencia" => 'Referencia',
            "NumPedimento" => 'NumPedimento',
            "FechaPago" => 'FechaPago',
            "Factura" => 'Factura',
            "MonedaFact" => 'MonedaFact',
            "FechaFact" => 'FechaFact',
            "FactDlls" => 'FactDlls',
            "FactMxn" => 'FactMxn',
            "Partida" => 'Partida',
            "FMoneda" => 'FMoneda',
            "Fraccion" => 'Fraccion',
            "NumParte" => 'NumParte',
            "DescNP" => 'DescNP',
            "Unidad" => 'Unidad',
            "Cantidad" => 'Cantidad',
            "CantidadTarifa" => 'CantidadTarifa',
            "UnidadTarifa" => 'UnidadTarifa',
            "TasaADV" => 'TasaADV',
            "Origen" => 'Origen',
            "Vendedor" => 'Vendedor',
            "Incoterm" => 'Incoterm',
            "Cove" => 'Cove',
        );

        $xmlString = file_get_contents($search->filestream);
        $xml = simplexml_load_string($xmlString);
        $result = @json_decode(@json_encode($xml), 1);

        switch ($search->aduana) {
            case 'http://wssitawin.localhost/webservice/customers-keys':
                $aduana = 640;
                break;
            case 'http://192.168.0.253:8081/webservice/customers-keys':
                $aduana = 640;
                break;
            default:
                $aduana = 640;
                break;
        }
        $reports = new OAQ_ExcelExport();
        $reports->anexo24Report($headers, $result['pedimento'], 'pre_anexo24', 'Anexo 24 Preliminar', $aduana, 'Reporte de partes');
    }

    public function relacionCovePedimentoAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(false);
        $search = NULL ? $search = new Zend_Session_Namespace('') : $search = new Zend_Session_Namespace('OAQAvalidacion');
        $validationMapper = new Operaciones_Model_ValidationsMapper();
        $result = $validationMapper->covePedimento($search->rfc, $search->fechaIni, $search->fechaFin);
        $data = array();
        foreach ($result as $item) {
            $tmp['pedimento'] = $item['pedimento'];
            $arr = explode("\n", $item['archivo_content']);
            $cov = array();
            foreach ($arr as $row) {
                if (preg_match('/504/', str_replace(array("\r\n", "\n", "\r"), '', $row))) {
                    $line = explode("|", str_replace(array("\r\n", "\n", "\r"), '', $row));
                    $cov[] = array(
                        'factura' => $line[2],
                        'cove' => $validationMapper->searchForConsolidado($item['pedimento'], $line[2])
                    );
                }
            }
            $tmp['coves'] = $cov;
            $data[] = $tmp;
            unset($cov);
            unset($tmp);
        }
        $this->view->coves = $data;
    }

    public function graficasAction() {
        $this->view->title = $this->_appconfig->getParam('title') . ' ' . " Graficas";
        $this->view->headMeta()->appendName('description', '');
        $this->view->headScript()->appendFile('/js/jquery.form.min.js')
                ->appendFile('/js/typeahead.min.js')
                ->appendFile('/js/zebra_dialog.js');
        $this->view->headLink()->appendStylesheet('/css/nuevo-estilo.css');
        $this->_helper->layout->setLayout('bootstrap-fluid');

        $model = new Automatizacion_Model_WsPedimentosMapper();
        $result = $model->reporte(2014);
    }

    public function validacionAction() {
        $this->view->title = $this->_appconfig->getParam('title') . ' ' . " Validación";
        $this->view->headMeta()->appendName('description', '');
        $this->view->headLink()->appendStylesheet('/css/nuevo-estilo.css');
        $this->view->headScript()->prependScript('var baseurl = "' . $this->view->baseUrl() . '";')
                ->appendFile('/js/jquery.form.min.js')
                ->appendFile('/js/jquery.validate.min.js')
                ->appendFile('/js/operaciones/index/validacion.js?' . time());

        $form = new Operaciones_Form_Validacion(array('usuario' => $this->_session->id));
        $this->view->form = $form;
    }

    public function reporteIvaAction() {
        $this->view->title = $this->_appconfig->getParam('title') . ' ' . " Reporte I.V.A.";
        $this->view->headMeta()->appendName('description', '');
        $this->view->headScript()
                ->appendFile("/js/operaciones/index/reporte-iva.js?" . time());
        $form = new Operaciones_Form_ReporteIva();
        $this->view->form = $form;
    }

    public function validadorAction() {
        $this->view->title = $this->_appconfig->getParam('title') . ' ' . " Validador Manual";
        $this->view->headMeta()->appendName('description', '');
        $this->view->headLink()
                ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css");
        $this->view->headScript()                                
                ->appendFile("/js/common/parallel.js")
                ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
                ->appendFile("/js/common/jquery-linedtextarea.js")
                ->appendFile("/js/operaciones/index/validador.js?" . time());
        $form = new Operaciones_Form_Validador(array('usuario' => $this->_session->id));
        $this->view->idUsuario = $this->_session->id;
        $this->view->form = $form;
    }

    public function operacionesUsuariosAction() {
        $this->view->title = $this->_appconfig->getParam('title') . ' ' . " Operaciones usuarios";
        $this->view->headMeta()->appendName('description', '');
        $this->view->headScript()                
                ->appendFile("/js/common/highcharts/js/highcharts.js")
                ->appendFile("/js/common/highcharts/js/modules/data.js")
                ->appendFile("/js/common/highcharts/js/modules/exporting.js")
                ->appendFile("/js/operaciones/index/operaciones-usuarios.js?" . time());
        $year = $this->_request->getParam('year', (int) date('Y'));
        $patente = $this->_request->getParam('patente', 3589);
        $aduana = $this->_request->getParam('aduana', 640);
        if ($patente == 3589) {
            if ($aduana == 640) {
                $sitawin = new OAQ_Sitawin(true, '192.168.200.5', 'sa', 'adminOAQ123', 'SITAW3010640', 1433, 'Pdo_Mssql');
            } elseif ($aduana == 646) {
                $sitawin = new OAQ_Sitawin(true, '192.168.0.253', 'sa', 'sqlcointer', 'SITAW3589640', 1433, 'Pdo_Mssql');
            } elseif ($aduana == 240) {
                $sitawin = new OAQ_Sitawin(true, '192.168.200.5', 'sa', 'adminOAQ123', 'SITAW3589240', 1433, 'Pdo_Mssql');
            }
        }
        $data = $sitawin->operacionesPorUsuario($year);
        $this->view->data = $data;
        $form = new Operaciones_Form_OperacionesUsuarios();
        $form->populate(array(
            'patente' => $patente,
            'aduana' => $aduana,
            'year' => $year
        ));
        $this->view->form = $form;
    }

    public function operacionesClientesAction() {
        $this->view->title = $this->_appconfig->getParam('title') . ' ' . " Operaciones clientes";
        $this->view->headMeta()->appendName('description', '');
        $this->view->headScript()                
                ->appendFile("/js/common/highcharts/js/highcharts.js")
                ->appendFile("/js/common/highcharts/js/modules/data.js")
                ->appendFile("/js/common/highcharts/js/modules/exporting.js")
                ->appendFile("/js/operaciones/index/operaciones-clientes.js?" . time());
        $year = $this->_request->getParam('year', (int) date('Y'));
        $form = new Operaciones_Form_OperacionesClientes();
        $this->view->form = $form;
    }

    public function operacionesMensualesAction() {
        $this->view->title = $this->_appconfig->getParam('title') . ' ' . " Operaciones mensuales";
        $this->view->headMeta()->appendName('description', '');        
        $this->view->headScript()
                ->appendFile("/js/common/highcharts/js/highcharts.js")
                ->appendFile("/js/common/highcharts/js/modules/data.js")
                ->appendFile("/js/common/highcharts/js/modules/exporting.js")
                ->appendFile("/js/operaciones/index/operaciones-mensuales.js?" . time());
        $year = $this->_request->getParam('year', (int) date('Y'));
        $month = $this->_request->getParam('month', (int) date('m'));
        $form = new Operaciones_Form_OperacionesMensuales();
        $form->populate(array(
            'year' => $year,
            'month' => $month,
        ));
        $this->view->form = $form;
    }

    public function operacionesDiariasAction() {
        $this->view->title = $this->_appconfig->getParam('title') . ' ' . " Operaciones diarias";
        $this->view->headMeta()->appendName('description', '');        
        $this->view->headScript()
                ->appendFile("/js/common/highcharts/js/highcharts.js")
                ->appendFile("/js/common/highcharts/js/modules/data.js")
                ->appendFile("/js/common/highcharts/js/modules/exporting.js")
                ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js")
                ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js")
                ->appendFile("/js/operaciones/index/operaciones-diarias.js?" . time());
        $form = new Operaciones_Form_OperacionesDiarias();
        $fecha = $this->_request->getParam('fecha', date('Y-m-d'));
        $form->populate(array(
            'fecha' => $fecha,
            'year' => date('Y', strtotime($fecha)),
            'month' => date('m', strtotime($fecha)),
            'day' => date('d', strtotime($fecha)),
        ));
        $this->view->form = $form;
        if (isset($fecha)) {
            $sitawin = new OAQ_Sitawin(true, '192.168.200.5', 'sa', 'adminOAQ123', 'SITAW3010640', 1433, 'Pdo_Mssql');
            $data = $sitawin->operacionesPorDiaUsuarios(date('Y', strtotime($fecha)), date('m', strtotime($fecha)), date('d', strtotime($fecha)));
            $cust = $sitawin->operacionesPorDiaClientes(date('Y', strtotime($fecha)), date('m', strtotime($fecha)), date('d', strtotime($fecha)));
            $impexp = $sitawin->operacionesPorDiaImpExp(date('Y', strtotime($fecha)), date('m', strtotime($fecha)), date('d', strtotime($fecha)));
            $this->view->qro = $data;
            $this->view->qrocli = $cust;
            $this->view->qroie = $impexp;
            unset($sitawin);
            $sitawin = new OAQ_Sitawin(true, '192.168.0.253', 'sa', 'sqlcointer', 'SITAW3589640', 1433, 'Pdo_Mssql');
            $data = $sitawin->operacionesPorDiaUsuarios(date('Y', strtotime($fecha)), date('m', strtotime($fecha)), date('d', strtotime($fecha)));
            $cust = $sitawin->operacionesPorDiaClientes(date('Y', strtotime($fecha)), date('m', strtotime($fecha)), date('d', strtotime($fecha)));
            $impexp = $sitawin->operacionesPorDiaImpExp(date('Y', strtotime($fecha)), date('m', strtotime($fecha)), date('d', strtotime($fecha)));
            $this->view->aero = $data;
            $this->view->aerocli = $cust;
            $this->view->aeroie = $impexp;
            unset($sitawin);
            $sitawin = new OAQ_Sitawin(true, '192.168.200.5', 'sa', 'adminOAQ123', 'SITAW3589240', 1433, 'Pdo_Mssql');
            $data = $sitawin->operacionesPorDiaUsuarios(date('Y', strtotime($fecha)), date('m', strtotime($fecha)), date('d', strtotime($fecha)));
            $cust = $sitawin->operacionesPorDiaClientes(date('Y', strtotime($fecha)), date('m', strtotime($fecha)), date('d', strtotime($fecha)));
            $impexp = $sitawin->operacionesPorDiaImpExp(date('Y', strtotime($fecha)), date('m', strtotime($fecha)), date('d', strtotime($fecha)));
            $this->view->nl = $data;
            $this->view->nlcli = $cust;
            $this->view->nlie = $impexp;
        }
    }

    public function cargarPlantillaAction() {
        $this->view->title = $this->_appconfig->getParam('title') . ' ' . " SITAWIN Plantilla";
        $this->view->headMeta()->appendName('description', '');
        $this->view->headLink()
                ->appendStylesheet('/css/nuevo-estilo.css')
                ->appendStylesheet('/less/traffic-module.css');
        $this->view->headScript()
                ->appendFile('/js/jquery.form.min.js')
                ->appendFile('/js/jquery.validate.min.js')
                ->appendFile('/js/common/jquery.floatThead.js')
                ->appendFile('/js/common/jquery.floatThead-slim.js')
                ->appendFile('/js/operaciones/index/cargar-plantilla.js?' . time());
        $mapper = new Operaciones_Model_PlantillaPedimentos();
        $array = $mapper->pedimentos();
        $this->view->pedimentos = $array;
        
    }

}
