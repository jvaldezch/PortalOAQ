<?php

/**
 * Description of Vucem
 *
 * @author Jaime
 */
class Utilerias_Vucem {

    protected $_patente;
    protected $_aduana;
    protected $_pedimento;
    protected $_referencia;
    protected $_numeroFacturaOriginal;
    protected $_relacionFacturas = false;
    protected $_appconfig;
    protected $_filename;
    protected $_array;
    protected $_domtree;
    protected $_envelope;
    protected $_body;
    protected $_document;
    protected $_service;
    protected $_header;
    protected $_request;
    protected $_comprobante;
    protected $_firmaElectronica;
    protected $_xsl;
    
    function set_patente($_patente) {
        $this->_patente = $_patente;
    }

    function set_aduana($_aduana) {
        $this->_aduana = $_aduana;
    }

    function set_pedimento($_pedimento) {
        $this->_pedimento = $_pedimento;
    }

    function set_referencia($_referencia) {
        $this->_referencia = $_referencia;
    }
    
    function get_filename() {
        return $this->_filename;
    }

    function __construct($cove = false, $edoc = false) {
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_domtree = new DOMDocument('1.0', 'UTF-8');
        $this->_domtree->formatOutput = true;
        if ($cove === true) {
            $this->_envelope = $this->_domtree->createElementNS('http://schemas.xmlsoap.org/soap/envelope/', 'soapenv:Envelope');
            $this->_envelope->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:oxml', "http://www.ventanillaunica.gob.mx/cove/ws/oxml/");
            $this->_domtree->appendChild($this->_envelope);
        } elseif ($edoc === true) {
            $this->_envelope = $this->_domtree->createElementNS('http://schemas.xmlsoap.org/soap/envelope/', 'soapenv:Envelope');
            $this->_envelope->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:dig', "http://www.ventanillaunica.gob.mx/aga/digitalizar/ws/oxml/DigitalizarDocumento");
            $this->_envelope->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:res', "http://www.ventanillaunica.gob.mx/common/ws/oxml/respuesta");
            $this->_domtree->appendChild($this->_envelope);
        }
        $this->_body = $this->_domtree->createElement('soapenv:Body');
        $this->_header = $this->_domtree->createElement('soapenv:Header');
        $this->_envelope->appendChild($this->_header);
        $this->_envelope->appendChild($this->_body);
        $this->_xsl = file_get_contents(APPLICATION_PATH . "/../library/Cove02.xsl");
    }

