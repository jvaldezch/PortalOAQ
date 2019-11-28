<?php

class Trafico_Form_SolicitudTrafico extends Twitter_Bootstrap_Form_Horizontal {
    
    protected $procesos;
    protected $esquemas;
    
    function setProcesos($procesos) {
        $this->procesos = $procesos;
    }

    function setEsquemas($esquemas) {
        $this->esquemas = $esquemas;
    }

    public function init() {

        if (!isset($this->esquemas)) {
            $mapper = new Trafico_Model_EsquemaFondos();
            $multiOptions = $mapper->multiOptions();
        } else {
            $multiOptions = $this->esquemas;
        }

        $decorators = array("ViewHelper", "Errors", "Label");

        $this->addElement("hidden", "idSolicitud", array("decorators" => $decorators));

        $this->addElement("select", "esquema", array(
            "class" => "traffic-select-medium",
            "multiOptions" => $multiOptions,
            "decorators" => $decorators
        ));
        
        if (!isset($this->procesos)) {
            $model = new Trafico_Model_SolicitudProceso();
            $options = $model->multiOptions();
        } else {
            $options = $this->procesos;
        }

        $this->addElement("select", "proceso", array(
            "class" => "traffic-select-medium",
            "multiOptions" => $options,
            "decorators" => $decorators
        ));
    }

}
