<?php

require_once "Spout/Autoloader/autoload.php";

class Trafico_CrudController extends Zend_Controller_Action
{

    protected $_session;
    protected $_config;
    protected $_appconfig;
    protected $_filtrosCookies;
    protected $_db;
    protected $_res;
    protected $_firephp;

    public function init()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $this->_db = Zend_Registry::get("oaqintranet");
        $this->_firephp = Zend_Registry::get("firephp");
    }

    public function preDispatch()
    {
        $this->_session = new Zend_Session_Namespace($this->_config->app->namespace);
        if ($this->_session->authenticated !== true) {
            $this->getResponse()->setRedirect($this->_appconfig->getParam("link-logout"));
        }
        $referencias = new OAQ_Referencias();
        $this->_res = $referencias->restricciones($this->_session->id, $this->_session->role);
    }

    protected function _update($id, $arr)
    {
        try {
            $stmt = $this->_db->update("traficos", $arr, array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function guardarFechasAction()
    {
        try {
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idTrafico" => "Digits",
                    "pedimento" => "Digits",
                );
                $v = array(
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                    "pedimento" => array("NotEmpty", new Zend_Validate_Int()),
                    "fechaEta" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}\s\d{2}:\d{2}$/")), // 10
                    "fechaEtaAlmacen" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}\s\d{2}:\d{2}$/")), // 28
                    "fechaPago" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}\s\d{2}:\d{2}$/")), // 2
                    "fechaEntrada" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")), // 1
                    "fechaPresentacion" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")), // 5
                    "fechaEir" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")), // 5
                    "fechaLiberacion" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}\s\d{2}:\d{2}$/")), // 8
                    "fechaNotificacion" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")), // 9
                    "fechaRevalidacion" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}\s\d{2}:\d{2}$/")), // 20
                    "fechaPrevio" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}\s\d{2}:\d{2}$/")), // 21
                    "fechaDeposito" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")), // 22
                    "fechaRecepcionDocs" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")), // 11
                    "fechaPresentacion" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")), // 5
                    "fechaCitaDespacho" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")), // 25
                    "fechaVistoBueno" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}\s\d{2}:\d{2}$/")), // 29
                    "fechaInstruccionEspecial" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")), // 54
                    "fechaEnvioProforma" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")), // 26
                    "fechaEnvioDocumentos" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}\s\d{2}:\d{2}$/")), // 27
                    "horaRecepcionDocs" => array("NotEmpty", new Zend_Validate_Regex("/(\d{2}):(\d{2}) (AM|PM)/")),
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("idTrafico")) {
                    $trafico = new OAQ_Trafico(array("idTrafico" => $i->idTrafico, "idUsuario" => $this->_session->id, "usuario" => $this->_session->username));
                    $traffics = new Trafico_Model_TraficosMapper();
                    if ($i->isValid("fechaEntrada")) {
                        $trafico->actualizarFecha(1, $i->fechaEntrada, $this->_session->username);
                    }
                    if ($i->isValid("fechaPresentacion")) {
                        $trafico->actualizarFecha(5, $i->fechaPresentacion, $this->_session->username);
                    }
                    if ($i->isValid("fechaPago")) {
                        if ($trafico->actualizarFecha(2, $i->fechaPago, $this->_session->username)) {
                            $traffics->actualizarFechaPago($i->idTrafico, $i->fechaPago);
                        }
                    }
                    if ($i->isValid("fechaPresentacion")) {
                        $trafico->actualizarFecha(5, $i->fechaPresentacion, $this->_session->username);
                    }
                    if ($i->isValid("fechaEir")) {
                        $trafico->actualizarFecha(55, $i->fechaEir, $this->_session->username);
                    }
                    if ($i->isValid("fechaLiberacion")) {
                        $trafico->actualizarFecha(8, $i->fechaLiberacion, $this->_session->username);
                    }
                    if ($i->isValid("fechaNotificacion")) {
                        $trafico->actualizarFecha(9, $i->fechaNotificacion, $this->_session->username);
                    }
                    if ($i->isValid("fechaEta")) {
                        $trafico->actualizarFecha(10, $i->fechaEta, $this->_session->username);
                    }
                    if ($i->isValid("fechaRevalidacion")) {
                        $trafico->actualizarFecha(20, $i->fechaRevalidacion, $this->_session->username);
                    }
                    if ($i->isValid("fechaPrevio")) {
                        $trafico->actualizarFecha(21, $i->fechaPrevio, $this->_session->username);
                    }
                    if ($i->isValid("fechaDeposito")) {
                        $trafico->actualizarFecha(22, $i->fechaDeposito, $this->_session->username);
                    }
                    if ($i->isValid("fechaCitaDespacho")) {
                        $trafico->actualizarFecha(25, $i->fechaCitaDespacho, $this->_session->username);
                    }
                    if ($i->isValid("fechaEnvioProforma")) {
                        $trafico->actualizarFecha(26, $i->fechaEnvioProforma, $this->_session->username);
                    }
                    if ($i->isValid("fechaEnvioDocumentos")) {
                        $trafico->actualizarFecha(27, $i->fechaEnvioDocumentos, $this->_session->username);
                    }
                    if ($i->isValid("fechaEtaAlmacen")) {
                        $trafico->actualizarFecha(28, $i->fechaEtaAlmacen, $this->_session->username);
                    }
                    if ($i->isValid("fechaVistoBueno")) {
                        $trafico->actualizarFecha(29, $i->fechaVistoBueno, $this->_session->username);
                    }
                    if ($i->isValid("fechaInstruccionEspecial")) {
                        $trafico->actualizarFecha(54, $i->fechaInstruccionEspecial, $this->_session->username);
                    }

                    $this->_db->query("UPDATE traficos AS t SET t.diasRetraso = DATEDIFF(t.fechaPago, t.fechaEta) WHERE t.fechaPago IS NOT NULL AND t.fechaEta IS NOT NULL AND t.id = {$i->id};");
                    $this->_db->query("UPDATE traficos AS t SET t.diasDespacho = DATEDIFF(fechaLiberacion, fechaEta) WHERE t.fechaLiberacion IS NOT NULL AND t.fechaEta IS NOT NULL AND t.id = {$i->id};");

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

    public function obtenerFechasAction()
    {
        try {
            $f = array(
                "idTrafico" => array("StringTrim", "StripTags", "Digits"),
            );
            $v = array(
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idTrafico")) {
                $sql = $this->_db->select()
                    ->from(array("t" => "traficos"), array(
                        new Zend_Db_Expr("DATE_FORMAT(fechaEta,'%Y-%m-%d %H:%i') AS fechaEta"),
                        new Zend_Db_Expr("DATE_FORMAT(fechaEnvioDocumentos,'%Y-%m-%d %H:%i') AS fechaEnvioDocumentos"),
                        new Zend_Db_Expr("DATE_FORMAT(fechaVistoBueno,'%Y-%m-%d %H:%i') AS fechaVistoBueno"),
                        new Zend_Db_Expr("DATE_FORMAT(fechaRevalidacion,'%Y-%m-%d %H:%i') AS fechaRevalidacion"),
                        new Zend_Db_Expr("DATE_FORMAT(fechaPrevio,'%Y-%m-%d %H:%i') AS fechaPrevio"),
                        new Zend_Db_Expr("DATE_FORMAT(fechaPago,'%Y-%m-%d %H:%i') AS fechaPago"),
                        new Zend_Db_Expr("DATE_FORMAT(fechaLiberacion,'%Y-%m-%d %H:%i') AS fechaLiberacion"),
                        new Zend_Db_Expr("DATE_FORMAT(fechaEtaAlmacen,'%Y-%m-%d %H:%i') AS fechaEtaAlmacen"),
                    ))
                    ->where("t.id = ?", $input->idTrafico);
                $stmt = $this->_db->fetchRow($sql);
                if ($stmt) {
                    return $this->_helper->json(array("success" => true, "dates" => $stmt));
                } else {
                    throw new Exception("No data found.");
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    protected function _todos($page, $rows, $filterRules = null, $cookies = null, $tipoAduana = null, $bodega = null)
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
                    // new Zend_Db_Expr("DATE_FORMAT(fechaInstruccionEspecial,'%Y-%m-%d %T') AS fechaInstruccionEspecial"),
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
                    // new Zend_Db_Expr("IF (fechaLiberacion IS NOT NULL, DATEDIFF(fechaLiberacion, fechaEta), 0) AS diasDespacho"),
                    // new Zend_Db_Expr("IF (fechaPago IS NOT NULL, DATEDIFF(fechaPago, fechaEta), 0) AS diasRetraso"),
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
                ->order(array("fechaEta DESC"))
                ->limit($rows, ($page - 1) * $rows);
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
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    protected function _total($filterRules = null, $cookies = null, $tipoAduana = null, $bodega = null)
    {
        try {
            $sql = $this->_db->select()
                ->from(array("t" => "traficos"), array("count(*) AS total"))
                ->joinInner(array("u" => "usuarios"), "u.id = t.idUsuario", array("nombre"))
                ->joinInner(array("a" => "trafico_aduanas"), "a.id = t.idAduana", array(""))
                ->joinInner(array("c" => "trafico_clientes"), "c.id = t.idCliente", array("nombre AS nombreCliente"));
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
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return (int) $stmt["total"];
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    /*public function traficosAction() {
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
    "tipoAduana" => array("Digits"),
    );
    $v = array(
    "page" => array(new Zend_Validate_Int(), "default" => 1),
    "rows" => array(new Zend_Validate_Int(), "default" => 10),
    "tipoAduana" => array(new Zend_Validate_Int(), "NotEmpty"),
    "filterRules" => "NotEmpty",
    "bodega" => "NotEmpty",
    );
    $input = new Zend_Filter_Input($f, $v, $r->getPost());
    if ($input->isValid("page") && $input->isValid("rows")) {
    $rows = $this->_todos($input->page, $input->rows, $input->filterRules, $this->_cookies(), $input->tipoAduana, $input->bodega);
    $arr = array(
    "total" => $this->_total($input->filterRules, $this->_cookies(), $input->tipoAduana, $input->bodega),
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
    }*/

    public function traficosAction()
    {
        try {
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "page" => array("Digits"),
                    "rows" => array("Digits"),
                    "tipoAduana" => array("Digits"),
                );
                $v = array(
                    "page" => array(new Zend_Validate_Int(), "default" => 1),
                    "rows" => array(new Zend_Validate_Int(), "default" => 20),
                    "tipoAduana" => array(new Zend_Validate_Int(), "NotEmpty"),
                    "filterRules" => "NotEmpty",
                    "bodega" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("page") && $input->isValid("rows")) {

                    $referencias = new OAQ_Trafico_Referencias($this->_session->id, $this->_session->role);
                    $arr = $referencias->referencias($input->page, $input->rows, $input->filterRules, $input->tipoAduana, $input->bodega);

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

    protected function _clientes()
    {
        try {
            $sql = $this->_db->select()
                ->from("trafico_clientes", array("id", "rfc", "nombre"))
                ->order(array("nombre ASC"));
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function misTraficosAction()
    {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $u_id = $this->_session->id;
            $sql = $this->_db->select()
                ->from(array("t" => "traficos"), array("id", "estatus", "patente", "aduana", "pedimento", "referencia"))
                ->where("t.pedimento IS NOT NULL")
                ->where("t.estatus NOT IN (3, 4)")
                ->where("(t.idUsuario = $u_id OR t.idUsuarioModif = $u_id)");
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                $this->_helper->json(array("success" => true, "results" => $stmt));
            }
            $this->_helper->json(array("success" => true, "results" => array()));
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function traficoActualizarAction()
    {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $traf = new OAQ_Trafico();
                $dates = $traf->obtenerFiltrosFechas();
                unset($traf);
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                    "estatus" => array("Digits"),
                    "blGuia" => array("StringToUpper"),
                    "contenedorCaja" => array("StringToUpper"),
                    "ordenCompra" => array("StringToUpper"),
                    "blGuia" => array("StringToUpper"),
                    "carrierNaviera" => array("Digits"),
                    "proveedores" => array("StringToUpper"),
                    "facturas" => array("StringToUpper"),
                    "cantidadFacturas" => array("Digits"),
                    "cantidadPartes" => array("Digits"),
                    "tipoCarga" => array("Digits"),
                    "almacen" => array("StringToUpper"),
                    "observaciones" => array("StringToUpper"),
                    "idPlanta" => array("Digits"),
                    "cumplimientoAdministrativo" => array("Digits"),
                    "cumplimientoOperativo" => array("Digits"),
                    "ccConsolidado" => array("StringToUpper"),
                );
                $v = array();
                foreach ($dates as $d) {
                    $v[$d["label"]] = array("NotEmpty", new Zend_Validate_Regex((string) $d["regx"]));
                }
                $v["id"] = array(new Zend_Validate_Int());
                $v["estatus"] = array("NotEmpty", new Zend_Validate_Int());
                $v["carrierNaviera"] = array("NotEmpty");
                $v["blGuia"] = array("NotEmpty");
                $v["contenedorCaja"] = array("NotEmpty");
                $v["ordenCompra"] = array("NotEmpty");
                $v["blGuia"] = array("NotEmpty");
                $v["proveedores"] = array("NotEmpty");
                $v["facturas"] = array("NotEmpty");
                $v["cantidadFacturas"] = array("NotEmpty");
                $v["cantidadPartes"] = array("NotEmpty");
                $v["observaciones"] = array("NotEmpty");
                $v["tipoCarga"] = array("NotEmpty");
                $v["almacen"] = array("NotEmpty");
                $v["idPlanta"] = array("NotEmpty");
                $v["cumplimientoAdministrativo"] = array("NotEmpty");
                $v["cumplimientoOperativo"] = array("NotEmpty");
                $v["ccConsolidado"] = array("NotEmpty");
                $v["fechaEtd"] = array("NotEmpty");
                $v["fechaEta"] = array("NotEmpty");
                $v["fechaNotificacion"] = array("NotEmpty");
                $v["fechaEnvioDocumentos"] = array("NotEmpty");
                $v["fechaEntrada"] = array("NotEmpty");
                $v["fechaPresentacion"] = array("NotEmpty");
                $v["fechaDeposito"] = array("NotEmpty");
                $v["fechaInstruccionEspecial"] = array("NotEmpty");
                $v["fechaEnvioProforma"] = array("NotEmpty");
                $v["fechaVistoBueno"] = array("NotEmpty");
                $v["fechaProformaTercero"] = array("NotEmpty");
                $v["fechaVistoBuenoTercero"] = array("NotEmpty");
                $v["fechaRevalidacion"] = array("NotEmpty");
                $v["fechaPrevio"] = array("NotEmpty");
                $v["fechaPago"] = array("NotEmpty");
                $v["fechaSolicitudTransfer"] = array("NotEmpty");
                $v["fechaArriboTransfer"] = array("NotEmpty");
                $v["fechaLiberacion"] = array("NotEmpty");
                $v["fechaEtaAlmacen"] = array("NotEmpty");
                $v["fechaFacturacion"] = array("NotEmpty");
                $v["fechaComprobacion"] = array("NotEmpty");

                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("id")) {
                    $trafico = new OAQ_Trafico(array("idTrafico" => $i->id, "idUsuario" => $this->_session->id, "usuario" => $this->_session->username));
                    $row = $trafico->obtenerDatos();

                    $estatus = 1;
                    if ($row['estatus'] && $row['estatus'] !== null) {
                        $estatus = $row['estatus'];
                    }
                    if ($i->isValid("fechaPago")) {
                        $estatus = 2;
                    }
                    if ($i->isValid("fechaLiberacion")) {
                        $estatus = 3;
                    }
                    $obs = '';
                    if ($i->isValid("observaciones")) {
                        $obs = $i->observaciones;
                    }
                    $cc = '';
                    if ($i->isValid("ccConsolidado")) {
                        $cc = $i->ccConsolidado;
                    }
                    $arr = array(
                        "estatus" => $estatus,
                        "blGuia" => ($i->isValid("blGuia")) ? $i->blGuia : $row['blGuia'],
                        "contenedorCaja" => ($i->isValid("contenedorCaja")) ? $i->contenedorCaja : $row['contenedorCaja'],
                        "observaciones" => $obs,
                        "ordenCompra" => ($i->isValid("ordenCompra")) ? $i->ordenCompra : $row['ordenCompra'],
                        "carrierNaviera" => ($i->isValid("carrierNaviera")) ? $i->carrierNaviera : $row['carrierNaviera'],
                        "proveedores" => ($i->isValid("proveedores")) ? $i->proveedores : $row['proveedores'],
                        "facturas" => ($i->isValid("facturas")) ? $i->facturas : $row['facturas'],
                        "cantidadFacturas" => ($i->isValid("cantidadFacturas")) ? $i->cantidadFacturas : $row['cantidadFacturas'],
                        "cantidadPartes" => ($i->isValid("cantidadPartes")) ? $i->cantidadPartes : $row['cantidadPartes'],
                        "tipoCarga" => ($i->isValid("tipoCarga")) ? $i->tipoCarga : $row['tipoCarga'],
                        "almacen" => ($i->isValid("almacen")) ? $i->almacen : $row['almacen'],
                        "idPlanta" => ($i->isValid("idPlanta")) ? $i->idPlanta : $row['idPlanta'],
                        "cumplimientoAdministrativo" => ($i->isValid("cumplimientoAdministrativo")) ? $i->cumplimientoAdministrativo : $row['cumplimientoAdministrativo'],
                        "cumplimientoOperativo" => ($i->isValid("cumplimientoOperativo")) ? $i->cumplimientoOperativo : $row['cumplimientoOperativo'],
                        "ccConsolidado" => $cc,
                        "fechaEtd" => ($i->isValid("fechaEtd")) ? $i->fechaEtd : $row['fechaEtd'],
                        "fechaEta" => ($i->isValid("fechaEta")) ? $i->fechaEta : $row['fechaEta'],
                        "fechaNotificacion" => ($i->isValid("fechaNotificacion")) ? $i->fechaNotificacion : $row['fechaNotificacion'],
                        "fechaEnvioDocumentos" => ($i->isValid("fechaEnvioDocumentos")) ? $i->fechaEnvioDocumentos : $row['fechaEnvioDocumentos'],
                        "fechaEntrada" => ($i->isValid("fechaEntrada")) ? $i->fechaEntrada : $row['fechaEntrada'],
                        "fechaPresentacion" => ($i->isValid("fechaPresentacion")) ? $i->fechaPresentacion : $row['fechaPresentacion'],
                        "fechaDeposito" => ($i->isValid("fechaDeposito")) ? $i->fechaDeposito : $row['fechaDeposito'],
                        "fechaInstruccionEspecial" => ($i->isValid("fechaInstruccionEspecial")) ? $i->fechaInstruccionEspecial : $row['fechaInstruccionEspecial'],
                        "fechaEnvioProforma" => ($i->isValid("fechaEnvioProforma")) ? $i->fechaEnvioProforma : $row['fechaEnvioProforma'],
                        "fechaVistoBueno" => ($i->isValid("fechaVistoBueno")) ? $i->fechaVistoBueno : $row['fechaVistoBueno'],
                        "fechaProformaTercero" => ($i->isValid("fechaProformaTercero")) ? $i->fechaProformaTercero : $row['fechaProformaTercero'],
                        "fechaVistoBuenoTercero" => ($i->isValid("fechaVistoBuenoTercero")) ? $i->fechaVistoBuenoTercero : $row['fechaVistoBuenoTercero'],
                        "fechaRevalidacion" => ($i->isValid("fechaRevalidacion")) ? $i->fechaRevalidacion : $row['fechaRevalidacion'],
                        "fechaPrevio" => ($i->isValid("fechaPrevio")) ? $i->fechaPrevio : $row['fechaPrevio'],
                        "fechaPago" => ($i->isValid("fechaPago")) ? $i->fechaPago : $row['fechaPago'],
                        "fechaSolicitudTransfer" => ($i->isValid("fechaSolicitudTransfer")) ? $i->fechaSolicitudTransfer : $row['fechaSolicitudTransfer'],
                        "fechaArriboTransfer" => ($i->isValid("fechaArriboTransfer")) ? $i->fechaArriboTransfer : $row['fechaArriboTransfer'],
                        "fechaLiberacion" => ($i->isValid("fechaLiberacion")) ? $i->fechaLiberacion : $row['fechaLiberacion'],
                        "fechaEtaAlmacen" => ($i->isValid("fechaEtaAlmacen")) ? $i->fechaEtaAlmacen : $row['fechaEtaAlmacen'],
                        "fechaFacturacion" => ($i->isValid("fechaFacturacion")) ? $i->fechaFacturacion : $row['fechaFacturacion'],
                        "fechaComprobacion" => ($i->isValid("fechaComprobacion")) ? $i->fechaComprobacion : $row['fechaComprobacion'],
                    );
                    foreach ($dates as $k => $v) {
                        if ($i->isValid($v["label"])) {
                            $trafico->actualizarFecha($k, $i->$v["label"], $this->_session->username);
                        }
                    }

                    $this->_db->query("UPDATE traficos AS t SET t.diasRetraso = DATEDIFF(t.fechaPago, t.fechaEta) WHERE t.fechaPago IS NOT NULL AND t.fechaEta IS NOT NULL AND t.id = {$i->id};");
                    $this->_db->query("UPDATE traficos AS t SET t.diasDespacho = DATEDIFF(fechaLiberacion, fechaEta) WHERE t.fechaLiberacion IS NOT NULL AND t.fechaEta IS NOT NULL AND t.id = {$i->id};");

                    if ($this->_update($i->id, $arr)) {
                        $this->_helper->json(array("success" => true));
                    }

                    $this->_helper->json(array("success" => false));
                } else {
                    $this->_helper->json(array("success" => false, "msg" => "Invalid input!"));
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function traficoBorrarAction()
    {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {

            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerClientesAction()
    {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $rows = $this->_clientes();
                $this->_helper->json($rows);
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    protected function _filtroReportes(Zend_Db_Select $sql, $filtro = null)
    {
        $referencias = new OAQ_Referencias();
        $res = $referencias->restricciones($this->_session->id, $this->_session->role);
        if ($this->_session->role == "inhouse") {
            $sql->where("t.rfcCliente IN (?)", $res["rfcs"]);
        } else if (in_array($this->_session->role, array("super", "trafico_operaciones", "trafico", "trafico_aero", "trafico_ejecutivo", "gerente"))) {
            if (!empty($res["idsAduana"])) {
                $sql->where("t.idAduana IN (?)", $res["idsAduana"]);
            }
        }
        if ((int) $filtro == 1) {
            $sql->where("t.fechaPago IS NOT NULL");
        }
        if ((int) $filtro == 2) {
            $sql->where("t.fechaPago IS NOT NULL AND fechaLiberacion IS NOT NULL");
        }
    }

    protected function _reporteIncompletos($page, $rows, $idAduana, $fechaInicio, $fechaFin, $idCliente = null)
    {
        try {
            $fields = array(
                "t.aduana",
                "t.referencia",
                "t.patente",
                "t.pedimento",
                "t.cvePedimento",
                "DATE_FORMAT(t.fechaPago,'%Y-%m-%d') AS fechaPago",
                "DATE_FORMAT(t.fechaEntrada,'%Y-%m-%d') AS fechaEntrada",
                "DATE_FORMAT(t.fechaLiberacion,'%Y-%m-%d') AS fechaLiberacion",
                "DATE_FORMAT(t.fechaRevalidacion,'%Y-%m-%d') AS fechaRevalidacion",
                "DATE_FORMAT(t.fechaEnvioDocumentos,'%Y-%m-%d') AS fechaEnvioDocumentos",
                "u.nombre AS usuario",
            );
            $sql = $this->_db->select()
                ->from(array("t" => "traficos"), $fields)
                ->joinLeft(array("u" => "usuarios"), "t.idUsuario = u.id", array("nombre"))
                ->where("t.fechaPago >= ?", date("Y-m-d H:i:s", strtotime($fechaInicio)))
                ->where("t.fechaPago <= ?", date("Y-m-d H:i:s", strtotime($fechaFin)))
                ->where("(t.fechaEntrada IS NULL OR t.fechaPago IS NULL OR t.fechaLiberacion IS NULL OR t.fechaRevalidacion IS NULL OR t.fechaEnvioDocumentos IS NULL)")
                ->where("t.pedimento IS NOT NULL");
            $count = $this->_db->select()
                ->from(array("t" => "traficos"), array("count(*) as total"))
                ->where("t.fechaPago >= ?", date("Y-m-d H:i:s", strtotime($fechaInicio)))
                ->where("t.fechaPago <= ?", date("Y-m-d H:i:s", strtotime($fechaFin)))
                ->where("(t.fechaEntrada IS NULL OR t.fechaPago IS NULL OR t.fechaLiberacion IS NULL OR t.fechaRevalidacion IS NULL OR t.fechaEnvioDocumentos IS NULL)")
                ->where("t.pedimento IS NOT NULL");
            if (isset($idCliente)) {
                $sql->where("t.idCliente = ?", $idCliente);
            }
            if (isset($rows) && isset($page)) {
                $sql->limit($rows, ($page - 1) * $rows);
            }
            if (isset($idAduana) && (int) $idAduana !== 0) {
                $sql->where("t.idAduana = ?", $idAduana);
                $count->where("t.idAduana = ?", $idAduana);
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
        }
    }

    protected function _reporteCandados($page, $rows, $idAduana, $fechaInicio, $fechaFin, $idCliente = null)
    {
        try {
            $fields = array(
                "t.aduana",
                "c.nombre AS nombreCliente",
                "t.referencia",
                "t.patente",
                "t.pedimento",
                "t.cvePedimento",
                "DATE_FORMAT(fechaPago,'%Y-%m-%d') AS fechaPago",
            );
            $sql = $this->_db->select()
                ->from(array("t" => "traficos"), $fields)
                ->joinLeft(array("c" => "trafico_clientes"), "c.id = t.idCliente", array(""))
                ->joinLeft(array("cc" => "trafico_candados"), "cc.idTrafico = t.id", array("numero"))
                ->joinLeft(array("ct" => "trafico_trans"), "ct.idTrafico = t.id", array("placas"))
                ->where("cc.numero IS NOT NULL")
                ->where("t.fechaPago >= ?", date("Y-m-d H:i:s", strtotime($fechaInicio)))
                ->where("t.fechaPago <= ?", date("Y-m-d H:i:s", strtotime($fechaFin)))
                ->where("t.pedimento IS NOT NULL");
            $count = $this->_db->select()
                ->from(array("t" => "traficos"), array("count(*) as total"))
                ->joinLeft(array("cc" => "trafico_candados"), "cc.idTrafico = t.id", array("numero"))
                ->where("cc.numero IS NOT NULL")
                ->where("t.fechaPago >= ?", date("Y-m-d H:i:s", strtotime($fechaInicio)))
                ->where("t.fechaPago <= ?", date("Y-m-d H:i:s", strtotime($fechaFin)))
                ->where("t.pedimento IS NOT NULL");
            if (isset($idCliente)) {
                $sql->where("t.idCliente = ?", $idCliente);
            }
            if (isset($rows) && isset($page)) {
                $sql->limit($rows, ($page - 1) * $rows);
            }
            if (isset($idAduana) && (int) $idAduana) {
                $sql->where("t.idAduana = ?", $idAduana);
                $count->where("t.idAduana = ?", $idAduana);
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
        }
    }

    public function reporteInventariosAction()
    {
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
                if ($input->tipoReporte == 13) {
                    if ($dexcel === false) {
                        $rows = $this->_reporteInventario($input->page, $input->rows, $input->idAduana, $input->fechaInicio, $input->fechaFin, $input->filtro);
                        $arr = array(
                            "total" => $this->_totalReporteInventario($input->idAduana, $input->fechaInicio, $input->fechaFin, $input->filtro),
                            "rows" => empty($rows) ? array() : $rows,
                        );
                        $this->_helper->json($arr);
                    } else {
                        $rows = $this->_reporteInventario(null, null, $input->idAduana, $input->fechaInicio, $input->fechaFin, $input->filtro);
                        $reportes->reportesTrafico(80, $rows);
                    }
                }
            } else {
                $this->_helper->json(array("success" => false, "message" => "Invalid Input!"));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    protected function _reporteInventario($page, $rows, $idAduana, $fechaInicio, $fechaFin, $filtro = null, $tipoAduana = null, $idCliente = null, $tipoOperacion = null)
    {
        try {
            $sql = $this->_db->select()
                ->from(array("t" => "traficos"), array(
                    "id",
                    "patente",
                    "aduana",
                    "pedimento",
                    "referencia",
                    "rfcCliente",
                    "ie",
                    "estatus",
                    "cvePedimento",
                    "regimen",
                    new Zend_Db_Expr("DATE_FORMAT(fechaLiberacion,'%Y-%m-%d %T') AS fechaLiberacion"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaPago,'%Y-%m-%d %T') AS fechaPago"),
                    "observacionSemaforo",
                    "semaforo",
                    "contenedorCaja",
                    "contenedorCajaEntrada",
                    "contenedorCajaSalida",
                ))
                ->joinLeft(array("c" => "trafico_clientes"), "c.id = t.idCliente", array("nombre AS nombreCliente"))
                ->joinLeft(array("r" => "repositorio_index"), "r.patente = t.patente AND r.aduana = t.aduana AND r.pedimento = t.pedimento", array("revisionOperaciones"))
                ->joinInner(array("a" => "trafico_aduanas"), "a.id = t.idAduana", array(""))
                ->where("t.estatus <> 4")
                ->where("t.fechaLiberacion IS NULL")
                ->where("t.pedimento IS NOT NULL")
                ->order(array("t.patente", "t.aduana", "t.pedimento", "t.referencia"));
            if (isset($idCliente)) {
                $sql->where("t.idCliente = ?", $idCliente);
            }
            if (isset($idAduana)) {
                $sql->where("t.idAduana = ?", $idAduana);
            }
            if (isset($tipoOperacion)) {
                $sql->where("t.ie = ?", $tipoOperacion);
            }
            if (isset($rows) && isset($page)) {
                $sql->limit($rows, ($page - 1));
            }
            if (isset($tipoAduana) && (int) $tipoAduana != 0) {
                $sql->where("a.tipoAduana = ?", $tipoAduana);
            }
            if (isset($filtro)) {
                $this->_filtroReportes($sql, $filtro);
            }
            if ((int) $idAduana != 0) {
                $sql->where("t.idAduana = ?", $idAduana);
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

    protected function _reporte($page, $rows, $idAduana, $fechaInicio, $fechaFin, $filtro = null, $tipoAduana = null, $idCliente = null, $tipoOperacion = null)
    {
        try {
            $sql = $this->_db->select()
                ->from(array("t" => "traficos"), array(
                    "id",
                    "patente",
                    "aduana",
                    "pedimento",
                    "referencia",
                    "rfcCliente",
                    "ie",
                    "blGuia",
                    "contenedorCaja",
                    "ordenCompra",
                    "proveedores",
                    "facturas",
                    "cantidadFacturas",
                    "cantidadPartes",
                    "almacen",
                    new Zend_Db_Expr("DATE_FORMAT(fechaEta,'%Y-%m-%d') AS fechaEta"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaNotificacion,'%Y-%m-%d %T') AS fechaNotificacion"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaEnvioDocumentos,'%Y-%m-%d %T') AS fechaEnvioDocumentos"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaEntrada,'%Y-%m-%d') AS fechaEntrada"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaPresentacion,'%Y-%m-%d') AS fechaPresentacion"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaDeposito,'%Y-%m-%d') AS fechaDeposito"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaEnvioProforma,'%Y-%m-%d %T') AS fechaEnvioProforma"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaVistoBueno,'%Y-%m-%d %T') AS fechaVistoBueno"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaProformaTercero,'%Y-%m-%d %T') AS fechaProformaTercero"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaRevalidacion,'%Y-%m-%d') AS fechaRevalidacion"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaPrevio,'%Y-%m-%d %T') AS fechaPrevio"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaPago,'%Y-%m-%d %T') AS fechaPago"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaSolicitudTransfer,'%Y-%m-%d %T') AS fechaSolicitudTransfer"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaArriboTransfer,'%Y-%m-%d %T') AS fechaArriboTransfer"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaInstruccionEspecial,'%Y-%m-%d %T') AS fechaInstruccionEspecial"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaLiberacion,'%Y-%m-%d %T') AS fechaLiberacion"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaEtaAlmacen,'%Y-%m-%d') AS fechaEtaAlmacen"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaFacturacion,'%Y-%m-%d') AS fechaFacturacion"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaComprobacion,'%Y-%m-%d') AS fechaComprobacion"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaEtd,'%Y-%m-%d') AS fechaEtd"),
                    new Zend_Db_Expr("IF (fechaLiberacion IS NOT NULL, DATEDIFF(fechaLiberacion, fechaEntrada), 0) AS diasDespacho"),
                    new Zend_Db_Expr("IF (fechaPago IS NOT NULL, DATEDIFF(fechaPago, fechaEta), 0) AS diasRetraso"),
                    "estatus",
                    "cvePedimento",
                    "regimen",
                    "idUsuario",
                    "observacionSemaforo",
                    "semaforo",
                    "coves",
                    "edocuments",
                ))
                ->joinLeft(array("u" => "usuarios"), "t.idUsuario = u.id", array("nombre"))
                ->joinLeft(array("c" => "trafico_clientes"), "c.id = t.idCliente", array("nombre AS nombreCliente"))
                ->joinInner(array("a" => "trafico_aduanas"), "a.id = t.idAduana", array(""))
                ->joinLeft(array("tc" => "trafico_tipocarga"), "tc.id = t.tipoCarga", array("tipoCarga AS carga"))
                ->joinLeft(array("p" => "trafico_clientes_plantas"), "p.id = t.idPlanta", array("descripcion AS descripcionPlanta"))
                ->joinLeft(array("l" => "trafico_almacen"), "l.id = t.almacen", array("nombre AS nombreAlmacen"))
                ->where("t.estatus <> 4")
                ->where("t.pedimento IS NOT NULL")
                ->order(array("fechaEta DESC"));
            if (isset($tipoAduana)) {
                if ($tipoAduana == 1) {
                    $sql->where("a.tipoAduana = 1 AND t.cvePedimento IN ('V1', 'G1', 'E1', 'V5', 'F4', 'F5', 'A3')");
                } elseif ($tipoAduana == 2) {
                    $sql->where("a.tipoAduana = 2 OR (a.tipoAduana = 1 AND t.cvePedimento NOT IN ('V1', 'G1', 'E1', 'V5', 'F4', 'F5', 'A3'))");
                } else {
                    $sql->where('a.tipoAduana = ?', $tipoAduana);
                }
            }
            if (isset($rows) && isset($page)) {
                $sql->limit($rows, $rows * ($page - 1));
            }

            if (isset($filtro)) {
                if ($filtro == 3) {
                    $sql->where("t.fechaPago >= ?", date("Y-m-d H:i:s", strtotime($fechaInicio)))
                        ->where("t.fechaPago <= ?", date("Y-m-d H:i:s", strtotime($fechaFin)))
                        ->where("fechaLiberacion IS NULL");
                } else {
                    $sql->where("t.fechaLiberacion >= ?", date("Y-m-d H:i:s", strtotime($fechaInicio)))
                        ->where("t.fechaLiberacion <= ?", date("Y-m-d H:i:s", strtotime($fechaFin)));
                    $this->_filtroReportes($sql, $filtro);
                }
            } else {
                $sql->where("t.fechaLiberacion >= ?", date("Y-m-d H:i:s", strtotime($fechaInicio)))
                    ->where("t.fechaLiberacion <= ?", date("Y-m-d H:i:s", strtotime($fechaFin)));
            }
            if (isset($idCliente)) {
                $sql->where("t.idCliente = ?", $idCliente);
            }
            if (isset($tipoOperacion)) {
                $sql->where("t.ie = ?", $tipoOperacion);
            }
            if ((int) $idAduana != 0) {
                $sql->where("t.idAduana = ?", $idAduana);
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

    protected function _reporteLiberados($idAduana, $fecha, $tipoAduana = null, $idCliente = null, $tipoOperacion = null, $pagados = null)
    {
        try {
            $sql = $this->_db->select()
                ->from(array("t" => "traficos"), array(
                    "id",
                    "patente",
                    "aduana",
                    "pedimento",
                    "referencia",
                    "rfcCliente",
                    "ie",
                    "blGuia",
                    "contenedorCaja",
                    "ordenCompra",
                    "proveedores",
                    "facturas",
                    "cantidadFacturas",
                    "cantidadPartes",
                    "almacen",
                    new Zend_Db_Expr("DATE_FORMAT(fechaEta,'%Y-%m-%d') AS fechaEta"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaPago,'%Y-%m-%d %T') AS fechaPago"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaLiberacion,'%Y-%m-%d %T') AS fechaLiberacion"),
                    "estatus",
                    "cvePedimento",
                    "regimen",
                    "idUsuario",
                    "observacionSemaforo",
                    "semaforo",
                    "coves",
                    "edocuments",
                ))
                ->joinLeft(array("u" => "usuarios"), "t.idUsuario = u.id", array("nombre"))
                ->joinLeft(array("c" => "trafico_clientes"), "c.id = t.idCliente", array("nombre AS nombreCliente"))
                ->joinInner(array("a" => "trafico_aduanas"), "a.id = t.idAduana", array(""))
                ->where("t.estatus <> 4")
                ->where("t.pedimento IS NOT NULL")
                ->order(array("pedimento ASC"));
            if (!$pagados) {
                $sql->where("t.fechaLiberacion IS NOT NULL")
                    ->where("t.fechaLiberacion BETWEEN '{$fecha} 00:00:00' AND '{$fecha} 23:59:59' ");
            } else {
                $sql->where("t.fechaPago IS NOT NULL")
                    ->where("t.fechaPago BETWEEN '{$fecha} 00:00:00' AND '{$fecha} 23:59:59' ");
            }
            if (isset($tipoAduana) && $tipoAduana != 50) {
                if ($tipoAduana == 1) {
                    $sql->where("a.tipoAduana = 1 AND t.cvePedimento IN ('V1', 'G1', 'E1', 'V5', 'F4', 'F5', 'A3')");
                } elseif ($tipoAduana == 2) {
                    $sql->where("a.tipoAduana = 2 OR (a.tipoAduana = 1 AND t.cvePedimento NOT IN ('V1', 'G1', 'E1', 'V5', 'F4', 'F5', 'A3'))");
                } else {
                    $sql->where('a.tipoAduana = ?', $tipoAduana);
                }
            }
            if (isset($idCliente)) {
                $sql->where("t.idCliente = ?", $idCliente);
            }
            if (isset($tipoOperacion)) {
                $sql->where("t.ie = ?", $tipoOperacion);
            }
            if ((int) $idAduana != 0) {
                $sql->where("t.idAduana = ?", $idAduana);
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

    /**
     *
     * @param int $page
     * @param int $rows
     * @param int $idAduana
     * @param string $fechaInicio
     * @param string $fechaFin
     * @param array $filtro
     * @param int $tipoAduana
     * @param int $idCliente
     * @param string $tipoOperacion Tipo de operacion TOCE.IMP o TOCE.EXP
     * @return type
     * @throws Exception
     */
    protected function _reporteAperturados($page, $rows, $idAduana, $fechaInicio, $fechaFin, $filtro = null, $tipoAduana = null, $idCliente = null, $tipoOperacion = null)
    {
        try {
            $sql = $this->_db->select()
                ->from(array("t" => "traficos"), array(
                    "id",
                    "patente",
                    "aduana",
                    "pedimento",
                    "referencia",
                    "rfcCliente",
                    "ie",
                    "blGuia",
                    "contenedorCaja",
                    "ordenCompra",
                    "proveedores",
                    "facturas",
                    "cantidadFacturas",
                    "cantidadPartes",
                    "almacen",
                    new Zend_Db_Expr("DATE_FORMAT(fechaEta,'%Y-%m-%d') AS fechaEta"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaNotificacion,'%Y-%m-%d %T') AS fechaNotificacion"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaEnvioDocumentos,'%Y-%m-%d %T') AS fechaEnvioDocumentos"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaEntrada,'%Y-%m-%d') AS fechaEntrada"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaPresentacion,'%Y-%m-%d') AS fechaPresentacion"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaDeposito,'%Y-%m-%d') AS fechaDeposito"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaEnvioProforma,'%Y-%m-%d %T') AS fechaEnvioProforma"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaVistoBueno,'%Y-%m-%d %T') AS fechaVistoBueno"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaProformaTercero,'%Y-%m-%d %T') AS fechaProformaTercero"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaRevalidacion,'%Y-%m-%d') AS fechaRevalidacion"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaPrevio,'%Y-%m-%d %T') AS fechaPrevio"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaPago,'%Y-%m-%d %T') AS fechaPago"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaSolicitudTransfer,'%Y-%m-%d %T') AS fechaSolicitudTransfer"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaArriboTransfer,'%Y-%m-%d %T') AS fechaArriboTransfer"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaInstruccionEspecial,'%Y-%m-%d %T') AS fechaInstruccionEspecial"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaLiberacion,'%Y-%m-%d %T') AS fechaLiberacion"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaEtaAlmacen,'%Y-%m-%d') AS fechaEtaAlmacen"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaFacturacion,'%Y-%m-%d') AS fechaFacturacion"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaComprobacion,'%Y-%m-%d') AS fechaComprobacion"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaEtd,'%Y-%m-%d') AS fechaEtd"),
                    new Zend_Db_Expr("IF (fechaLiberacion IS NOT NULL, DATEDIFF(fechaLiberacion, fechaEntrada), 0) AS diasDespacho"),
                    new Zend_Db_Expr("IF (fechaPago IS NOT NULL, DATEDIFF(fechaPago, fechaEta), 0) AS diasRetraso"),
                    "estatus",
                    "cvePedimento",
                    "regimen",
                    "idUsuario",
                    "observacionSemaforo",
                ))
                ->joinLeft(array("u" => "usuarios"), "t.idUsuario = u.id", array("nombre"))
                ->joinLeft(array("c" => "trafico_clientes"), "c.id = t.idCliente", array("nombre AS nombreCliente"))
                ->joinInner(array("a" => "trafico_aduanas"), "a.id = t.idAduana", array(""))
                ->joinLeft(array("tc" => "trafico_tipocarga"), "tc.id = t.tipoCarga", array("tipoCarga AS carga"))
                ->joinLeft(array("p" => "trafico_clientes_plantas"), "p.id = t.idPlanta", array("descripcion AS descripcionPlanta"))
                ->joinLeft(array("l" => "trafico_almacen"), "l.id = t.almacen", array("nombre AS nombreAlmacen"))
                ->where("t.estatus <> 4")
                ->where("t.pedimento IS NOT NULL")
                ->where("t.creado >= ?", date("Y-m-d", strtotime($fechaInicio)) . ' 00:00:00')
                ->where("t.creado <= ?", date("Y-m-d", strtotime($fechaFin)) . ' 23:59:59')
                ->order(array("patente ASC", "aduana ASC"));
            if (isset($tipoAduana)) {
                if ($tipoAduana == 1) {
                    $sql->where("a.tipoAduana = 1 AND t.cvePedimento IN ('V1', 'G1', 'E1', 'V5', 'F4', 'F5', 'A3')");
                } elseif ($tipoAduana == 2) {
                    $sql->where("a.tipoAduana = 2 OR (a.tipoAduana = 1 AND t.cvePedimento NOT IN ('V1', 'G1', 'E1', 'V5', 'F4', 'F5', 'A3'))");
                } else {
                    $sql->where('a.tipoAduana = ?', $tipoAduana);
                }
            }
            if (isset($rows) && isset($page)) {
                $sql->limit($rows, $rows * ($page - 1));
            }
            if (isset($idCliente)) {
                $sql->where("t.idCliente = ?", $idCliente);
            }
            if (isset($tipoOperacion)) {
                $sql->where("t.ie = ?", $tipoOperacion);
            }
            if ((int) $idAduana != 0) {
                $sql->where("t.idAduana = ?", $idAduana);
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

    protected function _reporteSinfacturar($page, $rows, $idAduana, $fechaInicio, $fechaFin, $filtro = null, $tipoAduana = null)
    {
        try {
            $sql = $this->_db->select()
                ->from(array("t" => "traficos"), array(
                    "id",
                    "patente",
                    "aduana",
                    "pedimento",
                    "referencia",
                    "rfcCliente",
                    "ie",
                    "blGuia",
                    "contenedorCaja",
                    "ordenCompra",
                    "proveedores",
                    "facturas",
                    "cantidadFacturas",
                    "cantidadPartes",
                    "almacen",
                    new Zend_Db_Expr("DATE_FORMAT(fechaEta,'%Y-%m-%d') AS fechaEta"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaNotificacion,'%Y-%m-%d %T') AS fechaNotificacion"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaEnvioDocumentos,'%Y-%m-%d %T') AS fechaEnvioDocumentos"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaEntrada,'%Y-%m-%d') AS fechaEntrada"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaPresentacion,'%Y-%m-%d') AS fechaPresentacion"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaDeposito,'%Y-%m-%d') AS fechaDeposito"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaEnvioProforma,'%Y-%m-%d %T') AS fechaEnvioProforma"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaVistoBueno,'%Y-%m-%d %T') AS fechaVistoBueno"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaProformaTercero,'%Y-%m-%d %T') AS fechaProformaTercero"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaRevalidacion,'%Y-%m-%d') AS fechaRevalidacion"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaPrevio,'%Y-%m-%d %T') AS fechaPrevio"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaPago,'%Y-%m-%d %T') AS fechaPago"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaSolicitudTransfer,'%Y-%m-%d %T') AS fechaSolicitudTransfer"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaArriboTransfer,'%Y-%m-%d %T') AS fechaArriboTransfer"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaInstruccionEspecial,'%Y-%m-%d %T') AS fechaInstruccionEspecial"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaLiberacion,'%Y-%m-%d %T') AS fechaLiberacion"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaEtaAlmacen,'%Y-%m-%d') AS fechaEtaAlmacen"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaFacturacion,'%Y-%m-%d') AS fechaFacturacion"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaComprobacion,'%Y-%m-%d') AS fechaComprobacion"),
                    new Zend_Db_Expr("DATE_FORMAT(fechaEtd,'%Y-%m-%d') AS fechaEtd"),
                    new Zend_Db_Expr("IF (fechaLiberacion IS NOT NULL, DATEDIFF(fechaLiberacion, fechaEntrada), 0) AS diasDespacho"),
                    "estatus",
                    "cvePedimento",
                    "regimen",
                    "idUsuario",
                    "observacionSemaforo",
                ))
                ->joinLeft(array("u" => "usuarios"), "t.idUsuario = u.id", array("nombre"))
                ->joinLeft(array("c" => "trafico_clientes"), "c.id = t.idCliente", array("nombre AS nombreCliente"))
                ->joinInner(array("a" => "trafico_aduanas"), "a.id = t.idAduana", array(""))
                ->joinLeft(array("tc" => "trafico_tipocarga"), "tc.id = t.tipoCarga", array("tipoCarga AS carga"))
                ->joinLeft(array("p" => "trafico_clientes_plantas"), "p.id = t.idPlanta", array("descripcion AS descripcionPlanta"))
                ->joinLeft(array("l" => "trafico_almacen"), "l.id = t.almacen", array("nombre AS nombreAlmacen"))
                ->where("t.fechaLiberacion >= ?", date("Y-m-d H:i:s", strtotime($fechaInicio)))
                ->where("t.fechaLiberacion <= ?", date("Y-m-d H:i:s", strtotime($fechaFin)))
                ->where("t.fechaFacturacion IS NULL")
                ->where("t.estatus <> 4")
                ->where("t.pedimento IS NOT NULL")
                ->order(array("fechaEta DESC"));
            if (isset($tipoAduana)) {
                if ($tipoAduana == 1) {
                    $sql->where("a.tipoAduana = 1 AND t.cvePedimento IN ('V1', 'G1', 'E1', 'V5', 'F4', 'F5', 'A3')");
                } elseif ($tipoAduana == 2) {
                    $sql->where("a.tipoAduana = 2 OR (a.tipoAduana = 1 AND t.cvePedimento NOT IN ('V1', 'G1', 'E1', 'V5', 'F4', 'F5', 'A3'))");
                } else {
                    $sql->where('a.tipoAduana = ?', $tipoAduana);
                }
            }
            if (isset($rows) && isset($page)) {
                $sql->limit($rows, ($page - 1));
            }
            if (isset($filtro)) {
                $this->_filtroReportes($sql, $filtro);
            }
            if ((int) $idAduana != 0) {
                $sql->where("t.idAduana = ?", $idAduana);
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

    protected function _totalReporteInventario($idAduana, $fechaInicio, $fechaFin, $filtro = null)
    {
        try {
            $sql = $this->_db->select()
                ->from(array("t" => "traficos"), array("count(*) AS total"))
                ->where("t.fechaPago >= ?", date("Y-m-d H:i:s", strtotime($fechaInicio)))
                ->where("t.fechaPago <= ?", date("Y-m-d H:i:s", strtotime($fechaFin)))
                ->where("t.estatus <> 4")
                ->where("t.pedimento IS NOT NULL");
            if ((int) $idAduana != 0) {
                $sql->where("t.idAduana = ?", $idAduana);
            }
            if (isset($filtro)) {
                $this->_filtroReportes($sql, $filtro);
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

    protected function _totalReporte($idAduana, $fechaInicio, $fechaFin, $filtro = null, $tipoAduana = null, $idCliente = null)
    {
        try {
            $sql = $this->_db->select()
                ->from(array("t" => "traficos"), array("count(*) AS total"))
                ->joinInner(array("a" => "trafico_aduanas"), "a.id = t.idAduana", array(""))
                ->where("t.estatus <> 4")
                ->where("t.pedimento IS NOT NULL");
            if (isset($idCliente)) {
                $sql->where("t.idCliente = ?", $idCliente);
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
            if ((int) $idAduana != 0) {
                $sql->where("t.idAduana = ?", $idAduana);
            }

            if (isset($filtro)) {
                if ($filtro == 3) {
                    $sql->where("t.fechaPago >= ?", date("Y-m-d H:i:s", strtotime($fechaInicio)))
                        ->where("t.fechaPago <= ?", date("Y-m-d H:i:s", strtotime($fechaFin)))
                        ->where("fechaLiberacion IS NULL");
                } else {
                    $sql->where("t.fechaLiberacion >= ?", date("Y-m-d H:i:s", strtotime($fechaInicio)))
                        ->where("t.fechaLiberacion <= ?", date("Y-m-d H:i:s", strtotime($fechaFin)));
                    $this->_filtroReportes($sql, $filtro);
                }
            } else {
                $sql->where("t.fechaLiberacion >= ?", date("Y-m-d H:i:s", strtotime($fechaInicio)))
                    ->where("t.fechaLiberacion <= ?", date("Y-m-d H:i:s", strtotime($fechaFin)));
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

    public function traficoNuevoAction()
    {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "pedimento" => "Digits",
                );
                $v = array(
                    "pedimento" => array(new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("pedimento")) {
                    $this->_helper->json(array("success" => true));
                } else {
                    $this->_helper->json(array("success" => false, "message" => "Invalid Input!"));
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function reporteTraficosAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "tipoReporte" => "Digits",
                "tipoAduana" => "Digits",
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
                "tipoAduana" => array("NotEmpty", new Zend_Validate_Int()),
                "tipoReporte" => array("NotEmpty", new Zend_Validate_Int()),
                "idAduana" => array("NotEmpty", new Zend_Validate_Int()),
                "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                "fechaInicio" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}-\d{2}-\d{2}$/")),
                "fechaFin" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}-\d{2}-\d{2}$/")),
                "filtro" => array("NotEmpty", new Zend_Validate_Int()),
                "excel" => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("tipoReporte") && $input->isValid("idAduana") && $input->isValid("fechaInicio") && $input->isValid("fechaFin")) {
                $dexcel = filter_var($input->excel, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                $reportes = new OAQ_ExcelReportes();
                if ($input->tipoReporte == 1 || $input->tipoReporte == 5 || $input->tipoReporte == 6 || $input->tipoReporte == 7 || $input->tipoReporte == 8) {
                    if ($dexcel === false) {
                        $rows = $this->_reporte($input->page, $input->rows, $input->idAduana, $input->fechaInicio, $input->fechaFin, $input->filtro, $input->tipoAduana, $input->idCliente);
                        $arr = array(
                            "total" => $this->_totalReporte($input->idAduana, $input->fechaInicio, $input->fechaFin, $input->filtro, $input->tipoAduana, $input->idCliente),
                            "rows" => empty($rows) ? array() : $rows,
                        );
                        $this->_helper->json($arr);
                    } else {
                        $rows = $this->_reporte(null, null, $input->idAduana, $input->fechaInicio, $input->fechaFin, $input->filtro, $input->tipoAduana, $input->idCliente);
                        $reportes->reportesTrafico($input->tipoReporte, $rows);
                    }
                }
                if ($input->tipoReporte == 2) {
                    if ($dexcel === false) {
                        $rows = $this->_reporteCandados($input->page, $input->rows, $input->idAduana, $input->fechaInicio, $input->fechaFin, $input->idCliente);
                        $this->_helper->json($rows);
                    } else {
                        $rows = $this->_reporteCandados(null, null, $input->idAduana, $input->fechaInicio, $input->fechaFin, $input->idCliente);
                        $reportes->reportesTrafico($input->tipoReporte, $rows["rows"]);
                    }
                }
                if ($input->tipoReporte == 3) {
                    $arr = array(
                        "errorMsg" => "Reporte no disponible.",
                    );
                }
                if ($input->tipoReporte == 4) {
                    if ($dexcel === false) {
                        $rows = $this->_reporteIncompletos($input->page, $input->rows, $input->idAduana, $input->fechaInicio, $input->fechaFin, $input->idCliente);
                        $this->_helper->json($rows);
                    } else {
                        $rows = $this->_reporteIncompletos(null, null, $input->idAduana, $input->fechaInicio, $input->fechaFin, $input->idCliente);
                        $reportes->reportesTrafico($input->tipoReporte, $rows["rows"]);
                    }
                }
                if ($input->tipoReporte == 13) {
                    if ($dexcel === true) {
                        $rows = $this->_reporteInventario(null, null, $input->idAduana, $input->fechaInicio, $input->fechaFin, $input->filtro);
                        $reportes->reportesTrafico(80, $rows);
                    }
                }
                if ($input->tipoReporte == 14) {
                    $sica = new OAQ_SicaDb("192.168.200.5", "sa", "adminOAQ123", "SICA", 1433, "SqlSrv");
                    $arr = $sica->facturacion();
                    $reportes->reportesTrafico(14, $arr);
                }
                if ($input->tipoReporte == 70) {
                    $mppr = new Vucem_Model_VucemSolicitudesMapper();
                    $arr = $mppr->reportePorUsuario($input->fechaInicio, $input->fechaFin);
                    $reportes->reportesTrafico(70, $arr);
                }
                if ($input->tipoReporte == 71) {
                    $mppr = new Vucem_Model_VucemEdocMapper();
                    $arr = $mppr->reportePorUsuario($input->fechaInicio, $input->fechaFin);
                    $reportes->reportesTrafico(71, $arr);
                }
                if ($input->tipoReporte == 72) { // reporte indicadores
                    $sql = $this->_reporteIndicadores($input->fechaInicio, $input->fechaFin, $input->idAduana, $input->idCliente);
                    $arr = $this->_db->fetchAll($sql);
                    $reportes->reportesTrafico(72, $arr);
                }
                if ($input->tipoReporte == 73) {
                    $sql = $this->_reporteMvhc($input->fechaInicio, $input->fechaFin, $input->idAduana, $input->idCliente);
                    $arr = $this->_db->fetchAll($sql);
                    $reportes->reportesTrafico(73, $arr);
                }
                if ($input->tipoReporte == 75) {
                    if ($dexcel === true) {
                        $rows = $this->_reporteTraficosFacturacion(null, null, $input->idAduana, $input->fechaInicio, $input->fechaFin, $input->filtro, $input->tipoAduana, $input->idCliente);
                        $reportes->reportesTrafico($input->tipoReporte, $rows);
                    }
                }
                if ($input->tipoReporte == 76) {
                    if ($dexcel === true) {
                        $sql = $this->_reporteEntrega(null, null, $input->fechaInicio, $input->fechaFin, $input->idAduana, $input->idCliente);
                        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($sql));
                        $paginator->setCurrentPageNumber(1);
                        $paginator->setItemCountPerPage(null);
                        $rows = (array) $paginator->getCurrentItems();
                        $reportes->reportesTrafico($input->tipoReporte, $rows);
                    }
                }
                if ($input->tipoReporte == 77) {
                    $mppr = new Trafico_Model_SellosAgentes();
                    $rows = $mppr->reporte();
                    $reportes->reportesTrafico($input->tipoReporte, $rows);
                }
                if ($input->tipoReporte == 78) {
                    $mppr = new Trafico_Model_SellosClientes();
                    $rows = $mppr->reporte();
                    $reportes->reportesTrafico($input->tipoReporte, $rows);
                }
            } else {
                $this->_helper->json(array("success" => false, "message" => "Invalid Input!"));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function reporteTraficosSinfacturarAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "tipoReporte" => "Digits",
                "tipoAduana" => "Digits",
                "idAduana" => "Digits",
                "page" => array("Digits"),
                "rows" => array("Digits"),
                "filtro" => array("Digits"),
                "excel" => array("StringToLower"),
            );
            $v = array(
                "page" => array(new Zend_Validate_Int(), "default" => 1),
                "rows" => array(new Zend_Validate_Int(), "default" => 20),
                "tipoAduana" => array("NotEmpty", new Zend_Validate_Int()),
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
                if ($dexcel == false) {
                    $rows = $this->_reporteSinfacturar($input->page, $input->rows, $input->idAduana, $input->fechaInicio, $input->fechaFin, $input->filtro, $input->tipoAduana);
                    $arr = array(
                        "total" => $this->_totalReporte($input->idAduana, $input->fechaInicio, $input->fechaFin, $input->filtro, $input->tipoAduana),
                        "rows" => empty($rows) ? array() : $rows,
                    );
                    $this->_helper->json($arr);
                } else {
                    $rows = $this->_reporteSinfacturar(null, null, $input->idAduana, $input->fechaInicio, $input->fechaFin, $input->filtro, $input->tipoAduana);
                    $reportes->reportesTrafico(74, $rows);
                }
            } else {
                $this->_helper->json(array("success" => false, "message" => "Invalid Input!"));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function reporteTraficosFacturacionAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "tipoReporte" => "Digits",
                "tipoAduana" => "Digits",
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
                "tipoAduana" => array("NotEmpty", new Zend_Validate_Int()),
                "tipoReporte" => array("NotEmpty", new Zend_Validate_Int()),
                "idAduana" => array("NotEmpty", new Zend_Validate_Int()),
                "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                "fechaInicio" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}-\d{2}-\d{2}$/")),
                "fechaFin" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}-\d{2}-\d{2}$/")),
                "filtro" => array("NotEmpty", new Zend_Validate_Int()),
                "excel" => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("tipoReporte") && $input->isValid("idAduana") && $input->isValid("fechaInicio") && $input->isValid("fechaFin")) {

                $rows = $this->_reporteTraficosFacturacion($input->page, $input->rows,
                    $input->idAduana, $input->fechaInicio, $input->fechaFin, $input->filtro, $input->tipoAduana, $input->idCliente);
                $arr = array(
                    "total" => $this->_totalReporteTraficosFacturacion($input->idAduana, $input->fechaInicio,
                        $input->fechaFin, $input->filtro, $input->tipoAduana, $input->idCliente),
                    "rows" => empty($rows) ? array() : $rows,
                );
                $this->_helper->json($arr);

            } else {
                $this->_helper->json(array("success" => false, "message" => "Invalid Input!"));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function aduanasAction()
    {
        try {
            $sql = $this->_db->select()
                ->from("trafico_aduanas", array("id", "CONCAT(patente, '-', aduana, ' ', nombre) AS aduana"))
                ->where("activo = 1 AND visible = 1")
                ->order(array("patente ASC"));
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                return $this->_helper->json($stmt);
            }
            return;
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function clientesAction()
    {
        try {
            $sql = $this->_db->select()
                ->from("trafico_clientes", array("id", "nombre AS razonSocial"))
                ->where("activo = 1")
                ->order(array("nombre ASC"));
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                return $this->_helper->json($stmt);
            }
            return;
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function tipoCargaAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "patente" => "Digits",
                "aduana" => "Digits",
                "tipoAduana" => "Digits",
            );
            $v = array(
                "patente" => array("NotEmpty", new Zend_Validate_Int()),
                "aduana" => array("NotEmpty", new Zend_Validate_Int()),
                "tipoAduana" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("patente") && $input->isValid("aduana")) {
                $sql = $this->_db->select()
                    ->from(array("t" => "trafico_tipocarga"), array("id", "tipoCarga"))
                    ->where("activo = 1")
                    ->order(array("t.tipoCarga DESC"));
                if ($input->isValid("tipoAduana")) {
                    $sql->where("tipoAduana = ?", $input->tipoAduana);
                } else {
                    $sql->where("tipoAduana = 0");
                }
                $stmt = $this->_db->fetchAll($sql);
                $this->_helper->json($stmt);
            } else {
                $this->_helper->json(array());
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function plantasAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idTrafico" => "Digits",
            );
            $v = array(
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idTrafico")) {
                $mppr = new Trafico_Model_TraficosMapper();
                $arr = $mppr->encabezado($input->idTrafico);
                if (!empty($arr)) {
                    $sql = $this->_db->select()
                        ->from(array("p" => "trafico_clientes_plantas"), array("id", "descripcion"))
                        ->where("idCliente = ?", $arr["idCliente"])
                        ->order(array("descripcion ASC"));
                    $stmt = $this->_db->fetchAll($sql);
                    if (!empty($stmt)) {
                        $this->_helper->json($stmt);
                    } else {
                        $this->_helper->json(array());
                    }
                }
            } else {
                $this->_helper->json(array());
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function almacenesAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "patente" => "Digits",
                "aduana" => "Digits",
            );
            $v = array(
                "patente" => array("NotEmpty", new Zend_Validate_Int()),
                "aduana" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("patente") && $input->isValid("aduana")) {
                $this->_helper->json(array());
            } else {
                $this->_helper->json(array());
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function navierasAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "patente" => "Digits",
                "aduana" => "Digits",
                "idNaviera" => "Digits",
            );
            $v = array(
                "patente" => array("NotEmpty", new Zend_Validate_Int()),
                "aduana" => array("NotEmpty", new Zend_Validate_Int()),
                "idNaviera" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $mapper = new Trafico_Model_NavieraMapper();
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("patente") && $input->isValid("aduana")) {
                $arr = $mapper->obtenerPorAduana($input->patente, $input->aduana);
                if (isset($arr)) {
                    $this->_helper->json($arr);
                }
                $this->_helper->json(array());
            } else {
                if ($input->isValid("idNaviera")) {
                    $arr = $mapper->get($input->idNaviera);
                    $this->_helper->json(array("nombre" => $arr["nombre"]));
                }
                $this->_helper->json(array());
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function traficosInventarioAction()
    {
        try {
            $f = array(
                "fechaIni" => array("StringTrim", "StripTags"),
                "fechaFin" => array("StringTrim", "StripTags"),
                "tipo" => array("StringTrim", "StripTags", "Digits"),
                "idCliente" => array("StringTrim", "StripTags", "Digits"),
                "idAduana" => array("StringTrim", "StripTags", "Digits"),
                "tipoOperacion" => array("StringTrim", "StripTags", "StringToUpper"),
                "excel" => array("StringTrim", "StripTags", "StringToLower"),
            );
            $v = array(
                "fechaIni" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}-\d{2}-\d{2}$/")),
                "fechaFin" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}-\d{2}-\d{2}$/")),
                "tipo" => array("NotEmpty", new Zend_Validate_Int()),
                "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                "idAduana" => array("NotEmpty", new Zend_Validate_Int()),
                "tipoAduana" => array("NotEmpty", new Zend_Validate_Int()),
                "tipoOperacion" => array("NotEmpty"),
                "excel" => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("fechaIni") && $input->isValid("fechaFin") && $input->isValid("tipo") && $input->isValid("tipoAduana")) {
                $dexcel = filter_var($input->excel, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                $view = new Zend_View();

                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $rows = $this->_reporteInventario(null, null, $input->idAduana, $input->fechaIni, $input->fechaFin, 99, $input->tipoAduana, $input->idCliente, $input->tipoOperacion);
                $view->fechaIni = $input->fechaIni;
                $view->fechaFin = $input->fechaFin;
                $view->results = $rows;
                $view->tipo = $input->tipo;
                $view->idCliente = $input->idCliente;
                $view->idAduana = $input->idAduana;
                $view->tipoAduana = $input->tipoAduana;
                $view->tipoOperacion = $input->tipoOperacion;

                $view->url = "/trafico/crud/traficos-inventario";

                if ($dexcel === true) {
                    $reportes = new OAQ_ExcelReportes();
                    $reportes->reportesTrafico($input->tipo, $rows);
                    return;
                }

                $mppr = new Trafico_Model_ClientesMapper();
                $view->clientes = $mppr->obtenerClientes();

                $mpprc = new Trafico_Model_TraficoAduanasMapper();
                $view->aduanas = $mpprc->obtenerTodas();

                echo $view->render("traficos-inventario.phtml");
                return;
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function traficosLiberadosAction()
    {
        try {
            $f = array(
                "fecha" => array("StringTrim", "StripTags"),
                "tipo" => "Digits",
                "idCliente" => array("StringTrim", "StripTags", "Digits"),
                "idAduana" => array("StringTrim", "StripTags", "Digits"),
                "tipoOperacion" => array("StringTrim", "StripTags", "StringToUpper"),
                "excel" => array("StringToLower"),
                "pagados" => array("StringToLower"),
            );
            $v = array(
                "fecha" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}-\d{2}-\d{2}$/")),
                "tipo" => array("NotEmpty", new Zend_Validate_Int()),
                "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                "idAduana" => array("NotEmpty", new Zend_Validate_Int()),
                "tipoOperacion" => array("NotEmpty"),
                "pagados" => array("NotEmpty"),
                "excel" => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("fecha") && $input->isValid("tipo")) {

                $dexcel = filter_var($input->excel, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                $rows = $this->_reporteLiberados($input->idAduana, $input->fecha, $input->tipo, $input->idCliente, $input->tipoOperacion, $input->pagados);
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");

                $view->fecha = $input->fecha;
                $view->results = $rows;
                $view->tipo = $input->tipo;
                $view->idCliente = $input->idCliente;
                $view->idAduana = $input->idAduana;
                $view->tipoOperacion = $input->tipoOperacion;
                $view->pagados = $input->pagados;

                $view->url = "/trafico/crud/traficos-liberados";

                if ($dexcel === true) {
                    $reportes = new OAQ_ExcelReportes();
                    $reportes->reportesTrafico($input->tipo, $rows);
                    return;
                }

                $mppr = new Trafico_Model_ClientesMapper();
                $view->clientes = $mppr->obtenerClientes();

                $mpprc = new Trafico_Model_TraficoAduanasMapper();
                $view->aduanas = $mpprc->obtenerTodas();

                echo $view->render("traficos-liberados.phtml");
                return;
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function traficosAperturadosAction()
    {
        try {
            $f = array(
                "fecha" => array("StringTrim", "StripTags"),
                "tipo" => "Digits",
                "idCliente" => array("StringTrim", "StripTags", "Digits"),
                "idAduana" => array("StringTrim", "StripTags", "Digits"),
                "tipoOperacion" => array("StringTrim", "StripTags", "StringToUpper"),
                "excel" => array("StringToLower"),
            );
            $v = array(
                "fecha" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}-\d{2}-\d{2}$/")),
                "tipo" => array("NotEmpty", new Zend_Validate_Int()),
                "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                "idAduana" => array("NotEmpty", new Zend_Validate_Int()),
                "tipoOperacion" => array("NotEmpty"),
                "excel" => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("fecha") && $input->isValid("tipo")) {
                $dexcel = filter_var($input->excel, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                $rows = $this->_reporteAperturados(null, null, $input->idAduana, $input->fecha, $input->fecha, 99, null, $input->idCliente, $input->tipoOperacion);
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");

                $view->fecha = $input->fecha;
                $view->results = $rows;
                $view->tipo = $input->tipo;
                $view->idCliente = $input->idCliente;
                $view->idAduana = $input->idAduana;
                $view->tipoOperacion = $input->tipoOperacion;

                $view->url = "/trafico/crud/traficos-aperturados";

                if ($dexcel === true) {
                    $reportes = new OAQ_ExcelReportes();
                    $reportes->reportesTrafico($input->tipo, $rows);
                    return;
                }

                $mppr = new Trafico_Model_ClientesMapper();
                $view->clientes = $mppr->obtenerClientes();

                $mpprc = new Trafico_Model_TraficoAduanasMapper();
                $view->aduanas = $mpprc->obtenerTodas();

                echo $view->render("traficos-aperturados.phtml");
                return;
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function _reporteIndicadores($fechaInicio, $fechaFin, $idAduana = null, $idCliente = null)
    {
        try {
            $sql = $this->_db->select()
                ->from(array("t" => "traficos"), array(
                    "t.id",
                    "t.patente",
                    "t.aduana",
                    "t.pedimento",
                    "t.referencia",
                    "t.cvePedimento",
                    "t.ie",
                    "DATE_FORMAT(t.fechaEta,'%Y-%m-%d %T') AS fechaEta",
                    "DATE_FORMAT(t.fechaPago,'%Y-%m-%d %T') AS fechaPago",
                    "DATE_FORMAT(t.fechaLiberacion,'%Y-%m-%d %T') AS fechaLiberacion",
                    "DATE_FORMAT(t.fechaFacturacion,'%Y-%m-%d %T') AS fechaFacturacion",
                    "t.semaforo",
                    "t.observacionSemaforo",
                    "t.observaciones",
                    "t.ccConsolidado",
                ))
                ->joinInner(array("a" => "trafico_aduanas"), "a.id = t.idAduana", array(""))
                ->joinInner(array("ta" => "trafico_tipoaduana"), "ta.id = a.tipoAduana", array("tipoAduana"))
                ->joinLeft(array("l" => "trafico_clientes"), "l.id = t.idCliente", array("nombre AS nombreCliente"))
                ->joinLeft(array("i" => "rpt_cuentas"), "i.idTrafico = t.id", array(""))
                ->joinLeft(array("x" => "repositorio_index"), "x.idTrafico = t.id", array("revisionAdministracion", "revisionOperaciones", "completo"))
                ->where("t.fechaLiberacion >= ?", date("Y-m-d H:i:s", strtotime($fechaInicio)))
                ->where("t.fechaLiberacion <= ?", date("Y-m-d H:i:s", strtotime($fechaFin)))
                ->where("t.estatus = 3")
                ->where("t.pedimento IS NOT NULL")
                ->where("t.idAduana IS NOT NULL");
            if (isset($idCliente)) {
                $sql->where("t.idCliente = ?", $idCliente);
            }
            if (isset($idAduana) && (int) $idAduana) {
                $sql->where("t.idAduana = ?", $idAduana);
            }
            return $sql;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function reporteIndicadoresAction()
    {
        try {
            $f = array(
                "fechaInicio" => array("StringTrim", "StripTags"),
                "fechaFin" => array("StringTrim", "StripTags"),
                "tipoReporte" => array("StringTrim", "StripTags", "Digits"),
                "idAduana" => array("StringTrim", "StripTags", "Digits"),
                "page" => array("Digits"),
                "rows" => array("Digits"),
            );
            $v = array(
                "fechaInicio" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}-\d{2}-\d{2}$/")),
                "fechaFin" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}-\d{2}-\d{2}$/")),
                "tipoReporte" => array("NotEmpty", new Zend_Validate_Int()),
                "idAduana" => array("NotEmpty", new Zend_Validate_Int()),
                "page" => array(new Zend_Validate_Int(), "default" => 1),
                "rows" => array(new Zend_Validate_Int(), "default" => 20),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("fechaInicio") && $input->isValid("fechaFin") && $input->isValid("tipoReporte") && $input->isValid("idAduana")) {
                $sql = $this->_reporteIndicadores($input->fechaInicio, $input->fechaFin, $input->idAduana);
                $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($sql));
                $paginator->setCurrentPageNumber($input->page);
                $paginator->setItemCountPerPage($input->rows);
                $resp = array(
                    "total" => $paginator->getTotalItemCount(),
                    "rows" => (array) $paginator->getCurrentItems(),
                );
                $this->_helper->json($resp);
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function _reporteMvhc($fechaInicio, $fechaFin, $idAduana = null, $idCliente = null)
    {
        try {
            $sql = $this->_db->select()
                ->from(array("r" => "repositorio_index"), array(
                    "*",
                ))
                ->joinLeft(array("c" => "trafico_clientes"), "c.rfc = r.rfcCliente", array("nombre AS nombreCliente"))
                ->where("r.creado >= ?", date("Y-m-d H:i:s", strtotime($fechaInicio)))
                ->where("r.creado <= ?", date("Y-m-d H:i:s", strtotime($fechaFin)));
            if (isset($idCliente)) {
                $sql->where("t.idCliente = ?", $idCliente);
            }
            if (isset($idAduana) && (int) $idAduana !== 0) {
                $sql->where("t.idAduana = ?", $idAduana);
            }
            return $sql;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function reporteEstatusMvhcAction()
    {
        try {
            $f = array(
                "fechaInicio" => array("StringTrim", "StripTags"),
                "fechaFin" => array("StringTrim", "StripTags"),
                "tipoReporte" => array("StringTrim", "StripTags", "Digits"),
                "idAduana" => array("StringTrim", "StripTags", "Digits"),
                "page" => array("Digits"),
                "rows" => array("Digits"),
            );
            $v = array(
                "fechaInicio" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}-\d{2}-\d{2}$/")),
                "fechaFin" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}-\d{2}-\d{2}$/")),
                "tipoReporte" => array("NotEmpty", new Zend_Validate_Int()),
                "idAduana" => array("NotEmpty", new Zend_Validate_Int()),
                "page" => array(new Zend_Validate_Int(), "default" => 1),
                "rows" => array(new Zend_Validate_Int(), "default" => 20),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("fechaInicio") && $input->isValid("fechaFin") && $input->isValid("tipoReporte") && $input->isValid("idAduana")) {
                $sql = $this->_reporteMvhc($input->fechaInicio, $input->fechaFin, $input->idAduana);
                $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($sql));
                $paginator->setCurrentPageNumber($input->page);
                $paginator->setItemCountPerPage($input->rows);
                $resp = array(
                    "total" => $paginator->getTotalItemCount(),
                    "rows" => (array) $paginator->getCurrentItems(),
                );
                $this->_helper->json($resp);
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function _reporteEntrega($page, $rows, $fechaInicio, $fechaFin, $idAduana = null, $idCliente = null)
    {
        try {
            $sql = $this->_db->select()
                ->from(array("r" => "repositorio_index"), array(
                    "t.id",
                    "t.patente",
                    "t.aduana",
                    "t.pedimento",
                    "t.referencia",
                    "t.cvePedimento",
                    "r.revisionAdministracion",
                    "r.revisionOperaciones",
                    "r.completo",
                    "r.mvhcCliente",
                    "r.mvhcFirmada",
                    "DATE_FORMAT(t.fechaPago,'%Y-%m-%d') AS fechaPago",
                ))
                ->joinLeft(array("t" => "traficos"), "t.id = r.idTrafico", array(""))
                ->joinLeft(array("c" => "trafico_clientes"), "c.rfc = r.rfcCliente", array("nombre AS nombreCliente"))
                ->where("r.revisionOperaciones = 1")
                ->where("r.revisionAdministracion IS NULL")
                ->where("r.creado >= ?", date("Y-m-d H:i:s", strtotime($fechaInicio)))
                ->where("r.creado <= ?", date("Y-m-d H:i:s", strtotime($fechaFin)));
            if (isset($idCliente)) {
                $sql->where("t.idCliente = ?", $idCliente);
            }
            if ((int) $idAduana != 0) {
                $sql->where("t.idAduana = ?", $idAduana);
            }
            if (isset($rows) && isset($page)) {
                $sql->limit($rows, $rows * ($page - 1));
            }
            return $sql;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function reporteEntregaAction()
    {
        try {
            $f = array(
                "fechaInicio" => array("StringTrim", "StripTags"),
                "fechaFin" => array("StringTrim", "StripTags"),
                "idAduana" => array("StringTrim", "StripTags", "Digits"),
                "page" => array("Digits"),
                "rows" => array("Digits"),
            );
            $v = array(
                "fechaInicio" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}-\d{2}-\d{2}$/")),
                "fechaFin" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}-\d{2}-\d{2}$/")),
                "idAduana" => array("NotEmpty", new Zend_Validate_Int()),
                "page" => array(new Zend_Validate_Int(), "default" => 1),
                "rows" => array(new Zend_Validate_Int(), "default" => 20),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("fechaInicio") && $input->isValid("fechaFin") && $input->isValid("idAduana")) {
                $sql = $this->_reporteEntrega($input->page, $input->rows, $input->fechaInicio, $input->fechaFin, $input->idAduana);
                $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($sql));
                $paginator->setCurrentPageNumber($input->page);
                $paginator->setItemCountPerPage($input->rows);
                $resp = array(
                    "total" => $paginator->getTotalItemCount(),
                    "rows" => (array) $paginator->getCurrentItems(),
                );
                $this->_helper->json($resp);
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function reporteVucemAction()
    {
        try {
            $f = array(
                "fechaInicio" => array("StringTrim", "StripTags"),
                "fechaFin" => array("StringTrim", "StripTags"),
                "tipoReporte" => array("StringTrim", "StripTags", "Digits"),
                "idAduana" => array("StringTrim", "StripTags", "Digits"),
                "page" => array("Digits"),
                "rows" => array("Digits"),
            );
            $v = array(
                "fechaInicio" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}-\d{2}-\d{2}$/")),
                "fechaFin" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}-\d{2}-\d{2}$/")),
                "tipoReporte" => array("NotEmpty", new Zend_Validate_Int()),
                "idAduana" => array("NotEmpty", new Zend_Validate_Int()),
                "page" => array(new Zend_Validate_Int(), "default" => 1),
                "rows" => array(new Zend_Validate_Int(), "default" => 20),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("fechaInicio") && $input->isValid("fechaFin") && $input->isValid("tipoReporte") && $input->isValid("idAduana")) {
                if ($input->tipoReporte == 70) {
                    $mppr = new Vucem_Model_VucemSolicitudesMapper();
                    $sql = $mppr->reportePorUsuario($input->fechaInicio, $input->fechaFin, true);
                    $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($sql));
                    $paginator->setCurrentPageNumber($input->page);
                    $paginator->setItemCountPerPage($input->rows);
                    $resp = array(
                        "total" => $paginator->getTotalItemCount(),
                        "rows" => (array) $paginator->getCurrentItems(),
                    );
                    $this->_helper->json($resp);
                }
                if ($input->tipoReporte == 71) {
                    $mppr = new Vucem_Model_VucemEdocMapper();
                    $sql = $mppr->reportePorUsuario($input->fechaInicio, $input->fechaFin, true);
                    $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($sql));
                    $paginator->setCurrentPageNumber($input->page);
                    $paginator->setItemCountPerPage($input->rows);
                    $resp = array(
                        "total" => $paginator->getTotalItemCount(),
                        "rows" => (array) $paginator->getCurrentItems(),
                    );
                    $this->_helper->json($resp);
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function facturacionAction()
    {
        try {
            $sica = new OAQ_SicaDb("192.168.200.5", "sa", "adminOAQ123", "SICA", 1433, "SqlSrv");
            $arr = $sica->facturacion();
            if (!empty($arr)) {
                $resp = array(
                    "total" => count($arr),
                    "rows" => $arr,
                );
            } else {
                $resp = array(
                    "total" => 0,
                    "rows" => array(),
                );
            }
            $this->_helper->json($resp);
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function getMenuAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idTrafico" => "Digits",
            );
            $v = array(
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idTrafico")) {
                $sql = $this->_db->select()
                    ->from(array("t" => "traficos"), array(
                        new Zend_Db_Expr("DATE_FORMAT(fechaEta,'%Y-%m-%d') AS fechaEta"),
                        new Zend_Db_Expr("DATE_FORMAT(fechaNotificacion,'%Y-%m-%d %T') AS fechaNotificacion"),
                        new Zend_Db_Expr("DATE_FORMAT(fechaEnvioDocumentos,'%Y-%m-%d %T') AS fechaEnvioDocumentos"),
                        new Zend_Db_Expr("DATE_FORMAT(fechaEntrada,'%Y-%m-%d') AS fechaEntrada"),
                        new Zend_Db_Expr("DATE_FORMAT(fechaPresentacion,'%Y-%m-%d') AS fechaPresentacion"),
                        new Zend_Db_Expr("DATE_FORMAT(fechaInstruccionEspecial,'%Y-%m-%d %T') AS fechaInstruccionEspecial"),
                        new Zend_Db_Expr("DATE_FORMAT(fechaEnvioProforma,'%Y-%m-%d %T') AS fechaEnvioProforma"),
                        new Zend_Db_Expr("DATE_FORMAT(fechaVistoBueno,'%Y-%m-%d %T') AS fechaVistoBueno"),
                        new Zend_Db_Expr("DATE_FORMAT(fechaRevalidacion,'%Y-%m-%d') AS fechaRevalidacion"),
                        new Zend_Db_Expr("DATE_FORMAT(fechaPrevio,'%Y-%m-%d %T') AS fechaPrevio"),
                        new Zend_Db_Expr("DATE_FORMAT(fechaPago,'%Y-%m-%d %T') AS fechaPago"),
                        new Zend_Db_Expr("DATE_FORMAT(fechaLiberacion,'%Y-%m-%d %T') AS fechaLiberacion"),
                    ))
                    ->where("t.id = ?", $input->idTrafico);
                $stmt = $this->_db->fetchRow($sql);
                if ($stmt) {
                    $html = '<ul>';
                    $html .= ($stmt["fechaNotificacion"] == null) ? '<li><a href="javascript:void(0);" onclick="mostrarCalendario(' . $input->idTrafico . ', 9);">Fecha Notificación</a></li>' : '';
                    $html .= ($stmt["fechaEnvioDocumentos"] == null) ? '<li><a href="javascript:void(0);" onclick="mostrarCalendario(' . $input->idTrafico . ', 27);">Fecha Envío de Doctos.</a></li>' : '';
                    $html .= ($stmt["fechaEnvioProforma"] == null) ? '<li><a href="javascript:void(0);" onclick="mostrarCalendario(' . $input->idTrafico . ', 26);">Fecha Proforma</a></li>' : '';
                    $html .= ($stmt["fechaVistoBueno"] == null) ? '<li><a href="javascript:void(0);" onclick="mostrarCalendario(' . $input->idTrafico . ', 29);">Fecha VoBo</a></li>' : '';
                    $html .= ($stmt["fechaRevalidacion"] == null) ? '<li><a href="javascript:void(0);" onclick="mostrarCalendario(' . $input->idTrafico . ', 20);">Fecha Revalidación</a></li>' : '';
                    $html .= ($stmt["fechaPrevio"] == null) ? '<li><a href="javascript:void(0);" onclick="mostrarCalendario(' . $input->idTrafico . ', 21);">Fecha Previo</a></li>' : '';
                    $html .= ($stmt["fechaPago"] == null) ? '<li><a href="javascript:void(0);" onclick="mostrarCalendario(' . $input->idTrafico . ', 2);">Fecha Pago</a></li>' : '';
                    $html .= ($stmt["fechaLiberacion"] == null) ? '<li><a href="javascript:void(0);" onclick="mostrarCalendario(' . $input->idTrafico . ', 8);">Fecha Liberación</a></li>' : '';
                    $html .= '</ul>';
                    echo $html;
                }
                return;
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
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

    protected function _filters(Zend_Db_Select $sql, $filterRules = null, $filtrosCookies = null)
    {
        $referencias = new OAQ_Referencias();
        $res = $referencias->restricciones($this->_session->id, $this->_session->role);
        if ($this->_session->role == "inhouse") {
            $sql->where("t.rfcCliente IN (?)", $res["rfcs"])
                ->where("t.idAduana IN (?)", $res["idsAduana"]);

        } else if (in_array($this->_session->role, array("super", "trafico_operaciones", "trafico", "trafico_aero", "trafico_ejecutivo", "gerente"))) {
            if (!empty($res["idsAduana"])) {
                $sql->where("t.idAduana IN (?)", $res["idsAduana"]);
            }
        }
        if ($this->_session->role == "corresponsal") {
            if (!empty($res["idsAduana"])) {
                $sql->where("t.idAduana IN (?)", $res["idsAduana"]);
            } else {
                $sql->where("t.idAduana IS NULL");
            }
        }
        if (isset($filterRules)) {
            $filter = json_decode(html_entity_decode($filterRules));
            foreach ($filter as $item) {
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
        if (!empty($this->_res["idsAduana"]) && $this->_session->role !== "inhouse") {
            $sql->where("t.idAduana IN (?)", $this->_res["idsAduana"]);
        }
    }

    public function graficaUsuariosAction()
    {
        try {
            $f = array(
                '*' => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags()),
                'year' => array(new Zend_Filter_Digits()),
                'month' => array(new Zend_Filter_Digits()),
                'idCliente' => array(new Zend_Filter_Digits()),
                'idAduana' => array(new Zend_Filter_Digits()),
            );
            $v = array(
                'year' => array('NotEmpty', new Zend_Validate_Int()),
                'month' => array('NotEmpty', new Zend_Validate_Int()),
                'idCliente' => array('NotEmpty', new Zend_Validate_Int()),
                'idAduana' => array('NotEmpty', new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid('year') && $input->isValid('month')) {
                $sql = $this->_db->select()
                    ->from(array("t" => "traficos"), array("count(*) AS y"))
                    ->where("YEAR(t.fechaLiberacion) = ?", $input->year)
                    ->where("MONTH(t.fechaLiberacion) = ?", $input->month)
                    ->where("t.estatus NOT IN (1,2,4)");
                if ($input->isValid('idCliente')) {
                    $sql->where('t.idCliente = ?', $input->idCliente);
                }
                if ($input->isValid('idAduana')) {
                    $sql->where('t.idAduana = ?', $input->idAduana);
                }
                $total = $this->_db->fetchRow($sql);
                if ($total['y'] != 0) {
                    $sql = $this->_db->select()
                        ->from(array("t" => "traficos"), array("count(*) AS y"))
                        ->joinInner(array("u" => "usuarios"), "u.id = t.idUsuario", array("nombre AS name"))
                        ->where("YEAR(t.fechaLiberacion) = ?", $input->year)
                        ->where("MONTH(t.fechaLiberacion) = ?", $input->month)
                        ->where("t.estatus NOT IN (1,2,4)")
                        ->group("u.nombre");
                    if ($input->isValid('idCliente')) {
                        $sql->where('t.idCliente = ?', $input->idCliente);
                    }
                    if ($input->isValid('idAduana')) {
                        $sql->where('t.idAduana = ?', $input->idAduana);
                    }
                    $stmt = $this->_db->fetchAll($sql);
                    if ($stmt) {
                        $arr = array();
                        foreach ($stmt as $value) {
                            $arr[] = array(
                                "name" => $value["name"],
                                "y" => floatval(number_format(($value["y"] / $total["y"]) * 100, 2)),
                            );
                        }
                        $this->_helper->json(array("success" => true, "results" => $arr));
                    }
                }
                $this->_helper->json(array("success" => false));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function graficaCumplimientoAction()
    {
        try {
            $f = array(
                '*' => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags()),
                'year' => array(new Zend_Filter_Digits()),
                'month' => array(new Zend_Filter_Digits()),
                'idCliente' => array(new Zend_Filter_Digits()),
                'idAduana' => array(new Zend_Filter_Digits()),
            );
            $v = array(
                'year' => array('NotEmpty', new Zend_Validate_Int()),
                'month' => array('NotEmpty', new Zend_Validate_Int()),
                'idCliente' => array('NotEmpty', new Zend_Validate_Int()),
                'idAduana' => array('NotEmpty', new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid('year') && $input->isValid('month')) {
                $sql = $this->_db->select()
                    ->from(array("t" => "traficos"), array(
                        new Zend_Db_Expr("SUM(CASE WHEN t.cumplimientoOperativo = 1 THEN 1 ELSE 0 END) AS cumplimientoOperativo"),
                        new Zend_Db_Expr("SUM(CASE WHEN t.cumplimientoOperativo = 0 THEN 1 ELSE 0 END) AS noCumplimientoOperativo"),
                        new Zend_Db_Expr("SUM(CASE WHEN t.cumplimientoOperativo IS NULL AND t.cumplimientoAdministrativo IS NULL THEN 1 ELSE 0 END) AS sinEvaluacion"),
                        new Zend_Db_Expr("count(*) AS total"),
                    ))
                    ->where("YEAR(t.fechaLiberacion) = ?", $input->year)
                    ->where("MONTH(t.fechaLiberacion) = ?", $input->month)
                    ->where("t.estatus NOT IN (1, 2, 4)");
                if ($input->isValid('idCliente')) {
                    $sql->where('t.idCliente = ?', $input->idCliente);
                }
                if ($input->isValid('idAduana')) {
                    $sql->where('t.idAduana = ?', $input->idAduana);
                }
                $stmt = $this->_db->fetchRow($sql);
                if ($stmt) {
                    $arr = array();
                    $total = 0;
                    if (isset($stmt["total"])) {
                        $total = $stmt["total"];
                        unset($stmt["total"]);
                    }
                    foreach ($stmt as $key => $value) {
                        $arr[] = array(
                            "name" => $key,
                            "y" => ((int) $total === 0) ? 0 : floatval(number_format(($value / $total) * 100, 2)),
                        );
                    }
                    $this->_helper->json(array("success" => true, "results" => $arr));
                }
                $this->_helper->json(array("success" => false));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    protected function _semaforos($year, $idCliente, $idAduana)
    {
        $fields = array(
            new Zend_Db_Expr("SUM(CASE WHEN MONTH(fechaLiberacion) = 1 THEN 1 ELSE 0 END) AS Ene"),
            new Zend_Db_Expr("SUM(CASE WHEN MONTH(fechaLiberacion) = 2 THEN 1 ELSE 0 END) AS Feb"),
            new Zend_Db_Expr("SUM(CASE WHEN MONTH(fechaLiberacion) = 3 THEN 1 ELSE 0 END) AS Mar"),
            new Zend_Db_Expr("SUM(CASE WHEN MONTH(fechaLiberacion) = 4 THEN 1 ELSE 0 END) AS Abr"),
            new Zend_Db_Expr("SUM(CASE WHEN MONTH(fechaLiberacion) = 5 THEN 1 ELSE 0 END) AS May"),
            new Zend_Db_Expr("SUM(CASE WHEN MONTH(fechaLiberacion) = 6 THEN 1 ELSE 0 END) AS Jun"),
            new Zend_Db_Expr("SUM(CASE WHEN MONTH(fechaLiberacion) = 7 THEN 1 ELSE 0 END) AS Jul"),
            new Zend_Db_Expr("SUM(CASE WHEN MONTH(fechaLiberacion) = 8 THEN 1 ELSE 0 END) AS Ago"),
            new Zend_Db_Expr("SUM(CASE WHEN MONTH(fechaLiberacion) = 9 THEN 1 ELSE 0 END) AS Sep"),
            new Zend_Db_Expr("SUM(CASE WHEN MONTH(fechaLiberacion) = 10 THEN 1 ELSE 0 END) AS 'Oct'"),
            new Zend_Db_Expr("SUM(CASE WHEN MONTH(fechaLiberacion) = 11 THEN 1 ELSE 0 END) AS Nov"),
            new Zend_Db_Expr("SUM(CASE WHEN MONTH(fechaLiberacion) = 12 THEN 1 ELSE 0 END) AS Dic"),
        );
        $sql = $this->_db->select()
            ->from(array("t" => "traficos"), $fields)
            ->where("YEAR(t.fechaLiberacion) = ?", $year)
            ->where("t.estatus NOT IN (1,2,4)")
            ->where("t.semaforo = 2");
        if ($idCliente) {
            $sql->where('t.idCliente = ?', $idCliente);
        }
        if ($idAduana) {
            $sql->where('t.idAduana = ?', $idAduana);
        }
        $stmt = $this->_db->fetchRow($sql);
        $data = [];
        foreach ($stmt as $k => $value) {
            $data[] = array(
                "name" => $k,
                "y" => (int) $value,
            );
        }
        return $data;
    }

    public function graficaSemaforosAction()
    {
        try {
            $f = array(
                '*' => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags()),
                'year' => array(new Zend_Filter_Digits()),
                'idCliente' => array(new Zend_Filter_Digits()),
                'idAduana' => array(new Zend_Filter_Digits()),
            );
            $v = array(
                'year' => array('NotEmpty', new Zend_Validate_Int()),
                'idCliente' => array('NotEmpty', new Zend_Validate_Int()),
                'idAduana' => array('NotEmpty', new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid('year')) {
                //$data = $this->_semaforos($input->year, $input->idCliente, $input->idAduana);

                $arr = array(
                    $this->_semaforos($input->year - 1, $input->idCliente, $input->idAduana),
                    $this->_semaforos($input->year, $input->idCliente, $input->idAduana),
                );

                $this->_helper->json(array("success" => true, "data" => $arr));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    protected function _reporteTraficosFacturacion($page, $rows, $idAduana, $fechaInicio, $fechaFin, $filtro = null, $tipoAduana = null, $idCliente = null)
    {
        try {
            $sql = $this->_db->select()
                ->from(array("t" => "traficos"), array(
                    "t.id",
                    "t.patente",
                    "t.aduana",
                    "t.pedimento",
                    "t.referencia",
                    "t.cvePedimento",
                    "t.rfcCliente",
                    "t.ie",
                    "t.blGuia",
                    "t.contenedorCaja",
                    "(CASE WHEN a.tipoAduana = 3 THEN t.nombreBuque ELSE 'N/A' END) AS nombreBuque",
                    "i.folio",
                    "DATE_FORMAT(i.fechaFacturacion,'%Y-%m-%d') AS fechaFacturacion",
                    "DATE_FORMAT(i.fechaPago,'%Y-%m-%d') AS fechaPago",
                    "i.honorarios",
                    "i.iva",
                    "i.subTotal",
                    "(SELECT sum(c.importe) FROM rpt_cuenta_conceptos AS c WHERE c.idCuenta = i.id AND c.tipo = 'C') AS pagoHechos",
                    "(SELECT sum(c.importe) FROM rpt_cuenta_conceptos AS c WHERE c.idCuenta = i.id AND c.tipo = 'S') AS sinComprobar",
                    "i.pagada",
                ))
                ->joinLeft(array("u" => "usuarios"), "t.idUsuario = u.id", array("nombre"))
                ->joinLeft(array("c" => "trafico_clientes"), "c.id = t.idCliente", array("nombre AS nombreCliente"))
                ->joinInner(array("i" => "rpt_cuentas"), "i.idTrafico = t.id", array(""))
                ->joinInner(array("a" => "trafico_aduanas"), "a.id = t.idAduana", array(""))
                ->where("i.fechaFacturacion >= ?", date("Y-m-d H:i:s", strtotime($fechaInicio)))
                ->where("i.fechaFacturacion <= ?", date("Y-m-d H:i:s", strtotime($fechaFin)))
                ->where("t.estatus <> 4")
                ->where("i.cancelada IS NULL")
                ->where("i.pagada IS NULL")
                ->order(array("t.patente ASC", "t.aduana ASC"));
            if (isset($idCliente)) {
                $sql->where("t.idCliente = ?", $idCliente);
            }
            if ((int) $idAduana != 0) {
                $sql->where("t.idAduana = ?", $idAduana);
            }
            if (isset($rows) && isset($page)) {
                $sql->limit($rows, $rows * ($page - 1));
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

    protected function _totalReporteTraficosFacturacion($idAduana, $fechaInicio, $fechaFin, $filtro = null, $tipoAduana = null, $idCliente = null)
    {
        try {
            $sql = $this->_db->select()
                ->from(array("t" => "traficos"), array(
                    "t.id",
                ))
                ->joinInner(array("i" => "rpt_cuentas"), "i.idTrafico = t.id", array(""))
                ->where("i.fechaFacturacion >= ?", date("Y-m-d H:i:s", strtotime($fechaInicio)))
                ->where("i.fechaFacturacion <= ?", date("Y-m-d H:i:s", strtotime($fechaFin)))
                ->where("t.estatus <> 4")
                ->where('i.cancelada IS NULL')
                ->where('i.pagada IS NULL');
            if (isset($idCliente)) {
                $sql->where("t.idCliente = ?", $idCliente);
            }
            if ((int) $idAduana != 0) {
                $sql->where("t.idAduana = ?", $idAduana);
            }
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                return count($stmt);
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function sellosAction()
    {
        try {
            $f = array(
                "tipoReporte" => array("StringTrim", "StripTags", "Digits"),
                "page" => array("Digits"),
                "rows" => array("Digits"),
            );
            $v = array(
                "tipoReporte" => array("NotEmpty", new Zend_Validate_Int()),
                "page" => array(new Zend_Validate_Int(), "default" => 1),
                "rows" => array(new Zend_Validate_Int(), "default" => 20),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("tipoReporte")) {
                if ($input->tipoReporte == 77) {
                    $mppr = new Trafico_Model_SellosAgentes();
                    $sql = $mppr->reporte(true);
                    $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($sql));
                    $paginator->setCurrentPageNumber($input->page);
                    $paginator->setItemCountPerPage($input->rows);
                    $resp = array(
                        "total" => $paginator->getTotalItemCount(),
                        "rows" => (array) $paginator->getCurrentItems(),
                    );
                    $this->_helper->json($resp);
                }
                if ($input->tipoReporte == 78) {
                    $mppr = new Trafico_Model_SellosClientes();
                    $sql = $mppr->reporte(true);
                    $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($sql));
                    $paginator->setCurrentPageNumber($input->page);
                    $paginator->setItemCountPerPage($input->rows);
                    $resp = array(
                        "total" => $paginator->getTotalItemCount(),
                        "rows" => (array) $paginator->getCurrentItems(),
                    );
                    $this->_helper->json($resp);
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function contarCovesEdocumentsAction()
    {
        try {
            $f = array(
                "idTrafico" => array("StringTrim", "StripTags", "Digits"),
            );
            $v = array(
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idTrafico")) {
                $traffic = new OAQ_Trafico(array("idTrafico" => $input->idTrafico));
                $arr = $traffic->contarCovesEdocuments();
                $this->_helper->json(array("success" => true, "cantidad" => $arr));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

}
