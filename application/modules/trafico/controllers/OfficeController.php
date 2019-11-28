<?php

class Trafico_OfficeController extends Zend_Controller_Action {

    protected $_session;
    protected $_config;
    protected $_appconfig;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
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

    public function obtenerContactosAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $ftr = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                );
                $vdr = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($ftr, $vdr, $request->getPost());
                if ($input->isValid("id")) {
                    $view = new Zend_View();
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/office/");
                    $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");
                    $mapper = new Trafico_Model_ContactosMapper();
                    $arr = $mapper->obtener($input->id);
                    if (isset($arr) && !empty($arr)) {
                        $view->data = $arr;
                        $this->_helper->json(array("success" => true, "html" => $view->render("obtener-contactos.phtml")));
                    } else {
                        $this->_helper->json(array("success" => true, "html" => $view->render("obtener-contactos.phtml")));
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

    public function obtenerClientesAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $ftr = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                );
                $vdr = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($ftr, $vdr, $request->getPost());
                if ($input->isValid("id")) {
                    $view = new Zend_View();
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/office/");
                    $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");
                    $mapper = new Trafico_Model_TraficoCliAduanasMapper();
                    $arr = $mapper->clientesAduana($input->id);
                    if (isset($arr) && !empty($arr)) {
                        $view->data = $arr;
                        $this->_helper->json(array("success" => true, "html" => $view->render("obtener-clientes.phtml")));
                    }
                    $this->_helper->json(array("success" => false, "html" => $view->render("obtener-clientes.phtml")));
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

    public function obtenerAlmacenesAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $ftr = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                );
                $vdr = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($ftr, $vdr, $request->getPost());
                if ($input->isValid("id")) {
                    $view = new Zend_View();
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/office/");
                    $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");
                    $mapper = new Trafico_Model_AlmacenMapper();
                    $arr = $mapper->obtener($input->id);
                    if (isset($arr) && !empty($arr)) {
                        $view->data = $arr;
                        $this->_helper->json(array("success" => true, "html" => $view->render("obtener-almacenes.phtml")));
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

    public function obtenerTransporteAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $ftr = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                );
                $vdr = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($ftr, $vdr, $request->getPost());
                if ($input->isValid("id")) {
                    $view = new Zend_View();
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/office/");
                    $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");
                    $mapper = new Trafico_Model_TransporteMapper();
                    $arr = $mapper->obtener($input->id);
                    if (isset($arr) && !empty($arr)) {
                        $view->data = $arr;
                        $this->_helper->json(array("success" => true, "html" => $view->render("obtener-transporte.phtml")));
                    } else {
                        $this->_helper->json(array("success" => true, "html" => $view->render("obtener-transporte.phtml")));
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

    public function obtenerNavierasAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $ftr = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                );
                $vdr = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($ftr, $vdr, $request->getPost());
                if ($input->isValid("id")) {
                    $view = new Zend_View();
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/office/");
                    $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");
                    $mapper = new Trafico_Model_NavieraMapper();
                    $arr = $mapper->obtener($input->id);
                    if (isset($arr) && !empty($arr)) {
                        $view->data = $arr;
                        $this->_helper->json(array("success" => true, "html" => $view->render("obtener-navieras.phtml")));
                    } else {
                        $this->_helper->json(array("success" => true, "html" => $view->render("obtener-navieras.phtml")));
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

    public function obtenerConceptosAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $ftr = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                );
                $vdr = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($ftr, $vdr, $request->getPost());
                if ($input->isValid("id")) {
                    $view = new Zend_View();
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/office/");
                    $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");
                    $mapper = new Trafico_Model_TraficoConceptosMapper();
                    $arr = $mapper->obtenerConCuentas($input->id);
                    if (isset($arr) && !empty($arr)) {
                        $view->id = $input->id;
                        $view->data = $arr;
                        $this->_helper->json(array("success" => true, "html" => $view->render("obtener-conceptos.phtml")));
                    } else {
                        $this->_helper->json(array("success" => true, "html" => $view->render("obtener-conceptos.phtml")));
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

    public function obtenerBancosAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $ftr = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                );
                $vdr = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($ftr, $vdr, $request->getPost());
                if ($input->isValid("id")) {
                    $view = new Zend_View();
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/office/");
                    $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");
                    $mapper = new Trafico_Model_TraficoBancosMapper();
                    $arr = $mapper->obtenerTodos($input->id);
                    $banks = new Trafico_Model_TraficoBancosMapper();
                    $view->bancoDefault = $banks->obtenerBancoDefault($input->id);
                    if (isset($arr) && !empty($arr)) {
                        $view->data = $arr;
                        $view->id = $input->id;
                        $this->_helper->json(array("success" => true, "html" => $view->render("obtener-bancos.phtml")));
                    } else {
                        $this->_helper->json(array("success" => true, "html" => $view->render("obtener-bancos.phtml")));
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
    
    public function cambiarBancoAction() {
        try {
            $r = $this->getRequest();
            if ($r->isPost()) {
                $ftr = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idAduana" => array("Digits"),
                    "idBanco" => array("Digits"),
                );
                $vdr = array(
                    "idAduana" => array("NotEmpty", new Zend_Validate_Int()),
                    "idBanco" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($ftr, $vdr, $r->getPost());
                if ($input->isValid("idAduana") && $input->isValid("idBanco")) {
                    $mapper = new Trafico_Model_TraficoBancosMapper();
                    if($mapper->establecerDefault($input->idBanco, $input->idAduana)) {
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

}
