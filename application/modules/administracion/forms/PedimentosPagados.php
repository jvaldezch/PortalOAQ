<?php

class Administracion_Form_PedimentosPagados extends Twitter_Bootstrap_Form_Horizontal
{
    public function init()
    {   
        
        $this->setIsArray(true);
        $this->setElementsBelongTo('bootstrap');

        $this->_addClassNames('well');

        $this->addElement('select', 'year', array(
            'label'         => 'Año',
            'class'         => 'focused span3',
            'multiOptions'          => array(
                '2009'      => '2009',
                '2010'      => '2010',
                '2011'      => '2011',
                '2012'      => '2012',
                '2013'      => '2013',
                '2014'      => '2014',
            ),
        ))->setDefault('year', date('Y'));
        
        $this->addElement('select', 'aduana', array(
            'label'         => 'Aduana',
            'class'         => 'focused span5',
            'attribs' => array('autocomplete' => 'off'),
            'multiOptions'          => array(
                '640'      => '640 - QUERETARO',
                '646'      => '646 - AEROPUERTO QUERETARO',
            ),
        ));
        
        $this->addElement('button', 'submit', array(
            'label'         => 'Generar reporte',
            'type'          => 'submit',
            'buttonType'    => 'primary',
            'decorators'    => Array('ViewHelper','HtmlTag'),
        ));
        
        
    }
}

