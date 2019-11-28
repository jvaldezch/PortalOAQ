<?php

class Operaciones_Form_DetallePedimento extends Twitter_Bootstrap_Form_Horizontal {

//    protected $_factura;
//    protected $_idprod;
//
//    public function setFactura($factura = null) {
//        $this->_factura = $factura;
//    }
//
//    public function setIdprod($idprod = null) {
//        $this->_idprod = $idprod;
//    }

    public function init() {
        $this->setMethod('post');
        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag'),
            array('Errors'),
            'Form',
        ));

        $element = new Zend_Form_Element_Text('numFactura');
//        $element->setAttribs(array('style' => 'width: 40px','readonly'=>'true','class'=>'readonly'));
        $element->setAttribs(array('style' => 'width: 150px'));
        $element->setDecorators(array(
            'ViewHelper', 'Errors', 'Description', 'Label'
        ));
        $this->addElement($element);

        $element = new Zend_Form_Element_Text('cove');
        $element->setAttribs(array('style' => 'width: 150px'));
        $element->setDecorators(array(
            'ViewHelper', 'Errors', 'Description', 'Label'
        ));
        $this->addElement($element);

        $element = new Zend_Form_Element_Text('fechaFactura');
        $element->setAttribs(array('style' => 'width: 100px'));
        $element->setDecorators(array(
            'ViewHelper', 'Errors', 'Description', 'Label'
        ));
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Text('incoterm');
        $element->setAttribs(array('style' => 'width: 100px'));
        $element->setDecorators(array(
            'ViewHelper', 'Errors', 'Description', 'Label'
        ));
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Text('valorFacturaMonExt');
        $element->setAttribs(array('style' => 'width: 100px'));
        $element->setDecorators(array(
            'ViewHelper', 'Errors', 'Description', 'Label'
        ));
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Text('valorFacturaUsd');
        $element->setAttribs(array('style' => 'width: 100px'));
        $element->setDecorators(array(
            'ViewHelper', 'Errors', 'Description', 'Label'
        ));
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Text('factorMonExt');
        $element->setAttribs(array('style' => 'width: 100px'));
        $element->setDecorators(array(
            'ViewHelper', 'Errors', 'Description', 'Label'
        ));
        $this->addElement($element);
        
        $vucemMon = new Vucem_Model_VucemMonedasMapper();
        $monedas = $vucemMon->getAllCurrencies();
        $dataMon[""] = '--';
        foreach ($monedas as $mon) {
            $dataMon[$mon["codigo"]] = $mon["codigo"];
        }
        
        $element = new Zend_Form_Element_Select('divisa');
        $element->setAttribs(array('style' => 'width: 70px'));
        $element->setMultiOptions($dataMon);
        $element->setDecorators(array(
            'ViewHelper', 'Errors', 'Description', 'Label'
        ));
        $this->addElement($element);

        $submit = new Zend_Form_Element_Submit('save');
        $submit->setLabel("Guardar factura");
        $submit->setAttribs(array('style' => 'float: right; margin: 5px;'));
        $submit->setDecorators(array(
            'ViewHelper',
            'Errors',
            'HtmlTag'
        ));
        $this->addElement($submit);
    }

}
