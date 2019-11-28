<?php

class Rrhh_GetController extends Zend_Controller_Action {

    protected $_session;
    protected $_config;
    protected $_appconfig;

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
    }
    
    public function puestosAction() {
        try {
            $f = array("*" => array("StringTrim", "StripTags"), "idDepto" => "Digits", "idPuesto" => "Digits");
            $v = array(
                "idDepto" => array("NotEmpty", new Zend_Validate_Int()),
                "idPuesto" => array("NotEmpty", new Zend_Validate_Int()),
                "multiple" => "NotEmpty",
            );
            $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($i->isValid("idDepto")) {
                $mppr = new Rrhh_Model_EmpresaDeptoPuestos();
                $html = new V2_Html();
                if (!$i->isValid("multiple")) {
                    $html->select(null, "idPuesto");
                    $html->setMultiple();
                    $html->setStyle("height: 180px; width: 250px");
                } else {
                    $html->select("traffic-select-large", "idPuesto");                    
                    $html->addSelectOption("", "---");
                }
                $arr = $mppr->obtener($i->idDepto);
                if (!empty($arr)) {
                    foreach ($arr as $item) {
                        if ($i->isValid("idPuesto") && $item["id"] == $i->idPuesto) {
                            $html->addSelectOption($item["id"], mb_strtoupper($item["descripcion"]), true);
                        } else {
                            $html->addSelectOption($item["id"], mb_strtoupper($item["descripcion"]));
                        }
                    }
                    $this->_helper->json(array("success" => true, "html" => $html->getHtml()));
                } else {
                    $html->setSelectDisabled();
                    $this->_helper->json(array("success" => true, "html" => $html->getHtml()));
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function actividadesAction() {
        try {
            $f = array("*" => array("StringTrim", "StripTags"), "idPuesto" => "Digits");
            $v = array(
                "idPuesto" => array("NotEmpty", new Zend_Validate_Int()),
                "multiple" => "NotEmpty",
            );
            $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($i->isValid("idPuesto")) {
                $mppr = new Rrhh_Model_EmpresaDeptoActividades();
                $html = new V2_Html();
                if (!$i->isValid("multiple")) {
                    $html->select(null, "idActividad");
                    $html->setMultiple();
                    $html->setStyle("height: 180px; width: 250px");
                } else {
                    $html->select("traffic-select-large", "idActividad");                    
                    $html->addSelectOption("", "---");
                }
                $arr = $mppr->obtener($i->idPuesto);
                if (!empty($arr)) {
                    foreach ($arr as $item) {
                        if ($i->isValid("idPuesto") && $item["id"] == $i->idDepto) {
                            $html->addSelectOption($item["id"], mb_strtoupper($item["descripcion"]), true);
                        } else {
                            $html->addSelectOption($item["id"], mb_strtoupper($item["descripcion"]));
                        }
                    }
                    $this->_helper->json(array("success" => true, "html" => $html->getHtml()));
                } else {
                    $html->setSelectDisabled();
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
    
    public function departamentosAction() {
        try {
            $f = array("*" => array("StringTrim", "StripTags"), "idEmpresa" => "Digits", "idDepto" => "Digits");
            $v = array(
                "idEmpresa" => array("NotEmpty", new Zend_Validate_Int()),
                "idDepto" => array("NotEmpty", new Zend_Validate_Int()),
                "multiple" => "NotEmpty",
            );
            $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($i->isValid("idEmpresa")) {
                $mppr = new Rrhh_Model_EmpresaDepartamentos();
                $html = new V2_Html();
                if (!$i->isValid("multiple")) {
                    $html->select(null, "idDepto");
                    $html->setMultiple();
                    $html->setStyle("height: 180px; width: 250px");
                } else {
                    $html->select("traffic-select-large", "idDepto");                    
                    $html->addSelectOption("", "---");
                }
                $arr = $mppr->obtener($i->idEmpresa);
                if (!empty($arr)) {
                    foreach ($arr as $item) {
                        if ($i->isValid("idDepto") && $item["id"] == $i->idDepto) {
                            $html->addSelectOption($item["id"], mb_strtoupper($item["descripcion"]), true);
                        } else {
                            $html->addSelectOption($item["id"], mb_strtoupper($item["descripcion"]));
                        }
                    }
                    $this->_helper->json(array("success" => true, "html" => $html->getHtml()));
                } else {
                    $html->setSelectDisabled();
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

    public function datosEmpleadoAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idEmpleado" => "Digits",
            );
            $v = array(
                "idEmpleado" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($i->isValid("idEmpleado")) {
                $mapper = new Rrhh_Model_Empleados();
                $addrs = new Rrhh_Model_EmpleadoDireccion();
                $other = new Rrhh_Model_EmpleadoOtros();
                $arr = $mapper->obtener($i->idEmpleado);
                if(isset($arr) && !empty($arr)) {
                    $direccion = $addrs->direccion($i->idEmpleado);
                    $otros = $other->otros($i->idEmpleado);
                    $array = array(
                        "generales" => $arr,
                        "direccion" => $direccion,
                        "otros" => $otros,
                        "depto" => array("idDepto" => $arr["idDepto"], "idPuesto" => $arr["idPuesto"]),
                    );
                    $this->_helper->json(array("success" => true, "json" => $array));
                }
                $this->_helper->json(array("success" => false));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
    public function cambiarPerfilAction() {
        $this->_helper->viewRenderer->setNoRender(false);
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idEmpleado" => "Digits",
            );
            $v = array(
                "idEmpleado" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($i->isValid("idEmpleado")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/index/");
                $view->idEmpleado = $i->idEmpleado;
                $this->_helper->json(array("success" => true, "html" => $view->render("cambiar-perfil.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
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
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                    $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");
                    $repo = new Rrhh_Model_RepositorioEmpleados();
                    $archivos = $repo->archivosEmpleado($input->id);
                    $view->archivos = $archivos;
                    if (in_array($this->_session->role, array("super", "rrhh"))) {
                        $view->canDelete = true;
                    }
                    $this->_helper->json(array("success" => true, "html" => $view->render("cargar-archivos.phtml")));
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function verArchivoAction() {
        try {
            error_reporting(E_ALL);
            ini_set("display_errors", 1);
            $f = array(
                "id" => array("Digits"),
                "view" => "StringToLower",
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
                "view" => array("NotEmpty", new Zend_Validate_InArray(array(true, false)))
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                if (boolval($input->view) === true) {
                    $mppr = new Rrhh_Model_RepositorioEmpleados();
                    $arr = $mppr->buscar($input->id);
                    $ofilename = $arr["ubicacion"] . DIRECTORY_SEPARATOR . $arr["nombreArchivo"];
                    $dfilename = $this->_appconfig->getParam("tmpDir") . DIRECTORY_SEPARATOR . $arr["nombreArchivo"];
                    if (is_readable($arr["ubicacion"]) && file_exists($ofilename)) {
                        if (copy($ofilename, $dfilename)) {
                            if (file_exists($dfilename)) {
                                header("Content-Type: application/octet-stream");
                                header("Content-Transfer-Encoding: Binary");
                                header("Content-Length: " . filesize($dfilename));
                                header("Content-disposition: attachment; filename=\"" . $arr["nombreArchivo"] . "\"");
                                readfile($dfilename);
                                unlink($dfilename);
                            }
                        }
                    }
                } else {
                    $view = new Zend_View();
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                    $view->id = $input->id;
                    echo $view->render("ver-archivo.phtml");
                }
            } else {
                throw new Exception("Invalid Input!");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function descargarArchivoAction() {
        try {
            error_reporting(E_ALL);
            ini_set("display_errors", 1);
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $mppr = new Rrhh_Model_RepositorioEmpleados();
                $arr = $mppr->buscar($input->id);
                $ofilename = $arr["ubicacion"] . DIRECTORY_SEPARATOR . $arr["nombreArchivo"];
                $dfilename = $this->_appconfig->getParam("tmpDir") . DIRECTORY_SEPARATOR . $arr["nombreArchivo"];
                if (is_readable($arr["ubicacion"]) && file_exists($ofilename)) {
                    if (copy($ofilename, $dfilename)) {
                        if (file_exists($dfilename)) {
                            header("Content-Type: application/octet-stream");
                            header("Content-Transfer-Encoding: Binary");
                            header("Content-Length: " . filesize($dfilename));
                            header("Content-disposition: attachment; filename=\"" . $arr["nombreArchivo"] . "\"");
                            readfile($dfilename);
                            unlink($dfilename);
                        }
                    }
                } else {
                    throw new Exception("Archivo no existe");
                }
            } else {
                throw new Exception("Invalid Input!");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
    public function usuariosAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $view = new Zend_View();
            $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
            $mppr = new Usuarios_Model_UsuariosMapper();
            $arr = $mppr->getUsers();
            $view->results = $arr;
            $this->_helper->json(array("success" => true, "html" => $view->render("usuarios.phtml")));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function retardosAction() {
        try {
            $f = array("*" => array("StringTrim", "StripTags"), "idEmpleado" => "Digits");
            $v = array("idEmpleado" => array("NotEmpty", new Zend_Validate_Int()), "fecha" => "NotEmpty");
            $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($i->isValid("idEmpleado")) {
                $mppr = new Rrhh_Model_EmpleadosRetardos();
                $year = date("Y", strtotime($i->fecha));
                $month = (int) date("m", strtotime($i->fecha));
                $arr = $mppr->retardos($i->idEmpleado, $year, $month);
                if (!empty($arr)) {
                    $data = array();
                    foreach ($arr as $item) {
                        if ($item["retardo"] == 1) {
                            $data[] = array(
                                "title" => "Retardo",
                                "start" => date("Y-m-d", strtotime($item["fecha"])),
                                "color" => "#c79100"
                            );
                        }
                        if ($item["falta"] == 1) {
                            $data[] = array(
                                "title" => "Falta",
                                "start" => date("Y-m-d", strtotime($item["fecha"])),
                                "color" => "#c70039"
                            );
                        }
                    }
                    $this->_helper->json($data);
                } else {
                    $this->_helper->json(array());
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function editarAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"), 
                "idEmpresa" => "Digits",
                "tipo" => "StringToLower",
                
            );
            $v = array(
                "idEmpresa" => array("NotEmpty", new Zend_Validate_Int()), 
                "tipo" => new Zend_Validate_InArray(array("depto", "puesto", "actividad")),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idEmpresa") && $input->isValid("tipo")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->idEmpresa = $input->idEmpresa;
                if ($input->tipo == "depto") {
                }
                $this->_helper->json(array("success" => true, "html" => $view->render("editar.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function edicionPuestoAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idEmpresa" => "Digits",
                "idDepto" => "Digits",
                "idPuesto" => "Digits"
            );
            $v = array(
                "idEmpresa" => array("NotEmpty", new Zend_Validate_Int()),
                "idDepto" => array("NotEmpty", new Zend_Validate_Int()),
                "idPuesto" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idEmpresa") && $input->isValid("idDepto") && $input->isValid("idPuesto")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->idEmpresa = $input->idEmpresa;
                $view->idDepto = $input->idDepto;
                $view->idPuesto = $input->idPuesto;

                $mppr = new Rrhh_Model_EmpresaDeptoPuestos();
                $row = $mppr->obtenerPuesto($input->idPuesto);
                if (!empty($row)) {
                    $view->descripcionPuesto = $row["descripcionPuesto"];
                }

                $html = new V2_Html();
                $html->select("traffic-select-medium", "supervisor");
                $html->addSelectOption("", "---");
                $arr = $mppr->obtener($input->idDepto);
                if (!empty($arr)) {
                    foreach ($arr as $item) {
                        if ($item["id"] !== $input->idPuesto) {
                            if (isset($row["supervisor"])) {
                                if ($item["id"] == $row["supervisor"]) {
                                    $html->addSelectOption($item["id"], mb_strtoupper($item["descripcion"]), true);
                                    continue;
                                }
                            }
                            $html->addSelectOption($item["id"], mb_strtoupper($item["descripcion"]));
                        }
                    }
                    $view->supervisor = $html->getHtml();
                }
                $this->_helper->json(array("success" => true, "html" => $view->render("edicion-puesto.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function reporteEmpleadosAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => "Digits",
                "fitler" => "Digits",
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
                "filter" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            
            $companies = new Application_Model_UsuariosEmpresas();
            $com = $companies->empresasDeUsuario($this->_session->id);
        
            $mapper = new Rrhh_Model_Empleados();
            $rows = $mapper->obtenerTodos($com, $input->id, $input->filter);
            
            $reportes = new OAQ_ExcelReportes();            
            $reportes->reportesTrafico(61, $rows);
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

}
