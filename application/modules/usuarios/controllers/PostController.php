<?php

class Usuarios_PostController extends Zend_Controller_Action {

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
        $ajaxContext = $this->_helper->getHelper("AjaxContext");
        $ajaxContext->setAutoJsonSerialization(false)
                ->addActionContext("encriptacion-sello", array("json"))
                ->initContext();
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

    public function cambiarAlertaAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => "Digits",
                    "action" => "StringToLower",
                    "alert" => "StringToUpper",
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "alert" => "NotEmpty",
                    "content" => "NotEmpty",
                    "dateFrom" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
                    "dateTo" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
                    "action" => array("NotEmpty", new Zend_Validate_InArray(array("activate", "deactivate", "delete", "save"))),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("id")) {
                    $news = new Application_Model_NoticiasInternas();
                    if ($input->isValid("action") == "activate") {
                        if ($input->action == "activate") {
                            if ($news->activar($input->id)) {
                                $this->_helper->json(array("success" => true));
                            }
                        } elseif ($input->action == "deactivate") {
                            if ($news->desactivar($input->id)) {
                                $this->_helper->json(array("success" => true));
                            }
                        }
                        $this->_helper->json(array("success" => false));
                    }
                    if ($input->isValid("dateFrom")) {
                        if ($news->actualizarFechaDesde($input->id, $input->dateFrom)) {
                            $this->_helper->json(array("success" => true));
                        }
                        $this->_helper->json(array("success" => false));
                    }
                    if ($input->isValid("dateTo")) {
                        if ($news->actualizarFechaHasta($input->id, $input->dateTo)) {
                            $this->_helper->json(array("success" => true));
                        }
                        $this->_helper->json(array("success" => false));
                    }
                    if ($input->isValid("alert")) {
                        if ($news->actualizarAlerta($input->id, $input->alert)) {
                            $this->_helper->json(array("success" => true));
                        }
                        $this->_helper->json(array("success" => false));
                    }
                    if ($input->isValid("content")) {
                        if ($news->actualizarContenido($input->id, $input->content)) {
                            $this->_helper->json(array("success" => true));
                        }
                        $this->_helper->json(array("success" => false));
                    }
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function agregarAlertaAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $this->_helper->json(array("success" => true));
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function enviarComunicadoAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {

                $this->_helper->json(array("success" => true));
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function agregarClienteInhouseAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idUsuario" => "Digits",
                    "idCliente" => "Digits",
                );
                $v = array(
                    "idUsuario" => array("NotEmpty", new Zend_Validate_Int()),
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("idUsuario") && $input->isValid("idCliente")) {
                    $mapper = new Usuarios_Model_UsuarioInhouse();
                    if (!($mapper->verificar($input->idUsuario, $input->idCliente))) {
                        $mapper->agregar($input->idUsuario, $input->idCliente);
                        $this->_helper->json(array("success" => true));
                    } else {
                        $this->_helper->json(array("success" => false));
                    }
                } else {
                    $this->_helper->json(array("success" => false));
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
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("id")) {
                    $mapper = new Usuarios_Model_UsuarioInhouse();
                    $view = new Zend_View();
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/post/");
                    $arr = $mapper->obtenerClientes($input->id);
                    if (isset($arr) && !empty($arr)) {
                        $view->data = $arr;
                    }
                    $this->_helper->json(array("success" => true, "html" => $view->render("obtener-clientes.phtml")));
                } else {
                    $this->_helper->json(array("success" => false));
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function todasAduanasAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idUsuario" => "Digits",
                    "checked" => "StringToLower",
                );
                $v = array(
                    "idUsuario" => array("NotEmpty", new Zend_Validate_Int()),
                    "checked" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("idUsuario")) {
                    $checked = filter_var($input->checked, FILTER_VALIDATE_BOOLEAN);
                    $mapper = new Application_Model_UsuariosAduanasMapper();
                    if ($checked == true) {
                        $mapper->borrarTodas($input->idUsuario);
                        $mapper->agregar($input->idUsuario, 0, 0);
                    } else if ($checked == false) {
                        $mapper->borrarTodas($input->idUsuario);
                    }
                    $this->_helper->json(array("success" => true));
                } else {
                    $this->_helper->json(array("success" => false));
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerBodegasAction() {
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
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/post/");                    
                    $mppr = new Bodega_Model_BodegasUsuarios();
                    $view->data = $mppr->obtenerTodos($input->id);
                    $view->idUsuario = $input->id;
                    $this->_helper->json(array("success" => true, "html" => $view->render("obtener-bodegas.phtml")));
                }
                $this->_helper->json(array("success" => false));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function agregarBodegaAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $filters = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idUsuario" => array("Digits"),
                    "idBodega" => array("Digits"),
                );
                $validators = array(
                    "idUsuario" => array("NotEmpty", new Zend_Validate_Int()),
                    "idBodega" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($filters, $validators, $request->getPost());
                if ($input->isValid("idUsuario") && $input->isValid("idBodega")) {
                    $mppr = new Bodega_Model_BodegasUsuarios();
                    
                    if (!($mppr->verificar($input->idUsuario, $input->idBodega))) {
                        
                        if (($mppr->agregar($input->idUsuario, $input->idBodega))) {
                            $this->_helper->json(array("success" => true));
                        } else {
                            throw new Exception("El usuario ya cuenta con la bodega asignada.");
                        }
                        
                    } else {
                        throw new Exception("El usuario ya cuenta con la bodega asignada.");
                    }
                } else {
                    throw new Exception("Invalid input!");
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function removerBodegaAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $filters = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idUsuario" => array("Digits"),
                    "id" => array("Digits"),
                );
                $validators = array(
                    "idUsuario" => array("NotEmpty", new Zend_Validate_Int()),
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($filters, $validators, $request->getPost());
                if ($input->isValid("idUsuario") && $input->isValid("id")) {
                    $mppr = new Bodega_Model_BodegasUsuarios();
                    
                    if (($mppr->borrar($input->idUsuario, $input->id))) {
                        $this->_helper->json(array("success" => true));
                    } else {
                        throw new Exception("No se pudo borrar.");
                    }
                    
                } else {
                    throw new Exception("Invalid input!");
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerAduanasAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "patente" => "Digits",
                );
                $v = array(
                    "patente" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("patente")) {
                    $adus = new Trafico_Model_TraficoAduanasMapper();
                    $arr = $adus->obtenerAduanas($input->patente);
                    $this->_helper->json(array("success" => true, "aduanas" => json_encode($arr)));
                } else {
                    $this->_helper->json(array("success" => false));
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerPatentesSellosAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "rfc" => "StringToUpper",
                );
                $v = array(
                    "rfc" => array(new Zend_Validate_Regex("/^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/")),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("rfc")) {
                    $mapper = new Vucem_Model_VucemFirmanteMapper();
                    $arr = $mapper->patentesPorSello($input->rfc);
                    $html = new V2_Html();
                    $html->select("traffic-select-small", "patenteFiel");
                    $html->addSelectOption("", "---");
                    foreach ($arr as $item) {
                        $html->addSelectOption($item["patente"], $item["patente"]);
                    }
                    $this->_helper->json(array("success" => true, "html" => $html->getHtml()));
                } else {
                    $this->_helper->json(array("success" => false));
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerAduanasSellosAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "patente" => "Digits",
                    "rfc" => "StringToUpper",
                );
                $v = array(
                    "patente" => array("NotEmpty", new Zend_Validate_Int()),
                    "rfc" => array(new Zend_Validate_Regex("/^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/")),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("rfc") && $input->isValid("patente")) {
                    $mapper = new Vucem_Model_VucemFirmanteMapper();
                    $arr = $mapper->aduanasPorPatente($input->rfc, $input->patente);
                    $html = new V2_Html();
                    $html->select("traffic-select-small", "aduanaFiel");
                    $html->addSelectOption("", "---");
                    foreach ($arr as $item) {
                        $html->addSelectOption($item["aduana"], $item["aduana"]);
                    }
                    $this->_helper->json(array("success" => true, "html" => $html->getHtml()));
                } else {
                    $this->_helper->json(array("success" => false));
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function borrarInhouseRfcAction() {
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
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("id")) {
                    $mapper = new Usuarios_Model_UsuarioInhouse();
                    if (($mapper->borrar($input->id))) {
                        $this->_helper->json(array("success" => true));
                    }
                    $this->_helper->json(array("success" => false));
                } else {
                    $this->_helper->json(array("success" => false));
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function agregarPermisoSelloAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $filters = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idUsuario" => array("Digits"),
                    "patenteFiel" => array("Digits"),
                    "aduanaFiel" => array("Digits"),
                    "razonSocial" => array("StringToUpper"),
                );
                $validators = array(
                    "idUsuario" => array("NotEmpty", new Zend_Validate_Int()),
                    "patenteFiel" => array("NotEmpty", new Zend_Validate_Int()),
                    "aduanaFiel" => array("NotEmpty", new Zend_Validate_Int()),
                    "razonSocial" => array(new Zend_Validate_Alnum(true)),
                );
                $input = new Zend_Filter_Input($filters, $validators, $request->getPost());
                if ($input->isValid("idUsuario") && $input->isValid("aduanaFiel") && $input->isValid("patenteFiel") && $input->isValid("idUsuario")) {
                    $perm = new Vucem_Model_VucemPermisosMapper();
                    $model = new Vucem_Model_VucemFirmanteMapper();
                    $idFirmante = $model->firmanteId($input->razonSocial, $input->patenteFiel, $input->aduanaFiel);
                    if (isset($idFirmante["id"])) {
                        if (!($perm->verificarPermiso($input->idUsuario, $input->razonSocial, $idFirmante["id"], $input->patenteFiel, $input->aduanaFiel))) {
                            $added = $perm->agregarNuevoPermiso($input->idUsuario, $input->razonSocial, $idFirmante["id"], $input->patenteFiel, $input->aduanaFiel);
                            if ($added === true) {
                                $this->_helper->json(array("success" => true));
                            } else {
                                $this->_helper->json(array("success" => false));
                            }
                        } else {
                            $this->_helper->json(array("success" => false));
                        }
                    }
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function agregarUsuarioAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $flt = array(
                    "*" => array("StringTrim", "StripTags"),
                    "patenteUsuario" => array("Digits"),
                    "aduanaUsuario" => array("Digits"),
                    "rol" => array("Digits"),
                    "sispedimentos" => array("Digits"),
                    "email" => array("StringToLower"),
                    "usuario" => array("StringToLower"),
                );
                $vld = array(
                    "nombre" => array("NotEmpty", new Zend_Validate_Regex("~[^\\pL\d]+~u")),
                    "patenteUsuario" => array("NotEmpty", new Zend_Validate_Int()),
                    "aduanaUsuario" => array("NotEmpty", new Zend_Validate_Int()),
                    "rol" => array("NotEmpty", new Zend_Validate_Int()),
                    "sispedimentos" => array("NotEmpty", new Zend_Validate_Int()),
                    "departamento" => array("NotEmpty"),
                    "empresa" => array("NotEmpty", new Zend_Validate_Regex("/^[a-zA-Z0-9]+$/")),
                    "email" => array("NotEmpty", new Zend_Validate_EmailAddress()),
                    "usuario" => array("NotEmpty", new Zend_Validate_Regex("/^[a-zA-Z0-9]+$/")),
                    "password" => array("NotEmpty"),
                    "confirm" => array("NotEmpty", new Zend_Validate_Identical(array("token" => $this->_request->getPost("password")))),
                );
                $input = new Zend_Filter_Input($flt, $vld, $request->getPost());
                if ($input->isValid("patenteUsuario") && $input->isValid("patenteUsuario") && $input->isValid("rol") && $input->isValid("password") && $input->isValid("confirm") && $input->isValid("usuario") && $input->isValid("nombre")) {
                    $mapper = new Usuarios_Model_UsuariosMapper();
                    if (!($mapper->verifyUser($input->usuario))) {
                        if (($id = $mapper->addNewUser(html_entity_decode($input->nombre), $input->email, strtolower($input->usuario), $input->patenteUsuario, $input->aduanaUsuario, $input->empresa, $input->password, $input->rol, $input->empresa, html_entity_decode($input->departamento), $input->sispedimentos))) {
                            $this->_helper->json(array("success" => true, "id" => $id));
                        }
                    } else {
                        $this->_helper->json(array("success" => false, "message" => "El usuario existe."));
                    }
                    $this->_helper->json(array("success" => false));
                } else {
                    throw new Exception(json_encode($input->getMessages()));
                }
            } else {
                throw new Exception("Ivalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function actualizarUsuarioAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $flt = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idUsuario" => array("Digits"),
                    "patenteUsuario" => array("Digits"),
                    "aduanaUsuario" => array("Digits"),
                    "sispedimentos" => array("Digits"),
                    "rol" => array("Digits"),
                    "estatus" => array("Digits"),
                    "usuario" => array("StringToLower"),
                );
                $vld = array(
                    "idUsuario" => array("NotEmpty", new Zend_Validate_Int()),
                    "patenteUsuario" => array("NotEmpty", new Zend_Validate_Int()),
                    "aduanaUsuario" => array("NotEmpty", new Zend_Validate_Int()),
                    "nombre" => array("NotEmpty", new Zend_Validate_Regex("/^[a-zA-Z0-9\s]+$/")),
                    "sispedimentos" => array("NotEmpty", new Zend_Validate_Int()),
                    "departamento" => array("NotEmpty"),
                    "nombre" => array("NotEmpty", new Zend_Validate_Regex("/^[a-zA-Z0-9.\s]+$/")),
                    "empresa" => array("NotEmpty", new Zend_Validate_Regex("/^[a-zA-Z0-9]+$/")),
                    "email" => array("NotEmpty", new Zend_Validate_EmailAddress()),
                    "rol" => array("NotEmpty", new Zend_Validate_Int()),
                    "estatus" => array("NotEmpty", new Zend_Validate_Int()),
                    "usuario" => array("NotEmpty", new Zend_Validate_Regex("/^[a-zA-Z0-9]+$/")),
                    "password" => array("NotEmpty"),
                    "confirm" => array("NotEmpty", new Zend_Validate_Identical(array("token" => $this->_request->getPost("password")))),
                );
                $input = new Zend_Filter_Input($flt, $vld, $request->getPost());
                if ($input->isValid("patenteUsuario") && $input->isValid("aduanaUsuario") && $input->isValid("idUsuario") && $input->isValid("rol") && $input->isValid("usuario")) {
                    $com = new Application_Model_CustomsMapper();
                    $empresa = $com->getCompanyName($input->empresa);
                    $users = new Usuarios_Model_UsuariosMapper();
                    $updated = $users->updateUserBasic($input->idUsuario, $input->nombre, $input->email, $input->usuario, $input->patenteUsuario, $input->aduanaUsuario, $input->empresa, $empresa, html_entity_decode($input->departamento), $input->rol, $input->sispedimentos, $input->estatus);
                    if ($input->isValid("password") && $input->isValid("confirm")) {
                        $updated = $users->updatePassword($input->idUsuario, $input->password);
                    }
                    if ($updated === true) {
                        $this->_helper->json(array("success" => true, "id" => $input->idUsuario));
                    }
                } else {
                    $this->_helper->json(array("success" => false, "message" => "Invalid input!"));
                }
            } else {
                $this->_helper->json(array("success" => false, "message" => "Invalid request type!"));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerPasswordAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idUsuario" => array("Digits"),
                );
                $v = array(
                    "idUsuario" => array("NotEmpty", new Zend_Validate_Int()),
                    "password" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idUsuario") && $input->isValid("password")) {
                    if ($input->password == "jvaldez") {
                        $mapper = new Usuarios_Model_UsuariosMapper();
                        if (($pass = $mapper->obtenerPassword($input->idUsuario))) {
                            $this->_helper->json(array("success" => true, "pass" => "La contraseña del usuario es: {$pass}"));
                        }
                    } else {
                        $this->_helper->json(array("success" => false, "pass" => "La contraseña es incorrecta."));
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

    public function actualizarDocumentosAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idUsuario" => array("Digits"),
                );
                $v = array(
                    "idUsuario" => array("NotEmpty", new Zend_Validate_Int()),
                    "ids" => "NotEmpty",
                );
                $i = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($i->isValid("idUsuario") && $i->isValid("ids")) {
                    $mppr = new Usuarios_Model_UsuariosDocumentos();
                    if (!($mppr->verificar($i->idUsuario))) {
                        $mppr->agregar(array("idUsuario" => $i->idUsuario, "documentos" => json_encode($i->ids)));
                    } else {
                        $mppr->actualizar($i->idUsuario, array("documentos" => json_encode($i->ids)));
                    }
                } else {
                    throw new Exception("Invalid input!");
                }
                $this->_helper->json(array("success" => true));
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function cerrarSesionAction() {
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
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("id")) {
                    $mppr = new Application_Model_UsuarioSesiones();
                    if ($mppr->borrar($input->id)) {
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
    
    public function aplicacionSubirAction() {
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
                    "appName" => "NotEmpty",
                    "sistemaOperativo" => "NotEmpty",
                    "versionName" => "NotEmpty",
                    "versionCode" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("sistemaOperativo") && $input->isValid("appName") && $input->isValid("versionName") && $input->isValid("versionCode")) {
                    $mppr = new Webservice_Model_AppVersion();
                    $upload = new Zend_File_Transfer_Adapter_Http();
                    $upload->addValidator("Count", false, array("min" => 1, "max" => 1))
                            ->addValidator("Size", false, array("min" => "1", "max" => "20MB"))
                            ->addValidator("Extension", false, array("extension" => "apk", "case" => false));
                    $files = $upload->getFileInfo();
                    if (APPLICATION_ENV == "production") {
                        $directory = "/home/samba-share/app";
                    } else if (APPLICATION_ENV == "staging") {
                        $directory = "/home/samba-share/app";
                    } else {
                        $directory = "D:\\Tmp\\movil";
                    }
                    $upload->setDestination($directory);
                    foreach ($files as $fieldname => $fileinfo) {
                        if (($upload->isUploaded($fieldname)) && ($upload->isValid($fieldname))) {
                            $upload->receive($fieldname);
                            if (file_exists($directory . DIRECTORY_SEPARATOR . basename($fileinfo["name"]))) {
                                if (!$mppr->verificar($input->sistemaOperativo, $input->versionName, $input->versionCode, $input->appName, basename($fileinfo["name"]))) {
                                    $arr = array(
                                        "sistemaOperativo" => $input->sistemaOperativo,
                                        "versionName" => $input->versionName,
                                        "versionCode" => $input->versionCode,
                                        "appName" => $input->appName,
                                        "filename" => basename($fileinfo["name"]),
                                        "creado" => date("Y-m-d H:i:s"),
                                    );
                                    if ($mppr->agregar($arr)) {
                                        $this->_helper->json(array("success" => true));
                                    } else {
                                        $this->_helper->json(array("success" => false, "message" => "No se pudo agregar."));                                        
                                    }
                                } else {
                                    $this->_helper->json(array("success" => false, "message" => "Ya existe el archivo."));
                                }
                            } else {
                                $this->_helper->json(array("success" => false, "message" => "Archivo no se recibio."));
                            }
                        }
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

    public function purgarQueueAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "queue" => "StringToLower"
                );
                $v = array(
                    "queue" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("queue")) {
                    $rabbit = new OAQ_Workers_Queues();
                    if ($rabbit->deleteQueue($input->queue)) {
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

    public function actualizarContactosAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => "Digits",
                    "type" => array("StringToLower"),
                    "value" => array("Digits"),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "type" => array("NotEmpty"),
                    "value" => array(new Zend_Validate_InArray(array(1, 0))),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("id") && $input->isValid("type") && $input->isValid("value")) {
                    $mppr = new Trafico_Model_ContactosMapper();
                    $arr = array(
                        "{$input->type}" => ($input->value == 1) ? 1 : 0
                    );
                    if (($mppr->actualizar($input->id, $arr))) {
                        $this->_helper->json(array("success" => true));
                    } else {
                        throw new Exception("No se puede actualizar.");
                    }
                } else {
                    throw new Exception("Datos de solicitud no son válidos.");
                }
            } else {
                throw new Exception("Tipo de solicitud no es válida.");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function borrarContactoAction() {
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
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("id")) {
                    $mppr = new Trafico_Model_ContactosMapper();
                    if (($mppr->delete($input->id))) {
                        $this->_helper->json(array("success" => true));
                    } else {
                        throw new Exception("No se puede borrar.");
                    }
                } else {
                    throw new Exception("Datos de solicitud no son válidos.");
                }
            } else {
                throw new Exception("Tipo de solicitud no es válida.");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function nuevoContactoAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/post/");
                $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");

                $mppr = new Trafico_Model_TraficoAduanasMapper();
                $view->aduanas = $mppr->obtenerTodas();

                $tmppr = new Trafico_Model_TipoContactoMapper();
                $view->tipoContactos = $tmppr->obtenerTodos();

                $this->_helper->json(array("success" => true, "html" => $view->render("nuevo-contacto.phtml")));
            } else {
                throw new Exception("Tipo de solicitud no es válida.");
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
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                );
                $v = array(
                    "nombre" => array("NotEmpty"),
                    "email" => array("NotEmpty"),
                    "idAduana" => array("NotEmpty"),
                    "tipoContacto" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("nombre") && $input->isValid("email")
                    && $input->isValid("idAduana") && $input->isValid("tipoContacto")) {

                    $mppr = new Trafico_Model_ContactosMapper();

                    $arr = array(
                        "nombre" => $input->nombre,
                        "email" => $input->email,
                        "idAduana" => $input->idAduana,
                        "tipoContacto" => $input->tipoContacto,
                        "creado" => date("Y-m-d H:is"),
                        "creadoPor" => $this->_session->username
                    );

                    if (($mppr->agregar($arr))) {
                        $this->_helper->json(array("success" => true));
                    }

                } else {
                    throw new Exception("Datos de solicitud no son válidos.");
                }
            } else {
                throw new Exception("Tipo de solicitud no es válida.");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

}
