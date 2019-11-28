<?php

require "tcpdf.php";

class Proforma extends TCPDF {

    protected $_data;
    protected $_ts = 15;
    protected $_ps = 7;
    protected $_c = array(0, 51, 53);
    protected $_b = array(
        "T" => array("width" => 0.3, "color" => array(50, 50, 120), "dash" => 0, "cap" => "square"),
        "B" => array("width" => 0.3, "color" => array(50, 50, 120), "dash" => 0, "cap" => "square"),
        "T" => array("width" => 0.3, "color" => array(0, 51, 53), "dash" => 0, "cap" => "square"),
        "B" => array("width" => 0.3, "color" => array(0, 51, 53), "dash" => 0, "cap" => "square"),
    );
    protected $_tc = array(0, 51, 53);
    protected $_hc = array(220, 220, 250);
    protected $_hv = array(250, 250, 250);
    protected $_f = "helvetica";
    protected $_fb = "helveticaB";

    function __construct($data, $orientation, $unit, $format) {
        parent::__construct($orientation, $unit, $format, true, "UTF-8", false);
        $this->_data = $data;
        $this->SetFont("helvetica", "C", 9);
        $this->SetMargins(26, 26, 26, true);
        $this->SetAutoPageBreak(true, 15);
        $this->SetCreator(PDF_CREATOR);
        $this->SetAuthor("Jaime E. Valdez");
        $this->SetTitle("FACTURA_" . $this->_data["Patente"] . "_" . $this->_data["Aduana"] . "_" . $this->_data["Pedimento"] . "_" . $this->_data["NumFactura"]);
        $this->SetSubject("");
        $this->SetKeywords("");
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);
        global $l;
        $this->setLanguageArray($l);
    }

    public function Header() {
        
    }

    public function Footer() {
        $this->SetY(-90, true);
        $this->SetFont($this->_f, "", $this->_ps);
        $this->SetTextColor($this->_tc[0], $this->_tc[1], $this->_tc[2]);
        $this->SetLineStyle(array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(70, 70, 70)));
        $this->SetFillColor(255, 255, 255);
        $this->MultiCell(340, 0, "De conformidad con la regla 3.1.8 general de comercio exterior vigente declaramos bajo protesta de decir\nla verdad que las presentes relación amparan las mercancias consignadas en ellas.", "T", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(220, 0, "LUIS ESTEBAN MARRON LIMON", "T", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln(26);
        $this->SetFont($this->_fb, "", $this->_ps);
        $this->MultiCell(340, 0, "Proforma factura para fines aduanales únicamente", "", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_f, "", $this->_ps);
        $this->MultiCell(220, 0, "RFC: MALL640523749     PATENTE:3589", "", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln(25);
        $this->MultiCell(340, 0, "JOSÉ ALEJANDRO MARTÍNEZ MAYORGA", "", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(180, 0, "AGENTE ADUANAL", "T", 'L', 1, 0, '', '', true, 0, false, true, 0);
    }

    public function Create() {
        $this->AddPage();

        $this->SetFont($this->_fb, "", $this->_ts);
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor($this->_tc[0], $this->_tc[1], $this->_tc[2]);
        $this->MultiCell(0, 0, "FACTURA " . $this->_data["NumFactura"], 0, "C", 1, 0, "", "", true, 0, false, true, 0);

        $this->Ln(30);
        $this->_fieldValue("", 100);
        $this->_fieldValue("", 180);
        $this->_fieldName("Fecha:", 100);
        $this->_fieldValue(date("d/m/Y", strtotime($this->_data["FechaFactura"])), 180);

        $this->Ln(25);
        $this->_fieldName("Exportador:", 50);
        $this->_fieldValue($this->_data["ProNombre"], 230);
        $this->_fieldName("Importador:", 50);
        $this->_fieldValue($this->_data["CteNombre"], 230);

        $this->Ln();
        $this->_fieldName("RFC/TAXID:", 50);
        $this->_fieldValue($this->_data["ProTaxID"], 230);
        $this->_fieldName("RFC/TAXID:", 50);
        $this->_fieldValue($this->_data["CteRfc"], 230);

        $this->Ln();
        $this->_fieldName("Direccion:", 50);
        $this->_fieldValue($this->_data["ProCalle"] . " " . $this->_data["ProNumExt"] . " " . $this->_data["ProNumInt"], 230);
        $this->_fieldName("Direccion:", 50);
        $this->_fieldValue($this->_data["CteCalle"] . " " . $this->_data["CteNumExt"] . " " . $this->_data["CteNumInt"], 230);

        $this->Ln();
        $this->_fieldValue("", 50);
        $this->_fieldValue($this->_data["ProColonia"], 230);
        $this->_fieldValue("", 50);
        $this->_fieldValue($this->_data["CteColonia"], 230);
        
        $this->Ln();
        $this->_fieldName("C.P.:", 50);
        $this->_fieldValue($this->_data["ProCP"], 230);
        $this->_fieldName("C.P.:", 50);
        $this->_fieldValue($this->_data["CteCP"], 230);
        
        $this->Ln();
        $this->_fieldName("Localidad:", 50);
        $this->_fieldValue($this->_data["ProLocalidad"], 230);
        $this->_fieldName("Localidad:", 50);
        $this->_fieldValue($this->_data["CteLocalidad"], 230);

        $this->Ln();
        $this->_fieldName("Ciudad:", 50);
        $this->_fieldValue($this->_data["ProMun"], 230);
        $this->_fieldName("Ciudad:", 50);
        $this->_fieldValue($this->_data["CteMun"], 230);

        $this->Ln();
        $this->_fieldName("Estado:", 50);
        $this->_fieldValue($this->_data["ProEdo"], 230);
        $this->_fieldName("Estado:", 50);
        $this->_fieldValue($this->_data["CteEdo"], 230);
        
        $this->Ln();
        $this->_fieldName("País:", 50);
        $this->_fieldValue($this->_data["ProPais"], 230);
        $this->_fieldName("País:", 50);
        $this->_fieldValue($this->_data["CtePais"], 230);
        
        $this->Ln();
        $this->_fieldName("Observaciones:", 100, null, 3);
        $this->_fieldValue($this->_data["Observaciones"], 460, null, 3);

        $this->Ln(35);
        $this->_fieldName("Cantidad", 75, "C");
        $this->_fieldName("Descripcion", 285, "C");
        $this->_fieldName("Unidades", 50, "C");
        $this->_fieldName("Unitario", 75, "C");
        $this->_fieldName("Subtotal", 75, "C");
        
        $uniTot = 0;
        $valTot = 0;
        
        if(isset($this->_data["PRODUCTOS"])) {
            foreach($this->_data["PRODUCTOS"] as $item) {
                $this->Ln();
                $rows = $this->getNumLines($item["DESC_COVE"], 285);
                $this->_fieldValue(number_format($item["CANTFAC"], 0), 75, "R", $rows);
                $this->_fieldValue($item["DESC_COVE"], 285, $rows);
                $this->_fieldValue($item["UMC_OMA"], 50, "C", $rows);
                $this->_fieldValue($item["PREUNI"], 75, "C", $rows);
                $this->_fieldValue(number_format($item["VALCOM"], 2), 75, "R", $rows);
                $uniTot = $uniTot + $item["CANTFAC"];
                $valTot = $valTot + $item["VALCOM"];
            }
        } else {
            $this->Ln();
            $this->_fieldValue("", 75);
            $this->_fieldValue("", 285);
            $this->_fieldValue("", 50);
            $this->_fieldValue("", 75);
            $this->_fieldValue("", 75);
        }

        $this->Ln();
        $this->_fieldName("Total", 75, "C");
        $this->_fieldValue("", 285);
        $this->_fieldValue("", 50);
        $this->_fieldValue("", 75, "C");
        $this->_fieldName("Total", 75, "C");
        
        $this->Ln();
        $this->_fieldValue(number_format($uniTot,0), 75, "R");
        $this->_fieldValue("", 285);
        $this->_fieldValue("", 50);
        $this->_fieldValue("", 75, "C");
        $this->_fieldValue(number_format($valTot, 2), 75, "R");
        $this->lastPage();
    }

    protected function _fieldName($value, $size, $align = "L", $rows = 1) {
        $b = array(
            "T" => array("width" => 0.5, "color" => array(50, 50, 50), "dash" => 0, "cap" => "square"),
            "B" => array("width" => 0.5, "color" => array(50, 50, 50), "dash" => 0, "cap" => "square"),
        );
        $this->SetFont($this->_fb, "", $this->_ps);
        $this->SetFillColor($this->_hc[0], $this->_hc[1], $this->_hc[2]);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell($size, (9 * $rows), $value, $b, $align, 1, 0, "", "", true, 0, false, true, 0);
    }

    protected function _fieldValue($value, $size, $align = "L", $rows = 1) {
        $this->SetFont($this->_f, "", $this->_ps);
        $this->SetFillColor($this->_hv[0], $this->_hv[1], $this->_hv[2]);
        $this->SetTextColor($this->_c[0], $this->_c[1], $this->_c[2]);
        $this->MultiCell($size, (9 * $rows), $value, $this->_b, $align, 1, 0, "", "", true, 0, false, true, 0);
    }

    protected function _fieldValueBold($value, $size, $align = "L", $rows = 1) {
        $this->SetFont($this->_fb, "", $this->_ps);
        $this->SetFillColor($this->_hv[0], $this->_hv[1], $this->_hv[2]);
        $this->SetTextColor($this->_c[0], $this->_c[1], $this->_c[2]);
        $this->MultiCell($size, (9 * $rows), $value, $this->_b, $align, 1, 0, "", "", true, 0, false, true, 0);
    }

}
