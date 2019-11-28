<?php

class Archivo_Form_PedimentosPagados extends Twitter_Bootstrap_Form_Horizontal
{
    public function init()
    {   
        // https://github.com/Emagister/zend-form-decorators-bootstrap
        $this->setIsArray(true);
        $this->setAction('/archivo/index/pedimentos-pagados');
        $this->setMethod('GET');

        $this->_addClassNames('well');

        $this->addElement('select', 'rfc', array(
            'label'         => 'RFC del cliente',
            'placeholder'   => 'RFC del cliente',
            'class'         => 'focused',
            'dimension'     => 6,
            'multiOptions'          => array(
                'DCM030212ET4'  =>  'DCM030212ET4 - DIEHL CONTROLS',
                'INO891205K76'  =>  'INO891205K76',
                'JMM931208JY9'  =>  'JMM931208JY9 - JOHNSON MATTHEY',
                'RHM720412B61'  =>  'RHM720412B61 - ROHM AND HAAS MEXICO S. DE R.L. DE C.V.',
            ),
        ));
        
        $this->addElement('text', 'fechaIni', array(
            'label'         => 'Fecha de consulta',
            'placeholder'   => 'Fecha de consulta',
            'prepend'       => '<i class="icon-calendar" id="date-init"></i>',
            'class'         => 'focused',
            'dimension'     => 2,
            'attribs'       => array('name' => 'fechaIni','style' => 'width:150px')
        ));
        
        $this->addElement('text', 'fechaFin', array(
            'label'         => 'Fecha de consulta',
            'placeholder'   => 'Fecha de consulta',
            'prepend'       => '<i class="icon-calendar" id="date-init"></i>',
            'class'         => 'focused',
            'dimension'     => 2,
            'attribs'       => array('name' => 'fechaFin','style' => 'width:150px')
        ));
        
        $this->addElement('text', 'archivom3', array(
            'label'         => 'Nombre del archivo',
            'placeholder'   => 'Nombre del archivo',
            'dimension'     => 3,
        ));
        
        $this->addElement('radio', 'archivo', array(
            'label'=>'Archivo',
            'multiOptions'=>array(
                'validacion' => 'Validación (M)',
                'respuesta' => 'Respuesta (.err)',
                'pago' => 'Pago',
            ),
            'value' => 'validacion',
        ));
        
        $this->addElement('text', 'pedimento', array(
            'label'         => 'Pedimento',
            'placeholder'   => 'Pedimento',
            'dimension'     => 2,
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

