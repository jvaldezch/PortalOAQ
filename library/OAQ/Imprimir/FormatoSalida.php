<?php

require "tcpdf/tcpdf.php";

class OAQ_Imprimir_FormatoSalida extends TCPDF {

    protected $_dir;
    protected $_filename;
    protected $_lineh = 12;
    protected $_margins = 40;
    protected $_font = "helvetica";
    protected $_fontB = "helveticaB";
    protected $_fontSize = 8;
    protected $_fontSmall = 6.5;
    protected $_marginTop = 20;
    protected $_shade = array(70, 70, 70);
    protected $_shaden = array(255, 255, 255);
    protected $_second = false;
    protected $_data = false;
    protected $_numPedimento = false;
    protected $_cp = false;
    protected $_inc = false;

    function set_dir($_dir) {
        $this->_dir = $_dir;
    }

    function set_filename($_filename) {
        $this->_filename = $_filename;
    }

    function set_data($_data) {
        $this->_data = $_data;
    }

    function get_filename() {
        return $this->_filename;
    }

    function __construct($data, $orientation, $unit, $format) {
        parent::__construct($orientation, $unit, $format, true, "UTF-8", false);
        $this->_data = $data;
        $this->SetFont($this->_font, "C", $this->_fontSize);
        $this->SetMargins($this->_margins, 26, $this->_margins, true);
        $this->SetAutoPageBreak(true, 26);
        $this->SetCreator(PDF_CREATOR);
        $this->SetAuthor("Jaime E. Valdez");
        $this->SetTitle($this->_data["prefijoDocumento"] . $this->_data["referencia"]);
        $this->SetSubject("");
        $this->SetKeywords("");
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);
    }

    public function Header() {
        $this->SetY($this->_marginTop, true);
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->SetLineStyle(array("width" => 1.0, "cap" => "butt", "join" => " 'miter'", "dash" => 0, "color" => array(54, 125, 162)));        
        $this->MultiCell(100, 50, '', 0, 'C', 1, 0, '', '', true, 0, false, true, 0, 'M');
        $this->MultiCell(434, 50, "FORMATO DE SALIDA", 0, 'C', 1, 0, '', '', true, 0, false, true, 50, 'M');
        $this->Ln(22);
        $this->MultiCell(533, 2, "", "B", "L", 1, 0, "", "", true, 0, true, true, 12, "B");        
        $this->SetXY(40, 28);
        $this->Image(K_PATH_IMAGES . "pdf_logo.jpg", "", "", 100, 26, "JPG", false, "T", false, 300, "", false, false, 0, false, false, false);
    }

    public function Footer() {
        $this->SetXY(62, -48);
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->SetFont($this->_font, "C", 6);
        $this->SetLineStyle(array("width" => 1.0, "cap" => "butt", "join" => " 'miter'", "dash" => 0, "color" => array(54, 125, 162)));
        $this->MultiCell(490, 38, "Este documento es propiedad de Organización Aduanal de Querétaro, S.C. queda<br>prohibida su reproducción total o parcial sin autorización de la Dirección General", "T", "L", 1, 0, "", "", true, 0, true, true, 8, "B");
        $this->SetFont($this->_font, "C", 8);
        $this->MultiCell(40, 38, "1 de 1", "T", "R", 1, 0, "", "", true, 0, true, true, 8, "B");
    }

    public function formatoSalida() {
        $this->AddPage();
        $this->SetY(110, true);
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->SetLineStyle(array("width" => 0.5, "cap" => "butt", "join" => " 'miter'", "dash" => 0, "color" => array(70, 70, 70)));
        $lineh = 9;
        $fontSize = 12;
        $col1 = 100;
        $col2 = 430;
        $ln = 30;
        $this->_fontNormal(430, "Fecha:", "R", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(100, date("d-m-Y"), null, 2, "B", null, $lineh, $fontSize);
        $this->Ln($ln);
        $this->_fontNormal($col1, "Referencia:", null, 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal($col2, $this->_data["referencia"], null, 2, "B", null, $lineh, $fontSize);
        $this->Ln($ln);
        $this->_fontNormal($col1, "Pedimento:", null, 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal($col2, $this->_data["pedimento"], null, 2, "B", null, $lineh, $fontSize);
        $this->Ln($ln);
        $this->_fontNormal($col1, "Cliente:", null, 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal($col2, $this->_data["nombreCliente"], null, 2, "B", null, $lineh, $fontSize);
        $this->Ln($ln);
        $this->_fontNormal($col1, "Guía(s):", null, 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal($col2, isset($this->_data["guias"]) ? preg_replace("/,\s$/", "", $this->_data["guias"]) : "", null, 2, "B", null, $lineh, $fontSize);
        $this->Ln($ln);
        $this->_fontNormal($col1, "Bultos:", null, 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal($col2, isset($this->_data["bultos"]) ? $this->_data["bultos"] : "", null, 2, "B", null, $lineh, $fontSize);
        $this->Ln($ln);
        $this->_fontNormal($col1, "Peso:", null, 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal($col2, isset($this->_data["pesoBruto"]) ? number_format($this->_data["pesoBruto"], 2, ".", ",") . " kg" : "", null, 2, "B", null, $lineh, $fontSize);
        $this->Ln($ln);
        $this->_fontNormal($col1, "Documentos:", null, 2, "", "B", $lineh, $fontSize);
        $this->Ln($ln);
        $this->_fontNormal(5, "", null, 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(20, "", null, 2, "TBLR", "B", $lineh, $fontSize);
        $this->_fontNormal(5, "", null, 2, "L", "B", $lineh, $fontSize);
        $this->_fontNormal(250, "Pedimento ambos formatos", null, 2, "", "B", $lineh, $fontSize);
        $this->Ln($ln);
        $this->_fontNormal(5, "", null, 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(20, "", null, 2, "TBLR", "B", $lineh, $fontSize);
        $this->_fontNormal(5, "", null, 2, "L", "B", $lineh, $fontSize);
        $this->_fontNormal(250, "Copia de guía revalidada", null, 2, "", "B", $lineh, $fontSize);
        $this->Ln($ln);
        $this->_fontNormal(5, "", null, 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(20, "", null, 2, "TBLR", "B", $lineh, $fontSize);
        $this->_fontNormal(5, "", null, 2, "L", "B", $lineh, $fontSize);
        $this->_fontNormal(250, "Copia de factura de embarque", null, 2, "", "B", $lineh, $fontSize);
        $this->Ln(50);
        $this->_fontNormal(50, "", "C", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(200, "Entrego", "C", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(50, "", "C", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(200, "Recibio", "C", 2, "", "B", $lineh, $fontSize);
        $this->Ln(50);
        $this->_fontNormal(50, "", "C", 2, "", "", $lineh, $fontSize);
        $this->_fontNormal(200, "", "C", 2, "", "", $lineh, $fontSize);
        $this->_fontNormal(50, "", "C", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(200, "", "C", 2, "", "", $lineh, $fontSize);
        $this->Ln(20);
        $this->_fontNormal(50, "", "C", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(200, "Nombre y firma", "C", 2, "T", "B", $lineh, $fontSize);
        $this->_fontNormal(50, "", "C", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(200, "Nombre y firma", "C", 2, "T", "B", $lineh, $fontSize);
    }
    
    protected function _fontNormal($width, $text, $align = "L", $multi = 1, $border = "TBRL", $valign = "B", $lineh = 12, $fontSize = 8) {
        if (!isset($lineh)) {
            $lineh = $this->_lineh;
        }
        if (!isset($fontSize)) {
            $fontSize = $this->_fontSize;
        }
        $this->SetFont($this->_font, "C", $fontSize);
        $this->MultiCell($width, $lineh * $multi, $text, $border, $align, 1, 0, "", "", true, 0, true, true, $lineh, $valign);
    }

    public function fileExists() {
        if (file_exists($this->_dir . DIRECTORY_SEPARATOR . $this->_filename)) {
            return true;
        }
        return false;
    }

    public function sendToBrowser() {
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
