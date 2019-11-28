<?php

class Administracion_Form_VerArchivos extends Twitter_Bootstrap_Form_Horizontal {

    public function init() {
        $this->setIsArray(false);        
        
        $this->addElement('text', 'poliza', array(
            'placeholder' => 'Poliza',
        ));

        $mapper = new Administracion_Model_DocumentosPolizasMapper();
        $data = array();
        foreach ($mapper->fetchAll() as $item) {
            $data[$item["id"]] = mb_strtoupper($item["tipoPoliza"], 'UTF-8');
        }

        $this->addElement('select', 'tipoPoliza', array(
            'placeholder' => 'Tipo',
            'class' => 'focused',
            'multiOptions' => $data,
            'attribs' => array('disabled' => 'disable')
        ));

        $mapper = new Administracion_Model_DocumentosArchivosMapper();
        $data = array();
        foreach ($mapper->fetchAll() as $item) {
            $data[$item["id"]] = mb_strtoupper($item["tipoArchivo"], 'UTF-8');
        }

        $this->addElement('select', 'tipoArchivo', array(
            'placeholder' => 'Tipo',
            'class' => 'focused',
            'multiOptions' => $data,
            'attribs' => array('disabled' => 'disable')
        ));

        $this->addElement('text', 'fecha', array(
            'placeholder' => 'Fecha',
            'attribs' => array('disabled' => 'disable')
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
