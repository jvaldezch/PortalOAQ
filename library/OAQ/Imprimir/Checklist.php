<?php

require "tcpdf/tcpdf.php";

class OAQ_Imprimir_Checklist extends TCPDF {

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
//        $this->SetTitle($this->_data["prefijoDocumento"] . $this->_data["referencia"]);
        $this->SetSubject("");
        $this->SetKeywords("");
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);
    }

    public function Header() {
        $this->SetY($this->_marginTop, true);
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->SetLineStyle(array("width" => 1.0, "cap" => "butt", "join" => " 'miter'", "dash" => 0, "color" => array(54, 125, 162)));
        $this->MultiCell(100, 50, '', 0, 'C', 1, 0, '', '', true, 0, false, true, 0, 'M');
        $this->MultiCell(434, 50, "CHECKLIST DE INTEGRACIÓN DE EXPEDIENTE", 0, 'C', 1, 0, '', '', true, 0, false, true, 50, 'M');
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

    public function checklist() {
        $this->AddPage();
        $this->SetY(65, true);
        
        $this->SetLineStyle(array("width" => 0.5, "cap" => "butt", "join" => " 'miter'", "dash" => 0, "color" => array(70, 70, 70)));
        $this->SetFont($this->_font, "B", 12);
        $this->SetFillColor(230, 225, 230);
        $this->MultiCell(534, 3, '', "TLR", 'C', 1, 0, '', '', true, 0, false, true, 3, 'M');
        $this->SetFont($this->_font, "B", $this->_fontSize);

        $this->Ln();
        $this->MultiCell(70, 10, "REFERENCIA:", "LB", "L", 1, 0, "", "", true, 0, true, true, 10);
        $this->SetFont($this->_font, "C", $this->_fontSize);
        $this->MultiCell(344, 10, $this->_data["referencia"], "B", "L", 1, 0, "", "", true, 0, true, true, 10);
        $this->SetFont($this->_font, "B", $this->_fontSize);
        $this->MultiCell(60, 10, "FECHA:", "B", "L", 1, 0, "", "", true, 0, true, true, 10);
        $this->SetFont($this->_font, "C", $this->_fontSize);
        $this->MultiCell(60, 10, date("Y-m-d"), "BR", "L", 1, 0, "", "", true, 0, true, true, 10);
        $this->Ln();
        $this->SetFont($this->_font, "B", $this->_fontSize);
        $this->MultiCell(70, 10, "PEDIMENTO:", "LB", "L", 1, 0, "", "", true, 0, true, true, 10);
        $this->SetFont($this->_font, "C", $this->_fontSize);
        $this->MultiCell(344, 10, $this->_data["pedimento"], "B", "L", 1, 0, "", "", true, 0, true, true, 10);
        $this->SetFont($this->_font, "B", $this->_fontSize);
        $this->MultiCell(60, 10, "", "B", "L", 1, 0, "", "", true, 0, true, true, 10);
        $this->SetFont($this->_font, "C", $this->_fontSize);
        $this->MultiCell(60, 10, "", "BR", "L", 1, 0, "", "", true, 0, true, true, 10);

        $this->Ln(15);
        $this->SetFont($this->_font, "B", $this->_fontSize);
        $this->MultiCell(414, 15, "OPERACIÓN", "TL", "C", 1, 0, "", "", true, 0, false, false, 15, 'M');
        $this->MultiCell(60, 15, "SI", "T", "C", 1, 0, "", "", true, 0, false, false, 15, 'M');
        $this->MultiCell(60, 15, "NO", "TR", "C", 1, 0, "", "", true, 0, false, false, 15, 'M');
        $arr = array(
            array('value' => 1, 'field' => 'Pedimento Completo', 'name' => 'pedimentos'),
            array('value' => 2, 'field' => 'Pedimento Simplicado', 'name' => 'pedimentosim'),
        );
        foreach ($arr as $key => $value) {
            $this->Ln();
            $this->MultiCell(20, 15, $value['value'], "L", "L", 0, 0, "", "", true, 0, false, false, 15, 'M');
            $this->MultiCell(394, 15, $value['field'], "", "L", 0, 0, "", "", true, 0, false, false, 15, 'M');
            if (isset($this->_data['checklist'][$value['name']]) && $this->_data['checklist'][$value['name']] == 1) {
                $this->MultiCell(60, 15, "X", "", "C", 0, 0, "", "", true, 0, true, true, 15);
                $this->MultiCell(60, 15, "", "R", "C", 0, 0, "", "", true, 0, true, true, 15);
            } else {
                $this->MultiCell(60, 15, "", "", "C", 0, 0, "", "", true, 0, true, true, 15);
                $this->MultiCell(60, 15, "", "R", "C", 0, 0, "", "", true, 0, true, true, 15);
            }
        }
        $this->Ln(5);
        $this->MultiCell(534, 1, "", "BLR", "L", 0, 0, "", "", true, 0, true, true, 1);

        $this->Ln(15);
        $this->SetFont($this->_font, "B", $this->_fontSize);
        $this->MultiCell(414, 15, "DOCUMENTACIÓN", "TL", "C", 1, 0, "", "", true, 0, false, false, 15, 'M');
        $this->MultiCell(60, 15, "SI", "T", "C", 1, 0, "", "", true, 0, false, false, 15, 'M');
        $this->MultiCell(60, 15, "NO", "TR", "C", 1, 0, "", "", true, 0, false, false, 15, 'M');
        $arr = array(
            array('value' => 3, 'field' => 'COVES detallado y xml', 'name' => 'coves'),
            array('value' => 4, 'field' => 'Bill of lading / Guía aérea / Lista de empaque y acuse de VUCEM', 'name' => 'bill'),
            array('value' => 5, 'field' => 'Carta 3.1.7 y acuse de VUCEM', 'name' => 'cartas'),
            array('value' => 6, 'field' => 'Certificado de origen y acuse de VUCE', 'name' => 'certi'),
            array('value' => 7, 'field' => 'NOM, RRNAs, Permisos, etc. y acuse de VUCEM', 'name' => 'cartase'),
            array('value' => 8, 'field' => 'Facturas comerciales', 'name' => 'factura'),
            array('value' => 9, 'field' => 'Carta de instrucciones', 'name' => 'cartai'),
            array('value' => 10, 'field' => 'Manifestaciones de valor y hojas de calculo previamente revisadas', 'name' => 'manif'),
            array('value' => 11, 'field' => 'Otros documentos', 'name' => 'otros')
        );
        foreach ($arr as $key => $value) {
            $this->Ln();
            $this->MultiCell(20, 15, $value['value'], "L", "L", 0, 0, "", "", true, 0, false, false, 15, 'M');
            $this->MultiCell(394, 15, $value['field'], "", "L", 0, 0, "", "", true, 0, false, false, 15, 'M');
            if (isset($this->_data['checklist'][$value['name']]) && $this->_data['checklist'][$value['name']] == 1) {
                $this->MultiCell(60, 15, "X", "", "C", 0, 0, "", "", true, 0, true, true, 15);
                $this->MultiCell(60, 15, "", "R", "C", 0, 0, "", "", true, 0, true, true, 15);
            } else {
                $this->MultiCell(60, 15, "", "", "C", 0, 0, "", "", true, 0, true, true, 15);
                $this->MultiCell(60, 15, "X", "R", "C", 0, 0, "", "", true, 0, true, true, 15);
            }
        }
        $this->Ln(5);
        $this->MultiCell(534, 1, "", "BLR", "L", 0, 0, "", "", true, 0, true, true, 1);

        $this->Ln(15);
        $this->SetFont($this->_font, "B", $this->_fontSize);
        $this->MultiCell(414, 15, "ADMINISTRACIÓN", "TL", "C", 1, 0, "", "", true, 0, false, false, 15, 'M');
        $this->MultiCell(60, 15, "SI", "T", "C", 1, 0, "", "", true, 0, false, false, 15, 'M');
        $this->MultiCell(60, 15, "NO", "TR", "C", 1, 0, "", "", true, 0, false, false, 15, 'M');
        $arr = array(
            array('value' => 12, 'field' => 'Autorizaciones (almacenajes, demoras, servicio extraordinario)', 'name' => 'serviex'),
            array('value' => 13, 'field' => 'Facturas de terceros (maniobras, transporte, almacenajes, demoras)', 'name' => 'mani'),
            array('value' => 14, 'field' => 'Cuenta de gastos americana', 'name' => 'gastosamericana'),
            array('value' => 15, 'field' => 'Cuenta de gastos corresponsal', 'name' => 'gastoscorresponsal'),
            array('value' => 16, 'field' => 'Cuenta de gastos OAQ: ____________', 'name' => 'gastos')
        );
        foreach ($arr as $key => $value) {
            $this->Ln();
            $this->MultiCell(20, 15, $value['value'], "L", "L", 0, 0, "", "", true, 0, false, false, 15, 'M');
            $this->MultiCell(394, 15, $value['field'], "", "L", 0, 0, "", "", true, 0, false, false, 15, 'M');
            if (isset($this->_data['checklist'][$value['name']]) && $this->_data['checklist'][$value['name']] == 1) {
                $this->MultiCell(60, 15, "X", "", "C", 0, 0, "", "", true, 0, true, true, 15);
                $this->MultiCell(60, 15, "", "R", "C", 0, 0, "", "", true, 0, true, true, 15);
            } else {
                $this->MultiCell(60, 15, "", "", "C", 0, 0, "", "", true, 0, true, true, 15);
                $this->MultiCell(60, 15, "X", "R", "C", 0, 0, "", "", true, 0, true, true, 15);
            }
        }
        $this->Ln(5);
        $this->MultiCell(534, 1, "", "BLR", "L", 0, 0, "", "", true, 0, true, true, 1);

        $this->Ln(15);
        $this->SetFont($this->_font, "B", $this->_fontSize);
        $this->MultiCell(534, 15, "OBSERVACIONES", "TLR", "C", 1, 0, "", "", true, 0, false, false, 15, 'M');
        $this->Ln();
        $this->MultiCell(534, 1, $this->_data['observaciones'], "LR", "L", 0, 0, "", "", true, 0, true, true, 1);
        $this->Ln(5);
        $this->MultiCell(534, 1, "", "BLR", "L", 0, 0, "", "", true, 0, true, true, 1);

        $this->Ln(10);
        $this->SetFont($this->_font, "B", $this->_fontSize);
        $this->MultiCell(414, 15, "", "TL", "C", 1, 0, "", "", true, 0, false, false, 15, 'M');
        $this->MultiCell(60, 15, "SI", "T", "C", 1, 0, "", "", true, 0, false, false, 15, 'M');
        $this->MultiCell(60, 15, "NO", "TR", "C", 1, 0, "", "", true, 0, false, false, 15, 'M');

        $this->Ln();
        $this->MultiCell(20, 15, "", "L", "L", 0, 0, "", "", true, 0, false, false, 15, 'M');
        $this->MultiCell(394, 15, "Revisión operaciones:", "", "R", 0, 0, "", "", true, 0, false, false, 15, 'M');
        if (isset($this->_data['revisionOperaciones']) && $this->_data['revisionOperaciones'] == 1) {
            $this->MultiCell(60, 15, "X", "", "C", 0, 0, "", "", true, 0, true, true, 15);
            $this->MultiCell(60, 15, "", "R", "C", 0, 0, "", "", true, 0, true, true, 15);
        } else {
            $this->MultiCell(60, 15, "", "", "C", 0, 0, "", "", true, 0, true, true, 15);
            $this->MultiCell(60, 15, "", "R", "C", 0, 0, "", "", true, 0, true, true, 15);
        }

        $this->Ln();
        $this->MultiCell(20, 15, "", "L", "L", 0, 0, "", "", true, 0, false, false, 15, 'M');
        $this->MultiCell(394, 15, "Revisión administración:", "", "R", 0, 0, "", "", true, 0, false, false, 15, 'M');
        if (isset($this->_data['revisionAdministracion']) && $this->_data['revisionAdministracion'] == 1) {
            $this->MultiCell(60, 15, "X", "", "C", 0, 0, "", "", true, 0, true, true, 15);
            $this->MultiCell(60, 15, "", "R", "C", 0, 0, "", "", true, 0, true, true, 15);
        } else {
            $this->MultiCell(60, 15, "", "", "C", 0, 0, "", "", true, 0, true, true, 15);
            $this->MultiCell(60, 15, "", "R", "C", 0, 0, "", "", true, 0, true, true, 15);
        }

        $this->Ln();
        $this->MultiCell(20, 15, "", "L", "L", 0, 0, "", "", true, 0, false, false, 15, 'M');
        $this->MultiCell(394, 15, "Expediente completo:", "", "R", 0, 0, "", "", true, 0, false, false, 15, 'M');
        if (isset($this->_data['completo']) && $this->_data['completo'] == 1) {
            $this->MultiCell(60, 15, "X", "", "C", 0, 0, "", "", true, 0, true, true, 15);
            $this->MultiCell(60, 15, "", "R", "C", 0, 0, "", "", true, 0, true, true, 15);
        } else {
            $this->MultiCell(60, 15, "", "", "C", 0, 0, "", "", true, 0, true, true, 15);
            $this->MultiCell(60, 15, "", "R", "C", 0, 0, "", "", true, 0, true, true, 15);
        }

        $this->Ln(5);
        $this->MultiCell(534, 1, "", "BLR", "L", 0, 0, "", "", true, 0, true, true, 1);

        $this->Ln(15);
        $this->SetFont($this->_font, "B", $this->_fontSize);
        $this->MultiCell(534, 15, "BITÁCORA", "TLR", "C", 1, 0, "", "", true, 0, false, false, 15, 'M');

        $this->Ln();
        $this->MultiCell(334, 15, "COMENTARIO", "L", "C", 1, 0, "", "", true, 0, false, false, 15, 'M');
        $this->MultiCell(100, 15, "USUARIO", "", "C", 1, 0, "", "", true, 0, false, false, 15, 'M');
        $this->MultiCell(100, 15, "FECHA", "R", "C", 1, 0, "", "", true, 0, false, false, 15, 'M');

        $this->SetFont($this->_font, "C", $this->_fontSize);
        if (isset($this->_data['bitacora'])) {
            foreach ($this->_data['bitacora'] as $item) {
                $this->Ln();
                $this->MultiCell(334, 15, $item["bitacora"], "L", "L", 0, 0, "", "", true, 0, false, false, 15, 'M');
                $this->MultiCell(100, 15, strtoupper($item["usuario"]), "", "C", 0, 0, "", "", true, 0, false, false, 15, 'M');
                $this->MultiCell(100, 15, $item["creado"], "R", "C", 0, 0, "", "", true, 0, false, false, 15, 'M');
            }
        } else {
            $this->Ln();
            $this->MultiCell(334, 15, "", "L", "C", 0, 0, "", "", true, 0, false, false, 15, 'M');
            $this->MultiCell(100, 15, "", "", "C", 0, 0, "", "", true, 0, false, false, 15, 'M');
            $this->MultiCell(100, 15, "", "R", "C", 0, 0, "", "", true, 0, false, false, 15, 'M');
        }

        $this->Ln(5);
        $this->MultiCell(534, 1, "", "BLR", "L", 0, 0, "", "", true, 0, true, true, 1);
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
