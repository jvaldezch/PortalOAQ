<?php

/**
 * Description of Vucem_Xml
 * 
 * Esta clase conforma arreglos en archivos XML requeridos por la Ventanilla Única de Comercio Exterior (VUCEM)
 *
 * @author Jaime E. Valdez jvaldezch at gmail
 */
class OAQ_XmlCoves
{

    protected $_dir;
    protected $_data;
    protected $_cove;
    protected $_domtree;
    protected $_envelope;
    protected $_cadena;
    protected $_body;
    protected $_header;
    protected $_xml;
    protected $_response;

    function __construct($data, $cove, $cadena)
    {
        $this->_data = $data;
        $this->_cove = $cove;
        $this->_cadena = $cadena;
    }

    public function xmlConsultaEdocument()
    {
        $this->_domtree = new DOMDocument("1.0", "UTF-8");
        $this->_domtree->formatOutput = true;
        $this->_envelope = $this->_domtree->createElementNS("http://schemas.xmlsoap.org/soap/envelope/", "soapenv:Envelope");

        $this->_envelope->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:con", "http://www.ventanillaunica.gob.mx/ConsultarEdocument/");
        $this->_envelope->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:oxml", "http://www.ventanillaunica.gob.mx/cove/ws/oxml/");

        $this->_domtree->appendChild($this->_envelope);

        $this->_body = $this->_domtree->createElement("soapenv:Body");
        $this->_header = $this->_domtree->createElement("soapenv:Header");

        $security = $this->_domtree->createElement("wsse:Security");
        $security->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:wsse', "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd");

        $username = $this->_domtree->createElement("wsse:UsernameToken");
        $username->appendChild($this->_domtree->createElement("wsse:Username", $this->_data["username"]));
        $username->appendChild($this->_domtree->createElement("wsse:Password", $this->_data["password"]));

        $security->appendChild($username);

        $this->_header->appendChild($security);

        $this->_envelope->appendChild($this->_header);

        $con = $this->_domtree->createElement("con:ConsultarEdocumentRequest");
        $req = $this->_domtree->createElement("con:request");
        $firma = $this->_domtree->createElement("con:firmaElectronica");
        $busq = $this->_domtree->createElement("con:criterioBusqueda");

        $cer = $this->_domtree->createElement("oxml:certificado", $this->_data['certificado']);
        $cad = $this->_domtree->createElement("oxml:cadenaOriginal", $this->_cadena);
        $fir = $this->_domtree->createElement("oxml:firma", $this->_data['firma']);

        $firma->appendChild($cer);
        $firma->appendChild($cad);
        $firma->appendChild($fir);

        $edoc = $this->_domtree->createElement("con:eDocument", $this->_cove);
        $busq->appendChild($edoc);

        $req->appendChild($firma);
        $req->appendChild($busq);
        $con->appendChild($req);
        $this->_body->appendChild($con);

        $this->_envelope->appendChild($this->_body);

        $this->_xml = (string) $this->_domtree->saveXML();
    }

    public function xmlConsultaAcuseEdocument()
    {
        $this->_domtree = new DOMDocument("1.0", "UTF-8");
        $this->_domtree->formatOutput = true;
        $this->_envelope = $this->_domtree->createElementNS("http://schemas.xmlsoap.org/soap/envelope/", "soapenv:Envelope");

        $this->_envelope->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:oxml", "http://www.ventanillaunica.gob.mx/consulta/acuses/oxml");

        $this->_domtree->appendChild($this->_envelope);

        $this->_body = $this->_domtree->createElement("soapenv:Body");
        $this->_header = $this->_domtree->createElement("soapenv:Header");

        $security = $this->_domtree->createElement("wsse:Security");
        $security->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:wsse', "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd");

        $username = $this->_domtree->createElement("wsse:UsernameToken");
        $username->appendChild($this->_domtree->createElement("wsse:Username", $this->_data["username"]));
        $username->appendChild($this->_domtree->createElement("wsse:Password", $this->_data["password"]));

        $security->appendChild($username);

        $this->_header->appendChild($security);

        $this->_envelope->appendChild($this->_header);

        $con = $this->_domtree->createElement("oxml:consultaAcusesPeticion");
        $req = $this->_domtree->createElement("idEdocument", $this->_cove);
        $con->appendChild($req);
        $this->_body->appendChild($con);

        $this->_envelope->appendChild($this->_body);

        $this->_xml = (string) $this->_domtree->saveXML();
    }

