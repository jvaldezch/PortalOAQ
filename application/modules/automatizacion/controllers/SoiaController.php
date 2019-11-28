<?php

class Automatizacion_SoiaController extends Zend_Controller_Action {

    protected $_config;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
    }

    /**
     * /automatizacion/soia/pedimentos-pagados?patente=3589&aduana=640&fechaInicio=2017-06-01&fechaFin=2017-06-30
     * 
     * @throws Zend_Exception
     * @throws Exception
     */
    public function pedimentosPagadosAction() {
        try {
            $f = array(
                "patente" => array("StringTrim", "StripTags", "Digits"),
                "aduana" => array("StringTrim", "StripTags", "Digits"),
                "fechaIni" => array("StringTrim", "StripTags"),
                "fechaFin" => array("StringTrim", "StripTags"),
            );
            $v = array(
                "patente" => new Zend_Validate_Int(),
                "aduana" => new Zend_Validate_Int(),
                "fechaInicio" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
                "fechaFin" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("patente") && $input->isValid("aduana") && $input->isValid("fechaInicio") && $input->isValid("fechaFin")) {
                $mppr = new Vucem_Model_VucemPedimentosMapper();
                $misc = new OAQ_Misc();
                $db = $misc->sitawinTrafico($input->patente, $input->aduana);
                $arr = $db->pedimentoPagados($input->fechaInicio, $input->fechaFin);
                foreach ($arr as $item) {
                    if (!($mppr->verificar($item["patente"], $item["aduana"], $item["pedimento"]))) {
                        $mppr->agregarVacio(null, $item["patente"], (int) str_pad($item["aduana"], 3, "0", STR_PAD_RIGHT), $item["pedimento"]);
                    }
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Zend_Exception($ex->getMessage());
        }
    }

    protected function _analizarPedimento($id) {
        $mppr = new Vucem_Model_VucemPedimentosMapper();
        $mppre = new Vucem_Model_VucemPedimentosEstado();
        $arr = $mppr->obtener($id);
        if ((int) str_pad($arr["aduana"], 3, "0", STR_PAD_RIGHT) == 640) {
            if (!($resp = $mppr->verificarOperacion($id))) {
                $resp = $this->_consultaPedimento("MALL640523749", (int) $arr["patente"], (int) str_pad($arr["aduana"], 3, "0", STR_PAD_RIGHT), (int) $arr["pedimento"]);
                if ($resp["error"] == false && isset($resp["numeroOperacion"])) {
                    $mppr->actualizarOperacion($id, $resp["numeroOperacion"], $resp["rfcCliente"], $resp["partidas"]);
                }
                var_dump($resp);
            }
            if ($mppr->verificarDesaduando($id)) {
                if (isset($resp["numeroOperacion"]) && isset($resp["rfcCliente"])) {
                    $ressp = $this->_consultaEstatusPedimento("MALL640523749", (int) $arr["patente"], (int) str_pad($arr["aduana"], 3, "0", STR_PAD_RIGHT), (int) $arr["pedimento"], $resp["numeroOperacion"]);
                    var_dump($ressp);
                    if ($ressp["error"] == false && isset($ressp["numeroPrevalidador"])) {
                        if (!($mppre->verificar($id))) {
                            $verde = false;
                            $cumplido = false;
                            foreach ($ressp["estados"] as $item) {
                                $mppre->agregar($id, $ressp["numeroPrevalidador"], $ressp["fechaEstado"], $item["estado"], $item["descripcionEstado"], $item["subEstado"], $item["descripcionSubEstado"]);
                                if ((int) $item["estado"] == 3 && (int) $item["subEstado"] == 320) {
                                    $verde = true;
                                }
                                if ((int) $item["estado"] == 7 && (int) $item["subEstado"] == 710) {
                                    $cumplido = true;
                                }
                                if ((int) $item["estado"] == 7 && (int) $item["subEstado"] == 730) {
                                    $cumplido = true;
                                }
                                if ($verde == true && $cumplido == true) {
                                    $fechaLiberacion = date("Y-m-d H:i:s", strtotime($ressp["fechaEstado"]));
                                    $mppr->desaduanado($id, $fechaLiberacion);
                                }
                            }
                        }
                    }
                }
            }
            return;
        }
    }

    /**
     * /automatizacion/soia/consulta?id=1001
     * 
     * @throws Zend_Exception
     * @throws Exception
     */
    public function consultaAction() {
        try {
            $f = array(
                "id" => array("StringTrim", "StripTags", "Digits"),
            );
            $v = array(
                "id" => new Zend_Validate_Int(),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $this->_analizarPedimento($input->id);
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Zend_Exception($ex->getMessage());
        }
    }

    /**
     * /automatizacion/soia/batch-consulta?limit=5
     * 
     * @throws Zend_Exception
     * @throws Exception
     */
    public function batchConsultaAction() {
        try {
            $f = array(
                "limit" => array("StringTrim", "StripTags", "Digits"),
            );
            $v = array(
                "limit" => new Zend_Validate_Int(),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("limit")) {
                $mppr = new Vucem_Model_VucemPedimentosMapper();
                $arr = $mppr->sinDesaduanar($input->limit);
                if (!empty($arr)) {
                    foreach ($arr as $item) {
                        $this->_analizarPedimento((int) $item["id"]);
                    }
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Zend_Exception($ex->getMessage());
        }
    }

    protected function _consultaEstatusPedimento($rfc, $patente, $aduana, $pedimento, $numeroOperacion) {
        $res = new OAQ_Respuestas();
        $serv = new OAQ_Servicios();
        $firmantes = new Vucem_Model_VucemFirmanteMapper();
        $sello = $firmantes->obtenerDetalleFirmante($rfc);
        if (!empty($sello)) {
            $xml = new OAQ_XmlPedimentos(null, true); // ojo: cuando es consulta de estado
            $data["usuario"] = array(
                "username" => $sello["rfc"],
                "password" => $sello["ws_pswd"],
                "certificado" => null,
                "key" => null,
                "new" => null,
            );
            $xml->set_patente($patente);
            $xml->set_aduana($aduana);
            $xml->set_pedimento($pedimento);
            $xml->set_numeroOperacion($numeroOperacion);
            $xml->set_array($data);
            $xml->consultaEstadoPedimento();
            $serv->setXml($xml->getXml());
            $serv->consultaEstadoPedimento();
            $resp = $res->analizarRespuestaPedimento($serv->getResponse());
            return $resp;
        }
        return array("error" => true, "message" => "No sellos.");
    }

    protected function _consultaPedimento($rfc, $patente, $aduana, $pedimento) {
        $res = new OAQ_Respuestas();
        $serv = new OAQ_Servicios();
        $firmantes = new Vucem_Model_VucemFirmanteMapper();
        $sello = $firmantes->obtenerDetalleFirmante($rfc);
        if (!empty($sello)) {
            $xml = new OAQ_XmlPedimentos();
            $data["usuario"] = array(
                "username" => $sello["rfc"],
                "password" => $sello["ws_pswd"],
                "certificado" => null,
                "key" => null,
                "new" => null,
            );
            $xml->set_patente($patente);
            $xml->set_aduana($aduana);
            $xml->set_pedimento($pedimento);
            $xml->set_array($data);
            $xml->consultaPedimentoCompleto();
            $serv->setXml($xml->getXml());
            $serv->consumirPedimento();
            $resp = $res->analizarRespuestaPedimento($serv->getResponse());
            return $resp;
        }
        return array("error" => true, "message" => "No sellos.");
    }

}