    /**
     * 
     * @param array $data
     * @param array $hideCredentials
     * @return string
     * @throws Exception
     */
    public function xmlCove($data, $hideCredentials = false, $save = true) {
        try {
            $this->_array = $data;
            if ($hideCredentials === false) {
                $this->_credenciales();
            }
            $this->_service = $this->_domtree->createElement('oxml:solicitarRecibirCoveServicio');
            $this->_body->appendChild($this->_service);
            $this->_comprobante = $this->_domtree->createElement('oxml:comprobantes');
            $this->_firmaElectronica = $this->_domtree->createElement("oxml:firmaElectronica");
            $this->_comprobante->appendChild($this->_firmaElectronica);
            $this->_service->appendChild($this->_comprobante);
            $this->_generalesCove();
            $this->_razonSocialDomicilio("emisor");
            $this->_razonSocialDomicilio("destinatario");
            $this->_mercancias();
            $this->_cadenaOriginal();
            if ($save == true) {
                $this->_guardarArchivoXml();
            }
            return (string) $this->_domtree->saveXML();
        } catch (Exception $ex) {
            throw new Exception("Zend Exception found on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    protected function _guardarArchivoXml() {
        $misc = new OAQ_Misc();
        if (APPLICATION_ENV == "production") {
            $misc->set_baseDir($this->_appconfig->getParam("expdest"));
        } else {
            $misc->set_baseDir("D:\\xampp\\tmp\\expedientes");
        }
        $directory = $misc->nuevoDirectorioExpediente($this->_patente, $this->_aduana, $misc->trimUpper($this->_referencia)); 
        $this->_filename = $directory . DIRECTORY_SEPARATOR . $this->_getXmlFilename();
        if (file_exists($this->_filename)) {
            unlink($this->_filename);
        }
        $this->_domtree->save($this->_filename);
    }
    
    protected function _guardarArchivoXmlConsulta() {
        $misc = new OAQ_Misc();
        $misc->set_baseDir($this->_appconfig->getParam("expdest"));
        $directory = $misc->nuevoDirectorioExpediente($this->_patente, $this->_aduana, $misc->trimUpper($this->_referencia));
        $filepath = $directory . DIRECTORY_SEPARATOR . "COVE_CONSULTA_.xml";
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        $this->_domtree->save($filepath);
    }
    
    protected function _cleanFilename($filename) {
        $misc = new OAQ_Misc();
        return $misc->formatFilename($filename);
    }

    public function getXmlFilename() {
        if (isset($this->_patente) && isset($this->_aduana) && isset($this->_pedimento) && isset($this->_referencia)) {
            return $this->_cleanFilename('COVE_' . $this->_aduana . '-' . $this->_patente . '-' . $this->_pedimento . '_' . $this->_referencia . '_' . $this->_numeroFacturaOriginal . '.xml');
        } else {
            return 'COVE_' . sha1(microtime()) . '.xml';
        }
    }

    protected function _getXmlFilename() {
        if (isset($this->_patente) && isset($this->_aduana) && isset($this->_pedimento) && isset($this->_referencia)) {
            return $this->_cleanFilename('COVE_' . $this->_aduana . '-' . $this->_patente . '-' . $this->_pedimento . '_' . $this->_referencia . '_' . $this->_numeroFacturaOriginal . '.xml');
        } else {
            return 'COVE_' . sha1(microtime()) . '.xml';
        }
    }

    public function xmlEdocument($data, $hideCredentials = false) {
        try {
            $this->_array = $data;
            if ($hideCredentials === false) {
                $this->_credenciales();
            }
            $this->_service = $this->_domtree->createElement('dig:registroDigitalizarDocumentoServiceRequest');
            $this->_service->appendChild($this->_domtree->createElement("dig:correoElectronico", $this->_array["archivo"]["correoElectronico"]));
            $this->_document = $this->_domtree->createElement('dig:documento');
            $this->_service->appendChild($this->_document);
            $this->_request = $this->_domtree->createElement("dig:peticionBase");
            $this->_service->appendChild($this->_request);
            $this->_firmaElectronica = $this->_domtree->createElement("res:firmaElectronica");
            $this->_request->appendChild($this->_firmaElectronica);
            $this->_body->appendChild($this->_service);
            $this->_documento();
            $this->_cadenaOriginalEdocument();
            if (APPLICATION_ENV == "production") {
                $this->_domtree->save("/tmp/vucem" . DIRECTORY_SEPARATOR . "edoc_" . sha1(microtime()) . ".xml");
            } else {
                $this->_domtree->save("D:\\wamp64\\tmp\\edocs" . DIRECTORY_SEPARATOR . "edoc_" . sha1(microtime()) . ".xml");
            }
            return (string) $this->_domtree->saveXML();
        } catch (Exception $ex) {
            throw new Exception("Zend Exception found on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function consultaEstatusOperacionCove($data, $hideCredentials = false) {
        try {
            $this->_array = $data;
            if ($hideCredentials === false) {
                $this->_credenciales();
            }
            $this->_service = $this->_domtree->createElement("oxml:solicitarConsultarRespuestaCoveServicio");
            $this->_service->appendChild($this->_domtree->createElement("oxml:numeroOperacion", $this->_array["consulta"]["operacion"]));
            $this->_body->appendChild($this->_service);
            $this->_firmaElectronica = $this->_domtree->createElement("oxml:firmaElectronica");
            $this->_service->appendChild($this->_firmaElectronica);
            $this->_cadena = "|{$this->_array["consulta"]["operacion"]}|{$this->_array["usuario"]["username"]}|";
            $this->_cadenaOriginalManual();
            $this->_guardarArchivoXmlConsulta();
            return (string) $this->_domtree->saveXML();
        } catch (Exception $ex) {
            throw new Exception("Zend Exception found on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function consultaEstatusOperacionEdocument($data, $hideCredentials = false) {
        try {
            $this->_array = $data;
            if ($hideCredentials === false) {
                $this->_credenciales();
            }
            $this->_service = $this->_domtree->createElement("dig:consultaDigitalizarDocumentoServiceRequest");
            $this->_service->appendChild($this->_domtree->createElement("dig:numeroOperacion", $this->_array["consulta"]["operacion"]));
            $this->_body->appendChild($this->_service);
            $peticionBase = $this->_domtree->createElement("dig:peticionBase");
            $this->_service->appendChild($peticionBase);
            $this->_firmaElectronica = $this->_domtree->createElement("res:firmaElectronica");
            $peticionBase->appendChild($this->_firmaElectronica);
            $this->_cadena = "|{$this->_array["usuario"]["username"]}|{$this->_array["consulta"]["operacion"]}|";
            $this->_cadenaOriginalManual("res");
            return (string) $this->_domtree->saveXML();
        } catch (Exception $ex) {
            throw new Exception("Zend Exception found on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    /**
     * 
     * @return type
     */
    protected function _documento() {
        try {
            $this->_document->appendChild($this->_domtree->createElement("dig:idTipoDocumento", $this->_array["archivo"]["idTipoDocumento"]));
            $this->_document->appendChild($this->_domtree->createElement("dig:nombreDocumento", $this->_array["archivo"]["nombreDocumento"]));
            $this->_document->appendChild($this->_domtree->createElement("dig:rfcConsulta", $this->_array["archivo"]["rfcConsulta"]));
            $this->_document->appendChild($this->_domtree->createElement("dig:archivo", $this->_array["archivo"]["archivo"]));
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     * 
     * @param string $cadena
     * @return type
     */
    protected function _firma($cadena) {
        try {
            $firma = '';
            if ($this->_array["usuario"]["new"] === true) {
                openssl_sign(html_entity_decode($cadena), $firma, $this->_array["usuario"]["key"], OPENSSL_ALGO_SHA256);
            } else {
                openssl_sign(html_entity_decode($cadena), $firma, $this->_array["usuario"]["key"]);
            }
            return base64_encode($firma);
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }
    
    public function firmar($sello, $cadena) {
        try {
            $key = openssl_get_privatekey(base64_decode($sello["spem"]), $sello["spem_pswd"]);
            $firma = '';
            if ($sello["sha"] == 'sha256') {
                openssl_sign(html_entity_decode($cadena), $firma, $key, OPENSSL_ALGO_SHA256);
            } else {
                openssl_sign(html_entity_decode($cadena), $firma, $key);
            }
            return base64_encode($firma);
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     * 
     * @return type
     */
    protected function _cadenaOriginalEdocument() {
        try {
            $cadena = "|{$this->_array["usuario"]["username"]}|{$this->_array["archivo"]["correoElectronico"]}|{$this->_array["archivo"]["idTipoDocumento"]}|{$this->_array["archivo"]["nombreDocumento"]}|{$this->_array["archivo"]["rfcConsulta"]}|{$this->_array["archivo"]["hash"]}|";
            $this->_firmaElectronica->appendChild($this->_domtree->createElement("res:certificado", $this->_array["usuario"]["certificado"]));
            $this->_firmaElectronica->appendChild($this->_domtree->createElement("res:cadenaOriginal", $cadena));
            $this->_firmaElectronica->appendChild($this->_domtree->createElement("res:firma", $this->_firma($cadena)));
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     * 
     * @return type
     */
    protected function _cadenaOriginal() {
        try {
            $xml = str_replace(array('oxml:', 'wsse:', 'soapenv:', 'dig:', 'res:', 'xmlns:'), '', $this->_domtree->saveXML());
            $xslt = new XSLTProcessor();
            $xslt->importStylesheet(new SimpleXMLElement($this->_xsl));
            $cadena = rtrim(ltrim(str_replace(array('<html>', '</html>', '<br>', "\r\n", "\r", "\n", 'Cadena Orignal :'), '', $xslt->transformToXml(new SimpleXMLElement($xml)))));
            if (isset($cadena)) {
                $this->_firmaElectronica->appendChild($this->_domtree->createElement("oxml:certificado", $this->_array["usuario"]["certificado"]));
                $this->_firmaElectronica->appendChild($this->_domtree->createElement("oxml:cadenaOriginal", $cadena));
                $this->_firmaElectronica->appendChild($this->_domtree->createElement("oxml:firma", $this->_firma(utf8_decode($cadena))));
                return true;
            }
            return false;
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }
    
    protected function _cadenaOriginalManual($namespace = null) {
        try {
            if (!isset($namespace)) {
                $namespace = "oxml";
            }
            $this->_firmaElectronica->appendChild($this->_domtree->createElement("{$namespace}:certificado", $this->_array["usuario"]["certificado"]));
            $this->_firmaElectronica->appendChild($this->_domtree->createElement("{$namespace}:cadenaOriginal", $this->_cadena));
            $this->_firmaElectronica->appendChild($this->_domtree->createElement("{$namespace}:firma", $this->_firma($this->_cadena)));
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     * 
     * @return type
     */
    protected function _credenciales() {
        try {
            if (!isset($this->_array["usuario"]["username"])) {
                throw new Exception('Username not set.');
            }
            $security = $this->_domtree->createElement("wsse:Security");
            $security->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:wsse', "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd");
            $username = $this->_domtree->createElement("wsse:UsernameToken");
            $username->appendChild($this->_domtree->createElement("wsse:Username", $this->_array["usuario"]["username"]));
            $username->appendChild($this->_domtree->createElement("wsse:Password", $this->_array["usuario"]["password"]));
            $security->appendChild($username);
            $this->_header->appendChild($security);
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     * 
     */
    protected function _generalesCove() {
        $keys = array("tipoOperacion", "patenteAduanal", "fechaExpedicion", "tipoFigura", "correoElectronico", "observaciones", "numeroFacturaOriginal");
        foreach ($keys as $key) {
            if (isset($this->_array["trafico"][$key])) {
                $this->_comprobante->appendChild($this->_domtree->createElement("oxml:{$key}", $this->_encodeChar($this->_array["trafico"][$key])));
            }
            if (isset($this->_array["trafico"]["numeroFacturaOriginal"])) {
                $this->_numeroFacturaOriginal = $this->_array["trafico"]["numeroFacturaOriginal"];
            }
        }
        if(isset($this->_array["trafico"]["coveAdenda"]) && $this->_array["trafico"]["coveAdenda"] !== null) {
            $this->_comprobante->appendChild($this->_domtree->createElement("oxml:e-document", $this->_array["trafico"]["coveAdenda"]));
        }
        $factura = $this->_domtree->createElement('oxml:factura');
        if(isset($this->_array["trafico"]["certificadoOrigen"]) && $this->_array["trafico"]["certificadoOrigen"] == 1) {
            $factura->appendChild($this->_domtree->createElement("oxml:certificadoOrigen", 1));
            $factura->appendChild($this->_domtree->createElement("oxml:numeroExportadorAutorizado", $this->_array["trafico"]["numExportador"]));
        } else {
            $factura->appendChild($this->_domtree->createElement("oxml:certificadoOrigen", 0));
        }
        $factura->appendChild($this->_domtree->createElement("oxml:subdivision", (isset($this->_array["trafico"]["subdivision"]) && $this->_array["trafico"]["subdivision"] == 1) ? 1 : 0));
        if (isset($this->_array["trafico"]["rfcConsulta"])) {
            if (is_array($this->_array["trafico"]["rfcConsulta"])) {
                foreach ($this->_array["trafico"]["rfcConsulta"] as $rfc) {
                    $this->_comprobante->appendChild($this->_domtree->createElement("oxml:rfcConsulta", $this->_encodeChar($rfc)));
                }
            } else {
                $this->_comprobante->appendChild($this->_domtree->createElement("oxml:rfcConsulta", $this->_encodeChar($this->_array["trafico"]["rfcConsulta"])));
            }
        }
        $this->_comprobante->appendChild($factura);
    }

    /**
     * 
     * @param type $namespace
     */
    protected function _razonSocialDomicilio($namespace) {
        if (isset($this->_array[$namespace])) {
            $element = $this->_domtree->createElement("oxml:{$namespace}");
            $iden = array("tipoIdentificador", "identificacion", "nombre");
            foreach ($iden as $ide) {
                if (isset($this->_array[$namespace][$ide])) {
                }
                    $element->appendChild($this->_domtree->createElement("oxml:{$ide}", $this->_encodeChar($this->_array[$namespace][$ide])));
            }
            $domicilio = $this->_domtree->createElement("oxml:domicilio");
            $domi = array("calle", "numeroExterior", "numeroInterior", "colonia", "localidad", "municipio", "entidadFederativa", "codigoPostal", "pais");
            foreach ($domi as $dom) {
                if (isset($this->_array[$namespace][$dom])) {
                    if (trim($this->_array[$namespace][$dom]) != "") {
                        $domicilio->appendChild($this->_domtree->createElement("oxml:{$dom}", $this->_encodeChar($this->_array[$namespace][$dom])));
                    }
                }
            }
            $element->appendChild($domicilio);
            $this->_comprobante->appendChild($element);
        }
    }

    /**
     * 
     */
    protected function _mercancias() {
        if (isset($this->_array["mercancias"])) {
            foreach ($this->_array["mercancias"] as $item) {
                $element = $this->_domtree->createElement("oxml:mercancias");
                if (isset($item["descripcionGenerica"])) {
                    $element->appendChild($this->_domtree->createElement("oxml:descripcionGenerica", $this->_encodeChar($item["descripcionGenerica"])));
                }
                if (isset($item["numParte"])) {
                    $element->appendChild($this->_domtree->createElement("oxml:numParte", $this->_encodeChar($item["numParte"])));
                }
                if (isset($item["secuencial"])) {
                    $element->appendChild($this->_domtree->createElement("oxml:secuencial", $this->_encodeChar($item["secuencial"])));
                }
                if (isset($item["claveUnidadMedida"])) {
                    $element->appendChild($this->_domtree->createElement("oxml:claveUnidadMedida", $this->_encodeChar($item["claveUnidadMedida"])));
                }
                if (isset($item["tipoMoneda"])) {
                    $element->appendChild($this->_domtree->createElement("oxml:tipoMoneda", $this->_encodeChar($item["tipoMoneda"])));
                }
                if (isset($item["cantidad"])) {
                    $element->appendChild($this->_domtree->createElement("oxml:cantidad", $this->_encodeChar($item["cantidad"])));
                }
                if (isset($item["valorUnitario"])) {
                    $element->appendChild($this->_domtree->createElement("oxml:valorUnitario", $this->_encodeChar($item["valorUnitario"])));
                }
                if (isset($item["valorTotal"])) {
                    $element->appendChild($this->_domtree->createElement("oxml:valorTotal", $this->_encodeChar($item["valorTotal"])));
                }
                if (isset($item["valorDolares"])) {
                    $element->appendChild($this->_domtree->createElement("oxml:valorDolares", $this->_encodeChar($item["valorDolares"])));
                }
                if (isset($item["marca"]) || isset($item["modelo"]) || isset($item["subModelo"]) || isset($item["numeroSerie"])) {
                    $descripcionesEspecificas = $this->_domtree->createElement("oxml:descripcionesEspecificas");
                    if (isset($item["marca"])) {
                        $descripcionesEspecificas->appendChild($this->_domtree->createElement("oxml:marca", $this->_encodeChar($item["marca"])));
                    }
                    if (isset($item["modelo"])) {
                        $descripcionesEspecificas->appendChild($this->_domtree->createElement("oxml:modelo", $this->_encodeChar($item["modelo"])));
                    }
                    if (isset($item["subModelo"])) {
                        $descripcionesEspecificas->appendChild($this->_domtree->createElement("oxml:subModelo", $this->_encodeChar($item["subModelo"])));
                    }
                    if (isset($item["numeroSerie"])) {
                        $descripcionesEspecificas->appendChild($this->_domtree->createElement("oxml:numeroSerie", $this->_encodeChar($item["numeroSerie"])));
                    }
                    $element->appendChild($descripcionesEspecificas);
                }
                $this->_comprobante->appendChild($element);
            }
        }
    }
    
    protected function _descripcionEspecificas() {
        
    }

    /**
     * 
     * @param type $value
     * @return type
     */
    protected function _encodeChar($value) {
        return str_replace('&', '&amp;', trim($value));
    }

    /**
     * Regresa valores Valores permitidos [0-TAX_ID, 1-RFC, 2-CURP,3-SIN_TAX_ID]
     * 
     * @param string $rfc
     * @param string $pais
     * @return string
     */
    public function tipoIdentificador($rfc, $pais) {
        $regRfc = '/^[A-Z]{3,4}([0-9]{2})(1[0-2]|0[1-9])([0-3][0-9])([A-Z0-9]{3,4})$/';
        if (($pais == 'MEX' || $pais == 'MEXICO') && preg_match($regRfc, str_replace(' ', '', trim($rfc)))) {
            if ($rfc != 'EXTR920901TS4') {
                if (strlen($rfc) > 12) {
                    return '2';
                }
                return '1';
            } else {
                return '0';
            }
        }
        if (($pais == 'MEX' || $pais == 'MEXICO') && !preg_match($regRfc, str_replace(' ', '', trim($rfc)))) {
            return '0';
        }
        if ($pais != 'MEX' && trim($rfc) != '') {
            return '0';
        }
        if ($pais != 'MEX' && trim($rfc) == '') {
            return '3';
        }
    }

    protected function _consumirServicioCove($xml, $service) {
        try {
            $headers = array(
                "Content-type: text/xml; charset=UTF-8",
                "Accept: text/xml",
                "Cache-Control: no-cache",
                "Pragma: no-cache",
                "Content-length: " . strlen($xml) . "");
            $soap = curl_init();
            curl_setopt($soap, CURLOPT_URL, "https://www.ventanillaunica.gob.mx/ventanilla/" . $service);
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
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());            
        }
    }

}
