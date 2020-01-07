<?php

class Operaciones_ValidadorController extends Zend_Controller_Action {

    protected $_session;
    protected $_config;
    protected $_appconfig;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
    }

    public function preDispatch() {
        $this->_session = NULL ? $this->_session = new Zend_Session_Namespace("") : $this->_session = new Zend_Session_Namespace($this->_config->app->namespace);
        if ($this->_session->authenticated == true) {
            $session = new OAQ_Session($this->_session, $this->_appconfig);
            $session->actualizar();
            $session->actualizarSesion(Zend_Controller_Front::getInstance()->getRequest()->getRequestUri());
        } else {
            $this->getResponse()->setRedirect($this->_appconfig->getParam("link-logout"));
        }
        $this->_svucem = NULL ? $this->_svucem = new Zend_Session_Namespace("") : $this->_svucem = new Zend_Session_Namespace("OAQVucem");
        $this->_svucem->setExpirationSeconds($this->_appconfig->getParam("session-exp"));
    }

    public function obtenerArchivosValidacionAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "patente" => array("Digits"),
                    "aduana" => array("Digits"),
                );
                $v = array(
                    "patente" => array("NotEmpty", new Zend_Validate_Int()),
                    "aduana" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $i = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($i->isValid("patente") && $i->isValid("aduana")) {
                    $table = new Operaciones_Model_ValidadorArchivos();
                    $model = new Application_Model_DirectoriosValidacion();
                    $archVal = new OAQ_ArchivosValidacion();
                    $folder = $model->obtener($i->patente, $i->aduana);
                    if (!file_exists($folder)) {
                        mkdir($folder, 0777, true);
                    }
                    if (isset($folder)) {
                        $view = new Zend_View();
                        $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/validador/");
                        $directory = new RecursiveDirectoryIterator($folder);
                        $iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);
                        $files = new RegexIterator($iterator, "/M[0-9]{7}.([0-9]{3})|E[0-9]{7}.([0-9]{3})/i", RecursiveRegexIterator::GET_MATCH);
                        foreach ($files as $nombre => $object) {
                            $data[] = basename($nombre);
                        }
                        array_multisort($data, SORT_DESC);
                        if (!empty($data)) {
                            $html = "";
                            $htmle = "";
                            foreach ($data as $item) {
                                if (preg_match("/M[0-9]{7}.([0-9]{3})/i", $item)) {
                                    if (!($id = $table->verificar($i->patente, $i->aduana, $item))) {
                                        $contenido = file_get_contents($folder . DIRECTORY_SEPARATOR . $item);
                                        $id = $table->agregar($i->patente, $i->aduana, $item, base64_encode($contenido), $this->_session->username);
                                        $archVal->agregarArchivoValidacion($i->patente, $i->aduana, $folder, $item, base64_encode($contenido), "m3", $this->_session->username);
                                    }
                                    $html .= "<div class=\"content-row\" id=\"m3_" . $id["id"] . "\" onclick=\"contenidoArchivo(" . $id["id"] . ");\">";
                                    $html .= "<div class=\"content-column column1\"><a>" . $item . "</a></div>";
                                    $html .= "</div>";
                                }
                                if (preg_match("/E[0-9]{7}.([0-9]{3})/i", $item)) {
                                    if (!($id = $table->verificar($i->patente, $i->aduana, $item))) {
                                        $id = $table->agregar($i->patente, $i->aduana, $item, base64_encode(file_get_contents($folder . DIRECTORY_SEPARATOR . $item)), $this->_session->username);
                                        $archVal->agregarArchivoValidacion($i->patente, $i->aduana, $folder, $item, base64_encode(file_get_contents($folder . DIRECTORY_SEPARATOR . $item)), "pago", $this->_session->username);
                                    }                                    
                                    $htmle .= "<div class=\"content-row\" id=\"ae_" . $id["id"] . "\" onclick=\"contenidoArchivoPago(" . $id["id"] . ");\">";
                                    $htmle .= "<div class=\"content-column column1\"><a>" . $item . "</a></div>";
                                    $htmle .= "</div>";
                                }
                            }
                            $this->_helper->json(array("success" => true, "archivos" => $html, "pagos" => $htmle));
                        } else {
                            $this->_helper->json(array("success" => false, "message" => "No se encontraron archivos"));
                        }                        
                    } else {
                        throw new Exception("Invalid folder!");
                    }
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function saveToDiskAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            if (isset($post["id"])) {
                try {
                    $model = new Application_Model_DirectoriosValidacion();
                    $table = new Operaciones_Model_ValidadorArchivos();
                    $file = $table->obtener($post["id"]);
                    $folder = $model->obtener($file["patente"], $file["aduana"]);
                    if (!file_exists($folder . DIRECTORY_SEPARATOR . $file["archivo"])) {
                        file_put_contents($folder . DIRECTORY_SEPARATOR . $file["archivo"], base64_decode($file["contenido"]));
                    }
                } catch (Exception $e) {
                    $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
                }
            }
        }
    }

    public function downloadFileAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $id = $this->_request->getParam("id", null);
        $type = $this->_request->getParam("type", null);
        if (isset($id)) {
            try {
                if ($type == "res") {
                    $table = new Operaciones_Model_ValidadorRespuestas();
                    $file = $table->obtenerContenido($id);
                } else {
                    $table = new Operaciones_Model_ValidadorArchivos();
                    $file = $table->obtener($id);
                }
                if (isset($file)) {
                    header("Content-Description: File Transfer");
                    header("Expires: 0");
                    header("Content-Type:text/plain");
                    header("Content-Disposition: attachment; filename=\"" . $file["archivo"] . "\"");
                    header("Content-Length: " . strlen(base64_decode($file["contenido"])));
                    echo base64_decode($file["contenido"]);
                    return;
                }
            } catch (Exception $ex) {
                $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
            }
        }
    }

    public function saveToDiskResAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            if (isset($post["id"])) {
                try {
                    $model = new Application_Model_DirectoriosValidacion();
                    $table = new Operaciones_Model_ValidadorRespuestas();
                    $file = $table->patenteAduana($post["id"]);
                    $folder = $model->obtener($file["patente"], $file["aduana"]);
                    if (!file_exists($folder . DIRECTORY_SEPARATOR . $file["archivo"])) {
                        file_put_contents($folder . DIRECTORY_SEPARATOR . $file["archivo"], base64_decode($file["contenido"]));
                    }
                } catch (Exception $ex) {
                    $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
                }
            }
        }
    }

    public function contenidoArchivoAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            if (isset($post["id"])) {
                try {
                    $view = new Zend_View();
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/validador/");
                    $table = new Operaciones_Model_ValidadorArchivos();
                    $model = new Operaciones_Model_ValidadorRespuestas();
                    if (($contenido = $table->obtener($post["id"]))) {
                        $estatus = $table->obtenerEstatus($post["id"]);
                        if (isset($estatus)) {
                            $view->archivo = $estatus;
                            $view->id = $post["id"];
                            $respuestas = $model->obtener($post["id"]);
                            $view->respuestas = $respuestas;
                            $modell = new Operaciones_Model_ValidadorBitacora();
                            $msgs = $modell->obtenerTodos($post["id"]);
                            $view->bitacora = $msgs;
                        }
                        $this->_helper->json(array("success" => true, "contenido" => $contenido["contenido"], "estatus" => $view->render("respuestas.phtml"), "bitacora" => $view->render("bitacora.phtml")));
                    }
                } catch (Exception $ex) {
                    $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
                }
            }
        }
    }

    public function contenidoArchivoRespuestaAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            if (isset($post["id"])) {
                try {
                    $model = new Operaciones_Model_ValidadorRespuestas();
                    if (($contenido = $model->obtenerContenido($post["id"]))) {
                        $this->_helper->json(array("success" => true, "contenido" => $contenido["contenido"], "archivo" => $post["id"]));
                    }
                } catch (Exception $ex) {
                    $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
                }
            }
        }
    }

    public function contenidoArchivoM3Action() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            if (isset($post["id"])) {
                try {
                    $table = new Operaciones_Model_ValidadorArchivos();
                    if (($contenido = $table->obtener($post["id"]))) {
                        $this->_helper->json(array("success" => true, "contenido" => $contenido["contenido"], "archivo" => $post["id"]));
                    }
                } catch (Exception $ex) {
                    $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
                }
            }
        }
    }

    public function contenidoArchivoEAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            if (isset($post["id"])) {
                try {
                    $table = new Operaciones_Model_ValidadorArchivos();
                    if (($contenido = $table->obtener($post["id"]))) {
                        $this->_helper->json(array("success" => true, "contenido" => $contenido["contenido"]));
                    }
                } catch (Exception $ex) {
                    $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
                }
            }
        }
    }

    public function contenidoArchivoAAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            if (isset($post["id"])) {
                try {
                    $table = new Operaciones_Model_ValidadorRespuestas();
                    if (($contenido = $table->obtenerContenido($post["id"]))) {
                        $this->_helper->json(array("success" => true, "contenido" => $contenido["contenido"]));
                    }
                } catch (Exception $ex) {
                    $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
                }
            }
        }
    }

    public function contenidoArchivoPagoAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            if (isset($post["id"])) {
                try {
                    $table = new Operaciones_Model_ValidadorArchivos();
                    $model = new Operaciones_Model_ValidadorRespuestas();
                    if (($contenido = $table->obtener($post["id"]))) {
                        $estatus = $table->obtenerEstatus($post["id"]);
                        if (isset($estatus)) {
                            $status = "<table class=\"traffic-table traffic-table-left\">";
                            $status .= "<tr><th colspan=\"4\" class=\"traffic-table-title\">ARCHIVOS</th></tr>";
                            $status .= "<tr><th style=\"width: 24px\">&nbsp;</th><th>Archivo</th><th>Tamaño</th><th style=\"width: 70px\">Estatus</th></tr>";
                            $status .= "<tr>";
                            $status .= "<td><img src=\"/images/icons/disk.png\"></td>";
                            $status .= "<td><a style=\"cursor: pointer;\" onclick=\"contenidoPago(" . $post["id"] . ");\">" . $estatus["archivo"] . "</a></td>";
                            $status .= "<td>" . $estatus["size"] . " bytes</td>";
                            if ($estatus["enviado"] == 0) {
                                $status .= "<td>";                                
                                $status .= "<img id=\"imge_" . $post["id"] . "\" src=\"/images/icons/send.png\" onclick=\"pagarArchivo(" . $estatus["id"] . ");\" >";
                                $status .= "<img id=\"imgl_" . $post["id"] . "\" src=\"/images/preloader.gif\" style=\"display: none;\" >";
                                $status .= "</td>";
                            } else {
                                if ($estatus["enviado"] == 1 && $estatus["pagado"] == 1) {
                                    $status .= "<td><img src=\"/images/icons/ok.png\" ></td>";
                                }
                                if ($estatus["enviado"] == 1 && $estatus["pagado"] == 0) {
                                    $status .= "<td>";
                                    $status .= "<img id=\"imge_" . $post["id"] . "\" src=\"/images/icons/send.png\" onclick=\"pagarArchivo(" . $estatus["id"] . ");\" >";
                                    $status .= "<img id=\"imgl_" . $post["id"] . "\" src=\"/images/preloader.gif\" style=\"display: none;\" >";
                                    $status .= "</td>";
                                }
                            }
                            $status .= "</tr>";
                            $respuestas = $model->obtener($post["id"]);
                            if (isset($respuestas) && !empty($respuestas)) {
                                foreach ($respuestas as $res) {
                                    $status .= "<tr>";
                                    $status .= "<td><img src=\"/images/icons/disk.png\"></td>";
                                    $status .= "<td><a style=\"cursor: pointer;\" onclick=\"contenidoRespuestaPago(" . $res["id"] . ");\">" . $res["archivo"] . "</a></td>";
                                    $status .= "<td>" . $res["size"] . " bytes</td>";
                                    $status .= "<td></td>";
                                    $status .= "</tr>";
                                }
                            }
                            $status .= "</table>";
                            $modell = new Operaciones_Model_ValidadorBitacora();
                            $msgs = $modell->obtenerTodos($post["id"]);
                            if (isset($msgs) && $msgs != false) {
                                $msg = "<table class=\"traffic-table traffic-table-left\">";
                                foreach ($msgs as $item) {
                                    $msg .= "<tr>";
                                    $msg .= "<td>" . $item["mensaje"] . "</td>";
                                    $msg .= "<td style=\"width: 95px\">" . date("d/m/y H:i", strtotime($item["creado"])) . "</td>";
                                    $msg .= "</tr>";
                                }
                                $msg .= "</table>";
                            }
                        }
                        $this->_helper->json(array("success" => true, "contenido" => $contenido["contenido"], "estatus" => $status, "bitacora" => isset($msg) ? $msg: ""));
                    }
                } catch (Exception $ex) {
                    $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
                }
            }
        }
    }

    public function validarArchivoAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            if (isset($post["id"])) {
                try {
                    $cmd = 'curl -k -d -X "https://127.0.0.1/automatizacion/validador/validar-archivo?id=' . $post["id"] . '&idUsuario=' . $this->_session->id . '" > /dev/null &';
                    $val = new Operaciones_Model_ValidadorArchivos();
                    $file = $val->obtener($post["id"]);
                    $table = new Operaciones_Model_ValidadorBitacora();
                    $table->agregar($post["id"], "INICIANDO " . $file["archivo"]);
                    exec($cmd);
                    $this->_helper->json(array("success" => true, "id" => $post["id"]));
                } catch (Exception $ex) {
                    $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
                }
            }
        }
    }

    public function reenviarArchivoAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            if (isset($post["id"])) {
                try {
                    $cmd = 'curl -k -d -X "https://127.0.0.1/automatizacion/validador/reenviar-archivo?id=' . $post["id"] . '&idUsuario=' . $this->_session->id . '" > /dev/null &';
                    $val = new Operaciones_Model_ValidadorArchivos();
                    $file = $val->obtener($post["id"]);
                    $table = new Operaciones_Model_ValidadorBitacora();
                    $table->agregar($post["id"], "REENVIANDO A VALIDADOR " . $file["archivo"]);
                    exec($cmd);
                    $this->_helper->json(array("success" => true, "id" => $post["id"]));
                } catch (Exception $ex) {
                    $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
                }
            }
        }
    }

    public function pagarArchivoAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            if (isset($post["id"])) {
                try {
                    $cmd = 'curl -k -d -X "https://127.0.0.1/automatizacion/validador/pagar-archivo?id=' . $post["id"] . '&idUsuario=' . $this->_session->id . '" > /dev/null &';
                    $val = new Operaciones_Model_ValidadorArchivos();
                    $file = $val->obtener($post["id"]);
                    $table = new Operaciones_Model_ValidadorBitacora();
                    $table->agregar($post["id"], "INICIANDO PAGO " . $file["archivo"]);
                    exec($cmd);
                    $this->_helper->json(array("success" => true, "id" => $post["id"]));
                } catch (Exception $ex) {
                    $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
                }
            }
        }
    }

    public function estatusRevisarValidacionAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $id = $this->_request->getParam("id", null);
        if (isset($id)) {
            $table = new Operaciones_Model_ValidadorBitacora();
            $last = $table->obtenerUltimo($id);
            $msgs = $table->obtenerTodos($id);
            if (isset($msgs) && $msgs != false) {
                $msg = "<table class=\"traffic-table traffic-table-left\">";
                foreach ($msgs as $item) {
                    $msg .= "<tr>";
                    $msg .= "<td>" . $item["mensaje"] . "</td>";
                    $msg .= "<td>" . $item["creado"] . "</td>";
                    $msg .= "</tr>";
                }
                $msg .= "</table>";
            }
            if (!preg_match("/SE AGOTO TIEMPO/i", $last["mensaje"]) && !preg_match("/TIENE ERROR/i", $last["mensaje"]) && !preg_match("/SE CONCLUYO VALIDA/i", $last["mensaje"]) && !preg_match("/TIMEOUT/i", $last["mensaje"])) {
                $this->_helper->json(array("message" => $msg, "success" => true));
            } else {
                $this->_helper->json(array("message" => $msg, "success" => false));
            }
        } else {
            $this->_helper->json(array("message" => "SIN RESPUESTA"));
        }
    }

    public function estatusRevisarPagosAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $id = $this->_request->getParam("id", null);
        if (isset($id)) {
            $table = new Operaciones_Model_ValidadorBitacora();
            $last = $table->obtenerUltimo($id);
            $msgs = $table->obtenerTodos($id);
            if (isset($msgs) && $msgs != false) {
                $msg = "<table class=\"traffic-table traffic-table-left\">";
                foreach ($msgs as $item) {
                    $msg .= "<tr>";
                    $msg .= "<td>" . $item["mensaje"] . "</td>";
                    $msg .= "<td>" . $item["creado"] . "</td>";
                    $msg .= "</tr>";
                }
                $msg .= "</table>";
            }
            if (!preg_match("/SE AGOTO TIEMPO/i", $last["mensaje"]) && !preg_match("/TIENE ERROR/i", $last["mensaje"]) && !preg_match("/SE CONCLUYO PAGO/i", $last["mensaje"]) && !preg_match("/TIMEOUT/i", $last["mensaje"])) {
                $this->_helper->json(array("message" => $msg, "success" => true));
            } else {
                $this->_helper->json(array("message" => $msg, "success" => false));
            }
        } else {
            $this->_helper->json(array("message" => "SIN RESPUESTA"));
        }
    }

    public function buscarArchivoAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            if (isset($post["search"])) {
                $table = new Operaciones_Model_ValidadorArchivos();
                $found = $table->buscarPorNombre($post["search"]);
                if ($found != false) {
                    if (preg_match("/M[0-9]{7}.([0-9]{3})/i", $post["search"])) {
                        $this->_helper->json(array("id" => $found, "success" => true, "validacion" => true));
                    } else {
                        $this->_helper->json(array("id" => $found, "success" => true, "validacion" => false));
                    }
                } else {
                    $this->_helper->json(array("success" => false));
                }
            }
        }
    }
    
    public function archivosAction() {
        $this->_helper->layout->setLayout("default");
        $this->_helper->viewRenderer->setNoRender(false);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->view->headLink(array("rel" => "icon shortcut", "href" => "/favicon.png"));
        $this->view->headLink()
                ->appendStylesheet("/js/common/bootstrap/css/bootstrap.min.css")
                ->appendStylesheet("/js/common/bootstrap/datepicker/css/datepicker.css")
                ->appendStylesheet("/css/fontawesome/css/fontawesome-all.min.css")
                ->appendStylesheet("/css/jqModal.css")
                ->appendStylesheet("/css/DT_bootstrap.css")
                ->appendStylesheet("/less/traffic-module.css?" . time());
        $this->view->headScript()
                ->appendFile("/js/common/jquery-1.9.1.min.js")
                ->appendFile("/js/common/bootstrap/js/bootstrap.min.js")
                ->appendFile("/js/common/jquery.form.min.js")
                ->appendFile("/js/common/jquery.validate.min.js")
                ->appendFile("/js/common/additional-methods.min.js")
                ->appendFile("/js/common/js.cookie.js")
                ->appendFile("/js/common/jquery.blockUI.js")
                ->appendFile("/js/common/jquery.dataTables.min.js")
                ->appendFile("/js/common/DT_bootstrap.js")
                ->appendFile("/js/common/jquery-linedtextarea.js")
                ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js")
                ->appendFile("/js/common/bootstrap/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js")
                ->appendFile("/js/common/jqModal.js")
                ->appendFile("/js/common/principal.js?" . time());
        $mapper = new Application_Model_MenuAccesos();
        $this->view->menu = $mapper->obtenerPorRol($this->_session->idRol);
        $this->view->username = $this->_session->username;
        $this->view->title = $this->_appconfig->getParam("title") . " Archivos de validación";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/js/operaciones/validador/archivos.js?" . time());
        $model = new Automatizacion_Model_ArchivosValidacionMapper();
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "patente" => "Digits",
            "aduana" => "Digits",
            "pedimento" => "Digits",
            "noCerrados" => "StringToLower",
        );
        $v = array(
            "patente" => array("NotEmpty", new Zend_Validate_Int()),
            "aduana" => array("NotEmpty", new Zend_Validate_Int()),
            "pedimento" => array("NotEmpty", new Zend_Validate_Int()),
            "nombreArchivo" => "NotEmpty",
            "fecha" => array("NotEmpty", new Zend_Validate_Regex("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/")),
            "noCerrados" => "NotEmpty",
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("pedimento")) {
            $this->view->pedimento = $input->pedimento;
            $arr = $model->buscarPedimento($input->pedimento);
        } elseif ($input->isValid("nombreArchivo")) {
            $this->view->nombreArchivo = $input->nombreArchivo;
            $arr = $model->buscarArchivo($input->nombreArchivo);            
        } elseif ($input->isValid("noCerrados")) {
            $past = date("Y-m-d H:i:s", strtotime($input->fecha . ' -31 days'));
            $arr = $model->pedimentosNoCerrados($past);
            $this->view->fecha = $input->fecha;
            $this->view->noCerrados = true;
        } elseif ($input->isValid("fecha")) {
            $arr = $model->obtenerPorFecha(null, null, $input->fecha);
            $this->view->fecha = $input->fecha;
        } else {
            $arr = $model->obtenerPorFecha(null, null, date("Y-m-d"));
            $this->view->fecha = date("Y-m-d");
        }
        if (isset($arr)) {
            $this->view->arr = $arr;
        }
    }

    public function abrirArchivoAction() {
        $this->_helper->viewRenderer->setNoRender(false);
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits"),
        );
        $v = array(
            "id" => array("NotEmpty", new Zend_Validate_Int()),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id")) {
            $model = new Automatizacion_Model_ArchivosValidacionMapper();
            $arr = $model->getFile($input->id);
            $this->view->arr = $arr;
        }
    }
    
    public function descargarArchivoAction() {
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits"),
        );
        $v = array(
            "id" => array("NotEmpty", new Zend_Validate_Int()),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id")) {
            $model = new Automatizacion_Model_ArchivosValidacionMapper();
            $arr = $model->getFile($input->id);
            if(isset($arr) && count($arr)) {
                header("Content-type: text/plain");
                header("Content-Disposition: attachment; filename={$arr["archivoNombre"]}");
                echo base64_decode($arr["contenido"]);
            }
        }
    }
    
    public function imprimirArchivoAction() {
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits"),
        );
        $v = array(
            "id" => array("NotEmpty", new Zend_Validate_Int()),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id")) {
            $model = new Automatizacion_Model_ArchivosValidacionMapper();
            $arr = $model->getFile($input->id);            
            if (isset($arr)) {            
                $validacion = new OAQ_Archivos_Validacion();
                $validacion->setPatente($arr["patente"]);
                $validacion->setAduana($arr["aduana"]);
                $validacion->setNombreArchivo($arr["archivoNombre"]);
                $validacion->setContenido(base64_decode($arr["contenido"]));
                $validacion->analizar();
            }
        }
    }

}
