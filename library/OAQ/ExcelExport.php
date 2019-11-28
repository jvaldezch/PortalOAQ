<?php
require_once "Spout/Autoloader/autoload.php";
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;

/**
 * Envio de emails que usa plantilla que se leen basadas en DOM parsing
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class OAQ_ExcelExport {

    protected $_session;
    protected $_config;

    function __construct() {
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
    }

    public function layoutTecico($titles, $data) {
        $writer = WriterFactory::create(Type::XLSX);
        $writer->openToBrowser("/tmp/eccc.xslx"); // stream data directly to the browser
        $writer->addRow($titles); // add a row at a time
        $writer->addRows($data); // add multiple rows at a time
        $writer->close();
    }

    /**
     * 
     * @param array $headers
     * @param array $data
     * @param String $reportFilename
     * @param String $sheetTitle
     * @param String $fechaIni
     * @param String $fechaFin
     * @param String $reportTitle
     * @param String $rfc
     * @param String $nombreCliente
     */
    public function createSimpleReport($headers, $data, $reportFilename, $sheetTitle, $fechaIni, $fechaFin, $reportTitle = null, $rfc = null, $nombreCliente = null, $saveTmp = null) {
        $search = NULL ? $search = new Zend_Session_Namespace('') : $search = new Zend_Session_Namespace('OAQCtaGastos');
        //  http://phpexcel.codeplex.com/discussions/70125
        $reportName = array(
            'font' => array(
                'bold' => true,
                'size' => 15,
            ),
        );
        $titles = array(
            'font' => array(
                'bold' => true,
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                    'color' => array('argb' => 'FF000000'),
                )
            ),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'B8CCE4')
            ),
        );

        $info = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb' => '999999'),
                )
            ),
        );

        ini_set('include_path', ini_get('include_path') . ';../Classes/');
        include 'PHPExcel.php';
        include 'PHPExcel/Writer/Excel2007.php';
        $objPHPExcel = new PHPExcel();

        $objPHPExcel->getProperties()->setCreator("Jaime E. Valdez");
        $objPHPExcel->getProperties()->setLastModifiedBy("Jaime E. Valdez");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Reporte");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Reporte");
        $objPHPExcel->getProperties()->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.");

        if (!isset($saveTmp)) {
            if (!isset($nombreCliente)) {
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, $sheetTitle);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->applyFromArray($reportName);
            } else if ($rfc != '' && !isset($nombreCliente)) {
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, preg_match('/Organización Aduanal de Querétaro/i', $nombreCliente) ? $nombreCliente : $nombreCliente . " (" . $rfc . ")");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->applyFromArray($reportName);
            } else {
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, $nombreCliente);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->applyFromArray($reportName);
            }
        } elseif (isset($reportTitle)) {
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, $reportTitle);
            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->applyFromArray($reportName);
        } else {
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, "Organización Aduanal de Querétaro, S.C.");
            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->applyFromArray($reportName);
        }

        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 2, 'Fecha de corte:');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->applyFromArray($titles);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 2, (isset($fechaIni)) ? $fechaIni : date('Y/m/d'));
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, 2)->applyFromArray($info);

        if ($fechaFin) {
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 2, 'Fecha de corte:');
            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, 2)->applyFromArray($titles);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 2, $fechaFin);
            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, 2)->applyFromArray($info);
        }

        $objPHPExcel->setActiveSheetIndex(0);
        $column = 0;
        $row = 4;
        foreach ($headers as $k => $v):
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($column, $row, $k);
            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $row)->applyFromArray($titles);
            $column++;
        endforeach;

        $monedas = array('total', 'impuestos_aduanales', 'iva', 'anticipo', 'valor_aduana', 'honorarios', 'gastos_complementarios', 'gastos_alijadores', 'revalidacion', 'rectificaciones', 'subtotal', 'maniobras', 'almacenaje', 'demoras', 'fleteaereo', 'fletemaritimo', 'fletesacarreos', 'subtotal_maniobras', 'iva_maniobras', 'subtotal_almacenaje', 'iva_almacenaje', 'subtotal_demoras', 'iva_demoras', 'subtotal_fleteaereo', 'iva_fleteaereo', 'subtotal_fletemaritimo', 'iva_fletemaritimo', 'subtotal_fleteterrestre', 'iva_fleteterrestre', 'subtotal_fletesacarreos', 'iva_fletesacarreos', 'cargo', 'abono', 'saldo', 'comprobados', 'complementarios');
        $texto = array('ref_factura');
        $total = 0;
        $num = count($data) + $row + 1;
        for ($i = $row + 1; $i < $num; $i++) {
            $column = 0;
            foreach ($headers as $k => $v) {
                $pos = $i - $row - 1;
                if (isset($data[$pos][$headers[$k]])) {
                    $value = $data[$pos][$headers[$k]];
                } else {
                    $value = '';
                }
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($column, $i, utf8_decode($value));
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $i)->applyFromArray($info);
                if (in_array($v, $monedas)) {
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $i)->getNumberFormat()->setFormatCode('$ #,##0.00');
                } else if (in_array($v, $texto)) {
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $i)->getNumberFormat()->setFormatCode('0');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                }
                $column++;

                if ($k == 'Total') {
                    $total = $total + $data[$pos][$headers[$k]];
                }
            }
        }
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(16.5);
        if ($search->desglose == '0') {
            foreach (range(1, 27) as $columnID) {
                $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($columnID)
                        ->setAutoSize(true);
            }
        } else if ($search->desglose == '1') {
            foreach (range(1, 42) as $columnID) {
                $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($columnID)
                        ->setAutoSize(true);
            }
        } else if ($reportFilename == 'envio_corresponsales') {
            foreach (range(0, 6) as $columnID) {
                $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($columnID)
                        ->setAutoSize(true);
            }
        } else if ($reportFilename == 'cobranza') {
            foreach (range(0, 3) as $columnID) {
                $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($columnID)
                        ->setAutoSize(true);
            }
        }

        $noSum = array(
            'envio_corresponsales',
            'pedimentospag',
            'tiemposrecu',
            'rptcoves',
            'rptdigitalizacion',
            'Pedimentos',
            'rptanexo24',
            'ingresoscorr',
        );

        if (!in_array($reportFilename, $noSum)) {
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($column - 2, $i, 'Total');
            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column - 2, $i)->applyFromArray($titles);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($column - 1, $i, $total);
            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column - 1, $i)->getNumberFormat()->setFormatCode('$ #,##0.00');
            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column - 1, $i)->applyFromArray($info);
        }

        $objPHPExcel->getActiveSheet()->setTitle('OAQ');

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

        if (!$saveTmp) {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            if (!isset($rfc)) {
                header('Content-Disposition: attachment;filename="' . $reportFilename . '_' . date('Y-m-d') . '.xlsx"');
            } else {
                header('Content-Disposition: attachment;filename="' . $rfc . "_" . $reportFilename . '_' . date('Y-m-d') . '.xlsx"');
            }
            header('Cache-Control: max-age=0');
            $objWriter->save('php://output');
        } else {
            $session = NULL ? $session = new Zend_Session_Namespace('') : $session = new Zend_Session_Namespace($this->_config->app->namespace);

            $emails = new OAQ_EmailNotifications($session->email, $session->username, $session->nombre);
            $search = NULL ? $search = new Zend_Session_Namespace('') : $search = new Zend_Session_Namespace('OAQCobranza');

            $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
            $filename = "CXC_Sumarizado_" . date('Y-m-d') . ".xlsx";
            $filepath = $config->app->tmp . $filename;
            $objWriter->save($filepath);

            $emails->sendEmailWithAttachment($search->emails, $filename, $filepath, 'Programación de cobranza al día ' . date('Y-m-d'), 'cxcSumarizado.html');
            unset($session);
            unlink($filepath);
        }

        $objPHPExcel->disconnectWorksheets();
        unset($objPHPExcel);
    }

    /**
     * 
     * @param array $headers
     * @param array $data
     * @param String $reportFilename
     * @param String $sheetTitle
     * @param String $fechaIni
     * @param String $fechaFin
     * @param String $reportTitle
     * @param String $rfc
     * @param String $nombreCliente
     */
    public function createCustomersReport($headers, $data, $reportFilename, $sheetTitle, $reportTitle = null) {
        //  http://phpexcel.codeplex.com/discussions/70125
        $titles = array(
            'font' => array(
                'bold' => true,
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                    'color' => array('argb' => 'FF000000'),
                )
            ),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'B8CCE4')
            ),
        );

        $info = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb' => '999999'),
                )
            ),
        );

        ini_set('include_path', ini_get('include_path') . ';../Classes/');
        include 'PHPExcel.php';
        include 'PHPExcel/Writer/Excel2007.php';
        $objPHPExcel = new PHPExcel();

        $objPHPExcel->getProperties()->setCreator("Jaime E. Valdez");
        $objPHPExcel->getProperties()->setLastModifiedBy("Jaime E. Valdez");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Reporte");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Reporte");
        $objPHPExcel->getProperties()->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.");

        $objPHPExcel->setActiveSheetIndex(0);
        $column = 0;
        $row = 1;
        foreach ($headers as $k => $v):
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($column, $row, $k);
            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $row)->applyFromArray($titles);
            $column++;
        endforeach;

        $num = count($data) + $row + 1;
        for ($i = $row + 1; $i < $num; $i++) {
            $column = 0;
            foreach ($headers as $k => $v) {
                $pos = $i - $row - 1;
                if (isset($data[$pos][$headers[$k]])) {
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($column, $i, $data[$pos][$headers[$k]]);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $i)->applyFromArray($info);
                    $column++;
                } else {
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($column, $i, '');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $i)->applyFromArray($info);
                    $column++;
                }
            }
        }

        $objPHPExcel->getActiveSheet()->setTitle('OAQ');

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $reportFilename . '_' . date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
        $objPHPExcel->disconnectWorksheets();
        unset($objPHPExcel);
    }

    public function traficReport($headers, $data, $reportFilename, $sheetTitle, $aduana, $reportTitle = null) {
        $reportName = array(
            'font' => array(
                'bold' => true,
                'size' => 15,
            ),
        );
        $titles = array(
            'font' => array(
                'bold' => true,
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                    'color' => array('argb' => 'FF000000'),
                )
            ),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'B8CCE4')
            ),
        );

        $info = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb' => '999999'),
                )
            ),
        );

        ini_set('include_path', ini_get('include_path') . ';../Classes/');
        include 'PHPExcel.php';
        include 'PHPExcel/Writer/Excel2007.php';
        $objPHPExcel = new PHPExcel();

        $objPHPExcel->getProperties()->setCreator("Jaime E. Valdez");
        $objPHPExcel->getProperties()->setLastModifiedBy("Jaime E. Valdez");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Reporte");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Reporte");
        $objPHPExcel->getProperties()->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.");

        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, 'Fecha de corte:');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->applyFromArray($titles);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 1, date('Y-m-d'));
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, 1)->applyFromArray($info);

        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 1, 'Aduana:');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, 1)->applyFromArray($titles);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 1, ($aduana == '') ? 'Todas' : $aduana );
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, 1)->applyFromArray($info);

        $objPHPExcel->setActiveSheetIndex(0);
        $column = 0;
        $row = 2;
        foreach ($headers as $k => $v):
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($column, $row, $k);
            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $row)->applyFromArray($titles);
            $column++;
        endforeach;

        $num = count($data) + $row + 1;
        for ($i = $row + 1; $i < $num; $i++) {
            $column = 0;
            foreach ($headers as $k => $v) {
                $pos = $i - $row - 1;
                if (isset($data[$pos][$headers[$k]])) {
                    if ($k == 'Días') {
                        if ($data[$pos][$headers[$k]] != '1969-12-31 18:00:00') {
                            $today = time();
                            $ref = strtotime($data[$pos][$headers[$k]]);
                            $cellInfo = floor(($today - $ref) / 60 / 60 / 24);
                        } else {
                            $cellInfo = '';
                        }
                    } else if ($k == 'Fecha entrada') {
                        $cellInfo = ($data[$pos][$headers[$k]] == 'Array') ? '' : date('Y-m-d', strtotime($data[$pos][$headers[$k]]));
                    } else {
                        $cellInfo = ($data[$pos][$headers[$k]] == 'Array') ? '' : $data[$pos][$headers[$k]];
                    }
                } else {
                    $cellInfo = '';
                }
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $i)->applyFromArray($info);
                if ($k == 'BL/Guía') {
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit(
                            $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($column, $i)->getCoordinate(), $cellInfo, PHPExcel_Cell_DataType::TYPE_STRING
                    );
                } else {
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($column, $i, $cellInfo);
                }
                $column++;
            }
        }

        $objPHPExcel->getActiveSheet()->setTitle('OAQ');

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        if (!isset($rfc)) {
            header('Content-Disposition: attachment;filename="' . $reportFilename . '_' . date('Y-m-d') . '.xlsx"');
        } else {
            header('Content-Disposition: attachment;filename="' . $rfc . "_" . $reportFilename . '_' . date('Y-m-d') . '.xlsx"');
        }
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
        $objPHPExcel->disconnectWorksheets();
        unset($objPHPExcel);
    }

    public function anexo24Report($headers, $data, $reportFilename, $sheetTitle, $aduana, $reportTitle = null) {
        $reportName = array(
            'font' => array(
                'bold' => true,
                'size' => 15,
            ),
        );
        $titles = array(
            'font' => array(
                'bold' => true,
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                    'color' => array('argb' => 'FF000000'),
                )
            ),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'B8CCE4')
            ),
        );

        $info = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb' => '999999'),
                )
            ),
        );

        ini_set('include_path', ini_get('include_path') . ';../Classes/');
        include 'PHPExcel.php';
        include 'PHPExcel/Writer/Excel2007.php';
        $objPHPExcel = new PHPExcel();

        $objPHPExcel->getProperties()->setCreator("Jaime E. Valdez");
        $objPHPExcel->getProperties()->setLastModifiedBy("Jaime E. Valdez");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Reporte");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Reporte");
        $objPHPExcel->getProperties()->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.");

        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, 'Fecha de corte:');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->applyFromArray($titles);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 1, date('Y-m-d'));
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, 1)->applyFromArray($info);

        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 1, 'Aduana:');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, 1)->applyFromArray($titles);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 1, ($aduana == '') ? 'Todas' : $aduana );
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, 1)->applyFromArray($info);

        $objPHPExcel->setActiveSheetIndex(0);
        $column = 0;
        $row = 2;
        foreach ($headers as $k => $v):
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($column, $row, $k);
            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $row)->applyFromArray($titles);
            $column++;
        endforeach;

        $num = count($data) + $row + 1;
        for ($i = $row + 1; $i < $num; $i++) {
            $column = 0;
            foreach ($headers as $k => $v) {
                $pos = $i - $row - 1;
                $cellInfo = ($data[$pos][$headers[$k]] == 'Array') ? '' : $data[$pos][$headers[$k]];
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($column, $i, $cellInfo);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $i)->applyFromArray($info);
                $column++;
            }
        }

        $objPHPExcel->getActiveSheet()->setTitle('OAQ');

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        if (!isset($rfc)) {
            header('Content-Disposition: attachment;filename="' . $reportFilename . '_' . date('Y-m-d') . '.xlsx"');
        } else {
            header('Content-Disposition: attachment;filename="' . $rfc . "_" . $reportFilename . '_' . date('Y-m-d') . '.xlsx"');
        }
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
        $objPHPExcel->disconnectWorksheets();
        unset($objPHPExcel);
    }

}

?>
