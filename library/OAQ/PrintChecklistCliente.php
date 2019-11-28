<?php

require "tcpdf/tcpdf.php";

class OAQ_PrintChecklistCliente extends TCPDF {

    protected $_dir;
    protected $_filename;
    protected $_lineh = 11;
    protected $_margins = 40;
    protected $_font = "helvetica";
    protected $_fontB = "helveticaB";
    protected $_fontSize = 7;
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
        $this->SetTitle("CHECKLIST CLIENTE");
        $this->SetSubject("");
        $this->SetKeywords("");
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);
    }

    public function Header() {
        $this->SetY($this->_marginTop, true);
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->SetLineStyle(array("width" => 1.0, "cap" => "butt", "join" => " 'miter'", "dash" => 0, "color" => array(54, 125, 162)));        
        $this->MultiCell(100, 50, '', 0, 'C', 1, 0, '', '', true, 0, false, true, 0, 'M');
        $this->MultiCell(434, 50, "CHECK LIST DE EXPEDIENTE DIGITAL", 0, 'C', 1, 0, '', '', true, 0, false, true, 50, 'M');
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

    public function printChecklist() {
        $this->_data["colors"]["line"] = array(5, 5, 5);
        $this->AddPage();
        $this->SetY(75, true);
        $this->SetTextColor(20, 20, 20);
        $this->SetFont($this->_font, "C", $this->_fontSize);
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->SetLineStyle(array("width" => 0.5, "cap" => "butt", "join" => " 'miter'", "dash" => 0, "color" => array(70, 70, 70)));
        $this->_fontBold(70, "Cliente:");
        $this->_fontNormal(280, $this->_data["nombreCliente"]);
        $this->_fontBold(60, "Fecha:");
        $this->_fontNormal(120, date("d/m/Y"));
        $this->Ln();
        $this->SetTextColor(250, 250, 250);
        $this->SetFillColor(79, 129, 189);
        $this->SetFont($this->_font, "B", $this->_fontSize);
        $this->MultiCell(20, 24, "#", 1, 'C', 1, 0, '', '', true, 0, false, true, 24, 'M');
        $this->MultiCell(100, 24, "DOCUMENTOS REQUERIDOS:", 1, 'C', 1, 0, '', '', true, 0, false, true, 24, 'M');
        $this->MultiCell(200, 24, "MOTIVO DE SU SOLICITUD:", 1, 'C', 1, 0, '', '', true, 0, false, true, 24, 'M');
        $this->MultiCell(170, 24, "CUMPLIMIENTO EN:", 1, 'C', 1, 0, '', '', true, 0, false, true, 24, 'M');
        $this->MultiCell(20, 24, "SI", 1, 'C', 1, 0, '', '', true, 0, false, true, 24, 'M');
        $this->MultiCell(20, 24, "N/A", 1, 'C', 1, 0, '', '', true, 0, false, true, 24, 'M');
        if (isset($this->_data["preguntas"])) {
            $this->SetFont($this->_font, "C", $this->_fontSize);
            $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
            $this->SetTextColor(20, 20, 20);
            $this->SetFillColor(217, 217, 217);
            $this->setCellHeightRatio(0.9);
            $json = $this->_data["checklist"];
            foreach ($this->_data["preguntas"] as $item) {
                $this->SetFillColor(255, 255, 255);
                $this->Ln();
                $this->_fontNormal(20, $item["orden"], "C", $item["reglones"]);
                $this->_fontNormal(100, $item["documento"], "L", $item["reglones"]);
                $this->_fontNormal(200, $item["motivo"], "L", $item["reglones"]);
                $this->_fontNormal(170, $item["cumplimiento"], "L", $item["reglones"]);
                if (isset($json->$item["nombre"])) {
                    if ($json->$item["nombre"] != "") {
                        $this->_fontNormal(20, ($json->$item["nombre"] == "1") ? "x" : "", "C", $item["reglones"]);
                        $this->_fontNormal(20, ($json->$item["nombre"] == "0") ? "x" : "", "C", $item["reglones"]);
                    }
                } else {
                    $this->_fontNormal(20, "", "C", $item["reglones"]);
                    $this->_fontNormal(20, "", "C", $item["reglones"]);
                }
            }
        }
        $this->Ln();
        $this->SetX(60);
        $this->_fontNormal(490, "Obervaciones: \n" . $this->_data["observaciones"], null, 3);
        $this->Ln($this->_lineh * 4);
        $this->_reviso("administracion");
    }

    protected function _fontNormal($width, $text, $align = "L", $multi = 1, $border = "TBRL", $valign = "M") {
        $this->SetFont($this->_font, "C", $this->_fontSize);
        $this->MultiCell($width, $this->_lineh * $multi, $text, $border, $align, 1, 0, "", "", true, 0, true, true, $this->_lineh, $valign);
    }

    protected function _fontBold($width, $text, $align = "L", $multi = 1, $border = "TBRL", $valign = "M") {
        $this->SetFont($this->_fontB, "C", $this->_fontSize);
        $this->MultiCell($width, $this->_lineh * $multi, $text, $border, $align, 1, 0, "", "", true, 0, true, true, $this->_lineh, $valign);
    }

    protected function _reviso($revision) {
        if (isset($revision)) {
            $this->MultiCell(20, 0, "", "", "", 1, 0, "", "", true, 0, true, true);
            $this->_fontBold(50, "Elaboró:", null, 2);
            $this->_fontNormal(113, isset($this->_data["revision"][$revision]["elaboro"]) ? strtoupper($this->_data["revision"][$revision]["elaboro"]["nombre"]) : "", null, 2);
            $this->_fontBold(50, "Revisó:", null, 2);
            $this->_fontNormal(113, isset($this->_data["revision"][$revision]["reviso"]) ? strtoupper($this->_data["revision"][$revision]["reviso"]["nombre"]) : "", null, 2);
            $this->_fontBold(50, "Autorizó:", null, 2);
            $this->_fontNormal(113, isset($this->_data["revision"][$revision]["recibio"]) ? strtoupper($this->_data["revision"][$revision]["recibio"]["nombre"]) : "", null, 2);
        }
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
