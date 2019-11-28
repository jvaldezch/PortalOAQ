<?php

require "tcpdf/tcpdf.php";

class OAQ_Imprimir_ImprimirEdocument2019 extends TCPDF {

    protected $_dir;
    protected $_filename;
    protected $_margin;
    protected $_data;
    
    function __construct($data, $orientation, $unit, $format) {
        parent::__construct($orientation, $unit, $format, true, "UTF-8", false);
        $this->_data = $data;
        $this->_margins = 26;
        $this->SetFont("helvetica", "C", 10);
        $this->SetMargins($this->_margins, 100, $this->_margins, true);
        $this->SetAutoPageBreak(true, 150);
        $this->SetCreator(PDF_CREATOR);
        $this->SetAuthor("Jaime E. Valdez");
        if (isset($this->_data["titulo"])) {
            $this->SetTitle($this->_data["titulo"]);
        } else {
            $this->SetTitle("ACUSE_" . $this->_data["edoc"]);
        }
        $this->SetSubject("");
        $this->SetKeywords("");
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);
    }
    
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

    public function Header() {
        $this->SetY(25);
        $this->SetFillColor(57, 60, 62);
        $this->SetTextColor(57, 60, 62);
        $this->MultiCell(0, 40, "", 0, 'C', 1, 0, '', '', true, 0, false, true, 0);        
        $this->ImageSVG(K_PATH_IMAGES . 'gob_mx.svg', $x=40, $y=34, $w='', $h=20, $link='', $align='', $palign='', $border=0, $fitonpage=false);
        $this->Ln();
        $this->SetFillColor(220, 220, 220);
        $this->SetTextColor(57, 60, 62);
        $this->SetFont('helveticaB', '', 10);
        $this->SetLineStyle(array("width" => 0.5, "cap" => "butt", "join" => " 'miter'", "dash" => 0, "color" => array(57, 60, 62)));
        $this->MultiCell(0, 0, "Acuse de digitalización de documentos\nVentanilla digital mexicana de comercio exterior\nPromoción o solicitud en materia de comercio exterior ", 'LRB', 'C', 1, 0, '', '', true, 0, false, true, 0);
    }

    public function Footer() {        
        $this->SetY(-175);
        $this->SetFont('helvetica', false, 8);
        
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(57, 60, 62);
        $this->SetLineStyle(array("width" => 1, "cap" => "butt", "join" => " 'miter'", "dash" => 0, "color" => array(57, 60, 62)));
        $this->MultiCell(0, 0, "Tiene 90 días a partir de esta fecha para utilizar su Acuse de Valor, si en ese tiempo no utiliza su comprobante, será dado de baja del sistema.", 'T', 'J', 1, 0, '', '', true, 0, true, true, 0);
        $this->Ln(15);
        $this->MultiCell(0, 0, "Los datos personales suministrados a través de las solicitudes, promociones, trámites, consultas y pagos, hechos por medios electrónicos e impresos, serán protegidos, incorporados y tratados en el sistema de datos personales de la \"Ventanilla Digital\" acorde con la Ley Federal de Transparencia y Acceso a la Información Pública Gubernamental y las demás disposiciones legales aplicables; y podrán ser transmitidos a las autoridades competentes en materia de comercio exterior, al propio titular de la información, o a terceros, en este último caso siempre que las disposiciones aplicables contemplen dicha transferencia.", 'T', 'J', 1, 0, '', '', true, 0, true, true, 0);

        $this->Image(K_PATH_IMAGES . 'vu_acuse_2019b.jpg', 26, 690, 560, 78, 'JPG', false, '', true, 150, '', false, false, 0, false, false, false);
    }
    
    public function Create() {
        $this->AddPage();
        $fontSizeDocTitle = 15;
        $fontSizeTitle = 10;
        $fontSize = 9;
        $txtHighlight = array(0, 112, 192);
        $font = "helvetica";
        $fontBold = "helveticaB";
        $this->SetFont($fontBold, "", $fontSizeDocTitle);
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor($txtHighlight[0], $txtHighlight[1], $txtHighlight[2]);
        $this->Ln(10);
        if (isset($this->_data["numTramite"])) {
            $this->SetFont($font, "", 11);
            $this->SetFillColor(255, 255, 255);
            $this->SetTextColor(20, 20, 20);
            $this->MultiCell(340, 0, "Folio de la solicitud:", 0, "R", 1, 0, "", "", true, 0, false, true, 0);
            $this->MultiCell(210, 0, $this->_data["numTramite"], 0, "R", 1, 0, "", "", true, 0, false, true, 0);
            $this->Ln();
        }
        $this->_firmante();
        $this->Ln();
        $this->SetFont($font, "", 9);
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(0, 0, "Siendo las " . date("H:i", strtotime($this->_data["actualizado"])) . " del " . date("d/m/Y", strtotime($this->_data["actualizado"])) . ", se tiene por recibida y atendida su solicitud de registro de Documentos Digitalizados presentado a través de la ventanilla única\n", 0, "J", 1, 0, "", "", true, 0, false, true, 0);
        $this->Ln(15);
        $this->SetFont($fontBold, "", 11);
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(0, 45, "Los datos de cada documento son los siguientes:", 0, "C", 1, 0, "", "", true, 0, false, true, 0);
        $lnHeight = 18;
        $empty = 50;
        $column1 = 170;
        $column2 = 280;
        $this->Ln(15);
        $this->SetFont($fontBold, "", $fontSizeTitle);
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell($empty, $lnHeight, "", 0, "L", 1, 0, "", "", true, 0, false, true, 0);
        $this->MultiCell($column1, $lnHeight, "Operación", "TRL", "L", 1, 0, "", "", true, 0, false, true, 0);
        $this->MultiCell($column2, $lnHeight, "Registro de documentos digitalizados", "TRL", "L", 1, 0, "", "", true, 0, false, true, 0);
        $this->Ln();
        $this->SetFont($font, "", $fontSize);
        $this->MultiCell($empty, $lnHeight, "", 0, "L", 1, 0, "", "", true, 0, false, true, 0);
        $this->MultiCell($column1, $lnHeight, "Número e_document", "TRL", "L", 1, 0, "", "", true, 0, false, true, 0);
        $this->MultiCell($column2, $lnHeight, $this->_data["edoc"], "TRL", "L", 1, 0, "", "", true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell($empty, $lnHeight, "", 0, "L", 1, 0, "", "", true, 0, false, true, 0);
        $this->MultiCell($column1, $lnHeight, "Tipo de documento", "TRL", "L", 1, 0, "", "", true, 0, false, true, 0);
        $this->MultiCell($column2, $lnHeight, $this->_data["tipoDoc"], "TRL", "L", 1, 0, "", "", true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell($empty, $lnHeight, "", 0, "L", 1, 0, "", "", true, 0, false, true, 0);
        $this->MultiCell($column1, $lnHeight, "Nombre del documento", "TRL", "L", 1, 0, "", "", true, 0, false, true, 0);
        $this->MultiCell($column2, $lnHeight, $this->_data["nomArchivo"], "TRL", "L", 1, 0, "", "", true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell($empty, $lnHeight, "", 0, "L", 1, 0, "", "", true, 0, false, true, 0);
        $this->MultiCell($column1, $lnHeight, "RFC para consulta", "TRL", "L", 1, 0, "", "", true, 0, false, true, 0);
        $this->MultiCell($column2, $lnHeight, $this->_data["rfcConsulta"], "TRL", "L", 1, 0, "", "", true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell($empty, 45, "", 0, "L", 1, 0, "", "", true, 0, false, true, 0);
        $this->MultiCell($column1, 45, "Fecha de registro(En la que se dio de alta el registro de documentos digitalizados)", "TRL", "L", 1, 0, "", "", true, 0, false, true, 0);
        $this->MultiCell($column2, 45, $this->_data["actualizado"], "TRL", "L", 1, 0, "", "", true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell($empty, $lnHeight, "", 0, "L", 1, 0, "", "", true, 0, false, true, 0);
        $this->MultiCell($column1, $lnHeight * $this->getNumLines($this->_data["cadena"], $column1), "Cadena Original (del documento)", "TRL", "L", 1, 0, "", "", true, 0, false, true, 0);
        $this->MultiCell($column2, $lnHeight * $this->getNumLines($this->_data["cadena"], $column2), $this->_data["cadena"], "TRL", "L", 1, 0, "", "", true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell($empty, $lnHeight, "", 0, "L", 1, 0, "", "", true, 0, false, true, 0);
        $this->MultiCell($column1, $lnHeight * $this->getNumLines($this->_data["firma"], $column2), "Sello digital del solicitante(del documento)", "TRL", "L", 1, 0, "", "", true, 0, false, true, 0);
        $this->MultiCell($column2, $lnHeight * $this->getNumLines($this->_data["firma"], $column2), $this->_data["firma"], "TRL", "L", 1, 0, "", "", true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell($empty, $lnHeight, "", 0, "L", 1, 0, "", "", true, 0, false, true, 0);
        $this->MultiCell($column1, 50, "Leyenda", 1, "L", 1, 0, "", "", true, 0, false, true, 0);
        $this->MultiCell($column2, 50, "Tiene 90 días a partir de esta fecha para utilizar su documento digitalizado, si en ese tiempo no lo utiliza, será dado de baja del sistema.", 1, "L", 1, 0, "", "", true, 0, false, true, 0);
        $this->lastPage();
    }

    protected function _firmante() {
        if (isset($this->_data["razonSocial"])) {
            $fontSizeTitle = 10;
            $fontSize = 10;
            $txtHighlight = array(20, 20, 20);
            $font = "helvetica";
            $fontBold = "helveticaB";
            $lnHeight = 0;
            $column1 = 90;
            $column2 = 300;
            $this->Ln(15);
            $this->SetFont($fontBold, "", $fontSizeTitle);
            $this->SetFillColor(255, 255, 255);
            $this->SetTextColor($txtHighlight[0], $txtHighlight[1], $txtHighlight[2]);
            $this->MultiCell($column1, $lnHeight, "Estimado(a) C. ", 0, "L", 1, 0, "", "", true, 0, false, true, 0);
            $this->SetFont($font, "", $fontSize);
            $this->SetFillColor(255, 255, 255);
            $this->SetTextColor($txtHighlight[0], $txtHighlight[1], $txtHighlight[2]);
            $this->MultiCell($column2, $lnHeight, $this->_data["razonSocial"], 0, "L", 1, 0, "", "", true, 0, false, true, 0);
            $this->Ln();
            $this->SetFont($fontBold, "", $fontSizeTitle);
            $this->SetFillColor(255, 255, 255);
            $this->SetTextColor($txtHighlight[0], $txtHighlight[1], $txtHighlight[2]);
            $this->MultiCell($column1, $lnHeight, "RFC: ", 0, "L", 1, 0, "", "", true, 0, false, true, 0);
            $this->SetFont($font, "", $fontSize);
            $this->SetFillColor(255, 255, 255);
            $this->SetTextColor($txtHighlight[0], $txtHighlight[1], $txtHighlight[2]);
            $this->MultiCell($column2, $lnHeight, $this->_data["rfc"], 0, "L", 1, 0, "", "", true, 0, false, true, 0);
            $this->Ln(15);
        }
    }
    

}
