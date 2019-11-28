<?php

class Trafico_Form_CrearTrafico extends Twitter_Bootstrap_Form_Horizontal {

    protected $_usuario;

    public function setUsuario($usuario = null) {
        $this->_usuario = $usuario;
    }

    public function init() {

        $model = new Trafico_Model_TraficoUsuAduanasMapper();
        $arr = $model->obtenerPatentes($this->_usuario);

        $this->addElement('select', 'Patente', array(
            'class' => 'focused',
            'multiOptions' => $arr,
            'attribs' => array('style' => 'width: 80px', 'tabindex' => '1'),
            'decorators' => array('ViewHelper', 'Errors', 'Label',),
        ));

        $this->addElement('select', 'Consolidado', array(
            'class' => 'selectconsolidado',
            'multiOptions' => array(
                '0' => 'N',
                '1' => 'S',
            ),
            'decorators' => array('ViewHelper', 'Errors', 'Label',),
        ));

        $this->addElement('select', 'Rectificacion', array(
            'class' => 'selectrectificacion',
            'multiOptions' => array(
                '0' => 'N',
                '1' => 'S',
            ),
            'decorators' => array('ViewHelper', 'Errors', 'Label',),
        ));

        $this->addElement('select', 'Aduana', array(
            'multiOptions' => array('' => '---'),
            'attribs' => array('style' => 'width: 80px', 'tabindex' => '2', 'disabled' => 'disabled'),
        ));

        $this->addElement('select', 'TipoOperacion', array(
            'class' => 'inputop',
            'multiOptions' => array('' => '---'),
            'attribs' => array('style' => 'width: 150px', 'tabindex' => '3', 'disabled' => 'disabled'),
        ));

        $this->addElement('select', 'CvePed', array(
            'class' => 'inputcveped',
            'attribs' => array(),
            'attribs' => array('style' => 'width: 60px', 'tabindex' => '4', 'disabled' => 'disabled'),
        ));

        $this->addElement('select', 'Rfc', array(
            'class' => 'inputop',
            'multiOptions' => array('' => '---'),
            'attribs' => array('style' => 'width: 300px', 'tabindex' => '5', 'disabled' => 'disabled'),
        ));

        $this->addElement('text', 'Pedimento', array(
            'class' => 'inputpedimento',
            'attribs' => array('tabindex' => '6'),
        ));

        $this->addElement('text', 'Referencia', array(
            'class' => 'inputreferencia',
            'attribs' => array('tabindex' => '7'),
        ));

        $this->addElement('text', 'TipoCambio', array(
            'class' => 'inputtc',
            'attribs' => array('tabindex' => '8', 'style' => 'width: 50px'),
        ));

        $this->addElement('text', 'Cantidad', array(
            'class' => 'inputcantidad',
            'attribs' => array('tabindex' => '9', 'style' => 'width: 40px'),
            'value' => 1,
        ));

        $this->addElement('text', 'Regimen', array(
            'class' => 'inputregimen',
            'attribs' => array(),
        ));
    }

}
