<?php

class WebService_TraficoController extends Zend_Controller_Action {

    protected $_config;
    protected $_salt = "dss78454";
    protected $_pepper = "oaq2013*";

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
    }

    /**
     * /webservice/trafico/pedimentos-pagados
     * /webservice/trafico/pedimentos-pagados?rfc=INM060424AP3&patente=3589&aduana=640&token=ac1cf59a5caec5f59d69c2581a6c005ff6679109&fechaIni=2019-01-01&fechaFin=2019-02-01
     * 
     */
    public function pedimentosPagadosAction() {
        $domtree = new DOMDocument("1.0", "UTF-8");
        $domtree->preserveWhiteSpace = false;
        $domtree->formatOutput = true;
        try {
            $f = array(
                "patente" => array("StringTrim", "StripTags", "Digits"),
                "aduana" => array("StringTrim", "StripTags", "Digits"),
                "rfc" => array("StringTrim", "StripTags", "StringToUpper"),
                "token" => array("StringTrim", "StripTags", "StringToLower"),
                "fechaIni" => array("StringTrim", "StripTags"),
                "fechaFin" => array("StringTrim", "StripTags"),
            );
            $v = array(
                "patente" => new Zend_Validate_Int(),
                "aduana" => new Zend_Validate_Int(),
                "rfc" => array(new Zend_Validate_Regex("/^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/")),
                "token" => array(new Zend_Validate_Regex("/^[a-z0-9]+$/")),
                "fechaIni" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
                "fechaFin" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("patente") && $input->isValid("aduana") && $input->isValid("token") && $input->isValid("rfc")) {

                $table = new Webservice_Model_WsTokens();
                if (($r = $table->search($input->token))) {
                    if ($r['rfc'] !== $input->rfc) {
                        throw new Exception("RFC no válido.");
                    }
                } else {
                    throw new Exception("Token no válido.");
                }

                $mppr = new Trafico_Model_TraficoAduanasMapper();
                $idAduana = $mppr->idAduana($input->patente, $input->aduana);
                if ($idAduana) {
                    $reportes = new OAQ_Reportes();
                    $rows = $reportes->obtenerDatos("encabezado", $idAduana, $input->rfc, $input->fechaIni, $input->fechaFin);
                    $operaciones = $domtree->createElement("operaciones");
                    $xmlRoot = $domtree->appendChild($operaciones);

                    if (!empty($rows)) {
                        $operaciones->setAttribute("cantidad", count($rows));
                        foreach ($rows as $item) {
                            $operacion = $domtree->createElement("pedimento");
                            $operacion->setAttribute("patente", $item["patente"]);
                            $operacion->setAttribute("pedimento", $item["pedimento"]);
                            $operacion->setAttribute("referencia", $item["trafico"]);
                            $operacion->setAttribute("aduana", $item["aduana"]);
                            $operacion->setAttribute("tipoOperacion", $item["tipoOperacion"]);
                            $operacion->setAttribute("year", date("Y", strtotime($item["fechaPago"])));
                            $operacion->setAttribute("fechaPago", date("Y-m-d", strtotime($item["fechaPago"])));
                            if (APPLICATION_ENV == "production") {
                                $url = "https://oaq.dnsalias.net/webservice/trafico/detalle-pedimento?rfc={$input->rfc}&aduana=" . $item["aduana"] . "&patente={$input->patente}&pedimento={$item["pedimento"]}&token=" . sha1($this->_salt . $item["pedimento"] . $this->_pepper);
                            } else {
                                $url = "http://localhost:8090/webservice/trafico/detalle-pedimento?rfc={$input->rfc}&aduana=" . $item["aduana"] . "&patente={$input->patente}&pedimento={$item["pedimento"]}&token=" . sha1($this->_salt . $item["pedimento"] . $this->_pepper);
                            }
                            $operacion->setAttribute("url", $url);
                            $operaciones->appendChild($operacion);
                        }
                    } else {
                        $operaciones->setAttribute("cantidad", 0);
                    }
                } else {
                    throw new Exception("Aduana no válida.");
                }
            } else {
                throw new Exception("Los datos proporcionados no son válidos.");
            }
            if (isset($domtree)) {
                Zend_Controller_Action_HelperBroker::getStaticHelper("viewRenderer")->setNoRender(true);
                Zend_Layout::getMvcInstance()->disableLayout();
                $this->_response->setHeader("Content-Type", "text/xml; charset=utf-8")
                        ->setBody($domtree->saveXML());
            }
        } catch (Exception $ex) {
            $root = $domtree->createElement("operaciones");
            $xmlRoot = $domtree->appendChild($root);
            $xmlRoot->appendChild($domtree->createElement("success", "false"));
            $xmlRoot->appendChild($domtree->createElement("message", $ex->getMessage()));
            Zend_Controller_Action_HelperBroker::getStaticHelper("viewRenderer")->setNoRender(true);
            Zend_Layout::getMvcInstance()->disableLayout();
            $this->_response->setHeader("Content-Type", "text/xml; charset=utf-8")
                    ->setBody($domtree->saveXML());
        }
    }

    public function pedimentosPagadosSociedadAction() {
        try {
            $domtree = new DOMDocument("1.0", "UTF-8");
            $domtree->preserveWhiteSpace = false;
            $domtree->formatOutput = true;
            $f = array(
                "patente" => array("StringTrim", "StripTags", "Digits"),
                "aduana" => array("StringTrim", "StripTags", "Digits"),
                "rfc" => array("StringTrim", "StripTags", "StringToUpper"),
                "token" => array("StringTrim", "StripTags", "StringToLower"),
                "fechaIni" => array("StringTrim", "StripTags"),
                "fechaFin" => array("StringTrim", "StripTags"),
            );
            $v = array(
                "patente" => new Zend_Validate_Int(),
                "aduana" => new Zend_Validate_Int(),
                "rfc" => array(new Zend_Validate_Regex("/^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/")),
                "token" => array(new Zend_Validate_Regex("/^[a-z0-9]+$/")),
                "fechaIni" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
                "fechaFin" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("patente") && $input->isValid("token") && $input->isValid("rfc")) {
                $row = new Webservice_Model_Table_WsTokens(array("rfc" => $input->rfc));
                $table = new Webservice_Model_WsTokens();
                $table->find($row);
                if (null === ($row->getId())) {
                    $root = $domtree->createElement("errores");
                    $xmlRoot = $domtree->appendChild($root);
                    $xmlRoot->appendChild($domtree->createElement("error", "RFC no en base de datos."));
                } else {
                    if ($row->getToken() !== $input->token) {
                        $root = $domtree->createElement("errores");
                        $xmlRoot = $domtree->appendChild($root);
                        $xmlRoot->appendChild($domtree->createElement("error", "Token no válido."));
                    } else {
                        $mapper = new Trafico_Model_TraficosMapper();
                        $arr = $mapper->pedimentosPagados($input->fechaIni, $input->fechaFin, $input->rfc);
                        if (isset($arr) && !empty($arr)) {
                            $operaciones = $domtree->createElement("operaciones");
                            $operaciones->setAttribute("cantidad", count($arr));
                            $domtree->appendChild($operaciones);
                            foreach ($arr as $item) {
                                $operacion = $domtree->createElement("pedimento");
                                $operacion->setAttribute("Patente", $item["patente"]);
                                $operacion->setAttribute("Pedimento", $item["pedimento"]);
                                $operacion->setAttribute("Referencia", $item["referencia"]);
                                $operacion->setAttribute("Aduana", $item["aduana"]);
                                $operacion->setAttribute("Cliente", $item["rfcCliente"]);
                                $operacion->setAttribute("TipoOpe", ($item["tipoMovimiento"] == "TOCE.IMP") ? 1 : 2);
                                $operacion->setAttribute("Year", date("Y", strtotime($item["fechaPago"])));
                                $operacion->setAttribute("FechaPago", date("Y-m-d", strtotime($item["fechaPago"])));
                                if (isset($item["rfcSociedad"])) {
                                    $operacion->setAttribute("RfcSociedad", $item["rfcSociedad"]);
                                }
                                if (APPLICATION_ENV == "production") {
                                    $url = "https://oaq.dnsalias.net/webservice/trafico/pedimento/rfc/{$input->rfc}/aduana/" . $item["aduana"] . "/patente/{$input->patente}/pedimento/{$item["pedimento"]}/token/" . sha1($this->_salt . $item["pedimento"] . $this->_pepper);
                                } else if (APPLICATION_ENV == "staging") {
                                    $url = "http://192.168.0.191/webservice/trafico/pedimento/rfc/{$input->rfc}/aduana/" . $item["aduana"] . "/patente/{$input->patente}/pedimento/{$item["pedimento"]}/token/" . sha1($this->_salt . $item["pedimento"] . $this->_pepper);
                                } else {
                                    $url = $this->_config->app->url . "/webservice/trafico/pedimento/rfc/{$input->rfc}/aduana/" . $item["aduana"] . "/patente/{$input->patente}/pedimento/{$item["pedimento"]}/token/" . sha1($this->_salt . $item["pedimento"] . $this->_pepper);
                                }
                                $operacion->setAttribute("Url", $url);
                                $operaciones->appendChild($operacion);
                            }
                            $operaciones->appendChild($operacion);
                        } else {
                            $root = $domtree->createElement("errores");
                            $xmlRoot = $domtree->appendChild($root);
                            $xmlRoot->appendChild($domtree->createElement("error", "No existen pedimentos en el periodo."));
                        }
                    }
                }
            } else {
                $root = $domtree->createElement("errores");
                $xmlRoot = $domtree->appendChild($root);
                $xmlRoot->appendChild($domtree->createElement("error", "Invalid Input!"));
            }
            if (isset($domtree)) {
                Zend_Controller_Action_HelperBroker::getStaticHelper("viewRenderer")->setNoRender(true);
                Zend_Layout::getMvcInstance()->disableLayout();
                $this->_response->setHeader("Content-Type", "text/xml; charset=utf-8")
                        ->setBody($domtree->saveXML());
            }
        } catch (Exception $ex) {
            throw new Zend_Exception($ex->getMessage());
        }
    }

    /**
     * /webservice/trafico/detalle-pedimento?rfc=INM060424AP3&aduana=640&patente=3589&pedimento=9001550&token=880fc1f846155dafc6240bdd7606c2ffd546c920
     * 
     */
    public function detallePedimentoAction() {
        $domtree = new DOMDocument("1.0", "UTF-8");
        $domtree->preserveWhiteSpace = false;
        $domtree->formatOutput = true;
        try {
            $f = array(
                "patente" => array("StringTrim", "StripTags", "Digits"),
                "aduana" => array("StringTrim", "StripTags", "Digits"),
                "pedimento" => array("StringTrim", "StripTags", "Digits"),
                "rfc" => array("StringTrim", "StripTags", "StringToUpper"),
                "token" => array("StringTrim", "StripTags", "StringToLower"),
            );
            $v = array(
                "patente" => new Zend_Validate_Int(),
                "aduana" => new Zend_Validate_Int(),
                "pedimento" => new Zend_Validate_Int(),
                "rfc" => array(new Zend_Validate_Regex("/^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/")),
                "token" => array(new Zend_Validate_Regex("/^[a-z0-9]+$/")),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("patente") && $input->isValid("aduana") && $input->isValid("rfc") && $input->isValid("pedimento")) {
                $mppr = new Trafico_Model_TraficoAduanasMapper();
                $idAduana = $mppr->idAduana($input->patente, $input->aduana);
                if ($idAduana) {
                    if (sha1($this->_salt . $input->pedimento . $this->_pepper) == $input->token) {
                        
                        $root = $domtree->createElement("pedimento");                        
                        
                        $reportes = new OAQ_Reportes();
                        $row = $reportes->obtenerDesglose($input->patente, $input->aduana, $input->pedimento);
                        if ($row) {
                            $root->appendChild($domtree->createElement("aduana", $row["aduana"]));
                            
                            $root->appendChild($domtree->createElement("patente", $row['patente']));
                            $root->appendChild($domtree->createElement("aduana", $row['aduana']));
                            $root->appendChild($domtree->createElement("pedimento", $row['pedimento']));
                            $root->appendChild($domtree->createElement("referencia", $row['referencia']));
                            $root->appendChild($domtree->createElement("tipoOperacion", ($row['tipoOperacion'] == 1) ? 'IMP' : 'EXP'));
                            $root->appendChild($domtree->createElement("transporteEntrada", $row['transporteEntrada']));
                            $root->appendChild($domtree->createElement("transporteArribo", $row['transporteArribo']));
                            $root->appendChild($domtree->createElement("transporteSalida", $row['transporteSalida']));
                            $root->appendChild($domtree->createElement("fechaEntrada", $row['fechaEntrada']));
                            $root->appendChild($domtree->createElement("fechaPago", $row['fechaPago']));
                            $root->appendChild($domtree->createElement("firmaValidacion", $row['firmaValidacion']));
                            $root->appendChild($domtree->createElement("firmaBanco", $row['firmaBanco']));
                            $root->appendChild($domtree->createElement("tipoCambio", $row['tipoCambio']));
                            $root->appendChild($domtree->createElement("cvePed", $row['cvePed']));
                            $root->appendChild($domtree->createElement("regimen", $row['regimen']));
                            $root->appendChild($domtree->createElement("aduanaEntrada", $row['aduanaEntrada']));
                            
                            $root->appendChild($domtree->createElement("valorDolares", $row['valorDolares']));
                            $root->appendChild($domtree->createElement("valorAduana", ($row['tipoOperacion'] == 1) ? $row['valorAduana'] : '0'));
                            $root->appendChild($domtree->createElement("valorComercial", $row['valorComercial']));
                            
                            $root->appendChild($domtree->createElement("fletes", round($row['fletes'])));
                            $root->appendChild($domtree->createElement("seguros", round($row['seguros'])));
                            $root->appendChild($domtree->createElement("embalajes", round($row['embalajes'])));
                            $root->appendChild($domtree->createElement("otrosIncrementales", round($row['otrosIncrementales'])));
                            
                            $root->appendChild($domtree->createElement("dta", $row['dta']));
                            $root->appendChild($domtree->createElement("iva", $row['iva']));
                            $root->appendChild($domtree->createElement("igi", $row['igi']));
                            $root->appendChild($domtree->createElement("prev", $row['prev']));
                            $root->appendChild($domtree->createElement("cnt", $row['cnt']));
                            $root->appendChild($domtree->createElement("totalEfectivo", $row['totalEfectivo']));
                            $root->appendChild($domtree->createElement("PesoBruto", $row['pesoBruto']));
                            $root->appendChild($domtree->createElement("bultos", $row['bultos']));
                            
                            if (isset($row["facturas"]) && !empty($row["facturas"])) {
                                $invoices = $root->appendChild($domtree->createElement("facturas"));
                                foreach ($row["facturas"] as $invoice) {
                                    $factura = $invoices->appendChild($domtree->createElement("factura"));
                                    $factura->appendChild($domtree->createElement("taxId", $invoice["taxId"]));
                                    $factura->appendChild($domtree->createElement("nomProveedor", htmlentities(utf8_decode(trim($invoice["nomProveedor"])), ENT_QUOTES, 'UTF-8')));
                                    $factura->appendChild($domtree->createElement("numFactura", $invoice["numFactura"]));
                                    $factura->appendChild($domtree->createElement("incoterm", htmlentities(utf8_decode(trim($invoice["incoterm"])), ENT_QUOTES, 'UTF-8')));
                                    $factura->appendChild($domtree->createElement("cove", htmlentities(utf8_decode(trim($invoice["cove"])), ENT_QUOTES, 'UTF-8')));
                                    $factura->appendChild($domtree->createElement("fechaFactura", htmlentities(utf8_decode(trim($invoice["fechaFactura"])), ENT_QUOTES, 'UTF-8')));
                                    $factura->appendChild($domtree->createElement("valorFacturaUsd", $invoice["valorFacturaUsd"]));
                                    $factura->appendChild($domtree->createElement("valorFacturaMonExt", $invoice["valorFacturaMonExt"]));
                                    $factura->appendChild($domtree->createElement("paisFactura", $invoice["paisFactura"]));
                                    $factura->appendChild($domtree->createElement("divisa", $invoice["divisa"]));
                                    $factura->appendChild($domtree->createElement("factorMonExt", $invoice["factorMonExt"]));
                                    if (isset($invoice["partes"]) && !empty($invoice["partes"])) {
                                        $parts = $factura->appendChild($domtree->createElement("partes"));
                                        foreach ($invoice["partes"] as $part) {
                                            $parte = $parts->appendChild($domtree->createElement("parte"));
                                            $parte->appendChild($domtree->createElement("numParte", strtoupper($part["numParte"])));
                                            
                                            $parte->appendChild($domtree->createElement("descripcion", htmlentities($part["descripcion"])));
                                            $parte->appendChild($domtree->createElement("fraccion", $part["fraccion"]));
                                            $parte->appendChild($domtree->createElement("ordenFraccion", $part["ordenFraccion"]));
                                            
                                            $parte->appendChild($domtree->createElement("valorMonExt", $part["precioUnitario"] * $part['cantUmc']));                                            
                                            $parte->appendChild($domtree->createElement("precioUnitario", $part["precioUnitario"]));
                                            
                                            $parte->appendChild($domtree->createElement("cantUmc", $part["cantUmc"]));
                                            $parte->appendChild($domtree->createElement("umc", $part["umc"]));
                                            $parte->appendChild($domtree->createElement("cantUmt", $part["cantUmt"]));
                                            $parte->appendChild($domtree->createElement("umt", $part["umt"]));
                                            $parte->appendChild($domtree->createElement("paisOrigen", $part["paisOrigen"]));
                                            $parte->appendChild($domtree->createElement("paisVendedor", $part["paisVendedor"]));
                                            $parte->appendChild($domtree->createElement("tasaAdvalorem", $part["tasaAdvalorem"]));
                                            if (isset($part["tlc"])) {
                                                if ($part["tlc"] == 'S') {
                                                    $tlc = 'S';
                                                } else {
                                                    $tlc = '';
                                                }
                                            }
                                            if (isset($part["prosec"])) {
                                                if ($part["prosec"] == 'S') {
                                                    $prosec = 'S';
                                                } else {
                                                    $prosec = '';
                                                }
                                            }
                                            $parte->appendChild($domtree->createElement("tlc", $tlc));
                                            $parte->appendChild($domtree->createElement("prosec", $prosec));
                                            if (isset($part["patenteOriginal"])) {
                                                $parte->appendChild($domtree->createElement("patenteOriginal", $part["patenteOriginal"]));
                                                $parte->appendChild($domtree->createElement("aduanaOriginal", $part["aduanaOriginal"]));
                                                $parte->appendChild($domtree->createElement("pedimentoOriginal", $part["pedimentoOriginal"]));
                                                $parte->appendChild($domtree->createElement("regimenOriginal", $part["regimenOriginal"]));
                                                $parte->appendChild($domtree->createElement("cantidadOriginal", $part["cantidadOriginal"]));
                                                $parte->appendChild($domtree->createElement("unidadOriginal", $part["unidadOriginal"]));
                                                $parte->appendChild($domtree->createElement("fechaOriginal", $part["fechaOriginal"]));
                                            }
                                            
                                        }
                                    }
                                }
                            }
                            
                        }
                        $xmlRoot = $domtree->appendChild($root);
                    } else {
                        throw new Exception("Token no válido.");
                    }                    
                } else {
                    throw new Exception("Aduana no válida.");
                }
            } else {
                throw new Exception("Los datos proporcionados no son válidos.");
            }
            if (isset($domtree)) {
                Zend_Controller_Action_HelperBroker::getStaticHelper("viewRenderer")->setNoRender(true);
                Zend_Layout::getMvcInstance()->disableLayout();
                $this->_response->setHeader("Content-Type", "text/xml; charset=utf-8")
                        ->setBody($domtree->saveXML());
            }
        } catch (Exception $ex) {
            $root = $domtree->createElement("operaciones");
            $xmlRoot = $domtree->appendChild($root);
            $xmlRoot->appendChild($domtree->createElement("success", "false"));
            $xmlRoot->appendChild($domtree->createElement("message", $ex->getMessage()));
            Zend_Controller_Action_HelperBroker::getStaticHelper("viewRenderer")->setNoRender(true);
            Zend_Layout::getMvcInstance()->disableLayout();
            $this->_response->setHeader("Content-Type", "text/xml; charset=utf-8")
                    ->setBody($domtree->saveXML());
        }
    }

    public function pedimentoAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $domtree = new DOMDocument("1.0", "UTF-8");
        $domtree->preserveWhiteSpace = false;
        $domtree->formatOutput = true;
        try {
            $f = array(
                "patente" => array("StringTrim", "StripTags", "Digits"),
                "aduana" => array("StringTrim", "StripTags", "Digits"),
                "pedimento" => array("StringTrim", "StripTags", "Digits"),
                "rfc" => array("StringTrim", "StripTags", "StringToUpper"),
                "token" => array("StringTrim", "StripTags", "StringToLower"),
            );
            $v = array(
                "patente" => new Zend_Validate_Int(),
                "aduana" => new Zend_Validate_Int(),
                "pedimento" => new Zend_Validate_Int(),
                "rfc" => array(new Zend_Validate_Regex("/^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/")),
                "token" => array(new Zend_Validate_Regex("/^[a-z0-9]+$/")),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("patente") && $input->isValid("aduana") && $input->isValid("rfc") && $input->isValid("pedimento")) {
                $row = new Webservice_Model_Table_WsTokens(array("rfc" => $input->rfc));
                $table = new Webservice_Model_WsTokens();
                $table->find($row);
                if (null === ($row->getId())) {
                    $root = $domtree->createElement("errores");
                    $xmlRoot = $domtree->appendChild($root);
                    $xmlRoot->appendChild($domtree->createElement("error", "RFC no en base de datos."));
                } else {
                    $mapper = new Trafico_Model_TraficosMapper();
                    $arr = $mapper->pedimento($input->patente, $input->aduana, $input->pedimento);
                    if (isset($arr) && !empty($arr)) {
                        $operaciones = $domtree->createElement("operaciones");
                        $operacion = $domtree->createElement("pedimento");
                        $operacion->appendChild($domtree->createElement("aduana", $arr["aduana"]));
                        $operacion->appendChild($domtree->createElement("patente", $arr["patente"]));
                        $operacion->appendChild($domtree->createElement("numero", $arr["pedimento"]));
                        $operacion->appendChild($domtree->createElement("referencia", $arr["referencia"]));
                        $operacion->appendChild($domtree->createElement("rfc", $arr["rfcCliente"]));
                        $operacion->appendChild($domtree->createElement("razonSocial", $arr["nombreCliente"]));
                        $operacion->appendChild($domtree->createElement("fechaPago", $arr["fechaPago"]));
                        $operacion->appendChild($domtree->createElement("regimen", $arr["regimen"]));
                        $operacion->appendChild($domtree->createElement("cvePedimento", $arr["cvePedimento"]));
                        $operaciones->appendChild($operacion);
                        $domtree->appendChild($operaciones);
                    } else {
                        $root = $domtree->createElement("errores");
                        $xmlRoot = $domtree->appendChild($root);
                        $xmlRoot->appendChild($domtree->createElement("error", "No data found!"));
                    }
                }
            } else {
                $root = $domtree->createElement("errores");
                $xmlRoot = $domtree->appendChild($root);
                $xmlRoot->appendChild($domtree->createElement("error", "Invalid Input!"));
            }
            $output = $domtree->saveXML();
            Zend_Controller_Action_HelperBroker::getStaticHelper("viewRenderer")->setNoRender(true);
            Zend_Layout::getMvcInstance()->disableLayout();
            $this->_response->setHeader("Content-Type", "text/xml; charset=utf-8")
                    ->setBody($output);
        } catch (Zend_Exception $ex) {
            throw new Zend_Exception($ex->getMessage());
        }
    }

    protected function _sistemaPedimentos($patente, $aduana) {
        $misc = new OAQ_Misc();
        $db = $misc->sitawin($patente, $aduana);
        return $db;
    }

}
