<?php

// borrar

class Trafico_Form_NuevaNaviera extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {

        $this->setAttrib("id", "nueva-naviera");
        $this->setAction("/trafico/ajax/nueva-naviera");
        $this->setMethod("POST");

        $deco = array('ViewHelper', 'Errors', 'Label',);

        $this->addElement('text', 'nombreNaviera', array(
            'attribs' => array('tabindex' => '1', 'style' => 'width: 250px'),
            'decorators' => $deco,
        ));
    }

}
