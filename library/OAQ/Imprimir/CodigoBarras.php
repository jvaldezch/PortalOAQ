<?php

require "tcpdf/config/tcpdf_config.php";
require "tcpdf/tcpdf.php";

class OAQ_Imprimir_CodigoBarras extends TCPDF {

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
        $pagelayout = array(152, 101); // 6x4 in
        parent::__construct($orientation, 'mm', $pagelayout, true, "UTF-8", false);
        $this->_margins = 5;
        $this->SetMargins($this->_margins, 5, $this->_margins, true);
        $this->SetAutoPageBreak(false, 5);
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
        
    }

    public function Footer() {
        
    }

    public function Create() {
        $this->AddPage();

        $this->SetFont('helvetica', false, 9);

        // set style for barcode
        $style = array(
            'border' => 2,
            'vpadding' => 1,
            'hpadding' => 1,
            'fgcolor' => array(0, 0, 0),
            'bgcolor' => false, //array(255,255,255)
            'module_width' => 1, // width of a single module in points
            'module_height' => 1 // height of a single module in points
        );

        $this->SetY(5);
        for ($i = 1; $i <= count($this->_data['bultos']); $i++) {
            $this->SetFillColor(255, 255, 255);
            $this->SetTextColor(0, 0, 0);
            $this->Ln();
            $this->MultiCell(0, 0, "BODEGA: " . $this->_data['nombre_bodega'], false, 'J', 1, 0, '', '', true, 0, true, true, 0);
            $this->Ln();
            $this->MultiCell(0, 0, "UUID: " . $this->_data['bultos'][$i]['uuid'], false, 'J', 1, 0, '', '', true, 0, true, true, 0);
            $this->Ln();
            $this->MultiCell(0, 0, "REFERENCIA: " . $this->_data['referencia'], false, 'J', 1, 0, '', '', true, 0, true, true, 0);
            $this->Ln();
            $this->MultiCell(0, 0, "RFC CLIENTE: " . $this->_data['rfc_cliente'], false, 'J', 1, 0, '', '', true, 0, true, true, 0);
            $this->Ln();
            $this->MultiCell(0, 0, "BULTO: " . $i, false, 'J', 1, 0, '', '', true, 0, true, true, 0);
            
            $json_arr = array(
                "uuid" => $this->_data['bultos'][$i]['uuid'],
                "id_trafico" => $this->_data['id_trafico'],
                "referencia" => $this->_data['referencia'],
                "rfc_cliente" => $this->_data['rfc_cliente'],
                "id_bulto" => $this->_data['bultos'][$i]['id_bulto'],
                "bulto" => (string) $i
            );

            $this->write2DBarcode(json_encode($json_arr), 'QRCODE,L', 5, 30, 50, 50, $style, 'N');

            $this->ImageSVG(K_PATH_IMAGES . $this->_data["logo"], $x = 100, $y = 5, $w = '', $h = 13, $link = '', $align = '', $palign = '', $border = 0, $fitonpage = false);
            
            $this->SetFont('helvetica', false, 8);
            $this->Ln();
            $this->MultiCell(0, 0, $this->_data["direccion"], 'T', 'J', 1, 0, '', '', true, 0, true, true, 0);
            $this->SetFont('helvetica', false, 9);
            
            if ($i < count($this->_data['bultos'])) {
                $this->AddPage();
                $this->SetY(0);
            }
        }

        $this->lastPage();
    }

}
