<?php

class V2_PostController extends Zend_Controller_Action {

    protected $_session;
    protected $_appconfig;
    protected $_config;
    protected $_request;

    public function init() {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
        }
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $contextSwitch = $this->_helper->getHelper("contextSwitch");
        $contextSwitch->addActionContext("usuarios-clientes", "json")
                ->addActionContext("nuevo-trafico", "json")
                ->initContext();
    }

    public function preDispatch() {
        $this->_session = NULL ? $this->_session = new Zend_Session_Namespace("") : $this->_session = new Zend_Session_Namespace($this->_config->app->namespace);
        if ($this->_session->authenticated == true) {
            $this->_session->setExpirationSeconds($this->_appconfig->getParam("session-exp"));
        } else {
            $this->_session = false;
        }
        $this->_request = $this->getRequest();
        if (!$this->_request->isPost()) {
            throw new Exception("Invalid request type!");
        }
    }

    public function usuarioClientesAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idUsuario" => "Digits",
                "idAduana" => "Digits",
            );
            $v = array(
                "idUsuario" => array("NotEmpty", new Zend_Validate_Int()),
                "idAduana" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $i = new Zend_Filter_Input($f, $v, $this->_request->getPost());
            if ($i->isValid("idUsuario") && $i->isValid("idAduana")) {
                $mapper = new V2_Model_Trafico_UsuarioClientes();
                $arr = $mapper->obtenerClientes($i->idUsuario, $i->idAduana);
                if (isset($arr) && !empty($arr)) {
                    $this->_helper->json(array("success" => true, "array" => $arr));
                } else {
                    $this->_helper->json(array("success" => false, "message" => "El usuario no tiene clientes asignados para esa aduana."));
                }
                $this->_helper->json(array("success" => false));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function nuevoTraficoAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idUsuario" => "Digits",
                "idAduana" => "Digits",
                "idCliente" => "Digits",
                "more" => "Digits",
                "cvePedimento" => "StringToUpper",
                "tipoOperacion" => "Digits",
                "pedimento" => "Digits",
                "referencia" => "StringToUpper",
            );
            $v = array(
                "idUsuario" => array("NotEmpty", new Zend_Validate_Int()),
                "idAduana" => array("NotEmpty", new Zend_Validate_Int()),
                "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                "more" => array("NotEmpty", new Zend_Validate_Int()),
                "cvePedimento" => array("NotEmpty"),
                "tipoOperacion" => array("NotEmpty", new Zend_Validate_Int()),
                "pedimento" => array("NotEmpty", new Zend_Validate_Int()),
                "referencia" => array("NotEmpty", new Zend_Validate_Regex("/[-a-zA-Z0-9\d]/")),
            );
            $i = new Zend_Filter_Input($f, $v, $this->_request->getPost());
            if ($i->isValid("idUsuario") && $i->isValid("idAduana") && $i->isValid("idCliente") && $i->isValid("cvePedimento") && $i->isValid("tipoOperacion") && $i->isValid("pedimento") && $i->isValid("referencia")) {
                if ($i->isValid("more")) {
                    $tmp = new V2_Model_Trafico_TraficoTmp();
                    if ($i->more == 1) {
                        $table = new V2_Model_Trafico_Table_TraficoTmp(array(
                            "idAduana" => $i->idAduana,
                            "idCliente" => $i->idCliente,
                            "idUsuario" => $i->idUsuario,
                            "pedimento" => $i->pedimento,
                            "referencia" => $i->referencia,
                            "tipoOperacion" => $i->tipoOperacion,
                            "cvePedimento" => $i->cvePedimento,
                            "consolidado" => 0,
                            "rectificacion" => 0,
                            "creado" => date("Y-m-d H:i:s"),
                        ));
                        $tmp->guardar($table);
                    } else {
                        for ($r = 0; $r < $i->more; $r++) {
                            $table = new V2_Model_Trafico_Table_TraficoTmp(array(
                                "idAduana" => $i->idAduana,
                                "idCliente" => $i->idCliente,
                                "idUsuario" => $i->idUsuario,
                                "pedimento" => $i->pedimento + $r,
                                "referencia" => $this->_obtenerReferencia($i->idAduana, $i->pedimento + $r, $i->tipoOperacion),
                                "tipoOperacion" => $i->tipoOperacion,
                                "cvePedimento" => $i->cvePedimento,
                                "consolidado" => 0,
                                "rectificacion" => 0,
                                "creado" => date("Y-m-d H:i:s"),
                            ));
                            $tmp->guardar($table);
                            unset($table);
                        }
                    }
                }
                $this->_helper->json(array("success" => true));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerReferenciaAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idAduana" => "Digits",
                "pedimento" => "Digits",
                "tipoOperacion" => "Digits",
            );
            $v = array(
                "idAduana" => array("NotEmpty", new Zend_Validate_Int()),
                "pedimento" => array("NotEmpty", new Zend_Validate_Int()),
                "tipoOperacion" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $i = new Zend_Filter_Input($f, $v, $this->_request->getPost());
            if ($i->isValid("idAduana") && $i->isValid("tipoOperacion") && $i->isValid("pedimento")) {                
                $this->_helper->json(array("success" => true, "referencia" => $this->_obtenerReferencia($i->idAduana, $i->pedimento, $i->tipoOperacion)));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    protected function _obtenerReferencia($idAduana, $pedimento, $tipoOperacion) {
        if ($idAduana == 1) {
            return "Q" . date("y") . substr($pedimento, 1, 6);
        } elseif ($idAduana == 7) {
            if ($tipoOperacion == 1) {
                return date("y") . "TQ" . substr($pedimento, 1, 6);
            } else {
                return date("y") . "ME-" . substr($pedimento, 1, 6);
            }
        }
    }

}
