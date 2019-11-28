<?php

require "tcpdf/tcpdf.php";

class OAQ_PrintChecklist extends TCPDF {

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
        $this->SetTitle("CHECKLIST_" . $this->_data["referencia"]);
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
                . '<th align="center" width="80" style="font-size: 10px;">Versión</th>'
                . '<th align="left" width="80" style="font-size: 10px;">00</th>'
                . '</tr>'
                . '<tr>'
                . '<th align="center" style="font-size: 11px; font-weight: bold;">SGC</th>'
                . '<th align="center" style="font-size: 10px;">Página</th>'
                . '<th align="center" style="font-size: 10px; text-align: center">' . $this->getAliasNumPage() . " de " . $this->getAliasNbPages() . '</th>'
                . '</tr>'
                . '<tr>'
                . '<th align="center" style="font-size: 11px; font-weight: bold;">Check-list de expediente digital</th>'
                . '<th align="center" style="font-size: 10px;">Código </th>'
                . '<th align="left" style="font-size: 10px;">SGC 78</th>'
                . '</tr>'
                . '</table>';
        $this->writeHTML($html, true, false, true, false, '');
        $this->SetXY(65, 28);
        $this->Image(K_PATH_IMAGES . "logo_oaq.jpg", "", "", 83, 33, "JPG", "https://oaq.dnsalias.net", "T", false, 300, "", false, false, 0, false, false, false);
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

    public function printChecklist() {
        $this->AddPage();
        $this->_data["colors"]["line"] = array(5, 5, 5);
        $this->SetY(75, true);
        $this->SetTextColor(20, 20, 20);
        $this->SetFont($this->_font, "C", $this->_fontSize);
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->SetX(390);
        $this->SetLineStyle(array("width" => 0.5, "cap" => "butt", "join" => " 'miter'", "dash" => 0, "color" => array(70, 70, 70)));
        $this->_fontBold(90, "Fecha:");
        $this->_fontNormal(90, date("d/m/Y"));
        $this->Ln();
        $this->_fontBold(70, "Oficina:");
        $this->_fontNormal(280, $this->_data["oficina"]);
        $this->_fontBold(90, "Referencia:");
        $this->_fontNormal(90, $this->_data["referencia"]);
        $this->Ln();
        $this->_fontBold(70, "Cliente:");
        $this->_fontNormal(280, $this->_data["nombreCliente"]);
        $this->_fontBold(90, "Pedimento:");
        $this->_fontNormal(90, $this->_data["pedimento"]);
        $this->Ln();
        $this->_fontBold(70, "Operación:");
        $this->_fontBold(32, "IMP:");
        $this->_fontNormal(22, "");
        $this->_fontBold(32, "EXP:");
        $this->_fontNormal(22, "");
        $this->_fontBold(90, "Clave de pedimento:");
        $this->_fontNormal(30, "");
        $this->_fontBold(40, "Sellos:");
        $this->_fontNormal(192, "");
        $this->Ln(10);
        $this->_subtitle("Operaciones");
        if (isset($this->_data["preguntas"])) {
            $json = $this->_data["checklist"];
            $title = $this->_data["preguntas"][0]["tipo"];
            foreach ($this->_data["preguntas"] as $item) {
                if ($title != $item["tipo"]) {
                    $title = $item["tipo"];
                    $this->Ln(12);
                    if ($item["tipo"] == "documentacion") {
                        $this->Ln($this->_lineh);
                        $this->SetTextColor(250, 250, 250);
                        $this->SetFillColor(79, 129, 189);
                        $this->_fontNormal(530, "DE CONFORMIDAD CON EL ART. 162 FRACCIÓN VII DE LA LEY ADUANERA Y PARA EFECTOS DE LOS ART. 6o, 36 Y 36-A SE ANEXA LO SIGUIENTE:", null, 2);
                        $this->SetTextColor(20, 20, 20);
                        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
                    }
                    if ($item["tipo"] == "administracion") {
                        $this->Ln($this->_lineh);
                        $this->_reviso("operacion");
                        $this->Ln($this->_lineh);
                        $this->_subtitle("Administración");
                    }
                }
                $this->Ln();
                $this->_fontNormal(20, $item["orden"], "C");
                $this->_fontNormal(470, $item["documento"]);
                if (isset($json->$item["nombre"])) {
                    if ($json->$item["nombre"] != "") {
                        $this->_fontNormal(20, ($json->$item["nombre"] == "1") ? "x" : "", "C");
                        $this->_fontNormal(20, ($json->$item["nombre"] == "0") ? "x" : "", "C");
                    }
                } else {
                    $this->_fontNormal(20, "", "C");
                    $this->_fontNormal(20, "", "C");
                }
                if((int) $item["orden"] == 7) {
                    $this->Ln();                    
                }
            }
            $this->Ln(12);
            $this->SetX(60);
            $this->_fontNormal(490, "Obervaciones: \n" . $this->_data["observaciones"], null, 3);
            $this->Ln($this->_lineh * 4);
            $this->SetX(60);
            $this->_reviso("administracion");
            $this->Ln($this->_lineh);
        }
    }