    public function descargaEdocument($xml)
    {
        try {

            $headers = array(
                "Content-type: text/xml; charset=UTF-8",
                "Accept: text/xml",
                "Cache-Control: no-cache",
                "Pragma: no-cache",
                "SOAPAction: \"http://www.ventanillaunica.gob.mx/cove/ws/service/ConsultarEdocument\"",
                "Content-length: " . strlen($xml) . ""
            );

            $soap = curl_init();

            curl_setopt($soap, CURLOPT_URL, "https://www.ventanillaunica.gob.mx/ventanilla/ConsultarEdocumentService");
            curl_setopt($soap, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($soap, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($soap, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($soap, CURLOPT_CONNECTTIMEOUT, 0);
            curl_setopt($soap, CURLOPT_TIMEOUT, 400);
            curl_setopt($soap, CURLOPT_POST, true);
            curl_setopt($soap, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($soap, CURLOPT_POSTFIELDS, $xml);
            $result = curl_exec($soap);
            curl_close($soap);
            $this->_response = $result;
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function _domnodeToArray($node)
    {
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

    public function xmlstrToArray($xmlstr)
    {
        if ($xmlstr != '') {
            $doc = new DOMDocument();
            $doc->loadXML($xmlstr);
            return $this->_domnodeToArray($doc->documentElement);
        }
        return;
    }

    public function replace($string)
    {
        try {
            return str_replace(array("S:", "soapenv:", "oxml:", "con:", "wsse:", "wsu:", "env:", "ns3:", "ns2:", "ns5:", "ns4:", "ns6:", "ns7:", "ns8:", "ns9:"), "", $string);
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function stringInsideTags($string, $tagname)
    {
        $pattern = "/<$tagname\b[^>]*>(.*?)<\/$tagname>/is";
        preg_match_all($pattern, $string, $matches);
        if (!empty($matches[1])) {
            return (string) $matches[1][0];
        }
        return array();
    }

    public function descargaAcuseEdocument($xml)
    {
        try {

            $headers = array(
                "Content-type: text/xml; charset=UTF-8",
                "Accept: text/xml",
                "Cache-Control: no-cache",
                "Pragma: no-cache",
                "SOAPAction: \"http://www.ventanillaunica.gob.mx/ventanilla/ConsultaAcusesService/consultarAcuseCove\"",
                "Content-length: " . strlen($xml) . ""
            );

            $soap = curl_init();

            curl_setopt($soap, CURLOPT_URL, "https://www.ventanillaunica.gob.mx/ventanilla-acuses-HA/ConsultaAcusesServiceWS");
            curl_setopt($soap, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($soap, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($soap, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($soap, CURLOPT_CONNECTTIMEOUT, 0);
            curl_setopt($soap, CURLOPT_TIMEOUT, 400);
            curl_setopt($soap, CURLOPT_POST, true);
            curl_setopt($soap, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($soap, CURLOPT_POSTFIELDS, $xml);
            $result = curl_exec($soap);
            curl_close($soap);
            $this->_response = $result;
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function analizarRespuesaAcuse($xml, $f_pdf)
    {
        $xmlstr = "<S:Envelope>" . $this->stringInsideTags($xml, "S:Envelope") . "</S:Envelope>";
        $xmlstr = $this->replace($xmlstr);
        $arr = $this->xmlstrToArray($xmlstr);

        if (isset($arr['Body'])) {
            if ($arr) {
                unset($arr['Header']);
            }
            $body = $arr['Body'];

            if (isset($body['responseConsultaAcuses'])) {
                if ($body['responseConsultaAcuses']['error'] === "false") {
                    $f = base64_decode($body['responseConsultaAcuses']['acuseDocumento']);
                    $fz = fopen($f_pdf, 'wb');
                    fwrite($fz, $f);
                    fclose($fz);

                    return true;
                }
            }
        }
    }

    public function getXml()
    {
        try {
            return (string) $this->_domtree->saveXML();
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getResponse()
    {
        try {
            return $this->_response;
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
}
