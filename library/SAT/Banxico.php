<?php

/**
 * Description of Facturas
 *
 * @author Jaime
 */
class SAT_Banxico {

    protected $_config;
    protected $_url;

    function __construct() {
        $this->_url = "http://www.banxico.org.mx/DgieWSWeb/DgieWS?WSDL";
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
    }

    public function banxicoToArray($xml) {
        return $xmlClean = simplexml_load_string($xml);
        unset($clean);
        return @json_decode(@json_encode($xmlClean), 1);
    }

    public function tipoDeCambioXml() {
        return "<soapenv:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:ws=\"http://ws.dgie.banxico.org.mx\">"
                . "<soapenv:Header/>"
                . "<soapenv:Body>"
                . "<ws:tiposDeCambioBanxico soapenv:encodingStyle=\"http://schemas.xmlsoap.org/soap/encoding/\"/>"
                . "</soapenv:Body>"
                . "</soapenv:Envelope>";
    }

    public function consultaTipoCambio($xml) {
        try {
            $headers = array(
                "Content-type: text/xml; charset=UTF-8",
                "Accept: text/xml",
                "Cache-Control: no-cache",
                "Pragma: no-cache",
                "SOAPAction: \"\"",
                "Host: www.banxico.org.mx",
                "Content-length: " . strlen($xml) . "");
            $soap = curl_init();
            curl_setopt($soap, CURLOPT_URL, $this->_url);
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
            throw new Exception("<b>Exception on " . __METHOD__ . "</b>" . $e->getMessage());
        }
    }

    public function scabber($url) {
        try {
            $ch = curl_init();
            $timeout = 5;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $data = curl_exec($ch);
            curl_close($ch);
            return $data;
        } catch (Exception $e) {
            throw new Exception("<b>Exception on " . __METHOD__ . "</b>" . $e->getMessage());
        }
    }

    public function insideTags($tag, $content) {
        preg_match("/<" . $tag . "[^>]*>(.*?)<\/$tag>/si", $content, $matches);
        return str_replace(array('bm:'), '', trim($matches[1]));
    }
    
    public function insideAllTags($tag, $content) {
        preg_match("/<" . $tag . "[^>]*>(.*?)<\/$tag>/si", $content, $matches);
        return $matches;
    }

    public function tagsToArray($string) {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\">" . $string . "</soapenv:Envelope>";
        $xmlClean = simplexml_load_string($xml);
        return @json_decode(@json_encode($xmlClean), 1);
    }

}
