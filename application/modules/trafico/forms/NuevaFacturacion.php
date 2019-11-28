<?php

class Trafico_Form_NuevaFacturacion extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {

        $this->setAttrib("id", "nueva-facturacion");
        $this->setAction("/trafico/ajax/nueva-facturacion");
        $this->setMethod("POST");

        $deco = array('ViewHelper', 'Errors', 'Label',);

        $this->addElement('text', 'nombre', array(
            'attribs' => array('tabindex' => '1', 'style' => 'width: 250px'),
            'decorators' => $deco,
        ));
        
        $this->addElement('text', 'rfc', array(
            'attribs' => array('tabindex' => '2', 'style' => 'width: 150px'),
            'decorators' => $deco,
        ));
    }

}
