<?php

require "tcpdf/tcpdf.php";

class OAQ_Imprimir_SolicitudEquipo extends TCPDF {

    protected $_dir;
    protected $_filename;
    protected $_lineh = 14;
    protected $_margins = 40;
    protected $_font = "helvetica";
    protected $_fontB = "helveticaB";
    protected $_fontSize = 10;
    protected $_fontSmall = 6.5;
    protected $_marginTop = 40;
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
        $this->SetTitle($this->_data["prefijoDocumento"]);
        $this->SetSubject("");
        $this->SetKeywords("");
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);
    }

    public function Header() {
        $this->SetY($this->_marginTop, true);
        $this->SetLineStyle(array("width" => 0.5, "cap" => "butt", "join" => " 'miter'", "dash" => 0, "color" => array(70, 70, 70)));
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->SetFont($this->_font, "B", 10);
        $this->MultiCell(100, 50, '', 1, 'C', 1, 0, '', '', true, 0, false, true, 40, 'M');
        $this->MultiCell(434, 20, $this->_data["empresa"], 1, 'C', 1, 0, '', '', true, 0, false, true, 20, 'M');
        $this->Ln();
        $this->setX(140);
        $this->SetFont($this->_font, "C", $this->_fontSize);
        $this->MultiCell(434, 30, $this->_data["direccion"], 1, 'C', 1, 0, '', '', true, 0, false, true, 30, 'M');
        $this->SetXY(48, 50);
        $this->Image(K_PATH_IMAGES . "logo_oaq.jpg", "", "", 83, 33, "JPG", false, "T", false, 300, "", false, false, 0, false, false, false);
    }

    public function Footer() {
        $this->SetFont($this->_font, "", $this->_fontSize);
        $this->SetY(-60, true);
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->SetFont($this->_font, "C", $this->_fontSize);
        $this->SetLineStyle(array("width" => 0.5, "cap" => "butt", "join" => " 'miter'", "dash" => 0, "color" => array(70, 70, 70)));
        $this->MultiCell(100, 30, '', 1, 'C', 1, 0, '', '', true, 0, false, true, 25, 'M');
        $this->MultiCell(5, 30, '', 'TB', 'C', 1, 0, '', '', true, 0, false, true, 25, 'M');
        $this->MultiCell(429, 30, "Este documento es propiedad de {$this->_data["empresa"]} queda prohibida su reproducción total o parcial sin previa autorización de la Dirección General.", 'TBR', 'L', 1, 0, '', '', true, 0, true, true, 25, 'M');
    }
    
    protected function _mes($value) {
        switch ($value) {
            case 1:
                return "Enero";
            case 2:
                return "Febrero";
            case 3:
                return "Marzo";
            case 4:
                return "Abril";
            case 5:
                return "Mayo";
            case 6:
                return "Junio";
            case 7:
                return "Julio";
            case 8:
                return "Agosto";
            case 9:
                return "Septiembre";
            case 10:
                return "Octubre";
            case 11:
                return "Noviembre";
            case 12:
                return "Diciembre";
            default:
                return "Mes";
        }
    }

    public function crear() {
        $this->AddPage();
        $this->SetY(100, true);
        $this->SetLineStyle(array("width" => 0.5, "cap" => "butt", "join" => " 'miter'", "dash" => 0, "color" => array(70, 70, 70)));
        $this->SetFont($this->_font, "B", 12);
        $this->SetFillColor(230, 225, 230);
        $this->MultiCell(534, 20, $this->_data["nombreDocumento"], 0, 'C', 0, 0, '', '', true, 0, false, true, 20, 'M');
        $this->Ln(30);
        $this->SetFont($this->_font, "C", $this->_fontSize);
        $this->MultiCell(534, 16, "Querétaro, Qro. a " . (int) date("d") . " de " . $this->_mes((int) date("m")) . " de " . date("Y"), 0, 'R', 0, 0, '', '', true, 0, false, true, 16, 'M');
        $this->Ln(30);
        $this->SetFont($this->_font, "B", $this->_fontSize);
        $this->MultiCell(250, 16, "ING. JAIME E. VALDEZ", 0, 'L', 0, 0, '', '', true, 0, false, true, 16, 'M');
        $this->Ln();
        $this->MultiCell(250, 16, "PRESENTE", 0, 'L', 0, 0, '', '', true, 0, false, true, 16, 'M');
        $this->Ln(30);
        $this->SetFont($this->_font, "C", $this->_fontSize);
        $this->MultiCell(534, 32, "Por medio de la presente se hace solicitud de credenciales de acceso para [Nombre de la persona que va usar los sistemas o recursos] a los siguientes sistemas o recursos.", 0, 'L', 0, 0, '', '', true, 0, true, true, 16, 'M');
        $this->Ln(40);
        $this->SetFont($this->_font, "B", $this->_fontSize);
        $this->MultiCell(5, 16, "", 0, 'L', 0, 0, '', '', true, 0, true, true, 16, 'M');
        $this->MultiCell(20, 16, "", 1, 'L', 0, 0, '', '', true, 0, true, true, 16, 'M');
        $this->MultiCell(65, 16, "Escritorio", 1, 'L', 0, 0, '', '', true, 0, true, true, 16, 'M');
        $this->MultiCell(50, 16, "Laptop", 1, 'L', 0, 0, '', '', true, 0, true, true, 16, 'M');
        $this->MultiCell(200, 16, "Objetivo", 1, 'L', 0, 0, '', '', true, 0, true, true, 16, 'M');
        $this->Ln();
        $this->SetFont($this->_font, "C", $this->_fontSize);
        $this->MultiCell(5, 16, "", 0, 'L', 0, 0, '', '', true, 0, true, true, 16, 'M');
        $this->MultiCell(20, 16, "", 1, 'L', 0, 0, '', '', true, 0, true, true, 16, 'M');
        $this->MultiCell(65, 16, "Escritorio", 1, 'L', 0, 0, '', '', true, 0, true, true, 16, 'M');
        $this->MultiCell(50, 16, "Laptop", 1, 'L', 0, 0, '', '', true, 0, true, true, 16, 'M');
        $this->MultiCell(200, 16, "Objetivo", 1, 'L', 0, 0, '', '', true, 0, true, true, 16, 'M');
        $this->Ln(20);
        $this->MultiCell(534, 32, "Software precargado:", 0, 'L', 0, 0, '', '', true, 0, true, true, 16, 'M');
        $this->Ln(20);
        $this->SetFont($this->_font, "B", $this->_fontSize);
        $this->MultiCell(5, 16, "", 0, 'L', 0, 0, '', '', true, 0, true, true, 16, 'M');
        $this->MultiCell(20, 16, "", 1, 'L', 0, 0, '', '', true, 0, true, true, 16, 'M');
        $this->MultiCell(200, 16, "Software", 1, 'L', 0, 0, '', '', true, 0, true, true, 16, 'M');
        $this->Ln();
        $this->SetFont($this->_font, "C", $this->_fontSize);
        $this->MultiCell(5, 16, "", 0, 'L', 0, 0, '', '', true, 0, true, true, 16, 'M');
        $this->MultiCell(20, 16, "", 1, 'L', 0, 0, '', '', true, 0, true, true, 16, 'M');
        $this->MultiCell(200, 16, "Office", 1, 'L', 0, 0, '', '', true, 0, true, true, 16, 'M');
        $this->Ln(40);
        $this->MultiCell(267, 50, "Atentamente,", 0, 'L', 0, 0, '', '', true, 0, true, true, 16, 'T');
        $this->MultiCell(267, 50, "Recibio,", 0, 'L', 0, 0, '', '', true, 0, true, true, 16, 'T');
        $this->Ln();
        $this->MultiCell(267, 16, "_________________________________", 0, 'L', 0, 0, '', '', true, 0, true, true, 16, 'T');
        $this->MultiCell(267, 16, "_________________________________", 0, 'L', 0, 0, '', '', true, 0, true, true, 16, 'T');
        $this->Ln();
        $this->MultiCell(267, 16, $this->_data["de"]["nombre"], 0, 'L', 0, 0, '', '', true, 0, true, true, 16, 'T');
        $this->MultiCell(267, 16, $this->_data["para"]["nombre"], 0, 'L', 0, 0, '', '', true, 0, true, true, 16, 'T');
        $this->Ln();
        $this->MultiCell(267, 16, $this->_data["de"]["posicion"], 0, 'L', 0, 0, '', '', true, 0, true, true, 16, 'T');
        $this->MultiCell(267, 16, $this->_data["para"]["posicion"], 0, 'L', 0, 0, '', '', true, 0, true, true, 16, 'T');
        $this->Ln();
        $this->MultiCell(267, 16, $this->_data["de"]["departamento"], 0, 'L', 0, 0, '', '', true, 0, true, true, 16, 'T');
        $this->MultiCell(267, 16, $this->_data["para"]["departamento"], 0, 'L', 0, 0, '', '', true, 0, true, true, 16, 'T');
        $this->isLastPage = true;
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
