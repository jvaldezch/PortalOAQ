<?php

/**
 * Description of Vucem_Xml
 * 
 * Esta clase conforma arreglos en archivos XML requeridos por la Ventanilla Única de Comercio Exterior (VUCEM)
 *
 * @author Jaime E. Valdez jvaldezch at gmail
 */
class Doda_Respuestas {

    protected $_debug = false;

    function __construct() {
    }
    
    function setDebug($debug) {
        $this->_debug = $debug;
    }
    
    protected function _xmlstrToArray($xmlstr) {
        $doc = new DOMDocument();
        $doc->loadXML($xmlstr);
        return $this->_domnodeToArray($doc->documentElement);
    }
    
    protected function _replace($string) {
        try {
            return str_replace(array("S:", "soapenv:", "oxml:", "con:", "wsse:", "wsu:", "NS1:", "NS2:", "NS3:", "NS4:", "env:", "ns3:", "ns2:", "ns5:", "ns4:", "ns6:", "ns7:", "ns8:", "ns9:"), "", $string);
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function respuestaAltaPedimento($xmlstr) {
        try {
            $array = $this->_xmlstrToArray($this->_replace($xmlstr));
            $res = null;
            if ($this->_debug == true) {
                Zend_Debug::dump($array);
            }
            if (isset($array["Body"])) {
                if (isset($array["Body"]["Fault"])) {
                    $fault = $array["Body"]["Fault"];
                    $res["success"] = false;
                    $res["message"] = urlencode(utf8_decode($fault["detail"]));
                }
            }
            if (isset($res)) {
                return $res;
            }
            return;
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

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
