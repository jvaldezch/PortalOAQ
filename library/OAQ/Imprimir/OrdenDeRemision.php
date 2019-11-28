<?php

require "tcpdf/tcpdf.php";

class OAQ_Imprimir_OrdenDeRemision extends TCPDF {

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
        $this->MultiCell(434, 50, "ORDEN DE REMISIÓN", 0, 'C', 1, 0, '', '', true, 0, false, true, 50, 'M');
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

    public function ordenDeRemision() {
        $this->AddPage();
        $this->SetY(80, true);
        $this->SetLineStyle(array("width" => 0.5, "cap" => "butt", "join" => " 'miter'", "dash" => 0, "color" => array(70, 70, 70)));
        $this->SetFont($this->_font, "B", 12);
        $this->SetFillColor(230,225, 230);
        $this->MultiCell(534, 10, '', "TLR", 'C', 1, 0, '', '', true, 0, false, true, 10, 'M');
        $this->SetFont($this->_font, "B", $this->_fontSize);
        $this->Ln();
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->MultiCell(534, 10, "EXPORTACIÓN", "LR", "C", 1, 0, "", "", true, 0, true, true, 10);
        $this->Ln();
        $this->MultiCell(60, 10, "PATENTE:", "LB", "L", 1, 0, "", "", true, 0, true, true, 10);
        $this->SetFont($this->_font, "C", $this->_fontSize);
        $this->MultiCell(354, 10, $this->_data["patente"], "B", "L", 1, 0, "", "", true, 0, true, true, 10);
        $this->SetFont($this->_font, "B", $this->_fontSize);
        $this->MultiCell(60, 10, "FECHA:", "B", "L", 1, 0, "", "", true, 0, true, true, 10);
        $this->SetFont($this->_font, "C", $this->_fontSize);
        $this->MultiCell(60, 10, date("Y-m-d"), "BR", "L", 1, 0, "", "", true, 0, true, true, 10);
        
        $this->Ln(20);
        $this->MultiCell(534, 5, "", "TLR", "L", 1, 0, "", "", true, 0, true, true, 5);
        $this->Ln();
        $this->MultiCell(120, 10, "TRANSFER / DIRECTO:", "L", "L", 1, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell(320, 10, $this->_data["transfer"], "B", "L", 0, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell(94, 10, "", "R", "L", 0, 0, "", "", true, 0, true, true, 10);
        $this->Ln();
        $this->MultiCell(120, 5, "", "L", "L", 0, 0, "", "", true, 0, true, true, 5);
        $this->MultiCell(320, 5, "", "T", "L", 0, 0, "", "", true, 0, true, true, 5);
        $this->MultiCell(94, 5, "", "R", "L", 0, 0, "", "", true, 0, true, true, 5);
        $this->Ln();
        $this->MultiCell(120, 10, "CLIENTE:", "L", "L", 1, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell(320, 10, $this->_data["nombreCliente"], "B", "L", 0, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell(94, 10, "", "R", "L", 0, 0, "", "", true, 0, true, true, 10);
        $this->Ln();
        $this->MultiCell(120, 5, "", "BL", "L", 0, 0, "", "", true, 0, true, true, 5);
        $this->MultiCell(320, 5, "", "B", "L", 0, 0, "", "", true, 0, true, true, 5);
        $this->MultiCell(94, 5, "", "BR", "L", 0, 0, "", "", true, 0, true, true, 5);
        
        $this->Ln(20);
        $this->SetFillColor(230,225, 230);
        $this->MultiCell(534, 10, "DOCUMENTOS ANEXOS:", "TBLR", "C", 1, 0, "", "", true, 0, true, true, 10);
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->Ln();
        $this->MultiCell(380, 5, "", "TLR", "L", 0, 0, "", "", true, 0, true, true, 5);
        $this->MultiCell(5, 5, "", "LT", "L", 0, 0, "", "", true, 0, true, true, 5);
        $this->MultiCell(149, 5, "", "TLR", "L", 0, 0, "", "", true, 0, true, true, 5);
        $this->Ln();
        $this->MultiCell(80, 10, "PED. SIMPLICADO", "L", "R", 0, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell(10, 10, ($this->_data["pedimentoSimplificado"] == 1) ? "x" : "", 1, "C", 0, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell(60, 10, "MANIFIESTO", "L", "R", 0, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell(10, 10, ($this->_data["manifiesto"] == 1) ? "x" : "", 1, "C", 0, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell(60, 10, "IN-BOND", "L", "R", 0, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell(10, 10, ($this->_data["inBond"] == 1) ? "x" : "", 1, "C", 0, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell(35, 10, "B/L", "L", "R", 0, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell(10, 10, ($this->_data["bl"] == 1) ? "x" : "", 1, "C", 0, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell(90, 10, "REL. DOCTOS.", "L", "R", 1, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell(10, 10, ($this->_data["relacionDocumentos"] == 1) ? "x" : "", 1, "C", 0, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell(5, 10, "", "RL", "L", 0, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell(5, 10, "", "RL", "L", 0, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell(30, 10, "CAJA", "L", "L", 0, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell(119, 10, $this->_data["caja"], "R", "L", 0, 0, "", "", true, 0, true, true, 10);
        $this->Ln();
        $this->MultiCell(380, 5, "", "BLR", "L", 0, 0, "", "", true, 0, true, true, 5);
        $this->MultiCell(5, 5, "", "LB", "L", 0, 0, "", "", true, 0, true, true, 5);
        $this->MultiCell(149, 5, "", "BLR", "L", 0, 0, "", "", true, 0, true, true, 5);
        
        $this->Ln(20);
        $this->MultiCell(534, 5, '', "TLR", 'C', 0, 0, '', '', true, 0, false, true, 5, 'M');
        $this->Ln();
        $cols = array(10, 80, 65, 175, 164);
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->MultiCell($cols[0], 10, "", "L", "C", 1, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell($cols[1], 10, "REFERENCIA", 0, "C", 1, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell($cols[0], 10, "", 0, "C", 1, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell($cols[2], 10, "PEDIMENTO", "", "C", 1, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell($cols[0], 10, "", 0, "C", 1, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell($cols[3], 10, "LÍNEA TRANSPORTISTA", "", "C", 1, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell($cols[0], 10, "", 0, "C", 1, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell($cols[4], 10, "ADUANA DE DESPACHO", 0, "C", 1, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell($cols[0], 10, "", "R", "C", 1, 0, "", "", true, 0, true, true, 10);
        $this->Ln();
        $this->MultiCell($cols[0], 20, "", "L", "C", 0, 0, "", "", true, 0, true, true, 20);
        $this->MultiCell($cols[1], 20, $this->_data["referencia"], "B", 'C', 0, 0, '', '', true, 0, false, true, 20, 'M');
        $this->MultiCell($cols[0], 20, "", 0, "C", 0, 0, "", "", true, 0, true, true, 20);
        $this->MultiCell($cols[2], 20, $this->_data["pedimento"], "B", "C", 0, 0, "", "", true, 0, false, true, 20, 'M');
        $this->MultiCell($cols[0], 20, "", 0, "C", 0, 0, "", "", true, 0, true, true, 20);
        $this->MultiCell($cols[3], 20, $this->_data["lineaTransportista"], "B", "C", 0, 0, "", "", true, 0, false, true, 20, 'M');
        $this->MultiCell($cols[0], 20, "", 0, "C", 0, 0, "", "", true, 0, true, true, 20);
        $this->MultiCell($cols[4], 20, $this->_data["aduanaDespacho"], "B", "C", 0, 0, "", "", true, 0, false, true, 20, 'M');
        $this->MultiCell($cols[0], 20, "", "R", "C", 0, 0, "", "", true, 0, true, true, 20);
        foreach (range(0, 4) as $value) {
            $this->Ln();
            $this->MultiCell($cols[0], 20, "", "L", "C", 0, 0, "", "", true, 0, true, true, 20);
            $this->MultiCell($cols[1], 20, "", "B", 'C', 0, 0, '', '', true, 0, false, true, 20, 'M');
            $this->MultiCell($cols[0], 20, "", 0, "C", 0, 0, "", "", true, 0, true, true, 20);
            $this->MultiCell($cols[2], 20, "", "B", "C", 0, 0, "", "", true, 0, false, true, 20, 'M');
            $this->MultiCell($cols[0], 20, "", 0, "C", 0, 0, "", "", true, 0, true, true, 20);
            $this->MultiCell($cols[3], 20, "", "B", "C", 0, 0, "", "", true, 0, false, true, 20, 'M');
            $this->MultiCell($cols[0], 20, "", 0, "C", 0, 0, "", "", true, 0, true, true, 20);
            $this->MultiCell($cols[4], 20, "", "B", "C", 0, 0, "", "", true, 0, false, true, 20, 'M');
            $this->MultiCell($cols[0], 20, "", "R", "C", 0, 0, "", "", true, 0, true, true, 20);            
        }
        $this->Ln();
        $this->MultiCell(534, 5, '', "BLR", 'C', 0, 0, '', '', true, 0, false, true, 5, 'M');
        
        $this->Ln(20);
        $this->SetFillColor(230,225, 230);
        $this->MultiCell(534, 20, 'DATOS DE ENTREGA EN EL LADO AMERICANO (LAREDO, TEXAS):', "TLR", 'C', 1, 0, '', '', true, 0, false, true, 20, 'M');
        $this->Ln();
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->MultiCell(5, 10, "", "L", "C", 1, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell(115, 10, "BODEGA", 0, "C", 1, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell(5, 10, "", 0, "C", 1, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell(115, 10, "FECHA RECIBIDO", "", "C", 1, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell(5, 10, "", 0, "C", 1, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell(115, 10, "HORA DE RECIBIDO", "", "C", 1, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell(5, 10, "", 0, "C", 1, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell(164, 10, "NOMBRE Y FIRMA", 0, "C", 1, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell(5, 10, "", "R", "C", 1, 0, "", "", true, 0, true, true, 10);
        $this->Ln();
        $this->MultiCell(534, 25, '', "LR", 'C', 1, 0, '', '', true, 0, false, true, 25, 'M');
        $this->Ln();
        $this->MultiCell(5, 10, "", "L", "C", 1, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell(115, 10, "________________________", 0, "C", 1, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell(5, 10, "", 0, "C", 1, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell(115, 10, "________________________", 0, "C", 1, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell(5, 10, "", 0, "C", 1, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell(115, 10, "________________________", 0, "C", 1, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell(5, 10, "", 0, "C", 1, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell(164, 10, "__________________________________", 0, "C", 1, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell(5, 10, "", "R", "C", 1, 0, "", "", true, 0, true, true, 10);
        $this->Ln();
        $this->MultiCell(534, 5, '', "BLR", 'C', 1, 0, '', '', true, 0, false, true, 5, 'M');
        $this->Ln();
        
        $this->Ln(10);
        $this->SetFillColor(230,225, 230);
        $this->MultiCell(534, 20, 'INSTRUCCIONES ESPECIALES', "TLR", 'C', 1, 0, '', '', true, 0, false, true, 20, 'M');        
        $this->Ln();
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->MultiCell(534, 45, $this->_data["instrucciones"], "BLR", 'L', 1, 0, '', '', true, 0, false, true, 45);
        $this->Ln();
        
        $this->Ln(10);
        $this->SetFillColor(230,225, 230);
        $this->MultiCell(534, 10, "EN CASO DE ALGUN PROBLEMA FAVOR DE CONTACTAR A:", "TBLR", "C", 1, 0, "", "", true, 0, true, true, 10);
        $this->Ln(15);
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        if (isset($this->_data["contactos"]) && !empty($this->_data["contactos"])) {
            foreach ($this->_data["contactos"] as $value) {
                $this->MultiCell(200, 10, $value["nombre"], 0, "L", 1, 0, "", "", true, 0, true, true, 10);
                $this->MultiCell(130, 10, $value["telefono2"], 0, "L", 1, 0, "", "", true, 0, true, true, 10);
                $this->MultiCell(150, 10, $value["telefono1"], 0, "L", 1, 0, "", "", true, 0, true, true, 10);
                $this->Ln();
            }
        }
        
        $this->Ln(20);
        $this->MultiCell(200, 10, "ATENTAMENTE:", 0, "L", 1, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell(130, 10, "", 0, "L", 1, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell(200, 10, "RECIBI DE CONFORMIDAD:", 0, "L", 1, 0, "", "", true, 0, true, true, 10);
        $this->Ln(40);
        $this->MultiCell(200, 10, "", "B", "L", 1, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell(130, 10, "", 0, "L", 1, 0, "", "", true, 0, true, true, 10);
        $this->MultiCell(200, 10, "", "B", "L", 1, 0, "", "", true, 0, true, true, 10);
        $this->Ln(12);
        $this->MultiCell(200, 10, $this->_data["elaboro"], 0, "L", 1, 0, "", "", true, 0, true, true, 10);
        $this->Ln();
        $this->MultiCell(200, 10, $this->_data["empresa"], 0, "L", 1, 0, "", "", true, 0, true, true, 10);
        
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
