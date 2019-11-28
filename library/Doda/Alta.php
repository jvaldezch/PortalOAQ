<?php

/**
 * Description of Vucem_Xml
 * 
 * Esta clase conforma arreglos en archivos XML requeridos por la Ventanilla Única de Comercio Exterior (VUCEM)
 *
 * @author Jaime E. Valdez jvaldezch at gmail
 */
class Doda_Alta {

    protected $_domtree;
    protected $_envelope;
    protected $_header;
    protected $_body;
    protected $_alta;
    protected $_dodas;
    protected $_doda;
    protected $_pedimentos;
    protected $_sellado;
    protected $_credenciales;
    protected $_dir;
    
    function get_dir() {
        return $this->_dir;
    }

    function set_dir($_dir) {
        $this->_dir = $_dir;
    }

    function __construct() {
        $this->_domtree = new DOMDocument('1.0', 'UTF-8');
        $this->_domtree->preserveWhiteSpace = false;
        $this->_domtree->formatOutput = true;
    }

    public function pedimento() {
        $this->_envelope = $this->_domtree->createElementNS('http://schemas.xmlsoap.org/soap/envelope/', 'soapenv:Envelope');
        $this->_envelope->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:soapenv', 'http://schemas.xmlsoap.org/soap/envelope/');
        $this->_envelope->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:mat', 'http://impl.service.qrws.ce.siat.sat.gob.mx/siatbus/matce');
        $this->_envelope->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xd', 'http://www.w3.org/2000/09/xmldsig#');
        $this->_domtree->appendChild($this->_envelope);

        $this->_header = $this->_domtree->createElement("soapenv:Header");
        $this->_body = $this->_domtree->createElement("soapenv:Body");
        $this->_alta = $this->_domtree->createElement("mat:altaDoda");
        $this->_dodas = $this->_domtree->createElement("dodas");
        $this->_doda = $this->_domtree->createElement("doda");
        $this->_pedimentos = $this->_domtree->createElement("pedimentos");
        $this->_sellado = $this->_domtree->createElement("sellado");
        $this->_credenciales();
        $this->_datosGenerales();
        $this->_pedimento();
        $this->_sellado();
        //$this->_signature = $this->_domtree->createElement("Signature");
        //$this->_signature->setAttribute("xmlns", "http://www.w3.org/2000/09/xmldsig#");
        //$this->_signature();
        $this->_doda->appendChild($this->_pedimentos);
        $this->_doda->appendChild($this->_sellado);
        $this->_dodas->appendChild($this->_doda);
        //$this->_dodas->appendChild($this->_signature);
        $this->_alta->appendChild($this->_dodas);
        $this->_body->appendChild($this->_alta);
        $this->_envelope->appendChild($this->_header);
        $this->_envelope->appendChild($this->_body);
    }

    protected function _signature() {
        $signedInfo = $this->_domtree->createElement("SignedInfo");
        $canonicalizationMethod = $this->_domtree->createElement("CanonicalizationMethod");
        $canonicalizationMethod->setAttribute("Algorithm", "http://www.w3.org/TR/2001/REC-xml-c14n-20010315#WithComments");
        $signedInfo->appendChild($canonicalizationMethod);

        $signatureMethod = $this->_domtree->createElement("SignatureMethod");
        $signatureMethod->setAttribute("Algorithm", "http://www.w3.org/2000/09/xmldsig#rsa-sha1");
        $signedInfo->appendChild($signatureMethod);

        $reference = $this->_domtree->createElement("Reference");
        $reference->setAttribute("URI", "");

        $transforms = $this->_domtree->createElement("Transforms");
        $transform = $this->_domtree->createElement("Transform");
        $transform->setAttribute("Algorithm", "http://www.w3.org/TR/1999/REC-xpath-19991116");
        $transform->appendChild($this->_domtree->createElement("XPath", "/dodas"));

        $transform2 = $this->_domtree->createElement("Transform");
        $transform2->setAttribute("Algorithm", "http://www.w3.org/2000/09/xmldsig#enveloped-signature");

        $transforms->appendChild($transform);
        $transforms->appendChild($transform2);
        $reference->appendChild($transforms);

        $digestMethod = $this->_domtree->createElement("DigestMethod");
        $digestMethod->setAttribute("Algorithm", "http://www.w3.org/2001/04/xmlenc#sha256");
        $reference->appendChild($digestMethod);
        $reference->appendChild($this->_domtree->createElement("DigestValue", "2Djwp9wOeaBQzaYPDVAG7/fRqKXjN8vxvpwfIa0PCTE="));

        $signedInfo->appendChild($reference);

        $this->_signature->appendChild($signedInfo);
        $this->_signature->appendChild($this->_domtree->createElement("SignatureValue", "L4kxYHkFg1ArPoy3MFDPcYytTQQaXtFu9UUCxi7FsjQIycUcKjW1UtzHbt8xxpvmBDQNgueK/kDa3NlBUrTsSvbCBKCyfIUkBPSln8DtecXtcRU/u0X8vIzBOJTqb9gC51J38u8NhdneaApo0WxzbWmcUgevkbo3AaqyFQpzBX0="));

        $keyInfo = $this->_domtree->createElement("KeyInfo");
        $keyValue = $this->_domtree->createElement("KeyValue");
        $rsaKeyValue = $this->_domtree->createElement("RSAKeyValue");
        $rsaKeyValue->appendChild($this->_domtree->createElement("Modulus", "yapCI4cdFFHP+pJ8LtCH8TxWpaPmNwhFMy2/K6HmOEbyGYD+J2F3YcMaDCUnaG22t3V90p4bjEDQvhu+QLNL7JOQFs8pEOfRWL0OP0YglwZ0UnuK9umGV4hGxlpuj9q/JT1hJMhYJgIXwjNd1vw0mDeXmrJzBQQNX0ShZd8T4AM="));
        $rsaKeyValue->appendChild($this->_domtree->createElement("Exponent", "AQAB"));
        $keyValue->appendChild($rsaKeyValue);
        $keyInfo->appendChild($keyValue);
        $this->_signature->appendChild($keyInfo);
    }

