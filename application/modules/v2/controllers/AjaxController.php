<?php

class V2_AjaxController extends Zend_Controller_Action {

    protected $_session;
    protected $_appconfig;
    protected $_config;

    public function init() {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
        }
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $contextSwitch = $this->_helper->getHelper("contextSwitch");
        $contextSwitch->initContext();
    }

    public function preDispatch() {
        $this->_session = NULL ? $this->_session = new Zend_Session_Namespace("") : $this->_session = new Zend_Session_Namespace($this->_config->app->namespace);
        if ($this->_session->authenticated == true) {
            $this->_session->setExpirationSeconds($this->_appconfig->getParam("session-exp"));
        } else {
            $this->_session = false;
        }
    }

    public function obtenerReferenciasTemporalesAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idUsuario" => "Digits",
            );
            $v = array(
                "idUsuario" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $i = new Zend_Filter_Input($f, $v, $this->_request->getPost());
            if ($i->isValid("idUsuario")) {
                $html = new V2_Html();
                $mapper = new V2_Model_Trafico_Clientes();
                $tmp = new V2_Model_Trafico_TraficoTmp();
                $arr = $tmp->referenciasTemporales($i->idUsuario);
                if(isset($arr) && !empty($arr)) {
                    foreach ($arr as $value) {
                        $html->nuevosTraficos($value["referencia"], $mapper->rfcCliente($value["idCliente"]), $value["tipoOperacion"]);
                    }
                    $this->_helper->json(array("success" => true, "html" => $html->getHtml()));
                }
                $this->_helper->json(array("success" => false));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

}
