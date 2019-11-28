<?php

class Operaciones_Form_BuscarCoves extends Twitter_Bootstrap_Form_Horizontal
{
    public function init()
    {   
        $this->setIsArray(true);
        $this->setElementsBelongTo('bootstrap');
        
        $this->_addClassNames('well');
        
        $this->setAction("/operaciones/index/coves");
        $this->setMethod('POST');

        $data = array();
        $cus = new Application_Model_CustomersMapper();
        $rows = $cus->getCustomers();
        $data[''] = '-- Seleccionar cliente --';
        foreach($rows as $item) {
            $data[$item['rfc']] = $item['nombre'] . ' - ' . $item['rfc'];
        }
        
        $this->addElement('select', 'rfc', array(
            'label'         => 'Cliente',
            'placeholder'   => 'Cliente',
            'class'         => 'focused',
            'multiOptions' => $data,
            'attribs' => array('style' => 'width: 550px'),
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
            'label'         => 'Buscar',
            'type'          => 'submit',
            'buttonType'    => 'primary',
            'decorators'    => Array('ViewHelper','HtmlTag'),
            'attribs' => array('style' => 'margin-top:5px'),
        ));
        
    }
}

