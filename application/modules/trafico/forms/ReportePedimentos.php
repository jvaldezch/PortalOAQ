<?php

class Trafico_Form_ReportePedimentos extends Twitter_Bootstrap_Form_Vertical
{
    public function init()
    {   
        $this->setIsArray(true);
        $this->setElementsBelongTo('bootstrap');
        
        $this->setAction("/trafico/index/reporte-de-pedimentos");
        $this->setMethod("POST");
        //$this->_addClassNames('well');
        
        $this->addElement('select', 'aduana', array(
            'label'         => 'Aduana',
            'placeholder'   => 'Aduana',
            'class'         => 'focused',
            'multiOptions'  => array(
                '*' => 'Todas',
                '640' => '640',
                '646' => '646',
            ),
            'attribs' => array('style' => 'width: 150px'),
        ));
        
        /*$this->addElement('text', 'fecha', array(
            'label'         => 'Fecha de entrada',
            'placeholder'   => 'Fecha de entrada',
            'class'         => 'focused',
            'attribs' => array('style' => 'width: 150px'),
        ));*/
        
        $this->addElement('button', 'submit', array(
            'label'         => 'Filtrar',
            'type'          => 'submit',
            'buttonType'    => 'info',
            'decorators'    => Array('ViewHelper','HtmlTag'),
            'attribs' => array('style' => 'margin-top:5px; margin-right: 5px'),
        ));
        
    }
}

