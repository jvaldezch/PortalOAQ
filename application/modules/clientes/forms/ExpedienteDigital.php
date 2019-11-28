<?php

class Clientes_Form_ExpedienteDigital extends Twitter_Bootstrap_Form_Horizontal
{
    public function init()
    {   
        // https://github.com/Emagister/zend-form-decorators-bootstrap
        $this->setIsArray(true);
        $this->setElementsBelongTo('bootstrap');

        $this->_addClassNames('well');

        $this->addElement('text', 'rfc', array(
            'label'         => 'RFC del cliente',
            'placeholder'   => 'RFC del cliente',
            'prepend'       => '<i class="icon-th-list" id="customer-list" style="cursor:pointer"></i>',
            'class'         => 'focused',
            'attribs' => array('readonly' => 'true'),
        ));
        
        $this->addElement('text', 'fechaIni', array(
            'label'         => 'Fecha de inicio',
            'placeholder'   => 'Fecha de inicio',
            'prepend'       => '<i class="icon-calendar" id="date-init"></i>',
            'class'         => 'focused',
            'dimension'     => 2,
            'attribs'       => array('name' => 'fechaIni','style' => 'width: 150px')
        ));
        
        $this->addElement('text', 'fechaFin', array(
            'label'         => 'Fecha fin',
            'placeholder'   => 'Fecha fin',
            'prepend'       => '<i class="icon-calendar" id="date-end"></i>',
            'class'         => 'focused',
            'dimension'     => 2,
            'attribs' => array('name' => 'fechaFin','style' => 'width: 150px','readonly' => 'true')
        ));
        
        $this->addElement('text', 'buscarReferencia', array(
            'label'         => 'Referencia',
            'class'         => 'focused',
            'dimension'     => 2,
        ));
        
        $this->addElement('button', 'submit', array(
            'label'         => 'Consultar expedientes',
            'type'          => 'submit',
            'buttonType'    => 'primary',
            'decorators'    => Array('ViewHelper','HtmlTag'),
            'attribs' => array('style' => 'margin-top:5px;'),
        ));
        
    }
}

