<?php

class Manifestacion_PostController extends Zend_Controller_Action
{
    protected $_session;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;
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

    public function actualizarAction()
    {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => "Digits",
                );
                $v = array(
                    "id" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty())
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("id")) {
                    $this->_helper->json(array("success" => true));
                } else {
                    throw new Exception("Invalid input.");
                }
            } else {
                throw new Exception("Invalid request type.");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function actualizaEdocumentAction()
    {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => "Digits",
                    "chk" => "StringToLower",
                );
                $v = array(
                    "id" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty()),
                    "chk" => array("NotEmpty", new Zend_Validate_InArray(array("true", "false"))),
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("id")) {
                    $chk = filter_var($i->chk, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

                    $mppr = new Manifestacion_Model_ManifestacionEdocuments();
                    $arr = array(
                        "usar" => ($chk == true) ? 1 : null,
                        "actualizado" => date("Y-m-d H:i:s")
                    );

                    if ($mppr->actualizar($i->id, $arr)) {
                        $this->_helper->json(array("success" => true));
                    }
                } else {
                    throw new Exception("Invalid input.");
                }
            } else {
                throw new Exception("Invalid request type.");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function agregarRfcConsultaAction()
    {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => "Digits",
                    "rfc" => "StringToUpper",
                );
                $v = array(
                    "id" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty()),
                    "rfc" => new Zend_Validate_NotEmpty(),
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("id") && $i->isValid("rfc")) {
                    $mppr = new Manifestacion_Model_ManifestacionRfcConsulta();
                    $v = $mppr->verificar($i->id, $i->rfc);
                    if (!$v) {
                        $arr = array(
                            "idManifestacion" => $i->id,
                            "rfc" => $i->rfc,
                        );
                        if ($mppr->agregar($arr)) {
                            $this->_helper->json(array("success" => true));
                        }
                    } else {
                        $this->_helper->json(array("success" => false, "message" => "El RFC de consulta ya existe."));
                    }
                } else {
                    throw new Exception("Invalid input.");
                }
            } else {
                throw new Exception("Invalid request type.");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function agregarEdocumentAction()
    {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => "Digits",
                    "edocument" => "StringToUpper",
                );
                $v = array(
                    "id" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty()),
                    "edocument" => new Zend_Validate_NotEmpty(),
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("id") && $i->isValid("edocument")) {
                    $mppr = new Manifestacion_Model_ManifestacionEdocuments();
                    $v = $mppr->verificar($i->id, $i->edocument);
                    if (!$v) {
                        if ($mppr->agregar($i->id, $i->edocument)) {
                            $this->_helper->json(array("success" => true));
                        }
                    } else {
                        $this->_helper->json(array("success" => false, "message" => "El Edocument ya existe."));
                    }
                } else {
                    throw new Exception("Invalid input.");
                }
            } else {
                throw new Exception("Invalid request type.");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function nuevaAction()
    {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idAduana" => "Digits",
                    "idCliente" => "Digits",
                    "tipoOperacion" => "StringToUpper",
                    "cvePedimento" => "StringToUpper",
                    "referencia" => "StringToUpper",
                    "pedimento" => "Digits",
                );
                $v = array(
                    "idAduana" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty()),
                    "idCliente" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty()),
                    "tipoOperacion" => new Zend_Validate_NotEmpty(),
                    "cvePedimento" => new Zend_Validate_NotEmpty(),
                    "referencia" => new Zend_Validate_NotEmpty(),
                    "pedimento" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty()),
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("idAduana") && $i->isValid("idCliente")) {
                    $man = new Manifestacion_Trafico();
                    $id =  $man->nueva($i->idAduana, $i->idCliente, $i->tipoOperacion, $i->cvePedimento, $i->pedimento, $i->referencia);
                    if ($id) {
                        $this->_helper->json(array("success" => true, "id" => $id));
                    } else {
                        throw new Exception("No pudo agregarse.");
                    }
                } else {
                    throw new Exception("Invalid input.");
                }
            } else {
                throw new Exception("Invalid request type.");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function guardarEdocumentAction()
    {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $this->_helper->json(array("success" => true));
            } else {
                throw new Exception("Invalid request type.");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
}