    protected function _sellado() {
        $this->_sellado->appendChild($this->_domtree->createElement("cadenaOriginalAA", "||800|5013|1|7051209|298312|5451212||2017-02-27 15:44:22||"));
        $this->_sellado->appendChild($this->_domtree->createElement("firmado", "fwz05hoi5EHPBY7qTHoHNaPp9lctsbIo3riwiKn1PWO/aur5FVFGfgd6IIrA7rp6mD5eWg/hTO/tCKOuNvjas/jileaYLMTiZqFJZGB6NRDbTkiObtBwFFeG1IBl7tKJ77EeZTfHFhJC40kJZHlW/VmaH9pQQJdFmEHXY9uVf28="));
        $this->_sellado->appendChild($this->_domtree->createElement("serie", "20001000000200003162"));
    }

    protected function _pedimento() {
        $pedimento = $this->_domtree->createElement("pedimento");
        $pedimento->appendChild($this->_domtree->createElement("patenteAutorizacion", "5013"));
        $pedimento->appendChild($this->_domtree->createElement("documento", "7051209"));
        $pedimento->appendChild($this->_domtree->createElement("numeroRemesa", "0")); // opcional
        $pedimento->appendChild($this->_domtree->createElement("dtaNiu", "0"));
        $pedimento->appendChild($this->_domtree->createElement("importeDifDolares", "0"));
        $pedimento->appendChild($this->_domtree->createElement("importeEfectivoDolares", "0"));
        $pedimento->appendChild($this->_domtree->createElement("umc", "0"));
        $pedimento->appendChild($this->_domtree->createElement("articulo7", "7"));
        $cove = $this->_domtree->createElement("cove");
        $pedimento->appendChild($cove);
        $this->_pedimentos->appendChild($pedimento);
    }

    protected function _datosGenerales() {
        $datosGenerales = $this->_domtree->createElement("datosGenerales");
        $datosGenerales->appendChild($this->_domtree->createElement("aduana", "800"));
        $datosGenerales->appendChild($this->_domtree->createElement("seccion", "800"));
        $datosGenerales->appendChild($this->_domtree->createElement("caat", "0669"));
        $datosGenerales->appendChild($this->_domtree->createElement("idTransporte", "0669"));
        $datosGenerales->appendChild($this->_domtree->createElement("tipoOperacion", "1"));
        $this->_doda->appendChild($datosGenerales);
    }

    protected function _credenciales() {
        $credenciales = $this->_domtree->createElement("credenciales");
        $ciec = $this->_domtree->createElement("ciec");
        $ciec->appendChild($this->_domtree->createElement("Usuario", "HEPA270920PX8"));
        $ciec->appendChild($this->_domtree->createElement("Contrasena", "12345678a"));
        $credenciales->appendChild($ciec);
        $this->_dodas->appendChild($credenciales);
    }

    public function viewXml() {
        try {
            $output = $this->_domtree->saveXML();
            Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoRender(true);
            Zend_Layout::getMvcInstance()->disableLayout();
            $this->_response->setHeader('Content-Type', 'text/xml; charset=utf-8')
                    ->setBody($output);
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getXml() {
        try {
            return (string) $this->_domtree->saveXML();
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function saveToDisk($name = null) {
        try {
            if ($this->get_dir() !== null) {
                if (file_exists($this->get_dir())) {                    
                    $this->_domtree->save($this->get_dir() . DIRECTORY_SEPARATOR . $name);
                } else {
                    throw new Exception("Directory do not exists.");
                }
            } else {
                throw new Exception("Directory is not set.");
            }
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
