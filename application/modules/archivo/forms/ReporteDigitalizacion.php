<?php

class Archivo_Form_ReporteDigitalizacion extends Twitter_Bootstrap_Form_Horizontal
{
    public function init()
    {   
        // https://github.com/Emagister/zend-form-decorators-bootstrap
        $this->setIsArray(true);
        $this->setElementsBelongTo('bootstrap');

        $this->_addClassNames('well');
        
        $this->addElement('text', 'fechaIni', array(
            'label'         => 'Fecha de inico',
            'prepend'       => '<i class="icon-calendar" id="date-init"></i>',
            'class'         => 'focused',
            'dimension'     => 2,
            'attribs'       => array('name' => 'fechaIni','style' => 'width:150px')
        ));
        
        $this->addElement('text', 'fechaFin', array(
            'label'         => 'Fecha de fin',
            'prepend'       => '<i class="icon-calendar" id="date-init"></i>',
            'class'         => 'focused',
            'dimension'     => 2,
            'attribs'       => array('name' => 'fechaFin','style' => 'width:150px')
        ));

        $this->addElement('button', 'submit', array(
            'label'         => 'Buscar',
            'type'          => 'submit',
            'buttonType'    => 'primary',
            'decorators'    => Array('ViewHelper','HtmlTag'),
            'attribs' => array('style' => 'margin-top:5px;margin-right: 5px'),
        ));
        
        
    }
}

