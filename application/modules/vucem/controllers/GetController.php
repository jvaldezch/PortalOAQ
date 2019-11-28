<?php

class Vucem_GetController extends Zend_Controller_Action {

    protected $_session;
    protected $_svucem;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $contextSwitch = $this->_helper->getHelper("contextSwitch");
        $contextSwitch->addActionContext("cargar-mis-edocuments", "json")
                ->initContext();
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
    }

    public function cargarMisEdocumentsAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $mapper = new Vucem_Model_VucemTmpEdocsMapper();
            $usuario = null;
            if (!in_array($this->_session->role, array("super", "trafico_operaciones", "gerente"))) {
                $usuario = $this->_session->username;
            }
            $arr = $mapper->obtener($usuario);
            $view = new Zend_View();
            $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
            $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");
            $view->data = $arr;
            $this->_helper->json(array("success" => true, "html" => $view->render("cargar-mis-edocuments.phtml")));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function enviarEdocumentsAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $mapper = new Vucem_Model_VucemTmpEdocsMapper();
            $usuario = null;
            if (!in_array($this->_session->role, array("super", "trafico_operaciones", "gerente"))) {
                $usuario = $this->_session->username;
            }
            $arr = $mapper->obtener($usuario);
            if (count($arr)) {
                $misc = new OAQ_Misc();
                $client = new GearmanClient();
                $client->addServer("127.0.0.1", 4730);
                if (APPLICATION_ENV === "production") {
                    $email = $this->_appconfig->getParam("vucem-email");
                } else {
                    $email = "soporte@oaq.com.mx";
                }
                foreach ($arr as $item) {
                    if (file_exists($item["nomArchivo"])) {
                        $uuid = $misc->getUuid($item["hash"] . microtime());
                        $file = array(
                            "firmante" => $item["firmante"],
                            "patente" => $item["patente"],
                            "aduana" => $item["aduana"],
                            "referencia" => $item["referencia"],
                            "pedimento" => $item["pedimento"],
                            "rfc" => $item["rfcConsulta"],
                            "name" => basename($item["nomArchivo"]),
                            "filename" => $item["nomArchivo"],
                            "type" => mime_content_type($item["nomArchivo"]),
                            "size" => filesize($item["nomArchivo"]),
                            "tipoArchivo" => $item["tipoArchivo"],
                            "subTipoArchivo" => $item["subTipoArchivo"],
                            "username" => $item["usuario"],
                            "uuid" => $uuid,
                            "email" => $email,
                            "urlvucem" => $this->_config->app->vucem . "DigitalizarDocumentoService",
                        );
                    }
                    $client->addTaskBackground("edoc_enviaredocs", serialize($file));
                }
                $client->runTasks();
            }
            $this->_helper->json(array("success" => true));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function consultarEdocumentsAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
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
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function xmlEdocumentAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $vucem = new OAQ_VucemEnh();
                $sello = new Vucem_Model_VucemFirmanteMapper();
                $mapper = new Vucem_Model_VucemEdocMapper();
                $data = $mapper->obtener($input->id);
                $fiel = $sello->obtenerDetalleFirmante($data["rfc"], null, $data["patente"], $data["aduana"]);
                $xml = $vucem->envioEdocument($data["rfc"], $fiel["ws_pswd"], $data["email"], $data["tipoDoc"], $data["nomArchivo"], $data["rfcConsulta"], "", $fiel["cer"], $data["firma"], $data["cadena"]);
                if (isset($xml)) {
                    header("Content-Type:text/xml;charset=utf-8");
                    echo utf8_decode($vucem->secureXml($xml));
                }
            } else {
                throw new Exception("Invalid parameters!");
            }
        } catch (Exception $ex) {
            throw new Zend_Exception($ex->getMessage());
        }
    }

    /**
     * http://localhost:8090/vucem/get/enviar-edocument?id=4
     * D:\Tmp\ed_56c32bc6552b32c1f0adf8fa65524937\OUTPUT.pdf
     * 
     * @throws Zend_Exception
     * @throws Exception
     */
    public function enviarEdocumentAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $mapper = new Vucem_Model_VucemTmpEdocsMapper();
                $arr = $mapper->obtenerArchivo($input->id);
                $mdl = new Vucem_Model_VucemFirmanteMapper();
                $sello = $mdl->obtenerDetalleFirmante($arr["firmante"]);
                $xml = new OAQ_Xml(false, true);
                $filename = pathinfo($arr["nomArchivo"]);
                $archivo = array(
                    "idTipoDocumento" => $arr["tipoArchivo"],
                    "nomArchivo" => basename($arr["nomArchivo"]),
                    "archivo" => base64_encode(file_get_contents($arr["nomArchivo"])),
                    "hash" => sha1_file($arr["nomArchivo"]),
                );
                $conv = new OAQ_Conversion();
                $data = $conv->crearEdocument("jvaldezch@gmail.com", $sello, $archivo, "OAQ030623UL8");
                $xml->xmlEdocument($data);

                file_put_contents($filename["dirname"] . DIRECTORY_SEPARATOR . $arr["hash"] . ".xml", $xml->getXml());

                $serv = new OAQ_Servicios();
                $serv->setXml($xml->getXml());
                $serv->consumirServicioEdocument();

                $res = new OAQ_Respuestas();
                $resp = $res->analizarRespuesta($serv->getResponse());
            } else {
                throw new Exception("Invalid parameters!");
            }
        } catch (Exception $ex) {
            throw new Zend_Exception($ex->getMessage());
        }
    }

    public function prepararEdocumentAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $mppr = new OAQ_Archivos_Procesar();
                $mppr->procesarEdocument($input->id);
            } else {
                throw new Exception("Invalid parameters!");
            }
        } catch (Exception $ex) {
            throw new Zend_Exception($ex->getMessage());
        }
    }

    public function mostrarFacturasAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "pedimento" => array("Digits"),
                "sistema" => "StringToLower",
            );
            $v = array(
                "pedimento" => array("NotEmpty", new Zend_Validate_Int()),
                "sistema" => "NotEmpty",
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("sistema") && $input->isValid("pedimento")) {
                $this->_svucem = $this->_svucem = new Zend_Session_Namespace("OAQVucem");
                $this->_svucem->setExpirationSeconds($this->_appconfig->getParam('session-exp'));
                if (!isset($this->_svucem->solicitante) || !isset($this->_svucem->patente) || !isset($this->_svucem->aduana)) {
                    $this->view->warning = "No ha seleccionado una firma.";
                    $error = true;
                }
                $misc = new OAQ_Misc();
                $sita = $misc->sitawinTrafico($this->_svucem->patente, $this->_svucem->aduana);
                if (isset($sita)) {
                    $arr = $sita->buscarPedimento($input->pedimento);
                    if (!$arr) {
                        $this->view->warning = "El número de pedimento no existe.";
                        $error = true;
                    } else {
                        if ((int) $arr["tipoOperacion"] == 1) {
                            if (isset($arr["consolidado"]) && $arr["consolidado"] == true) {
                                $facturas = $sita->mostrarFacturasImportacion($arr["referencia"], $input->pedimento, true);
                            } else {
                                $facturas = $sita->mostrarFacturasImportacion($arr["referencia"], $input->pedimento);
                            }
                        } else if ((int) $arr["tipoOperacion"] == 2) {
                            if (isset($arr["consolidado"]) && $arr["consolidado"] == true) {
                                $facturas = $sita->mostrarFacturasExportacion($arr["referencia"], $input->pedimento, true);
                            } else {
                                $facturas = $sita->mostrarFacturasExportacion($arr["referencia"], $input->pedimento);
                            }
                        }
                    }
                    if (!isset($error) && isset($facturas)) {
                        $view = new Zend_View();
                        $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                        $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");
                        $view->pedimento = $arr["pedimento"];
                        $view->patente = $arr["patente"];
                        $view->aduana = $arr["aduana"];
                        $view->referencia = $arr["referencia"];
                        $view->cvePedimento = $arr["cvePedimento"];
                        $view->regimen = $arr["regimen"];
                        $view->consolidado = $arr["consolidado"];
                        $view->rectificacion = $arr["rectificacion"];
                        $view->firmaValidacion = isset($arr["firmaValidacion"]) ? $arr["firmaValidacion"] : "";
                        $view->firmaBanco = isset($arr["firmaBanco"]) ? $arr["firmaValidacion"] : "";
                        $view->operacion = isset($arr["operacion"]) ? $arr["operacion"] : "";
                        $view->tipoOperacion = ((int) $arr["tipoOperacion"] == 1) ? 'TOCE.IMP' : 'TOCE.EXP';
                        $view->facturas = $facturas;
                        echo $view->render("mostrar-facturas.phtml");
                    }
                } else {
                    throw new Exception("No base de datos!");
                }
            } else {
                throw new Exception("Invalid parameters!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function seleccionarFacturasAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "sistema" => "StringToLower",
                "pedimento" => array("Digits"),
                "tipoOperacion" => "StringToUpper",
            );
            $v = array(
                "sistema" => "NotEmpty",
                "pedimento" => array("NotEmpty", new Zend_Validate_Int()),
                "facturas" => "NotEmpty",
                "tipoOperacion" => "NotEmpty",
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("sistema") && $input->isValid("pedimento") && $input->isValid("tipoOperacion") && $input->isValid("facturas")) {
                $this->_svucem = $this->_svucem = new Zend_Session_Namespace("OAQVucem");
                $this->_svucem->setExpirationSeconds($this->_appconfig->getParam('session-exp'));
                $misc = new OAQ_Misc();
                $sita = $misc->sitawinTrafico($this->_svucem->patente, $this->_svucem->aduana);
                $customers = new Vucem_Model_VucemClientesMapper();
                $mpprFact = new Vucem_Model_VucemTmpFacturasMapper();
                $mpprProd = new Vucem_Model_VucemTmpProductosMapper();
                if (isset($sita)) {
                    $facturas = explode("|", $input->facturas);
                    if (!empty($facturas)) {
                        $arr = $sita->buscarPedimento($input->pedimento);
                        foreach ($facturas as $item) {
                            $numFactura = html_entity_decode($item);
                            if ($input->tipoOperacion == "TOCE.IMP") {
                                if (isset($arr["consolidado"]) && $arr["consolidado"] == true) {
                                    $factura = $sita->seleccionarFacturaImportacion($arr["referencia"], $input->pedimento, $numFactura, $arr["tipoCambio"], true);
                                } else {
                                    $factura = $sita->seleccionarFacturaImportacion($arr["referencia"], $input->pedimento, $numFactura, $arr["tipoCambio"]);
                                }
                            } else if ($input->tipoOperacion == "TOCE.EXP") {
                                if (isset($arr["consolidado"]) && $arr["consolidado"] == true) {
                                    $factura = $sita->seleccionarFacturaExportacion($arr["referencia"], $input->pedimento, $numFactura, $arr["tipoCambio"], true);
                                } else {
                                    $factura = $sita->seleccionarFacturaExportacion($arr["referencia"], $input->pedimento, $numFactura, $arr["tipoCambio"]);
                                }
                            }
                            if (isset($factura) && !empty($factura)) {
                                $factura["IdFact"] = $misc->getUuid($factura["Patente"] . $factura["Pedimento"] . $factura["Aduana"] . $factura["NumFactura"] . $factura["OrdenFact"] . time());
                                $factura["TipoOperacion"] = $input->tipoOperacion;
                                if (isset($factura["CteRfc"])) {
                                    $cliente = $customers->datosCliente($factura["CteRfc"]);
                                    if (isset($cliente) && !empty($cliente)) {
                                        $factura["CteNombre"] = $cliente["razon_soc"];
                                        $factura["CteCalle"] = $cliente["calle"];
                                        $factura["CteNumExt"] = $cliente["numext"];
                                        $factura["CteNumInt"] = $cliente["numint"];
                                        $factura["CteColonia"] = $cliente["colonia"];
                                        $factura["CteLocalidad"] = $cliente["localidad"];
                                        $factura["CteCP"] = $cliente["cp"];
                                        $factura["CteMun"] = $cliente["municipio"];
                                        $factura["CteEdo"] = $cliente["estado"];
                                        $factura["CtePais"] = $cliente["pais"];
                                    }
                                }
                                $factura["IdFact"] = $misc->getUuid($factura["Patente"] . $factura["Pedimento"] . $factura["Aduana"] . $factura["NumFactura"] . $factura["OrdenFact"] . time());
                                if (isset($arr["consolidado"]) && $arr["consolidado"] == true) {
                                    $factura["Consolidado"] = 1;
                                }
                                $idTmp = $mpprFact->nuevaFactura($this->_svucem->solicitante, $this->_svucem->tipoFigura, $this->_svucem->patente, $this->_svucem->aduana, $factura, $this->_session->username, 0);
                                if ($idTmp) {
                                    foreach ($factura["Productos"] as $k => $producto) {
                                        $producto["ID_PROD"] = $misc->getUuid($factura["Pedimento"] . $factura["NumFactura"] . $producto["PARTE"] . $producto["CODIGO"] . md5(time()) . $k);
                                        $mpprProd->nuevoProducto($idTmp, $factura["IdFact"], $this->_svucem->patente, $this->_svucem->aduana, $factura["Pedimento"], $factura["Referencia"], $producto, $this->_session->username);
                                    }
                                }
                            }
                        }
                        $this->_helper->json(array("success" => true));
                    } else {
                        throw new Exception("No hay facturas.");
                    }
                } else {
                    throw new Exception("No hay sistema de pedimentos.");
                }
            } else {
                throw new Exception("Parametros no válidos.");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function checarSubtiposAction() {
        try {
            $f = array(
                "idDocumento" => array("Digits", "StringTrim", "StripTags"),
            );
            $v = array(
                "idDocumento" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idDocumento")) {
                $mppr = new Archivo_Model_DocumentosSubtipos();
                $arr = $mppr->verificar($input->idDocumento);
                if (!empty($arr)) {
                    $this->_helper->json(array("success" => true, "result" => $arr));
                } else {
                    $this->_helper->json(array("success" => false));
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function verEdocumentAction() {
        try {
            $f = array(
                "id" => array("Digits", "StringTrim", "StripTags"),
                "view" => "StringToLower",
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
                "view" => array("NotEmpty", new Zend_Validate_InArray(array(true, false)))
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                if (boolval($input->view) === true) {
                    $mppr = new Vucem_Model_VucemEdocMapper();
                    $arr = $mppr->obtenerArchivoEdocument($input->id);
                    if (!empty($arr)) {
                        header('Content-type: application/octet-stream');
                        header('Content-disposition: attachment;filename="' . $arr["nomArchivo"] . '"');
                        header('Cache-Control: public, must-revalidate, max-age=0');
                        header('Pragma: public');
                        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
                        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
                        echo base64_decode($arr["archivo"]);
                    }
                } else {
                    $view = new Zend_View();
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                    $view->id = $input->id;
                    echo $view->render("ver-edocument.phtml");
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function verArchivoAction() {
        try {
            $f = array(
                "id" => array("Digits", "StringTrim", "StripTags"),
                "view" => "StringToLower",
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
                "view" => array("NotEmpty", new Zend_Validate_InArray(array(true, false)))
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $mppr = new Vucem_Model_VucemTmpEdocsMapper();
                $usuario = null;
                if (!in_array($this->_session->role, array("super", "trafico_operaciones", "gerente"))) {
                    $usuario = $this->_session->username;
                }
                $arr = $mppr->obtenerArchivo($input->id, $usuario);
                if (!empty($arr)) {
                    if (file_exists($arr["nomArchivo"])) {
                        if (boolval($input->view) === true) {
                            header('Content-type: application/octet-stream');
                            header('Content-Length: ' . filesize($arr["nomArchivo"]));
                            header('Content-disposition: attachment;filename="' . basename($arr["nomArchivo"]) . '"');
                            header('Cache-Control: public, must-revalidate, max-age=0');
                            header('Pragma: public');
                            header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
                            header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
                            readfile($arr["nomArchivo"]);
                        } else {
                            $view = new Zend_View();
                            $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                            $view->id = $input->id;
                            echo $view->render("ver-archivo.phtml");
                        }                        
                    } else {
                        throw new Exception("Archivo no existe.");
                    }
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function analizarArchivoAction() {
        try {
            $f = array(
                "id" => array("Digits", "StringTrim", "StripTags"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                //$process = new OAQ_Archivos_Procesar();
                //$res = $process->analizarArchivo($input->id);
                //Zend_Debug::dump($res);                
                $sender = new OAQ_Workers_EdocSender();
                $sender->edocs($input->id);
                var_dump($input->id);
                
                
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
    public function subirPlantillaAction() {
        try {
            $view = new Zend_View();
            $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
            $this->_helper->json(array("success" => true, "html" => $view->render("subir-plantilla.phtml")));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    /**
     * /vucem/get/transformar-edocument?edocument=04381708IBCG8&idFiel=121
     * 
     */
    public function transformarEdocumentAction() {
        try {
            $f = array(
                "edocument" => array("StringTrim", "StripTags", "StringToUpper"),
                "idFiel" => array("StringTrim", "StripTags", "Digits"),
            );
            $v = array(
                "edocument" => array("NotEmpty"),
                "idFiel" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("edocument") && $input->isValid("idFiel")) {
                if (APPLICATION_ENV == "production") {
                    
                } else {
                    $directory = "D:\\Tmp\\OAQ\\edocs";
                }
                $vucem = new OAQ_VucemEnh();
                $filename = $directory . DIRECTORY_SEPARATOR . $input->edocument . "_DOCTO.xml";
                if (file_exists($directory . DIRECTORY_SEPARATOR . $input->edocument . "_DOCTO.xml")) {
                    /// CREAR EL PDF ORIGINAL
                    $xml = file_get_contents($filename);
                    $arr = $vucem->xmlStrToArray($xml);
                    if (isset($arr["s:Body"]["DocumentoOut"])) {
                        $res = $arr["s:Body"]["DocumentoOut"];
                        if (isset($res["CadenaOriginal"])) {
                            $cadena = explode("|", $res["CadenaOriginal"]);
                            if (isset($cadena[4])) {
                                $fileout = $directory . DIRECTORY_SEPARATOR . "EDOC_" . $input->edocument . "_" . $cadena[3] . "_" . $cadena[4];
                                file_put_contents($fileout, base64_decode($res["File"]));
                            }
                        }
                    }
                    if (isset($res["SelloDigital"])) {
                        $selloDigital = $res["SelloDigital"];
                    }                    
                    /// CREAR EL XML
                    $mapper = new Vucem_Model_VucemFirmanteMapper();
                    $conv = new OAQ_Conversion();                    
                    $sello = $mapper->obtenerDetalleFirmanteId($input->idFiel);
                    $xmlEdoc = new OAQ_Xml(false, true);
                    $hash = sha1_file($fileout);
                    $archivo = array(
                        "idTipoDocumento" => $cadena[3],
                        "nomArchivo" => $cadena[4],
                        "archivo" => $res["File"],
                        "hash" => $hash,
                    );
                    $data = $conv->crearEdocument("soporte@oaq.com.mx", $sello, $archivo, $sello["rfc"]);
                    $xmlEdoc->imitarEdocument($data, false, $selloDigital);
                    file_put_contents($directory . DIRECTORY_SEPARATOR . "EDOC_" . $input->edocument . ".xml", $xmlEdoc->getXml());
                    
                    /// CREAR EL NUEVO ACUSE
                    require "tcpdf/acuseedocvu.php";
                    $arrPdf = array(
                        "numTramite" => null,
                        "actualizado" => date("Y-m-d H:i:s"),
                        "edoc" => $input->edocument,
                        "tipoDoc" => $cadena[3],
                        "nomArchivo" => $cadena[4],
                        "rfcConsulta" => $sello["rfc"],
                        "cadena" => "|{$sello["rfc"]}|soporte@oaq.com.mx|{$cadena[3]}|{$cadena[4]}|{$sello["rfc"]}|{$hash}|",
                        "firma" => $selloDigital,
                    );
                    $arrPdf["titulo"] = "EDOC_" . $input->edocument . ".pdf";
                    $print = new EdocumentVU($arrPdf, "P", "pt", "LETTER");
                    $print->Create();
                    $print->Output($arrPdf["titulo"] . ".pdf", "I");
                    
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function otroSistemaAction() {
        try {
            $view = new Zend_View();
            $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
            $this->_helper->json(array("success" => true, "html" => $view->render("otro-sistema.phtml")));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function facturaAction() {
        $sis = new Sistemas_Casa();
        $row = $sis->factura(3589, 640, 8003746, '263468');
        
        Zend_Debug::Dump($row);
    }

    public function verFacturaAction() {
        try {
            $f = array(
                "uuid" => array("StringTrim", "StripTags", "StringToLower"),
            );
            $v = array(
                "uuid" => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("uuid")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");

                $tmpFact = new Vucem_Model_VucemTmpFacturasMapper();
                if ($tmpFact->verify($input->uuid, $this->_session->username)) {
                    $factura = $tmpFact->obtenerFactura($input->uuid, $this->_session->username);
                    if ($factura["TipoOperacion"] == "TOCE.EXP") {
                        if (!isset($factura["ProIden"])) {
                            $factura["ProIden"] = $vucem->tipoIdentificador($factura["CteRfc"], $factura["CtePais"]);                        
                        }
                        if (!isset($factura["CteIden"])) {
                            $factura["CteIden"] = $vucem->tipoIdentificador($factura["ProTaxID"], $factura["ProPais"]);                        
                        }
                    } else {
                        if (!isset($factura["CteIden"])) {
                            $factura["CteIden"] = $vucem->tipoIdentificador($factura["CteRfc"], $factura["CtePais"]);
                        }
                        if (!isset($factura["ProIden"])) {
                            $factura["ProIden"] = $vucem->tipoIdentificador($factura["ProTaxID"], $factura["ProPais"]);                        
                        }
                    }
                    $factura["FechaFactura"] = date('Y-m-d', strtotime($factura["FechaFactura"]));
                    unset($factura["id"]);
                    unset($factura["figura"]);
                    unset($factura["adenda"]);
                    unset($factura["Creado"]);
                    unset($factura["Modificado"]);
                    unset($factura["Usuario"]);
                    unset($factura["Active"]);
                    unset($factura["OrdenFact"]);
                    unset($factura["OrdenFactCon"]);
                    unset($factura["ValDls"]);
                    unset($factura["ValExt"]);
                    unset($factura["RelFact"]);
                    unset($factura["CveImp"]);
                    unset($factura["CvePro"]);
                    //$this->_svucem->uuidFactura = $factura["IdFact"];
                    $view->uuidFactura = $factura["IdFact"];
                }
                $form = new Vucem_Form_NuevaFactura();
                $form->populate(array(
                    "firmante" => isset($this->_svucem->solicitante) ? $this->_svucem->solicitante : null,
                    "TipoOperacion" => isset($this->_svucem->tipoOperacion) ? $this->_svucem->tipoOperacion : null,
                    "Patente" => isset($this->_svucem->patente) ? $this->_svucem->patente : null,
                    "Aduana" => isset($this->_svucem->aduana) ? $this->_svucem->aduana : null,
                    "FactFacAju" => $factura["FactorEquivalencia"],
                ));
                $misc = new OAQ_Misc();
                if (isset($factura)) {            
                    $form->populate($factura); // ???????????????????????????????????????????????????
                } elseif (isset($this->_svucem->solicitante) && !isset($factura)) {
                    $firmante = new Vucem_Model_VucemClientesMapper();
                    $cliente = $firmante->datosCliente($this->_svucem->solicitante);
                    if (isset($cliente) && !empty($cliente)) {
                        if ($this->_svucem->tipoOperacion == "TOCE.EXP") {
                            $form->populate($misc->datosFacturaProveedor($cliente));
                        } elseif ($this->_svucem->tipoOperacion == "TOCE.IMP") {
                            $form->populate($misc->datosFacturaCliente($cliente));
                        }
                    }
                }
                if (isset($this->_svucem->uuidFactura) && $this->_svucem->uuidFactura != '') {
                    $tmpFact = new Vucem_Model_VucemTmpFacturasMapper();
                    $factura = $tmpFact->obtenerFactura($input->uuid, $this->_session->username);
                    if ($factura) {
                        $factura["FechaFactura"] = date("Y-m-d", strtotime($factura["FechaFactura"]));
                        if ($factura["TipoOperacion"] == "TOCE.EXP" && ($factura["Manual"] == "0" || $factura["Manual"] == null)) {
                            $form->populate($misc->populateArrayExpo($factura));
                        } elseif($factura["TipoOperacion"] == "TOCE.IMP") {
                            $form->populate($misc->populateArrayImpo($factura));
                        }
                    } else {
                        unset($this->_svucem->uuidFactura);
                    }
                }
                $view->form = $form;
                $formprod = new Vucem_Form_AddNewProduct();
                $view->formprod = $formprod;

                $tmpProd = new Vucem_Model_VucemTmpProductosMapper();
                $products = $tmpProd->obtenerProductos($input->uuid, $this->_session->username);
                $view->products = $products;
                $this->_helper->json(array("success" => true, "html" => $view->render("ver-factura.phtml")));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

}
