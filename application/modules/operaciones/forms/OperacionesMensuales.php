<?php

class Operaciones_Form_OperacionesMensuales extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {
        $this->setIsArray(true);
        $this->_addClassNames('well');
        $this->setAction("/operaciones/index/operaciones-mensuales");
        $this->setMethod('GET');
        
        $years = array();
        foreach (range(date('Y'), 2012, -1) as $number) {
            $years[$number] = $number;
        }
        $this->addElement('select', 'year', array(
            'label' => 'Año:',
            'placeholder' => 'Año:',
            'class' => 'focused',
            'multiOptions' => $years,
            'value' => date('Y'),
        ));
        
        $this->addElement('select', 'month', array(
            'label' => 'Mes:',
            'class' => 'focused',
            'multiOptions' => array(
                '1' => 'Enero',
                '2' => 'Febrero',
                '3' => 'Marzo',
                '4' => 'Abril',
                '5' => 'Mayo',
                '6' => 'Junio',
                '7' => 'Julio',
                '8' => 'Agosto',
                '9' => 'Septiembre',
                '10' => 'Octubre',
                '11' => 'Noviembre',
                '12' => 'Diciembre',
            ),
            'value' => date('m'),
        ));

        $this->addElement('button', 'submit', array(
            'label' => 'Gráficar',
            'type' => 'submit',
            'buttonType' => 'primary',
            'decorators' => Array('ViewHelper', 'HtmlTag'),
        ));
    }

}
