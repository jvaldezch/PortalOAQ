<?php

class Operaciones_Form_BuscarRegistro extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {
        $this->setIsArray(true);

        $this->addElement('text', 'patente', array(
            'class' => 'span6',
            'attribs' => array('style' => 'margin-bottom:0; width: 150px;', 'readonly' => 'true'),
        ));
        $this->addElement('select', 'aduana', array(
            'class' => 'span6',
            'attribs' => array('style' => 'margin-bottom:0; width: 250px;'),
            'multiOptions' => array(
                '640' => '640 (Querétaro)',
                '240' => '240 Nuevo Laredo (Aeropuerto)',
                '370' => '370 Ciudad Hidalgo Chiapas',
                '800' => '800 Colombia',
                ),
        ));
        $this->addElement('text', 'pedimento', array(
            'class' => 'span6',
            'attribs' => array('style' => 'margin-bottom:0; width: 150px;'),
            'required' => true,
            'validators' => array(
                array('validator' => 'NotEmpty', 'breakChainOnFailure' => true, 'options' => array('messages' => array(
                            'isEmpty' => 'Debe especificar el número de pedimento'
                        ))),
                array('validator' => 'StringLength', 'options' => array(7, 8, 'messages' => array(
                            'stringLengthTooShort' => 'Pedimento debe ser de al menos %min% caracteres',
                            'stringLengthTooLong' => 'Pedimento no debe tener más de %max% caracteres'
                        ))),
            ),
        ));
        $this->addElement('button', 'submit', array(
            'label' => 'Buscar',
            'type' => 'submit',
            'class' => 'btn btn-small',
            'buttonType' => 'primary',
            'decorators' => Array('ViewHelper', 'HtmlTag'),
            'attribs' => array('style' => 'float: left; margin-bottom: 10px;'),
        ));
    }

}
