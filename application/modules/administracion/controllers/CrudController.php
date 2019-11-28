<?php

require_once "Spout/Autoloader/autoload.php";

class Administracion_CrudController extends Zend_Controller_Action {

    protected $_session;
    protected $_config;
    protected $_appconfig;
    protected $_filtrosCookies;
    protected $_db;
    protected $_res;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $sica = new OAQ_SicaDb("192.168.200.5", "sa", "adminOAQ123", "SICA", 1433, "SqlSrv");
        $this->_db = $sica->getAdapter();
    }

    public function preDispatch() {
        $this->_session = NULL ? $this->_session = new Zend_Session_Namespace("") : $this->_session = new Zend_Session_Namespace($this->_config->app->namespace);
        if ($this->_session->authenticated == true) {
            $session = new OAQ_Session($this->_session, $this->_appconfig);
            $session->actualizar();
            $session->actualizarSesion();
        } else {
            $this->getResponse()->setRedirect($this->_appconfig->getParam("link-logout"));
        }
        $referencias = new OAQ_Referencias();
        $this->_res = $referencias->restricciones($this->_session->id, $this->_session->role);
    }

    public function reportesAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "tipoReporte" => "Digits",
                "idAduana" => "Digits",
                "page" => array("Digits"),
                "rows" => array("Digits"),
                "filtro" => array("Digits"),
                "excel" => array("StringToLower"),
            );
            $v = array(
                "page" => array(new Zend_Validate_Int(), "default" => 1),
                "rows" => array(new Zend_Validate_Int(), "default" => 20),
                "tipoReporte" => array("NotEmpty", new Zend_Validate_Int()),
                "idAduana" => array("NotEmpty", new Zend_Validate_Int()),
                "fechaInicio" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}-\d{2}-\d{2}$/")),
                "fechaFin" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}-\d{2}-\d{2}$/")),
                "filtro" => array("NotEmpty", new Zend_Validate_Int()),
                "excel" => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("tipoReporte") && $input->isValid("idAduana") && $input->isValid("fechaInicio") && $input->isValid("fechaFin")) {
                $dexcel = filter_var($input->excel, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                $reportes = new OAQ_ExcelReportes();
                if ($input->tipoReporte == 12) {
                    if ($dexcel === false) {
                        $arr = $this->_tiempos($input->page, $input->rows, $input->idAduana, $input->fechaInicio, $input->fechaFin, $input->filtro);
                        $this->_helper->json($arr);
                    } else {
                        $arr = $this->_tiempos(null, null, $input->idAduana, $input->fechaInicio, $input->fechaFin, $input->filtro);
                        $reportes->reportesTrafico($input->tipoReporte, $arr["rows"]);
                    }
                } else {
                    $this->_helper->json(array("success" => false, "message" => "Invalid report!"));
                }
            } else {
                $this->_helper->json(array("success" => false, "message" => "Invalid Input!"));
            }
        } catch (Exception $e) {
            $this->_helper->json(array("success" => false, "message" => $e->getMessage()));
        }
    }

    protected function _filtroAduana(Zend_Db_Select $sql, $idAduana) {
        $mapper = new Trafico_Model_TraficoAduanasMapper();
        $arr = $mapper->aduana($idAduana);
        if (isset($arr)) {
            $sql->where("Patente = ?", $arr["patente"])
                    ->where("AduanaID = ?", $arr["aduana"]);
            /*if ($arr["tipoAduana"] == 1 || $arr["tipoAduana"] == 2) {
                $sql->where("DATEDIFF(day, F.FechaPedimento, F.Fecha) > 3");
            }
            if ($arr["tipoAduana"] == 3) {
                $sql->where("DATEDIFF(day, F.FechaPedimento, F.Fecha) > 15");
            }
            if ($arr["tipoAduana"] == 4) {
                $sql->where("DATEDIFF(day, F.FechaPedimento, F.Fecha) > 3");
            }*/
        }
    }

    public function cantidadAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idAduana" => "Digits",
                "filtro" => array("Digits"),
            );
            $v = array(
                "idAduana" => array("NotEmpty", new Zend_Validate_Int()),
                "fechaInicio" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}-\d{2}-\d{2}$/")),
                "fechaFin" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}-\d{2}-\d{2}$/")),
                "filtro" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idAduana") && $input->isValid("fechaInicio") && $input->isValid("fechaFin")) {
                $mapper = new Trafico_Model_TraficoAduanasMapper();
                $arr = $mapper->aduana($input->idAduana);
                $sql = $this->_db->select()
                    ->from(array("F" => "Factura"), array("count(*) AS total"))
                    ->where("F.Fecha >= ?", date("Y-m-d H:i:s", strtotime($input->fechaInicio)))
                    ->where("F.Fecha <= ?", date("Y-m-d H:i:s", strtotime($input->fechaFin)))
                    ->where("F.Estatus = 'A'")
                    ->where("F.Pedimento <> 0");
                $sqll = $this->_db->select()
                    ->from(array("F" => "Factura"), array("count(*) AS total"))
                    ->where("F.Fecha >= ?", date("Y-m-d H:i:s", strtotime($input->fechaInicio)))
                    ->where("F.Fecha <= ?", date("Y-m-d H:i:s", strtotime($input->fechaFin)))
                    ->where("F.Estatus = 'A'")
                    ->where("F.Pedimento <> 0");
                if (isset($arr)) {
                    $sql->where("Patente = ?", $arr["patente"])
                            ->where("AduanaID = ?", $arr["aduana"]);
                    $sqll->where("Patente = ?", $arr["patente"])
                            ->where("AduanaID = ?", $arr["aduana"]);
                    if ($arr["tipoAduana"] == 1 || $arr["tipoAduana"] == 2) {
                        $sql->where("DATEDIFF(day, F.FechaPedimento, F.Fecha) > 3");
                        $leyendaFueraTiempo = "> 3 días";
                        $leyendaEnTiempo = "<= 3 días";
                    }
                    if ($arr["tipoAduana"] == 3) {
                        $sql->where("DATEDIFF(day, F.FechaPedimento, F.Fecha) > 15");
                        $leyendaFueraTiempo = "> 5 días";
                        $leyendaEnTiempo = "<= 5 días";
                    }
                    if ($arr["tipoAduana"] == 4) {
                        $sql->where("DATEDIFF(day, F.FechaPedimento, F.Fecha) > 3");
                        $leyendaFueraTiempo = "> 3 días";
                        $leyendaEnTiempo = "<= 3 días";
                    }
                    //
                    if ($arr["tipoAduana"] == 1 || $arr["tipoAduana"] == 2) {
                        $sqll->where("DATEDIFF(day, F.FechaPedimento, F.Fecha) <= 3");
                    }
                    if ($arr["tipoAduana"] == 3) {
                        $sqll->where("DATEDIFF(day, F.FechaPedimento, F.Fecha) <= 15");
                    }
                    if ($arr["tipoAduana"] == 4) {
                        $sqll->where("DATEDIFF(day, F.FechaPedimento, F.Fecha) <= 3");
                    }
                }
                $fueraTiempo = $this->_db->fetchRow($sql);
                $enTiempo = $this->_db->fetchRow($sqll);
                $total = ((int) $fueraTiempo["total"] + (int) $enTiempo["total"]);
                $pFueraTiempo = round(($fueraTiempo["total"] / $total) * 100);
                $pEnTiempo = round(($enTiempo["total"] / $total) * 100);
                $this->_helper->json(array(
                    "success" => true,
                    "aduana" => $arr["patente"] . "-" . $arr["aduana"],
                    "fueraTiempo" => $fueraTiempo["total"],
                    "pFueraTiempo" => $pFueraTiempo . " %",
                    "leyendaFueraTiempo" => $leyendaFueraTiempo,
                    "enTiempo" => $enTiempo["total"],
                    "pEnTiempo" => $pEnTiempo . " %",
                    "leyendaEnTiempo" => $leyendaEnTiempo,
                    "total" => $total
                ));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $e) {
            $this->_helper->json(array("success" => false, "message" => $e->getMessage()));
        }
    }
    
    protected function _tiempos($page, $rows, $idAduana, $fechaInicio, $fechaFin, $filtro = null) {
        try {
            $fields = array(
                "F.FolioID",
                "F.Patente",
                "F.AduanaID",
                "F.Pedimento",
                "F.Referencia",
                new Zend_Db_Expr("CONVERT(VARCHAR(10), F.Fecha, 111) AS Fecha"),
                new Zend_Db_Expr("CONVERT(VARCHAR(10), F.FechaPedimento, 111) AS FechaPedimento"),
                new Zend_Db_Expr("DATEDIFF(day, F.FechaPedimento, F.Fecha) AS FechaDiff"),
                "F.RefFactura AS Factura",
                "(SELECT TOP 1 C.Nombre FROM Cliente C WHERE C.ClienteID = F.ClienteID) AS Nombre",
            );
            $sql = $this->_db->select()
                    ->from(array("F" => "Factura"), $fields)
                    ->where("F.Fecha >= ?", date("Y-m-d H:i:s", strtotime($fechaInicio)))
                    ->where("F.Fecha <= ?", date("Y-m-d H:i:s", strtotime($fechaFin)))
                    ->where("F.Estatus = 'A'")
                    ->where("F.Pedimento <> 0")
                    ->order("FechaDiff DESC");
            $count = $this->_db->select()
                    ->from(array("F" => "Factura"), array("count(*) AS total"))
                    ->where("F.Fecha >= ?", date("Y-m-d H:i:s", strtotime($fechaInicio)))
                    ->where("F.Fecha <= ?", date("Y-m-d H:i:s", strtotime($fechaFin)))
                    ->where("F.Estatus = 'A'")
                    ->where("F.Pedimento <> 0");
            if ((int) $idAduana != 0) {
                $this->_filtroAduana($sql, $idAduana);
                $this->_filtroAduana($count, $idAduana);
            }
            if (isset($rows) && isset($page)) {
                $sql->limit($rows, ($page - 1));
            }
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                $total = $this->_db->fetchRow($count);
                return array(
                    "total" => !empty($stmt) ? $total["total"] : 0,
                    "rows" => !empty($stmt) ? $stmt : array(),
                );
            } else {
                return array(
                    "total" => 0,
                    "rows" => array(),
                );
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        } catch (Zend_Exception $ex) {
            throw new Exception("Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

}
