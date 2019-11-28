<?php

/**
 * Description of Vucem_Respuestas
 * 
 * Esta clase analiza los XML de respuesta de VUCEM y los convierte a un arreglo
 * que puede traer false o true dependiendo del tipo de respuesta.
 *
 * @author Jaime E. Valdez jvaldezch at gmail
 */
class OAQ_Respuestas {

    protected $debug = false;

    function __construct() {
    }

    /**
     * 
     * En ambiente de desarrollo establecer Debug para ver respuesta en forma de arreglo
     * 
     * @param boolean $debug
     */
    function setDebug($debug) {
        $this->debug = $debug;
    }

    /**
     * 
     * Remueve de la cadena de respuesta de VUCEM todos los namespaces innecesarios
     * 
     * @param string $string
     * @return type
     * @throws Exception
     */
    public function replace($string) {
        try {
            return str_replace(array("S:", "soapenv:", "oxml:", "con:", "wsse:", "wsu:", "env:", "ns3:", "ns2:", "ns5:", "ns4:", "ns6:", "ns7:", "ns8:", "ns9:"), "", $string);
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * Las respuestas de EDOCUMENT traen prefijos y subfijos, esta función los remueve
     * 
     * @param string $string
     * @param string $tagname
     * @return type
     */
    public function stringInsideTags($string, $tagname) {
        $pattern = "/<$tagname\b[^>]*>(.*?)<\/$tagname>/is";
        preg_match_all($pattern, $string, $matches);
        if (!empty($matches[1])) {
            return (string) $matches[1][0];
        }
        return array();
    }

    /**
     * 
     * Analiza la respuesta comparandola con los casos o tipos de respuesta que da VUCEM
     * 
     * @param type $xmlstr
     */
    public function analizarRespuesta($xmlstr) {
        if (!preg_match('/uuid:/i', $xmlstr)) {
            $array = $this->analizarRespuestaXml($this->replace($xmlstr));
        } else {
            $r = $this->stringInsideTags($xmlstr, "S:Envelope");
            if (empty($r)) {
                $r = $this->stringInsideTags($xmlstr, "env:Envelope");
            }
            $fix = "<S:Envelope>" . $r . "</S:Envelope>";
            $array = $this->analizarRespuestaXml($fix);
        }
        return $array;
    }
    
    /**
     * 
     * Analiza la respuesta comparandola con los casos o tipos de respuesta que da VUCEM
     * 
     * @param type $xmlstr
     */
    public function analizarRespuestaPedimento($xmlstr) {
        if (!preg_match('/uuid:/i', $xmlstr)) {
            $array = $this->respuestaPedimentoXml($this->replace($xmlstr));
        } else {
            $fix = "<S:Envelope>" . $this->stringInsideTags($xmlstr, "S:Envelope") . "</S:Envelope>";
            $array = $this->respuestaPedimentoXml($fix);
        }
        return $array;
    }

    /**
     * 
     * Esta función realiza el analisis de la respuesta en forma de arreglo obtenida de VUCEM
     * Para probar los ejemplos usar la clase Vucem_RespuestasEjemplos
     * 
     * @param string $xmlstr
     * @return boolean
     * @throws Exception
     */
    public function respuestaPedimentoXml($xmlstr) {
        try {
            $array = $this->xmlstrToArray($this->replace($xmlstr));
            if (isset($array["Header"])) {
                unset($array["Header"]);
            }
            if ($this->debug == true) {
                Zend_Debug::dump($array);
            }
            if (isset($array["Body"]) && isset($array["Body"]["Fault"])) {
                $msg["error"] = true;
                $res = $array["Body"]["Fault"];
                if (isset($res["faultcode"])) {
                    $msg["message"] = urlencode(utf8_decode($res["faultcode"] . " " . $res["faultstring"]));
                }
            }
            // consulta estado pedimento
            if (isset($array["Body"]) && isset($array["Body"]["consultarEstadoPedimentosRespuesta"])) {
                $res = $array["Body"]["consultarEstadoPedimentosRespuesta"];
                if (isset($res["tieneError"]) && $res["tieneError"] == "true") {
                    if (isset($res["error"]["mensaje"])) {
                        $msg["message"] = urlencode(utf8_decode($res["error"]["mensaje"]));
                    }
                    $msg["error"] = true;
                }
                if (isset($res["tieneError"]) && $res["tieneError"] == "false") {
                    $msg["error"] = false;
                    if (isset($res["pedimento"])) {
                        $msg["numeroPrevalidador"] = $res["pedimento"]["numeroPrevalidador"];
                        $msg["descripcionPrevalidador"] = $res["pedimento"]["descripcionPrevalidador"];
                        $msg["fechaEstado"] = $res["pedimento"]["fechaEstado"];
                        if (isset($res["pedimento"]["estadosPedimento"]) && is_array($res["pedimento"]["estadosPedimento"])) {
                            $msg["estados"] = $this->_convertirEstados($res["pedimento"]["estadosPedimento"]);
                        }
                    }
                }
            }
            // consulta pedimento completo
            if (isset($array["Body"]) && isset($array["Body"]["consultarPedimentoCompletoRespuesta"])) {
                $res = $array["Body"]["consultarPedimentoCompletoRespuesta"];
                if (isset($res["tieneError"]) && $res["tieneError"] == "true") {
                    if (isset($res["error"]["mensaje"])) {
                        $msg["message"] = urlencode(utf8_decode($res["error"]["mensaje"]));
                    }
                    $msg["error"] = true;
                }
                if (isset($res["tieneError"]) && $res["tieneError"] == "false") {
                    $msg["error"] = false;
                    if (isset($res["numeroOperacion"])) {
                        $msg["numeroOperacion"] = $res["numeroOperacion"];
                    }
                    if (isset($res["pedimento"]["partidas"])) {
                        $msg["partidas"] = (int) $res["pedimento"]["partidas"];
                    }
                    if (isset($res["pedimento"]["importadorExportador"]["fechas"])) {
                        $msg["fechas"] = $res["pedimento"]["importadorExportador"]["fechas"];
                        $msg["fechas"] = $this->_convertirFechas($res["pedimento"]["importadorExportador"]["fechas"]);
                    }
                    if (isset($res["pedimento"]["importadorExportador"]["rfc"])) {
                        $msg["rfcCliente"] = $res["pedimento"]["importadorExportador"]["rfc"];
                    }
                }
            }
            if (isset($msg)) {
                return $msg;
            }
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function _convertirEstados($array) {
        $arr = array();
        if (is_array($array) && !empty($array)) {
            foreach ($array as $item) {
                $arr[] = array(
                    "estado" => (int) $item["estado"],
                    "descripcionEstado" => $item["descripcionEstado"],
                    "subEstado" => (int) $item["subEstado"],
                    "descripcionSubEstado" => $item["descripcionSubEstado"],
                );
            }
        }
        return $arr;
    }

    protected function _convertirFechas($array) {
        $arr = array();
        if (is_array($array) && !empty($array)) {
            foreach ($array as $item) {
                $arr[$item["tipo"]["clave"]] = $item["fecha"];
            }
        }
        return $arr;
    }

    /**
     * 
     * Esta función realiza el analisis de la respuesta en forma de arreglo obtenida de VUCEM
     * Para probar los ejemplos usar la clase Vucem_RespuestasEjemplos
     * 
     * @param string $xmlstr
     * @return boolean
     * @throws Exception
     */
    public function analizarRespuestaXml($xmlstr) {
        try {
            $errm = new Application_Model_Errores();
            $array = $this->xmlstrToArray($this->replace($xmlstr));
            if (isset($array["Header"])) {
                unset($array["Header"]);
            }
            if ($this->debug == true) {
                Zend_Debug::dump($array);
            }
            if (isset($array["Body"]) && isset($array["Body"]["ConsultarEdocumentResponse"]) && isset($array["Body"]["ConsultarEdocumentResponse"]["response"])) {
                $res = $array["Body"]["ConsultarEdocumentResponse"]["response"];
                if (isset($res["mensaje"]) && isset($res["contieneError"])) {
                    if (preg_match('/El Cove o Adenda no existe/i', $res["mensaje"])) {
                        $msg["messages"] = $this->_getMessages($res["mensaje"]);
                        $msg["error"] = true;
                    }
                    if (preg_match('/Existen errores en los par/i', $res["mensaje"])) {
                        $msg["message"] = urlencode(utf8_decode($res["mensaje"]));
                        if (isset($res["errores"]["error"])) {
                            $msg["messages"] = $this->_getMessages($res["errores"]["error"]);
                        }
                        $msg["error"] = true;
                    }
                }
                if (isset($res["resultadoBusqueda"]["cove"])) {
                    $ress = $res["resultadoBusqueda"]["cove"];
                    $msg["edocument"] = $ress["eDocument"];
                    $msg["messages"] = $this->_getMessages($res["mensaje"]);
                    $msg["error"] = false;
                }
            } elseif (isset($array["Body"]) && isset($array["Body"]["solicitarRecibirCoveServicioResponse"]) && isset($array["Body"]["solicitarRecibirCoveServicioResponse"]["mensajeInformativo"])) {
                $res = $array["Body"]["solicitarRecibirCoveServicioResponse"];
                if (preg_match('/No se recibieron comprobantes/i', $res["mensajeInformativo"])) {
                    $msg["messages"] = $this->_getMessages($res["mensajeInformativo"]);
                    $msg["error"] = true;
                }
                if (preg_match('/No recibira correo de respuesta/i', $res["mensajeInformativo"])) {
                    $msg["messages"] = $this->_getMessages($res["mensajeInformativo"]);
                    $msg["error"] = true;
                }
                if (isset($res["numeroDeOperacion"]) && preg_match('/del COVE fue exitosa/i', $res["mensajeInformativo"])) {
                    $msg["numeroOperacion"] = (int) $res["numeroDeOperacion"];
                    $msg["messages"] = $this->_getMessages($res["mensajeInformativo"]);
                    $msg["error"] = false;
                }
            } elseif (isset($array["Body"]) && isset($array["Body"]["solicitarConsultarRespuestaCoveServicioResponse"])) {
                $res = $array["Body"]["solicitarConsultarRespuestaCoveServicioResponse"];
                if (isset($res["leyenda"]) && !isset($res["numeroOperacion"])) {
                    if (preg_match('/No existe el n/i', $res["leyenda"])) {
                        $msg["message"] = $this->_getMessages($res["leyenda"]);
                        $msg["error"] = true;
                    }
                    if (preg_match('/Firma Elec/i', $res["leyenda"])) {
                        $msg["message"] = "Firma Electrónica : Firma inválida";
                        $msg["error"] = true;
                    }
                }
                if (isset($res["numeroOperacion"])) {
                    if (isset($res["respuestasOperaciones"]["eDocument"]) && $res["respuestasOperaciones"]["contieneError"] === 'false') {
                        $msg["numeroOperacion"] = (int) $res["numeroOperacion"];
                        $msg["numFactura"] = $res["respuestasOperaciones"]["numeroFacturaORelacionFacturas"];
                        $msg["edocument"] = $res["respuestasOperaciones"]["eDocument"];
                        if (isset($res["respuestasOperaciones"]["numeroAdenda"])) {
                            $msg["numeroAdenda"] = $res["respuestasOperaciones"]["numeroAdenda"];
                        }
                        $msg["error"] = false;
                    }
                    if (isset($res["numeroOperacion"]) && $res["respuestasOperaciones"]["contieneError"] === 'true') {
                        $msg["numeroOperacion"] = (int) $res["numeroOperacion"];
                        $msg["numFactura"] = $res["respuestasOperaciones"]["numeroFacturaORelacionFacturas"];
                        $msg["error"] = true;
                        if (isset($res["respuestasOperaciones"]["errores"]["mensaje"])) {
                            $msg["messages"] = $this->_getMessages($res["respuestasOperaciones"]["errores"]["mensaje"]);
                        }
                    }
                }
            } elseif (isset($array["Body"]) && isset($array["Body"]["consultaDigitalizarDocumentoServiceResponse"]) && isset($array["Body"]["consultaDigitalizarDocumentoServiceResponse"]["respuestaBase"])) {
                $res = $array["Body"]["consultaDigitalizarDocumentoServiceResponse"]["respuestaBase"];
                if (isset($res["tieneError"]) && $res["tieneError"] === 'true') {
                    if (isset($res["error"]["mensaje"])) {
                        $msg["messages"] = $this->_getMessages($res["error"]["mensaje"]);
                    }
                    $msg["error"] = true;
                }
                if (isset($res["tieneError"]) && $res["tieneError"] === 'false') {
                    $res = $array["Body"]["consultaDigitalizarDocumentoServiceResponse"];
                    $msg["edocument"] = $res["eDocument"];
                    $msg["numeroDeTramite"] = (string) $res["numeroDeTramite"];
                    $msg["error"] = false;
                }
            } elseif (isset($array["Body"]) && isset($array["Body"]["registroDigitalizarDocumentoServiceResponse"])) {
                $res = $array["Body"]["registroDigitalizarDocumentoServiceResponse"];
                if (isset($res["respuestaBase"]) && isset($res["respuestaBase"]["tieneError"]) && $res["respuestaBase"]["tieneError"] === 'false') {
                    if (isset($res["acuse"])) {
                        $msg["numeroOperacion"] = $res["acuse"]["numeroOperacion"];
                        if (isset($res["acuse"]["mensaje"])) {
                            $msg["messages"] = $this->_getMessages($res["acuse"]["mensaje"]);
                        }
                        $msg["error"] = false;
                    }
                }
                if (isset($res["respuestaBase"]) && isset($res["respuestaBase"]["tieneError"]) && $res["respuestaBase"]["tieneError"] === 'true') {
                    if (isset($res["respuestaBase"]["error"]["mensaje"])) {
                        $msg["messages"] = $this->_getMessages($res["respuestaBase"]["error"]["mensaje"]);
                    }
                    $msg["error"] = true;
                }
            } elseif (isset($array["Body"]) && isset($array["Body"]["consultarPartidaRespuesta"])) {
                $res = $array["Body"]["consultarPartidaRespuesta"];
                if (isset($res["tieneError"]) && $res["tieneError"] == 'false') {
                    if(isset($res["partida"])) {
                        $msg["numeroPartida"] = (int)$res["partida"]["numeroPartida"];
                        $msg["fraccionArancelaria"] = $res["partida"]["fraccionArancelaria"];
                    }
                    $msg["error"] = false;
                }
            } elseif (isset($array["Body"]) && isset($array["Body"]["consultarPedimentoCompletoRespuesta"])) {
                $res = $array["Body"]["consultarPedimentoCompletoRespuesta"];
                if (isset($res["tieneError"]) && $res["tieneError"] == 'true') {
                    if (isset($res["error"]["mensaje"])) {
                        $msg["messages"] = $this->_getMessages($res["error"]["mensaje"]);
                    }
                    $msg["error"] = true;
                }
                if (isset($res["tieneError"]) && $res["tieneError"] == 'false') {
                    if(isset($res["numeroOperacion"])) {
                        $msg["numeroOperacion"] = (int)$res["numeroOperacion"];
                    }
                    if(isset($res["pedimento"]["encabezado"]["tipoOperacion"])) {
                        $msg["tipoOperacion"] = (int)$res["pedimento"]["encabezado"]["tipoOperacion"]["clave"];
                    }
                    if(isset($res["pedimento"]["partidas"])) {
                        if(!is_array($res["pedimento"]["partidas"])) {
                            $msg["partidas"] = (int)$res["pedimento"]["partidas"];
                        } else {
                            $msg["partidas"] = count($res["pedimento"]["partidas"]);
                        }
                    }
                    if(isset($res["pedimento"]["encabezado"]["claveDocumento"])) {
                        $msg["clavePedimento"] = $res["pedimento"]["encabezado"]["claveDocumento"]["clave"];
                    }
                    if(isset($res["pedimento"]["importadorExportador"]["fechas"])) {
                        $fechas = $this->_getDates($res["pedimento"]["importadorExportador"]["fechas"]);
                        $msg["fechaPago"] = isset($fechas[2]) ? $fechas[2] : null;
                        $msg["fechaEntrada"] = isset($fechas[1]) ? $fechas[1] : null;
                    }
                    $msg["error"] = false;
                }
            } elseif (isset($array["Body"]) && isset($array["Body"]["Fault"])) {
                $res = $array["Body"]["Fault"];
                if (isset($res["faultcode"]) && isset($res["faultstring"])) {
                    if (preg_match('/FailedAuthentication/i', $res["faultcode"])) {
                        $msg["messages"] = $this->_getMessages($errm->get(3001));
                        $msg["error"] = true;
                    }
                    if (preg_match('/Undeclared namespace prefix/i', $res["faultstring"])) {
                        $msg["messages"] = $this->_getMessages($errm->get(3002));
                        $msg["error"] = true;
                    }
                    if (preg_match('/Cannot find dispatch method for/i', $res["faultstring"])) {
                        $msg["messages"] = $this->_getMessages($res["faultstring"]);
                        $msg["error"] = true;
                    }
                }
            } else {
                return false;
            }
            if (isset($array["Body"]) && isset($array["Body"]["Fault"])) {
                if (isset($array["Body"]["Fault"]["faultstring"])) {
                    $msg["messages"] = $this->_getMessages($array["Body"]["Fault"]["faultstring"]);
                    $msg["error"] = true;
                }
            }
            if (isset($msg)) {
                return $msg;
            }
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * Codifica los mensajes obtenidos en las respuestas de VUCEM
     * 
     * @param array|string $message
     * @return type
     */
    protected function _getMessages($message) {
        if (is_string($message)) {
            return array(utf8_encode($message));
        } elseif (is_array($message)) {
            $msgs = array();
            foreach ($message as $msg) {
                $msgs[] = utf8_encode($msg);
            }
            return $msgs;
        }
        return array();
    }
    
    /**
     * 
     * Convierte la fecha en el formato propio de MySQL
     * 
     * @param string $fechas
     * @return type
     */
    protected function _getDates($fechas) {
        if(is_array($fechas)) {
            foreach ($fechas as $item) {
                if((int)$item["tipo"]["clave"] == 2) {
                    $data[2] = date('Y-m-d H:i:s', strtotime($item["fecha"]));
                }
                if((int)$item["tipo"]["clave"] == 1) {
                    $data[1] = date('Y-m-d H:i:s', strtotime($item["fecha"]));
                }
            }
        }
        return $data;
    }

    /**
     * 
     * Convierte un XML depurado sin namespaces a arreglo
     * 
     * @param string $xmlstr
     * @return type
     */
    public function xmlstrToArray($xmlstr) {
        $doc = new DOMDocument();
        $doc->loadXML($xmlstr);
        return $this->_domnodeToArray($doc->documentElement);
    }

    /**
     * 
     * Conversión del XML
     * 
     * @param type $node
     * @return type
     */
    protected function _domnodeToArray($node) {
        $output = array();
        switch ($node->nodeType) {
            case XML_CDATA_SECTION_NODE:
            case XML_TEXT_NODE:
                $output = trim($node->textContent);
                break;
            case XML_ELEMENT_NODE:
                for ($i = 0, $m = $node->childNodes->length; $i < $m; $i++) {
                    $child = $node->childNodes->item($i);
                    $v = $this->_domnodeToArray($child);
                    if (isset($child->tagName)) {
                        $t = $child->tagName;
                        if (!isset($output[$t])) {
                            $output[$t] = array();
                        }
                        $output[$t][] = $v;
                    } elseif ($v) {
                        $output = (string) $v;
                    }
                }
                if (is_array($output)) {
                    if ($node->attributes->length) {
                        $a = array();
                        foreach ($node->attributes as $attrName => $attrNode) {
                            $a[$attrName] = (string) $attrNode->value;
                        }
                        $output['@attributes'] = $a;
                    }
                    foreach ($output as $t => $v) {
                        if (is_array($v) && count($v) == 1 && $t != '@attributes') {
                            $output[$t] = $v[0];
                        }
                    }
                }
                break;
        }
        return $output;
    }

}
