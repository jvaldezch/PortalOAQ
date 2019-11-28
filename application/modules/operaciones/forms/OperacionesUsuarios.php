<?php

class Operaciones_Form_OperacionesUsuarios extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {
        $this->setIsArray(true);
        $this->_addClassNames('well');
        $this->setAction("/operaciones/index/operaciones-usuarios");
        $this->setMethod('GET');

        $years = array();
        foreach (range(date('Y'), 2012, -1) as $number) {
            $years[$number] = $number;
        }
        
        $this->addElement('select', 'patente', array(
            'label' => 'Patente:',
            'placeholder' => 'Patente:',
            'class' => 'focused',
            'multiOptions' => array(
                    3589 => 3589
                ),
            'value' => 3589,
        ));
        
        $this->addElement('select', 'aduana', array(
            'label' => 'Aduana:',
            'placeholder' => 'Aduana:',
            'class' => 'focused',
            'multiOptions' => array(
                    640 => 640,
                    646 => 646, 
                    240 => 240
                ),
            'value' => 640,
        ));

        $this->addElement('select', 'year', array(
            'label' => 'Año:',
            'placeholder' => 'Año:',
            'class' => 'focused',
            'multiOptions' => $years,
            'value' => date('Y'),
        ));

        $this->addElement('button', 'submit', array(
            'label' => 'Gráficas',
            'type' => 'submit',
            'buttonType' => 'primary',
            'decorators' => Array('ViewHelper', 'HtmlTag'),
        ));
    }

}
