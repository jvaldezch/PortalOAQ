<?php

class Administracion_Form_CrearPoliza extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {
        $this->setIsArray(false);
        $this->addElement('text', 'poliza', array(
            'placeholder' => 'Poliza',
            'class' => 'focused',
            'required' => true,
            'filters' => array('StringTrim', 'StringToUpper'),
            'validators' => array(array(
                    'StringLength', false, array(3, 9, 'messages' => array(
                            Zend_Validate_StringLength::TOO_SHORT => 'Patente debe tener minimo 3 digitos.',
                            Zend_Validate_StringLength::TOO_LONG => 'Patente debe tener maximo 9 digitos.',
                            Zend_Validate_StringLength::INVALID => 'No válido',
                        ))), array(
                    'Digits', false, array('messages' => array(
                            Zend_Validate_Digits::NOT_DIGITS => 'Solo debe contener digitos.',
                            Zend_Validate_Digits::STRING_EMPTY => '',
                        ))), array(
                    'NotEmpty', false, array('messages' => array(
                            Zend_Validate_NotEmpty::IS_EMPTY => 'No debe estar vacio',
                        )))),
        ));
        
        $mapper = new Administracion_Model_DocumentosPolizasMapper();
        $data = array();
        foreach ($mapper->fetchAll() as $item) {
            $data[$item["id"]] = mb_strtoupper($item["tipoPoliza"], 'UTF-8');
        }
        
        $this->addElement('radio', 'tipoPoliza', array(
            'placeholder' => 'Tipo',
            'class' => 'focused',
            'multiOptions' => $data,
        ));

        $this->addElement('select', 'tipoArchivo', array(
            'placeholder' => 'Tipo',
            'class' => 'focused',
            'multiOptions' => array(
                '' => '---',
            ),
            'attribs' => array('disabled' => 'disable')
        ));

        $this->addElement('text', 'fecha', array(
            'placeholder' => 'Fecha',
        ));

        $this->addElement('text', 'folio', array(
            'placeholder' => 'Folio',
        ));

        $this->addElement('text', 'importe', array(
            'placeholder' => 'Importe',
        ));

        $this->addElement('textarea', 'factura', array(
            'placeholder' => 'Número de factura',
            'attribs' => array('style' => 'width: 250px; height: 100px')
        ));
        
        $this->addElement('textarea', 'observaciones', array(
            'placeholder' => 'Observaciones',
            'attribs' => array('style' => 'width: 250px; height: 100px')
        ));

        $this->addElement('text', 'transferencia', array(
            'placeholder' => 'Transferencia',
            'class' => 'focused',
        ));

        $this->addElement('text', 'recibo', array(
            'placeholder' => 'Recibo',
            'class' => 'focused',
        ));

        $this->addElement('text', 'cliente', array(
            'placeholder' => 'Beneficiario',
            'class' => 'focused',
        ));
    }

}
