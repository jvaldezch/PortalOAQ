<?php

class Vucem_DataController extends Zend_Controller_Action {

    protected $_session;
    protected $_svucem;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;
    protected $_logger;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper('Redirector');
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
        $this->_logger = Zend_Registry::get("logDb");
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        $contextSwitch->addActionContext('recent-coves', 'json')
                ->addActionContext('borrar-solicitud-cove', 'json')
                ->initContext();
    }

    public function preDispatch() {
        $this->_session = NULL ? $this->_session = new Zend_Session_Namespace('') : $this->_session = new Zend_Session_Namespace($this->_config->app->namespace);
        if ($this->_session->authenticated == true) {
            $session = new OAQ_Session($this->_session, $this->_appconfig);
            $session->actualizar();
            $session->actualizarSesion();
        } else {
            $this->getResponse()->setRedirect($this->_appconfig->getParam('link-logout'));
        }
        $this->_svucem = NULL ? $this->_svucem = new Zend_Session_Namespace('') : $this->_svucem = new Zend_Session_Namespace("OAQVucem");
        $this->_svucem->setExpirationSeconds($this->_appconfig->getParam('session-exp'));
    }

    public function recentCovesAction() {
        error_reporting(E_ERROR | E_PARSE);
        $coves = new Vucem_Model_VucemSolicitudesMapper();
        $total_rows = $coves->contarSolicitudes($this->_svucem->username);
        $result = $coves->obtenerSolicitudes($this->_svucem->username);
        $per_page = $_POST["perPage"] ? $_POST["perPage"] : 10;
        $current_page = $_POST["currentPage"] ? $_POST["currentPage"] : 1;
        $sort = $_POST["sort"] ? array(array("column_0", "desc"), array("column_2", "asc")) : array(array("column_0", "desc"), array("column_2", "asc"));
        $filter = $_POST["filter"] ? array("column_0" => "foo") : array("column_0" => "foo");
        $example = array(
            "totalRows" => $total_rows,
            "perPage" => $per_page,
            "sort" => $sort,
            "filter" => $filter,
            "currentPage" => $current_page,
            "data" => array(),
            "posted" => $_POST
        );
        for ($i = 0; $i <= $per_page; $i++) {
            if ($result[$i]["solicitud"] == '') {
                continue;
            }
            $current_row = ($current_page * $per_page) - $per_page + $i;
            if ($current_row > $total_rows) {
                break;
            }
            $error = 'No';
            $semaforo = "<div class=\"statusCove cove\"></div>";
            if ($result[$i]["estatus"] == 0 && $result[$i]["respuesta_vu"] != null && $result[$i]["actualizado"] != null) {
                $error = 'Si';
                $semaforo = "<a href=\"/vucem/index/ver-error-cove?id={$result[$i]["id"]}\"><div class=\"statusCove error\"></div></a>";
            } else if ($result[$i]["estatus"] == 1) {
                $error = 'No';
                $semaforo = "<div class=\"statusCove sent\"></div>";
            }
            $example["data"][] = array(
                "semaforo" => $semaforo,
                "link" => "<a title=\"Consultar el COVE enviado.\" href=\"/vucem/index/consultar-cove-enviado?id={$result[$i]["id"]}\"><i class=\"icon icon-file\"></i></a>",
                "relfact" => ($result[$i]["relfact"] == '1') ? 'Si' : 'No',
                "factura" => ($result[$i]["relfact"] == '0') ? $result[$i]["factura"] : '',
                "solicitud" => $result[$i]["solicitud"],
                "patente" => $result[$i]["patente"],
                "aduana" => $result[$i]["aduana"],
                "pedimento" => $result[$i]["pedimento"],
                "error" => $error,
                "cove" => $result[$i]["cove"],
                "enviado" => $result[$i]["enviado"],
                "actualizado" => $result[$i]["actualizado"],
                "usuario" => $result[$i]["usuario"],
            );
        }
        echo json_encode($example);
    }

    protected function currency($value) {
        return '$ ' . number_format($value, 3, '.', ',');
    }

    protected function number($value) {
        return number_format($value, 3, '.', '');
    }

    /*public function obtenerFacturasAction() {
        try {
            $this->_helper->viewRenderer->setNoRender(false);
            $ped = $this->_request->getParam("ped");
            $model = new Vucem_Model_VucemClientesMapper();
            if (!isset($this->_svucem->solicitante) || !isset($this->_svucem->patente) || !isset($this->_svucem->aduana)) {
                $this->view->warning = "No ha seleccionado una firma.";
                $error = true;
            }
            $misc = new OAQ_Misc();
            $sita = $misc->sitawin($this->_svucem->patente, $this->_svucem->aduana);
            if (isset($sita) && !isset($error)) {
                $verificar = $sita->verificarPedimento($ped);
                if (!$verificar) {
                    $this->view->warning = "El número de pedimento no existe.";
                    $error = true;
                } else {
                    if ((strtoupper($this->_svucem->username) == strtoupper($verificar["usuario"])) && ($this->_session->role != 'super' && $this->_session->role != 'trafico_operaciones')) {
                        $invoices = $sita->obtenerFacturasPedimento($ped, $verificar["consolidado"]);
                        $facturas = array();
                        if (isset($invoices) && !empty($invoices)) {
                            foreach ($invoices as $invoice) {
                                if (isset($invoice["CteRfc"])) {
                                    $data = $model->datosCliente($invoice["CteRfc"]);
                                    if (isset($data) && !empty($data)) {
                                        $invoice["CteNombre"] = $data["razon_soc"];
                                        $invoice["CteCalle"] = $data["calle"];
                                        $invoice["CteNumExt"] = $data["numext"];
                                        $invoice["CteNumInt"] = $data["numint"];
                                        $invoice["CteColonia"] = $data["colonia"];
                                        $invoice["CteLocalidad"] = $data["localidad"];
                                        $invoice["CteCP"] = $data["cp"];
                                        $invoice["CteMun"] = $data["municipio"];
                                        $invoice["CteEdo"] = $data["estado"];
                                        $invoice["CtePais"] = $data["pais"];
                                    }
                                    $facturas[] = $invoice;
                                } else {
                                    $facturas[] = $invoice;
                                }
                            }
                        }
                        if (!$facturas) {
                            $this->view->warning = "El pedimento no contiene facturas.";
                        }
                    } elseif ($this->_session->role == 'super' || $this->_session->role == 'trafico_operaciones' || $this->_session->role == 'gerente') {
                        $invoices = $sita->obtenerFacturasPedimento($ped, $verificar["consolidado"]);
                        $facturas = array();
                        if (isset($invoices) && !empty($invoices)) {
                            foreach ($invoices as $invoice) {
                                if (isset($invoice["CteRfc"])) {
                                    $data = $model->datosCliente($invoice["CteRfc"]);
                                    if (isset($data) && !empty($data)) {
                                        $invoice["CteNombre"] = $data["razon_soc"];
                                        $invoice["CteCalle"] = $data["calle"];
                                        $invoice["CteNumExt"] = $data["numext"];
                                        $invoice["CteNumInt"] = $data["numint"];
                                        $invoice["CteColonia"] = $data["colonia"];
                                        $invoice["CteLocalidad"] = $data["localidad"];
                                        $invoice["CteCP"] = $data["cp"];
                                        $invoice["CteMun"] = $data["municipio"];
                                        $invoice["CteEdo"] = $data["estado"];
                                        $invoice["CtePais"] = $data["pais"];
                                    }
                                    $facturas[] = $invoice;
                                } else {
                                    $facturas[] = $invoice;
                                }
                            }
                        }
                        if (!$facturas) {
                            $this->view->warning = "El pedimento no contiene facturas.";
                        }
                    } else {
                        $this->view->warning = "El pedimento pertenece a otro usuario: {$verificar["usuario"]}.";
                    }
                }
                if (!isset($error) && isset($facturas)) {
                    $pedimento = $sita->infoPedimentoBasica($ped);
                    $this->view->pedimento = $pedimento;
                    $this->view->facturas = $facturas;
                    $this->view->ped = $ped;
                    $this->view->tipoPed = ($pedimento["IMP_EXP"] == '1') ? 'TOCE.IMP' : 'TOCE.EXP';
                }
            }
        } catch (Exception $ex) {
        }
    }*/

    public function obtenerFacturasSlamAction() {
        $this->_helper->viewRenderer->setNoRender(false);
        $referencia = $this->_request->getParam('referencia');
        $model = new Vucem_Model_VucemClientesMapper();
        if (!isset($this->_svucem->solicitante) || !isset($this->_svucem->patente) || !isset($this->_svucem->aduana)) {
            $this->view->warning = "No ha seleccionado una firma.";
            $error = true;
        }
        try {
            if ($this->_svucem->patente == 3589 && $this->_svucem->aduana == 240) {
                $slam = new OAQ_Slam('162.253.186.242', 'master', 'master', 'Aduana', 'Pdo_Mssql', 1433);
            }
            if (isset($slam)) {
                $facturas = $slam->slamConsultarFacturasImpo($this->_svucem->patente, $this->_svucem->aduana, $referencia);
                if ($facturas == false) {
                    $this->view->warning = "El pedimento no contiene facturas.";
                }
                if (isset($facturas) && !empty($facturas)) {
                    $this->view->pedimento = $facturas[0];
                    $this->view->facturas = $facturas;
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function seleccionarFacturasSlamAction() {
        $vucem = new OAQ_VucemEnh();
        $model = new Vucem_Model_VucemClientesMapper();
        $tmpFact = new Vucem_Model_VucemTmpFacturasMapper();
        $tmpProd = new Vucem_Model_VucemTmpProductosMapper();
        $referencia = $this->_request->getParam('referencia');
        $facts = $this->_request->getParam('facts');
        try {
            if ($this->_svucem->patente == 3589 && $this->_svucem->aduana == 240) {
                $slam = new OAQ_Slam('162.253.186.242', 'master', 'master', 'Aduana', 'Pdo_Mssql', 1433);
            }
            if (isset($slam)) {
                $invoices = $slam->slamObtenerFacturasImpo($this->_svucem->patente, $this->_svucem->aduana, $referencia, $facts);
                if (isset($invoices) && !empty($invoices)) {
                    foreach ($invoices as $f) {
                        if (isset($f["CteRfc"])) {
                            $data = $model->datosCliente($f["CteRfc"]);
                            if (isset($data) && !empty($data)) {
                                $f["CteNombre"] = $data["razon_soc"];
                                $f["CteCalle"] = $data["calle"];
                                $f["CteNumExt"] = $data["numext"];
                                $f["CteNumInt"] = $data["numint"];
                                $f["CteColonia"] = $data["colonia"];
                                $f["CteLocalidad"] = $data["localidad"];
                                $f["CteCP"] = $data["cp"];
                                $f["CteMun"] = $data["municipio"];
                                $f["CteEdo"] = $data["estado"];
                                $f["CtePais"] = $data["pais"];
                            }
                        }
                        if (!isset($f["ProIden"])) {
                            $f["ProIden"] = $vucem->tipoIdentificador($f["ProTaxID"], $f["ProPais"]);
                        }
                        if (!isset($f["CteIden"])) {
                            $f["CteIden"] = $vucem->tipoIdentificador($f["CteRfc"], $f["CtePais"]);
                        }
                        $idTmpFact = $tmpFact->nuevaFactura($this->_svucem->solicitante, $this->_svucem->tipoFigura, $this->_svucem->patente, $this->_svucem->aduana, $f, $this->_session->username, 0);
                        foreach ($f["Productos"] as $p) {
                            $tmpProd->nuevoProducto($idTmpFact, $f["IdFact"], $this->_svucem->patente, $this->_svucem->aduana, $f["Pedimento"], $f["Referencia"], $p, $this->_session->username);
                        }
                    }
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function loadProductsAction() {
        error_reporting(E_ERROR | E_PARSE);
        $get = $this->_request->getParams();

        $tmpProd = new Vucem_Model_VucemTmpProductosMapper();
        $productos = $tmpProd->obtenerProductos($get["factura"], $this->_session->username);

        $total_rows = count($productos);
        $per_page = $_POST["perPage"] ? $_POST["perPage"] : 10;
        $current_page = $_POST["currentPage"] ? $_POST["currentPage"] : 1;
        $sort = $_POST["sort"] ? array(array("column_0", "desc"), array("column_2", "asc")) : array(array("column_0", "desc"), array("column_2", "asc"));
        $filter = $_POST["filter"] ? array("column_0" => "foo") : array("column_0" => "foo");

        $example = array(
            "totalRows" => $total_rows,
            "perPage" => $per_page,
            "sort" => $sort,
            "filter" => $filter,
            "currentPage" => $current_page,
            "data" => array(),
            "posted" => $_POST
        );

        $misc = new OAQ_Misc();
        $umc = new Vucem_Model_VucemUmcMapper();
        for ($i = 0; $i < $per_page; $i++) {
            $current_row = ($current_page * $per_page) - $per_page + $i;
            if ($current_row > $total_rows - 1)
                break;
            if (count($productos) > 0) {
                if ($productos[$current_row]["ID_PROD"] != '') {
                    $link = '';
                    if ($productos[$current_row]["ID_PROD"] != '') {
                        if (!isset($get["idfact"])) {
                            $link = '<a href="/vucem/index/cambiar-producto?factura=' . $get["factura"] . '&idprod=' . $productos[$current_row]["ID_PROD"] . '"><i class="icon-pencil" rel="tooltip" title="Consultar producto"></i></a>';
                            $link .= '<a onclick="deleteProduct(' . "'" . $get["factura"] . "','" . $productos[$current_row]["ID_PROD"] . "'" . ');" style="margin-left:5px; cursor: pointer"><i class="icon-trash" rel="tooltip" title="Borrar producto"></i></a>';
                        } else {
                            $link = "&nbsp;";
                        }
                    }
                    if (!isset($productos[$current_row]["PREUNI"]) || $productos[$current_row]["PREUNI"] == '') {
                        if ($productos[$current_row]["VALDLS"] != 0) {
                            $productos[$current_row]["PREUNI"] = $this->number($productos[$current_row]["VALDLS"] / $productos[$current_row]["CAN_OMA"]);
                        } else {
                            $productos[$current_row]["PREUNI"] = $this->number($productos[$current_row]["VALCOM"] / $productos[$current_row]["CAN_OMA"]);
                        }
                    }
                    if (preg_match('/USD/i', $productos[$current_row]["MONVAL"])) {
                        if ((int) $productos[$current_row]["VALDLS"] != 0) {
                            $valUsd = $productos[$current_row]["VALDLS"];
                        } else {
                            $valUsd = $productos[$current_row]["VALCOM"];
                        }
                    } else {
                        $valUsd = $this->currency($productos[$current_row]["VALCOM"] * $productos[$current_row]["VALCEQ"]);
                    }
                    $example["data"][] = array(
                        "Link" => $link,
                        "ORDEN" => $productos[$current_row]["ORDEN"],
                        "CODIGO" => $productos[$current_row]["CODIGO"],
                        "DESC_COVE" => ($productos[$current_row]["DESC_COVE"] != '') ? $productos[$current_row]["DESC_COVE"] : $productos[$current_row]["DESC1"],
                        "PREUNI" => $productos[$current_row]["PREUNI"],
                        "VALCOM" => $this->currency($productos[$current_row]["VALCOM"]),
                        "MONVAL" => $misc->tipoMoneda($productos[$current_row]["MONVAL"]),
                        "VALCEQ" => $productos[$current_row]["VALCEQ"],
                        "VALDLS" => $valUsd,
                        "CANTFAC" => $this->number($productos[$current_row]["CANTFAC"]),
                        "UMC" => $productos[$current_row]["UMC"],
                        "UMT" => $productos[$current_row]["UMT"],
                        "PAIORI" => $productos[$current_row]["PAIORI"],
                        "PAICOM" => $productos[$current_row]["PAICOM"],
                        "FACTAJU" => ($productos[$current_row]["FACTAJU"] == '' || !isset($productos[$current_row]["FACTAJU"])) ? 0 : $productos[$current_row]["FACTAJU"],
                        "CERTLC" => $productos[$current_row]["CERTLC"],
                        "PARTE" => $productos[$current_row]["PARTE"],
                        "UMC" => (isset($productos[$current_row]["UMC"])) ? $umc->getUmcDesc($productos[$current_row]["UMC"]) : null,
                        "CAN_OMA" => $productos[$current_row]["CAN_OMA"],
                        "UMC_OMA" => strtoupper($productos[$current_row]["UMC_OMA"]),
                    );
                }
            }
        }
        echo json_encode($example);
    }

    public function borrarSolicitudCoveAction() {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $id = $this->getRequest()->getPost('id', null);
            $vucemSol = new Vucem_Model_VucemSolicitudesMapper();
            $deleted = $vucemSol->borrarSolicitud($id, ($this->_session->role != 'super' && $this->_session->role != 'trafico_operaciones') ? $this->_session->username : null);
            if ($deleted) {
                echo Zend_Json_Encoder::encode(array('success' => true));
                return true;
            }
            echo Zend_Json_Encoder::encode(array('success' => false));
            return true;
        } else {
            echo 'Action cannot be accessed directly.';
            return false;
        }
    }

    public function borrarSolicitudEdocAction() {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $uuid = $this->getRequest()->getPost('uuid', null);
            $edocs = new Vucem_Model_VucemEdocMapper();
            $deleted = $edocs->borrarEdoc($uuid, ($this->_session->role != 'super' && $this->_session->role != 'trafico_operaciones') ? $this->_session->username : null);
            if ($deleted) {
                echo Zend_Json_Encoder::encode(array('success' => true));
                return true;
            }
            echo Zend_Json_Encoder::encode(array('success' => false));
            return false;
        } else {
            echo 'Action cannot be accessed directly.';
            return false;
        }
    }

    public function obtenerAduanasAction() {
        $this->_helper->viewRenderer->setNoRender(false);
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
            );
            $v = array(
                "rfc" => "NotEmpty",
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("rfc")) {
                $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
                $firmante = new Vucem_Model_VucemFirmanteMapper();
                $vucemP = new Vucem_Model_VucemPermisosMapper();
                $aduanas = $vucemP->obtenerAduanas($this->_session->id, $input->rfc);
                $tipoFigura = $firmante->tipoFigura($input->rfc);
                $this->view->tipoFigura = $tipoFigura;
                $arr = array();
                if (!empty($aduanas)) {
                    foreach ($aduanas as $item) {
                        if ($item["aduana"] != '646') {
                            $arr[] = $item;
                        }
                    }
                }
                $this->view->aduanas = $arr;
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerAduanasEdocsAction() {
        $this->_helper->viewRenderer->setNoRender(false);
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "rfc" => array("StringToUpper"),
            );
            $v = array(
                "rfc" => array("NotEmpty", new Zend_Validate_Regex("/^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/")),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("rfc")) {
                $mppr = new Vucem_Model_VucemFirmanteMapper();
                $mpprp = new Vucem_Model_VucemPermisosMapper();
                $arr = $mpprp->obtenerPermisosAduanas($this->_session->id, $input->rfc);
                $tipoFigura = $mppr->tipoFigura($input->rfc);
                $this->view->tipoFigura = $tipoFigura;
                $this->view->aduanas = $arr;
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerEmisorExpAction() {
        $this->_helper->viewRenderer->setNoRender(false);
        $tipo = $this->_request->getParam('tipo', null);
        if ($tipo == 'TOCE.EXP') {
            $clients = new Vucem_Model_VucemClientesMapper();
            $clientes = $clients->getCustomers($this->_svucem->patente, $this->_svucem->aduana);
            $this->view->clientes = $clientes;
            $this->view->tipo = $tipo;
        }
    }

    public function detalleEmisorExpAction() {
        $this->_helper->viewRenderer->setNoRender(false);
        $tipo = $this->_request->getParam('tipo', null);
        $cvecli = $this->_request->getParam('cvecli', null);
        if ($tipo == 'TOCE.EXP') {
            $clients = new Vucem_Model_VucemClientesMapper();
            $detail = $clients->detailCustomer($this->_svucem->patente, $this->_svucem->aduana, $cvecli);
            echo Zend_Json_Encoder::encode($detail);
            return true;
        }
    }

    public function detalleDestinatarioExpAction() {
        $this->_helper->viewRenderer->setNoRender(false);
        $tipo = $this->_request->getParam('tipo', null);
        $cvedest = $this->_request->getParam('cvedest', null);
        if ($tipo == 'TOCE.EXP') {
            $dest = new Vucem_Model_VucemDestinatarioMapper();
            $destinatario = $dest->getProviderDetail($this->_svucem->patente, $this->_svucem->aduana, $cvedest);
            echo Zend_Json_Encoder::encode($destinatario);
            return true;
        }
    }

    public function obtenerDestinatarioExpAction() {
        $this->_helper->viewRenderer->setNoRender(false);
        $tipo = $this->_request->getParam('tipo', null);
        $cvecli = $this->_request->getParam('cvecli', null);
        if ($tipo == 'TOCE.EXP') {
            $dest = new Vucem_Model_VucemDestinatarioMapper();
            $destinatarios = $dest->getProviders($this->_svucem->patente, $this->_svucem->aduana, $cvecli);
            $this->view->destinatarios = $destinatarios;
            $this->view->tipo = $tipo;
        }
    }

    public function obtenerDestinatarioImpAction() {
        $this->_helper->viewRenderer->setNoRender(false);
        $tipo = $this->_request->getParam('tipo', null);
        if ($tipo == 'TOCE.IMP') {
            $clients = new Vucem_Model_VucemClientesMapper();
            $clientes = $clients->getCustomers($this->_svucem->patente, $this->_svucem->aduana);
            $this->view->clientes = $clientes;
            $this->view->tipo = $tipo;
        }
    }

    public function obtenerEmisorImpAction() {
        $this->_helper->viewRenderer->setNoRender(false);
        $tipo = $this->_request->getParam('tipo', null);
        $cvecli = $this->_request->getParam('cvecli', null);
        if ($tipo == 'TOCE.IMP') {
            $client = new Vucem_Model_VucemProveedoresMapper();
            $clientes = $client->getProviders($this->_svucem->patente, $this->_svucem->aduana, $cvecli);
            $this->view->clientes = $clientes;
            $this->view->tipo = $tipo;
        }
    }

    public function detalleDestinatarioImpAction() {
        $this->_helper->viewRenderer->setNoRender(false);
        $tipo = $this->_request->getParam('tipo', null);
        $cvecli = $this->_request->getParam('cvecli', null);

        if ($tipo == 'TOCE.IMP') {
            $clients = new Vucem_Model_VucemClientesMapper();
            $detail = $clients->detailCustomer($this->_svucem->patente, $this->_svucem->aduana, $cvecli);
            echo Zend_Json_Encoder::encode($detail);
            return true;
        }
    }

    public function detalleEmisorImpAction() {
        $this->_helper->viewRenderer->setNoRender(false);
        $tipo = $this->_request->getParam('tipo', null);
        $cvepro = $this->_request->getParam('cvepro', null);

        if ($tipo == 'TOCE.IMP') {
            $client = new Vucem_Model_VucemProveedoresMapper();
            $detail = $client->getProviderDetail($this->_svucem->patente, $this->_svucem->aduana, $cvepro);
            echo Zend_Json_Encoder::encode($detail);
            return true;
        }
    }

    public function verificarNuevaFacturaAction() {
        $this->_helper->viewRenderer->setNoRender(false);
        $tmpProd = new Vucem_Model_VucemTmpProductosMapper();
        $request = $this->getRequest();
        $data = $request->getPost();
        if ($data["factura"] != null) {
            $prod = $tmpProd->obtenerProductos($data["factura"], $this->_session->username);
            if ($prod) {
                echo Zend_Json_Encoder::encode(array('success' => true));
                return true;
            } else {
                echo Zend_Json_Encoder::encode(array('success' => false));
                return true;
            }
        } else {
            echo Zend_Json_Encoder::encode(array('success' => false));
            return true;
        }
    }

    public function borrarFacturaAction() {
        $this->_helper->viewRenderer->setNoRender(false);
        if (isset($this->_svucem->newInvoice)) {
            unset($this->_svucem->newInvoice);
            unset($this->_svucem->productList);
            echo Zend_Json_Encoder::encode(array('success' => true));
            return true;
        }
    }

    public function agregarProductoAction() {
        $this->_helper->viewRenderer->setNoRender(false);
        if (isset($this->_svucem->newProduct)) {
            $this->_svucem->productList[] = $this->_svucem->newProduct;
            echo Zend_Json_Encoder::encode(array('success' => true));
            return true;
        }
    }

    public function removerFacturaAction() {
        $tmpFact = new Vucem_Model_VucemTmpFacturasMapper();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $deleted = $tmpFact->borrarFactura($data["id"], $data["factura"], $this->_session->username);
            if ($deleted) {
                echo Zend_Json_Encoder::encode(array('success' => true));
                return true;
            }
        }
    }

    public function removerFacturaIdAction() {
        $tmpFact = new Vucem_Model_VucemTmpFacturasMapper();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            $deleted = $tmpFact->borrarFacturaId($post["id"], $this->_session->username);
            if ($deleted) {
                echo Zend_Json_Encoder::encode(array('success' => true));
                return true;
            }
        }
    }

    public function excelReporteCovesAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $headers = array(
            'RFC' => 'rfc',
            'Patente' => 'patente',
            'Aduana' => 'aduana',
            'Pedimento' => 'pedimento',
            'Referencia' => 'referencia',
            'Factura' => 'factura',
            'COVE' => 'cove',
        );
        $misc = new OAQ_Misc();
        $sol = new Vucem_Model_VucemSolicitudesMapper();
        if (!($result = $misc->checkCache('rptcoves' . $this->_svucem->username))) {
            $result = $sol->reporteCoves($this->_svucem->rptCoveFechaIni, $this->_svucem->rptCoveFechaFin, $this->_svucem->rptCoveReferencia, $this->_svucem->rptCovePedimento, ($this->_session->role != 'super' && $this->_session->role != 'trafico_operaciones') ? $this->_svucem->username : null);
            $misc->saveCache('rptcoves' . $this->_svucem->username, $result);
        }
        $reports = new OAQ_ExcelExport();
        $reports->createSimpleReport($headers, $result, 'rptcoves', 'Reporte de COVES', $this->_svucem->rptCoveFechaIni, $this->_svucem->rptCoveFechaFin, 'Reporte de COVES', null, null);
    }

    public function obtenerPedimentoAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $vucem = new OAQ_Vucem();
        $firmantes = new Vucem_Model_VucemFirmanteMapper();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $this->_svucem->edoPedPat = $data["patente"];
            $this->_svucem->edoPedPed = $data["pedimento"];
            $this->_svucem->edoPedAdu = $data["aduana"];
            $this->_svucem->edoPedRfc = $data["rfc"];
            $rfc = $firmantes->obtenerDetalleFirmante($this->_svucem->edoPedRfc, 'prod', $this->_svucem->edoPedPat, $this->_svucem->edoPedAdu);
            $xml = $vucem->solicitudPedimentoCompleto($this->_svucem->edoPedRfc, $rfc["ws_pswd"], $this->_svucem->edoPedPat, $this->_svucem->edoPedAdu, $this->_svucem->edoPedPed); /* XML SOLICITUD PEDIMENTO */
            $solicitud = $vucem->vucemPedimento('ConsultarPedimentoCompletoService', $xml); /* XML RESPUESTA PEDIMENTO */
            $array = $vucem->vucemXmlToArray($solicitud);
            unset($array["Header"]);
            if ($array["Body"]["consultarPedimentoCompletoRespuesta"]["tieneError"] == 'true') {
                echo Zend_Json_Encoder::encode(array('error' => true, 'message' => $array["Body"]["consultarPedimentoCompletoRespuesta"]["error"]["mensaje"]));
            } elseif ($array["Body"]["consultarPedimentoCompletoRespuesta"]["tieneError"] == 'false') {
                $this->_svucem->edoPedCompleto = $array;
                $edoXml = $vucem->solicitudEstadoPedimento($this->_svucem->edoPedRfc, $rfc["ws_pswd"], $this->_svucem->edoPedPat, $this->_svucem->edoPedAdu, $this->_svucem->edoPedPed, $array["Body"]["consultarPedimentoCompletoRespuesta"]["numeroOperacion"]); /* XML SOLICITUD ESTADO PEDIMENTO */
                $edoArray = $vucem->vucemPedimento('ConsultarEstadoPedimentosService', $edoXml); /* XML RESPUESTA ESTADO PEDIMENTO */
                $this->_svucem->edoPedEdo = $vucem->vucemXmlToArray($edoArray);
                echo Zend_Json_Encoder::encode(array('error' => false, 'message' => false));
            }
            if (isset($array["Body"]["consultarPedimentoCompletoRespuesta"]["error"]["mensaje"])) {
                echo Zend_Json_Encoder::encode(array('error' => true, 'message' => $array["Body"]["consultarPedimentoCompletoRespuesta"]["error"]["mensaje"]));
            }
            return true;
        }
        return false;
    }

    public function cargarClientesAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $misc = new OAQ_Misc();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $info = explode(',', $data["value"]);
            $cust = new Vucem_Model_VucemClientesMapper();
            $customers = $cust->getCustomers($info[0], $info[1]);
            if ($customers) {
                $html = '<table class="table table-striped table-bordered small">
                    <tr>
                        <th>&nbsp;</th>
                        <th>Cve.</th>
                        <th>Patente</th>
                        <th>Aduana</th>
                        <th>RFC</th>
                        <th>Nombre</th>
                        <th>&nbsp;</th>
                    </tr>';
                foreach ($customers as $c) {
                    $href = $info[0] . ',' . $info[1] . ',' . $c['rfc'];
                    $prov = $info[0] . ',' . $info[1] . ',' . $c['cvecte'] . ',' . $c['rfc'];
                    $html .= '<tr>
                            <td><a class="view-customer-data" data="' . $href . '" style="cursor:pointer"><i class="icon-info-sign"></i></a>&nbsp;<a href="/vucem/catalogo/editar-cliente?edit=' . $href . '"><i class="icon-pencil"></i></a></td>
                            <td>' . $c["cvecte"] . '</td>
                            <td>' . $c["patente"] . '</td>
                            <td>' . $c["aduana"] . '</td>
                            <td>' . $c["rfc"] . '</td>
                            <td>' . $c["razon_soc"] . '</td>
                            <td style="width: 330px"><div class="btn-group">
                                <button class="btn btn-success view-providers" data="' . $prov . '" style="font-family: Arial,sans-serif; font-size:11px">Proveedores (IMPO)</button>
                                <button class="btn btn-info view-addressees" data="' . $prov . '" style="font-family: Arial,sans-serif; font-size:11px">Destinatarios (EXPO)</button>
                                <a href="/vucem/catalogo/ver-catalogo?patente=' . $info[0] . '&aduana=' . $info[1] . '&cvecte=' . $c['cvecte'] . '&rfc=' . $c['rfc'] . '" class="btn btn-warning" style="font-family: Arial,sans-serif; font-size:11px">Catalogo de partes</a>
                            </div></td>
                        </tr>';
                }
                $html .= '</table>
                    <a href="/vucem/catalogo/agregar-cliente?patente=' . urlencode($misc->myEncrypt($info[0])) . '&aduana=' . urlencode($misc->myEncrypt($info[1])) . '" class="btn btn-success" style="margin-bottom: 20px">Agregar cliente</a>';
                echo $html;
            } else {
                echo "<div class=\"alert alert-error\">
                        <p>No existen clientes dados de alta para la Patente <b>{$info[0]}</b> y Aduana <b>{$info[1]}</b>. Dar <a href=\"/vucem/index/agregar-cliente?patente={$info[0]}&aduana={$info[1]}\">clic aqui para agregar</a>.</p>
                    </div>";
            }
        }
    }

    public function consultarRespuestasEdocAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $misc = new OAQ_Misc();
            $firmante = new Vucem_Model_VucemFirmanteMapper();
            $vucemEdoc = new Vucem_Model_VucemEdocMapper();
            $vucem = new OAQ_VucemEnh();
            $edocs = $vucemEdoc->obtenerSinRespuestaEdoc($this->_session->username);            
            foreach ($edocs as $edoc) {
                $cadenaOriginal = "|{$edoc["rfc"]}|{$edoc["solicitud"]}|";
                $rfc = $firmante->obtenerDetalleFirmante($edoc["rfc"], null, $edoc["patente"], $edoc["aduana"]);
                $pkeyid = openssl_get_privatekey(base64_decode($rfc["spem"]), $rfc["spem_pswd"]);
                $signature = "";
                if (isset($rfc["sha"]) && $rfc["sha"] == "sha256") {
                    openssl_sign($cadenaOriginal, $signature, $pkeyid, OPENSSL_ALGO_SHA256);
                } else {
                    openssl_sign($cadenaOriginal, $signature, $pkeyid);
                }
                $xmlEstatus = $vucem->estatusEDocument($rfc["rfc"], $rfc["ws_pswd"], $edoc["solicitud"], $rfc["cer"], $cadenaOriginal, base64_encode($signature));
                $response = $vucem->vucemServicio($xmlEstatus, "https://www.ventanillaunica.gob.mx/ventanilla/DigitalizarDocumentoService", 15);
                $string = $misc->stringInsideTags($response, "S:Envelope");
                if (isset($string[0])) {
                    $xmlInden = $misc->xmlIdent("<?xml version= \"1.0\"?><S:Envelope xmlns:S=\"http://schemas.xmlsoap.org/soap/envelope/\">" . $string[0] . "</S:Envelope>");
                } else {
                    continue;
                }
                $sentArray = $vucem->vucemXmlToArray($xmlInden);
                if (isset($sentArray["Body"])) {
                    $respuesta = $sentArray["Body"]["consultaDigitalizarDocumentoServiceResponse"];
                    if (isset($sentArray["Body"]) && $respuesta["respuestaBase"]["tieneError"] == "false") {
                        if (isset($respuesta["eDocument"]) && isset($respuesta["numeroDeTramite"])) {
                            $vucemEdoc->actualizarEdoc($edoc["id"], $edoc["solicitud"], 2, $xmlInden, $respuesta["eDocument"], $respuesta["numeroDeTramite"]);
                            $context = stream_context_create(array(
                                "ssl" => array(
                                    "verify_peer" => false,
                                    "verify_peer_name" => false,
                                    "allow_self_signed" => true
                                )
                            ));
                            $client = new Zend_Http_Client($this->_config->app->url . "/automatizacion/vucem/guardar-edocument", array("stream_context" => $context));
                            $client->setParameterPost(array("id" => $edoc["id"], "solicitud" => $edoc["solicitud"]));
                            $response = $client->request(Zend_Http_Client::POST);
                            if (($db = $misc->connectSitawin($edoc["patente"], $edoc["aduana"]))) {
                                if (($db->buscarReferencia($edoc["referencia"]) != null)) {
                                    $exists = $db->verificarEdoc($edoc["referencia"], $respuesta["eDocument"]);
                                    if (!$exists) {
                                        $folio = $db->folioEdoc($edoc["referencia"]);
                                        if ($folio) {
                                            $nuevoFolio = (int) $folio + 1;
                                        } else {
                                            $nuevoFolio = 1;
                                        }
                                        if (isset($nuevoFolio)) {
                                            $pago = $db->verificarPagoPedimento($edoc["referencia"], $edoc["pedimento"]);
                                            if ($pago == false || trim($pago) == '') {
                                                $db->actualizarEdocEnPedimento($edoc["referencia"], (int) $folio + 1, $respuesta["eDocument"]);
                                            }
                                        }
                                    }
                                } else {
                                    break;
                                }
                            }
                        }
                    } elseif ($respuesta["respuestaBase"]["tieneError"] == "true" && !preg_match('/se encuentra procesando/i', $respuesta["respuestaBase"]["error"]["mensaje"])) {
                        $vucemEdoc->actualizarEdoc($edoc["id"], $edoc["solicitud"], 0, $xmlInden);
                    }
                }
                unset($sentArray);
                unset($respuesta);
                unset($response);
            }
            $this->_helper->json(array("success" => true));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    /**
     * NO BORRAR 
     * // /vucem/data/prueba-firma?rfc=GWT921026L97&cadena=|216068|GWT921026L97|
      // /vucem/data/prueba-firma?rfc=MALL640523749&cadena=|23629532|MALL640523749|
      // /vucem/data/prueba-firma?rfc=MME921204HZ4&cadena=|TOCE.IMP|01-117762|0|2011-03-24|5|Solicitud de COVE automatica|OAQ030623UL8|GCO980828GY0|3589|0|0|0|36-2704499|STANDAR CAR TRUCK CO.|BUSSE HWY|865|PARK RIDEGE, IL|USA|60068|1|GCO980828GY0|GUNDERSON CONCARRIL SA DE CV|DOMICILIO CONOCIDO|S/N|CD. SAHAGUN|HG|MEX|43998|RESORTES HELICOIDALES|C62_1|1600.000|USD|9.000|14400.000|14400.000|
     */
    public function pruebaFirmaAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if ($this->_session->username != 'jvaldez') {
            die("No.");
        }
        $rfcg = $this->_request->getParam('rfc');
        $patente = $this->_request->getParam('patente');
        $aduana = $this->_request->getParam('aduana');
        $cadena = $this->_request->getParam('cadena');
        $env = $this->_request->getParam('env', null);
        $firmante = new Vucem_Model_VucemFirmanteMapper();
        $rfc = $firmante->obtenerDetalleFirmante($rfcg, $env, $patente, $aduana);
        $pkeyid = openssl_get_privatekey(base64_decode($rfc['spem']), $rfc['spem_pswd']);
        $signature = "";
        if (isset($rfc["sha"]) && $rfc["sha"] == 'sha256') {
            openssl_sign($cadena, $signature, $pkeyid, OPENSSL_ALGO_SHA256);
        } else {
            openssl_sign($cadena, $signature, $pkeyid);
        }
        echo "<!doctype html><html lang=\"en\"><head><meta charset=\"utf-8\"></head><style>body { font-family: sans-serif; font-size: 12px; margin: 0; padding: 0; } table { border-collapse: collapse; border-spacing: 0; } table th, table td { font-size: 11px; font-family: sans-serif; padding: 2px; border: 1px #777 solid; }</style>"
        . "<body>";
        echo "<label>RFC:</label><br><textarea style=\"width: 180px; height: 30px\">" . $rfcg . "</textarea><br>";
        echo "<label>Cadena:</label><br><textarea style=\"width: 850px; height: 50px\">" . $cadena . "</textarea><br>";
        echo "<label>Password WS:</label><br><textarea style=\"width: 850px; height: 50px\">" . $rfc["ws_pswd"] . "</textarea><br>";
        echo "<label>Certificado:</label><br><textarea style=\"width: 850px; height: 180px\">" . $rfc["cer"] . "</textarea><br>";
        echo "<label>Firma:</label><br><textarea style=\"width: 850px; height: 70px\">" . base64_encode($signature) . "</textarea>";
        echo "</body></html>";
    }

    /**
     * /vucem/data/tipos-documentos?rfc=MALL640523749
     * 
     */
    public function tiposDocumentosAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if ($this->_session->username != 'jvaldez') {
            die("No.");
        }
        $vucem = new OAQ_VucemEnh();
        $misc = new OAQ_Misc();
        $rfc = $this->_request->getParam('rfc');
        $patente = $this->_request->getParam('patente');
        $aduana = $this->_request->getParam('aduana');
        $firmante = new Vucem_Model_VucemFirmanteMapper();
        $sello = $firmante->obtenerDetalleFirmante($rfc, 'prod', $patente, $aduana);
        $cadena = "|{$rfc}|";
        $pkeyid = openssl_get_privatekey(base64_decode($sello['spem']), $sello['spem_pswd']);
        $signature = "";
        if (isset($sello["sha"]) && $sello["sha"] == 'sha256') {
            openssl_sign($cadena, $signature, $pkeyid, OPENSSL_ALGO_SHA256);
        } else {
            openssl_sign($cadena, $signature, $pkeyid);
        }
        $xml = $vucem->tiposDocumentos($sello["rfc"], $sello["ws_pswd"], $sello["cer"], $cadena, base64_encode($signature));
        $response = $vucem->vucemServicio($xml, "https://www.ventanillaunica.gob.mx/ventanilla/DigitalizarDocumentoService");
        $string = $misc->stringInsideTags($response, "S:Envelope");
        if (empty($string)) {
            $string = $misc->stringInsideTags($response, "env:Envelope");
        }
        $array = $vucem->vucemXmlToArray("<?xml version= \"1.0\"?><S:Envelope xmlns:S=\"http://schemas.xmlsoap.org/soap/envelope/\">" . $string[0] . "</S:Envelope>");
        $documents = array();
        foreach ($array['Body']['consultaTipoDocumentoServiceResponse']['tiposDeDocumentos']['tipoDeDocumento'] as $item) {
            $documents[(int) $item["idTipoDeDocumento"]] = $item["descripcion"];
        }
        ksort($documents);
        echo "<!doctype html><html lang=\"en\"><head><meta charset=\"utf-8\"></head><style>body { font-family: sans-serif; font-size: 12px; margin: 0; padding: 0; } table { border-collapse: collapse; border-spacing: 0; } table th, table td { font-size: 11px; font-family: sans-serif; padding: 2px; border: 1px #777 solid; }</style><body><table>"
        . "<tr>"
        . "<th>Tipo Documento</th>"
        . "<th>Descripción</th>"
        . "</tr>";
        foreach ($documents as $k => $v) {
            echo "<tr>"
            . "<td>{$k}</td>"
            . "<td>{$v}</td>"
            . "</tr>";
        }
        echo "</table></body></html>";
    }

    public function convertCoveToPdfAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $id = $this->_request->getParam('id', null);
        try {
            define("DOMPDF_ENABLE_REMOTE", true);
            require_once 'dompdf/dompdf_config.inc.php';
            $vucemSol = new Vucem_Model_VucemSolicitudesMapper();
            $vucem = new OAQ_Vucem();
            $xml = $vucemSol->obtenerSolicitudPorId($id);
            $this->view->fechas = array(
                'enviado' => $xml['enviado'],
                'actualizado' => $xml['actualizado']
            );
            $xmlArray = $vucem->vucemXmlToArray($xml["xml"]);
            unset($xmlArray["Header"]);
            if ($xml["cove"] != '' && $xml["cove"] != null) {
                $this->view->cove = $xml["cove"];
            }
            $this->view->pedimento = $xml["pedimento"];
            $this->view->referencia = $xml["referencia"];
            $this->view->id = $id;
            $this->view->data = $xmlArray["Body"]["solicitarRecibirCoveServicio"]["comprobantes"];
            $this->view->url = $this->_config->app->url;
            $cove = $vucemSol->obtenerNombreCove($id);
            if ($cove["cove"] != null && $cove["cove"] != '') {
                $filename = $cove["cove"];
            } else {
                $filename = 'Operacion_' . $cove["solicitud"];
            }
            $html = $this->view->render('data/convert-cove-to-pdf.phtml');
            $dompdf = new DOMPDF();
            $dompdf->set_paper("letter", "portrait");
            $dompdf->load_html($html);
            $dompdf->set_base_path($_SERVER['DOCUMENT_ROOT']);
            $dompdf->render();
            $dompdf->stream($filename . ".pdf");
        } catch (Exception $ex) {
            echo $e->getMessage();
        }
    }

    public function descargaXmlAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $vucemSol = new Vucem_Model_VucemSolicitudesMapper();
        $id = $this->_request->getParam('id');
        $debug = $this->_request->getParam('debug', null);
        if ($this->_session->role == 'super' || $this->_session->role == 'trafico_operaciones') {
            $xml = $vucemSol->obtenerSolicitudPorId($id);
        } else {
            $xml = $vucemSol->obtenerSolicitudPorId($id);
        }
        if (strtotime($xml["creado"]) > strtotime('2014-06-09 11:00:00')) {
            if(isset($debug) && $debug == true && $this->_session->username == "jvaldez") {
                header("Content-Type:text/xml;charset=utf-8");
                echo utf8_decode($xml["xml"]);
            } else {
                header("Content-Type:text/xml;charset=utf-8");
                echo utf8_decode($this->_cleanXml($xml["xml"]));
            }
        } else {
            header("Content-Type:text/xml");
            echo $this->_cleanXml($xml["xml"]);
        }
    }

    public function convertEdocToPdfAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        define("DOMPDF_ENABLE_REMOTE", true);
        require_once 'dompdf/dompdf_config.inc.php';
        $uuid = $this->_request->getParam('uuid');
        $solicitud = $this->_request->getParam('solicitud');
        $vucemEdoc = new Vucem_Model_VucemEdocMapper();
        if ($this->_session->role == 'super' || $this->_session->role == 'trafico_operaciones') {
            $data = $vucemEdoc->obtenerEdocPorUuid($uuid, $solicitud);
        } else {
            $data = $vucemEdoc->obtenerEdocPorUuid($uuid, $solicitud);
        }
        $this->view->data = $data;
        $this->view->id = $uuid;
        $this->view->solicitud = $solicitud;
        $this->view->url = $this->_config->app->url;
        $html = $this->view->render('data/convert-edoc-to-pdf.phtml');
        if ($data["edoc"] != null && $data["edoc"] != '') {
            $filename = $data["edoc"];
        } else {
            $filename = 'Operacion_' . $data["solicitud"];
        }
        $dompdf = new DOMPDF();
        $dompdf->set_paper("letter", "portrait");
        $dompdf->load_html($html);
        $dompdf->set_base_path($_SERVER['DOCUMENT_ROOT']);
        $dompdf->render();
        $dompdf->stream($filename . ".pdf");
    }

    protected function saveCove($cove) {
        $uri = "{$this->_config->app->url}/automatizacion/vucem/guardar-cove-pdf?cove={$cove}";
        $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_exec($ch);
        curl_close($ch);
    }

    protected function saveEDoc($uuid) {
        $uri = "{$this->_config->app->url}/automatizacion/vucem/guardar-edoc?uuid={$uuid}";
        $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_exec($ch);
        curl_close($ch);
    }

    public function consultaCoveAction() {
        $html = "";
        if ($this->getRequest()->isXmlHttpRequest()) {
            $cove = $this->getRequest()->getPost('cove', null);
            $rfc = $this->getRequest()->getPost('rfc', null);
            if (isset($rfc) && $rfc != '' && isset($cove) && $cove != '') {
                $vucem = new OAQ_VucemEnh();
                $firm = new Vucem_Model_VucemFirmanteMapper();
                $signature = "";
                $cadenaOriginal = "|{$rfc}|{$cove}|";
                $firmante = $firm->obtenerDetalleFirmante($rfc);
                $pkeyid = openssl_get_privatekey(base64_decode($firmante['spem']), $firmante['spem_pswd']);
                if (isset($firmante["sha"]) && $firmante["sha"] == 'sha256') {
                    openssl_sign($cadenaOriginal, $signature, $pkeyid, OPENSSL_ALGO_SHA256);
                } else {
                    openssl_sign($cadenaOriginal, $signature, $pkeyid);
                }
                $firma = base64_encode($signature);
                $xml = $vucem->consultaCove($firmante["rfc"], $firmante["ws_pswd"], $firmante['cer'], $cadenaOriginal, $firma, $cove);
                $url = (APPLICATION_ENV == 'production') ? "https://www.ventanillaunica.gob.mx/ventanilla/ConsultarEdocumentService" : "https://www2.ventanillaunica.gob.mx/procesamiento-cove-0/ConsultarEdocumentService";
                $response = $vucem->vucemXmlToArray($vucem->vucemServicio($xml, $url));
                unset($response["Header"]);
                if ($response["Body"]["ConsultarEdocumentResponse"]["response"]["contieneError"] == 'error' || preg_match('/no existe/i', $response["Body"]["ConsultarEdocumentResponse"]["response"]["mensaje"])) {
                    $html = "<div class=\"alert alert-error\" id=\"vu-result\">
                    <button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button><h4>¡Error!</h4>";
                    $html .= $response["Body"]["ConsultarEdocumentResponse"]["response"]["mensaje"] . "<br>";
                    $html .= "</div>";
                    echo Zend_Json_Encoder::encode(array('error' => $html));
                    return true;
                }
                $this->_svucem->coveVucem = $response;
                echo Zend_Json_Encoder::encode(array('success' => true));
                return true;
            } else {
                $html = "<div class=\"alert alert-error\" id=\"vu-result\">
        <button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button><h4>¡Error!</h4>";
                if (!isset($rfc) || $rfc == '') {
                    $html .= "<b>RFC:</b> Debe proporcionar RFC  o firmante.<br>";
                }
                if (!isset($cove) || $cove == '') {
                    $html .= "<b>COVE:</b> Debe proporcionar número de COVE.";
                }
                $html .= "</div>";
                echo Zend_Json_Encoder::encode(array('error' => $html));
                return true;
            }
        }
    }

    public function removerCoveAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if ($this->getRequest()->isXmlHttpRequest()) {
            $id = $this->getRequest()->getPost('id', null);
            $vucemSol = new Vucem_Model_VucemSolicitudesMapper();
            $vucemFact = new Vucem_Model_VucemFacturasMapper();
            $vucemProd = new Vucem_Model_VucemProductosMapper();
            $deleted = $vucemSol->removerSolicitud($id, ($this->_session->role != 'super' && $this->_session->role != 'trafico_operaciones') ? $this->_session->username : null);
            if ($deleted) {
                $idFact = $vucemFact->obtenerIdFactura($id);
                $vucemFact->removerFactura($id);
                $vucemProd->removerProductos($idFact);
            }
            $this->_logger->logEntry(
                    $this->_request->getModuleName() . ":" . $this->_request->getControllerName() . ":" . $this->_request->getActionName(), "BORRAR SOLICITUD: {$id}", $_SERVER['REMOTE_ADDR'], $this->_session->username);
            if ($deleted) {
                echo Zend_Json_Encoder::encode(array('success' => true));
                return true;
            }
            echo Zend_Json_Encoder::encode(array('success' => false));
            return true;
        } else {
            echo 'Action cannot be accessed directly.';
            return true;
        }
    }

    public function recentCoveAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if ($this->getRequest()->isXmlHttpRequest()) {
            $vucemSol = new Vucem_Model_VucemSolicitudesMapper();
            $vucemFact = new Vucem_Model_VucemFacturasMapper();
            $vucemProd = new Vucem_Model_VucemProductosMapper();
            $vucem = new OAQ_Vucem();
            $id = $this->getRequest()->getPost('id', null);
            $xml = $vucemSol->obtenerXmlSolicitud($id);
            $responseXml = $vucem->enviarCoveVucem($vucem->htmlSpanish($xml));
            $resultVucem = $vucem->respuestaVucem($responseXml);
            if (isset($resultVucem['operacion']) && $resultVucem['operacion'] != '') {
                $updated = $vucemSol->actualizarEstatusSolicitud($id, $resultVucem['operacion']);
                if ($updated) {
                    $idFact = $vucemFact->obtenerIdFactura($id);
                    $vucemFact->actualizarNumSolicitud($id, $resultVucem['operacion']);
                    $vucemProd->actualizarNumSolicitud($idFact, $resultVucem['operacion']);
                }
                echo Zend_Json_Encoder::encode(array('success' => true));
                return true;
            }
            echo Zend_Json_Encoder::encode(array('success' => false));
            return true;
        } else {
            echo 'Action cannot be accessed directly.';
            return true;
        }
    }

    public function removeProductAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if ($this->getRequest()->isXmlHttpRequest()) {
            $tmpProd = new Vucem_Model_VucemTmpProductosMapper();
            $idprod = $this->getRequest()->getPost('idprod', null);
            $ids = explode(',', $idprod);
            if ($ids) {
                $removed = $tmpProd->borrarProducto($ids[0], $ids[1], $this->_session->username);
                if ($removed) {
                    echo Zend_Json_Encoder::encode(array('success' => true));
                    return true;
                }
            } else {
                echo Zend_Json_Encoder::encode(array('success' => false));
                return true;
            }
        } else {
            echo 'Action cannot be accessed directly.';
            return true;
        }
    }

    public function borrarProductoAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if ($this->getRequest()->isXmlHttpRequest()) {
            $idprod = $this->getRequest()->getPost('prod', null);
            $factura = $this->getRequest()->getPost('fact', null);
            if ($idprod && $factura) {
                $tmpProd = new Vucem_Model_VucemTmpProductosMapper();
                $tmpProd->borrarProducto($factura, $idprod, $this->_session->username);
                echo Zend_Json_Encoder::encode(array('success' => true));
                return true;
            } else {
                echo Zend_Json_Encoder::encode(array('success' => false));
                return true;
            }
        }
    }

    protected function _cleanXml($xml) {
        return preg_replace('#<soapenv:Header(.*?)>(.*?)</soapenv:Header>#is', '', $xml);
    }

    public function downloadXmlAction() {
        $vucemSol = new Clientes_Model_CovesMapper();
        $id = $this->_request->getParam('id');
        $debug = $this->_request->getParam('debug', false);
        $xml = $vucemSol->obtenerSolicitudPorId($id, $this->_session->rfc);
        if (isset($xml["cove"]) && $xml["cove"] != null) {
            header('Content-disposition: attachment; filename="' . $xml["cove"] . '.xml"');
        } else {
            header('Content-disposition: attachment; filename="' . $xml["uuid"] . '.xml"');
        }
        header('Content-type: "text/xml"; charset="utf8"');
        if(isset($debug) && $debug === true) {
            echo $xml["xml"];
        } else {
            echo $this->_cleanXml($xml["xml"]);            
        }
    }

    public function setInvoiceSubvisionAction() {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $IdFact = $this->getRequest()->getPost('idfact', null);
            $tmpFact = new Vucem_Model_VucemTmpFacturasMapper();
            $currSub = $tmpFact->getSubdivisionValue($IdFact, $this->_session->username);
            if ($currSub == 0) {
                $tmpFact->updateSubdivisionValue($IdFact, $this->_session->username, 1);
            } if ($currSub == 1) {
                $tmpFact->updateSubdivisionValue($IdFact, $this->_session->username, 0);
            }
        }
    }

    public function setInvoiceRelfactAction() {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $IdFact = $this->getRequest()->getPost('idfact', null);
            $tmpFact = new Vucem_Model_VucemTmpFacturasMapper();
            $currSub = $tmpFact->getRelfactValue($IdFact, $this->_session->username);
            if ($currSub == 0) {
                $tmpFact->updateRelfactValue($IdFact, $this->_session->username, 1);
            } if ($currSub == 1) {
                $tmpFact->updateRelfactValue($IdFact, $this->_session->username, 0);
            }
        }
    }

    public function setInvoiceSendfactAction() {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $IdFact = $this->getRequest()->getPost('idfact', null);
            $tmpFact = new Vucem_Model_VucemTmpFacturasMapper();
            $currSub = $tmpFact->getSendfactValue($IdFact, $this->_session->username);
            if ($currSub == 0) {
                $tmpFact->updateSendfactValue($IdFact, $this->_session->username, 1);
            } if ($currSub == 1) {
                $tmpFact->updateSendfactValue($IdFact, $this->_session->username, 0);
            }
        }
    }

    public function descargarArchivoAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $get = $this->_request->getParams();
        $edoc = new Vucem_Model_VucemEdocMapper();
        $dig = $edoc->obtenerEdocDigitalizado($get["uuid"]);
        if ($dig) {
            header('Content-Type: application/pdf');
            header("Content-Transfer-Encoding: Binary");
            header("Content-disposition: attachment; filename=" . $dig["nomArchivo"]);
            echo base64_decode($dig["archivo"]);
        }
    }

    public function newFilesUploadAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $misc = new OAQ_Misc();
        $this->_svucem = NULL ? $this->_svucem = new Zend_Session_Namespace('') : $this->_svucem = new Zend_Session_Namespace('OAQVucem');
        $request = $this->getRequest();
        $sent = 0;
        if ($request->isPost()) {
            $data = $request->getPost();
            if (!empty($data)) {
                $this->_svucem->edReferencia = $data["referencia"];
                $this->_svucem->edAduana = $data["aduana"];
                $this->_svucem->edPatente = $data["patente"];
                $this->_svucem->edPedimento = $data["pedimento"];
                $this->_svucem->edFirmante = $data["firmante"];
            }
            if (!empty($this->_svucem->edfiles)) {
                $client = new GearmanClient();
                $client->addServer('127.0.0.1', 4730);
                foreach ($this->_svucem->edfiles as $item) {
                    $file = array(
                        'patente' => $data["patente"],
                        'aduana' => $data["aduana"],
                        'referencia' => strtoupper($data["referencia"]),
                        'pedimento' => $data["pedimento"],
                        'rfc' => $data["rfc"],
                        'firmante' => $data["firmante"],
                        'name' => basename($item["name"]),
                        'filename' => $this->_svucem->edtmp . DIRECTORY_SEPARATOR . basename($item["name"]),
                        'type' => mime_content_type($item["name"]),
                        'size' => filesize($item["name"]),
                        'tipoArchivo' => $item["tipoArchivo"],
                        'subTipoArchivo' => $item["subTipoArchivo"],
                        'output' => $this->_svucem->edtmp,
                        'username' => $this->_session->username,
                        'uuid' => $misc->getUuid($data["firmante"] . $data["patente"] . $data["aduana"] . $data["referencia"] . $data["pedimento"] . microtime()),
                        'email' => $this->_appconfig->getParam('vucem-email'),
                        'urlvucem' => $this->_config->app->vucem . "DigitalizarDocumentoService",
                    );
                    $client->addTaskBackground("edoc_enviaredocs", serialize($file));
                    $sent++;
                }
                $client->runTasks();
            }
            echo Zend_Json::encode(array('sent' => $sent));
        }
    }

    public function putInvoiceDataAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $misc = new OAQ_Misc();
                $tmpFact = new Vucem_Model_VucemTmpFacturasMapper();
                $data = $request->getPost();
                if (isset($data["CteRfc"]) && isset($data["CteIden"])) {
                    $tmpFact->actualizarIdentificadorCliente($data["IdFact"], $data["CteIden"], $data["CteRfc"], $this->_session->username);
                }
                if (isset($data["ProTaxID"]) && isset($data["ProIden"])) {
                    $tmpFact->actualizarIdentificadorProveedor($data["IdFact"], $data["ProIden"], $data["ProTaxID"], $this->_session->username);
                }
                if (!isset($data["IdFact"]) || $data["IdFact"] == "") {
                    $data["IdFact"] = strtoupper($misc->getUuid($data["Pedimento"] . $data["Referencia"]));
                }
                $data["Manual"] = true;
                $clean = array_map(array($misc, "trimUc"), $data);
                if (!($tmpFact->verify($data["IdFact"], $this->_session->username))) {
                    $tmpFact->nuevaFactura($this->_svucem->solicitante, $this->_svucem->tipoFigura, $this->_svucem->patente, $this->_svucem->aduana, $clean, $this->_session->username);
                    $this->_helper->json(array("success" => true, "uuid" => $data["IdFact"]));
                } else {
                    $tmpFact->actualizarDatosFactura($data["IdFact"], $this->_session->username, $clean);
                    $this->_helper->json(array("success" => true, "uuid" => $data["IdFact"]));
                }
                $this->_svucem->uuidFactura = $data["IdFact"];
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => true, "message" => $ex->getMessage()));
        }
    }

    public function putInvoiceProductAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
        }
        $request = $this->getRequest();
        if ($request->isPost()) {
            $misc = new OAQ_Misc();
            $tmpFact = new Vucem_Model_VucemTmpFacturasMapper();
            $tmpProd = new Vucem_Model_VucemTmpProductosMapper();
            $data = $request->getPost();
            if (isset($this->_svucem->uuidFactura)) {
                $data["ID_FACT"] = $this->_svucem->uuidFactura;
            }
            if (isset($data["ID_FACT"]) && (isset($data["ID_PROD"]) && $data["ID_PROD"] !== "")) {
                $updated = $tmpProd->actualizarDetalleProducto($data["ID_FACT"], $data["ID_PROD"], $this->_session->username, $data);
                if ($updated === true) {
                    $this->_helper->json(array("success" => true));
                } else {
                    $this->_helper->json(array("success" => false, "message" => "No se pudo actualizar."));
                }
            } elseif (isset($data["ID_FACT"]) && (isset($data["ID_PROD"]) && $data["ID_PROD"] === "")) {
                $data["ID_PROD"] = strtoupper($misc->getUuid($data["ID_FACT"] . microtime()));
                $fact = $tmpFact->facturaBasico($data["ID_FACT"]);
                $added = $tmpProd->nuevoProducto($fact["id"], $data["ID_FACT"], $this->_svucem->patente, $this->_svucem->aduana, null, null, $data, $this->_session->username);
                if ($added) {
                    $this->_helper->json(array("success" => true));
                } else {
                    $this->_helper->json(array("success" => false, "message" => "No se pudo agregar."));
                }
            }
        }
    }

    public function getInvoiceProductAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $idFact = $this->_request->getParam("idfact", null);
        $idProd = $this->_request->getParam("idprod", null);
        if (isset($idFact) && isset($idProd)) {
            $tmpProd = new Vucem_Model_VucemTmpProductosMapper();
            if (($tmpProd->verify($idFact, $idProd, $this->_session->username))) {
                $product = $tmpProd->obtenerProducto($idFact, $idProd, $this->_session->username);
                $data = array(
                    'success' => true,
                    'ORDEN' => $product["ORDEN"],
                    'DESC_COVE' => $product["DESC_COVE"],
                    'CODIGO' => $product["CODIGO"],
                    'PREUNI' => $product["PREUNI"],
                    'VALCOM' => $product["VALCOM"],
                    'MONVAL' => $product["MONVAL"],
                    'VALCEQ' => $product["VALCEQ"],
                    'VALMN' => $product["VALMN"],
                    'VALDLS' => $product["VALDLS"],
                    'CANTFAC' => $product["CANTFAC"],
                    'CANTTAR' => $product["CANTTAR"],
                    'UMC' => $product["UMC"],
                    'UMT' => $product["UMT"],
                    'PARTE' => $product["PARTE"],
                    'VALCEQ' => $product["VALCEQ"],
                    'MARCA' => $product["MARCA"],
                    'MODELO' => $product["MODELO"],
                    'NUMSERIE' => $product["NUMSERIE"],
                    'SUBMODELO' => $product["SUBMODELO"],
                    'UMC_OMA' => $product["UMC_OMA"],
                );
                echo Zend_Json_Encoder::encode($data);
                return true;
            } else {
                echo Zend_Json_Encoder::encode(array('success' => false, 'message' => "No se encontro el producto."));
                return true;
            }
        }
    }

    public function viewInvoiceProductsAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(false);
        $this->view->headLink()
                ->appendStylesheet("/css/rich_calendar.css")
                ->appendStylesheet("/less/traffic-module.css?" . time());
        if (isset($this->_svucem->uuidFactura) && $this->_svucem->uuidFactura != '') {
            $tmpProd = new Vucem_Model_VucemTmpProductosMapper();
            $products = $tmpProd->obtenerProductos($this->_svucem->uuidFactura, $this->_session->username);
            $this->view->products = $products;
        }
    }

    public function obtenerDestinatariosAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $query = $this->_request->getParam("query", null);
        $patente = $this->_request->getParam("patente", null);
        $aduana = $this->_request->getParam("aduana", null);
        $tipo = $this->_request->getParam("tipo", null);
        $cvecli = $this->_request->getParam("cvecli", null);
        $cvepro = $this->_request->getParam("cvepro", null);

        if ($tipo == 'TOCE.IMP') {
            $dest = new Vucem_Model_VucemClientesMapper();
            if (strlen($query) >= 3) {
                $destinatarios = $dest->searchByRfc($patente, $aduana, $query);
                if ($destinatarios !== false) {
                    echo Zend_Json_Encoder::encode($destinatarios);
                }
            }
        }
    }

    public function obtenerDestinatariosEnhAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $query = $this->_request->getParam("query", null);
        $tipo = $this->_request->getParam("tipo", null);

        if ($tipo == 'TOCE.IMP') {
            $dest = new Vucem_Model_VucemClientesMapper();
            if (strlen($query) >= 3) {
                $destinatarios = $dest->searchByRfcEnh($query);
                if ($destinatarios !== false) {
                    echo Zend_Json_Encoder::encode($destinatarios);
                }
            }
        }
    }

    public function obtenerEmisoresAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $query = $this->_request->getParam("query", null);
        $patente = $this->_request->getParam("patente", null);
        $aduana = $this->_request->getParam("aduana", null);
        $tipo = $this->_request->getParam("tipo", null);
        $cvecli = $this->_request->getParam("cvecli", null);
        $cvepro = $this->_request->getParam("cvepro", null);

        if ($tipo == 'TOCE.IMP') {
            $emi = new Vucem_Model_VucemProveedoresMapper();
            if (isset($this->_svucem->cveCli)) {
                $emisores = $emi->searchByTaxId($patente, $aduana, $query, $this->_svucem->cveCli);
                if ($emisores !== false) {
                    echo Zend_Json_Encoder::encode($emisores);
                }
            }
        }
    }

    public function detalleDestinatarioAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $rfc = $this->_request->getParam('rfc', null);
        $taxid = $this->_request->getParam('taxid', null);
        $tipo = $this->_request->getParam("tipo", null);
        $patente = $this->_request->getParam("patente", null);
        $aduana = $this->_request->getParam("aduana", null);
        if (isset($rfc)) {
            if ($tipo == 'TOCE.IMP') {
                if (strlen($rfc) >= 10) {
                    $clients = new Vucem_Model_VucemClientesMapper();
                    $detail = $clients->detalleCliente($patente, $aduana, $rfc);
                    $this->_svucem->cveCli = $detail["cvecte"];
                    echo Zend_Json_Encoder::encode($detail);
                    return true;
                }
            }
        }
    }

    public function detalleDestinatarioEnhAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $rfc = $this->_request->getParam('rfc', null);
        $tipo = $this->_request->getParam("tipo", null);
        if (isset($rfc)) {
            if ($tipo == 'TOCE.IMP') {
                if (strlen($rfc) >= 10) {
                    $clients = new Vucem_Model_VucemClientesMapper();
                    $detail = $clients->detalleClienteRfc($rfc);
                    $this->_svucem->cveCli = $detail["cvecte"];
                    echo Zend_Json_Encoder::encode($detail);
                }
            }
        }
    }

    public function detalleEmisorAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $rfc = $this->_request->getParam('rfc', null);
        $taxid = $this->_request->getParam('taxid', null);
        $tipo = $this->_request->getParam("tipo", null);
        $patente = $this->_request->getParam("patente", null);
        $aduana = $this->_request->getParam("aduana", null);
        if (isset($rfc) && isset($this->_svucem->cveCli)) {
            if ($tipo == 'TOCE.IMP') {
                $providers = new Vucem_Model_VucemProveedoresMapper();
                $detail = $providers->detalleProveedor($patente, $aduana, $taxid, $this->_svucem->cveCli);
                $this->_svucem->cvePro = $detail["cvepro"];
                echo Zend_Json_Encoder::encode($detail);
            }
        }
    }

    /**
     * http://casaws.localhost/sitawinReferencia?patente=3589&aduana=640&tipo=1&referencia=Q1403836&factura=2562
     * http://casaws.localhost/slamReferencia?patente=3589&aduana=240&tipo=1&referencia=14TQ006533&factura=KI033629
     * https://proexi.ddns.net:8443/slamReferencia?patente=3574&aduana=160&tipo=1&referencia=MI4-01756&factura=MP-MAY-1402
     * 
     * @throws Zend_Controller_Request_Exception
     */
    public function importFromWsAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $referencia = $this->_request->getParam("referencia", null);
        $numFactura = $this->_request->getParam("numfactura", null);
        $patente = $this->_request->getParam("patente", null);
        $aduana = $this->_request->getParam("aduana", null);
        $tipo = $this->_request->getParam("tipo", null);
        $ajuste = $this->_request->getParam("ajuste", null);
        $certificado = $this->_request->getParam("certificado", null);
        $subdiv = $this->_request->getParam("subdiv", null);
        $numexportador = $this->_request->getParam("numexportador", null);
        $context = stream_context_create(array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
                "allow_self_signed" => true
            )
        ));
        $soapSitawin = new Zend_Soap_Client("https://192.168.200.5/casaws/zfsoapsitawin?wsdl", array("stream_context" => $context));
        $soapAduanet = new Zend_Soap_Client("https://162.253.186.242:8443/zfsoapaduanet?wsdl", array("stream_context" => $context));
        $soapSlam = new Zend_Soap_Client("https://162.253.186.242:8443/zfsoapslam?wsdl", array("stream_context" => $context));
        $soapSlamManza = new Zend_Soap_Client("https://proexi.ddns.net:8443/zfsoapslam?wsdl", array("stream_context" => $context));
        $soapSlamDf = new Zend_Soap_Client("https://proexi.dyndns.org:8445/zfsoapslam.php?wsdl", array("stream_context" => $context));

        $tmpFact = new Vucem_Model_VucemTmpFacturasMapper();
        $tmpProd = new Vucem_Model_VucemTmpProductosMapper();
        $oma = new Vucem_Model_VucemUnidadesMapper();
        $vucem = new OAQ_VucemEnh();
        $misc = new OAQ_Misc();

        if ($patente == '3589' && $aduana == '640') {
            $invoice = $soapSitawin->facturaReferencia(3589, 640, ($tipo == 'TOCE.IMP') ? 1 : 2, $referencia, $numFactura);
        } elseif ($patente == '3589' && $aduana == '646') {
            $invoice = $soapSitawin->facturaReferencia(3589, 646, ($tipo == 'TOCE.IMP') ? 1 : 2, $referencia, $numFactura);
        } elseif ($patente == '3589' && $aduana == '240') {
            $invoice = $soapSlam->facturaReferencia(3589, 240, ($tipo == 'TOCE.IMP') ? 1 : 2, $referencia, $numFactura);
        } elseif ($patente == '3574' && $aduana == '240') {
            $invoice = $soapSlam->facturaReferencia(3574, 240, ($tipo == 'TOCE.IMP') ? 1 : 2, $referencia, $numFactura);
        } elseif ($patente == '3574' && $aduana == '160') {
            $invoice = $soapSlamManza->facturaReferencia(3574, 160, ($tipo == 'TOCE.IMP') ? 1 : 2, $referencia, $numFactura);
        } elseif ($patente == '3574' && $aduana == '470') {
            $invoice = $soapSlamDf->facturaReferencia(3574, 470, ($tipo == 'TOCE.IMP') ? 1 : 2, $referencia, $numFactura);
        }
        if (isset($invoice) && !empty($invoice)) {
            $uuid = strtoupper($misc->getUuid($patente . $invoice["pedimento"] . $aduana . $numFactura));
            $this->_svucem->uuidFactura = $uuid;
            if (!isset($invoice["destinatario"]["municipio"]) && isset($invoice["destinatario"]["ciudad"])) {
                $municipio = $invoice["destinatario"]["ciudad"];
            } else if (isset($invoice["destinatario"]["municipio"])) {
                $municipio = $invoice["destinatario"]["municipio"];
            }
            $data = array(
                'IdFact' => $uuid,
                'success' => true,
                'FechaFactura' => str_replace("/", "-", $invoice["fechaFactura"]),
                'Pedimento' => $invoice["pedimento"],
                'NumFactura' => $invoice["numFactura"],
                'Referencia' => $referencia,
                'CertificadoOrigen' => $certificado,
                'Subdivision' => $subdiv,
                'NumExportador' => $numexportador,
                'TipoOperacion' => $tipo,
                'CteIden' => $vucem->tipoIdentificador($invoice["destinatario"]["rfc"], $invoice["destinatario"]["pais"]),
                'CteNombre' => $invoice["destinatario"]["nombre"],
                'CteRfc' => $invoice["destinatario"]["rfc"],
                'CteCalle' => $invoice["destinatario"]["calle"],
                'CteNumExt' => $invoice["destinatario"]["numExt"],
                'CteNumInt' => $invoice["destinatario"]["numInt"],
                'CteColonia' => $invoice["destinatario"]["colonia"],
                'CteEdo' => $invoice["destinatario"]["estado"],
                'CteLocalidad' => $invoice["destinatario"]["localidad"],
                'CteMun' => $municipio,
                'CteCP' => $invoice["destinatario"]["codigoPostal"],
                'CtePais' => $invoice["destinatario"]["pais"],
                'ProIden' => $vucem->tipoIdentificador($invoice["emisor"]["taxID"], $invoice["emisor"]["pais"]),
                'ProNombre' => $invoice["emisor"]["nombre"],
                'ProTaxID' => $invoice["emisor"]["taxID"],
                'ProCalle' => $invoice["emisor"]["calle"],
                'ProNumExt' => $invoice["emisor"]["numExt"],
                'ProNumInt' => $invoice["emisor"]["numInt"],
                'ProColonia' => $invoice["emisor"]["colonia"],
                'ProEdo' => $invoice["emisor"]["estado"],
                'ProLocalidad' => $invoice["emisor"]["ciudad"],
                'ProMun' => $invoice["emisor"]["municipio"],
                'ProCP' => $invoice["emisor"]["codigoPostal"],
                'ProPais' => $invoice["emisor"]["pais"],
            );
            $data["Manual"] = true;
            $clean = array_map(array($misc, 'trimUc'), $data);
            if (!($tmpFact->verify($data["IdFact"], $this->_session->username))) {
                $idFact = $tmpFact->nuevaFactura($this->_svucem->solicitante, $this->_svucem->tipoFigura, $this->_svucem->patente, $this->_svucem->aduana, $clean, $this->_session->username);
                if ($idFact) {
                    foreach ($invoice["productos"] as $item) {
                        $tmp["ID_FACT"] = $uuid;
                        $tmp["ID_PROD"] = strtoupper($misc->getUuid(microtime() . $referencia));
                        $tmp["CODIGO"] = $item["fraccion"];
                        $tmp["DESC_COVE"] = str_replace(array("\r\n", "\r", "\n"), ' ', $item["descripcion"]);
                        $tmp["SUBFRA"] = 0;
                        $tmp["SUB"] = 0;
                        $tmp["DESC1"] = str_replace(array("\r\n", "\r", "\n"), ' ', $item["descripcion"]);
                        $tmp["ORDEN"] = $item["secuencia"];
                        $tmp["PARTE"] = strtoupper($item["numParte"]);
                        $tmp["PREUNI"] = $item["precioUnitario"];
                        $tmp["VALCOM"] = $item["valorComercial"];
                        $tmp["MONVAL"] = $item["moneda"];
                        $tmp["VALCEQ"] = (isset($ajuste)) ? $ajuste : $item["factorEquivalencia"];
                        $tmp["VALDLS"] = isset($item["valorDolares"]) ? $item["valorDolares"] : (isset($ajuste)) ? $ajuste * $item["valorComercial"] : 0;
                        $tmp["PREUNI"] = $item["precioUnitario"];
                        $tmp["CANTFAC"] = $item["cantidadFactura"];
                        $tmp["CANTTAR"] = $item["cantidadTarifa"];
                        $tmp["UMC"] = $item["umc"];
                        $tmp["UMT"] = $item["umt"];
                        $tmp["UMC_OMA"] = $oma->getOma($item["umc"]);
                        $tmp["CAN_OMA"] = $item["cantidadFactura"];
                        $tmpProd->nuevoProducto($idFact, $uuid, $patente, $aduana, $invoice["pedimento"], $referencia, array_map('trim', $tmp), $this->_session->username);
                        usleep(20000);
                    }
                }
            }
            echo Zend_Json_Encoder::encode($data);
        } else {
            echo Zend_Json_Encoder::encode(array('success' => false, 'message' => "La referencia o factura no existen en la patente {$patente} y aduana {$aduana}"));
        }
    }

    public function deleteInvoiceProductAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();

            $tmpProd = new Vucem_Model_VucemTmpProductosMapper();

            if (($tmpProd->verify($data["fact"], $data["prod"], $this->_session->username))) {
                $deleted = $tmpProd->borrarProducto($data["fact"], $data["prod"], $this->_session->username);
                if ($deleted === true) {
                    echo Zend_Json_Encoder::encode(array('success' => true, 'message' => "El producto ha sido borrado."));
                } else {
                    echo Zend_Json_Encoder::encode(array('success' => false, 'message' => "Ha ocurrido un problema al borrar el producto."));
                }
            }
        }
    }

    public function multiplesUploadsAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $misc = new OAQ_Misc();
        $this->_svucem = NULL ? $this->_svucem = new Zend_Session_Namespace('') : $this->_svucem = new Zend_Session_Namespace('OAQVucem');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $table = new Archivo_Model_DocumentosMapper();
            $result = $table->getAllEdocument();
            $select = '<option value="0">-- Seleccionar --</option>';
            foreach ($result as $item) {
                $select .= '<option value="' . $item['id'] . '">' . ($item['id'] . ' - ' . $item['nombre']) . '</option>';
            }
            $allowedExts = array("pdf");
            $info = array();
            foreach ($_FILES["file"]["name"] as $k => $file) {
                $temp = explode(".", $_FILES["file"]["name"][$k]);
                $extension = strtolower(end($temp));
                if ((
                        $_FILES["file"]["type"][$k] == "application/x-pdf" ||
                        $_FILES["file"]["type"][$k] == "application/acrobat"
                        ) && $_FILES["file"]["size"][$k] < 8388608 && in_array($extension, $allowedExts)) {

                    $size = round(($_FILES["file"]["size"][$k] / 1024 / 1024), 2);
                    $info[$k] = array('size' => $size, 'type' => $_FILES["file"]["type"][$k], 'docs' => '<select class="doctype" id="doctype_' . $k . '" onchange="dropdownChange(' . $k . ',this);">' . $select . '</select>');
                    move_uploaded_file($_FILES["file"]["tmp_name"][$k], $this->_svucem->edtmp . DIRECTORY_SEPARATOR . $misc->formatURL($_FILES["file"]["name"][$k]));
                    $this->_svucem->edfiles[$k]["name"] = $this->_svucem->edtmp . DIRECTORY_SEPARATOR . $misc->formatURL($_FILES["file"]["name"][$k]);
                    $this->_svucem->edfiles[$k]["referencia"] = (String) $data["ref_" . $k];
                    $this->_svucem->edfiles[$k]["pedimento"] = (String) $data["ped_" . $k];
                }
            }
            echo Zend_Json::encode($info);
        }
    }

    public function setEdocumentTypeAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $this->_svucem = NULL ? $this->_svucem = new Zend_Session_Namespace('') : $this->_svucem = new Zend_Session_Namespace('OAQVucem');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if (isset($data["id"]) && isset($data["val"])) {
                $this->_svucem->edfiles[$data["id"]]["tipoArchivo"] = $data["val"];
            }
        }
    }

    public function loadedDocumentsAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(false);
        $this->view->headLink()
                ->appendStylesheet("/less/traffic-module.css?" . time());
        $this->view->headScript()
                ->appendFile("/js/common/jquery-1.9.1.min.js")
                ->appendFile("/js/common/jquery.form.js");
        $this->_svucem = NULL ? $this->_svucem = new Zend_Session_Namespace('') : $this->_svucem = new Zend_Session_Namespace('OAQVucem');
        if (!empty($this->_svucem->edfiles)) {
            $this->view->files = $this->_svucem->edfiles;
        }
    }

    public function loadedDocumentsEnhAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(false);
        $this->view->headLink()
                ->appendStylesheet("/less/traffic-module.css?" . time());
        $this->view->headScript()
                ->appendFile("/js/jquery-1.9.1.min.js")
                ->appendFile("/js/jquery.form.js");
        $this->_svucem = NULL ? $this->_svucem = new Zend_Session_Namespace('') : $this->_svucem = new Zend_Session_Namespace('OAQVucem');
        try {
            $model = new Vucem_Model_VucemTmpEdocsMapper();
            $files = $model->obtener($this->_session->username);
            foreach ($files as $file) {
                if (!file_exists($file["nomArchivo"])) {
                    $model->eliminar($file["id"]);
                } else {
                    $data[] = $file;
                }
            }
            $this->view->files = $data;
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function changeFileTypeAction() {
        try {
            $tmp = new Vucem_Model_VucemTmpEdocsMapper();
            $id = $this->getRequest()->getParam('id', null);
            $type = $this->getRequest()->getParam('type', null);
            $updated = $tmp->cambiarTipo($id, $type);
            $model = new Archivo_Model_DocumentosMapper();
            if ($updated) {
                $icons = "<img onclick=\"editarArchivo('" . $id . "');\" src=\"" . $this->view->baseUrl() . "/images/icons/small_edit.png\" style=\"cursor: pointer;\" />&nbsp;<img src=\"" . $this->view->baseUrl() . "/images/icons/small_delete.png\" onclick=\"borrarArchivo('" . $id . "');\" style=\"cursor: pointer;\" />";
                echo Zend_Json_Encoder::encode(array('success' => true, 'type' => $model->tipoDocumento($type), 'icons' => $icons));
                return true;
            } else {
                echo Zend_Json_Encoder::encode(array('success' => false));
                return true;
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function changeEdocFileTypeAction() {
        try {
            $tmp = new Vucem_Model_VucemTmpEdocsMapper();
            $id = $this->getRequest()->getParam('id', null);
            $type = $this->getRequest()->getParam('type', null);
            $updated = $tmp->cambiarTipo($id, $type);
            $model = new Archivo_Model_DocumentosMapper();
            if ($updated) {
                $icons = "<img onclick=\"editarArchivo('" . $id . "');\" src=\"" . $this->view->baseUrl() . "/images/icons/small_edit.png\" style=\"cursor: pointer;\" />&nbsp;<img src=\"" . $this->view->baseUrl() . "/images/icons/small_delete.png\" onclick=\"borrarArchivo('" . $id . "');\" style=\"cursor: pointer;\" />";
                echo Zend_Json_Encoder::encode(array('success' => true, 'type' => $model->tipoDocumentoEdoc($type), 'icons' => $icons));
                return true;
            } else {
                echo Zend_Json_Encoder::encode(array('success' => false));
                return true;
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function cancelEditAction() {
        try {
            $id = $this->getRequest()->getParam('id', null);
            $type = $this->getRequest()->getParam('type', null);
            $model = new Archivo_Model_DocumentosMapper();
            if (isset($id)) {
                $icons = "<img onclick=\"editarArchivo('" . $id . "');\" src=\"" . $this->view->baseUrl() . "/images/icons/small_edit.png\" style=\"cursor: pointer;\" />&nbsp;<img src=\"" . $this->view->baseUrl() . "/images/icons/small_delete.png\" onclick=\"borrarArchivo('" . $id . "');\" style=\"cursor: pointer;\" />";
                echo Zend_Json_Encoder::encode(array('success' => true, 'type' => $model->tipoDocumento($type), 'icons' => $icons));
                return true;
            } else {
                echo Zend_Json_Encoder::encode(array('success' => false));
                return true;
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function borrarEdocumentAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $this->_svucem = NULL ? $this->_svucem = new Zend_Session_Namespace('') : $this->_svucem = new Zend_Session_Namespace('OAQVucem');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if (isset($this->_svucem->edfiles[$data["uuid"]])) {
                unlink($this->_svucem->edfiles[$data["uuid"]]["name"]);
                unset($this->_svucem->edfiles[$data["uuid"]]);
                echo Zend_Json::encode(array('success' => true));
                return true;
            } else {
                echo Zend_Json::encode(array('success' => false));
                return true;
            }
        }
    }

    public function enviarCoveAPedimentoAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost();
                $sita = new OAQ_Sitawin(true, $this->_svucem->sysdirip, $this->_svucem->sysuser, $this->_svucem->syspwd, $this->_svucem->sysdb, $this->_svucem->sysport, $this->_svucem->systype);
                $model = new Vucem_Model_VucemSolicitudesMapper();
                $data = $model->obtenerFacturaSolicitudPorId($post["id"]);
                if (isset($sita)) {
                    if ($sita->buscarFactura($data["referencia"], $data["factura"], $data["patente"])) {
                        $pago = $sita->verificarPagoPedimento($data["referencia"], $data["pedimento"]);
                        if ($pago == false || trim($pago) == '') {
                            $tieneCove = $sita->verificarCoveEnFactura($data["referencia"], $data["factura"]);
                            if ($tieneCove == false || trim($tieneCove) == '') {
                                $sita->actualizarCoveEnFactura($data["referencia"], $data["factura"], $data["cove"]);
                                $this->_helper->json(array('success' => true, 'message' => 'Se actualizo COVE en factura de pedimento.'));
                            } else {
                                $this->_helper->json(array('success' => false, 'message' => 'No se pudo actualizar por que la factura ya tiene COVE.'));
                            }
                            $this->_helper->json(array('success' => false, 'message' => 'No se pudo actualizar.'));
                        } else {
                            $this->_helper->json(array('success' => false, 'message' => 'No se pudo actualizar, pedimento pagado.'));
                        }
                    } else {
                        $this->_helper->json(array('success' => false, 'message' => 'No se encontro factura.'));
                    }
                } else {
                    $this->_helper->json(array('success' => false, 'message' => 'No tiene sistema de pedimentos asignado.'));
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function enviarAPedimentoAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $model = new Vucem_Model_VucemEdocMapper();
            $edoc = $model->obtenerEdocPorUuid($data["uuid"], $data["solicitud"]);
            if (isset($edoc) && !empty($edoc)) {
                if ($edoc["patente"] == 3589 && preg_match('/^64/', $edoc["aduana"])) {
                    $sis = new Application_Model_SisPedimentos();
                    $params = $sis->sisPedimentos(3589, 640);
                    if (isset($params) && !empty($params)) {
                        $sita = new OAQ_Sitawin(true, $params["direccion_ip"], $params["usuario"], $params["pwd"], $params["dbname"], $params["puerto"], $params["tipo"]);
                    }
                    if (isset($sita)) {
                        $referencia = $sita->infoPedimentoBasicaReferencia($edoc["referencia"]);
                    } else {
                        $this->_helper->json(array('success' => false, 'message' => 'No se puede conectar a la DB.'));
                    }
                } else {
                    $this->_helper->json(array('success' => false, 'message' => 'No se encontro referencia en DB.'));
                }
                if (isset($referencia) && !empty($referencia)) {
                    try {
                        $verificar = $sita->verificarEdoc($edoc["referencia"], $edoc["edoc"]);
                        if (!$verificar) {
                            $folio = $sita->folioEdoc($edoc["referencia"]);
                            if (isset($folio)) {
                                $nuevoFolio = $folio + 1;
                            } else {
                                $nuevoFolio = 1;
                            }
                            if (isset($nuevoFolio)) {
                                $updated = $sita->actualizarEdocEnPedimento($edoc["referencia"], $nuevoFolio, $edoc["edoc"]);
                                if ($updated === true) {
                                    echo Zend_Json::encode(array('success' => true, 'message' => 'EDocument actualizado.'));
                                    return true;
                                } else {
                                    echo Zend_Json::encode(array('success' => true, 'message' => 'NO se pudo actualizar.'));
                                    return true;
                                }
                            }
                        } else {
                            echo Zend_Json::encode(array('success' => false, 'message' => 'EDocument existe en pedimento.'));
                            return true;
                        }
                    } catch (Exception $ex) {
                        $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
                    }
                } else {
                    echo Zend_Json::encode(array('success' => false, 'message' => 'La referencia no existe en la BD.'));
                    return true;
                }
            } else {
                echo Zend_Json::encode(array('success' => true, 'message' => 'No edoc.'));
                return true;
            }
        }
    }

    public function sendNewFileAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost();
                if (!file_exists('/tmp/edoctmp')) {
                    mkdir('/tmp/edoctmp', 0777, true);
                }
                if (isset($post["id"])) {
                    $this->_session->uploadProgress = 0;
                    $misc = new OAQ_Misc();
                    $vucem = new OAQ_VucemEnh();
                    $model = new Vucem_Model_VucemTmpEdocsMapper();
                    $private = new Vucem_Model_VucemFirmanteMapper();

                    $file = $model->obtenerArchivo($post["id"]);
                    if (isset($file["nomArchivo"]) && file_exists($file["nomArchivo"])) {
                        $firmante = $private->obtenerDetalleFirmante($file["firmante"], null, $file["patente"], $file["aduana"]);
                        $base64 = base64_encode(file_get_contents($file["nomArchivo"]));
                        $hash = sha1_file($file["nomArchivo"]);
                        $uuid = $misc->getUuid($hash . microtime());

                        $pkeyid = openssl_get_privatekey(base64_decode($firmante['spem']), $firmante['spem_pswd']);
                        $signature = "";
                        $cadena = $vucem->cadenaEdocument($file["firmante"], $this->_appconfig->getParam('vucem-email'), $file["tipoArchivo"], pathinfo($file["nomArchivo"], PATHINFO_FILENAME), $file["rfcConsulta"], $hash);

                        if (isset($firmante["sha"]) && $firmante["sha"] == 'sha256') {
                            openssl_sign($cadena, $signature, $pkeyid, OPENSSL_ALGO_SHA256);
                        } else {
                            openssl_sign($cadena, $signature, $pkeyid);
                        }
                        $firma = base64_encode($signature);

                        $xml = $vucem->envioEdocument($firmante["rfc"], $firmante["ws_pswd"], $this->_appconfig->getParam('vucem-email'), $file["tipoArchivo"], pathinfo($file["nomArchivo"], PATHINFO_FILENAME), $file["rfcConsulta"], $base64, $firmante["cer"], $cadena, $firma);

                        $xmlFile = '/tmp/edoctmp' . DIRECTORY_SEPARATOR . $uuid . '.xml';
                        file_put_contents($xmlFile, $xml);
                        if (file_exists($xmlFile)) {
                            unset($xml);
                            $headers = array(
                                "Content-type: text/xml; charset=UTF-8",
                                "Accept: text/xml",
                                "Cache-Control: no-cache",
                                "Pragma: no-cache",
                                "Content-length: " . filesize($xmlFile) . "");
                            $soap = curl_init();
                            curl_setopt($soap, CURLOPT_URL, "https://www.ventanillaunica.gob.mx/ventanilla/DigitalizarDocumentoService");
                            curl_setopt($soap, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($soap, CURLOPT_SSL_VERIFYPEER, FALSE);
                            curl_setopt($soap, CURLOPT_SSL_VERIFYHOST, 1);
                            curl_setopt($soap, CURLOPT_SSL_VERIFYHOST, 0);
                            curl_setopt($soap, CURLOPT_POST, true);
                            curl_setopt($soap, CURLOPT_HTTPHEADER, $headers);
                            curl_setopt($soap, CURLOPT_POSTFIELDS, file_get_contents($xmlFile));
                            curl_setopt($soap, CURLOPT_TIMEOUT, 600);
                            curl_setopt($soap, CURLOPT_BUFFERSIZE, 128);
                            curl_setopt($soap, CURLOPT_PROGRESSFUNCTION, function($DownloadSize, $Downloaded, $UploadSize, $Uploaded) {
                                if ($UploadSize > 0) {
                                    echo json_decode(round(($Uploaded / $UploadSize) * 100));
                                    $this->_session->uploadProgress = round(($Uploaded / $UploadSize) * 100);
                                }
                            });
                            curl_setopt($soap, CURLOPT_NOPROGRESS, false);
                            $result = curl_exec($soap);
                            curl_close($soap);
                            $acuse = $this->_analizarRespuesta($result);

                            if (isset($acuse) && $acuse["success"] == true) {
                                $model->actualizarSolicitud($post["id"], $acuse["solicitud"]);
                                $this->_newEdocument($firmante["rfc"], $file["patente"], $file["aduana"], $file["pedimento"], $file["referencia"], $hash, $firmante["cer"], $cadena, $firma, $base64, $file["nomArchivo"], $file["tipoArchivo"], $file["rfcConsulta"], $acuse["solicitud"]);
                            }
                            unset($firmante);
                            unset($base64);
                        }
                    }
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function sendNewFileMultipleAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost();
                if (!file_exists('/tmp/edoctmp')) {
                    mkdir('/tmp/edoctmp', 0777, true);
                }
                if (isset($post["id"])) {
                    $this->_session->uploadProgress = 0;
                    $misc = new OAQ_Misc();
                    $vucem = new OAQ_VucemEnh();
                    $model = new Vucem_Model_VucemTmpEdocsMapper();
                    $private = new Vucem_Model_VucemFirmanteMapper();

                    $file = $model->obtenerArchivo($post["id"]);
                    $edocs = new Vucem_Model_VucemEdocMapper();
                    if (isset($file["nomArchivo"]) && file_exists($file["nomArchivo"]) && !($edocs->verificar($file["patente"], $file["aduana"], pathinfo($file["nomArchivo"], PATHINFO_FILENAME) . '.pdf', sha1_file($file["nomArchivo"])))) {
                        $firmante = $private->obtenerDetalleFirmante($file["firmante"], null, $file["patente"], $file["aduana"]);
                        $base64 = base64_encode(file_get_contents($file["nomArchivo"]));
                        $hash = sha1_file($file["nomArchivo"]);
                        $uuid = $misc->getUuid($hash . microtime());

                        $pkeyid = openssl_get_privatekey(base64_decode($firmante['spem']), $firmante['spem_pswd']);
                        $signature = "";
                        $cadena = $vucem->cadenaEdocument($file["firmante"], $this->_appconfig->getParam('vucem-email'), $file["tipoArchivo"], pathinfo($file["nomArchivo"], PATHINFO_FILENAME), $file["rfcConsulta"], $hash);
                        if (isset($firmante["sha"]) && $firmante["sha"] == 'sha256') {
                            openssl_sign($cadena, $signature, $pkeyid, OPENSSL_ALGO_SHA256);
                        } else {
                            openssl_sign($cadena, $signature, $pkeyid);
                        }
                        $firma = base64_encode($signature);

                        $xml = $vucem->envioEdocument($firmante["rfc"], $firmante["ws_pswd"], $this->_appconfig->getParam('vucem-email'), $file["tipoArchivo"], pathinfo($file["nomArchivo"], PATHINFO_FILENAME), $file["rfcConsulta"], $base64, $firmante["cer"], $cadena, $firma);
                        $xmlFile = '/tmp/edoctmp' . DIRECTORY_SEPARATOR . $uuid . '.xml';
                        file_put_contents($xmlFile, $xml);
                        if (file_exists($xmlFile)) {
                            unset($xml);
                            $headers = array(
                                "Content-type: text/xml; charset=UTF-8",
                                "Accept: text/xml",
                                "Cache-Control: no-cache",
                                "Pragma: no-cache",
                                "Content-length: " . filesize($xmlFile) . "");
                            $soap = curl_init();
                            curl_setopt($soap, CURLOPT_URL, "https://www.ventanillaunica.gob.mx/ventanilla/DigitalizarDocumentoService");
                            curl_setopt($soap, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($soap, CURLOPT_SSL_VERIFYPEER, false);
                            curl_setopt($soap, CURLOPT_POST, true);
                            curl_setopt($soap, CURLOPT_HTTPHEADER, $headers);
                            curl_setopt($soap, CURLOPT_POSTFIELDS, file_get_contents($xmlFile));
                            curl_setopt($soap, CURLOPT_TIMEOUT, 600);
                            curl_setopt($soap, CURLOPT_BUFFERSIZE, 128);
                            curl_setopt($soap, CURLOPT_PROGRESSFUNCTION, function($DownloadSize, $Downloaded, $UploadSize, $Uploaded) {
                                if ($UploadSize > 0) {
                                    if (round(($Uploaded / $UploadSize) * 100) == '100') {
                                        echo json_decode(round(($Uploaded / $UploadSize) * 100));
                                        $this->_session->uploadProgress = json_decode(round(($Uploaded / $UploadSize) * 100));
                                        return;
                                    }
                                }
                            });
                            curl_setopt($soap, CURLOPT_NOPROGRESS, false);
                            $result = curl_exec($soap);
                            curl_close($soap);
                            $acuse = $this->_analizarRespuesta($result);

                            if (isset($acuse) && $acuse["success"] == true) {
                                $model->actualizarSolicitud($post["id"], $acuse["solicitud"]);
                                $this->_newEdocument($firmante["rfc"], $file["patente"], $file["aduana"], $file["pedimento"], $file["referencia"], $hash, $firmante["cer"], $cadena, $firma, $base64, $file["nomArchivo"], $file["tipoArchivo"], $file["subTipoArchivo"], $file["rfcConsulta"], $acuse["solicitud"]);
                            }
                            unset($firmante);
                            unset($base64);
                        }
                    }
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    protected function _newEdocument($firmante, $patente, $aduana, $pedimento, $referencia, $hash, $cer, $cadena, $firma, $base64, $filename, $tipoArchivo, $subTipoArchivo, $rfcConsulta, $solicitud) {
        try {
            $misc = new OAQ_Misc();
            $model = new Vucem_Model_VucemEdocMapper();
            $uuid = $misc->getUuid($firmante . $patente . $aduana . $pedimento . $referencia . $hash . pathinfo($filename, PATHINFO_FILENAME));
            if (!($model->verificar($patente, $aduana, pathinfo($filename, PATHINFO_FILENAME) . '.pdf', $hash))) {
                $model->nuevaSolicitudEdoc($firmante, $patente, $aduana, $pedimento, $referencia, $uuid, $solicitud, $cer, $cadena, $firma, $base64, $tipoArchivo, $subTipoArchivo, pathinfo($filename, PATHINFO_FILENAME) . '.pdf', $hash, $this->_session->username, $this->_session->email, $rfcConsulta);
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    protected function _analizarRespuesta($response) {
        $misc = new OAQ_Misc();
        $vucem = new OAQ_VucemEnh();
        $string = $misc->stringInsideTags($response, "S:Envelope");
        if (empty($string)) {
            $string = $misc->stringInsideTags($response, "env:Envelope");
        }
        $xmlInden = "<?xml version= \"1.0\"?><S:Envelope xmlns:S=\"http://schemas.xmlsoap.org/soap/envelope/\">" . $string[0] . "</S:Envelope>";
        $sentArray = $vucem->vucemXmlToArray($xmlInden);
        if (isset($sentArray["Body"]["registroDigitalizarDocumentoServiceResponse"])) {
            $respuesta = $sentArray["Body"]["registroDigitalizarDocumentoServiceResponse"];
            if ($respuesta["respuestaBase"]["tieneError"] == 'false') {
                return array(
                    'success' => true,
                    'solicitud' => $respuesta["acuse"]["numeroOperacion"],
                );
            } else {
                return array(
                    'success' => false,
                    'message' => "Error en recepción",
                );
            }
        } else {
            return array(
                'success' => false,
                'message' => "No se obutvo respuesta",
            );
        }
    }

    public function getProgressUploadAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $request = $this->getRequest();
        if ($request->isPost()) {
            if (isset($this->_session->uploadProgress)) {
                echo json_decode($this->_session->uploadProgress);
            } else {
                echo 0;
            }
        }
    }

    public function markAsSendAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            if (isset($post["id"])) {
                $model = new Vucem_Model_VucemTmpEdocsMapper();
                try {
                    $model->marcarEnviado($post["id"]);
                } catch (Exception $ex) {
                }
            }
        }
    }

    public function deleteFileAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
        }
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            if (isset($post["id"])) {
                $model = new Vucem_Model_VucemTmpEdocsMapper();
                try {
                    $nomArchivo = $model->obtenerNomArchivo($post["id"]);
                    if (file_exists($nomArchivo["nomArchivo"])) {
                        unlink($nomArchivo["nomArchivo"]);
                    }
                    $model->borrar($post["id"]);
                } catch (Exception $ex) {
                    $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
                }
            }
        }
    }

    public function edocFileTypesAction() {
        $repo = new Archivo_Model_RepositorioMapper();
        $docs = new Archivo_Model_DocumentosMapper();

        $id = $this->getRequest()->getParam('id', null);
        $d = $docs->getAllEdocs();
        $type = $repo->getEdocFileType($id);

        $html = '<select id="select_' . $id . '" style="width: 350px; margin-bottom: 0;border-radius:2px; padding:0; height:20px;">';
        foreach ($d as $doc) {
            $html .= '<option value="' . $doc["id"] . '"'
                    . (($doc["id"] == $type) ? ' selected="selected"' : '')
                    . '>'
                    . $doc["id"] . ' - ' . $doc["nombre"]
                    . '</option>';
        }
        $html .= '</select>';
        echo $html;
    }

    public function descargaEdocumentsAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $model = new Archivo_Model_RepositorioMapper();
        $zipName = 'EDOCS_' . md5(microtime()) . '.zip';
        if (!file_exists('/tmp/zips')) {
            mkdir('/tmp/zips', 0777, true);
        }
        $zipFilename = '/tmp/zips' . DIRECTORY_SEPARATOR . $zipName;
        if (file_exists($zipFilename)) {
            unlink($zipFilename);
        }
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost();
                $edocs = array();
                if (isset($post["files"]) && !empty($post["files"])) {
                    foreach ($post["files"] as $file) {
                        array_push($edocs, $file);
                    }
                    $files = $model->getFilesEdocuments($edocs);
                    if (isset($files) && !empty($files)) {
                        $zip = new ZipArchive();
                        if ($zip->open($zipFilename, ZIPARCHIVE::CREATE) !== TRUE) {
                            return null;
                        }
                        foreach ($files as $file) {
                            if (file_exists($file["ubicacion"])) {
                                $zip->addFile($file["ubicacion"], basename($file["ubicacion"]));
                            }
                        }
                        $zip->close();
                        if (file_exists($zipFilename)) {
                            echo Zend_Json::encode(array('success' => true, 'filename' => $zipFilename));
                            return true;
                        }
                    }
                } else {
                    echo Zend_Json::encode(array('success' => false));
                    return true;
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function descargarEdocumentsAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $gets = $this->_request->getParams();
        if (isset($gets["filename"])) {
            if (file_exists($gets["filename"])) {
                if (!is_file($gets["filename"])) {
                    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
                    echo 'File not found';
                } else if (!is_readable($gets["filename"])) {
                    header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
                    echo 'File not readable';
                }
                header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
                header("Content-Type: application/zip");
                header("Content-Transfer-Encoding: Binary");
                header("Content-Length: " . filesize($gets["filename"]));
                header("Content-Disposition: attachment; filename=\"" . basename($gets["filename"]) . "\"");
                readfile($gets["filename"]);
                unlink($gets["filename"]);
                exit;
            }
        }
    }

    public function descargaCovesAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $model = new Archivo_Model_RepositorioMapper();
        $zipName = 'COVES_' . md5(microtime()) . '.zip';
        if (!file_exists('/tmp/zips')) {
            mkdir('/tmp/zips', 0777, true);
        }
        $zipFilename = '/tmp/zips' . DIRECTORY_SEPARATOR . $zipName;
        if (file_exists($zipFilename)) {
            unlink($zipFilename);
        }
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost();
                $coves = "";
                if (isset($post["files"]) && !empty($post["files"])) {
                    foreach ($post["files"] as $file) {
                        $coves .= $file . "|";
                    }
                    $files = $model->getFilesCoves(substr($coves, 0, -1));
                    if (isset($files) && !empty($files)) {
                        $zip = new ZipArchive();
                        if ($zip->open($zipFilename, ZIPARCHIVE::CREATE) !== TRUE) {
                            return null;
                        }
                        foreach ($files as $file) {
                            if (file_exists($file["ubicacion"])) {
                                $zip->addFile($file["ubicacion"], basename($file["ubicacion"]));
                            }
                        }
                        $zip->close();
                        if (file_exists($zipFilename)) {
                            echo Zend_Json::encode(array('success' => true, 'filename' => $zipFilename));
                            return false;
                        } else {
                            echo Zend_Json::encode(array('success' => false));
                            return false;
                        }
                    }
                } else {
                    echo Zend_Json::encode(array('success' => false));
                    return false;
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function descargarCovesAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $gets = $this->_request->getParams();
        if (isset($gets["filename"])) {
            if (file_exists($gets["filename"])) {
                if (!is_file($gets["filename"])) {
                    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
                    echo 'File not found';
                } else if (!is_readable($gets["filename"])) {
                    header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
                    echo 'File not readable';
                }
                header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
                header("Content-Type: application/zip");
                header("Content-Transfer-Encoding: Binary");
                header("Content-Length: " . filesize($gets["filename"]));
                header("Content-Disposition: attachment; filename=\"" . basename($gets["filename"]) . "\"");
                readfile($gets["filename"]);
                unlink($gets["filename"]);
                return true;
            }
        }
    }

    public function xmlEdocumentAction() {
        try {
            $filters = array(
                '*' => array('StringTrim', 'StripTags'),
                'id' => array('Digits'),
            );
            $input = new Zend_Filter_Input($filters, null, $this->_request->getParams());
            if ($input->isValid()) {
                $vucem = new OAQ_VucemEnh();
                $sello = new Vucem_Model_VucemFirmanteMapper();
                $mapper = new Vucem_Model_VucemEdocMapper();
                $data = $mapper->obtener($input->id);
                $fiel = $sello->obtenerDetalleFirmante($data["rfc"], null, $data["patente"], $data["aduana"]);
                $xml = $vucem->envioEdocument($data["rfc"], $fiel["ws_pswd"], $this->_appconfig->getParam('vucem-email'), $data["tipoDoc"], $data["nomArchivo"], $data["rfcConsulta"], "", $fiel["cer"], $data["firma"], $data["cadena"]);
                if (isset($xml)) {
                    header("Content-Type:text/xml;charset=utf-8");
                    echo utf8_decode($this->_cleanXml($xml));
                }
            } else {
                throw new Exception("Invalid parameters!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

}
