<?php

class Bitacora_PostController extends Zend_Controller_Action {

    protected $_session;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;

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

    public function agregarAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "patente" => "Digits",
                    "aduana" => "Digits",
                    "pedimento" => "Digits",
                    "referencia" => "StringToUpper",
                    "blGuia" => "StringToUpper",
                    "tipoOperacion" => "StringToUpper",
                    "clavePedimento" => "StringToUpper",
                    "observaciones" => "StringToUpper",
                );
                $v = array(
                    "patente" => array("NotEmpty", new Zend_Validate_Int()),
                    "aduana" => array("NotEmpty", new Zend_Validate_Int()),
                    "pedimento" => array("NotEmpty", new Zend_Validate_Int()),
                    "referencia" => "NotEmpty",
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                    "blGuia" => "NotEmpty",
                    "tipoOperacion" => "NotEmpty",
                    "clavePedimento" => "NotEmpty",
                    "observaciones" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("blGuia")) {
                    $mppr = new Bitacora_Model_BitacoraPedimentos();
                    $arr = array(
                        "patente" => $input->patente,
                        "aduana" => $input->aduana,
                        "pedimento" => $input->pedimento,
                        "referencia" => $input->referencia,
                        "estatus" => 1,
                        "tipoOperacion" => isset($input->tipoOperacion) ? $input->tipoOperacion : null,
                        "clavePedimento" => $input->clavePedimento,
                        "blGuia" => $input->blGuia,
                        "observaciones" => $input->observaciones,
                        "nombreCliente" => $input->nombreCliente,
                        "fechaNotificacion" => date("Y-m-d H:i:s"),
                        "creado" => date("Y-m-d H:i:s"),
                        "creadoPor" => $this->_session->username,
                    );
                    if ($input->isValid("idCliente")) {
                        $mpprc = new Trafico_Model_ClientesMapper();
                        $arr["idCliente"] = $input->idCliente;
                        if (($array = $mpprc->datosCliente($input->idCliente))) {
                            $arr["nombreCliente"] = $array["nombre"];
                            $arr["rfcCliente"] = $array["rfc"];
                        }
                    }
                    if ($mppr->agregar($arr)) {
                        $this->_helper->json(array("success" => true));
                    }
                } else {
                    $this->_helper->json(array("success" => false, "message" => "Invalid Input!"));
                }
            }
        } catch (Exception $e) {
            $this->_helper->json(array("success" => false, "message" => $e->getMessage()));
        }
    }

    public function actualizarGuiaAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => "Digits",
                    "numeroFotos" => "Digits",
                    "completa" => "Digits",
                    "averia" => "Digits",
                    "numeroPiezas" => "Digits",
                    "observaciones" => "StringToUpper",
                    "nombreProveedor" => "StringToUpper",
                    "paisOrigen" => "StringToUpper",
                    "modelo" => "StringToUpper",
                    "marca" => "StringToUpper",
                    "numeroParte" => "StringToUpper",
                    "numeroSerie" => "StringToUpper",
                    "selloFiscal" => "StringToUpper",
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "numeroFotos" => array("NotEmpty", new Zend_Validate_Int()),
                    "completa" => array("NotEmpty", new Zend_Validate_Int()),
                    "averia" => array("NotEmpty", new Zend_Validate_Int()),
                    "numeroPiezas" => array("NotEmpty", new Zend_Validate_Int()),
                    "observaciones" => "NotEmpty",
                    "nombreProveedor" => "NotEmpty",
                    "fechaEta" => "NotEmpty",
                    "fechaColocacion" => "NotEmpty",
                    "fechaApertura" => "NotEmpty",
                    "pesoBruto" => "NotEmpty",
                    "paisOrigen" => "NotEmpty",
                    "modelo" => "NotEmpty",
                    "marca" => "NotEmpty",
                    "numeroParte" => "NotEmpty",
                    "numeroSerie" => "NotEmpty",
                    "selloFiscal" => "NotEmpty",
                    "fechaEta" => "NotEmpty",
                    "fechaColocacion" => "NotEmpty",
                    "fechaApertura" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id")) {
                    $mppr = new Bitacora_Model_BitacoraPedimentos();
                    $arr = array(
                        "nombreProveedor" => $input->nombreProveedor,
                        "observaciones" => $input->observaciones,
                        "pesoBruto" => $input->pesoBruto,
                        "paisOrigen" => $input->paisOrigen,
                        "completa" => $input->completa,
                        "averia" => $input->averia,
                        "numeroFotos" => $input->numeroFotos,
                        "modelo" => $input->modelo,
                        "marca" => $input->marca,
                        "numeroParte" => $input->numeroParte,
                        "numeroSerie" => $input->numeroSerie,
                        "numeroPiezas" => $input->numeroPiezas,
                        "fechaEta" => $input->fechaEta,
                        "fechaColocacion" => $input->fechaColocacion,
                        "fechaApertura" => $input->fechaApertura,
                        "selloFiscal" => $input->selloFiscal,
                        "actualizado" => date("Y-m-d H:i:s"),
                        "actualizadoPor" => $this->_session->username,
                    );
                    if ($mppr->actualizarGuia($input->id, $arr)) {
                        $this->_helper->json(array("success" => true));
                    }
                } else {
                    $this->_helper->json(array("success" => false, "message" => "Invalid Input!"));
                }
            }
        } catch (Exception $e) {
            $this->_helper->json(array("success" => false, "message" => $e->getMessage()));
        }
    }

    public function actualizarAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => "Digits",
                    "idCliente" => "Digits",
                    "patente" => "Digits",
                    "aduana" => "Digits",
                    "pedimento" => "Digits",
                    "estatus" => "Digits",
                    "referencia" => "StringToUpper",
                    "blGuia" => "StringToUpper",
                    "tipoOperacion" => "StringToUpper",
                    "clavePedimento" => "StringToUpper",
                    "observaciones" => "StringToUpper",
                    "nombreCliente" => "StringToUpper",
                    "nombreProveedor" => "StringToUpper",
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                    "nombreCliente" => array("NotEmpty", new Zend_Validate_Int()),
                    "patente" => array("NotEmpty", new Zend_Validate_Int()),
                    "aduana" => array("NotEmpty", new Zend_Validate_Int()),
                    "pedimento" => array("NotEmpty", new Zend_Validate_Int()),
                    "estatus" => array("NotEmpty", new Zend_Validate_Int()),
                    "referencia" => "NotEmpty",
                    "blGuia" => "NotEmpty",
                    "tipoOperacion" => "NotEmpty",
                    "clavePedimento" => "NotEmpty",
                    "observaciones" => "NotEmpty",
                    "nombreCliente" => "NotEmpty",
                    "nombreProveedor" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id")) {
                    $mppr = new Bitacora_Model_BitacoraPedimentos();
                    $arr = array(
                        "patente" => $input->patente,
                        "aduana" => $input->aduana,
                        "pedimento" => $input->pedimento,
                        "referencia" => $input->referencia,
                        "estatus" => $input->estatus,
                        "blGuia" => $input->blGuia,
                        "tipoOperacion" => $input->tipoOperacion,
                        "clavePedimento" => $input->clavePedimento,
                        "nombreProveedor" => $input->nombreProveedor,
                        "observaciones" => $input->observaciones,
                        "nombreCliente" => $input->nombreCliente,
                        "actualizado" => date("Y-m-d H:i:s"),
                        "actualizadoPor" => $this->_session->username,
                    );
                    if ($input->isValid("idCliente")) {
                        $mpprc = new Trafico_Model_ClientesMapper();
                        $arr["idCliente"] = $input->idCliente;
                        if (($array = $mpprc->datosCliente($input->idCliente))) {
                            $arr["idCliente"] = $array["idCliente"];
                            $arr["nombreCliente"] = $array["nombre"];
                            $arr["rfcCliente"] = $array["rfc"];
                        }
                    }
                    if ($mppr->actualizar($input->id, $arr)) {
                        $this->_helper->json(array("success" => true));
                    }
                } else {
                    $this->_helper->json(array("success" => false, "message" => "Invalid Input!"));
                }
            }
        } catch (Exception $e) {
            $this->_helper->json(array("success" => false, "message" => $e->getMessage()));
        }
    }

    public function agruparAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "ids" => "Digits",
                );
                $v = array(
                    "ids" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("ids")) {
                    if (is_array($input->ids)) {
                        $error = false;
                        $mppr = new Bitacora_Model_BitacoraPedimentos();
                        foreach ($input->ids as $k => $v) {
                            $arr = $mppr->obtenerDatos($v);
                            if ($arr["idTrafico"] == null) {
                                if (!isset($patente) && !isset($aduana)) {
                                    $patente = (int) $arr["patente"];
                                    $aduana = (int) $arr["aduana"];
                                } else {
                                    if ((int) $arr["patente"] != $patente || (int) $arr["aduana"] != $aduana) {
                                        $error = true;
                                        $message = "Los registros no pertecen a la misma aduana.";
                                    }
                                }
                            } else {
                                $error = true;
                                $message = "No se puede agrupar un registro con trafico.";
                            }
                        }
                        if ($error == true) {
                            $this->_helper->json(array("success" => false, "message" => $message));
                        } else {
                            $arr = $mppr->obtenerDatos((int) max($input->ids));
                            if (!empty($arr)) {
                                $blGuia = "";
                                $observaciones = "";
                                $idCliente = null;
                                $nombreCliente = null;
                                $rfcCliente = null;
                                foreach ($input->ids as $k => $v) {
                                    $arrayo = $mppr->obtenerDatos($v);
                                    if (!isset($referencia) && !isset($pedimento)) {
                                        if (isset($arrayo["referencia"]) && isset($arrayo["pedimento"])) {
                                            $referencia = $arrayo["referencia"];
                                            $pedimento = $arrayo["pedimento"];
                                        }
                                    }
                                    if ($arrayo["observaciones"]) {
                                        $observaciones .= $arrayo["observaciones"] . ", ";
                                    }
                                    if ($arrayo["blGuia"]) {
                                        $blGuia .= $arrayo["blGuia"] . ", ";
                                    }
                                    if ($arrayo["idCliente"]) {
                                        $idCliente = $arrayo["idCliente"];
                                        $nombreCliente = $arrayo["nombreCliente"];
                                        $rfcCliente = $arrayo["rfcCliente"];
                                    }
                                }
                                $array = array(
                                    "patente" => $arr["patente"],
                                    "aduana" => $arr["aduana"],
                                    "pedimento" => isset($pedimento) ? $pedimento : null,
                                    "referencia" => isset($referencia) ? $referencia : null,
                                    "blGuia" => ($blGuia != "") ? substr($blGuia, 0, -2) : null,
                                    "tipoOperacion" => $arr["tipoOperacion"],
                                    "clavePedimento" => $arr["clavePedimento"],
                                    "observaciones" => ($observaciones != "") ? substr($observaciones, 0, -2) : null,
                                    "idCliente" => $idCliente,
                                    "nombreCliente" => $nombreCliente,
                                    "rfcCliente" => $rfcCliente,
                                    "estatus" => 1,
                                    "agrupados" => json_encode($input->ids),
                                    "creado" => date("Y-m-d H:i:s"),
                                    "creadoPor" => $this->_session->username,
                                );
                                if ($mppr->agregar($array)) {
                                    $arr = array(
                                    );
                                    foreach ($input->ids as $k => $v) {
                                        $mppr->limpiar($v);
                                    }
                                    $this->_helper->json(array("success" => true));
                                }
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $this->_helper->json(array("success" => false, "message" => $e->getMessage()));
        }
    }

    public function borrarAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => "Digits",
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id")) {
                    $mppr = new Bitacora_Model_BitacoraPedimentos();
                    if ($mppr->borrar($input->id)) {
                        $this->_helper->json(array("success" => true));
                    }
                    $this->_helper->json(array("success" => false, "message" => "Unable to delete!"));
                } else {
                    $this->_helper->json(array("success" => false, "message" => "Invalid Input!"));
                }
            }
        } catch (Exception $e) {
            $this->_helper->json(array("success" => false, "message" => $e->getMessage()));
        }
    }

}
