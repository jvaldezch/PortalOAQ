<?php

class Manifestacion_GetController extends Zend_Controller_Action
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
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $this->_logger = Zend_Registry::get("logDb");
        $this->_firephp = Zend_Registry::get("firephp");
    }

    public function preDispatch()
    {
        $this->_session = NULL ? $this->_session = new Zend_Session_Namespace("") : $this->_session = new Zend_Session_Namespace($this->_config->app->namespace);
        if ($this->_session->authenticated == true) {
            $session = new OAQ_Session($this->_session, $this->_appconfig);
            $session->actualizar();
            $session->actualizarSesion();
        } else {
            $this->getResponse()->setRedirect($this->_appconfig->getParam("link-logout"));
        }
    }

    public function todasAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "page" => array("Digits"),
                "rows" => array("Digits"),
            );
            $v = array(
                "page" => array(new Zend_Validate_Int(), "default" => 1),
                "rows" => array(new Zend_Validate_Int(), "default" => 20),
            );
            $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            $manifestaciones = new Manifestacion_Trafico();
            $resp = $manifestaciones->todas($i->page, $i->rows);
            $this->_helper->json($resp);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function traficoAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idAduana" => "Digits",
                "referencia" => "StringToUpper",
            );
            $v = array(                
                "idAduana" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty()),
                "referencia" => new Zend_Validate_NotEmpty(),
            );
            $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($i->isValid("idAduana") && $i->isValid("referencia")) {
                $man = new Manifestacion_Trafico();
                $resp = $man->datosTrafico($i->idAduana, $i->referencia);
                if ($resp) {
                    $this->_helper->json(array("success" => true, "result" => $resp));
                } else {
                    $this->_helper->json(array("success" => false));
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function edocumentsTraficoAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => "Digits",
                "patente" => "Digits",
                "aduana" => "Digits",
                "pedimento" => "Digits",
                "referencia" => "StringToUpper",
            );
            $v = array(                
                "id" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty()),
                "patente" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty()),
                "aduana" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty()),
                "pedimento" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty()),
                "referencia" => new Zend_Validate_NotEmpty(),
            );
            $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($i->isValid("id") && $i->isValid("patente") && $i->isValid("aduana") && $i->isValid("pedimento") && $i->isValid("referencia")) {
                $man = new Manifestacion_Trafico();
                $resp = $man->edocumentsTrafico($i->patente, $i->aduana, $i->pedimento, $i->referencia);
                if ($resp) {
                    foreach ($resp as $edoc) {
                        $man->agregarEdocument($i->id, $edoc['edocument']);
                    }
                    $this->_helper->json(array("success" => true, "results" => $resp));
                } else {
                    $this->_helper->json(array("success" => false));
                }                
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function edocumentsAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => "Digits",
            );
            $v = array(                
                "id" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty()),
            );
            $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($i->isValid("id")) {
                $man = new Manifestacion_Trafico();
                $resp = $man->edocuments($i->id);
                if ($resp) {
                    $this->_helper->json(array("success" => true, "results" => $resp));
                } else {
                    $this->_helper->json(array("success" => false));
                }                
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function rfcConsultaAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => "Digits",
            );
            $v = array(                
                "id" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty()),
            );
            $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($i->isValid("id")) {
                $man = new Manifestacion_Trafico();
                $resp = $man->rfcConsulta($i->id);
                if ($resp) {
                    $this->_helper->json(array("success" => true, "results" => $resp));
                } else {
                    $this->_helper->json(array("success" => false));
                }                
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function datosEdocumentAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => "Digits",
                "edocument" => "StringToUpper",
            );
            $v = array(                
                "id" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty()),
                "edocument" => new Zend_Validate_NotEmpty(),
            );
            $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($i->isValid("id") && $i->isValid("edocument")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");              

                $fp = new Manifestacion_Model_ManifestacionFormasPago();
                $inc = new Manifestacion_Model_ManifestacionTipoIncrementables();
                $dec = new Manifestacion_Model_ManifestacionTipoDecrementables();
                $mv = new Manifestacion_Model_ManifestacionMetodosValoracion();

                $view->id = $i->id;
                $view->edocument = $i->edocument;
                $view->formas_pago = $fp->todos();
                $view->tipo_incrementables = $inc->todos();
                $view->tipo_decrementables = $dec->todos();
                $view->metodos_valoracion = $mv->todos();

                $this->_helper->json(array("success" => true, "html" => $view->render("datos-cove.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
}
