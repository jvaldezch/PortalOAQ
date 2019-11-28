<?php

require 'tcpdf.php';

class DetalleCoveVU extends TCPDF {
    
    protected $_fontSizeDocTitle = 15;
    protected $_fontSizeTitle = 10;
    protected $_fontSize = 9;
    protected $_font = 'helvetica';
    protected $_fontBold = 'helveticaB';
            
    function __construct($data, $orientation, $unit, $format) {
        parent::__construct($orientation, $unit, $format, true, 'UTF-8', false);
        $this->invoiceData = $data;
        $this->SetFont('helvetica', 'C', 10);
        $this->SetMargins(26, 98, 26, true);
        $this->SetAutoPageBreak(true, 15);
        $this->SetCreator(PDF_CREATOR);
        $this->SetAuthor("Jaime E. Valdez");
        $this->SetTitle('DETALLE_' . $this->invoiceData["cove"]);
        $this->SetSubject("");
        $this->SetKeywords("");
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);
        global $l;
        $this->setLanguageArray($l);
    }

    public function Header() {
        $this->Image(K_PATH_IMAGES . 'vu_acuse.jpg', 26, 15, 550, 78, 'JPG', false, '', true, 150, '', false, false, 0, false, false, false);
    }

    public function Footer() {
        $this->SetLineStyle(array('width' => 20, 'color' => array(185,225,224)));
        $this->Line(26, $this->getPageHeight() - 55, $this->getPageWidth() - 26, $this->getPageHeight() - 55);
        
        $this->SetLineStyle(array('width' => 20, 'color' => array(53,128,193)));
        $this->Line(26, $this->getPageHeight() - 35, $this->getPageWidth() - 26, $this->getPageHeight() - 35);
        $this->SetFont('helvetica', '', 7);
        $this->SetY(-33, true);
        $tbl = "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
            <tr>
                <td></td>
                <td style=\"text-align:center; color: #fff;\">" . $this->getAliasNumPage() . '/' . $this->getAliasNbPages() . "</td>
                <td style=\"text-align:right;\"></td>
            </tr>
        </table>";
        $this->writeHTML($tbl, true, false, false, false, '');
    }

    public function Create() {
        $this->AddPage();
        $vucem = new OAQ_VucemEnh();
        $array = $vucem->xmlStrToArray($this->invoiceData["xml"]);
        if (!isset($array["Body"]["solicitarRecibirCoveServicio"]["comprobantes"])) {
            throw new Exception("No data found.");
        }
        $data = $array["Body"]["solicitarRecibirCoveServicio"]["comprobantes"];
        $txtHighlight = array(0, 51, 153);
        $tblTitle = array(220, 220, 220);
        $tbl = array(255, 255, 255);
        $this->SetFont($this->_fontBold, '', $this->_fontSizeDocTitle);
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor($txtHighlight[0], $txtHighlight[1], $txtHighlight[2]);
        $this->MultiCell(0, 0, "Información de Valor y de Comercialización", 0, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln(25);
        $this->SetFont($this->_fontBold, '', $this->_fontSizeTitle);
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor($txtHighlight[0], $txtHighlight[1], $txtHighlight[2]);
        $this->MultiCell(180, 0, "Datos del Acuse de Valor", 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, '', $this->_fontSizeTitle);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(120, 0, $this->invoiceData["cove"], 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
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
        $this->MultiCell(($this->getPageWidth() - 52) / 3, 0, $data["numeroFacturaOriginal"], "BR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFont($this->_fontBold, '', $this->_fontSize);
        $this->SetFillColor($tblTitle[0], $tblTitle[1], $tblTitle[2]);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(($this->getPageWidth() - 52) / 2, 0, "Tipo de figura", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(($this->getPageWidth() - 52) / 2, 0, "Fecha Exp.", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->SetFillColor(255, 255, 255);
        $this->MultiCell(($this->getPageWidth() - 52) / 2, 0, $this->_tipoFigura($data["tipoFigura"]), "BL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(($this->getPageWidth() - 52) / 2, 0, date('d/m/Y', strtotime($data["fechaExpedicion"])), "BLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFont($this->_fontBold, '', $this->_fontSize);
        $this->SetFillColor($tblTitle[0], $tblTitle[1], $tblTitle[2]);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(($this->getPageWidth() - 52), 0, "Observaciones", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->SetFillColor(255, 255, 255);
        $this->MultiCell(($this->getPageWidth() - 52), 0, isset($data['observaciones']) ? $data['observaciones'] : '', "BLR", "L", 1, 0, "", "", true, 0, true, true, 0);
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
            foreach ($data["rfcConsulta"] as $rfc) {
                $this->Ln();
                $this->MultiCell(150, 0, $rfc, "BL", "L", 1, 0, "", "", true, 0, true, true, 0);
                $this->MultiCell(($this->getPageWidth() - 52) - 150, 0, $this->_rfcConsulta($rfc, $data), "BLR", "L", 1, 0, "", "", true, 0, true, true, 0);
            }
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
        $this->MultiCell(($this->getPageWidth() - 52), 0, $data["patenteAduanal"], "BTLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
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
        $this->_generales($data["emisor"], "proveedor");
        $this->Ln(1);
        $this->_generales($data["destinatario"], "destinatario");
        $this->Ln(1);
        if(($this->getPageHeight() - $this->GetY()) < 150) {
            $this->AddPage();
        }
        $this->Ln(1);
        $this->Ln();
        $this->SetFont($this->_fontBold, '', $this->_fontSizeTitle);
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor($txtHighlight[0], $txtHighlight[1], $txtHighlight[2]);
        $this->MultiCell(0, 0, "Datos de la mercancía", 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        if (isset($data["mercancias"]["descripcionGenerica"])) {
            $this->_mercancia($data["mercancias"]);
        } elseif(isset($data["mercancias"][0])) {
            foreach ($data["mercancias"] as $item) {
                if(($this->getPageHeight() - $this->GetY()) < 150) {
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
        $txtHighlight = array(0, 51, 153);
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
        $linecount = $this->getNumLines($data["nombre"], ($this->getPageWidth() - 52) / 3);
        $this->MultiCell(($this->getPageWidth() - 52) / 3, 13 * $linecount, $data["nombre"], "BL", 'L', 1, 0, '', '', true, 0, false, true, 0);
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
        if(isset($data["domicilio"]["calle"])) {
            $linecount = $this->getNumLines($data["domicilio"]["calle"], 255);
        } else {
            $linecount = 1;            
        }
        $this->MultiCell(260, 13 * $linecount, $data["domicilio"]["calle"], "BL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(100, 13 * $linecount, (isset($data["domicilio"]["numeroExterior"]) && !is_array($data["domicilio"]["numeroExterior"])) ? $data["domicilio"]["numeroExterior"] : '', "BLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(100, 13 * $linecount, (isset($data["domicilio"]["numeroInterior"]) && !is_array($data["domicilio"]["numeroInterior"])) ? $data["domicilio"]["numeroInterior"] : '', "BR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(100, 13 * $linecount, (isset($data["domicilio"]["codigoPostal"]) && !is_array($data["domicilio"]["codigoPostal"])) ? $data["domicilio"]["codigoPostal"] : '', "BR", 'L', 1, 0, '', '', true, 0, false, true, 0);
//
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
            $linecount = $this->getNumLines($data["domicilio"]["colonia"], 255);
        } else {
            $linecount = 1;            
        }
        $this->MultiCell(260, 13 * $linecount, (isset($data["domicilio"]["colonia"]) && !is_array($data["domicilio"]["colonia"])) ? $data["domicilio"]["colonia"] : '', "BL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(300, 13 * $linecount, (isset($data["domicilio"]["localidad"]) && !is_array($data["domicilio"]["localidad"])) ? $data["domicilio"]["localidad"]: '', "BLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFont($this->_fontBold, '', $this->_fontSize);
        $this->SetFillColor($tblTitle[0], $tblTitle[1], $tblTitle[2]);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(260, 0, "Entidad federativa", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(300, 0, "Municipio", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->SetFillColor(260, 255, 255);
        if(isset($data["domicilio"]["entidadFederativa"]) && !is_array($data["domicilio"]["entidadFederativa"])) {
            $linecount = $this->getNumLines($data["domicilio"]["entidadFederativa"], 255);
        } else {
            $linecount = 1;            
        }
        $this->MultiCell(260, 13 * $linecount, (isset($data["domicilio"]["entidadFederativa"]) && !is_array($data["domicilio"]["entidadFederativa"])) ? $data["domicilio"]["entidadFederativa"]: '', "BL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(300, 13 * $linecount, (isset($data["domicilio"]["municipio"]) && !is_array($data["domicilio"]["municipio"])) ? $data["domicilio"]["municipio"]: '', "BLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $mppr = new Vucem_Model_VucemPaisesMapper();
        $this->SetFont($this->_fontBold, '', $this->_fontSize);
        $this->SetFillColor($tblTitle[0], $tblTitle[1], $tblTitle[2]);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(0, 0, "País", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->SetFillColor(260, 255, 255);
        $this->MultiCell(0, 0, isset($data["domicilio"]["pais"]) ? $mppr->getName($data["domicilio"]["pais"]) : '', "BLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
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
        $linecount = $this->getNumLines($data["descripcionGenerica"], 300);
        $this->MultiCell(340, 13 * $linecount, utf8_decode($data["descripcionGenerica"]), "BL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(110, 13 * $linecount, $oma->getMeasurementUnitEnglish($data["claveUnidadMedida"]), "BLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(110, 13 * $linecount, number_format($data["cantidad"], 4, '.', ','), "BLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
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
        $this->MultiCell(75, 0, isset($data["tipoMoneda"]) ? $data["tipoMoneda"] : '', "BL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(175, 0, $this->_obtenerParte($data), "BL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(90, 0, '$ ' . number_format($data["valorUnitario"], 6, '.', ','), "BL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(110, 0, '$ ' . number_format($data["valorTotal"], 6, '.', ','), "BLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(110, 0, '$ ' . number_format($data["valorDolares"], 4, '.', ','), "BLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        if(isset($data["descripcionesEspecificas"])) {
            $arr = $data["descripcionesEspecificas"];
            $max = array(
                0 => isset($arr["marca"]) ? $this->getNumLines($arr["marca"], 150) : 1,
                1 => isset($arr["modelo"]) ? $this->getNumLines($arr["modelo"], 150) : 1,
                2 => isset($arr["subModelo"]) ? $this->getNumLines($arr["subModelo"], 150) : 1,
                3 => isset($arr["numeroSerie"]) ? $this->getNumLines($arr["numeroSerie"], 150) : 1,
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
            $this->MultiCell(150, 13 * max($max), isset($arr["marca"]) ? $arr["marca"] : "", "BL", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(150, 13 * max($max), isset($arr["modelo"]) ? $arr["modelo"] : "", "BL", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(130, 13 * max($max), isset($arr["subModelo"]) ? $arr["subModelo"] : "", "BLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(130, 13 * max($max), isset($arr["numeroSerie"]) ? $arr["numeroSerie"] : "", "BLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
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
            $this->MultiCell(150, 13 * max($max), isset($data["marca"]) ? $data["marca"] : "", "BL", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(150, 13 * max($max), isset($data["modelo"]) ? $data["modelo"] : "", "BL", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(130, 13 * max($max), isset($data["subModelo"]) ? $data["subModelo"] : "", "BLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(130, 13 * max($max), isset($data["numeroSerie"]) ? $data["numeroSerie"] : "", "BLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        }
    }

    protected function _obtenerParte($data) {
        if (isset($data["numParte"])) {
            return $data["numParte"];
        } else if (isset($data["numparte"])) {
            return $data["numparte"];
        } else {
            return '';
        }
    }

    protected function _tipoFigura($tipoFigura) {
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

}
