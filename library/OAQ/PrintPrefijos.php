<?php

require "tcpdf/tcpdf.php";

class OAQ_PrintPrefijos extends TCPDF {

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
        $this->SetTitle("PREFIJOS");
        $this->SetSubject("");
        $this->SetKeywords("");
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);
    }

    public function Header() {
        $this->SetY($this->_marginTop, true);
        $html = '<table border="1" cellspacing="2" cellpadding="2" style="width:650px; border-width: 0.5px;">'
                . '<tr>'
                . '<th rowspan="3"></th>'
                . '<th align="center" width="332" style="font-size: 11px; font-weight: bold;">' . $this->_data["empresa"] . '</th>'
                . '<th align="center" width="80" style="font-size: 10px;"></th>'
                . '<th align="left" width="80" style="font-size: 10px;"></th>'
                . '</tr>'
                . '<tr>'
                . '<th align="center" style="font-size: 11px; font-weight: bold;"></th>'
                . '<th align="center" style="font-size: 10px;">Página</th>'
                . '<th align="center" style="font-size: 10px; text-align: center">' . $this->getAliasNumPage() . " de " . $this->getAliasNbPages() . '</th>'
                . '</tr>'
                . '<tr>'
                . '<th align="center" style="font-size: 11px; font-weight: bold;">Prefijos de Expediente Digital</th>'
                . '<th align="center" style="font-size: 10px;"></th>'
                . '<th align="left" style="font-size: 10px;"></th>'
                . '</tr>'
                . '</table>';
        $this->writeHTML($html, true, false, true, false, '');
        $this->SetXY(65, 28);
        $this->Image(K_PATH_IMAGES . "logo_oaq.jpg", "", "", 83, 33, "JPG", "", "T", false, 300, "", false, false, 0, false, false, false);
    }

    public function Footer() {
        $this->SetFont($this->_font, "", $this->_fontSize);
        $this->SetY(-50, true);
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->SetFont($this->_font, "C", $this->_fontSize);
        $this->SetLineStyle(array("width" => 0.5, "cap" => "butt", "join" => " 'miter'", "dash" => 0, "color" => array(70, 70, 70)));
        $this->_fontNormal(100, "", null, 2);
        $this->_fontNormal(400, "Este documento es propiedad de Organización Aduanal de Querétaro, S.C. queda prohibida su reproducción total o parcial sin previa autorización de la Dirección General.", null, 2);
        $this->SetXY(62, -48);
        $this->Image(K_PATH_IMAGES . "logo_oaq.jpg", "", "", 50, 20, "JPG", "https://oaq.dnsalias.net", "T", false, 300, "", false, false, 0, false, false, false);
    }

    public function printPrefijos() {
        $this->AddPage();
        $this->_data["colors"]["line"] = array(5, 5, 5);
        $this->SetY(95, true);
        $this->SetTextColor(20, 20, 20);
        $this->SetFont($this->_font, "C", $this->_fontSize);
        $this->SetTextColor(250, 250, 250);
        $this->SetFillColor(79, 129, 189);
        $this->_fontBold(80, "PREFIJO", "C");
        $this->_fontBold(448, "DESCRIPCIÓN", "C");
        if (isset($this->_data["documentos"])) {
            $this->SetTextColor(20, 20, 20);
            $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
            foreach ($this->_data["documentos"] as $item) {
                $this->Ln();
                $this->_fontNormal(80, $item["prefijo"]);
                $this->_fontNormal(448, $item["descripcion"], "L");
            }
        }
    }

    protected function _fontNormal($width, $text, $align = "L", $multi = 1, $border = "TBRL", $valign = "M") {
        $this->SetFont($this->_font, "C", $this->_fontSize);
        $this->MultiCell($width, $this->_lineh * $multi, $text, $border, $align, 1, 0, "", "", true, 0, true, true, $this->_lineh, $valign);
    }

    protected function _fontBold($width, $text, $align = "L", $multi = 1, $border = "TBRL", $valign = "M") {
        $this->SetFont($this->_fontB, "C", $this->_fontSize);
        $this->MultiCell($width, $this->_lineh * $multi, $text, $border, $align, 1, 0, "", "", true, 0, true, true, $this->_lineh, $valign);
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
