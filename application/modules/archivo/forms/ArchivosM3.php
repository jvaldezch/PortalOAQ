<?php

class Archivo_Form_ArchivosM3 extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {
        $deco = array('ViewHelper', 'Errors', 'Label');

        $this->addElement('text', 'fechaIni', array(
            'placeholder' => 'Fecha de consulta',
            "class" => "traffic-input-small",
            "decorators" => $deco,
        ));

        $this->addElement('text', 'fechaFin', array(
            'placeholder' => 'Fecha de consulta',
            "class" => "traffic-input-small",
            "decorators" => $deco,
        ));

        $this->addElement('text', 'pedimento', array(
            'placeholder' => 'pedimento',
            "class" => "traffic-input-small",
            "decorators" => $deco,
        ));
    }

}
