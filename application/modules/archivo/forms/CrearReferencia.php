<?php

class Archivo_Form_CrearReferencia extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {
        // https://github.com/Emagister/zend-form-decorators-bootstrap
        // http://www.god-object.com/2010/01/08/zend_form-validators-and-custom-error-messages/
        $this->setIsArray(false);

        $this->_addClassNames('well');

        $this->setAction("/archivo/index/crear-referencia");
        $this->setMethod('GET');

        $this->addElement('text', 'patente', array(
            'label' => 'Patente *',
            'placeholder' => 'Patente',
            'class' => 'focused',
            'required' => true,
            'filters' => array('StringTrim', 'StringToUpper'),
            'validators' => array(array(
                    'StringLength', false, array(4, 4, 'messages' => array(
                            Zend_Validate_StringLength::TOO_SHORT => 'Patente debe tener 4 digitos.',
                            Zend_Validate_StringLength::TOO_LONG => 'Patente debe tener 4 digitos.',                          
                            Zend_Validate_StringLength::INVALID => 'No válido',
                        ))), array(
                    'Digits',false, array('messages' => array( 
                            Zend_Validate_Digits::NOT_DIGITS => 'Solo debe contener digitos.',
                            Zend_Validate_Digits::STRING_EMPTY => '',
                        ))), array(
                    'NotEmpty',false,array('messages'=>array(
                            Zend_Validate_NotEmpty::IS_EMPTY => 'No debe estar vacio',
                        )))),
        ));

        $this->addElement('text', 'aduana', array(
            'label' => 'Aduana *',
            'placeholder' => 'Aduana',
            'class' => 'focused',
            'required' => true,
            'filters' => array('StringTrim', 'StringToUpper'),
            'validators' => array(array(
                    'StringLength', false, array(3, 3, 'messages' => array(
                            Zend_Validate_StringLength::TOO_SHORT => 'Aduana debe tener 3 digitos.',
                            Zend_Validate_StringLength::TOO_LONG => 'Aduana debe tener 3 digitos.',
                        ))), array(
                    'Digits',false, array('messages' => array( 
                            Zend_Validate_Digits::NOT_DIGITS => 'Solo debe contener digitos.',
                            Zend_Validate_Digits::STRING_EMPTY => '',
                        ))), array(
                    'NotEmpty',false,array('messages'=>array(
                            Zend_Validate_NotEmpty::IS_EMPTY => 'No debe estar vacio',
                        )))),
        ));

        $this->addElement('text', 'referencia', array(
            'label' => 'Referencia *',
            'placeholder' => 'Referencia',
            'class' => 'focused',
            'required' => true,
            'filters' => array('StringTrim', 'StringToUpper'),
            'validators' => array(array(
                    'NotEmpty',false,array('messages'=>array(
                            Zend_Validate_NotEmpty::IS_EMPTY => 'No debe estar vacio',
                        )))),
        ));

        $this->addElement('button', 'submit', array(
            'label' => 'Crear referencia',
            'type' => 'submit',
            'buttonType' => 'primary',
            'decorators' => Array('ViewHelper', 'HtmlTag'),
            'attribs' => array('style' => 'margin-top:5px'),
        ));
    }

}
