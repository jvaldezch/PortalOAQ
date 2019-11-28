<?php

class Trafico_Form_NumeroPedimento extends Twitter_Bootstrap_Form_Horizontal
{
//    protected $_id = null;
//    protected $_action = null;
//    
//    public function setId($id = null){
//        $this->_id = $id;
//    }
//    
//    public function setAction($action = null){
//        $this->_action = $action;
//    }
    
    public function init()
    {   
        $this->setIsArray(true);
        $this->setElementsBelongTo('bootstrap');
        $this->setAction("/trafico/index/consecutivo");
        $this->setMethod("POST");
        $this->_addClassNames('well');

        $cant = array();
        foreach (range(1, 100) as $number) {
            $cant[$number] = $number;
        }
        $this->addElement('select', 'cantidad', array(
            'label'         => 'Cantidad',
            'class'         => 'focused',
            'multiOptions'  => $cant,
            'required'      => true,
            'attribs' => array('style' => 'width: 110px'),           
        ));

        $this->addElement('text', 'patente', array(
            'label'         => 'Patente',
            'class'         => 'focused',
            'required'      => true,            
            'attribs' => array('style' => 'width: 100px','readonly'=>'true'),
        ));

        $this->addElement('text', 'aduana', array(
            'label'         => 'Aduana',
            'class'         => 'focused',
            'required'      => true,            
            'attribs' => array('style' => 'width: 100px','readonly'=>'true'),
        ));
        
        $this->addElement('select', 'year', array(
            'label'         => 'Año',
            'class'         => 'focused',
            'multiOptions'  => array(
                '2013' => '2013',
                '2014' => '2014',
            ),
            'required'      => true,
            'attribs' => array('style' => 'width: 110px'),           
        ));        
        
        $this->addElement('text', 'pedimento', array(
            'label'         => 'Pedimento',
            'placeholder'   => 'Pedimento',
            'class'         => 'focused',
            'required'      => true,
            'validators'    => array(
                array('validator' => 'NotEmpty', 'breakChainOnFailure' => true, 'options' => array('messages' => array(
                    'isEmpty' => 'Debe proporcionar un número de pedimento'
                    ))),
                array('validator' => 'Digits', 'breakChainOnFailure' => true, 'options' => array('messages' => array(
                    'notDigits' => 'Pedimento debe contener solo números'
                    ))),
                array('validator' => 'StringLength', 'options' => array(7, 7, 'messages' => array(
                    'stringLengthTooShort'  => 'Pedimento debe ser de al menos %min% caracteres',
                    'stringLengthTooLong'   => 'Pedimento no debe tener más de %max% caracteres'
                    ))),
                ),
            'attribs' => array('style' => 'width: 150px'),
        ));
        
        $this->addElement('text', 'referencia', array(
            'label'         => 'Referencia',
            'placeholder'   => 'Referencia',
            'class'         => 'focused',
            'required'      => true,
            'validators'    => array(
                array('validator' => 'NotEmpty', 'breakChainOnFailure' => true, 'options' => array('messages' => array(
                    'isEmpty' => 'Debe proporcionar un número de referencia'
                    ))),
                array('validator' => 'StringLength', 'options' => array(7, 8, 'messages' => array(
                    'stringLengthTooShort'  => 'Referencia debe ser de al menos %min% caracteres',
                    'stringLengthTooLong'   => 'Referencia no debe tener más de %max% caracteres'
                    ))),
                ),
            'attribs' => array('style' => 'width: 150px','readonly' => 'true','autocomplete' => 'off'),
        ));

        $this->addElement('checkbox', 'rectificacion', array(
            'label'         => 'Rectificacion',
            'class'         => 'focused',
            'attribs' => array('style' => 'margin-top:5px'),
        ));

        $this->addElement('text', 'nombrepedimento', array(
            'label'         => 'Cliente de pedimento',
            'placeholder'   => 'Cliente de pedimento',
            'class'         => 'focused',
            'attribs' => array('style' => 'width: 450px'),
        ));

        $this->addElement('text', 'rfc-pedimento', array(
            'label'         => 'RFC de pedimento',
            'placeholder'   => 'RFC de pedimento',
            'prepend'       => '<i class="icon-th-list" id="clients-list" style="cursor:pointer"></i>',
            'class'         => 'focused',
            'attribs' => array('readonly' => 'true','autocomplete' => 'off'),
        ));
        
        $this->addElement('text', 'nombre', array(
            'label'         => 'Cliente a facturar',
            'placeholder'   => 'Cliente a facturar',
            'class'         => 'focused',
            'attribs' => array('style' => 'width: 450px','autocomplete' => 'off'),
        ));

        $this->addElement('text', 'rfc', array(
            'label'         => 'RFC facturación',
            'placeholder'   => 'RFC facturación',
            'prepend'       => '<i class="icon-th-list" id="customer-list" style="cursor:pointer"></i>',
            'class'         => 'focused'
        ));
        
        $this->addElement('button', 'submit', array(
            'label'         => 'Asignar',
            'type'          => 'submit',
            'buttonType'    => 'primary',
            'decorators'    => Array('ViewHelper','HtmlTag'),
            'attribs' => array('style' => 'margin-top:5px; float:left; margin-right: 5px'),
        ));
        
        $this->addElement('button', 'obtain', array(
            'label'         => 'Obtener consecutivo',
            'type'          => 'submit',
            'buttonType'    => 'danger',
            'decorators'    => Array('ViewHelper','HtmlTag'),
            'attribs' => array('style' => 'margin-top:5px'),
        ));
        
    }
}

