<?php

class Trafico_PedimentosController extends Zend_Controller_Action {

    protected $_session;
    protected $_config;
    protected $_appconfig;
    protected $_firephp;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
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

    /**
     * /trafico/pedimentos/descarga?idTrafico=42677
     * 
     */
    public function descargaAction() {
        try {
            $f = array(
                "idTrafico" => array("StringTrim", "StripTags", "StringToUpper"),
            );
            $v = array(
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idTrafico")) {
                $trafico = new OAQ_Trafico(array("idTrafico" => $input->idTrafico, "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));
                if (($res = $trafico->descargaPedimento($trafico->getIdCliente())) === true) {
                    $this->_helper->json(array("success" => true));
                } else {
                    if (($res = $trafico->descargaPedimento(null, $trafico->getPatente())) === true) {
                        $this->_helper->json(array("success" => true));
                    }
                    $this->_helper->json(array("success" => false, "message" => $res));
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function capturaPedimentoAction() {
        try {
            $f = array(
                "id" => array("StringTrim", "StripTags", "StringToUpper"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {

                $pedimento = new OAQ_TraficoPedimento(array("idTrafico" => $input->id));
                $row = $pedimento->buscar($this->_session->username);

                $cvemppr = new Trafico_Model_CvePedimentos();

                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/pedimentos/");
                $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");

                $view->idTrafico = $input->id;
                $view->idPedimento = $row['id'];

                $trafico = new OAQ_Trafico(array("idTrafico" => $input->id, "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));

                $view->patente = $trafico->getPatente();
                $view->aduana = $trafico->getAduana();
                $view->pedimento = $trafico->getPedimento();
                $view->tipoOperacion = $trafico->getTipoOperacion();

                $view->cves = $cvemppr->obtenerClaves();

                $row = $trafico->obtenerDatos();

                $cust = $trafico->obtenerCliente();

                $view->cvePedimento = $row['cvePedimento'];
                $view->rfcCliente = $row['rfcCliente'];
                $view->nomCliente = $cust['nombre'];

                $medios = new Pedimento_Model_MedioTransporte();
                $view->medios = $medios->obtenerTodos();

                $destinos = new Pedimento_Model_MedioTransporte();
                $view->destinos = $destinos->obtenerTodos();

                $a_despacho = new Pedimento_Model_AduanasDespacho();
                $view->a_despacho = $a_despacho->obtenerTodos();

                $paises = new Pedimento_Model_Paises();
                $view->paises = $paises->obtenerTodos();

                $this->_helper->json(array("success" => true, "html" => $view->render("captura-pedimento.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

}
