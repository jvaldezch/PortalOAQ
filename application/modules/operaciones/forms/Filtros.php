<?php

class Operaciones_Form_Filtros extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {
        $this->addElement('text', 'material', array(
            'decorators'=> array ('ViewHelper','Errors', 'Label',),
            'attribs' => array('style' => 'width: 80px','tabindex' => '100'),
        ));
        $this->addElement('text', 'descripcion', array(
            'decorators'=> array ('ViewHelper','Errors', 'Label',),
            'attribs' => array('style' => 'width: 250px','tabindex' => '100'),
        ));
        $this->addElement('text', 'parte', array(
            'decorators'=> array ('ViewHelper','Errors', 'Label',),
            'attribs' => array('style' => 'width: 120px','tabindex' => '100'),
        ));
    }

}
