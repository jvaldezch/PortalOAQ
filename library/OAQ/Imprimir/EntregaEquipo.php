<?php

require_once "tcpdf/tcpdf.php";

class OAQ_Imprimir_EntregaEquipo extends TCPDF
{

    protected $_dir;
    protected $_filename;
    protected $_lineh = 12;
    protected $_margins = 40;
    protected $_font = "helvetica";
    protected $_fontBold = "helveticaB";
    protected $_fontSize = 8;
    protected $_fontSmall = 6.5;
    protected $_fontSizeDocTitle = 15;
    protected $_fontSizeTitle = 10;
    protected $_marginTop = 20;
    protected $_shade = array(70, 70, 70);
    protected $_shaden = array(255, 255, 255);
    protected $_second = false;
    protected $_data = false;
    protected $_cp = false;
    protected $_inc = false;

    function set_dir($_dir)
    {
        $this->_dir = $_dir;
    }

    function set_filename($_filename)
    {
        $this->_filename = $_filename;
    }

    function set_data($_data)
    {
        $this->_data = $_data;
    }

    function get_filename()
    {
        return $this->_filename;
    }

    function __construct($data, $orientation, $unit, $format)
    {
        parent::__construct($orientation, $unit, $format, true, "UTF-8", false);
        $this->_margins = 26;
        $this->SetMargins($this->_margins, 100, $this->_margins, true);
        $this->SetAutoPageBreak(true, 80);
        $this->_data = $data;
        $this->SetFont($this->_font, "C", $this->_fontSize);
        $this->SetCreator(PDF_CREATOR);
        $this->SetAuthor("Jaime E. Valdez");
        $this->SetTitle($this->_data["filename"]);
        $this->SetSubject("");
        $this->SetKeywords("");
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);
    }

    public function Header()
    {
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

    public function Footer()
    {
        $this->SetXY(62, -48);
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->SetFont($this->_font, "C", 6);
        $this->SetLineStyle(array("width" => 1.0, "cap" => "butt", "join" => " 'miter'", "dash" => 0, "color" => array(54, 125, 162)));
        $this->MultiCell(490, 38, "Última actualización del documento: {$this->_data["actualizacion"]} por {$this->_data["autor"]} versión {$this->_data["version"]}", "T", "L", 1, 0, "", "", true, 0, true, true, 8, "B");
        $this->SetFont($this->_font, "C", 8);
        $this->MultiCell(40, 38, "1 de 1", "T", "R", 1, 0, "", "", true, 0, true, true, 8, "B");
    }

    public function Create()
    {
        setlocale(LC_TIME, 'es_ES.UTF-8');

        $this->AddPage();
        
        $this->SetY(110, true);
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->SetLineStyle(array("width" => 0.5, "cap" => "butt", "join" => " 'miter'", "dash" => 0, "color" => array(70, 70, 70)));

        $lineh = 9;
        $fontSize = 9;
        $ln = 22;

        $this->_fontNormal(530, "Querétaro, Querétaro a " . strftime("%A, %d de %B de %Y"), "R", 2, "", "B", $lineh, $fontSize);
        $this->Ln($ln);
        $this->_fontNormal(530, "COMPROBANTE DE ENTREGA DE EQUIPO DE CÓMPUTO Y CREDENCIALES ELECTRÓNICAS", "C", 2, "", "B", $lineh, $fontSize);


        $this->lastPage();
    }
    
    protected function _fontNormal($width, $text, $align = "L", $multi = 1, $border = "TBRL", $valign = "B", $lineh = 12, $fontSize = 8) {
        $this->SetFont($this->_font, "C", $fontSize);
        $this->MultiCell($width, $lineh * $multi, $text, $border, $align, 1, 0, "", "", true, 0, true, true, $lineh, $valign);
    }
}
