<?php

/**
 * Description of Vucem
 *
 * @author Jaime
 */
class OAQ_PrintEdocuments {

    protected $_dir;
    protected $_filename;
    protected $_directory;
    protected $_data;
    protected $_array;
    protected $_partidas;
    protected $_save = false;

    function get_dir() {
        return $this->_dir;
    }

    function set_dir($_dir) {
        $this->_dir = $_dir;
    }

    function set_data($_data) {
        $this->_data = $_data;
    }

    function set_save($_save) {
        $this->_save = $_save;
    }

    function set_array($_array) {
        $this->_array = $_array;
    }
    
    function set_partidas($_partidas) {
        $this->_partidas = $_partidas;
    }
    
    function __construct() {
    }

    protected function _prepare() {
        $this->_table->setIdCliente(1);
        $this->_mapper->buscar($this->_table);
        $this->_createDir();
        if (null == ($this->_table->getId())) {
            return false;
        } else {
            $this->_directory = $this->_table->getDirectorio();
            $this->_sendToBrowser();
            return true;
        }
    }
    
    public function obtener($id) {
        $this->_table->setId($id);
        $this->_table->setIdCliente(1);
        $this->_mapper->obtener($this->_table);
        if (null !== ($this->_table->getId())) {
            $this->_directory = $this->_table->getDirectorio();
            $this->_sendToBrowser();
            return true;
        }
    }

    protected function _fileExists() {
        if (file_exists($this->_dir . DIRECTORY_SEPARATOR . $this->_directory . DIRECTORY_SEPARATOR . $this->_table->getArchivo())) {
            return true;
        }
        return false;
    }

    public function printCove() {
        $this->_table->setArchivo($this->_data["edocument"] . ".pdf");
        if ($this->_prepare() == false) {
            require "tcpdf/acusevu.php";
            $pdf = new DetalleCoveVU($this->_data, "P", "pt", "LETTER");            
            $pdf->Create();
            $pdf->Output($this->_dir . DIRECTORY_SEPARATOR . $this->_directory . DIRECTORY_SEPARATOR . $this->_table->getArchivo(), "F");
            if (($this->_fileExists()) == true) {
                $this->_table->setDirectorio($this->_directory);
                $this->_mapper->save($this->_table);
                $this->_sendToBrowser();
            }
        }
    }
    
    public function viewCove() {
        $this->_table->setArchivo($this->_data["edocument"] . ".pdf");
        require "tcpdf/acusevu.php";
        $pdf = new DetalleCoveVU($this->_data, "P", "pt", "LETTER");
        $pdf->Create();
        if(file_exists($this->_dir . DIRECTORY_SEPARATOR . $this->_directory . DIRECTORY_SEPARATOR . $this->_table->getArchivo())) {
            unlink($this->_dir . DIRECTORY_SEPARATOR . $this->_directory . DIRECTORY_SEPARATOR . $this->_table->getArchivo());
        }
        $pdf->Output($this->_dir . DIRECTORY_SEPARATOR . $this->_directory . DIRECTORY_SEPARATOR . $this->_table->getArchivo(), "I");
    }

    public function saveEdocument($titulo = null) {
        require "tcpdf/acuseedocvu.php";            
        $pdf = new EdocumentVU($this->_data, "P", "pt", "LETTER");
        $pdf->Create();
        if (isset($titulo)) {
            $pdf->Output($this->_dir . DIRECTORY_SEPARATOR . $titulo . ".pdf", "F");
        } else {
            $pdf->Output($this->_dir . DIRECTORY_SEPARATOR . "EDOC_" . $this->_data["edoc"] . ".pdf", "F");            
        }
    }
    
