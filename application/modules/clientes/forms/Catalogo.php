<?php

class Clientes_Form_Catalogo extends Twitter_Bootstrap_Form_Horizontal {

    protected $_rfc;
    protected $_name;

    public function setRfc($rfc = null) {
        $this->_rfc = $rfc;
    }

    public function setName($name = null) {
        $this->_name = $name;
    }

    public function init() {
        $this->addElement('select', 'clientes', array(
            'decorators' => array('ViewHelper', 'Errors', 'Label',), // no decorators
            'multiOptions' => array(
                $this->_rfc => $this->_name,
            ),
            'attribs' => array('style' => 'width: 400px', 'tabindex' => '1'),
        ));
        $this->addElement('select', 'tipo', array(
            'decorators' => array('ViewHelper', 'Errors', 'Label',), // no decorators
            'multiOptions' => array(
                'imp_def' => 'Importaciones Definitivas',
                'imp_tmp' => 'Importaciones Temporales',
            ),
            'attribs' => array('style' => 'width: 250px', 'tabindex' => '1'),
        ));
    }

}
