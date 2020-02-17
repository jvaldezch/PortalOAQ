<?php

class Clientes_DataController extends Zend_Controller_Action {

    protected $_session;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
    }

    public function preDispatch() {
        $this->_session = NULL ? $this->_session = new Zend_Session_Namespace('') : $this->_session = new Zend_Session_Namespace($this->_config->app->namespace);
        if ($this->_session->authenticated == true) {
            $this->_session->setExpirationSeconds($this->_appconfig->getParam('session-exp'));
        } else {
            $this->getResponse()->setRedirect('/default/index/logout');
        }
    }

    public function excelReporteAnexo24Action() {
        $search = NULL ? $search = new Zend_Session_Namespace('') : $search = new Zend_Session_Namespace('OAQPedimentos');
        if ($search->rptAnexo24Tip == 'anexo-24') {
            $headers = array(
                'Impo/Expo' => 'Importacion',
                'Operacion' => 'Operacion',
                'Patente' => 'Patente',
                'Trafico' => 'Trafico',
                'Pedimento' => 'Pedimento',
                'Aduana' => 'Aduana',
                'SeccionAd' => 'SeccionAd',
                'TransporteEntrada' => 'TransporteEntrada',
                'TransporteArribo' => 'TransporteArribo',
                'TransporteSalida' => 'TransporteSalida',
                'FechaEntrada' => 'FechaEntrada',
                'FechaPago' => 'FechaPago',
                'TipoCambio' => 'TipoCambio',
                'CvePed' => 'CvePed',
                'ValorDolares' => 'ValorDolares',
                'ValorAduana' => 'ValorAduana',
                'FacturaNo' => 'FacturaNo',
                'FechaFactura' => 'FechaFactura',
                'Incoterm' => 'Incoterm',
                'Fletes' => 'Fletes',
                'Seguros' => 'Seguros',
                'Embalajes' => 'Embalajes',
                'Otros_Incr' => 'Otros_Incr',
                'DTA' => 'DTA',
                'IVA' => 'IVA',
                'IGI' => 'IGI',
                'Prevalid' => 'Prevalid',
                'TotalEfectivo' => 'TotalEfectivo',
                'CveProveedor' => 'CveProveedor',
                'Nomproveedor' => 'Nomproveedor',
                'TaxId' => 'TaxId',
                'PaisVendComp' => 'PaisVendComp',
                'Divisa' => 'Divisa',
                'FactorME' => 'FactorME',
                'NumParte' => 'NumParte',
                'Descripcion' => 'Descripcion',
                'FraccionA' => 'FraccionA',
                'ValorME' => 'ValorME',
                'ValorAduanaMXP' => 'ValorAduanaMXP',
                'UnidadUMC' => 'UnidadUMC',
                'AbrevUMC' => 'AbrevUMC',
                'UMT' => 'UMT',
                'AbrevUMT' => 'AbrevUMT',
                'CantUnidTarifa' => 'CantUnidTarifa',
                'Origen' => 'Origen',
                'TasaAdv' => 'TasaAdv',
                'FormaPagoAdv' => 'FormaPagoAdv',
                'Vendedor' => 'Vendedor',
                'TLC' => 'TLC',
                'IVA_' => 'IVA_',
                'IEPS' => 'IEPS',
                'ISAN' => 'ISAN',
                'PatenteOrig' => 'PatenteOrig',
                'PedimentoOrig' => 'PedimentoOrig',
                'AduanaOrig' => 'AduanaOrig',
                'PesoBru' => 'PesoBru',
                'Bultos' => 'Bultos',
                'CantOMA' => 'CantOMA',
                'UnidOMA' => 'UnidOMA',
                'Cove' => 'Cove',
            );
        } elseif ($search->rptAnexo24Tip == 'anexo-24-enc') {
            $headers = array(
                "Importacion" => "Importacion",
                "Aduanas" => "Aduanas",
                "Fecha" => "Fecha",
                "TipoCambio" => "TipoCambio",
                "IVA" => "IVA",
                "Clave" => "Clave",
                "Fletes" => "Fletes",
                "Seguros" => "Seguros",
                "Embalajes" => "Embalajes",
                "Otros" => "Otros",
                "DTA" => "DTA",
                "ValorCom" => "ValorCom",
                "ValorAd" => "ValorAd",
                "Observaciones" => "Observaciones",
                "Consolidado" => "Consolidado",
                "Virtual" => "Virtual",
                "Prev" => "Prev",
            );
        } elseif ($search->rptAnexo24Tip == 'anexo-24-fact') {
            $headers = array(
                "Importacion" => "Importacion",
                "Factura" => "Factura",
                "CodigoP" => "CodigoP",
                "FechaF" => "FechaF",
                "FMoneda" => "FMoneda",
                "NumParte" => "NumParte",
                "Descripcion" => "Descripcion",
                "TipoBien" => "TipoBien",
                "FraccionImpo" => "FraccionImpo",
                "Tasa" => "Tasa",
                "TipoTasa" => "TipoTasa",
                "Unidad" => "Unidad",
                "Precio" => "Precio",
                "Cantidad" => "Cantidad",
                "Conversion" => "Conversion",
                "Origen" => "Origen",
                "Vendedor" => "Vendedor",
                "Fpago" => "Fpago",
                "Incoterm" => "Incoterm",
                "Cove" => "Cove",
                "AcuerdoCom" => "AcuerdoCom",
                "TLC" => "TLC",
            );
        }

        $misc = new OAQ_Misc();
        if (($result = $misc->checkCache('rptanexo24' . $this->_config->username))) {
            $reports = new OAQ_ExcelExport();
            $reports->createSimpleReport($headers, $result, 'rptanexo24', 'Reporte de Anexo 24', null, null, 'Reporte de Anexo 24', null, null);
        } else {
            
        }
    }

    protected function _cleanXml($xml) {
        return preg_replace('#<soapenv:Header(.*?)>(.*?)</soapenv:Header>#is', '', $xml);
    }

    public function verXmlAction() {
        $vucemSol = new Clientes_Model_CovesMapper();
        $vucemFact = new Clientes_Model_FacturasMapper();
        $id = $this->_request->getParam('id');

        if (($xml = $vucemSol->obtenerSolicitudPorId($id, $this->_session->rfc))) {
            $fact = $vucemFact->verificarFactura($xml["solicitud"], $this->_session->rfc);
            if ($fact) {
                header("Content-Type:text/xml");
                echo $this->_cleanXml($xml["xml"]);
            }
        }
    }

    public function descargaXmlAction() {
        $vucemSol = new Clientes_Model_CovesMapper();
        $id = $this->_request->getParam('id');
        $xml = $vucemSol->obtenerSolicitudPorId($id, $this->_session->rfc);
        header('Content-disposition: attachment; filename="' . $xml["cove"] . '.xml"');
        header('Content-type: "text/xml"; charset="utf8"');
        echo $this->_cleanXml($xml["xml"]);
    }

    public function convertCoveToPdfAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        define("DOMPDF_ENABLE_REMOTE", true);
        require_once 'dompdf/dompdf_config.inc.php';

        $id = $this->_request->getParam('id');

        $strCookie = 'PHPSESSID=' . $_COOKIE['PHPSESSID'] . '; path=/';
        session_write_close();

        $uri = "{$this->_config->app->url}/clientes/data/render-cove-to-html?id={$id}";
        $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_COOKIE, $strCookie);
        $response = curl_exec($ch);
        curl_close($ch);

        $vucemSol = new Vucem_Model_VucemSolicitudesMapper();
        $cove = $vucemSol->obtenerNombreCove($id);
        if ($cove["cove"] != null && $cove["cove"] != '') {
            $filename = $cove["cove"];
        } else {
            $filename = 'Operacion_' . $cove["solicitud"];
        }
        $dompdf = new DOMPDF();
        $dompdf->set_paper("letter", "portrait");
        $dompdf->load_html($response);
        $dompdf->set_base_path($_SERVER['DOCUMENT_ROOT']);
        $dompdf->render();
        $dompdf->stream($filename . ".pdf");
    }

    public function renderCoveToHtmlAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(false);

        $vucemSol = new Clientes_Model_CovesMapper();
        $vucem = new OAQ_Vucem();
        $id = $this->_request->getParam('id');
        $xml = $vucemSol->obtenerSolicitudPorId($id, $this->_session->rfc);

        $xmlArray = $vucem->vucemXmlToArray($xml["xml"]);
        unset($xmlArray["Header"]);
        if ($xml["cove"] != '' && $xml["cove"] != null) {
            $this->view->cove = $xml["cove"];
        }
        $this->view->id = $id;
        $this->view->data = $xmlArray["Body"]["solicitarRecibirCoveServicio"]["comprobantes"];
        $this->view->url = $this->_config->app->url;
    }

    public function referenciaCargarArchivosAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "referencia" => "StringToUpper",
                "patente" => "Digits",
                "aduana" => "Digits",
            );
            $v = array(
                "referencia" => "NotEmpty",
                "patente" => "NotEmpty",
                "aduana" => "NotEmpty",
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("referencia") && $input->isValid("patente") && $input->isValid("aduana")) {
                $mapper = new Archivo_Model_RepositorioMapper();
                $arr = $mapper->archivosDeReferencia($input->patente, $input->aduana, $input->referencia);
                
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/data/");
                echo $view->render("referencia-cargar-archivos.phtml");
                
                if (isset($arr) && !empty($arr)) {
                    $html = "";
                    $html .= '<table class="traffic-table traffic-table-left">'
                            . '<thead>'
                            . '<tr>'
                            . '<th>Nombre de archivo</th>'
                            . '<th>Tipo</th>'
                            . '<th>EDoc</th>'
                            . '<th>Fecha</th>'
                            . '<th>Tamaño</th>'
                            . '<th>&nbsp;</th>'
                            . '</tr>'
                            . '</thead>'
                            . '<tbody>';
                    foreach ($arr as $item) {
                        if (preg_match('/Acuse E-Document/i', $item["nombre"])) {
                            $edoc = substr($item["nom_archivo"], 0, -4);
                        } else {
                            $edoc = '&nbsp;';
                        }
                        if (file_exists($item["ubicacion"])) {
                            $filesize = number_format(filesize($item["ubicacion"]) / 1024 / 1024, 2);
                        }
                        if ($item["tipo_archivo"] >= 168 && $item["tipo_archivo"] <= 445) {
                            $vucem = '<a style="cursor:pointer;" onclick="sendVucem(' . $item["id"] . ')"><img src="/images/icons/vucem_logo.png" /></a>';
                        } else {
                            $vucem = '&nbsp;';
                        }
                        $html .= '<tr>'
                                . "<td><a href=\"/clientes/data/download-file?id={$item["id"]}\">{$item["nom_archivo"]}</a></td>"
                                . "<td><div id=\"edit_{$item["id"]}\">" . wordwrap($item["nombre"], 45, '<br />') . "</div></td>"
                                . "<td>{$edoc}</td>"
                                . "<td>" . date('d/m/Y H:i a', strtotime($item["creado"])) . "</td>"
                                . "<td>" . (isset($filesize) ? $filesize : "") . " Mb</td>"
                                . '<td>'
                                . '<a class="openfile" href="/archivo/index/load-file-repo?id=' . $item["id"] . '"><div class="traffic-icon traffic-icon-folder"></div></a>&nbsp;'
                                . '</td>'
                                . '</tr>';
                    }
                    $html . '</tbody>'
                            . '</table>';
                    echo $html;
                } else {
                    throw new Exception("No data found!");
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    /*public function downloadFileAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        $id = $this->_request->getParam('id', null);
        if ($id) {
            $archive = new Archivo_Model_RepositorioMapper();
            $fileinfo = $archive->getFileById($id);

            if (is_readable($fileinfo["ubicacion"]) && file_exists($fileinfo["ubicacion"])) {
                $sha = sha1_file($fileinfo["ubicacion"]);
                $basename = basename($fileinfo["ubicacion"]);

                if (copy($fileinfo["ubicacion"], '/tmp' . DIRECTORY_SEPARATOR . $sha)) {
                    if (file_exists('/tmp' . DIRECTORY_SEPARATOR . $sha)) {
                        header('Content-Type: application/octet-stream');
                        header("Content-Transfer-Encoding: Binary");
                        header("Content-Length: " . filesize('/tmp' . DIRECTORY_SEPARATOR . $sha));
                        header("Content-disposition: attachment; filename=\"" . $basename . "\"");
                        readfile('/tmp' . DIRECTORY_SEPARATOR . $sha);
                        unlink('/tmp' . DIRECTORY_SEPARATOR . $sha);
                    }
                }
                unset($fileinfo);
            }
        }
    }*/

    protected function month($month) {
        switch ($month) {
            case 1:
                $mes = "Enero";
                break;
            case 2:
                $mes = "Febrero";
                break;
            case 3:
                $mes = "Marzo";
                break;
            case 4:
                $mes = "Abril";
                break;
            case 5:
                $mes = "Mayo";
                break;
            case 6:
                $mes = "Junio";
                break;
            case 7:
                $mes = "Julio";
                break;
            case 8:
                $mes = "Agosto";
                break;
            case 9:
                $mes = "Septiembre";
                break;
            case 10:
                $mes = "Octubre";
                break;
            case 11:
                $mes = "Noviembre";
                break;
            case 12:
                $mes = "Diciembre";
                break;
            default:
                $mes = "n/d";
                break;
        }
        return $mes;
    }

    public function excelReporteAnexoAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        ini_set("soap.wsdl_cache_enabled", 0);

        $rfc = $this->_request->getParam('rfc', null);
        $year = $this->_request->getParam('year', null);
        $tipo = $this->_request->getParam('tipo', null);
        $month = $this->_request->getParam('month', null);
        $aduana = $this->_request->getParam('aduana', null);
        $patente = $this->_request->getParam('patente', null);
        $pedimento = $this->_request->getParam('pedimento', null);
        $version = $this->_request->getParam('version', null);
        $ie = $this->_request->getParam('ie', null);
        $fechaIni = $this->_request->getParam('fechaIni', null);
        $fechaFin = $this->_request->getParam('fechaFin', null);

        if (!isset($version)) {
            
        } elseif (isset($tipo)) {
            $ped = new Automatizacion_Model_WsPedimentosMapper();
            if ($tipo == 'parcial' || $tipo == 'extendido') {
                if (isset($month) && $month != '') {
                    if ($year >= 2015 && $patente == 3589) {
                        if ($patente == 3589 && $aduana == 640) {
                            $db = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITAW3010640", 1433, "Pdo_Mssql");
                        } elseif ($patente == 3589 && $aduana == 240) {
                            $db = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITAW3589240", 1433, "Pdo_Mssql");
                        } elseif ($patente == 3589 && $aduana == 646) {
                            $db = new OAQ_Sitawin(true, '192.168.0.253', 'sa', 'sqlcointer', 'SITAW3589640', 1433, 'Pdo_Mssql');
                        }
                    }
                    if (isset($db)) {
                        try {
                            $pedimentos = $db->anexo24Extendido($rfc, $year, $month);
                        } catch (Exception $ex) {
                            Zend_Debug::dump($ex->getMessage());
                        }
                    } else {
                        $pedimentos = $ped->obtenerAnexo($patente, $aduana, $year, $month, $rfc, $tipo, $pedimento, $ie);
                    }
                } elseif (isset($fechaIni) && isset($fechaFin)) {
                    try {
                        $pedimentos = $ped->obtenerAnexoFechas($patente, $aduana, $year, $fechaIni, $fechaFin, $rfc, $tipo, $pedimento, $ie);
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    }
                }
            }
            if ($tipo == 'header') {
                $pedimentos = $ped->obtenerEncabezados($patente, $aduana, $year, $month, $rfc, $tipo, $pedimento, $ie);
            }
            if (isset($pedimentos) && !empty($pedimentos)) {
                $reportName = array(
                    'font' => array(
                        'name' => "Arial",
                        'bold' => true,
                        'size' => 15,
                    ),
                );
                $titles = array(
                    'font' => array(
                        'bold' => true,
                        'name' => "Arial",
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
                    'font' => array(
                        'name' => "Arial",
                        'size' => 10,
                    ),
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

                $headers = $this->_excelHeaders($tipo);
                $misc = new OAQ_Misc();

                $objPHPExcel->getProperties()->setCreator("Jvaldezch at gmail");
                $objPHPExcel->getProperties()->setLastModifiedBy("Jvaldezch at gmail");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Reporte");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Reporte");
                $objPHPExcel->getProperties()->setDescription("Reporte de anexo 24 de Orgnización Aduanal de Queretaro.");

                $objPHPExcel->setActiveSheetIndex(0);
                $column = 0;
                $row = 4;

                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, "REPORTE DE ANEXO 24");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->applyFromArray($reportName);

                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 2, 'RFC CLIENTE:');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->applyFromArray($titles);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 2, $rfc);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, 2)->applyFromArray($info);

                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 3, 'AÑO:');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->applyFromArray($titles);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 3, $year);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, 3)->applyFromArray($info);

                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 3, 'MES:');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, 3)->applyFromArray($titles);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 3, $misc->mes($month));
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, 3)->applyFromArray($info);

                foreach ($headers["headers"] as $k => $v):
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($column, $row, $k);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $row)->applyFromArray($titles);
                    $column++;
                endforeach;
                $num = count($pedimentos) + $row + 1;
                for ($i = $row + 1; $i < $num; $i++) {
                    $column = 0;
                    foreach ($headers["headers"] as $k => $v) {
                        $pos = $i - $row - 1;
                        if (isset($pedimentos[$pos][$headers["headers"][$k]])) {
                            $value = $pedimentos[$pos][$headers["headers"][$k]];
                        } else {
                            if ($headers["headers"][$k] == 'PrecioUnitario') {
                                $value = $pedimentos[$pos]["Total"] / $pedimentos[$pos]["CantUMC"];
                            } else {
                                $value = '';
                            }
                        }
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($column, $i, utf8_decode($value));
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $i)->applyFromArray($info);
                        if (in_array($v, $headers["monedas"])) {
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $i)->getNumberFormat()->setFormatCode('$ #,##0.00');
                        } else if (in_array($v, $headers["texto"])) {
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $i)->getNumberFormat()->setFormatCode('0');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        }
                        $column++;
                    }
                }
                foreach (range('A', $objPHPExcel->getActiveSheet()->getHighestDataColumn()) as $col) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $rfc . '_' . date('Y-m-d') . '_' . date('His') . '.xlsx"');
                header('Cache-Control: max-age=0');
                $objWriter->save('php://output');
            }
        }
    }

    protected function _excelHeaders($tipo) {
        if ($tipo == 'extendido') {
            return array('headers' => array(
                    'Referencia' => 'Referencia',
                    'TipoOperacion' => 'Operacion',
                    'Patente' => 'Patente',
                    'Aduana' => 'Aduana',
                    'Pedimento' => 'Pedimento',
                    'Trafico' => 'Trafico',
                    'TransporteEntrada' => 'TransporteEntrada',
                    'TransporteArribo' => 'TransporteArribo',
                    'TransporteSalida' => 'TransporteSalida',
                    'FechaEntrada' => 'FechaEntrada',
                    'FechaPago' => 'FechaPago',
                    'FirmaValidacion' => 'FirmaValidacion',
                    'FirmaBanco' => 'FirmaBanco',
                    'TipoCambio' => 'TipoCambio',
                    'CvePed' => 'CvePed',
                    'Regimen' => 'Regimen',
                    'ValorDolares' => 'ValorDolares',
                    'ValorAduana' => 'ValorAduana',
                    'Fletes' => 'Fletes',
                    'Seguros' => 'Seguros',
                    'Embalajes' => 'Embalajes',
                    'OtrosIncrementales' => 'OtrosIncrementales',
                    'DTA' => 'DTA',
                    'IVA' => 'IVA',
                    'IGI' => 'IGI',
                    'PREV' => 'PREV',
                    'CNT' => 'CNT',
                    'TotalEfectivo' => 'TotalEfectivo',
                    'PesoBruto' => 'PesoBruto',
                    'Bultos' => 'Bultos',
                    'NumFactura' => 'NumFactura',
                    'Cove' => 'Cove',
                    'FechaFactura' => 'FechaFactura',
                    'Incoterm' => 'Incoterm',
                    'ValorFacturaUsd' => 'ValorFacturaUsd',
                    'ValorFacturaMonExt' => 'ValorFacturaMonExt',
                    'NomProveedor' => 'NomProveedor',
                    'TaxId' => 'TaxId',
                    'PaisFactura' => 'PaisFactura',
                    'Divisa' => 'Divisa',
                    'FactorMonExt' => 'FactorMonExt',
                    'NumParte' => 'NumParte',
                    'Descripcion' => 'Descripcion',
                    'Fraccion' => 'Fraccion',
                    'OrdenFraccion' => 'OrdenFraccion',
                    'PrecioUnitario' => 'PrecioUnitario',
                    'ValorMonExt' => 'ValorMonExt',
                    'ValorAduanaMXN' => 'ValorAduanaMXN',
                    'UMC' => 'UMC',
                    'CantUMC' => 'CantUMC',
                    'UMT' => 'UMT',
                    'CantUMT' => 'CantUMT',
                    'PaisOrigen' => 'PaisOrigen',
                    'PaisVendedor' => 'PaisVendedor',
                    'TLC' => 'TLC',
                    'TLCAN' => 'TLCAN',
                    'TLCUE' => 'TLCUE',
                    'PROSEC' => 'PROSEC',
                    'TasaAdvalorem' => 'TasaAdvalorem',
                    'Guías' => 'Guias',
                    'PatenteOrig' => 'PatenteOrig',
                    'PedimentoOrig' => 'PedimentoOrig',
                    'AduanaOrig' => 'AduanaOrig',
                ),
                'monedas' => array('TotalEfectivo', 'DTA', 'IGI', 'CNT', 'PREV', 'Fletes', 'Seguros', 'Embalajes', 'OtrosIncrementales', 'ValorDolares', 'ValorAduana', 'PrecioUnitario', 'ValorMonExt', 'ValorAduanaMXN'),
                'texto' => array('NumParte', 'Descripcion', 'Fraccion'));
        }
        if ($tipo == 'parcial') {
            return array('headers' => array(
                    'Referencia' => 'Referencia',
                    'CvePedimento' => 'CvePed',
                    'ReferenciaAA' => 'ReferenciaAA',
                    'ClaveProyecto' => 'ClaveProyecto',
                    'NumFactura' => 'NumFactura',
                    'NumParte' => 'NumParte',
                    'PaisOrigen' => 'PaisOrigen',
                    'Secuencia' => 'Secuencia',
                    'UMC' => 'UMC',
                    'CantUMC' => 'CantUMC',
                    'PrecioUnitario' => 'PrecioUnitario',
                    'Total' => 'Total',
                    'PatenteOrig' => 'PatenteOrig',
                    'PedimentoOrig' => 'PedimentoOrig',
                    'AduanaOrig' => 'AduanaOrig',
                ),
                'monedas' => array(),
                'texto' => array());
        }
        if ($tipo == 'header') {
            return array('headers' => array(
                    'Referencia' => 'Referencia',
                    'TipoOperacion' => 'Operacion',
                    'Patente' => 'Patente',
                    'Aduana' => 'Aduana',
                    'Pedimento' => 'Pedimento',
                    'Trafico' => 'Trafico',
                    'TransporteEntrada' => 'TransporteEntrada',
                    'TransporteArribo' => 'TransporteArribo',
                    'TransporteSalida' => 'TransporteSalida',
                    'FechaEntrada' => 'FechaEntrada',
                    'FechaPago' => 'FechaPago',
                    'FirmaValidacion' => 'FirmaValidacion',
                    'FirmaBanco' => 'FirmaBanco',
                    'TipoCambio' => 'TipoCambio',
                    'CvePed' => 'CvePed',
                    'Regimen' => 'Regimen',
                    'ValorDolares' => 'ValorDolares',
                    'ValorAduana' => 'ValorAduana',
                    'Fletes' => 'Fletes',
                    'Seguros' => 'Seguros',
                    'Embalajes' => 'Embalajes',
                    'OtrosIncrementales' => 'OtrosIncrementales',
                    'DTA' => 'DTA',
                    'IVA' => 'IVA',
                    'IGI' => 'IGI',
                    'PREV' => 'PREV',
                    'CNT' => 'CNT',
                    'TotalEfectivo' => 'TotalEfectivo',
                    'PesoBruto' => 'PesoBruto',
                    'Bultos' => 'Bultos',
                ),
                'monedas' => array(),
                'texto' => array());
        }
    }

    /**
     * 
     * @return boolean
     */
    public function reportesAction() {
        try {
            date_default_timezone_set('America/Mexico_City');
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(false);
            ini_set("soap.wsdl_cache_enabled", 0);
            $filters = array(
                "*" => array("StringTrim", "StripTags"),
                "patente" => "Digits",
                "aduana" => "Digits",
                "year" => "Digits",
                "month" => "Digits",
            );
            $validators = array(
                "patente" => array("Digits", new Zend_Validate_Int()),
                "aduana" => array("Digits", new Zend_Validate_Int()),
                "year" => array("Digits", new Zend_Validate_Int(),
                    new Zend_Validate_Between(
                            array(
                        "min" => 2012,
                        "max" => 2019,
                        "inclusive" => true
                            )
                    )
                ),
                "month" => array("Digits", new Zend_Validate_Int(),
                    new Zend_Validate_Between(
                            array(
                        "min" => 1,
                        "max" => 12,
                        "inclusive" => true
                            )
                    )
                ),
                "rfc" => new Zend_Validate_StringLength(array("max" => 25)),
                "tipo" => new Zend_Validate_StringLength(array("max" => 25)),
                "fechaIni" => new Zend_Validate_Date(),
                "fechaFin" => new Zend_Validate_Date(),
            );
            $reportes = new OAQ_Reportes();
            $misc = new OAQ_Misc();
            $input = new Zend_Filter_Input($filters, $validators, $this->_request->getParams());
            if ($input->isValid("tipo")) {
                $viewsFolder = realpath(dirname(__FILE__)) . "/../views/scripts/layouts/";
                $view = new Zend_View();
                $view->setScriptPath($viewsFolder);
                switch ($input->tipo) {
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
                $this->view->type = $input->tipo;
                if (in_array($input->tipo, array("encabezado", "prasad", "anexo", "proveedores", "cnh"))) {
                    $db = new Automatizacion_Model_WsPedimentosMapper();
                    if ($input->tipo == "encabezado") {
                        $rows = $db->encabezados((int) $input->patente, (int) $input->aduana, $input->rfc, $input->fechaIni, $input->fechaFin, (int) $input->year, (int) $input->month);
                    }
                    if ($input->tipo == "prasad") {
                        $rows = $db->prasad((int) $input->patente, (int) $input->aduana, $input->rfc, $input->fechaIni, $input->fechaFin, (int) $input->year, (int) $input->month);
                    }
                    if ($input->tipo == "anexo") {
                        $rows = $db->anexo((int) $input->patente, (int) $input->aduana, $input->rfc, $input->fechaIni, $input->fechaFin, (int) $input->year, (int) $input->month);
                    }
                    if ($input->tipo == "cnh") {
                        $rows = $db->anexoCnh((int) $input->patente, (int) $input->aduana, $input->rfc, $input->fechaIni, $input->fechaFin, (int) $input->year, (int) $input->month);
                    }
                    if ($input->tipo == "proveedores") {
                        $rows = $db->proveedores((int) $input->patente, (int) $input->aduana, $input->rfc);
                    }
                    if (!isset($rows) || empty($rows)) {
                        $db = $misc->sitawinTrafico($input->patente, $input->aduana);
                        if (isset($db)) {
                            if ($input->tipo == "encabezado") {
                                $rows = $db->encabezado($input->rfc, $input->fechaIni, $input->fechaFin);
                            } else if ($input->tipo == "anexo") {
                                $rows = $db->anexo($input->rfc, $input->fechaIni, $input->fechaFin);
                            }
                        }
                    }
                    $view->titulos = $reportes->anexoHeaders($input->tipo);
                    if (isset($rows) && count($rows)) {
                        $view->data = $rows;
                        $cache = $this->_cache();
                        $cache->save($input->tipo, "rpt_type_" . $this->_session->username);
                        $cache->save($rows, "rpt_data_" . $this->_session->username);
                        $cache->save($this->_customHeaders($input->tipo), "rpt_titles_" . $this->_session->username);
                    }
                    $this->view->content = $view->render($layout);
                    return;
                } elseif ($input->tipo == "tecnico") {
                    $db = $misc->sitawinCargoquin($input->patente, $input->aduana);
                    if (!isset($db)) {
                        Zend_Debug::dump("No system found!");
                        return;
                    }
                    $rows = $db->layoutTecnico($input->rfc, null, null, $input->fechaIni, $input->fechaFin);
                    $view->titulos = $reportes->anexoHeaders($input->tipo);
                    if (isset($rows) && count($rows)) {
                        $view->data = $rows;
                    }
                    $this->view->patente = $input->patente;
                    $this->view->aduana = $input->aduana;
                    $this->view->rfc = $input->rfc;
                    $this->view->fechaIni = $input->fechaIni;
                    $this->view->fechaFin = $input->fechaFin;
                    $this->view->content = $view->render($layout);
                    return;
                } elseif ($input->tipo == "cargoquin") {
                    $db = $misc->sitawinCargoquin($input->patente, $input->aduana);
                    if (!isset($db)) {
                        return;
                    }
                    $rows = $db->grupoCargoQuin($input->rfc, null, null, $input->fechaIni, $input->fechaFin);
                    $fracc = $db->grupoCargoQuinFracciones($input->rfc, null, null, $input->fechaIni, $input->fechaFin);
                    $parts = $db->grupoCargoQuinPartes($input->rfc, null, null, $input->fechaIni, $input->fechaFin);
                    $result = array();
                    foreach ($rows as $row) {
                        $tbl = new Vucem_Model_VucemUmcMapper();
                        $repo = new Archivo_Model_RepositorioMapper();
                        $factura = $repo->facturasTerminalPedimento($row["pedimento"]);
                        if (isset($factura) && !empty($factura)) {
                            if (file_exists($factura["ubicacion"])) {
                                $doc = new DOMDocument();
                                $doc->loadXML(str_replace(array("cfdi:", "xmlns:", "tfd:", "xsi:"), "", file_get_contents($factura["ubicacion"])));
                                $domXpath = new DOMXPath($doc);
                                $conceptos = $domXpath->query("//Conceptos/*");
                                $array = array();
                                foreach ($conceptos as $con) {
                                    $array[$con->getAttribute("descripcion")] = array(
                                        "folio" => $factura["folio"],
                                        "cantidad" => $con->getAttribute("cantidad"),
                                        "importe" => $con->getAttribute("importe"),
                                        "valorUnitario" => $con->getAttribute("valorUnitario"),
                                    );
                                }
                            }
                        }
                        if (!empty($array)) {
                            $row["conceptos"] = $array;
                        }
                        if (isset($row["dta"])) {
                            $row["impuestos"]["DTA"] = array(
                                "importe" => $row["dta"],
                            );
                        }
                        if (isset($row["prev"])) {
                            $row["impuestos"]["PREV"] = array(
                                "importe" => $row["prev"],
                            );
                        }
                        if (isset($row["cnt"])) {
                            $row["impuestos"]["CNT"] = array(
                                "importe" => $row["cnt"],
                            );
                        }
                        $result[] = $row;
                    }
                    $helperFolder = realpath(dirname(__FILE__)) . "/../views/helpers/";
                    $view->setHelperPath($helperFolder);
                    $view->data = $result;
                    $pre = "<!doctype html>\n<html lang=\"en\">\n<head>\n<title>Facturas</title>\n<meta charset=\"utf-8\">\n<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n<meta name=\"description\" content=\"OAQ Web Portal\">\n<meta name=\"author\" content=\"Jaime E. Valdez\">\n<meta http-equiv=\"Cache-Control\" content=\"no-cache, no-store, must-revalidate\" />\n<meta http-equiv=\"Pragma\" content=\"no-cache\" />\n<meta http-equiv=\"Expires\" content=\"0\" />\n</head>\n<body>";
                    file_put_contents("/tmp/cargoquin-facturas.html", $pre . $view->render("cargo-quin-en.phtml") . "</body></html>");
                    $this->view->content = $view->render("cargo-quin-en.phtml");
                    $view->data = $fracc;
                    $this->view->fracciones = $view->render("cargo-quin-fracciones-en.phtml");
                    file_put_contents("/tmp/cargoquin-fracciones.html", $pre . $view->render("cargo-quin-fracciones-en.phtml") . "</body></html>");
                    $view->data = $parts;
                    $this->view->partes = $view->render("cargo-quin-partes-en.phtml");
                    file_put_contents("/tmp/cargoquin-partes.html", $pre . $view->render("cargo-quin-partes-en.phtml") . "</body></html>");
                    return false;
                }
                $this->view->content = $view->render($layout);
            } else {
                $this->view->error = "Los parámetros de consulta no son correctos.";
            }
        } catch (Exception $ex) {
            Zend_Debug::Dump($ex->getMessage());
        }
    }

    public function reporteCovesExcelAction() {
        try {
            date_default_timezone_set("America/Mexico_City");
            ini_set("soap.wsdl_cache_enabled", 0);
            $f = array(
                "*" => array("StringTrim", "StripTags"),
            );
            $v = array(
                "fechaIni" => new Zend_Validate_Date(),
                "fechaFin" => new Zend_Validate_Date(),
            );
            $reportes = new OAQ_ExcelReportes();
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("fechaIni") && $input->isValid("fechaFin")) {
                $mapper = new Clientes_Model_CovesMapper();
                $rows = $mapper->getReporteCoves($this->_session->rfc, $input->fechaIni, $input->fechaFin);
                $reportes->setFilename($this->_session->rfc . "_" . $input->fechaIni . "_" . $input->fechaFin);
                if (isset($rows) && !empty($rows)) {
                    $reportes->setData($rows);
                    $reportes->reporteCoves();
                    $reportes->download();
                } else {
                    throw new Exception("No records!");
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Zend_Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function excelAction() {
        try {
            $cache = $this->_cache();
            if (($tipo = $cache->load("rpt_type_" . $this->_session->username)) && ($data = $cache->load("rpt_data_" . $this->_session->username)) && ($titles = $cache->load("rpt_titles_" . $this->_session->username))) {
                $excel = new OAQ_Reportes();
                $excel->set_titles($excel->anexoHeaders($tipo));
                $excel->set_data($data);
                $excel->set_type($tipo);
                $excel->simpleReport();
                $excel->download();
            } else {
                echo "Nothing in cache.";
                return false;
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function excelLayoutTecnicoAction() {
        try {
            date_default_timezone_set('America/Mexico_City');
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            ini_set("soap.wsdl_cache_enabled", 0);
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "patente" => "Digits",
                "aduana" => "Digits",
            );
            $v = array(
                "patente" => array("Digits", new Zend_Validate_Int()),
                "aduana" => array("Digits", new Zend_Validate_Int()),
                "rfc" => new Zend_Validate_StringLength(array("max" => 25)),
                "fechaIni" => new Zend_Validate_Date(),
                "fechaFin" => new Zend_Validate_Date(),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("patente") && $input->isValid("aduana") && $input->isValid("rfc")) {
                $misc = new OAQ_Misc();
                $reportes = new OAQ_Reportes();
                $db = $misc->sitawinCargoquin((int) $input->patente, (int) $input->aduana);
                if (!isset($db)) {
                    Zend_Debug::dump("No system found!");
                    return;
                }
                $titles = $reportes->anexoHeaders("tecnico");
                foreach ($titles["titulos"] as $k => $v) {
                    $titulos[] = $k;
                }
                $rows = $db->layoutTecnico($input->rfc, null, null, $input->fechaIni, $input->fechaFin);
                if (isset($rows) && !empty($rows)) {
                    foreach ($rows as $item) {
                        foreach ($titles["titulos"] as $k => $v) {
                            if (isset($item[$v]) && !isset($titles["numbers"][$v]) && !isset($titles["dates"][$v])) {
                                $tmp[] = $item[$v];
                            } elseif (isset($item[$v]) && isset($titles["numbers"][$v]) && !isset($titles["dates"][$v])) {
                                $tmp[] = number_format($item[$v], $titles["numbers"][$v]);
                            } elseif (isset($item[$v]) && !isset($titles["numbers"][$v]) && isset($titles["dates"][$v])) {
                                $tmp[] = date($titles["dates"][$v], strtotime($item[$v]));
                            } else {
                                $tmp[] = "";
                            }
                        }
                        $datos[] = $tmp;
                        unset($tmp);
                    }
                }
                unset($rows);
                unset($titles);
                $report = new OAQ_ExcelReportes();
                $report->setTitles($titulos);
                $report->setData($datos);
                $report->setFilename($input->rfc . "_" . $input->fechaIni . "_" . $input->fechaFin);
                $report->layoutTecnico();
            }
        } catch (Exception $ex) {
            Zend_Debug::Dump($ex->getMessage());
        }
    }

    public function excelCargoquinAction() {
        try {
            $excel = new OAQ_Reportes();
            $excel->fromHtml();
        } catch (Exception $ex) {
            Zend_Debug::Dump($ex->getMessage());
        }
    }

    protected function _cache() {
        $frontendOptions = array(
            "lifetime" => 1200,
            "automatic_serialization" => true
        );
        if (APPLICATION_ENV == "production") {
            if (!file_exists("/tmp/cache")) {
                mkdir("/tmp/cache", 0777, true);
            }
            $backendOptions = array(
                "cache_dir" => "/tmp/cache"
            );
        } else if (APPLICATION_ENV == "staging") {
            if (!file_exists("/tmp/cache")) {
                mkdir("/tmp/cache", 0777, true);
            }
            $backendOptions = array(
                "cache_dir" => "/tmp/cache"
            );
        } else {
            if (!file_exists("D:/tmp/cache")) {
                mkdir("D:/tmp/cache", 0777, true);
            }
            $backendOptions = array(
                "cache_dir" => "D:/tmp/cache"
            );            
        }
        return Zend_Cache::factory("Core", "File", $frontendOptions, $backendOptions);
    }

    protected function _customHeaders($tipo) {
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
                "TotalEfectivo" => "totalEfectivo",
                "PesoBruto" => "pesoBruto",
                "Bultos" => "bultos",
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
                "PrecioUnitario" => "precioUnitario",
                "ValorMonExt" => "total",
                "UMC" => "umc",
                "CantUMC" => "cantUmc",
                "UMT" => "umt",
                "CantUMT" => "cantUmt",
                "PaisOrigen" => "paisOrigen",
                "TLC" => "tlc",
                "TLCAN" => "tlcan",
                "TLCUE" => "tlcue",
                "PROSEC" => "prosec",
                "TasaAdvalorem" => "tasaAdvalorem",
                "PaisVendedor" => "paisVendedor",
                "PatenteOrig" => "patenteOrig",
                "AduanaOrig" => "aduanaOrig",
                "PedimentoOrig" => "fraccion",
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
                "IVA" => "iva",
                "IGI" => "igi",
                "PREV" => "prev",
                "CNT" => "cnt",
                "TotalEfectivo" => "totalEfectivo",
                "PesoBruto" => "pesoBruto",
                "Bultos" => "bultos",
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
                "PrecioUnitario" => "precioUnitario",
                "ValorMonExt" => "total",
                "UMC" => "umc",
                "CantUMC" => "cantUmc",
                "UMT" => "umt",
                "CantUMT" => "cantUmt",
                "PaisOrigen" => "paisOrigen",
                "TLC" => "tlc",
                "TLCAN" => "tlcan",
                "TLCUE" => "tlcue",
                "PROSEC" => "prosec",
                "TasaAdvalorem" => "tasaAdvalorem",
                "PaisVendedor" => "paisVendedor",
                "PatenteOrig" => "patenteOrig",
                "AduanaOrig" => "aduanaOrig",
                "PedimentoOrig" => "fraccion",
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
                "TotalEfectivo" => "totalEfectivo",
                "PesoBruto" => "pesoBruto",
                "Bultos" => "bultos",
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

    /**
     * https://192.168.0.246/clientes/data/ver-reporte-anexo?patente=9999&aduana=999&year=2015&rfc=CTM990607US8&tipo=extendido&version=2&ie=&fechaIni=2015-04-01&fechaFin=2015-04-17
     * 
     */
    public function verReporteAnexoAction() {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            ini_set("soap.wsdl_cache_enabled", 0);
            $f = array(
                "*" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags()),
                "patente" => new Zend_Filter_Digits(),
                "aduana" => new Zend_Filter_Digits(),
                "pedimento" => new Zend_Filter_Digits(),
                "year" => new Zend_Filter_Digits(),
                "month" => new Zend_Filter_Digits(),
                "tipo" => new Zend_Filter_StringToLower(),
                "rfc" => new Zend_Filter_StringToUpper(),
                "download" => new Zend_Filter_StringToLower(),
            );
            $v = array(
                "patente" => array("NotEmpty", new Zend_Validate_Int()),
                "aduana" => array("NotEmpty", new Zend_Validate_Int()),
                "pedimento" => array("NotEmpty", new Zend_Validate_Int()),
                "year" => array("NotEmpty", new Zend_Validate_Int()),
                "month" => array("NotEmpty", new Zend_Validate_Int(), new Zend_Validate_InArray([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12])),
                "tipo" => array("NotEmpty", new Zend_Validate_InArray(["parcial", "extendido", "header", "cnh"])),
                "rfc" => array("NotEmpty", new Zend_Validate_Regex("/^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/")),
                "fechaIni" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
                "fechaFin" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
                "download" => "NotEmpty",
            );
            $debug = filter_var($this->getRequest()->getParam("debug", null), FILTER_VALIDATE_BOOLEAN);
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("rfc") && $input->isValid("tipo")) {
                $ped = new Automatizacion_Model_WsPedimentosMapper();
                $misc = new OAQ_Misc();
                if ($input->tipo == "parcial" || $input->tipo == "extendido") {
                    if ($input->isValid("month") && $input->isValid("year")) {
                        if ($input->year >= 2015 && $input->patente == 3589) {
                            $db = $misc->sitawin($input->patente, $input->aduana);
                        }
                        if (isset($db) && !empty($db)) {
                            if ($input->isValid("download") && $input->download == "true") {
                                $array = $db->anexo24ExtendidoHtml($input->rfc, $input->year, $input->month);
                            } else {
                                $array = $db->anexo24Extendido($input->rfc, $input->year, $input->month);
                            }
                        } else {
                            if ($input->isValid("download") && $input->download == "true") {
                                $array = $ped->obtenerAnexoHtml($input->patente, $input->aduana, $input->year, $input->month, $input->rfc, $input->pedimento, true);
                            } else {
                                $array = $ped->obtenerAnexoHtml($input->patente, $input->aduana, $input->year, $input->month, $input->rfc, $input->pedimento);
                            }
                        }
                    } elseif ($input->isValid("fechaIni") && $input->isValid("fechaIni")) {
                        $array = $ped->obtenerAnexoFechas($input->patente, $input->aduana, $input->year, $input->fechaIni, $input->fechaFin, $input->rfc, $input->tipo, $input->pedimento, $input->ie);
                    }
                }                
                if ($input->tipo == "header") {
                    $array = $ped->obtenerEncabezados($input->patente, $input->aduana, $input->year, $input->month, $input->rfc, $input->tipo, $input->pedimento, $input->ie);
                    if (empty($array)) {
                        $db = $misc->sitawin($input->patente, $input->aduana);
                        if (isset($db) && !empty($db)) {
                            $array = $db->encabezado($input->rfc, $input->year, $input->month);
                        }
                    }
                }
//                if (empty($array) && $input->year >= 2015 && $input->patente == 3589 && $input->aduana == 640 && $input->tipo == "extendido") {
//                    $db = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITAW3589640R", 1433, "Pdo_Mssql");
//                    $array = $db->anexo24Extendido($input->rfc, $input->year, $input->month);
//                }
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/layouts/");
                if (count($array) > 0) {
                    if ($input->tipo == "extendido" && $input->isValid("download") && $input->download == "true") {
                        $excel = new OAQ_ExcelReportes();
                        $excel->setTitles(["Referencia", "Operacion", "TipoOperacion", "Patente", "Aduana", "Pedimento", "TransporteEntrada", "TransporteArribo", "TransporteSalida", "FechaEntrada", "FechaPago", "FirmaValidacion", "FirmaBanco", "TipoCambio", "CvePed", "Regimen", "AduanaEntrada", "ValorDolares", "ValorAduana", "Fletes", "Seguros", "Embalajes", "OtrosIncrementales", "DTA", "IVA", "IGI", "PREV", "CNT", "TotalEfectivo", "PesoBruto", "Bultos", "Guías", "NumFactura", "Cove", "FechaFactura", "Incoterm", "ValorFacturaUsd", "ValorFacturaMonExt", "NomProveedor", "TaxId", "PaisFactura", "Divisa", "FactorMonExt", "NumParte", "Descripcion", "Fraccion", "OrdenFraccion", "PrecioUnitario", "ValorMonExt", "ValorAduanaMXN", "CantUMC", "UMC", "CantUMT", "UMT", "PaisOrigen", "TLC", "TLCAN", "TLCUE", "PROSEC", "TasaAdvalorem", "PaisVendedor", "PatenteOriginal", "AduanaOriginal", "PedimentoOriginal"]);
                        if($debug == true) {
                            var_dump($array);
                            return;
                        }
                        $excel->setData($array);
                        $excel->setFilename("ANEXO24_" . $input->rfc . "_" . date("Ymd-His") . ".xlsx");
                        $excel->layoutAnexo24Clientes();
                        return;
                    }
                    if ($input->tipo == "header" && $input->isValid("download") && $input->download == "true") {
                        $excel = new OAQ_ExcelReportes();
                        $excel->setTitles(["Referencia", "Operacion", "Patente", "Aduana", "Pedimento", "Trafico", "TransporteEntrada", "TransporteArribo", "TransporteSalida", "FechaEntrada", "FechaPago", "FirmaValidacion", "FirmaBanco", "TipoCambio", "CvePed", "Regimen", "AduanaEntrada", "ValorDolares", "ValorAduana", "Fletes", "Seguros", "Embalajes", "OtrosIncrementales", "DTA", "IVA", "IGI", "PREV", "CNT", "TotalEfectivo", "PesoBruto", "Bultos"]);
                        $excel->setData($array);
                        $excel->setFilename("ENCABEZADO_" . $input->rfc . "_" . date("Ymd-His") . ".xlsx");
                        $excel->layoutAnexo24Clientes();
                    }
                    $view->excelUrl = http_build_query($input->getEscaped());
                    if ($input->patente == 9999 && $input->aduana == 999) {
                        $view->aduana = " Todas Aduanas";
                    } else {
                        $view->aduana = $input->patente . "-" . $input->aduana;
                    }
                    if($debug == true) {
                        var_dump($array);
                        return;
                    }
                    $view->data = $array;
                    if ($input->tipo == "parcial") {
                        echo $view->render("pedimentos-parcial.phtml");
                    }
                    if ($input->tipo == "header") {
                        echo $view->render("pedimentos-header.phtml");
                    }
                    if ($input->tipo == "extendido") {
                        echo $view->render("pedimentos-extendido.phtml");
                    }
                } else {
                    echo $view->render("pedimentos-empty.phtml");
                    return;
                }
            }
            return;
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function reporteCuentaDeGastosAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "page" => array("Digits"),
                "rows" => array("Digits"),
                "excel" => array("StringToLower"),
            );
            $v = array(
                "fechaIni" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}-\d{2}-\d{2}$/"),"default" => date('Y-m-d')),
                "fechaFin" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}-\d{2}-\d{2}$/"),"default" => date('Y-m-d')),
                "page" => array(new Zend_Validate_Int(), "default" => 1),
                "rows" => array(new Zend_Validate_Int(), "default" => 20),
                "excel" => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("fechaIni") && $input->isValid("fechaFin")) {
                $dexcel = filter_var($input->excel, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                
                $sica = new OAQ_SicaDb("192.168.200.5", "sa", "adminOAQ123", "SICA", 1433, "SqlSrv");
                $rows = $sica->facturacionCliente($this->_session->username, $input->fechaIni, $input->fechaFin);
                
                if ($dexcel === false) {
                    $arr = array(
                        "total" => $sica->totalFacturacionCliente($this->_session->username, $input->fechaIni, $input->fechaFin),
                        "rows" => empty($rows) ? array() : $rows,
                    );
                    $this->_helper->json($arr);
                } else {
                    $reportes = new OAQ_ExcelReportesClientes();
                    $reportes->reporteFacturacionCliente($rows);
                }
                
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function cuentaDeGastosAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(false);
        ini_set("soap.wsdl_cache_enabled", 0);
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "rfc" => "StringToUpper",
                "idAduana" => "Digits",
                "page" => array("Digits"),
                "size" => array("Digits"),
            );
            $v = array(
                "rfc" => "NotEmpty",
                "fechaIni" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}-\d{2}-\d{2}$/")),
                "fechaFin" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}-\d{2}-\d{2}$/")),
                "desglose" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("rfc") && $input->isValid("fechaIni") && $input->isValid("fechaFin") && $input->isValid("desglose")) {
                $this->view->rfc = $input->rfc;
                if ($input->rfc == $this->_session->rfc) {
                    $sica = new OAQ_SicaDb("192.168.200.5", "sa", "adminOAQ123", "SICA", 1433, "SqlSrv");
                    $result = $sica->facturacionCliente($input->rfc, $input->fechaIni, $input->fechaFin);
                    $this->view->results = $result;
                    $context = stream_context_create(array(
                        "ssl" => array(
                            "verify_peer" => false,
                            "verify_peer_name" => false,
                            "allow_self_signed" => true
                        )
                    ));
                    $con = new Application_Model_WsWsdl();
                    if (($wsdl = $con->getWsdl(3589, 640, "sitawin"))) {
                        $this->view->soapSitawinQro = new Zend_Soap_Client($wsdl, array("stream_context" => $context));
                    }
                    if (($wsdl = $con->getWsdl(3589, 240, "sitawin"))) {
                        $this->view->soapSitawinNld = new Zend_Soap_Client($wsdl, array("stream_context" => $context));
                    }
                    if (($wsdl = $con->getWsdl(3589, 800, "sitawin"))) {
                        $this->view->soapSitawinCol = new Zend_Soap_Client($wsdl, array("stream_context" => $context));
                    }
                } else {
                    throw new Exception("Invalid input!");
                }
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function descargarFtpAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $f = array(
                "id" => array("StringTrim", "StripTags", "Digits"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($i->isValid("id")) {
                $ftp = new OAQ_Archivos_FtpDescarga($i->id, $this->_appconfig->getParam("ftpfolder"), $this->_session->username);
                if (($link = $ftp->obtenerLink())) {
                    echo $link;
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function descargarAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => "Digits",
        );
        $v = array(
            "id" => array("NotEmpty", new Zend_Validate_Int()),
        );
        $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($i->isValid("id")) {
            $exp = new OAQ_Expediente_Descarga();
            $mppr = new Clientes_Model_Repositorio();
            $arr = $mppr->datos($i->id, $this->_session->username);
            $files = $mppr->archivosCliente($arr["referencia"], $arr["patente"], $arr["aduana"]);
            if (isset($files) && !empty($files)) {
                $zipName = $exp->zipFilename($arr["patente"], $arr["aduana"], $arr["pedimento"], $arr["referencia"], $arr["rfcCliente"]);
                $zipDir = "D:\\Tmp\zips";
                if (APPLICATION_ENV === "production") {
                    $zipDir = "/tmp/zips";
                }
                if (!file_exists($zipDir)) {
                    mkdir($zipDir, 0777, true);
                }
                $zipFilename = $zipDir . DIRECTORY_SEPARATOR . $zipName;
                if (file_exists($zipFilename)) {
                    unlink($zipFilename);
                }
                $zip = new ZipArchive();
                if ($zip->open($zipFilename, ZIPARCHIVE::CREATE) !== TRUE) {
                    return null;
                }
                foreach ($files as $file) {
                    if (file_exists($file["ubicacion"])) {
                        $tmpfile = $zipDir . DIRECTORY_SEPARATOR . sha1($file["ubicacion"]);
                        copy($file["ubicacion"], $tmpfile);
                        $cp_filename = $exp->filename($arr["patente"], $arr["aduana"], $arr["pedimento"], basename($file["ubicacion"]), $file["tipo_archivo"], $arr["rfcCliente"], $file);                        
                        if (($zip->addFile($tmpfile, $cp_filename)) === true) {
                            $added[] = $cp_filename;
                        }
                        unset($tmpfile);
                    }
                }
                $complementos = $mppr->complementosReferencia($arr["referencia"]);
                if (!empty($complementos)) {
                    if (file_exists($file["ubicacion"])) {
                        $tmpfile = $zipDir . DIRECTORY_SEPARATOR . sha1($file["ubicacion"]);
                        copy($file["ubicacion"], $tmpfile);
                        if (($zip->addFile($tmpfile, $exp->filename($arr["patente"], $arr["aduana"], $arr["pedimento"], basename($file["ubicacion"]), $file["tipo_archivo"], $this->_session->username))) === true) {
                            $added[] = $tmpfile;
                        }
                        unset($tmpfile);
                    }
                }
            
                $val = new OAQ_ArchivosValidacion();
                if (isset($arr["pedimento"])) {
                    $arch_val = $val->archivosDePedimento($arr["patente"], $arr["aduana"], $arr["pedimento"]);
                    if (!empty($arch_val)) {
                        $mppr_val = new Automatizacion_Model_ArchivosValidacionMapper();
                        foreach ($arch_val as $a_val) {
                            if ($a_val['idArchivoValidacion']) {
                                $file_val = $mppr_val->fileContent($a_val['idArchivoValidacion']);
                                if ($file_val) {
                                    $zip->addFromString($a_val['archivoNombre'], base64_decode(base64_decode($file_val["contenido"])));
                                }
                            }
                        }
                    }
                }

                if (($zip->close()) === TRUE) {
                    $closed = true;
                }
                if ($closed === true) {
                    foreach ($added as $tmp) {
                        unlink($tmp);
                    }
                }
                if (file_exists($zipFilename)) {
                    if (!is_file($zipFilename)) {
                        header($this->getRequest()->getServer("SERVER_PROTOCOL") . " 404 Not Found");
                        header($this->getRequest()->getServer("SERVER_PROTOCOL") . " 404 Not Found");
                        echo "File not found";
                    } else if (!is_readable($zipFilename)) {
                        header($this->getRequest()->getServer("SERVER_PROTOCOL") . " 403 Forbidden");
                        echo "File not readable";
                    }
                    header($this->getRequest()->getServer("SERVER_PROTOCOL") . " 200 OK");
                    header("Content-Type: application/zip");
                    header("Content-Transfer-Encoding: Binary");
                    header("Content-Length: " . filesize($zipFilename));
                    header("Content-Disposition: attachment; filename=\"" . basename($zipFilename) . "\"");
                    readfile($zipFilename);
                    unlink($zipFilename);
                    return false;
                }
            } else {
                throw new Exception("No files!");
            }
        } else {
            throw new Exception("Invalid input!");
        }
    }

    public function operacionesClientesAction() {
        try {
            $year = $this->_request->getParam('year', (int) date('Y'));
            $rfc = $this->_request->getParam('rfc', null);
            $patente = $this->_request->getParam('patente', null);
            $aduana = $this->_request->getParam('aduana', null);
            if ($patente == 3589) {
                if ($aduana == 640) {
                    $sitawin = new OAQ_Sitawin(true, '192.168.200.5', 'sa', 'adminOAQ123', 'SITAW3010640', 1433, 'Pdo_Mssql');
                } elseif ($aduana == 646) {
                    $sitawin = new OAQ_Sitawin(true, '192.168.0.253', 'sa', 'sqlcointer', 'SITAW3589640', 1433, 'Pdo_Mssql');
                } elseif ($aduana == 240) {
                    $sitawin = new OAQ_Sitawin(true, '192.168.200.5', 'sa', 'adminOAQ123', 'SITAW3589240', 1433, 'Pdo_Mssql');
                }
            }
            $data = $sitawin->operacionesCliente($year, $this->_session->username);
            $sum[0] = 0;
            $sum[1] = 0;
            $sum[2] = 0;
            $sum[3] = 0;
            $sum[4] = 0;
            $sum[5] = 0;
            $sum[6] = 0;
            $sum[7] = 0;
            $sum[8] = 0;
            $sum[9] = 0;
            $sum[10] = 0;
            $sum[11] = 0;
            if (!empty($data)) {
                foreach ($data as $item) {
                    $graph_data[] = array(
                        'name' => ($item["TipoOperacion"] == 1) ? 'IMPO' : 'EXPO',
                        'data' => array(
                            (int) $item["Ene"] ? (int) $item["Ene"] : null,
                            (int) $item["Feb"] ? (int) $item["Feb"] : null,
                            (int) $item["Mar"] ? (int) $item["Mar"] : null,
                            (int) $item["Abr"] ? (int) $item["Abr"] : null,
                            (int) $item["May"] ? (int) $item["May"] : null,
                            (int) $item["Jun"] ? (int) $item["Jun"] : null,
                            (int) $item["Jul"] ? (int) $item["Jul"] : null,
                            (int) $item["Ago"] ? (int) $item["Ago"] : null,
                            (int) $item["Sep"] ? (int) $item["Sep"] : null,
                            (int) $item["Oct"] ? (int) $item["Oct"] : null,
                            (int) $item["Nov"] ? (int) $item["Nov"] : null,
                            (int) $item["Dic"] ? (int) $item["Dic"] : null
                        ),
                        'pointPadding' => 10,
                        'pointWidth' => 10
                    );
                    $sum[0] += $item["Ene"];
                    $sum[1] += $item["Feb"];
                    $sum[2] += $item["Mar"];
                    $sum[3] += $item["Abr"];
                    $sum[4] += $item["May"];
                    $sum[5] += $item["Jun"];
                    $sum[6] += $item["Jul"];
                    $sum[7] += $item["Ago"];
                    $sum[8] += $item["Sep"];
                    $sum[9] += $item["Oct"];
                    $sum[10] += $item["Nov"];
                    $sum[11] += $item["Dic"];
                }
                $graph_data[] = array(
                    'name' => 'TOTAL',
                    'data' => array(
                        (int) $sum[0] ? (int) $sum[0] : null,
                        (int) $sum[1] ? (int) $sum[1] : null,
                        (int) $sum[2] ? (int) $sum[2] : null,
                        (int) $sum[3] ? (int) $sum[3] : null,
                        (int) $sum[4] ? (int) $sum[4] : null,
                        (int) $sum[5] ? (int) $sum[5] : null,
                        (int) $sum[6] ? (int) $sum[6] : null,
                        (int) $sum[7] ? (int) $sum[7] : null,
                        (int) $sum[8] ? (int) $sum[8] : null,
                        (int) $sum[9] ? (int) $sum[9] : null,
                        (int) $sum[10] ? (int) $sum[10] : null,
                        (int) $sum[11] ? (int) $sum[11] : null
                    ),
                    'pointPadding' => 10,
                    'pointWidth' => 10
                );
                echo json_encode($graph_data);
                exit;
            } else {
                echo json_encode(array('success' => false));
                exit;
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function archivosValidacionAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost();
                if ((isset($post["patente"]) && $post["patente"] != '') && (isset($post["aduana"]) && $post["aduana"] != '') && (isset($post["pedimento"]) && $post["pedimento"] != '')) {
                    $tbl = new Operaciones_Model_ValidadorArchivos();
                    $model = new Operaciones_Model_ValidadorRespuestas();
                    $files = array();
                    $files["validacion"] = $tbl->obtenerPorPedimento($post["patente"], $post["aduana"], $post["pedimento"]);
                    if (isset($files["validacion"]['id'])) {
                        $res = $model->obtener($files["validacion"]['id']);
                        if (isset($res) && $res !== false && !empty($res) && is_array($res)) {
                            $files["respuesta"] = $res;
                        }
                    }
                    if (isset($files) && $files !== false && !empty($files) && is_array($files)) {
                        $html = '<span><strong>Archivos de validación:</strong> </span>';
                        $html .= "<span><a href=\"/clientes/data/download-raw-file?id={$files["validacion"]['id']}\" style=\"margin-right: 5px\">{$files["validacion"]['archivo']}</a> | ";
                        if (isset($files["respuesta"]) && is_array($files["respuesta"])) {
                            foreach ($files["respuesta"] as $item) {
                                $html .= "<a href=\"/clientes/data/download-raw-file?id={$item["id"]}&type=res\" style=\"margin-right: 5px\">{$item["archivo"]}</a> | ";
                            }
                        }
                        $html .= '</span>';
                        echo Zend_Json::encode(array('success' => true, 'html' => $html));
                        return false;
                    }
                    echo Zend_Json::encode(array('success' => false));
                    return false;
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

}
