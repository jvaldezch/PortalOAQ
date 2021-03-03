<?php

class Operaciones_CatalogoController extends Zend_Controller_Action
{
    protected $_session;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;

    public function init()
    {
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->view->headLink(array("rel" => "icon shortcut", "href" => "/favicon.png"));
        $this->view->headLink()
            ->appendStylesheet("/js/common/bootstrap/css/bootstrap.min.css")
            ->appendStylesheet("/js/common/bootstrap/datepicker/css/datepicker.css")
            ->appendStylesheet("/css/fontawesome/css/fontawesome-all.min.css")
            ->appendStylesheet("/less/traffic-module.css?" . time());
        $this->view->headScript()
            ->appendFile("/js/common/jquery-1.9.1.min.js")
            ->appendFile("/js/common/bootstrap/js/bootstrap.min.js")
            ->appendFile("/js/common/jquery.form.min.js")
            ->appendFile("/js/common/jquery.validate.min.js")
            ->appendFile("/js/common/additional-methods.min.js")
            ->appendFile("/js/common/js.cookie.js")
            ->appendFile("/js/common/jquery.blockUI.js")
            ->appendFile("/js/common/principal.js?" . time());
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
    }

    public function preDispatch()
    {
        $this->_session = NULL ? $this->_session = new Zend_Session_Namespace("") : $this->_session = new Zend_Session_Namespace($this->_config->app->namespace);
        if ($this->_session->authenticated == true) {
            $session = new OAQ_Session($this->_session, $this->_appconfig);
            $session->actualizar();
            $session->actualizarSesion(Zend_Controller_Front::getInstance()->getRequest()->getRequestUri());
        } else {
            $this->getResponse()->setRedirect($this->_appconfig->getParam("link-logout"));
        }
        $mapper = new Application_Model_MenuAccesos();
        $this->view->menu = $mapper->obtenerPorRol($this->_session->idRol);
        $this->view->username = $this->_session->username;
        $this->view->rol = $this->_session->role;
    }

