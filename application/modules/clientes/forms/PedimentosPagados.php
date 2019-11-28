<?php

class Clientes_Form_PedimentosPagados extends Twitter_Bootstrap_Form_Horizontal
{
    protected $_id_cli = null;
    
    protected function setId($id_cli){
        $this->_id_cli = $id_cli;
    }
    
    public function init()
    {   
        // https://github.com/Emagister/zend-form-decorators-bootstrap
        $this->setIsArray(true);
        $this->setElementsBelongTo('bootstrap');
        
        $this->_addClassNames('well');
        
        $this->setAction("/clientes/index/pedimentos-pagados");
        $this->setMethod('POST');
        
        $this->addElement('text', 'rfc', array(
            'label'         => 'RFC del cliente',
            'placeholder'   => 'RFC del cliente',
            'prepend'       => '<i class="icon-th-list" id="customer-list" style="cursor:pointer"></i>',
            'class'         => 'focused',
            'attribs' => array('readonly' => 'true'),
        ));
        
        $sispeds = array();
        $sis = new Usuarios_Model_SisPedimentosMapper();
        $peds = $sis->getSystems();
        foreach($peds as $item) {
            $sispeds[$item['id']] = 'Patente ' . $item['patente'] . ', Aduana ' . $item["aduana"] . ', ' . $item['ubicacion'];
        }
        
        $this->addElement('select', 'aduana', array(
            'label'         => 'Aduana',
            'placeholder'   => 'Aduana',
            'class'         => 'focused span6',
            'multiOptions' => $sispeds,
        ));
        
        $this->addElement('text', 'fechaIni', array(
            'label'         => 'Fecha de inicio',
            'placeholder'   => 'Fecha de inicio',
            'prepend'       => '<i class="icon-calendar"></i>',
            'class'         => 'focused',
            'dimension'     => 2,
            'attribs'       => array('name'=>'fechaIni','style'=>'width:150px')
        ));
        
        $this->addElement('text', 'fechaFin', array(
            'label'         => 'Fecha fin',
            'placeholder'   => 'Fecha fin',
            'prepend'       => '<i class="icon-calendar"></i>',
            'class'         => 'focused',
            'dimension'     => 2,
            'attribs' => array('name'=>'fechaFin','style'=>'width:150px','readonly' => 'true')
        ));

        $this->addElement('button', 'submit', array(
            'label'         => 'Crear reporte',
            'type'          => 'submit',            
            'buttonType'    => 'primary',
            'decorators'    => Array('ViewHelper','HtmlTag'),
            'attribs' => array('style' => 'margin-top:5px'),
        ));
        
    }
}

