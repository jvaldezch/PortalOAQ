<?php

class Automatizacion_FacturacionController extends Zend_Controller_Action {

    protected $_config;
    protected $_logger;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_logger = Zend_Registry::get("logDb");
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
    }

    /**
     * /automatizacion/facturacion/facturacion?fechaIni=2017-11-01&fechaFin=2017-11-15
     * 
     * @throws Exception
     */
    public function facturacionAction() {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $f = array(
                "*" => array("StringTrim", "StripTags"),
            );
            $v = array(
                "fechaIni" => new Zend_Validate_Date(),
                "fechaFin" => new Zend_Validate_Date(),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("fechaIni") && $input->isValid("fechaFin")) {
                $mppr = new Administracion_Model_AdmonFacturacion();
                $sica = new OAQ_SicaDb("192.168.200.5", "sa", "adminOAQ123", "SICA", 1433, "SqlSrv");
                $rows = $sica->facturacionRango($input->fechaIni, $input->fechaFin);
                if (!empty($rows)) {
                    foreach ($rows as $item) {
                        if (!($mppr->verificar($item))) {
                            $mppr->agregar($item);
                        }
                    }
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
    /**
     * http://localhost:8090/automatizacion/facturacion/rpt-cuentas
     * https://oaq.dnsalias.net/automatizacion/facturacion/rpt-cuentas?fecha=2018-12-06
     * 
     */
    public function rptCuentasAction() {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            
            if (APPLICATION_ENV == 'production') {
                $uri = 'http://192.168.200.5:3002';
            } else {
                $uri = 'http://localhost:3002';
            }
            
            $f = array(
                "*" => array("StringTrim", "StripTags"),
            );
            $v = array(
                "fecha" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/"), "default" => date("Y-m-d")),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            
            $client = new Zend_Rest_Client($uri);
            $httpClient = $client->getHttpClient();
            $httpClient->setConfig(array('timeout' => 30));
            $client->setHttpClient($httpClient);
            
            $response = $client->restPost("/sica/folios", array(
                'fecha' => $input->fecha,
            ));
            
            if (($body = $response->getBody())) {
                
                $rows = json_decode($body, true);
                
                if (isset($rows["success"]) && $rows["success"] == true) {
                    
                    $mppr  = new Automatizacion_Model_RptCuentas();
                    
                    foreach ($rows["results"] as $item) {
                        
                       if (!($mppr->verificar($item["idSucursal"], $item["folio"], $item["patente"], $item["aduana"], $item["pedimento"], $item["referencia"]))) {
                           $arr = array(
                               "idSucursal" => $item["idSucursal"],
                               "folio" => $item["folio"],
                               "patente" => $item["patente"],
                               "aduana" => $item["aduana"],
                               "pedimento" => $item["pedimento"],
                               "referencia" => $item["referencia"],
                                "nomSucursal" => $item["nomSucursal"],
                                "rfcSucursal" => $item["rfcSucursal"],
                                "referencia" => $item["referencia"],
                                "idCliente" => $item["idCliente"],
                                "rfcCliente" => $item["rfcCliente"],
                                "nomCliente" => $item["nomCliente"],
                                "idProveedor" => $item["idProveedor"],
                                "rfcProveedor" => $item["rfcProveedor"],
                                "nomProveedor" => $item["nomProveedor"],
                                "referenciaFactura" => $item["referenciaFactura"],
                                "tipoOperacion" => $item["tipoOperacion"],
                                "fechaFacturacion" => $input->fecha,
                                "fechaPago" => $item["fechaPago"],
                                "valorFactura" => $item["valorFactura"],
                                "peso" => $item["peso"],
                                "contenido" => $item["contenido"],
                                "bultos" => $item["bultos"],
                                "anticipo" => $item["anticipo"],
                                "subTotal" => $item["subTotal"],
                                "total" => $item["total"],
                                "phonorarios" => $item["phonorarios"],
                                "observaciones" => $item["observaciones"],
                                "honorarios" => $item["honorarios"],
                                "honorarioFijo" => $item["honorarioFijo"],
                                "caja" => $item["caja"],
                                "tipoCambio" => $item["tipoCambio"],
                                "fechaModificacion" => $item["fechaModificacion"],
                                "impuestosGarantizados" => $item["impuestosGarantizados"],
                                "usuario" => $item["usuario"],
                                "porcentajeIva" => $item["porcentajeIva"],
                                "iva" => $item["iva"],
                                "polizaCancelacion" => $item["polizaCancelacion"],
                                "banco" => $item["banco"],
                                "formaPago" => $item["formaPago"],
                                "creado" => date('Y-m-d H:i:s'),
                           );
                           $mppr->agregar($arr);
                        }
                        
                    }
                }
                $this->_helper->json(array("success" => true, "quantity" => $rows["quantity"]));
            } else {
                $this->_helper->json(array("success" => false, "message" => 'Nothing to process'));
            }
            
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
    public function rptConceptosAction() {
        try {
            
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
                "limit" => array("Digits"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
                "limit" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            
            $mapper = new Automatizacion_Model_RptCuentaConceptos();
            $mppr  = new Automatizacion_Model_RptCuentas();
            $arr = $mppr->sinAnalizar($input->id, $input->limit);
            
            if (!empty($arr)) {
                
                if (APPLICATION_ENV == 'production') {
                    $uri = 'http://192.168.200.5:3002';
                } else {
                    ini_set('max_execution_time', 300);
                    $uri = 'http://localhost:3002';
                }

                $client = new Zend_Rest_Client($uri);
                $httpClient = $client->getHttpClient();
                $httpClient->setConfig(array('timeout' => 30));
                $httpClient->setAdapter('Zend_Http_Client_Adapter_Curl');
                $client->setHttpClient($httpClient);
                
                foreach ($arr as $item) {
                    
                    $response = $client->restPost("/sica/conceptos", array(
                        'folio' => $item["folio"],
                    ));
                    
                    if (($body = $response->getBody())) {                
                        $rows = json_decode($body, true);
                        if (isset($rows["success"]) && $rows["success"] == true) {
                            
                            $results = $rows["results"];
                            if  (!empty($results)) {                                
                                foreach ($results as $i) {
                                    
                                    if (!($mapper->verificar($i["idSucursal"], $item['id'], $i["idConcepto"]))) {
                                        $array = array(
                                            "idSucursal" => $i["idSucursal"],
                                            "idCuenta" => $item["id"],
                                            "idConcepto" => $i["idConcepto"],
                                            "nomConcepto" => $i["nomConcepto"],
                                            "tipo" => $i["tipo"],
                                            "reglon" => $i["reglon"],
                                            "descripcion" => $i["descripcion"],
                                            "importe" => $i["importe"],
                                            "cantidad" => $i["cantidad"],
                                            "divisa" => $i["divisa"],
                                            "valorDolares" => $i["valorDolares"],
                                            "precioUnitario" => $i["precioUnitario"],
                                            "iva0" => $i["iva0"],
                                            "descuento" => $i["descuento"],
                                            "iva" => $i["iva"],
                                            "retencionIva" => $i["retencionIva"],
                                            "idUnidadNegocio" => $i["idUnidadNegocio"],
                                            "creado" => date('Y-m-d H:i:s'), 
                                        );
                                        $mapper->agregar($array);
                                    }
                                }
                                $mppr->actualizar($item["id"], array("analizado" => 1, "conceptos" => 1));
                            } else {
                                $mppr->actualizar($item["id"], array("analizado" => 1, "noConceptos" => 1));
                            }
                        } else {
                            $mppr->actualizar($item["id"], array("analizado" => 1));
                        }
                    }
                    
                }
                $this->_helper->json(array("success" => true, "results" => $arr));
            } else {
                $this->_helper->json(array("success" => false, "message" => 'Nothing to process'));
            }
            
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
    public function rptSyncAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "limit" => array("Digits"),
            );
            $v = array(
                "limit" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            
            $trafico = new OAQ_Trafico();
            
            $mppr  = new Automatizacion_Model_RptCuentas();
            $arr = $mppr->sinTrafico($input->limit);
            
            $results = array();
            $notfound = 0;
            
            if (!(empty($arr))) {
                foreach ($arr as $item) {
                    
                    $referencia = $trafico->removerSufijos($item["referencia"]);
                    
                    $trafico->setPatente($item["patente"]);
                    $trafico->setAduana($item["aduana"]);
                    $trafico->setReferencia($referencia);
                    
                    if (($id = $trafico->buscarTrafico())) {
                        if ($id) {
                            $mppr->actualizar($item["id"], array("idTrafico" => $id));
                            $results[] = array(
                                "id" => $item['id'],
                                "folio" => $item['folio'],
                                "idTrafico" => $id,
                                "patente" => $item['patente'],
                                "aduana" => $item['folio'],
                                "referencia" => $referencia,
                            );
                        }                        
                    } else {
                        $notfound++;
                    }
                }
            }
            $this->_helper->json(array("success" => true, "quantity" => count($results), "results" => $results, "notFound" => $notfound));
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    /**
     * http://localhost:8090/automatizacion/facturacion/rpt-status?limit=1&fecha=2018-11-01
     * /automatizacion/facturacion/rpt-status?limit=3400&fecha=2018-11-01
     * 
     */
    public function rptStatusAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "limit" => array("Digits"),
                "folio" => array("Digits"),
            );
            $v = array(
                "limit" => array("NotEmpty", new Zend_Validate_Int()),
                "folio" => array("NotEmpty", new Zend_Validate_Int()),
                "fecha" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
            );
            
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            
            $debug = filter_var($this->getRequest()->getParam("debug", null), FILTER_VALIDATE_BOOLEAN);

            $mppr = new Automatizacion_Model_RptCuentas();
            if (!$input->isValid("folio")) {
                $arr = $mppr->sinPagar($input->limit, $input->fecha);
            } else {
                $arr = $mppr->folio($input->folio);
                if ($debug) {
                    Zend_Debug::dump($arr);
                    die();
                }
            }

            $results = array();

            if (!empty($arr)) {

                if (APPLICATION_ENV == 'production') {
                    $uri = 'http://192.168.200.5:3002';
                } else {
                    ini_set('max_execution_time', 300);
                    $uri = 'http://192.168.200.5:3002';
                }

                $client = new Zend_Rest_Client($uri);
                $httpClient = $client->getHttpClient();
                $httpClient->setConfig(array('timeout' => 120));
                $httpClient->setAdapter('Zend_Http_Client_Adapter_Curl');
                $client->setHttpClient($httpClient);

                foreach ($arr as $item) {

                    $response = $client->restPost("/sica/estatus", array(
                        'folio' => $item["folio"],
                        'referencia' => $item["referencia"],
                    ));

                    if (($body = $response->getBody())) {
                        $rows = json_decode($body, true);
                        if (isset($rows["success"]) && $rows["success"] == true) {

                            if  (!empty($rows["results"])) { 
                                $abono = 0;
                                foreach ($rows["results"] as $a) {
                                    $abono = $abono + $a['abono'];
                                }                          

                                if ((float) $item['total'] == (float) $abono) {
                                    $res['folio'] = $item["folio"];
                                    $results[] = $res;
                                    $mppr->actualizar($item["id"], array("pagada" => 1, "analizado" => 1));
                                    continue;
                                }

                                if ($res['estatus'] == "C") {
                                    $mppr->actualizar($item["id"], array("cancelada" => 1, "analizado" => 1));
                                    continue;
                                }
                            }
                            
                        }
                        
                        $responsem = $client->restPost("/sica/movimientos", array(
                            'folio' => $item["folio"],
                            'referencia' => $item["referencia"],
                        ));
                        
                        if (($bodym = $responsem->getBody())) {
                            $rowsm = json_decode($bodym, true);
                            if (isset($rowsm["success"]) && $rowsm["success"] == true) {
                                
                                if  (!empty($rowsm["results"])) { 
                                    $abono = 0;
                                    foreach ($rowsm["results"] as $a) {
                                        $abono = $abono + $a['abono'];
                                    }

                                    if ((float) $item['total'] == (float) $abono) {
                                        $resm['folio'] = $item["folio"];
                                        $results[] = $resm;
                                        $mppr->actualizar($item["id"], array("pagada" => 1, "analizado" => 1));
                                        continue;
                                    }

                                    if ($resm['estatus'] == "C") {
                                        $mppr->actualizar($item["id"], array("cancelada" => 1, "analizado" => 1));
                                        continue;
                                    }
                                }
                                
                            }
                        }
                        
                        $mppr->actualizar($item["id"], array("analizado" => 1));
                        
                    }
                }
            }
            $this->_helper->json(array("success" => true, "quantity" => count($results), "results" => $results));
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

}