    protected function _subtitle($subitle) {
        $this->Ln();
        $this->SetTextColor(20, 20, 20);
        $this->SetFillColor(217, 217, 217);
        $this->_fontNormal(490, $subitle, "C");
        $this->_fontNormal(20, "SI");
        $this->_fontNormal(20, "N/A");
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
    }

    protected function _fontNormal($width, $text, $align = "L", $multi = 1, $border = "TBRL", $valign = "M") {
        $this->SetFont($this->_font, "C", $this->_fontSize);
        $this->MultiCell($width, $this->_lineh * $multi, $text, $border, $align, 1, 0, "", "", true, 0, true, true, $this->_lineh, $valign);
    }

    protected function _fontBold($width, $text, $align = "L", $multi = 1, $border = "TBRL", $valign = "M") {
        $this->SetFont($this->_fontB, "C", $this->_fontSize);
        $this->MultiCell($width, $this->_lineh * $multi, $text, $border, $align, 1, 0, "", "", true, 0, true, true, $this->_lineh, $valign);
    }

    protected function _revisado() {
        $this->SetFillColor(217, 217, 217);
        $this->_fontBold(177, "Elaborado por:", null, 1);
        $this->_fontBold(176, "Revisado por:", null, 1);
        $this->_fontBold(176, "Recibido por:", null, 1);
        $this->Ln();
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->_fontNormal(177, "", null, 2);
        $this->_fontNormal(176, "", null, 2);
        $this->_fontNormal(176, "", null, 2);
        $this->Ln();
        $this->_fontNormal(177, "", null, 4);
        $this->_fontNormal(176, "", null, 4);
        $this->_fontNormal(176, "", null, 4);
    }

    protected function _reviso($revision) {
        if (isset($revision)) {
            $this->SetX(60);
            $this->_fontBold(164, "Elaborado por:", null, 1);
            $this->_fontBold(163, "Revisado por:", null, 1);
            $this->_fontBold(163, "Recibido por:", null, 1);
            $this->Ln();
            $this->SetX(60);
            $this->_fontNormal(164, isset($this->_data["revision"][$revision]["elaboro"]) ? strtoupper($this->_data["revision"][$revision]["elaboro"]["nombre"]) : "", null, 2);
            $this->_fontNormal(163, isset($this->_data["revision"][$revision]["reviso"]) ? strtoupper($this->_data["revision"][$revision]["reviso"]["nombre"]) : "", null, 2);
            $this->_fontNormal(163, isset($this->_data["revision"][$revision]["recibio"]) ? strtoupper($this->_data["revision"][$revision]["recibio"]["nombre"]) : "", null, 2);
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
