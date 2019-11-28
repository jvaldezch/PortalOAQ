<?php

class Operaciones_Form_BuscarCliente extends Twitter_Bootstrap_Form_Horizontal
{
    public function init()
    {   
        // https://github.com/Emagister/zend-form-decorators-bootstrap
        $this->setIsArray(true);
        $this->setElementsBelongTo('bootstrap');
        
        $this->_addClassNames('well');
        
        $this->setAction("/operaciones/index/pedimentos");
        $this->setMethod('POST');

        $this->addElement('text', 'rfc', array(
            'label'         => 'RFC',
            'placeholder'   => 'RFC',
            'class'         => 'focused'
        ));
        
        $this->addElement('select', 'servidor', array(
            'label'         => 'Aduana',
            'placeholder'   => 'Aduana',
            'class'         => 'focused',
            'multiOptions' => array(
                'http://192.168.0.253:8081/webservice/pedimentos?wsdl' => '640 (Querétaro)',
                'http://oaqaeropuerto.no-ip.org:8081/webservice/pedimentos?wsdl' => '646 (Aeropuerto)',
                ),
        ));
        
        $this->addElement('text', 'nombre', array(
            'label'         => 'Nombre',
            'placeholder'   => 'Nombre',
            'class'         => 'focused',
            'attribs' => array('autocomplete' => 'off','style' => 'width: 320px'),
        ));
        
        $this->addDisplayGroup(
            array('rfc', 'nombre','servidor'),
                'search',
            array(
                'legend' => 'Buscar cliente y aduana'
            )
        );
        
        $this->addElement('text', 'fechaIni', array(
            'label'         => 'Fecha de inicio',
            'placeholder'   => 'Fecha de inicio',
            'prepend'       => '<i class="icon-calendar"></i>',
            'class'         => 'focused',
            'dimension'     => 2,
            'attribs'       => array('name' => 'fechaIni')
        ));
        
        $this->addElement('text', 'fechaFin', array(
            'label'         => 'Fecha fin',
            'placeholder'   => 'Fecha fin',
            'prepend'       => '<i class="icon-calendar"></i>',
            'class'         => 'focused',
            'dimension'     => 2,
            'attribs' => array('name' => 'fechaFin')
        ));
        $this->addDisplayGroup(
            array('fechaIni', 'fechaFin'),
                'dates',
            array(
                'legend' => 'Rango de fechas'
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

