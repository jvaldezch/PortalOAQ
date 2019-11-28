<?php

class Trafico_Form_NuevaOperacion extends Twitter_Bootstrap_Form_Horizontal {

    protected $_edit = null;

    protected function setEdit($edit = null) {
        $this->_edit = $edit;
    }

    public function init() {
        // https://github.com/Emagister/zend-form-decorators-bootstrap
        // http://www.zfforums.com/zend-framework-components-13/model-view-controller-mvc-21/form-validator-notempty-without-being-required-3573.html
        $this->setIsArray(true);
        $this->setElementsBelongTo('bootstrap');

        $this->setMethod("POST");

        $this->_addClassNames('well');

        $required = true;
        if ($this->_edit) {
            $required = false;
        }

        $this->addElement('text', 'nombre', array(
            'label' => 'Nombre cliente',
            'class' => 'focused',
            'dimension' => 5,
            'attribs' => array('autocomplete' => 'off'),
            'required' => $required,
            'validators' => array(
                array('validator' => 'NotEmpty', 'breakChainOnFailure' => true, 'options' => array('messages' => array(
                            'isEmpty' => 'Debe especificar el cliente'
                        ))),
            )
        ));

        $this->addElement('hidden', 'rfc', array());

        $this->addElement('text', 'referencia', array(
            'label' => 'Referencia',
            'placeholder' => 'Referencia',
            'class' => 'focused',
            'required' => $required,
            'dimension' => 2,
            'validators' => array(
                array('validator' => 'NotEmpty', 'breakChainOnFailure' => true, 'options' => array('messages' => array(
                            'isEmpty' => 'Debe especificar la referencia'
                        ))),
            )
        ));

        $this->addElement('text', 'pedimento', array(
            'label' => 'Pedimento',
            'placeholder' => 'Pedimento',
            'class' => 'focused',
            'required' => $required,
            'dimension' => 2,
            'validators' => array(
                array('validator' => 'NotEmpty', 'breakChainOnFailure' => true, 'options' => array('messages' => array(
                            'isEmpty' => 'Debe especificar el pedimento'
                        ))),
                array('validator' => 'Digits', 'breakChainOnFailure' => true, 'options' => array('messages' => array(
                            'notDigits' => 'Pedimento debe contener solo números'
                        ))),
            ),
        ));

        $this->addElement('select', 'cvedoc', array(
            'label' => 'Clave de pedimento',
            'placeholder' => 'Clave de pedimento',
            'class' => 'focused',
            'multiOptions' => array(
                'A1' => 'A1',
                'IN' => 'IN',
            ),
            'dimension' => 1,
        ));

        $this->addElement('text', 'fechanotif', array(
            'label' => 'Fecha de notificación o prealerta',
            'prepend' => '<i class="icon-calendar"></i>',
            'class' => 'focused',
            'dimension' => 2,
        ));

        $this->addElement('text', 'fechadoc', array(
            'label' => 'Fecha de documentación',
            'prepend' => '<i class="icon-calendar"></i>',
            'class' => 'focused',
            'dimension' => 2,
        ));

        $this->addElement('text', 'bl', array(
            'label' => 'Número de BL',
            'class' => 'focused',
            'dimension' => 3,
        ));

        $this->addElement('text', 'fecharevalidacion', array(
            'label' => 'Fecha de revalidación',
            'prepend' => '<i class="icon-calendar"></i>',
            'class' => 'focused',
            'dimension' => 2,
        ));

        $this->addElement('text', 'contenedor', array(
            'label' => 'Contenedor / carga suelta',
            'class' => 'focused',
            'dimension' => 3,
        ));

        $this->addElement('text', 'operadora', array(
            'label' => 'Operadora / almacen',
            'class' => 'focused',
            'dimension' => 3,
        ));

        $this->addElement('text', 'diaslibres', array(
            'label' => 'Días libres almacenaje',
            'class' => 'focused',
            'dimension' => 1,
        ));

        $this->addElement('text', 'fechaarribo', array(
            'label' => 'Fecha de arribo',
            'prepend' => '<i class="icon-calendar"></i>',
            'class' => 'focused',
            'dimension' => 2,
        ));

        $this->addElement('text', 'fechasolicitud', array(
            'label' => 'Fecha de solicitud anticipo',
            'prepend' => '<i class="icon-calendar"></i>',
            'class' => 'focused',
            'dimension' => 2,
        ));

        $this->addElement('text', 'fecharecepanti', array(
            'label' => 'Fecha de recepción anticipo',
            'prepend' => '<i class="icon-calendar"></i>',
            'class' => 'focused',
            'dimension' => 2,
        ));

        $this->addElement('text', 'fechaprevio', array(
            'label' => 'Fecha previo',
            'prepend' => '<i class="icon-calendar"></i>',
            'class' => 'focused',
            'dimension' => 2,
        ));

        $this->addElement('text', 'fechappedimento', array(
            'label' => 'Fecha pago pedimento',
            'prepend' => '<i class="icon-calendar"></i>',
            'class' => 'focused',
            'dimension' => 2,
        ));

        $this->addElement('text', 'fechadespacho', array(
            'label' => 'Fecha despacho',
            'prepend' => '<i class="icon-calendar"></i>',
            'class' => 'focused',
            'dimension' => 2,
        ));

        $this->addElement('text', 'resultseleccion', array(
            'label' => 'Resultado selección',
            'class' => 'focused',
            'dimension' => 5,
        ));

        $this->addElement('text', 'fechaeir', array(
            'label' => 'Fecha de EIR entregado',
            'prepend' => '<i class="icon-calendar"></i>',
            'class' => 'focused',
            'dimension' => 2,
        ));

        $this->addElement('text', 'fechaexp', array(
            'label' => 'Fecha entrega expediente',
            'prepend' => '<i class="icon-calendar"></i>',
            'class' => 'focused',
            'dimension' => 2,
        ));

        $this->addElement('text', 'fechaenvio', array(
            'label' => 'Fecha de envio a OAQ',
            'prepend' => '<i class="icon-calendar"></i>',
            'class' => 'focused',
            'dimension' => 2,
        ));

        $this->addElement('textarea', 'observaciones', array(
            'label' => 'Observaciones',
            'placeholder' => 'Observaciones',
            'dimension' => 5,
            'attribs' => array('style' => 'height: 150px'),
        ));

        $submitLabel = 'Generar operación';
        if ($this->_edit) {
            $submitLabel = 'Guardar cambios';
        }
        $this->addElement('button', 'submit', array(
            'label' => $submitLabel,
            'type' => 'submit',
            'buttonType' => 'primary',
            'decorators' => Array('ViewHelper', 'HtmlTag'),
            'attribs' => array('style' => 'margin-top:5px; margin-right: 5px'),
        ));
    }

}
