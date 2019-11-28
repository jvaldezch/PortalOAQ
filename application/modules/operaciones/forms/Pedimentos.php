<?php

class Operaciones_Form_Pedimentos extends Twitter_Bootstrap_Form_Horizontal
{
    public function init()
    {   
        $this->setIsArray(true);
        $this->setElementsBelongTo('bootstrap');
        
        $this->_addClassNames('well');
        
        $this->setAction("/operaciones/index/anexo-24");
        $this->setMethod('POST');
        
        $this->addElement('select', 'aduana', array(
            'label'         => 'Aduana',
            'placeholder'   => 'Aduana',
            'class'         => 'focused',
            'multiOptions' => array(
                '' => '-- Aduana --',
                'http://192.168.0.253:8081/webservice/pedimentos?wsdl' => '640 - Querétaro',
                'http://oaqaeropuerto.no-ip.biz:8081/webservice/pedimentos?wsdl' => '646 - Aeropuerto Querétaro',
                ),
            'attribs' => array('style' => 'width: 450px'),
        ));
        
        $this->addElement('select', 'rfc', array(
            'label'         => 'Cliente',
            'placeholder'   => 'Cliente',
            'class'         => 'focused',
            'multiOptions' => array(
                'null' => '-- Elegir cliente --',
                ),
            'attribs' => array('style' => 'width: 550px','disabled' => 'disabled'),
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
            'label'         => 'Reporte',
            'type'          => 'submit',
            'buttonType'    => 'primary',
            'decorators'    => Array('ViewHelper','HtmlTag'),
            'attribs' => array('style' => 'margin-top:5px'),
        ));
        
    }
}

