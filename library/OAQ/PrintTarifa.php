<?php

require "tcpdf/tcpdf.php";

class OAQ_PrintTarifa extends TCPDF {

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
        $this->SetTitle("TARIFA");
        $this->SetSubject("");
        $this->SetKeywords("");
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);
    }

    public function Header() {
        $this->SetY($this->_marginTop, true);
        $this->SetXY(55, 28);
        $this->Image(K_PATH_IMAGES . "logo_oaq_plain.jpg", "", "", 103, 30, "JPG", "https://oaq.dnsalias.net", "", false, 300, "", false, false, 0, false, false, false);
        $this->SetXY(40, 28);
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(235, 47, 35);
        $this->SetFont($this->_fontB, "C", $this->_fontSize);
        $this->MultiCell(0, 40, mb_strtoupper($this->_data["empresa"], "UTF-8"), 0, "C", 1, 0, '', '', true, 0, false, true, 40, "M");
        $init = 425;
        $this->SetXY($init, 28);
        $this->Image(K_PATH_IMAGES . "logo_sige.jpg", "", "", 40, 41, "JPG", false, "", false, 300, "", false, false, 0, false, false, false);
        $this->SetXY($init + 50, 28);
        $this->Image(K_PATH_IMAGES . "logo_iqnet.jpg", "", "", 40, 41, "JPG", false, "", false, 300, "", false, false, 0, false, false, false);
        $this->SetXY($init + 95, 28);
        $this->Image(K_PATH_IMAGES . "logo_oea.jpg", "", "", 55, 40, "JPG", false, "", false, 300, "", false, false, 0, false, false, false);
    }

    public function Footer() {
        $this->SetFont($this->_font, "", $this->_fontSize);
        $this->SetY(-70, true);
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->SetFont($this->_font, "C", $this->_fontSize);
        $this->SetLineStyle(array("width" => 1, "cap" => "butt", "join" => "miter", "dash" => 0, "color" => array(235, 47, 35)));
        $this->SetFillColor(255, 255, 255);
        $this->MultiCell(0, 12, "http://www.oaq.com.mx", false, "C", 1, 0, '', '', true, 0, false, true, 12);
        $this->Ln();
        $this->MultiCell(300, 50, "Organización Aduanal de Querétaro, S.C.\nAv. Tecnológico Sur 102, Int. 3,4 y 7 Esq. Mariano Perrusquia\nCol. San Ángel, C.P. 76030, Querétaro, Querétaro.\nTeléfonos +52 (442) 242-7050 / 216-0533", "T", "L", 1, 0, '', '', true, 0, false, true, 50);
        $this->MultiCell(0, 50, "TDQ Tamex de Querétaro, LLC.\n10224 Crossrodas,\nMilo Distribution Center, Z.C. 78045, Laredo, Texas\nTeléfono 001 (956) 523-7000", "T", "L", 1, 0, '', '', true, 0, false, true, 50);
    }

    public function printTarifa() {
        $this->_data["colors"]["line"] = array(5, 5, 5);
        $this->AddPage();
        $this->SetY(70, true);
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(5, 5, 128);
        $this->SetFont($this->_fontB, "C", 14);
        $this->MultiCell(0, 0, "TARIFA DE SERVICIOS", 0, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFont($this->_fontB, "C", 12);
        $this->MultiCell(0, 0, "ENERO 2017 - ENERO 2018", 0, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln(20);
        $this->SetFont($this->_fontB, "C", 11);
        $this->SetFillColor(31, 73, 125);
        $this->SetTextColor(255, 255, 255);
        $this->MultiCell(0, 22, "CLIENTES (FACTURACIÓN A TERCEROS)\n", 0, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln(24);
        $this->SetFillColor(255, 255, 255);
        $this->SetFont($this->_font, "C", 9);
        $this->SetTextColor(5, 5, 5);
        $this->MultiCell(0, 0, "Por medio de la presente nos permitiríamos poner a su amable consideración nuestras propuestas de Tarifa de Servicios Aduanales.", 0, "J", 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln(24);
        $this->_seccion("LADO MEXICANO MXP");
        $this->Ln(20);
        $arr = array(
            "aereas" => "AÉREA",
            "maritimas" => "MARÍTIMA",
            "terrestres" => "FRONTERA",
            "especiales" => "OPERACIONES ESPECIALES",
        );
        foreach ($arr as $key => $value) {
            if (isset($this->_data[$key]) && is_array($this->_data[$key]) && count($this->_data[$key]) > 1) {
                $this->_nuevaSeccion($value);
                $this->Ln(20);
                $this->_tarifaFlat($this->_data[$key]);
                $this->Ln(6);
            }
        }
        $this->AddPage();
        $this->SetY(80, true);
        if (isset($this->_data["conceptos"]) && !empty($this->_data["conceptos"])) {
            $this->_seccion("LADO AMERICANO USD");
            $this->Ln(20);
            $this->_nuevaSeccion("TAMEX DE QUERÉTARO (LAREDO TEXAS.)", "", "");
            $this->Ln(20);
            foreach ($this->_data["conceptos"] as $item) {
                $this->SetFillColor(255, 255, 255);
                $this->SetTextColor(5, 5, 5);
                $this->SetFont($this->_font, "C", 9);
                if ($item !== null) {
                    $this->MultiCell(15, 0, "", 0, "L", 1, 0, "", "", true, 0, false, true, 0);
                    $this->MultiCell(317, 0, strtoupper($item["name"]), "", "L", 1, 0, "", "", true, 0, false, true, 0);
                    $this->MultiCell(100, 0, "$ " . number_format($item["importe"], 2, ",", "."), "", "R", 1, 0, "", "", true, 0, false, true, 0);
                    $this->MultiCell(100, 0, $item["modo"], "", "L", 1, 0, "", "", true, 0, false, true, 0);
                    $this->Ln();
                }
            }
        }
        if (isset($this->_data["otros"]) && !empty($this->_data["otros"])) {
            $this->Ln(10);
            $this->_seccion("OTROS CONCEPTOS");
            $this->Ln(20);
            $this->_nuevaSeccion("TAMEX DE QUERÉTARO (LAREDO TEXAS.)", "", "");
            $this->Ln(20);
            foreach ($this->_data["otros"] as $item) {
                $this->SetFillColor(255, 255, 255);
                $this->SetTextColor(5, 5, 5);
                $this->SetFont($this->_font, "C", 9);
                if ($item !== null) {
                    $this->MultiCell(15, 0, "", 0, "L", 1, 0, "", "", true, 0, false, true, 0);
                    $this->MultiCell(317, 0, mb_strtoupper($item["name"], "UTF-8"), "", "L", 1, 0, "", "", true, 0, false, true, 0);
                    $this->MultiCell(100, 0, "$ " . number_format($item["importe"], 2, ",", "."), "", "R", 1, 0, "", "", true, 0, false, true, 0);
                    $this->MultiCell(100, 0, $item["modo"], "", "L", 1, 0, "", "", true, 0, false, true, 0);
                    $this->Ln();
                }
            }
        }
        if (isset($this->_data["notas"]) && !empty($this->_data["notas"])) {
            $this->AddPage();
            $this->SetY(80, true);
            $notes = new Trafico_Model_TarifaNotasGenerales();
            $this->SetFillColor(89, 89, 89);
            $this->SetTextColor(255, 255, 255);
            $this->SetFont($this->_fontB, "C", 11);
            $this->MultiCell(0, 0, "NOTAS GENERALES", "RTLB", "C", 1, 0, '', '', true, 0, false, true, 0);
            $this->Ln();
            $this->SetFillColor(255, 255, 255);
            $this->SetTextColor(5, 5, 5);
            $this->SetFont($this->_font, "C", 9);
            $i = 1;
            foreach ($this->_data["notas"] as $k => $v) {
                if ($v !== null) {
                    $this->MultiCell(0, 0, $i . ". " . $notes->obtener($k), "LR", "L", 1, 0, "", "", true, 0, false, true, 0);
                    $this->Ln();
                    $i++;
                }
            }
            $this->MultiCell(0, 30, "Esperando que la presente cotización sea de su agrado, quedo como siempre a sus apreciables órdenes.", "T", "L", 1, 0, "", "", true, 0, false, true, 30, "M");
            $this->Ln();
        }
        $this->SetFont($this->_fontB, "C", 9);
        $this->MultiCell(260, 50, "Atentamente", 0, "C", 1, 0, "", "", true, 0, false, true, 50, "M");
        $this->MultiCell(260, 50, "Atentamente", 0, "C", 1, 0, "", "", true, 0, false, true, 50, "M");
        $this->Ln();
        $this->MultiCell(260, 0, "__________________________", 0, "C", 1, 0, "", "", true, 0, false, true, 0, "M");
        $this->MultiCell(260, 0, "__________________________", 0, "C", 1, 0, "", "", true, 0, false, true, 0, "M");
        $this->Ln();
        $this->MultiCell(260, 0, isset($this->_data["firmante"][0]["nombre"]) ? $this->_data["firmante"][0]["nombre"]: "", 0, "C", 1, 0, "", "", true, 0, false, true, 0, "M");
        $this->MultiCell(260, 0, isset($this->_data["firmante"][1]["nombre"]) ? $this->_data["firmante"][1]["nombre"]: "", 0, "C", 1, 0, "", "", true, 0, false, true, 0, "M");
        $this->Ln();
        $this->MultiCell(260, 0, mb_strtoupper($this->_data["empresa"], "UTF-8"), 0, "C", 1, 0, "", "", true, 0, false, true, 0, "M");
        $this->MultiCell(260, 0, mb_strtoupper($this->_data["razonSocial"], "UTF-8"), 0, "C", 1, 0, "", "", true, 0, false, true, 0, "M");
        $this->Ln();
        $this->lastPage();
    }

    protected function _seccion($nombre) {
        $this->SetFillColor(218, 150, 148);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont($this->_fontB, "C", 10);
        $this->MultiCell(0, 0, $nombre, 0, "L", 1, 0, "", "", true, 0, false, true, 0);
    }

    protected function _tarifaFlat($arr) {
        $this->SetFillColor(217, 217, 217);
        $this->SetTextColor(5, 5, 5);
        $this->SetFont($this->_font, "C", 9);
        $this->SetLineStyle(array("width" => 0.5, "cap" => "butt", "join" => "miter", "dash" => 0, "color" => array(70, 70, 70)));
        $this->MultiCell(0, 0, "TARIFA FLAT (MERCANCÍA EN GENERAL)", "TRBL", "L", 1, 0, "", "", true, 0, false, true, 0);
        $this->Ln();
        if (isset($arr) && !empty($arr)) {
            $this->SetFillColor(255, 255, 255);
            $this->SetTextColor(5, 5, 5);
            foreach ($arr as $item) {
                if ($item !== null && (isset($item["id"]) && $item["id"] != 99)) {
                    $text = mb_strtoupper($item["name"], "UTF-8");
                    $lines = $this->getNumLines($text, 217);
                    $this->MultiCell(15, 12 * $lines, "", 0, "L", 1, 0, "", "", true, 0, false, true, 0);
                    $this->MultiCell(217, 12 * $lines, mb_strtoupper($item["name"], "UTF-8"), "LTR", "L", 1, 0, "", "", true, 0, false, true, 0);
                    $this->MultiCell(100, 12 * $lines, "$ " . number_format($item["impo"], 2, ",", "."), "LTR", "R", 1, 0, "", "", true, 0, false, true, 0);
                    $this->MultiCell(100, 12 * $lines, "$ " . number_format($item["expo"], 2, ",", "."), "LTR", "R", 1, 0, "", "", true, 0, false, true, 0);
                    $this->MultiCell(100, 12 * $lines, $item["modo"], "LTR", "L", 1, 0, "", "", true, 0, false, true, 0);
                    $this->Ln();
                }
            }
            $this->MultiCell(15, 0, "", 0, "L", 1, 0, "", "", true, 0, false, true, 0);
            $this->MultiCell(0, 0, "Más de 10 fracciones, 25% sobre honorarios.", "T", "L", 1, 0, "", "", true, 0, false, true, 0);
            $this->Ln(16);
            if (isset($arr[99])) {
                $this->SetFillColor(217, 217, 217);
                $this->SetTextColor(5, 5, 5);
                $this->SetFont($this->_font, "C", 9);
                $this->SetLineStyle(array("width" => 0.5, "cap" => "butt", "join" => "miter", "dash" => 0, "color" => array(70, 70, 70)));
                $this->MultiCell(0, 0, " TARIFA PORCENTAJE (ACTIVO FIJO)", "TRBL", "L", 1, 0, "", "", true, 0, false, true, 0);
                $this->Ln();
                $this->SetFillColor(255, 255, 255);
                $this->SetTextColor(5, 5, 5);
                $this->MultiCell(15, 0, "", 0, "L", 1, 0, "", "", true, 0, false, true, 0);
                $this->MultiCell(217, 0, "MAQUINARIAS Y SUS REFACCIONES", "LTR", "L", 1, 0, "", "", true, 0, false, true, 0);
                $this->MultiCell(100, 0, number_format($arr[99]["impo"], 2) . " %", "LTR", "R", 1, 0, "", "", true, 0, false, true, 0);
                $this->MultiCell(100, 0, number_format($arr[99]["expo"], 2) . " %", "LTR", "R", 1, 0, "", "", true, 0, false, true, 0);
                $this->MultiCell(100, 0, $arr[99]["modo"], "LTR", "L", 1, 0, "", "", true, 0, false, true, 0);
                $this->Ln();
                $this->MultiCell(15, 0, "", 0, "L", 1, 0, "", "", true, 0, false, true, 0);
                $this->MultiCell(0, 0, "Tomando como mínimo la Tarifa Flat de Mercancía en General.", "T", "L", 1, 0, "", "", true, 0, false, true, 0);
                $this->Ln(16);
            }
        }
    }

    protected function _nuevaSeccion($nombre, $impo = "Importación", $expo = "Exportación", $modo = "Modo de calculo") {
        $this->SetFillColor(89, 89, 89);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont($this->_fontB, "C", 9);
        $this->MultiCell(232, 0, $nombre, 0, "L", 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(100, 0, $impo, 0, "L", 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(100, 0, $expo, 0, "L", 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(100, 0, $modo, 0, "L", 1, 0, '', '', true, 0, false, true, 0);
    }

    protected function _fontNormal($width, $text, $align = "L", $multi = 1, $border = "TBRL", $valign = "M") {
        $this->SetFont($this->_font, "C", $this->_fontSize);
        $this->MultiCell($width, $this->_lineh * $multi, $text, $border, $align, 1, 0, "", "", true, 0, true, true, $this->_lineh, $valign);
    }

    protected function _fontBold($width, $text, $align = "L", $multi = 1, $border = "TBRL", $valign = "M") {
        $this->SetFont($this->_fontB, "C", $this->_fontSize);
        $this->MultiCell($width, $this->_lineh * $multi, $text, $border, $align, 1, 0, "", "", true, 0, true, true, $this->_lineh, $valign);
    }

}
