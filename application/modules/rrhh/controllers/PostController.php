<?php

class Rrhh_PostController extends Zend_Controller_Action {

    protected $_session;
    protected $_svucem;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;
    protected $_logger;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $this->_logger = Zend_Registry::get("logDb");
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
    }

    public function guardarDatosEmpleadoAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idEmpleado" => array("Digits"),
                );
                $v = array(
                    "idEmpleado" => array("NotEmpty", new Zend_Validate_Int()),
                    "json" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idEmpleado") && $input->isValid("json")) {
                    $mapper = new Rrhh_Model_Empleados();
                    $addrs = new Rrhh_Model_EmpleadoDireccion();
                    $json = json_decode(html_entity_decode($input->json), true);
                    if (isset($json["generales"])) {
                        $arr = $json["generales"];
                        $arr["modificado"] = date("Y-m-d H:i:s");
                        $mapper->actualizar($input->idEmpleado, $arr);
                    }
                    if (isset($json["otros"])) {
                        $other = new Rrhh_Model_EmpleadoOtros();
                        if ($other->buscar($input->idEmpleado) == true) {
                            $other->actualizar($input->idEmpleado, array(
                                "json" => json_encode($json["otros"]),
                                "actualizado" => date("Y-m-d H:i:s")
                            ));
                        } else {
                            $other->agregar(array(
                                "idEmpleado" => $input->idEmpleado,
                                "json" => json_encode($json["otros"]),
                                "creado" => date("Y-m-d H:i:s")
                            ));
                        }
                    }
                    if (isset($json["direccion"])) {
                        $arr = $json["direccion"];
                        if (!$addrs->buscar($input->idEmpleado)) {
                            $arr["idEmpleado"] = $input->idEmpleado;
                            $arr["creado"] = date("Y-m-d H:i:s");
                            $addrs->agregar($arr);
                        } else {
                            $arr["modificado"] = date("Y-m-d H:i:s");
                            $addrs->actualizar($input->idEmpleado, $arr);
                        }
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

    public function altaEmpleadoAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                );
                $v = array(
                    "nombre" => "NotEmpty",
                    "apellido" => "NotEmpty",
                    "idEmpresa" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("nombre") && $input->isValid("apellido") && $input->isValid("idEmpresa")) {
                    $mapper = new Rrhh_Model_Empleados();
                    $arr = array(
                        "nombre" => $input->nombre,
                        "apellido" => $input->apellido,
                        "idEmpresa" => $input->idEmpresa,
                        "estatus" => 1,
                        "creado" => date("Y-m-d H:i:s"),
                    );
                    if (($id = $mapper->agregar($arr))) {
                        $this->_helper->json(array("success" => true, "id" => $id));
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

    public function cambiarPerfilAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost();
                if ($post["photo"]) {
                    $mapper = new Rrhh_Model_EmpleadoFotos();
                    $ex = explode(",", $post["photo"]);
                    if (isset($ex[1])) {
                        $image = base64_decode($ex[1]);
                    }
                    if (isset($ex[0])) {
                        $matches = [];
                        preg_match("/^([^:]+):([^;]+);/", $ex[0], $matches);
                        if (isset($matches[2])) {
                            $mimeType = $matches[2];
                        }
                    }
                    $arr = array(
                        "idEmpleado" => $post["idEmpleado"],
                        "image" => $image,
                        "mimeType" => $mimeType,
                        "creado" => date("Y-m-d H:i:s"),
                    );
                    $mapper->agregar($arr);
                }
            } else {
                $this->_helper->json(array("success" => false, "message" => "No data recieved."));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function subirArchivoEmpleadoAction() {
        try {
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                );
                $v = array(
                    "idEmpleado" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("idEmpleado")) {
                    $misc = new OAQ_Misc();
                    $m = new Rrhh_Model_DocumentosEmpleados();
                    $upload = new Zend_File_Transfer_Adapter_Http();
                    $upload->addValidator("Count", false, array("min" => 1, "max" => 50))
                            ->addValidator("Size", false, array("min" => "1", "max" => "30MB"))
                            ->addValidator("Extension", false, array("extension" => "pdf,xml,xls,xlsx,doc,docx,zip,bmp,tif,jpg,jpeg", "case" => false));
                    $model = new Rrhh_Model_Empleados();
                    $arr = $model->obtener($input->idEmpleado);
                    $path = $this->_appconfig->getParam("expempl") . DIRECTORY_SEPARATOR . sha1($arr["id"]);
                    if (!file_exists($path)) {
                        mkdir($path, 0777, true);
                    }
                    $mapper = new Rrhh_Model_RepositorioEmpleados();
                    $upload->setDestination($path);
                    $files = $upload->getFileInfo();
                    foreach ($files as $fieldname => $fileinfo) {
                        if (($upload->isUploaded($fieldname)) && ($upload->isValid($fieldname))) {
                            $filename = $misc->formatFilename($fileinfo["name"], false);
                            $upload->receive($fieldname);
                            if (($misc->renombrarArchivo($path, $fileinfo["name"], $filename))) {
                                if (!($mapper->buscarArchivo($input->idEmpleado, $filename))) {
                                    $mapper->nuevoArchivo($input->idEmpleado, $m->rgex($filename), $filename, $path, "", $this->_session->username);
                                }
                            }
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

    public function borrarArchivoAction() {
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
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("id")) {
                    $repositorio = new Rrhh_Model_RepositorioEmpleados();
                    if ($repositorio->borrarArchivo($i->id)) {
                        $this->_helper->json(array("success" => true));
                    }
                    $this->_helper->json(array("success" => false));
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function cambiarArchivoAction() {
        try {
            $filters = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
                "tipo" => array("Digits"),
            );
            $validators = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
                "tipoArchivo" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($filters, $validators, $this->_request->getParams());
            if ($input->isValid("id") && $input->isValid("tipoArchivo")) {
                $repositorio = new Rrhh_Model_RepositorioEmpleados();
                if ($repositorio->actualizarTipoArchivo($input->id, $input->tipoArchivo)) {
                    $tipo = $repositorio->tipoArchivo($input->id);
                    $this->_helper->json(array("success" => true, "nombre" => $tipo));
                }
                $this->_helper->json(array("success" => false));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function editarArchivoAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $html = new V2_Html();
                $repositorio = new Rrhh_Model_RepositorioEmpleados();
                $documentos = new Rrhh_Model_DocumentosEmpleados();
                $arr = $documentos->obtenerTodos();
                $tipo = $repositorio->tipo($input->id);
                $html->select("traffic-select-large", "select_" . $input->id);
                $html->addSelectOption("", "---");
                foreach ($arr as $item) {
                    if ($tipo == $item["id"]) {
                        $html->addSelectOption($item["id"], $item["nombre"], true);
                    } else {
                        $html->addSelectOption($item["id"], $item["nombre"]);
                    }
                }
                $this->_helper->json(array("success" => true, "html" => $html->getHtml()));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function cancelarEdicionAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $repositorio = new Rrhh_Model_RepositorioEmpleados();
                $tipo = $repositorio->tipoArchivo($input->id);
                $this->_helper->json(array("success" => true, "nombre" => $tipo));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function editarActividadAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array("*" => array("StringTrim", "StripTags"), "idPuesto" => "Digits", "idActividad" => "Digits");
                $v = array("idPuesto" => array("NotEmpty", new Zend_Validate_Int()), "idActividad" => array("NotEmpty", new Zend_Validate_Int()), "nombreActividad" => "NotEmpty");
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idPuesto") && $input->isValid("idActividad") && $input->isValid("nombreActividad")) {
                    $mppr = new Rrhh_Model_EmpresaDeptoActividades();
                    $arr = array(
                        "descripcion" => html_entity_decode($input->nombreActividad),
                        "actualizado" => date("Y-m-d H:i:s"),
                        "actualizadoPor" => $this->_session->username,
                    );
                    $mppr->update($input->idActividad, $arr);
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
    
    public function agregarActividadAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array("*" => array("StringTrim", "StripTags"), "idPuesto" => "Digits", "idDepto" => "Digits");
                $v = array("idDepto" => array("NotEmpty", new Zend_Validate_Int()),"idPuesto" => array("NotEmpty", new Zend_Validate_Int()), "nombreActividad" => "NotEmpty");
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idDepto") && $input->isValid("idPuesto") && $input->isValid("nombreActividad")) {
                    $mppr = new Rrhh_Model_EmpresaDeptoActividades();
                    if (!$mppr->verificar($input->idDepto, $input->idPuesto, html_entity_decode($input->nombreActividad))) {
                        $arr = array(
                            "idDepto" => $input->idDepto,
                            "idPuesto" => $input->idPuesto,
                            "descripcion" => html_entity_decode($input->nombreActividad),
                            "creado" => date("Y-m-d H:i:s"),
                            "creadoPor" => $this->_session->username,
                        );
                        if ($mppr->agregar($arr)) {
                            $this->_helper->json(array("success" => true));
                        } else {
                            $this->_helper->json(array("success" => false, "message" => "No se pudo agregar."));
                        }
                    } else {
                        $this->_helper->json(array("success" => false, "message" => "Departamento ya existe."));
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
    
    public function editarPuestoAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array("*" => array("StringTrim", "StripTags"), "idDepto" => "Digits", "idPuesto" => "Digits");
                $v = array("idDepto" => array("NotEmpty", new Zend_Validate_Int()), "idPuesto" => array("NotEmpty", new Zend_Validate_Int()), "nombrePuesto" => "NotEmpty");
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idDepto") && $input->isValid("idPuesto") && $input->isValid("nombrePuesto")) {
                    $mppr = new Rrhh_Model_EmpresaDeptoPuestos();
                    $arr = array(
                        "descripcion" => html_entity_decode($input->nombrePuesto),
                        "actualizado" => date("Y-m-d H:i:s"),
                        "actualizadoPor" => $this->_session->username,
                    );
                    $mppr->update($input->idPuesto, $arr);
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
    
    public function agregarPuestoAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array("*" => array("StringTrim", "StripTags"), "idDepto" => "Digits");
                $v = array("idDepto" => array("NotEmpty", new Zend_Validate_Int()), "nombrePuesto" => "NotEmpty");
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idDepto") && $input->isValid("nombrePuesto")) {
                    $mppr = new Rrhh_Model_EmpresaDeptoPuestos();
                    if (!$mppr->verificar($input->idDepto, html_entity_decode($input->nombrePuesto))) {
                        $arr = array(
                            "idDepto" => $input->idDepto,
                            "descripcion" => html_entity_decode($input->nombrePuesto),
                            "creado" => date("Y-m-d H:i:s"),
                            "creadoPor" => $this->_session->username,
                        );
                        if ($mppr->agregar($arr)) {
                            $this->_helper->json(array("success" => true));
                        } else {
                            $this->_helper->json(array("success" => false, "message" => "No se pudo agregar."));
                        }
                    } else {
                        $this->_helper->json(array("success" => false, "message" => "Departamento ya existe."));
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
    
    public function editarDepartamentoAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array("*" => array("StringTrim", "StripTags"), "idEmpresa" => "Digits", "idDepto" => "Digits");
                $v = array("idEmpresa" => array("NotEmpty", new Zend_Validate_Int()), "idDepto" => array("NotEmpty", new Zend_Validate_Int()), "nombreDepto" => "NotEmpty");
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idEmpresa") && $input->isValid("idDepto") && $input->isValid("nombreDepto")) {
                    $mppr = new Rrhh_Model_EmpresaDepartamentos();
                    $arr = array(
                        "descripcion" => html_entity_decode($input->nombreDepto),
                        "actualizado" => date("Y-m-d H:i:s"),
                        "actualizadoPor" => $this->_session->username,
                    );
                    $mppr->update($input->idDepto, $arr);
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
    
    public function agregarRetardoAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idEmpleado" => "Digits",
                    "retardo" => "Digits",
                    "falta" => "Digits",
                );
                $v = array(
                    "idEmpleado" => array("NotEmpty", new Zend_Validate_Int()),
                    "retardo" => array("NotEmpty", new Zend_Validate_Int()),
                    "falta" => array("NotEmpty", new Zend_Validate_Int()),
                    "fecha" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idEmpleado") && $input->isValid("fecha")) {
                    $mppr = new Rrhh_Model_EmpleadosRetardos();
                    if ($input->isValid("retardo") && $input->retardo == 1) {
                        if (!$mppr->verificarRetardo($input->idEmpleado, $input->fecha)) {
                            $arr = array(
                                "idEmpleado" => $input->idEmpleado,
                                "retardo" => 1,
                                "falta" => 0,
                                "fecha" => $input->fecha,
                                "creado" => date("Y-m-d H:i:s"),
                                "creadoPor" => $this->_session->username,
                            );
                            $mppr->agregar($arr);
                        }
                    }
                    if ($input->isValid("falta") && $input->falta == 1) {
                        if (!$mppr->verificarFalta($input->idEmpleado, $input->fecha)) {
                            $arr = array(
                                "idEmpleado" => $input->idEmpleado,
                                "retardo" => 0,
                                "falta" => 1,
                                "fecha" => $input->fecha,
                                "creado" => date("Y-m-d H:i:s"),
                                "creadoPor" => $this->_session->username,
                            );
                            $mppr->agregar($arr);
                        }
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

    public function agregarDepartamentoAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array("*" => array("StringTrim", "StripTags"), "idEmpresa" => "Digits");
                $v = array("idEmpresa" => array("NotEmpty", new Zend_Validate_Int()), "nombreDepto" => "NotEmpty");
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idEmpresa") && $input->isValid("nombreDepto")) {
                    $mppr = new Rrhh_Model_EmpresaDepartamentos();
                    if (!$mppr->verificar($input->idEmpresa, html_entity_decode($input->nombreDepto))) {
                        $arr = array(
                            "idEmpresa" => $input->idEmpresa,
                            "descripcion" => html_entity_decode($input->nombreDepto),
                            "creado" => date("Y-m-d H:i:s"),
                            "creadoPor" => $this->_session->username,
                        );
                        if ($mppr->agregar($arr)) {
                            $this->_helper->json(array("success" => true));
                        } else {
                            $this->_helper->json(array("success" => false, "message" => "No se pudo agregar."));
                        }
                    } else {
                        $this->_helper->json(array("success" => false, "message" => "Departamento ya existe."));
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

    public function estatusEmpleadoAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idEmpleado" => "Digits",
                    "checked" => "StringToLower",
                );
                $v = array(
                    "idEmpleado" => array("NotEmpty", new Zend_Validate_Int()),
                    "checked" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idEmpleado") && $input->isValid("checked")) {
                    $mppr = new Rrhh_Model_Empleados();
                    if ($input->checked == "true") {
                        $update = $mppr->actualizar($input->idEmpleado, array("estatus" => 1, "modificado" => date("Y-m-d H:i:s")));
                        $this->_helper->json(array("success" => true));
                    }
                    if ($input->checked == "false") {
                        $update = $mppr->actualizar($input->idEmpleado, array("estatus" => 0, "modificado" => date("Y-m-d H:i:s")));
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

    public function guardarPuestoAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idEmpresa" => "Digits",
                    "idDepto" => "Digits",
                    "idPuesto" => "Digits",
                    "descripcionPuesto" => "StringToUpper",
                    "supervisor" => "Digits"
                );
                $v = array(
                    "idEmpresa" => array("NotEmpty", new Zend_Validate_Int()),
                    "idDepto" => array("NotEmpty", new Zend_Validate_Int()),
                    "idPuesto" => array("NotEmpty", new Zend_Validate_Int()),
                    "descripcionPuesto" => array("NotEmpty"),
                    "supervisor" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getParams());
                if ($input->isValid("idEmpresa") && $input->isValid("idDepto") && $input->isValid("idPuesto")) {
                    $mppr = new Rrhh_Model_EmpresaDeptoPuestos();
                    $arr = array(
                        "supervisor" => $input->supervisor,
                        "descripcionPuesto" => $input->descripcionPuesto
                    );
                    if ($mppr->update($input->idPuesto, $arr)) {
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

    public function establecerPropiedadAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idEmpleado" => "Digits",
                    "propiedad" => "StringToLower",
                    "estatus" => "Digits"
                );
                $v = array(
                    "idEmpleado" => array("NotEmpty", new Zend_Validate_Int()),
                    "propiedad" => array("NotEmpty"),
                    "estatus" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getParams());
                if ($input->isValid("idEmpleado") && $input->isValid("propiedad") && $input->isValid("estatus")) {
                    $mppr = new Rrhh_Model_Empleados();
                    if ($input->propiedad == 'doctos') {
                        $mppr->actualizar($input->idEmpleado, array("documentacion" => ($input->estatus == 1) ? 1 : null, "modificado" => date("Y-m-d H:i:s")));
                    }
                    if ($input->propiedad == 'capacit') {
                        $mppr->actualizar($input->idEmpleado, array("capacitacion" => ($input->estatus == 1) ? 1 : null, "modificado" => date("Y-m-d H:i:s")));
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

}
