<?php

class Archivo_Form_BuscarExpediente extends Twitter_Bootstrap_Form_Inline
{
    public function init()
    {   
        // https://github.com/Emagister/zend-form-decorators-bootstrap
        $this->setIsArray(true);
        $this->setElementsBelongTo('bootstrap');
        
        $this->_addClassNames('well');
        
        $this->setAction("/archivo/index/expedientes-digitales");
        $this->setMethod('POST');

        $this->addElement('text', 'referencia', array(
            'label'         => 'Referencia',
            'placeholder'   => 'Referencia',
            'class'         => 'focused'
        ));
        
        $this->addElement('text', 'pedimento', array(
            'label'         => 'Pedimento',
            'placeholder'   => 'Pedimento',
            'class'         => 'focused'
        ));
        
        $this->addElement('text', 'ctagastos', array(
            'label'         => 'Cuenta de gastos',
            'placeholder'   => 'Cuenta de gastos',
            'class'         => 'focused'
        ));
        
        $this->addDisplayGroup(
            array('referencia', 'pedimento','ctagastos'),
                'login',
            array(
                'legend' => 'Buscar expediente'
            )
        );

        $this->addElement('button', 'submit', array(
            'label'         => 'Buscar',
            'type'          => 'submit',
            'decorators'    => Array('ViewHelper','HtmlTag'),
            'attribs' => array('style' => 'margin-top:5px'),
        ));
        
    }
}

