<?php

class Trafico_PostController extends Zend_Controller_Action {

    protected $_session;
    protected $_svucem;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;
    protected $_logger;
    protected $_firephp;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $this->_logger = Zend_Registry::get("logDb");
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
        $this->_svucem = NULL ? $this->_svucem = new Zend_Session_Namespace("") : $this->_svucem = new Zend_Session_Namespace("OAQVucem");
        $this->_svucem->setExpirationSeconds($this->_appconfig->getParam("session-exp"));
    }

    public function obtenerClientesAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idAduana" => array("Digits"),
                );
                $v = array(
                    "idAduana" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idAduana")) {
                    $mppr = new Trafico_Model_TraficoAduanasMapper();
                    $mapper = new Trafico_Model_TraficoCliAduanasMapper();

                    if ($this->_session->role == 'inhouse') {
                        $referencias = new OAQ_Referencias();
                        $res = $referencias->restricciones($this->_session->id, $this->_session->role);

                        $mapper = new Trafico_Model_ClientesMapper();
                        $rows = $mapper->datosClientes($res['rfcs']);
                    } else {
                        $rows = $mapper->clientesAduana($input->idAduana);
                    }
                    if (isset($rows) && !empty($rows)) {
                        $html = "";
                        foreach ($rows as $item) {
                            if (isset($item["idCliente"])) {
                                $html .= "<option value=\"{$item["idCliente"]}\">{$item["nombre"]}</option>";
                            } else {
                                $html .= "<option value=\"{$item["id"]}\">{$item["nombre"]}</option>";
                            }
                        }
                        $this->_helper->json(array("success" => true, "html" => $html, "tipoAduana" => $mppr->tipoAduana($input->idAduana)));
                    } else {
                        throw new Exception("No tiene clientes asignados o no hay clientes asignados a esa aduana.");
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

    public function clavesAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "page" => array("Digits"),
                "size" => array("Digits"),
                "buscar" => array("StringToUpper"),
            );
            $v = array(
                "page" => array(new Zend_Validate_Int(), "default" => 1),
                "size" => array(new Zend_Validate_Int(), "default" => 10),
                "buscar" => array(new Zend_Validate_Regex("/^[-_a-zA-Z0-9.]+$/")),
            );
            $input = new Zend_Filter_Input($f, $v, $r->getPost());
            $mapper = new Trafico_Model_CvePedimentos();
            $rows = $mapper->obtenerClaves($input->buscar);
            if (isset($rows) && !empty($rows)) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/catalogos/");
                $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");
                $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array($rows));
                $paginator->setItemCountPerPage($input->size);
                $paginator->setCurrentPageNumber($input->page);
                $view->paginator = $paginator;
                $view->funcion = "clavePedimento";
                $view->busqueda = $input->buscar;
                $this->_helper->json(array("success" => true, "html" => $view->render("claves.phtml"), "pagina" => $input->page, "funcion" => "clavePedimento"));
            }
            $this->_helper->json(array("success" => false));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function unidadesAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "page" => array("Digits"),
                "size" => array("Digits"),
                "buscar" => array("StringToUpper"),
            );
            $v = array(
                "page" => array(new Zend_Validate_Int(), "default" => 1),
                "size" => array(new Zend_Validate_Int(), "default" => 10),
                "buscar" => array(new Zend_Validate_Regex("/^[-_a-zA-Z0-9.]+$/")),
            );
            $input = new Zend_Filter_Input($f, $v, $r->getPost());
            $mapper = new Vucem_Model_VucemUnidadesMapper();
            $rows = $mapper->obtenerUnidades($input->buscar);
            if (isset($rows) && !empty($rows)) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/catalogos/");
                $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");
                $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array($rows));
                $paginator->setItemCountPerPage($input->size);
                $paginator->setCurrentPageNumber($input->page);
                $view->paginator = $paginator;
                $view->funcion = "unidades";
                $view->busqueda = $input->buscar;
                $this->_helper->json(array("success" => true, "html" => $view->render("unidades.phtml"), "pagina" => $input->page, "funcion" => "unidades"));
            }
            $this->_helper->json(array("success" => false));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function monedasAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "page" => array("Digits"),
                "size" => array("Digits"),
                "buscar" => array("StringToUpper"),
            );
            $v = array(
                "page" => array(new Zend_Validate_Int(), "default" => 1),
                "size" => array(new Zend_Validate_Int(), "default" => 14),
                "buscar" => array(new Zend_Validate_Regex("/^[-_a-zA-Z0-9.]+$/")),
            );
            $input = new Zend_Filter_Input($f, $v, $r->getPost());
            $mapper = new Vucem_Model_VucemMonedasMapper();
            $rows = $mapper->obtenerMonedas($input->buscar);
            if (isset($rows) && !empty($rows)) {
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/catalogos/");
                $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");
                $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array($rows));
                $paginator->setItemCountPerPage($input->size);
                $paginator->setCurrentPageNumber($input->page);
                $view->paginator = $paginator;
                $view->funcion = "monedas";
                $view->busqueda = $input->buscar;
                $this->_helper->json(array("success" => true, "html" => $view->render("monedas.phtml"), "pagina" => $input->page, "funcion" => "monedas"));
            }
            $this->_helper->json(array("success" => false));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function rfcSociedadAction() {
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
                        $html->select("traffic-select-medium", "planta");
                        $html->addSelectOption("", "---");
                        if (count($arr)) {
                            foreach ($arr as $item) {
                                $html->addSelectOption($item["id"], $item["descripcion"]);
                            }
                        }
                    }
                    $mapper = new Trafico_Model_ClientesMapper();
                    $row = $mapper->datosCliente($input->idCliente);
                    $rfcSociedad = "";
                    if (isset($row) && !empty($row)) {
                        $rfcSociedad = $row["rfcSociedad"];
                    }
                    $this->_helper->json(array("success" => true, "rfcSociedad" => $rfcSociedad, "plantas" => isset($html) ? $html->getHtml() : null));
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

    public function subirArchivosClienteAction() {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idCliente" => array("Digits"),
                );
                $v = array(
                    "idCliente" => new Zend_Validate_Int(),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if (!$input->isValid()) {
                    throw new Exception("Invalid input!");
                }
            }
            $misc = new OAQ_Misc();
            $m = new Archivo_Model_DocumentosFiscalMapper();
            $upload = new Zend_File_Transfer_Adapter_Http();
            $upload->addValidator("Count", false, array("min" => 1, "max" => 15))
                    ->addValidator("Size", false, array("min" => "1", "max" => "20MB"))
                    ->addValidator("Extension", false, array("extension" => "pdf,xml,xls,xlsx,doc,docx,zip,bmp,tif,jpg,jpeg", "case" => false));
            $model = new Trafico_Model_ClientesMapper();
            $arr = $model->datosCliente($input->idCliente);
            $path = $this->_appconfig->getParam("expfiscal") . DIRECTORY_SEPARATOR . $arr["rfc"];
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            $mapper = new Archivo_Model_RepositorioFiscalMapper();
            $upload->setDestination($path);
            $files = $upload->getFileInfo();
            foreach ($files as $fieldname => $fileinfo) {
                if (($upload->isUploaded($fieldname)) && ($upload->isValid($fieldname))) {
                    $filename = $misc->formatFilename($fileinfo["name"], false);
                    $upload->receive($fieldname);
                    if (($misc->renombrarArchivo($path, $fileinfo["name"], $filename))) {
                        if (!($mapper->buscarArchivo($input->idCliente, $filename))) {
                            $mapper->nuevoArchivo($input->idCliente, $m->rgex($filename), $filename, $path, "", $this->_session->username);
                        }
                    }
                    $uploaded = true;
                } else {
                    
                }
            }
            if (isset($uploaded) && $uploaded == true) {
                $alerts = new Trafico_Model_ClientesAlertas();
                $alerts->agregar($input->idCliente, "SUBIÓ ARCHIVOS A EXPEDIENTE", $this->_session->username);
            }
            $this->_helper->json(array("success" => true));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function guardarSolicitudAnticipoAction() {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "aduana" => "Digits",
                    "cliente" => "Digits",
                    "idSolicitud" => "Digits",
                    "operacion" => "StringToUpper",
                    "pedimento" => "Digits",
                    "referencia" => "StringToUpper",
                );
                $v = array(
                    "aduana" => array("NotEmpty", new Zend_Validate_Int()),
                    "cliente" => array("NotEmpty", new Zend_Validate_Int()),
                    "idSolicitud" => array("NotEmpty", new Zend_Validate_Int()),
                    "operacion" => array("NotEmpty", new Zend_Validate_InArray(array("TOCE.EXP", "TOCE.IMP"))),
                    "pedimento" => array("NotEmpty", new Zend_Validate_Int()),
                    "referencia" => array("Notempty", new Zend_Validate_Regex("/^[-_a-zA-Z0-9.]+$/")),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("idSolicitud") && $input->isValid("cliente") && $input->isValid("pedimento") && $input->isValid("operacion") && $input->isValid("referencia")) {
                    $model = new Trafico_Model_TraficoSolicitudesMapper();
                    if (!$model->verificar($input->cliente, $input->aduana, $input->operacion, $input->pedimento, $input->referencia)) {
                        if ($model->exists($input->idSolicitud)) {
                            if ($model->guardar($input->idSolicitud, $input->cliente, $input->aduana, $input->operacion, $input->pedimento, $input->referencia)) {
                                $this->_helper->json(array("success" => true, "id" => $input->idSolicitud, "aduana" => $input->aduana));
                            }
                            $this->_helper->json(array("success" => false, "message" => "Unable to update!"));
                        }
                    } else {
                        $this->_helper->json(array("success" => false, "message" => "La operación ya exite"));
                    }
                }
                $this->_helper->json(array("success" => false, "message" => "Invalid input!"));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function cargarArchivosClienteAction() {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idCliente" => "Digits",
                );
                $v = array(
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("idCliente")) {
                    $view = new Zend_View();
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                    $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");
                    $mapper = new Archivo_Model_RepositorioFiscalMapper();
                    $arr = $mapper->archivosCliente($input->idCliente);
                    $view->arr = $arr;
                    $this->_helper->json(array("success" => true, "html" => $view->render("cargar-archivos-cliente.phtml")));
                }
                $this->_helper->json(array("success" => false, "message" => "Invalid input!"));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function editarArchivoFiscalAction() {
        try {
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => "Digits",
                    "tipoArchivo" => "Digits",
                    "accion" => "StringToLower",
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "tipoArchivo" => array("NotEmpty", new Zend_Validate_Int()),
                    "fecha" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
                    "accion" => array("NotEmpty", new Zend_Validate_InArray(array("edit", "cancel", "save"))),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                $repo = new Archivo_Model_RepositorioFiscalMapper();
                if ($input->isValid("id") && $input->isValid("accion")) {
                    $html = new V2_Html();
                    $mapper = new Archivo_Model_DocumentosFiscalMapper();
                    if ($input->accion == "edit") {
                        $html->select("traffic-select-medium", "selectFile_" . $input->id);
                        $arr = $mapper->obtenerTodos();
                        if (count($arr)) {
                            $idTipoArchivo = $repo->idTipoArchivo($input->id);
                            $html->addSelectOption("", "---");
                            foreach ($arr as $item) {
                                if ((int) $item["id"] == (int) $idTipoArchivo) {
                                    $html->addSelectOption($item["id"], $item["nombre"], true);
                                } else {
                                    $html->addSelectOption($item["id"], $item["nombre"]);
                                }
                            }
                        }
                        $dd = ($d = $repo->fechaVencimiento($input->id)) ? date("Y-m-d", strtotime($d)) : "";
                        $select = $html->getHtml();
                        $html->dateInput("date_" . $input->id, isset($dd) ? $dd : null);
                        $date = $html->getHtml();
                        $this->_helper->json(array("success" => true, "select" => $select, "date" => $date, "icons" => $html->trafficIconCancel($input->id, "cancelarEdicion") . $html->trafficIconSave($input->id, "guardarArchivo")));
                    } elseif ($input->accion == "cancel") {
                        $dd = ($d = $repo->fechaVencimiento($input->id)) ? date("Y-m-d", strtotime($d)) : "";
                        $this->_helper->json(array("success" => true, "select" => $repo->tipoArchivo($input->id), "date" => $dd, "icons" => $html->trafficIconEdit($input->id, "editarArchivo") . $html->trafficIconDelete($input->id, "borrarArchivo")));
                    } elseif ($input->accion == "save") {
                        if ($input->isValid("tipoArchivo")) {
                            if ($repo->actualizarTipoArchivo($input->id, $input->tipoArchivo, $input->fecha)) {
                                $dd = ($d = $repo->fechaVencimiento($input->id)) ? date("Y-m-d", strtotime($d)) : "";
                                $this->_helper->json(array("success" => true, "select" => $repo->tipoArchivo($input->id), "date" => $dd, "icons" => $html->trafficIconEdit($input->id, "editarArchivo") . $html->trafficIconDelete($input->id, "borrarArchivo")));
                            }
                        }
                    }
                }
                $this->_helper->json(array("success" => false));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function agregarComentarioTraficoAction() {
        try {
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idTrafico" => "Digits",
                    "comment" => "StringToUpper",
                );
                $v = array(
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                    "comment" => "NotEmpty",
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("idTrafico") && $i->isValid("comment")) {
                    $m = new Trafico_Model_ComentariosMapper();
                    if (($idc = $m->agregar($i->idTrafico, $this->_session->id, $i->comment))) {
                        $cs = new Trafico_Model_ClientesMapper();
                        $ts = new Trafico_Model_TraficosMapper();
                        $t = new Trafico_Model_Table_Traficos();
                        $t->setId($i->idTrafico);
                        $ts->find($t);
                        $c = $cs->datosCliente($t->getIdCliente());
                        $trafico = new OAQ_Trafico(array("idTrafico" => $i->idTrafico, "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));
                        $trafico->subirArchivoTemporal(null, $idc);
                        if (null !== ($t->getId())) {
                            if (APPLICATION_ENV == "production") {
                                $emails = new OAQ_EmailNotifications();
                                $mensaje = "El usuario " . $this->_session->nombre . " (" . $this->_session->email . ") ha agregado un comentario al trafico (" . $i->idTrafico . ") referencia " . $t->getReferencia() . " operación " . $t->getAduana() . "-" . $t->getPatente() . "-" . $t->getPedimento() . " del cliente " . $c["nombre"] . "<br><p><em>&ldquo;{$i->comment}&rdquo;</em></p>";
                                //$emails->nuevaNotificacion($t->getIdAduana(), $t->getPedimento(), $t->getReferencia(), $this->_session->id, $t->getIdUsuario(), $mensaje, "notificacion-comentario");
                                if (($id = $emails->nuevaNotificacion($t->getIdAduana(), $t->getPedimento(), $t->getReferencia(), $this->_session->id, $t->getIdUsuario(), $mensaje, "notificacion-comentario", $i->idTrafico))) {
                                    $this->_helper->json(array("success" => true, "id" => $id, "idc" => $idc));
                                }
                            }
                            $this->_helper->json(array("success" => true, "id" => $i->idTrafico));
                        }
                        $this->_helper->json(array("success" => false, "message" => "No record found on DB"));
                    }
                    $this->_helper->json(array("success" => false, "message" => "Can't add comment to DB"));
                }
                $this->_helper->json(array("success" => false, "message" => "Invalid input!"));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function agregarComentarioSolicitudAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "solicitud" => "Digits",
                    "comments" => "StringToUpper",
                    "pedimento" => "Digits",
                    "referencia" => "StringToUpper",
                );
                $v = array(
                    "solicitud" => array("NotEmpty", new Zend_Validate_Int()),
                    "comments" => "NotEmpty",
                    "pedimento" => array("NotEmpty", new Zend_Validate_Int()),
                    "referencia" => array("Notempty", new Zend_Validate_Regex("/^[-_a-zA-Z0-9.]+$/")),
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("solicitud") && $i->isValid("comments")) {
                    $tbl = new Trafico_Model_TraficoSolComentarioMapper();
                    $stmt = $tbl->agregarComentario(array(
                        "idSolicitud" => $i->solicitud,
                        "comentario" => $i->comments,
                        "creado" => date("Y-m-d H:i:s"),
                        "creadoPor" => $this->_session->username,
                    ));
                    if ($stmt) {
                        $emails = new OAQ_EmailNotifications();
                        $model = new Trafico_Model_TraficoSolicitudesMapper();
                        $p = $model->propietario($i->solicitud);
                        $mensaje = "El usuario " . $this->_session->nombre . " (" . $this->_session->email . ") ha agregado un comentario a la solicitud de anticipo (" . $i->solicitud . ") referencia " . $i->referencia . " pedimento " . $i->pedimento . "<br><p><em>&ldquo;{$i->comments}&rdquo;</em></p>";
                        $emails->nuevaNotificacion($p["idAduana"], $i->pedimento, $i->referencia, $this->_session->id, $p["idUsuario"], $mensaje, "notificacion-comentario-solicitud");
                    }
                    if ($stmt) {
                        $this->_helper->json(array("success" => true));
                    }
                }
            }
            $this->_helper->json(array("success" => false));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function enviarSolicitudAction() {
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
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("id")) {
                    $sa = new Trafico_Model_TraficoSolicitudesMapper();
                    $s = $sa->obtener($i->id);

                    $log = new OAQ_Referencias(array("patente" => $s["patente"], "aduana" => $s["aduana"], "pedimento" => $s["pedimento"], "referencia" => $s["referencia"], "usuario" => $this->_session->username));
                    $log->agregarBitacora("SE CREO SE SOLICITUD DE ANTICIPO");
                    $stmt = $sa->activarSolicitud($i->id);

                    if ($stmt === true) {

                        if (APPLICATION_ENV == 'production') {
                            $emails = new OAQ_EmailNotifications();
                            $p = $sa->propietario($i->id);
                            $mensaje = "El usuario " . $this->_session->nombre . " (" . $this->_session->email . ") ha generado una nueva solicitud de anticipo (" . $i->id . ") referencia " . $s["referencia"] . " pedimento " . $s["aduana"] . "-" . $s["patente"] . "-" . $s["pedimento"];
                            $emails->nuevaNotificacion($p["idAduana"], $s["pedimento"], $s["referencia"], $this->_session->id, $p["idUsuario"], $mensaje, "nueva-solicitud");
                        }

                        $referencias = new OAQ_Referencias();
                        $referencias->crearRepositorio($s["patente"], $s["aduana"], $s["referencia"], "AutoSolicitud", $s["rfcCliente"], $s["pedimento"]);
                        $referencias->crearTrafico($s["idAduana"], $s["idCliente"], $s["patente"], $s["aduana"], $s["pedimento"], $s["referencia"], $s["rfcCliente"], $s["tipoOperacion"], $s["fechaEta"], $s["bl"], $s["numFactura"], $s["cvePed"], $this->_session->id, isset($s["idPlanta"]) ? $s["idPlanta"] : null);

                        if (($idTrafico = $referencias->getIdTrafico())) {

                            $sa->establecerIdTrafico($i->id, $idTrafico);

                            $mppr = new Trafico_Model_TraficoGuiasMapper();
                            $guias = explode(',', $s["bl"]);
                            foreach ($guias as $guia) {
                                $mppr->agregarGuia($idTrafico, "H", preg_replace('/\s+/', '', $guia), $this->_session->id);
                            }

                            $mpprf = new Trafico_Model_TraficoFacturasMapper();
                            $det = new Trafico_Model_FactDetalle();

                            $facturas = explode(',', $s["numFactura"]);
                            foreach ($facturas as $numFactura) {
                                $idFactura = $mpprf->agregarFacturaSimple($idTrafico, $numFactura, $this->_session->id);
                                if ($idFactura == true) {
                                    $det->agregarFacturaSimple($idFactura, $numFactura);
                                }
                            }
                        }
                    }
                    $this->_helper->json(array("success" => false));
                }
            }
            $this->_helper->json(array("success" => false));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function cargarImagenesAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idTrafico" => array("Digits"),
                );
                $v = array(
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int())
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid()) {
                    $data = $input->getEscaped();
                    $tbl = new Trafico_Model_TraficosMapper();
                    $mdl = new Trafico_Model_Imagenes();
                    $misc = new OAQ_Misc();
                    $info = $tbl->obtenerPorId($data["idTrafico"]);
                    $dir = $misc->crearNuevoDirectorio($this->_appconfig->getParam("expdest"), $info["patente"] . "/" . $info["aduana"] . "/" . $info["referencia"]);
                    $upload = new Zend_File_Transfer_Adapter_Http();
                    $upload->addValidator("Count", false, array("min" => 1, "max" => 15))
                            ->addValidator("Size", false, array("min" => "1kB", "max" => "6MB"))
                            ->addValidator("Extension", false, array("extension" => "jpg,jpeg", "case" => false));
                    $upload->setDestination($dir);
                    $files = $upload->getFileInfo();
                    foreach ($files as $fieldname => $fileinfo) {
                        if (($upload->isUploaded($fieldname)) && ($upload->isValid($fieldname))) {
                            $ext = strtolower(pathinfo($fileinfo["name"], PATHINFO_EXTENSION));
                            $filename = sha1(time() . $fileinfo["name"]) . "." . $ext;
                            $upload->addFilter("Rename", $filename, $fieldname);
                            $upload->receive($fieldname);
                            $thumb = $dir . DIRECTORY_SEPARATOR . pathinfo($filename, PATHINFO_FILENAME) . "_thumb." . pathinfo($filename, PATHINFO_EXTENSION);
                            if (file_exists($dir . DIRECTORY_SEPARATOR . $filename)) {
                                if (extension_loaded("imagick")) {
                                    $im = new Imagick();
                                    $im->pingImage($dir . DIRECTORY_SEPARATOR . $filename);
                                    $im->readImage($dir . DIRECTORY_SEPARATOR . $filename);

                                    if ($im->getimagewidth() > 1024) {
                                        $im->resizeimage(1024, round($im->getimageheight() / ($im->getimagewidth() / 1024), 0), Imagick::FILTER_LANCZOS, 1);
                                        $im->writeImage($dir . DIRECTORY_SEPARATOR . $filename);
                                    }
                                    $im->thumbnailImage(150, round($im->getimageheight() / ($im->getimagewidth() / 150), 0));
                                    $im->writeImage($thumb);
                                    $im->destroy();
                                    if (isset($thumb) && file_exists($thumb)) {
                                        $mdl->agregar($data["idTrafico"], 1, $dir, pathinfo($filename, PATHINFO_BASENAME), pathinfo($thumb, PATHINFO_BASENAME), $fileinfo["name"]);
                                    }
                                }
                                if (!isset($thumb) || !file_exists($thumb)) {
                                    $mdl->agregar($data["idTrafico"], 1, $dir, pathinfo($filename, PATHINFO_BASENAME), null, $fileinfo["name"]);
                                }
                            }
                        }
                    }
                    $this->_helper->json(array("success" => true));
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                $this->_helper->json(array("success" => false, "message" => "No data recieved."));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function cargarTarifaFirmadaAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idCliente" => array("Digits"),
                    "idTarifa" => array("Digits"),
                );
                $v = array(
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                    "idTarifa" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("idCliente") && $input->isValid("idTarifa")) {
                    $misc = new OAQ_Misc();
                    $m = new Archivo_Model_DocumentosFiscalMapper();
                    $upload = new Zend_File_Transfer_Adapter_Http();
                    $upload->addValidator("Count", false, array("min" => 1, "max" => 15))
                            ->addValidator("Size", false, array("min" => "1", "max" => "20MB"))
                            ->addValidator("Extension", false, array("extension" => "pdf,xml,xls,xlsx,doc,docx,zip,bmp,tif,jpg,jpeg", "case" => false));
                    $model = new Trafico_Model_ClientesMapper();
                    $arr = $model->datosCliente($input->idCliente);
                    $path = $this->_appconfig->getParam("expfiscal") . DIRECTORY_SEPARATOR . $arr["rfc"];
                    if (!file_exists($path)) {
                        mkdir($path, 0777, true);
                    }
                    $mapper = new Archivo_Model_RepositorioFiscalMapper();
                    $upload->setDestination($path);
                    $files = $upload->getFileInfo();
                    foreach ($files as $fieldname => $fileinfo) {
                        if (($upload->isUploaded($fieldname)) && ($upload->isValid($fieldname))) {
                            $filename = $misc->formatFilename($fileinfo["name"], false);
                            $upload->receive($fieldname);
                            if (($misc->renombrarArchivo($path, $fileinfo["name"], $filename))) {
                                if (!($mapper->buscarArchivo($input->idCliente, $filename))) {
                                    $id = $mapper->nuevoArchivo($input->idCliente, 171, $filename, $path, "", $this->_session->username);
                                }
                                if (isset($id)) {
                                    $tarifas = new Trafico_Model_Tarifas();
                                    $tarifas->removerTarifasPrevias($input->idCliente);
                                    $tarifas->actualizarEstatus($input->idTarifa, 2);
                                    $tarifas->archivoTarifa($input->idTarifa, $id, $this->_session->username);
                                }
                            }
                            $uploaded = true;
                        } else {
                            
                        }
                    }
                    if (isset($uploaded) && $uploaded == true) {
                        $alerts = new Trafico_Model_ClientesAlertas();
                        $alerts->agregar($input->idCliente, "SUBIÓ TARIFA FIRMADA", $this->_session->username);
                        $this->_helper->json(array("success" => true));
                    }
                    $this->_helper->json(array("success" => false));
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                $this->_helper->json(array("success" => false, "message" => "No data recieved."));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function cargarFotosAction() {
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
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/extra/");
                    $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");

                    $mppr = new Trafico_Model_TraficosMapper();

                    $view->idTrafico = $input->id;
                    if ($input->isValid("borrar")) {
                        $view->borrar = 0;
                    }
                    if ($input->isValid("uri")) {
                        $view->uri = $input->uri;
                    }
                    $gallery = new Trafico_Model_Imagenes();
                    $view->gallery = $gallery->miniaturas($input->id);
                    $this->_helper->json(["success" => true, "html" => $view->render("photos.phtml")]);
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function borrarTraficoAction() {
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
                if ($input->isValid("id")) {
                    $trafico = new OAQ_Trafico(array("idTrafico" => $input->id, "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));
                    if ($trafico->borrar()) {
                        $this->_helper->json(array("success" => true));
                    }
                    /* $mapper = new Trafico_Model_TraficosMapper();
                      $table = new Trafico_Model_Table_Traficos(array("id" => $input->id));
                      $mapper->find($table);
                      if (null !== $table->getId()) {
                      if (($mapper->borrar($input->id)) == true) {
                      $this->_helper->json(array("success" => true));
                      } else {
                      $this->_helper->json(array("success" => false, "message" => "No se pudo borrar"));
                      }
                      } */
                    $this->_helper->json(array("success" => false));
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function borrarImagenAction() {
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
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($filters, $validators, $request->getPost());
                if ($input->isValid("id")) {
                    $mapper = new Trafico_Model_Imagenes();
                    if ($mapper->borrarImagen($input->id)) {
                        $this->_helper->json(["success" => true]);
                    }
                    $this->_helper->json(["success" => false]);
                }
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
                    $mapper = new Archivo_Model_RepositorioMapper();
                    $arr = $mapper->getFileInfo($i->id);
                    $mapper->removeFileById($i->id);
                    if (isset($arr) && file_exists($arr["ubicacion"])) {
                        unlink($arr["ubicacion"]);
                    } else {
                        $this->_helper->json(array("success" => false, "message" => "El archivo no existe."));
                    }
                    $this->_helper->json(array("success" => true));
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function borrarArchivoClienteAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $filters = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                    "idCliente" => array("Digits"),
                );
                $validators = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($filters, $validators, $request->getPost());
                if ($input->isValid("id") && $input->isValid("idCliente")) {
                    $tarifas = new Trafico_Model_Tarifas();
                    $mapper = new Archivo_Model_RepositorioFiscalMapper();
                    $arr = $mapper->buscar($input->id);
                    if ((int) $arr["tipoArchivo"] == 171) {
                        $tarifas->removerArchivoTarifa($input->idCliente, $input->id, $this->_session->username);
                    }
                    if (isset($arr) && !empty($arr)) {
                        if (($deleted = $mapper->borrarArchivo($input->id)) == true) {
                            if ($deleted === true) {
                                if (file_exists($arr["ubicacion"] . DIRECTORY_SEPARATOR . $arr["nombreArchivo"])) {
                                    unlink($arr["ubicacion"] . DIRECTORY_SEPARATOR . $arr["nombreArchivo"]);
                                }
                            }
                            $this->_helper->json(array("success" => true));
                        }
                    }
                    $this->_helper->json(array("success" => false));
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function cambiarEsquemaClienteAction() {
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
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                    "value" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($filters, $validators, $request->getPost());
                if ($input->isValid("idCliente") && $input->isValid("value")) {
                    $customers = new Trafico_Model_ClientesMapper();
                    if (($customers->esquemaDefault($input->idCliente, $input->value)) == true) {
                        $this->_helper->json(["success" => true]);
                    }
                    $this->_helper->json(["success" => false]);
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

    public function cambiarTipoClienteAction() {
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
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                    "value" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($filters, $validators, $request->getPost());
                if ($input->isValid("idCliente") && $input->isValid("value")) {
                    $customers = new Trafico_Model_ClientesMapper();
                    if (($customers->tipoCliente($input->idCliente, $input->value)) == true) {
                        $this->_helper->json(["success" => true]);
                    }
                    $this->_helper->json(["success" => false]);
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

//    public function cambiarPecaClienteAction() {
//        try {
//            if (!$this->getRequest()->isXmlHttpRequest()) {
//                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
//            }
//            $request = $this->getRequest();
//            if ($request->isPost()) {
//                $filters = array(
//                    "*" => array("StringTrim", "StripTags"),
//                );
//                $validators = array(
//                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
//                    "value" => array("NotEmpty", new Zend_Validate_Int()),
//                );
//                $input = new Zend_Filter_Input($filters, $validators, $request->getPost());
//                if ($input->isValid("idCliente") && $input->isValid("value")) {
//                    $customers = new Trafico_Model_ClientesMapper();
//                    if (($customers->pecaDefault($input->idCliente, $input->value)) == true) {
//                        $this->_helper->json(["success" => true]);
//                    }
//                    $this->_helper->json(["success" => false]);
//                } else {
//                    throw new Exception("Invalid input!");
//                }
//            } else {
//                throw new Exception("Invalid request type!");
//            }
//        } catch (Exception $ex) {
//            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
//        }
//    }

    public function atributoClienteAction() {
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
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                    "atributo" => array("NotEmpty"),
                    "value" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($filters, $validators, $request->getPost());
                if ($input->isValid("idCliente") && $input->isValid("atributo") && $input->isValid("value")) {
                    $mppr = new Trafico_Model_ClientesMapper();
                    $arr = array(
                        $input->atributo => $input->value,
                        "actualizado" => date("Y-m-d H:i:s")
                    );
                    if (($mppr->actualizar($input->idCliente, $arr))) {
                        $this->_helper->json(["success" => true]);
                    }
                    $this->_helper->json(["success" => false]);
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(["success" => false, "message" => $ex->getMessage()]);
        }
    }

    public function guardarChecklistClienteAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idCliente" => "Digits",
                    "completo" => "Digits",
                );
                $v = array(
                    "*" => "NotEmpty",
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                    "completo" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("idCliente")) {
                    $data = $request->getPost();
                    unset($data["idCliente"]);
                    unset($data["observaciones"]);
                    if (isset($data["completo"])) {
                        unset($data["completo"]);
                    }
                    $mapper = new Archivo_Model_ChecklistClientes();
                    $arr = array(
                        "observaciones" => $input->observaciones,
                        "completo" => $input->completo,
                        "fechaCompleto" => ($input->completo == 1) ? date("Y-m-d H:i:s") : null,
                        "checklist" => json_encode($data),
                    );
                    if ($mapper->buscar($input->idCliente)) {
                        $arr["actualizado"] = date("Y-m-d H:i:s");
                        $arr["actualizadoPor"] = strtolower($this->_session->username);
                        if ($mapper->actualizar($input->idCliente, $arr)) {
                            $this->_helper->json(["success" => true]);
                        }
                    } else {
                        $arr["idCliente"] = $input->idCliente;
                        $arr["creado"] = date("Y-m-d H:i:s");
                        $arr["creadoPor"] = strtolower($this->_session->username);
                        if ($mapper->agregar($arr)) {
                            $this->_helper->json(["success" => true]);
                        }
                    }
                    $this->_helper->json(["success" => false]);
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

    public function tarifaObtenerAduanasAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $filters = array(
                    "*" => array("StringTrim", "StripTags"),
                    "value" => "Digits",
                );
                $validators = array(
                    "value" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($filters, $validators, $r->getPost());
                if ($input->isValid("value")) {
                    $mapper = new Trafico_Model_TraficoAduanasMapper();
                    $arr = $mapper->obtener($input->value);
                    $html = new V2_Html();
                    if (count($arr)) {
                        $html->select("traffic-select-large", "aduana");
                        $html->addSelectOption("", "---");
                        foreach ($arr as $item) {
                            $html->addSelectOption($item["id"], $item["nombre"]);
                        }
                        $this->_helper->json(["success" => true, "html" => $html->getHtml()]);
                    }
                    $this->_helper->json(["success" => false]);
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function actualizarClienteAccesoAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idCliente" => "Digits",
                    "sicaId" => "Digits",
                    "webaccess" => "Digits",
                    "rfc" => "StringToUpper",
                    "nombre" => "StringToUpper",
                );
                $v = array(
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                    "sicaId" => array("NotEmpty", new Zend_Validate_Int()),
                    "password" => array("NotEmpty"),
                    "webaccess" => array("NotEmpty"),
                    "rfc" => array(new Zend_Validate_Regex("/^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/")),
                    "nombre" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("idCliente")) {
                    $sys = new Trafico_Model_ClientesDbs();
                    if ($input->isValid("sicaId")) {
                        if (!($ids = $sys->verificarSistema($input->idCliente, "sica"))) {
                            $sys->agregar($input->idCliente, $input->sicaId, "sica", $this->_session->username);
                        } else {
                            $sys->actualizar($ids, $input->sicaId, $this->_session->username);
                        }
                    }
                    if ($input->isValid("password") && (int) $input->webaccess == 1) {
                        if (!($id = $sys->verificarSistema($input->idCliente, "portal"))) {
                            $sys->agregarPassword($input->idCliente, $input->password, "portal", $this->_session->username);
                        } else {
                            $sys->actualizarPassword($id, $input->password, $this->_session->username);
                        }
                    }
                    if ($input->isValid("dashboard")) {
                        if (!($ids = $sys->verificarSistema($input->idCliente, "dashboard"))) {
                            $sys->agregar($input->idCliente, $input->dashboard, "dashboard", $this->_session->username);
                        } else {
                            $sys->actualizar($ids, $input->dashboard, $this->_session->username);
                        }
                    }
                    if ($input->isValid("password") && (int) $input->webaccess == 0) {
                        if (($id = $sys->verificarSistema($input->idCliente, "portal"))) {
                            $sys->noAcceso($id, $this->_session->username);
                        }
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

    public function aduanasClienteAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idCliente" => "Digits",
                    "idAduana" => "Digits",
                    "action" => "StringToLower",
                );
                $v = array(
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                    "idAduana" => array("NotEmpty", new Zend_Validate_Int()),
                    "action" => array("NotEmpty", new Zend_Validate_InArray(array("delete", "add"))),
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("idCliente") && $i->isValid("idAduana") && $i->isValid("action")) {
                    $mapper = new Trafico_Model_TraficoCliAduanasMapper();
                    if ($i->action == "add") {
                        $arr = array(
                            "idAduana" => $i->idAduana,
                            "idCliente" => $i->idCliente,
                            "creado" => date("Y-m-d H:i:s"),
                            "activo" => 1,
                            "usuario" => $this->_session->username,
                        );
                        if ($mapper->agregar($arr)) {
                            $this->_helper->json(["success" => true]);
                        }
                    } else if ($i->action == "delete") {
                        if ($mapper->remover($i->idAduana, $i->idCliente)) {
                            $this->_helper->json(["success" => true]);
                        }
                    }
                    $this->_helper->json(["success" => false]);
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function guardarTarifaAction() {
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
                    "tarifa" => "NotEmpty",
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("tarifa")) {
                    $alerts = new Trafico_Model_ClientesAlertas();
                    $json = json_decode(html_entity_decode($i->tarifa), true);
                    $mapper = new Trafico_Model_Tarifas();
                    if (isset($json["idCliente"]) && $json["idCliente"] != "") {
                        if (($id = $mapper->verificar($json["idCliente"]))) {
                            $arr = array(
                                "idCliente" => $json["idCliente"],
                                "tipoVigencia" => $json["tipoVigencia"],
                                "tarifa" => html_entity_decode($i->tarifa),
                                "modificado" => date("Y-m-d H:i:s"),
                                "modificadoPor" => strtolower($this->_session->username),
                            );
                            $alerts->agregar($json["idCliente"], "ACTUALIZÓ TARIFA", $this->_session->username);
                            $mapper->actualizar($id, $arr);
                            $this->_helper->json(array("success" => true, "id" => $id));
                        } else {
                            $arr = array(
                                "idCliente" => $json["idCliente"],
                                "tipoVigencia" => $json["tipoVigencia"],
                                "tarifa" => html_entity_decode($i->tarifa),
                                "creado" => date("Y-m-d H:i:s"),
                                "creadoPor" => strtolower($this->_session->username),
                            );
                            $alerts->agregar($json["idCliente"], "GENERÓ TARIFA", $this->_session->username);
                            $id = $mapper->guardar($arr);
                            $this->_helper->json(array("success" => true, "id" => $id));
                        }
                        $this->_helper->json(["success" => false]);
                    }
                    $this->_helper->json(["success" => false]);
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

    public function guardarFacturaAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idTrafico" => "Digits",
                );
                $v = array(
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                    "factura" => "NotEmpty",
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("idTrafico")) {
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

    public function nuevoMensajeAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idTrafico" => "Digits",
                    "idUsuarioDe" => "Digits",
                    "idUsuarioPara" => "Digits",
                    "estatus" => "Digits",
                );
                $v = array(
                    "mensaje" => "NotEmpty",
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                    "idUsuarioDe" => array("NotEmpty", new Zend_Validate_Int()),
                    "idUsuarioPara" => array("NotEmpty", new Zend_Validate_Int()),
                    "estatus" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("idTrafico") && $i->isValid("idUsuarioDe") && $i->isValid("idUsuarioPara")) {
                    $mapper = new Application_Model_Mensajes();
                    $mensajes = new Application_Model_MensajesFijos();
                    $arr = array(
                        "idTrafico" => $i->idTrafico,
                        "idUsuarioDe" => $i->idUsuarioDe,
                        "idUsuarioPara" => $i->idUsuarioPara,
                        "leido" => 0,
                        "creado" => date("Y-m-d H:i:s"),
                    );
                    $ts = new Trafico_Model_TraficosMapper();
                    $t = new Trafico_Model_Table_Traficos();
                    $t->setId($i->idTrafico);
                    $ts->find($t);
                    if (null !== ($t->getId())) {
                        $emails = new OAQ_EmailNotifications();
                    }
                    $trafico = new OAQ_Trafico(array("idTrafico" => $i->idTrafico, "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));
                    if ($i->isValid("estatus")) {
                        $arr["mensaje"] = $mensajes->obtener($i->estatus);
                        if ($i->estatus == 11) { // liberado                    
                            $trafico->actualizarFecha(8, date("Y-m-d"));
                            $trafico->actualizarFechaLiberacion(date("Y-m-d"));
                        } else if ($i->estatus == 1) { // recepcion de documentos
                            $trafico->actualizarFecha(11, date("Y-m-d"));
                        } else if ($i->estatus == 2) { // guía revalidada
                            $trafico->actualizarFecha(20, date("Y-m-d"));
                        } else if ($i->estatus == 14) { // notificacion
                            $trafico->actualizarFecha(9, date("Y-m-d"));
                        } else if ($i->estatus == 23) { // en captura
                            $ts->actualizarEstatus($i->idTrafico, 7);
                        } else if ($i->estatus == 13) { // pagado                            
                            $ts->actualizarEstatus($i->idTrafico, 2);
                            $trafico->actualizarFecha(2, date("Y-m-d"));
                            $trafico->actualizarFechaPago(date("Y-m-d"));
                            if ((int) $t->getPatente() == 3589) {
                                $vucemPed = new Vucem_Model_VucemPedimentosMapper();
                                $vucemPed->agregarVacio($i->idTrafico, $t->getPatente(), $t->getAduana(), $t->getPedimento());
                            }
                        } else if ($i->estatus == 12) { // enviar proforma
                            $ts->actualizarEstatus($i->idTrafico, 5);
                            if (APPLICATION_ENV == "production") {
                                $mensaje = "El usuario " . $this->_session->nombre . " (" . $this->_session->email . ") ha agregado un comentario al trafico (" . $i->idTrafico . ") referencia " . $t->getReferencia() . " operación " . $t->getAduana() . "-" . $t->getPatente() . "-" . $t->getPedimento() . "<br><p><em>&ldquo;{$i->mensaje}&rdquo;</em></p>";
                                $emails->nuevaNotificacion($t->getIdAduana(), $t->getPedimento(), $t->getReferencia(), $this->_session->id, $t->getIdUsuario(), $mensaje, "notificacion-comentario");
                            }
                        } else if ($i->estatus == 15) { // en espera
                            $ts->actualizarEstatus($i->idTrafico, 6);
                        }
                    } else {
                        $arr["mensaje"] = $i->mensaje;
                    }
                    if (($id = $mapper->agregar($arr))) {
                        if (isset($id)) {
                            $trafico->subirArchivoTemporal($id);
                        }
                        $row = $mapper->obtenerMensaje($id);
                        $msg = $row["mensaje"];
                        if (isset($row["nombreArchivo"])) {
                            $msg .= "<br><img src=\"/images/icons/attachment.gif\"><span style=\"font-size: 11px\"><a href=\"/archivo/data/descargar-archivo-temporal?id={$row["idArchivo"]}\">{$row["nombreArchivo"]}</a></span>";
                        }
                        $this->_helper->json(array("success" => true, "de" => $row["usuarioDe"], "mensaje" => $msg, "fecha" => date("Y-m-d H:i a", strtotime($row["creado"]))));
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
    
    public function cambiarEstatusAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idTrafico" => "Digits",
                    "estatus" => "Digits"
                );
                $v = array(
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                    "estatus" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("idTrafico") && $i->isValid("estatus")) {
                    $trafico = new OAQ_Trafico(array("idTrafico" => $i->idTrafico, "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));
                    if (($trafico->cambiarEstatus($i->estatus))) {
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

    public function leerMensajeAction() {
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
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("id")) {
                    $mapper = new Application_Model_Mensajes();
                    $arr = $mapper->obtenerMensaje($i->id);
                    if ($arr["idUsuarioPara"] === $this->_session->id) {
                        $mapper->leido($i->id);
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

    public function cambiarPropietarioAction() {
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
                    "ids" => array("NotEmpty"),
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("ids")) {
                    foreach ($i->ids as $id) {
                        
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

    public function seleccionarFacturasAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idTrafico" => "Digits",
                );
                $v = array(
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                    "facturas" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("idTrafico") && $input->isValid("facturas")) {
                    $misc = new OAQ_Misc();
                    $mapper = new Trafico_Model_TraficosMapper();
                    $model = new Trafico_Model_TraficoFacturasMapper();
                    $arr = $mapper->obtenerPorId($input->idTrafico);
                    if (isset($arr["patente"]) && isset($arr["aduana"])) {
                        $db = $misc->sitawinTrafico($arr["patente"], $arr["aduana"]);
                    }
                    foreach ($input->facturas as $numFactura) {
                        if (!($model->verificar($input->idTrafico, $numFactura))) {
                            if (isset($db)) {
                                $factura = $db->infoBasicaFactura($arr["referencia"], $numFactura);
                                if (count($factura) > 0) {
                                    $model->agregarFactura($input->idTrafico, $factura, $this->_session->id);
                                }
                            }
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

    /* public function cargarPlantillaAction() {
      try {
      if (!$this->getRequest()->isXmlHttpRequest()) {
      throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
      }
      $r = $this->getRequest();
      if ($r->isPost()) {
      $f = array(
      "*" => array("StringTrim", "StripTags"),
      "idTrafico" => "Digits",
      );
      $v = array(
      "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
      );
      $input = new Zend_Filter_Input($f, $v, $r->getPost());
      if ($input->isValid("idTrafico")) {
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
      } */

    public function vucemEnviarFacturasAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idTrafico" => "Digits",
                );
                $v = array(
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                    "facturas" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("idTrafico") && $input->isValid("facturas")) {
                    $invoices = new Trafico_Model_TraficoFacturasMapper();
                    if (is_array($input->facturas)) {
                        foreach ($input->facturas as $idFactura) {
                            $invoice = $invoices->detalleFactura($idFactura);
                            if ($invoice) {
                                $factura = new Trafico_Model_TraficoVucem();
                                if (!($id = $factura->verificar($input->idTrafico, $idFactura, $invoice["numeroFactura"]))) {
                                    $arr = array(
                                        "idTrafico" => $input->idTrafico,
                                        "idFactura" => $idFactura,
                                        "numFactura" => $invoice["numeroFactura"],
                                        "instruccion" => "Generar COVE.",
                                        "creado" => date("Y-m-d H:is")
                                    );
                                    if(!($factura->agregar($arr))) {
                                        $this->_helper->json(array("success" => false, "message" => "Unable to add invoice"));
                                    }
                                }                           
                            } else {
                            }
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

    public function asignarmeOperacionAction() {
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
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("idTrafico")) {
                    $trafico = new OAQ_Trafico(array("idTrafico" => $i->idTrafico, "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));
                    if ($trafico->asignarmeOperacion()) {
                        $this->_helper->json(array("success" => true));
                    }
                    $this->_helper->json(array("success" => false));
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function agregarAgenteAduanalAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => "Digits",
                    "patente" => "Digits",
                    "nombre" => "StringToUpper",
                    "rfc" => "StringToUpper",
                );
                $v = array(
                    "*" => "NotEmpty",
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "patente" => array("Alnum", array("stringLength", array("min" => 4, "max" => 4))),
                    "nombre" => "NotEmpty",
                    "rfc" => array(new Zend_Validate_Regex("/^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/")),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("patente") && $input->isValid("nombre") && $input->isValid("rfc")) {
                    $mapper = new Trafico_Model_Agentes();
                    if (!$input->isValid("id")) {
                        if (!($mapper->verificar($input->rfc))) {
                            if ($mapper->agregar($input->rfc, $input->patente, $input->nombre)) {
                                $this->_helper->json(array("success" => true));
                            } else {
                                $this->_helper->json(array("success" => false));
                            }
                        }
                        $this->_helper->json(array("success" => false));
                    } else {
                        $mapper->actualizar($input->id, $input->rfc, $input->patente, $input->nombre);
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

    public function editarAgenteAduanalAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idAgente" => "Digits",
                    "patente" => "Digits",
                    "nombre" => "StringToUpper",
                    "rfc" => "StringToUpper",
                );
                $v = array(
                    "*" => "NotEmpty",
                    "idAgente" => array("NotEmpty", new Zend_Validate_Int()),
                    "patente" => array("Alnum", array("stringLength", array("min" => 4, "max" => 4))),
                    "nombre" => "NotEmpty",
                    "rfc" => array(new Zend_Validate_Regex("/^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/")),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idAgente") && $input->isValid("patente") && $input->isValid("nombre") && $input->isValid("rfc")) {
                    $mapper = new Trafico_Model_Agentes();

                    $mapper->actualizar($input->idAgente, $input->rfc, $input->patente, $input->nombre);
                    $this->_helper->json(array("success" => true));

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

    public function nuevaOficinaAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "patente" => array("Digits"),
                    "aduana" => array("Digits"),
                    "corresponsal" => array("Digits"),
                    "tipoAduana" => array("Digits"),
                    "nombre" => array("StringToUpper"),
                );
                $v = array(
                    "patente" => array("NotEmpty", new Zend_Validate_Int()),
                    "aduana" => array("NotEmpty", new Zend_Validate_Int()),
                    "corresponsal" => new Zend_Validate_InArray(array(0, 1)),
                    "tipoAduana" => array("NotEmpty", new Zend_Validate_Int()),
                    "nombre" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("patente")) {
                    $mapper = new Trafico_Model_TraficoAduanasMapper();
                    $tbl = new Trafico_Model_Table_Aduanas($input->getEscaped());
                    $tbl->setActivo(1);
                    $mapper->find($tbl);
                    if (null === ($tbl->getId())) {
                        $catadu = new Trafico_Model_CatAduanas();
                        $mapper->save($tbl);
                        $customs = new Application_Model_Aduanas();
                        if (!($customs->verificar($input->patente, $input->aduana))) {
                            $customs->agregar($input->patente, $input->aduana, $input->nombre);
                        }
                        $adus = new Application_Model_Aduanas();
                        if (!($adus->verificar($input->patente, $input->aduana))) {
                            $adus->agregar($input->patente, $input->aduana, $input->nombre);
                        }
                        $this->_helper->json(array("success" => true));
                    } else {
                        $this->_helper->json(array("success" => false, "message" => "La aduana ya ha sido <br>dada de alta."));
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

    public function pedimentoEstatusAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idTrafico" => array("Digits"),
                    "optradio" => array("Digits"),
                    "observaciones" => array("StringToUpper"),
                );
                $v = array(
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                    "optradio" => array("NotEmpty", new Zend_Validate_Int()),
                    "observaciones" => "NotEmpty",
                );
                $i = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($i->isValid("idTrafico")) {
                    $trafico = new OAQ_Trafico(array("idTrafico" => $i->idTrafico, "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));
                    $trafico->actualizarSemaforo($i->optradio, $i->isValid("observaciones") ? $i->observaciones : null);
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

    public function crearOrdenRemisionAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idTrafico" => array("Digits"),
                    "caja" => array("StringToUpper"),
                    "transfer" => array("StringToUpper"),
                    "instrucciones" => array("StringToUpper"),
                    "lineaTransportista" => array("StringToUpper"),
                    "elaboro" => array("StringToUpper"),
                );
                $v = array(
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                    "caja" => "NotEmpty",
                    "transfer" => "NotEmpty",
                    "instrucciones" => "NotEmpty",
                    "lineaTransportista" => "NotEmpty",
                    "elaboro" => "NotEmpty",
                    "pedimentoSimplificado" => "NotEmpty",
                    "relacionDocumentos" => "NotEmpty",
                    "manifiesto" => "NotEmpty",
                    "inBond" => "NotEmpty",
                    "bl" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idTrafico")) {
                    $mapper = new Trafico_Model_OrdenRemision();
                    $arr = array(
                        "idTrafico" => $input->idTrafico,
                        "caja" => $input->caja,
                        "transfer" => $input->transfer,
                        "lineaTransportista" => $input->lineaTransportista,
                        "elaboro" => $input->elaboro,
                        "instrucciones" => $input->instrucciones,
                        "pedimentoSimplificado" => ($input->isValid("pedimentoSimplificado")) ? 1 : 0,
                        "relacionDocumentos" => ($input->isValid("relacionDocumentos")) ? 1 : 0,
                        "manifiesto" => ($input->isValid("manifiesto")) ? 1 : 0,
                        "inBond" => ($input->isValid("inBond")) ? 1 : 0,
                        "bl" => ($input->isValid("bl")) ? 1 : 0,
                    );
                    if (!($id = $mapper->verificar($input->idTrafico))) {
                        $arr["creado"] = date("Y-m-d H:i:s");
                        $arr["creadoPor"] = $this->_session->username;
                        $id = $mapper->agregar($arr);
                    } else {
                        $mapper->actualizar($id, $arr);
                    }
                    $this->_helper->json(array("success" => true, "id" => $id));
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

    public function plantaGuardarAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idCliente" => array("Digits"),
                    "idPlanta" => array("Digits"),
                    "clave" => array("StringToUpper"),
                    "ubicacion" => array("StringToUpper"),
                    "descripcion" => array("StringToUpper"),
                );
                $v = array(
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                    "idPlanta" => array("NotEmpty", new Zend_Validate_Int()),
                    "clave" => "NotEmpty",
                    "ubicacion" => "NotEmpty",
                    "descripcion" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idCliente") && $input->isValid("clave")) {
                    $mppr = new Trafico_Model_ClientesPlantas();
                    if (!$input->isValid("idPlanta")) {
                        if (!($id = $mppr->verificar($input->idCliente, $input->clave))) {
                            $arr = array(
                                "idCliente" => $input->idCliente,
                                "clave" => $input->clave,
                                "ubicacion" => $input->ubicacion,
                                "descripcion" => $input->descripcion,
                                "creado" => date("Y-m-d H:i:s"),
                                "creadoPor" => $this->_session->username,
                            );
                            if ($mppr->agregar($arr) == true) {
                                $this->_helper->json(array("success" => true));
                            } else {
                                throw new Exception("No se puedo agregar.");
                            }
                        } else {
                            throw new Exception("Ya existe en la base de datos.");
                        }
                    } else {
                        $arr = array(
                            "clave" => $input->clave,
                            "ubicacion" => $input->ubicacion,
                            "descripcion" => $input->descripcion,
                            "actualizado" => date("Y-m-d H:i:s"),
                            "actualizadoPor" => $this->_session->username,
                        );
                        if ($mppr->actualizar($input->idCliente, $input->idPlanta, $arr) == true) {
                            $this->_helper->json(array("success" => true));
                        } else {
                            throw new Exception("No se pudo actualizar en la base de datos.");
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

    public function plantaBorrarAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idCliente" => array("Digits"),
                    "idPlanta" => array("Digits"),
                );
                $v = array(
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                    "idPlanta" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idCliente") && $input->isValid("idPlanta")) {
                    $mppr = new Trafico_Model_ClientesPlantas();
                    if ($mppr->borrar($input->idCliente, $input->idPlanta) == true) {
                        $this->_helper->json(array("success" => true));
                    }
                    throw new Exception("No se puede borrar de la base de datos.");
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

    public function atualizarFechaTraficoAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "idTrafico" => array("StringTrim", "StripTags", "Digits"),
                    "tipoFecha" => array("StringTrim", "StripTags", "Digits"),
                );
                $v = array(
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                    "tipoFecha" => array("NotEmpty", new Zend_Validate_Int()),
                    "fecha" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idTrafico") && $input->isValid("tipoFecha")) {
                    $trafico = new OAQ_Trafico(array("idTrafico" => $input->idTrafico, "idUsuario" => $this->_session->id));
                    $fecha = $input->fecha;
                    if (strtolower($input->fecha) == "n/d" || strtolower($input->fecha) == "n/a") {
                        $fecha = '1900-01-01 00:00:00';
                    }
                    $trafico->actualizarFecha($input->tipoFecha, $fecha);
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

    public function traficoTmpGuardarAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {

                $f = array(
                    "*" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags()),
                    "id" => new Zend_Filter_Digits(),
                    "editPatente" => new Zend_Filter_Digits(),
                    "editAduana" => new Zend_Filter_Digits(),
                    "editCliente" => new Zend_Filter_Digits(),
                    "editReferencia" => new Zend_Filter_StringToUpper(),
                    "editClave" => new Zend_Filter_StringToUpper(),
                    "tipoOperacion" => new Zend_Filter_StringToUpper(),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "editPatente" => array("NotEmpty", new Zend_Validate_Int()),
                    "editAduana" => array("NotEmpty", new Zend_Validate_Int()),
                    "editPedimento" => array("NotEmpty"),
                    "editCliente" => array("NotEmpty", new Zend_Validate_Int()),
                    "editReferencia" => array(new Zend_Validate_Regex("/^[-_a-zA-Z0-9.\/]+$/"), "presence" => "required"),
                    "editClave" => array(array("stringLength", array("min" => 2, "max" => 3))),
                    "editFechaEta" => array("NotEmpty", new Zend_Validate_Regex("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/")), // 10
                    "tipoOperacion" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id")) {
                    $mppr = new Trafico_Model_TraficosTmp();
                    $arr = array(
                        "idCliente" => $input->editCliente,
                        "patente" => $input->editPatente,
                        "aduana" => $input->editAduana,
                        "pedimento" => str_pad($input->editPedimento, 7, '0', STR_PAD_LEFT),
                        "referencia" => $input->editReferencia,
                        "cvePedimento" => $input->editClave,
                        "fechaEta" => $input->editFechaEta,
                        "tipoOperacion" => $input->tipoOperacion,
                    );
                    if ($mppr->actualizar($input->id, $arr)) {
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

    public function traficoTmpCrearAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "ids" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags()),
                );
                $v = array(
                    "ids" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("ids")) {
                    $mppr = new Trafico_Model_TraficosTmp();
                    $mpprt = new Trafico_Model_TraficosMapper();
                    $clientes = new Trafico_Model_ClientesMapper();
                    if (!empty($input->ids) && is_array($input->ids)) {
                        foreach ($input->ids as $id) {
                            $tmp = $mppr->seleccionar($id);
                            if (!($mpprt->verificar($tmp["patente"], $tmp["aduana"], $tmp["pedimento"]))) {
                                $cliente = $clientes->datosCliente($tmp["idCliente"]);
                                $arr = array(
                                    "idAduana" => $tmp["idAduana"],
                                    "idUsuario" => $this->_session->id,
                                    "idCliente" => $tmp["idCliente"],
                                    "patente" => $tmp["patente"],
                                    "aduana" => $tmp["aduana"],
                                    "pedimento" => str_pad($tmp["pedimento"], 7, '0', STR_PAD_LEFT),
                                    "referencia" => $tmp["referencia"],
                                    "ie" => $tmp["tipoOperacion"],
                                    "rfcCliente" => $cliente["rfc"],
                                    "estatus" => 1,
                                    "cvePedimento" => $tmp["cvePedimento"],
                                    "fechaEta" => date("Y-m-d H:i:s", strtotime($tmp["fechaEta"])),
                                    "creado" => date("Y-m-d H:i:s"),
                                );
                                if ($mpprt->insertar($arr)) {
                                    $mppr->borrar($id);
                                }
                            }
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

    public function traficoTmpBorrarAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "id" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags(), new Zend_Filter_Digits()),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id")) {
                    $mppr = new Trafico_Model_TraficosTmp();
                    if (($mppr->borrar($input->id))) {
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

    public function traficoTmpAgregarAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags()),
                    "aduana" => new Zend_Filter_Digits(),
//                    "pedimento" => new Zend_Filter_Digits(),
                    "referencia" => new Zend_Filter_StringToUpper(),
                    "pedimentoRectificar" => new Zend_Filter_Digits(),
                    "rectificacion" => new Zend_Filter_Digits(),
                    "consolidado" => new Zend_Filter_Digits(),
                    "operacion" => new Zend_Filter_StringToUpper(),
                    "cvePedimento" => new Zend_Filter_StringToUpper(),
                    "cliente" => new Zend_Filter_Digits(),
                    "cantidad" => new Zend_Filter_Digits(),
                );
                $v = array(
                    "aduana" => array("NotEmpty", new Zend_Validate_Int()),
                    "cliente" => array("NotEmpty", new Zend_Validate_Int()),
                    "pedimento" => array("NotEmpty"),
                    "pedimentoRectificar" => array("NotEmpty"),
                    "rectificacion" => array("NotEmpty", new Zend_Validate_Int()),
                    "consolidado" => array("NotEmpty", new Zend_Validate_Int()),
                    "cantidad" => array("NotEmpty", new Zend_Validate_Int()),
                    "tipoCambio" => new Zend_Validate_Float(),
                    "operacion" => array(array("stringLength", array("min" => 7, "max" => 8))),
                    "cvePedimento" => array(array("stringLength", array("min" => 2, "max" => 3))),
                    "referencia" => array(new Zend_Validate_Regex("/^[-_a-zA-Z0-9.\/]+$/"), "presence" => "required"),
                    "fechaEta" => array("NotEmpty", new Zend_Validate_Regex("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/")), // 10
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("aduana") && $input->isValid("cliente") && $input->isValid("pedimento") && $input->isValid("operacion") && $input->isValid("referencia") && $input->isValid("cantidad")) {
                    $mppr = new Trafico_Model_TraficosTmp();
                    $adus = new Trafico_Model_TraficoAduanasMapper();
                    $adu = $adus->obtenerAduana($input->aduana);
                    if ($input->cantidad == 1) {
                        if (!($mppr->verificar($input->aduana, $input->pedimento))) {
                            $arr = array(
                                "idAduana" => $input->aduana,
                                "patente" => $adu["patente"],
                                "aduana" => $adu["aduana"],
                                "pedimento" => $input->pedimento,
                                "referencia" => $input->referencia,
                                "idCliente" => $input->cliente,
                                "tipoOperacion" => $input->operacion,
                                "cvePedimento" => $input->cvePedimento,
                                "fechaEta" => date("Y-m-d H:i:s", strtotime($input->fechaEta)),
                                "usuario" => $this->_session->username,
                                "creado" => date("Y-m-d H:i:s"),
                            );
                            $mppr->agregar($arr);
                        }
                    } else {
                        if ($input->cantidad > 1) {
                            foreach (range(1, $input->cantidad) as $value) {
                                $pedimento = str_pad(($input->pedimento + (int) $value), 7, '0', STR_PAD_LEFT);
//                                if (preg_match('/^Q17/', $input->referencia) || preg_match('/^Q18/', $input->referencia) || preg_match('/^Q19/', $input->referencia) || preg_match('/^Q20/', $input->referencia)) {
//                                    $referencia = 'Q1' . substr($pedimento, 0, 1) . substr($pedimento, -5);
//                                } else {
//                                    $referencia = $input->referencia;
//                                }
                                if (preg_match('/^Q/', $input->referencia)) {
                                    $referencia = substr($input->referencia, 0, 2) . substr($pedimento, -6);
                                }
                                $arr = array(
                                    "idAduana" => $input->aduana,
                                    "patente" => $adu["patente"],
                                    "aduana" => $adu["aduana"],
                                    "pedimento" => $pedimento,
                                    "referencia" => $referencia,
                                    "idCliente" => $input->cliente,
                                    "tipoOperacion" => $input->operacion,
                                    "cvePedimento" => $input->cvePedimento,
                                    "fechaEta" => date("Y-m-d H:i:s", strtotime($input->fechaEta)),
                                    "usuario" => $this->_session->username,
                                    "creado" => date("Y-m-d H:i:s"),
                                );
                                $mppr->agregar($arr);
                            }
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

    public function traficoJustificarAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "id" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags(), new Zend_Filter_Digits()),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id")) {
                    $trafico = new OAQ_Trafico(array("idTrafico" => $input->id, "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));
                    $trafico->actualizarFecha(54, date("Y-m-d"));
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

    public function traficoDesjustificarAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "id" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags(), new Zend_Filter_Digits()),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id")) {
                    $db = Zend_Registry::get("oaqintranet");
                    $stmt = $db->update("traficos", array("fechaInstruccionEspecial" => null), array("id = ?" => $input->id));
                    if ($stmt) {
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

    public function traficoDocumentosCompletosAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "id" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags(), new Zend_Filter_Digits()),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id")) {
                    $trafico = new OAQ_Trafico(array("idTrafico" => $input->id, "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));
                    $trafico->actualizarFecha(27, date("Y-m-d H:i:s"));
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

    public function cancelarOperacionAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "id" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags(), new Zend_Filter_Digits()),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id")) {
                    $trafico = new OAQ_Trafico(array("idTrafico" => $input->id, "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));
                    $trafico->cancelarTrafico($input->id);
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

    public function establecerSelloVucemAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idTrafico" => "Digits",
                    "idSello" => "Digits",
                    "tipo" => new Zend_Filter_StringToLower(),
                );
                $v = array(
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                    "idSello" => array("NotEmpty", new Zend_Validate_Int()),
                    "tipo" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idTrafico") && $input->isValid("idSello") && $input->isValid("tipo")) {
                    $mppr = new Trafico_Model_VucemMapper();
                    if ($input->tipo == "agente") {
                        $mppr->establecerSelloAgente($input->idTrafico, $input->idSello);
                    } else {
                        $mppr->establecerSelloCliente($input->idTrafico, $input->idSello);
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

    public function borrarVucemAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "id" => array("StringTrim", "StripTags", "Digits"),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id")) {
                    $mppr = new Trafico_Model_VucemMapper();
                    $log = new Trafico_Model_TraficoVucemLog();
                    $log->borrarIdVucem($input->id);
                    $mppr->borrar($input->id);
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

    public function borrarFacturaAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "id" => array("StringTrim", "StripTags", "Digits"),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id")) {

                    $mppr = new Trafico_Model_TraficoFacturasMapper();
                    
                    $info = $mppr->obtener($input->id);
                    
                    if (isset($info) && !empty($info)) {
                        $facturas = new OAQ_Archivos_Facturas(array("idTrafico" => $info["idTrafico"], "idFactura" => $input->id));
                        $facturas->log($info["idTrafico"], $input->id, "Borro factura " . $info["numeroFactura"], $this->_session->username);
                        
                        $deta = new Trafico_Model_FactDetalle();

                        $deta->borrarIdFactura($input->id);
                        $prod = new Trafico_Model_FactProd();
                        $prod->borrarIdFactura($input->id);
                        $vlog = new Trafico_Model_VucemMapper();
                        $vlog->borrarIdFactura($input->id);
                        $stmt = $mppr->delete($input->id);
                        if ($stmt == true) {
                            $this->_helper->json(array("success" => true));
                        }
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

    public function subirCdfisAction() {
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
                    "idTrafico" => new Zend_Validate_Int()
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                $upload = new Zend_File_Transfer_Adapter_Http();
                $upload->addValidator("Count", false, array("min" => 1, "max" => 20))
                        ->addValidator("Size", false, array("min" => "1", "max" => "20MB"))
                        ->addValidator("Extension", false, array("extension" => "xml", "case" => false));
                $upload->setDestination($this->_appconfig->getParam("tmpDir"));
                $files = $upload->getFileInfo();
                $sat = new OAQ_SATValidar();
                foreach ($files as $fieldname => $fileinfo) {
                    if (($upload->isUploaded($fieldname)) && ($upload->isValid($fieldname))) {
                        $ext = strtolower(pathinfo($fileinfo['name'], PATHINFO_EXTENSION));
                        $sha = sha1_file($fileinfo['tmp_name']);
                        $filename = $sha . '.' . $ext;
                        $upload->addFilter('Rename', $filename, $fieldname);
                        $upload->receive($fieldname);
                    }
                    if (file_exists($this->_appconfig->getParam("tmpDir") . DIRECTORY_SEPARATOR . $filename) && $input->isValid("idTrafico")) {
                        
                    }
                }
            } else {
                $this->_helper->json(array("success" => false, "message" => "No data recieved."));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function enviarEmailAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "id" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags(), new Zend_Filter_Digits()),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "data" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id") && $input->isValid("data")) {
                    $json = json_decode(html_entity_decode($input->data), true);
                    if (!empty($json)) {

                        $model = new Trafico_Model_TraficosMapper();
                        $arr = $model->obtenerPorId($input->id);
                        if (!empty($arr)) {
                            $emails = new OAQ_EmailsTraffic();
                            $view = new Zend_View();
                            $view->setScriptPath(APPLICATION_PATH . "/../library/Templates/");

                            $view->patente = $arr["patente"];
                            $view->aduana = $arr["aduana"];
                            $view->pedimento = $arr["pedimento"];
                            $view->referencia = $arr["referencia"];

                            if (!empty($json["emails"]) && !empty($json["archivos"])) {
                                $cont = new Trafico_Model_ContactosCliMapper();
                                $arrCont = $cont->obtenerPorArregloId($json["emails"]);
                                $mapper = new Archivo_Model_Repositorio();
                                $arrFiles = $mapper->obtenerPorArregloId($json["archivos"]);
                                if (APPLICATION_ENV == "production") {
                                    if (!empty($arrCont)) {
                                        foreach ($arrCont as $contact) {
                                            $emails->addTo($contact["email"], $contact["nombre"]);
                                        }
                                    } else {
                                        $emails->addTo("soporte@oaq.com.mx", "Soporte OAQ");
                                    }
                                } else {
                                    $emails->addTo("soporte@oaq.com.mx", "Soporte OAQ");
                                }
                                if (!empty($arrFiles)) {
                                    foreach ($arrFiles as $file) {
                                        $emails->addAttachment($file["ubicacion"]);
                                    }
                                }
                            }
                            $emails->contenidoPersonalizado($view->render("enviar-pedimento.phtml"));
                            $emails->setSubject("Pedimento pagado " . $arr["aduana"] . "-" . $arr["patente"] . "-" . $arr["pedimento"] . " " . $arr["referencia"]);
                            $emails->send();
                            $this->_helper->json(array("success" => true));
                        }
                    } else {
                        throw new Exception("No data recieved!");
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

    public function enviarEmailPermalinkAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "id" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags(), new Zend_Filter_Digits()),
                    "ccs" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags()),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "data" => array("NotEmpty"),
                    "uri" => array("NotEmpty"),
                    "ccs" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id")) {
                    $json = json_decode(html_entity_decode($input->data), true);
                    if (!empty($json)) {

                        $traffic = new OAQ_Trafico(array("idTrafico" => $input->id));

                        $emails = new OAQ_EmailsTraffic();

                        $view = new Zend_View();
                        $view->setScriptPath(APPLICATION_PATH . "/../library/Templates/");

                        $view->patente = $traffic->getPatente();
                        $view->aduana = $traffic->getAduana();
                        $view->pedimento = $traffic->getPedimento();
                        $view->referencia = $traffic->getReferencia();

                        if (!empty($json["emails"])) {
                            $mppr = new Trafico_Model_ContactosCliMapper();
                            $arr = $mppr->obtenerPorArregloId($json["emails"]);
                            if (APPLICATION_ENV == "production") {
                                foreach ($arr as $contact) {
                                    $emails->addTo($contact["email"], $contact["nombre"]);
                                }
                            } else {
                                $emails->addTo("soporte@oaq.com.mx", "Soporte OAQ");
                            }
                        }

                        if ($input->isValid("ccs") && $input->ccs !== "") {
                            $ccs = explode(';', $input->ccs);
                            if (!empty($ccs)) {
                                foreach ($ccs as $c) {
                                    $emails->addCc($c);
                                }
                            }
                        }
                        if (!empty($arr) || !empty($ccs)) {
                            $view->message = "A continuación se le envia una liga para descargar un expediente digital:";
                            $view->uri = $input->uri;

                            $emails->contenidoPersonalizado($view->render("link-expediente.phtml"));
                            $emails->setSubject("Link de expediente " . $traffic->getAduana() . "-" . $traffic->getPatente() . "-" . $traffic->getPedimento() . " " . $traffic->getReferencia());
                            $emails->send();

                            $this->_helper->json(array("success" => true));
                        } else {
                            $this->_helper->json(array("success" => false, "message" => "No ha seleccionado emails para enviar el expediente."));
                        }
                    } else {
                        throw new Exception("No data recieved!");
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

    public function subirPlantillaAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idTrafico" => "Digits",
                );
                $v = array(
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                    "replace" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                $upload = new Zend_File_Transfer_Adapter_Http();
                $upload->addValidator("Count", false, array("min" => 1, "max" => 1))
                        ->addValidator("Size", false, array("min" => "1", "max" => "20MB"))
                        ->addValidator("Extension", false, array("extension" => "xls,xlsx", "case" => false));
                if (APPLICATION_ENV == "production") {
                    $directory = $this->_appconfig->getParam("tmpDir");
                } else {
                    $directory = "D:\\xampp\\tmp";
                }
                if (!file_exists($directory)) {
                    throw new Exception("Directorio base no existe para subir plantilla en servidor.");
                }
                $upload->setDestination($directory);
                $files = $upload->getFileInfo();
                
                foreach ($files as $fieldname => $fileinfo) {
                    if (($upload->isUploaded($fieldname)) && ($upload->isValid($fieldname))) {
                        $ext = strtolower(pathinfo($fileinfo['name'], PATHINFO_EXTENSION));
                        $sha = sha1_file($fileinfo['tmp_name']);
                        $filename = $sha . '.' . $ext;
                        $upload->addFilter('Rename', $filename, $fieldname);
                        $upload->receive($fieldname);
                    }
                    if (file_exists($directory . DIRECTORY_SEPARATOR . $filename)) {
                        
                        $plantilla = new OAQ_Archivos_PlantillaFacturas($directory . DIRECTORY_SEPARATOR . $filename);
                        $plantilla->set_idTrafico($input->idTrafico);
                        $plantilla->set_idUsuario($this->_session->id);
                        $plantilla->set_usuario($this->_session->username);
                        if ($input->replace) {
                            // set replace to false when no overwrite required
                            $plantilla->set_replace(false);
                        }
                        if ($plantilla->analizar() == true) {
                            $this->_helper->json(array("success" => true));
                        }
                        $this->_helper->json(array("success" => true));
                    }
                    $this->_helper->json(array("success" => false));
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function subirSelloAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim"),
                    "idCliente" => "Digits",
                );
                $v = array(
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                    "pwdvu" => array("NotEmpty"),
                    "pwdws" => array("NotEmpty"),
                    "figura" => array("NotEmpty", new Zend_Validate_Int()),
                    "tipo" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if (!$input->isValid("idCliente") && !$input->isValid("pwdvu") && !$input->isValid("pwdws")) {
                    throw new Exception("Invalid data!");
                }
                $mppr = new Trafico_Model_ClientesMapper();
                $arr = $mppr->datosCliente($input->idCliente);

                $upload = new Zend_File_Transfer_Adapter_Http();
                $upload->addValidator("Count", false, array("min" => 1, "max" => 2))
                        ->addValidator("Size", false, array("min" => "1", "max" => "1MB"))
                        ->addValidator("Extension", false, array("extension" => "key,cer", "case" => false));
                if (APPLICATION_ENV == "production") {
                    $directory = $this->_appconfig->getParam("tmpDir");
                } else {
                    $directory = "D:\\wamp64\\tmp\\vucem";
                }
                $upload->setDestination($directory);
                $files = $upload->getFileInfo();
                $row = array(
                    "idCliente" => $input->idCliente,
                    "rfc" => $arr["rfc"],
                    "razonSocial" => $arr["nombre"],
                    "vuPass" => $input->pwdvu,
                    "wsPass" => $input->pwdws,
                    "figura" => $input->figura,
                    "tipo" => $input->tipo,
                    "usuario" => $this->_session->username,
                );

                foreach ($files as $fieldname => $fileinfo) {
                    if (($upload->isUploaded($fieldname)) && ($upload->isValid($fieldname))) {
                        $ext = strtolower(pathinfo($fileinfo['name'], PATHINFO_EXTENSION));
                        $sha = sha1_file($fileinfo['tmp_name']);
                        $filename = $sha . '.' . $ext;
                        $upload->addFilter('Rename', $filename, $fieldname);
                        $upload->receive($fieldname);
                    }
                    if (file_exists($directory . DIRECTORY_SEPARATOR . $filename)) {
                        if (strtolower($ext) == "cer") {
                            $row["cerFile"] = $directory . DIRECTORY_SEPARATOR . $filename;
                            $row["cerFileName"] = $fileinfo['name'];
                        }
                        if (strtolower($ext) == "key") {
                            $row["keyFile"] = $directory . DIRECTORY_SEPARATOR . $filename;
                            $row["keyFileName"] = $fileinfo['name'];
                        }
                    }
                }
                $sellos = new OAQ_Archivos_SellosVucem($row);
                $res = $sellos->analizarSello();
                if ($res["success"] === false) {
                    $this->_helper->json(array("success" => false, "messages" => $res["messages"]));
                } else {
                    $sellos->guardarSello();
                    $this->_helper->json(array("success" => true));
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function actualizarSelloAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim"),
                    "id" => "Digits",
                    "idCliente" => "Digits",
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                    "pwdws" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("idCliente") && $input->isValid("id") && $input->isValid("pwdws")) {
                    $sellos = new OAQ_Archivos_SellosVucem(array("id" => $input->id, "idCliente" => $input->idCliente));
                    $sellos->setWsPass($input->pwdws);
                    if ($sellos->actualizarWs()) {
                        $this->_helper->json(array("success" => true));
                    } else {
                        throw new Exception("No se pudo actualizar contraseña.");
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

    public function subirSelloAgenteAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim"),
                    "idAgente" => "Digits",
                    "patente" => "Digits",
                );
                $v = array(
                    "idAgente" => array("NotEmpty", new Zend_Validate_Int()),
                    "patente" => array("NotEmpty", new Zend_Validate_Int()),
                    "pwdvu" => array("NotEmpty"),
                    "pwdws" => array("NotEmpty"),
                    "figura" => array("NotEmpty", new Zend_Validate_Int()),
                    "tipo" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if (!$input->isValid("idAgente") && !$input->isValid("pwdvu") && !$input->isValid("pwdws")) {
                    throw new Exception("Invalid data!");
                }
                $mppr = new Trafico_Model_Agentes();
                $arr = $mppr->obtener($input->idAgente);

                $upload = new Zend_File_Transfer_Adapter_Http();
                $upload->addValidator("Count", false, array("min" => 1, "max" => 2))
                        ->addValidator("Size", false, array("min" => "1", "max" => "1MB"))
                        ->addValidator("Extension", false, array("extension" => "key,cer", "case" => false));
                if (APPLICATION_ENV == "production") {
                    $directory = $this->_appconfig->getParam("tmpDir");
                } else {
                    $directory = "D:\\xampp\\tmp\\vucem";
                }
                $upload->setDestination($directory);
                $files = $upload->getFileInfo();
                $row = array(
                    "idAgente" => $input->idAgente,
                    "patente" => $input->patente,
                    "rfc" => $arr["rfc"],
                    "vuPass" => $input->pwdvu,
                    "wsPass" => $input->pwdws,
                    "figura" => $input->figura,
                    "tipo" => $input->tipo,
                    "usuario" => $this->_session->username,
                );

                foreach ($files as $fieldname => $fileinfo) {
                    if (($upload->isUploaded($fieldname)) && ($upload->isValid($fieldname))) {
                        $ext = strtolower(pathinfo($fileinfo['name'], PATHINFO_EXTENSION));
                        $sha = sha1_file($fileinfo['tmp_name']);
                        $filename = $sha . '.' . $ext;
                        $upload->addFilter('Rename', $filename, $fieldname);
                        $upload->receive($fieldname);
                    }
                    if (file_exists($directory . DIRECTORY_SEPARATOR . $filename)) {
                        if (strtolower($ext) == "cer") {
                            $row["cerFile"] = $directory . DIRECTORY_SEPARATOR . $filename;
                            $row["cerFileName"] = $fileinfo['name'];
                        }
                        if (strtolower($ext) == "key") {
                            $row["keyFile"] = $directory . DIRECTORY_SEPARATOR . $filename;
                            $row["keyFileName"] = $fileinfo['name'];
                        }
                    }
                }
                $sellos = new OAQ_Archivos_SellosVucem($row);
                $res = $sellos->analizarSello();
                if ($res["success"] === false) {
                    $this->_helper->json(array("success" => false, "messages" => $res["messages"]));
                } else {
                    $sellos->guardarSelloAgente();
                    $this->_helper->json(array("success" => true));
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function actualizarSelloAgenteAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim"),
                    "id" => "Digits",
                    "idCliente" => "Digits",
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                    "pwdws" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("idCliente") && $input->isValid("id") && $input->isValid("pwdws")) {
                    $sellos = new OAQ_Archivos_SellosVucem(array("id" => $input->id, "idCliente" => $input->idCliente));
                    $sellos->setWsPass($input->pwdws);
                    if ($sellos->actualizarWs()) {
                        $this->_helper->json(array("success" => true));
                    } else {
                        throw new Exception("No se pudo actualizar contraseña.");
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

    public function mvhcEstatusAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "id" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags(), new Zend_Filter_Digits()),
                    "estatus" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags(), new Zend_Filter_Digits()),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "estatus" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id")) {
                    $traffic = new OAQ_Trafico(array("idTrafico" => $input->id));
                    $idRepo = $traffic->verificarIndexRepositorios();
                    if (isset($idRepo)) {
                        $mppr = new Archivo_Model_RepositorioIndex();
                        if ((int) $input->estatus === 0) {
                            $mppr->update($idRepo, array("mvhcCliente" => null, "mvhcFirmada" => null));
                        } else if ((int) $input->estatus === 1) {
                            $mppr->update($idRepo, array("mvhcCliente" => 1, "mvhcFirmada" => null));
                        } else if ((int) $input->estatus === 2) {
                            $mppr->update($idRepo, array("mvhcCliente" => 1, "mvhcFirmada" => 1));
                        }
                        $this->_helper->json(array("success" => true));
                    } else {
                        throw new Exception("No data found!");
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

    public function mvhcEstatusEnviadaAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "id" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags(), new Zend_Filter_Digits()),
                    "estatus" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags(), new Zend_Filter_Digits()),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "estatus" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id")) {
                    $traffic = new OAQ_Trafico(array("idTrafico" => $input->id));
                    $idRepo = $traffic->verificarIndexRepositorios();
                    if (isset($idRepo)) {
                        $mppr = new Archivo_Model_RepositorioIndex();
                        if ((int) $input->estatus === 0) {
                            $mppr->update($idRepo, array("mvhcEnviada" => null));
                        } else if ((int) $input->estatus === 1) {
                            $mppr->update($idRepo, array("mvhcEnviada" => 1));
                        }
                        $this->_helper->json(array("success" => true));
                    } else {
                        throw new Exception("No data found!");
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

    public function mvhcNumGuiaAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "id" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags(), new Zend_Filter_Digits()),
                    "mvhcGuia" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags(), new Zend_Filter_StringToUpper()),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "mvhcGuia" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("id") && $input->isValid("mvhcGuia")) {
                    $traffic = new OAQ_Trafico(array("idTrafico" => $input->id));
                    $idRepo = $traffic->verificarIndexRepositorios();
                    if (isset($idRepo)) {
                        $mppr = new Archivo_Model_RepositorioIndex();
                        if ((int) $input->estatus === 0) {
                            $mppr->update($idRepo, array("numGuia" => $input->mvhcGuia));
                        }
                        $this->_helper->json(array("success" => true));
                    } else {
                        throw new Exception("No data found!");
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

    public function establecerDefaultAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idCliente" => array("Digits"),
                    "idSello" => array("Digits"),
                    "tipo" => array("StringToLower"),
                );
                $v = array(
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                    "idSello" => array("NotEmpty", new Zend_Validate_Int()),
                    "tipo" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("idCliente") && $input->isValid("idSello") && $input->isValid("tipo")) {
                    $mppr = new Trafico_Model_CliSello();
                    if (!($mppr->verificar($input->idCliente))) {
                        $mppr->agregar($input->idCliente, $input->idSello, $input->tipo);
                        $this->_helper->json(array("success" => true));
                    } else {
                        $mppr->actualizar($input->idCliente, $input->idSello, $input->tipo);
                        $this->_helper->json(array("success" => true));
                    }
                    throw new Exception("Uncaught values.");
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("No data recieved.");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function actualizarSellosAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idCliente" => array("Digits"),
                    "idSello" => array("Digits"),
                );
                $v = array(
                    "idCliente" => array("NotEmpty", new Zend_Validate_Int()),
                    "idSello" => array("NotEmpty", new Zend_Validate_Int()),
                    "ids" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("idCliente") && $input->isValid("idSello") && $input->isValid("ids")) {
                    $sellos = new OAQ_Archivos_SellosVucem();
                    if (is_array($input->ids)) {
                        foreach ($input->ids as $value) {
                            $sellos->actualizarSelloDesdeTrafico($input->idSello, $value);
                        }
                    } else {
                        $sellos->actualizarSelloDesdeTrafico($input->idSello, $input->ids);
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

    public function actualizarTraficoAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idTrafico" => array("Digits"),
                    "contenedorCaja" => array("StringToUpper"),
                    "nombreBuque" => array("StringToUpper"),
                );
                $v = array(
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                    "contenedorCaja" => array("NotEmpty"),
                    "nombreBuque" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("idTrafico")) {
                    $trafico = new OAQ_Trafico(array("idTrafico" => $input->idTrafico));
                    $arr = array(
                        "contenedorCaja" => $input->isValid("contenedorCaja") ? $input->contenedorCaja : null,
                        "nombreBuque" => $input->isValid("nombreBuque") ? $input->nombreBuque : null,
                    );
                    if ($trafico->actualizar($arr)) {
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

    public function avisosAction() {
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
                    "alert" => new Zend_Validate_InArray(array("comentario", "deposito", "creacion", "cancelacion", "habilitado")),
                );
                $input = new Zend_Filter_Input($flt, $vdr, $request->getPost());
                if ($input->isValid("id") && $input->isValid("action") && $input->isValid("alert")) {
                    $mapper = new Trafico_Model_ContactosMapper();
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

    public function modificarTraficoAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idTrafico" => array("Digits"),
                    "contenedorCaja" => array("StringToUpper"),
                    "nombreBuque" => array("StringToUpper"),
                    "placas" => array("StringToUpper"),
                    "ordenCompra" => array("StringToUpper"),
                    "candados" => array("StringToUpper"),
                    "tipoCarga" => array("Digits"),
                    "idPlanta" => array("Digits"),
                );
                $v = array(
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                    "contenedorCaja" => "NotEmpty",
                    "nombreBuque" => "NotEmpty",
                    "placas" => "NotEmpty",
                    "ordenCompra" => "NotEmpty",
                    "candados" => "NotEmpty",
                    "tipoCarga" => "NotEmpty",
                    "idPlanta" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("idTrafico")) {

                    $log = new Trafico_Model_BitacoraMapper();
                    $trafico = new OAQ_Trafico(array("idTrafico" => $input->idTrafico, "idUsuario" => $this->_session->id, "usuario" => $this->_session->username));
                    $arr = array(
                        "contenedorCaja" => ($input->isValid("contenedorCaja")) ? $input->contenedorCaja : null,
                        "nombreBuque" => ($input->isValid("nombreBuque")) ? $input->nombreBuque : null,
                        "placas" => ($input->isValid("placas")) ? $input->placas : null,
                        "ordenCompra" => ($input->isValid("ordenCompra")) ? $input->ordenCompra : null,
                        "candados" => ($input->isValid("candados")) ? $input->candados : null,
                        "tipoCarga" => ($input->isValid("tipoCarga")) ? $input->tipoCarga : null,
                        "idPlanta" => ($input->isValid("idPlanta")) ? $input->idPlanta : null,
                    );

                    $log_msg = "SE MODIFICÓ TRÁFICO: ";
                    if ($input->isValid("contenedorCaja") && trim($input->contenedorCaja) != '') {
                        $log_msg = $log_msg . "CONTENEDOR/CAJA: " . $input->contenedorCaja . " ";
                    }
                    if ($input->isValid("nombreBuque") && trim($input->nombreBuque) != '') {
                        $log_msg = $log_msg . "NOM. BUQUE: " . $input->nombreBuque . " ";
                    }
                    if ($input->isValid("placas") && trim($input->placas) != '') {
                        $log_msg = $log_msg . "PLACAS: " . $input->placas . " ";
                    }
                    if ($input->isValid("ordenCompra") && trim($input->ordenCompra) != '') {
                        $log_msg = $log_msg . "ORDEN DE COMPRA: " . $input->ordenCompra . " ";
                    }
                    if ($input->isValid("candados") && trim($input->candados) != '') {
                        $log_msg = $log_msg . "CANDADOS: " . $input->candados . " ";
                    }
                    if ($input->isValid("tipoCarga") && trim($input->tipoCarga) != '') {
                        $log_msg = $log_msg . "TIPO DE CARGA: " . $input->tipoCarga . " ";
                    }

                    $row = array(
                        "patente" => $trafico->getPatente(),
                        "aduana" => $trafico->getAduana(),
                        "pedimento" => $trafico->getPedimento(),
                        "referencia" => $trafico->getReferencia(),
                        "bitacora" => $log_msg,
                        "usuario" => $this->_session->username,
                        "creado" => date("Y-m-d H:i:s"),
                    );

                    if (($trafico->actualizar($arr))) {
                        $log->agregar($row);
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

    public function pdfFacturaAction() {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idTrafico" => array("Digits"),
                    "idFactura" => array("Digits"),
                );
                $v = array(
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                    "idFactura" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if (!$input->isValid("idTrafico") || !$input->isValid("idFactura")) {
                    throw new Exception("Invalid input!" . Zend_Debug::dump($input->getErrors(), true) . Zend_Debug::dump($input->getMessages(), true));
                }
            }
            $misc = new OAQ_Misc();
            if (APPLICATION_ENV == "production") {
                $misc->set_baseDir($this->_appconfig->getParam("expdest"));
            } else {
                $misc->set_baseDir("D:\\xampp\\tmp\\expedientes");
            }

            $traffic = new OAQ_Trafico(array("idTrafico" => $input->idTrafico));

            $mppr = new Trafico_Model_TraficoFacturasMapper();
            $arr = $mppr->detalleFactura($input->idFactura);

            $model = new Archivo_Model_RepositorioMapper();

            $upload = new Zend_File_Transfer_Adapter_Http();
            $upload->addValidator("Count", false, array("min" => 1, "max" => 15))
                    ->addValidator("Size", false, array("min" => "1", "max" => "25MB"))
                    ->addValidator("Extension", false, array("extension" => "pdf", "case" => false));
            if (($path = $misc->directorioExpedienteDigital($traffic->getPatente(), $traffic->getAduana(), $traffic->getReferencia()))) {
                $upload->setDestination($path);
            }
            $files = $upload->getFileInfo();
            foreach ($files as $fieldname => $fileinfo) {
                if (($upload->isUploaded($fieldname))) {
                    $filename = "FO_" .  $arr["numeroFactura"] . '_' . preg_replace('/\s+/', '_', $arr["nombreProveedor"]) . '.pdf';
                    $verificar = $model->verificarArchivo($traffic->getPatente(), $traffic->getReferencia(), $filename);
                    if ($verificar == false) {
                        $upload->receive($fieldname);
                        if (($misc->renombrarArchivo($path, $fileinfo["name"], $filename))) {
                            if (($model->nuevaFacturaOriginal($input->idTrafico, $input->idFactura, 34, null, $traffic->getPatente(), $traffic->getAduana(), $traffic->getPedimento(), $traffic->getReferencia(), $filename, $path . DIRECTORY_SEPARATOR . $filename, $this->_session->username, $traffic->getRfcCliente(), $arr["numeroFactura"], $arr["nombreProveedor"]))) {
                                $mppr->actualizar($input->idFactura, array("pdfFactura" => 1));
                            }
                        }
                    }
                } else {
                    $error = $upload->getErrors();
                    $errors[] = array(
                        "filename" => $fileinfo["name"],
                        "errors" => $error,
                    );
                }
            }
            if (isset($errors)) {
                $this->_helper->json(array("success" => false, "errors" => $errors));
            } else {
                $this->_helper->json(array("success" => true));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function guardarFacturaOriginalAction() {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idRepositorio" => array("Digits"),
                    "idTrafico" => array("Digits"),
                    "idFactura" => array("Digits"),
                    "numeroFactura" => array("StringToUpper"),
                    "nombreProveedor" => array("StringToUpper"),
                );
                $v = array(
                    "idRepositorio" => array("NotEmpty", new Zend_Validate_Int()),
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                    "idFactura" => array("NotEmpty", new Zend_Validate_Int()),
                    "numeroFactura" => array("NotEmpty"),
                    "nombreProveedor" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("idRepositorio") && $input->isValid("idTrafico") && $input->isValid("idFactura")) {

                    $model = new Archivo_Model_RepositorioMapper();
                    $arr = array(
                        "folio" => $input->numeroFactura,
                        "emisor_nombre" => $input->nombreProveedor,
                    );
                    if (($model->update($input->idRepositorio, $arr))) {
                        $this->_helper->json(array("success" => true));
                    }
                    $this->_helper->json(array("success" => false));
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

    public function agregarRfcConsultaEdocAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => "Digits",
                    "rfc" => "StringToUpper"
                );
                $v = array(
                    "id" => new Zend_Validate_Int(),
                    "rfc" => new Zend_Validate_NotEmpty(),
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("id") && $i->isValid("rfc")) {
                    $mppr = new Trafico_Model_RfcConsultaMapper();
                    if (!($mppr->verificarRfcEdocument($i->id, $i->rfc))) {
                        $arr = array(
                            "idCliente" => $i->id,
                            "rfc" => $i->rfc,
                            "tipo" => 'edoc'
                        );
                        if ($mppr->agregar($arr)) {
                            $this->_helper->json(array("success" => true));
                        } else {
                            $this->_helper->json(array("success" => false, "message" => "No se pudo agregar"));
                        }
                    } else {
                        $this->_helper->json(array("success" => false, "message" => "RFC ya existe"));
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

    public function agregarRfcConsultaCoveAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => "Digits",
                    "rfc" => "StringToUpper"
                );
                $v = array(
                    "id" => new Zend_Validate_Int(),
                    "rfc" => new Zend_Validate_NotEmpty(),
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("id") && $i->isValid("rfc")) {
                    $mppr = new Trafico_Model_RfcConsultaMapper();
                    if (!($mppr->verificarRfcEdocument($i->id, $i->rfc))) {
                        $arr = array(
                            "idCliente" => $i->id,
                            "rfc" => $i->rfc,
                            "tipo" => 'cove'
                        );
                        if ($mppr->agregar($arr)) {
                            $this->_helper->json(array("success" => true));
                        } else {
                            $this->_helper->json(array("success" => false, "message" => "No se pudo agregar"));
                        }
                    } else {
                        $this->_helper->json(array("success" => false, "message" => "RFC ya existe"));
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

    public function removerRfcConsultaAction() {
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
                    "id" => new Zend_Validate_Int(),
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("id")) {
                    $mppr = new Trafico_Model_RfcConsultaMapper();
                    if (!($mppr->borrar($i->id))) {
                        $this->_helper->json(array("success" => true));
                    } else {
                        $this->_helper->json(array("success" => false, "message" => "No se pudo borrar"));
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

    /*public function vucemEnviarMultipleAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "idTrafico" => array("Digits"),
                );
                $v = array(
                    "idTrafico" => array("NotEmpty", new Zend_Validate_Int()),
                    "ids" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("idTrafico") && $input->isValid("ids")) {
                    $sellos = new OAQ_Archivos_SellosVucem();
                    if (is_array($input->ids)) {
                        foreach ($input->ids as $value) {
                            $sellos->actualizarSelloDesdeTrafico($input->idSello, $value);
                        }
                    } else {
                        $sellos->actualizarSelloDesdeTrafico($input->idSello, $input->ids);
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
    }*/

}
