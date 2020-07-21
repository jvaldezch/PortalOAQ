<?php

class Operaciones_GetController extends Zend_Controller_Action {

    protected $_session;
    protected $_config;
    protected $_appconfig;
    protected $_todosClientes;

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
        $this->_todosClientes = array("trafico", "super", "trafico_ejecutivo", "gerente");
    }
    
    public function subirArchivoAction() {
        $this->_helper->viewRenderer->setNoRender(false);
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "patente" => array("Digits"),
                "aduana" => array("Digits"),
            );
            $v = array(
                "patente" => array("NotEmpty", new Zend_Validate_Int()),
                "aduana" => array("NotEmpty", new Zend_Validate_Int())
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("patente") && $input->isValid("aduana")) {
                $mppr = new Application_Model_DirectoriosValidacion();
                $arr = $mppr->get($input->patente, $input->aduana);
                $this->view->idDirectorio = $arr["id"];
                $this->view->patente = $input->patente;
                $this->view->aduana = $input->aduana;
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function subirPlantillaAction() {
        try {
            $view = new Zend_View();
            $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
            $this->_helper->json(array("success" => true, "html" => $view->render("subir-plantilla.phtml")));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function obtenerCartasAction() {
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
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("page") && $input->isValid("rows")) {
                
                $referencias = new OAQ_Referencias();
                $res = $referencias->restricciones($this->_session->id, $this->_session->role);
                
                $mppr = new Operaciones_Model_CartaInstrucciones();
                
                if (!empty($res["idsClientes"])) {
                    $select = $mppr->cartasSelect($res["idsClientes"]);                    
                } else {
                    $arr = array(
                        "total" => 0,
                        "rows" => 0,
                        "paginator" => 0,
                    );
                    $this->_helper->json($arr);
                }                
                
                $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
                $paginator->setCurrentPageNumber($input->page);
                $paginator->setItemCountPerPage($input->rows);
                
                $arr = array(
                    "total" => $paginator->getTotalItemCount(),
                    "rows" => iterator_to_array($paginator),
                    "paginator" => $paginator->getPages(),
                );
                
                $this->_helper->json($arr);
            }            
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function obtenerDeliveriesAction() {
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
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("page") && $input->isValid("rows")) {
                
                $referencias = new OAQ_Referencias();
                $res = $referencias->restricciones($this->_session->id, $this->_session->role);
                
                $mppr = new Operaciones_Model_CartaInstruccionesPartes();
                
                if (!empty($res["idsClientes"])) {
                    //$select = $mppr->cartasSelect($res["idsClientes"]);                    
                    $select = $mppr->deliveriesSelect($res["idsClientes"]);
                } else {
                    $arr = array(
                        "total" => 0,
                        "rows" => 0,
                        "paginator" => 0,
                    );
                    $this->_helper->json($arr);
                }

                $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
                $paginator->setCurrentPageNumber($input->page);
                $paginator->setItemCountPerPage($input->rows);
                
                $arr = array(
                    "total" => $paginator->getTotalItemCount(),
                    "rows" => iterator_to_array($paginator),
                    "paginator" => $paginator->getPages(),
                );
                
                $this->_helper->json($arr);
            }            
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function obtenerCartaAction() {
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
                $mppr = new Operaciones_Model_CartaPartes();
                
                $select = $mppr->cartaPartesSelect($input->id);

                $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
                $paginator->setCurrentPageNumber($input->page);
                $paginator->setItemCountPerPage($input->rows);
                
                $arr = array(
                    "total" => $paginator->getTotalItemCount(),
                    "rows" => iterator_to_array($paginator),
                    "paginator" => $paginator->getPages(),
                );
                
                $this->_helper->json($arr);
            }            
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function nuevaCartaAction() {
        try {
            $view = new Zend_View();
            $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");            
            $referencias = new OAQ_Referencias();
            $res = $referencias->restricciones($this->_session->id, $this->_session->role);            
            if (!empty($res["idsClientes"])) {
                $rows = $referencias->obtenerClientes(null, $res["idsClientes"]);
                if (!empty($rows)) {
                    $view->clientes = $rows;
                }
            }
            $this->_helper->json(array("success" => true, "html" => $view->render("nueva-carta.phtml")));            
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function subirFacturasAction() {
        try {
            $view = new Zend_View();
            $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
            $referencias = new OAQ_Referencias();
            $res = $referencias->restricciones($this->_session->id, $this->_session->role);            
            if (!empty($res["idsClientes"])) {
                $rows = $referencias->obtenerClientes(null, $res["idsClientes"]);
                if (!empty($rows)) {
                    $view->clientes = $rows;
                }
            }
            $this->_helper->json(array("success" => true, "html" => $view->render("subir-facturas.phtml")));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function subirCatalogoAction() {
        try {
            $view = new Zend_View();
            $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
            $referencias = new OAQ_Referencias();
            $res = $referencias->restricciones($this->_session->id, $this->_session->role);            
            if (!empty($res["idsClientes"])) {
                $rows = $referencias->obtenerClientes(null, $res["idsClientes"]);
                if (!empty($rows)) {
                    $view->clientes = $rows;
                }
            }
            $this->_helper->json(array("success" => true, "html" => $view->render("subir-catalogo.phtml")));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function nuevoRetornableAction() {
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
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->id = $input->id;
                $this->_helper->json(array("success" => true, "html" => $view->render("nuevo-retornable.phtml")));                
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    /*public function leerLayoutAction() {
        try {
            $plantilla = new OAQ_Archivos_PlantillaBos("D:\\xampp\\tmp\\layout\\41a199106e4793b1ed30e64e991c594405a4ad1c.xlsx");
            $plantilla->analizar();
            
            
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }*/
    
    public function imprimirCartaAction() {
        try {
            $view = new Zend_View();
            $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
            $referencias = new OAQ_Referencias();
            $res = $referencias->restricciones($this->_session->id, $this->_session->role);            
            if (!empty($res["idsClientes"])) {
                $rows = $referencias->obtenerClientes(null, $res["idsClientes"]);
                if (!empty($rows)) {
                    $view->clientes = $rows;
                }
            }
            $this->_helper->json(array("success" => true, "html" => $view->render("imprimir-carta.phtml")));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function enviarCartaAction() {
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
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");

                // $mppr = new Operaciones_Model_CartaInstrucciones();

                $mppr = new Trafico_Model_TraficoUsuAduanasMapper();
                if (in_array($this->_session->role, $this->_todosClientes)) {
                    $customs = $mppr->aduanasDeUsuario();
                } else {
                    $customs = $mppr->aduanasDeUsuario($this->_session->id);
                }
                if (!empty($customs)) {
                    $form = new Trafico_Form_CrearTraficoNew(array("aduanas" => $customs));
                } else {
                    $form = new Trafico_Form_CrearTraficoNew();
                }
                $view->form = $form;

                $mppr = new Operaciones_Model_CartaPartes();

                $rows = $mppr->invoices($input->id);

                $view->rows = $rows;

                /*$referencias = new OAQ_Referencias();
                $res = $referencias->restricciones($this->_session->id, $this->_session->role);            
                if (!empty($res["idsClientes"])) {
                    $rows = $referencias->obtenerClientes(null, $res["idsClientes"]);
                    if (!empty($rows)) {
                        $view->clientes = $rows;
                    }
                }*/
                $this->_helper->json(array("success" => true, "html" => $view->render("enviar-carta.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
}
