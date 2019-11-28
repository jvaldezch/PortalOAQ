<?php

class Archivo_Form_NuevaReferencia extends Twitter_Bootstrap_Form_Horizontal {

    protected $_patentes;
    protected $_id;

    public function setPatentes($patentes = null) {
        $this->_patentes = $patentes;
    }

    public function setId($id = null) {
        $this->_id = $id;
    }

    public function init() {
        $this->setIsArray(true);
        $this->setMethod("post");
        $decorators = array("ViewHelper", "Errors", "Label");

        if (isset($this->_patentes) && $this->_patentes == false) {
            $model = new Application_Model_CustomsMapper();
            $patentes = $model->getAllPatents();
            $options = array();
            $options[""] = "---";
            foreach ($patentes as $item) {
                $options[$item["patente"]] = $item["patente"];
            }
        } else {
            $options[""] = "---";
            foreach ($this->_patentes as $item) {
                $options[$item] = $item;
            }
        }
        $this->addElement("hidden", "id", array(
            "value" => $this->_id,
        ));

        $this->addElement("select", "patente", array(
            "class" => "traffic-select-small",
            "required" => true,
            "multiOptions" => $options,
            "decorators" => $decorators,
            "attribs" => array("tabindex" => "10"),
        ));


        $this->addElement("text", "rfc_cliente", array(
            "required" => true,
            "class" => "traffic-input-medium",
            "decorators" => $decorators,
            "attribs" => array("tabindex" => "11"),
        ));

        $this->addElement("text", "nombre", array(
            "required" => true,
            "class" => "traffic-input-large",
            "decorators" => $decorators,
            "attribs" => array("tabindex" => "12", "autocomplete" => "off"),
        ));
        
        $this->addElement("text", "pedimento", array(
            "required" => true,
            "class" => "traffic-input-small",
            "decorators" => $decorators,
            "attribs" => array("tabindex" => "13", "onKeyPress" => "return check(event,value)", "onInput" => "checkLength(7,this)"),
        ));
        
        $this->addElement("text", "referencia", array(
            "required" => true,
            "class" => "traffic-input-small",
            "decorators" => $decorators,
            "attribs" => array("tabindex" => "14"),
        ));

    }

}
