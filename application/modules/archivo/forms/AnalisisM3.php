<?php

class Archivo_Form_AnalisisM3 extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {
        $this->setIsArray(true);
        $this->setMethod('GET');
        $this->setAttrib("id", "analysis");

        $this->_addClassNames('well');

        $this->addElement('text', 'patente', array(
            'placeholder' => 'Patente',
            'dimension' => 2,
        ));

        $this->addElement('text', 'pedimento', array(
            'placeholder' => 'Pedimento',
            'dimension' => 2,
        ));

        $this->addElement('button', 'submit', array(
            'label' => 'Buscar',
            'type' => 'submit',
            'buttonType' => 'primary',
            'decorators' => Array('ViewHelper', 'HtmlTag'),
            'attribs' => array('id' => 'do-analysis'),
        ));
    }

}
