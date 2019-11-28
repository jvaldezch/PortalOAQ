<?php

class Bitacora_GetController extends Zend_Controller_Action {

    protected $_session;
    protected $_config;
    protected $_appconfig;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
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

    public function consecutivoAction() {
        try {
            $f = array(
                "idAduana" => array("StringTrim", "StripTags", "Digits"),
            );
            $v = array(
                "idAduana" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idAduana")) {
                $referencias = new OAQ_Referencias(array("idAduana" => $input->idAduana));
                if ($referencias->consecutivo()) {
                    $this->_helper->json(array("success" => true, "pedimento" => $referencias->getPedimento(), "referencia" => $referencias->getReferencia()));
                } else {
                    $this->_helper->json(array("success" => false));
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerGuiasAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "page" => array("Digits"),
                "rows" => array("Digits"),
            );
            $v = array(
                "page" => array(new Zend_Validate_Int(), "default" => 1),
                "rows" => array(new Zend_Validate_Int(), "default" => 10),
                "filterRules" => "NotEmpty",
                "sort" => "NotEmpty",
                "order" => "NotEmpty",
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("page") && $input->isValid("rows")) {
                $mppr = new Bitacora_Model_BitacoraPedimentos();
                $arr = $mppr->obtenerGuias($input->page, $input->rows, $input->filterRules, $input->sort, $input->order);
                if (!empty($arr)) {
                    $this->_helper->json($arr);
                } else {
                    $this->_helper->json(array(
                        "total" => 0,
                        "rows" => array(),
                    ));
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "page" => array("Digits"),
                "rows" => array("Digits"),
            );
            $v = array(
                "page" => array(new Zend_Validate_Int(), "default" => 1),
                "rows" => array(new Zend_Validate_Int(), "default" => 10),
                "filterRules" => "NotEmpty",
                "sort" => "NotEmpty",
                "order" => "NotEmpty",
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("page") && $input->isValid("rows")) {
                $mppr = new Bitacora_Model_BitacoraPedimentos();
                $arr = $mppr->obtener($input->page, $input->rows, $input->filterRules, $input->sort, $input->order);
                if (!empty($arr)) {
                    $this->_helper->json($arr);
                } else {
                    $this->_helper->json(array(
                        "total" => 0,
                        "rows" => array(),
                    ));
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function clavesAction() {
        try {
            $mppr = new Trafico_Model_CvePedimentos();
            $arr = $mppr->obtener();
            $this->_helper->json($arr);
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function clientesAction() {
        try {
            $mppr = new Trafico_Model_ClientesMapper();
            $arr = $mppr->obtener(true);
            $this->_helper->json($arr);
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function imprimirAction() {
        try {
            $mppr = new Bitacora_Model_BitacoraPedimentos();
            $arr = $mppr->obtener();
            $data = array(
                "prefijoDocumento" => "BITACORADEPEDIMENTOS",
                "versionDocumento" => "00",
                "nombreDocumento" => "Bitácora de pedimentos",
                "codigoDocumento" => "SGC 67",
                "aduana" => "3589-640 QUERÉTARO, QRO.",
                "empresa" => "ORGANIZACIÓN ADUANAL DE QUERÉTARO S.C.",
                "data" => $arr["rows"],
            );
            $print = new OAQ_Imprimir_BitacoraPedimentos($data, "P", "pt", "LETTER");
            $print->bitacoraPedimentos();
            $print->set_filename("BITACORA_DE_PEDIMENTOS_.pdf");
            $print->Output($print->get_filename(), "I");
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function detalleGuiaAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idGuia" => array("Digits"),
            );
            $v = array(
                "idGuia" => array(new Zend_Validate_Int(), "NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idGuia")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");
                $guia = new Bitacora_Model_BitacoraPedimentos();
                $detalle = $guia->obtenerDatos($input->idGuia);
                if (!empty($detalle)) {
                    $view->bultos = $detalle["bultos"];
                    $view->numFacturas = $detalle["numFacturas"];
                    $view->observaciones = $detalle["observaciones"];
                    $view->completa = $detalle["completa"];
                    $view->averia = $detalle["averia"];
                    $view->linea = $detalle["linea"];
                }
                $mppr = new Webservice_Model_TraficoBitacoraFacturas();
                $arr = $mppr->obtenerFacturas($input->idGuia);
                $view->facturas = $arr;
                $this->_helper->json(array("success" => true, "html" => $view->render("detalle-guia.phtml")));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function verFotoAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $v = array(
                "id" => array(new Zend_Validate_Int(), "NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $mppr = new Webservice_Model_TraficoBitacoraFotos();
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");
                $arr = $mppr->obtenerFoto($input->id);
                if (!empty($arr)) {
                    $view->title = $arr["archivoNombre"];
                    $view->image = 'data: ' . mime_content_type($arr["ubicacion"]) . ';base64,' . base64_encode(file_get_contents($arr["ubicacion"]));
                }
                echo $view->render("ver-foto.phtml");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerPagadosAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "page" => array("Digits"),
                "rows" => array("Digits"),
                "tipoAduana" => array("Digits"),
            );
            $v = array(
                "page" => array(new Zend_Validate_Int(), "default" => 1),
                "rows" => array(new Zend_Validate_Int(), "default" => 10),
                "tipoAduana" => array(new Zend_Validate_Int(), "NotEmpty"),
                "filterRules" => "NotEmpty",
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("page") && $input->isValid("rows")) {
                $rows = $this->_todosPagados($input->page, $input->rows, $input->filterRules, $this->_cookies(), $input->tipoAduana);
                if (!empty($rows)) {
                    $arr = array(
                        "total" => $this->_totalPagados($input->filterRules, $this->_cookies()),
                        "rows" => empty($rows) ? array() : $rows,
                    );
                    $this->_helper->json($arr);
                } else {
                    throw new Exception("No data!");
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $e) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    protected function _totalPagados($filterRules = null, $cookies = null) {
        try {
            $db = Zend_Registry::get("oaqintranet");
            $sql = $db->select()
                    ->from(array("t" => "traficos"), array("count(*) AS total"))
                    ->joinInner(array("u" => "usuarios"), "u.id = t.idUsuario", array("nombre"))
                    ->joinInner(array("c" => "trafico_clientes"), "c.id = t.idCliente", array("nombre AS nombreCliente"));
            $this->_filters($sql, $filterRules, $cookies);
            $stmt = $db->fetchRow($sql);
            if ($stmt) {
                return (int) $stmt["total"];
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    protected function _todosPagados($page, $rows, $filterRules = null, $cookies = null, $tipoAduana = null) {
        try {
            $db = Zend_Registry::get("oaqintranet");
            $sql = $db->select()
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
                        new Zend_Db_Expr("DATE_FORMAT(fechaEta,'%Y-%m-%d') AS fechaEta"),
                        new Zend_Db_Expr("DATE_FORMAT(fechaPago,'%Y-%m-%d %T') AS fechaPago"),
                        new Zend_Db_Expr("DATE_FORMAT(fechaLiberacion,'%Y-%m-%d %T') AS fechaLiberacion"),
                        "idPlanta",
                        "idUsuario"
                    ))
                    ->joinInner(array("u" => "usuarios"), "u.id = t.idUsuario", array("nombre"))
                    ->joinInner(array("a" => "trafico_aduanas"), "a.id = t.idAduana", array(""))
                    ->joinInner(array("c" => "trafico_clientes"), "c.id = t.idCliente", array("nombre AS nombreCliente"))
                    ->joinLeft(array("tc" => "trafico_tipocarga"), "tc.id = t.tipoCarga", array("tipoCarga AS carga"))
                    ->joinLeft(array("p" => "trafico_clientes_plantas"), "p.id = t.idPlanta", array("descripcion AS descripcionPlanta"))
                    ->order(array("fechaEta DESC"))
                    ->limit($rows, ($page - 1) * $rows);
            if (isset($tipoAduana)) {
                if ($tipoAduana == 1) {
                    $sql->where("a.tipoAduana = 1 AND t.cvePedimento IN ('V1', 'G1', 'E1', 'V5', 'F4', 'F5', 'A3')");
                }
            }
            $this->_filters($sql, $filterRules, $cookies);
            $stmt = $db->fetchAll($sql);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    protected function _filters(Zend_Db_Select $sql, $filterRules = null, $filtrosCookies = null) {
        $referencias = new OAQ_Referencias();
        $res = $referencias->restricciones($this->_session->id, $this->_session->role);
        if ($this->_session->role == "inhouse") {
            $sql->where("rfcCliente IN (?)", $res["rfcs"]);
        } else if (in_array($this->_session->role, array("super", "trafico_operaciones", "trafico", "trafico_aero", "trafico_ejecutivo", "gerente"))) {
            if (!empty($res["idsAduana"])) {
                $sql->where("idAduana IN (?)", $res["idsAduana"]);
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
            if ($filtrosCookies["noPagadas"] == true) {
                $sql->where("fechaPago IS NULL");
            } else {
                $sql->where("fechaLiberacion IS NOT NULL");                
            }
        }
        if (!empty($this->_res["idsAduana"]) && $this->_session->role !== "inhouse") {
            $sql->where("idAduana IN (?)", $this->_res["idsAduana"]);
        }
    }

    protected function _cookies() {
        $request = new Zend_Controller_Request_Http();
        $filtrosCookies = array(
            "noPagadas" => filter_var($request->getCookie("noPagadas"), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
        );
        return $filtrosCookies;
    }
    
    public function imprimirFormatoSalidaAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => "Digits"
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $mapper = new Trafico_Model_TraficosMapper();
                $arr = $mapper->obtenerPorId($input->id);
                $data = array(
                    "prefijoDocumento" => "FMTSAL_",
                    "nombreDocumento" => "Formato de salida de aeropuerto",
                    "versionDocumento" => "SGC 77",
                    "referencia" => $arr["referencia"],
                    "pedimento" => $arr["pedimento"],
                    "nombreCliente" => $arr["nombreCliente"],
                    "empresa" => "ORGANIZACIÓN ADUANAL DE QUERÉTARO S.C.",
                );
                $misc = new OAQ_Misc();
                if ($arr["patente"] == 3589 && ($arr["aduana"] == 240 || $arr["aduana"] == 640)) {
                    $db = $misc->sitawinTrafico($arr["patente"], $arr["aduana"]);
                    if (isset($db)) {
                        $b = $db->infoPedimentoBasicaReferencia($arr["referencia"]);
                    }
                    if (isset($b) && !empty($b)) {
                        if (isset($b["bultos"])) { $data["bultos"] = $b["bultos"]; }
                        if (isset($b["pesoBruto"])) { $data["pesoBruto"] = $b["pesoBruto"]; }
                        if (isset($b["guias"]) && !empty($b["guias"])) {
                            $data["guias"] = "";
                            foreach ($b["guias"] as $value) {
                                $data["guias"] .= preg_replace("/\s+/", "", $value["guia"]) . ", ";
                            }
                        }
                    }
                }
                $print = new OAQ_Imprimir_FormatoSalida($data, "P", "pt", "LETTER");
                $print->formatoSalida();
                $print->Output($print->get_filename(), "I");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
    public function imprimirFormatoSalidaMultipleAction() {
        try {
            $f = array(
                "ids" => array("StringTrim", "StripTags"),
            );
            $v = array(
                "ids" => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("ids")) {
                
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

}
