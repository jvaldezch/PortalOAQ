<?php

class Trafico_PedimentosController extends Zend_Controller_Action {

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

}
