<?php

require_once "Spout/Autoloader/autoload.php";

use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Border;
use Box\Spout\Writer\Style\BorderBuilder;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;

/**
 * Description of Reportes
 *
 * @author Jaime
 */
class OAQ_ExcelReportes {

    protected $initRow;
    protected $excel;
    protected $range;
    protected $title;
    protected $titles;
    protected $data;
    protected $filename;
    protected $appConfig;
    protected $style = array(
        "font" => array(
            "bold" => true,
            "name" => "Arial",
            "size" => 10,
            "color" => array("rgb" => "FFFFFF")
        ),
        "alignment" => array(
            "horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ),
        "borders" => array(
            "allborders" => array(
                "style" => PHPExcel_Style_Border::BORDER_MEDIUM,
                "color" => array("rgb" => "222222"),
            )
        ),
        "fill" => array(
            "type" => PHPExcel_Style_Fill::FILL_SOLID,
            "color" => array("rgb" => "538dd5")
        )
    );
    protected $styleh = array(
        "fill" => array(
            "type" => PHPExcel_Style_Fill::FILL_SOLID,
            "color" => array("rgb" => "c5d9f1")
        )
    );
    protected $stylec = array(
        "font" => array(
            "name" => "Arial",
            "size" => 10,
            "color" => array("rgb" => "222222")
        ),
        "alignment" => array(
            "horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
        ),
    );

    function getStyle() {
        return $this->style;
    }

    function getStyleh() {
        return $this->styleh;
    }

    function getStylec() {
        return $this->stylec;
    }

    function setFilename($filename) {
        $this->filename = $filename;
    }

    function setData($data) {
        $this->data = $data;
    }

    function setTitles($titles) {
        $this->titles = $titles;
    }

    function __construct() {
        $this->excel = new PHPExcel();
        $this->appConfig = new Application_Model_ConfigMapper();
    }

    protected function _autosize($i) {
        $this->excel->getActiveSheet()->getStyle($this->range[0] . ($this->initRow + 1) . ":{$this->range[1]}{$i}")->applyFromArray($this->stylec);
        foreach (range($this->range[0], $this->range[1]) as $columnID) {
            $this->excel->getActiveSheet()
                    ->getColumnDimension($columnID)
                    ->setAutoSize(true);
        }
    }

    protected function _ini($sheet = null) {
        if ($sheet !== null) {
            $this->excel->createSheet();
            $this->excel->setActiveSheetIndex($sheet);
        } else {
            $this->excel->setActiveSheetIndex(0);
        }
        $this->excel->getActiveSheet()->setTitle($this->title);
        $this->excel->getActiveSheet()->fromArray($this->titles, null, "{$this->range[0]}{$this->initRow}");
        $this->excel->getActiveSheet()->getStyle("{$this->range[0]}{$this->initRow}:{$this->range[1]}{$this->initRow}")->applyFromArray($this->style);
    }

    public function reporteCoves() {
        $this->initRow = 1;
        $i = $this->initRow;
        $this->range = array("A", "G");
        $this->title = "COVES";
        $this->titles = array("Solicitud", "RFC Solicitante", "Operación", "Referencia", "Factura", "COVE", "Fecha");
        $this->_ini();
        $i++;
        foreach ($this->data as $item) {
            $this->excel->getActiveSheet()->setCellValue("A{$i}", $item["solicitud"]);
            $this->excel->getActiveSheet()->setCellValue("B{$i}", $item["rfc"]);
            $this->excel->getActiveSheet()->setCellValue("C{$i}", $item["aduana"] . "-" . $item["patente"] . "-" . $item["pedimento"]);
            $this->excel->getActiveSheet()->setCellValue("D{$i}", $item["referencia"]);
            $this->excel->getActiveSheet()->setCellValue("E{$i}", $item["factura"]);
            $this->excel->getActiveSheet()->setCellValue("F{$i}", $item["cove"]);
            $this->excel->getActiveSheet()->setCellValue("G{$i}", date("d/m/Y", strtotime($item["actualizado"])));
            $i++;
        }
        $this->_autosize($i);
    }

    public function reporteCargoQuin() {
        $this->initRow = 1;
        $i = $this->initRow;
        $this->range = array("A", "CA");
        $this->title = "Assessment PN";
        $this->titles = array("Month", "Customs Entry Form", "Importation Date", "Customs", "Supplier", "Invoice #", "AWB", "P.O.", "P.N.", "Quantity", "Item Cost", "Weight kg", "Arrival", "Carrier", "Packages", "Date Crossing", "Vehicle", "Plates", "Incoterm", "#Invoice", "Invoice Date", "Administrative Fees MXN (without IVA)", "Administrative Fees Assessment by Invoice and AWB", "Administrative Fees Assessment by P.N.", "Extra Freight", "National Freight Assessment by Invoice and AWB", "National Freight Assessment by P.N.", "International Freight", "International Freight Assessment by Invoice and AWB", "International Freight Assessment by P.N.", "Invoice Revalidación", "Total without IVA", "Total with IVA", "Revalidación Assessment by Invoice and AWB", "Revalidación Assessment by P.N.", "Invoice Previo", "Total without IVA", "Total with IVA", "Previo Assessment by Invoice and AWB", "Previo Assessment by P.N.", "Invoice Manejo", "Total without IVA", "Total with IVA", "Manejo Assessment by Invoice and AWB", "Manejo Assessment by P.N.", "Invoice Almacenaje", "Total without IVA", "Total with IVA", "Almacenaje Assessment by Invoice and AWB", "Almacenaje Assessment by P.N.", "Invoice Custodia", "Total without IVA", "Total with IVA", "Custodia Assessment by Invoice and AWB", "Custodia Assessment by P.N.", "Invoice Valores", "Total without IVA", "Total with IVA", "Valores Assessment by Invoice and AWB", "Valores Assessment by P.N.", "Invoice Fleje", "Total without IVA", "Total with IVA", "Fleje Assessment by Invoice and AWB", "Fleje Assessment by P.N.", "PREV MXN", "PREV Assessment by Invoice and AWB", "PREV Assessment by P.N.", "DTA MXN", "DTA Assessment by Invoice and AWB", "DTA Assessment by P.N.", "CNT MXN", "CNT Assessment by Invoice and AWB", "CNT Assessment by P.N.", "Total Logistics Unit Cost.", "Validation by P.N.", "Total SUM validation per AWB", "Total Custom Cost USD", "Total Invoice Cost MXN");
        $this->_ini();
        $i++;
        foreach ($this->data as $item) {
            $first = true;
            $initp = $i;
            $sum = '$J' . ($item["totalRegistros"] + $initp);
            $this->excel->getActiveSheet()->setCellValue("I" . ($item["totalRegistros"] + $initp), $item["totalRegistros"]);
            $this->excel->getActiveSheet()->setCellValue("J" . ($item["totalRegistros"] + $initp), $item["sumatoria"]);
            foreach ($item["facturas"] as $fact) {
                $firsti = true;
                if(!isset($fact["partes"]) || empty($fact["partes"])) {
                    continue;
                }
                foreach ($fact["partes"] as $part) {
                    if(!isset($part["cantidadFactura"])) {
                        continue;
                    }
                    $flete = (float) ($item["fletes"] * $item["tipoCambio"]);
                    $this->excel->getActiveSheet()->setCellValue("A{$i}", ($first == true) ? $item["mes"] : '');
                    $this->excel->getActiveSheet()->setCellValue("B{$i}", ($first == true) ? $item["operacion"] : '');
                    $this->excel->getActiveSheet()->setCellValue("C{$i}", ($first == true) ? $item["fechaImportacion"] : '');
                    $this->excel->getActiveSheet()->setCellValue("D{$i}", ($first == true) ? $item["aduana"] : '');
                    $this->excel->getActiveSheet()->setCellValue("E{$i}", ($firsti == true) ? $fact["proveedor"] : '');
                    $this->excel->getActiveSheet()->setCellValue("F{$i}", ($firsti == true) ? $fact["numFactura"] : '');
                    $this->excel->getActiveSheet()->setCellValueExplicit("G{$i}", ($first == true) ? str_replace(array(' '), '', $item["guias"][0]["guia"]) : '', PHPExcel_Cell_DataType::TYPE_STRING);
                    $this->excel->getActiveSheet()->setCellValueExplicit("I{$i}", isset($part["numParte"]) ? $part["numParte"] : '', PHPExcel_Cell_DataType::TYPE_STRING);
                    $this->excel->getActiveSheet()->setCellValue("J{$i}", isset($part["cantidadFactura"]) ? $part["cantidadFactura"] : '');
                    $this->excel->getActiveSheet()->setCellValue("K{$i}", number_format($part["valorComercial"], 4));
                    $this->excel->getActiveSheet()->setCellValue("L{$i}", ($first == true) ? $item["peso"] : '');
                    $this->excel->getActiveSheet()->setCellValue("M{$i}", ($first == true) ? $item["fechaEntrada"] : '');
                    $this->excel->getActiveSheet()->setCellValue("N{$i}", ($first == true) ? "DHL" : '');
                    $this->excel->getActiveSheet()->setCellValue("O{$i}", ($first == true) ? $item["bultos"] : '');
                    $this->excel->getActiveSheet()->setCellValue("P{$i}", ($first == true) ? $item["fechaPago"] : '');
                    $this->excel->getActiveSheet()->setCellValue("R{$i}", ($first == true) ? $item["placas"] : '');
                    $this->excel->getActiveSheet()->setCellValue("S{$i}", ($firsti == true) ? $fact["incoterm"] : '');
                    $this->excel->getActiveSheet()->setCellValue("W{$i}", '=$V$' . $initp . '/' . $sum . '*$J' . $i);
                    $this->excel->getActiveSheet()->setCellValue("X{$i}", '=$W$' . $i . '/' . $sum);
                    $this->excel->getActiveSheet()->setCellValue("AB{$i}", ($first == true) ? number_format($flete, 0) : '');
                    $this->excel->getActiveSheet()->setCellValue("AC{$i}", '=$AB$' . $initp . '/' . $sum . '*$J' . $i);
                    $this->excel->getActiveSheet()->setCellValue("AD{$i}", '=$AC' . $i . '/' . '$J' . $i);
                    $this->excel->getActiveSheet()->setCellValue("AH{$i}", '=$AF$' . $initp . '/' . $sum . '*$J' . $i);
                    $this->excel->getActiveSheet()->setCellValue("AI{$i}", '=$AH' . $i . '/' . '$J' . $i);
                    $this->excel->getActiveSheet()->setCellValue("AM{$i}", '=$AK$' . $initp . '/' . $sum . '*$J' . $i);
                    $this->excel->getActiveSheet()->setCellValue("AN{$i}", '=$AM' . $i . '/' . '$J' . $i);
                    $this->excel->getActiveSheet()->setCellValue("AR{$i}", '=$AP$' . $initp . '/' . $sum . '*$J' . $i);
                    $this->excel->getActiveSheet()->setCellValue("AS{$i}", '=$AR' . $i . '/' . $sum);
                    $this->excel->getActiveSheet()->setCellValue("AA{$i}", '=$AR' . $i . '/' . '$J' . $i);
                    $this->excel->getActiveSheet()->setCellValue("AW{$i}", '=$AU' . $initp . '/' . $sum . '*$J' . $i);
                    $this->excel->getActiveSheet()->setCellValue("AX{$i}", '=$AW' . $i . '/' . '$J' . $i);
                    $this->excel->getActiveSheet()->setCellValue("BB{$i}", '=$AZ' . $i . '/' . $sum . '*$J' . $i);
                    $this->excel->getActiveSheet()->setCellValue("BC{$i}", '=$BB' . $i . '/' . '$J' . $i);
                    $this->excel->getActiveSheet()->setCellValue("BG{$i}", '=$BE' . $i . '/' . $sum . '*$J' . $i);
                    $this->excel->getActiveSheet()->setCellValue("BH{$i}", '=$BG' . $i . '/' . '$J' . $i);
                    $this->excel->getActiveSheet()->setCellValue("BL{$i}", '=$BJ' . $i . '/' . $sum . '*$J' . $i);
                    $this->excel->getActiveSheet()->setCellValue("BM{$i}", '=$BL' . $i . '/' . '$J' . $i);
                    $this->excel->getActiveSheet()->setCellValue("BW{$i}", "=BV{$i}+BS{$i}+BP{$i}+BM{$i}+BH{$i}+BC{$i}+AX{$i}+AS{$i}+AN{$i}+AI{$i}+AD{$i}+AA{$i}+IX{$i}");
                    $this->excel->getActiveSheet()->setCellValue("BX{$i}", '=$BW' . $i . '/' . '$J' . $i);
                    if (isset($item["prev"])) {
                        if ($first == true) {
                            $prev = "BN{$i}";
                            $this->excel->getActiveSheet()->setCellValue($prev, number_format($item["prev"], 0));
                        }
                        $this->excel->getActiveSheet()->setCellValue("BO{$i}", '=$BN$' . $initp . '/' . $sum . '*$J' . $i);
                        $this->excel->getActiveSheet()->setCellValue("BP{$i}", "=BO{$i}/J{$i}");
                    }
                    if (isset($item["dta"])) {
                        if ($first == true) {
                            $dta = "BQ{$i}";
                            $this->excel->getActiveSheet()->setCellValue($dta, number_format($item["dta"], 0));
                        }
                        $this->excel->getActiveSheet()->setCellValue("BR{$i}", '=$BQ' . $initp . '/' . $sum . '*$J' . $i);
                        $this->excel->getActiveSheet()->setCellValue("BS{$i}", '=$BR' . $i . '/$J' . $i);
                    }
                    if (isset($item["cnt"])) {
                        if ($first == true) {
                            $cnt = "BT{$i}";
                            $this->excel->getActiveSheet()->setCellValue($cnt, number_format($item["cnt"], 0));
                        }
                        $this->excel->getActiveSheet()->setCellValue("BU{$i}", '=$BT' . $initp . '/' . $sum . '*$J' . $i);
                        $this->excel->getActiveSheet()->setCellValue("BV{$i}", '=$BU' . $i . '/$J' . $i);
                    }
                    $this->excel->getActiveSheet()->setCellValue("BZ{$i}", isset($item["valorAduana"]) ? number_format($item["valorAduana"], 2) : '');
                    $this->excel->getActiveSheet()->setCellValue("CA{$i}", isset($item["valorAduanaMxn"]) ? number_format($item["valorAduanaMxn"], 4) : '');
                    if ($first == true) {
                        $first = false;
                    }
                    if ($firsti == true) {
                        $firsti = false;
                    }
                    $i++;
                } // partes
                $this->excel->getActiveSheet()->setCellValue("W{$i}", "=SUM(W{$initp}:W" . ($i - 1) . ")");
                $this->excel->getActiveSheet()->setCellValue("AC{$i}", "=SUM(AC{$initp}:AC" . ($i - 1) . ")");
                $this->excel->getActiveSheet()->setCellValue("AH{$i}", "=SUM(AH{$initp}:AH" . ($i - 1) . ")");
                $this->excel->getActiveSheet()->setCellValue("AM{$i}", "=SUM(AM{$initp}:AM" . ($i - 1) . ")");
                $this->excel->getActiveSheet()->setCellValue("AP{$i}", "=SUM(AP{$initp}:AP" . ($i - 1) . ")");
                $this->excel->getActiveSheet()->setCellValue("AW{$i}", "=SUM(AW{$initp}:AW" . ($i - 1) . ")");
                $this->excel->getActiveSheet()->setCellValue("BB{$i}", "=SUM(BB{$initp}:BB" . ($i - 1) . ")");
                $this->excel->getActiveSheet()->setCellValue("BG{$i}", "=SUM(BG{$initp}:BG" . ($i - 1) . ")");
                $this->excel->getActiveSheet()->setCellValue("BL{$i}", "=SUM(BL{$initp}:BL" . ($i - 1) . ")");
                $this->excel->getActiveSheet()->setCellValue("BO{$i}", "=SUM(BO{$initp}:BO" . ($i - 1) . ")");
                $this->excel->getActiveSheet()->setCellValue("BR{$i}", "=SUM(BR{$initp}:BR" . ($i - 1) . ")");
                $this->excel->getActiveSheet()->setCellValue("BU{$i}", "=SUM(BU{$initp}:BU" . ($i - 1) . ")");
                $this->excel->getActiveSheet()->setCellValue("BW{$i}", "=SUM(BW{$initp}:BW" . ($i - 1) . ")");
                $this->excel->getActiveSheet()->setCellValue("BX{$i}", "=SUM(BX{$initp}:BX" . ($i - 1) . ")");
            } // facturas
            $this->excel->getActiveSheet()->setCellValue("BY{$i}", "=V{$initp}+Y{$initp}+AB{$initp}+AF{$initp}+AK{$initp}+AP{$initp}+AU{$initp}+AZ{$initp}+BE{$initp}+BJ{$initp}+BN{$initp}+BQ{$initp}+BT{$initp}");
            $this->excel->getActiveSheet()->getStyle("A{$i}:CA{$i}")->applyFromArray($this->styleh);
            $i++;
        } // pedimentos
        $this->_autosize($i);
    }

    public function reporteCargoQuinFracciones($fracciones) {
        $this->initRow = 1;
        $i = $this->initRow;
        $this->range = array("A", "Q");
        $this->title = "Tariff Report";
        $this->titles = array("# Tariff", "P.N.", "Quantity", "Tariff Descrption", "UM", "Origin Country", "Unit Price", "Total Quantity", "Invoice #", "Reference", "% IVA", "IVA", "% IGI", "IGI", "P.N. Assessment", "IVA", "P.N. Assessment");
        $this->_ini(1);
        $i++;
        foreach ($fracciones as $item) {
            if(!isset($item["cantidadFactura"])) {
                continue;
            }
            $this->excel->getActiveSheet()->setCellValue("A{$i}", $item["ordenFactura"]);
            $this->excel->getActiveSheet()->setCellValue("B{$i}", $item["numParte"]);
            $this->excel->getActiveSheet()->setCellValue("C{$i}", $item["cantidadFactura"]);
            $this->excel->getActiveSheet()->setCellValue("D{$i}", $item["descripcion"]);
            $this->excel->getActiveSheet()->setCellValue("E{$i}", $item["umc"]);
            $this->excel->getActiveSheet()->setCellValue("F{$i}", $item["paisOrigen"]);
            $this->excel->getActiveSheet()->setCellValue("G{$i}", number_format($item["precioUnitario"], 6));
            $this->excel->getActiveSheet()->setCellValue("H{$i}", $item["cantidadFactura"]);
            $this->excel->getActiveSheet()->setCellValue("I{$i}", $item["numFactura"]);
            $this->excel->getActiveSheet()->setCellValue("J{$i}", $item["referencia"]);
            $this->excel->getActiveSheet()->setCellValue("K{$i}", $item["iva"]);
            $this->excel->getActiveSheet()->setCellValue("L{$i}", $item["importeIva"]);
            $this->excel->getActiveSheet()->setCellValue("M{$i}", $item["igi"]);
            $this->excel->getActiveSheet()->setCellValue("N{$i}", $item["importeIgi"]);
            $this->excel->getActiveSheet()->setCellValue("O{$i}", "=L{$i}/C{$i}");
            $this->excel->getActiveSheet()->setCellValue("Q{$i}", "=N{$i}/C{$i}");
            $i++;
        }
        $this->_autosize($i);
    }

    public function reporteCargoQuinPartes($partes) {
        $this->initRow = 1;
        $i = $this->initRow;
        $this->range = array("A", "G");
        $this->title = "PN Report";
        $this->titles = array("NP", "Tariff Quantity", "UMC PED", "NP", "Invoice Aquantity", "UM / OMA", "");
        $this->_ini(2);
        $i++;
        foreach ($partes as $item) {
            $this->excel->getActiveSheet()->setCellValue("A{$i}", $item["numParte"]);
            $this->excel->getActiveSheet()->setCellValue("B{$i}", $item["cantidadFactura"]);
            $this->excel->getActiveSheet()->setCellValue("C{$i}", $item["umc"]);
            $this->excel->getActiveSheet()->setCellValue("D{$i}", $item["numParte"]);
            $this->excel->getActiveSheet()->setCellValue("E{$i}", $item["cantidadOma"]);
            $this->excel->getActiveSheet()->setCellValue("F{$i}", $item["oma"]);
            $this->excel->getActiveSheet()->setCellValue("G{$i}", "=B{$i}-E{$i}");
            $i++;
        }
        $this->_autosize($i);
    }

    public function layoutTecnico() {
        $border = (new BorderBuilder())
                ->setBorderBottom(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                ->build();
        $style = (new StyleBuilder())
                ->setFontBold()
                ->setFontSize(10)
                ->setFontName("Arial")
                ->setFontColor(Color::BLACK)
                ->setBackgroundColor("c6d9f0")
                ->setBorder($border)
                ->build();
        $styles = (new StyleBuilder())
                ->setFontSize(10)
                ->setFontName("Arial")
                ->setFontColor(Color::BLACK)
                ->build();
        $writer = WriterFactory::create(Type::XLSX);
        $writer->openToBrowser($this->filename);
        $writer->addRowWithStyle($this->titles, $style);
        $writer->addRowsWithStyle($this->data, $styles);
        $writer->close();
    }
    
    public function layoutClientes() {
        $border = (new BorderBuilder())
                ->setBorderBottom(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                ->build();
        $style = (new StyleBuilder())
                ->setFontBold()
                ->setFontSize(10)
                ->setFontName("Arial")
                ->setFontColor(Color::BLACK)
                ->setBackgroundColor("c6d9f0")
                ->setBorder($border)
                ->build();
        $styles = (new StyleBuilder())
                ->setFontSize(10)
                ->setFontName("Arial")
                ->setFontColor(Color::BLACK)
                ->build();
        $writer = WriterFactory::create(Type::XLSX);
        $writer->openToBrowser($this->filename);
        $writer->addRowWithStyle($this->titles, $style);
        $writer->addRowsWithStyle($this->data, $styles);
        $writer->close();
    }
    
    public function layoutAnexo24Clientes() {
        $border = (new BorderBuilder())
                ->setBorderBottom(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                ->build();
        $style = (new StyleBuilder())
                ->setFontBold()
                ->setFontSize(10)
                ->setFontName("Arial")
                ->setFontColor(Color::BLACK)
                ->setBackgroundColor("c6d9f0")
                ->setBorder($border)
                ->build();
        $styles = (new StyleBuilder())
                ->setFontSize(10)
                ->setFontName("Arial")
                ->setFontColor(Color::BLACK)
                ->build();
        $writer = WriterFactory::create(Type::XLSX);
        $writer->openToBrowser($this->filename);
        $writer->addRowWithStyle($this->titles, $style);
        $writer->addRowsWithStyle($this->data, $styles);
        $writer->close();
    }

    protected function _value($item, $key) {
        if (isset($item[$key]) && $item[$key] != "") {
            return $item[$key];
        }
        return "";
    }

    public function download() {
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header('Content-Disposition: attachment;filename="' . $this->filename . '.xlsx"');
        header("Cache-Control: max-age=0");
        header("Content-Transfer-Encoding: binary ");
        $objWriter = new PHPExcel_Writer_Excel2007($this->excel);
        $objWriter->setOffice2003Compatibility(true);
        $objWriter->save("php://output");
    }
    
    public function semaforo($value) {
        if ((int) $value == 1) {
            return 'Verde';
        }
        if ((int) $value == 2) {
            return 'Rojo';
        }
        return '';
    }
    
    public function reportesTrafico($tipoReporte, $rows = null) {
        $border = (new BorderBuilder())
                ->setBorderBottom(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                ->build();
        $tstyle = (new StyleBuilder())
                ->setFontBold()
                ->setFontSize(10)
                ->setFontName("Arial")
                ->setFontColor(Color::BLACK)
                ->setBackgroundColor("c6d9f0")
                ->setBorder($border)
                ->build();
        $dstyle = (new StyleBuilder())
                ->setFontSize(10)
                ->setFontName("Arial")
                ->setFontColor(Color::BLACK)
                ->build();
        $writer = WriterFactory::create(Type::XLSX);
        if (APPLICATION_ENV == "production" || APPLICATION_ENV == "staging") {
            $writer->setTempFolder($this->appConfig->getParam("tmpDir"));            
        } else {
            $writer->setTempFolder("C:\\wamp64\\tmp");            
        }
        if ($tipoReporte == 1) {
            $writer->openToBrowser("TRAFICOS_" . time() . ".xlsx");
        }
        if ($tipoReporte == 2) {
            $writer->openToBrowser("TRAFICOS_CANDADOS_" . time() . ".xlsx");            
        }
        if ($tipoReporte == 4) {
            $writer->openToBrowser("TRAFICOSINCOMP_" . time() . ".xlsx");
        }
        if ($tipoReporte == 5) {
            $writer->openToBrowser("TRAFICOS_AEREOS_" . time() . ".xlsx");
        }
        if ($tipoReporte == 6) {
            $writer->openToBrowser("TRAFICOS_MARITIMOS_" . time() . ".xlsx");
        }
        if ($tipoReporte == 7) {
            $writer->openToBrowser("TRAFICOS_OPSESP_" . time() . ".xlsx");
        }
        if ($tipoReporte == 8) {
            $writer->openToBrowser("TRAFICOS_TERRESTRES_" . time() . ".xlsx");
        }
        if ($tipoReporte == 12) {
            $writer->openToBrowser("TIEMPOSFACT_" . time() . ".xlsx");
        }
        if ($tipoReporte == 14) {
            $writer->openToBrowser("REPFACT_" . time() . ".xlsx");
        }
        if ($tipoReporte == 50 || $tipoReporte == 51 || $tipoReporte == 52 || $tipoReporte == 53 || $tipoReporte == 54) {
            $writer->openToBrowser("TRAFICOS_" . date("Y-m-d") . "_" . time() . ".xlsx");
        }
        if ($tipoReporte == 60) {
            $writer->openToBrowser("FACT_TERMINAL_" . date("Y-m-d") . "_" . time() . ".xlsx");
        }
        if ($tipoReporte == 61) { // empleados
            $writer->openToBrowser("EMPLEADOS_" . date("Y-m-d") . "_" . time() . ".xlsx");
        }
        if ($tipoReporte == 100) {
            $writer->openToBrowser("REPORTE_IVA_" . date("Y-m-d") . "_" . time() . ".xlsx");
        }
        if ($tipoReporte == 70) {
            $writer->openToBrowser("REP_COVE_" . date("Y-m-d") . "_" . time() . ".xlsx");
        }
        if ($tipoReporte == 71) {
            $writer->openToBrowser("REP_EDOC_" . date("Y-m-d") . "_" . time() . ".xlsx");
        }
        if ($tipoReporte == 72) { // indicadores
            $writer->openToBrowser("REP_INDCADORES_" . date("Y-m-d") . "_" . time() . ".xlsx");            
        }
        if ($tipoReporte == 73) {
            $writer->openToBrowser("REP_ESTATUS_MVHC_" . date("Y-m-d") . "_" . time() . ".xlsx");            
        }
        if ($tipoReporte == 74) {
            $writer->openToBrowser("REP_SIN_FACTURAR_" . date("Y-m-d") . "_" . time() . ".xlsx");            
        }
        if ($tipoReporte == 75) {
            $writer->openToBrowser("RPT_TRAFICOS_FACTURACION_" . date("Y-m-d") . "_" . time() . ".xlsx");            
        }
        if ($tipoReporte == 76) {
            $writer->openToBrowser("RPT_ENTREGA_EXPEDIENTES_" . date("Y-m-d") . "_" . time() . ".xlsx");            
        }
        if ($tipoReporte == 77) {
            $writer->openToBrowser("RPT_SELLOS_AGENTES_" . date("Y-m-d") . "_" . time() . ".xlsx");            
        }
        if ($tipoReporte == 78) {
            $writer->openToBrowser("RPT_SELLOS_CLIENTES_" . date("Y-m-d") . "_" . time() . ".xlsx");            
        }
        if ($tipoReporte == 80) {
            $writer->openToBrowser("FACT_INV_" . date("Y-m-d") . "_" . time() . ".xlsx");
        }
        if ($tipoReporte == 81) {
            $writer->openToBrowser("PROVEEDORES_" . date("Y-m-d") . "_" . time() . ".xlsx");
        }
        if ($tipoReporte == 82) {
            $writer->openToBrowser("CATALOGO_PARTES_" . date("Y-m-d") . "_" . time() . ".xlsx");
        }
        if ($tipoReporte == 83) { // catalogo de partes clientes
            $writer->openToBrowser("CATALOGO_PARTES_" . date("Y-m-d") . "_" . time() . ".xlsx");
        }
        if ($tipoReporte == 84) { // // traficos vs facturacion --- desde admin
            $writer->openToBrowser("RPT_TRAFICOS_FACTURACION_" . date("Y-m-d") . "_" . time() . ".xlsx");            
        }
        if ($tipoReporte == 85) { // catalogo de partes clientes
            $writer->openToBrowser("CATALOGO_PARTES_" . date("Y-m-d") . "_" . time() . ".xlsx");
        }
        if ($tipoReporte == 2) {
            $writer->addRowWithStyle(array(
                "Sello",
                "Cliente",
                "Trafico",
                "Pedimento",
                "Fecha",
                    ), $tstyle);
            foreach ($rows as $row) {
                $writer->addRowWithStyle(array(
                    $row["numero"],
                    $row["nombreCliente"],
                    $row["referencia"],
                    $row["pedimento"],
                    $row["fechaPago"],
                        ), $dstyle);
            }
        }
        if ($tipoReporte == 1) {
            $writer->addRowWithStyle(array(
                "Patente",
                "Aduana",
                "Pedimento",
                "Referencia",
                "Tipo Operación",
                "Cve.",
                "Nombre Cliente",
                "Coves",
                "Edocuments",
                "Usuario",
                "ETA",
                "F. Notificación",
                "F. Envio Doctos.",
                "F. Entrada",
                "F. Presentación",
                "F. Envio Proforma",
                "F. VoBo",
                "F. Revalidación",
                "F. Previo",
                "F. Pago",
                "F. Liberación",
                "ETA Almacen",
                "F. Facturación",
                "BL/Guía",
                "Almacen",
                "Planta",
                "Días despacho",
                "Días retraso",
                "Semaforo",
                    ), $tstyle);
            foreach ($rows as $row) {
                $writer->addRowWithStyle(array(
                    $row["patente"],
                    $row["aduana"],
                    $row["pedimento"],
                    $row["referencia"],
                    $row["ie"],
                    $row["cvePedimento"],
                    $row["nombreCliente"],
                    $row["coves"],
                    $row["edocuments"],
                    $row["nombre"],
                    $row["fechaEta"],
                    $row["fechaNotificacion"],
                    $row["fechaEnvioDocumentos"],
                    $row["fechaEntrada"],
                    $row["fechaPresentacion"],
                    $row["fechaEnvioProforma"],
                    $row["fechaVistoBueno"],
                    $row["fechaRevalidacion"],
                    $row["fechaPrevio"],
                    $row["fechaPago"],
                    $row["fechaLiberacion"],
                    $row["fechaEtaAlmacen"],
                    $row["fechaFacturacion"],
                    $row["blGuia"],
                    $row["nombreAlmacen"],
                    $row["descripcionPlanta"],
                    $row["diasDespacho"],
                    $row["diasRetraso"],
                    $this->semaforo($row["semaforo"]),
                        ), $dstyle);
            }
        }
        if ($tipoReporte == 5) { // traficos aereos
            $writer->addRowWithStyle(array(
                "Patente",
                "Aduana",
                "Pedimento",
                "Referencia",
                "Tipo Operación",
                "cvePedimento",
                "Nombre Cliente",
                "Usuario",
                "Fecha Entrada",
                "Fecha Presentación",
                "BL/Guía",
                "Almacen",
                "Fecha ETA",
                "Fecha Instrucciones Especiales",
                "Fecha Revalidación",
                "Fecha Previo",
                "Fecha Pago",
                "Fecha Liberación",
                "ETA Almacen",
                "Fecha Facturación",
                "Planta",
                "Semaforo"
                    ), $tstyle);
            foreach ($rows as $row) {
                $writer->addRowWithStyle(array(
                    $row["patente"],
                    $row["aduana"],
                    $row["pedimento"],
                    $row["referencia"],
                    $row["ie"],
                    $row["cvePedimento"],
                    $row["nombreCliente"],
                    $row["nombre"],
                    $row["fechaEntrada"],
                    $row["fechaPresentacion"],
                    $row["blGuia"],
                    $row["nombreAlmacen"],
                    $row["fechaEta"],
                    $row["fechaInstruccionEspecial"],
                    $row["fechaRevalidacion"],
                    $row["fechaPrevio"],
                    $row["fechaPago"],
                    $row["fechaLiberacion"],
                    $row["fechaEtaAlmacen"],
                    $row["fechaFacturacion"],
                    $row["descripcionPlanta"],                    
                    $this->semaforo($row["semaforo"]),
                        ), $dstyle);
            }
        }
        if ($tipoReporte == 6) { // traficos maritimos
            $writer->addRowWithStyle(array(
                "Patente",
                "Aduana",
                "Pedimento",
                "Referencia",
                "Tipo Operación",
                "cvePedimento",
                "Nombre Cliente",
                "Usuario",
                "BL",
                "Contenedor/CS",
                "Fecha ETA Puerto",
                "Almacen",
                "Fecha Instrucciones Especiales",
                "Fecha Revalidación",
                "Fecha Previo",
                "Fecha Entrada",
                "Fecha Pago",
                "Fecha Liberación",
                "ETA Almacen",
                "Fecha Facturación",
                "Tipo de Carga",
                "Planta",
                "Semaforo"
                    ), $tstyle);
            foreach ($rows as $row) {
                $writer->addRowWithStyle(array(
                    $row["patente"],
                    $row["aduana"],
                    $row["pedimento"],
                    $row["referencia"],
                    $row["ie"],
                    $row["cvePedimento"],
                    $row["nombreCliente"],
                    $row["nombre"],
                    $row["blGuia"],
                    $row["contenedorCaja"],
                    $row["fechaEta"],
                    $row["nombreAlmacen"],
                    $row["fechaInstruccionEspecial"],
                    $row["fechaRevalidacion"],
                    $row["fechaPrevio"],
                    $row["fechaEntrada"],
                    $row["fechaPago"],
                    $row["fechaLiberacion"],
                    $row["fechaEtaAlmacen"],
                    $row["fechaFacturacion"],
                    $row["carga"],
                    $row["descripcionPlanta"],
                    $this->semaforo($row["semaforo"]),
                        ), $dstyle);
            }
        }
        if ($tipoReporte == 7) { // traficos ops espec
            $writer->addRowWithStyle(array(
                "Patente",
                "Aduana",
                "Pedimento",
                "Referencia",
                "Tipo Operación",
                "cvePedimento",
                "Nombre Cliente",
                "Usuario",
                "Fecha Instrucciones Especiales",
                "Fecha Envío Proforma",
                "Fecha VoBo",
                "Fecha Pago",
                "Fecha Liberación",
                "Fecha Facturación",
                "Tipo de Carga",
                "Semaforo"
                    ), $tstyle);
            foreach ($rows as $row) {
                $writer->addRowWithStyle(array(
                    $row["patente"],
                    $row["aduana"],
                    $row["pedimento"],
                    $row["referencia"],
                    $row["ie"],
                    $row["cvePedimento"],
                    $row["nombreCliente"],
                    $row["nombre"],
                    $row["fechaInstruccionEspecial"],
                    $row["fechaEnvioProforma"],
                    $row["fechaVistoBueno"],
                    $row["fechaPago"],
                    $row["fechaLiberacion"],
                    $row["fechaFacturacion"],
                    $row["carga"],
                    $this->semaforo($row["semaforo"])
                        ), $dstyle);
            }
        }
        if ($tipoReporte == 8) { // traficos terrestres
            $writer->addRowWithStyle(array(
                "Patente",
                "Aduana",
                "Pedimento",
                "Referencia",
                "Tipo Operación",
                "cvePedimento",
                "Nombre Cliente",
                "Usuario",
                "Fecha ETA",
                "Fecha Entrada",
                "Fecha Presentación",
                "Fecha Instrucciones Especiales",
                "Fecha Previo",
                "Fecha Pago",
                "Fecha Liberación",
                "Fecha ETA Destino",
                "Fecha Facturación",
                "Tipo de Carga",
                "Almacen",
                "Planta",
                "Semaforo"
                    ), $tstyle);
            foreach ($rows as $row) {
                $writer->addRowWithStyle(array(
                    $row["patente"],
                    $row["aduana"],
                    $row["pedimento"],
                    $row["referencia"],
                    $row["ie"],
                    $row["cvePedimento"],
                    $row["nombreCliente"],
                    $row["nombre"],
                    $row["fechaEta"],
                    $row["fechaEntrada"],
                    $row["fechaPresentacion"],
                    $row["fechaInstruccionEspecial"],
                    $row["fechaPrevio"],
                    $row["fechaPago"],
                    $row["fechaLiberacion"],
                    $row["fechaEtaAlmacen"],
                    $row["fechaFacturacion"],
                    $row["carga"],
                    $row["nombreAlmacen"],
                    $row["descripcionPlanta"],
                    $this->semaforo($row["semaforo"])
                        ), $dstyle);
            }
        }
        if ($tipoReporte == 4) {
            $writer->addRowWithStyle(array(
                "Patente",
                "Aduana",
                "Pedimento",
                "Referencia",
                "Fecha Entrada",
                "Fecha Envio Documentos",
                "Fecha Revalidación",
                "Fecha Pago",
                "Fecha Liberación",
                "Usuario",
                    ), $tstyle);
            foreach ($rows as $row) {
                $writer->addRowWithStyle(array(
                    $row["patente"],
                    $row["aduana"],
                    $row["pedimento"],
                    $row["referencia"],
                    $row["fechaEntrada"],
                    $row["fechaEnvioDocumentos"],
                    $row["fechaRevalidacion"],
                    $row["fechaPago"],
                    $row["fechaLiberacion"],
                    $row["usuario"],
                        ), $dstyle);
            }
        }
        if ($tipoReporte == 12) {
            $writer->addRowWithStyle(array(
                "Folio",
                "Patente",
                "Aduana",
                "Pedimento",
                "Referencia",
                "Fecha Facturación",
                "Fecha Pedimento",
                "Fecha Diferencia",
                "Factura",
                "Cliente",
                    ), $tstyle);
            foreach ($rows as $row) {
                $writer->addRowWithStyle(array(
                    $row["FolioID"],
                    $row["Patente"],
                    $row["AduanaID"],
                    $row["Pedimento"],
                    $row["Referencia"],
                    $row["Fecha"],
                    $row["FechaPedimento"],
                    $row["FechaDiff"],
                    $row["Factura"],
                    $row["Nombre"],
                        ), $dstyle);
            }
        }
        if ($tipoReporte == 14) {
            $writer->addRowWithStyle(array(
                "Patente",
                "Aduana",
                "Usuario",
                "Enero",
                "Febrero",
                "Marzo",
                "Abril",
                "Mayo",
                "Junio",
                "Julio",
                "Agosto",
                "Septiembre",
                "Octubre",
                "Noviembre",
                "Diciembre",
                    ), $tstyle);
            foreach ($rows as $row) {
                $writer->addRowWithStyle(array(
                    $row["patente"],
                    $row["aduana"],
                    $row["usuario"],
                    $row["ene"],
                    $row["feb"],
                    $row["mar"],
                    $row["abr"],
                    $row["may"],
                    $row["jun"],
                    $row["jul"],
                    $row["ago"],
                    $row["sep"],
                    $row["oct"],
                    $row["nov"],
                    $row["dic"],
                        ), $dstyle);
            }
        }
        if ($tipoReporte == 50 || $tipoReporte == 54) { // ops del día totales
            $writer->addRowWithStyle(array(
                "I/E",
                "Patente",
                "Aduana",
                "Pedimento",
                "Referencia",
                "Cve.",
                "Nombre Cliente",
                "Usuario",
                "ETA",
                "F. Envio Documentos",
                "F. Pago",
                "F. Liberación",
                "F. Facturación",
                "BL/Guía",
                "Planta",
                "Semaforo",
                    ), $tstyle);
            foreach ($rows as $row) {
                $writer->addRowWithStyle(array(
                    $row["ie"],
                    $row["patente"],
                    $row["aduana"],
                    $row["aduana"] . "-" . $row["patente"] . "-" . $row["pedimento"],
                    $row["referencia"],
                    $row["cvePedimento"],
                    $row["nombreCliente"],
                    $row["nombre"],
                    $row["fechaEta"],
                    $row["fechaEnvioDocumentos"],
                    $row["fechaPago"],
                    $row["fechaLiberacion"],
                    $row["fechaFacturacion"],
                    $row["blGuia"],
                    $row["descripcionPlanta"],
                    $this->semaforo($row["semaforo"]),
                        ), $dstyle);
            }
        }
        if ($tipoReporte == 51) { // ops del día aereas
            $writer->addRowWithStyle(array(
                "I/E",
                "Patente",
                "Aduana",
                "Pedimento",
                "Referencia",
                "Cve.",
                "Nombre Cliente",
                "Usuario",
                "F. Notificación",
                "F. Envio Documentos",
                "BL/Guía",
                "Alcamen",
                "F. ETA Destino",
                "F. Revalidación",
                "F. Previo",
                "F. Proforma",
                "F. VoBo.",
                "F. Entrada",
                "F. Presentación",
                "F. Pago",
                "F. Liberación",
                "F. Facturación",
                "Planta",
                "Días Despacho",
                "Semaforo",
                    ), $tstyle);
            foreach ($rows as $row) {
                $writer->addRowWithStyle(array(
                    $row["ie"],
                    $row["patente"],
                    $row["aduana"],
                    $row["aduana"] . "-" . $row["patente"] . "-" . $row["pedimento"],
                    $row["referencia"],
                    $row["cvePedimento"],
                    $row["nombreCliente"],
                    $row["nombre"],
                    $row["fechaNotificacion"],
                    $row["fechaEnvioDocumentos"],
                    $row["blGuia"],
                    $row["almacen"],
                    $row["fechaEtaAlmacen"],
                    $row["fechaRevalidacion"],
                    $row["fechaPrevio"],
                    $row["fechaEnvioProforma"],
                    $row["fechaVistoBueno"],
                    $row["fechaEntrada"],
                    $row["fechaPresentacion"],
                    $row["fechaPago"],
                    $row["fechaLiberacion"],
                    $row["fechaFacturacion"],
                    $row["descripcionPlanta"],
                    $row["diasDespacho"],
                    $this->semaforo($row["semaforo"]),
                        ), $dstyle);
            }
        }
        if ($tipoReporte == 52) { // ops maritimo
            $writer->addRowWithStyle(array(
                "I/E",
                "Patente",
                "Aduana",
                "Pedimento",
                "Referencia",
                "Cve.",
                "Nombre Cliente",
                "Usuario",
                "F. Notificación",
                "F. Envio Docs.",
                "BL",
                "Contenedor/CS",
                "ETA Puerto",
                "Alcamen",
                "ETA Almacen",
                "F. Revalidación",
                "F. Previo",
                "F. Proforma",
                "F. VoBo.",
                "F. Entrada",
                "F. Presentación",
                "F. Pago",
                "F. Liberación",
                "F. Facturación",
                "Tipo de Carga",
                "Planta",
                "Días Despacho",
                "Semaforo",
                    ), $tstyle);
            foreach ($rows as $row) {
                $writer->addRowWithStyle(array(
                    $row["ie"],
                    $row["patente"],
                    $row["aduana"],
                    $row["aduana"] . "-" . $row["patente"] . "-" . $row["pedimento"],
                    $row["referencia"],
                    $row["cvePedimento"],
                    $row["nombreCliente"],
                    $row["nombre"],
                    $row["fechaNotificacion"],
                    $row["fechaEnvioDocumentos"],
                    $row["blGuia"],
                    $row["contenedorCaja"],
                    $row["fechaEta"],
                    $row["almacen"],
                    $row["fechaEtaAlmacen"],
                    $row["fechaRevalidacion"],
                    $row["fechaPrevio"],
                    $row["fechaEnvioProforma"],
                    $row["fechaVistoBueno"],
                    $row["fechaEntrada"],
                    $row["fechaPresentacion"],
                    $row["fechaPago"],
                    $row["fechaLiberacion"],
                    $row["fechaFacturacion"],
                    $row["carga"],
                    $row["descripcionPlanta"],
                    $row["diasDespacho"],
                    $this->semaforo($row["semaforo"]),
                        ), $dstyle);
            }
        }
        if ($tipoReporte == 53) { // ops especiales
            $writer->addRowWithStyle(array(
                "I/E",
                "Patente",
                "Aduana",
                "Pedimento",
                "Referencia",
                "Cve.",
                "Nombre Cliente",
                "Usuario",
                "F. Notificación",
                "F. Envio Documentos",
                "F. Proforma",
                "F. VoBo.",
                "F. Entrada",
                "F. Presentación",
                "F. Pago",
                "F. Liberación",
                "F. Facturación",
                "Días Despacho",
                "Semaforo"
                    ), $tstyle);
            foreach ($rows as $row) {
                $writer->addRowWithStyle(array(
                    $row["ie"],
                    $row["patente"],
                    $row["aduana"],
                    $row["aduana"] . "-" . $row["patente"] . "-" . $row["pedimento"],
                    $row["referencia"],
                    $row["cvePedimento"],
                    $row["nombreCliente"],
                    $row["nombre"],
                    $row["fechaNotificacion"],
                    $row["fechaEnvioDocumentos"],
                    $row["fechaEnvioProforma"],
                    $row["fechaVistoBueno"],
                    $row["fechaEntrada"],
                    $row["fechaPresentacion"],
                    $row["fechaPago"],
                    $row["fechaLiberacion"],
                    $row["fechaFacturacion"],
                    $row["diasDespacho"],
                    $this->semaforo($row["semaforo"]),
                        ), $dstyle);
            }
        }
        if ($tipoReporte == 60) { // ops especiales
            $writer->addRowWithStyle(array(
                "idRepo",
                "Patente",
                "Aduana",
                "Pedimento",
                "Referencia",
                "Folio",
                "Fecha Folio",
                "RFC Cliente",
                "RFC Emisor",
                "RFC Receptor",
                "Guía",
                "Nombre Archivo",
                    ), $tstyle);
            foreach ($rows as $row) {
                $writer->addRowWithStyle(array(
                    $row["idRepositorio"],
                    $row["patente"],
                    $row["aduana"],
                    $row["pedimento"],
                    $row["referencia"],
                    $row["folio"],
                    $row["fechaFolio"],
                    $row["rfcCliente"],
                    $row["rfcEmisor"],
                    $row["rfcReceptor"],
                    $row["guia"],
                    $row["nombreArchivo"],
                        ), $dstyle);
            }
        }
        if ($tipoReporte == 61) { // empleados
            $writer->addRowWithStyle(array(
                "Empresa",
                "Nombre",
                "Email",
                "Email Personal",
                "Tel.",
                "Capacit.",
                "Doctos.",
                "Estatus",
                    ), $tstyle);
            foreach ($rows as $row) {
                $writer->addRowWithStyle(array(
                    $row["razonSocial"],
                    $row["apellido"] . ', ' . $row["nombre"],
                    $row["emailEmpresa"],
                    $row["emailPersonal"],
                    $row["telefono"],
                    ($row["capacitacion"] == 1) ? 'Si' : '',
                    ($row["documentacion"] == 1) ? 'Si' : '',
                    ($row["estatus"] == 0) ? 'Ináctivo' : '',
                        ), $dstyle);
            }
        }
        if ($tipoReporte == 70) { // reporte coves
            $writer->addRowWithStyle(array(
                "Nombre de usuario",
                "Sin error",
                "Con error",
                "Total",
                    ), $tstyle);
            foreach ($rows as $row) {
                $writer->addRowWithStyle(array(
                    $row["nombre"],
                    $row["sinError"],
                    $row["conError"],
                    $row["total"],
                        ), $dstyle);
            }
        }
        if ($tipoReporte == 71) { // reporte edocuments
            $writer->addRowWithStyle(array(
                "Nombre de usuario",
                "Sin error",
                "Con error",
                "Total",
                    ), $tstyle);
            foreach ($rows as $row) {
                $writer->addRowWithStyle(array(
                    $row["nombre"],
                    $row["sinError"],
                    $row["conError"],
                    $row["total"],
                        ), $dstyle);
            }
        }
        if ($tipoReporte == 72) { // reporte indicadores
            $writer->addRowWithStyle(array(
                "Patente",
                "Aduana",
                "Pedimento",
                "Referencia",
                "Cve.",
                "Nombre Cliente",
                "Tipo de Aduana",
                "I/E",
                "Semaforo",
                "Obs. Semaforo",
//                "BL/Guía",
//                "Cont./Caja",
                "Planta",
//                "Expediente",
                "Fecha ETA",
                "Fecha Pago",
                "Fecha Env. Documentos",
                "Fecha Liberación",
                "Fecha Facturación",
                "Usuario",
                "Folio",
                "Fecha Facturación Sica",
                "Honorarios",
                "Justificado",
                "Cump. Admin.",
                "Cump. Ope.",
//                "Cumplimiento",
                "Observaciones",
                "CC. Consolidado",
                "Rev. Admon.",
                "Rev. Operaciones",
                "Completo",
                "MV HC Cliente",
                "Firmada",
                    ), $tstyle);
            foreach ($rows as $row) {
                $writer->addRowWithStyle(array(
                    $row["patente"],
                    $row["aduana"],
                    $row["pedimento"],
                    $row["referencia"],
                    $row["cvePedimento"],
                    $row["nombreCliente"],
                    $row["tipoAduana"],
                    $row["ie"],
                    ($row["semaforo"] == 1) ? 'Verde' : ($row["semaforo"] == 2) ? 'Rojo' : '',
                    $row["observacionSemaforo"],
                    //$row["blGuia"],
                    //$row["contenedorCaja"],
                    $row["descripcionPlanta"],
//                    ($row["revisionOperaciones"] == 1) ? 'Si' : '',
                    $row["fechaEta"],
                    $row["fechaPago"],
                    $row["fechaEnvioDocumentos"],
                    $row["fechaLiberacion"],
                    $row["fechaFacturacion"],
                    $row["nombreUsuario"],
                    $row["folio"],
                    $row["fechaFacturacionSica"],
                    $row["honorarios"],
                    ($row["justificado"] == 1) ? 'Si' : '',
                    //($row["cumplimientoAdministrativo"] == 1) ? 'Si' : '',
                    //($row["cumplimientoOperativo"] == 1) ? 'Si' : '',
                    //$row["cumplimiento"],
                    $row["observaciones"],
                    $row["ccConsolidado"],
                    ($row["revisionAdministracion"] == 1) ? 'Si' : '',
                    ($row["revisionOperaciones"] == 1) ? 'Si' : '',
                    ($row["completo"] == 1) ? 'Si' : '',
                    ($row["mvhcCliente"] == 1) ? 'Si' : '',
                    ($row["mvhcFirmada"] == 1) ? 'Si' : '',
                        ), $dstyle);
            }
        }
        if ($tipoReporte == 73) { // 
            $writer->addRowWithStyle(array(
                "Patente",
                "Aduana",
                "Pedimento",
                "Referencia",
                "Nombre Cliente",
                "Rev. Admon.",
                "Rev. Operaciones",
                "Completo",
                "MV/HC N/A",
                "MV/HC Firmada",
                "MV/HC Enviada",
                "Num. Guía",
                    ), $tstyle);
            foreach ($rows as $row) {
                $writer->addRowWithStyle(array(
                    $row["patente"],
                    $row["aduana"],
                    $row["pedimento"],
                    $row["referencia"],
                    $row["nombreCliente"],
                    ($row["revisionAdministracion"] != null) ? 'Si' : '',
                    ($row["revisionOperaciones"] != null) ? 'Si' : '',
                    ($row["completo"] != null) ? 'Si' : '',
                    ($row["mvhcCliente"] != null) ? 'Si' : '',
                    ($row["mvhcFirmada"] != null) ? 'Si' : '',
                    ($row["mvhcEnviada"] != null) ? 'Si' : '',
                    $row["numGuia"],
                        ), $dstyle);
            }
        }
        if ($tipoReporte == 74) { // 
            $writer->addRowWithStyle(array(
                "Patente",
                "Aduana",
                "Pedimento",
                "Referencia",
                "Nombre Cliente",
                "Usuario",
                "F. ETA",
                "F. Notificación",
                "F. Env. Doctos.",
                "F. Entrada",
                "F. Presentación",
                "F. Env. Proforma",
                "F. Vo. Bo.",
                "F. Revalidación",
                "F. Previo",
                "F. Pago",
                "F. Lib.",
                "F. ETA Almacen",
                "F. Facturación",
                "Bl / Guía",
                "Nom. Almacen",
                "Desc. Planta",
                    ), $tstyle);
            foreach ($rows as $row) {
                $writer->addRowWithStyle(array(
                    $row["patente"],
                    $row["aduana"],
                    $row["pedimento"],
                    $row["referencia"],
                    $row["nombreCliente"],
                    $row["nombre"],
                    $row["fechaEta"],
                    $row["fechaNotificacion"],
                    $row["fechaEnvioDocumentos"],
                    $row["fechaEntrada"],
                    $row["fechaPresentacion"],
                    $row["fechaEnvioProforma"],
                    $row["fechaVistoBueno"],
                    $row["fechaRevalidacion"],
                    $row["fechaPrevio"],
                    $row["fechaPago"],
                    $row["fechaLiberacion"],
                    $row["fechaEtaAlmacen"],
                    $row["fechaFacturacion"],
                    $row["blGuia"],
                    $row["nombreAlmacen"],
                    $row["descripcionPlanta"],
                        ), $dstyle);
            }
        }
        if ($tipoReporte == 75) { // traficos vs facturacion
            $writer->addRowWithStyle(array(
                "Patente",
                "Aduana",
                "Pedimento",
                "Referencia",
                "I/E",
                "Cve. Pedimento",
                "Nom. Cliente",
                "BL/Guia",
                "Nom. Buque",
                "Folio",
                "F. Facturacion",
                "F. Pago",
                "Pagos hechos",
                "G. sin comprobar",
                "Honorarios",
                "I.V.A.",
                "SubTotal",
                "Pagada",
                    ), $tstyle);
            foreach ($rows as $row) {
                $writer->addRowWithStyle(array(
                    $row["patente"],
                    $row["aduana"],
                    $row["pedimento"],
                    $row["referencia"],
                    $row["ie"],
                    $row["cvePedimento"],
                    $row["nombreCliente"],
                    $row["blGuia"],
                    $row["nombreBuque"],
                    $row["folio"],
                    $row["fechaFacturacion"],
                    $row["fechaPago"],
                    $row["pagoHechos"],
                    $row["sinComprobar"],
                    $row["honorarios"],
                    $row["iva"],
                    $row["subTotal"],
                    ($row["pagada"] != null) ? 'Si' : '',
                        ), $dstyle);
            }
        }
        if ($tipoReporte == 76) { // traficos vs facturacion
            $writer->addRowWithStyle(array(
                "Patente",
                "Aduana",
                "Pedimento",
                "Referencia",
                "Fecha Pago",
                "Rev. Admon.",
                "Rev. Operaciones",
                "Completo",
                "MV / HC Cliente",
                "Firmada",
                    ), $tstyle);
            foreach ($rows as $row) {
                $writer->addRowWithStyle(array(
                    $row["patente"],
                    $row["aduana"],
                    $row["pedimento"],
                    $row["referencia"],
                    $row["fechaPago"],
                    ($row["revisionAdministracion"] != null) ? 'Si' : '',
                    ($row["revisionOperaciones"] != null) ? 'Si' : '',
                    ($row["completo"] != null) ? 'Si' : '',
                    ($row["mvhcCliente"] != null) ? 'Si' : '',
                    ($row["mvhcFirmada"] != null) ? 'Si' : '',
                        ), $dstyle);
            }
        }
        
        if ($tipoReporte == 77) { // reporte inventario de traficos
            $writer->addRowWithStyle(array(
                "Patente",
                "Nombre",
                "Válido desde",
                "Válido hasta"
                    ), $tstyle);
            foreach ($rows as $row) {
                $writer->addRowWithStyle(array(
                    $row["patente"],
                    $row["nombre"],
                    date("Y-m-d", strtotime($row["valido_desde"])),
                    date("Y-m-d", strtotime($row["valido_hasta"]))
                        ), $dstyle);
            }
            $writer->close();
            die();
        }
        
        if ($tipoReporte == 78) { // // Sellos de clientes
            $writer->addRowWithStyle(array(
                "RFC",
                "Razón Social",
                "Válido desde",
                "Válido hasta"
                    ), $tstyle);
            foreach ($rows as $row) {
                $writer->addRowWithStyle(array(
                    $row["rfc"],
                    $row["razon"],
                    date("Y-m-d", strtotime($row["valido_desde"])),
                    date("Y-m-d", strtotime($row["valido_hasta"]))
                        ), $dstyle);
            }
            $writer->close();
            die();
        }  
        
        if ($tipoReporte == 80) { // reporte inventario de traficos
            $writer->addRowWithStyle(array(
                "I/E",
                "Patente",
                "Aduana",
                "Pedimento",
                "Referencia",
                "Cve.",
                "Nombre Cliente",
                "Contenedor / Caja",
                "Fecha Pago",
                "Fecha Liberación",
                "Semáforo",
                "Observaciones",
                "Expediente",
                    ), $tstyle);
            foreach ($rows as $row) {
                $writer->addRowWithStyle(array(
                    $row["ie"],
                    $row["patente"],
                    $row["aduana"],
                    $row["pedimento"],
                    $row["referencia"],
                    $row["cvePedimento"],
                    $row["nombreCliente"],
                    $row["contenedorCaja"],
                    ($row["fechaPago"]) ? date("Y-m-d", strtotime($row["fechaPago"])) : '',
                    ($row["fechaLiberacion"]) ? date("Y-m-d", strtotime($row["fechaLiberacion"])) : '',
                    $this->semaforo($row["semaforo"]),
                    $row["observacionSemaforo"],
                    ((int) $row["revisionOperaciones"] == 1) ? 'Sí' : '',
                        ), $dstyle);
            }
        }
                
        if ($tipoReporte == 81) { // Catalogo de partes proveedores
            $writer->addRowWithStyle(array(
                "Identificador",
                "Nombre",
                "Calle",
                "Num. Int.",
                "Num. Ext.",
                "Colonia",
                "Localidad",
                "Ciudad",
                "Estado",
                "C.P.",
                "País",
                    ), $tstyle);
            foreach ($rows as $row) {
                $writer->addRowWithStyle(array(
                    $row["identificador"],
                    $row["nombre"],
                    $row["calle"],
                    $row["numInt"],
                    $row["numExt"],
                    $row["colonia"],
                    $row["localidad"],
                    $row["ciudad"],
                    $row["estado"],
                    $row["codigoPostal"],
                    $row["pais"],
                        ), $dstyle);
            }
        }
                
        if ($tipoReporte == 82) { // Catalogo de partes proveedores
            $writer->addRowWithStyle(array(
                "Fraccion",
                "Num. Parte",
                "Descripción",
                "Pais Origen",
                "UMC",
                "UMT",
                "OMA",
                    ), $tstyle);
            foreach ($rows as $row) {
                $writer->addRowWithStyle(array(
                    $row["fraccion"],
                    $row["numParte"],
                    $row["descripcion"],
                    $row["paisOrigen"],
                    $row["umc"],
                    $row["umt"],
                    $row["oma"],
                        ), $dstyle);
            }
        }
                
        if ($tipoReporte == 83) { // Catalogo de partes clientes
            $writer->addRowWithStyle(array(
                "Identificador",
                "Nombre",
                "Calle",
                "Num. Int.",
                "Num. Ext.",
                "Colonia",
                "Localidad",
                "Ciudad",
                "Estado",
                "C.P.",
                "País",
                "Fraccion",
                "Num. Parte",
                "Descripción",
                "Pais Origen",
                "UMC",
                "UMT",
                "OMA",
                    ), $tstyle);
            foreach ($rows as $row) {
                $writer->addRowWithStyle(array(
                    $row["identificador"],
                    $row["nombre"],
                    $row["calle"],
                    $row["numInt"],
                    $row["numExt"],
                    $row["colonia"],
                    $row["localidad"],
                    $row["ciudad"],
                    $row["estado"],
                    $row["codigoPostal"],
                    $row["pais"],
                    $row["fraccion"],
                    $row["numParte"],
                    $row["descripcion"],
                    $row["paisOrigen"],
                    $row["umc"],
                    $row["umt"],
                    $row["oma"],
                        ), $dstyle);
            }
        }
        if ($tipoReporte == 84) { // traficos vs facturacion --- desde admin
            $writer->addRowWithStyle(array(
                "Patente",
                "Aduana",
                "Pedimento",
                "Referencia",
                "I/E",
                "Cve. Pedimento",
                "Nom. Cliente",
                "BL/Guia",
                "Nom. Buque",
                "Folio",
                "F. Facturacion",
                "F. Pago",
                "Pagos hechos",
                "G. sin comprobar",
                "Honorarios",
                "I.V.A.",
                "SubTotal",
                "Pagada",
                    ), $tstyle);
            foreach ($rows as $row) {
                $writer->addRowWithStyle(array(
                    $row["patente"],
                    $row["aduana"],
                    $row["pedimento"],
                    $row["referencia"],
                    $row["ie"],
                    $row["cvePedimento"],
                    $row["nombreCliente"],
                    $row["blGuia"],
                    $row["nombreBuque"],
                    $row["folio"],
                    $row["fechaFacturacion"],
                    $row["fechaPago"],
                    $row["pagoHechos"],
                    $row["sinComprobar"],
                    $row["honorarios"],
                    $row["iva"],
                    $row["subTotal"],
                    ($row["pagada"] != null) ? 'Si' : '',
                        ), $dstyle);
            }
        }
        
        if ($tipoReporte == 85) { // Catalogo de partes clientes
            $writer->addRowWithStyle(array(
                "Identificador",
                "Nombre",
                "Fraccion",
                "Num. Parte",
                "Descripción",
                "Pais Origen",
                "UMC",
                "UMT",
                "OMA",
                    ), $tstyle);
            foreach ($rows as $row) {
                $writer->addRowWithStyle(array(
                    $row["identificador"],
                    $row["nombreProveedor"],
                    $row["fraccion"],
                    $row["numParte"],
                    $row["descripcion"],
                    $row["paisOrigen"],
                    $row["umc"],
                    $row["umt"],
                    $row["oma"],
                        ), $dstyle);
            }
            $writer->close();
            die();
        }
        
        if ($tipoReporte == 100) { // reporte iva
            $writer->addRowWithStyle(array(
                "Operación",
                "I/E",
                "Referencia",
                "Cve. Ped.",
                "Tax Id",
                "Proveedor",
                "O. Fraccion",
                "Fraccion",
                "Desc.",
                "Valor",
                "I.V.A.",
                    ), $tstyle);
            foreach ($rows as $row) {
                $writer->addRowWithStyle(array(
                    $row["operacion"],
                    $row["impexp"],
                    $row["trafico"],
                    $row["cvePedimento"],
                    $row["taxID"],
                    $row["nomProveedor"],
                    $row["ordenFraccion"],
                    $row["fraccion"],
                    $row["descripcion"],
                    $row["valor"],
                    $row["iva"],
                        ), $dstyle);
            }
        }
        $writer->close();
    }
    
    public function reportesOperaciones($tipoReporte, $fechaIni, $fechaFin, $titles, $rows = null) {
        $border = (new BorderBuilder())
                ->setBorderBottom(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                ->build();
        $tstyle = (new StyleBuilder())
                ->setFontBold()
                ->setFontSize(10)
                ->setFontName("Arial")
                ->setFontColor(Color::BLACK)
                ->setBackgroundColor("c6d9f0")
                ->setBorder($border)
                ->build();
        $dstyle = (new StyleBuilder())
                ->setFontSize(10)
                ->setFontName("Arial")
                ->setFontColor(Color::BLACK)
                ->build();
        $writer = WriterFactory::create(Type::XLSX);
        if (APPLICATION_ENV == "production" || APPLICATION_ENV == "staging") {
            $writer->setTempFolder($this->appConfig->getParam("tmpDir"));            
        } else {
            $writer->setTempFolder("C:\\wamp64\\tmp\\reportes");            
        }
        if ($tipoReporte == "encabezado") {
            $writer->openToBrowser("REPORTE_ENCABEZADOS_" . $fechaIni . "_" . $fechaFin . ".xlsx");
        }
        if ($tipoReporte == "anexo") {
            $writer->openToBrowser("REPORTE_ANEXO_" . $fechaIni . "_" . $fechaFin . ".xlsx");
        }
        if ($tipoReporte == "encabezado") {
            $writer->addRowWithStyle($titles, $tstyle);
            foreach ($rows as $row) {
                $writer->addRowWithStyle(array(
                    $row["operacion"],
                    $row["tipoOperacion"],
                    $row["patente"],
                    $row["aduana"],
                    $row["pedimento"],
                    $row["trafico"],
                    $row["transporteEntrada"],
                    $row["transporteArribo"],
                    $row["transporteSalida"],
                    $row["fechaEntrada"],
                    $row["fechaPago"],
                    $row["firmaValidacion"],
                    $row["firmaBanco"],
                    $row["tipoCambio"],
                    $row["cvePed"],
                    $row["regimen"],
                    $row["valorDolares"],
                    $row["valorAduana"],
                    $row["fletes"],
                    $row["seguros"],
                    $row["embalajes"],
                    $row["otrosIncrementales"],
                    $row["dta"],
                    $row["iva"],
                    $row["igi"],
                    $row["prev"],
                    $row["cnt"],
                    $row["efectivo"],
                    $row["otrosEfectivo"],
                    $row["totalEfectivo"],
                    $row["pesoBruto"],
                    $row["bultos"],
                        ), $dstyle);
            }
        }
        if ($tipoReporte == "anexo") {
            $writer->addRowWithStyle($titles, $tstyle);
            foreach ($rows as $row) {
                $writer->addRowWithStyle(array(
                    $row["operacion"],
                    $row["tipoOperacion"],
                    $row["patente"],
                    $row["aduana"],
                    $row["pedimento"],
                    $row["trafico"],
                    $row["transporteEntrada"],
                    $row["transporteArribo"],
                    $row["transporteSalida"],
                    date('d/m/Y', strtotime($row["fechaEntrada"])),
                    date('d/m/Y', strtotime($row["fechaPago"])),
                    $row["firmaValidacion"],
                    $row["firmaBanco"],
                    $row["tipoCambio"],
                    $row["cvePed"],
                    $row["regimen"],
                    number_format($row["valorDolares"], 2, '.', ''),
                    number_format($row["valorAduana"], 0, '.', ''),
                    number_format($row["fletes"], 0, '.', ''),
                    number_format($row["seguros"], 0, '.', ''),
                    number_format($row["embalajes"], 0, '.', ''),
                    number_format($row["otrosIncrementales"], 0, '.', ''),
                    number_format($row["dta"], 0, '.', ''),
                    number_format($row["ivaPedimento"], 0, '.', ''),
                    number_format($row["igi"], 0, '.', ''),
                    number_format($row["prev"], 0, '.', ''),
                    number_format($row["cnt"], 0, '.', ''),
                    number_format($row["efectivo"], 0, '.', ''),
                    number_format($row["otrosEfectivo"], 0, '.', ''),
                    number_format($row["totalEfectivo"], 0, '.', ''),
                    number_format($row["pesoBruto"], 4, '.', ''),
                    number_format($row["bultos"], 0, '.', ''),
                    $row["guias"],
                    $row["planta"],
                    $row["numFactura"],
                    $row["cove"],
                    date('d/m/Y', strtotime($row["fechaFactura"])),
                    $row["incoterm"],
                    number_format($row["valorFacturaUsd"], 2, '.', ''),
                    number_format($row["valorFacturaMonExt"], 2, '.', ''),
                    $row["nomProveedor"],
                    $row["taxId"],
                    $row["paisFactura"],
                    $row["divisa"],
                    number_format($row["factorMonExt"], 6, '.', ''),
                    $row["numParte"],
                    $row["descripcion"],
                    $row["fraccion"],
                    $row["ordenFraccion"],
                    number_format($row["precioUnitario"], 5, '.', ''),
                    $row["umc"],
                    number_format($row["cantUmc"], 3, '.', ''),
                    $row["umt"],
                    number_format($row["cantUmt"], 3, '.', ''),
                    $row["paisOrigen"],
                    $row["tlc"],
                    $row["tlcan"],
                    $row["tlcue"],
                    $row["prosec"],
                    number_format($row["ivaParte"], 2, '.', ''),
                    number_format($row["tasaAdvalorem"], 2, '.', ''),
                    $row["paisVendedor"],
                    $row["patenteOrig"],
                    $row["aduanaOrig"],
                    $row["pedimentoOrig"]
                        ), $dstyle);
            }
        }
        $writer->close();
    }

}
