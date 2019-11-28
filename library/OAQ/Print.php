<?php

require "tcpdf/pedimentositawin.php";

class OAQ_Print {

    protected $_dir;
    protected $_filename;
    protected $_data;

    function set_dir($_dir) {
        $this->_dir = $_dir;
    }
    
    function clearData() {
        $this->_data = [];
    }

    function set_data($_data) {
        $this->_data = $_data;
    }
    
    function get_filename() {
        return $this->_filename;
    }

    function __construct() {
        $this->_data["colors"]["line"] = array(5, 5, 5);
        $this->_data["copia"] = 2;
        $this->_data["codigoBarras"] = true;
        $this->_data["sis"] = 'SITA';
    }

    protected function _fileExists() {
        if (file_exists($this->_dir . DIRECTORY_SEPARATOR . $this->_filename)) {
            return true;
        }
        return false;
    }

    public function printPedimentoSitawin() {
        self::__construct();
        $this->_filename = 'PED_' . $this->_data["aduana"] . '-' . $this->_data["patente"] . '-' . $this->_data["pedimento"] . '.pdf';
//        if (!$this->_fileExists()) {
            $pdf = new PedimentoSitawin($this->_data, 'P', 'pt', 'LETTER');
            $pdf->PedimentoUnico();
            //$pdf->Output($this->_dir . DIRECTORY_SEPARATOR . $this->_filename, 'F');
            $pdf->Output($this->_dir . DIRECTORY_SEPARATOR . $this->_filename, 'I');
            //$this->_sendToBrowser();
//        } else {
//            $this->_sendToBrowser();
//        }
    }

    public function printPedimentoSimplificadoSitawin() {
        self::__construct();
        $this->_filename = 'PED_' . $this->_data["aduana"] . '-' . $this->_data["patente"] . '-' . $this->_data["pedimento"] . '_SIMP.pdf';
//        if (!$this->_fileExists()) {
            $this->_data["transportista"] = true;
            $pdf = new PedimentoSitawin($this->_data, 'P', 'pt', 'LETTER');
            $pdf->PedimentoSimplificado();
            $pdf->Output($this->_dir . DIRECTORY_SEPARATOR . $this->_filename, 'F');
            $this->_sendToBrowser();
//        } else {
//            $this->_sendToBrowser();
//        }
    }
    
    public function printChecklist($referencia) {
        self::__construct();
        $this->_filename = "CHECKLIST_" . $referencia . ".pdf";
    }

    protected function _sendToBrowser() {
        if ($this->_save == true) {
            return true;
        }
        if (file_exists($this->_dir . DIRECTORY_SEPARATOR . $this->_filename)) {
            header("Content-type:application/pdf");
            header('Content-Disposition:attachment;filename="' . $this->_filename . '"');
            readfile($this->_dir . DIRECTORY_SEPARATOR . $this->_filename);
        } else {
            throw new Exception("Craps! File doesn't exists.");
        }
    }

}
