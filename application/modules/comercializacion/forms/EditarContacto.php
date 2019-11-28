<?php

class Comercializacion_Form_EditarContacto extends Twitter_Bootstrap_Form_Horizontal
{
    protected $_id = null;
    
    protected function setId($id){
        $this->_id = $id;
    }
    
    public function init()
    {   
        // https://github.com/Emagister/zend-form-decorators-bootstrap        
        // http://getbootstrap.com/2.3.2/base-css.html#icons
        // http://www.plugolabs.com/twitter-bootstrap-button-generator/
        $this->setIsArray(true);
        $this->setElementsBelongTo('bootstrap');
        
        $this->_addClassNames('well');
        
        $this->setAction("/comercializacion/index/editar-contacto?id={$this->_id}");
        $this->setMethod('POST');

        $this->addElement('text', 'nombre', array(
            'label'         => 'Nombre',
            'placeholder'   => 'Nombre',
            'class'         => 'focused',
            'attribs' => array(
                'autocomplete' => 'off',
                'style' => 'width: 550px',
                ),
        ));
        
        $this->addElement('text', 'email', array(
            'label'         => 'Email',
            'placeholder'   => 'Email',
            'class'         => 'focused',
            'prepend'       => '<i class="icon-envelope"></i>',
            'attribs' => array(
                'autocomplete' => 'off',
                'style' => 'width: 450px',
                ),
        ));
        
        $this->addElement('select', 'tipo', array(
            'label'         => 'Tipo de contacto',
            'placeholder'   => 'Tipo de contacto',
            'class'         => 'focused',
            'multiOptions' => array(
                1 => 'Cartera',
                2 => 'Trafico',
                ),
        ));

        $this->addElement('button', 'submit', array(
            'label'         => 'Guardar cambios',
            'type'          => 'submit',            
            'buttonType'    => 'primary',
            'decorators'    => Array('ViewHelper','HtmlTag'),
            'attribs' => array('style' => 'margin-top:5px; margin-right: 5px'),
        ));
        
    }
}