    public function indexAction()
    {
        $this->view->title = $this->_appconfig->getParam('title') . " Indice de clientes";
        $this->view->headMeta()->appendName('description', '');
        $this->view->headScript()
            ->appendFile("/js/operaciones/catalogo/index.js?" . time());
        $request = new Zend_Controller_Request_Http();
        $filter = $request->getCookie("filter");
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "page" => "Digits",
            "size" => "Digits",
            "busqueda" => array("StringToUpper"),
        );
        $v = array(
            "page" => array(new Zend_Validate_Int(), "default" => 1),
            "size" => array(new Zend_Validate_Int(), "default" => 20),
            "busqueda" => "NotEmpty",
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        $model = new Trafico_Model_ClientesMapper();
        $alerts = new Trafico_Model_ClientesAlertas();
        $this->view->alertas = $alerts->ultimaActividad();
        if ($input->isValid("busqueda")) {
            $data = $model->busqueda(html_entity_decode($input->busqueda));
            $this->view->busqueda = $input->busqueda;
        } else {
            $data = $model->obtener(false, $filter);
        }
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array($data));
        $paginator->setItemCountPerPage($input->size);
        $paginator->setCurrentPageNumber($input->page);
        $this->view->paginator = $paginator;
    }

    public function consultaAction()
    {
        $this->view->title = $this->_appconfig->getParam('title') . ' ' . " Consulta del catalogo";
        $this->view->headMeta()->appendName('description', '');
        $this->view->headLink()
            ->appendStylesheet("/easyui/themes/metro/easyui.css")
            ->appendStylesheet("/easyui/themes/icon.css")
            ->appendStylesheet("/easyui/themes/color.css")
            ->appendStylesheet("/js/common/contentxmenu/jquery.contextMenu.min.css");
        $this->view->headScript()
            ->appendFile("/easyui/jquery.easyui.min.js")
            ->appendFile("/easyui/jquery.edatagrid.js")
            ->appendFile("/easyui/datagrid-filter.js")
            ->appendFile("/easyui/locale/easyui-lang-es.js")
            ->appendFile("/js/common/contentxmenu/jquery.contextMenu.min.js")
            ->appendFile("/js/operaciones/catalogo/consulta.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => "Digits",
        );
        $v = array(
            "id" => array(new Zend_Validate_Int(), "NotEmpty"),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id")) {
            $this->view->idCliente = $input->id;
            if (in_array($this->_session->role, array("gerente", "super"))) {
                $this->view->edit = true;
            } else {
                $this->view->edit = false;
            }
        }
    }

    public function imagenesAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idProducto" => "Digits",
            );
            $v = array(
                "idProducto" => array(new Zend_Validate_Int(), "NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idProducto")) {
                $mppr = new Operaciones_Model_CatalogoPartesImagenes();
                $arr = $mppr->obtenerImagenes($input->idProducto);
                $this->_helper->json(array("success" => true, "data" => $arr));
            } else {
                $this->_helper->json(array("success" => false));
            }
        } catch (Exception $e) {
            $this->_helper->json(array("success" => false, "message" => $e->getMessage()));
        }
    }

    public function umcAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $mppr = new Vucem_Model_VucemUmcMapper();
            $arr = $mppr->getAllUnits();
            return $this->_helper->json($arr);
        } catch (Exception $e) {
            $this->_helper->json(array("success" => false, "message" => $e->getMessage()));
        }
    }

    public function omaAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $mppr = new Vucem_Model_VucemUnidadesMapper();
            $arr = $mppr->getAllUnits();
            return $this->_helper->json($arr);
        } catch (Exception $e) {
            $this->_helper->json(array("success" => false, "message" => $e->getMessage()));
        }
    }

    public function paisesAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $mppr = new Vucem_Model_VucemPaisesMapper();
            $arr = $mppr->getAllCountries();
            return $this->_helper->json($arr);
        } catch (Exception $e) {
            $this->_helper->json(array("success" => false, "message" => $e->getMessage()));
        }
    }

    public function productoGuardarAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => "Digits",
                    "numParte" => array("StringToUpper"),
                    "numParteProveedor" => array("StringToUpper"),
                    "descripcion" => array("StringToUpper"),
                    "paisOrigen" => array("StringToUpper"),
                    "umc" => array("StringToUpper"),
                    "umt" => array("StringToUpper"),
                    "oma" => array("StringToUpper"),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "fraccion" => array("NotEmpty"),
                    "fraccion_2020" => array("NotEmpty"),
                    "nico" => array("NotEmpty"),
                    "numParte" => array("NotEmpty"),
                    "numParteProveedor" => array("NotEmpty"),
                    "descripcion" => array("NotEmpty"),
                    "paisOrigen" => array("NotEmpty"),
                    "umc" => array("NotEmpty"),
                    "umt" => array("NotEmpty"),
                    "oma" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("id")) {
                    $mppr = new Operaciones_Model_CatalogoPartes();
                    $arr = array(
                        "fraccion" => $input->isValid("fraccion") ? $input->fraccion : null,
                        "fraccion_2020" => $input->isValid("fraccion_2020") ? $input->fraccion_2020 : null,
                        "nico" => $input->isValid("nico") ? $input->nico : null,
                        "numParte" => $input->isValid("numParte") ? $input->numParte : null,
                        "numParteProveedor" => $input->isValid("numParteProveedor") ? $input->numParteProveedor : null,
                        "descripcion" => $input->isValid("descripcion") ? $input->descripcion : null,
                        "paisOrigen" => $input->isValid("paisOrigen") ? $input->paisOrigen : null,
                        "umc" => $input->isValid("umc") ? $input->umc : null,
                        "umt" => $input->isValid("umt") ? $input->umt : null,
                        "oma" => $input->isValid("oma") ? $input->oma : null,
                    );
                    if (($mppr->actualizar((int) $input->id, $arr))) {
                        $this->_helper->json(array("success" => true));
                    }
                    $this->_helper->json(array("success" => false));
                } else {
                    throw new Exception("Invalid Input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("errorMsg" => $ex->getMessage()));
        }
    }

    public function validarProductoAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => "Digits",
                    "idCliente" => "Digits",
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("id") && $input->isValid("idCliente")) {
                    $mppr = new Operaciones_Model_CatalogoPartes();
                    $r = $mppr->obtener($input->id);
                    if ($r) {
                        if ($r['valido']) {
                            $mppr->no_valido($input->id);
                        } else {
                            $mppr->valido($input->id, $this->_session->username);
                        }
                    }
                    $this->_helper->json(array("success" => true));
                } else {
                    throw new Exception("Invalid Input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("errorMsg" => $ex->getMessage()));
        }
    }

    public function validarProveedorAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => "Digits",
                    "idCliente" => "Digits",
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("id") && $input->isValid("idCliente")) {
                    $mppr = new Trafico_Model_FactPro();
                    $r = $mppr->obtener($input->id);
                    if ($r) {
                        if ($r['valido']) {
                            $mppr->no_valido($input->id);
                        } else {
                            $mppr->valido($input->id, $this->_session->username);
                        }
                    }
                    $this->_helper->json(array("success" => true));
                } else {
                    throw new Exception("Invalid Input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("errorMsg" => $ex->getMessage()));
        }
    }

    public function productoBorrarAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
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
                    $mppr = new Operaciones_Model_CatalogoPartes();
                    if ($mppr->borrar($input->id)) {
                        // to-do borrar imagenes
                        $this->_helper->json(array("success" => true));
                    }
                    throw new Exception("Unable to remove record!");
                } else {
                    throw new Exception("Invalid Input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("errorMsg" => $ex->getMessage()));
        }
    }

    public function productoNuevoAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idCliente" => "Digits",
                "idProveedor" => "Digits",
                "fraccion" => "StringToUpper",
                "numParteCliente" => "StringToUpper",
                "descripcion" => "StringToUpper",
            );
            $v = array(
                "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                "idProveedor" => array("NotEmpty", new Zend_Validate_Int()),
                "fraccion" => array("NotEmpty"),
                "numParteCliente" => array("NotEmpty"),
                "descripcion" => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idCliente") && $input->isValid("idProveedor") && $input->isValid("fraccion")) {
                $mapper = new Operaciones_Model_CatalogoPartes();
                if (!($mapper->verificar($input->idCliente, $input->idProveedor, $input->fraccion, $input->numParteCliente))) {
                    $mapper->agregar($input->idCliente, $input->idProveedor, $input->fraccion, $input->numParteCliente, $input->descripcion, $this->_session->username);
                    $this->_helper->json(array("success" => true));
                } else {
                    throw new Exception("<p style=\"color: red\"><strong>Error:</strong> El número de parte ya existe en la base de datos.</p>");
                }
            } else {
                throw new Exception("<p style=\"color: red\"><strong>Error:</strong> Invalid input!</p>");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("errorMsg" => $ex->getMessage()));
        }
    }

    public function proveedorBorrarAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
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
                    $mppr = new Trafico_Model_FactPro();
                    if ($mppr->borrar($input->id)) {
                        $this->_helper->json(array("success" => true));
                    }
                } else {
                    throw new Exception("Invalid Input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("errorMsg" => $ex->getMessage()));
        }
    }

    public function proveedorGuardarAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => "Digits",
                    "clave" => "StringToUpper",
                    "identificador" => "StringToUpper",
                    "nombre" => "StringToUpper",
                    "calle" => "StringToUpper",
                    "colonia" => "StringToUpper",
                    "localidad" => "StringToUpper",
                    "ciudad" => "StringToUpper",
                    "municipio" => "StringToUpper",
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "clave" => array("NotEmpty"),
                    "identificador" => array("NotEmpty"),
                    "nombre" => array("NotEmpty"),
                    "nombre" => array("NotEmpty"),
                    "calle" => array("NotEmpty"),
                    "numExt" => array("NotEmpty"),
                    "numInt" => array("NotEmpty"),
                    "colonia" => array("NotEmpty"),
                    "localidad" => array("NotEmpty"),
                    "municipio" => array("NotEmpty"),
                    "ciudad" => array("NotEmpty"),
                    "estado" => array("NotEmpty"),
                    "codigoPostal" => array("NotEmpty"),
                    "pais" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("id") && $input->isValid("clave") && $input->isValid("identificador") && $input->isValid("nombre")) {
                    $mppr = new Trafico_Model_FactPro();
                    $arr = array(
                        "identificador" => $input->isValid("identificador") ? $input->identificador : null,
                        "clave" => $input->isValid("clave") ? $input->clave : null,
                        "nombre" => $input->isValid("nombre") ? $input->nombre : null,
                        "calle" => $input->isValid("calle") ? $input->calle : null,
                        "numExt" => $input->isValid("numExt") ? $input->numExt : null,
                        "numInt" => $input->isValid("numInt") ? $input->numInt : null,
                        "colonia" => $input->isValid("colonia") ? $input->colonia : null,
                        "localidad" => $input->isValid("localidad") ? $input->localidad : null,
                        "municipio" => $input->isValid("municipio") ? $input->municipio : null,
                        "ciudad" => $input->isValid("ciudad") ? $input->ciudad : null,
                        "estado" => $input->isValid("estado") ? $input->ciudad : null,
                        "codigoPostal" => $input->isValid("codigoPostal") ? $input->codigoPostal : null,
                        "pais" => $input->isValid("pais") ? $input->pais : null,
                        "modificado" => date("Y-m-d H:i:s"),
                    );
                    if (($mppr->actualizar((int) $input->id, $arr))) {
                        $this->_helper->json(array("success" => true));
                    }
                    $this->_helper->json(array("success" => false));
                } else {
                    throw new Exception("Invalid Input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("errorMsg" => $ex->getMessage()));
        }
    }

    public function proveedorNuevoAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idCliente" => "Digits",
                "clave" => "StringToUpper",
                "identificador" => "StringToUpper",
                "nombre" => "StringToUpper",
            );
            $v = array(
                "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                "clave" => array("NotEmpty"),
                "identificador" => array("NotEmpty"),
                "nombre" => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("clave") && $input->isValid("identificador") && $input->isValid("nombre")) {
                $mppr = new Trafico_Model_FactPro();
                if (($mppr->verificar($input->idCliente, $input->identificador)) == false) {
                    $arr = array(
                        "idCliente" => $input->idCliente,
                        "clave" => $input->clave,
                        "identificador" => $input->identificador,
                        "nombre" => $input->nombre,
                        "creado" => date("Y-m-d H:i:s"),
                    );
                    $mppr->agregar($arr);
                    $this->_helper->json(array("success" => true));
                } else {
                    throw new Exception("<p style=\"color: red\"><strong>Error:</strong> El proveedor ya existe en la base de datos.</p>");
                }
            } else {
                throw new Exception("<p style=\"color: red\"><strong>Error:</strong> Invalid input!</p>");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("errorMsg" => $ex->getMessage()));
        }
    }

    public function productosAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idCliente" => array("Digits"),
                "idProveedor" => array("Digits"),
                "page" => array("Digits"),
                "rows" => array("Digits"),
                "excel" => array("StringToLower"),
            );
            $v = array(
                "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                "idProveedor" => array("NotEmpty", new Zend_Validate_Int()),
                "page" => array(new Zend_Validate_Int(), "default" => 1),
                "rows" => array(new Zend_Validate_Int(), "default" => 10),
                "filterRules" => "NotEmpty",
                "excel" => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idCliente") && $input->isValid("idProveedor")) {

                $dexcel = filter_var($input->excel, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

                $mppr = new Operaciones_Model_CatalogoPartes();
                if ($dexcel == false) {
                    $arr = $mppr->todos($input->idCliente, $input->idProveedor, $input->page, $input->rows, $input->filterRules);

                    if (isset($arr)) {
                        $this->_helper->json($arr);
                    } else {
                        $this->_helper->json(array("total" => 0, "rows" => array()));
                    }
                } else {
                    $arr = $mppr->todos($input->idCliente, $input->idProveedor);

                    $reportes = new OAQ_ExcelReportes();
                    $reportes->reportesTrafico(82, $arr['rows']);
                }
            } else {
                $this->_helper->json(array("total" => 0, "rows" => array()));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("errorMsg" => $ex->getMessage()));
        }
    }

    public function proveedoresAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {

            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idCliente" => array("Digits"),
                "page" => array("Digits"),
                "rows" => array("Digits"),
                "excel" => array("StringToLower"),
            );

            $v = array(
                "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                "page" => array(new Zend_Validate_Int(), "default" => 1),
                "rows" => array(new Zend_Validate_Int(), "default" => 10),
                "filterRules" => "NotEmpty",
                "excel" => array("NotEmpty"),
            );

            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());

            if ($input->isValid("idCliente")) {

                $dexcel = filter_var($input->excel, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

                $mppr = new Trafico_Model_FactPro();

                if ($dexcel == false) {
                    $arr = $mppr->obtenerPorCliente($input->idCliente, $input->page, $input->rows, $input->filterRules);
                    if (isset($arr)) {
                        $this->_helper->json($arr);
                    } else {
                        $this->_helper->json(array("total" => 0, "rows" => array()));
                    }
                } else {
                    $arr = $mppr->obtenerPorCliente($input->idCliente);

                    $reportes = new OAQ_ExcelReportes();
                    $reportes->reportesTrafico(81, $arr['rows']);
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("errorMsg" => $ex->getMessage()));
        }
    }

    public function subirFotosAction()
    {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idProducto" => array("Digits"),
                );
                $v = array(
                    "idProducto" => new Zend_Validate_Int(),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if (!$input->isValid("idProducto")) {
                    throw new Exception("Invalid input!");
                }
                $archivos = new OAQ_Archivos_Procesar();
                $mppr = new Operaciones_Model_CatalogoPartesImagenes();
                $upload = new Zend_File_Transfer_Adapter_Http();
                $upload->addValidator("Count", false, array("min" => 1, "max" => 15))
                    ->addValidator("Size", false, array("min" => "1", "max" => "20MB"))
                    ->addValidator("Extension", false, array("extension" => "jpg,jpeg", "case" => false));
                $path = APPLICATION_PATH . "/../public/imagenes/" . date("Y") . DIRECTORY_SEPARATOR . str_pad(date("m"), 2, '0', STR_PAD_LEFT) . DIRECTORY_SEPARATOR . str_pad(date("d"), 2, '0', STR_PAD_LEFT);
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
                $upload->setDestination($path);
                $files = $upload->getFileInfo();
                foreach ($files as $fieldname => $fileinfo) {
                    if (($upload->isUploaded($fieldname)) && ($upload->isValid($fieldname))) {
                        $ext = strtolower(pathinfo($fileinfo['name'], PATHINFO_EXTENSION));
                        $sha = sha1_file($fileinfo['tmp_name']);
                        $filename = $sha . '.' . $ext;
                        $upload->addFilter('Rename', $filename, $fieldname);
                        $upload->receive($fieldname);
                        if ((!$mppr->verificar($input->idProducto, $filename))) {
                            if (($id = $mppr->agregar($input->idProducto, realpath($path) . DIRECTORY_SEPARATOR . $filename, $fileinfo['name'], $this->_session->username))) {
                                $archivos->reducirImagen($id);
                            }
                        }
                    } else {
                    }
                }
                $this->_helper->json(array("success" => true));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function cartaInstruccionesAction()
    {
        $this->view->title = $this->_appconfig->getParam('title') . ' ' . " Carta de instrucciones";
        $this->view->headMeta()->appendName('description', '');
        $this->view->headLink()
            ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
            ->appendStylesheet("/js/common/toast/jquery.toast.min.css")
            ->appendStylesheet("/easyui/themes/metro/easyui.css")
            ->appendStylesheet("/easyui/themes/icon.css")
            ->appendStylesheet("/easyui/themes/color.css")
            ->appendStylesheet("/js/common/contentxmenu/jquery.contextMenu.min.css");
        $this->view->headScript()
            ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
            ->appendFile("/easyui/jquery.easyui.min.js")
            ->appendFile("/easyui/jquery.edatagrid.js")
            ->appendFile("/easyui/datagrid-filter.js")
            ->appendFile("/easyui/locale/easyui-lang-es.js")
            ->appendFile("/js/common/contentxmenu/jquery.contextMenu.min.js")
            ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js")
            ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js")
            ->appendFile("/js/common/toast/jquery.toast.min.js?")
            ->appendFile("/js/operaciones/catalogo/carta-instrucciones.js?" . time());
    }

    public function deliveriesAction()
    {
        $this->view->title = $this->_appconfig->getParam('title') . ' ' . " Deliveries";
        $this->view->headMeta()->appendName('description', '');
        $this->view->headLink()
            ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
            ->appendStylesheet("/js/common/toast/jquery.toast.min.css")
            ->appendStylesheet("/easyui/themes/metro/easyui.css")
            ->appendStylesheet("/easyui/themes/icon.css")
            ->appendStylesheet("/easyui/themes/color.css")
            ->appendStylesheet("/js/common/contentxmenu/jquery.contextMenu.min.css");
        $this->view->headScript()
            ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
            ->appendFile("/easyui/jquery.easyui.min.js")
            ->appendFile("/easyui/jquery.edatagrid.js")
            ->appendFile("/easyui/datagrid-filter.js")
            ->appendFile("/easyui/locale/easyui-lang-es.js")
            ->appendFile("/js/common/contentxmenu/jquery.contextMenu.min.js")
            ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js")
            ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js")
            ->appendFile("/js/common/toast/jquery.toast.min.js?")
            ->appendFile("/js/operaciones/catalogo/deliveries.js?" . time());
    }

    public function editarCartaInstruccionesAction()
    {
        $this->view->title = $this->_appconfig->getParam('title') . ' ' . " Editar carta de instrucciones";
        $this->view->headMeta()->appendName('description', '');
        $this->view->headLink()
            ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
            ->appendStylesheet("/js/common/toast/jquery.toast.min.css")
            ->appendStylesheet("/easyui/themes/metro/easyui.css")
            ->appendStylesheet("/easyui/themes/icon.css")
            ->appendStylesheet("/easyui/themes/color.css")
            ->appendStylesheet("/js/common/contentxmenu/jquery.contextMenu.min.css");
        $this->view->headScript()
            ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
            ->appendFile("/js/common/toast/jquery.toast.min.js?")
            ->appendFile("/easyui/jquery.easyui.min.js")
            ->appendFile("/easyui/jquery.edatagrid.js")
            ->appendFile("/easyui/datagrid-filter.js")
            ->appendFile("/easyui/locale/easyui-lang-es.js")
            ->appendFile("/js/common/contentxmenu/jquery.contextMenu.min.js")
            ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js")
            ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js")
            ->appendFile("/js/common/toast/jquery.toast.min.js?")
            ->appendFile("/js/operaciones/catalogo/editar-carta-instrucciones.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits"),
        );
        $v = array(
            "id" => array(new Zend_Validate_Int(), "NotEmpty")
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id")) {
            $mppr = new Operaciones_Model_CartaInstrucciones();
            $arr = $mppr->obtener($input->id);
            $this->view->arr = $arr;
        }
    }
}
