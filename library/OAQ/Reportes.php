<?php

/**
 * Description of Reportes
 *
 * @author Jaime
 */

error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED & ~E_USER_DEPRECATED & ~E_USER_NOTICE & ~E_USER_ERROR);

class OAQ_Reportes {

    protected $_excel;
    protected $_titles;
    protected $_data;
    protected $_type;

    function set_titles($_titles) {
        $this->_titles = $_titles;
    }

    function set_data($_data) {
        $this->_data = $_data;
    }

    function set_type($_type) {
        $this->_type = $_type;
    }

    function __construct() {
        error_reporting(E_ALL);
        ini_set("include_path", ini_get("include_path") . ";../Classes/");
        include "PHPExcel.php";
        include "PHPExcel/Writer/Excel2007.php";
        $this->_excel = new PHPExcel();
        $this->_excel->getProperties()->setCreator("Jaime E. Valdez");
        $this->_excel->getProperties()->setLastModifiedBy("Jaime E. Valdez");
        $this->_excel->getProperties()->setTitle("Reporte");
        $this->_excel->getProperties()->setSubject("Reporte");
        $this->_excel->getProperties()->setDescription("");
    }

    public function simpleReport() {
        foreach ($this->_titles["titulos"] as $k => $v) {
            $titulos[] = $k;
            $datos[] = $v;
        }
        $this->_excel->setActiveSheetIndex(0);
        $this->_excel->getActiveSheet()->fromArray($titulos, null, "A1");
        $this->_excel->getActiveSheet()->setTitle(strtoupper($this->_type));
        foreach ($this->_data as $k => $v) {
            foreach ($v as $i => $p) {
                if (in_array($i, $datos)) {
                    $this->_excel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(array_search($i, $datos)) . ($k + 2), $p);
                }
            }
        }
        $titles = array(
            "font" => array(
                "bold" => true,
                "name" => "Arial",
                "size" => 9,
            ),
            "alignment" => array(
                "horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            "borders" => array(
                "allborders" => array(
                    "style" => PHPExcel_Style_Border::BORDER_THIN,
                    "color" => array("argb" => "FF000000"),
                )
            ),
            "fill" => array(
                "type" => PHPExcel_Style_Fill::FILL_SOLID,
                "color" => array("rgb" => "B8CCE4")
            ),
        );
        $info = array(
            "font" => array(
                "name" => "Arial",
                "size" => 9,
            ),
            "borders" => array(
                "allborders" => array(
                    "style" => PHPExcel_Style_Border::BORDER_THIN,
                    "color" => array("rgb" => "999999"),
                )
            ),
        );
        $this->_excel->getActiveSheet()->getStyle("A1:" . PHPExcel_Cell::stringFromColumnIndex(count($datos) - 1) . "1")->applyFromArray($titles);
        $this->_excel->getActiveSheet()->getStyle("A2:" . PHPExcel_Cell::stringFromColumnIndex(count($datos) - 1) . (count($this->_data) + 1))->applyFromArray($info);
        foreach (range(0, count($datos)) as $col) {
            $this->_excel->getActiveSheet()->getColumnDimensionByColumn($col)->setAutoSize(true);
        }
    }

    public function download() {
        $objWriter = new PHPExcel_Writer_Excel2007($this->_excel);
        $filename = strtoupper($this->_type) . "_" . date("Ymd-His") . ".xlsx";
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment;filename=\"{$filename}\"");
        header("Cache-Control: max-age=0");
        $objWriter->save("php://output");
    }

    public function fromHtml() {
        $inputFileName = "/tmp/cargoquin-facturas.html";
        $outputFileType = "Excel2007";
        $outputFileName = "/tmp/" . "CARGOQIUIN_" . date("YmdHis") . ".xlsx";

        $objPHPExcel = new PHPExcel();
        $excelHTMLReader = PHPExcel_IOFactory::createReader("HTML");
        $excelHTMLReader->setSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle("Assessment PN");
        $excelHTMLReader->loadIntoExisting($inputFileName, $objPHPExcel);
        $excelHTMLReader->setSheetIndex(1);
        $excelHTMLReader->loadIntoExisting("/tmp/cargoquin-fracciones.html", $objPHPExcel);
        $excelHTMLReader->setSheetIndex(2);
        $excelHTMLReader->loadIntoExisting("/tmp/cargoquin-partes.html", $objPHPExcel);
        $objPHPExcel->getSheet(0)->setTitle("Assessment PN");
        $objPHPExcel->getSheet(1)->setTitle("Tariff Report");
        $objPHPExcel->getSheet(2)->setTitle("PN Report");
        $objPHPExcel->setActiveSheetIndex(0);


        $objPHPExcelWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $outputFileType);
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment;filename='" . pathinfo($outputFileName, PATHINFO_BASENAME) . "'");
        header("Content-Description: File Transfer");
        header("Accept-Ranges: bytes");
        header("Content-Transfer-Encoding: binary");
        ob_clean();
        flush();
        $objPHPExcelWriter->save("php://output");
    }

    public function anexoHeaders($tipo) {
        $array["tecnico"] = array(
            "titulos" => array(
                "CodigoCliente" => "codigoCliente",
                "FechaFactura" => "fechaFactura",
                "FactorModena" => "factorMoneda",
                "Incoterm" => "incoterm",
                "Destino" => "paisFactura",
                "Comprador" => "comprador",
                "Numero de parte" => "numParte",
                "Unidad" => "unidad",
                "Cantidad" => "cantidad",
                "Precio Unitario" => "precioUnitario",
                "Importacion Afectada" => "numFactura",
                "Factura Afectada" => "numFactura",
                "Constancia de Transferencia/Folio de la Venta/Folio del Ajuste Anual" => null,
                "Cove" => "cove",
                "Nota Interna detalle" => null,
                "Tipo de Partida a descargar" => null,
                "Valor agregado" => "valorAgregado",
                "EB" => null,
                "Monto EB" => null,
                "Proveedor" => "nomProveedor",
                "CNT" => "cnt",
                "Nota Interna (2) detalle" => null,
            ),
            "numbers" => array(
                "precioUnitario" => 6
            ),
            "dates" => array(),
        );
        $array["proveedores"] = array(
            "titulos" => array(
                "NomProveedor" => "nomProveedor",
                "TaxId" => "taxId",
                "PaisOrigen" => "paisOrigen",
                "PaisVendedor" => "paisVendedor",
            ),
            "numbers" => array(),
            "dates" => array(),
        );
        $array["cnh"] = array(
            "titulos" => array(
                "Paginas" => "paginas",
                "Referencia" => "trafico",
                "Pedimento" => "pedimento",
                "Patente" => "patente",
                "Aduana" => "aduana",
                "Seccion" => "seccion", // falta
                "Tpo.Oper" => "tipoOperacion",
                "Cve" => "cvePed",
                "Regimen" => "regimen",
                "Dest / Orig" => "destino", // falta
                "Tpo.Cambio" => "tipoCambio",
                "Peso KG" => "pesoBruto",
                "Transporte Entrada/Salida" => "transporteEntrada",
                "Transporte Arribo" => "transporteArribo",
                "Transporte Salida" => "transporteSalida",
                "Valor Dolares" => "valorDolares",
                "Valor Aduana" => "valorAduana",
                "Valor Comercial" => "valorComecial",
                "Empresa" => "empresa",
                "Seguros" => "valorSeguros", // falta
                "Seguro" => "seguros",
                "Flete" => "fletes",
                "Embalajes" => "embalajes",
                "Incrementales" => "otrosIncrementales",
                "Bls" => "bl",
                "Fecha Entrada" => "fechaEntrada",
                "Fecha Pago" => "fechaPago",
                "IGI" => "igi",
                "IVA" => "iva",
                "DTA" => "dta",
                "PRV" => "prev",
                "Multas" => "multas",
                "Recargos" => "recargos",
                "CC" => "cnt",
                "Art 303" => "art",
                "RT" => "rt",
                "Otros" => "otros",
                "ISAN" => "isan",
                "IEPS" => "ieps",
                "Forma Pago IGI" => "formaPagoIgi", // falta 0
                "Forma Pago IVA" => "formaPagoIva", // falta 0
                "Forma Pago DTA" => "formaPagoDta", // falta 0
                "Forma Pago PRV" => "formaPagoPrev", // falta 0
                "Forma Pago Multas" => "formaPagoMultas",
                "Forma Pago Recargos" => "formaPagoRecargos",
                "Forma Pago CC" => "formaPagoCc",
                "Forma Pago Art 302" => "formaPagoArt",
                "Forma Pago RT" => "formaPagoRt",
                "Forma Pago Otros" => "formaPagoOtros",
                "Forma Pago ISAN" => "formaPagoIsan",
                "Forma Pago IEPS" => "formaPagoIeps",
                "Total" => "totalEfectivo",
                "Proveedor" => "nomProveedor",
                "Vinculacion" => "vinculacion",
                "No. Guia Mstr" => "guiaMaster",
                "No. Guia House" => "numGuia", // falta
                "Talon Flete" => "talon",
                "Observaciones" => "observaciones",
                "Fecha Despacho Puerto" => "fechaPago",
                "Fecha Embarque" => "fechaPago",
                "Fecha Prevalidacion" => "fechaPago",
                "Fecha Revision" => "fechaRevision",
                "Transportista" => "transportista",
                "Consolidador" => "consolidados",
                "Moneda" => "divisa",
                "R1 o A3" => "",
                "Pedimento Original" => "pedimentoOrig",
                "RFC" => "rfc", // falta
            ),
            "numbers" => array(
                "totalEfectivo" => 2,
                "valorDolares" => 2,
                "valorAduana" => 2,
                "valorSeguros" => 2,
                "fletes" => 2,
                "seguros" => 2,
                "embalajes" => 2,
                "otrosIncrementales" => 2,
                "dta" => 2,
                "iva" => 2,
                "igi" => 2,
                "prev" => 2,
                "cnt" => 2,
                "pesoBruto" => 1,
                "tipoCambio" => 4,
                "valorFacturaUsd" => 4,
                "valorFacturaMonExt" => 4,
                "factorMonExt" => 6,
                "precioUnitario" => 6,
                "total" => 4,
                "cantUmc" => 4,
                "cantUmt" => 4,
                "tasaAdvalorem" => 2,
            ),
            "dates" => array(
                "fechaEntrada" => "d/m/Y",
                "fechaPago" => "d/m/Y",
                "fechaFactura" => "d/m/Y",
            ),
        );
        $array["prasad"] = array(
            "titulos" => array(
                "Referencia" => "operacion",
                "CvePedimento" => "cvePed",
                "ReferenciaAA" => "trafico",
                "ClaveProyecto" => "claveProyecto",
                "NumFactura" => "numFactura",
                "NumParte" => "numParte",
                "PaisOrigen" => "paisOrigen",
                "Secuencia" => "secuencia",
                "UMC" => "umc",
                "CantUMC" => "cantUmc",
                "PrecioUnitario" => "precioUnitario",
                "Total" => "total",
                "PatenteOrig" => "patenteOrig",
                "PedimentoOrig" => "pedimentoOrig",
                "AduanaOrig" => "aduanaOrig",
            ),
            "numbers" => array(
                "precioUnitario" => 6,
                "total" => 4,
                "cantUmc" => 4,
            ),
            "dates" => array(),
        );
        $array["anexo"] = array(
            "titulos" => array(
                "Referencia" => "operacion",
                "TipoOperacion" => "tipoOperacion",
                "Patente" => "patente",
                "Aduana" => "aduana",
                "Pedimento" => "pedimento",
                "Trafico" => "trafico",
                "TransporteEntrada" => "transporteEntrada",
                "TransporteArribo" => "transporteArribo",
                "TransporteSalida" => "transporteSalida",
                "FechaEntrada" => "fechaEntrada",
                "FechaPago" => "fechaPago",
                "FirmaValidacion" => "firmaValidacion",
                "FirmaBanco" => "firmaBanco",
                "TipoCambio" => "tipoCambio",
                "CvePed" => "cvePed",
                "Regimen" => "regimen",
                "ValorDolares" => "valorDolares",
                "ValorAduana" => "valorAduana",
                "Fletes" => "fletes",
                "Seguros" => "seguros",
                "Embalajes" => "embalajes",
                "OtrosIncrementales" => "otrosIncrementales",
                "DTA" => "dta",
                "IVA" => "ivaPedimento",
                "IGI" => "igi",
                "PREV" => "prev",
                "CNT" => "cnt",
                "Efectivo" => "totalEfectivo",
                "Otros" => "otrosEfectivo",
                "Total" => "totalEfectivo",
                "PesoBruto" => "pesoBruto",
                "Bultos" => "bultos",
                "Guias" => "guias",
                "Planta" => "planta",
                "NumFactura" => "numFactura",
                "Cove" => "cove",
                "FechaFactura" => "fechaFactura",
                "Incoterm" => "incoterm",
                "ValorFacturaUsd" => "valorFacturaUsd",
                "ValorFacturaMonExt" => "valorFacturaMonExt",
                "NomProveedor" => "nomProveedor",
                "TaxId" => "taxId",
                "PaisFactura" => "paisFactura",
                "Divisa" => "divisa",
                "FactorMonExt" => "factorMonExt",
                "NumParte" => "numParte",
                "Descripcion" => "descripcion",
                "Fraccion" => "fraccion",
                "Orden Fracción" => "ordenFraccion",
                "PrecioUnitario" => "precioUnitario",
                "UMC" => "umc",
                "CantUMC" => "cantUmc",
                "UMT" => "umt",
                "CantUMT" => "cantUmt",
                "PaisOrigen" => "paisOrigen",
                "TLC" => "tlc",
                "TLCAN" => "tlcan",
                "TLCUE" => "tlcue",
                "PROSEC" => "prosec",
                "IVA (%)" => "ivaParte",
                "TasaAdvalorem" => "tasaAdvalorem",
                "PaisVendedor" => "paisVendedor",
                "PatenteOrig" => "patenteOrig",
                "AduanaOrig" => "aduanaOrig",
                "PedimentoOrig" => "pedimentoOrig",
            ),
            "numbers" => array(
                "totalEfectivo" => 2,
                "valorDolares" => 2,
                "valorAduana" => 2,
                "fletes" => 2,
                "seguros" => 2,
                "embalajes" => 2,
                "otrosIncrementales" => 2,
                "dta" => 2,
                "iva" => 2,
                "igi" => 2,
                "prev" => 2,
                "cnt" => 2,
                "pesoBruto" => 1,
                "tipoCambio" => 4,
                "valorFacturaUsd" => 4,
                "valorFacturaMonExt" => 4,
                "factorMonExt" => 6,
                "precioUnitario" => 6,
                "total" => 4,
                "cantUmc" => 4,
                "cantUmt" => 4,
                "tasaAdvalorem" => 2,
            ),
            "dates" => array(
                "fechaEntrada" => "d/m/Y",
                "fechaPago" => "d/m/Y",
                "fechaFactura" => "d/m/Y",
            ),
        );
        $array["encabezado"] = array(
            "titulos" => array(
                "Referencia" => "operacion",
                "TipoOperacion" => "tipoOperacion",
                "Patente" => "patente",
                "Aduana" => "aduana",
                "Pedimento" => "pedimento",
                "Trafico" => "trafico",
                "TransporteEntrada" => "transporteEntrada",
                "TransporteArribo" => "transporteArribo",
                "TransporteSalida" => "transporteSalida",
                "FechaEntrada" => "fechaEntrada",
                "FechaPago" => "fechaPago",
                "FirmaValidacion" => "firmaValidacion",
                "FirmaBanco" => "firmaBanco",
                "TipoCambio" => "tipoCambio",
                "CvePed" => "cvePed",
                "Regimen" => "regimen",
                "ValorDolares" => "valorDolares",
                "ValorAduana" => "valorAduana",
                "Fletes" => "fletes",
                "Seguros" => "seguros",
                "Embalajes" => "embalajes",
                "OtrosIncrementales" => "otrosIncrementales",
                "DTA" => "dta",
                "IVA" => "iva",
                "IGI" => "igi",
                "PREV" => "prev",
                "CNT" => "cnt",
                "Efectivo" => "efectivo",
                "Otros" => "otrosEfectivo",
                "Total" => "totalEfectivo",
                "PesoBruto" => "pesoBruto",
                "Bultos" => "bultos",
                "Planta" => "planta",
            ),
            "numbers" => array(
                "totalEfectivo" => 2,
                "valorDolares" => 2,
                "valorAduana" => 2,
                "fletes" => 2,
                "seguros" => 2,
                "embalajes" => 2,
                "otrosIncrementales" => 2,
                "dta" => 2,
                "iva" => 2,
                "igi" => 2,
                "prev" => 2,
                "cnt" => 2,
                "pesoBruto" => 1,
                "tipoCambio" => 4,
            ),
            "dates" => array(
                "fechaEntrada" => "d/m/Y",
                "fechaPago" => "d/m/Y",
            ),
        );
        if (isset($array[$tipo])) {
            return $array[$tipo];
        }
    }

    public function obtenerLayout($tipo) {
        $layout = null;
        switch ($tipo) {
            case "encabezado":
                $layout = "default.phtml";
                break;
            case "prasad":
                $layout = "default.phtml";
                break;
            case "anexo":
                $layout = "default.phtml";
                break;
            case "cnh":
                $layout = "default.phtml";
                break;
            case "tecnico":
                $layout = "default.phtml";
                break;
            default:
                $layout = "default.phtml";
                break;
        }
        return $layout;
    }

    public function rfcCliente($idCliente) {
        $mppr = new Trafico_Model_ClientesMapper();
        if (($rfcCliente = $mppr->obtenerRfcCliente($idCliente))) {
            return $rfcCliente;
        }
        return;
    }

    public function obtenerDesglose($patente, $aduana, $pedimento) {
        $mppr = new Automatizacion_Model_RptPedimentos();
        $row = $mppr->wsObtenerPedimento($patente, $aduana, $pedimento);
        if (!empty($row)) {
            $mpr = new Automatizacion_Model_RptPedimentoDesglose();
            $invoices = $mpr->wsObtenerFacturas($row['id']);
            if (!empty($invoices)) {
                foreach ($invoices as $k => $v) {
                    $p = $mpr->wsObtenerPartes($row['id'], $v['numFactura']);
                    if (!empty($p)) {
                        $invoices[$k]['partes'] = $p;
                    }
                }
                $row['facturas'] = $invoices;
            }
            return $row;
        }
        return;
    }

    public function obtenerDatos($tipoLayout, $idAduana, $rfcCliente, $fechaIni, $fechaFin) {
        $mdl = new Trafico_Model_TraficoAduanasMapper();
        $row = $mdl->aduana($idAduana);
        if (in_array($tipoLayout, array("encabezado", "prasad", "anexo", "proveedores", "cnh"))) {
            if ($tipoLayout == "anexo") {
                $mppr = new Automatizacion_Model_RptPedimentos();
                $arr = $mppr->reporteAnexo($row["patente"], $row["aduana"], $rfcCliente, $fechaIni, $fechaFin);
                if (!empty($arr)) {
                    return $arr;
                }
                return;
            }
            if ($tipoLayout == "encabezado") {
                $mppr = new Automatizacion_Model_RptPedimentos();
                $arr = $mppr->reporteEncabezado($row["patente"], $row["aduana"], $rfcCliente, $fechaIni, $fechaFin);
                if (!empty($arr)) {
                    return $arr;
                }
                return;
            }
        }
    }

}
