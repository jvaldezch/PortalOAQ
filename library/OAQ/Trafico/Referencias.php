<?php

class OAQ_Trafico_Referencias
{

    protected $_db;
    protected $_config;
    protected $_firephp;
    protected $_sessionId;
    protected $_sessionRole;


    function __construct($sessionId, $sessionRole)
    {
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $this->_db = Zend_Registry::get("oaqintranet");
        $this->_firephp = Zend_Registry::get("firephp");

        $this->_sessionId = $sessionId;
        $this->_sessionRole = $sessionRole;
    }

    protected function _cookies()
    {
        $request = new Zend_Controller_Request_Http();
        $filtrosCookies = array(
            "allOperations" => filter_var($request->getCookie("allOperations"), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            "pagadas" => filter_var($request->getCookie("pagadas"), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            "liberadas" => filter_var($request->getCookie("liberadas"), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            "impos" => filter_var($request->getCookie("impos"), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            "expos" => filter_var($request->getCookie("expos"), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            "fdates" => filter_var($request->getCookie("fdates"), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            "ninvoices" => filter_var($request->getCookie("ninvoices"), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            "checklist" => filter_var($request->getCookie("checklist"), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            "dateini" => filter_var($request->getCookie("dateini"), FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => "/^\d{4}\-\d{2}\-\d{2}$/"))),
            "dateend" => filter_var($request->getCookie("dateend"), FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => "/^\d{4}\-\d{2}\-\d{2}$/"))),
        );
        return $filtrosCookies;
    }
    
    protected function _filters(Zend_Db_Select $sql, $filterRules = null, $filtrosCookies = null) {

        $referencias = new OAQ_Referencias();
        $res = $referencias->restricciones($this->_sessionId, $this->_sessionRole);

        if ($this->_sessionRole == "inhouse") {
            $sql->where("t.rfcCliente IN (?)", $res["rfcs"])
                    ->where("t.idAduana IN (?)", $res["idsAduana"]);
            
        } else if (in_array($this->_sessionRole, array("super", "trafico_operaciones", "trafico", "trafico_aero", "trafico_ejecutivo", "gerente"))) {
            if (!empty($res["idsAduana"])) {
                $sql->where("t.idAduana IN (?)", $res["idsAduana"]);
            }
        }
        if ($this->_sessionRole == "corresponsal") {
            if (!empty($res["idsAduana"])) {
                $sql->where("t.idAduana IN (?)", $res["idsAduana"]);
            } else {
                $sql->where("t.idAduana IS NULL");
            }
        }

        if (isset($filterRules)) {
            $filter = json_decode(html_entity_decode($filterRules));
            foreach ($filter AS $item) {
                if ($item->field == "pedimento" && $item->value != "") {
                    $sql->where("t.pedimento LIKE ?", "%" . trim($item->value) . "%");
                }
                if ($item->field == "referencia" && $item->value != "") {
                    $sql->where("t.referencia LIKE ?", "%" . trim($item->value) . "%");
                }
                if ($item->field == "aduana" && $item->value != "") {
                    $sql->where("t.aduana LIKE ?", "%" . trim($item->value) . "%");
                }
                if ($item->field == "patente" && $item->value != "") {
                    $sql->where("t.patente LIKE ?", "%" . trim($item->value) . "%");
                }
                if ($item->field == "nombreCliente" && $item->value != "") {
                    $sql->where("c.nombre LIKE ?", "%" . trim($item->value) . "%");
                }
                if ($item->field == "blGuia" && $item->value != "") {
                    $sql->where("t.blGuia LIKE ?", "%" . trim($item->value) . "%");
                }
                if ($item->field == "contenedorCaja" && $item->value != "") {
                    $sql->where("t.contenedorCaja LIKE ?", "%" . trim($item->value) . "%");
                }
                if ($item->field == "ordenCompra" && $item->value != "") {
                    $sql->where("t.ordenCompra LIKE ?", "%" . trim($item->value) . "%");
                }
                if ($item->field == "proveedores" && $item->value != "") {
                    $sql->where("t.proveedores LIKE ?", "%" . trim($item->value) . "%");
                }
                if ($item->field == "nombre" && trim($item->value) != "") {
                    $mppr = new Usuarios_Model_UsuariosMapper();
                    $arr = $mppr->buscarUsuario(trim($item->value));
                    if (!empty($arr)) {
                        $sql->where("t.idUsuario IN (?)", $arr);
                    }
                }
            }
        }
        if (isset($filtrosCookies)) {
            if ($filtrosCookies["impos"] == true) {
                $sql->where("t.ie = 'TOCE.IMP'");
            }
            if ($filtrosCookies["expos"] == true) {
                $sql->where("t.ie = 'TOCE.EXP'");
            }
            if ($filtrosCookies["ninvoices"] == true) {
                $sql->where("t.fechaFacturacion IS NULL");
                if ($filtrosCookies["fdates"] == true) {
                    if ($filtrosCookies["dateini"] !== null && $filtrosCookies["dateend"] !== null) {
                        $sql->where("t.fechaPago >= ?", date('Y-m-d', strtotime($filtrosCookies["dateini"])))
                            ->where("t.fechaPago <= ?", date('Y-m-d', strtotime($filtrosCookies["dateend"])));
                    }
                }
            }
            if ($filtrosCookies["pagadas"] == true) {
                $sql->where("t.estatus = 2");
                if ($filtrosCookies["fdates"] == true) {
                    if ($filtrosCookies["dateini"] !== null && $filtrosCookies["dateend"] !== null) {
                        $sql->where("t.fechaPago >= ?", date('Y-m-d', strtotime($filtrosCookies["dateini"])))
                            ->where("t.fechaPago <= ?", date('Y-m-d', strtotime($filtrosCookies["dateend"])));
                    }
                }
            }
            if ($filtrosCookies["checklist"] == true) {
                $sql->where("t.revisionAdministracion IS NULL OR t.revisionOperaciones IS NULL OR t.completo IS NULL");
            }
            if ($filtrosCookies["liberadas"] == true) {
                $sql->where("t.estatus = 3");
                if ($filtrosCookies["fdates"] == true) {
                    if ($filtrosCookies["dateini"] !== null && $filtrosCookies["dateend"] !== null) {
                        $sql->where("t.fechaLiberacion >= ?", date('Y-m-d', strtotime($filtrosCookies["dateini"])))
                            ->where("t.fechaLiberacion <= ?", date('Y-m-d', strtotime($filtrosCookies["dateend"])));
                    }
                }
            } else {
                $sql->where("t.estatus NOT IN (3, 4)");
            }
            if ($filtrosCookies["allOperations"] !== true) {
                $sql->where("t.idUsuario = ?", $this->_sessionId);
                if ($filtrosCookies["fdates"] == true) {
                    if ($filtrosCookies["dateini"] !== null && $filtrosCookies["dateend"] !== null) {
                        $sql->where("t.creado >= ?", date('Y-m-d', strtotime($filtrosCookies["dateini"])))
                            ->where("t.creado <= ?", date('Y-m-d', strtotime($filtrosCookies["dateend"])));
                    }
                }
            } else {
                if ($filtrosCookies["fdates"] == true) {
                    if ($filtrosCookies["dateini"] !== null && $filtrosCookies["dateend"] !== null) {
                        $sql->where("t.fechaEta >= ?", date('Y-m-d', strtotime($filtrosCookies["dateini"])))
                            ->where("t.fechaEta <= ?", date('Y-m-d', strtotime($filtrosCookies["dateend"])));
                    }
                }
            }
        }
        if (!empty($this->_res["idsAduana"]) && $this->_sessionRole !== "inhouse") {
            $sql->where("t.idAduana IN (?)", $this->_res["idsAduana"]);
        }
    }

    public function referencias($page, $rows, $filterRules = null, $tipoAduana = null, $bodega = null)
    {
        $select = $this->_select($filterRules, $this->_cookies(), $tipoAduana, $bodega);

        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($rows);

        return array(
            "total" => $paginator->getTotalItemCount(),
            "rows" => iterator_to_array($paginator),
        );
    }

    protected function _select($filterRules = null, $cookies = null, $tipoAduana = null, $bodega = null)
    {
        try {
            $sql = $this->_db->select()
                ->from(array("t" => "traficos"), array(
                    "id",
                    "estatus",
                    "patente",
                    "aduana",
                    "pedimento",
                    "referencia",
                    "cvePedimento",
                    "regimen",
                    "rfcCliente",
                    "ie",
                    "blGuia",
                    "contenedorCaja",
                    "ordenCompra",
                    "carrierNaviera",
                    "proveedores",
                    "facturas",
                    "cantidadFacturas",
                    "cantidadPartes",
                    "tipoCarga",
                    "almacen",
                    "observaciones",
                    "ccConsolidado",
                    new Zend_Db_Expr("DATE_FORMAT(fechaEtd,'%Y-%m-%d') AS fechaEtd"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaEta,'%Y-%m-%d') AS fechaEta"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaNotificacion,'%Y-%m-%d %T') AS fechaNotificacion"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaEnvioDocumentos,'%Y-%m-%d') AS fechaEnvioDocumentos"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaEntrada,'%Y-%m-%d') AS fechaEntrada"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaPresentacion,'%Y-%m-%d') AS fechaPresentacion"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaDeposito,'%Y-%m-%d') AS fechaDeposito"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaEnvioProforma,'%Y-%m-%d %T') AS fechaEnvioProforma"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaVistoBueno,'%Y-%m-%d %T') AS fechaVistoBueno"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaProformaTercero,'%Y-%m-%d %T') AS fechaProformaTercero"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaVistoBuenoTercero,'%Y-%m-%d %T') AS fechaVistoBuenoTercero"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaRevalidacion,'%Y-%m-%d') AS fechaRevalidacion"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaPrevio,'%Y-%m-%d %T') AS fechaPrevio"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaPago,'%Y-%m-%d %T') AS fechaPago"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaSolicitudTransfer,'%Y-%m-%d %T') AS fechaSolicitudTransfer"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaArriboTransfer,'%Y-%m-%d %T') AS fechaArriboTransfer"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaLiberacion,'%Y-%m-%d %T') AS fechaLiberacion"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaEtaAlmacen,'%Y-%m-%d') AS fechaEtaAlmacen"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaFacturacion,'%Y-%m-%d') AS fechaFacturacion"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaComprobacion,'%Y-%m-%d') AS fechaComprobacion"),
                    new Zend_Db_Expr("diasDespacho"),
                    new Zend_Db_Expr("diasRetraso"),
                    "estatusRepositorio",
                    "cumplimientoAdministrativo",
                    "cumplimientoOperativo",
                    "idPlanta",
                    "idUsuario",
                    "semaforo",
                    "coves",
                    "edocuments",
                    "revisionAdministracion",
                    "revisionOperaciones",
                    "completo",
                    new Zend_Db_Expr("CASE WHEN t.revisionAdministracion IS NOT NULL AND t.revisionOperaciones IS NULL THEN 1 WHEN t.revisionAdministracion IS NULL AND t.revisionOperaciones IS NOT NULL THEN 2 WHEN t.revisionAdministracion IS NOT NULL AND t.revisionOperaciones IS NOT NULL THEN 3 ELSE 0 END AS estatusExpdnt"),
                ))
                ->joinInner(array("u" => "usuarios"), "u.id = t.idUsuario", array("nombre"))
                ->joinInner(array("a" => "trafico_aduanas"), "a.id = t.idAduana", array(""))
                ->joinInner(array("c" => "trafico_clientes"), "c.id = t.idCliente", array("nombre AS nombreCliente"))
                ->joinLeft(array("tc" => "trafico_tipocarga"), "tc.id = t.tipoCarga", array("tipoCarga AS carga"))
                ->joinLeft(array("p" => "trafico_clientes_plantas"), "p.id = t.idPlanta", array("descripcion AS descripcionPlanta"))
                ->joinLeft(array("l" => "trafico_almacen"), "l.id = t.almacen", array("nombre AS nombreAlmacen"))
                ->where("t.estatus <> 4")
                ->order(array("fechaEta DESC"));

            if (isset($bodega)) {
                $sql->where("t.pedimento IS NULL");
            } else {
                $sql->where("t.pedimento IS NOT NULL");
            }
            
            if (isset($tipoAduana)) {
                if ($tipoAduana == 1) {
                    $sql->where("a.tipoAduana = 1 AND t.cvePedimento IN ('V1', 'G1', 'E1', 'V5', 'F4', 'F5', 'A3')");
                } elseif ($tipoAduana == 2) {
                    $sql->where("a.tipoAduana = 2 OR (a.tipoAduana = 1 AND t.cvePedimento NOT IN ('V1', 'G1', 'E1', 'V5', 'F4', 'F5', 'A3'))");
                } else {
                    $sql->where('a.tipoAduana = ?', $tipoAduana);
                }
            }            
            $this->_filters($sql, $filterRules, $cookies);
            $this->_firephp->info($sql->assemble());
            return $sql;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
}
