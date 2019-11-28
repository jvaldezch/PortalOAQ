<?php

// borrar

class Trafico_Form_NuevoAlmacen extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {

        $this->setAttrib("id", "nuevo-almacen");
        $this->setAction("/trafico/ajax/nuevo-almacen");
        $this->setMethod("POST");

        $deco = array('ViewHelper', 'Errors', 'Label',);

        $this->addElement('text', 'nombreAlmacen', array(
            'attribs' => array('tabindex' => '1', 'style' => 'width: 250px'),
            'decorators' => $deco,
        ));
    }

}
