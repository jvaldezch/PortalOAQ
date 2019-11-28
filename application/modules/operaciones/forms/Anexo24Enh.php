<?php

class Operaciones_Form_Anexo24Enh extends Twitter_Bootstrap_Form_Horizontal {
    
    protected $rfcs;
    
    function getRfcs() {
        return $this->rfcs;
    }

    function setRfcs($rfcs) {
        $this->rfcs = $rfcs;
    }

    public function init() {
        
        $mppr = new Trafico_Model_TraficoAduanasMapper();
        $arr = $mppr->obtenerReporteo();
        $array = array("" => "---");
        foreach ($arr as $key => $value) {
            $array[$value["id"]] = $value["patente"] . '-' . $value["aduana"] . " " . $value["nombre"];
        }

        $this->addElement("select", "idAduana", array(
            "placeholder" => "Aduana:",
            "class" => "focused span4",
            "multiOptions" => $array,
        ));
        
        $mdl = new Trafico_Model_ClientesMapper();
        if(isset($this->rfcs) && !empty($this->rfcs)) {
            $arr = $mdl->obtenerClientes($this->rfcs);            
        } else {
            $arr = $mdl->obtenerClientes();            
        }
        $array = array("" => "---");
        foreach ($arr as $key => $value) {
            $array[$value["id"]] = $value["nombre"];
        }
        
        $this->addElement("select", "idCliente", array(
            "placeholder" => "Cliente:",
            "class" => "focused span4",
            "multiOptions" => $array,
        ));

        $years = array();
        foreach (range(date("Y"), 2012, -1) as $number) {
            $years[$number] = $number;
        }
        $this->addElement("select", "year", array(
            "placeholder" => "Año:",
            "class" => "focused span4",
            "multiOptions" => $years,
            "value" => date("Y"),
        ));

        $this->addElement("select", "month", array(
            "class" => "focused span4",
            "multiOptions" => array(
                "1" => "Enero",
                "2" => "Febrero",
                "3" => "Marzo",
                "4" => "Abril",
                "5" => "Mayo",
                "6" => "Junio",
                "7" => "Julio",
                "8" => "Agosto",
                "9" => "Septiembre",
                "10" => "Octubre",
                "11" => "Noviembre",
                "12" => "Diciembre",
            ),
            "value" => date("m"),
        ));

        $this->addElement("text", "fechaIni", array(
            "class" => "focused span2",
            "value" => date("Y-m-" . "01"),
        ));

        $this->addElement("text", "fechaFin", array(
            "class" => "focused span2",
            "value" => date("Y-m-d"),
        ));
    }

}
