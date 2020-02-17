<?php

class Administracion_GetController extends Zend_Controller_Action {

    protected $_session;
    protected $_config;
    protected $_appconfig;
    protected $_db;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $this->_db = Zend_Registry::get("oaqintranet");
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
    }
    
    public function reporteFacturacionAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idAduana" => "Digits",
                "idCliente" => "Digits",
                "page" => array("Digits"),
                "rows" => array("Digits"),
                "filtro" => array("Digits"),
                "excel" => array("StringToLower"),
            );
            $v = array(
                "page" => array(new Zend_Validate_Int(), "default" => 1),
                "rows" => array(new Zend_Validate_Int(), "default" => 20),
                "idAduana" => array("NotEmpty", new Zend_Validate_Int()),
                "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                "fechaInicio" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}-\d{2}-\d{2}$/")),
                "fechaFin" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}-\d{2}-\d{2}$/")),
                "filtro" => array("NotEmpty", new Zend_Validate_Int()),
                "excel" => array("NotEmpty"),
                "filterRules" => array("NotEmpty")
            );            
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            
            if ($input->isValid("fechaInicio") && $input->isValid("fechaFin")) {                
                $dexcel = filter_var($input->excel, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                $reportes = new OAQ_ExcelReportes();
                if ($dexcel == false) {
                    $rows = $this->_reporteTrafico($input->page, $input->rows, $input->idAduana, $input->idCliente, $input->fechaInicio, $input->fechaFin, $input->filterRules);
                    $arr = array(
                        "total" => $this->_totalReporte($input->idAduana, $input->idCliente, $input->fechaInicio, $input->fechaFin, $input->filterRules),
                        "rows" => empty($rows) ? array() : $rows,
                    );
                    $this->_helper->json($arr);
                } else {
                    $rows = $this->_reporteTrafico(null, null, $input->idAduana, $input->idCliente, $input->fechaInicio, $input->fechaFin, $input->filterRules);
                    $reportes->reportesTrafico(84, $rows);
                }
            } else {
                $this->_helper->json(array("success" => false, "message" => "Invalid Input!"));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    protected function _reporteTrafico($page, $rows, $idAduana, $idCliente, $fechaInicio, $fechaFin, $filterRules = null) {
        try {
            $sql = $this->_db->select()
                    ->from(array("r" => "rpt_cuentas"), array(
                        "r.id",
                        "r.idTrafico",
                        "r.patente",
                        "r.aduana",
                        "r.pedimento",
                        "r.referencia",
                        "r.folio",
                        "r.nomCliente AS nombreCliente",
                        "r.tipoOperacion AS ie",
                        new Zend_Db_Expr("DATE_FORMAT(r.fechaFacturacion,'%Y-%m-%d') AS fechaFacturacion"),
                        new Zend_Db_Expr("DATE_FORMAT(r.fechaPago,'%Y-%m-%d') AS fechaPago"),
                        new Zend_Db_Expr("(SELECT sum(c.importe) FROM rpt_cuenta_conceptos AS c WHERE c.idCuenta = r.id AND c.tipo = 'C') AS pagoHechos"),
                        new Zend_Db_Expr("(SELECT sum(c.importe) FROM rpt_cuenta_conceptos AS c WHERE c.idCuenta = r.id AND c.tipo = 'S') AS sinComprobar"),
                        new Zend_Db_Expr("(SELECT sum(c.importe) FROM rpt_cuenta_conceptos AS c WHERE c.idCuenta = r.id AND c.nomConcepto LIKE '%MANIOBRAS%' AND c.tipo = 'C') AS maniobras"),
                        "r.honorarios",
                        "r.anticipo",
                        "r.iva",
                        "(r.subTotal - r.anticipo) AS subTotal",
                        "r.total",
                    ))
                    ->joinLeft(array("t" => "traficos"), "r.idTrafico = t.id", array("id AS idTrafico", "t.blGuia", "(CASE WHEN a.tipoAduana = 3 THEN t.nombreBuque ELSE 'N/A' END) AS nombreBuque"))
                    ->joinInner(array("a" => "trafico_aduanas"), "a.id = t.idAduana", array(""))
                    ->where("r.fechaFacturacion >= ?", date("Y-m-d H:i:s", strtotime($fechaInicio)))
                    ->where("r.fechaFacturacion <= ?", date("Y-m-d H:i:s", strtotime($fechaFin)))
                    ->where("r.pagada IS NULL")
                    ->limit($rows, ($page - 1) * $rows)
                    ->order(array("r.folio ASC"));
            if (isset($idCliente)) {
                $mapper = new Trafico_Model_ClientesMapper();
                $arr = $mapper->datosCliente($idCliente);                
                $sql->where("r.rfcCliente = ?", $arr['rfc']);
            }
            if (isset($rows) && isset($page)) {
                $sql->limit($rows, ($page - 1) * $page);
            }
            if (isset($filterRules)) {
                $this->_filterRules($sql, $filterRules);
            }
            if ((int) $idAduana != 0) {                
                $mppr = new Trafico_Model_TraficoAduanasMapper();
                $arr = $mppr->aduana($idAduana);
                
                $sql->where("r.patente = ?", $arr['patente'])
                        ->where("r.aduana = ?", $arr['aduana']);
            }
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    protected function _filterRules(Zend_Db_Select $sql, $filterRules = null) {
        if (isset($filterRules)) {
            $filter = json_decode(html_entity_decode($filterRules));
            foreach ($filter AS $item) {
                if ($item->field == "referencia" && $item->value != "") {
                    $sql->where("r.referencia LIKE ?", "%" . trim($item->value) . "%");
                }
                if ($item->field == "folio" && $item->value != "") {
                    $sql->where("r.folio LIKE ?", "%" . trim($item->value) . "%");
                }
                if ($item->field == "pedimento" && $item->value != "") {
                    $sql->where("r.pedimento LIKE ?", "%" . trim($item->value) . "%");
                }
                if ($item->field == "nombreCliente" && $item->value != "") {
                    $sql->where("r.nomCliente LIKE ?", "%" . trim($item->value) . "%");
                }
            }
        }
    }
    
    protected function _totalReporte($idAduana, $idCliente, $fechaInicio, $fechaFin, $filterRules = null) {
        try {
            
            $sql = $this->_db->select()
                    ->from(array("r" => "rpt_cuentas"), array(
                        "count(*) AS total",
                    ))
                    ->where("r.fechaFacturacion >= ?", date("Y-m-d H:i:s", strtotime($fechaInicio)))
                    ->where("r.fechaFacturacion <= ?", date("Y-m-d H:i:s", strtotime($fechaFin)))
                    ->where("r.pagada IS NULL");
            if (isset($filterRules)) {
                $this->_filterRules($sql, $filterRules);
            }
            if (isset($idCliente)) {
                $mapper = new Trafico_Model_ClientesMapper();
                $arr = $mapper->datosCliente($idCliente);                
                $sql->where("r.rfcCliente = ?", $arr['rfc']);
            }
            if ((int) $idAduana != 0) {
                $mppr = new Trafico_Model_TraficoAduanasMapper();
                $arr = $mppr->aduana($idAduana);
            
                $sql->where("r.patente = ?", $arr['patente'])
                        ->where("r.aduana = ?", $arr['aduana']);
            }
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return (int) $stmt["total"];
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    public function excelEnviosPorComprobarAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "page" => array("Digits"),
            "size" => array("Digits"),
            "opcion" => array("Digits"),
        );
        $v = array(
            "page" => array(new Zend_Validate_Int(), "default" => 1),
            "size" => array(new Zend_Validate_Int(), "default" => 20),
            "opcion" => array("NotEmpty", new Zend_Validate_Int(), "default" => 0),
            "fechaIni" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/"), "default" => date("Y-m-d")),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        $sica = new OAQ_SicaDb("192.168.200.5", "sa", "adminOAQ123", "SICA", 1433, "SqlSrv");
        if ($input->isValid("opcion") && $input->isValid("fechaIni")) {
            $result = $sica->enviosPorComprobar($input->opcion);         
            if(isset($result) && !empty($result)) {
                $excel = new OAQ_ExcelReportes();
                $excel->setTitles(["CORRESPONSAL", "REFERENCIA", "CARGO", "ABONO", "SALDO", "FECHA ENVIO", "FECHA ELAB.", "SOLICITUDES", "FECHA LIBERACION", "CLIENTE"]);
                $excel->setData($result);
                $excel->setFilename("ENVIOSCOMP_" . date("Ymd") . ".xlsx");
                $excel->layoutClientes();
            }
        }
    }
    
    public function solicitudesMultiplesAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
            );
            $v = array(
                "ids" => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("ids")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");
                
                $mppr = new Trafico_Model_TraficoSolicitudesMapper();
                $arr = [];
                foreach ($input->ids as $id) {
                    $row = $mppr->obtener($id);
                    $arr[] = array(
                        "patente" => $row["patente"],
                        "aduana" => $row["aduana"],
                        "referencia" => $row["referencia"],
                        "pedimento" => str_pad($row["pedimento"], 7, '0', STR_PAD_LEFT),
                        "total" => $row["subtotal"] - $row["anticipo"],
                    );                    
                }
                $view->data = $arr;
                $ids = "";
                foreach ($input->ids as $id) {
                    $ids .= $id . ',';
                }
                $view->ids = substr($ids, 0, -1);
                
                $this->_helper->json(array("success" => true, "html" => $view->render("solicitudes-multiples.phtml")));
                
            } else {
                $this->_helper->json(array("success" => false, "message" => "Invalid input!"));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

}
