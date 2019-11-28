<?php

class Archivo_Form_ReferenciasCorresponsal extends Twitter_Bootstrap_Form_Horizontal
{
    function __construct() {
        parent::__construct();
        $this->addElement('hash','csrf_token',array(
            'salt' => get_class($this) . 'sed34342dy287@2323',
        ));
    }
    
    public function init()
    {   
        // https://github.com/Emagister/zend-form-decorators-bootstrap
        $this->setIsArray(true);
        $this->setElementsBelongTo('bootstrap');
        
        $this->_addClassNames('well');
        
        $this->setAction("/archivo/index/referencias");
        $this->setMethod('POST');
        
        $this->addElement('text', 'referencia', array(
            'label'         => 'Referencia',
            'placeholder'   => 'Referencia',
            'class'         => 'focused',
            'required'      => true,
            'validators'    => array(
                array('validator' => 'NotEmpty', 'breakChainOnFailure' => true, 'options' => array('messages' => array(
                    'isEmpty' => 'Debe especificar una referencia a buscar'
                    ))),
                ),
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

