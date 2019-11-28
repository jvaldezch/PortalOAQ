<?php

class Operaciones_Form_PedimentosPdf extends Twitter_Bootstrap_Form_Horizontal
{
    public function init()
    {   
        $this->setIsArray(true);
        $this->setElementsBelongTo('bootstrap');
        
        $this->_addClassNames('well');
        
        $this->setAction("/operaciones/index/pedimentos-pdf");
        $this->setMethod('POST');
        
        $this->addElement('select', 'aduana', array(
            'label'         => 'Aduana',
            'placeholder'   => 'Aduana',
            'class'         => 'focused',
            'multiOptions' => array(
                '' => '-- Seleccionar aduana --',
                '*' => 'Todas',
                '160' => '160 - Manzanillo, Col.',
                '470' => '470 - México, D.F. (COINSAR)',
                ),
            'attribs' => array('style' => 'width: 450px'),
            'required'      => true,
            'validators'    => array(
                array('validator' => 'NotEmpty', 'breakChainOnFailure' => true, 'options' => array('messages' => array(
                    'isEmpty' => 'Debe seleccionar aduana'
                    ))),
                ),
        ));
        
        $this->addElement('text', 'fechaIni', array(
            'label'         => 'Fecha de pago inicio',
            'placeholder'   => 'Fecha de pago inicio',
            'prepend'       => '<i class="icon-calendar" id="date-init"></i>',
            'class'         => 'focused',
            'dimension'     => 2,
            'attribs'       => array('name' => 'fechaIni')
        ));
        
        $this->addElement('text', 'fechaFin', array(
            'label'         => 'Fecha de pago fin',
            'placeholder'   => 'Fecha de pago fin',
            'prepend'       => '<i class="icon-calendar" id="date-end"></i>',
            'class'         => 'focused',
            'dimension'     => 2,
            'attribs' => array('name' => 'fechaFin')
        ));
        
        $this->addElement('text', 'pedimento', array(
            'label'         => 'Pedimento',
            'placeholder'   => 'Pedimento',
            'class'         => 'focused',
            'dimension'     => 2,
        ));
        
        $this->addElement('button', 'submit', array(
            'label'         => 'Ver pedimentos',
            'type'          => 'submit',
            'buttonType'    => 'primary',
            'decorators'    => Array('ViewHelper','HtmlTag'),
            'attribs' => array('style' => 'margin-top:5px'),
        ));
        
    }
}

