<?php

// borrar

class Trafico_Form_NuevoTransporte extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {

        $this->setAttrib("id", "nuevo-transporte");
        $this->setAction("/trafico/ajax/nuevo-transporte");
        $this->setMethod("POST");

        $deco = array('ViewHelper', 'Errors', 'Label',);

        $this->addElement('text', 'nombreTransporte', array(
            'attribs' => array('tabindex' => '1', 'style' => 'width: 250px'),
            'decorators' => $deco,
        ));
    }

}
