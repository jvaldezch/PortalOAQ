<?php

class Vucem_AjaxController extends Zend_Controller_Action {

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
        $this->_svucem = NULL ? $this->_svucem = new Zend_Session_Namespace('') : $this->_svucem = new Zend_Session_Namespace('OAQVucem');
        $this->_svucem->setExpirationSeconds($this->_appconfig->getParam('session-exp'));
    }

    public function obtenerProveedoresAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $gets = $this->_request->getParams();
        if ($gets["tipo"] == 'TOCE.IMP') {
            if (isset($gets["dest"]) && $gets["dest"] != '') {
                echo $this->_obtenerEmisores(strtoupper($gets["query"]), $gets["dest"]);
            }
        }
    }

    public function obtenerDetalleProveedorAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $gets = $this->_request->getParams();

        if ($gets["tipo"] == 'TOCE.IMP') {
            if (isset($gets["dest"]) && $gets["dest"] != '') {
                echo $this->_detalleEmisor(strtoupper($gets["nom"]), $gets["dest"]);
            }
        }
    }

    protected function _obtenerEmisores($query, $rfc) {
        $prov = new Vucem_Model_VucemProveedoresMapper();
        $rows = $prov->searchProvByRfcEnh($query, $rfc);
        if ($rows !== false) {
            return Zend_Json_Encoder::encode($rows);
        }
    }

    protected function _detalleEmisor($razonSocial, $cveCli) {
        $prov = new Vucem_Model_VucemProveedoresMapper();
        $rows = $prov->datosProveedor($razonSocial, $cveCli);
        if ($rows !== false) {
            return Zend_Json_Encoder::encode($rows);
        }
    }

    public function hddEdocumentsBackgroundAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $model = new Vucem_Model_VucemEdocMapper();
        $rows = $model->obtenerSinExpediente($this->_session->username);
        if (isset($rows) && $rows != false) {
            try {
                $misc = new OAQ_Misc();
                if (($misc->runGearmanProcess("edocs_worker.php", 1))) {
                    $client = new GearmanClient();
                    $client->addServer('127.0.0.1', 4730);

                    foreach ($rows as $item) {
                        $client->addTaskBackground("edoc_saveedoc", serialize(array('uuid' => $item["uuid"], 'solicitud' => $item["solicitud"])));
                    }
                    $client->runTasks();
                }
            } catch (Exception $ex) {
                $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
            }
        }
    }

    public function hddCovesBackgroundAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $model = new Vucem_Model_VucemSolicitudesMapper();
        $rows = $model->obtenerSinExpediente($this->_session->username);
        if (isset($rows) && $rows != false) {
            try {
                $misc = new OAQ_Misc();
                if (($misc->runGearmanProcess("edocs_worker.php", 1))) {
                    $client = new GearmanClient();
                    $client->addServer('127.0.0.1', 4730);
                    foreach ($rows as $item) {
                        $client->addTaskBackground("edoc_savecove", serialize(array('id' => $item["id"], 'solicitud' => $item["solicitud"])));
                    }
                    $client->runTasks();
                }
            } catch (Exception $ex) {
                $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
            }
        }
    }

    public function buscarRfcClienteAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $misc = new OAQ_Misc();
                $post = $request->getPost();
                if (isset($post["patente"]) && isset($post["aduana"]) && isset($post["referencia"])) {
                    $data = $misc->basicoReferencia($post["patente"], $post["aduana"], $post["referencia"]);
                    if (isset($data) && $data !== false) {
                        echo Zend_Json::encode(array('success' => true, 'rfc' => $data["rfcCliente"]));
                        return true;
                    }
                    echo Zend_Json::encode(array('success' => false));
                    return true;
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function enviarFacturaVucemAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $flt = array(
                    "*" => array('StringTrim', 'StripTags'),
                    "id" => array("Digits"),
                    "debug" => array("StringToLower"),
                );
                $vdr = array(
                    "id" => array('NotEmpty', new Zend_Validate_Int()),
                    "debug" => new Zend_Validate_InArray(array(true, false)),
                );
                $input = new Zend_Filter_Input($flt, $vdr, $request->getPost());
                if ($input->isValid("id")) {

                    $mapper = new Vucem_Model_VucemTmpFacturasMapper();
                    $arr = $mapper->obtenerFacturasParaEnvio($this->_session->username, $input->id);
                    if (!empty($arr)) {
                        $vucem = new OAQ_VucemEnh();
                        $conv = new OAQ_Conversion();
                        $misc = new OAQ_Misc();

                        $res = new OAQ_Respuestas();
                        $ser = new OAQ_Servicios();

                        $firmante = new Vucem_Model_VucemFirmanteMapper();
                        $sol = new Vucem_Model_VucemSolicitudesMapper();
                        foreach ($arr as $inv) {
                            if (isset($inv["Productos"])) {
                                if (!(count($inv["Productos"] >= 1))) {
                                    $this->_helper->json(array("success" => false, "message" => "La factura {$inv["NumFactura"]} no tiene mercancia(s)."));
                                }
                            } elseif (!isset($inv["Productos"])) {
                                $this->_helper->json(array("success" => false, "message" => "La factura {$inv["NumFactura"]} no tiene mercancia(s)."));
                            }                            
                            if ($conv->rfConsulta($vucem, $inv)) {
                                $rfcConsulta = $conv->rfConsulta($vucem, $inv);
                            }
                            $sello = $firmante->obtenerDetalleFirmante($inv["firmante"], null, $inv["Patente"], $inv["Aduana"]);
                            if (!isset($sello)) {
                                $this->_helper->json(array("success" => false, "message" => "Los sellos del firmante {$inv["firmante"]} no existen."));
                            }
                            $xml = new OAQ_Xml(true);
                            if(APPLICATION_ENV === "production") {
                                $data = $conv->crear($this->_appconfig->getParam("vucem-email"), $sello, $inv, $rfcConsulta);
                            } else {
                                $data = $conv->crear($this->_appconfig->getParam("vucem-email"), $sello, $inv, $rfcConsulta);                                
                            }
                            $xml->xmlCove($data);
                            $ser->setXml($xml->getXml());
                            $ser->consumirServicioCove();
                            $respuesta = $ser->getResponse();
                            $resp = $res->analizarRespuesta($ser->getResponse());
                            if (isset($resp) && !empty($resp)) {
                                $uuid = $misc->getUuid($inv["TipoOperacion"] . '-' . $inv["Patente"] . '-' . $inv["Aduana"] . '-' . $inv["Pedimento"] . '-' . $inv["Referencia"] . '-' . $inv["NumFactura"] . '-' . microtime());
                                if ($resp["error"] == false && isset($resp["numeroOperacion"])) {
                                    $id = $conv->agregarNuevaFactura($inv, $xml->getXml(), $uuid, $inv["firmante"], $this->_session->username);
                                    if (isset($id)) {
                                        $sol->actualizarSolicitudNueva($id, $resp['numeroOperacion'], $respuesta);
                                    }
                                }
                            }
                            $this->_helper->json(array("success" => true, "id" => $input->id));
                        }
                    }
                    $this->_helper->json(array("success" => false, "message" => "Nothing processed!"));
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function borrarEdocumentAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => "Digits",
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $i = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($i->isValid("id")) {
                    $mapper = new Vucem_Model_VucemEdocMapper();
                    if(true == ($mapper->borrar($i->id))) {
                        $this->_helper->json(array("success" => true));                        
                    }
                    $this->_helper->json(array("success" => false));
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function consultarSolicitudEdocumentAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => "Digits",
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $i = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($i->isValid("id")) {
                    $firmante = new Vucem_Model_VucemFirmanteMapper();
                    $mapper = new Vucem_Model_VucemEdocMapper();
                    $edoc = $mapper->obtener($i->id);
                    $sello = $firmante->obtenerDetalleFirmante($edoc["rfc"], null, $edoc["patente"], $edoc["aduana"]);
                    if (!isset($sello)) {
                        $this->_helper->json(array("success" => false));
                    }
                    $serv = new OAQ_Servicios();
                    $conv = new OAQ_Conversion();
                    $xml = new OAQ_Xml(false, true);
                    $data = $conv->consultaSolicitud($sello, $edoc["solicitud"]);
                    $xml->consultaEstatusOperacionEdocument($data);
                    $serv->setXml($xml->getXml());
                    $serv->consultaEstatusEdocument();
                    $res = new OAQ_Respuestas();
                    $resp = $res->analizarRespuesta($serv->getResponse());
                    if (isset($resp) && !empty($resp)) {
                        if ($resp["error"] == false && isset($resp["edocument"])) {
                            $this->_helper->json(array("success" => true));
                        }
                        if(isset($resp["messages"]['0'])) {
                            $this->_helper->json(array("success" => false, "message" => utf8_encode($resp["messages"]['0'])));
                        }
                    } else {
                        $this->_helper->json(array("success" => false, "message" => "No hay respuesta de la VUCEM."));
                    }
                    $this->_helper->json(array("success" => false));
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function consultarSolicitudCoveAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $flt = array(
                    "*" => array('StringTrim', 'StripTags'),
                    "id" => array("Digits"),
                );
                $vdr = array(
                    "id" => array('NotEmpty', new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($flt, $vdr, $request->getPost());
                if ($input->isValid("id")) {
                    $sol = new Vucem_Model_VucemSolicitudesMapper();
                    $firmante = new Vucem_Model_VucemFirmanteMapper();
                    $solicitud = $sol->obtenerSolicitud($input->id);
                    if (isset($solicitud) && !empty($solicitud)) {
                        $sello = $firmante->obtenerDetalleFirmante($solicitud["rfc"], null, $solicitud["patente"], $solicitud["aduana"]);
                        if (!isset($sello)) {
                            $this->_helper->json(array("success" => false));
                        }
                        $serv = new OAQ_Servicios();
                        $conv = new OAQ_Conversion();
                        $xml = new OAQ_Xml(true);
                        $data = $conv->consultaSolicitud($sello, $solicitud["solicitud"]);
                        $xml->consultaEstatusOperacionCove($data);
                        $serv->setXml($xml->getXml());
                        $serv->consultaEstatusCove();
                        $res = new OAQ_Respuestas();
                        $resp = $res->analizarRespuesta($serv->getResponse());
                        if (isset($resp) && !empty($resp)) {
                            if ($resp["error"] == false && isset($resp["edocument"])) {
                                $sol->actualizarSolicitudVucem($input->id, 2, $serv->getResponse(), $resp["edocument"], isset($resp["numeroAdenda"]) ? $resp["numeroAdenda"] : null);
                                $this->_helper->json(array("success" => true, "message" => "Respuesta satisfactoria de <strong>{$resp["edocument"]}</strong> para la factura <strong>{$solicitud["factura"]}</strong>.", "id" => $input->id, "solicitud" => $solicitud["solicitud"], "factura" => $solicitud["factura"]));
                            } else {
                                if(isset($resp["messages"]["0"])) {
                                    if(preg_match("/se encuentra procesando/", $resp["messages"]["0"])) {
                                        $this->_helper->json(array("success" => false, "message" => htmlentities(utf8_decode($resp["messages"]["0"]))));
                                    } else {
                                        $sol->actualizarSolicitudVucem($input->id, 0, $serv->getResponse());                                
                                        $this->_helper->json(array("success" => false, "message" => htmlentities(utf8_decode($resp["messages"]["0"]))));
                                    }
                                } elseif(isset($resp["message"])) {
                                    $sol->actualizarSolicitudVucem($input->id, 0, $serv->getResponse());                                
                                    $this->_helper->json(array("success" => false, "message" => htmlentities(utf8_decode($resp["message"]))));
                                }
                            }
                        } else {
                            $this->_helper->json(array("success" => false, "message" => "No hay respuesta de VUCEM."));
                        }
                    } else {
                        $this->_helper->json(array("success" => false, "message" => "Unknown error!"));                    
                    }
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function resendCoveAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $flt = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                );
                $vdr = array(
                    "id" => array('NotEmpty', new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($flt, $vdr, $request->getPost());
                if ($input->isValid("id")) {
                    $ser = new OAQ_Servicios();
                    $res = new OAQ_Respuestas();
                    $sol = new Vucem_Model_VucemSolicitudesMapper();
                    $fact = new Vucem_Model_VucemFacturasMapper();
                    $prod = new Vucem_Model_VucemProductosMapper();
                    $xml = $sol->obtenerXmlSolicitud($input->id);
                    $ser->setXml($xml);
                    $ser->consumirServicioCove();
                    $respuesta = $ser->getResponse();
                    $resp = $res->analizarRespuesta($ser->getResponse());
                    if (isset($resp) && !empty($resp)) {
                        if ($resp["error"] == false && isset($resp["numeroOperacion"])) {
                            $idFact = $fact->obtenerIdFactura($input->id);
                            $fact->actualizarNumSolicitud($input->id, $resp["numeroOperacion"]);
                            $prod->actualizarNumSolicitud($idFact, $resp["numeroOperacion"]);
                            $this->_helper->json(array("success" => true));
                        }
                    }
                    $this->_helper->json(array("success" => false, "message" => html_entity_decode($resp["messages"][0])));
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function adendaCoveAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $flt = array(
                    "*" => array("StringTrim", "StripTags"),
                    "factura" => array("StringToUpper"),
                    "cove" => array("Digits"),
                );
                $vdr = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "factura" => array("NotEmpty"),
                    "cove" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($flt, $vdr, $request->getPost());
                if ($input->isValid("id") && $input->isValid("factura") && $input->isValid("cove")) {
                    $sol = new Vucem_Model_VucemSolicitudesMapper();
                    $rfc = new Vucem_Model_VucemFirmanteMapper();
                    $fact = new Vucem_Model_VucemFacturasMapper();
                    $prod = new Vucem_Model_VucemProductosMapper();                    
                    $misc = new OAQ_Misc();
                    $detalleSol = $sol->obtenerDetalleSolicitudPorId($input->id);            
                    $tipFig = $rfc->tipoFigura($detalleSol["rfc"]);
                    if ($tipFig == 5 && $detalleSol["tipo"] == 'TOCE.IMP') {
                        $tipFig = 5;
                    } elseif ($tipFig == 5 && $detalleSol["tipo"] == 'TOCE.EXP') {
                        $tipFig = 4;
                    }
                    $detalleFact = $fact->obtenerFactura($detalleSol["solicitud"], urldecode($input->factura));
                    if(!isset($detalleFact)) {
                        throw new Exception("Invoice not found!");
                    }
                    $productos = $prod->obtenerProductos($detalleFact["id"]);
                    
                    unset($detalleFact["id"]);
                    unset($detalleFact["idSolicitud"]);
                    unset($detalleFact["Solicitud"]);
                    unset($detalleFact["Creado"]);
                    unset($detalleFact["Modificado"]);
                    unset($detalleFact["Usuario"]);
                    
                    $impExp = $detalleSol["tipo"] . '-' . $detalleSol["patente"] . '-' . $detalleSol["aduana"] . '-' . $detalleFact["Pedimento"] . '-' . $detalleFact["Referencia"] . '-' . $input->factura . '-' . time();
                    $uuid = $misc->getUuid($impExp);
                    $detalleFact["IdFact"] = $uuid;                    
                    $detalleFact["adenda"] = $detalleSol["cove"];
                    $detalleFact["Manual"] = null;
                    
                    $tmpFact = new Vucem_Model_VucemTmpFacturasMapper();
                    $tmpProd = new Vucem_Model_VucemTmpProductosMapper();
                    $added = $tmpFact->nuevaFactura($detalleSol["rfc"], $rfc->tipoFigura($detalleSol["rfc"]), $detalleSol["patente"], $detalleSol["aduana"], $detalleFact, $this->_session->username);
                    foreach ($productos as $p) {
                        if (isset($p["SOLICITUD"])) {
                            unset($p["SOLICITUD"]);
                        }
                        if (isset($p["IDFACTURA"])) {
                            unset($p["IDFACTURA"]);
                        }
                        if (isset($p["CREADO"])) {
                            unset($p["CREADO"]);
                        }
                        if (isset($p["MODIFICADO"])) {
                            unset($p["MODIFICADO"]);
                        }
                        if (isset($p["USUARIO"])) {
                            unset($p["USUARIO"]);
                        }
                        if (isset($p["ACTIVE"])) {
                            unset($p["ACTIVE"]);
                        }
                        $tmpProd->nuevoProducto($added, $detalleFact["IdFact"], $detalleSol["patente"], $detalleSol["aduana"], $detalleFact["Pedimento"], $detalleFact["Referencia"], $p, $this->_session->username);
                    } // foreach
                    if($added) {
                        $this->_helper->json(array("success" => true));                        
                    }
                    $this->_helper->json(array("success" => false));
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function reenviarCoveAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $flt = array(
                    "*" => array("StringTrim", "StripTags"),
                    "factura" => array("StringToUpper"),
                );
                $vdr = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "factura" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($flt, $vdr, $request->getPost());
                if ($input->isValid("id") && $input->isValid("factura")) {
                    $sol = new Vucem_Model_VucemSolicitudesMapper();
                    $rfc = new Vucem_Model_VucemFirmanteMapper();
                    $fact = new Vucem_Model_VucemFacturasMapper();
                    $prod = new Vucem_Model_VucemProductosMapper();
                    $misc = new OAQ_Misc();
                    $detalleSol = $sol->obtenerDetalleSolicitudPorId($input->id);
                    $tipFig = $rfc->tipoFigura($detalleSol["rfc"]);
                    if ($tipFig == 5 && $detalleSol["tipo"] == "TOCE.IMP") {
                        $tipFig = 5;
                    } elseif ($tipFig == 5 && $detalleSol["tipo"] == "TOCE.EXP") {
                        $tipFig = 4;
                    }
                    $detalleFact = $fact->obtenerFacturaPorIdSolicitud($input->id);
                    if (!isset($detalleFact)) {
                        throw new Exception("No invoice found!");
                    }
                    $productos = $prod->obtenerProductos($detalleFact["id"]);
                    if (isset($productos) && !empty($productos)) {
                        $tmpFact = new Vucem_Model_VucemTmpFacturasMapper();
                        $tmpProd = new Vucem_Model_VucemTmpProductosMapper();
                        $table = new Vucem_Model_Table_TmpFacturas($detalleFact);
                        $table->setId(null);
                        $table->setIdFact($misc->getUuid($detalleFact["uuid"] . microtime()));
                        $table->setFirmante($detalleSol["rfc"]);
                        $table->setFigura($rfc->tipoFigura($detalleSol["rfc"]));
                        $table->setPatente($detalleSol["patente"]);
                        $table->setAduana($detalleSol["aduana"]);
                        $table->setCreado(date("Y-m-d H:i:s"));
                        $table->setAdenda(null);
                        $table->setManual(null);
                        $table->setUsuario($this->_session->username);
                        $tmpFact->save($table);
                        if (null !== ($table->getId())) {
                            foreach ($productos as $p) {
                                $tbl = new Vucem_Model_Table_TmpProductos($p);
                                $tbl->setId(null);
                                $tbl->setID_PROD($misc->getUuid($p["id"] . microtime()));
                                $tbl->setIDFACTURA($table->getId());
                                $tbl->setID_FACT($table->getIdFact());
                                $tbl->setUSUARIO($this->_session->username);
                                $tbl->setCREADO(date("Y-m-d H:i:s"));
                                $tbl->setMODIFICADO(null);
                                $tmpProd->save($tbl);
                                unset($tbl);
                            }
                            $this->_helper->json(array("success" => true));
                        }
                    } else {
                        throw new Exception("No products found!");
                    }
                    $this->_helper->json(array("success" => false));
                } else {
                    throw new Exception("Invalid input!");                    
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function solicitudesCovesAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $misc = new OAQ_Misc();                
                $serv = new OAQ_Servicios();
                $conv = new OAQ_Conversion();
                $res = new OAQ_Respuestas();
                $firmante = new Vucem_Model_VucemFirmanteMapper();
                $mapper = new Vucem_Model_VucemSolicitudesMapper();
                if (APPLICATION_ENV == "production") {
                    $arr = $mapper->obtenerSinRespuestaCove($this->_session->username);
                } else {
                    $arr = $mapper->obtenerSinRespuestaCove();
                }
                foreach ($arr as $item) {
                    $sello = $firmante->obtenerDetalleFirmante($item["rfc"], null, $item["patente"], $item["aduana"]);
                    if (!isset($sello)) {
                        $this->_helper->json(array("success" => false, "message" => "No key found!"));
                    }
                    $xml = new OAQ_Xml(true);
                    $data = $conv->consultaSolicitud($sello, $item["solicitud"]);
                    $xml->consultaEstatusOperacionCove($data);
                    $serv->setXml($xml->getXml());
                    $serv->consultaEstatusCove();
                    $resp = $res->analizarRespuesta($serv->getResponse());
                    if (isset($resp) && !empty($resp)) {
                        if ($resp["error"] == false && isset($resp["edocument"])) {
                            $mapper->actualizarSolicitudVucem($item["id"], 2, $serv->getResponse(), $resp["edocument"], isset($resp["numeroAdenda"]) ? $resp["numeroAdenda"] : null);
                            if (($db = $misc->connectSitawin($item["patente"], $item["aduana"]))) {
                                if ((int) $item["consolidado"] == 0) {
                                    if (($find = $db->buscarFactura($item["referencia"], $item["factura"], $item["patente"]))) {
                                        if (APPLICATION_ENV == "production" && $find) {
                                            $pago = $db->verificarPagoPedimento($item["referencia"], $item["pedimento"]);
                                            if ($pago === false) {
                                                $mapper->enPedimento($item["id"]);
                                                $db->actualizarCoveConsolidadoEnFactura($item["pedimento"], $item["factura"], $resp["edocument"]);
                                            }
                                        }
                                    }
                                } else if((int) $item["consolidado"] == 1) {
                                    if ($db->verificarPagoPedimento($item["referencia"], $item["pedimento"]) === false) {
                                        if ($db->buscarFacturaConsolidado($item["pedimento"], $item["factura"]) === true) {
                                            $db->actualizarCoveConsolidadoEnFactura($item["pedimento"], $item["factura"], $resp["edocument"]);
                                            $mapper->enPedimento($item["id"]);
                                        }                                        
                                    }
                                }
                                unset($db);
                                unset($pago);
                                unset($find);
                            }
                            $context = stream_context_create(array(
                                "ssl" => array(
                                    "verify_peer" => false,
                                    "verify_peer_name" => false,
                                    "allow_self_signed" => true
                                )
                            ));
                            $client = new Zend_Http_Client($this->_config->app->url . "/automatizacion/vucem/guardar-cove", array("stream_context" => $context));
                            $client->setParameterPost(array("id" => $item["id"]));
                            $client->request(Zend_Http_Client::POST);
                        } elseif($resp["error"] == true && !preg_match("/se encuentra procesando/i", $resp["message"][0])) {
                            $mapper->actualizarSolicitudVucem($item["id"], 0, $serv->getResponse());
                        }
                    }
                    unset($resp);
                }
                $this->_helper->json(array("success" => true));
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function enviarPedimentoAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id")) {
                    $misc = new OAQ_Misc();
                    $mapper = new Vucem_Model_VucemSolicitudesMapper();
                    $arr = $mapper->obtenerFacturaSolicitudPorId($input->id);
                    if (($db = $misc->sitawinTrafico($arr["patente"], $arr["aduana"]))) {
                        $pago = $db->verificarPagoPedimento($arr["referencia"], $arr["pedimento"]);
                        if ($pago == false || trim($pago) == "") {
                            $tiene = $db->verificarCoveEnFactura($arr["referencia"], $arr["factura"]);
                            if ($tiene == false || trim($tiene) == "") {
                                $db->actualizarCoveEnFactura($arr["referencia"], $arr["factura"], $arr["cove"]);
                                $mapper->enPedimento($input->id);
                                $this->_helper->json(array("success" => true, "message" => "Se actualizo COVE en factura de pedimento."));
                            } else {
                                $this->_helper->json(array("success" => false, "message" => "No se pudo actualizar por que la factura ya tiene COVE."));
                            }
                        } else {
                            $mapper->enPedimento($input->id);
                            $this->_helper->json(array("success" => false, "message" => "No se pudo actualizar, pedimento pagado."));
                        }
                    }
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function solicitudesEdocumentsAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $misc = new OAQ_Misc();
                $serv = new OAQ_Servicios();
                $conv = new OAQ_Conversion();
                $res = new OAQ_Respuestas();
                $firmante = new Vucem_Model_VucemFirmanteMapper();
                $mapper = new Vucem_Model_VucemEdocMapper();
                $index = new Vucem_Model_VucemEdocIndex();
                if (APPLICATION_ENV == "production" && $this->_session->role != "super") {
                    $arr = $mapper->obtenerSinRespuestaEdoc($this->_session->username);
                } elseif (APPLICATION_ENV == "production" && $this->_session->role == "super") {
                    $arr = $mapper->obtenerSinRespuestaEdoc(null, null, null, 2);
                } else {
                    $arr = $mapper->obtenerSinRespuestaEdoc();
                }
                foreach ($arr as $item) {
                    $sello = $firmante->obtenerDetalleFirmante($item["rfc"], null, $item["patente"], $item["dauana"]);
                    if (!isset($sello)) {
                        $this->_helper->json(array("success" => false, "message" => "No key found!"));
                    }
                    $xml = new OAQ_Xml(false, true);
                    $data = $conv->consultaSolicitud($sello, $item["solicitud"]);
                    $xml->consultaEstatusOperacionEdocument($data);
                    $serv->setXml($xml->getXml());
                    $serv->consultaEstatusEdocument();
                    $resp = $res->analizarRespuesta($serv->getResponse());
                    if (isset($resp) && !empty($resp)) {
                        if ($resp["error"] == false && isset($resp["edocument"])) {
                            $mapper->actualizarEdoc($item["id"], $item["solicitud"], 2, $serv->getResponse(), $resp["edocument"], $resp["numeroDeTramite"]);
                            $index->actualizarEdoc($item["id"], 2, $resp["edocument"]);
                            $context = stream_context_create(array(
                                "ssl" => array(
                                    "verify_peer" => false,
                                    "verify_peer_name" => false,
                                    "allow_self_signed" => true
                                )
                            ));
                            $client = new Zend_Http_Client($this->_config->app->url . "/automatizacion/vucem/guardar-edocument", array("stream_context" => $context));
                            $client->setParameterPost(array("id" => $item["id"], "solicitud" => $item["solicitud"]));
                            $client->request(Zend_Http_Client::POST);
                            if (($db = $misc->connectSitawin($item["patente"], $item["aduana"]))) {
                                if (($db->buscarReferencia($item["referencia"]) != null)) {
                                    $exists = $db->verificarEdoc($item["referencia"], $resp["edocument"]);
                                    if (!$exists) {
                                        $folio = $db->folioEdoc($item["referencia"]);
                                        if ($folio) {
                                            $nuevoFolio = (int) $folio + 1;
                                        } else {
                                            $nuevoFolio = 1;
                                        }
                                        if (isset($nuevoFolio)) {
                                            $pago = $db->verificarPagoPedimento($item["referencia"], $item["pedimento"]);
                                            if ($pago == false || trim($pago) == '') {
                                                $db->actualizarEdocEnPedimento($item["referencia"], (int) $folio + 1, $resp["edocument"]);
                                            }
                                        }
                                    }
                                } else {
                                    continue;
                                }
                            }
                        } elseif ($resp["error"] == true && !preg_match("/se encuentra procesando/i", $resp["message"][0])) {
                            $mapper->actualizarEdoc($item["id"], $item["solicitud"], 1, $serv->getResponse());
                            $index->actualizarEdoc($item["id"], 1);
                        }
                    }
                }
                $this->_helper->json(array("success" => true));
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function guardarEncabezadoFacturaAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $flt = array(
                    "*" => array("StringTrim", "StripTags"),
                );
                $vdr = array(
                    "numFactura" => array("NotEmpty"),
                    "idFact" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($flt, $vdr, $request->getPost());
                if ($input->isValid("numFactura") && $input->isValid("idFact")) {
                    $mapper = new Vucem_Model_VucemTmpFacturasMapper();
                    $updated = $mapper->actualizarNumeroFactura($input->idFact, $input->numFactura);
                    if($updated === true) {
                        $this->_helper->json(array("success" => true, "idFact" => $input->idFact));
                    }
                    $this->_helper->json(array("success" => false));
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function existenFacturasAction() {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $tmpFact = new Vucem_Model_VucemTmpFacturasMapper();
            $cantidad = $tmpFact->existenFacturas($this->_session->username);
            if ($cantidad == 0) {
                $this->_helper->json(array('success' => false, 'message' => 'No ha seleccionado facturas.'));
            } else {
                $this->_helper->json(array('success' => true));
            }
        }
    }

}
