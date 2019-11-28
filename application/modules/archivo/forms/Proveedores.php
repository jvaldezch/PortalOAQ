<?php

class Archivo_Form_Proveedores extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {
        
        $decorators = array("ViewHelper", "Errors", "Label");
        
        $this->addElement('select', 'rfc', array(
            'multiOptions' => array(
                '' => '-- Seleccionar --',
                'TLO050804QY7' => 'TERMINAL LOGISTICS, S.A. DE C.V.',
                'IEY091126RR3' => 'INVERSORA EY SA DE CV',
                'MEA1206064Z9' => 'MULTISERVICIOS EMPRESARIALES, ADMINISTRATIVOS Y DE COMERCIO S DE RL DE CV',
                'VAHM660429IT3' => 'MYRIAM VALLARINO HERNANDO',
                'CEE821001QQ2' => 'CENTRO EMPRESARIAL DEL ESTADO DE QUERETARO SINDICATO PATRONAL',
                'DOSM700928KR4' => 'MIGUEL ANGEL DOPHE SALINAS',
                'COC970908418' => 'COCOFI, S.C.',
            ),
            'class' => 'traffic-select-large',
            'required' => true,
            'attribs' => array('style' => 'width: 350px;'),
            'validators' => array(
                array('validator' => 'NotEmpty', 'breakChainOnFailure' => true, 'options' => array('messages' => array(
                            'isEmpty' => 'Debe seleccionar un proveedor'
                        ))),
            ),
            "decorators" => $decorators,
        ));

        $this->addElement('text', 'folio', array(
            'class' => 'traffic-input-small',
            "decorators" => $decorators,
        ));

        $this->addElement('text', 'rfcCliente', array(
            'class' => 'traffic-input-medium',
            "decorators" => $decorators,
        ));

        $this->addElement('text', 'fechaIni', array(
            'class' => 'traffic-input-date',
            'attribs' => array('name' => 'fechaIni'),
            "decorators" => $decorators,
        ));

        $this->addElement('text', 'fechaFin', array(
            'class' => 'traffic-input-date',
            'attribs' => array('name' => 'fechaFin'),
            "decorators" => $decorators,
        ));

        $this->addElement('button', 'submit', array(
            'label' => 'Buscar',
            'type' => 'submit',
            'buttonType' => 'primary',
            'decorators' => Array('ViewHelper', 'HtmlTag'),
            'attribs' => array('style' => 'margin-top:5px;margin-right: 5px'),
        ));
    }

}
