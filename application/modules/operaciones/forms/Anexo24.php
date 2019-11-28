<?php

class Operaciones_Form_Anexo24 extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {
        $this->setIsArray(true);
        $this->_addClassNames('well');
        $this->setAction("/operaciones/index/anexo-24");
        $this->setMethod('POST');
        
        $this->addElement('select', 'aduana', array(
            'label' => 'Aduana:',
            'placeholder' => 'Aduana:',
            'class' => 'focused',
            'multiOptions' => array(
                '' => '-- Aduana --',
                '9999,999' => 'Todas',
                '3589,640' => '640 - Operaciones especiales (Querétaro)',
                '3589,646' => '646 - Aeropuerto intercontinental Queretaro',
                '3589,240' => '240 - Nuevo Laredo (3589)',
                '3589,370' => '370 - Chiapas (3589)',
                '3574,160' => '160 - Manzanillo (3574)',
                '3574,470' => '470 - AICM (3574)',
                '3933,430' => '430 - PYM (3933)',
            ),
            'attribs' => array('style' => 'width: 550px'),
        ));

        $model = new Operaciones_Model_ClientesAnexo24Mapper();
        $array = $model->todos();
        $this->addElement('select', 'rfc', array(
            'label' => 'RFC del cliente:',
            'placeholder' => 'RFC del cliente:',
            'class' => 'focused',
            'multiOptions' => $array,
            'attribs' => array('style' => 'width: 550px'),
        ));

        $years = array();
        foreach (range(date('Y'), 2012, -1) as $number) {
            $years[$number] = $number;
        }
        $this->addElement('select', 'year', array(
            'label' => 'Año:',
            'placeholder' => 'Año:',
            'class' => 'focused',
            'multiOptions' => $years,
            'value' => date('Y'),
        ));

        $this->addElement('select', 'month', array(
            'label' => 'Mes:',
            'class' => 'focused',
            'multiOptions' => array(
                '1' => 'Enero',
                '2' => 'Febrero',
                '3' => 'Marzo',
                '4' => 'Abril',
                '5' => 'Mayo',
                '6' => 'Junio',
                '7' => 'Julio',
                '8' => 'Agosto',
                '9' => 'Septiembre',
                '10' => 'Octubre',
                '11' => 'Noviembre',
                '12' => 'Diciembre',
            ),
            'value' => date('m'),
        ));

        $this->addElement('select', 'tipo', array(
            'label' => 'Layout:',
            'placeholder' => 'Layout:',
            'class' => 'focused span4',
            'multiOptions' => array(
                '' => '-- Layout --',
                'header' => 'Encabezado pedimentos',
                'parcial' => 'PRASAD',
                'extendido' => 'Extendido (Completo)',
                'techops' => 'OAQ Tech Ops',
                'proveedores' => 'Proveedores',
            ),
            'value' => 'extendido',
        ));

        $this->addElement('select', 'ie', array(
            'label' => 'I/E:',
            'placeholder' => 'I/E:',
            'class' => 'focused span2',
            'multiOptions' => array(
                '' => '-- E/I --',
                'IMP' => 'Importaciones',
                'EXP' => 'Exportaciones',
            ),
            'value' => 'extendido',
        ));

        $this->addElement('button', 'submit', array(
            'label' => 'Ver reporte',
            'type' => 'submit',
            'buttonType' => 'primary',
            'decorators' => Array('ViewHelper', 'HtmlTag'),
            'attribs' => array('onclick' => 'viewReport();', 'style' => 'float: left;'),
        ));
        
        $this->addElement('button', 'excel', array(
            'label' => 'Excel',
            'type' => 'submit',
            'buttonType' => 'success',
            'decorators' => Array('ViewHelper', 'HtmlTag'),
            'attribs' => array('onclick' => 'viewReportExcel();', 'style' => 'margin-left: 5px;'),
        ));
    }

}
