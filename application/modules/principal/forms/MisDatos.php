<?php

class Principal_Form_MisDatos extends Twitter_Bootstrap_Form_Horizontal {

    protected $_edit = null;
    protected $_patente = null;

    protected function setEdit($edit) {
        $this->_edit = $edit;
    }
    
    public function setPatente($patente) {
        $this->_patente = $patente;
    }

    public function init() {
        $this->setIsArray(true);

        $this->addElement("text", "nombre", array(
            "class" => "traffic-input-medium",
            "decorators" => array("ViewHelper", "Errors", "Label",)
        ));

        $this->addElement("text", "email", array(
            "class" => "traffic-input-medium",
            "required" => true,
            "decorators" => array("ViewHelper", "Errors", "Label",)
        ));

        $this->addElement("text", "usuario", array(
            "class" => "traffic-input-medium",
            "required" => true,
            "decorators" => array("ViewHelper", "Errors", "Label",)
        ));

        $arr = array("" => "---");
        $mapper = new Application_Model_CustomsMapper();
        $rows = $mapper->getAllCompanies();
        foreach ($rows as $item) {
            $arr[$item["rfc"]] = $item["nombre"];
        }
        $this->addElement("select", "empresa", array(
            "class" => "traffic-select-large",
            "multiOptions" => $arr,
            "decorators" => array("ViewHelper", "Errors", "Label",)
        ));

        $deptosUsuarios = array("" => "---");
        $dep = new Usuarios_Model_DepartamentosMapper();
        $deptos = $dep->getDeptos();
        foreach ($deptos as $d) {
            $deptosUsuarios[$d["nombre"]] = ucfirst($d["nombre"]);
        }

        $this->addElement("select", "departamento", array(
            "class" => "traffic-select-large",
            "multiOptions" => $deptosUsuarios,
            "decorators" => array("ViewHelper", "Errors", "Label",)
        ));
        
        $this->addElement("text", "telefono", array(
            "class" => "traffic-input-medium",
            "decorators" => array("ViewHelper", "Errors", "Label",)
        ));
        
        $this->addElement("text", "extension", array(
            "class" => "traffic-input-medium",
            "decorators" => array("ViewHelper", "Errors", "Label",)
        ));

        /*$this->addElement("password", "password", array(
            "class" => "traffic-input-small",
            "required" => true,
        ));

        $this->addElement("password", "confirm", array(
            "class" => "traffic-input-small",
            "decorators" => array("ViewHelper", "Errors", "Label",)
        ));*/
        
    }

}
