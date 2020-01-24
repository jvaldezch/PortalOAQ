<?php

class Bodega_PostController extends Zend_Controller_Action {

    protected $_session;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;
    protected $_db;
    protected $_firephp;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $this->_db = Zend_Registry::get("oaqintranet");
        $this->_firephp = Zend_Registry::get("firephp");
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

    public function obtenerProveedoresAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idCliente" => array("Digits"),
                    "idBodega" => array("Digits"),
                );
                $v = array(
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                    "idBodega" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idCliente") && $input->isValid("idBodega")) {
                    $mppr = new Bodega_Model_Proveedores();
                    $arr = $mppr->obtenerProveedores($input->idCliente, $input->idBodega);
                    $providers = array();
                    if (!empty($arr)) {
                        foreach ($arr as $item) {
                            if (trim($item["nombre"]) !== '') {
                                $providers[] = array("id" => $item["id"], "nombre" => $item["nombre"]);
                            }
                        }
                    }
                    $this->_helper->json(array("success" => true, "results" => $providers));
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerEmbarcadorAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idCliente" => array("Digits"),
                    "idProveedor" => array("Digits"),
                    "tipoOperacion" => array("Digits"),
                );
                $v = array(
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                    "idProveedor" => array("NotEmpty", new Zend_Validate_Int()),
                    "tipoOperacion" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idCliente") && $input->isValid("idProveedor") && $input->isValid("tipoOperacion")) {
                    $provs = new Trafico_Model_FactPro();
                    $arr = $provs->obtener($input->idProveedor);
                    $address = array();
                    if (isset($arr) && !empty($arr)) {
                        $address = array(
                            "numero" => $input->idProveedor,
                            "direccion" => $arr["calle"],
                            "ciudad" => $arr["localidad"] . ',' . $arr["municipio"],
                            "estado" => $arr["estado"],
                            "pais" => $arr["pais"],
                        );
                    }
                    $shippers = array();
                    $mppr = new Bodega_Model_Embarcadores();
                    $this->_helper->json(array("success" => true, "direccion" => $address, "embarcadores" => $shippers));
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerConsignatarioAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idCliente" => array("Digits"),
                    "idProveedor" => array("Digits"),
                    "tipoOperacion" => array("Digits"),
                );
                $v = array(
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                    "idProveedor" => array("NotEmpty", new Zend_Validate_Int()),
                    "tipoOperacion" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idCliente") && $input->isValid("idProveedor") && $input->isValid("tipoOperacion")) {
                    $provs = new Trafico_Model_FactPro();
                    $arr = $provs->obtener($input->idProveedor);
                    $address = array();
                    if (isset($arr) && !empty($arr)) {
                        $address = array(
                            "numero" => $input->idProveedor,
                            "direccion" => $arr["calle"],
                            "ciudad" => $arr["localidad"] . ',' . $arr["municipio"],
                            "estado" => $arr["estado"],
                            "pais" => $arr["pais"],
                        );
                    }
                    $shippers = array();
                    $mppr = new Bodega_Model_Consignatarios();
                    $this->_helper->json(array("success" => true, "direccion" => $address, "consignatarios" => $shippers));
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function nuevaEntradaAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idCliente" => array("Digits"),
                    "idProveedor" => array("Digits"),
                    "tipoOperacion" => array("Digits"),
                );
                $v = array(
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                    "idProveedor" => array("NotEmpty", new Zend_Validate_Int()),
                    "tipoOperacion" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idCliente") && $input->isValid("idProveedor") && $input->isValid("tipoOperacion")) {

                    $this->_helper->json(array("success" => false, "message" => "Función no disponible"));
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function actualizarEntradaAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idTrafico" => array("Digits"),
                    "ie" => array("StringToUpper"),
                    "bultos" => array("Digits"),
                    "blGuia" => array("StringToUpper"),
                    "contenedorCaja" => array("StringToUpper"),
                    "lineaTransporte" => array("StringToUpper"),
                    "proveedores" => array("StringToUpper"),
                    "ubicacion" => array("StringToUpper"),
                    "contenedorCajaEntrada" => array("StringToUpper"),
                    "contenedorCajaSalida" => array("StringToUpper"),
                    "bultos" => array("Digits"),
                );
                $v = array(
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                    "ie" => "NotEmpty",
                    "blGuia" => "NotEmpty",
                    "contenedorCaja" => "NotEmpty",
                    "lineaTransporte" => "NotEmpty",
                    "contenedorCaja" => "NotEmpty",
                    "proveedores" => "NotEmpty",
                    "ubicacion" => "NotEmpty",
                    "contenedorCajaEntrada" => "NotEmpty",
                    "contenedorCajaSalida" => "NotEmpty",
                    "pesoKg" => "NotEmpty",
                    "pesoLbs" => "NotEmpty",
                    "bultos" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idTrafico")) {
                    $trafico = new OAQ_Trafico(array("idTrafico" => $input->idTrafico, "idUsuario" => $this->_session->id, "usuario" => $this->_session->username));
                    $arr = array(
                        "ie" => ($input->isValid("ie")) ? $input->ie : null,
                        "blGuia" => ($input->isValid("blGuia")) ? $input->blGuia : null,
                        "contenedorCaja" => ($input->isValid("contenedorCaja")) ? $input->contenedorCaja : null,
                        "lineaTransporte" => ($input->isValid("lineaTransporte")) ? $input->lineaTransporte : null,
                        "proveedores" => ($input->isValid("proveedores")) ? $input->proveedores : null,
                        "ubicacion" => ($input->isValid("ubicacion")) ? $input->ubicacion : null,
                        "contenedorCajaEntrada" => ($input->isValid("contenedorCajaEntrada")) ? $input->contenedorCajaEntrada : null,
                        "contenedorCajaSalida" => ($input->isValid("contenedorCajaSalida")) ? $input->contenedorCajaSalida : null,
                        "pesoKg" => ($input->isValid("pesoKg")) ? $input->pesoKg : null,
                        "pesoLbs" => ($input->isValid("pesoLbs")) ? $input->pesoLbs : null,
                        "bultos" => ($input->isValid("bultos")) ? $input->bultos : null,
                    );
                    if (($trafico->actualizar($arr))) {
                        $this->_helper->json(array("success" => true));
                    }
                    $this->_helper->json(array("success" => true));
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function entradaActualizarAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                    "idPlanta" => array("Digits"),
                    "blGuia" => array("StringToUpper"),
                    "contenedorCaja" => array("StringToUpper"),
                    "observaciones" => array("StringToUpper"),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "idPlanta" => "NotEmpty",
                    "blGuia" => "NotEmpty",
                    "contenedorCaja" => "NotEmpty",
                    "fechaEnvioDocumentos" => "NotEmpty",
                    "fechaEta" => "NotEmpty",
                    "fechaPago" => "NotEmpty",
                    "fechaLiberacion" => "NotEmpty",
                    "observaciones" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id")) {
                    $entrada = new OAQ_Bodega(array("idTrafico" => $input->id, "idUsuario" => $this->_session->id, "usuario" => $this->_session->username));
                    $arr = array(
                        "blGuia" => ($input->isValid("blGuia")) ? $input->blGuia : null,
                        "contenedorCaja" => ($input->isValid("contenedorCaja")) ? $input->contenedorCaja : null,
                        "fechaEnvioDocumentos" => ($input->isValid("fechaEnvioDocumentos")) ? $input->fechaEnvioDocumentos : null,
                        "fechaEta" => ($input->isValid("fechaEta")) ? $input->fechaEta : null,
                        "fechaPago" => ($input->isValid("fechaPago")) ? $input->fechaPago : null,
                        "fechaLiberacion" => ($input->isValid("fechaLiberacion")) ? $input->fechaLiberacion : null,
                        "observaciones" => ($input->isValid("observaciones")) ? $input->observaciones : null,
                    );
                    if ($input->isValid("fechaPago")) {
                        $arr['estatus'] = 2;
                    }
                    if ($input->isValid("fechaLiberacion")) {
                        $arr['estatus'] = 3;
                    }
                    if (($entrada->actualizar($arr))) {
                        $this->_helper->json(array("success" => true));
                    }
                    $this->_helper->json(array("success" => true));
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function guardarFechasEntradaAction() {
        try {
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idTrafico" => "Digits",
                );
                $v = array(
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                    "fechaRevision" => array("NotEmpty"), // 56
                    "fechaDescarga" => array("NotEmpty"), // 57
                    "fechaCarga" => array("NotEmpty"), // 58
                    "fechaSalida" => array("NotEmpty"), // 59
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("idTrafico")) {
                    $trafico = new OAQ_Trafico(array("idTrafico" => $i->idTrafico, "idUsuario" => $this->_session->id, "usuario" => $this->_session->username));
                    if ($i->isValid("fechaRevision")) {
                        $trafico->actualizarFecha(56, $i->fechaRevision, $this->_session->username);
                    }
                    if ($i->isValid("fechaDescarga")) {
                        $trafico->actualizarFecha(57, $i->fechaDescarga, $this->_session->username);
                    }
                    if ($i->isValid("fechaCarga")) {
                        $trafico->actualizarFecha(58, $i->fechaCarga, $this->_session->username);
                    }
                    if ($i->isValid("fechaSalida")) {
                        $trafico->actualizarFecha(59, $i->fechaSalida, $this->_session->username);
                    }
                    $this->_helper->json(array("success" => true));
                } else {
                    $this->_helper->json(array("success" => false));
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function nuevoTraficoAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idBodega" => array("Digits"),
                    "idCliente" => array("Digits"),
                    "idPlanta" => array("Digits"),
                    "idProveedor" => array("Digits"),
                    "proveedor" => array("StringToUpper"),
                    "proveedores" => array("StringToUpper"),
                    "contenedorCaja" => array("StringToUpper"),
                    "contenedorCajaEntrada" => array("StringToUpper"),
                    "lineaTransporte" => array("StringToUpper"),
                    "rfcSociedad" => array("StringToUpper"),
                    "blGuia" => array("StringToUpper"),
                    "referencia" => array("StringToUpper"),
                    "bultos" => array("Digits"),
                );
                $v = array(
                    "idBodega" => array("NotEmpty", new Zend_Validate_Int()),
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                    "idPlanta" => array("NotEmpty", new Zend_Validate_Int()),
                    "idProveedor" => array("NotEmpty", new Zend_Validate_Int()),
                    "proveedor" => "NotEmpty",
                    "proveedores" => "NotEmpty",
                    "rfcSociedad" => "NotEmpty",
                    "contenedorCaja" => "NotEmpty",
                    "contenedorCajaEntrada" => "NotEmpty",
                    "lineaTransporte" => "NotEmpty",
                    "blGuia" => "NotEmpty",
                    "planta" => "NotEmpty",
                    "bultos" => array("NotEmpty", new Zend_Validate_Int()),
                    "referencia" => "NotEmpty",
                    "fechaEta" => array("NotEmpty", new Zend_Validate_Regex("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/")), // 10
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());

                if ($input->isValid("idBodega") && $input->isValid("idCliente") && $input->isValid("referencia")) {

                    $prov = new Bodega_Model_Proveedores();
                    $nomProv = $prov->obtener($input->idProveedor);

                    $trafico = new OAQ_Trafico(array("usuario" => $this->_session->username, "idUsuario" => $this->_session->id));

                    $array = array(
                        "idBodega" => $input->idBodega,
                        "idUsuario" => $this->_session->id,
                        "idCliente" => $input->idCliente,
                        "idProveedor" => $input->idProveedor,
                        "referencia" => $input->referencia,
                        "rfcSociedad" => $input->rfcSociedad,
                        "idPlanta" => isset($input->idPlanta) ? $input->idPlanta : null,
                        "blGuia" => preg_replace('/\s+/', '', $input->blGuia),
                        "contenedorCaja" => $input->contenedorCajaEntrada,
                        "contenedorCajaEntrada" => $input->contenedorCajaEntrada,
                        "lineaTransporte" => $input->lineaTransporte,
                        "proveedores" => $nomProv['nombre'],
                        "bultos" => $input->bultos,
                        "fechaEta" => date("Y-m-d H:i:s", strtotime($input->fechaEta)),
                        "creado" => date("Y-m-d H:i:s"),
                    );

                    if (($res = $trafico->nuevaEntradaBodega($array))) {
                        if ($input->isValid("blGuia")) {
                            $trafico->agregarGuia($res["id"], $this->_session->id, $input->blGuia);
                        }
                        $this->_helper->json(array("success" => true, "id" => (int) $res["id"]));
                    }
                    $this->_helper->json(array("success" => false));
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function entradasAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "page" => array("Digits"),
                    "rows" => array("Digits"),
                );
                $v = array(
                    "page" => array(new Zend_Validate_Int(), "default" => 1),
                    "rows" => array(new Zend_Validate_Int(), "default" => 20),
                    "filterRules" => "NotEmpty",
                    "bodega" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("page") && $input->isValid("rows")) {
                    $mppr = new Bodega_Model_BodegasUsuarios();
                    $bs = $mppr->obtenerBodegas($this->_session->id);
                    $b = array();
                    if (!empty($bs)) {
                        foreach ($bs as $item) {
                            $b[] = $item['idBodega'];
                        }
                    }
                    $rows = $this->_todos($input->page, $input->rows, $b, $input->filterRules, $this->_cookies());
                    $total = $this->_total($b, $input->filterRules, $this->_cookies());
                    $arr = array(
                        "total" => $total,
                        "rows" => empty($rows) ? array() : $rows,
                    );
                    $this->_helper->json($arr);
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    protected function _todos($page, $rows, $warehouses, $filterRules = null, $cookies = null) {
        try {
            $sql = $this->_db->select()
                    ->from(array("t" => "traficos"), array(
                        "id",
                        "idTrafico",
                        "estatus",
                        "referencia",
                        "cvePedimento",
                        "regimen",
                        "rfcCliente",
                        "blGuia",
                        "contenedorCaja",
                        "ordenCarga",
                        "ordenCompra",
                        "carrierNaviera",
                        "proveedores",
                        "facturas",
                        "observaciones",
                        "bultos",
                        new Zend_Db_Expr("DATE_FORMAT(fechaEta,'%Y-%m-%d') AS fechaEta"),
                        new Zend_Db_Expr("DATE_FORMAT(fechaPago,'%Y-%m-%d') AS fechaPago"),
                        new Zend_Db_Expr("DATE_FORMAT(fechaLiberacion,'%Y-%m-%d') AS fechaLiberacion"),
                        new Zend_Db_Expr("DATE_FORMAT(fechaEnvioDocumentos,'%Y-%m-%d') AS fechaEnvioDocumentos"),
                        new Zend_Db_Expr("IF (fechaLiberacion IS NOT NULL, DATEDIFF(fechaLiberacion, fechaEta), 0) AS diasDespacho"),
                        "idPlanta",
                        "idUsuario"
                    ))
                    ->joinInner(array("u" => "usuarios"), "u.id = t.idUsuario", array("nombre"))
                    ->joinInner(array("c" => "trafico_clientes"), "c.id = t.idCliente", array("nombre AS nombreCliente"))
                    ->joinLeft(array("tc" => "trafico_tipocarga"), "tc.id = t.tipoCarga", array("tipoCarga AS carga"))
                    ->joinLeft(array("p" => "trafico_clientes_plantas"), "p.id = t.idPlanta", array("descripcion AS descripcionPlanta"))
                    ->joinLeft(array("l" => "trafico_almacen"), "l.id = t.almacen", array("nombre AS nombreAlmacen"))
                    ->joinLeft(array("b" => "trafico_bodegas"), "b.id = t.idBodega", array("siglas"))
                    ->where("t.idBodega IN (?)", $warehouses)
                    ->order(array("fechaEta DESC"))
                    ->limit($rows, ($page - 1) * $rows);
            $this->_filters($sql, $filterRules, $cookies);
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    protected function _total($warehouses, $filterRules = null, $cookies = null) {
        try {
            $sql = $this->_db->select()
                    ->from(array("t" => "traficos"), array("count(*) AS total"))
                ->joinInner(array("c" => "trafico_clientes"), "c.id = t.idCliente", array("nombre AS nombreCliente"))
                    ->joinLeft(array("u" => "usuarios"), "u.id = t.idUsuario", array(""))
                    ->where("t.idBodega IN (?)", $warehouses);
            $this->_filters($sql, $filterRules, $cookies);
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return (int) $stmt['total'];
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    protected function _filters(Zend_Db_Select $sql, $filterRules = null, $filtrosCookies = null) {
        if (isset($filterRules)) {
            $filter = json_decode(html_entity_decode($filterRules));
            foreach ($filter AS $item) {
                if ($item->field == "referencia" && $item->value != "") {
                    $sql->where("t.referencia LIKE ?", "%" . trim($item->value) . "%");
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
                if ($item->field == "ordenCarga" && $item->value != "") {
                    $sql->where("t.ordenCarga LIKE ?", "%" . trim($item->value) . "%");
                }
                if ($item->field == "nombre" && trim($item->value) != "") {
                    $mppr = new Usuarios_Model_UsuariosMapper();
                    $arr = $mppr->buscarUsuario(trim($item->value));
                    if (!empty($arr)) {
                        $sql->where("t.idUsuario IN (?)", $arr);
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
        }

        if (isset($filtrosCookies)) {
            if ($filtrosCookies["intraffic"] == true) {
                $sql->where("t.pedimento IS NOT NULL");
            } else {
                $sql->where("t.pedimento IS NULL");
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
                $sql->where("t.idUsuario = ?", $this->_session->id);
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
    }

    protected function _cookies() {
        $request = new Zend_Controller_Request_Http();
        $filtrosCookies = array(
            "allOperations" => filter_var($request->getCookie("allOperations"), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            "pagadas" => filter_var($request->getCookie("pagadas"), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            "liberadas" => filter_var($request->getCookie("liberadas"), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            "fdates" => filter_var($request->getCookie("fdates"), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            "ninvoices" => filter_var($request->getCookie("ninvoices"), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            "intraffic" => filter_var($request->getCookie("intraffic"), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            "dateini" => filter_var($request->getCookie("dateini"), FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => "/^\d{4}\-\d{2}\-\d{2}$/"))),
            "dateend" => filter_var($request->getCookie("dateend"), FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => "/^\d{4}\-\d{2}\-\d{2}$/"))),
        );
        return $filtrosCookies;
    }

    public function asignarOrdenCargaAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                );
                $v = array(
                    "ids" => "NotEmpty",
                    "ordenCarga" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("ids") && $input->isValid("ordenCarga")) {

                    $ids = explode(",", $input->ids);

                    $traficos = new OAQ_Trafico();
                    if ($traficos->asignarOrdenCarga($ids, $input->ordenCarga)) {
                        $this->_helper->json(array("success" => true));
                    }
                    $this->_helper->json(array("success" => false));
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function consolidarTraficosAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idMaster" => "Digits"
                );
                $v = array(
                    "idMaster" => "NotEmpty",
                    "ids" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("idMaster") && $input->isValid("ids")) {
                    $traficos = new OAQ_Trafico();
                    $arr = explode(",", $input->ids);
                    foreach ($arr as $item) {
                        if ($item != $input->idMaster) {
                            $traficos->consolidarTraficos($input->idMaster, $item);
                        }
                        $this->_helper->json(array("success" => true));
                    }
                    $this->_helper->json(array("success" => false, "message" => "Not ready yet!"));
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function removerEntradaAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => "Digits"
                );
                $v = array(
                    "id" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("id")) {

                    $this->_helper->json(array("success" => false));
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function subirArchivosAction() {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idTrafico" => array("Digits"),
                    "idBodega" => array("Digits"),
                    "referencia" => array("StringToUpper"),
                    "rfcCliente" => array("StringToUpper"),
                );
                $v = array(
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                    "idBodega" => array("NotEmpty", new Zend_Validate_Int()),
                    "referencia" => array(new Zend_Validate_Regex("/^[-_a-zA-Z0-9.]+$/"), "presence" => "required"),
                    "rfcCliente" => array(new Zend_Validate_Regex("/^[-_a-zA-Z0-9.]+$/"), "presence" => "required"),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if (!$input->isValid("idTrafico") || !$input->isValid("idBodega") || !$input->isValid("referencia")) {
                    throw new Exception("Invalid input!" . Zend_Debug::dump($input->getErrors(), true) . Zend_Debug::dump($input->getMessages(), true));
                }
            }

            $misc = new OAQ_Misc();
            $misc->set_baseDir($this->_appconfig->getParam("expdest"));

            if (APPLICATION_ENV == "development") {
                $misc->set_baseDir("D:\\xampp\\tmp\\expedientes");
            }

            $traffic = new OAQ_Bodega(array("idTrafico" => $input->idTrafico));
            $t = $traffic->obtenerDatos();

            $mpr = new Bodega_Model_Bodegas();
            $b = $mpr->obtener($input->idBodega);

            $model = new Archivo_Model_RepositorioMapper();
            $upload = new Zend_File_Transfer_Adapter_Http();
            $upload->addValidator("Count", false, array("min" => 1, "max" => 15))
                    ->addValidator("Size", false, array("min" => "1", "max" => "20MB"))
                    ->addValidator("Extension", false, array("extension" => "pdf,xml,xls,xlsx,doc,docx,zip,bmp,tif,jpg,msg", "case" => false));

            if (($path = $misc->directorioExpedienteDigitalBodega($b['siglas'], $t["fechaEta"], $input->referencia))) {
                $upload->setDestination($path);
            } else {
                throw new Exception("Could not set base directory.");
            }

            $files = $upload->getFileInfo();
            foreach ($files as $fieldname => $fileinfo) {
                if (($upload->isUploaded($fieldname)) && ($upload->isValid($fieldname))) {
                    $tipoArchivo = $misc->tipoArchivo(basename($fileinfo["name"]));
                    $ext = pathinfo($fileinfo["name"], PATHINFO_EXTENSION);
                    if (preg_match('/msg/i', $ext)) {
                        $tipoArchivo = 2001;
                    }
                    $filename = $misc->formatFilename($fileinfo["name"], false);
                    $verificar = $model->verificarArchivo(null, $input->referencia, $filename);
                    if ($verificar == false) {
                        $upload->receive($fieldname);
                        if (($misc->renombrarArchivo($path, $fileinfo["name"], $filename))) {
                            $model->nuevoArchivo($tipoArchivo, null, null, null, null, $input->referencia, $filename, $path . DIRECTORY_SEPARATOR . $filename, $this->_session->username, $input->rfcCliente);
                        }
                    } else {
                        $errors[] = array(
                            "filename" => $fileinfo["name"],
                            "errors" => array("errors" => "El archivo ya existe."),
                        );
                    }

                    $this->_firephp->info($path . DIRECTORY_SEPARATOR . $filename);
                } else {
                    $error = $upload->getErrors();
                    $errors[] = array(
                        "filename" => $fileinfo["name"],
                        "errors" => $error,
                    );
                }
            }
            if (isset($errors)) {
                $this->_helper->json(array("success" => false, "errors" => $errors));
            } else {
                $this->_helper->json(array("success" => true));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function subirImagenesAction() {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idTrafico" => array("Digits"),
                    "idBodega" => array("Digits"),
                    "referencia" => array("StringToUpper"),
                    "rfcCliente" => array("StringToUpper"),
                );
                $v = array(
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                    "idBodega" => array("NotEmpty", new Zend_Validate_Int()),
                    "referencia" => array(new Zend_Validate_Regex("/^[-_a-zA-Z0-9.]+$/"), "presence" => "required"),
                    "rfcCliente" => array(new Zend_Validate_Regex("/^[-_a-zA-Z0-9.]+$/"), "presence" => "required"),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if (!$input->isValid("idTrafico") || !$input->isValid("idBodega") || !$input->isValid("referencia")) {
                    throw new Exception("Invalid input!" . Zend_Debug::dump($input->getErrors(), true) . Zend_Debug::dump($input->getMessages(), true));
                }
            }

            $misc = new OAQ_Misc();
            if (APPLICATION_ENV == "production") {
                $misc->set_baseDir($this->_appconfig->getParam("expdest"));
            } else {
                $misc->set_baseDir("D:\\xampp\\tmp\\expedientes");
            }

            $traffic = new OAQ_Bodega(array("idTrafico" => $input->idTrafico));
            $t = $traffic->obtenerDatos();

            $mpr = new Bodega_Model_Bodegas();
            $b = $mpr->obtener($input->idBodega);

            $mdl = new Trafico_Model_Imagenes();

            $upload = new Zend_File_Transfer_Adapter_Http();
            $upload->addValidator("Count", false, array("min" => 1, "max" => 15))
                    ->addValidator("Size", false, array("min" => "1kB", "max" => "20MB"))
                    ->addValidator("Extension", false, array("extension" => "jpg,png", "case" => false));

            if (($path = $misc->directorioExpedienteDigitalBodega($b['siglas'], $t["fechaEta"], $input->referencia))) {
                $upload->setDestination($path);
            }

            $files = $upload->getFileInfo();
            foreach ($files as $fieldname => $fileinfo) {
                if (($upload->isUploaded($fieldname)) && ($upload->isValid($fieldname))) {

                    $ext = strtolower(pathinfo($fileinfo["name"], PATHINFO_EXTENSION));
                    $filename = sha1(time() . $fileinfo["name"]) . "." . $ext;
                    $upload->addFilter("Rename", $filename, $fieldname);
                    $upload->receive($fieldname);

                    $thumb = $path . DIRECTORY_SEPARATOR . pathinfo($filename, PATHINFO_FILENAME) . "_thumb." . pathinfo($filename, PATHINFO_EXTENSION);
                    if (file_exists($path . DIRECTORY_SEPARATOR . $filename)) {
                        if (extension_loaded("imagick")) {
                            $im = new Imagick();
                            $im->pingImage($path . DIRECTORY_SEPARATOR . $filename);
                            $im->readImage($path . DIRECTORY_SEPARATOR . $filename);

                            if ($im->getimagewidth() > 1024) {
                                $im->resizeimage(1024, round($im->getimageheight() / ($im->getimagewidth() / 1024), 0), Imagick::FILTER_LANCZOS, 1);
                                $im->writeImage($path . DIRECTORY_SEPARATOR . $filename);
                            }
                            $im->thumbnailImage(150, round($im->getimageheight() / ($im->getimagewidth() / 150), 0));
                            $im->writeImage($thumb);
                            $im->destroy();
                            if (isset($thumb) && file_exists($thumb)) {
                                $mdl->agregar($input->idTrafico, 1, $path, pathinfo($filename, PATHINFO_BASENAME), pathinfo($thumb, PATHINFO_BASENAME), $fileinfo["name"]);
                            }
                        }
                        if (!isset($thumb) || !file_exists($thumb)) {
                            $mdl->agregar($input->idTrafico, 1, $path, pathinfo($filename, PATHINFO_BASENAME), null, $fileinfo["name"]);
                        }
                    }
                } else {
                    $error = $upload->getErrors();
                    $errors[] = array(
                        "filename" => $fileinfo["name"],
                        "errors" => $error,
                    );
                }
            }
            if (isset($errors)) {
                $this->_helper->json(array("success" => false, "errors" => $errors));
            } else {
                $this->_helper->json(array("success" => true));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function borrarFacturaAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "id" => array("StringTrim", "StripTags", "Digits"),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id")) {

                    $mppr = new Trafico_Model_TraficoFacturasMapper();
                    $deta = new Trafico_Model_FactDetalle();

                    $info = $mppr->detalleFactura($input->id);

                    $facturas = new OAQ_Archivos_Facturas(array("idTrafico" => $info["idTrafico"], "idFactura" => $info["idFactura"]));
                    $facturas->log($info["idTrafico"], $info["idFactura"], "Borro factura " . $info["numeroFactura"], $this->_session->username);

                    $deta->borrarIdFactura($input->id);
                    $prod = new Trafico_Model_FactProd();
                    $prod->borrarIdFactura($input->id);
                    $vlog = new Trafico_Model_VucemMapper();
                    $vlog->borrarIdFactura($input->id);
                    $stmt = $mppr->delete($input->id);
                    if ($stmt == true) {
                        $this->_helper->json(array("success" => true));
                    }
                } else {
                    $this->_helper->json(array("success" => false, "message" => "Invalid data."));
                }
            } else {
                $this->_helper->json(array("success" => false, "message" => "No data recieved."));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function borrarEntradaAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "id" => array("StringTrim", "StripTags", "Digits"),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id")) {
                    $bodega = new OAQ_Bodega(array("idTrafico" => $input->id));
                    if (($bodega->borrar())) {
                        $this->_helper->json(array("success" => true));
                    }
                } else {
                    $this->_helper->json(array("success" => false, "message" => "Invalid data."));
                }
            } else {
                $this->_helper->json(array("success" => false, "message" => "No data recieved."));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function enviarNotificacionAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "id" => array("StringTrim", "StripTags", "Digits"),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id")) {

                    $bodega = new OAQ_Bodega(array("idTrafico" => $input->id));
                    if (($bodega->enviarNotificacion())) {
                        $this->_helper->json(array("success" => true));
                    }
                } else {
                    $this->_helper->json(array("success" => false, "message" => "Invalid data."));
                }
            } else {
                $this->_helper->json(array("success" => false, "message" => "No data recieved."));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function borrarArchivoAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("id")) {
                    $mapper = new Archivo_Model_RepositorioMapper();
                    $arr = $mapper->getFileInfo($i->id);
                    $mapper->removeFileById($i->id);
                    if (isset($arr) && file_exists($arr["ubicacion"])) {
                        unlink($arr["ubicacion"]);
                    } else {
                        $this->_helper->json(array("success" => false, "message" => "El archivo no existe."));
                    }
                    $this->_helper->json(array("success" => true));
                } else {
                    $this->_helper->json(array("success" => false, "message" => "Invalid data!"));
                }
            } else {
                $this->_helper->json(array("success" => false, "message" => "Invalid request type!"));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function enviarEdocumentAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idTrafico" => array("Digits"),
                    "idArchivo" => array("Digits"),
                    "tipo_documento" => array("Digits"),
                );
                $v = array(
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                    "idArchivo" => array("NotEmpty", new Zend_Validate_Int()),
                    "tipo_documento" => array("NotEmpty", new Zend_Validate_Int()),
                    "convert" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idTrafico") && $input->isValid("idArchivo") && $input->isValid("tipo_documento")) {

                    $tbl = new Trafico_Model_VucemMapper();
                    $repo = new Archivo_Model_RepositorioMapper();
                    $file = $repo->informacionVucem($input->idArchivo);

                    if ($input->isValid("convert")) {
                        $proc = new OAQ_Archivos_Procesar();

                        $arr = $proc->convertirArchivoEdocument($input->idArchivo);

                        if ($arr && isset($arr["filename"])) {
                            $edoc = array(
                                "idTrafico" => $input->idTrafico,
                                "idArchivo" => $input->idArchivo,
                                "instruccion" => "Digitalizar documento.",
                                "nombreArchivo" => $file["nom_archivo"],
                                "ubicacion" => $arr["filename"],
                                "tipoDocumento" => $input->tipo_documento,
                                "descripcionDocumento" => '',
                                "creado" => date("Y-m-d H:i:s"),
                            );
                            $tbl->agregar($edoc);
                            $this->_helper->json(array("success" => true));
                        }
                    } else {
                        $edoc = array(
                            "idTrafico" => $input->idTrafico,
                            "idArchivo" => $input->idArchivo,
                            "instruccion" => "Digitalizar documento.",
                            "nombreArchivo" => $file["nom_archivo"],
                            "tipoDocumento" => $input->tipo_documento,
                            "descripcionDocumento" => '',
                            "creado" => date("Y-m-d H:i:s"),
                        );
                        $tbl->agregar($edoc);
                        $this->_helper->json(array("success" => true));
                    }
                    $this->_helper->json(array("success" => true));
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function borrarVucemAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "id" => array("StringTrim", "StripTags", "Digits"),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id")) {
                    $mppr = new Trafico_Model_VucemMapper();
                    $log = new Trafico_Model_TraficoVucemLog();
                    $log->borrarIdVucem($input->id);
                    $mppr->borrar($input->id);
                    $this->_helper->json(array("success" => true));
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function establecerSelloVucemAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idTrafico" => "Digits",
                    "idSello" => "Digits",
                    "tipo" => new Zend_Filter_StringToLower(),
                );
                $v = array(
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                    "idSello" => array("NotEmpty", new Zend_Validate_Int()),
                    "tipo" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idTrafico") && $input->isValid("idSello") && $input->isValid("tipo")) {
                    $mppr = new Trafico_Model_VucemMapper();
                    if ($input->tipo == "agente") {
                        $mppr->establecerSelloAgente($input->idTrafico, $input->idSello);
                    } else {
                        $mppr->establecerSelloCliente($input->idTrafico, $input->idSello);
                    }
                    $this->_helper->json(array("success" => true));
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function agregarFacturaAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idTrafico" => "Digits",
                );
                $v = array(
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                    "numFactura" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idTrafico") && $input->isValid("numFactura")) {
                    $mppr = new Trafico_Model_TraficoFacturasMapper();
                    $idFactura = $mppr->agregarFacturaSimple($input->idTrafico, $input->numFactura, $this->_session->id);
                    if ($idFactura == true) {
                        $det = new Trafico_Model_FactDetalle();
                        $det->agregarFacturaSimple($idFactura, $input->numFactura);
                        $this->_helper->json(array("success" => true));
                    }
                } else {
                    $this->_helper->json(array("success" => false, "message" => "Invalid data."));
                }
            } else {
                $this->_helper->json(array("success" => false, "message" => "No data recieved."));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function seleccionarFacturasAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idTrafico" => "Digits",
                );
                $v = array(
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                    "facturas" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("idTrafico") && $input->isValid("facturas")) {
                    $misc = new OAQ_Misc();
                    $mapper = new Trafico_Model_TraficosMapper();
                    $model = new Trafico_Model_TraficoFacturasMapper();
                    $arr = $mapper->obtenerPorId($input->idTrafico);
                    if (isset($arr["patente"]) && isset($arr["aduana"])) {
                        $db = $misc->sitawinTrafico($arr["patente"], $arr["aduana"]);
                    }
                    foreach ($input->facturas as $numFactura) {
                        if (!($model->verificar($input->idTrafico, $numFactura))) {
                            if (isset($db)) {
                                $factura = $db->infoBasicaFactura($arr["referencia"], $numFactura);
                                if (count($factura) > 0) {
                                    $model->agregarFactura($input->idTrafico, $factura, $this->_session->id);
                                }
                            }
                        }
                    }
                    $this->_helper->json(array("success" => true));
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function subirPlantillaAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idTrafico" => "Digits",
                );
                $v = array(
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                    "replace" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                $upload = new Zend_File_Transfer_Adapter_Http();
                $upload->addValidator("Count", false, array("min" => 1, "max" => 1))
                        ->addValidator("Size", false, array("min" => "1", "max" => "20MB"))
                        ->addValidator("Extension", false, array("extension" => "xls,xlsx", "case" => false));
                if (APPLICATION_ENV == "production") {
                    $directory = $this->_appconfig->getParam("tmpDir");
                } else {
                    $directory = "C:\\wamp64\\tmp";
                }
                $upload->setDestination($directory);
                $files = $upload->getFileInfo();
                foreach ($files as $fieldname => $fileinfo) {
                    if (($upload->isUploaded($fieldname)) && ($upload->isValid($fieldname))) {
                        $ext = strtolower(pathinfo($fileinfo['name'], PATHINFO_EXTENSION));
                        $sha = sha1_file($fileinfo['tmp_name']);
                        $filename = $sha . '.' . $ext;
                        $upload->addFilter('Rename', $filename, $fieldname);
                        $upload->receive($fieldname);
                    }
                    if (file_exists($directory . DIRECTORY_SEPARATOR . $filename)) {
                        $plantilla = new OAQ_Archivos_PlantillaFacturas($directory . DIRECTORY_SEPARATOR . $filename);
                        $plantilla->set_idTrafico($input->idTrafico);
                        $plantilla->set_idUsuario($this->_session->id);
                        $plantilla->set_usuario($this->_session->username);
                        if ($input->replace) {
                            // set replace to false when no overwrite required
                            $plantilla->set_replace(false);
                        }
                        if ($plantilla->analizar() == true) {
                            $this->_helper->json(array("success" => true));
                        }
                        $this->_helper->json(array("success" => true));
                    }
                    $this->_helper->json(array("success" => false));
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function agregarComentarioTraficoAction() {
        try {
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idTrafico" => "Digits",
                    "comment" => "StringToUpper",
                );
                $v = array(
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                    "comment" => "NotEmpty",
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("idTrafico") && $i->isValid("comment")) {
                    $m = new Trafico_Model_ComentariosMapper();
                    if (($idc = $m->agregar($i->idTrafico, $this->_session->id, $i->comment))) {
                        $cs = new Trafico_Model_ClientesMapper();
                        $ts = new Trafico_Model_TraficosMapper();
                        $t = new Trafico_Model_Table_Traficos();
                        $t->setId($i->idTrafico);
                        $ts->find($t);
                        $c = $cs->datosCliente($t->getIdCliente());
                        $trafico = new OAQ_Trafico(array("idTrafico" => $i->idTrafico, "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));
                        $trafico->subirArchivoTemporal(null, $idc);
                        if (null !== ($t->getId())) {
                            if (APPLICATION_ENV == "production") {
                                $emails = new OAQ_EmailNotifications();
                                $mensaje = "El usuario " . $this->_session->nombre . " (" . $this->_session->email . ") ha agregado un comentario al trafico (" . $i->idTrafico . ") referencia " . $t->getReferencia() . " operación " . $t->getAduana() . "-" . $t->getPatente() . "-" . $t->getPedimento() . " del cliente " . $c["nombre"] . "<br><p><em>&ldquo;{$i->comment}&rdquo;</em></p>";
                                $emails->nuevaNotificacion($t->getIdAduana(), $t->getPedimento(), $t->getReferencia(), $this->_session->id, $t->getIdUsuario(), $mensaje, "notificacion-comentario");
                            }
                            $this->_helper->json(array("success" => true, "id" => $i->idTrafico));
                        }
                        $this->_helper->json(array("success" => false, "message" => "No record found on DB"));
                    }
                    $this->_helper->json(array("success" => false, "message" => "Can't add comment to DB"));
                }
                $this->_helper->json(array("success" => false, "message" => "Invalid input!"));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function vucemEnviarFacturasAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idTrafico" => "Digits",
                );
                $v = array(
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                    "facturas" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("idTrafico") && $input->isValid("facturas")) {
                    $mapper = new Trafico_Model_TraficoVucem();
                    $invoices = new Trafico_Model_TraficoFacturasMapper();
                    if (is_array($input->facturas)) {
                        foreach ($input->facturas as $item) {
                            $invoice = $invoices->detalleFactura($item);
                            $factura = new Trafico_Model_Table_TraficoVucem(array("idTrafico" => $input->idTrafico, "idFactura" => $item, "numFactura" => $invoice["numeroFactura"]));
                            $mapper->find($factura);
                            if (null === ($factura->getId())) {
                                $factura->setCreado(date("Y-m-d H:i:s"));
                                $mapper->save($factura);
                            }
                        }
                    }
                    $this->_helper->json(array("success" => true));
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerPlantasAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idCliente" => array("Digits"),
                );
                $v = array(
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idCliente")) {
                    $mppr = new Trafico_Model_ClientesPlantas();
                    $arr = $mppr->obtener($input->idCliente);
                    if (!empty($arr)) {
                        $html = new V2_Html();
                        $html->select("traffic-select-medium", "idPlanta");
                        $html->addSelectOption("", "---");
                        if (count($arr)) {
                            foreach ($arr as $item) {
                                $html->addSelectOption($item["id"], $item["descripcion"]);
                            }
                        }
                    }
                    $mapper = new Bodega_Model_FactPro();
                    $rows = $mapper->obtenerProveedores($input->idCliente);
                    if (!empty($rows)) {
                        $html2 = new V2_Html();
                        $html2->select("traffic-select-large", "idProveedor");
                        $html2->addSelectOption("", "---");
                        foreach ($rows as $item) {
                            if (trim($item["nombre"])) {
                                $html2->addSelectOption($item["id"], $item["nombre"]);
                            }
                        }
                    }
                    $this->_helper->json(array("success" => true, "proveedores" => isset($html2) ? $html2->getHtml() : null, "plantas" => isset($html) ? $html->getHtml() : null));
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerTransportesAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idBodega" => array("Digits"),
                );
                $v = array(
                    "idBodega" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idBodega")) {
                    $mppr = new Bodega_Model_Transporte();
                    $arr = $mppr->obtenerPorBodega($input->idBodega);
                    if (!empty($arr)) {
                        $html = new V2_Html();
                        $html->select("traffic-select-large", "idLineaTransporte");
                        $html->addSelectOption("", "---");
                        if (count($arr)) {
                            foreach ($arr as $item) {
                                $html->addSelectOption($item["id"], $item["nombre"]);
                            }
                        }
                    }
                    $this->_helper->json(array("success" => true, "transportes" => isset($html) ? $html->getHtml() : null));
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function guardarProveedorAction() {
        try {
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idBodega" => array("StringTrim", "StripTags", "Digits"),
                    "idCliente" => array("StringTrim", "StripTags", "Digits"),
                );
                $v = array(
                    "idBodega" => array("NotEmpty", new Zend_Validate_Int()),
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                    "nombre" => "NotEmpty",
                    "tipoIdentificador" => "NotEmpty",
                    "identificador" => "NotEmpty",
                    "calle" => "NotEmpty",
                    "numExt" => "NotEmpty",
                    "numInt" => "NotEmpty",
                    "colonia" => "NotEmpty",
                    "localidad" => "NotEmpty",
                    "municipio" => "NotEmpty",
                    "estado" => "NotEmpty",
                    "codigoPostal" => "NotEmpty",
                    "pais" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("idBodega") && $input->isValid("idCliente")) {
                    $mppr = new Bodega_Model_Proveedores();
                    $arr = array(
                        "idBodega" => $input->idBodega,
                        "idCliente" => $input->idCliente,
                        "nombre" => $input->nombre,
                        "tipoIdentificador" => $input->tipoIdentificador,
                        "identificador" => $input->identificador,
                        "calle" => $input->calle,
                        "numExt" => $input->numExt,
                        "numInt" => $input->numInt,
                        "colonia" => $input->colonia,
                        "localidad" => $input->localidad,
                        "municipio" => $input->municipio,
                        "estado" => $input->estado,
                        "codigoPostal" => $input->codigoPostal,
                        "pais" => $input->pais,
                    );
                    if (!($mppr->buscar($input->idCliente, $input->idBodega, $input->identificador, $input->nombre))) {
                        if (($mppr->agregar($arr))) {
                            $this->_helper->json(array("success" => true));
                        } else {
                            throw new Exception("No pudo agregarse en la base de datos.");
                        }
                    } else {
                        throw new Exception("Proveedor existe.");
                    }
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function guardarBultoAction() {
        try {
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idBulto" => "Digits",
                    "tipoBulto" => "Digits",
                    "mercancia" => "StringToUpper",
                    "observacion" => "StringToUpper",
                );
                $v = array(
                    "idBulto" => array("NotEmpty", new Zend_Validate_Int()),
                    "tipoBulto" => "NotEmpty",
                    "bulto_descarga" => "NotEmpty",
                    "bulto_carga" => "NotEmpty",
                    "bulto_revision" => "NotEmpty",
                    "mercancia" => "NotEmpty",
                    "observacion" => "NotEmpty",
                    "damage" => "NotEmpty",
                    "escaneado" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("idBulto")) {

                    $model = new Bodega_Model_Bultos();

                    $arr = array(
                        "tipoBulto" => $input->isValid("tipoBulto") ? $input->tipoBulto : null,
                        "descarga" => $input->isValid("bulto_descarga") ? date("Y-m-d H:i:s", strtotime($input->bulto_descarga)) : null,
                        "carga" => $input->isValid("bulto_carga") ? date("Y-m-d H:i:s", strtotime($input->bulto_carga)) : null,
                        "revision" => $input->isValid("bulto_revision") ? date("Y-m-d H:i:s", strtotime($input->bulto_revision)) : null,
                        "mercancia" => $input->mercancia,
                        "observacion" => $input->observacion,
                        "dano" => $input->isValid("damage") ? 1 : null,
                        "escaneado" => $input->isValid("escaneado") ? date("Y-m-d H:i:s") : null,
                        "actualizado" => date("Y-m-d H:i:s")
                    );

                    $this->_firephp->info($arr);

                    if (($model->actualizar($input->idBulto, $arr))) {
                        $this->_helper->json(array("success" => true));
                    }
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function borrarBultoAction() {
        try {
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => "Digits",
                    "idTrafico" => "Digits",
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("id") && $input->isValid("idTrafico")) {

                    $mppr = new Trafico_Model_TraficosMapper();
                    $model = new Bodega_Model_Bultos();

                    if (($model->borrar($input->id))) {

                        $totalBultos = $model->totalBultos($input->idTrafico);

                        $mppr->actualizarDatosTrafico($input->idTrafico, array("bultos" => $totalBultos));

                        $this->_helper->json(array("success" => true, "totalBultos" => $totalBultos));
                    }
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function agregarBultoAction() {
        try {
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idTrafico" => "Digits",
                );
                $v = array(
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int())
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("idTrafico")) {

                    $misc = new OAQ_Misc();

                    $mppr = new Trafico_Model_TraficosMapper();
                    $row = $mppr->obtenerPorId($input->idTrafico);

                    $model = new Bodega_Model_Bultos();

                    $ultimoBulto = $model->ultimoBulto($input->idTrafico);
                    $totalBultos = $model->totalBultos($input->idTrafico);

                    $arr = array(
                        "idTrafico" => $input->idTrafico,
                        "idBodega" => $row['idBodega'],
                        "idUsuario" => $this->_session->id,
                        "numBulto" => $ultimoBulto + 1,
                        "uuid" => $misc->getUuid($row['referencia'] . $row['rfcCliente'] . ($ultimoBulto + 1)),
                        "creado" => date("Y-m-d H:i:s")
                    );

                    if (($id = $model->agregar($arr))) {
                        $mppr->actualizarDatosTrafico($input->idTrafico, array("bultos" => $totalBultos + 1));
                        $this->_helper->json(array("success" => true, "totalBultos" => $totalBultos + 1, "ultimoBulto" => $ultimoBulto + 1));
                    }
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function subdividirAction() {
        try {
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => "Digits",
                    "bultos_restantes" => "Digits",
                    "n_referencia" => "StringToUpper"
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "bultos_restantes" => array("NotEmpty", new Zend_Validate_Int()),
                    "ids" => array("NotEmpty"),
                    "n_referencia" => array("NotEmpty")
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("id") && $input->isValid("ids")) {

                    $ids = explode("," ,$input->ids);

                    $bodega = new OAQ_Bodega(array("idTrafico" => $input->id));
                    if ($bodega->subdividir($ids, $input->bultos_restantes, $input->n_referencia)) {
                        $this->_helper->json(array("success" => true));
                    } else {
                        $this->_helper->json(array("success" => false));
                    }
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function cambiarTipoBultoAction() {
        try {
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => "Digits",
                    "tipoBulto" => "Digits",
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "ids" => array("NotEmpty"),
                    "tipoBulto" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("id") && $input->isValid("ids") && $input->isValid("tipoBulto")) {

                    $ids = explode("," ,$input->ids);

                    $mppr = new Bodega_Model_Bultos();

                    if (!empty($ids)) {
                        foreach ($ids as $id_bulto) {
                            $mppr->actualizar($id_bulto, array("tipoBulto" => $input->tipoBulto));
                        }
                    }
                    $this->_helper->json(array("success" => true));

                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function enviarATraficoAction() {
        try {
            $r = $this->getRequest();
            if ($r->isPost()) {

                $f = array(
                    "idTrafico" => array("StringTrim", "StripTags"),
                    "aduana" => "Digits",
                    "operacion" => "StringToUpper",
                    "cvePedimento" => "StringToUpper",
                    "pedimento" => "Digits"
                );
                $v = array(
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                    "aduana"  => array("NotEmpty"),
                    "operacion" => array("NotEmpty"),
                    "cvePedimento" => array("NotEmpty"),
                    "pedimento" => array("NotEmpty")
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("idTrafico")) {

                    $bodega = new OAQ_Bodega(array("idTrafico" => $input->idTrafico));
                    if (($bodega->enviarATrafico($input->aduana, $input->operacion, $input->cvePedimento, $input->pedimento))) {
                        $this->_helper->json(array("success" => true, "id" => $input->idTrafico));
                    }
                    $this->_helper->json(array("success" => false));
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function modificarEntradaAction()
    {
        try {
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags()),
                    "idTrafico" => new Zend_Filter_Digits(),
                    "idBodega" => new Zend_Filter_Digits(),
                    "idCliente" => new Zend_Filter_Digits(),
                    "referencia" => new Zend_Filter_StringToUpper(),
                );
                $v = array(
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                    "idBodega" => array("NotEmpty", new Zend_Validate_Int()),
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                    "referencia" => array(new Zend_Validate_Regex("/^[-_a-zA-Z0-9.\/]+$/"), "presence" => "required"),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("idTrafico")) {

                    $referencias = new OAQ_Referencias();
                    if ($referencias->modificarEntrada($input->idTrafico, $input->idBodega, $input->idCliente, $input->referencia, $this->_session->username)) {
                        $this->_helper->json(array("success" => true));
                    } else {
                        $this->_helper->json(array("success" => false));
                    }

                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

}

