<?php

class Usuarios_AjaxController extends Zend_Controller_Action {

    protected $_session;
    protected $_config;
    protected $_appconfig;

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

    public function encriptacionSelloAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $filters = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                    "crypt" => array("Digits"),
                );
                $validators = array(
                    "id" => new Zend_Validate_Int(),
                    "crypt" => new Zend_Validate_Int(),
                );
                $input = new Zend_Filter_Input($filters, $validators, $request->getPost());
                if ($input->isValid()) {
                    $mapper = new Vucem_Model_VucemFirmanteMapper();
                    switch ($input->crypt) {
                        case 1:
                            $mapper->update($input->id, array("sha" => "sha256"));
                            break;
                        default:
                            $mapper->update($input->id, array("sha" => null));
                            break;
                    }
                    $this->_helper->json(array("success" => true));
                    return;
                }
                $this->_helper->json(array("success" => false));
                return;
            }
            $this->_helper->json(array("success" => false));
            return;
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function borrarSelloAction() {
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
                $vld = array(
                    "id" => new Zend_Validate_Int(),
                );
                $input = new Zend_Filter_Input($flt, $vld, $request->getPost());
                if ($input->isValid("id")) {
                    $mapper = new Vucem_Model_VucemFirmanteMapper();
                    $stmt = $mapper->delete($input->id);
                    if ($stmt) {
                        $this->_helper->json(array("success" => true));
                    }
                }
                $this->_helper->json(array("success" => false));
            }
            $this->_helper->json(array("success" => false));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function cargarMenuAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $filters = array(
                    "*" => array("StringTrim", "StripTags"),
                    "rol" => array("StringtoLower"),
                );
                $input = new Zend_Filter_Input($filters, null, $request->getPost());
                if ($input->isValid("rol")) {
                    $view = new Zend_View();
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/ajax/");
                    $mapper = new Application_Model_MenusMapper();
                    $arr1 = $mapper->obtenerMenuUsuario("super");
                    $arr2 = $mapper->obtenerMenuUsuario($input->rol);
                    $presence = array();
                    foreach ($arr1 as $k => $m) {
                        foreach ($m["acciones"] as $i => $a) {
                            $b = $arr1[$k]["acciones"][$i];
                            if (isset($arr2[$k]["acciones"][$i])) {
                                $b["presencia"] = true;
                            } else {
                                $b["presencia"] = false;
                            }
                            $presence[$k]["nombre"] = $arr1[$k]["nombre"];
                            $presence[$k]["acciones"][$i] = $b;
                        }
                    }
                    $view->rol = $mapper->obtenerIdRol($input->rol);
                    $view->menu = $presence;
                    $this->_helper->json(array("success" => true, "html" => $view->render("menus.phtml")));
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
            $this->_helper->json(array("success" => false));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function editarMenuAction() {
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
                $input = new Zend_Filter_Input($filters, null, $request->getPost());
                if ($input->isValid()) {
                    $view = new Zend_View();
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/ajax/");
                    $mapper = new Application_Model_MenusMapper();
                    $menu = $mapper->obtenerMenu($input->id);
                    $view->menu = $menu;
                    $this->_helper->json(array("success" => true, "html" => $view->render("editar-menu.phtml")));
                } else {
                    throw new Exception("Invalid input!");
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function guardarMenuAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $filters = array(
                    "*" => array("StringTrim", "StripTags"),
                    "orden" => array("Digits"),
                    "idAccion" => array("Digits"),
                );
                $input = new Zend_Filter_Input($filters, null, $request->getPost());
                if ($input->isValid()) {
                    $mapper = new Application_Model_MenusMapper();
                    $up = $mapper->actualizarAccion($input->idAccion, $input->nombre, $input->orden);
                    if ($up === true) {
                        $this->_helper->json(array("success" => true));
                        return;
                    }
                    $this->_helper->json(array("success" => true));
                    return;
                } else {
                    throw new Exception("Invalid input!");
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function agregarMenuAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $filters = array(
                    "*" => array("StringTrim", "StripTags"),
                );
                $validator = array(
                    "*" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($filters, $validator, $request->getPost());
                if ($input->isValid()) {
                    $mapper = new Application_Model_MenusMapper();
                    $found = $mapper->comprobarAccion($input->controller, $input->action);
                    if (!$found) {
                        $idAccion = $mapper->agregarAccion($input->controller, $input->action, $input->name);
                        $added = $mapper->agregarAccionRol($idAccion, 1);
                    }
                    if ($added === true) {
                        $this->_helper->json(array("success" => true));
                        return;
                    } else {
                        $this->_helper->json(array("success" => false, "message" => "Cannot be added!"));
                    }
                } else {
                    throw new Exception("Invalid input!");
                }
                $this->_helper->json(array("success" => false, "message" => "Invalid input!"));
                return;
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerSellosAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int())
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id")) {
                    $view = new Zend_View();
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/ajax/");
                    $perm = new Vucem_Model_VucemPermisosMapper();
                    if (($permisos = $perm->obtenerPermisos($input->id))) {
                        $view->permisos = $permisos;
                        $view->userId = $input->id;
                    }
                    $this->_helper->json(array("success" => true, "html" => $view->render("obtener-sellos.phtml")));
                }
                $this->_helper->json(array("success" => false));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerPrefijosAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $mapper = new Archivo_Model_RepositorioPrefijos();
                $arr = $mapper->fetchAll();
                if (isset($arr) && !empty($arr)) {
                    $view = new Zend_View();
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/ajax/");
                    $view->data = $arr;
                    $this->_helper->json(array("success" => true, "html" => $view->render("obtener-prefijos.phtml")));
                }
                $this->_helper->json(array("success" => false));
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerAduanasTraficoAction() {
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
                    "id" => array("NotEmpty", new Zend_Validate_Int())
                );
                $input = new Zend_Filter_Input($filters, $validators, $request->getPost());
                if ($input->isValid("id")) {
                    $view = new Zend_View();
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/ajax/");
                    $table = new Trafico_Model_TraficoUsuClientesMapper();
                    $clientes = $table->obtenerClientesAduanaUsuario($input->id);
                    $view->data = $clientes;
                    $view->idUsuario = $input->id;
                    $this->_helper->json(array("success" => true, "html" => $view->render("obtener-aduanas-trafico.phtml")));
                }
                $this->_helper->json(array("success" => false));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerValidadorAsignadoAction() {
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
                    "id" => array("NotEmpty", new Zend_Validate_Int())
                );
                $input = new Zend_Filter_Input($filters, $validators, $request->getPost());
                if ($input->isValid("id")) {
                    $view = new Zend_View();
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/ajax/");
                    $tbl = new Trafico_Model_TraficoUsuAduanasValMapper();
                    $rows = $tbl->obtenerAduanasUsuarioDirectorio($input->id);
                    $view->data = $rows;
                    $view->idUsuario = $input->id;
                    $this->_helper->json(array("success" => true, "html" => $view->render("obtener-validador-asignado.phtml")));
                }
                $this->_helper->json(array("success" => false));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerRepositoriosAction() {
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
                    "id" => array("NotEmpty", new Zend_Validate_Int())
                );
                $input = new Zend_Filter_Input($filters, $validators, $request->getPost());
                if ($input->isValid("id")) {
                    $view = new Zend_View();
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/ajax/");
                    $model = new Application_Model_UsuariosAduanasMapper();
                    $aduanas = $model->aduanasAsignadas($input->id);
                    $todas = false;
                    if (isset($aduanas[0])) {
                        if ($aduanas[0]["aduana"] == 0) {
                            $todas = true;
                        }
                    }
                    $view->data = $aduanas;
                    $this->_helper->json(array("success" => true, "html" => $view->render("obtener-repositorios.phtml"), "todas" => $todas));
                }
                $this->_helper->json(array("success" => false));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function agregarNuevaAduanaAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $filters = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idUsuario" => array("Digits"),
                    "idAduana" => array("Digits"),
                    "idCliente" => array("Digits"),
                );
                $validators = array(
                    "idUsuario" => array("NotEmpty", new Zend_Validate_Int()),
                    "idAduana" => array("NotEmpty", new Zend_Validate_Int()),
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($filters, $validators, $request->getPost());
                if ($input->isValid("idUsuario") && $input->isValid("idAduana") && $input->isValid("idCliente")) {
                    $tblUsuCliente = new Trafico_Model_TraficoUsuClientesMapper();
                    $tblUsuAduanas = new Trafico_Model_TraficoUsuAduanasMapper();
                    if (!($found = $tblUsuCliente->verificar($input->idUsuario, $input->idCliente, $input->idAduana))) {
                        $tblUsuCliente->agregar(array("idUsuario" => $input->idUsuario, "idCliente" => $input->idCliente, "idAduana" => $input->idAduana));
                    }
                    if (isset($input->tipoOperacion)) {
                        
                    } else {
                        if (!($tblUsuAduanas->verificar($input->idUsuario, $input->idCliente, $input->idAduana, "TOCE.IMP"))) {
                            $tblUsuAduanas->agregar(array("idUsuario" => $input->idUsuario, "idCliente" => $input->idCliente, "idAduana" => $input->idAduana, "tipo" => "TOCE.IMP", "descripcion" => "Importación"));
                        }
                        if (!($tblUsuAduanas->verificar($input->idUsuario, $input->idCliente, $input->idAduana, "TOCE.EXP"))) {
                            $tblUsuAduanas->agregar(array("idUsuario" => $input->idUsuario, "idCliente" => $input->idCliente, "idAduana" => $input->idAduana, "tipo" => "TOCE.EXP", "descripcion" => "Exportación"));                            
                        }
                    }
                    $this->_helper->json(array("success" => true));
                } else {
                    throw new Exception("Invalid input!");
                }
                $this->_helper->json(array("success" => false));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function agregarValidadorAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $filters = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idUsuario" => array("Digits"),
                    "idAduanaValidador" => array("Digits"),
                );
                $validators = array(
                    "idUsuario" => array("NotEmpty", new Zend_Validate_Int()),
                    "idAduanaValidador" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($filters, $validators, $request->getPost());
                if ($input->isValid("idUsuario") && $input->isValid("idAduanaValidador")) {
                    $tblUsuAduanas = new Trafico_Model_TraficoUsuAduanasValMapper();
                    $newValidator = array(
                        "idUsuario" => $input->idUsuario,
                        "idAduana" => $input->idAduanaValidador,
                        "idCliente" => 1,
                    );
                    if (!($tblUsuAduanas->verificar($newValidator["idUsuario"], $newValidator["idCliente"], $newValidator["idAduana"], "TOCE.IMP"))) {
                        $newValidator["tipo"] = "TOCE.IMP";
                        $newValidator["descripcion"] = "Importación";
                        $tblUsuAduanas->agregar($newValidator);
                    }
                    if (!($tblUsuAduanas->verificar($newValidator["idUsuario"], $newValidator["idCliente"], $newValidator["idAduana"], "TOCE.EXP"))) {
                        $newValidator["tipo"] = "TOCE.EXP";
                        $newValidator["descripcion"] = "Exportación";
                        $tblUsuAduanas->agregar($newValidator);
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

    public function borrarPermisoAction() {
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
                    $model = new Vucem_Model_VucemPermisosMapper();
                    $deleted = $model->borrarPermiso($input->id);
                    if ($deleted === true) {
                        $this->_helper->json(array("success" => true));
                    } else {
                        $this->_helper->json(array("success" => false));
                    }
                }
                $this->_helper->json(array("success" => false));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function borrarRepositorioAction() {
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
                    $model = new Application_Model_UsuariosAduanasMapper();
                    $deleted = $model->borrarAduana($input->id);
                    if ($deleted === true) {
                        $this->_helper->json(array("success" => true));
                    } else {
                        $this->_helper->json(array("success" => false));
                    }
                }
                $this->_helper->json(array("success" => false));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function removerValidadorAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $filters = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                    "patente" => array("Digits"),
                    "aduana" => array("Digits"),
                );
                $validators = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "patente" => array("NotEmpty", new Zend_Validate_Int()),
                    "aduana" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($filters, $validators, $request->getPost());
                if ($input->isValid("id") && $input->isValid("patente") && $input->isValid("aduana")) {
                    $tbl = new Trafico_Model_TraficoAduanasMapper();
                    $model = new Trafico_Model_TraficoUsuAduanasValMapper();
                    $idAduana = $tbl->idAduana($input->patente, $input->aduana);
                    if (isset($idAduana)) {
                        $model->removerValidador($idAduana, $input->id);
                    }
                    $this->_helper->json(array("success" => true));
                }
                $this->_helper->json(array("success" => false));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function removerClienteUsuarioAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $filters = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idUsuario" => array("Digits"),
                    "idCliente" => array("Digits"),
                    "idAduana" => array("Digits"),
                );
                $validators = array(
                    "idUsuario" => array("NotEmpty", new Zend_Validate_Int()),
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                    "idAduana" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($filters, $validators, $request->getPost());
                if ($input->isValid("idUsuario") && $input->isValid("idCliente") && $input->isValid("idAduana")) {
                    $tbl = new Trafico_Model_TraficoUsuClientesMapper();
                    $mdl = new Trafico_Model_TraficoUsuAduanasMapper();
                    if (($f = $tbl->verificar($input->idUsuario, $input->idCliente, $input->idAduana))) {
                        if ($f === true) {
                            if (($x = $mdl->verificar($input->idUsuario, $input->idCliente, $input->idAduana))) {
                                $mdl->remover($input->idUsuario, $input->idCliente, $input->idAduana);
                            }
                            if (!($mdl->existe($input->idUsuario, $input->idCliente, $input->idAduana))) {
                                $tbl->remover($input->idUsuario, $input->idCliente, $input->idAduana);
                            }
                            $this->_helper->json(array("success" => true));
                        }
                    }
                    $this->_helper->json(array("success" => true));
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerAduanasAction() {
        try {
            $rfc = $this->_getParam("rfc");
            $patente = $this->_getParam("patente");
            if (isset($patente)) {
                $model = new Vucem_Model_VucemFirmanteMapper();
                $aduanas = $model->aduanasPorPatente($rfc, $patente);
                if (isset($aduanas) && !empty($aduanas)) {
                    $html = "<select class=\"traffic-select-small\" id=\"aduana\" name=\"aduana\">";
                    $html .= "<option label=\"-- Seleccionar --\" value=\"\">-- Seleccionar --</option>";
                    foreach ($aduanas as $aduana) {
                        $html .= "<option value=\"" . $aduana["aduana"] . "\">" . $aduana["aduana"] . "</option>";
                    }
                    $html .= "</select>";
                    $this->_helper->json(array("success" => true, "html" => $html));
                } else {
                    $html = "<select disabled=\"disabled\" class=\"traffic-select-small\" id=\"aduana\" name=\"aduana\"><option label=\"-- Seleccionar --\" value=\"\">-- Seleccionar --</option></select>";
                    $this->_helper->json(array("success" => false, "html" => $html));
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerPatentesAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $rfc = $this->_getParam("rfc");
        if (isset($rfc)) {
            $model = new Vucem_Model_VucemFirmanteMapper();
            $patentes = $model->patentesPorSello($rfc);
            if (isset($patentes) && !empty($patentes)) {
                $html = "<select class=\"traffic-select-small\" id=\"patente\" name=\"patente\">";
                $html .= "<option label=\"-- Seleccionar --\" value=\"\">-- Seleccionar --</option>";
                foreach ($patentes as $patente) {
                    $html .= "<option value=\"" . $patente["patente"] . "\">" . $patente["patente"] . "</option>";
                }
                $html .= "</select>";
                $this->_helper->json(array("success" => true, "html" => $html));
            } else {
                $html = "<select disabled=\"disabled\" class=\"traffic-select-small\" id=\"patente\" name=\"patente\"><option label=\"-- Seleccionar --\" value=\"\">-- Seleccionar --</option></select>";
                $this->_helper->json(array("success" => false, "html" => $html));
            }
        }
    }

    public function obtenerAduanasDisponiblesAction() {
        try {
            $patente = $this->_getParam("patente");
            if (isset($patente)) {
                $model = new Application_Model_CustomsMapper();
                $aduanas = $model->getAllCustomsByPatent($patente);
                if (isset($aduanas) && !empty($aduanas)) {
                    $html = "<select class=\"traffic-select-large\" id=\"aduana_disponible\" name=\"aduana_disponible\">";
                    $html .= "<option label=\"-- Seleccionar --\" value=\"\">-- Seleccionar --</option>";
                    foreach ($aduanas as $aduana) {
                        $html .= "<option value=\"" . $aduana["aduana"] . "\">" . $aduana["aduana"] . " - " . $aduana["ubicacion"] . "</option>";
                    }
                    $html .= "</select>";
                    $this->_helper->json(array("success" => true, "html" => $html));
                } else {
                    $html = "<select disabled=\"disabled\" class=\"traffic-select-large\" id=\"aduana_disponible\" name=\"aduana_disponible\"><option label=\"-- Seleccionar --\" value=\"\">-- Seleccionar --</option></select>";
                    $this->_helper->json(array("success" => false, "html" => $html));
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function agregarPrefijoAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $filters = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idDocumento" => array("Digits"),
                    "prefijo" => array("StringToUpper"),
                );
                $validators = array(
                    "idDocumento" => array("NotEmpty", new Zend_Validate_Int()),
                    "prefijo" => array(new Zend_Validate_Regex("/^[A-Z._]+$/"), new Zend_Validate_StringLength(array("min" => 1, "max" => 8))),
                );
                $input = new Zend_Filter_Input($filters, $validators, $request->getPost());
                if ($input->isValid("idDocumento") && $input->isValid("prefijo")) {
                    $mapper = new Archivo_Model_RepositorioPrefijos();
                    $table = new Archivo_Model_Table_RepositorioPrefijos(array(
                        "idDocumento" => $input->idDocumento,
                        "prefijo" => $input->prefijo
                    ));
                    $mapper->find($table);
                    if (null === ($table->getId())) {
                        $mapper->save($table);
                        $this->_helper->json(array("success" => true));
                    } else {
                        $this->_helper->json(array("success" => false, "error" => "El documento ya tiene un prefijo."));
                    }
                } else {
                    $this->_helper->json(array("success" => false, "error" => "Prefijo no cumple con las restricciones."));
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function agregarRepositorioAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $filters = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idUsuario" => array("Digits"),
                    "patentesExpediente" => array("Digits"),
                    "aduanasExpediente" => array("Digits"),
                );
                $validators = array(
                    "idUsuario" => array("NotEmpty", new Zend_Validate_Int()),
                    "patentesExpediente" => array("NotEmpty", new Zend_Validate_Int()),
                    "aduanasExpediente" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($filters, $validators, $request->getPost());
                if ($input->isValid("idUsuario") && $input->isValid("patentesExpediente") && $input->isValid("aduanasExpediente")) {
                    $model = new Application_Model_UsuariosAduanasMapper();
                    if (!($model->verificar($input->idUsuario, $input->patentesExpediente, $input->aduanasExpediente))) {
                        $added = $model->agregar($input->idUsuario, $input->patentesExpediente, $input->aduanasExpediente);
                        if ($added === true) {
                            $this->_helper->json(array("success" => true));
                        } else {
                            $this->_helper->json(array("success" => false));
                        }
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

    public function clientesAduanasTraficoAction() {
        try {
            $idAduana = $this->_getParam("idAduana", null);
            if (isset($idAduana)) {
                $cust = new Trafico_Model_TraficoCliAduanasMapper();
                $rows = $cust->clientesAduana($idAduana);
                if (isset($rows) && !empty($rows)) {
                    $html = "<select class=\"traffic-select-large\" id=\"idCliente\" name=\"idCliente\">";
                    $html .= "<option label=\" --Seleccionar --\" value=\"\">-- Seleccionar --</option>";
                    foreach ($rows as $item) {
                        $html .= "<option value=\"" . $item["idCliente"] . "\">" . $item["nombre"] . "</option>";
                    }
                    $html .= "</select>";
                    $this->_helper->json(array("success" => true, "html" => $html));
                } else {
                    $html = "<select disabled=\"disabled\" id=\"idCliente\" class=\"traffic-select-large\" name=\"idCliente\"><option label=\" --Seleccionar --\" value=\"\">-- Seleccionar --</option></select>";
                    $this->_helper->json(array("success" => false, "html" => $html));
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function menuAgregarAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $filters = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idAccion" => array("Digits"),
                    "idRol" => array("Digits")
                );
                $validators = array(
                    "idAccion" => array("NotEmpty", new Zend_Validate_Int()),
                    "idRol" => array("NotEmpty", new Zend_Validate_Int())
                );
                $input = new Zend_Filter_Input($filters, $validators, $request->getPost());
                if ($input->isValid("idAccion") && $input->isValid("idRol")) {
                    $mapper = new Application_Model_MenusMapper();
                    if (!($mapper->verificar($input->idAccion, $input->idRol))) {
                        $added = $mapper->agregarAccionRol($input->idAccion, $input->idRol);
                        if ($added === true) {
                            $this->_helper->json(array("success" => true));
                        }
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

    public function menuRemoverAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $filters = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idAccion" => array("Digits"),
                    "idRol" => array("Digits")
                );
                $validators = array(
                    "idAccion" => array("NotEmpty", new Zend_Validate_Int()),
                    "idRol" => array("NotEmpty", new Zend_Validate_Int())
                );
                $input = new Zend_Filter_Input($filters, $validators, $request->getPost());
                if ($input->isValid("idAccion") && $input->isValid("idRol")) {
                    $mapper = new Application_Model_MenusMapper();
                    if (!($mapper->verificar($input->idAccion, $input->idRol))) {
                        $added = $mapper->removerAccionRol($input->idAccion, $input->idRol);
                        if ($added === true) {
                            $this->_helper->json(array("success" => true));
                        }
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
    
    public function cambiarArchivoAction() {
        $this->_helper->viewRenderer->setNoRender(false);
        try {
            $filters = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
                "type" => array("StringToLower"),
            );
            $validators = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
                "type" => array("NotEmpty", new Zend_Validate_InArray(array("cert", "key"))),
            );
            $input = new Zend_Filter_Input($filters, $validators, $this->_request->getParams());
            if ($input->isValid("id") && $input->isValid("type")) {
                $this->view->id = $input->id;
                $this->view->type = $input->type;
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function actualizarArchivoAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $flt = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                    "type" => array("StringToLower"),
                );
                $vld = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "type" => array("NotEmpty", new Zend_Validate_InArray(array("cert", "key"))),
                );
                $input = new Zend_Filter_Input($flt, $vld, $request->getPost());
                if (!$input->isValid("id") && !$input->isValid("type")) {
                    throw new Exception("Invalid input!");
                }                
                $misc = new OAQ_VucemEnh();
                $sello = new Vucem_Model_VucemFirmanteMapper();
                $k = "5203bfec0c3db@!b2295";
                $directory = "/tmp";
                $upload = new Zend_File_Transfer_Adapter_Http();
                $upload->addValidator("Count", false, array("min" => 1, "max" => 1))
                        ->addValidator("Size", false, array('min' => '1kB', 'max' => '1MB'))
                        ->addValidator("Extension", false, array("extension" => "cer,key", "case" => false));
                $upload->setDestination($directory);
                $files = $upload->getFileInfo();
                foreach ($files as $file => $fileinfo) {
                    if (($upload->isUploaded($file)) && ($upload->isValid($file))) {
                        $upload->receive($file);
                        $filename = $fileinfo["destination"] . DIRECTORY_SEPARATOR . $fileinfo["name"];
                        if($input->type == "cert") {
                            $arr = $misc->analizarCertificado($filename);
                            if(isset($arr) && !empty($arr)) {
                                $content = base64_encode(file_get_contents($filename));
                                $data = array(
                                    "certificado" => new Zend_Db_Expr("AES_ENCRYPT('{$content}','{$k}')"),
                                    "certificado_nom" => $fileinfo["name"],
                                    "valido_desde" => $arr["valido_desde"],
                                    "valido_hasta" => $arr["valido_hasta"],
                                );
                                $sello->update($input->id, $data);
                            }
                            $this->_helper->json(array("success" => true));
                        }
                        if($input->type == "key") {
                            $data = array(
                                "key" => new Zend_Db_Expr("AES_ENCRYPT('{$content}','{$k}')"),
                                "key_nom" => $fileinfo["name"]
                            );
                            $sello->update($input->id, $data);
                        }
                    } else {
                        $this->_helper->json(array("success" => false, "message" => $upload->getErrors()));
                    }
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {

        }
    }
    
    public function viewAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "patente" => "Digits",
                "aduana" => "Digits",
                "pedimento" => "Digits",
                "fiel" => "Digits",
                "id" => "Digits",
                "type" => "StringToLower",
                "email" => "StringToLower",
            );
            $v = array(
                "patente" => array("NotEmpty", new Zend_Validate_Int()),
                "aduana" => array("NotEmpty", new Zend_Validate_Int()),
                "pedimento" => array("NotEmpty", new Zend_Validate_Int()),
                "fiel" => array("NotEmpty", new Zend_Validate_Int()),
                "id" => array("NotEmpty", new Zend_Validate_Int()),
                "type" => new Zend_Validate_InArray(array("cove", "edoc", "consultaedoc", "consultaped", "descargaedoc")),
                "email" => array("NotEmpty", new Zend_Validate_EmailAddress()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("fiel")) {
                $mapper = new Vucem_Model_VucemFirmanteMapper();
                $view = new Zend_View();
                if ($input->isValid("type") && $input->isValid("id") && $input->isValid("email")) {
                    $sello = $mapper->obtenerDetalleFirmanteId($input->fiel);
                    $conv = new OAQ_Conversion();
                    if($input->type == "edoc") {
                        $mapper = new Vucem_Model_VucemEdocMapper();
                        $documento = $mapper->archivoDigitalizado($input->id);
                        $xml = new OAQ_Xml(false, true);
                        $archivo = array(
                            "idTipoDocumento" => $documento["tipoDoc"],
                            "nomArchivo" => $documento["nomArchivo"],
                            "archivo" => $documento["archivo"],
                            "hash" => $documento["hash"],
                        );
                        $data = $conv->crearEdocument($input->email, $sello, $archivo, "OAQ030623UL8");
                        $xml->xmlEdocument($data);
                        $view->contenido = $xml->getXml();
                        if(APPLICATION_ENV == "production") {
                            file_put_contents("/tmp/" . $documento["hash"] . ".xml", $xml->getXml());
                        } else if (APPLICATION_ENV == "staging") {
                            file_put_contents("/tmp/" . $documento["hash"] . ".xml", $xml->getXml());
                        } else {
                            file_put_contents("C:/tmp/" . $documento["hash"] . ".xml", $xml->getXml());
                        }
                    } else if($input->type == "cove") {
                        $fact = new Vucem_Model_VucemFacturasMapper();
                        $prod = new Vucem_Model_VucemProductosMapper();
                        $factura = $fact->obtenerFacturaPorIdSolicitud($input->id);
                        $factura["Figura"] = $sello["figura"];
                        $factura["FactorMonExt"] = "";
                        $productos = $prod->obtenerProductos($factura["id"]);
                        $factura["Productos"] = $productos;
                        if(isset($productos[0]["VALCEQ"])) {
                            $factura["FactorMonExt"] = $productos[0]["VALCEQ"];
                        }
                        $xml = new OAQ_Xml(true);
                        $rfcConsulta[] = "OAQ030623UL8";
                        if ($factura["Patente"] == 3920) {
                            $rfcConsulta[] = "NOGI660213BI0";
                        }
                        if ($factura["Patente"] == 3574) {
                            $rfcConsulta[] = "PEPJ561122765";
                        }
                        $data = $conv->crear($input->email, $sello, $factura, $rfcConsulta);
                        $xml->xmlCove($data);
                        $view->contenido = $xml->getXml();
                    }
                } else {
                    $conv = new OAQ_Conversion();
                    $sello = $mapper->obtenerDetalleFirmanteId($input->fiel);
                    if ($input->type == "consultaedoc") {
                        $xml = new OAQ_Xml(false, true);
                        $data = $conv->consultaEdocument($input->email, $sello, $input->id);
                        $xml->consultaEstatusOperacionEdocument($data);
                        $view->contenido = $xml->getXml();
                    } else if ($input->type == "descargaedoc") {
                        $xml = new OAQ_Xml(false, false, true);
                        $xml->documentoDigitalizado($sello["rfc"], $sello["ws_pswd"], $input->id);
                        $view->contenido = $xml->getXml();
                    } else if ($input->type == "consultaped") {
                        $xml = new OAQ_XmlPedimentos();
                        $data["usuario"] = array(
                            "username" => $sello["rfc"],
                            "password" => $sello["ws_pswd"],
                            "certificado" => null,
                            "key" => null,
                            "new" => null,
                        );
                        $xml->set_patente($input->patente);
                        $xml->set_aduana($input->aduana);
                        $xml->set_pedimento($input->pedimento);
                        $xml->set_array($data);
                        $xml->consultaPedimentoCompleto();
                        $view->contenido = $xml->getXml();
                    } else {
                        $xml = new OAQ_Xml(true);
                        $data = $conv->consultaSolicitud($sello, $input->id);
                        $xml->consultaEstatusOperacionCove($data);
                        $view->contenido = $xml->getXml();
                    }
                }
                $view->setScriptPath(realpath(dirname(__FILE__)) . '/../views/scripts/ajax/');
                echo $view->render("view.phtml");
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function actualizarWsAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "ws" => array("NotEmpty")
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("id") && $input->isValid("ws")) {
                    $mp = new Vucem_Model_VucemFirmanteMapper();
                    if(true == ($mp->actualizarWs($input->id, $input->ws))) {
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
    
    public function actualizarMisDatosAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("idUsuario")) {
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
    
    public function descargarSelloAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => "Digits",
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $mapper = new Vucem_Model_VucemFirmanteMapper();
                $arr = $mapper->obtenerDetalleFirmanteId($input->id);
                if (count($arr)) {
                    if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN") {
                        $keyName = "D:/Tmp/OAQ/sellos" . DIRECTORY_SEPARATOR . $arr["key_nom"];
                        $cerName = "D:/Tmp/OAQ/sellos" . DIRECTORY_SEPARATOR . $arr["cer_nom"];
                        $txtFilename = "D:/Tmp/OAQ/sellos" . DIRECTORY_SEPARATOR . $arr["rfc"] . ".txt";
                        $zipName = "D:/Tmp/OAQ/sellos" . DIRECTORY_SEPARATOR . $arr["rfc"] . ".zip";
                    } else {
                        $keyName = "/tmp" . DIRECTORY_SEPARATOR . $arr["key_nom"];
                        $cerName = "/tmp" . DIRECTORY_SEPARATOR . $arr["cer_nom"];
                        $txtFilename = "/tmp" . DIRECTORY_SEPARATOR . $arr["rfc"] . ".txt";
                        $zipName = "/tmp" . DIRECTORY_SEPARATOR . $arr["rfc"] . ".zip";
                    }
                    if (file_exists($txtFilename)) {
                        unlink($txtFilename);
                    }
                    $file = file_put_contents($txtFilename, "User: {$arr["rfc"]}\nPass: {$arr["spem_pswd"]}\nWS: {$arr["ws_pswd"]}", FILE_APPEND | LOCK_EX);
                    file_put_contents($keyName, base64_decode($arr["key"]));
                    file_put_contents($cerName, base64_decode($arr["cer"]));
                    $zip = new ZipArchive();
                    if ($zip->open($zipName, ZipArchive::CREATE) !== TRUE) {
                        exit("cannot open <$zipName>\n");
                    }
                    $zip->addFile($keyName, basename($keyName));
                    $zip->addFile($cerName, basename($cerName));
                    $zip->addFile($txtFilename, basename($txtFilename));
                    $zip->close();
                    header("Content-Type: application/zip");
                    header("Content-Disposition: attachment; filename=" . basename($zipName));
                    header("Content-Length: " . filesize($zipName));
                    readfile($zipName);
                }
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

}
