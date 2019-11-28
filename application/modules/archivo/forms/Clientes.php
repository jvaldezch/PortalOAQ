<?php

class Archivo_Form_Clientes extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {
        $this->setIsArray(true);
        $this->setAttrib("id", "customer-form");
        $this->_addClassNames('well');
        $this->addElement('text', 'rfc', array(
            'label' => 'RFC del cliente',
            'placeholder' => 'RFC del cliente',
            'class' => 'focused'
        ));
        $this->addElement('text', 'nombre', array(
            'label' => 'Nombre del cliente',
            'placeholder' => 'Nombre del cliente',
            'class' => 'focused',
            'attribs' => array('style' => 'width: 450px', 'autocomplete' => 'off'),
        ));
//        $this->addElement('button', 'submit', array(
//            'label' => 'Ver reporte',
//            'type' => 'submit',
//            'buttonType' => 'primary',
//            'decorators' => Array('ViewHelper', 'HtmlTag'),
//            'attribs' => array('style' => 'margin-right: 5px', 'onclick' => 'viewReport()'),
//        ));
    }

}
