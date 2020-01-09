<?php

class WebService_PedimentoController extends Zend_Controller_Action {

    protected $_config;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
    }

    /**
     * /webservice/pedimento/index?aduana=640&patente=3589&pedimento=6000002
     * /webservice/pedimento/index?aduana=640&patente=3589&pedimento=6000002&debug=true
     * /webservice/pedimento/index?aduana=640&patente=3589&pedimento=6001131&debug=true
     * 
     * @throws Exception
     */
    public function indexAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $domtree = new DOMDocument("1.0", "UTF-8");
        $domtree->preserveWhiteSpace = false;
        $domtree->formatOutput = true;
        try {
            $f = array(
                "patente" => array("StringTrim", "StripTags", "Digits"),
                "aduana" => array("StringTrim", "StripTags", "Digits"),
                "pedimento" => array("StringTrim", "StripTags", "Digits"),
            );
            $vld = array(
                "patente" => new Zend_Validate_Int(),
                "aduana" => new Zend_Validate_Int(),
                "pedimento" => new Zend_Validate_Int(),
                "debug" => new Zend_Validate_InArray(array(true, false))
            );
            $i = new Zend_Filter_Input($f, $vld, $this->_request->getParams());
            if ($i->isValid("patente") && $i->isValid("aduana") && $i->isValid("pedimento")) {
                $misc = new OAQ_Misc();
                $f = new OAQ_ArchivosM3();
                $map = new Automatizacion_Model_ArchivosValidacionPedimentosMapper();
                $val = new Automatizacion_Model_ArchivosValidacionMapper();
                $db = $misc->sitawin($i->patente, $i->aduana);
                if (!isset($db)) {
                    $root = $domtree->createElement("errores");
                    $xmlRoot = $domtree->appendChild($root);
                    $xmlRoot->appendChild($domtree->createElement("error", "No database!"));
                }
                $reg = $map->pedimento($i->patente, $i->pedimento, $i->aduana);
                if(isset($reg)) {
                    $arch = $val->findFile($reg["archivoNombre"], $i->aduana);
                    if (isset($arch["contenido"])) {
                        $m3 = $f->fileToArray(base64_decode($arch["contenido"]), null, $i->pedimento);
                    }
                    $f->arrayM3To($m3);
                    if($i->debug == true) {
    //                    Zend_Debug::dump($f->arrayM3To);                    
                        Zend_Debug::dump($f->get_array()); 
                        unset($m3[500]);
                        unset($m3[501]);
                        unset($m3[502]);
                        unset($m3[503]);
                        unset($m3[504]);
                        unset($m3[505]);
                        unset($m3[506]);
                        unset($m3[507]);
                        unset($m3[509]);
                        unset($m3[510]);
                        unset($m3[511]);
                        unset($m3[512]);
                        unset($m3[516]);
                        unset($m3[551]);
                        unset($m3[554]);
                        unset($m3[556]);
                        unset($m3[557]);
                        unset($m3[558]);
                        unset($m3[601]);
                        unset($m3[701]);
                        unset($m3[702]);
                        unset($m3[800]);
                        unset($m3[801]);
                        Zend_Debug::dump($m3); 
                        return;
                    }
                    $xml = new OAQ_XmlPedimento();
                    $xml->security(date("c"));
                    $xml->consulta();
                    $xml->set_arr($f->get_array());
                    $xml->pedimento();
                    Zend_Controller_Action_HelperBroker::getStaticHelper("viewRenderer")->setNoRender(true);
                    Zend_Layout::getMvcInstance()->disableLayout();
                    $this->_response->setHeader("Content-Type", "text/xml; charset=utf-8")
                            ->setBody($xml->getXml());
                    return;                    
                } else {
                    $root = $domtree->createElement("errores");
                    $xmlRoot = $domtree->appendChild($root);
                    $xmlRoot->appendChild($domtree->createElement("error", "No file found on database!"));
                }
            } else {
                $root = $domtree->createElement("errores");
                $xmlRoot = $domtree->appendChild($root);
                $xmlRoot->appendChild($domtree->createElement("error", "Invalid Input!"));
            }
            Zend_Controller_Action_HelperBroker::getStaticHelper("viewRenderer")->setNoRender(true);
            Zend_Layout::getMvcInstance()->disableLayout();
            $this->_response->setHeader("Content-Type", "text/xml; charset=utf-8")
                    ->setBody($domtree->saveXML());
        } catch (Zend_Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

}
