<?php

class Clientes_Form_ReporteAnexo24 extends Twitter_Bootstrap_Form_Horizontal
{
    protected $_rfc = null;
    
    protected function setRfc($rfc = true){
        $this->_rfc = $rfc;
    }
    
    public function init()
    {   
        $this->setIsArray(true);
        $this->setElementsBelongTo('bootstrap');
        $this->setAction("/clientes/index/reporte-anexo-24");

        $this->_addClassNames('well');
        
        $this->addElement('select', 'year', array(
            'label'         => 'Año',
            'class'         => 'focused',
            'multiOptions' => array(
                '2013'=>'2013',
                '2014'=>'2014',
                ),
            'attribs' => array('style'=>'width:200px'),
            'value' => date('Y'),
        ));
        
        $this->addElement('select', 'month', array(
            'label'         => 'Mes',
            'class'         => 'focused',            
            'multiOptions' => array(
                '1'=>'Enero',
                '2'=>'Febrero',
                '3'=>'Marzo',
                '4'=>'Abril',
                '5'=>'Mayo',
                '6'=>'Junio',
                '7'=>'Julio',
                '8'=>'Agosto',
                '9'=>'Septiembre',
                '10'=>'Octubre',
                '11'=>'Noviembre',
                '12'=>'Diciembre',
                ),
            'attribs' => array('style'=>'width:200px'),
        ));

        $reportMapper = new Clientes_Model_ReportesMapper();
        $tipos = $reportMapper->tiposReportes($this->_rfc);
        $types = array();
        if(isset($tipos)) {
            foreach($tipos as $t) {
                $types[$t["tipo"]] = $t["desc_reporte"];
            }
        }

        $this->addElement('select', 'tipo', array(
            'label'         => 'Tipo de reporte',
            'class'         => 'focused',            
            'multiOptions' => $types,
            'attribs' => array('style'=>'width:200px'),
        ));
        
        $reportMapper = new Clientes_Model_ReportesMapper();
        $reps = $reportMapper->obtenerAduanas($this->_rfc);
        $reports = array();
        if(isset($reps)) {
            foreach($reps as $r) {
                $reports[$r["aduana"]] = $r["aduana"] . " - " . $r["descripcion"];
            }            
            $this->addElement('select', 'aduana', array(
                'label'         => 'Aduana',
                'class'         => 'focused',
                'attribs' => array('style'=>'width:380px'),
                'multiOptions' => $reports,
            ));
        } else {
            $this->addElement('select', 'aduana', array(
                'label'         => 'Aduana',
                'class'         => 'focused',
                'multiOptions' => $reports,
                'attribs' => array('disabled'=>'true','style'=>'width:150px'),
            ));
        }
        
        $this->addElement('button', 'submit', array(
            'label'         => 'Generar reporte',
            'type'          => 'submit',
            'buttonType'    => 'primary',
            'decorators'    => Array('ViewHelper','HtmlTag'),
        ));        
    }
}

