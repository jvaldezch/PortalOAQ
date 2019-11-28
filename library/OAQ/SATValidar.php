<?php

include('phpseclib/File/X509.php');
include('phpseclib/Math/BigInteger.php');

/**
 * Description of EmailNotifications
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class OAQ_SATValidar {

    protected $_config;
    protected $_data;
    protected $_array;
    protected $_folio;
    protected $_patente;
    protected $_guia;
    protected $_fechaFolio;
    protected $_rfcEmisor;
    protected $_rfcReceptor;
    protected $_cdfi = false;
    
    function get_rfcEmisor() {
        return $this->_rfcEmisor;
    }

    function get_rfcReceptor() {
        return $this->_rfcReceptor;
    }
    
    function get_folio() {
        return $this->_folio;
    }
    
    function get_patente() {
        return $this->_patente;
    }

    function get_guia() {
        return $this->_guia;
    }
    
    function get_fechaFolio() {
        return $this->_fechaFolio;
    }

    function get_data() {
        return $this->_data;
    }

    function __construct() {
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
    }
    
    protected function _clean($xml) {
        return str_replace(array('ns2:', 'xsi:', 'sat:', 'cfd:', 'cfdi:', 'cce11:', 'tfd:', 'xmlns:', 'ns3:', 'ns9:', 'ns8:', 'S:', 'wsu:', 'wsse:', 'ns3:', 'wsu:', 'soapenv:', 'soap:', 'oxml:', '<![CDATA[', ']]>'), '', $xml);
    }

    public function satToArray($xml) {
        $sxe = new SimpleXMLElement($this->_clean($xml));
        $dom_sxe = dom_import_simplexml($sxe);
        $dom = new DOMDocument('1.0');
        $dom_sxe = $dom->importNode($dom_sxe, true);
        $dom_sxe = $dom->appendChild($dom_sxe);
        $element = $dom->childNodes->item(0);
        foreach ($sxe->getDocNamespaces() as $name => $uri) {
            $element->removeAttributeNS($uri, $name);
        }
        $clean = simplexml_load_string($dom->saveXML());
        return json_decode(json_encode($clean), true);
    }

    public function comprobarCDFXml($rfc, $serie, $folio, $numAprob, $yearAprob, $cert, $fecha, $id = 1) {
        $xml = "<cdf:ColleccionFoliosCfd xsi:schemaLocation=\"http://www.sat.gob.mx/Asf/Sicofi/ValidacionFoliosCDF/1.0.0 FoliosCDFNuevo.xsd\" xmlns:cdf=\"http://www.sat.gob.mx/Asf/Sicofi/ValidacionFoliosCFD/1.0.0\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">
            <cdf:Folio>
              <cdf:Id>{$id}</cdf:Id>
              <cdf:Rfc>{$rfc}</cdf:Rfc>
              <cdf:Serie>{$serie}</cdf:Serie>
              <cdf:NumeroFolio>{$folio}</cdf:NumeroFolio>
              <cdf:NumeroAprobacion>{$numAprob}</cdf:NumeroAprobacion>
              <cdf:AnioAprobacion>{$yearAprob}</cdf:AnioAprobacion>
              <cdf:CertificadoNumeroSerie>{$cert}</cdf:CertificadoNumeroSerie>
              <cdf:CertificadoFechaEmision>{$fecha}</cdf:CertificadoFechaEmision>
            </cdf:Folio>
          </cdf:ColleccionFoliosCfd>";
        return $xml;
    }

    public function solicitudValidarCDF($ColleccionFolios) {
        $xml = "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:sat=\"http://www.sat.gob.mx/\">
            <soapenv:Header/>
            <soapenv:Body>
               <sat:ValidarXmlCFD>
                  <sat:xml>{$ColleccionFolios}</sat:xml>
               </sat:ValidarXmlCFD>
            </soapenv:Body>
         </soapenv:Envelope>";
        return $xml;
    }

    public function satValidarCDF($xml) {
        try {
            $headers = array(
                "Content-type: text/xml; charset=UTF-8",
                "Accept: text/xml",
                "Cache-Control: no-cache",
                "Pragma: no-cache",
                "Content-length: " . strlen($xml) . "");
            $url = "https://tramitesdigitales.sat.gob.mx/Sicofi.wsExtValidacionCFD/WsValidacionCFDsExt.asmx";
            $soap = curl_init();
            curl_setopt($soap, CURLOPT_URL, $url);
            curl_setopt($soap, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($soap, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($soap, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($soap, CURLOPT_POST, true);
            curl_setopt($soap, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($soap, CURLOPT_POSTFIELDS, $xml);
            $result = curl_exec($soap);
            curl_close($soap);
            return $result;
        } catch (Exception $e) {
            echo "<b>Exception on " . __METHOD__ . "</b>" . $e->getMessage();
            die();
        }
    }

    public function obtenerGeneralesComercioExterior($arr, $array) {
        if (isset($array["@attributes"])) {
            $d = $array["@attributes"];
            isset($d["CodigoPostal"]) ? $arr["codigoPostal"] = $d["CodigoPostal"] : null;
            isset($d["Pais"]) ? $arr["pais"] = $d["Pais"] : null;
            isset($d["Estado"]) ? $arr["estado"] = $d["Estado"] : null;
            isset($d["Calle"]) ? $arr["calle"] = $d["Calle"] : null;
            isset($d["Municipio"]) ? $arr["municipio"] = $d["Municipio"] : null;
            isset($d["NumeroExterior"]) ? $arr["numExt"] = $d["NumeroExterior"] : null;
            isset($d["NumeroIxterior"]) ? $arr["numInt"] = $d["NumeroIxterior"] : null;
        }
        return $arr;
    }
    
    public function obtenerGenerales($datos) {
        $values = $this->array_change_key_case_ext($datos);
        unset($datos);

        $data["rfc"] = $values["@attributes"]["rfc"];
        $data["razonSocial"] = $values["@attributes"]["nombre"];
        $valid = array(
            'calle' => 'calle',
            'noexterior' => 'numExt',
            'nointerior' => 'numInt',
            'localidad' => 'localidad',
            'colonia' => 'colonia',
            'ciudad' => 'ciudad',
            'municipio' => 'municipio',
            'estado' => 'estado',
            'pais' => 'pais',
            'codigopostal' => 'codigoPostal',
            'numregidtrib' => 'taxId',
        );
        $dom = "domicilio";
        if (isset($values["domiciliofiscal"])) {
            $dom = "domiciliofiscal";
        } elseif (isset($values["domicilio"])) {
            $dom = "domicilio";
        } else {
            $dom = 0;
        }
        foreach ($valid as $k => $v) {
            if (isset($values[$dom]["@attributes"][$k])) {
                $data["domicilio"][$v] = isset($values[$dom]["@attributes"][$k]) ? $values[$dom]["@attributes"][$k] : null;
            }
        }
        $data["regimen"] = isset($values["regimenfiscal"]["@attributes"]["regimen"]) ? strtoupper($values["regimenfiscal"]["@attributes"]["regimen"]) : null;
        $data["taxId"] = isset($values["@attributes"]["numregidtrib"]) ? strtoupper($values["@attributes"]["numregidtrib"]) : null;
        return $data;
    }

    protected function array_change_key_case_ext(array $array, $case = 10, $useMB = false, $mbEnc = 'UTF-8') {
        $newArray = array();
        //for more speed define the runtime created functions in the global namespace
        //get function
        if ($useMB === false) {
            $function = 'strToUpper'; //default
            switch ($case) {
                //first-char-to-lowercase
                case 25:
                    //maybee lcfirst is not callable
                    if (!function_exists('lcfirst')) {
                        $function = create_function('$input', 'return strToLower($input[0]) . substr($input, 1, (strLen($input) - 1));');
                    } else {
                        $function = 'lcfirst';
                    }
                    break;
                //first-char-to-uppercase                
                case 20:
                    $function = 'ucfirst';
                    break;
                //lowercase
                case 10:
                    $function = 'strToLower';
            }
        } else {
            //create functions for multibyte support
            switch ($case) {
                //first-char-to-lowercase
                case 25:
                    $function = create_function('$input', '
                        return mb_strToLower(mb_substr($input, 0, 1, \'' . $mbEnc . '\')) . 
                            mb_substr($input, 1, (mb_strlen($input, \'' . $mbEnc . '\') - 1), \'' . $mbEnc . '\');
                    ');
                    break;
                //first-char-to-uppercase
                case 20:
                    $function = create_function('$input', '
                        return mb_strToUpper(mb_substr($input, 0, 1, \'' . $mbEnc . '\')) . 
                            mb_substr($input, 1, (mb_strlen($input, \'' . $mbEnc . '\') - 1), \'' . $mbEnc . '\');
                    ');
                    break;
                //uppercase
                case 15:
                    $function = create_function('$input', '
                        return mb_strToUpper($input, \'' . $mbEnc . '\');
                    ');
                    break;
                //lowercase
                default: //case 10:
                    $function = create_function('$input', '
                        return mb_strToLower($input, \'' . $mbEnc . '\');
                    ');
            }
        }
        //loop array
        foreach ($array as $key => $value) {
            if (is_array($value)) { //$value is an array, handle keys too
                $newArray[$function($key)] = $this->array_change_key_case_ext($value, $case, $useMB);
            } elseif (is_string($key)) {
                $newArray[$function($key)] = $value;
            } else {
                $newArray[$key] = $value; //$key is not a string
            }
        } //end loop
        return $newArray;
    }

    public function obtenerComplemento($datos) {
        $values = $this->array_change_key_case_ext($datos);
        unset($datos);
        if (!isset($values["@attributes"]) && isset($values[0]["@attributes"])) {
            $values = $values[0]["@attributes"];
        } elseif (isset($values["timbrefiscaldigital"])) {
            $values = $values["timbrefiscaldigital"]["@attributes"];
        }
        $data["uuid"] = strtoupper($values["uuid"]);
        $data["fechaTimbrado"] = date('Y-m-d H:i:s', strtotime($values["fechatimbrado"]));
        $data["noCertificadoSAT"] = $values["nocertificadosat"];
        $data["selloSAT"] = $values["sellosat"];
        $data["selloCFD"] = $values["sellocfd"];
        return $data;
    }

    public function parametrosAdenda($array) {
        $data = array();
        if (isset($array["patente"]["@attributes"]["valor"])) {
            $data["patente"] = (int) $array["patente"]["@attributes"]["valor"];
        }
        if (isset($array["patente"]["pedimento"]["guia"])) {
            $data["observaciones"] = "GUIA: " . $array["patente"]["pedimento"]["guia"];
        }
        if (isset($array["patente"]["guia"])) {
            $data["observaciones"] = "GUIA: " . $array["patente"]["guia"];
        }
        if (isset($array["patente"]["pedimento"]["@attributes"]["numero"])) {
            $data["pedimento"] = (int) $array["patente"]["pedimento"]["@attributes"]["numero"];
        }
        return $data;
    }

    public function analizarArchivoXml($filename) {
        $this->_array = $this->satToArray(file_get_contents($filename));
        if ($this->_array !== false) {
            $this->_cdfi = true;
            $this->_analizarCdfi();
        }
    }

    public function isCdfi() {
        if ($this->_cdfi === true) {
            return true;
        }
    }

    protected function _analizarCdfi() {
        if (empty($this->_array["@attributes"]) || !isset($this->_array["@attributes"])) {
            throw new Exception("Unable to get data from XML file.");
        }
        if (isset($this->_array["@attributes"]["Fecha"])) { // v 3.3
            
            if (isset($this->_array["Complemento"]["TimbreFiscalDigital"]["@attributes"]["UUID"])) {
                $this->_data["uuid"] = $this->_array["Complemento"]["TimbreFiscalDigital"]["@attributes"]["UUID"];
            } elseif (isset($this->_array["Complemento"][0]["@attributes"]["UUID"])) {
                $this->_data["uuid"] = $this->_array["Complemento"][0]["@attributes"]["UUID"];
            }
            if (isset($this->_array["Addenda"]["operacion"]["patente"]["pedimento"]["guia"])) {
                $this->_guia = $this->_array["Addenda"]["operacion"]["patente"]["pedimento"]["guia"];
            }
            if (isset($this->_array["Addenda"]["operacion"]["patente"]["pedimento"]["@attributes"]["numero"])) {
                $this->_data["pedimento"] = (int) $this->_array["Addenda"]["operacion"]["patente"]["pedimento"]["@attributes"]["numero"];
            }
            if (isset($this->_array["@attributes"]["Folio"])) {
                $this->_data["folio"] = $this->_array["@attributes"]["Folio"];
                $this->_folio = $this->_array["@attributes"]["Folio"];
            }
            if (isset($this->_array["@attributes"]["Fecha"])) {
                $this->_data["fecha"] = date('Y-m-d H:i:s', strtotime($this->_array["@attributes"]["Fecha"]));
                $this->_fechaFolio = date('Y-m-d H:i:s', strtotime($this->_array["@attributes"]["Fecha"]));
            }
            if (isset($this->_array["Emisor"]["@attributes"]["Rfc"])) {
                $this->_data["emisor_rfc"] = $this->_array["Emisor"]["@attributes"]["Rfc"];
                $this->_rfcEmisor = $this->_array["Emisor"]["@attributes"]["Rfc"];
            }
            if (isset($this->_array["Emisor"]["@attributes"]["Nombre"])) {
                $this->_data["emisor_nombre"] = $this->_array["Emisor"]["@attributes"]["Nombre"];
            }
            if (isset($this->_array["Receptor"]["@attributes"]["Rfc"])) {
                $this->_data["receptor_rfc"] = $this->_array["Receptor"]["@attributes"]["Rfc"];
                $this->_rfcReceptor = $this->_array["Receptor"]["@attributes"]["Rfc"];
            }
            if (isset($this->_array["Receptor"]["@attributes"]["Nombre"])) {
                $this->_data["receptor_nombre"] = $this->_array["Receptor"]["@attributes"]["Nombre"];
            }
            if (isset($this->_array["Addenda"]["operacion"]["patente"]["pedimento"]["guia"])) {
                $this->_guia = $this->_array["Addenda"]["operacion"]["patente"]["pedimento"]["guia"];
            } else if (isset($this->_array["Addenda"]["operacion"]["patente"]["guia"])) {
                $this->_guia = $this->_array["Addenda"]["operacion"]["patente"]["guia"];
            }
            return;
            
        } else if (isset($this->_array["@attributes"]["fecha"])) { // v 2.4
            
            if (isset($this->_array["Complemento"]["TimbreFiscalDigital"]["@attributes"]["UUID"])) {
                $this->_data["uuid"] = $this->_array["Complemento"]["TimbreFiscalDigital"]["@attributes"]["UUID"];
            } elseif (isset($this->_array["Complemento"][0]["@attributes"]["UUID"])) {
                $this->_data["uuid"] = $this->_array["Complemento"][0]["@attributes"]["UUID"];
            }
            if (isset($this->_array["Addenda"]["operacion"]["patente"]["@attributes"]["valor"])) {
                $this->_data["patente"] = (int) $this->_array["Addenda"]["operacion"]["patente"]["@attributes"]["valor"];
                $this->_patente = (int) $this->_array["Addenda"]["operacion"]["patente"]["@attributes"]["valor"];
            }
            if (isset($this->_array["Addenda"]["operacion"]["patente"]["pedimento"]["guia"])) {
                $this->_data["observaciones"] = $this->_array["Addenda"]["operacion"]["patente"]["pedimento"]["guia"];
            }
            if (isset($this->_array["Addenda"]["operacion"]["patente"]["guia"])) {
                $this->_data["observaciones"] = $this->_array["Addenda"]["operacion"]["patente"]["guia"];
                if (isset($this->_array["Addenda"]["operacion"]["patente"]["guia"])) {
                    $this->_guia = $this->_array["Addenda"]["operacion"]["patente"]["guia"];
                }
            }
            if (isset($this->_array["Addenda"]["operacion"]["patente"]["pedimento"]["guia"])) {
                $this->_guia = $this->_array["Addenda"]["operacion"]["patente"]["pedimento"]["guia"];
            }
            if (isset($this->_array["Addenda"]["operacion"]["patente"]["pedimento"]["@attributes"]["numero"])) {
                $this->_data["pedimento"] = (int) $this->_array["Addenda"]["operacion"]["patente"]["pedimento"]["@attributes"]["numero"];
            }
            if (isset($this->_array["@attributes"]["folio"])) {
                $this->_data["folio"] = $this->_array["@attributes"]["folio"];
                $this->_folio = $this->_array["@attributes"]["folio"];
            }
            if (isset($this->_array["Emisor"]["@attributes"]["rfc"])) {
                $this->_data["emisor_rfc"] = $this->_array["Emisor"]["@attributes"]["rfc"];
                $this->_rfcEmisor = $this->_array["Emisor"]["@attributes"]["rfc"];
            }
            if (isset($this->_array["Emisor"]["@attributes"]["nombre"])) {
                $this->_data["emisor_nombre"] = $this->_array["Emisor"]["@attributes"]["nombre"];
            }
            if (isset($this->_array["Receptor"]["@attributes"]["rfc"])) {
                $this->_data["receptor_rfc"] = $this->_array["Receptor"]["@attributes"]["rfc"];
                $this->_rfcReceptor = $this->_array["Receptor"]["@attributes"]["rfc"];
            }
            if (isset($this->_array["Receptor"]["@attributes"]["nombre"])) {
                $this->_data["receptor_nombre"] = $this->_array["Receptor"]["@attributes"]["nombre"];
            }
            if (isset($this->_array["@attributes"]["fecha"])) {
                $this->_data["fecha"] = date('Y-m-d H:i:s', strtotime($this->_array["@attributes"]["fecha"]));
                $this->_fechaFolio = date('Y-m-d H:i:s', strtotime($this->_array["@attributes"]["fecha"]));
            }
            return;
        }
        return;
    }

    public function fechasDeCertificado($cer) {
        $arr = array();
        $x509 = new File_X509();
        $cert = $x509->loadX509($cer);
        if (isset($cert["tbsCertificate"]["validity"]["notBefore"]["utcTime"])) {
            $arr["valido_desde"] = date("Y-m-d H:i:s", strtotime($cert["tbsCertificate"]["validity"]["notBefore"]["utcTime"]));
        }
        if (isset($cert["tbsCertificate"]["validity"]["notAfter"]["utcTime"])) {
            $arr["valido_hasta"] = date("Y-m-d H:i:s", strtotime($cert["tbsCertificate"]["validity"]["notAfter"]["utcTime"]));
        }
        return $arr;
    }
    
    public function cdfiComercio($content) {
        $xml = $this->satToArray($content);
        if (isset($xml["Complemento"]["ComercioExterior"])) {
            $receptor = $this->obtenerGenerales($xml["Receptor"]);
            $receptor = $this->obtenerGeneralesComercioExterior($receptor, $xml["Complemento"]["ComercioExterior"]["Receptor"]["Domicilio"]);

            if (isset($xml["@attributes"]["TipoCambio"])) {
                $tipoCambio = $xml["@attributes"]["TipoCambio"];
            }
            if (isset($xml["@attributes"]["Total"])) {
                $total = $xml["@attributes"]["Total"];
            }
            if (isset($xml["@attributes"]["Moneda"])) {
                $moneda = $xml["@attributes"]["Moneda"];
            }
            
            $productos = array();
            
            if (isset($xml["Conceptos"]["Concepto"])) {
                foreach ($xml["Conceptos"]["Concepto"] as $item) {
                    $d = $item["@attributes"];
                    $tmp = array();
                    isset($d["NoIdentificacion"]) ? $tmp["numParte"] = $d["NoIdentificacion"] : null;
                    isset($d["Cantidad"]) ? $tmp["cantidadFactura"] = $d["Cantidad"] : null;
                    isset($d["Descripcion"]) ? $tmp["descripcion"] = $d["Descripcion"] : null;
                    isset($d["ValorUnitario"]) ? $tmp["precioUnitario"] = $d["ValorUnitario"] : null;
                    isset($d["Importe"]) ? $tmp["valorComercial"] = $d["Importe"] : null;
                    $productos[$d["NoIdentificacion"]] = $tmp;
                }
            }
            
            if (isset($xml["Complemento"]["ComercioExterior"]["Mercancias"])) {
                foreach ($xml["Complemento"]["ComercioExterior"]["Mercancias"]["Mercancia"] as $item) {
                    $d = $item["@attributes"];
                    if (isset($productos[$d["NoIdentificacion"]])) {
                        isset($d["FraccionArancelaria"]) ? $productos[$d["NoIdentificacion"]]["fraccion"] = $d["FraccionArancelaria"] : null;
                        isset($d["UnidadAduana"]) ? $productos[$d["NoIdentificacion"]]["umc"] = $d["UnidadAduana"] : null;
                        isset($d["ValorDolares"]) ? $productos[$d["NoIdentificacion"]]["calorDolares"] = $d["ValorDolares"] : null;
                    }
                }
            }
            
            return array(
                "receptor" => $receptor,
                "productos" => $productos,
            );
            
        }
        return;
    }

}
