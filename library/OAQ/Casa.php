<?php
/**
 * Description of Casa
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class OAQ_Casa {
    //put your code here
    protected $_path;
    
    function __construct($path)
    {
        $this->_path = $path . DIRECTORY_SEPARATOR;
    }
    
    public function pedimentoXml()
    {
        if(file_exists($this->_path . "SAAIO_PEDIME.XML")) {
            $xml = file_get_contents($this->_path . "SAAIO_PEDIME.XML");
            $clean = str_replace(array('ns2:','S:','wsse:','ns3:','wsu:'), '', $xml);        
            $xmlClean = simplexml_load_string($clean);
            unset($clean);
            return @json_decode(@json_encode($xmlClean),1);
        }
        return null;
    }
    
    public function buscarCliente($cve)
    {
        try {
            if(file_exists($this->_path . "CTRAC_CLIENT.XML")) {
                $xml = file_get_contents($this->_path . "CTRAC_CLIENT.XML");
                $clean = str_replace(array('ns2:','S:','wsse:','ns3:','wsu:'), '', $xml);        
                $xmlClean = simplexml_load_string($clean);
                unset($clean);
                $array = @json_decode(@json_encode($xmlClean),1);
                foreach($array['renglon'] as $item) {
                    if($cve == $item['CVE_IMP']) {
                        $data = array(
                            'NOM_IMP' => $item['NOM_IMP'],
                            'RFC_IMP' => $item['RFC_IMP'],
                        );
                        return $data;
                    }
                }
                return null;
            } else {
                throw new Exception("File not found.");
            }
        } catch (Exception $e) {
            echo $e->getMessage(); die();
        }
    }
    
    public function buscarFacturas($referencia)
    {
        try {
            if(file_exists($this->_path . "SAAIO_FACTUR.XML")) {
                $xml = file_get_contents($this->_path . "SAAIO_FACTUR.XML");
                $clean = str_replace(array('ns2:','S:','wsse:','ns3:','wsu:'), '', $xml);        
                $xmlClean = simplexml_load_string($clean);
                unset($clean);
                $array = @json_decode(@json_encode($xmlClean),1);
                $data = array();
                foreach($array['renglon'] as $item) {
                    if($referencia == $item['NUM_REFE']) {
                        $data[] = array(
                            'NUM_FACT' => $item['NUM_FACT'],
                            'ICO_FACT' => $item['ICO_FACT'],
                            'CVE_PROV' => $item['CVE_PROV'],
                            'CONS_FACT' => $item['CONS_FACT'],
                            'VAL_DLLS' => $item['VAL_DLLS'],
                            'VAL_EXTR' => $item['VAL_EXTR']                           
                        );                        
                    }
                }
                if(count($data) > 0) {
                    return $data;
                }
                return null;
            } else {
                throw new Exception("File not found.");
            }
        } catch (Exception $e) {
            echo $e->getMessage(); die();
        }
    }
    
    /*public function buscarFracciones($referencia)
    {
        try {
            if(file_exists($this->_path . "SAAIO_FRACCI.XML")) {
                $xml = file_get_contents($this->_path . "SAAIO_FRACCI.XML");
                $clean = str_replace(array('ns2:','S:','wsse:','ns3:','wsu:'), '', $xml);        
                $xmlClean = simplexml_load_string($clean);
                unset($clean);
                $array = @json_decode(@json_encode($xmlClean),1);
                $data = array();
                foreach($array['renglon'] as $item) {
                    if($referencia == $item['NUM_REFE']) {
                        $data[] = array(
                            'NUM_FACT' => $item['NUM_FACT'],
                            'ICO_FACT' => $item['ICO_FACT'],
                            'CVE_PROV' => $item['CVE_PROV'],
                            'CONS_FACT' => $item['CONS_FACT']
                        );                        
                    }
                }
                if(count($data) > 0) {
                    return $data;
                }
                return null;
            } else {
                throw new Exception("File not found.");
            }
        } catch (Exception $e) {
            echo $e->getMessage(); die();
        }
    }*/
    
    public function buscarFactPartes($referencia,$factura)
    {
        try {
            if(file_exists($this->_path . "SAAIO_FACPAR.XML")) {
                $xml = file_get_contents($this->_path . "SAAIO_FACPAR.XML");
                $clean = str_replace(array('ns2:','S:','wsse:','ns3:','wsu:'), '', $xml);        
                $xmlClean = simplexml_load_string($clean);
                unset($clean);
                $array = @json_decode(@json_encode($xmlClean),1);
                $data = array();
                foreach($array['renglon'] as $item) {
                    if($referencia == $item['NUM_REFE'] && $factura == $item['CONS_FACT']) {
                        $data[] = array(
                            'NUM_PART' => $item['NUM_PART'],
                            'FRACCION' => $item['FRACCION'],
                            'PAI_ORIG' => $item['PAI_ORIG'],
                            'PAI_VEND' => $item['PAI_VEND']
                        );                        
                    }
                }
                if(count($data) > 0) {
                    return $data;
                }
                return null;
            } else {
                throw new Exception("File not found.");
            }
        } catch (Exception $e) {
            echo $e->getMessage(); die();
        }
    }
    
    
    public function buscarProveedor($cve,$ie)
    {
        try {
            if($ie == '1') {
                $filename = $this->_path . "CTRAC_PROVED.XML";
            } elseif($ie == '2') {
                $filename = $this->_path . "CTRAC_DESTIN.XML";
            }
            if(file_exists($filename)) {
                $xml = file_get_contents($filename);
                $clean = str_replace(array('ns2:','S:','wsse:','ns3:','wsu:'), '', $xml);        
                $xmlClean = simplexml_load_string($clean);
                unset($clean);
                $array = @json_decode(@json_encode($xmlClean),1);
                foreach($array['renglon'] as $item) {
                    if($cve == $item['CVE_PRO']) {
                        $data = array(
                            'CVE_PRO' => $item['CVE_PRO'],
                            'NOM_PRO' => $item['NOM_PRO']
                        );
                        return $data;
                    }
                }
                return null;
            } else {
                throw new Exception("File not found.");
            }
        } catch (Exception $e) {
            echo $e->getMessage(); die();
        }
    }
    
    public function buscarCove($referencia)
    {
        try {
            if(file_exists($this->_path . "SAAIO_COVE.XML")) {
                $xml = file_get_contents($this->_path . "SAAIO_COVE.XML");
                $clean = str_replace(array('ns2:','S:','wsse:','ns3:','wsu:'), '', $xml);        
                $xmlClean = simplexml_load_string($clean);
                unset($clean);
                $array = @json_decode(@json_encode($xmlClean),1);
                foreach($array['renglon'] as $item) {
                    if($referencia == $item['NUM_REFE']) {
                        $data = array(
                            'E_DOCUMENT' => $item['E_DOCUMENT']
                        );
                        return $data;
                    }
                }
                return null;
            } else {
                throw new Exception("File not found.");
            }
        } catch (Exception $e) {
            echo $e->getMessage(); die();
        }
    }
}
