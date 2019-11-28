<?php

class Trafico_Form_ConceptosSolicitud extends Twitter_Bootstrap_Form_Horizontal {

    protected $_aduana;

    public function setAduana($aduana = null) {
        $this->_aduana = $aduana;
    }

    public function init() {
        
        $model = new Trafico_Model_TraficoConceptosMapper();
        $concepts = $model->obtener($this->_aduana);
        
        $deco = array ('ViewHelper','Errors', 'Label',);
        
        $this->addElement('select', 'concepto', array(
            'class' => 'focused',
            'multiOptions' => $concepts,
            'attribs' => array('style' => 'width: 380px','tabindex' => '1'),
            'decorators'=> $deco,
        ));
        
        $this->addElement('text', 'importe', array(
            'class' => 'inputimporte',
            'attribs' => array('tabindex' => '2'),
            'decorators'=> $deco,
        ));
        
        $this->addElement('hidden', 'id', array());
        
        $this->addElement('hidden', 'aduana', array());
    }

}
