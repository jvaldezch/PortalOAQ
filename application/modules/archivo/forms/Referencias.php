<?php

class Archivo_Form_Referencias extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {
        $this->setIsArray(false);

        $this->setAction("/archivo/index/referencias");
        $this->setMethod("GET");
        
        $decorators = array("ViewHelper", "Errors", "Label");

        $this->addElement("text", "patente", array(
            "class" => "traffic-input-xs",
            "decorators" => $decorators,
            "attribs" => array("style" => "text-align: center"),
        ));
        
        $this->addElement("text", "aduana", array(
            "class" => "traffic-input-xs",
            "decorators" => $decorators,
            "attribs" => array("style" => "text-align: center"),
        ));
        
        $this->addElement("text", "pedimento", array(
            "class" => "traffic-input-small",
            "decorators" => $decorators,
            "attribs" => array("style" => "text-align: center"),
        ));
        
        $this->addElement("text", "referencia", array(
            "class" => "traffic-input-small",
            "decorators" => $decorators,
            "attribs" => array("style" => "text-align: center"),
        ));
        
        $this->addElement("text", "rfcCliente", array(
            "class" => "traffic-input-small",
            "decorators" => $decorators,
            "attribs" => array("style" => "text-align: center"),
        ));

        $this->addElement("button", "submit", array(
            "label" => "Buscar",
            "type" => "submit",
            "buttonType" => "primary",
            "decorators" => Array("ViewHelper", "HtmlTag"),
            "attribs" => array("style" => "margin-top:5px"),
        ));
    }

}
