<?php

class Bodega_GetController extends Zend_Controller_Action
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
        $this->_firephp = Zend_Registry::get("firephp");
    }

    public function preDispatch()
    {
        $this->_session = null ? $this->_session = new Zend_Session_Namespace("") : $this->_session = new Zend_Session_Namespace($this->_config->app->namespace);
        if ($this->_session->authenticated == true) {
            $session = new OAQ_Session($this->_session, $this->_appconfig);
            $session->actualizar();
            $session->actualizarSesion();
        } else {
            $this->getResponse()->setRedirect($this->_appconfig->getParam("link-logout"));
        }
    }

    public function proveedorAction()
    {
        try {
            $f = array(
                "*" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags()),
                "idCliente" => array(new Zend_Filter_Digits()),
                "idProveedor" => array(new Zend_Filter_Digits()),
                "tipoOperacion" => array(new Zend_Filter_Digits()),
            );
            $v = array(
                "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                "idProveedor" => array("NotEmpty", new Zend_Validate_Int()),
                "tipoOperacion" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idCliente")) {

                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");

                $view->idCliente = $input->idCliente;
                if ($input->isValid("idProveedor")) {
                    $view->idProveedor = $input->idProveedor;
                }
                if ($input->isValid("tipoOperacion")) {
                    if ($input->tipoOperacion == 1) {
                        $view->proveedorDestinatario = "Proveedor";
                    } else {
                        $view->proveedorDestinatario = "Destinatario";
                    }
                }
                $this->_helper->json(array("success" => true, "html" => $view->render("proveedor.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Zend_Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerFacturasAction()
    {
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
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
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

    public function obtenerBultosAction()
    {
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
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                    $model = new Bodega_Model_Bultos();
                    $rows = $model->obtenerBultos($input->id);
                    if (isset($rows)) {
                        $view->data = $rows;
                        $view->id = $input->id;
                        $this->_helper->json(array("success" => true, "html" => $view->render("obtener-bultos.phtml")));
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

    public function previewSubidivisionAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
                "bultos" => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");

                $bodega = new OAQ_Bodega(array("idTrafico" => $input->id));

                if (!empty($input->bultos)) {

                    $model = new Bodega_Model_Bultos();
                    $bultos = $model->obtenerBultosByIds($input->bultos);

                    $total_bultos = $model->totalBultos($input->id);

                    $view->id = $input->id;
                    $view->ids = implode($input->bultos, ",");
                    $view->total = $total_bultos;
                    $view->cantidad = count($bultos);
                    $view->restantes = $total_bultos - count($bultos);

                    $n_referencia = $bodega->buscarSubdivision();

                    $view->n_referencia = $n_referencia;
                }

                $this->_helper->json(array("success" => true, "html" => $view->render("preview-subidivision.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function cambiarTipoBultoAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
                "bultos" => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");

                $view->id = $input->id;
                $view->ids = implode($input->bultos, ",");

                $mppr = new Bodega_Model_TipoBulto();
                $view->tipo_bultos = $mppr->obtenerTodos();

                $this->_helper->json(array("success" => true, "html" => $view->render("cambiar-tipo-bulto.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function editarBultoAction()
    {
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

                $model = new Bodega_Model_Bultos();
                $mppr = new Bodega_Model_TipoBulto();

                $row = $model->obtenerBulto($input->id);
                if (isset($row)) {
                    $view->data = $row;
                    $view->tipo_bultos = $mppr->obtenerTodos();
                    $this->_helper->json(array("success" => true, "html" => $view->render("editar-bulto.phtml")));
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerConsolidadoAction()
    {
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
                    $traficos = new OAQ_Trafico(array("idTrafico" => $input->id));
                    if (($arr = $traficos->traficosConsolidados())) {
                        $this->_helper->json(array("success" => true, "results" => $arr));
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

    public function obtenerGuiasAction()
    {
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
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
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

    public function obtenerComentariosAction()
    {
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
                    $arr['bitacora'] = $trafico->obtenerBitacoraBodega();
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

    public function obtenerArchivosAction()
    {
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
                    "id" => new Zend_Validate_Int(),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid()) {
                    $view = new Zend_View();
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                    $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");
                    $mppr = new Trafico_Model_TraficosMapper();
                    $array = $mppr->obtenerPorId($input->id);

                    $repo = new Archivo_Model_RepositorioMapper();

                    $traficos = new OAQ_Bodega(array("idTrafico" => $input->id, "idBodega" => $array['idBodega']));
                    if (($arr = $traficos->traficosConsolidados())) {
                        $referencias = array($array["referencia"]);
                        foreach ($arr as $item) {
                            $referencias[] = $item["referencia"];
                        }
                        $archivos = $repo->obtenerArchivosReferencia($referencias);
                    } else {
                        $archivos = $repo->obtenerArchivosReferencia($array["referencia"]);
                    }
                    $view->archivos = $archivos;

                    $trafico = new OAQ_Bodega(array("idTrafico" => $input->id, "idBodega" => $array['idBodega'], "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));
                    $index = $trafico->verificarIndexRepositorios();

                    if (in_array($this->_session->role, array("super", "gerente", "trafico_ejecutivo", "trafico"))) {
                        $view->canDelete = true;
                    }

                    $this->_helper->json(array("success" => true, "html" => $view->render("obtener-archivos.phtml"), "repos" => $index));
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function procesarMiniaturaAction()
    {
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
                $gallery = new Trafico_Model_Imagenes();
                $row = $gallery->obtener($input->id);

                $image = $row["carpeta"] . DIRECTORY_SEPARATOR . $row["imagen"];

                if (file_exists($image)) {
                    $ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));

                    $thumb = pathinfo($image, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . pathinfo($image, PATHINFO_FILENAME) . "_thumb." . pathinfo($image, PATHINFO_EXTENSION);
                    if (file_exists($image)) {
                        if (extension_loaded("imagick")) {
                            if (!file_exists($thumb)) {
                                $im = new Imagick();
                                $im->pingImage($image);
                                $im->readImage($image);

                                if ($im->getimagewidth() > 1024) {
                                    $im->resizeimage(1024, round($im->getimageheight() / ($im->getimagewidth() / 1024), 0), Imagick::FILTER_LANCZOS, 1);
                                    $im->writeImage($image);
                                }
                                $im->thumbnailImage(150, round($im->getimageheight() / ($im->getimagewidth() / 150), 0));
                                $im->writeImage($thumb);
                                $im->destroy();
                            }
                            if (file_exists($thumb)) {
                                $gallery->actualizar($input->id, array("miniatura" => pathinfo($thumb, PATHINFO_BASENAME)));
                            }
                        }
                    }
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerFotosAction()
    {
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
                    "id" => new Zend_Validate_Int(),
                    "borrar" => new Zend_Validate_Int(),
                    "uri" => new Zend_Validate_NotEmpty(),
                );
                $input = new Zend_Filter_Input($filters, $validators, $request->getPost());
                if ($input->isValid("id")) {
                    $view = new Zend_View();
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                    $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");

                    $view->idTrafico = $input->id;
                    if ($input->isValid("borrar")) {
                        $view->borrar = 0;
                    }
                    if ($input->isValid("uri")) {
                        $view->uri = $input->uri;
                    }
                    $gallery = new Trafico_Model_Imagenes();
                    $view->gallery = $gallery->miniaturas($input->id);
                    $this->_helper->json(["success" => true, "html" => $view->render("obtener-fotos.phtml")]);
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerFechasAction()
    {
        try {
            $f = array(
                "idTrafico" => array("StringTrim", "StripTags", "Digits"),
            );
            $v = array(
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idTrafico")) {
                $db = Zend_Registry::get("oaqintranet");
                $sql = $db->select()
                    ->from(array("t" => "traficos"), array(
                        new Zend_Db_Expr("DATE_FORMAT(fechaEta,'%Y-%m-%d') AS fechaEta"),
                        new Zend_Db_Expr("DATE_FORMAT(fechaRevision,'%Y-%m-%d') AS fechaRevision"),
                        new Zend_Db_Expr("DATE_FORMAT(fechaDescarga,'%Y-%m-%d') AS fechaDescarga"),
                        new Zend_Db_Expr("DATE_FORMAT(fechaCarga,'%Y-%m-%d') AS fechaCarga"),
                        new Zend_Db_Expr("DATE_FORMAT(fechaSalida,'%Y-%m-%d') AS fechaSalida"),
                    ))
                    ->where("t.id = ?", $input->idTrafico);
                $stmt = $db->fetchRow($sql);
                if ($stmt) {
                    return $this->_helper->json(array("success" => true, "dates" => $stmt));
                } else {
                    throw new Exception("No data found.");
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function consolidarTraficosAction()
    {
        try {
            $f = array(
                "ids" => array("StringTrim", "StripTags"),
            );
            $v = array(
                "ids" => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("ids")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $traficos = new OAQ_Trafico();
                $arr = $traficos->seleccionConsolidarTraficos((array) $input->ids);
                if (!empty($arr)) {
                    $view->data = $arr;
                    $view->ids = $input->ids;
                }
                $this->_helper->json(array("success" => true, "html" => $view->render("consolidar-traficos.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function enviarATraficoAction()
    {
        try {
            $f = array(
                "id" => array("StringTrim", "StripTags"),
            );
            $v = array(
                "id" => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");

                $mppr = new Trafico_Model_TraficoUsuAduanasMapper();
                if (in_array($this->_session->role, $this->_todosClientes)) {
                    $customs = $mppr->aduanasDeUsuario();
                } else {
                    $customs = $mppr->aduanasDeUsuario($this->_session->id);
                }
                $form = new Trafico_Form_CrearTraficoNew(array("aduanas" => $customs));
                $view->id = $input->id;
                $view->form = $form;

                $this->_helper->json(array("success" => true, "html" => $view->render("enviar-a-trafico.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function ordenCargaAction()
    {
        try {
            $f = array(
                "ids" => array("StringTrim", "StripTags"),
            );
            $v = array(
                "ids" => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("ids")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");

                $traficos = new OAQ_Trafico();
                $arr = $traficos->seleccionConsolidarTraficos((array) $input->ids);
                if (!empty($arr)) {

                    $view->data = $arr;

                    $ordenc = new Bodega_Model_OrdenCarga();
                    if (!($orden_carga = $ordenc->verificar($input->ids[0]))) {
                        $orden_carga = $ordenc->agregar($input->ids[0], $this->_session->id);
                    }
                    $view->ordenCarga = $orden_carga;
                }
                $view->ids = implode(",", $input->ids);

                $this->_helper->json(array("success" => true, "html" => $view->render("orden-carga.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function verEdocumentAction()
    {
        error_reporting(E_ALL & E_NOTICE);
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(false);
        $this->view->headLink()
            ->appendStylesheet("/less/traffic-module.css?" . time());
        try {
            $this->view->headScript()
                ->appendFile("/js/common/jquery-1.9.1.min.js");
            $f = array(
                "idFactura" => array("StringTrim", "StripTags", "Digits"),
                "idTrafico" => array("StringTrim", "StripTags", "Digits"),
                "type" => array("StringTrim", "StripTags", "StringToLower"),
                "cove" => array("StringTrim", "StripTags", "StringToUpper"),
            );
            $v = array(
                "idFactura" => array("NotEmpty", new Zend_Validate_Int()),
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                "type" => array("NotEmpty"),
                "cove" => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idFactura") && $input->isValid("idTrafico") && $input->isValid("cove") && $input->isValid("type")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . '/../views/scripts/get/');
                $mppr = new Trafico_Model_VucemMapper();
                $row = $mppr->obtenerPorFactura($input->idFactura);
                if (isset($row)) {
                    $vucem = new OAQ_TraficoVucem();
                    $trafico = new OAQ_Trafico(array("idTrafico" => $input->idTrafico, "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));
                    $vucem->setPatente($trafico->getPatente());
                    $vucem->setAduana($trafico->getAduana());
                    $vucem->setPedimento($trafico->getPedimento());
                    $vucem->setReferencia($trafico->getReferencia());
                    $xml = $vucem->enviarCove($row["id"], $input->idTrafico, $input->idFactura, false);
                    if ($xml) {
                        $lib = new OAQ_VucemEnh();
                        $array = $lib->vucemXmlToArray($xml);
                        unset($array['Header']);
                        if (isset($array['Body']['solicitarRecibirCoveServicio']['comprobantes'])) {
                            $this->view->idFactura = $input->idFactura;
                            $this->view->cove = $input->cove;
                            $view->data = $array['Body']['solicitarRecibirCoveServicio']['comprobantes'];
                            $view->data['cove'] = '';
                            $view->data['emisor']['tipoIdentificador'] = $this->view->identificadorDesc($view->data['emisor']['tipoIdentificador']);
                            $this->view->content = $view->render('edocument-cove.phtml');
                        }
                    }
                }
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function enviarVucemAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => "Digits",
                "idTrafico" => "Digits",
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id") && $input->isValid("idTrafico")) {

                $mppr = new Trafico_Model_VucemMapper();
                $arr = $mppr->obtenerVucem($input->id);
                $vucem = new OAQ_TraficoVucem();

                $trafico = new OAQ_Trafico(array("idTrafico" => $input->idTrafico, "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));
                if (isset($arr["idFactura"])) {
                    $vucem->setPatente($trafico->getPatente());
                    $vucem->setAduana($trafico->getAduana());
                    $vucem->setPedimento($trafico->getPedimento());
                    $vucem->setReferencia($trafico->getReferencia());
                    $arr = $vucem->enviarCove($input->id, $input->idTrafico, $arr["idFactura"]);
                    if (!empty($arr)) {
                        if ($vucem->analizarEnvioCove($mppr, $input->idTrafico, $input->id, $this->_session->id, $arr)) {
                            $this->_helper->json(array("success" => true));
                        }
                    }
                }
                if (isset($arr["idArchivo"])) {
                    $vucem->setPatente($trafico->getPatente());
                    $vucem->setAduana($trafico->getAduana());
                    $vucem->setPedimento($trafico->getPedimento());
                    $vucem->setReferencia($trafico->getReferencia());
                    $arr = $vucem->enviarEdocument($input->id, $input->idTrafico, $arr["idArchivo"], $arr["tipoDocumento"]);
                    if (!empty($arr)) {
                        if ($vucem->analizarEnvioEdocument($mppr, $input->idTrafico, $input->id, $this->_session->id, $arr)) {
                            $this->_helper->json(array("success" => true));
                        }
                    } else {
                        $this->_helper->json(array("success" => false));
                    }
                }
            } else {
                throw new Exception("Invalid input");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function consultaRespuestaVucemAction()
    {
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
                $mppr = new Trafico_Model_TraficoVucemLog();
                $arr = $mppr->obtener($input->id);
                if (!empty($arr)) {
                    $trafico = new OAQ_Trafico(array("idTrafico" => $arr["idTrafico"], "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));
                    $vucem = new OAQ_TraficoVucem();
                    $vucem->setPatente($trafico->getPatente());
                    $vucem->setAduana($trafico->getAduana());
                    $vucem->setPedimento($trafico->getPedimento());
                    $vucem->setReferencia($trafico->getReferencia());
                    $mpprv = new Trafico_Model_VucemMapper();
                    $arrv = $mpprv->obtenerVucem($arr["idVucem"]);
                    if (isset($arrv["idFactura"])) {
                        if ($vucem->consultaRespestaCove($mppr, $input->id, $arr["idVucem"], $arr["numeroOperacion"], $this->_session->username, $this->_session->id)) {
                            $this->_helper->json(array("success" => true));
                        }
                    }
                    if (isset($arrv["idArchivo"])) {
                        if ($vucem->consultaRespuestaEdocument($mppr, $input->id, $arr["idVucem"], $arr["numeroOperacion"], $this->_session->username)) {
                            $this->_helper->json(array("success" => true));
                        }
                    }
                }
            } else {
                throw new Exception("Invalid input");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage() . " " . $ex->getTraceAsString()));
        }
    }

    public function consultaDetalleLogAction()
    {
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
                $mppr = new Trafico_Model_TraficoVucemLog();
                $arr = $mppr->obtenerUltimo($input->id);
                if (!empty($arr)) {
                    $this->_helper->json(array("success" => true, "results" => $arr));
                }
            } else {
                throw new Exception("Invalid input");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function vucemGuardarAction()
    {
        try {
            $f = array(
                "id" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags(), new Zend_Filter_Digits()),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {

                $vucem = new OAQ_TraficoVucem();

                $mppr = new Trafico_Model_VucemMapper();
                $arr = $mppr->obtenerVucem($input->id);

                if (isset($arr["idFactura"])) {
                    if ($vucem->guardarDetalleCoveXmlPdf($input->id, $this->_session->username, $this->_session->id)) {
                        $this->_helper->json(array("success" => true));
                    }
                }
                if (isset($arr["idArchivo"])) {
                    if ($vucem->guardarEdocumentXmlPdf($input->id, $this->_session->username, $this->_session->id)) {
                        $this->_helper->json(array("success" => true));
                    }
                }

                $this->_helper->json(array("success" => true));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Zend_Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function vucemFirmasAction()
    {
        try {
            $f = array(
                "idTrafico" => array("StringTrim", "StripTags", "Digits"),
            );
            $v = array(
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idTrafico")) {
                $trafico = new OAQ_Trafico(array("idTrafico" => $input->idTrafico, "idUsuario" => $this->_session->id));
                $arr = array();
                if ($trafico->getPatente() !== null) {
                    $mppr = new Trafico_Model_SellosAgentes();
                    $rows = $mppr->obtener($trafico->getPatente());
                    if (!empty($rows)) {
                        $arr["agente"] = $rows;
                    }
                }
                if ($trafico->getIdCliente() !== null) {
                    $mppr = new Trafico_Model_SellosClientes();
                    $rows = $mppr->obtener($trafico->getIdCliente());
                    if (!empty($rows)) {
                        $arr["cliente"] = $rows;
                    }
                }
                $vu = new Trafico_Model_VucemMapper();
                if (($config = $vu->obtenerConfig($input->idTrafico))) {
                    $arr['config'] = array(
                        'idSelloAgente' => $config["idSelloAgente"],
                        'idSelloCliente' => $config["idSelloCliente"],
                    );
                }
                $this->_helper->json(array("success" => true, "results" => $arr));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function vucemBitacoraAction()
    {
        try {
            $f = array(
                "idTrafico" => array("StringTrim", "StripTags", "Digits"),
            );
            $v = array(
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idTrafico")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");
                $mppr = new Trafico_Model_VucemMapper();
                $arr = $mppr->obtener($input->idTrafico);
                if (isset($arr) && !empty($arr)) {
                    $view->results = $arr;
                }
                $this->_helper->json(array("success" => true, "html" => $view->render("vucem-bitacora.phtml")));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerSelloDefaultAction()
    {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $f = array(
                "*" => array("StringTrim", "StripTags"),
            );
            $v = array(
                "idCliente" => new Zend_Validate_Int(),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idCliente")) {
                $mppr = new Trafico_Model_CliSello();
                $id = $mppr->obtenerDefault($input->idCliente);
                if (isset($id)) {
                    $this->_helper->json(array("success" => true, "id" => $id));
                }
                $this->_helper->json(array("success" => false));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function facturasPedimentoAction()
    {
        $this->_helper->viewRenderer->setNoRender(false);
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idTrafico" => "Digits",
            );
            $v = array(
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idTrafico")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->idTrafico = $input->idTrafico;
                $misc = new OAQ_Misc();
                $mapper = new Trafico_Model_TraficosMapper();
                $arr = $mapper->obtenerPorId($input->idTrafico);
                if (isset($arr["patente"]) && isset($arr["aduana"])) {
                    $db = $misc->sitawinTrafico($arr["patente"], $arr["aduana"]);
                    if (isset($db)) {
                        $facturas = $db->obtenerFacturas($arr["referencia"]);
                        if (!isset($facturas) || empty($facturas)) {
                            $facturas = $db->obtenerFacturasRemesas($arr["pedimento"]);
                        }
                        if (isset($facturas) && !empty($facturas)) {
                            $view->arr = $facturas;
                        }
                    }
                }
                $this->_helper->json(array("success" => true, "html" => $view->render("facturas-pedimento.phtml")));
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function importarPlantillaAction()
    {
        $this->_helper->viewRenderer->setNoRender(false);
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idTrafico" => "Digits",
            );
            $v = array(
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idTrafico")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->idTrafico = $input->idTrafico;
                $this->_helper->json(array("success" => true, "html" => $view->render("importar-plantilla.phtml")));
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function vucemPreviewAction()
    {
        $this->_helper->viewRenderer->setNoRender(false);
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
                $mppr = new Trafico_Model_TraficoFacturasMapper();
                $arr = $mppr->detalleFactura($input->idFactura);
                if (isset($arr["archivoCove"]) && $arr["archivoCove"] !== null) {
                    if (file_exists($arr["archivoCove"])) {
                        $this->view->contenido = file_get_contents($arr["archivoCove"]);
                    }
                } else {
                    $this->view->contenido = "No hay archivo XML";
                }
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function descargaCarpetaExpedienteAction()
    {
        try {
            $f = array(
                "id" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags(), new Zend_Filter_Digits()),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $traffic = new OAQ_Trafico(array("idTrafico" => $input->id));
                $id = $traffic->verificarIndexRepositorios();
                if ($id) {
                    $this->_helper->json(array("success" => true, "id" => $id));
                } else {
                    $this->_helper->json(array("success" => false));
                }
            } else {
                $this->_helper->json(array("success" => false, "message" => "Invalid input!"));
            }
        } catch (Zend_Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function imprimirCodigoAction()
    {
        try {

            $data = array();
            $print = new OAQ_Imprimir_CodigoBarras($data, "L", "pt", "LETTER");
            $print->set_filename("codigo_barras.pdf");
            $print->Create();
            $print->Output("codigo_barras.pdf", "I");
        } catch (Zend_Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function imprimirEtiquetasAction()
    {
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

                $model = new Trafico_Model_TraficosMapper();
                $row = $model->obtenerPorId($input->id);
                $misc = new OAQ_Misc();

                $mppr = new Bodega_Model_Bultos();
                $bmppr = new Bodega_Model_Bodegas();

                $bodega = $bmppr->obtenerDatos($row['idBodega']);

                $direccion = $bodega['calle'] . $bodega['numExt'];
                if (isset($bodega['numInt'])) {
                    $direccion .= ", " . $bodega['numInt'];
                }
                if (isset($bodega['colonia'])) {
                    $direccion .= ", " . $bodega['colonia'];
                }
                if (isset($bodega['localidad'])) {
                    $direccion .= ", " . $bodega['localidad'];
                }
                if (isset($bodega['municipio'])) {
                    $direccion .= ", " . $bodega['municipio'];
                }
                if (isset($bodega['estado'])) {
                    $direccion .= ", " . $bodega['estado'];
                }
                if (isset($bodega['codigoPortal'])) {
                    $direccion .= " CP " . $bodega['codigoPortal'];
                }
                if (isset($bodega['pais'])) {
                    $direccion .= ", " . $bodega['pais'];
                }
                if (isset($bodega['telefono'])) {
                    $direccion .= ", Tel(s). " . $bodega['telefono'];
                }
                if (isset($bodega['url'])) {
                    $direccion .= " URL: " . $bodega['url'];
                }

                $data = array(
                    "filename" => "ETIQUETAS_{$row['referencia']}",
                    "bultos" => array(),
                    "nombre_bodega" => $bodega['nombre'],
                    "logo" => $bodega['imagen'],
                    "referencia" => $row['referencia'],
                    "rfc_cliente" => $row['rfcCliente'],
                    "direccion" => $direccion,
                    "id_trafico" => $input->id,
                );

                for ($i = 1; $i <= (int) $row['bultos']; $i++) {
                    if (!($r = $mppr->verificar($i, $row['id']))) {
                        $arr = array(
                            "idTrafico" => $input->id,
                            "idBodega" => $row['idBodega'],
                            "idUsuario" => $this->_session->id,
                            "numBulto" => $i,
                            "uuid" => $misc->getUuid($row['referencia'] . $row['rfcCliente'] . $i),
                            "creado" => date("Y-m-d H:i:s"),
                        );
                        if (($id = $mppr->agregar($arr))) {
                            $data['bultos'][$i] = array("id_bulto" => $r['id'], "uuid" => $arr['uuid']);
                        }
                    } else {
                        $data['bultos'][$i] = array("id_bulto" => $r['id'], "uuid" => $r['uuid']);
                    }
                }

                $print = new OAQ_Imprimir_CodigoBarras($data, "L", "pt", "LETTER");
                $print->set_filename($data['filename'] . ".pdf");
                $print->Create();
                $print->Output($data['filename'] . ".pdf");
            } else {
            }
        } catch (Zend_Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function formatoEntradaAction()
    {
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

                $model = new Trafico_Model_TraficosMapper();
                $row = $model->obtenerPorId($input->id);

                $mpr = new Bodega_Model_Bodegas();
                $b = $mpr->obtener($row['idBodega']);

                $filename = "FORMATO_ENTRADA_{$row['referencia']}.pdf";

                $data = array(
                    "title" => "FORMATO DE ENTRADA",
                    "title_logo" => "pdf_logo.jpg",
                    "company" => $this->_appconfig->getParam('empresa'),
                    "filename" => $filename,
                    "bultos" => array(),
                    "nombre_bodega" => $b['nombre'],
                    "referencia" => $row['referencia'],
                    "rfc_cliente" => $row['rfcCliente'],
                    "nom_cliente" => $row['nombreCliente'],
                    "fecha_entrada" => $row['fechaEntrada'],
                    "fecha_descarga" => $row['fechaDescarga'],
                    "fecha_revision" => $row['fechaRevision'],
                    "descripcionMercancia" => $row['descripcionMercancia'],
                    "comentarios" => $row['comentarios'],
                    "observaciones" => $row['observaciones'],
                    "valorDolares" => $row['valorDolares'],
                    "valorComercial" => $row['valorComercial'],
                    "divisa" => $row['divisa'],
                    "ubicacion" => $row['ubicacion'],
                    "peso_kg" => $row['pesoKg'],
                    "bultos" => $row['bultos'],
                );

                $print = new OAQ_Imprimir_FormatoEntrada($data, "P", "pt", "LETTER");
                $print->crear();
                $print->set_filename($filename);
                $print->Output($print->get_filename(), "I");
            } else {
            }
        } catch (Zend_Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function formatoSalidaAction()
    {
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

                $model = new Trafico_Model_TraficosMapper();
                $row = $model->obtenerPorId($input->id);

                $mpr = new Bodega_Model_Bodegas();
                $b = $mpr->obtener($row['idBodega']);

                $filename = "SALIDA_BODEGA_{$row['referencia']}.pdf";

                $data = array(
                    "title" => "SALIDA DE BODEGA",
                    "title_logo" => "pdf_logo.jpg",
                    "company" => $this->_appconfig->getParam('empresa'),
                    "filename" => $filename,
                    "bultos" => array(),
                    "nombre_bodega" => $b['nombre'],
                    "referencia" => $row['referencia'],
                    "rfc_cliente" => $row['rfcCliente'],
                    "nom_cliente" => $row['nombreCliente'],
                    "fecha_entrada" => $row['fechaEntrada'],
                    "fecha_descarga" => $row['fechaDescarga'],
                    "fecha_revision" => $row['fechaRevision'],
                    "fecha_salida" => $row['fechaSalida'],
                    "peso_kg" => $row['pesoKg'],
                    "bultos" => $row['bultos'],
                    "descripcionMercancia" => $row['descripcionMercancia'],
                    "valorDolares" => $row['valorDolares'],
                    "valorComercial" => $row['valorComercial'],
                    "divisa" => $row['divisa'],
                    "ubicacion" => $row['ubicacion'],
                    "bultos" => $row['bultos'],
                );

                $print = new OAQ_Imprimir_FormatoSalidaBodega($data, "P", "pt", "LETTER");
                $print->crear();
                $print->set_filename($filename);
                $print->Output($print->get_filename(), "I");
            } else {
            }
        } catch (Zend_Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function editarProveedorAction()
    {
        $this->_helper->viewRenderer->setNoRender(false);
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idBodega" => array("Digits"),
                "idCliente" => array("Digits"),
                "idProveedor" => array("Digits"),
            );
            $v = array(
                "idBodega" => array("NotEmpty", new Zend_Validate_Int()),
                "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                "idProveedor" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idBodega") && $input->isValid("idCliente")) {
                $view = new Zend_View();
                $mppr = new Vucem_Model_VucemPaisesMapper();
                $view->paisSelect = $mppr->getAllCountries();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");

                $view->idBodega = $input->idBodega;
                $view->idCliente = $input->idCliente;

                if ($input->isValid("idProveedor")) {

                    $mapper = new Bodega_Model_Proveedores();
                    $row = $mapper->obtener($input->idProveedor);

                    if (!empty($row)) {
                        $view->nombre = $row["nombre"];
                        $view->tipoIdentificador = $row["tipoIdentificador"];
                        $view->identificador = $row["identificador"];
                        $view->calle = $row["calle"];
                        $view->numInt = $row["numInt"];
                        $view->numExt = $row["numExt"];
                        $view->colonia = $row["colonia"];
                        $view->localidad = $row["localidad"];
                        $view->municipio = $row["municipio"];
                        $view->estado = $row["estado"];
                        $view->codigoPostal = $row["codigoPostal"];
                        $view->pais = $row["pais"];
                    }
                }
                $this->_helper->json(array("success" => true, "html" => $view->render("editar-proveedor.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function editarTransporteAction()
    {
        $this->_helper->viewRenderer->setNoRender(false);
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idBodega" => array("Digits"),
                "idLineaTransporte" => array("Digits"),
            );
            $v = array(
                "idBodega" => array("NotEmpty", new Zend_Validate_Int()),
                "idLineaTransporte" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idBodega")) {
                $view = new Zend_View();
                $mppr = new Vucem_Model_VucemPaisesMapper();
                $view->paisSelect = $mppr->getAllCountries();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");

                $this->_helper->json(array("success" => true, "html" => $view->render("editar-transporte.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function notificacionAction()
    {
        $this->_helper->viewRenderer->setNoRender(false);
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idTrafico" => array("Digits"),
            );
            $v = array(
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idTrafico")) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");

                $bodega = new OAQ_Bodega(array("idTrafico" => $input->idTrafico));

                $mppr = new Trafico_Model_ContactosCliMapper();
                $contactos = $mppr->notificacion($bodega->getIdCliente());

                $view->contactos = $contactos;

                $this->_helper->json(array("success" => true, "html" => $view->render("notificacion.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function readImageAction()
    {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => "Digits",
            );
            $v = array(
                "id" => array("Digits", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $mppr = new Trafico_Model_Imagenes();
                $image = $mppr->obtenerImagen($input->id);
                header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
                header("Cache-Control: post-check=0, pre-check=0", false);
                header("Pragma: no-cache");
                header("Content-Type: image/jpeg");
                header("Content-Length: " . filesize($image));
                echo file_get_contents($image);
            } else {
                throw new Exception("Invalid input");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerPlantasAction()
    {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idCliente" => array("Digits"),
                );
                $v = array(
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idCliente")) {
                    $mppr = new Trafico_Model_ClientesPlantas();
                    $arr = $mppr->obtener($input->idCliente);
                    if (!empty($arr)) {
                        $html = new V2_Html();
                        $html->select("traffic-select-medium", "idPlanta");
                        $html->addSelectOption("", "---");
                        if (count($arr)) {
                            foreach ($arr as $item) {
                                $html->addSelectOption($item["id"], $item["descripcion"]);
                            }
                        }
                    } else {
                        $html = new V2_Html();
                        $html->select("traffic-select-medium", "idPlanta");
                        $html->addSelectOption("", "---");
                        //$html->addSelectOption($item["id"], $item["descripcion"]);
                        $html->setDisabled();
                    }
                    $this->_helper->json(array("success" => true, "plantas" => isset($html) ? $html->getHtml() : null));
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

    public function proveedoresAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "name" => "StringToUpper",
                "idCliente" => "Digits",
                "idBodega" => "Digits",
                "idFactura" => "Digits",
                "idTrafico" => "Digits",
            );
            $v = array(
                "name" => "NotEmpty",
                "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                "idBodega" => array("NotEmpty", new Zend_Validate_Int()),
                "idFactura" => array("NotEmpty", new Zend_Validate_Int()),
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($i->isValid("name")) {
                if ($i->isValid("idTrafico")) {
                    $bodega = new OAQ_Bodega(array("idTrafico" => $i->idTrafico));
                    $mapper = new Bodega_Model_Proveedores();
                    $arr = $mapper->buscarProveedor($i->idCliente, $i->name);
                    $this->_helper->json($arr);
                } else if ($i->isValid("idBodega")) {
                    $mapper = new Bodega_Model_Proveedores();
                    $arr = $mapper->buscarProveedor($i->idCliente, $i->name);
                    $this->_helper->json($arr);
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerProveedorAction()
    {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "name" => "StringToUpper",
                "idCliente" => "Digits",
                "idBodega" => "Digits",
                "idFactura" => "Digits",
                "idTrafico" => "Digits",
            );
            $v = array(
                "name" => "NotEmpty",
                "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                "idBodega" => array("NotEmpty", new Zend_Validate_Int()),
                "idFactura" => array("NotEmpty", new Zend_Validate_Int()),
                "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($i->isValid("name")) {
                if ($i->isValid("idTrafico")) {
                    $mapper = new Bodega_Model_Proveedores();
                    $row = $mapper->obtenerProveedor($i->idCliente, $i->name);
                } else if ($i->isValid("idBodega")) {
                    $mapper = new Bodega_Model_Proveedores();
                    $row = $mapper->obtenerProveedor($i->idCliente, $i->name);
                } else {
                    throw new Exception("Invalid input!");
                }
                $this->_helper->json(array("success" => true, "results" => $row));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
}
