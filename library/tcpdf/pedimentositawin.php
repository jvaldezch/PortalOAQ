<?php

require "tcpdf.php";
require "tcpdf_barcodes_2d.php";

class PedimentoSitawin extends TCPDF {

    protected $_margins = 20;
    protected $_font = "helvetica";
    protected $_fontB = "helveticaB";
    protected $_fontSize = 7;
    protected $_fontSmall = 6.5;
    protected $_marginTop = 32;
    protected $_shade = array(217, 217, 217);
    protected $_shaden = array(255, 255, 255);
    protected $_second = false;
    protected $_data = false;
    protected $_numPedimento = false;
    protected $_cp = false;
    protected $_inc = false;

    function __construct($data, $orientation, $unit, $format) {
        parent::__construct($orientation, $unit, $format, true, "UTF-8", false);
        $this->_data = $data;
        $this->SetFont($this->_font, 'C', $this->_fontSize);
        $this->SetMargins($this->_margins, 26, $this->_margins, true);
        $this->SetAutoPageBreak(true, 26);
        $this->SetCreator(PDF_CREATOR);
        $this->SetAuthor("Jaime E. Valdez");
        $this->SetTitle($this->_data["aduana"] . '-' . $this->_data["patente"] . '-' . $this->_data["pedimento"]);
        $this->SetSubject("");
        $this->SetKeywords("");
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);
        global $l;
        $this->setLanguageArray($l);
        $this->_cp = array(
            21, // ini              0
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
            36, // tasa             12
            19, // tt               13
            19, // fp               14
            54, // importe          15
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

        $this->_inc = array(
            'row' => array(1 => 9),
            'col' => array(
                1 => 80, // idfiscal
                2 => 210, // razon social
                3 => 222, // domicilio
                4 => 60     // vinculacion
            ),
            'coli' => array(
                1 => 122, // numero de acuse
                2 => 60, // fecha
                3 => 50, // incoterm
                4 => 70, // moneda
                5 => 90, // valor factura
                6 => 90, // factor
                7 => 90, // valor dolares
            )
        );
        $this->SetLineStyle(array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(70, 70, 70)));
    }

    public function Header() {
        $this->SetY(20, true);
        $this->SetFont($this->_font, 'C', $this->_fontSize);
        $tbl = "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
            <tr>
                <td>CLIENTE: " . $this->_data["rfcCliente"] . "</td>
                <td style=\"text-align:center;\">USUARIO:" . $this->_data["usuario"] . "</td>
                <td style=\"text-align:right;\">" . $this->_data["sis"] . ': ' . date('d/m/Y H:i a') . "</td>
            </tr>
        </table>";
        $this->writeHTML($tbl, true, false, false, false, '');
    }
    
    protected function _doWaterMarking() {
        $img_file = K_PATH_IMAGES . "sin_validez.png";
        $this->Image($img_file, 70, 40, 450, 450, '', '', '', false, 300, '', false, false, 0);
        $this->SetPageMark();        
    }

    public function PedimentoUnico() {
        $this->AddPage();
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->SetY($this->_marginTop, true);
        $this->SetFillColor($this->_shade[0], $this->_shade[1], $this->_shade[2]);
        $this->MultiCell(($this->getPageWidth() - ($this->_margins * 2)) / 4, 0, "", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(($this->getPageWidth() - ($this->_margins * 2)) / 4, 0, "PEDIMENTO", "T", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(($this->getPageWidth() - ($this->_margins * 2)) / 4, 0, "REF: " . $this->_data["referencia"], "T", 'R', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->SetFont($this->_font, '', $this->_fontSize);
        $curPage = "Página " . $this->PageNo() . ' de ' . $this->getAliasNbPages();
        $this->MultiCell(143, 0, $curPage, "TLR", 'R', 1, 0, '', '', true, 0, false, true, 0);
        $this->_datosEncabezado();
        $this->_datosPago(292);
        $this->_medios(60);
        $this->_cliente();
        $this->_incrementables();
        $this->_drawCodeBar(110, 162, 60, 209);
        $this->_marcas();
        $this->_fechasTasas();
        $this->_liquidacion();
        if (isset($this->_data["proveedores"]) && $this->_data["proveedores"] !== false) {
            $this->_facturas();
        }
        if (isset($this->_data["identificadores"]) && $this->_data["identificadores"] !== false) {
            $this->_identificadores();
        }
        if (isset($this->_data["transporte"]) && $this->_data["transporte"] !== false) {
            $this->_transporte();
        }
        if (isset($this->_data["candados"]) && $this->_data["candados"] !== false) {
            $this->_candados();
        }
        if (isset($this->_data["contenedores"]) && $this->_data["contenedores"] !== false) {
            $this->_contenedores();
        }
        if (isset($this->_data["guias"]) && $this->_data["guias"] !== false) {
            $this->_guias();
        }
        if (isset($this->_data["extracciones"]) && $this->_data["extracciones"] !== false) {
            $this->_extracciones();
        }
        if (isset($this->_data["observaciones"]) && $this->_data["observaciones"] !== false) {
            $this->_observaciones();
        }
        if (isset($this->_data["fracciones"]) && $this->_data["fracciones"] !== false) {
            $this->_partidas();
        }
        $this->_finDePedimento();
        //$this->_doWaterMarking();
    }

    public function PedimentoSimplificado() {
        $this->AddPage();
        $this->SetFont($this->_font, "", $this->_fontSize);
        $this->SetY($this->_marginTop, true);
        $this->SetFillColor($this->_shade[0], $this->_shade[1], $this->_shade[2]);
        $this->MultiCell(($this->getPageWidth() - ($this->_margins * 2)) / 4, 0, "", "TL", "L", 1, 0, "", "", true, 0, false, true, 0);
        $this->MultiCell(($this->getPageWidth() - ($this->_margins * 2)) / 4, 0, "PEDIMENTO", "T", "C", 1, 0, "", "", true, 0, false, true, 0);
        $this->MultiCell(($this->getPageWidth() - ($this->_margins * 2)) / 4, 0, "Ref: " . $this->_data["referencia"], "T", "R", 1, 0, "", "", true, 0, false, true, 0);
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->MultiCell(143, 0, "Página " . $this->getAliasNumPage() . " de " . trim($this->getAliasNbPages()), "TLR", "C", 1, 0, "", "", true, 0, false, true, 0);
        $this->Ln();
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->MultiCell(160, 0, "NUM. PEDIMENTO: " . substr($this->_data["fechaPago"], -2) . " " . substr($this->_data["aduana"], 0, 2) . " " . $this->_data["patente"] . " " . $this->_data["pedimento"], "TL", "L", 1, 0, "", "", true, 0, false, true, 0);
        $this->MultiCell(110, 0, "T. OPER: " . $this->_data["tipoOperacion"], "T", "C", 1, 0, "", "", true, 0, false, true, 0);
        $this->MultiCell(159, 0, "CVE. PEDIMENTO: " . $this->_data["cvePedimento"], "T", "R", 1, 0, "", "", true, 0, false, true, 0);
        $this->SetFillColor($this->_shade[0], $this->_shade[1], $this->_shade[2]);
        $this->MultiCell(143, 0, "CERTIFICACIONES", "TLR", "C", 1, 0, "", "", true, 0, false, true, 0);
        if (isset($this->_data["pago"]) && $this->_data["pago"] !== false) {
            $pago = "*** PAGO ELECTRÓNICO ***\n"
                    . "{$this->_data["pago"]["nombreBanco"]}\n"
                    . "{$this->_data["pago"]["patente"]} {$this->_data["pedimento"]}\n"
                    . "OP. {$this->_data["pago"]["operacion"]}\n"
                    . "{$this->_data["fechaPago"]}\n"
                    . "ACUSE: {$this->_data["pago"]["firmaBanco"]}\n"
                    . "IMPORTE TOTAL: {$this->_numberCurrency($this->_data["liquidacion"]["total"])}\n"
                    . "CONTRIB. OTRAS F.P.: {$this->_numberCurrency($this->_data["liquidacion"]["otros"])}\n"
                    . "CONTRIB. EFECTIVO: {$this->_numberCurrency($this->_data["pago"]["efectivo"] - $this->_data["cnt"])}\n"
                    . "TOTAL DE CONTRIB.: " . $this->_numberCurrency($this->_data["liquidacion"]["total"] - $this->_data["cnt"]) . "\n"
                    . "CONTRAPRESTACIONES: {$this->_numberCurrency($this->_data["cnt"])}";
        } else {
            $pago = "";
        }
        $this->Ln();
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->MultiCell(160, 0, "DESTINO/ORIGEN: " . $this->_data["destinoOrigen"], "TL", "L", 1, 0, "", "", true, 0, false, true, 0);
        $this->MultiCell(110, 0, "PESO BRUTO " . $this->_data["pesoBruto"], "T", "C", 1, 0, "", "", true, 0, false, true, 0);
        $this->MultiCell(159, 0, "ADUANA E/S: " . $this->_data["aduanaEntrada"], "T", "R", 1, 0, "", "", true, 0, false, true, 0);
        $this->SetFont($this->_font, "", $this->_fontSmall);
        $this->MultiCell(143, 120, $pago, "TLR", "L", 1, 1, "", "", true, 0, false, true, 0);
        $this->SetY(60);
        $this->SetFillColor($this->_shade[0], $this->_shade[1], $this->_shade[2]);
        $this->MultiCell(429, 0, "DATOS DEL IMPORTADOR/EXPORTADOR", "TLR", "C", 1, 0, "", "", true, 0, false, true, 0);
        $this->Ln();
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->MultiCell(30, 0, "RFC: ", "TL", "L", 1, 0, "", "", true, 0, false, true, 0);
        $this->MultiCell(80, 0, $this->_data["cliente"]["rfc"], "T", "L", 1, 0, "", "", true, 0, false, true, 0);
        $this->MultiCell(319, 0, "CURP", "TR", "L", 1, 0, "", "", true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell(90, 80, "CODIGO DE ACEPTACIÓN\n" . $this->_data["firmaValidacion"], "TL", "C", 1, 0, "", "", true, 0, false, true, 0);
        if (isset($this->_data["codigoBarras"]) && $this->_data["codigoBarras"] == true) {
//            $this->_codigoDeBarras(110, 77, 60, 209, $this->_data["patente"], $this->_data["pedimento"], $this->_data["cvePedimento"], $this->_data["cliente"]["rfc"], $this->_data["firmaValidacion"], ($this->_data["pago"]["efectivo"] - $this->_data["cnt"]), $this->_data["pago"]["total"], $this->_data["pago"]["otros"], $this->_data["dta"]);
            $this->MultiCell(209, 77, "", "LTR", "L", 1, 0, "", "", true, 0, false, true, 0);
        } else {
            $this->MultiCell(209, 162, "", "LTR", "L", 1, 0, "", "", true, 0, false, true, 0);
        }
        if (isset($this->_data["aduanaNombre"]) && $this->_data["aduanaNombre"] != "") {
            $nombreAduana = "\n" . utf8_encode($this->_data["aduanaNombre"]);
        } else {
            $nombreAduana = "";
        }
        $this->MultiCell(130, 60, "CLAVE DE LA SECCIÓN ADUANERA DE DESPACHO: {$this->_data["aduana"]}" . $nombreAduana, "TR", "L", 1, 0, "", "", true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell(170, 0, "MARCAS, NUMEROS Y TOTAL DE BULTOS", "TL", "L", 1, 0, "", "", true, 0, false, true, 0);
        $this->MultiCell(259, 0, $this->_data["marcas"] . "; " . $this->_data["bultos"], "TR", "L", 1, 0, "", "", true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell(429, 0, "FECHAS", "TLR", "C", 1, 0, "", "", true, 0, false, true, 0);
        if (!preg_match("/1899/", $this->_data["fechaExtraccion"])) {
            $this->Ln();
            $this->MultiCell(110, 0, "ENTRADA", "TL", "L", 1, 0, "", "", true, 0, false, true, 0);
            $this->MultiCell(115, 0, $this->_data["fechaEntrada"], "T", "L", 1, 0, "", "", true, 0, false, true, 0);
            $this->MultiCell(100, 0, "PAGO", "T", "L", 1, 0, "", "", true, 0, false, true, 0);
            $this->MultiCell(104, 0, $this->_data["fechaPago"], "TR", "L", 1, 0, "", "", true, 0, false, true, 0);
            $this->Ln();
            $this->MultiCell(110, 0, "EXTRACCION", "L", "L", 1, 0, "", "", true, 0, false, true, 0);
            $this->MultiCell(115, 0, $this->_data["fechaExtraccion"], "", "L", 1, 0, "", "", true, 0, false, true, 0);
            $this->MultiCell(100, 0, "", "", "L", 1, 0, "", "", true, 0, false, true, 0);
            $this->MultiCell(104, 0, "", "R", "L", 1, 0, "", "", true, 0, false, true, 0);
        } else {
            $this->Ln();
            $this->MultiCell(110, 0, "PRESENTACION", "TL", "L", 1, 0, "", "", true, 0, false, true, 0);
            $this->MultiCell(115, 0, $this->_data["fechaEntrada"], "T", "L", 1, 0, "", "", true, 0, false, true, 0);
            $this->MultiCell(100, 0, "PAGO", "T", "L", 1, 0, "", "", true, 0, false, true, 0);
            $this->MultiCell(104, 0, $this->_data["fechaPago"], "TR", "L", 1, 0, "", "", true, 0, false, true, 0);
            $this->Ln();
            $this->MultiCell(429, 0, "", "LR", "C", 1, 0, "", "", true, 0, false, true, 0);
        }
        if (isset($this->_data["transportista"]) && $this->_data["transportista"] === true) {
            if (isset($this->_data["coves"]) && $this->_data["coves"] !== false) {
                $coves = $this->_data["coves"];
                $this->Ln();
                $this->MultiCell(120, 0, "NUMERO DE ACUSE DE VALOR", "TL", "L", 1, 0, "", "", true, 0, false, true, 0);
                $this->MultiCell(90, 0, isset($coves[0]) ? $coves[0]["cove"] : "", "TL", "L", 1, 0, "", "", true, 0, false, true, 0);
                $this->MultiCell(90, 0, isset($coves[1]) ? $coves[1]["cove"] : "", "TL", "L", 1, 0, "", "", true, 0, false, true, 0);
                $this->MultiCell(90, 0, isset($coves[2]) ? $coves[2]["cove"] : "", "TL", "L", 1, 0, "", "", true, 0, false, true, 0);
                $this->MultiCell(90, 0, isset($coves[3]) ? $coves[3]["cove"] : "", "TL", "L", 1, 0, "", "", true, 0, false, true, 0);
                $this->MultiCell(92, 0, isset($coves[4]) ? $coves[4]["cove"] : "", "TLR", "L", 1, 0, "", "", true, 0, false, true, 0);
            }
            if (isset($this->_data["edocuments"]) && $this->_data["edocuments"] !== false) {
                $edocs = $this->_data["edocuments"];
                $this->Ln();
                $this->MultiCell(120, 0, "NUMERO DE E-EDOCUMENT", "TL", "L", 1, 0, "", "", true, 0, false, true, 0);
                $this->MultiCell(90, 0, isset($edocs[0]) ? $edocs[0]["caso1"] : "", "TL", "L", 1, 0, "", "", true, 0, false, true, 0);
                $this->MultiCell(90, 0, isset($edocs[1]) ? $edocs[1]["caso1"] : "", "TL", "L", 1, 0, "", "", true, 0, false, true, 0);
                $this->MultiCell(90, 0, isset($edocs[2]) ? $edocs[2]["caso1"] : "", "TL", "L", 1, 0, "", "", true, 0, false, true, 0);
                $this->MultiCell(90, 0, isset($edocs[3]) ? $edocs[3]["caso1"] : "", "TL", "L", 1, 0, "", "", true, 0, false, true, 0);
                $this->MultiCell(92, 0, isset($edocs[4]) ? $edocs[4]["caso1"] : "", "TLR", "L", 1, 0, "", "", true, 0, false, true, 0);
            }
            if (isset($this->_data["observaciones"]) && $this->_data["observaciones"] !== false) {
                $this->Ln();
                $this->SetFillColor($this->_shade[0], $this->_shade[1], $this->_shade[2]);
                $this->MultiCell(0, 0, "OBSERVACIONES", "TLR", "C", 1, 0, "", "", true, 0, false, true, 0);
                $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
                $this->Ln();
                $this->MultiCell(0, 0, $this->_data["observaciones"], "TLR", "L", 1, 0, "", "", true, 0, false, true, 0);
            }
        }
        $this->Ln();
        $this->MultiCell(32, 0, "****", "T", "C", 1, 0, "", "", true, 0, false, true, 0);
        $this->MultiCell(130, 0, "FIN DE PEDIMENTO", "T", "C", 1, 0, "", "", true, 0, false, true, 0);
        $this->MultiCell(140, 0, "**** NUM. TOTAL DE PARTIDAS:", "T", "C", 1, 0, "", "", true, 0, false, true, 0);
        $this->MultiCell(80, 0, count($this->_data["fracciones"]), "T", "C", 1, 0, "", "", true, 0, false, true, 0);
        $this->MultiCell(130, 0, "**** CLAVE PREVALIDADOR:", "T", "C", 1, 0, "", "", true, 0, false, true, 0);
        $this->MultiCell(30, 0, "010", "T", "C", 1, 0, "", "", true, 0, false, true, 0);
        $this->MultiCell(30, 0, "****", "T", "C", 1, 0, "", "", true, 0, false, true, 0);
        //$this->_doWaterMarking();
    }

    protected function _encabezadoPartidas() {
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->Ln();
        $this->MultiCell($this->_cp[0], 0, "", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[1], 0, "FRACCIÓN", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[2], 0, "SUBD.", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[3], 0, "VINC.", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[4], 0, "MET VAL", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[5], 0, "UMC", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[6], 0, "CANTIDAD UMC", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[7], 0, "UTM", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[8], 0, "CANTIDAD UTM", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[9], 0, "P.V/C", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[10], 0, "P.O/D", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[11], 0, "", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[12], 0, "", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[13], 0, "", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[14], 0, "", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[15], 0, "", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell($this->_cp[0], 0, "SEC", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[16], 0, "DESCIPCIÓN (REGLONES VARIABLES SEGÚN SE REQUIERA)", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[11], 0, "CON.", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[12], 0, "TASA", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[13], 0, "T.T.", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[14], 0, "F.P.", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[15], 0, "IMPORTE", "LR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell($this->_cp[0], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[17], 0, "VAL ADU/USD", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[18], 0, "IMP. PRECIO PAG.", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[19], 0, "PRECIO UNIT.", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[20], 0, "VAL.AGREG.", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[21], 0, "", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[11], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[12], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[13], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[14], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[15], 0, "", "LR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell($this->_cp[0], 0, "", "LR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[22], 0, "MARCA", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[23], 0, "MODELO", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[24], 0, "CODIGO PRODUCTO", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[11], 0, "", "BL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[12], 0, "", "BL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[13], 0, "", "BL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[14], 0, "", "BL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[15], 0, "", "BLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
    }

    protected function _verificarPartidasSiguientePagina($min) {
        if (($this->getPageHeight() - $this->GetY()) < $min) {
//            $this->_doWaterMarking();
            $this->_singleLine();
            $this->AddPage();
            $this->_second = true;
            $this->_encabezadoSegundaPagina();
            $this->_encabezadoPartidas();
        }
        return false;
    }

    protected function _singleLine() {
        $this->Ln();
        $this->MultiCell(572, 0, "", "T", 'C', 1, 0, '', '', true, 0, false, true, 0);
    }

    protected function _verificarSiguientePagina($min) {
        if (($this->getPageHeight() - $this->GetY()) < $min) {
            $this->_doWaterMarking();
            $this->AddPage();
            $this->_encabezadoSegundaPagina();
            $this->_second = true;
        }
        return false;
    }

    protected function _verificarFacturasSiguientePagina($min) {
        if (($this->getPageHeight() - $this->GetY()) < $min) {
            $this->_doWaterMarking();
            $this->AddPage();
            $this->_second = true;
        }
        return false;
    }

    protected function _encabezadoSegundaPagina() {
        $c = array(
            1 => 70, // num ped title
            2 => 90, // num ped
            3 => 40, // tipo operacion title
            4 => 50, // tipo operacion
            5 => 70, // cve pedimento title
            6 => 70, // cve pedimento
            7 => 30, // rfc title
            8 => 152, // rfc
        );
        $this->Ln(5);
        $this->SetFillColor($this->_shade[0], $this->_shade[1], $this->_shade[2]);
        $this->MultiCell(($this->getPageWidth() - ($this->_margins * 2)) / 4, 0, "ANEXO DEL PEDIMENTO", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->MultiCell(($this->getPageWidth() - ($this->_margins * 2)) / 4, 0, "", 0, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(($this->getPageWidth() - ($this->_margins * 2)) / 4, 0, "Ref: " . $this->_data["referencia"], "B", 'R', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(143, 0, "Página " . $this->getAliasNumPage() . ' de ' . trim($this->getAliasNbPages()), "B", 'R', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell($c[1], 0, "NUM. PEDIMENTO:", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->MultiCell($c[2], 0, $this->_numPedimento, "T", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell($c[3], 0, "T. OPER:", "T", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->MultiCell($c[4], 0, $this->_data["tipoOperacion"], "T", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell($c[5], 0, "CVE. PEDIMENTO:", "T", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->MultiCell($c[6], 0, $this->_data["cvePedimento"], "T", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell($c[7], 0, "RFC:", "T", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->MultiCell($c[8], 0, $this->_data['cliente']["rfc"], "TR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell($c[1] + $c[2] + $c[3] + $c[4] + $c[5] + $c[6], 0, "", "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell($c[7], 0, "CURP:", "", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->MultiCell($c[8], 0, isset($this->_data['cliente']["curp"]) ? $this->_data['cliente']["curp"] : '', "R", 'L', 1, 0, '', '', true, 0, false, true, 0);
    }

    protected function _numberCurrency($value) {
        return '$ ' . number_format($value, 0, '.', ',');
    }

    protected function _destino($value) {
        switch ($value) {
            case 9:
                return 'INTERIOR DEL PAÍS';
            default:
                return '';
        }
    }

    protected function _copia($value) {
        switch ($value) {
            case 2:
                return 'IMPORTADOR / EXPORTADOR';
            default:
                return '';
        }
    }

    /**
     * 
     * @param int $xpos
     * @param int $ypos
     * @param int $height
     * @param int $width
     * @param int $patente
     * @param int $pedimento
     * @param string $cvePedimento
     * @param string $rfc
     * @param string $firmaValidacion
     * @param float $efectivo
     * @param float $total
     * @param float $otros
     * @param float $dta
     */
    protected function _codigoDeBarras($xpos, $ypos, $height, $width, $patente, $pedimento, $cvePedimento, $rfc, $firmaValidacion, $efectivo, $total, $otros, $dta) {
        $style = array(
            'border' => 1,
            'vpadding' => 0,
            'hpadding' => 0,
            'fgcolor' => $this->_data["colors"]["line"],
            'bgcolor' => false, //array(255,255,255)            
            'module_width' => 2, // width of a single module in points
            'module_height' => 1  // height of a single module in points
        );
        $string = $patente . "\n"                           // 1
                . $pedimento . "\n"                            // 2
                . str_pad($cvePedimento, 3, '0', STR_PAD_LEFT) . "\n"        // 3
                . str_pad($rfc, 13, '0', STR_PAD_LEFT) . "\n"   // 4
                . str_pad("0", 13, '0', STR_PAD_LEFT) . "\n"                        // 5
                . str_pad($firmaValidacion, 8, '0', STR_PAD_LEFT) . "\n"                  // 6
                . str_pad($efectivo, 15, '0', STR_PAD_LEFT) . "\n"                  // 7
                . str_pad($total, 12, '0', STR_PAD_LEFT) . "\n"                     // 8
                . str_pad($otros, 12, '0', STR_PAD_LEFT) . "\n"                        // 9
                . str_pad($dta, 13, '0', STR_PAD_LEFT) . "\n"                      // 10
                . str_pad("0", 4, '0', STR_PAD_LEFT) . "\n"                         // 11
                . "0";                                                              // 12
        $this->write2DBarcode($string, 'PDF417', $xpos, $ypos, $width, $height, $style, 'T');
    }

    /**
     * IDENTIFICADORES A NIVEL PEDIMENTO
     */
    protected function _identificadores() {
        $column1 = 147;
        $column2 = 20;
        $column3 = 135;
        if (isset($this->_data["identificadores"])) {
            $this->Ln();
            $this->SetFont($this->_font, 'B', $this->_fontSize);
            $this->MultiCell($column1, 0, "CLAVE/COMPL. IDENTIFICADOR", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($column2, 0, "", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($column3, 0, "COMPLEMENTO 1", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($column3, 0, "COMPLEMENTO 2", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($column3, 0, "COMPLEMENTO 3", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->SetFont($this->_font, '', $this->_fontSize);
            foreach ($this->_data["identificadores"] as $item) {
                $this->Ln();
                $this->MultiCell($column1, 0, "", "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($column2, 0, $item["tipoCaso"], "L", "C", 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($column3, 0, trim($item["caso1"]), "L", "L", 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($column3, 0, trim($item["caso2"]), "L", "L", 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($column3, 0, trim($item["caso3"]), "LR", "L", 1, 0, '', '', true, 0, false, true, 0);
            }
        }
    }

    /**
     * EXTRACCIONES O DESCARGOS
     */
    protected function _extracciones() {
        $this->Ln();
        $this->SetFillColor($this->_shade[0], $this->_shade[1], $this->_shade[2]);
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(0, 0, "DESCARGOS", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $column1 = 190;
        $column2 = 190;
        $column3 = 192;
        $this->Ln();
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->MultiCell($column1, 0, "NUM. PEDIMENTO ORIGINAL", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column2, 0, "FECHA DE OPERACIÓN ORIGINAL", "T", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($column3, 0, "CVE. PEDIMENTO ORIGINAL", "TR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        foreach ($this->_data["extracciones"] as $item) {
            $this->Ln();
            $this->SetFont($this->_font, '', $this->_fontSize);
            $this->MultiCell($column1, 0, substr($item["year"], -2) . ' ' . substr($item["aduana"], 0, 2) . " " . $item["patente"] . " " . $item["pedimento"], "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($column2, 0, $item["fecha"], 0, 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($column3, 0, $item["regimen"], "R", 'C', 1, 0, '', '', true, 0, false, true, 0);
        }
    }

    /**
     * OBSERVACIONES
     */
    protected function _observaciones() {
        $this->Ln();
        $this->SetFillColor($this->_shade[0], $this->_shade[1], $this->_shade[2]);
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(0, 0, "OBSERVACIONES", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->Ln();
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->MultiCell(0, 0, $this->_data["observaciones"], "TLBR", 'L', 1, 0, '', '', true, 0, false, true, 0);
    }

    /**
     * DATOS DE LA PARTIDA
     */
    protected function _datosPartida($item) {
        $this->Ln();
        $this->SetFont($this->_font, '', $this->_fontSmall);
        $this->MultiCell($this->_cp[0], 0, $item["secuencia"], "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[1], 0, $item["fraccion"], "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[2], 0, ($item["subdivision"] == "0") ? "" : "", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[3], 0, $item["vinculacion"], "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[4], 0, $item["valoracion"], "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[5], 0, $item["umc"], "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[6], 0, number_format($item["cantidad"], 3), "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[7], 0, $item["umt"], "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[8], 0, number_format($item["tarifa"], 3), "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[9], 0, $item["paisComprador"], "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[10], 0, $item["paisOrigen"], "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[11], 0, "IGI", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[12], 0, ($item["igiTasa"] == 0) ? 'Ex.' : number_format($item["igiTasa"], 4, '.', ''), "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[13], 0, "1", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[14], 0, $item["igiFp"], "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[15], 0, $item["igi"], "TLR", 'R', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell($this->_cp[0], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[16], 0, $item["descripcion"], "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[11], 0, "IVA", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[12], 0, number_format($item["ivaTasa"], 4, '.', ''), "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[13], 0, "1", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[14], 0, $item["ivaFp"], "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[15], 0, $item["iva"], "LR", 'R', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell($this->_cp[0], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[17], 0, round($item["valorComercial"] * $this->_data["tipoCambio"]), "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[18], 0, round($item["valorComercial"] * $this->_data["tipoCambio"]), "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[19], 0, number_format(($item["valorComercial"] * $this->_data["tipoCambio"]) / $item["cantidad"], 4), "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[20], 0, "", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[21], 0, "", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[11], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[12], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[13], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[14], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_cp[15], 0, "", "LR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        if (isset($item["observaciones"]) && $item["observaciones"] !== false) {
            if (isset($item["observaciones"]["observacion"])) {
                $this->Ln();
                $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
                $this->MultiCell($this->_cp[0], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->SetFillColor($this->_shade[0], $this->_shade[1], $this->_shade[2]);
                $this->MultiCell($this->_cp[16], 0, "OBSERVACIONES A NIVEL PARTIDA", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
                $this->MultiCell($this->_cp[11], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($this->_cp[12], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($this->_cp[13], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($this->_cp[14], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($this->_cp[15], 0, "", "LR", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->Ln();
                $this->MultiCell($this->_cp[0], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($this->_cp[16], 0, preg_replace("/\r|\n|\s+/", " ", strtoupper($item["observaciones"]["observacion"])), "TLBR", 'L', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($this->_cp[11], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($this->_cp[12], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($this->_cp[13], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($this->_cp[14], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($this->_cp[15], 0, "", "LR", 'C', 1, 0, '', '', true, 0, false, true, 0);
            }
        }
        if (isset($item["identificadores"]) && $item["identificadores"] !== false) {
            $this->Ln();
            $this->MultiCell($this->_cp[0], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(40, 0, "IDENTIF.", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(110, 0, "COMPLEMENTO 1", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(110, 0, "COMPLEMENTO 2", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(138, 0, "COMPLEMENTO 3", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($this->_cp[11], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($this->_cp[12], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($this->_cp[13], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($this->_cp[14], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($this->_cp[15], 0, "", "LR", 'C', 1, 0, '', '', true, 0, false, true, 0);
            foreach ($item["identificadores"] as $iden) {
                $this->Ln();
                $this->MultiCell($this->_cp[0], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell(40, 0, $iden["tipoCaso"], "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell(110, 0, $iden["caso1"], "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell(110, 0, $iden["caso2"], "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell(138, 0, $iden["caso3"], "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($this->_cp[11], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($this->_cp[12], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($this->_cp[13], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($this->_cp[14], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($this->_cp[15], 0, "", "LR", 'C', 1, 0, '', '', true, 0, false, true, 0);
            }
        }
    }

    /**
     * FRACCIONES O PARTIDAS
     */
    protected function _partidas() {
        $this->_verificarSiguientePagina(300);
        $this->Ln();
        $this->SetFillColor($this->_shade[0], $this->_shade[1], $this->_shade[2]);
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(0, 0, "PARTIDAS", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->_encabezadoPartidas();
        foreach ($this->_data["fracciones"] as $item) {
            $this->_verificarPartidasSiguientePagina(250);
            $this->_datosPartida($item);
        }
    }

    /**
     * FACTURAS
     */
    protected function _facturas() {
//        $this->_verificarFacturasSiguientePagina(250);
        $this->Ln();
        $this->SetFillColor($this->_shade[0], $this->_shade[1], $this->_shade[2]);
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(0, 0, "DATOS DEL PROVEEDOR O COMPRADOR", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->_titulosProveedor();
        foreach ($this->_data["proveedores"] as $item) {
            $this->_datosProveedor($item);
            if (isset($item["facturas"]) && $item["facturas"] !== false) {
                $this->_titulosFactura();
                foreach ($item["facturas"] as $invoice) {
                    $this->_datosFacturas($invoice);
                }
            }
        }
    }

    /**
     * LIQUIDACION
     */
    protected function _liquidacion() {
        $column1 = 59;
        $column2 = 20;
        $column3 = 65;
        $this->Ln();
        $this->SetFont($this->_font, 'B', $this->_fontSize);
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
        if (isset($this->_data["liquidacion"]) && $this->_data["liquidacion"] !== false) {
            $this->Ln();
            if (isset($this->_data["liquidacion"]['impuestos'][0])) {
                $this->SetFont($this->_font, '', $this->_fontSize);
                $this->MultiCell($column1, 0, $this->_data["liquidacion"]['impuestos'][0]['impuesto'], "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($column2, 0, $this->_data["liquidacion"]['impuestos'][0]['fp'], "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($column3, 0, $this->_data["liquidacion"]['impuestos'][0]['cantidad'], "TL", 'R', 1, 0, '', '', true, 0, false, true, 0);
            } else {
                $this->SetFont($this->_font, '', $this->_fontSize);
                $this->MultiCell($column1, 0, '', "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($column2, 0, '', "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($column3, 0, '', "TL", 'R', 1, 0, '', '', true, 0, false, true, 0);
            }
            if (isset($this->_data["liquidacion"]['impuestos'][3])) {
                $this->SetFont($this->_font, '', $this->_fontSize);
                $this->MultiCell($column1, 0, $this->_data["liquidacion"]['impuestos'][3]['impuesto'], "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($column2, 0, $this->_data["liquidacion"]['impuestos'][3]['fp'], "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($column3, 0, $this->_data["liquidacion"]['impuestos'][3]['cantidad'], "TL", 'R', 1, 0, '', '', true, 0, false, true, 0);
            } else {
                $this->SetFont($this->_font, '', $this->_fontSize);
                $this->MultiCell($column1, 0, '', "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($column2, 0, '', "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($column3, 0, '', "TL", 'R', 1, 0, '', '', true, 0, false, true, 0);
            }
            $this->SetFont($this->_font, 'B', $this->_fontSize);
            $this->MultiCell(61, 0, "EFECTIVO", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->SetFont($this->_font, '', $this->_fontSize);
            if (isset($this->_data["liquidacion"]['efectivo'])) {
                $this->MultiCell(80, 0, $this->_data["liquidacion"]['efectivo'], "TLR", 'R', 1, 0, '', '', true, 0, false, true, 0);
            } else {
                $this->MultiCell(80, 0, "", "TLR", 'R', 1, 0, '', '', true, 0, false, true, 0);                
            }
            $this->Ln();
            if (isset($this->_data["liquidacion"]['impuestos'][1])) {
                $this->SetFont($this->_font, '', $this->_fontSize);
                $this->MultiCell($column1, 0, $this->_data["liquidacion"]['impuestos'][1]['impuesto'], "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($column2, 0, $this->_data["liquidacion"]['impuestos'][1]['fp'], "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($column3, 0, $this->_data["liquidacion"]['impuestos'][1]['cantidad'], "L", 'R', 1, 0, '', '', true, 0, false, true, 0);
            } else {
                $this->SetFont($this->_font, '', $this->_fontSize);
                $this->MultiCell($column1, 0, '', "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($column2, 0, '', "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($column3, 0, '', "L", 'R', 1, 0, '', '', true, 0, false, true, 0);
            }
            if (isset($this->_data["liquidacion"]['impuestos'][4])) {
                $this->SetFont($this->_font, '', $this->_fontSize);
                $this->MultiCell($column1, 0, $this->_data["liquidacion"]['impuestos'][4]['impuesto'], "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($column2, 0, $this->_data["liquidacion"]['impuestos'][4]['fp'], "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($column3, 0, $this->_data["liquidacion"]['impuestos'][4]['cantidad'], "L", 'R', 1, 0, '', '', true, 0, false, true, 0);
            } else {
                $this->SetFont($this->_font, '', $this->_fontSize);
                $this->MultiCell($column1, 0, '', "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($column2, 0, '', "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($column3, 0, '', "L", 'R', 1, 0, '', '', true, 0, false, true, 0);
            }
            $this->SetFont($this->_font, 'B', $this->_fontSize);
            $this->MultiCell(61, 0, "OTROS", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->SetFont($this->_font, '', $this->_fontSize);
            if (isset($this->_data["liquidacion"]['efectivo'])) {
                $this->MultiCell(80, 0, $this->_data["liquidacion"]['otros'], "TLR", 'R', 1, 0, '', '', true, 0, false, true, 0);
            } else {
                $this->MultiCell(80, 0, "", "TLR", 'R', 1, 0, '', '', true, 0, false, true, 0);                
            }
            $this->Ln();
            if (isset($this->_data["liquidacion"]['impuestos'][2])) {
                $this->SetFont($this->_font, '', $this->_fontSize);
                $this->MultiCell($column1, 0, $this->_data["liquidacion"]['impuestos'][2]['impuesto'], "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($column2, 0, $this->_data["liquidacion"]['impuestos'][2]['fp'], "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($column3, 0, $this->_data["liquidacion"]['impuestos'][2]['cantidad'], "L", 'R', 1, 0, '', '', true, 0, false, true, 0);
            } else {
                $this->SetFont($this->_font, '', $this->_fontSize);
                $this->MultiCell($column1, 0, '', "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($column2, 0, '', "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($column3, 0, '', "L", 'R', 1, 0, '', '', true, 0, false, true, 0);
            }
            if (isset($this->_data["liquidacion"]['impuestos'][5])) {
                $this->SetFont($this->_font, '', $this->_fontSize);
                $this->MultiCell($column1, 0, $this->_data["liquidacion"]['impuestos'][5]['impuesto'], "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($column2, 0, $this->_data["liquidacion"]['impuestos'][5]['fp'], "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($column3, 0, $this->_data["liquidacion"]['impuestos'][5]['cantidad'], "L", 'R', 1, 0, '', '', true, 0, false, true, 0);
            } else {
                $this->SetFont($this->_font, '', $this->_fontSize);
                $this->MultiCell($column1, 0, '', "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($column2, 0, '', "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
                $this->MultiCell($column3, 0, '', "L", 'R', 1, 0, '', '', true, 0, false, true, 0);
            }
            $this->SetFont($this->_font, 'B', $this->_fontSize);
            $this->MultiCell(61, 0, "TOTAL", "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->SetFont($this->_font, '', $this->_fontSize);
            if (isset($this->_data["liquidacion"]['efectivo'])) {
                $this->MultiCell(80, 0, $this->_data["liquidacion"]['total'], "TLR", 'R', 1, 0, '', '', true, 0, false, true, 0);
            } else {
                $this->MultiCell(80, 0, "", "TLR", 'R', 1, 0, '', '', true, 0, false, true, 0);                
            }
        }
    }

    /**
     * CLIENTE
     */
    protected function _cliente() {
        $this->Ln();
        $this->SetFillColor($this->_shade[0], $this->_shade[1], $this->_shade[2]);
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(429, 0, "DATOS DEL IMPORTADOR/EXPORTADOR", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->MultiCell(30, 0, "RFC: ", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->MultiCell(80, 0, $this->_data['cliente']["rfc"], "T", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(319, 0, "NOMBRE, DEMONINACIÓN O RAZÓN SOCIAL", "TR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->MultiCell(30, 0, "CURP:", "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(80, 0, isset($this->_data['cliente']["curp"]) ? $this->_data['cliente']["curp"] : '', 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->MultiCell(319, 0, $this->_data['cliente']["razonSocial"], "R", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $dom = $this->_data['cliente']['domicilio']["calle"] . ' #' . $this->_data['cliente']['domicilio']["numExterior"] . $this->_data['cliente']['domicilio']["numInterior"] . ', ' . $this->_data['cliente']['domicilio']["municipio"] . ', CP ' . $this->_data['cliente']['domicilio']["codigoPostal"] . ', ' . $this->_data['cliente']['domicilio']["pais"];
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(45, 22, "DOMICILIO:", "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->MultiCell(384, 22, $dom, "R", 'L', 1, 0, '', '', true, 0, false, true, 0);
    }

    /**
     * FECHAS Y TASAS
     */
    protected function _fechasTasas() {
        $this->Ln();
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(150, 0, "FECHAS", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($this->_shade[0], $this->_shade[1], $this->_shade[2]);
        $this->MultiCell(279, 0, "TASAS A NIVEL PEDIMENTO", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->MultiCell(75, 0, "ENTRADA", "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->MultiCell(75, 0, $this->_data["fechaEntrada"], 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(93, 0, "CONTRIB", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(93, 0, "CVE. T. TASA", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(93, 0, "TASA", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell(75, 0, "PAGO", "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->MultiCell(75, 0, $this->_data["fechaPago"], 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        if (isset($this->_data['liquidacion']['tasas'][0])) {
            $this->MultiCell(93, 0, $this->_data['liquidacion']['tasas'][0]['impuesto'], "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(93, 0, $this->_data['liquidacion']['tasas'][0]['cve'], "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(93, 0, number_format($this->_data['liquidacion']['tasas'][0]['tasa'], 5), "LR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        } else {
            $this->MultiCell(93, 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(93, 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(93, 0, "", "LR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        }
        $this->Ln();
        if (isset($this->_data["fechaExtraccion"]) && !preg_match('/1899/', $this->_data["fechaExtraccion"])) {
            $this->SetFont($this->_font, 'B', $this->_fontSize);
            $this->MultiCell(75, 0, "EXTRACCIÓN", "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->SetFont($this->_font, '', $this->_fontSize);
            $this->MultiCell(75, 0, $this->_data["fechaExtraccion"], 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        } else {
            $this->MultiCell(75, 0, "", "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(75, 0, "", 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        }
        if (isset($this->_data['liquidacion']['tasas'][1])) {
            $this->MultiCell(93, 0, $this->_data['liquidacion']['tasas'][1]['impuesto'], "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(93, 0, $this->_data['liquidacion']['tasas'][1]['cve'], "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(93, 0, number_format($this->_data['liquidacion']['tasas'][1]['tasa'], 5), "LR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        } else {
            $this->MultiCell(93, 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(93, 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(93, 0, "", "LR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        }
        $this->Ln();
        $this->MultiCell(75, 0, "", "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(75, 0, "", 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        if (isset($this->_data['liquidacion']['tasas'][2])) {
            $this->MultiCell(93, 0, $this->_data['liquidacion']['tasas'][2]['impuesto'], "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(93, 0, $this->_data['liquidacion']['tasas'][2]['cve'], "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(93, 0, number_format($this->_data['liquidacion']['tasas'][2]['tasa'], 5), "LR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        } else {
            $this->MultiCell(93, 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(93, 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(93, 0, "", "LR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        }
        $this->Ln();
        $this->MultiCell(75, 0, "", "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(75, 0, "", 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        if (isset($this->_data['liquidacion']['tasas'][3])) {
            $this->MultiCell(93, 0, $this->_data['liquidacion']['tasas'][3]['impuesto'], "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(93, 0, $this->_data['liquidacion']['tasas'][3]['cve'], "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(93, 0, number_format($this->_data['liquidacion']['tasas'][3]['tasa'], 5), "LR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        } else {
            $this->MultiCell(93, 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(93, 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(93, 0, "", "LR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        }
    }

    /**
     * MARCAS
     */
    protected function _marcas() {
        $this->Ln();
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(170, 0, "MARCAS, NUMEROS Y TOTAL DE BULTOS", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, 'C', $this->_fontSize);
        $this->MultiCell(259, 0, $this->_data["marcas"] . '; ' . $this->_data["bultos"], "TR", 'L', 1, 0, '', '', true, 0, false, true, 0);
    }

    /**
     * INCREMENTABLES
     */
    protected function _incrementables() {
        $this->Ln();
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(99, 0, "VAL. SEGUROS", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(70, 0, "SEGUROS", "T", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(70, 0, "FLETES", "T", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(70, 0, "EMBALAJES", "T", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(120, 0, "OTROS INCREMENTABLES", "TR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->MultiCell(99, 0, ($this->_data["seguros"] * $this->_data["tipoCambio"]), "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(70, 0, ($this->_data["seguros"] * $this->_data["tipoCambio"]), 0, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(70, 0, ($this->_data["fletes"] * $this->_data["tipoCambio"]), 0, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(70, 0, ($this->_data["embalajes"] * $this->_data["tipoCambio"]), 0, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(120, 0, ($this->_data["otrosIncrementables"] * $this->_data["tipoCambio"]), "R", 'C', 1, 0, '', '', true, 0, false, true, 0);
    }

    /**
     * DATOS PARA PAGO
     * @param int $height
     */
    protected function _datosPago($height) {
        if (isset($this->_data['pago']) && $this->_data['pago'] !== false) {
            $pago = "*** PAGO ELECTRÓNICO ***\n";
            if (isset($this->_data['pago']['nombreBanco'])) {
                $pago .= utf8_encode($this->_data['pago']['nombreBanco']) . "\n";
            }
            if (isset($this->_data['pago']['patente'])) {
                $pago .= "{$this->_data['pago']['patente']}\n";
            }
            if (isset($this->_data['pago']['operacion'])) {
                $pago .= "OP. {$this->_data['pago']['operacion']}\n";
            }
            if (isset($this->_data['fechaPago'])) {
                $pago .= "{$this->_data["fechaPago"]}\n";
            }
            if (isset($this->_data['pago']['firmaBanco'])) {
                $pago .= "{$this->_data["pago"]["firmaBanco"]}\n";
            }
            if (isset($this->_data["liquidacion"]["total"])) {
                $pago .= "IMPORTE TOTAL: {$this->_numberCurrency($this->_data["liquidacion"]['total'])}\n";
            }
            if (isset($this->_data["liquidacion"]["otros"])) {
                $pago .= "CONTRIB. OTRAS F.P.: {$this->_numberCurrency($this->_data["liquidacion"]['otros'])}\n";
            }
            if (isset($this->_data["pago"]["efectivo"])) {
                $pago .= "CONTRIB. EFECTIVO: {$this->_numberCurrency($this->_data['pago']['efectivo'] - $this->_data["cnt"])}\n";
            }
            if (isset($this->_data["liquidacion"]["total"])) {
                $pago .= "TOTAL DE CONTRIB.: " . $this->_numberCurrency($this->_data["liquidacion"]['total'] - $this->_data["cnt"]) . "\n";
            }
            if (isset($this->_data["cnt"])) {
                $pago .= "CONTRAPRESTACIONES: {$this->_numberCurrency($this->_data["cnt"])}";
            }
            $this->MultiCell(143, $height, $pago, "TLR", 'L', 1, 1, '', '', true, 0, false, false, 0);
        } else {
            $this->MultiCell(143, $height, "", "TLR", 'L', 1, 1, '', '', true, 0, false, false, 0);
        }
    }

    /**
     * DATOS DE ENCABEZADO
     */
    protected function _datosEncabezado() {
        $this->Ln();
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(70, 0, "NUM. PEDIMENTO:", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->_numPedimento = substr($this->_data["fechaPago"], -2) . ' ' . substr($this->_data["aduana"], 0, 2) . ' ' . $this->_data["patente"] . ' ' . $this->_data["pedimento"];
        $this->MultiCell(90, 0, $this->_numPedimento, "T", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(50, 0, "T. OPER:", "T", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->MultiCell(30, 0, $this->_data["tipoOperacion"], "T", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(70, 0, "CVE. PEDIMENTO:", "T", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->MultiCell(40, 0, $this->_data["cvePedimento"], "T", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(59, 0, "REGIMEN:", "T", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->MultiCell(20, 0, $this->_data["regimen"], "T", 'R', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($this->_shade[0], $this->_shade[1], $this->_shade[2]);
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(143, 0, "CERTIFICACIONES", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->MultiCell(80, 0, "DESTINO/ORIGEN:", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->MultiCell(30, 0, $this->_data["destinoOrigen"], "T", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(70, 0, "TIPO CAMBIO:", "T", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->MultiCell(35, 0, number_format($this->_data["tipoCambio"], 5), "T", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(55, 0, "PESO BRUTO:", "T", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->MultiCell(70, 0, number_format($this->_data["pesoBruto"], 3), "T", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(55, 0, "ADUANA E/S:", "T", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->MultiCell(34, 0, $this->_data["aduanaEntrada"], "T", 'R', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, '', $this->_fontSmall);
    }

    /**
     * MEDIOS DE TRANSPORTE
     */
    protected function _medios($y) {
        $this->SetY($y);
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(230, 0, "MEDIOS DE TRANSPORTE:", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(110, 0, "VALOR DOLARES:", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->MultiCell(89, 0, number_format($this->_data["valorDolares"], 2, '.', ''), "TR", 'R', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(78, 0, "ENTRADA/SALIDA:", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(76, 0, "ARRIBO:", 0, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(76, 0, "SALIDA:", 0, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(110, 0, "VALOR ADUANA:", "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->MultiCell(89, 0, $this->_data["valorAduana"], "R", 'R', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell(78, 0, $this->_data["transporteEntrada"], "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(76, 0, $this->_data["transporteArribo"], 0, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(76, 0, $this->_data["transporteSalida"], 0, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(110, 0, "PRECIO PAGADO/VALOR:", "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(89, 0, "", "R", 'R', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell(230, 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(110, 0, "COMERCIAL:", "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->MultiCell(89, 0, $this->_data["valorComercial"], "R", 'R', 1, 0, '', '', true, 0, false, true, 0);
    }

    /**
     * FIN DE PEDIMENTO
     */
    protected function _finDePedimento() {
        $this->Ln();
        $this->MultiCell(32, 0, "****", "T", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(130, 0, "FIN DE PEDIMENTO", "T", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(140, 0, "**** NUM. TOTAL DE PARTIDAS:", "T", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(80, 0, count($this->_data["fracciones"]), "T", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(130, 0, "**** CLAVE PREVALIDADOR:", "T", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(30, 0, "010", "T", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(30, 0, "****", "T", 'C', 1, 0, '', '', true, 0, false, true, 0);
    }

    /**
     * DIBUJAR EL CODIGO DE BARRAS
     */
    protected function _drawCodeBar($xpos, $ypos, $height, $width) {
        $this->Ln();
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(90, 80, "CODIGO DE ACEPTACIÓN\n" . $this->_data["firmaValidacion"], "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        if (isset($this->_data["codigoBarras"]) && $this->_data["codigoBarras"] == true) {
//            $this->_codigoDeBarras($xpos, $ypos, $height, $width, $this->_data["patente"], $this->_data["pedimento"], $this->_data["cvePedimento"], $this->_data['cliente']["rfc"], $this->_data["
//            firmaValidacion"], ($this->_data['pago']['efectivo'] - $this->_data["cnt"]), $this->_data['pago']['total'], $this->_data['pago']['otros'], $this->_data['dta']);
            $this->MultiCell(209, 77, "", "LTR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        } else {
            $this->MultiCell(209, 60, "", "LTR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        }
        if (isset($this->_data["aduanaNombre"]) && $this->_data["aduanaNombre"] != '') {
            $nombreAduana = "\n" . utf8_encode($this->_data["aduanaNombre"]);
        } else {
            $nombreAduana = '';
        }
        $this->MultiCell(130, 60, "CLAVE DE LA SECCIÓN ADUANERA DE DESPACHO: " . $this->_data["aduana"] . $nombreAduana, "TR", 'L', 1, 0, '', '', true, 0, false, true, 0);
    }

    protected function _titulosProveedor() {
        $this->Ln();
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->MultiCell($this->_inc["col"][1], 0, "ID. FISCAL", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_inc["col"][2], 0, "NOMBRE, DENOMINACIÓN O RAZON SOCIAL", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_inc["col"][3], 0, "DOMICILIO:", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_inc["col"][4], 0, "VINCULACIÓN", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
    }

    protected function _datosProveedor($item) {
        $this->Ln();
        $this->SetFont($this->_font, '', $this->_fontSize);
        $address = $item["domicilio"]["calle"] . ', ' . $item["domicilio"]["numExterior"] . ', ' . ', ' . $item["domicilio"]["numInterior"] . ', ' . $item["domicilio"]["municipio"] . ', ' . $item["domicilio"]["pais"] . ', CP ' . $item["domicilio"]["codigoPostal"];
        $max = max($this->getNumLines($item["nomProveedor"], $this->_inc["col"][2]), $this->getNumLines($address, $this->_inc["col"][3]));
        $this->MultiCell($this->_inc["col"][1], $max * $this->_inc["row"][1], $item["taxId"], "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_inc["col"][2], $max * $this->_inc["row"][1], $item["nomProveedor"], "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_inc["col"][3], $max * $this->_inc["row"][1], $address, "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_inc["col"][4], $max * $this->_inc["row"][1], "NO", "LR", 'C', 1, 0, '', '', true, 0, false, true, 0);
    }

    protected function _titulosFactura() {
        $this->Ln();
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell($this->_inc["coli"][1], 0, "NUMERO DE ACUSE DE VALOR", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_inc["coli"][2], 0, "FECHA", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_inc["coli"][3], 0, "INCOTERM", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_inc["coli"][4], 0, "MONEDA FACT.", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_inc["coli"][5], 0, "VAL. MON. FACT.", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_inc["coli"][6], 0, "FACTOR MON. FACT.", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($this->_inc["coli"][7], 0, "VAL.DOLARES", "TLR", 'C', 1, 0, '', '', true, 0, false, true, 0);
    }

    protected function _datosFacturas($invoice) {
        $this->SetFont($this->_font, '', $this->_fontSize);
        if (trim($invoice["cove"]) === '') {
            $this->Ln();
            $this->MultiCell($this->_inc["coli"][1], 0, $invoice["numFactura"], "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($this->_inc["coli"][2], 0, $invoice["fechaFactura"], "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($this->_inc["coli"][3], 0, $invoice["incoterm"], "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($this->_inc["coli"][4], 0, $invoice["divisa"], "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($this->_inc["coli"][5], 0, number_format((float) $invoice["valorMonExt"], 2), "L", 'R', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($this->_inc["coli"][6], 0, number_format((float) $invoice["factorEquivalencia"], 8), "L", 'R', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($this->_inc["coli"][7], 0, number_format((float) $invoice["valorDolares"], 2), "LR", 'R', 1, 0, '', '', true, 0, false, true, 0);
        } else {
            $this->Ln();
            $this->MultiCell($this->_inc["coli"][1], 0, $invoice["numFactura"], "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($this->_inc["coli"][2], 0, $invoice["fechaFactura"], "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($this->_inc["coli"][3], 0, $invoice["incoterm"], "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($this->_inc["coli"][4], 0, $invoice["divisa"], "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($this->_inc["coli"][5], 0, number_format((float) $invoice["valorMonExt"], 2), "L", 'R', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($this->_inc["coli"][6], 0, number_format($invoice["factorEquivalencia"], 8), "L", 'R', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($this->_inc["coli"][7], 0, number_format((float) $invoice["valorDolares"], 2), "LR", 'R', 1, 0, '', '', true, 0, false, true, 0);
            $this->Ln();
            $this->MultiCell($this->_inc["coli"][1], 0, $invoice["cove"], "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($this->_inc["coli"][2], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($this->_inc["coli"][3], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($this->_inc["coli"][4], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($this->_inc["coli"][5], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($this->_inc["coli"][6], 0, "", "L", 'C', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($this->_inc["coli"][7], 0, "", "LR", 'C', 1, 0, '', '', true, 0, false, true, 0);
        }
    }

    protected function _transporte() {
        $this->Ln();
        $this->SetFillColor($this->_shade[0], $this->_shade[1], $this->_shade[2]);
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(70, 0, "TRANSPORTE:", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->MultiCell(70, 0, "IDENTIFICACION:", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->MultiCell(212, 0, $this->_data["transporte"]["placas"], "T", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(40, 0, "PAIS:", "T", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->MultiCell(180, 0, $this->_data["transporte"]["pais"], "TR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(70, 0, "TRANSPORTISTA:", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->MultiCell(282, 0, $this->_data["transporte"]["transportista"], "T", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(40, 0, "RFC:", "T", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->MultiCell(180, 0, "", "TR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(70, 0, "CURP:", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->MultiCell(70, 0, "", "T", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(110, 0, "DOMICILIO/CIUDAD/ESTADO:", "T", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->MultiCell(322, 0, (isset($this->_data["transporte"]["domicilio"])) ? $this->_data["transporte"]["domicilio"] : '', "TR", 'L', 1, 0, '', '', true, 0, false, true, 0);
    }

    protected function _candados() {
        $this->Ln();
        $this->SetFillColor($this->_shade[0], $this->_shade[1], $this->_shade[2]);
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(90, 0, "NUMERO DE CANDADO:", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->MultiCell(80, 0, (isset($this->_data["candados"]['candado1']) && $this->_data["candados"]['candado1'] != '') ? $this->_data["candados"]['candado1'] : '', "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(80, 0, (isset($this->_data["candados"]['candado2']) && $this->_data["candados"]['candado2'] != '') ? $this->_data["candados"]['candado2'] : '', "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(80, 0, (isset($this->_data["candados"]['candado3']) && $this->_data["candados"]['candado3'] != '') ? $this->_data["candados"]['candado3'] : '', "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(80, 0, (isset($this->_data["candados"]['candado4']) && $this->_data["candados"]['candado4'] != '') ? $this->_data["candados"]['candado4'] : '', "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(80, 0, (isset($this->_data["candados"]['candado5']) && $this->_data["candados"]['candado5'] != '') ? $this->_data["candados"]['candado5'] : '', "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(82, 0, (isset($this->_data["candados"]['candado6']) && $this->_data["candados"]['candado6'] != '') ? $this->_data["candados"]['candado6'] : '', "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $rowHeight = 15;
        $this->MultiCell(80, $rowHeight, "1RA. REVISION", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(492, $rowHeight, "", "TR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell(80, $rowHeight, "2DA. REVISION", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(492, $rowHeight, "", "TR", 'L', 1, 0, '', '', true, 0, false, true, 0);
    }

    protected function _guias() {
        $this->Ln();
        $this->SetFillColor($this->_shade[0], $this->_shade[1], $this->_shade[2]);
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(145, 0, "NUMERO(GUIA/ORDEN EMBARQUE)/ID:", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->SetFont($this->_font, '', $this->_fontSize);
        if (isset($this->_data["guias"][0])) {
            $this->MultiCell(92, 0, $this->_data["guias"][0]['guia'], "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(20, 0, $this->_data["guias"][0]['tipoGuia'], "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        } else {
            $this->MultiCell(92, 0, '', "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(30, 0, '', "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        }
        if (isset($this->_data["guias"][1])) {
            $this->MultiCell(85, 0, $this->_data["guias"][1]['guia'], "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(20, 0, $this->_data["guias"][1]['tipoGuia'], "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        } else {
            $this->MultiCell(85, 0, '', "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(20, 0, '', "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        }
        if (isset($this->_data["guias"][2])) {
            $this->MultiCell(85, 0, $this->_data["guias"][2]['guia'], "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(20, 0, $this->_data["guias"][2]['tipoGuia'], "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        } else {
            $this->MultiCell(85, 0, '', "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(20, 0, '', "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        }
        if (isset($this->_data["guias"][3])) {
            $this->MultiCell(85, 0, $this->_data["guias"][3]['guia'], "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(20, 0, $this->_data["guias"][3]['tipoGuia'], "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        } else {
            $this->MultiCell(85, 0, '', "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell(20, 0, '', "TLR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        }
    }

    protected function _contenedores() {
        $this->Ln();
        $this->SetFillColor($this->_shade[0], $this->_shade[1], $this->_shade[2]);
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(90, 0, "NUMERO / TIPO:", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->MultiCell(90, 0, (isset($this->_data["contenedores"]['numContenedor']) && $this->_data["contenedores"]['numContenedor'] != '') ? $this->_data["contenedores"]['numContenedor'] : '', "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(30, 0, (isset($this->_data["contenedores"]['tipoContenedor']) && $this->_data["contenedores"]['tipoContenedor'] != '') ? $this->_data["contenedores"]['tipoContenedor'] : '', "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(90, 0, '', "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(30, 0, '', "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(90, 0, '', "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(30, 0, '', "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(90, 0, '', "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(32, 0, '', "TRL", 'L', 1, 0, '', '', true, 0, false, true, 0);
    }

    public function Footer() {
        $this->SetFont("helvetica", '', 6.5);
        $this->SetY(-138, true);
        $this->SetLineStyle(array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(70, 70, 70)));
        $this->SetFillColor($this->_shaden[0], $this->_shaden[1], $this->_shaden[2]);
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(340, 0, "AGENTE ADUANAL, APODERADO ADUANAL O DE ALMACEN", "TL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(232, 40, "DECLARO BAJO PROTESTA DE DECIR VERDAD, EN LOS TERMINOS DE LO DISPUESTO POR EL ARTICULO 81 DE LA LEY ADUANERA: PATENTE O AUTORIZACIÓN: " . $this->_data["patente"], "TLR", 'J', 1, 1, '', '', true, 0, false, true, 0);
        $this->SetY(-128, true);
        $this->MultiCell(100, 0, "NOMBRE O RAZ. SOC:", "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->MultiCell(240, 0, $this->_data["agente"]["nombre"] . ' ' . $this->_data["agente"]["rfc"], "R", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell(340, 0, $this->_data["sociedad"]["nombre"], "LR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(50, 0, "RFC:", "L", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->MultiCell(140, 0, $this->_data["sociedad"]["rfc"], 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(50, 0, "CURP:", 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->MultiCell(100, 0, $this->_data["agente"]["curp"], "R", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(180, 0, "NUMERO DE SERIE DEL CERTIFICADO:", "TL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->MultiCell(160, 0, $this->_data["agente"]["serie"], "TR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(232, 0, "FIRMA AUTOGRAFA", "RL", 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFont($this->_font, 'B', $this->_fontSize);
        $this->MultiCell(120, 18, "e.firma:", "TBL", 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFont($this->_font, '', $this->_fontSize);
        $this->MultiCell(452, 18, $this->_data["firmaDigital"], "TBR", 'L', 1, 0, '', '', true, 0, false, true, 0);
        if ($this->_second == false) {
            $this->Ln();
            $this->SetFont("helvetica", '', 6);
            $this->MultiCell(572, 0, "El pago de las contribuciones puede realizarse mediante el servicio de \"Pago Electrónico Centralizado Aduanero\" (PECA), conforme a lo establecido en la regla 1.6.2., con la posibilidad de que la cuenta bancaria de la persona que contrate los servicios sea afectada directamente por el Banco. El agente o apoderado aduanal que utilice el servicio de PECA, deberá imprimir la certificación bancaria en el campo correspondiente del pedimento o en el documento oficial, conforme al Apéndice 20 \"Certificación de Pago Electrónico Centralizado\" del Anexo 22. El Importador-Exportador podrá solicitar la certificación de la información contenida en este pedimento en: Administración General de Aduanas, Administración de Operación Aduanera \"7\" Av. Hidalgo Núm. 77. Módulo IV, P.B., Col. Guerrero C.P. 06300. México, D.F.", "TLRB", 'L', 1, 0, '', '', true, 0, false, true, 0);
        } else {
            $this->Ln();
            $this->SetFont("helvetica", '', 6);
            $this->MultiCell(572, 25, "", "TLRB", 'L', 1, 0, '', '', true, 0, false, true, 0);
        }
        $this->Ln();
        $this->SetFont("helvetica", '', 6.5);
        $this->MultiCell(170, 20, 'COPIA: ' . $this->_copia((int) $this->_data["copia"]), 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(400, 20, 'DESTINO/ORIGEN: ' . $this->_destino((int) $this->_data["destinoOrigen"]), 0, 'R', 1, 0, '', '', true, 0, false, true, 0);
    }

}
