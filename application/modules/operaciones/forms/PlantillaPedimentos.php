<?php

class Operaciones_Form_PlantillaPedimentos extends Twitter_Bootstrap_Form_Horizontal
{
    public function init()
    {   
        $this->setIsArray(true);
//        $this->setElementsBelongTo('bootstrap');
        
        $this->_addClassNames('well');
        
        $this->setAction("/operaciones/index/anexo-24");
        $this->setMethod('POST');
        
        $this->addElement('text', 'rfc', array(
            'label' => 'RFC del cliente',
            'placeholder' => 'RFC del cliente',
            'prepend' => '<i class="icon-th-list" id="customer-list" style="cursor:pointer"></i>',
            'class' => 'focused'
        ));

        $this->addElement('text', 'nombre', array(
            'label' => 'Nombre del cliente',
            'placeholder' => 'Nombre del cliente',
            'class' => 'focused',
            'attribs' => array('style' => 'width: 450px', 'autocomplete' => 'off'),
        ));
        
        $this->addElement('select', 'aduana', array(
            'label'         => 'Aduana',
            'placeholder'   => 'Aduana',
            'class'         => 'focused',
            'multiOptions' => array(
                '' => '-- Aduana --',
                '3589,640' => '640 - Querétaro, Operaciones Especiales',
                '3589,646' => '646 - Querétaro, Aeropuerto Intercontinental',
                ),
            'attribs' => array('style' => 'width: 450px'),
        ));
               
        $this->addElement('text', 'fechaIni', array(
            'label'         => 'Fecha de inicio',
            'placeholder'   => 'Fecha de inicio',
            'prepend'       => '<i class="icon-calendar" id="date-init"></i>',
            'class'         => 'focused',
            'dimension'     => 2,
            'attribs'       => array('name' => 'fechaIni')
        ));
        
        $this->addElement('text', 'fechaFin', array(
            'label'         => 'Fecha fin',
            'placeholder'   => 'Fecha fin',
            'prepend'       => '<i class="icon-calendar" id="date-end"></i>',
            'class'         => 'focused',
            'dimension'     => 2,
            'attribs' => array('name' => 'fechaFin')
        ));
        
        $this->addElement('button', 'submit', array(
            'label'         => 'Ver reporte',
            'type'          => 'submit',
            'buttonType'    => 'primary',
            'decorators'    => Array('ViewHelper','HtmlTag'),
            'attribs' => array('style' => 'margin-top:5px','onclick' => 'viewReport();'),
        ));
        
    }
}

