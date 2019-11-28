<?php

class Automatizacion_ReportesController extends Zend_Controller_Action {

    protected $_config;
    protected $_logger;
    protected $_campos;
    protected $_fechas;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_logger = Zend_Registry::get("logDb");
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
    }

    /**
     * Esta función hacer preg_match de un array
     * 
     * @param Array $array Arreglo que contiene las cadenas a las cuales se va comparar
     * @param String $match Cadena que se va comparar con el arreglo válido
     * @return boolean
     */
    protected function pregMatch($array, $match) {
        foreach ($array as $item) {
            if (preg_match('/' . $match . '/i', $item)) {
                return true;
            }
        }
        return false;
    }

    protected function worksheetProcessor($worksheet, $highestRow, $highestColumnIndex, $worksheetTitle) {
        $array = array();
        for ($row = 2; $row <= $highestRow; ++$row) {
            for ($col = 0; $col < $highestColumnIndex; ++$col) {
                $cell = $worksheet->getCellByColumnAndRow($col, $row);
                if ($worksheet->getCellByColumnAndRow($col, 1)->getValue() != '') {
                    if ($cell->getFormattedValue() != '') {
                        if ($this->pregMatch($this->_fechas, trim($worksheet->getCellByColumnAndRow($col, 1)->getValue())) != true) {
                            if ($this->pregMatch($this->_campos, trim($worksheet->getCellByColumnAndRow($col, 1)->getValue())) != true) {
                                $tmp[trim($worksheet->getCellByColumnAndRow($col, 1)->getValue())] = preg_match('/=/', $cell->getValue()) ? $cell->getCalculatedValue() : $cell->getFormattedValue();
                            } else {
                                $tmp[trim($worksheet->getCellByColumnAndRow($col, 1)->getValue())] = str_replace('$', '', $cell->getFormattedValue());
                            }
                        } else {
                            if (preg_match('/fechaEntrada/i', trim($worksheet->getCellByColumnAndRow($col, 1)->getValue()))) {
                                $tmp[trim($worksheet->getCellByColumnAndRow($col, 1)->getValue())] = date('Y-m-d H:i:s', PHPExcel_Shared_Date::ExcelToPHP($cell->getValue()));
                            }
                            if (preg_match('/fecha$/i', trim($worksheet->getCellByColumnAndRow($col, 1)->getValue()))) {
                                $tmp[trim($worksheet->getCellByColumnAndRow($col, 1)->getValue())] = date('Y-m-d H:i:s', PHPExcel_Shared_Date::ExcelToPHP($cell->getValue()));
                            }
                            if (preg_match('/fechaPagoContribuciones/i', trim($worksheet->getCellByColumnAndRow($col, 1)->getValue()))) {

                                if (preg_match('/a.m/i', $cell->getFormattedValue()) || preg_match('/p.m/i', $cell->getFormattedValue())) {
                                    $date = explode(' ', $cell->getFormattedValue());
                                    $d = explode('/', $date[0]);
                                }
                                if (isset($d)) {
                                    $tmp[trim($worksheet->getCellByColumnAndRow($col, 1)->getValue())] = $d[2] . '-' . $d[1] . '-' . $d[0];
                                } else {
                                    $tmp[trim($worksheet->getCellByColumnAndRow($col, 1)->getValue())] = '';
                                }
                            }
                        }
                    } else {
                        continue;
                    }
                }
            }
            if (isset($tmp)) {
                if (isset($tmp["pedimento"])) {
                    $tmp["operacion"] = $tmp["pedimento"];
                    $info = explode('-', $tmp["pedimento"]);
                    $tmp["patente"] = $info[2];
                    $tmp["aduana"] = $info[1];
                    $tmp["pedimento"] = $info[3];
                    unset($info);
                }
                array_push($array, $tmp);
                unset($tmp);
            }
        }
        if (isset($array)) {
            return $array;
        }
        return null;
    }

    protected function _addToTable($idEnc, $operacion, $obj, $array) {
        if (!empty($array)) {
            foreach ($array as $item) {
                if ($operacion == $item["operacion"]) {
                    $obj->add($idEnc, $item);
                }
            }
        }
    }
    
    /**
     * /automatizacion/reportes/reportes?patente=3589&aduana=646&rfc=ADM111215BS6&tipo=cargoquin&fechaIni=2016-02-01&fechaFin=2016-02-15
     * /automatizacion/reportes/reportes?patente=3589&aduana=646&rfc=CIN0309091D3&tipo=cnh&fechaIni=2016-01-01&fechaFin=2016-01-15
     * 
     * @throws Exception
     */
    public function reportesAction() {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            ini_set("soap.wsdl_cache_enabled", 0);
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "patente" => "Digits",
                "aduana" => "Digits",
                "year" => "Digits",
                "month" => "Digits",
            );
            $v = array(
                "patente" => array("Digits", new Zend_Validate_Int()),
                "aduana" => array("Digits", new Zend_Validate_Int()),
                "year" => array("Digits", new Zend_Validate_Int(),
                    new Zend_Validate_Between(
                            array(
                        "min" => 2012,
                        "max" => 2018,
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
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid()) {
                $misc = new OAQ_Misc();
                $view = new Zend_View();
                $reportes = new OAQ_ExcelReportes();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/reportes/");
                if ($input->tipo == "cargoquin") {
                    $db = $misc->sitawinCargoquin($input->patente, $input->aduana);
                    if (!isset($db)) {
                        $view->message = "No hay información para la aduana seleccionada.";
                        echo $view->render("custom.phtml");
                        return;
                    }
                    $pedimentos = $db->grupoCargoQuin($input->rfc, null, null, $input->fechaIni, $input->fechaFin);
                    $fracciones = $db->grupoCargoQuinFracciones($input->rfc, null, null, $input->fechaIni, $input->fechaFin);
                    $partes = $db->grupoCargoQuinPartes($input->rfc, null, null, $input->fechaIni, $input->fechaFin);
                    if (isset($pedimentos) && !empty($pedimentos)) {
                        $reportes->setFilename($input->rfc . "_" . $input->fechaIni . "_" . $input->fechaFin);
                        $reportes->setData($pedimentos);
                        $reportes->reporteCargoQuin();
                        $reportes->reporteCargoQuinFracciones($fracciones);
                        $reportes->reporteCargoQuinPartes($partes);
                        $reportes->download();
                    } else {
                        throw new Exception("No records!");
                    }
                } elseif ($input->tipo == "cnh") {
                    $db = $misc->sitawinCargoquin($input->patente, $input->aduana);
                    if (isset($db) && !empty($db)) {
                        $rows = $db->cnhIndustrial($input->rfc, null, null, $input->fechaIni, $input->fechaFin);
                        $view->content = $rows;
                        echo $view->render("cnh.phtml");
                        return;
                    } else {
                        $view->message = "No hay información para la aduana seleccionada.";
                        echo $view->render("custom.phtml");
                        return;
                    }
                }
            } else {
                $view->message = "El layout seleccionado no es válido.";
                echo $view->render("custom.phtml");
                return;
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
    /**
     * 
     * @throws Exception
     */
    public function reporteActividadesAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
            );
            $v = array(
                "fecha" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/"), "default" => date("Y-m-d")),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            $mppr = new Principal_Model_UsuariosActividades();
            $view = new Zend_View();
            $view->setScriptPath(APPLICATION_PATH . "/../library/Templates/");
            $view->fecha = $input->fecha;
            $arr = $mppr->obtenerActividades($input->fecha);
            if (isset($arr) && !empty($arr)) {
                $view->actividades = $arr;                
            } else {
                throw new Exception("No activities.");
            }
            $emails = new OAQ_EmailsTraffic();
            $emails->setSubject("Reporte de actividades " . $input->fecha);
            if (APPLICATION_ENV == "production") {
                $emails->addTo("david.lopez@oaq.com.mx", "David López Mendoza");
                $emails->addTo("dlopez@oaq.com.mx", "David López Rosales");
                $emails->addBcc("ti.jvaldez@oaq.com.mx", "Jaime E. Valdez");
            } else if (APPLICATION_ENV == "staging" || APPLICATION_ENV == "development") {
                $emails->addTo("ti.jvaldez@oaq.com.mx", "Jaime E. Valdez");
            }
            $emails->contenidoPersonalizado($view->render("actividades.phtml"));
            $emails->send();
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
    /*
     * https://oaq.dnsalias.net/automatizacion/reportes/expedientes?fecha=2018-11-30
     * http://localhost:8090/automatizacion/reportes/expedientes?fecha=2018-11-30
     */
    public function expedientesAction() {
        try {
            header('Content-Type: application/json');
            $f = array(
                "*" => array("StringTrim", "StripTags"),
            );
            $v = array(
                "fecha" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/"), "default" => date("Y-m-d")),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            
            $view = new Zend_View();
            $view->setScriptPath(APPLICATION_PATH . "/../library/Templates/");
            
            $mppr = new Archivo_Model_RepositorioIndex();
            $arr_op = $mppr->obtenerNoCompletos($input->fecha, true);
            $arr_ad = $mppr->obtenerNoCompletos($input->fecha, null, true);
            
            $emails = new OAQ_EmailsTraffic();
            $emails->setSubject("Reporte de expedientes " . $input->fecha);
            
            $emails->addTo("dlopez@oaq.com.mx", "David López Rosales");
            $emails->addTo("soporte@oaq.com.mx", "Soporte OAQ");
            
            // cada jueves enviar el reporte
            
            if (!empty($arr_op)) {
                $view->expedientes_op = $arr_op;
            }
            if (!empty($arr_ad)) {
                $view->expedientes_ad = $arr_ad;
            }
            
            $emails->contenidoPersonalizado($view->render("expedientes.phtml"));
            $emails->send();
            
            $this->_helper->json(array("success" => true,"results" =>  array("operaciones" => $arr_op, "administracion" => $arr_ad)));
            
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

}
