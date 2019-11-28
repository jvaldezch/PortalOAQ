<?php

require 'tcpdf.php';

class PedimentoCompleto extends TCPDF {

    protected $_margins = 20;
    protected $_font = 'helvetica';
    protected $_fontB = 'helveticaB';
    protected $_fontSize = 7;
    protected $_fontSmall = 6.5;
    protected $_marginTop = 32;
    protected $_shade = array(210, 210, 210);
    protected $_shaden = array(255, 255, 255);
    protected $_second = false;

    function __construct($data, $orientation, $unit, $format) {
        parent::__construct($orientation, $unit, $format, true, 'UTF-8', false);

        $this->data = $data;
        $this->SetFont($this->_font, 'C', $this->_fontSize);
        $this->SetMargins($this->_margins, 26, $this->_margins, true);
        $this->SetAutoPageBreak(true, 26);
        $this->SetCreator(PDF_CREATOR);
        $this->SetAuthor("Jaime E. Valdez");
        $this->SetTitle($this->data["aduana"] . '-' . $this->data["patente"] . '-' . $this->data["pedimento"]);
        $this->SetSubject("");
        $this->SetKeywords("");
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);
        global $l;
        $this->setLanguageArray($l);
    }

    public function Header() {

        $this->SetY(20, true);
        $this->SetFont($this->_font, 'C', $this->_fontSize);
        $tbl = "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
            <tr>
                <td>CLIENTE: " . $this->data["rfcCliente"] . "</td>
                <td style=\"text-align:center;\">USUARIO:" . $this->data["usuario"] . "</td>
                <td style=\"text-align:right;\">" . $this->data["fechaPago"] . "</td>
            </tr>
        </table>";
        $this->writeHTML($tbl, true, false, false, false, '');
    }

    public function Footer() {
        $this->SetFont("helvetica", '', 6.5);
        $this->SetY(-138, true);
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->MultiCell(380, 0, "AGENTE ADUANAL, APODERADO ADUANAL O DE ALMACEN", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(192, 51, "DECLARO BAJO PROTESTA DE DECIR VERDAD, EN LOS TERMINOS DE LO DISPUESTO POR EL ARTICULO 81 DE LA LEY ADUANERA:\nPATENTE O AUTORIZACIÓN: 3589", "TLR", 'L', 1, 1, '', '', true, 0, false, true, 0);
        $this->SetY(-128, true);
        $this->MultiCell(100, 0, "NOMBRE O RAZ. SOC:", "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(280, 0, "LUIS ESTEBAN MARRON LIMON MALL640523749", "R", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell(380, 0, "ORGANIZACIÓN ADUANAL DE QUERÉTARO, S.C.", "LR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell(50, 0, "RFC:", "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(140, 0, "OAQ030623UL8", 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(50, 0, "CURP:", 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(140, 0, "MALL640523HTSRMS00", "R", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell(380, 15, "", "LR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell(180, 0, "NUMERO DE SERIE DEL CERTIFICADO:", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(200, 0, "00001000000305212553", "TR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(192, 0, "FIRMA AUTOGRAFA", "RL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell(120, 23, "FIRMA ELECTRÓNICA AVANZADA:", "TBL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(452, 23, "wWgdg3xC9+lzVv8CrPGN/MQBOyt4Tx+PTLhSgy46ZzUelJEAzEAlUio38ofEgcGC2/xrF2qglJfvzGjgeD7ccK77snds1Of12t3uwFwaVvl5f0
oHrGxI/xixNeKU4NuXZfPgos8TPOCvXVYOrtkFMu1Zd2H61oJ9ceD6HcDv+DM=", "TBR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        if($this->_second == false) {            
            $this->Ln();
            $this->SetFont("helvetica", '', 6);
            $this->MultiCell(572,0, "El pago de las contribuciones puede realizarse mediante el servicio de \"Pago Electrónico Centralizado Aduanero\" (PECA), conforme a lo establecido en la regla 1.6.2., con la posibilidad de que la cuenta bancaria de la persona que contrate los servicios sea afectada directamente por el Banco. El agente o apoderado aduanal que utilice el servicio de PECA, deberá imprimir la certificación bancaria en el campo correspondiente del pedimento o en el documento oficial, conforme al Apéndice 20 \"Certificación de Pago Electrónico Centralizado\" del Anexo 22. El Importador-Exportador podrá solicitar la certificación de la información contenida en este pedimento en: Administración General de Aduanas, Administración de Operación Aduanera \"7\" Av. Hidalgo Núm. 77. Módulo IV, P.B., Col. Guerrero C.P. 06300. México, D.F.", "TLRB", 'L', 1, 0, '', '', true, 0, false, true, 0);
        } else {
            $this->Ln();
            $this->SetFont("helvetica", '', 6);
            $this->MultiCell(572,25, "", "TLRB", 'L', 1, 0, '', '', true, 0, false, true, 0);            
        }
        $this->Ln();
        $this->SetFont("helvetica", '', 6.5);
        $this->MultiCell(170, 20, $this->data["copia"], 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(400, 20, $this->data["destino"], 0, 'R', 1, 0, '', '', true, 0, false, true, 0);
    }

    public function CreateDocument() {
        $this->AddPage();
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->SetY($this->_marginTop, true);

        $this->SetFillColor($this->_shade[0], $this->_shade[1], $this->_shade[2]);
        $this->MultiCell(($this->getPageWidth() - ($this->_margins * 2)) / 4, 0, "", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(($this->getPageWidth() - ($this->_margins * 2)) / 4, 0, "PEDIMENTO", "T", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(($this->getPageWidth() - ($this->_margins * 2)) / 4, 0, "Ref: " . $this->data["referencia"], "T", 'R', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->MultiCell(143, 0, "Página " . $this->getAliasNumPage() . ' de ' . trim($this->getAliasNbPages()), "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);

        $this->Ln();
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->MultiCell(160, 0, "NUM. PEDIMENTO: " . substr($this->data["aduana"], 0, 2) . ' ' . $this->data["patente"] . ' ' . $this->data["pedimento"], "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(80, 0, "T. OPER: " . $this->data["tipoOp"], "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(110, 0, "CVE. PEDIMENTO: " . $this->data["cvePed"], "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(79, 0, "REGIMEN: " . $this->data["regimen"], "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($this->_shade[0], $this->_shade[1], $this->_shade[2]);
        $this->MultiCell(143, 0, "CERTIFICACIONES", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);

        $pago = "*** PAGO ELECTRÓNICO ***\nBANORTE, S.A.\n3589 5001190\nOP. 8160733\n30/01/2015\nACUSE: 8008160733\nCONTRIB. OTRAS F.P.: $0\nCONTRIB. EFECTIVO: $3,625\nTOTAL DE CONTRIB.: $3,625\nCONTRAPRESTACIONES: $0\nIMPORTE TOTAL: $3,625";
        $this->Ln();
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->MultiCell(110, 0, "DESTINO/ORIGEN: 9", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(105, 0, "TIPO CAMBIO: 13.19980", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(125, 0, "PESO BRUTO 249.680", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(89, 0, "ADUANA E/S: 160", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, '', $this->_fontSmall);
        $this->MultiCell(143, 248, $pago, "TLR", 'L', 1, 1, '', '', true, 0, false, true, 0);

        $this->SetY(60);
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->MultiCell(230, 0, "MEDIOS DE TRANSPORTE:", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(110, 0, "VALOR DOLARES:", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(89, 0, "1562", "TR", 'R', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell(78, 0, "ENTRADA/SALIDA:", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(76, 0, "ARRIBO:", 0, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(76, 0, "SALIDA:", 0, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(110, 0, "VALOR ADUANA:", "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(89, 0, "20618", "R", 'R', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell(78, 0, "98", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(76, 0, "98", 0, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(76, 0, "98", 0, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(110, 0, "PRECIO PAGADO/VALOR:", "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(89, 0, "", "R", 'R', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell(230, 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(110, 0, "COMERCIAL:", "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(89, 0, "20618", "R", 'R', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFillColor($this->_shade[0], $this->_shade[1], $this->_shade[2]);
        $this->MultiCell(429, 0, "DATOS DEL IMPORTADOR/EXPORTADOR", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->MultiCell(30, 0, "RFC: ", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(80, 0, $this->data["rfcCliente"], "T", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(319, 0, "NOMBRE, DEMONINACIÓN O RAZÓN SOCIAL", "TR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->MultiCell(30, 0, "", "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(80, 0, "", 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(319, 0, "CNH INDUSTRIAL, S.A. DE C.V.", "R", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell(429, 0, "DOMICILIO: ", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell(99, 0, "VAL. SEGUROS", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(70, 0, "SEGUROS", "T", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(70, 0, "FLETES", "T", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(70, 0, "EMBALAJES", "T", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(120, 0, "OTROS INCROMENTABLES", "TR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell(99, 0, "0.00", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(70, 0, "0", 0, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(70, 0, "0", 0, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(70, 0, "0", 0, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(120, 0, "0", "R", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell(90, 60, "CODIGO DE ACEPTACIÓN\nXYZHRL9A", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
//        $this->MultiCell(230, 70, "", "T", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $style = array(
            'border' => 1,
            'vpadding' => 0,
            'hpadding' => 0,
            'fgcolor' => $this->data["colors"]["line"],
            'bgcolor' => false, //array(255,255,255)
            
            'module_width' => 2, // width of a single module in points
            'module_height' => 1  // height of a single module in points
        );
        $pedimento = $this->data["patente"] . "\n"                           // 1
                . $this->data["pedimento"] . "\n"                            // 2
                . str_pad($this->data["cvePed"], 3, '0', STR_PAD_LEFT) . "\n"        // 3
                . str_pad($this->data["rfcCliente"], 13, '0', STR_PAD_LEFT) . "\n"   // 4
                . str_pad("0", 13, '0', STR_PAD_LEFT) . "\n"                        // 5
                . str_pad("XYZHRL9A", 8, '0', STR_PAD_LEFT) . "\n"                  // 6
                . str_pad("440.000", 15, '0', STR_PAD_LEFT) . "\n"                  // 7
                . str_pad("3625", 12, '0', STR_PAD_LEFT) . "\n"                     // 8
                . str_pad("0", 12, '0', STR_PAD_LEFT) . "\n"                        // 9
                . str_pad("281", 13, '0', STR_PAD_LEFT) . "\n"                      // 10
                . str_pad("0", 4, '0', STR_PAD_LEFT) . "\n"                         // 11
                . "0";                                                              // 12
        $this->write2DBarcode($pedimento, 'PDF417', 110, 149, 209, 60, $style, 'T');
        $this->MultiCell(130, 60, "CLAVE DE LA SECCIÓN ADUANERA DE DESPACHO: 646\nAEROPUERTO INTERCONTINENTAL DE QUERETARO, MARQUES Y COLO, QUERETARO", "TR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell(170, 0, "MARCAS, NUMEROS Y TOTAL DE BULTOS", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(259, 0, "S/M, S/N; 8", "TR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell(150, 0, "FECHAS", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($this->_shade[0], $this->_shade[1], $this->_shade[2]);
        $this->MultiCell(279, 0, "TASAS A NIVEL PEDIMENTO", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->MultiCell(75, 0, "ENTRADA", "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(75, 0, "13/08/2014", 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(93, 0, "CONTRIB", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(93, 0, "CVE. T. TASA", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(93, 0, "TASA", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell(75, 0, "PAGO", "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(75, 0, "30/01/2015", 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(93, 0, "DTA", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(93, 0, "4", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(93, 0, "281.00000", "LR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell(75, 0, "EXTRACCIÓN", "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(75, 0, "30/01/2015", 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(93, 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(93, 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(93, 0, "", "LR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        
        $column1 = 59;
        $column2 = 20;
        $column3 = 65;
        $this->Ln();
        $this->SetFillColor($this->_shade[0], $this->_shade[1], $this->_shade[2]);
        $this->MultiCell(429, 0, "CUADRO DE LIQUIDACIÓN", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->MultiCell($column1, 0, "CONCEPTO", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column2, 0, "F.P.", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column3, 0, "IMPORTE", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column1, 0, "CONCEPTO", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column2, 0, "F.P.", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column3, 0, "IMPORTE", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(141, 0, "TOTALES", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell($column1, 0, "DTA", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column2, 0, "0", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column3, 0, "281", "TL", 'R', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column1, 0, "", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column2, 0, "", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column3, 0, "", "TL", 'R', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(61, 0, "EFECTIVO", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(80, 0, "3625", "TLR", 'R', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell($column1, 0, "IVA", "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column2, 0, "0", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column3, 0, "3344", "L", 'R', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column1, 0, "", "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column2, 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column3, 0, "", "L", 'R', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(61, 0, "OTROS", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(80, 0, "0", "TLR", 'R', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell($column1, 0, "", "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column2, 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column3, 0, "", "L", 'R', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column1, 0, "", "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column2, 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column3, 0, "", "L", 'R', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(61, 0, "TOTAL", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(80, 0, "3625", "TLR", 'R', 1, 0, '', '', true, 0, false, true, 0);
        
        $this->Ln();
        $this->SetFillColor($this->_shade[0], $this->_shade[1], $this->_shade[2]);
        $this->MultiCell(0, 0, "DATOS DEL PROVEEDOR O COMPRADOR", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        
        $column1 = 80;
        $column2 = 210;
        $column3 = 222;
        $column4 = 60;
        $rowHeight = 9;
        $this->Ln();
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->MultiCell($column1, 0, "ID. FISCAL", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column2, 0, "NOMBRE, DENOMINACIÓN O RAZON SOCIAL", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column3, 0, "DOMICILIO:", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column4, 0, "VINCULACIÓN", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $company = "TAURUS INTERNATIONAL CORPORATION";
        $address = "NORTH FRANKLIN TURNPIKE #275, RAMSEY NEW JERSEY, C.P. 07446, ESTADOS UNIDOS DE AMERICA";
        $max = max($this->getNumLines($company,$column2),$this->getNumLines($address,$column3));
        $this->MultiCell($column1, $max * $rowHeight, "222339308 " . $max, "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column2, $max * $rowHeight, $company, "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column3, $max * $rowHeight, $address, "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column4, $max * $rowHeight, "NO", "LR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        
        $column1 = 132;
        $column2 = 60;
        $column3 = 60;
        $column4 = 80;
        $this->Ln();
        $this->MultiCell($column1, 0, "NUMERO DE ACUSE DE VALOR", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column2, 0, "FECHA", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column3, 0, "INCOTERM", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column4, 0, "MONEDA FACT.", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column4, 0, "VAL. MON. FACT.", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column4, 0, "FACTOR MON. FACT.", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column4, 0, "VAL.DOLARES", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell($column1, 0, "COVE150UF1714", "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column2, 0, "02/02/2015", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column3, 0, "CPT", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column4, 0, "USD", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column4, 0, "1562.00", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column4, 0, "1.00000000", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column4, 0, "1562", "LR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell($column1, 0, "00039612", "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column2, 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column3, 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column4, 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column4, 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column4, 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column4, 0, "", "LR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        
        $column1 = 147;
        $column2 = 20;
        $column3 = 135;
        $this->Ln();
        $this->MultiCell($column1, 0, "CLAVE/COMPL. IDENTIFICADOR", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column2, 0, "", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column3, 0, "COMPLEMENTO 1", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column3, 0, "COMPLEMENTO 2", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column3, 0, "COMPLEMENTO 3", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell($column1, 0, "", "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column2, 0, "AG", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column3, 0, "9037", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column3, 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column3, 0, "", "LR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell($column1, 0, "", "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column2, 0, "AC", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column3, 0, "ACNS010", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column3, 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column3, 0, "", "LR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell($column1, 0, "", "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column2, 0, "SF", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column3, 0, "157", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column3, 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column3, 0, "", "LR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell($column1, 0, "", "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column2, 0, "ED", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column3, 0, "04421500ACDY2", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column3, 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column3, 0, "", "LR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell($column1, 0, "", "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column2, 0, "ED", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column3, 0, "04361502GB183", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column3, 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column3, 0, "", "LR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        
        $this->Ln();
        $this->SetFillColor($this->_shade[0], $this->_shade[1], $this->_shade[2]);
        $this->MultiCell(0, 0, "DESCARGOS", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);        
        $column1 = 190;
        $column2 = 190;
        $column3 = 192;
        $this->Ln();
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->MultiCell($column1, 0, "NUM. PEDIMENTO ORIGINAL", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column2, 0, "FECHA DE OPERACIÓN ORIGINAL", "T", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column3, 0, "CVE. PEDIMENTO ORIGINAL", "TR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell($column1, 0, "14 16 3010 4 002575", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column2, 0, "29/08/2014", 0, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column3, 0, "A4", "R", 'C', 1, 0, '', '', true, 0, false, true, 0);
        
        $this->Ln();
        $this->SetFillColor($this->_shade[0], $this->_shade[1], $this->_shade[2]);
        $this->MultiCell(0, 0, "OBSERVACIONES", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->Ln();
        $this->MultiCell(0, 0, "EXTRACCIÓN DE DEPOSITO FISCAL PARA IMPORTACIÓN DEFINITIVA DE CONFORMIDAD CON EL ART. 119,120,122,123 DE LA LEY ADUANERA. SIN SELLO FISCAL\n\nEXTRACCIÓN NO: 8 \nQUEDANDO PENDIENTE DE IMPORTAR: 2,160 PIEZAS", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        
        $this->Ln();
        $this->SetFillColor($this->_shade[0], $this->_shade[1], $this->_shade[2]);
        $this->MultiCell(0, 0, "PARTIDAS", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $c = array(
            20, // ini              0
            52, // fraccion         1
            30, // subidivision     2
            30, // vinc             3
            40, // met val          4
            28, // umc              5
            70, // cant umc         6
            28, // umt              7
            60, // cant umt         8
            30, // pvc              9
            30, // pod              10
            25, // con              11
            40, // tasa             12
            17, // tt               13
            17, // fp               14
            55, // importe          15
            398, // descipcion      16
            87, // val. usd         17
            80, // imp. precio pag  18
            90, // precio unit.     19
            81, // val agreg        20
            60, // blank            21
            135, // marca           22    
            132, // modelo          23
            131, // cod. producto   24
        );
        $this->Ln();
        $this->SetFont($this->_font, '', $this->_fontSmall);
        $this->MultiCell($c[0], 0, "", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[1], 0, "FRACCIÓN", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[2], 0, "SUBD.", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[3], 0, "VINC.", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[4], 0, "MET VAL", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[5], 0, "UMC", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[6], 0, "CANTIDAD UMC", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[7], 0, "UTM", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[8], 0, "CANTIDAD UTM", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[9], 0, "P.V/C", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[10], 0, "P.O/D", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[11], 0, "", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[12], 0, "", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[13], 0, "", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[14], 0, "", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[15], 0, "", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell($c[0], 0, "SEC", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[16], 0, "DESCIPCIÓN (REGLONES VARIABLES SEGÚN SE REQUIERA)", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[11], 0, "CON.", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[12], 0, "TASA", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[13], 0, "T.T.", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[14], 0, "F.P.", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[15], 0, "IMPORTE", "LR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell($c[0], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[17], 0, "VAL ADU/USD", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[18], 0, "IMP. PRECIO PAG.", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[19], 0, "PRECIO UNIT.", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[20], 0, "VAL.AGREG.", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[21], 0, "", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[11], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[12], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[13], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[14], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[15], 0, "", "LR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell($c[0], 0, "", "LR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[22], 0, "MARCA", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[23], 0, "MODELO", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[24], 0, "CODIGO PRODUCTO", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[11], 0, "", "BL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[12], 0, "", "BL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[13], 0, "", "BL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[14], 0, "", "BL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($c[15], 0, "", "BLR", 'C', 1, 0, '', '', true, 0, false, true, 0);

        $array[0] = array(
            'secuencia' => 1,
            'fraccion' => 73182299,
            'subdivision' => null,
            'vinculacion' => '0',
            'metVal' => '1',
            'umc' => 6,
            'cantUMC' => 200,
            'umt' => 1,
            'cantUMT' => 16.710,
            'paisOrigen' => 'IND',
            'paisVendedor' => 'USA',
            'descripcion' => 'RONDANA ESPACIADOR',
            'valorAduana' => 3828,
            'importePagado' => 3828,
            'precioUnitario' => 19.14000,
            'identificadores' => array(
                'EN' => array('VII', 'NOM-050-SCFI-2004')
            ),
            'tasas' => array(
                6 => array('Ex.', 0, 0, 0), // igi ejemplo
                4 => array('IVA', 1, 0, 635), // iva ejemplo
            ),
            'observaciones' => 'NP: 84371348'
        );
        $array[1] = array(
            'secuencia' => 2,
            'fraccion' => 87084001,
            'subdivision' => null,
            'vinculacion' => '0',
            'metVal' => '1',
            'umc' => 6,
            'cantUMC' => 240,
            'umt' => 6,
            'cantUMT' => 240,
            'paisOrigen' => 'IND',
            'paisVendedor' => 'USA',
            'descripcion' => 'ESPACIADOR DENTADO',
            'valorAduana' => 16790,
            'importePagado' => 16790,
            'precioUnitario' => 69.95833,
            'identificadores' => null,
            'tasas' => array(
                6 => array('Ex.', 0, 0, 0), // igi ejemplo
                4 => array('IVA', 1, 0, 2709), // iva ejemplo
            ),
            'observaciones' => 'NP: 84371350'
        );
        $array[2] = array(
            'secuencia' => 3,
            'fraccion' => 87084001,
            'subdivision' => null,
            'vinculacion' => '0',
            'metVal' => '1',
            'umc' => 6,
            'cantUMC' => 240,
            'umt' => 6,
            'cantUMT' => 240,
            'paisOrigen' => 'IND',
            'paisVendedor' => 'USA',
            'descripcion' => 'ESPACIADOR DENTADO',
            'valorAduana' => 16790,
            'importePagado' => 16790,
            'precioUnitario' => 69.95833,
            'identificadores' => null,
            'tasas' => array(
                6 => array('Ex.', 0, 0, 0), // igi ejemplo
                4 => array('IVA', 1, 0, 2709), // iva ejemplo
            ),
            'observaciones' => 'NP: 84371350'
        );
        $array[3] = array(
            'secuencia' => 4,
            'fraccion' => 87084001,
            'subdivision' => null,
            'vinculacion' => '0',
            'metVal' => '1',
            'umc' => 6,
            'cantUMC' => 240,
            'umt' => 6,
            'cantUMT' => 240,
            'paisOrigen' => 'IND',
            'paisVendedor' => 'USA',
            'descripcion' => 'ESPACIADOR DENTADO',
            'valorAduana' => 16790,
            'importePagado' => 16790,
            'precioUnitario' => 69.95833,
            'identificadores' => null,
            'tasas' => array(
                6 => array('Ex.', 0, 0, 0), // igi ejemplo
                4 => array('IVA', 1, 0, 2709), // iva ejemplo
            ),
            'observaciones' => 'NP: 84371350'
        );
        $array[4] = array(
            'secuencia' => 5,
            'fraccion' => 87084001,
            'subdivision' => null,
            'vinculacion' => '0',
            'metVal' => '1',
            'umc' => 6,
            'cantUMC' => 240,
            'umt' => 6,
            'cantUMT' => 240,
            'paisOrigen' => 'IND',
            'paisVendedor' => 'USA',
            'descripcion' => 'ESPACIADOR DENTADO',
            'valorAduana' => 16790,
            'importePagado' => 16790,
            'precioUnitario' => 69.95833,
            'identificadores' => null,
            'tasas' => array(
                6 => array('Ex.', 0, 0, 0), // igi ejemplo
                4 => array('IVA', 1, 0, 2709), // iva ejemplo
            ),
            'observaciones' => 'NP: 84371350'
        );
        $array[5] = array(
            'secuencia' => 6,
            'fraccion' => 87084001,
            'subdivision' => null,
            'vinculacion' => '0',
            'metVal' => '1',
            'umc' => 6,
            'cantUMC' => 240,
            'umt' => 6,
            'cantUMT' => 240,
            'paisOrigen' => 'IND',
            'paisVendedor' => 'USA',
            'descripcion' => 'ESPACIADOR DENTADO',
            'valorAduana' => 16790,
            'importePagado' => 16790,
            'precioUnitario' => 69.95833,
            'identificadores' => null,
            'tasas' => array(
                6 => array('Ex.', 0, 0, 0), // igi ejemplo
                4 => array('IVA', 1, 0, 2709), // iva ejemplo
            ),
            'observaciones' => 'NP: 84371350'
        );
        $array[6] = array(
            'secuencia' => 6,
            'fraccion' => 87084001,
            'subdivision' => null,
            'vinculacion' => '0',
            'metVal' => '1',
            'umc' => 6,
            'cantUMC' => 240,
            'umt' => 6,
            'cantUMT' => 240,
            'paisOrigen' => 'IND',
            'paisVendedor' => 'USA',
            'descripcion' => 'ESPACIADOR DENTADO',
            'valorAduana' => 16790,
            'importePagado' => 16790,
            'precioUnitario' => 69.95833,
            'identificadores' => null,
            'tasas' => array(
                6 => array('Ex.', 0, 0, 0), // igi ejemplo
                4 => array('IVA', 1, 0, 2709), // iva ejemplo
            ),
            'observaciones' => 'NP: 84371350'
        );

        foreach ($array as $item) {
            $this->Ln();
            $this->SetFont($this->_font, '', $this->_fontSmall);
            $this->MultiCell($c[0], 0, $item["secuencia"], "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($c[1], 0, $item["fraccion"], "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($c[2], 0, $item["subdivision"], "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($c[3], 0, $item["vinculacion"], "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($c[4], 0, $item["metVal"], "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($c[5], 0, $item["umc"], "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($c[6], 0, $item["cantUMC"], "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($c[7], 0, $item["umt"], "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($c[8], 0, $item["cantUMT"], "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($c[9], 0, $item["paisVendedor"], "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($c[10], 0, $item["paisOrigen"], "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($c[11], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($c[12], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($c[13], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($c[14], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($c[15], 0, "", "LR", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->Ln();
            $this->MultiCell($c[0], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($c[16], 0, $item["descripcion"], "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($c[11], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($c[12], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($c[13], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($c[14], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($c[15], 0, "", "LR", 'C', 1, 0, '', '', true, 0, false, true, 0);
            if (isset($item["observaciones"])) {
                $this->Ln();
                $this->MultiCell($c[0], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($c[16], 0, "OBSERVACIONES A NIVEL PARTIDA", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($c[11], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($c[12], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($c[13], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($c[14], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($c[15], 0, "", "LR", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->Ln();
                $this->MultiCell($c[0], 0, "", "BL", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($c[16], 0, $item["observaciones"], "TLBR", 'L', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($c[11], 0, "", "LB", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($c[12], 0, "", "LB", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($c[13], 0, "", "LB", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($c[14], 0, "", "LB", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($c[15], 0, "", "LBR", 'C', 1, 0, '', '', true, 0, false, true, 0);
            }
            if (($this->getPageHeight() - $this->GetY()) < 200) {
                $this->AddPage();
                $this->_second = true;
                $this->Ln(5);
                $this->SetFillColor($this->_shade[0], $this->_shade[1], $this->_shade[2]);
                $this->MultiCell(($this->getPageWidth() - ($this->_margins * 2)) / 4, 0, "ANEXO DEL PEDIMENTO", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
                $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
                $this->MultiCell(($this->getPageWidth() - ($this->_margins * 2)) / 4, 0, "", 0, 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell(($this->getPageWidth() - ($this->_margins * 2)) / 4, 0, "Ref: " . $this->data["referencia"], "B", 'R', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell(143, 0, "Página " . $this->getAliasNumPage() . ' de ' . trim($this->getAliasNbPages()), "B", 'C', 1, 0, '', '', true, 0, false, true, 0);
            }
        }
        $this->Ln();
        $this->MultiCell(32, 0, "****", "T", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(130, 0, "FIN DE PEDIMENTO", "T", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(140, 0, "**** NUM. TOTAL DE PARTIDAS:", "T", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(80, 0, count($array), "T", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(130, 0, "**** CLAVE PREVALIDADOR:", "T", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(30, 0, "010", "T", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(30, 0, "****", "T", 'C', 1, 0, '', '', true, 0, false, true, 0);
    }

}
