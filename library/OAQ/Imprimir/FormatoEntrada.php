<?php

require "tcpdf/tcpdf.php";

class OAQ_Imprimir_FormatoEntrada extends TCPDF {

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
        $fontSize = 9;
        $col1 = 100;
        $col2 = 430;
        $ln = 20;
        $this->_fontNormal(430, "Fecha:", "R", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(100, date("d-m-Y"), null, 2, "B", null, $lineh, $fontSize);

        $this->Ln(50);
        $this->_fontNormal(150, "Cliente:", "R", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(300, $this->_data["nom_cliente"], null, 2, null, null, $lineh, $fontSize);
        $this->Ln($ln);
        $this->_fontNormal(150, "Referencia:", "R", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(300, $this->_data["referencia"], null, 2, null, null, $lineh, $fontSize);
        $this->Ln($ln);
        $this->_fontNormal(150, "Fecha entrada:", "R", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(280, $this->_data["fecha_entrada"], null, 2, null, null, $lineh, $fontSize);
        $this->Ln($ln);
        $this->_fontNormal(150, "Ubicación:", "R", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(150, $this->_data["ubicacion"], null, 2, null, null, $lineh, $fontSize);

        $this->Ln(40);
        $this->_fontNormal(150, "Descripción de mercancia:", "R", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(280, $this->_data["descripcionMercancia"], null, 2, null, null, $lineh, $fontSize);
        $this->Ln($ln);
        $this->_fontNormal(150, "Peso:", "R", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(280, $this->_data["peso_kg"] . ' kg', null, 2, null, null, $lineh, $fontSize);
        $this->Ln($ln);
        $this->_fontNormal(150, "NP:", "R", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(280, "", null, 2, null, null, $lineh, $fontSize);
        $this->Ln($ln);
        $this->_fontNormal(150, "Bultos:", "R", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(280, $this->_data["bultos"], null, 2, null, null, $lineh, $fontSize);
        $this->Ln($ln);
        $this->_fontNormal(150, "Val. Comercial:", "R", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(280, number_format($this->_data["valorComercial"], 2) . ' ' . $this->_data["divisa"], null, 2, null, null, $lineh, $fontSize);
        $this->Ln($ln);
        $this->_fontNormal(150, "Val. Dólares:", "R", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(280, number_format($this->_data["valorDolares"], 2), null, 2, null, null, $lineh, $fontSize);

        $this->Ln(30);
        $this->_fontNormal(150, "Comentarios:", "R", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(280, $this->_data["comentarios"], null, 2, null, null, $lineh, $fontSize);

        $this->Ln(50);
        $this->_fontNormal(150, "¿Discrepancias en mercancia?:", "R", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(50, "Si", null, 2, null, null, $lineh, $fontSize);
        $this->_fontNormal(50, "No", null, 2, null, null, $lineh, $fontSize);
        $this->Ln($ln);
        $this->_fontNormal(150, "¿Daños en mercancia?:", "R", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(50, "Si", null, 2, null, null, $lineh, $fontSize);
        $this->_fontNormal(50, "No", null, 2, null, null, $lineh, $fontSize);

        $this->Ln(30);
        $this->_fontNormal(150, "Observaciones:", "R", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(280, $this->_data["observaciones"], null, 2, null, null, $lineh, $fontSize);

        $this->Ln(40);
        $this->_fontNormal(150, "Fecha de descarga:", "R", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(130, $this->_data["fecha_descarga"], null, 2, "B", null, $lineh, $fontSize);
        $this->Ln($ln);
        $this->_fontNormal(150, "Fecha de revisión:", "R", 2, "", "B", $lineh, $fontSize);
        $this->_fontNormal(130, $this->_data["fecha_revision"], null, 2, "B", null, $lineh, $fontSize);
        
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
