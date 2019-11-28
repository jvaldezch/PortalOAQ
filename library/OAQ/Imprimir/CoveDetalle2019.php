<?php

require_once "tcpdf/tcpdf.php";

class OAQ_Imprimir_CoveDetalle2019 extends TCPDF {

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
        $this->Image(K_PATH_IMAGES . 'vu_acuse_2019b.jpg', 26, 690, 560, 78, 'JPG', false, '', true, 150, '', false, false, 0, false, false, false);
    }

    public function Create() {
        $this->AddPage();
        $vucem = new OAQ_VucemEnh();
        $array = $vucem->xmlStrToArray($this->_data["xml"]);
        if (!isset($array["Body"]["solicitarRecibirCoveServicio"]["comprobantes"])) {
            throw new Exception("No data found.");
        }
        $data = $array["Body"]["solicitarRecibirCoveServicio"]["comprobantes"];
        $txtHighlight = array(57, 60, 62);
        $tblTitle = array(220, 220, 220);
        $tbl = array(255, 255, 255);
        
        $this->Ln(5);
        $this->SetFont($this->_fontBold, '', $this->_fontSizeTitle);
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor($txtHighlight[0], $txtHighlight[1], $txtHighlight[2]);
        $this->MultiCell(130, 0, "Datos del Acuse de Valor", 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, '', $this->_fontSizeTitle);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(120, 0, $this->_data["cove"], 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFont($this->_fontBold, '', $this->_fontSize);
        $this->SetFillColor($tblTitle[0], $tblTitle[1], $tblTitle[2]);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(($this->getPageWidth() - 52) / 3, 0, "Tipo de Operación", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(($this->getPageWidth() - 52) / 3, 0, "Relación de facturas", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(($this->getPageWidth() - 52) / 3, 0, "No. Factura", "TR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->SetFillColor(255, 255, 255);
        $this->MultiCell(($this->getPageWidth() - 52) / 3, 0, ($data["tipoOperacion"] == "TOCE.IMP") ? "Importación" : "Exportación", "BL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(($this->getPageWidth() - 52) / 3, 0, "Sin relación de facturas", "BLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(($this->getPageWidth() - 52) / 3, 0, $this->_vdata($data, "numeroFacturaOriginal"), "BR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFont($this->_fontBold, '', $this->_fontSize);
        $this->SetFillColor($tblTitle[0], $tblTitle[1], $tblTitle[2]);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(($this->getPageWidth() - 52) / 2, 0, "Tipo de figura", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(($this->getPageWidth() - 52) / 2, 0, "Fecha Exp.", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->SetFillColor(255, 255, 255);
        $this->MultiCell(($this->getPageWidth() - 52) / 2, 0, $this->_tipoFigura($data["tipoFigura"], $data["tipoOperacion"]), "BL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(($this->getPageWidth() - 52) / 2, 0, date('d/m/Y', strtotime($data["fechaExpedicion"])), "BLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFont($this->_fontBold, '', $this->_fontSize);
        $this->SetFillColor($tblTitle[0], $tblTitle[1], $tblTitle[2]);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(($this->getPageWidth() - 52), 0, "Observaciones", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->SetFillColor(255, 255, 255);
        $this->MultiCell(($this->getPageWidth() - 52), 0, $this->_vdata($data, "observaciones"), "BLR", "L", 1, 0, "", "", true, 0, true, true, 0);
        $this->Ln(1);
        $this->Ln();
        $this->SetFont($this->_fontBold, '', $this->_fontSizeTitle);
        $this->SetFillColor($tbl[0], $tbl[1], $tbl[2]);
        $this->SetTextColor($txtHighlight[0], $txtHighlight[1], $txtHighlight[2]);
        $this->MultiCell(0, 0, "RFC con permisos de consulta", 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFont($this->_fontBold, '', $this->_fontSize);
        $this->SetFillColor($tblTitle[0], $tblTitle[1], $tblTitle[2]);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(150, 0, "RFC de consulta", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(($this->getPageWidth() - 52) - 150, 0, "Nombre o Razón social", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->SetFillColor($tbl[0], $tbl[1], $tbl[2]);
        if (isset($data["rfcConsulta"]) && !empty($data["rfcConsulta"])) {
            if (is_array($data["rfcConsulta"])) {
                foreach ($data["rfcConsulta"] as $rfc) {
                    $this->Ln();
                    $this->MultiCell(150, 0, $rfc, "BL", "L", 1, 0, "", "", true, 0, true, true, 0);
                    $this->MultiCell(($this->getPageWidth() - 52) - 150, 0, $this->_rfcConsulta($rfc, $data), "BLR", "L", 1, 0, "", "", true, 0, true, true, 0);
                }
            } else {
                $this->Ln();
                $this->MultiCell(150, 0, $data["rfcConsulta"], "BL", "L", 1, 0, "", "", true, 0, true, true, 0);
                $this->MultiCell(($this->getPageWidth() - 52) - 150, 0, '', "BLR", "L", 1, 0, "", "", true, 0, true, true, 0);
            }
        } else {
            $this->Ln();
            $this->MultiCell(150, 0, '', "BL", "L", 1, 0, "", "", true, 0, true, true, 0);
            $this->MultiCell(($this->getPageWidth() - 52) - 150, 0, '', "BLR", "L", 1, 0, "", "", true, 0, true, true, 0);
        }
        $this->Ln(1);
        $this->Ln();
        $this->SetFont($this->_fontBold, '', $this->_fontSizeTitle);
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor($txtHighlight[0], $txtHighlight[1], $txtHighlight[2]);
        $this->MultiCell(0, 0, "Número de patente aduanal", 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFont($this->_fontBold, '', $this->_fontSize);
        $this->SetFillColor($tblTitle[0], $tblTitle[1], $tblTitle[2]);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(($this->getPageWidth() - 52), 0, "Número autorización aduanal", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->SetFillColor(255, 255, 255);
        $this->MultiCell(($this->getPageWidth() - 52), 0, $this->_vdata($data, "patenteAduanal"), "BTLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln(1);
        $this->Ln();
        $this->SetFont($this->_fontBold, '', $this->_fontSizeTitle);
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor($txtHighlight[0], $txtHighlight[1], $txtHighlight[2]);
        $this->MultiCell(0, 0, "Datos de la factura", 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln(1);
        $this->Ln();
        $this->SetFont($this->_fontBold, '', $this->_fontSize);
        $this->SetFillColor($tblTitle[0], $tblTitle[1], $tblTitle[2]);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(($this->getPageWidth() - 52) / 3, 0, "Subdivisión", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(($this->getPageWidth() - 52) / 3, 0, "Certificado de origen", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(($this->getPageWidth() - 52) / 3, 0, "No. de exportador autorizado", "TR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->SetFillColor(255, 255, 255);
        $this->MultiCell(($this->getPageWidth() - 52) / 3, 0, $this->_subDivision($data), "BL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(($this->getPageWidth() - 52) / 3, 0, $this->_certificadoDeOrigen($data), "BLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(($this->getPageWidth() - 52) / 3, 0, $this->_numeroExportador($data), "BR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln(1);
        $this->_generales($data["emisor"], "proveedor");
        $this->Ln(1);
        $this->_generales($data["destinatario"], "destinatario");
        $this->Ln(1);

        if(($this->getPageHeight() - $this->GetY()) < 200) {
            $this->AddPage();
        }
        $this->Ln(1);
        $this->Ln();
        $this->SetFont($this->_fontBold, '', $this->_fontSizeTitle);
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor($txtHighlight[0], $txtHighlight[1], $txtHighlight[2]);
        $this->MultiCell(0, 0, "Datos de la mercancía", 0, 'L', 1, 0, '', '', true, 0, false, true, 0);

        if (isset($data["mercancias"]["descripcionGenerica"])) {
            if(($this->getPageHeight() - $this->GetY()) < 80) {
                $this->AddPage();
            }
            $this->_mercancia($data["mercancias"]);
        } elseif(isset($data["mercancias"][0])) {
            foreach ($data["mercancias"] as $item) {
                if(($this->getPageHeight() - $this->GetY()) < 200) {
                    $this->AddPage();
                }
                $this->_mercancia($item);
            }
        }

        $this->lastPage();
    }
    
    protected function _rfcConsulta($rfc, $data) {
        if($data["emisor"]["identificacion"] == $rfc) {
            return $data["emisor"]["nombre"];
        } elseif($data["destinatario"]["identificacion"] == $rfc) {
            return $data["destinatario"]["nombre"];
        } else {
            $corres = new Application_Model_CustomsMapper();
            $nombre = $corres->getCompanyName($rfc);
            if($nombre != false) {
                return $nombre;
            }
            $model = new Vucem_Model_VucemFirmanteMapper();
            $agente = $model->nombreFirmante($rfc);
            if(isset($agente)) {
                return $agente["razon"];
            }
            return "";
        }
    }

    protected function _generales($data, $title) {
        $txtHighlight = array(57, 60, 62);
        $tblTitle = array(220, 220, 220);
        $tbl = array(255, 255, 255);
        $this->Ln();
        $this->SetFont($this->_fontBold, '', $this->_fontSizeTitle);
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor($txtHighlight[0], $txtHighlight[1], $txtHighlight[2]);
        $this->MultiCell(0, 0, "Datos generales del " . $title, 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFont($this->_fontBold, '', $this->_fontSize);
        $this->SetFillColor($tblTitle[0], $tblTitle[1], $tblTitle[2]);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(($this->getPageWidth() - 52) / 3, 0, "Tipo de identificador", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(($this->getPageWidth() - 52) - ($this->getPageWidth() - 52) / 3, 0, "Tax ID/Sin Tax ID/RFC/CURP", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->SetFillColor($tbl[0], $tbl[1], $tbl[2]);
        $this->Ln();
        $this->MultiCell(($this->getPageWidth() - 52) / 3, 0, $this->_identificadorDesc($data["tipoIdentificador"]), "BL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(($this->getPageWidth() - 52) - ($this->getPageWidth() - 52) / 3, 0, $data["identificacion"], "BLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFont($this->_fontBold, '', $this->_fontSize);
        $this->SetFillColor($tblTitle[0], $tblTitle[1], $tblTitle[2]);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(($this->getPageWidth() - 52) / 3, 0, "Nombre(s) o Razón Social", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(($this->getPageWidth() - 52) / 3, 0, "Apellido paterno", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(($this->getPageWidth() - 52) / 3, 0, "Apellido materno", "TR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->SetFillColor(255, 255, 255);
        $linecount = $this->getNumLines($this->_vdata($data, "nombre"), ($this->getPageWidth() - 52) / 3);
        $this->MultiCell(($this->getPageWidth() - 52) / 3, 13 * $linecount, $this->_vdata($data, "nombre"), "BL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(($this->getPageWidth() - 52) / 3, 13 * $linecount, "", "BLR", 'L', 1, 0, '', '', true, 1, false, true, 1);
        $this->MultiCell(($this->getPageWidth() - 52) / 3, 13 * $linecount, "", "BR", 'L', 1, 0, '', '', true, 1, false, true, 1);
        $this->Ln(1);

        $this->Ln();
        $this->SetFont($this->_fontBold, '', $this->_fontSizeTitle);
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor($txtHighlight[0], $txtHighlight[1], $txtHighlight[2]);
        $this->MultiCell(0, 0, "Domicilio del " . $title, 0, 'L', 1, 0, '', '', true, 0, false, true, 0);

        $this->Ln();
        $this->SetFont($this->_fontBold, '', $this->_fontSize);
        $this->SetFillColor($tblTitle[0], $tblTitle[1], $tblTitle[2]);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(260, 0, "Calle", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(100, 0, "No. exterior", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(100, 0, "No. interior", "TR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(100, 0, "Código postal", "TR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();

        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->SetFillColor(260, 255, 255);
        if (isset($data["domicilio"]["calle"])) {
            $linecount = $this->getNumLines($this->_vdata($data["domicilio"], "calle"), 255);
        } else {
            $linecount = 1;            
        }
        $this->MultiCell(260, 13 * $linecount, $this->_vdata($data["domicilio"], "calle"), "BL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(100, 13 * $linecount, $this->_vdata($data["domicilio"], "numeroExterior"), "BLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(100, 13 * $linecount, $this->_vdata($data["domicilio"], "numeroInterior"), "BR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(100, 13 * $linecount, $this->_vdata($data["domicilio"], "codigoPostal"), "BR", 'L', 1, 0, '', '', true, 0, false, true, 0);

        $this->Ln();
        $this->SetFont($this->_fontBold, '', $this->_fontSize);
        $this->SetFillColor($tblTitle[0], $tblTitle[1], $tblTitle[2]);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(260, 0, "Colonia", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(300, 0, "Localidad", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();

        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->SetFillColor(260, 255, 255);
        if(isset($data["domicilio"]["colonia"])) {
            $linecount = $this->getNumLines($this->_vdata($data["domicilio"], "colonia"), 255);
        } else {
            $linecount = 1;            
        }
        $this->MultiCell(260, 13 * $linecount, $this->_vdata($data["domicilio"], "colonia"), "BL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(300, 13 * $linecount, $this->_vdata($data["domicilio"], "localidad"), "BLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();

        $this->SetFont($this->_fontBold, '', $this->_fontSize);
        $this->SetFillColor($tblTitle[0], $tblTitle[1], $tblTitle[2]);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(260, 0, "Entidad federativa", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(300, 0, "Municipio", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->SetFillColor(260, 255, 255);
        if (isset($data["domicilio"]["entidadFederativa"]) && !is_array($data["domicilio"]["entidadFederativa"])) {
            $linecount = $this->getNumLines($this->_vdata($data["domicilio"], "entidadFederativa"), 255);
        } else {
            $linecount = 1;            
        }
        $this->MultiCell(260, 13 * $linecount, $this->_vdata($data["domicilio"], "entidadFederativa"), "BL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(300, 13 * $linecount, $this->_vdata($data["domicilio"], "municipio"), "BLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();

        $mppr = new Vucem_Model_VucemPaisesMapper();
        $this->SetFont($this->_fontBold, '', $this->_fontSize);
        $this->SetFillColor($tblTitle[0], $tblTitle[1], $tblTitle[2]);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(0, 0, "País", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->SetFillColor(260, 255, 255);
        $this->MultiCell(0, 0, $mppr->getName($this->_vdata($data["domicilio"], "pais")), "BLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
    }

    protected function _mercancia($data) {
        
        $tblTitle = array(220, 220, 220);
        $oma = new Vucem_Model_VucemUnidadesMapper();
        $this->Ln();
        $this->SetFont($this->_fontBold, '', $this->_fontSize);
        $this->SetFillColor($tblTitle[0], $tblTitle[1], $tblTitle[2]);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(340, 0, "Descripción genérica de la mercancía", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(110, 0, "Clave UMC", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(110, 0, "Cantidad UMC", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->SetFillColor(260, 255, 255);

        $linecount = $this->getNumLines($this->_vdata($data, "descripcionGenerica"), 300);
        $this->MultiCell(340, 13 * $linecount, $this->_vdata($data, "descripcionGenerica"), "BL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(110, 13 * $linecount, $oma->getMeasurementUnitEnglish($this->_vdata($data, "claveUnidadMedida")), "BLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(110, 13 * $linecount, number_format($this->_vdata($data, "cantidad"), 4, '.', ','), "BLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();

        $this->SetFont($this->_fontBold, '', $this->_fontSize);
        $this->SetFillColor($tblTitle[0], $tblTitle[1], $tblTitle[2]);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(75, 0, "Tipo moneda", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(175, 0, "Número de parte", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(90, 0, "Valor unitario", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(110, 0, "Valor total", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(110, 0, "Valor total en dólares", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();

        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->SetFillColor(260, 255, 255);
        $this->MultiCell(75, 0, $this->_vdata($data, "tipoMoneda"), "BL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(175, 0, $this->_obtenerParte($data), "BL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(90, 0, '$ ' . number_format($this->_vdata($data, "valorUnitario"), 6, '.', ','), "BL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(110, 0, '$ ' . number_format($this->_vdata($data, "valorTotal"), 6, '.', ','), "BLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(110, 0, '$ ' . number_format($this->_vdata($data, "valorDolares"), 4, '.', ','), "BLR", 'L', 1, 0, '', '', true, 0, false, true, 0);

        if(isset($data["descripcionesEspecificas"])) {
            $arr = $data["descripcionesEspecificas"];
            $max = array(
                0 => isset($arr["marca"]) ? $this->getNumLines($this->_vdata($arr, "marca"), 150) : 1,
                1 => isset($arr["modelo"]) ? $this->getNumLines($this->_vdata($arr, "modelo"), 150) : 1,
                2 => isset($arr["subModelo"]) ? $this->getNumLines($this->_vdata($arr, "subModelo"), 150) : 1,
                3 => isset($arr["numeroSerie"]) ? $this->getNumLines($this->_vdata($arr, "numeroSerie"), 150) : 1,
            );
            $this->Ln();
            $this->SetFont($this->_fontBold, '', $this->_fontSize);
            $this->SetFillColor($tblTitle[0], $tblTitle[1], $tblTitle[2]);
            $this->SetTextColor(20, 20, 20);
            $this->MultiCell(150, 0, "Marca", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(150, 0, "Modelo", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(130, 0, "Submodelo", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(130, 0, "Num. de Serie", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->Ln();
            $this->SetFont($this->_font, '', $this->_fontSize);
            $this->SetFillColor(260, 255, 255);
            $this->MultiCell(150, 13 * max($max), $this->_vdata($arr, "marca"), "BL", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(150, 13 * max($max), $this->_vdata($arr, "modelo"), "BL", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(130, 13 * max($max), $this->_vdata($arr, "subModelo"), "BLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(130, 13 * max($max), $this->_vdata($arr, "numeroSerie"), "BLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        }
        if(isset($data["marca"]) || isset($data["modelo"]) || isset($data["subModelo"]) || isset($data["numeroSerie"])) {
            $max = array(
                0 => isset($data["marca"]) ? $this->getNumLines($data["marca"], 150) : 1,
                1 => isset($data["modelo"]) ? $this->getNumLines($data["modelo"], 150) : 1,
                2 => isset($data["subModelo"]) ? $this->getNumLines($data["subModelo"], 150) : 1,
                3 => isset($data["numeroSerie"]) ? $this->getNumLines($data["numeroSerie"], 150) : 1,
            );
            $this->Ln();
            $this->SetFont($this->_fontBold, '', $this->_fontSize);
            $this->SetFillColor($tblTitle[0], $tblTitle[1], $tblTitle[2]);
            $this->SetTextColor(20, 20, 20);
            $this->MultiCell(150, 0, "Marca", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(150, 0, "Modelo", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(130, 0, "Submodelo", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(130, 0, "Num. de Serie", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->Ln();
            $this->SetFont($this->_font, '', $this->_fontSize);
            $this->SetFillColor(260, 255, 255);
            $this->MultiCell(150, 13 * max($max), $this->_vdata($data, "marca"), "BL", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(150, 13 * max($max), $this->_vdata($data, "modelo"), "BL", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(130, 13 * max($max), $this->_vdata($data, "subModelo"), "BLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(130, 13 * max($max), $this->_vdata($data, "numeroSerie"), "BLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        }
    }

    protected function _obtenerParte($data) {
        if (isset($data["numParte"])) {
            if (is_array($data["numParte"])) {
                return '';
            }
            return $data["numParte"];
        } else if (isset($data["numparte"])) {
            if (is_array($data["numparte"])) {
                return '';
            }
            return $data["numparte"];
        } else {
            return '';
        }
    }

    protected function _tipoFigura($tipoFigura, $tipoOperacion = null) {
        switch ((int) $tipoFigura) {
            case 1:
                return 'Agente Aduanal';
            case 2:
                return 'Apoderado Aduanal';
            case 3:
                return 'Mandatario';
            case 4:
                return 'Exportador';
            case 5:
                if (isset($tipoOperacion)) {
                    if ($tipoOperacion == "TOCE.EXP") {
                        return "Exportador";
                    }
                }
                return 'Importador';
        }
    }

    protected function _subDivision($data) {
        if (isset($data["factura"]["subdivision"])) {
            if ($data["factura"]["subdivision"] == '1') {
                return "Con subdivisión";
            } else {
                return "Sin subdivisión";
            }
        } else {
            return "";
        }
    }

    protected function _certificadoDeOrigen($data) {
        if (isset($data["factura"]["certificadoOrigen"])) {
            if ($data["factura"]["certificadoOrigen"] == '1') {
                return "Si funge como certificado de origen";
            } else {
                return "No funge como certificado de origen";
            }
        } else {
            return "";
        }
    }

    protected function _numeroExportador($data) {
        if (isset($data["factura"]["numeroExportadorAutorizado"])) {
            if ($data["factura"]["numeroExportadorAutorizado"] != '') {
                return $data["factura"]["numeroExportadorAutorizado"];
            }
        } else {
            return "";
        }
    }

    protected function _identificadorDesc($iden) {
        switch ((int) $iden) {
            case 0:
                return 'TAX_ID';
            case 1:
                return 'RFC';
            case 2:
                return 'CURP';
            case 3:
                return 'SIN_TAX_ID';
            default:
                return '';
        }
    }

    protected function _vdata(array $arr, $key) {
        if (isset($arr[$key])) {
            if (is_array($arr[$key])) {
                return '';
            }
            if (empty($arr[$key])) {
                return '';
            }
            if ($arr[$key] == '') {
                return '';
            }
            return $arr[$key];
        }
        return '';
    }

}
