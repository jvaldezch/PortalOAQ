<?php

class Trafico_DataController extends Zend_Controller_Action
{

    protected $_session;
    protected $_svucem;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;
    protected $_logger;
    protected $_rolesEditarTrafico;
    protected $_todosClientes;

    public function init()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $this->_logger = Zend_Registry::get("logDb");
        $contextSwitch = $this->_helper->getHelper("contextSwitch");
        $contextSwitch->addActionContext("recent-coves", "json")
            ->addActionContext("borrar-solicitud-cove", "json")
            ->initContext();
    }

    public function preDispatch()
    {
        $this->_session = NULL ? $this->_session = new Zend_Session_Namespace("") : $this->_session = new Zend_Session_Namespace($this->_config->app->namespace);
        if ($this->_session->authenticated == true) {
            $session = new OAQ_Session($this->_session, $this->_appconfig);
            $session->actualizar();
            $session->actualizarSesion();
        } else {
            $this->getResponse()->setRedirect($this->_appconfig->getParam("link-logout"));
        }
        $this->_svucem = NULL ? $this->_svucem = new Zend_Session_Namespace("") : $this->_svucem = new Zend_Session_Namespace("OAQVucem");
        $this->_svucem->setExpirationSeconds($this->_appconfig->getParam("session-exp"));
        $this->_rolesEditarTrafico = array("trafico", "super", "trafico_operaciones", "trafico_aero");
        $this->_todosClientes = array("trafico", "super", "trafico_operaciones", "trafico_aero");
    }

    public function addTrackingNumberAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if (isset($data["guia"]) && $data["guia"] != '') {
                $tn = new Trafico_Model_TraficoGuiasMapper();
                if (($n = $tn->verificarId($data["guia"]))) {
                    $inserted = $tn->actualizarGuia($data["guia"], $data["transportista"], $data["tipoguia"], $data["number"], $this->_session->id);
                    if ($inserted == true) {
                        $array = array(
                            'success' => true
                        );
                        $this->_helper->json($array);
                    }
                }
            }
        }
    }

    public function addInvoiceAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
        }
    }

    public function obtainCustomsAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            if (isset($post["cliente"]) && $post["cliente"] != '') {
                if (in_array($this->_session->role, $this->_todosClientes)) {
                    $mdl = new Trafico_Model_TraficoCliAduanasMapper();
                    $customs = $mdl->clientesAduanas($post["cliente"]);
                } else {
                    $mdl = new Trafico_Model_TraficoUsuClientesMapper();
                    $customs = $mdl->obtenerMisAduanas($post["cliente"], $this->_session->id);
                }
                $html = "<select tabindex=\"2\" class=\"traffic-select-large\" id=\"aduana\" name=\"aduana\">";
                foreach ($customs as $k => $item) {
                    if ($k != '') {
                        $html .= "<option value=\"{$k}\">{$item}</option>";
                    } else {
                        $html .= "<option value=\"\">---</option>";
                    }
                }
                $html .= "</select>";
                $this->_helper->json(array('success' => true, 'html' => $html));
            } else {
                $html = "<select disabled=\"disabled\" tabindex=\"2\" class=\"traffic-select-large\" id=\"aduana\" name=\"aduana\">";
                $html .= "<option value=\"\">---</option>";
                $html .= "</select>";
                $this->_helper->json(array('success' => true, 'html' => $html));
            }
        } else {
            throw new Exception("Invalid request type!");
        }
    }

    public function obtainConceptsAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            if (isset($post["idTipoConcepto"]) && $post["idTipoConcepto"] != '') {
                $tbl = new Trafico_Model_TraficoCuentasMapper();
                $conceptos = $tbl->obtener($post["idTipoConcepto"]);
                $html = "<label for=\"idCuenta\">CONCEPTOS:</label>";
                $html .= "<select class=\"traffic-select-large\" tabindex=\"51\" id=\"idCuenta\" name=\"idCuenta\">";
                $html .= "<option value=\"\">---</option>";
                foreach ($conceptos as $k => $item) {
                    if ($k != '') {
                        $html .= "<option value=\"{$k}\">{$item}</option>";
                    } else {
                        $html .= "<option value=\"\">---</option>";
                    }
                }
                $html .= "</select>";
                $html .= "<label for=\"concepto\">CONCEPTO A MOSTRAR:</label>";
                $html .= '<input name="concepto" id="concepto" value="" class="traffic-input-large">';
                $this->_helper->json(array('success' => true, 'html' => $html));
            } else {
                $html = "<select class=\"traffic-select-large\" disabled=\"disabled\" id=\"idCuenta\" name=\"idCuenta\">";
                $html .= "<option value=\"\">---</option>";
                $html .= "</select>";
                $this->_helper->json(array('success' => true, 'html' => $html));
            }
        }
    }

    public function obtainMyOpsAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            if (isset($post["aduana"]) && $post["aduana"] != '') {
                $rows = array(
                    "" => "---",
                    "TOCE.IMP" => "Importación",
                    "TOCE.EXP" => "Exportación",
                );
                $html = "<select tabindex=\"3\" class=\"traffic-select-small\" id=\"operacion\" name=\"operacion\">";
                foreach ($rows as $k => $item) {
                    if ($k != '') {
                        $html .= "<option value=\"{$k}\">{$item}</option>";
                    } else {
                        $html .= "<option value=\"\">---</option>";
                    }
                }
                $html .= "</select>";
                $this->_helper->json(array('success' => true, 'html' => $html));
            } else {
                $html = "<select disabled=\"disabled\" tabindex=\"3\" class=\"traffic-select-small\" id=\"operacion\" name=\"operacion\">";
                $html .= "<option value=\"\">---</option>";
                $html .= "</select>";
                $this->_helper->json(array('success' => true, 'html' => $html));
            }
        } else {
            throw new Exception("Invalid request type!");
        }
    }

    public function getCustomsAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $misc = new OAQ_Misc();
            $ophtml = $misc->mySelect(false, false, 3, "TipoOperacion", "TipoOperacion", "", "width: 150px;");
            $cvhtml = $misc->mySelect(false, false, 4, "CvePed", "", "CvePed", "width: 60px");
            $cuhtml = $misc->mySelect(false, false, 5, "Rfc", "Rfc", "", "width: 300px;");
            if (isset($data["patente"]) && $data["patente"] != '') {
                $model = new Trafico_Model_TraficoUsuAduanasMapper();
                $aduanas = $model->obtenerAduanas($data["patente"], $this->_session->id);
                if ($aduanas != false) {
                    $html = $misc->mySelect(true, $aduanas, 2, "Aduana", "Aduana", "", "width: 80px;");
                }
                $this->_helper->json(array('success' => true, 'html' => $html, 'cust' => $cuhtml, 'cveped' => $cvhtml, 'ops' => $ophtml));
            } else {
                $html = $misc->mySelect(false, false, 2, "Aduana", "Aduana", "", "width: 80px;");
                $this->_helper->json(array('success' => true, 'html' => $html, 'cust' => $cuhtml, 'cveped' => $cvhtml, 'ops' => $ophtml));
            }
        }
    }

    public function getOpsAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $misc = new OAQ_Misc();
            if (($db = $misc->connectSitawin($data["patente"], $data["aduana"]))) {
                $tipoCambio = (($tc = $db->tipoCambio(date('Y-m-d H:i:s'))) != null) ? $tc : '';
            } else {
                $tipoCambio = '';
            }
            $cvhtml = $misc->mySelect(false, false, 4, "CvePed", "", "CvePed", "width: 60px");
            $cuhtml = $misc->mySelect(false, false, 5, "Rfc", "Rfc", "", "width: 300px;");
            if (isset($data["aduana"]) && $data["aduana"] != '') {
                $model = new Trafico_Model_TraficoUsuAduanasMapper();
                $operaciones = $model->obtenerOperaciones($data["patente"], $data["aduana"], $this->_session->id);
                if ($operaciones != false) {
                    $html = $misc->mySelect(true, $operaciones, 3, "TipoOperacion", "TipoOperacion", "", "width: 150px;");
                }
                $this->_helper->json(array('success' => true, 'html' => $html, 'cust' => $cuhtml, 'cveped' => $cvhtml, 'tc' => $tipoCambio));
            } else {
                $html = $misc->mySelect(false, false, 3, "TipoOperacion", "TipoOperacion", "", "width: 150px;");
                $this->_helper->json(array('success' => true, 'html' => $html, 'cust' => $cuhtml, 'cveped' => $cvhtml, 'tc' => $tipoCambio));
            }
        }
    }

    public function getCustomersAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if (isset($data["aduana"]) && $data["aduana"] != '' && isset($data["tipo"])) {
                try {
                    $misc = new OAQ_Misc();
                    $table = new Trafico_Model_TraficoClavesMapper();
                    $model = new Trafico_Model_TraficoUsuClientesMapper();
                    $rows = $model->obtenerClientesPorAduana($this->_session->id, $data["patente"], $data["aduana"]);
                    if ($rows !== false) {
                        $html = $misc->mySelect(true, $rows, 5, "Rfc", "Rfc", "", "width: 300px;");
                    }
                    $cves = $table->obtenerClaves($data["patente"], $data["aduana"]);
                    if ($cves !== false) {
                        $cvhtml = $misc->mySelect(true, $cves, 4, "CvePed", "", "CvePed", "width: 60px");
                    }
                    $this->_helper->json(array('success' => true, 'cust' => $html, 'cveped' => $cvhtml));
                } catch (Exception $ex) {
                    $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
                }
            } else {
                $cvhtml = $misc->mySelect(false, false, 4, "CvePed", "", "CvePed", "width: 60px");
                $html = $misc->mySelect(false, false, 5, "Rfc", "Rfc", "", "width: 300px;");
                $this->_helper->json(array('success' => true, 'cust' => $html, 'cveped' => $cvhtml));
            }
        }
    }

    public function updateTrafficAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data["patente"] == 3589 && preg_match('/64/', $data["aduana"])) {
                $db = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITAW3010640", 1433, "Pdo_Mssql");
                $row = $db->infoPedimentoBasicaReferencia($data["referencia"]);
                if (!isset($row)) {
                    $db = new OAQ_Sitawin(true, '192.168.0.253', 'sa', 'sqlcointer', 'SITAW3589640', 1433, 'Pdo_Mssql');
                    $row = $db->infoPedimentoBasicaReferencia($data["referencia"]);
                }
                if (isset($row)) {
                    $facturas = $db->basicInvoices($data["referencia"]);
                    if (!empty($facturas)) {
                        $model = new Trafico_Model_TraficoFacturasMapper();
                        foreach ($facturas as $fact) {
                            if (!($model->verificar($data["id"], $fact["numFactura"]))) {
                                $model->agregarFactura($data["id"], $fact, $this->_session->id);
                            }
                        }
                    }
                    $paid = $db->paidInformation($data["referencia"]);
                    $dates = new Trafico_Model_TraficoFechasMapper();
                    /*
                     *  1 ENTRADA FECHA DE ENTRADA A TERRITORIO NACIONAL.
                      2 PAGO FECHA DE PAGO DE LAS CONTRIBUCIONES Y CUOTAS COMPENSATORIAS.
                      3 EXTRACCIÓN FECHA DE EXTRACCIÓN DE DEPÓSITO FISCAL.
                      5 PRESENTACIÓN FECHA DE PRESENTACIÓN EN PEDIMENTOS DE EXPORTACION.
                      6 IMP. EUA/CAN FECHA DE IMPORTACIÓN A ESTADOS UNIDOS DE AMÉRICA O CANADÁ.
                      (UNICAMENTE PARA PEDIMENTOS COMPLEMENTARIOS CON CLAVE CT CUANDO SE
                      CUENTE CON LA PRUEBA SUFICIENTE).
                      7 ORIGINAL FECHA DE PAGO DEL PEDIMENTO ORIGINAL (PARA LOS CASOS DE CAMBIO DE
                      RÉGIMEN DE INSUMOS, EXCEPTO DESPERDICIOS.
                     */
                    if (isset($paid["fechaEntrada"]) && $paid["fechaEntrada"] != '') {
                        if (!($fecent = $dates->verificarFecha($data["id"], 1))) {
                            $dates->agregarFecha($data["id"], $paid["fechaEntrada"], 1, $this->_session->id);
                        } else {
                            $dates->actualizarFecha($data["id"], $paid["fechaEntrada"], 1, $this->_session->id);
                        }
                    }
                    if (isset($paid["fechaPago"]) && $paid["fechaPago"] != '') {
                        if (!($fecent = $dates->verificarFecha($data["id"], 2))) {
                            $dates->agregarFecha($data["id"], $paid["fechaPago"], 2, $this->_session->id);
                        } else {
                            $dates->actualizarFecha($data["id"], $paid["fechaPago"], 2, $this->_session->id);
                        }
                    }
                    $tracking = $db->trackingNumbers($data["referencia"]);
                    $tn = new Trafico_Model_TraficoGuiasMapper();
                    if (isset($tracking) && !empty($tracking)) {
                        foreach ($tracking as $item) {
                            if (!($tn->verificarGuia($data["id"], $item["tipo"], $item["guia"]))) {
                                $tn->agregarGuia($data["id"], $item["tipo"], $item["guia"], $this->_session->id);
                            }
                        }
                    }
                }
            }
        }
    }

    public function viewInvoicesAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(false);
        $this->view->headLink()
            ->appendStylesheet('/less/traffic-module.css');
        $id = $this->_request->getParam("id", null);
        if (isset($id) && is_int((int) $id)) {
            $model = new Trafico_Model_TraficoFacturasMapper();
            $facturas = $model->obtenerFacturas($id, $this->_session->id);
            if (isset($facturas) && !empty($facturas)) {
                $this->view->data = $facturas;
            }
        }
    }

    public function viewTrackingNumbersAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(false);
        $this->view->headLink()
            ->appendStylesheet('/less/traffic-module.css');
        $id = $this->_request->getParam("id", null);
        if (isset($id) && is_int((int) $id)) {
            $tn = new Trafico_Model_TraficoGuiasMapper();
            $guias = $tn->obtenerGuias($id);
            if (isset($guias) && !empty($guias)) {
                $this->view->data = $guias;
            }
        }
    }

    public function trackingNumberInfoAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $tn = new Trafico_Model_TraficoGuiasMapper();
            $guia = $tn->obtenerGuia($data["id"]);
            if (isset($guia) && !empty($guia)) {
                $array = array(
                    'success' => true,
                    'guia' => $data["id"],
                    'transportista' => ($guia["idTransportista"] == null) ? 0 : $guia["idTransportista"],
                    'tipoguia' => $guia["tipo"],
                    'number' => $guia["guia"],
                );
                $this->_helper->json($array);
            }
        }
    }

    public function loadConceptsAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(false);
    }

    public function misSolicitudesAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(false);
        $this->view->headLink()
            ->appendStylesheet("/js/common/bootstrap/css/bootstrap.min.css")
            ->appendStylesheet("/css/fontawesome/css/fontawesome-all.min.css")
            ->appendStylesheet("/less/traffic-module.css?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "idAduana" => array("Digits"),
        );
        $v = array(
            "idAduana" => array("NotEmpty", new Zend_Validate_Int()),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());

        $s = new OAQ_SolicitudesAnticipo();
        $s->obtenerPermisos($this->_session->id, $this->_session->role);

        if ($input->isValid("idAduana")) {
            $ads = $s->get_idCustoms();
            if (in_array($input->idAduana, $ads)) {
                $model = new Trafico_Model_TraficoSolicitudesMapper();
                $arr = $model->obtenerMisSolicitudes($this->_session->id, $input->idAduana);
                if (count($arr)) {
                    $this->view->data = $arr;
                }
            }
        }
    }

    public function viewInformationAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(false);
        $this->view->headLink()
            ->appendStylesheet("/js/common/bootstrap/css/bootstrap.min.css")
            ->appendStylesheet("/less/traffic-module.css?" . time());
        $gets = $this->_request->getParams();
        $table = new Automatizacion_Model_WsAnexoPedimentosMapper();
        $anexo = $table->obtenerAnexo($gets["patente"], $gets["aduana"], $gets["pedimento"]);
        if ($anexo) {
            $this->view->data = $anexo;
        }
    }

    public function myConceptsAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(false);
        $this->view->headLink()
            ->appendStylesheet("/js/common/bootstrap/css/bootstrap.min.css")
            ->appendStylesheet("/less/traffic-module.css?" . time());
        $id = $this->_request->getParam("id", null);
        if (isset($id)) {
            $model = new Trafico_Model_TraficoSolConceptoMapper();
            $concepts = $model->obtener($id);
            if ($concepts !== false) {
                $this->view->data = $concepts;
            }
        }
    }

    public function addNewRequestAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Exception("Not an AJAX request detected");
        }
        $request = $this->getRequest();
        if ($request->isPost()) {
            $flt = array(
                "*" => array("StringTrim", "StripTags"),
                "cliente" => "Digits",
                "aduana" => "Digits",
                "pedimento" => "StringToUpper",
                "planta" => "Digits",
                "referencia" => "StringToUpper",
                "operacion" => "StringToUpper",
            );
            $vld = array(
                "aduana" => array("NotEmpty", new Zend_Validate_Int()),
                "cliente" => array("NotEmpty", new Zend_Validate_Int()),
                "pedimento" => array("NotEmpty"),
                "planta" => array("NotEmpty", new Zend_Validate_Int()),
                "operacion" => array("NotEmpty", new Zend_Validate_InArray(array("TOCE.EXP", "TOCE.IMP"))),
                "referencia" => array("Notempty", new Zend_Validate_Regex("/^[-_a-zA-Z0-9.]+$/")),
            );
            $input = new Zend_Filter_Input($flt, $vld, $request->getPost());
            if ($input->isValid("cliente") && $input->isValid("pedimento") && $input->isValid("operacion") && $input->isValid("referencia")) {

                $pedimento = str_pad($input->pedimento, 7, '0', STR_PAD_LEFT);

                $model = new Trafico_Model_TraficoSolicitudesMapper();
                if (!($found = $model->verificar($input->cliente, $input->aduana, $input->operacion, $pedimento, $input->referencia))) {

                    $added = $model->agregar($input->cliente, $input->aduana, $input->operacion, $pedimento, $input->referencia, $this->_session->id, $input->planta);

                    if ($added == true) {
                        $this->_helper->json(array("success" => true));
                    }
                } else {
                    $this->_helper->json(array("success" => false, "message" => "<p>La referencia ya existe en la base de datos: <br>Usuario que genero: " . strtoupper($found["usuario"]) . "<br>Fecha: {$found["creado"]}<br>Estatus: {$found["activa"]}<br>Generada: {$found["generada"]}<br>Enviada: {$found["enviada"]}<br>Borrada: {$found['borrada']}</p>"));
                }
            } else {
                $this->_helper->json(array("success" => false, "message" => "Invalid data input."));
            }
        } else {
            throw new Zend_Controller_Request_Exception("An error ocurrs!");
        }
    }

    public function addNewConceptAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim"),
                    "id" => "Digits",
                    "aduana" => "Digits",
                    "concepto" => "Digits",
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "aduana" => array("NotEmpty", new Zend_Validate_Int()),
                    "concepto" => array("NotEmpty"),
                    "importe" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("id") && $input->isValid("aduana") && $input->isValid("concepto")) {
                    $model = new Trafico_Model_TraficoSolConceptoMapper();
                    if (!($model->verificar($input->aduana, $input->id, $input->concepto))) {
                        $model->agregar($input->aduana, $input->id, $input->concepto, $input->importe);
                        $this->_helper->json(array("success" => true));
                    }
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function addNewAccountAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $filters = array(
                    '*' => array('StringTrim', 'StripTags'),
                    'idAduana' => 'Digits',
                    'idTipoConcepto' => 'Digits',
                    'idCuenta' => 'Digits',
                    'concepto' => 'StringToUpper',
                );
                $validators = array(
                    '*' => 'NotEmpty',
                    'idAduana' => new Zend_Validate_Int(),
                    'idTipoConcepto' => new Zend_Validate_Int(),
                    'idCuenta' => new Zend_Validate_Int(),
                    'concepto' => array(new Zend_Validate_Alnum(true)),
                );
                $input = new Zend_Filter_Input($filters, $validators, $request->getPost());
                if ($input->isValid()) {
                    $tbl = new Trafico_Model_TraficoConceptosMapper();
                    if (!($tbl->verificar($input->idAduana, $input->idCuenta))) {
                        $data = array(
                            'idAduana' => $input->idAduana,
                            'idCuenta' => $input->idCuenta,
                            'concepto' => trim($input->concepto),
                        );
                        $stmt = $tbl->agregar($data);
                        if ($stmt) {
                            $this->_helper->json(array('success' => true));
                        } else {
                            $this->_helper->json(array('success' => false, 'message' => 'Algo ocurrio.'));
                        }
                    } else {
                        $this->_helper->json(array('success' => false, 'message' => 'El concepto ya fue dado de alta.'));
                    }
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function addNewBankAccountAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost();
                $tbl = new Trafico_Model_TraficoBancosMapper();
                if (!($tbl->verificar($post["idAduana"], $post["nombreBanco"], $post["cuenta"]))) {
                    $data = array(
                        'idAduana' => $post["idAduana"],
                        'nombre' => $post["nombreBanco"],
                        'cuenta' => $post["cuenta"],
                        'sucursal' => $post["sucursal"],
                        'clabe' => $post["clabe"],
                        'razonSocial' => $post["razonSocial"],
                        'creado' => date('Y-m-d H:i:s'),
                        'creadoPor' => $this->_session->username,
                    );
                    $stmt = $tbl->agregar($data);
                    if ($stmt) {
                        $this->_helper->json(array('success' => true));
                    }
                } else {
                    $this->_helper->json(array('success' => false, 'message' => 'El banco ya fue dado de alta.'));
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    protected function _tipoArchivo($basename)
    {
        switch (true) {
            case preg_match('/^PED_/i', $basename):
                return 1;
            case preg_match('/^SOL_/i', $basename):
                return 31;
            case preg_match('/^PF_/i', $basename):
                return 1;
            case preg_match('/^PS_/i', $basename):
                return 33;
            case preg_match('/^FO_/i', $basename):
                return 34;
            case preg_match('/^CO_/i', $basename):
                return 35;
            case preg_match('/^MV_/i', $basename):
                return 10;
            case preg_match('/^HC_/i', $basename):
                return 11;
            case preg_match('/^RRNS_/i', $basename):
                return 36;
            case preg_match('/^OD_/i', $basename):
                return 37;
            case preg_match('/^CV_/i', $basename):
                return 22;
            case preg_match('/^ED_/i', $basename):
                return 27;
            case preg_match('/^CI_/i', $basename):
                return 4;
            case preg_match('/^EC_/i', $basename):
                return 17;
            case preg_match('/^PL_/i', $basename):
                return 38;
            case preg_match('/^BL_/i', $basename):
                return 12;
            case preg_match('/^FT_/i', $basename):
                return 40;
            case preg_match('/^FC_/i', $basename):
                return 29;
            case preg_match('/^NOM_/i', $basename):
                return 18;
            default:
                return 99;
        }
    }

    protected function _crearDirectorio($patente, $aduana, $referencia)
    {
        $folder = '/home/samba-share/expedientes' . DIRECTORY_SEPARATOR . $patente;
        if (!file_exists('/home/samba-share/expedientes' . DIRECTORY_SEPARATOR . $patente)) {
            mkdir('/home/samba-share/expedientes' . DIRECTORY_SEPARATOR . $patente);
        }
        if (!file_exists('/home/samba-share/expedientes' . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana)) {
            mkdir('/home/samba-share/expedientes' . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana);
        }
        if (!file_exists('/home/samba-share/expedientes' . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana . DIRECTORY_SEPARATOR . $referencia)) {
            mkdir('/home/samba-share/expedientes' . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana . DIRECTORY_SEPARATOR . $referencia);
        }
        $folder = '/home/samba-share/expedientes' . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana . DIRECTORY_SEPARATOR . $referencia;
        if (file_exists($folder)) {
            return $folder;
        } else {
            return false;
        }
    }

    protected function _renombrarArchivo($path, $sourceFile, $newFile)
    {
        if (!rename($path . DIRECTORY_SEPARATOR . $sourceFile, $path . DIRECTORY_SEPARATOR . $newFile)) {
            return false;
        }
        return true;
    }

    public function traficoTemporalAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(false);
        $this->view->headLink()
            ->appendStylesheet("/less/traffic-module.css?" . time());
        $this->view->headScript()
            ->appendFile("/js/common/jquery-1.9.1.min.js")
            ->appendFile("/js/common/jquery.form.js");
        try {
            $model = new Trafico_Model_TraficoTmpMapper();
            $data = $model->obtener($this->_session->username);
            if (isset($data)) {
                $this->view->data = $data;
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    protected function _arrayValue($value, $array)
    {
        if (isset($array[$value])) {
            return $array[$value];
        }
        return 0;
    }

    public function cargarConceptosAction()
    {
        try {
            $table = new Trafico_Model_TraficoSolConceptoMapper();
            $aduana = $this->_getParam('aduana', null);
            $solicitud = $this->_getParam('solicitud', null);
            $conceptos = $table->obtener($solicitud);
            $model = new Trafico_Model_TraficoConceptosMapper();
            $concepts = $model->obtenerGenerales();
            $chunk = array_chunk($concepts, 2);
            $rows = array();
            foreach ($chunk as $item) {
                $rows[] = array(
                    trim($item[0]),
                    ($conceptos !== false) ? $this->_arrayValue(trim($item[0]), $conceptos) : 0,
                    trim($item[1]),
                    ($conceptos !== false) ? $this->_arrayValue(trim($item[1]), $conceptos) : 0,
                    ''
                );
            }
            $rows[] = array('', '', 'TOTAL', '=SUM(B1:B12,D1:D12)', '');
            $rows[] = array('', '', 'ANTICIPO', ($conceptos !== false) ? $this->_arrayValue('ANTICIPO', $conceptos) : 0, '');
            $rows[] = array('', '', 'POR DEPOSITAR', "=D13-D14", '');
            echo Zend_Json::encode(array('data' => $rows));
            return false;
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function newRequestAction()
    {
        try {
            $table = new Trafico_Model_TraficoSolConceptoMapper();
            $body = $this->getRequest()->getRawBody();
            $data = Zend_Json::decode($body);
            $array = array();
            if (isset($data) && !empty($data)) {
                foreach ($data['input'] as $item) {
                    $array[$item[0]] = $item[1];
                    $array[$item[2]] = $item[3];
                }
            }
            if (isset($array) && !empty($array)) {
                foreach ($array as $key => $value) {
                    if ((!preg_match('/^TOTAL$/', $key) && !preg_match('/^POR DEPOSITAR$/', $key)) && $key !== '') {
                        $concepts[$key] = $value;
                    }
                }
                unset($item);
                $model = new Trafico_Model_TraficoSolDetalleMapper();
                $row = array(
                    'idSolicitud' => $data['data']["idSolicitud"],
                    'idAduana' => $data['data']["idAduana"],
                    'cvePed' => $data['data']["cvePed"],
                    'fechaArribo' => $data['data']["fechaArribo"],
                    'fechaAlmacenaje' => $data['data']["fechaAlmacenaje"],
                    'fechaEta' => $data['data']["fechaEta"],
                    'tipoCarga' => $data['data']["tipoCarga"],
                    'tipoFacturacion' => $data['data']["tipoFacturacion"],
                    'numFactura' => $data['data']["numFactura"],
                    'bl' => $data['data']["bl"],
                    'peso' => $data['data']["peso"],
                    'peca' => $data['data']["peca"],
                    'mercancia' => $data['data']["mercancia"],
                    'valorMercancia' => $data['data']["valorMercancia"],
                );
                if (!($id = $model->buscar($data['data']["idSolicitud"]))) {
                    $row["creado"] = date('Y-m-d H:i:s');
                    $model->agregar($row);
                } else {
                    $row["actualizado"] = date('Y-m-d H:i:s');
                    $model->actualizar($id, $row);
                }
                $table->borrarAnterior($data['data']["idSolicitud"]);
                foreach ($concepts as $key => $value) {
                    if ($value > 0) {
                        $table->agregar($data['data']["idAduana"], $data['data']["idSolicitud"], $key, $value);
                    }
                }
                $this->_helper->json(array('success' => true));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function guardarSolicitudAction()
    {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $post = $r->getPost();
                $model = new Trafico_Model_TraficoSolDetalleMapper();
                $row = array(
                    "idSolicitud" => $post["idSolicitud"],
                    "idAduana" => $post["idAduana"],
                    "idPlanta" => isset($post["idPlanta"]) ? $post["idPlanta"] : null,
                    "cvePed" => $post["cvePed"],
                    "fechaArribo" => isset($post["fechaArribo"]) ? $post["fechaArribo"] : null,
                    "fechaAlmacenaje" => isset($post["fechaAlmacenaje"]) ? $post["fechaAlmacenaje"] : null,
                    "fechaEta" => isset($post["fechaEta"]) ? $post["fechaEta"] : null,
                    "tipoCarga" => isset($post["tipoCarga"]) ? $post["tipoCarga"] : null,
                    "tipoFacturacion" => isset($post["tipoFacturacion"]) ? $post["tipoFacturacion"] : null,
                    "numFactura" => isset($post["numFactura"]) ? $post["numFactura"] : null,
                    "bl" => isset($post["bl"]) ? $post["bl"] : null,
                    "peso" => isset($post["peso"]) ? (float) $post["peso"] : null,
                    "peca" => isset($post["peca"]) ? $post["peca"] : null,
                    "almacen" => isset($post["almacen"]) ? $post["almacen"] : null,
                    "banco" => (isset($post["peca"]) && $post["peca"] == 1) ? null : isset($post["banco"]) ? $post["banco"] : null,
                    "mercancia" => isset($post["mercancia"]) ? $post["mercancia"] : null,
                    "valorMercancia" => isset($post["valorMercancia"]) ? (float) $post["valorMercancia"] : null,
                );
                if (!($id = $model->buscar($post["idSolicitud"]))) {
                    $row["creado"] = date("Y-m-d H:i:s");
                    $model->agregar($row);
                } else {
                    $row["actualizado"] = date("Y-m-d H:i:s");
                    $model->actualizar($id, $row);
                }
                if (isset($post["conceptos"])) {
                    $tbl = new Trafico_Model_TraficoConceptosMapper();
                    $concepts = array();
                    foreach ($post["conceptos"] as $k => $v) {
                        if ((float) $v != 0) {
                            $concepts[] = array(
                                "idAduana" => $post["idAduana"],
                                "idSolicitud" => $post["idSolicitud"],
                                "idConcepto" => $k,
                                "importe" => (float) str_replace(array("$", ",", "'"), "", $v),
                                "concepto" => $tbl->nombreConcepto($post["idAduana"], $k),
                            );
                        }
                    }
                    $mdl = new Trafico_Model_TraficoSolConceptoMapper();
                    if (isset($concepts) && !empty($concepts)) {
                        $mdl->borrarAnterior($post["idSolicitud"]);
                        foreach ($concepts as $concept) {
                            if ($concept["importe"] > 0) {
                                $mdl->agregar($post["idAduana"], $post["idSolicitud"], $concept["idConcepto"], $concept["concepto"], $concept["importe"]);
                            }
                        }
                    }
                    if (isset($post["anticipo"]) && (float) $post["anticipo"] > 0) {
                        $mdl->agregar($post["idAduana"], $post["idSolicitud"], null, "ANTICIPO", $post["anticipo"]);
                    }
                }
                $this->_helper->json(array("success" => true));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function imprimirSolicitudAction()
    {
        try {
            $id = $this->_getParam('id', null);
            $sto = new Trafico_Model_AlmacenMapper();
            $model = new Trafico_Model_TraficoSolicitudesMapper();
            if (isset($id)) {
                $request = new Trafico_Model_TraficoSolicitudesMapper();
                $header = $request->obtener($id);
                $table = new Trafico_Model_TraficoSolDetalleMapper();
                $detalle = $table->obtener($id);
                $model = new Trafico_Model_TraficoSolConceptoMapper();
                $conceptos = $model->obtenerImpresion($id);
                $dbtable = new Trafico_Model_TraficoConceptosMapper();
                $bank = new Trafico_Model_TraficoBancosMapper();
                $concepts = $dbtable->obtener($header["idAduana"]);
                $chunk = array_chunk($concepts, 2, true);
                $rows = array();
                $total = 0;
                foreach ($chunk as $row) {
                    $roww = array();
                    foreach ($row as $k => $v) {
                        if (!isset($roww[0])) {
                            $roww[0] = trim($v);
                            if (isset($conceptos[$k])) {
                                $roww[1] = $conceptos[$k];
                                $total += $conceptos[$k];
                            } else {
                                $roww[1] = '';
                            }
                        } else {
                            $roww[2] = trim($v);
                            if (isset($conceptos[$k])) {
                                $roww[3] = $conceptos[$k];
                                $total += $conceptos[$k];
                            } else {
                                $roww[3] = '';
                            }
                        }
                    }
                    $rows[] = $roww;
                }
                $pre["header"] = $header;
                $pre["detalle"] = $detalle;
                $pre["conceptos"] = $rows;
                $pre["detalle"]["almacen"] = (isset($pre["detalle"]["almacen"])) ? $sto->obtenerNombreAlmacen($pre["detalle"]["almacen"]) : null;
                $pre["anticipo"] = $model->obtenerAnticipo($id);
                $pre["total"] = $total;
                $tbl = new Trafico_Model_TraficoBancosMapper();
                $banco = $tbl->obtenerBancoDefault((int) $header["idAduana"]);
                if (isset($banco) && !empty($banco)) {
                    $pre["banco"] = $banco;
                } else {
                    $pre["banco"] = array(
                        'nombre' => 'N/D',
                        'razonSocial' => '',
                        'cuenta' => '',
                        'clabe' => '',
                        'sucursal' => '',
                    );
                }
                require 'tcpdf/solicitud.php';
                if (isset($pre)) {
                    $pre["colors"]["line"] = array(5, 5, 5);
                    $pdf = new Trafico($pre, 'P', 'pt', 'LETTER');
                    $pdf->SolicitudAnticipo();
                    $filename = "SOL_" . $header["aduana"] . '_' . $header["patente"] . '_' . $header["pedimento"] . '_' . $header["referencia"] . '_' . $id . '.pdf';
                    $pdf->Output($filename, 'I');
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function deleteRequestAction()
    {
        try {
            $model = new Trafico_Model_TraficoSolicitudesMapper();
            $logtbl = new Trafico_Model_BitacoraMapper;
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost();
                if (isset($post["id"])) {
                    $request = new Trafico_Model_TraficoSolicitudesMapper();
                    $head = $request->obtener($post["id"]);
                    $log = array(
                        'patente' => $head["patente"],
                        'aduana' => $head["aduana"],
                        'pedimento' => $head["pedimento"],
                        'referencia' => $head["referencia"],
                        'bitacora' => "SE BORRO SOLICITUD DE ANTICIPO",
                        'usuario' => $this->_session->username,
                        'creado' => date('Y-m-d H:i:s'),
                    );
                    $logtbl->agregar($log);
                    $model->borrarSolicitud($post["id"]);
                }
                $this->_helper->json(array('success' => true));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function deletePreRequestAction()
    {
        try {
            $model = new Trafico_Model_TraficoSolicitudesMapper();
            $logtbl = new Trafico_Model_BitacoraMapper;
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost();
                if (isset($post["id"])) {
                    $request = new Trafico_Model_TraficoSolicitudesMapper();
                    $detail = new Trafico_Model_TraficoSolDetalleMapper();
                    $concepts = new Trafico_Model_TraficoSolConceptoMapper();
                    $deleted = $request->delete($post["id"]);
                    if ($deleted == true) {
                        $details = $detail->delete($post["id"]);
                        if ($details == true) {
                            $concepts->delete($post["id"]);
                        }
                    }
                }
                $this->_helper->json(array('success' => true));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function transferredRequestAction()
    {
        try {
            $model = new Trafico_Model_TraficoSolicitudesMapper();
            $logtbl = new Trafico_Model_BitacoraMapper;
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => "Digits",
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("id")) {
                    $emails = new OAQ_EmailNotifications();
                    $sa = new Trafico_Model_TraficoSolicitudesMapper();
                    $s = $sa->obtener($i->id);
                    $log = new OAQ_Referencias(["patente" => $s["patente"], "aduana" => $s["aduana"], "pedimento" => $s["pedimento"], "referencia" => $s["referencia"], "usuario" => $this->_session->username]);
                    $log->agregarBitacora("SE DEPOSITO");
                    $stmt = $sa->depositarSolicitud($i->id);
                    if ($stmt === true) {
                        $p = $sa->propietario($i->id);
                        $mensaje = "Se ha realizado el depósito de la solicitud de anticipo número " . $i->id . " referencia " . $s["referencia"] . " pedimento " . $s["aduana"] . "-" . $s["patente"] . "-" . $s["pedimento"];
                        $emails->nuevaNotificacion($p["idAduana"], $s["pedimento"], $s["referencia"], $this->_session->id, $p["idUsuario"], $mensaje, "deposito-solicitud");
                    }
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function customerInformationAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(false);
        $this->view->headLink()
            ->appendStylesheet('/less/trafic-module.css');
        $this->view->headScript()
            ->appendFile("/js/common/jquery-1.9.1.min.js")
            ->appendFile("/js/common/jquery.form.min.js")
            ->appendFile("/js/common/jquery.validate.min.js");
        $gets = $this->_request->getParams();
        if (isset($gets["idCliente"]) && isset($gets["idAduana"])) {
            $this->view->idAduana = $gets["idAduana"];
            $this->view->idCliente = $gets["idCliente"];
            $adu = new Trafico_Model_TraficoAduanasMapper();
            $aduana = $adu->obtenerAduana($gets["idAduana"]);
            $this->view->aduana = $aduana;
            $fact = new Trafico_Model_TraficoTipoFacturacionMapper();
            $facturacion = $fact->obtenerTiposFacturacion($gets["idCliente"], $gets["idAduana"]);
            $this->view->facturacion = $facturacion;
            $factForm = new Trafico_Form_NuevaFacturacion();
            $this->view->factForm = $factForm;
        }
    }

    public function verFacturaAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(false);
        try {
            $this->view->headLink()
                ->appendStylesheet("/less/traffic-module.css?" . time());
            $this->view->headScript()
                ->appendFile("/js/common/jquery-1.9.1.min.js");
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $vucemFacturas = new OAQ_TraficoVucem(array("idFactura" => $input->id));
                $this->view->invoice = $vucemFacturas->verFactura();
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Zend_Exception($ex->getMessage());
        }
    }

    public function readThumbnailAction()
    {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => "Digits",
            );
            $v = array(
                "id" => array("Digits", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $mppr = new Trafico_Model_Imagenes();
                $miniatura = $mppr->obtenerMiniatura($input->id);
                header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
                header("Cache-Control: post-check=0, pre-check=0", false);
                header("Pragma: no-cache");
                header("Content-Type: image/jpeg");
                header("Content-Length: " . filesize($miniatura));
                echo file_get_contents($miniatura);
            } else {
                throw new Exception("Invalid input");
            }
        } catch (Exception $ex) {
            Zend_Debug::Dump($ex->getMessage());
        }
    }

    public function readImageAction()
    {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => "Digits",
            );
            $v = array(
                "id" => array("Digits", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $mppr = new Trafico_Model_Imagenes();
                $image = $mppr->obtenerImagen($input->id);
                header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
                header("Cache-Control: post-check=0, pre-check=0", false);
                header("Pragma: no-cache");
                header("Content-Type: image/jpeg");
                header("Content-Length: " . filesize($image));
                echo file_get_contents($image);
            } else {
                throw new Exception("Invalid input");
            }
        } catch (Exception $ex) {
            Zend_Debug::Dump($ex->getMessage());
        }
    }

    public function downloadFileAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $gets = $this->_request->getParams();
        if (isset($gets["id"]) && isset($gets["id"])) {
            $filters = array(
                '*' => array('StringTrim', 'StripTags'),
                'id' => array('Digits'),
            );
            $input = new Zend_Filter_Input($filters, null, $gets);
            if ($input->isValid()) :
                $data = $input->getEscaped();
                if (isset($data["id"]) && is_int((int) $data["id"])) {
                    $archive = new Archivo_Model_RepositorioMapper();
                    $fileinfo = $archive->getFileById((int) $data["id"]);
                    if ($fileinfo["tipo_archivo"] == 22 && preg_match('/.xml$/i', $fileinfo["nom_archivo"])) {
                        $misc = new OAQ_Misc();
                        if (is_readable($fileinfo["ubicacion"]) && file_exists($fileinfo["ubicacion"])) {
                            $sha = sha1_file($fileinfo["ubicacion"]);
                            $basename = basename($fileinfo["ubicacion"]);
                            if (copy($fileinfo["ubicacion"], '/tmp' . DIRECTORY_SEPARATOR . $sha)) {
                                $xml = file_get_contents($fileinfo["ubicacion"], '/tmp' . DIRECTORY_SEPARATOR . $sha);
                                $cleanXml = $misc->removeSecurityHeaders($xml);
                                header('Content-Type: application/octet-stream');
                                header("Content-Transfer-Encoding: Binary");
                                header("Content-Length: " . strlen($cleanXml));
                                header("Content-disposition: attachment; filename=\"" . $basename . "\"");
                                echo $cleanXml;
                            }
                        }
                    } else {
                        if (is_readable($fileinfo["ubicacion"]) && file_exists($fileinfo["ubicacion"])) {
                            $sha = sha1_file($fileinfo["ubicacion"]);
                            $basename = basename($fileinfo["ubicacion"]);
                            if (copy($fileinfo["ubicacion"], '/tmp' . DIRECTORY_SEPARATOR . $sha)) {
                                if (file_exists('/tmp' . DIRECTORY_SEPARATOR . $sha)) {
                                    header('Content-Type: application/octet-stream');
                                    header("Content-Transfer-Encoding: Binary");
                                    header("Content-Length: " . filesize('/tmp' . DIRECTORY_SEPARATOR . $sha));
                                    header("Content-disposition: attachment; filename=\"" . $basename . "\"");
                                    readfile('/tmp' . DIRECTORY_SEPARATOR . $sha);
                                    unlink('/tmp' . DIRECTORY_SEPARATOR . $sha);
                                }
                            }
                            unset($fileinfo);
                        }
                    }
                }
            endif;
        }
    }

    public function verSolicitudAction()
    {
        error_reporting(E_ALL & E_NOTICE);

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(false);
        $this->view->headLink()
            ->appendStylesheet('/css/nuevo-estilo-iframe.css')
            ->appendStylesheet("/css/fontawesome/css/fontawesome-all.min.css")
            ->appendStylesheet('/less/traffic-module.css');
        $this->view->headScript()->appendFile('/js/jquery.form.min.js')
            ->appendFile('/js/trafico/index/ver-solicitud.js?' . time());
        $id = $this->_getParam('id', null);
        $model = new Trafico_Model_TraficoSolicitudesMapper();
        if (in_array($this->_session->role, array('super', 'trafico_operaciones', 'trafico'))) {
            $this->view->edit = true;
        }
        $table = new Application_Model_UsuariosAduanasMapper();
        $aduanas = $table->aduanasUsuario($this->_session->id);
        if (isset($aduanas) && !empty($aduanas)) {
            if (isset($id)) {
                $tbl = new Trafico_Model_TraficoBancosMapper();
                $request = new Trafico_Model_TraficoSolicitudesMapper();
                if ($aduanas['patente'][0] != '0' && $aduanas['aduana'][0] != '0') {
                    $header = $request->obtener($id, $aduanas['patente'], $aduanas['aduana']);
                } else {
                    $header = $request->obtener($id);
                }
                $banco = $tbl->obtenerBancoDefault((int) $header["idAduana"]);
                if (isset($banco) && !empty($banco)) {
                    $this->view->banco = $banco;
                }
            }
        }
        if (isset($id) && isset($header) && $header !== false) {
            $sto = new Trafico_Model_AlmacenMapper();
            $comments = new Trafico_Model_TraficoSolComentarioMapper();
            $table = new Trafico_Model_TraficoSolDetalleMapper();
            $detalle = $table->obtener($id);
            $model = new Trafico_Model_TraficoSolConceptoMapper();
            $conceptos = $model->obtener($id);

            Zend_Debug::dump($conceptos);

            $dbtable = new Trafico_Model_TraficoConceptosMapper();
            $bank = new Trafico_Model_TraficoBancosMapper();
            $concepts = $dbtable->obtener($header["idAduana"]);

            $chunk = array_chunk($concepts, 2);

            $rows = array();
            $total = 0;
            foreach ($chunk as $item) {
                $rows[] = array(
                    trim($item[0]),
                    ($conceptos !== false) ? $this->_arrayValue(trim($item[0]), $conceptos) : 0,
                    isset($item[1]) ? trim($item[1]) : '',
                    ($conceptos !== false) ? isset($item[1]) ? $this->_arrayValue(trim($item[1]), $conceptos) : 0 : 0,
                    ''
                );
                $total += ($conceptos !== false) ? $this->_arrayValue(trim($item[0]), $conceptos) : 0;
                $total += ($conceptos !== false) ? isset($item[1]) ? $this->_arrayValue(trim($item[1]), $conceptos) : 0 : 0;
            }
            $detalle["almacen"] = (isset($detalle["almacen"])) ? $sto->obtenerNombreAlmacen($detalle["almacen"]) : null;
            $data["header"] = $header;
            $data["detalle"] = $detalle;
            $data["conceptos"] = $rows;
            if (isset($detalle["banco"])) {
                $data["banco"] = $bank->obtenerBanco($detalle["banco"]);
            } else {
                $data["banco"] = array(
                    'nombre' => '',
                    'razonSocial' => '',
                    'cuenta' => '',
                    'clabe' => '',
                    'sucursal' => '',
                );
            }
            $data["anticipo"] = ($conceptos !== false) ? $this->_arrayValue('ANTICIPO', $conceptos) : 0;
            $data["total"] = $total;
            $this->view->data = $data;
        }
    }

    public function verArchivoValidacionAction()
    {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
            }
            $request = $this->_request->getParams();
            $filters = array(
                '*' => array('StringTrim', 'StripTags'),
                'id' => array('Digits'),
            );
            $validators = array(
                'id' => array('NotEmpty', new Zend_Validate_Int())
            );
            $input = new Zend_Filter_Input($filters, $validators, $request);
            if ($input->isValid()) {
                $mapper = new Automatizacion_Model_ArchivosValidacionMapper();
                $file = $mapper->fileContent($input->id);
                $view = new Zend_View();
                $view->nomArchivo = $file["archivoNombre"];
                $view->contenido = base64_decode($file["contenido"]);
                $view->setScriptPath(realpath(dirname(__FILE__)) . '/../views/scripts/data/');
                echo $view->render('ver-archivo-validacion.phtml');
                return;
            } else {
                throw new Exception("Invalid Input!");
            }
        } catch (Exception $e) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function cancelacionSolicitudAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(false);
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int())
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
            } else {
                throw new Exception("Invalid Input!");
            }
        } catch (Exception $e) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function reporteClientesAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "reportFilter" => array("Digits"),
            );
            $v = array(
                "reportFilter" => array("NotEmpty", new Zend_Validate_Int())
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("reportFilter")) {
                $report = new OAQ_ExcelReportes();
                $report->setTitles(array(
                    "Id",
                    "Nombre",
                    "RFC",
                ));
                $model = new Trafico_Model_ClientesMapper();
                $data = $model->obtener(false, $input->reportFilter);
                $report->setData($data);
                $report->setFilename("CLIENTES_" . date("Ymd-His") . ".xlsx");
                $report->layoutClientes();
            } else {
                throw new Exception("Invalid Input!");
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function reporteOficinaClientesAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int())
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $mapper = new Trafico_Model_TraficoCliAduanasMapper();
                $arr = $mapper->reporteOficinaClientes($input->id);
                $report = new OAQ_ExcelReportes();
                $report->setTitles(array(
                    "Id",
                    "Nombre",
                    "RFC",
                ));
                $report->setData($arr);
                $report->setFilename("CLIENTES_" . date("Ymd-His") . ".xlsx");
                $report->layoutClientes();
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
