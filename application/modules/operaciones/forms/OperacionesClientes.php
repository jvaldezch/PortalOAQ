<?php

class Operaciones_Form_OperacionesClientes extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {
        $this->setIsArray(true);
        $this->_addClassNames('well');
        $this->setAction("/operaciones/index/operaciones-clientes");
        $this->setMethod('POST');

        $model = new Operaciones_Model_ClientesAnexo24Mapper();
        $array = $model->todos();
        $this->addElement('select', 'rfc', array(
            'label' => 'Nombre del cliente:',
            'placeholder' => 'RFC del cliente:',
            'class' => 'focused',
            'multiOptions' => $array,
            'attribs' => array('style' => 'width: 550px'),
        ));

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

        $this->addElement('button', 'submit', array(
            'label' => 'Ver reporte',
            'type' => 'submit',
            'buttonType' => 'primary',
            'decorators' => Array('ViewHelper', 'HtmlTag'),
        ));
    }

}
