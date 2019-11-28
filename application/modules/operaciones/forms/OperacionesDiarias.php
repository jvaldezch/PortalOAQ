<?php

class Operaciones_Form_OperacionesDiarias extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {
        $this->setIsArray(true);
        $this->_addClassNames('well');
        $this->setAction("/operaciones/index/operaciones-diarias");
        $this->setMethod('GET');
        
        $this->addElement('hidden', 'year');
        $this->addElement('hidden', 'month');
        $this->addElement('hidden', 'day');

        $this->addElement('text', 'fecha', array(
            'label' => 'Fecha de consulta',
            'placeholder' => 'Fecha de consulta',
            'class' => 'focused',
            'dimension' => 2,
            'attribs' => array('name' => 'fecha')
        ));

        $this->addElement('button', 'submit', array(
            'label' => 'Gráficar',
            'type' => 'submit',
            'buttonType' => 'primary',
            'decorators' => Array('ViewHelper', 'HtmlTag'),
        ));
    }

}
