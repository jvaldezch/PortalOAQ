<?php

class Pedimento_GetController extends Zend_Controller_Action
{

    protected $_session;
    protected $_config;
    protected $_appconfig;
    protected $_firephp;

    public function init()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        try {
            $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        } catch (Zend_Config_Exception $e) {
        }
        $this->_logger = Zend_Registry::get("logDb");
        $this->_firephp = Zend_Registry::get("firephp");
    }

    public function preDispatch()
    {
        try {
            $this->_session = NULL ? $this->_session = new Zend_Session_Namespace("") :
                $this->_session = new Zend_Session_Namespace($this->_config->app->namespace);
        } catch (Zend_Session_Exception $e) {
        }
        if ($this->_session->authenticated == true) {
            $session = new OAQ_Session($this->_session, $this->_appconfig);
            $session->actualizar();
            $session->actualizarSesion();
        } else {
            $this->getResponse()->setRedirect($this->_appconfig->getParam("link-logout"));
        }
    }

    public function agruparPartidasAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idPedimento" => "Digits",
                "idTrafico" => "Digits",
            );
            $v = array(
                "idPedimento" => array("NotEmpty", new Zend_Validate_Int()),
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idPedimento") && $input->isValid("idTrafico")) {
                $pedimento = new OAQ_TraficoPedimento(array("idTrafico" => $input->idTrafico));
                $trafico = new OAQ_Trafico(array("idTrafico" => $input->idTrafico, "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));

                $partidas = $trafico->obtenerProductosPartidas(true);

                $tipoCambio = 0;
                $row = $trafico->obtenerDatos();
                if ($row['id']) {
                    $d = $pedimento->detalle();
                    if (!empty($d)) {
                        $tipoCambio = $d['tipoCambio'];
                    }
                }

                $invoices = $pedimento->procesarProductos($input->idPedimento, $tipoCambio, $partidas);

                $this->_helper->json(array("success" => true));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function cargarPartidasAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idPedimento" => "Digits",
                "idTrafico" => "Digits",
            );
            $v = array(
                "idPedimento" => array("NotEmpty", new Zend_Validate_Int()),
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idPedimento") && $input->isValid("idTrafico")) {

                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");

                $pedimento = new OAQ_TraficoPedimento(array("idTrafico" => $input->idTrafico));
                $trafico = new OAQ_Trafico(array("idTrafico" => $input->idTrafico, "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));

                $partidas_fact = $trafico->obtenerProductosPartidas();

                $tipoCambio = 0;
                $row = $trafico->obtenerDatos();
                if ($row['id']) {
                    $d = $pedimento->detalle();
                    if (!empty($d)) {
                        $tipoCambio = $d['tipoCambio'];
                    }
                }

                $view->partidas = $partidas_fact;
                $view->tipoCambio = $tipoCambio;

                $this->_helper->json(array("success" => true, "html" => $view->render("partidas-facturas.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function configuracionPartidasAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idPedimento" => "Digits",
                "idTrafico" => "Digits",
            );
            $v = array(
                "idPedimento" => array("NotEmpty", new Zend_Validate_Int()),
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idPedimento") && $input->isValid("idTrafico")) {

                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");

                $pedimento = new OAQ_TraficoPedimento(array("idTrafico" => $input->idTrafico));
                $trafico = new OAQ_Trafico(array("idTrafico" => $input->idTrafico, "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));

                $this->_helper->json(array("success" => true, "html" => $view->render("configuracion-partidas.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function recargarPartidasAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idPedimento" => "Digits",
                "idTrafico" => "Digits",
                "group" => array("StringToLower"),
            );
            $v = array(
                "idPedimento" => array("NotEmpty", new Zend_Validate_Int()),
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                "group" => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idPedimento") && $input->isValid("idTrafico")) {
                $group = filter_var($input->group, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

                $pedimento = new OAQ_TraficoPedimento(array("idTrafico" => $input->idTrafico));
                $trafico = new OAQ_Trafico(array("idTrafico" => $input->idTrafico, "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));

                $partidas_fact = $trafico->obtenerProductosPartidas();

                $row = $trafico->obtenerDatos();
                $tipoCambio = 0;
                if ($row['id']) {
                    $d = $pedimento->detalle();
                    if (!empty($d)) {
                        $tipoCambio = $d['tipoCambio'];
                    }
                }

                $partidas = $pedimento->procesarProductos($row['id'], $tipoCambio, $partidas_fact);
                $this->_helper->json(array("success" => true, "results" => $partidas));

            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
}
