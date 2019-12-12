<?php

class Trafico_AjaxController extends Zend_Controller_Action {

    protected $_session;
    protected $_svucem;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;
    protected $_logger;
    protected $_rolesEditarTrafico;
    protected $_todosClientes;

    public function init() {
        $this->_helper->layout()->disableLayout();  
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $this->_logger = Zend_Registry::get("logDb");
        $contextSwitch = $this->_helper->getHelper("contextSwitch");
        $contextSwitch->addActionContext("recent-coves", "json")
                ->addActionContext("borrar-solicitud-cove", "json")
                ->initContext();
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
        $this->_svucem = NULL ? $this->_svucem = new Zend_Session_Namespace("") : $this->_svucem = new Zend_Session_Namespace("OAQVucem");
        $this->_svucem->setExpirationSeconds($this->_appconfig->getParam("session-exp"));
        $this->_rolesEditarTrafico = array("trafico", "super", "trafico_operaciones", "trafico_aero");
        $this->_todosClientes = array("trafico", "super", "trafico_operaciones", "trafico_aero");
    }

    public function nuevoClienteAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $filters = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idEmpresa" => array("Digits"),
                    "rfc" => array("StringToUpper"),
                    "rfcSociedad" => array("StringToUpper"),
                    "nombre" => array("StringToUpper"),
                );
                $validators = array(
                    "idEmpresa" => array("NotEmpty", new Zend_Validate_Int()),
                    "rfc" => array("NotEmpty", new Zend_Validate_Regex("/^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/")),
                    "rfcSociedad" => array("NotEmpty", new Zend_Validate_Regex("/^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/")),
                    "nombre" => array(new Zend_Validate_Regex("/^[-_a-zA-Z0-9ÑñÁÉÍÓÚáéíóú.,& ]+$/"), new Zend_Validate_StringLength(array("min" => 1, "max" => 254))),
                );
                $input = new Zend_Filter_Input($filters, $validators, $request->getPost());
                if ($input->isValid("idEmpresa") && $input->isValid("rfc") && $input->isValid("nombre")) {
                    $model = new Trafico_Model_ClientesMapper();
                    if (!($model->buscar($input->rfc))) {
                        $data = array(
                            "idEmpresa" => $input->idEmpresa,
                            "rfc" => $input->rfc,
                            "rfcSociedad" => html_entity_decode($input->rfcSociedad),
                            "nombre" => $input->nombre,
                            "creado" => date("Y-m-d H:i:s"),
                            "usuario" => $this->_session->username,
                        );
                        $added = $model->nuevoCliente($data);
                        if ($added === true) {
                            $this->_helper->json(array("success" => true));
                        } else {
                            $this->_helper->json(array("success" => false, "message" => "Ups algo ocurrio."));
                        }
                    } else {
                        $this->_helper->json(array("success" => false, "message" => "<p style=\"color: white; background: red\">El RFC del cliente ya existe en la base de datos de Trafico.</p>"));
                    }
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

    public function removerConceptoAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost();
                $filters = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idCliente" => "Digits",
                    "idConcepto" => "Digits",
                );
                $validators = array(
                    "*" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($filters, $validators, $post);
                if ($input->isValid()) {
                    $data = $input->getEscaped();
                    $tbl = new Trafico_Model_TraficoConceptosMapper();
                    if ($tbl->remover($data["idAduana"], $data["idConcepto"])) {
                        $this->_helper->json(array("success" => true));
                    } else {
                        $this->_helper->json(array("success" => false, "message" => "No se puede borrar."));
                    }
                } else {
                    $this->_helper->json(array("success" => false, "message" => "Invalid data."));
                }
            } else {
                $this->_helper->json(array("success" => false, "message" => "No data recieved."));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function removerClienteAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost();
                $filters = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idCliente" => "Digits",
                    "idConcepto" => "Digits",
                );
                $validators = array(
                    "*" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($filters, $validators, $post);
                if ($input->isValid()) {
                    $data = $input->getEscaped();
                    $model = new Trafico_Model_TraficoCliAduanasMapper();
                    if ($model->remover($data["idAduana"], $data["idCliente"])) {
                        $this->_helper->json(array("success" => true));
                    }
                } else {
                    $this->_helper->json(array("success" => false, "message" => "Invalid data."));
                }
            } else {
                $this->_helper->json(array("success" => false, "message" => "No data recieved."));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function removerAlmacenAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost();
                $filters = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idCliente" => "Digits",
                    "idConcepto" => "Digits",
                );
                $validators = array(
                    "*" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($filters, $validators, $post);
                if ($input->isValid()) {
                    $data = $input->getEscaped();
                    $mdl = new Trafico_Model_AlmacenMapper();
                    if ($mdl->desactivar($data["idAlmacen"], $data["idAduana"])) {
                        $this->_helper->json(array("success" => true));
                    }
                } else {
                    $this->_helper->json(array("success" => false, "message" => "Invalid data."));
                }
            } else {
                $this->_helper->json(array("success" => false, "message" => "No data recieved."));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function removerContactoAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $flt = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                );
                $vdr = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($flt, $vdr, $request->getPost());
                if ($input->isValid("id")) {
                    $mapper = new Trafico_Model_ContactosMapper();
                    if ($mapper->delete($input->id)) {
                        $this->_helper->json(array("success" => true));
                    } else {
                        $this->_helper->json(array("success" => false));
                    }
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

    public function removerContactoClienteAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $flt = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                );
                $vdr = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($flt, $vdr, $request->getPost());
                if ($input->isValid("id")) {
                    $mapper = new Trafico_Model_ContactosCliMapper();
                    if ($mapper->delete($input->id)) {
                        $this->_helper->json(array("success" => true));
                    } else {
                        $this->_helper->json(array("success" => false));
                    }
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

    public function removerTransporteAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost();
                $filters = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idCliente" => "Digits",
                    "idConcepto" => "Digits",
                );
                $validators = array(
                    "*" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($filters, $validators, $post);
                if ($input->isValid()) {
                    $data = $input->getEscaped();
                    $mdl = new Trafico_Model_TransporteMapper();
                    if ($mdl->desactivar($data["idTransporte"], $data["idAduana"])) {
                        $this->_helper->json(array("success" => true));
                    }
                } else {
                    $this->_helper->json(array("success" => false, "message" => "Invalid data."));
                }
            } else {
                $this->_helper->json(array("success" => false, "message" => "No data recieved."));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function removerBancoAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost();
                $filters = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idAduana" => "Digits",
                    "idBanco" => "Digits",
                );
                $validators = array(
                    "*" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($filters, $validators, $post);
                if ($input->isValid()) {
                    $data = $input->getEscaped();
                } else {
                    $this->_helper->json(array("success" => false, "message" => "Invalid data."));
                }
            } else {
                $this->_helper->json(array("success" => false, "message" => "No data recieved."));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function datosBancoAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost();
                $filters = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idAduana" => "Digits",
                    "idBanco" => "Digits",
                );
                $validators = array(
                    "*" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($filters, $validators, $post);
                if ($input->isValid()) {
                    $data = $input->getEscaped();
                    $banks = new Trafico_Model_TraficoBancosMapper();
                    $info = $banks->obtenerBanco($data["idBanco"]);
                    if (isset($info) && !empty($info)) {
                        $json = array(
                            "success" => true,
                            "idBanco" => $info["id"],
                            "descripcion" => $info["descripcion"],
                            "nombreBanco" => $info["nombre"],
                            "cuenta" => $info["cuenta"],
                            "clabe" => $info["clabe"],
                            "sucursal" => $info["sucursal"],
                            "razonSocial" => $info["razonSocial"],
                        );
                        $this->_helper->json($json);
                    }
                } else {
                    $this->_helper->json(array("success" => false, "message" => "Invalid data."));
                }
            } else {
                $this->_helper->json(array("success" => false, "message" => "No data recieved."));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function cambiarOrdenAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost();
                $filters = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idConcepto" => "Digits",
                );
                $validators = array(
                    "*" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($filters, $validators, $post);
                if ($input->isValid()) {
                    $data = $input->getEscaped();
                    $concepts = new Trafico_Model_TraficoConceptosMapper();
                    if ($concepts->actualizarOrden($data["idConcepto"], $data["orden"])) {
                        $this->_helper->json(array("success" => true));
                    }
                } else {
                    $this->_helper->json(array("success" => false, "message" => "Invalid data."));
                }
            } else {
                $this->_helper->json(array("success" => false, "message" => "No data recieved."));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function actualizarBancoAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost();
                $filters = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idAduana" => "Digits",
                    "idBanco" => "Digits",
                );
                $validators = array(
                    "*" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($filters, $validators, $post);
                if ($input->isValid()) {
                    $data = $input->getEscaped();
                    $banks = new Trafico_Model_TraficoBancosMapper();
                    $row = array(
                        "descripcion" => $data["descripcion"],
                        "nombre" => $data["nombreBanco"],
                        "cuenta" => $data["cuenta"],
                        "clabe" => $data["clabe"],
                        "sucursal" => $data["sucursal"],
                        "razonSocial" => $data["razonSocial"],
                        "modificado" => date("Y-m-d H:i:s"),
                        "modificadoPor" => $this->_session->username,
                    );
                    if ($banks->actualizar($data["idBanco"], $row)) {
                        $this->_helper->json(array("success" => true));
                    }
                } else {
                    $this->_helper->json(array("success" => false, "message" => "Invalid data."));
                }
            } else {
                $this->_helper->json(array("success" => false, "message" => "No data recieved."));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function removerNavieraAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost();
                $filters = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idCliente" => "Digits",
                    "idConcepto" => "Digits",
                );
                $validators = array(
                    "*" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($filters, $validators, $post);
                if ($input->isValid()) {
                    $data = $input->getEscaped();
                    $mdl = new Trafico_Model_NavieraMapper();
                    if ($mdl->desactivar($data["idNaviera"], $data["idAduana"])) {
                        $this->_helper->json(array("success" => true));
                    }
                } else {
                    $this->_helper->json(array("success" => false, "message" => "Invalid data."));
                }
            } else {
                $this->_helper->json(array("success" => false, "message" => "No data recieved."));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function nuevoAlmacenAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost();
                $model = new Trafico_Model_AlmacenMapper();
                if (!($model->buscar($post["idAduana"], $post["nombreAlmacen"]))) {
                    $data = array(
                        "idAduana" => $post["idAduana"],
                        "nombre" => filter_var(trim($post["nombreAlmacen"])),
                        "creado" => date("Y-m-d Y:i:s"),
                        "usuario" => $this->_session->username,
                    );
                    $added = $model->agregar($data);
                    if ($added === true) {
                        $this->_helper->json(array("success" => true));
                    }
                }
                $this->_helper->json(array("success" => false));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function nuevaNavieraAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost();
                $filters = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idAduana" => "Digits",
                    "nombreNaviera" => "StringToUpper"
                );
                $validators = array(
                    "*" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($filters, $validators, $post);
                if ($input->isValid()) {
                    $data = $input->getEscaped();
                    $model = new Trafico_Model_NavieraMapper();
                    if (!($model->buscar($data["idAduana"], $data["nombreNaviera"]))) {
                        $insert = array(
                            "idAduana" => $data["idAduana"],
                            "nombre" => filter_var(trim($data["nombreNaviera"])),
                            "creado" => date("Y-m-d Y:i:s"),
                            "usuario" => $this->_session->username,
                        );
                        $added = $model->agregar($insert);
                        if ($added === true) {
                            $this->_helper->json(array("success" => true));
                        }
                    }
                } else {
                    $this->_helper->json(array("success" => false, "message" => "Invalid data."));
                }
            } else {
                $this->_helper->json(array("success" => false, "message" => "No data received."));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function nuevoBancoAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idAduana" => "Digits",
                    "cuenta" => "Digits",
                    "clabe" => "Digits",
                );
                $v = array(
                    "*" => "NotEmpty",
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("idAduana") && $i->isValid("nombreBanco") && $i->isValid("cuenta")) {
                    $mapper = new Trafico_Model_TraficoBancosMapper();
                    if (($mapper->verificar($i->idAduana, $i->nombreBanco, $i->cuenta)) === false) {
                        $arr = array(
                            "idAduana" => $i->idAduana,
                            "descripcion" => $i->descripcion,
                            "nombre" => $i->nombreBanco,
                            "cuenta" => $i->cuenta,
                            "sucursal" => $i->sucursal,
                            "clabe" => $i->clabe,
                            "razonSocial" => $i->razonSocial,
                            "activo" => 1,
                            "default" => 0,
                            "creado" => date("Y-m-d H:i:s"),
                            "creadoPor" => $this->_session->username,
                        );
                        if (($mapper->agregar($arr)) === true) {
                            $this->_helper->json(array("success" => true));
                        }
                        $this->_helper->json(array("success" => false, "message" => "Not added"));
                    }
                    $this->_helper->json(array("success" => false, "message" => "Bank already exists"));
                } else {
                    $this->_helper->json(array("success" => false, "message" => "Invalid data."));
                }
            } else {
                $this->_helper->json(array("success" => false, "message" => "No data received."));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function nuevoTransporteAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost();
                $model = new Trafico_Model_TransporteMapper();
                if (!($model->buscar($post["idAduana"], $post["nombreTransporte"]))) {
                    $data = array(
                        "idAduana" => $post["idAduana"],
                        "nombre" => filter_var(trim($post["nombreTransporte"])),
                        "creado" => date("Y-m-d Y:i:s"),
                        "usuario" => $this->_session->username,
                    );
                    $added = $model->agregar($data);
                    if ($added === true) {
                        $this->_helper->json(array("success" => true));
                    }
                }
                $this->_helper->json(array("success" => false));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function agregarClienteAduanaAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost();
                $model = new Trafico_Model_TraficoCliAduanasMapper();
                if (!($model->verificar($post["idAduana"], $post["idCliente"]))) {
                    $data = array(
                        "idAduana" => $post["idAduana"],
                        "idCliente" => $post["idCliente"],
                        "creado" => date("Y-m-d H:i:s"),
                        "usuario" => $this->_session->username,
                    );
                    $added = $model->agregar($data);
                    if ($added === true) {
                        $this->_helper->json(array("success" => true));
                    }
                }
                $this->_helper->json(array("success" => false));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function agregarContactoAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $flt = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idAduana" => array("Digits"),
                    "tipoContacto" => array("Digits"),
                    "email" => array("StringToLower"),
                    "nombre" => array("StringToLower", new OAQ_Filter_Ucfirst()),
                );
                $vdr = array(
                    "idAduana" => array("NotEmpty", new Zend_Validate_Int()),
                    "tipoContacto" => array("NotEmpty", new Zend_Validate_Int()),
                    "email" => array("NotEmpty", new Zend_Validate_EmailAddress()),
                    "nombre" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($flt, $vdr, $request->getPost());
                if ($input->isValid("idAduana") && $input->isValid("tipoContacto") && $input->isValid("email")) {
                    $mapper = new Trafico_Model_ContactosMapper();
                    $tbl = new Trafico_Model_Table_Contactos($input->getEscaped());
                    $tbl->setCreacion(1);
                    $tbl->setComentario(1);
                    $tbl->setCancelacion(1);
                    $tbl->setDeposito(1);
                    $tbl->setHabilitado(1);
                    $tbl->setCreado(date("Y-m-d H:i:s"));
                    $tbl->setCreadoPor($this->_session->username);
                    $mapper->find($tbl);
                    if (null === ($tbl->getId())) {
                        $mapper->save($tbl);
                    }
                    $this->_helper->json(array("success" => true));
                } else {
                    throw new Exception("Invalid input!");
                }
                $this->_helper->json(array("success" => false));
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function nuevaFacturacionAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost();
                $model = new Trafico_Model_TraficoTipoFacturacionMapper();
                if (!($model->verificar($post["idAduana"], $post["idCliente"], $post["nombre"]))) {
                    $data = array(
                        "idAduana" => $post["idAduana"],
                        "idCliente" => $post["idCliente"],
                        "nombre" => $post["nombre"],
                        "rfc" => $post["rfc"],
                        "creado" => date("Y-m-d Y:i:s"),
                        "usuario" => $this->_session->username,
                    );
                    $added = $model->agregar($data);
                    if ($added === true) {
                        $this->_helper->json(array("success" => true));
                    }
                }
                $this->_helper->json(array("success" => false));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function borrarSolicitudAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $mdl = new Trafico_Model_TraficoSolicitudesMapper();
                $log = new Trafico_Model_BitacoraMapper;
                $filters = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idSolicitud" => "Digits",
                    "comentario" => "StringToUpper",
                );
                $validators = array(
                    "*" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($filters, $validators, $request->getPost());
                if ($input->isValid()) {
                    $i = $mdl->obtener($input->idSolicitud);
                    if (isset($i) && !empty($i)) {
                        $row = array(
                            "patente" => $i["patente"],
                            "aduana" => $i["aduana"],
                            "pedimento" => $i["pedimento"],
                            "referencia" => $i["referencia"],
                            "bitacora" => "BORRO SOLICITUD DE ANT. MOTIVO: " . $input->comentario,
                            "usuario" => $this->_session->username,
                            "creado" => date("Y-m-d H:i:s"),
                        );
                        $log->agregar($row);
                        $mdl->borrarSolicitud($input->idSolicitud);
                    }
                    $this->_helper->json(array("success" => true));
                }
                $this->_helper->json(array("success" => false));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function agregarContactoClienteAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost();
                $filters = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idCliente" => "Digits",
                    "nombre" => "StringToUpper",
                    "email" => "StringToLower",
                    "tipoContacto" => "Digits",
                    "idPlanta" => "Digits",
                );
                $validators = array(
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                    "nombre" => array("NotEmpty"),
                    "email" => array("NotEmpty", new Zend_Validate_EmailAddress()),
                    "tipoContacto" => array("NotEmpty", new Zend_Validate_Int()),
                    "idPlanta" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($filters, $validators, $post);
                if ($input->isValid("idCliente") && $input->isValid("nombre") && $input->isValid("email") && $input->isValid("tipoContacto")) {
                    $mapper = new Trafico_Model_ContactosCliMapper();
                    $table = new Trafico_Model_Table_ContactosCli($input->getEscaped());
                    $table->setAviso(1);
                    $table->setPedimento(1);
                    $table->setCruce(1);
                    if ($input->isValid("idPlanta")) {
                        $table->setIdPlanta(1);
                    }
                    $table->setCreado(date("Y-m-d H:i:s"));
                    $table->setCreadoPor($this->_session->username);
                    $mapper->findEmail($table);
                    if (null === ($table->getId())) {
                        $mapper->save($table);
                        $this->_helper->json(array("success" => true, "id" => $input->idCliente));
                    } else {
                        $this->_helper->json(array("success" => false, "message" => "El contacto ya existe."));
                    }
                } else {
                    $this->_helper->json(array("success" => false, "message" => "Invalid data."));
                }
            } else {
                $this->_helper->json(array("success" => false, "message" => "No data recieved."));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function actualizarDireccionAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $addr = array(new Zend_Validate_Regex("/^[-_a-zA-Z0-9ÑñÁÉÍÓÚáéíóú.,& ]+$/"));
                $f = array(
                    "*" => array("StringTrim", "StripTags", "StringToUpper"),
                    "id" => "Digits",
                    "idCliente" => "Digits",
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                    "rfcCliente" => array(new Zend_Validate_Regex("/^[-_a-zA-Z0-9]+$/"), "presence" => "required"),
                    "cp" => array("NotEmpty", new Zend_Validate_Regex("/^[0-9]+$/")),
                    "razon_soc" => $addr,
                    "calle" => $addr,
                    "numext" => $addr,
                    "numint" => $addr,
                    "colonia" => $addr,
                    "localidad" => $addr,
                    "municipio" => $addr,
                    "estado" => $addr,
                    "pais" => $addr,
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("idCliente")) {
                    $vucemTrafico = new OAQ_TraficoVucem(array("idCliente" => $i->idCliente, "username" => $this->_session->username));
                    if ($vucemTrafico->actualizarDireccionCliente($i->getEscaped())) {
                        $alerts = new Trafico_Model_ClientesAlertas();
                        $alerts->agregar($i->idCliente, "ACTUALIZO DIRECCIÓN", $this->_session->username);
                        $this->_helper->json(array("success" => true, "id" => $i->idCliente));
                    }
                    $this->_helper->json(array("success" => false, "message" => "Unable to update!"));
                } else {
                    $this->_helper->json(array("success" => false, "message" => "Invalid data."));
                }
            } else {
                $this->_helper->json(array("success" => false, "message" => "No data recieved."));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function obtenerGuiasAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "id" => array("StringTrim", "StripTags", "Digits"),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid()) {
                    $view = new Zend_View();
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/ajax/");
                    $model = new Trafico_Model_TraficoGuiasMapper();
                    $rows = $model->obtenerGuias($input->id);
                    if (isset($rows)) {
                        $view->data = $rows;
                        $view->idTrafico = $input->id;
                        $this->_helper->json(array("success" => true, "html" => $view->render("obtener-guias.phtml")));
                    }
                }
                $this->_helper->json(array("success" => false));
            }
            $this->_helper->json(array("success" => false));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function borrarGuiaAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost();
                $filters = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                    "idTrafico" => array("Digits"),
                );
                $input = new Zend_Filter_Input($filters, null, $post);
                if ($input->isValid()) {
                    $data = $input->getEscaped();
                    $mapper = new Trafico_Model_TraficoGuiasMapper();
                    $stmt = $mapper->delete($data["idGuia"]);
                    if ($stmt == true) {
                        $trafico = new OAQ_Trafico(array("idTrafico" => $data["idTrafico"], "idUsuario" => $this->_session->id));
                        $trafico->actualizarGuias($mapper->obtenerGuias($data["idTrafico"]));
                        $this->_helper->json(array("success" => true, "id" => $data["idTrafico"], "active" => "guias"));
                    }
                } else {
                    $this->_helper->json(array("success" => false, "message" => "Invalid data."));
                }
            } else {
                $this->_helper->json(array("success" => false, "message" => "No data recieved."));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function agregarGuiaAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost();
                $filters = array(
                    "*" => array("StringTrim", "StripTags", "StringToUpper"),
                );
                $input = new Zend_Filter_Input($filters, null, $post);
                if ($input->isValid()) {
                    $data = $input->getEscaped();
                    $mapper = new Trafico_Model_TraficoGuiasMapper();
                    $added = $mapper->agregarGuia($data["idTrafico"], $data["tipoguia"], $data["number"], $this->_session->id, isset($data["transportista"]) ? $data["transportista"] :null);
                    if ($added == true) {
                        $trafico = new OAQ_Trafico(array("idTrafico" => $data["idTrafico"], "idUsuario" => $this->_session->id));
                        $trafico->actualizarGuias($mapper->obtenerGuias($data["idTrafico"]));
                        $this->_helper->json(array("success" => true, "id" => $data["idTrafico"], "active" => "guias"));
                    }
                } else {
                    $this->_helper->json(array("success" => false, "message" => "Invalid data.", "active" => "guias"));
                }
            } else {
                $this->_helper->json(array("success" => false, "message" => "No data recieved."));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function agregarFacturaAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idTrafico" => "Digits",
                );
                $v = array(
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                    "numFactura" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idTrafico") && $input->isValid("numFactura")) {
                    $mppr = new Trafico_Model_TraficoFacturasMapper();
                    $idFactura = $mppr->agregarFacturaSimple($input->idTrafico, $input->numFactura, $this->_session->id);
                    if ($idFactura == true) {
                        $det =  new Trafico_Model_FactDetalle();
                        $det->agregarFacturaSimple($idFactura, $input->numFactura);
                        $this->_helper->json(array("success" => true));
                    }
                } else {
                    $this->_helper->json(array("success" => false, "message" => "Invalid data."));
                }
            } else {
                $this->_helper->json(array("success" => false, "message" => "No data recieved."));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function importarFacturaGetAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idFactura" => "Digits",
            );
            $v = array(
                "idFactura" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idFactura")) {
                $mapper = new Trafico_Model_TraficoFacturasMapper();
                $arr = $mapper->informacionFactura($input->idFactura);
                $traffics = new Trafico_Model_TraficosMapper();
                $traffic = $traffics->obtenerPorId($arr["idTrafico"]);
                $vucemFacturas = new OAQ_TraficoVucem(array("idFactura" => $input->idFactura, "idCliente" => $traffic["idCliente"], "patente" => $arr["patente"], "aduana" => $arr["aduana"], "referencia" => $arr["referencia"], "pedimento" => $arr["pedimento"], "numFactura" => $arr["numFactura"], "tipoOperacion" => $arr["ie"]));
                if (isset($traffic["consolidado"]) && $traffic["consolidado"] == 1) {
                    $vucemFacturas->setConsolidado(true);
                }
                $vucemFacturas->importarFactura();
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function importarFacturaAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idFactura" => array("Digits"),
                );
                $v = array(
                    "idFactura" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("idFactura")) {
                    $mapper = new Trafico_Model_TraficoFacturasMapper();
                    $arr = $mapper->informacionFactura($input->idFactura);
                    $traffics = new Trafico_Model_TraficosMapper();
                    $traffic = $traffics->obtenerPorId($arr["idTrafico"]);
                    $vucemFacturas = new OAQ_TraficoVucem(array("idFactura" => $input->idFactura, "idCliente" => $traffic["idCliente"], "patente" => $arr["patente"], "aduana" => $arr["aduana"], "referencia" => $arr["referencia"], "pedimento" => $arr["pedimento"], "numFactura" => $arr["numeroFactura"], "tipoOperacion" => $arr["ie"]));
                    if (isset($traffic["consolidado"]) && $traffic["consolidado"] == 1) {
                        $vucemFacturas->setConsolidado(true);
                    }
                    if ($vucemFacturas->importarFactura()) {
                        $this->_helper->json(array("success" => true));
                    }
                    $this->_helper->json(array("success" => false, "message" => "Unable to import!"));
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

    public function cancelarEdicionAction() {
        try {
            $id = $this->getRequest()->getParam("id", null);
            $type = $this->getRequest()->getParam("type", null);
            if (isset($id) && isset($type)) {
                $model = new Archivo_Model_DocumentosMapper();
                $this->_helper->json(array(
                    "success" => true,
                    "type" => "<p>" . $model->tipoDocumento($type) . "</p>",
                    "icons" => $this->view->archivosIconos($id, $type)
                ));
            } else {
                $this->_helper->json(array("success" => false));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerTipoCambioAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                /*$misc = new OAQ_Misc();
                $db = $misc->sitawin(3589, 640);
                if(!isset($db)) {
                    throw new Exception("No DB connected!");                    
                }
                $tipo = $db->tipoCambio(date("Y-m-d"));
                if (isset($tipo)) {
                    $this->_helper->json(array("success" => true, "data" => $tipo));
                } else {
                    $this->_helper->json(array("success" => false));
                }*/
                $this->_helper->json(array("success" => false));
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerFacturasAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $filters = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                );
                $validators = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($filters, $validators, $request->getPost());
                if ($input->isValid("id")) {
                    $view = new Zend_View();
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/ajax/");
                    $model = new Trafico_Model_TraficoFacturasMapper();
                    $rows = $model->obtenerFacturas($input->id);
                    if (isset($rows)) {
                        $mppr = new Trafico_Model_TraficosMapper();
                        $arr = $mppr->obtenerPorId($input->id);
                        if ($arr["estatus"] == 3) {
                            $view->noBorrar = true;
                        }
                        $view->data = $rows;
                        $this->_helper->json(array("success" => true, "html" => $view->render("obtener-facturas.phtml")));
                    }
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

    public function actualizarSolicitudAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idSolicitud" => array("Digits"),
                    "esquema" => array("Digits"),
                    "proceso" => array("Digits"),
                );
                $v = array(
                    "idSolicitud" => array("NotEmpty", new Zend_Validate_Int()),
                    "esquema" => array("NotEmpty", new Zend_Validate_Int()),
                    "proceso" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idSolicitud") && $input->isValid("esquema") && $input->isValid("proceso")) {
                    
                    $solicitud = new OAQ_SolicitudesAnticipo($input->idSolicitud);
                    $solicitud->set_username($this->_session->username);
                    $solicitud->set_esquema($input->esquema);
                    $solicitud->set_process($input->proceso);
                    
                    
                    if ($input->proceso == 1 && $solicitud->get_process() !== 3) {
                        if (true === $solicitud->aprobada($input->esquema)) {
                            $this->_helper->json(array("success" => true));
                        }
                    }
                    
                    if ($input->proceso == 2 && $solicitud->get_process() !== 3) {
                        if (true === $solicitud->enviarTramite($input->esquema)) {
                            $this->_helper->json(array("success" => true, "message" => "Solicitud en tramite."));
                        }
                    }
                    if ($input->proceso == 3 && $solicitud->get_process() !== 3) {
                        if (true === $solicitud->enviarDeposito($input->esquema)) {
                            $this->_helper->json(array("success" => true));
                        }
                    }
                    if ($input->proceso == 4 && $solicitud->get_process() !== 3) {
                        // cancelado
                        $this->_helper->json(array("success" => true));
                    }
                    if ($input->proceso == 5 && $solicitud->get_process() !== 3) {
                        // hsbc
                        $this->_helper->json(array("success" => true));
                    }
                    if ($input->proceso == 6 && $solicitud->get_process() !== 3) {
                        // banamex
                        $this->_helper->json(array("success" => true));
                    }
                    
                    throw new Exception("No se pudo procesar la solicitud.");
                    
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

    public function bancoDefaultAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost();
                $filters = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idBanco" => array("Digits"),
                    "idAduana" => array("Digits"),
                );
                $validator = array(
                    "idBanco" => new Zend_Validate_Int(),
                    "idAduana" => new Zend_Validate_Int(),
                );
                $input = new Zend_Filter_Input($filters, $validator, $post);
                if ($input->isValid()) {
                    $data = $input->getEscaped();
                    $tbl = new Trafico_Model_TraficoBancosMapper();
                    if (isset($data["idBanco"]) && isset($data["idAduana"])) {
                        $tbl->removerDefault((int) $data["idAduana"]);
                        $tbl->establecerDefault((int) $data["idBanco"], (int) $data["idAduana"]);
                    }
                    $this->_helper->json(array("success" => true));
                }
                $this->_helper->json(array("success" => true));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function esquemaDefaultAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost();
                $filters = array(
                    "*" => array("StringTrim", "StripTags"),
                    "esquema" => array("Digits"),
                    "idCliente" => array("Digits"),
                );
                $validator = array(
                    "esquema" => new Zend_Validate_Int(),
                    "idCliente" => new Zend_Validate_Int(),
                );
                $input = new Zend_Filter_Input($filters, $validator, $post);
                if ($input->isValid()) {
                    $data = $input->getEscaped();
                    $tbl = new Trafico_Model_ClientesMapper();
                    $tbl->esquemaDefault($data["idCliente"], $data["esquema"]);
                    $this->_helper->json(array("success" => true));
                }
                $this->_helper->json(array("success" => true));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function recuperarTraficoAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags()),
                    "idTrafico" => new Zend_Filter_Digits(),
                    "aduana" => new Zend_Filter_Digits(),
                    "cliente" => new Zend_Filter_Digits(),
                    "planta" => new Zend_Filter_Digits(),
                    "rectificacion" => new Zend_Filter_Digits(),
                    "consolidado" => new Zend_Filter_Digits(),
                    "pedimento" => new Zend_Filter_Digits(),
                    "pedimentoRectificar" => new Zend_Filter_Digits(),
                    "operacion" => new Zend_Filter_StringToUpper(),
                    "cvePedimento" => new Zend_Filter_StringToUpper(),
                    "referencia" => new Zend_Filter_StringToUpper(),
                    "blGuia" => new Zend_Filter_StringToUpper(),
                );
                $v = array(
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                    "aduana" => array("NotEmpty", new Zend_Validate_Int()),
                    "cliente" => array("NotEmpty", new Zend_Validate_Int()),
                    "planta" => array("NotEmpty", new Zend_Validate_Int()),
                    "pedimento" => array("NotEmpty", new Zend_Validate_Int()),
                    "pedimentoRectificar" => array("NotEmpty", new Zend_Validate_Int()),
                    "rectificacion" => array("NotEmpty", new Zend_Validate_Int()),
                    "consolidado" => array("NotEmpty", new Zend_Validate_Int()),
                    "tipoCambio" => new Zend_Validate_Float(),
                    "operacion" => array(array("stringLength", array("min" => 7, "max" => 8))),
                    "cvePedimento" => array(array("stringLength", array("min" => 2, "max" => 3))),
                    "referencia" => array(new Zend_Validate_Regex("/^[-_a-zA-Z0-9.\/]+$/"), "presence" => "required"),
                    "blGuia" => "NotEmpty",
                    "fechaNotificacion" => array("NotEmpty", new Zend_Validate_Regex("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/")), // 9
                    "fechaEta" => array("NotEmpty", new Zend_Validate_Regex("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/")), // 10
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idTrafico") && $input->isValid("aduana") && $input->isValid("cliente") && $input->isValid("pedimento") && $input->isValid("operacion") && $input->isValid("referencia")) {
                    $traficos = new OAQ_Trafico(array("idAduana" => $input->aduana, "idCliente" => $input->cliente, "pedimento" => $input->pedimento, "referencia" => $input->referencia, "ie" => $input->operacion, "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));
                    if (($traficos->recuperarTrafico($input->idTrafico))) {
                        $this->_helper->json(array("success" => true, "id" => $input->idTrafico));
                    } else {
                        throw new Exception("No se pudo recuperar el trafico.");
                    }
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
    
    public function nuevoTraficoAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags()),
                    "aduana" => new Zend_Filter_Digits(),
                    "cliente" => new Zend_Filter_Digits(),
                    "planta" => new Zend_Filter_Digits(),
                    "rectificacion" => new Zend_Filter_Digits(),
                    "consolidado" => new Zend_Filter_Digits(),
                    "pedimento" => new Zend_Filter_StringToUpper(),
                    "pedimentoRectificar" => new Zend_Filter_Digits(),
                    "operacion" => new Zend_Filter_StringToUpper(),
                    "cvePedimento" => new Zend_Filter_StringToUpper(),
                    "referencia" => new Zend_Filter_StringToUpper(),
                    "blGuia" => new Zend_Filter_StringToUpper(),
                    "contenedorCaja" => new Zend_Filter_StringToUpper(),
                );
                $v = array(
                    "aduana" => array("NotEmpty", new Zend_Validate_Int()),
                    "cliente" => array("NotEmpty", new Zend_Validate_Int()),
                    "planta" => array("NotEmpty", new Zend_Validate_Int()),
                    "pedimento" => array("NotEmpty"),
                    "pedimentoRectificar" => array("NotEmpty", new Zend_Validate_Int()),
                    "rectificacion" => array("NotEmpty", new Zend_Validate_Int()),
                    "consolidado" => array("NotEmpty", new Zend_Validate_Int()),
                    "tipoCambio" => new Zend_Validate_Float(),
                    "operacion" => array(array("stringLength", array("min" => 7, "max" => 8))),
                    "cvePedimento" => array(array("stringLength", array("min" => 2, "max" => 3))),
                    "referencia" => array(new Zend_Validate_Regex("/^[-_a-zA-Z0-9.\/]+$/"), "presence" => "required"),
                    "blGuia" => "NotEmpty",
                    "contenedorCaja" => "NotEmpty",
                    "fechaNotificacion" => array("NotEmpty", new Zend_Validate_Regex("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/")), // 9
                    "fechaEta" => array("NotEmpty", new Zend_Validate_Regex("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/")), // 10
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("aduana") && $input->isValid("cliente") && $input->isValid("pedimento") && $input->isValid("operacion") && $input->isValid("referencia")) {
                    $traficos = new OAQ_Trafico(array("idAduana" => $input->aduana, "idCliente" => $input->cliente, "pedimento" => $input->pedimento, "referencia" => $input->referencia, "ie" => $input->operacion, "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));
                    $comp = $traficos->comprobarTrafico();
                    if (!isset($comp["error"])) {
                        $arr = array(
                            "idAduana" => $input->aduana,
                            "idUsuario" => $this->_session->id,
                            "idCliente" => $input->cliente,
                            "idPlanta" => ($input->isValid("planta")) ? $input->planta : null,
                            "pedimento" => str_pad($input->pedimento, 7, '0', STR_PAD_LEFT),
                            "referencia" => $input->referencia,
                            "blGuia" => preg_replace('/\s+/', '', $input->blGuia),
                            "pedimentoRectificar" => $input->pedimentoRectificar,
                            "ie" => $input->operacion,
                            "cvePedimento" => $input->cvePedimento,
                            "tipoCambio" => $input->tipoCambio,
                            "consolidado" => $input->consolidado,
                            "rectificacion" => $input->rectificacion,
                            "contenedorCaja" => $input->contenedorCaja,
                            "estatus" => 1,
                            "fechaEta" => date("Y-m-d H:i:s", strtotime($input->fechaEta)),
                            "creado" => date("Y-m-d H:i:s"),
                        );
                        $res = $traficos->nuevoTrafico($arr);
                        if ($res["success"] === true) {
                            if ($input->isValid("blGuia")) {
                                $mppr = new Trafico_Model_TraficoGuiasMapper();
                                $guias = explode(',', $input->blGuia);                                
                                foreach ($guias as $guia) {
                                    $mppr->agregarGuia($res["id"], "H", preg_replace('/\s+/', '', $guia), $this->_session->id);
                                }
                            }
                            $this->_helper->json(array("success" => true, "id" => $res["id"]));
                        } else {
                            $this->_helper->json(array("success" => false));
                        }
                    } else {
                        $this->_helper->json(array("success" => false, "message" => $comp["message"], "idTrafico" => isset($comp["idTrafico"]) ? $comp["idTrafico"] : null));
                    }
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

    public function modificarTraficoAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags()),
                    "idTrafico" => new Zend_Filter_Digits(),
                    "aduana" => new Zend_Filter_Digits(),
                    "cliente" => new Zend_Filter_Digits(),
                    "rectificacion" => new Zend_Filter_Digits(),
                    "consolidado" => new Zend_Filter_Digits(),
                    "pedimento" => new Zend_Filter_Digits(),
                    "operacion" => new Zend_Filter_StringToUpper(),
                    "cvePedimento" => new Zend_Filter_StringToUpper(),
                    "referencia" => new Zend_Filter_StringToUpper(),
                    "contenedorCaja" => new Zend_Filter_StringToUpper(),
                    "nombreBuque" => new Zend_Filter_StringToUpper(),
                );
                $v = array(
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                    "aduana" => array("NotEmpty", new Zend_Validate_Int()),
                    "cliente" => array("NotEmpty", new Zend_Validate_Int()),
                    "pedimento" => array("NotEmpty", new Zend_Validate_Int()),
                    "rectificacion" => array("NotEmpty", new Zend_Validate_Int()),
                    "consolidado" => array("NotEmpty", new Zend_Validate_Int()),
                    "tipoCambio" => "NotEmpty",
                    "contenedorCaja" => "NotEmpty",
                    "nombreBuque" => "NotEmpty",
                    "operacion" => array(array("stringLength", array("min" => 7, "max" => 8))),
                    "cvePedimento" => array(array("stringLength", array("min" => 2, "max" => 3))),
                    "referencia" => array(new Zend_Validate_Regex("/^[-_a-zA-Z0-9.\/]+$/"), "presence" => "required"),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idTrafico")) {
                    
                    $log = new Trafico_Model_BitacoraMapper();
                    
                    $log_msg = "SE EDITÓ TRÁFICO: ";
                    
                    if ($input->isValid("aduana") && trim($input->aduana) != '') {
                        $log_msg = $log_msg . "ADUANA: " . $input->aduana . " ";
                    }
                    if ($input->isValid("cliente") && trim($input->cliente) != '') {
                        $log_msg = $log_msg . "CLIENTE: " . $input->cliente . " ";
                    }
                    if ($input->isValid("pedimento") && trim($input->pedimento) != '') {
                        $log_msg = $log_msg . "PEDIMENTO: " . $input->pedimento . " ";
                    }
                    if ($input->isValid("referencia") && trim($input->referencia) != '') {
                        $log_msg = $log_msg . "REFERENCIA: " . $input->referencia . " ";
                    }
                    if ($input->isValid("cvePedimento") && trim($input->cvePedimento) != '') {
                        $log_msg = $log_msg . "CVE. PED.: " . $input->cvePedimento . " ";
                    }
                    if ($input->isValid("operacion") && trim($input->operacion) != '') {
                        $log_msg = $log_msg . "TIPO DE OP.: " . $input->operacion . " ";
                    }                    
                    
                    $referencias = new OAQ_Referencias();
                    if($referencias->modificarTraficoReferencia($input->idTrafico, $input->aduana, $input->cliente, $input->pedimento, $input->referencia, $input->operacion, $input->cvePedimento, $input->contenedorCaja, $input->nombreBuque, $this->_session->username)) {
                        $trafico = new OAQ_Trafico(array("idTrafico" => $input->idTrafico, "idUsuario" => $this->_session->id, "usuario" => $this->_session->username));
                        $row = array(
                            "patente" => $trafico->getPatente(),
                            "aduana" => $trafico->getAduana(),
                            "pedimento" => $trafico->getPedimento(),
                            "referencia" => $trafico->getReferencia(),
                            "bitacora" => $log_msg,
                            "usuario" => $this->_session->username,
                            "creado" => date("Y-m-d H:i:s"),
                        );
                        $log->agregar($row);
                        $this->_helper->json(array("success" => true));
                    }
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

    protected function _curlNewMessage($id, $tipo) {
        $sender = new OAQ_WorkerSender("emails");
        $sender->enviarEmail($id);
        $misc = new OAQ_Misc();
        $misc->execCurl("enviar-email");
    }

    public function agregarComplementoAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost();
                $filters = array(
                    "*" => array("StringTrim", "StripTags"),
                );
                $input = new Zend_Filter_Input($filters, null, $post);
                
                if ($input->isValid("idSolicitud")) {

                    $data = $input->getEscaped();

                    $model = new Trafico_Model_TraficoSolicitudesMapper();
                    $row = $model->obtener($data["idSolicitud"]);
                    if (($found = $model->verificar($row["idCliente"], $row["idAduana"], $row["tipoOperacion"], $row["pedimento"], $row["referencia"]))) {
                        $comp = isset($row["complmento"]) ? $row["complemento"] + 1 : 1;
                        $added = $model->agregarComplemento($row["idCliente"], $row["idAduana"], $row["tipoOperacion"], $row["pedimento"], $row["referencia"], $comp, $this->_session->id);
                        if (is_int((int) $added)) {
                            $detail = new Trafico_Model_TraficoSolDetalleMapper();
                            $det = $detail->obtener($data["idSolicitud"]);
                            if (isset($det) && $det !== false) {
                                unset($det["id"]);
                                unset($det["idSolicitud"]);
                                $det["idSolicitud"] = $added;
                                $det["creado"] = date("Y-m-d H:i:s");
                                $detail->agregar($det);

                                $concepts = new Trafico_Model_TraficoSolConceptoMapper();
                                $conc = $concepts->obtenerTodos($data["idSolicitud"]);
                                if (isset($conc) && !empty($conc)) {
                                    foreach ($conc as $con) {
                                        $concepts->agregar($con["idAduana"], $added, $con["idConcepto"], $con["concepto"], $con["importe"]);
                                    }
                                }
                            }
                        }
                        $logtbl = new Trafico_Model_BitacoraMapper;
                        $log = array(
                            "patente" => $row["patente"],
                            "aduana" => $row["aduana"],
                            "pedimento" => $row["pedimento"],
                            "referencia" => $row["referencia"],
                            "bitacora" => " SE AGREGO COMPLEMENTO {$comp} ID {$added}",
                            "usuario" => $this->_session->username,
                            "creado" => date("Y-m-d H:i:s"),
                        );
                        $logtbl->agregar($log);
                        
                        $this->_helper->json(array("success" => true));
                    } else {
                        $this->_helper->json(array("success" => false, "message" => "No se encontro toda la información.", "data" => $row));
                    }
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

    public function enviarEdocumentAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idTrafico" => array("Digits"),
                    "idArchivo" => array("Digits"),
                    "tipo_documento" => array("Digits"),
                );
                $v = array(
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                    "idArchivo" => array("NotEmpty", new Zend_Validate_Int()),
                    "tipo_documento" => array("NotEmpty", new Zend_Validate_Int()),
                    "convert" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idTrafico") && $input->isValid("idArchivo") && $input->isValid("tipo_documento")) {
                
                    $tbl = new Trafico_Model_VucemMapper();
                    $repo = new Archivo_Model_RepositorioMapper();
                    $file = $repo->informacionVucem($input->idArchivo);

                    if ($input->isValid("convert")) {
                        $proc =  new OAQ_Archivos_Procesar();

                        $arr = $proc->convertirArchivoEdocument($input->idArchivo);

                        if ($arr && isset($arr["filename"])) {                        
                            $edoc = array(
                                "idTrafico" => $input->idTrafico,
                                "idArchivo" => $input->idArchivo,
                                "instruccion" => "Digitalizar documento.",
                                "nombreArchivo" => $file["nom_archivo"],
                                "ubicacion" => $arr["filename"],
                                "tipoDocumento" => $input->tipo_documento,
                                "descripcionDocumento" => '',
                                "creado" => date("Y-m-d H:i:s"),
                            );
                            $tbl->agregar($edoc);
                            $this->_helper->json(array("success" => true));
                        }

                    } else {
                        $edoc = array(
                            "idTrafico" => $input->idTrafico,
                            "idArchivo" => $input->idArchivo,
                            "instruccion" => "Digitalizar documento.",
                            "nombreArchivo" => $file["nom_archivo"],
                            "tipoDocumento" => $input->tipo_documento,
                            "descripcionDocumento" => '',
                            "creado" => date("Y-m-d H:i:s"),
                        );
                        $tbl->agregar($edoc);
                        $this->_helper->json(array("success" => true));
                    }
                    $this->_helper->json(array("success" => true));
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

    public function editarClienteAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                    "idEmpresa" => array("Digits"),
                    "rfc" => array("StringToUpper"),
                    "nombre" => array("StringToUpper"),
                    "rfcSociedad" => array("StringToUpper"),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "idEmpresa" => array("NotEmpty", new Zend_Validate_Int()),
                    "rfc" => array("NotEmpty", new Zend_Validate_Regex("/^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/")),
                    "rfcSociedad" => array("NotEmpty", new Zend_Validate_Regex("/^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/")),
                    "nombre" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id") && $input->isValid("idEmpresa") && $input->isValid("rfc") && $input->isValid("nombre")) {
                    $mapper = new Trafico_Model_ClientesMapper();
                    if (($mapper->actualizarNombre($input->id, preg_replace("/[ \t]+/", " ", preg_replace("/\s*$^\s*/m", " ", html_entity_decode($input->nombre))), $input->rfcSociedad))) {
                        $alerts = new Trafico_Model_ClientesAlertas();
                        $alerts->agregar($input->id, "MODIFICÓ NOMBRE DE CLIENTE", $this->_session->username);
                        $this->_helper->json(array("success" => true));
                    } else {
                        $this->_helper->json(array("success" => false));
                    }
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

    public function guardarSolicitudAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $filters = array(
                    "*" => array("StringTrim", "StripTags"),
                );
                $input = new Zend_Filter_Input($filters, null, $request->getPost());
                if ($input->isValid()) {
                    $misc = new OAQ_Misc();
                    $repo = new Archivo_Model_RepositorioMapper();
                    $request = new Trafico_Model_TraficoSolicitudesMapper();
                    $header = $request->obtener($input->id);
                    if (isset($header["patente"]) && isset($header["aduana"]) && isset($header["referencia"])) {
                        if (isset($header["complemento"]) && $header["complemento"] != null) {
                            $filename = $header["aduana"] . "_" . $header["patente"] . "_" . $header["pedimento"] . "_SOLICITUD_COMP" . $header["complemento"] . ".pdf";
                        } else {
                            $filename = $header["aduana"] . "_" . $header["patente"] . "_" . $header["pedimento"] . "_SOLICITUD.pdf";
                        }
                        $folder = $misc->createNewDir($header["patente"] . DIRECTORY_SEPARATOR . $header["aduana"] . DIRECTORY_SEPARATOR . $header["referencia"]);
                        if ($folder !== false) {
                            if (!file_exists($folder . DIRECTORY_SEPARATOR . $filename)) {
                                $sto = new Trafico_Model_AlmacenMapper();
                                $table = new Trafico_Model_TraficoSolDetalleMapper();
                                $detalle = $table->obtener($input->id);
                                $model = new Trafico_Model_TraficoSolConceptoMapper();
                                $conceptos = $model->obtener($input->id);
                                $dbtable = new Trafico_Model_TraficoConceptosMapper();
                                $concepts = $dbtable->obtener($header["idAduana"]);
                                $chunk = array_chunk($concepts, 2);
                                $rows = array();
                                $total = 0;
                                foreach ($chunk as $item) {
                                    $rows[] = array(
                                        trim($item[0]),
                                        ($conceptos !== false) ? $this->_arrayValue(trim($item[0]), $conceptos) : 0,
                                        isset($item[1]) ? trim($item[1]) : "",
                                        ($conceptos !== false) ? isset($item[1]) ? $this->_arrayValue(trim($item[1]), $conceptos) : 0 : 0,
                                        ""
                                    );
                                    $total += ($conceptos !== false) ? $this->_arrayValue(trim($item[0]), $conceptos) : 0;
                                    $total += ($conceptos !== false) ? isset($item[1]) ? $this->_arrayValue(trim($item[1]), $conceptos) : 0 : 0;
                                }
                                $pre["header"] = $header;
                                $pre["detalle"] = $detalle;
                                $pre["conceptos"] = $rows;
                                $pre["detalle"]["almacen"] = (isset($pre["detalle"]["almacen"])) ? $sto->obtenerNombreAlmacen($pre["detalle"]["almacen"]) : null;
                                $pre["anticipo"] = ($conceptos !== false) ? $this->_arrayValue("ANTICIPO", $conceptos) : 0;
                                $pre["total"] = $total;
                                $tbl = new Trafico_Model_TraficoBancosMapper();
                                $banco = $tbl->obtenerBancoDefault((int) $header["idAduana"]);
                                if (isset($banco) && !empty($banco)) {
                                    $pre["banco"] = $banco;
                                } else {
                                    $pre["banco"] = array(
                                        "nombre" => "N/D",
                                        "razonSocial" => "",
                                        "cuenta" => "",
                                        "clabe" => "",
                                        "sucursal" => "",
                                    );
                                }
                                require "tcpdf/solicitud.php";
                                if (isset($pre)) {
                                    $pre["colors"]["line"] = array(5, 5, 5);
                                    $pdf = new Trafico($pre, "P", "pt", "LETTER");
                                    $pdf->SolicitudAnticipo();
                                    $pdf->Output($folder . DIRECTORY_SEPARATOR . $filename, "F");
                                }
                                if (file_exists($folder . DIRECTORY_SEPARATOR . $filename)) {
                                    $id = $repo->addFile(31, null, $header["referencia"], $header["patente"], $header["aduana"], pathinfo($folder . DIRECTORY_SEPARATOR . $filename, PATHINFO_BASENAME), $folder . DIRECTORY_SEPARATOR . $filename, $this->_session->username, $header["rfcCliente"], $header["pedimento"]);
                                    $this->_helper->json(array("success" => true, "id" => $id));
                                } else {
                                    $this->_helper->json(array("success" => false));
                                }
                            } else {
                                $id = $repo->searchFile(31, $header["referencia"], $header["patente"], $header["aduana"], pathinfo($folder . DIRECTORY_SEPARATOR . $filename, PATHINFO_BASENAME));
                                if ($id !== false) {
                                    $this->_helper->json(array("success" => true, "id" => $id));
                                }
                            }
                        }
                    }
                } else {
                    $this->_helper->json(array("success" => false));
                }
            }
            $this->_helper->json(array("success" => false));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    protected function _arrayValue($value, $array) {
        if (isset($array[$value])) {
            return $array[$value];
        }
        return 0;
    }

    public function solicitudDesdeTraficoAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idTrafico" => "Digits"
                );
                $v = array(
                    "idTrafico" => new Zend_Validate_Int()
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("idTrafico")) {
                    $traffics = new Trafico_Model_TraficosMapper();
                    $mapper = new Trafico_Model_TraficoSolicitudesMapper();
                    $traffic = new Trafico_Model_Table_Traficos();
                    $traffic->setId($i->idTrafico);
                    $traffics->find($traffic);
                    if (null !== ($traffic->getId())) {
                        $solicitud = new Trafico_Model_Table_TraficoSolicitudes();
                        $solicitud->setIdCliente($traffic->getIdCliente());
                        $solicitud->setIdAduana($traffic->getIdAduana());
                        $solicitud->setIdUsuario($this->_session->id);
                        $solicitud->setTipoOperacion($traffic->getIe());
                        $solicitud->setPedimento($traffic->getPedimento());
                        $solicitud->setReferencia($traffic->getReferencia());
                        $solicitud->setCreado(date("Y-m-d H:i:s"));
                        $mapper->find($solicitud);
                        if (null === ($solicitud->getId())) {
                            $misc = new OAQ_Misc();
                            $id = $mapper->save($solicitud);
                            $dates = new Trafico_Model_TraficoFechasMapper();
                            $fechaEta = $dates->obtenerFecha($i->idTrafico, 10);
                            $details = new Trafico_Model_TraficoSolDetalleMapper();
                            /*if ($traffic->getPatente() == 3589 && ($traffic->getAduana() == 240 || $traffic->getAduana() == 640)) {
                                $db = $misc->sitawinTrafico($traffic->getPatente(), $traffic->getAduana());
                                if (isset($db)) {
                                    $b = $db->infoPedimentoBasicaReferencia($traffic->getReferencia());
                                }
                                if (isset($b) && !empty($b)) {
                                    if (isset($b["pesoBruto"])) {
                                        $data["pesoBruto"] = $b["pesoBruto"];
                                    }
                                    if (isset($b["guias"]) && !empty($b["guias"])) {
                                        $data["guias"] = "";
                                        foreach ($b["guias"] as $value) {
                                            $data["guias"] .= preg_replace("/\s+/", "", $value["guia"]) . ", ";
                                        }
                                    }
                                    if (isset($b["facturas"]) && !empty($b["facturas"])) {
                                        $valorUsd = 0;
                                        $data["facturas"] = "";
                                        foreach ($b["facturas"] as $value) {
                                            $data["facturas"] .= preg_replace("/\s+/", "", $value["numFactura"]) . ", ";
                                            $valorUsd += $value["valorFacturaUsd"];
                                        }
                                    }
                                }
                            }*/
                            $detail = array(
                                "idSolicitud" => $id,
                                "idAduana" => $traffic->getIdAduana(),
                                "cvePed" => $traffic->getCvePedimento(),
                                "peso" => isset($data["pesoBruto"]) ? $data["pesoBruto"] : null,
                                "numFactura" => isset($data["facturas"]) ? $data["facturas"] : null,
                                "valorMercancia" => isset($valorUsd) ? $valorUsd : null,
                                "bl" => isset($data["guias"]) ? preg_replace("/,\s$/", "", $data["guias"]) : null,
                                "fechaEta" => isset($fechaEta) ? $fechaEta : null,
                                "creado" => date("Y-m-d H:i:s"),
                            );
                            $details->agregar($detail);
                            $this->_helper->json(array("success" => true, "id" => $id, "aduana" => $traffic->getIdAduana()));
                        } else {
                            $this->_helper->json(array("success" => false, "message" => "La solicitud de anticipo existe."));
                        }
                    } else {
                        throw new Exception("No data found!");
                    }
                } else {
                    throw new Exception("Invalid Input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function revisarSolicitudAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idTrafico" => "Digits"
                );
                $v = array(
                    "idTrafico" => new Zend_Validate_Int()
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("idTrafico")) {
                    $model = new Trafico_Model_TraficosMapper();
                    $mapper = new Trafico_Model_TraficoSolicitudesMapper();
                    $table = new Trafico_Model_Table_Traficos();
                    $table->setId($i->idTrafico);
                    $model->find($table);
                    if (null !== ($table->getId())) {
                        $solicitud = new Trafico_Model_Table_TraficoSolicitudes();
                        $solicitud->setIdCliente($table->getIdCliente());
                        $solicitud->setIdAduana($table->getIdAduana());
                        $solicitud->setIdUsuario($this->_session->id);
                        $solicitud->setTipoOperacion($table->getIe());
                        $solicitud->setPedimento($table->getPedimento());
                        $solicitud->setReferencia($table->getReferencia());
                        $solicitud->setCreado(date("Y-m-d H:i:s"));
                        $mapper->find($solicitud);
                        if (null === ($solicitud->getId())) {                            
                            $this->_helper->json(array("success" => false));
                        } else {
                            if($solicitud->getGenerada() == null) {
                                $this->_helper->json(array("success" => true, "id" => $solicitud->getId(), "aduana" => $solicitud->getIdAduana()));
                            } else {
                                $this->_helper->json(array("success" => true, "id" => $solicitud->getId()));                                
                            }
                        }
                    }
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function cargarComentariosAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "idTrafico" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags(), new Zend_Filter_Digits()),
                );
                $v = array(
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("idTrafico")) {
                    $trafico = new OAQ_Trafico(array("idTrafico" => $input->idTrafico, "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));
                    $arr = array();
                    $arr['bitacora'] = $trafico->obtenerBitacora();
                    $arr['comentarios'] = $trafico->obtenerComentarios();
                    $arr['archivos'] = $trafico->obtenerArchivosComentarios();
                    $this->_helper->json(array("success" => true, "results" => $arr));
                } else {
                    throw new Exception("Invalid input");
                }
            } else {
                throw new Exception("Invalid request type");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function cargarRegistrosAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $filters = array(
                    "*" => array("StringTrim", "StripTags"),
                );
                $validators = array(
                    "id" => new Zend_Validate_Int()
                );
                $input = new Zend_Filter_Input($filters, $validators, $request->getPost());
                if ($input->isValid()) {
                    $view = new Zend_View();
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/extra/");
                    $mapper = new Trafico_Model_TraficosMapper();
                    $basico = $mapper->obtenerPorId($input->id);
                    $val = new Automatizacion_Model_ArchivosValidacionPedimentosMapper();
                    $validacion = $val->obtenerTodos($basico["patente"], $basico["pedimento"], $basico["aduana"]);
                    if (isset($validacion) && !empty($validacion)) {
                        $view->archivosm = $validacion;
                    }
                    $pre = new Automatizacion_Model_ArchivosValidacionFirmasMapper();
                    $firmas = $pre->obtenerTodos($basico["patente"], $basico["pedimento"]);
                    if (isset($firmas) && !empty($firmas)) {
                        $view->archivosv = $firmas;
                    }
                    $pag = new Automatizacion_Model_ArchivosValidacionPagosMapper();
                    $pagos = $pag->findFile($basico["patente"], $basico["aduana"], $basico["pedimento"]);
                    if (isset($pagos) && !empty($pagos)) {
                        $view->archivosp = $pagos;
                    }
                    $this->_helper->json(array("success" => true, "html" => $view->render("register.phtml")));
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function cargarArchivosAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                );
                $v = array(
                    "id" => new Zend_Validate_Int()
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid()) {
                    $view = new Zend_View();
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/ajax/");
                    $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");
                    $mppr = new Trafico_Model_TraficosMapper();
                    $array = $mppr->obtenerPorId($input->id);
                    
                    $repo = new Archivo_Model_RepositorioMapper();
                    $archivos = $repo->obtenerArchivosReferencia($array["referencia"]);
                    $view->archivos = $archivos;
                    
                    $trafico = new OAQ_Trafico(array("idTrafico" => $input->id, "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));
                    $index = $trafico->verificarIndexRepositorios();
                    
                    if (in_array($this->_session->role, array("super", "gerente", "trafico_ejecutivo", "trafico"))) {
                        $view->canDelete = true;
                    }
                    $val = new OAQ_ArchivosValidacion();
                    if (isset($array["pedimento"])) {
                        $view->validacion = $val->archivosDePedimento($array["patente"], $array["aduana"], $array["pedimento"]);
                    }
                    
                    $complementos = $repo->complementosReferencia($array["referencia"]);
                    if (!empty($complementos)) {
                        $view->complementos = $complementos;
                    }
                    $this->_helper->json(array("success" => true, "html" => $view->render("cargar-archivos.phtml"), "repos" => $index));
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function subirArchivosAction() {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "patente" => array("Digits"),
                    "aduana" => array("Digits"),
                    "pedimento" => array("Digits"),
                    "referencia" => array("StringToUpper"),
                    "rfcCliente" => array("StringToUpper"),
                );
                $v = array(
                    "patente" => new Zend_Validate_Int(),
                    "aduana" => new Zend_Validate_Int(),
                    "pedimento" => new Zend_Validate_Int(),
                    "referencia" => array(new Zend_Validate_Regex("/^[-_a-zA-Z0-9.]+$/"), "presence" => "required"),
                    "rfcCliente" => array(new Zend_Validate_Regex("/^[-_a-zA-Z0-9.]+$/"), "presence" => "required"),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if (!$input->isValid()) {
                    throw new Exception("Invalid input!" . Zend_Debug::dump($input->getErrors(), true) . Zend_Debug::dump($input->getMessages(), true));
                }
            }
            $errors = [];
            
            if(!is_writable($this->_appconfig->getParam("expdest"))) {
                throw new Exception("Directory [" . $this->_appconfig->getParam("expdest") . "] is not writable.");
            }
            $misc = new OAQ_Misc();
            if (APPLICATION_ENV == "production") {
                $misc->set_baseDir($this->_appconfig->getParam("expdest"));
            } else {
                $misc->set_baseDir("D:\\xampp\\tmp\\expedientes");
            }
            $model = new Archivo_Model_RepositorioMapper();
            $upload = new Zend_File_Transfer_Adapter_Http();
            $upload->addValidator("Count", false, array("min" => 1, "max" => 15))
                    ->addValidator("Size", false, array("min" => "1", "max" => "25MB"));
                    //->addValidator("Extension", false, array("extension" => "pdf,xml,xls,xlsx,doc,docx,zip,bmp,tif,jpg,msg", "case" => false))
                    //->addValidator(new Zend_Validate_Regex('/(.*\.(pdf|xml|xls|xlsx|doc|docx|zip|bmp|tif|jpg|msg))|A[0-9]{7}.([0-9]{3})|E[0-9]{7}.([0-9]{3})|M[0-9]{7}.([0-9]{3})|m[0-9]{7}.err/i'));
            if (($path = $misc->directorioExpedienteDigital($input->patente, $input->aduana, $input->referencia))) {
                $upload->setDestination($path);
            }
            $files = $upload->getFileInfo();
            $up = array();
            $nup = array();
            foreach ($files as $fieldname => $fileinfo) { 
                if (($upload->isUploaded($fieldname))) {
                    
                    if (!preg_match('/\.(pdf|xml|xls|xlsx|doc|docx|zip|bmp|tif|jpe?g|bmp|png|msg|([0-9]{3})|err)(?:[\?\#].*)?$/i', $fileinfo["name"])) {
                        continue;
                    }

                    $tipoArchivo = $misc->tipoArchivo(basename($fileinfo["name"]));
                    
                    if (preg_match('/^A[0-9]{7}.([0-9]{3})$/i', basename($fileinfo["name"]))) {
                        $tipoArchivo = 1030;
                    }
                    if (preg_match('/^E[0-9]{7}.([0-9]{3})$/i', basename($fileinfo["name"]))) {
                        $tipoArchivo = 1030;
                    }
                    if (preg_match('/^M[0-9]{7}.([0-9]{3})$/i', basename($fileinfo["name"]))) {
                        $tipoArchivo = 1010;
                    }
                    if (preg_match('/^m[0-9]{7}.err$/i', basename($fileinfo["name"]))) {
                        $tipoArchivo = 1020;
                    }
                    
                    $ext = pathinfo($fileinfo["name"], PATHINFO_EXTENSION);
                    if (preg_match('/msg/i', $ext)) {
                        $tipoArchivo = 2001;
                    }                    

                    if ($tipoArchivo == 99) {
                        $nup[] = $fileinfo["name"];
                        unlink($fileinfo["name"]);
                        continue;
                    }

                    $filename = $misc->formatFilename($fileinfo["name"], false);
                    $verificar = $model->verificarArchivo($input->patente, $input->referencia, $filename);
                    if ($verificar == false) {
                        $upload->receive($fieldname);
                        if (in_array($tipoArchivo, array(1010, 1020, 1030))) {
                            $up[] = $fileinfo["name"];
                            $model->nuevoArchivo($tipoArchivo, null, $input->patente, $input->aduana, $input->pedimento, $input->referencia, $filename, $path . DIRECTORY_SEPARATOR . $filename, $this->_session->username, $input->rfcCliente);                            
                        }
                        if (($misc->renombrarArchivo($path, $fileinfo["name"], $filename))) {
                            $up[] = $fileinfo["name"];
                            $model->nuevoArchivo($tipoArchivo, null, $input->patente, $input->aduana, $input->pedimento, $input->referencia, $filename, $path . DIRECTORY_SEPARATOR . $filename, $this->_session->username, $input->rfcCliente);
                        }
                    } else {
                        $errors[] = array(
                            "filename" => $fileinfo["name"],
                            "errors" => array("errors" => "El archivo ya existe."),
                        );
                    }

                } else {
                    $error = $upload->getErrors();
                    $errors[] = array(
                        "filename" => $fileinfo["name"],
                        "errors" => $error,
                    );
                }
            }
            if (!empty($errors)) {
                $this->_helper->json(array("success" => false, "errors" => $errors));
            } else {
                $this->_helper->json(array("success" => true, "uploaded" => $up, "not_uploaded" => $nup, "errors" => $errors));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function tiposArchivosAction() {
        try {
            $filters = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $validators = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($filters, $validators, $this->_request->getParams());
            if ($input->isValid()) {
                $repo = new Archivo_Model_RepositorioMapper();
                $docs = new Archivo_Model_DocumentosMapper();
                $d = $docs->getAll();
                $type = $repo->getFileType($input->id);
                $html = "<select id=\"select_" . $input->id . "\" class=\"traffic-select-large\">";
                foreach ($d as $doc) {
                    $html .= "<option value=\"" . $doc["id"] . "\""
                            . (($doc["id"] == $type) ? " selected=\"selected\"" : "")
                            . ">"
                            . $doc["id"] . " - " . $doc["nombre"]
                            . "</option>";
                }
                $html .= "</select>";
                echo $html;
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function cambiarTipoArchivoAction() {
        try {
            $filters = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
                "type" => array("Digits"),
                "idTrafico" => array("Digits"),
            );
            $validators = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
                "type" => array("NotEmpty", new Zend_Validate_Int()),
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($filters, $validators, $this->_request->getParams());
            if ($input->isValid()) {
                $sat = new OAQ_SATValidar();
                $repo = new Archivo_Model_RepositorioMapper();
                $updated = $repo->changeFileType($input->id, $input->type);
                $file = $repo->obtenerInfo($input->id);
                if ($input->type == 29 || $input->type == 2) {
                    $filename = $repo->getFilePathById($input->id);
                    $basename = basename($filename);
                    if (preg_match("/.xml$/i", $basename)) {
                        $xmlArray = $sat->satToArray(html_entity_decode(file_get_contents($filename)));
                        if (isset($xmlArray["Addenda"]["operacion"])) {
                            $adenda = $sat->parametrosAdenda($xmlArray["Addenda"]["operacion"]);
                        }
                        $emisor = $sat->obtenerGenerales($xmlArray["Emisor"]);
                        $receptor = $sat->obtenerGenerales($xmlArray["Receptor"]);
                        $complemento = $sat->obtenerComplemento($xmlArray["Complemento"]);
                        $data = array(
                            "tipo_archivo" => $input->type,
                            "emisor_rfc" => $emisor["rfc"],
                            "emisor_nombre" => $emisor["razonSocial"],
                            "receptor_rfc" => $receptor["rfc"],
                            "receptor_nombre" => $receptor["razonSocial"],
                            "folio" => $xmlArray["@attributes"]["folio"],
                            "fecha" => date("Y-m-d H:i:s", strtotime($xmlArray["@attributes"]["fecha"])),
                            "uuid" => $complemento["uuid"],
                            "observaciones" => isset($adenda["observaciones"]) ? $adenda["observaciones"] : null,
                        );
                        unset($xmlArray);
                        $updated = $repo->actualizarFactura($input->id, $data);
                        if ($updated) {
                            if (($idd = $repo->searchFileByName($file["patente"], $file["aduana"], pathinfo($basename, PATHINFO_FILENAME) . ".pdf"))) {
                                $repo->actualizarFactura($idd, $data);
                            }
                        }
                    }
                }
                $model = new Archivo_Model_DocumentosMapper();
                if ($updated) {
                    $icons = $this->view->archivosIconos($input->id, $input->type, null, $file["nom_archivo"]);
                    $repo->modificado($input->id, $this->_session->username);
                    $this->_helper->json(array("success" => true, "type" => $model->tipoDocumento($input->type), "icons" => $icons));
                } else {
                    $this->_helper->json(array("success" => false));
                }
                $this->_helper->json(array("success" => false));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function avisosClienteAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $flt = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                    "action" => array("StringToLower"),
                    "alert" => array("StringToLower"),
                );
                $vdr = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "action" => new Zend_Validate_InArray(array("add", "remove")),
                    "alert" => new Zend_Validate_InArray(array("aviso", "pedimento", "cruce")),
                );
                $input = new Zend_Filter_Input($flt, $vdr, $request->getPost());
                if ($input->isValid("id") && $input->isValid("action") && $input->isValid("alert")) {
                    $mapper = new Trafico_Model_ContactosCliMapper();
                    if ($mapper->cambiarAlerta($input->id, $input->alert, ($input->action == "add") ? 1 : 0, $this->_session->username)) {
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

    public function guardarFechasAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
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
                    "fechaPago" => array("NotEmpty", new Zend_Validate_Regex("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/")), // 2
                    "fechaEntrada" => array("NotEmpty", new Zend_Validate_Regex("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/")), // 1
                    "fechaPresentacion" => array("NotEmpty", new Zend_Validate_Regex("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/")), // 5
                    "fechaLiberacion" => array("NotEmpty", new Zend_Validate_Regex("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/")), // 8
                    "fechaArribo" => array("NotEmpty", new Zend_Validate_Regex("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/")), // 10
                    "fechaNotificacion" => array("NotEmpty", new Zend_Validate_Regex("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/")), // 9
                    "fechaRevalidado" => array("NotEmpty", new Zend_Validate_Regex("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/")), // 20
                    "fechaPrevio" => array("NotEmpty", new Zend_Validate_Regex("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/")), // 21
                    "fechaDeposito" => array("NotEmpty", new Zend_Validate_Regex("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/")), // 22
                    "fechaRecepcionDocs" => array("NotEmpty", new Zend_Validate_Regex("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/")), // 11
                    "fechaPresentacion" => array("NotEmpty", new Zend_Validate_Regex("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/")), // 5
                    "fechaCitaDespacho" => array("NotEmpty", new Zend_Validate_Regex("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/")), // 25
                    "fechaEnvioDocumentos" => array("NotEmpty", new Zend_Validate_Regex("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/")), // 
                    "horaRecepcionDocs" => array("NotEmpty", new Zend_Validate_Regex("/(\d{2}):(\d{2}) (AM|PM)/")),
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("idTrafico")) {
                    $trafico = new OAQ_Trafico(array("idTrafico" => $i->idTrafico, "idUsuario" => $this->_session->id));
                    $traffics = new Trafico_Model_TraficosMapper();
                    $traffic = new Trafico_Model_Table_Traficos();
                    $traffic->setId($i->idTrafico);
                    $traffics->find($traffic);
                    if ($i->isValid("fechaEntrada")) {
                        $trafico->actualizarFecha(1, $i->fechaEntrada);
                    }
                    if ($i->isValid("fechaPresentacion")) {
                        $trafico->actualizarFecha(5, $i->fechaPresentacion);
                    }
                    if ($i->isValid("fechaPago")) {
                        if ($trafico->actualizarFecha(2, $i->fechaPago)) {
                            $traffics->actualizarFechaPago($i->idTrafico, $i->fechaPago);
                        }
                        if ((int) $traffic->getPatente() == 3589) {
                            $vucemPed = new Vucem_Model_VucemPedimentosMapper();
                            $vucemPed->agregarVacio($i->idTrafico, $traffic->getPatente(), $traffic->getAduana(), $traffic->getPedimento());
                        }
                        if (null !== ($traffic->getId())) {
                            $traffic->setPagado(1);
                            $traffic->setEstatus(2);
                            $traffic->setIdUsuarioModif($this->_session->id);
                            $traffics->save($traffic);
                        }
                        $mapper = new Trafico_Model_NotificacionesMapper();
                        $table = new Automatizacion_Model_Table_Notificaciones();
                        $table->setIdTrafico($i->idTrafico);
                        $table->setReferencia($traffic->getReferencia());
                        $table->setPedimento($traffic->getPedimento());
                        $table->setDe($this->_session->id);
                        $table->setTipo("pago");
                        $table->setContenido("Se notifica el pago de pedimento.");
                        $table->setCreado(date("Y-m-d H:i:s"));
                        $mapper->save($table);
                    }
                    if ($i->isValid("fechaRevalidado")) {
                        $trafico->actualizarFecha(20, $i->fechaRevalidado);
                    }
                    if ($i->isValid("fechaPrevio")) {
                        $trafico->actualizarFecha(21, $i->fechaPrevio);
                    }
                    if ($i->isValid("fechaDeposito")) {
                        $trafico->actualizarFecha(22, $i->fechaDeposito);
                    }
                    if ($i->isValid("fechaNotificacion")) {
                        $trafico->actualizarFecha(9, $i->fechaNotificacion);
                    }
                    if ($i->isValid("fechaLiberacion")) {
                        $trafico->actualizarFecha(8, $i->fechaLiberacion);
                    }
                    if ($i->isValid("fechaArribo")) {
                        $trafico->actualizarFecha(10, $i->fechaArribo);
                    }
                    if ($i->isValid("fechaPresentacion")) {
                        $trafico->actualizarFecha(5, $i->fechaPresentacion);
                    }
                    if ($i->isValid("fechaCitaDespacho")) {
                        $trafico->actualizarFecha(25, $i->fechaCitaDespacho);
                    }
                    if ($i->isValid("fechaEnvioDocumentos")) {
                        $trafico->actualizarFecha(27, $i->fechaEnvioDocumentos);
                    }
                    $this->_helper->json(array("success" => true, "id" => $i->idTrafico));
                } else {
                    throw new Exception("Unable to find record on DB!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

}
