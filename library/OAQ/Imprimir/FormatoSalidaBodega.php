<?php

require "tcpdf/tcpdf.php";

class OAQ_Imprimir_FormatoSalidaBodega extends TCPDF {

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
        $this->MultiCell(434, 50, $this->_data['title'], 0, 'C', 1, 0, '', '', true, 0, false, true, 50, 'M');
        $this->Ln(22);
        $this->MultiCell(533, 2, "", "B", "L", 1, 0, "", "", true, 0, true, true, 12, "B");        
        $this->SetXY(40, 28);
        $this->Image(K_PATH_IMAGES . $this->_data["title_logo"], "", "", 100, 26, "JPG", false, "T", false, 300, "", false, false, 0, false, false, false);
    }

    public function Footer() {
        $this->SetXY(62, -48);
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->SetFont($this->_font, "C", 6);
        $this->SetLineStyle(array("width" => 1.0, "cap" => "butt", "join" => " 'miter'", "dash" => 0, "color" => array(54, 125, 162)));
        $this->MultiCell(490, 38, "Este documento es propiedad de {$this->_data['company']} queda<br>prohibida su reproducción total o parcial sin autorización de la Dirección General", "T", "L", 1, 0, "", "", true, 0, true, true, 8, "B");
        $this->SetFont($this->_font, "C", 8);
        $this->MultiCell(40, 38, "1 de 1", "T", "R", 1, 0, "", "", true, 0, true, true, 8, "B");
    }

    public function crear() {
        $this->AddPage();
        
        $this->SetY(110, true);
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->SetLineStyle(array("width" => 0.5, "cap" => "butt", "join" => " 'miter'", "dash" => 0, "color" => array(70, 70, 70)));
        $lineh = 9;
        $fontSize = 12;
        $col1 = 100;
        $col2 = 430;
        $ln = 20;
        $this->_fontNormal(430, "Fecha:", "R", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(100, date("d-m-Y"), null, 2, "B", null, $lineh, $fontSize);

        $this->Ln(50);
        $this->_fontNormal(200, "Cliente:", "R", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(200, $this->_data["nom_cliente"], null, 2, null, null, $lineh, $fontSize);
        $this->Ln($ln);
        $this->_fontNormal(200, "Referencia:", "R", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(200, $this->_data["referencia"], null, 2, null, null, $lineh, $fontSize);
        $this->Ln($ln);
        $this->_fontNormal(200, "Fecha entrada:", "R", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(230, $this->_data["fecha_entrada"], null, 2, null, null, $lineh, $fontSize);
        $this->Ln($ln);
        $this->_fontNormal(200, "Fecha salida:", "R", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(200, $this->_data["fecha_salida"], null, 2, null, null, $lineh, $fontSize);

        $this->Ln(40);
        $this->_fontNormal(200, "Descripción de mercancia:", "R", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(230, "", null, 2, null, null, $lineh, $fontSize);
        $this->Ln($ln);
        $this->_fontNormal(200, "Peso:", "R", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(230, $this->_data["peso_kg"] . ' kg', null, 2, null, null, $lineh, $fontSize);
        $this->Ln($ln);
        $this->_fontNormal(200, "NP:", "R", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(230, "", null, 2, null, null, $lineh, $fontSize);
        $this->Ln($ln);
        $this->_fontNormal(200, "Bultos:", "R", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(230, $this->_data["bultos"], null, 2, null, null, $lineh, $fontSize);
        $this->Ln($ln);
        $this->_fontNormal(200, "Val. Comercial:", "R", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(230, "", null, 2, null, null, $lineh, $fontSize);
        $this->Ln($ln);
        $this->_fontNormal(200, "Val. Dólares:", "R", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(230, "", null, 2, null, null, $lineh, $fontSize);
        
        $this->Ln(110);
        $this->_fontNormal(50, "", "C", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(200, "Elaboró", "C", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(50, "", "C", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(200, "Recibió", "C", 2, "", "B", $lineh, $fontSize);
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

        $this->Ln(20);
        $this->_fontNormal(300, "", "C", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(200, "Línea:", "C", 2, null, "B", $lineh, $fontSize);
        $this->Ln(20);
        $this->_fontNormal(300, "", "C", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(200, "Placas:", "C", 2, null, "B", $lineh, $fontSize);
        $this->Ln(20);
        $this->_fontNormal(300, "", "C", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(200, "Operador:", "C", 2, null, "B", $lineh, $fontSize);
    }
    
    protected function _fontNormal($width, $text, $align = "L", $multi = 1, $border = "TBRL", $valign = "B", $lineh = 12, $fontSize = 8) {
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
