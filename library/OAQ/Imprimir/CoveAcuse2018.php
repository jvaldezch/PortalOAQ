<?php

require "tcpdf/tcpdf.php";

class OAQ_Imprimir_CoveAcuse2018 extends TCPDF {

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
        $this->_margins = 26;
        $this->SetMargins($this->_margins, 100, $this->_margins, true);
        $this->SetAutoPageBreak(true, 150);
        $this->_data = $data;
        $this->SetFont($this->_font, "C", $this->_fontSize);
        $this->SetCreator(PDF_CREATOR);
        $this->SetAuthor("Jaime E. Valdez");
        $this->SetTitle($this->_data["filename"]);
        $this->SetSubject("");
        $this->SetKeywords("");
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);
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
        $this->MultiCell(0, 0, "Acuse Información de Valor y de Comercialización\nVentanilla digital mexicana de comercio exterior\nPromoción o solicitud en materia de comercio exterior ", 'LRB', 'C', 1, 0, '', '', true, 0, false, true, 0);
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

        $this->Image(K_PATH_IMAGES . 'vu_acuse_2018.jpg', 26, 690, 560, 78, 'JPG', false, '', true, 150, '', false, false, 0, false, false, false);
    }

    public function Create() {
        $this->AddPage();

        $vucem = new OAQ_Vucem();
        $array = $vucem->xmlStrToArray($this->_data["xml"]);
        $data = $array["Body"]["solicitarRecibirCoveServicio"]["comprobantes"];

        $fontSizeDocTitle = 15;
        $fontSizeTitle = 9;
        $fontSize = 8;
        $txtHighlight = array(0, 112, 192);
        $font = 'helvetica';
        $fontBold = 'helveticaB';
        $tblTitle = array(220, 220, 220);
        $tbl = array(255, 255, 255);

        $this->_firmante($array["Header"]["Security"]["UsernameToken"]["Username"]);

        $this->Ln();
        $this->SetFont($font, '', 8);
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(0, 0, "Siendo las " . date('H:i:s', strtotime($this->_data["actualizado"])) . " del " . date('d/m/Y', strtotime($this->_data["actualizado"])) . ", se tiene por recibida y atendida su(s) registro(s) de Información de Valor y de Comercialización presentado(s) a través de la Ventanilla Digital Mexicana de Comercio.\n", 0, 'J', 1, 0, '', '', true, 0, false, true, 0);

        $lnHeight = 18;
        $column1 = 180;
        $column2 = 300;
        $this->Ln(25);
        $this->SetFont($fontBold, '', $fontSizeTitle);
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell($column1, $lnHeight, "Operación:", 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($font, '', $fontSize);
        $this->SetFillColor(255, 255, 255);
        $this->MultiCell($column2, $lnHeight, "Registro de Información de Valor y de Comercialización", 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFont($fontBold, '', $fontSizeTitle);
        $this->SetFillColor(255, 255, 255);
        $this->MultiCell($column1, $lnHeight, "Número de Acuse de Valor:", 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($font, '', $fontSize);
        $this->SetFillColor(255, 255, 255);
        $this->MultiCell($column2, $lnHeight, $this->_data["cove"], 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFont($fontBold, '', $fontSizeTitle);
        $this->SetFillColor(255, 255, 255);
        $this->MultiCell($column1, $lnHeight, "Número de ADENDA:", 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($font, '', $fontSize);
        $this->SetFillColor(255, 255, 255);
        $this->MultiCell($column2, $lnHeight, "N/A", 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFont($fontBold, '', $fontSizeTitle);
        $this->SetFillColor(255, 255, 255);
        $this->MultiCell($column1, $lnHeight, "Fecha de registro:", 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($font, '', $fontSize);
        $this->SetFillColor(255, 255, 255);
        $this->MultiCell($column2, $lnHeight, date('d/m/Y H:i:s', strtotime($this->_data["creado"])), 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln(5);

        $this->Ln();
        $this->SetFont($fontBold, '', 9);
        $this->SetFillColor(255, 255, 255);
        $this->MultiCell(0, 18, "Sello Digital del Solicitante:", 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFont($font, '', 8);
        $this->SetFillColor(255, 255, 255);
        $this->MultiCell(0, 0, $data["firmaElectronica"]["firma"], 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln(25);
        $this->SetFont($fontBold, '', 9);
        $this->SetFillColor(255, 255, 255);
        $this->MultiCell(0, 18, "Cadena original:", 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFont($font, '', 8);
        $this->SetFillColor(255, 255, 255);
        $this->MultiCell(0, 0, utf8_decode($data["firmaElectronica"]["cadenaOriginal"]), 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->lastPage();
    }

    protected function _firmante($rfc) {
        $model = new Vucem_Model_VucemFirmanteMapper();
        $nombre = $model->nombreFirmante($rfc);
        if (isset($nombre)) {
            $fontSizeTitle = 8;
            $fontSize = 8;
            $txtHighlight = array(20, 20, 20);
            $font = 'helvetica';
            $fontBold = 'helveticaB';

            $lnHeight = 0;
            $column1 = 90;
            $column2 = 300;

            $this->Ln(5);
            $this->SetFont($fontBold, '', $fontSizeTitle);
            $this->SetFillColor(255, 255, 255);
            $this->SetTextColor($txtHighlight[0], $txtHighlight[1], $txtHighlight[2]);
            $this->MultiCell($column1, $lnHeight, "Estimado(a) C. ", 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->SetFont($font, '', $fontSize);
            $this->SetFillColor(255, 255, 255);
            $this->SetTextColor($txtHighlight[0], $txtHighlight[1], $txtHighlight[2]);
            $this->MultiCell($column2, $lnHeight, $nombre["razon"], 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->Ln();
            $this->SetFont($fontBold, '', $fontSizeTitle);
            $this->SetFillColor(255, 255, 255);
            $this->SetTextColor($txtHighlight[0], $txtHighlight[1], $txtHighlight[2]);
            $this->MultiCell($column1, $lnHeight, "RFC: ", 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->SetFont($font, '', $fontSize);
            $this->SetFillColor(255, 255, 255);
            $this->SetTextColor($txtHighlight[0], $txtHighlight[1], $txtHighlight[2]);
            $this->MultiCell($column2, $lnHeight, $rfc, 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->Ln(5);
        }
    }

}
