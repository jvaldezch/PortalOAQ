<?php

class Operaciones_Form_M3Search extends Twitter_Bootstrap_Form_Horizontal
{
    public function init()
    {
        $this->setIsArray(true);
        $this->setElementsBelongTo('bootstrap');
        
        $this->_addClassNames('well');
        
        $this->setAction("/operaciones/index/archivos-de-validacion");
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
        
        /*$this->setName('search-validation')
                ->setAttrib('enctype', 'multipart/form-data')
                ->setMethod('post')
                ->setAction('/operaciones/index/resultado')
                ->setDecorators(array(
                    'FormElements',
                    array('HtmlTag', array(
                        'tag' => 'div',
                        'class'=>'ym-form'
                    )),
                    'Form'
                ));
        
        $customersMapper = new Application_Model_CustomersMapper();
        $customers = $customersMapper->getCustomers();
        
        $clientes = new Zend_Form_Element_Select('clientes');
        $clientes->addMultiOption('', '-- Seleccione cliente --');
        $i = 1;
        foreach ($customers as $customer):            
            $cliente = $customer['nombre'] ? $customer['nombre'] : $customer['rfc'];            
            $clientes->addMultiOption($customer['rfc'], $cliente);        
        endforeach;
        $clientes->setDecorators(array(
                        array('ViewHelper'),
                        array('Description'),
                        array('HtmlTag'),
                    )
                )
                ->setAttrib('style', 'width: 400px');
        
        $pedimento = new Zend_Form_Element_Text('pedimento');
        $pedimento->setDecorators(array(
                        array('ViewHelper'),
                        array('Description'),
                        array('HtmlTag'),
                    )
                );
        
        $nombre = new Zend_Form_Element_Text('nombre');
        $nombre->setDecorators(array(
                        array('ViewHelper'),
                        array('Description'),
                        array('HtmlTag'),
                    )
                );
        
        $patente = new Zend_Form_Element_Text('patente');
        $patente->setDecorators(array(
                        array('ViewHelper'),
                        array('Description'),
                        array('HtmlTag'),
                    )
                );
        
        $aduana = new Zend_Form_Element_Text('aduana');
        $aduana->setDecorators(array(
                        array('ViewHelper'),
                        array('Description'),
                        array('HtmlTag'),
                    )
                );
        
        $fechaini = new Zend_Form_Element_Text('fecha_inicio');
        $fechaini->setAttrib('id', 'datepicker_begin')
                ->setDecorators(array(
                        array('ViewHelper'),
                        array('Description'),
                        array('HtmlTag'),
                    )
                );
        
        $fechafin = new Zend_Form_Element_Text('fecha_fin');
        $fechafin->setAttrib('id', 'datepicker_end')
                ->setDecorators(array(
                        array('ViewHelper'),
                        array('Description'),
                        array('HtmlTag'),
                    )
                );
        
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setlabel('Consultar')
                ->setAttrib('class', 'btn')
                ->setAttrib('style', 'margin-top: 15px; height: 30px')
                ->setDecorators(array(
                        array('ViewHelper'),
                        array('HtmlTag'),
                ))
                ->removeDecorator('htmlTag');
        
        $coves = new Zend_Form_Element_Submit('coves');
        $coves->setlabel('COVES')
                ->setAttrib('class', 'ym-button')
                ->setDecorators(array(
                        array('ViewHelper'),
                        array('HtmlTag'),
                ))
                ->removeDecorator('htmlTag');
        
        $reset = new Zend_Form_Element_Reset('reset');
        $reset->setlabel('Limpiar')
                ->setAttrib('class', 'btn')
                ->setDecorators(array(
                        array('ViewHelper'),
                        array('HtmlTag'),
                ))
                ->removeDecorator('htmlTag');
        
        $this->addElement($clientes)
                ->addElement($pedimento)
                ->addElement($patente)
                ->addElement($aduana)
                ->addElement($nombre)
                ->addElement($fechaini)
                ->addElement($fechafin)
                ->addElement($submit)
                ->addElement($reset)
                ->addElement($coves);*/
    }
}
