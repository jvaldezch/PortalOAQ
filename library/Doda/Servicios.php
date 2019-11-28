<?php

/**
 * Description of Vucem_Xml
 * 
 * Esta clase conforma arreglos en archivos XML requeridos por la Ventanilla Única de Comercio Exterior (VUCEM)
 *
 * @author Jaime E. Valdez jvaldezch at gmail
 */
class Doda_Servicios {

    protected $_altaPedimento = "https://200.57.3.82:2443/AdministradorQr/WebServiceDodaPort";
    protected $_xml;
    protected $_response;

    function setXml($xml) {
        $this->_xml = $xml;
    }

    function getResponse() {
        return $this->_response;
    }

    function __construct() {
    }

    public function altaPedimento() {
        try {
            $headers = array(
                "Content-type: text/xml; charset=UTF-8",
                "Accept: text/xml",
                "Cache-Control: no-cache",
                "Pragma: no-cache",
                "Content-length: " . strlen($this->_xml) . "");
            $soap = curl_init();
            curl_setopt($soap, CURLOPT_URL, $this->_altaPedimento);
            curl_setopt($soap, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($soap, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($soap, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($soap, CURLOPT_CONNECTTIMEOUT, 0);
            curl_setopt($soap, CURLOPT_TIMEOUT, 400);
            curl_setopt($soap, CURLOPT_POST, true);
            curl_setopt($soap, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($soap, CURLOPT_POSTFIELDS, $this->_xml);
            $result = curl_exec($soap);
            curl_close($soap);
            $this->_response = $result;
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
