<?php

class Administracion_Form_SolicitudTrafico extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {

        $mapper = new Trafico_Model_EsquemaFondos();
        $multiOptions = $mapper->multiOptions();

        $decorators = array("ViewHelper", "Errors", "Label",);

        $this->addElement("hidden", "idSolicitud", array("decorators" => $decorators));
        $this->addElement("hidden", "aduana", array("decorators" => $decorators));
        $this->addElement("hidden", "patente", array("decorators" => $decorators));
        $this->addElement("hidden", "pedimento", array("decorators" => $decorators));
        $this->addElement("hidden", "referencia", array("decorators" => $decorators));

        $this->addElement("select", "esquema", array(
            "class" => "traffic-select-medium",
            "multiOptions" => $multiOptions,
            "decorators" => $decorators
        ));
        
        $model = new Trafico_Model_SolicitudProceso();
        $options = $model->multiOptions(true);

        $this->addElement("select", "proceso", array(
            "class" => "traffic-select-medium",
            "multiOptions" => $options,
            "decorators" => $decorators
        ));
    }

}
