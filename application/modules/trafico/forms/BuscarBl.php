<?php

class Trafico_Form_BuscarBl extends Twitter_Bootstrap_Form_Horizontal
{
    public function init()
    {   
        $this->setIsArray(true);
        $this->setElementsBelongTo('bootstrap');
        
        $this->setAction("/trafico/index/embarques-terminal");
        $this->setMethod("POST");
        $this->_addClassNames('well');
        
        $this->addElement('text', 'guia', array(
            'label'         => 'Guía',
            'placeholder'   => 'Guía',
            'class'         => 'focused',
            'attribs' => array('style' => 'width: 250px'),
        ));
        
        $this->addElement('button', 'submit', array(
            'label'         => 'Buscar Guía',
            'type'          => 'submit',
            'buttonType'    => 'primary',
            'decorators'    => Array('ViewHelper','HtmlTag'),
            'attribs' => array('style' => 'margin-top:5px; margin-right: 5px; display: inline-block; float:left;'),
        ));
        
        $this->addElement('button', 'create', array(
            'label'         => 'Crear trafico',
            'type'          => 'submit',
            'buttonType'    => 'warning',
            'decorators'    => Array('ViewHelper','HtmlTag'),
            'attribs' => array('style' => 'margin-top:5px; margin-right: 5px; display: inline-block;'),
        ));
        
    }
}