    public function printDocument() {
        $this->_table->setArchivo("PED_" . $this->_data["aduana"] . "-" . $this->_data["patente"] . "-" . $this->_data["pedimento"] . ".pdf");
        if ($this->_prepare() == false) {
            require "tcpdf/pedimento.php";
            if(isset($this->_partidas)) {
                $this->_array["partidas"] = $this->_partidas;
            }
            $pdf = new Pedimento($this->_array, "P", "pt", "LETTER");
            $pdf->set_aduana($this->_data["aduana"]);
            $pdf->set_patente($this->_data["patente"]);
            $pdf->set_pedimento($this->_data["pedimento"]);
            $pdf->set_rfcCliente($this->_data["rfcCliente"]);
            $pdf->set_usuario("JVALDEZ");
            $pdf->set_sis("VUCEM");
            $pdf->set_referencia("NaN");
            $pdf->set_fechaPago($this->_data["fechaPago"]);
            $pdf->set_fechaEntrada($this->_data["fechaEntrada"]);
            $pdf->PedimentoDataStage();
            $pdf->Output($this->_dir . DIRECTORY_SEPARATOR . $this->_directory . DIRECTORY_SEPARATOR . $this->_table->getArchivo(), "F");
            if ($this->_fileExists()) {
                $this->_table->setDirectorio($this->_directory);
                $this->_mapper->save($this->_table);
                $this->_sendToBrowser();
            }
        }
    }
    
    public function viewDocument() {
        $this->_table->setArchivo("PED_" . $this->_data["aduana"] . "-" . $this->_data["patente"] . "-" . $this->_data["pedimento"] . ".pdf");
        require "tcpdf/pedimento.php";
        if(isset($this->_partidas)) {
            $this->_array["partidas"] = $this->_partidas;
        }
        $pdf = new Pedimento($this->_array, "P", "pt", "LETTER");
        $pdf->set_aduana($this->_data["aduana"]);
        $pdf->set_patente($this->_data["patente"]);
        $pdf->set_pedimento($this->_data["pedimento"]);
        $pdf->set_rfcCliente($this->_data["rfcCliente"]);
        $pdf->set_usuario("JVALDEZ");
        $pdf->set_sis("VUCEM");
        $pdf->set_referencia("NaN");
        $pdf->set_fechaPago($this->_data["fechaPago"]);
        $pdf->set_fechaEntrada($this->_data["fechaEntrada"]);
        $pdf->PedimentoDataStage();
        $pdf->Output("/tmp" . DIRECTORY_SEPARATOR . $this->_table->getArchivo(), "F");
        header("Content-type: application/pdf");
        readfile("/tmp" . DIRECTORY_SEPARATOR . $this->_table->getArchivo());
    }

    /**
     * 
     * @return string
     * @throws Exception
     */
    public function _createDir() {
        if (isset($this->_dir) && file_exists($this->_dir)) {
            if (is_readable($this->_dir)) {
                $newDir = $this->_dir . DIRECTORY_SEPARATOR . date("Y") . DIRECTORY_SEPARATOR . date("m") . DIRECTORY_SEPARATOR . str_pad(date("d"), 2, "0", STR_PAD_LEFT);
                if (!file_exists($newDir)) {
                    mkdir($newDir, 0777, true);
                    if (file_exists($newDir)) {
                        $this->_directory = date("Y") . DIRECTORY_SEPARATOR . date("m") . DIRECTORY_SEPARATOR . str_pad(date("d"), 2, "0", STR_PAD_LEFT);
                    }
                } else {
                    $this->_directory = date("Y") . DIRECTORY_SEPARATOR . date("m") . DIRECTORY_SEPARATOR . str_pad(date("d"), 2, "0", STR_PAD_LEFT);
                }
            } else {
                throw new Exception("Craps! Files directory is not readable '{$this->_dir}'");
            }
        } else {
            throw new Exception("Craps! Files directory not found.");
        }
    }

    /**
     * 
     * @param string $filename
     * @throws Exception
     */
    protected function _sendToBrowser() {
        if ($this->_save == true) {
            return true;
        }
        if (file_exists($this->_dir . DIRECTORY_SEPARATOR . $this->_directory . DIRECTORY_SEPARATOR . $this->_table->getArchivo())) {
            header('Content-type:application/pdf');
            header('Content-Disposition:attachment;filename="' . $this->_table->getArchivo() . '"');
            readfile($this->_dir . DIRECTORY_SEPARATOR . $this->_directory . DIRECTORY_SEPARATOR . $this->_table->getArchivo());
        } else {
            throw new Exception("Craps! File doesn't exists.");
        }
    }

}
