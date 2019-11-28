<?php

class Automatizacion_ValidadorController extends Zend_Controller_Action {

    protected $_config;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
    }

    protected function _connectFtp($server) {
        $conn_id = ftp_connect($server["host"], $server["puerto"]);
        $login_result = ftp_login($conn_id, $server["usuario"], $server["password"]);
        if ((!$conn_id) || (!$login_result)) {
            return false;
        }
        return $conn_id;
    }

    protected function _downloadFile($conn_id, $remotefile, $localfile) {
        $download = ftp_get($conn_id, $localfile, $remotefile, FTP_BINARY);
        if ($download) {
            return true;
        }
        return false;
    }
    
    protected function _validador($patente, $aduana, $idUsuario = null) {
        $vv = new Operaciones_Model_Validador();
        if (isset($idUsuario) && !in_array((int) $idUsuario, array(149, 23))) {
            $server = $vv->validador($patente, $aduana);
            if ($server !== false) {
                return $server;
            }
        } else {
            /*return array(
                "patente" => 3589,
                "aduana" => 640,
                "validador" => "010",
                "host" => "prevalidador.alvaroquintana.com",
                "usuario" => "userOAQ",
                "password" => "HL1f67uF",
                "puerto" => 21,
                "carpeta" => "/640",
                "habilitado" => 1,
            );*/
            return array(
                "patente" => 3589,
                "aduana" => 640,
                "validador" => "010",
                "host" => "aaabac2.ddns.net",
                "usuario" => "ag3589",
                "password" => "lm3589",
                "puerto" => 21,
                "carpeta" => "/lm3589",
                "habilitado" => 1,
            );
        }
        return;
    }

    /**
     * /automatizacion/validador/validar-archivo?id=76
     * $cmd = 'curl -k -X GET "https://192.168.0.246/automatizacion/validador/validar-archivo?id=' . $id . '" > /dev/null';
     * 
     */
    public function validarArchivoAction() {
        try {
            $bitacora = new Operaciones_Model_ValidadorBitacora();
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
                "idUsuario" => array("Digits"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
                "idUsuario" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $val = new Operaciones_Model_ValidadorArchivos();
                $rss = new Operaciones_Model_ValidadorRespuestas();
                $model = new Application_Model_DirectoriosValidacion();
                $file = $val->obtener($input->id);
                $folder = $model->obtener($file["patente"], $file["aduana"]);
                $archVal = new OAQ_ArchivosValidacion();
                if (isset($folder)) {
                    if (file_exists($folder . DIRECTORY_SEPARATOR . $file["archivo"])) {
                        $server = $this->_validador($file["patente"], $file["aduana"], $input->isValid("idUsuario") ? $input->idUsuario : null);
                        /**                         * */
                        $ftp = new OAQ_Ftp(array(
                            "port" => $server["puerto"],
                            "host" => $server["host"],
                            "username" => $server["usuario"],
                            "password" => $server["password"],
                        ));
                        if (true !== ($conn = $ftp->connect())) {
                            $bitacora->agregar($input->id, "NO CONEXION A VALIDADOR");
                            $ftp->disconnect();
                            return;
                        } else {
                            $bitacora->agregar($input->id, "CONECTO CON VALIDADOR");
                            $ftp->setTimeout();
                        }
                        $ftp->setTransmission(FTP_BINARY);
                        if (file_exists($folder . DIRECTORY_SEPARATOR . $file["archivo"])) {
                            if ($ftp->upload($folder . DIRECTORY_SEPARATOR . $file["archivo"])) {
                                $bitacora->agregar($input->id, "ENVIANDO " . $file["archivo"]);
                            }
                        } else {
                            $bitacora->agregar($input->id, "NO EXISTE ARCHIVO " . $file["archivo"]);
                            $ftp->disconnect();
                            return;
                        }
                        $val->fueEnviado($input->id);
                        /*                         * ***** ****** */
                        $validador = new OAQ_Validador(array(
                            "filename" => $file["archivo"],
                            "directory" => $folder,
                        ));
                        $numIntentos = 25;
                        $respRespuesta = false;
                        $respValidacon = false;
                        for ($i = 0; $i < $numIntentos; $i++) {
                            $validador->validarArchivo($ftp);
                            if ($validador->get_respuesta() === true && $respRespuesta === false) {
                                $bitacora->agregar($input->id, "EXISTE RESP. VALIDADOR " . $validador->get_k());
                                $respRespuesta = true;
                                if (!($rss->verificar($input->id, $validador->get_k()))) {
                                    $rss->agregar($input->id, $validador->get_k(), $validador->contenidoArchivoBase64($validador->get_k()), strtolower($file["usuario"]));
                                    $archVal->agregarArchivoValidacion($file["patente"], $file["aduana"], $folder, $validador->get_k(), $validador->contenidoArchivoBase64($validador->get_k()), "resultado", strtolower($file["usuario"]));
                                }
                                usleep(500000);
                            }
                            if ($validador->get_validacion() === true && $respValidacon === false) {
                                $bitacora->agregar($input->id, "EXISTE VALIDACION " . $validador->get_m());
                                $respValidacon = true;
                                if (!($rss->verificar($input->id, $validador->get_m()))) {
                                    $rss->agregar($input->id, $validador->get_m(), $validador->contenidoArchivoBase64($validador->get_m()), strtolower($file["usuario"]), ($validador->get_error() == true) ? 1 : 0);
                                    $archVal->agregarArchivoValidacion($file["patente"], $file["aduana"], $folder, $validador->get_m(), $validador->contenidoArchivoBase64($validador->get_m()), "validacion", strtolower($file["usuario"]));
                                    if ($validador->get_error() === true) {
                                        $bitacora->agregar($input->id, "TIENE ERROR. " . $validador->get_m());
                                    }
                                }
                                usleep(500000);
                            }
                            if ($validador->get_respuesta() === false || $validador->get_validacion() === false) {
                                $bitacora->agregar($input->id, "INTENTO " . ($i + 1) . " de " . $numIntentos);
                                sleep(15);
                            }
                            if ($validador->get_respuesta() === true && $validador->get_validacion() === true) {
                                $bitacora->agregar($input->id, "SE CONCLUYO VALIDACION.");
                                $val->fueValidado($input->id);
                                break;
                            }
                        } // for
                        $ftp->disconnect();
                        $bitacora->agregar($input->id, "TIMEOUT.");
                    } // if file exists
                } // if folder isset
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            if (isset($ftp)) {
                $ftp->disconnect();
            }
            if (isset($bitacora) && isset($input)) {
                $bitacora->agregar($input->id, $ex->getMessage());
            }
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    /**
     * https://192.168.0.246/automatizacion/validador/validar-archivo?id=76
     * $cmd = 'curl -k -X GET "https://192.168.0.246/automatizacion/validador/validar-archivo?id=' . $id . '" > /dev/null';
     * 
     * @return boolean
     */
    public function reenviarArchivoAction() {
        try {
            $bitacora = new Operaciones_Model_ValidadorBitacora();
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
                "idUsuario" => array("Digits"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
                "idUsuario" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $val = new Operaciones_Model_ValidadorArchivos();
                $bitacora = new Operaciones_Model_ValidadorBitacora();
                $rss = new Operaciones_Model_ValidadorRespuestas();
                //$vv = new Operaciones_Model_Validador();
                $model = new Application_Model_DirectoriosValidacion();
                $file = $val->obtener($input->id);
                $folder = $model->obtener($file["patente"], $file["aduana"]);
                $archVal = new OAQ_ArchivosValidacion();
                if (isset($folder)) {
                    if (file_exists($folder . DIRECTORY_SEPARATOR . $file["archivo"])) {
                        //$server = $vv->validador($file["patente"], $file["aduana"]);
                        $server = $this->_validador($file["patente"], $file["aduana"], $input->isValid("idUsuario") ? $input->idUsuario : null);
                        $ftp = new OAQ_Ftp(array(
                            "port" => $server["puerto"],
                            "host" => $server["host"],
                            "username" => $server["usuario"],
                            "password" => $server["password"],
                        ));
                        if (true !== ($conn = $ftp->connect())) {
                            $bitacora->agregar($input->id, "NO CONEXION A VALIDADOR");
                            $ftp->disconnect();
                            return;
                        } else {
                            $bitacora->agregar($input->id, "CONECTO CON VALIDADOR");
                            $ftp->setTimeout();
                        }
                        $ftp->setTransmission(FTP_BINARY);
                        $validador = new OAQ_Validador(array(
                            "filename" => $file["archivo"],
                            "directory" => $folder,
                        ));
                        $numIntentos = 25;
                        $respRespuesta = false;
                        $respValidacion = false;
                        for ($i = 0; $i < $numIntentos; $i++) {
                            $validador->validarArchivo($ftp);
                            if ($validador->get_respuesta() === true && $respRespuesta === false) {
                                $bitacora->agregar($input->id, "EXISTE RESP. VALIDADOR " . $validador->get_k());
                                $respRespuesta = true;
                                if (!($rss->verificar($input->id, $validador->get_k()))) {
                                    $rss->agregar($input->id, $validador->get_k(), $validador->contenidoArchivoBase64($validador->get_k()), strtolower($file["usuario"]));
                                    $archVal->agregarArchivoValidacion($file["patente"], $file["aduana"], $folder, $validador->get_k(), $validador->contenidoArchivoBase64($validador->get_k()), "resultado", strtolower($file["usuario"]));
                                }
                                usleep(500000);
                            }
                            if ($validador->get_validacion() === true && $respValidacion === false) {
                                $bitacora->agregar($input->id, "EXISTE VALIDACIÓN " . $validador->get_m());
                                $respValidacion = true;
                                if (!($rss->verificar($input->id, $validador->get_m()))) {
                                    $rss->agregar($input->id, $validador->get_m(), $validador->contenidoArchivoBase64($validador->get_m()), strtolower($file["usuario"]), ($validador->get_error() == true) ? 1 : 0);
                                    $archVal->agregarArchivoValidacion($file["patente"], $file["aduana"], $folder, $validador->get_m(), $validador->contenidoArchivoBase64($validador->get_m()), "validacion", strtolower($file["usuario"]));
                                    if ($validador->get_error() === true) {
                                        $bitacora->agregar($input->id, "TIENE ERROR. " . $validador->get_m());
                                    }
                                }
                                usleep(500000);
                            }
                            if ($validador->get_respuesta() === false || $validador->get_validacion() === false) {
                                $bitacora->agregar($input->id, "INTENTO " . ($i + 1) . " de " . $numIntentos);
                                sleep(15);
                            }
                            if ($validador->get_respuesta() === true && $validador->get_validacion() === true) {
                                $bitacora->agregar($input->id, "SE CONCLUYO VALIDACION.");
                                $val->fueValidado($input->id);
                                break;
                            }
                        } // for
                        $bitacora->agregar($input->id, "TIMEOUT.");
                        $ftp->disconnect();
                        $this->_helper->json(array("success" => false));
                    } // if file exists
                } // if folder isset
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            if (isset($ftp)) {
                $ftp->disconnect();
            }
            if (isset($bitacora) && isset($input)) {
                $bitacora->agregar($input->id, $ex->getMessage());
            }
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    /**
     * https://192.168.0.246/automatizacion/validador/validar-archivo?id=76
     * $cmd = 'curl -k -X GET "https://192.168.0.246/automatizacion/validador/validar-archivo?id=' . $id . '" > /dev/null';
     * 
     */
    public function pagarArchivoAction() {
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits"),
            "idUsuario" => array("Digits"),
        );
        $v = array(
            "id" => array("NotEmpty", new Zend_Validate_Int()),
            "idUsuario" => array("NotEmpty", new Zend_Validate_Int()),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id")) {
            $sender = new OAQ_Workers_ArchivosValidacionSender();
            $val = new Operaciones_Model_ValidadorArchivos();
            $bitacora = new Operaciones_Model_ValidadorBitacora();
            $rss = new Operaciones_Model_ValidadorRespuestas();
            $model = new Application_Model_DirectoriosValidacion();
            $archVal = new OAQ_ArchivosValidacion();
            $file = $val->obtener($input->id);
            $folder = $model->obtener($file["patente"], $file["aduana"]);
            try {
                if (isset($folder)) {
                    if (file_exists($folder . DIRECTORY_SEPARATOR . $file["archivo"])) {
                        $server = $this->_validador($file["patente"], $file["aduana"], $input->isValid("idUsuario") ? $input->idUsuario : null);
                        $ftp = new OAQ_Ftp(array(
                            "port" => $server["puerto"],
                            "host" => $server["host"],
                            "username" => $server["usuario"],
                            "password" => $server["password"],
                        ));
                        if (true !== ($conn = $ftp->connect())) {
                            $bitacora->agregar($input->id, "NO CONEXION A VALIDADOR");
                            $ftp->disconnect();
                            return;
                        } else {
                            $bitacora->agregar($input->id, "CONECTO CON VALIDADOR");
                            $ftp->setTimeout();
                        }
                        $ftp->setTransmission(FTP_BINARY);
                        if (file_exists($folder . DIRECTORY_SEPARATOR . $file["archivo"])) {
                            if (($up = $ftp->upload($folder . DIRECTORY_SEPARATOR . $file["archivo"]))) {
                                $bitacora->agregar($input->id, "ENVIANDO PAGO " . $file["archivo"]);
                            }
                        } else {
                            $bitacora->agregar($input->id, "NO EXISTE ARCHIVO " . $file["archivo"]);
                            $ftp->disconnect();
                            return;
                        }
                        $val->fueEnviado($input->id);
                        $validador = new OAQ_Validador(array(
                            "filename" => $file["archivo"],
                            "directory" => $folder,
                        ));
                        usleep(1000000);
                        $numIntentos = 25;
                        for ($i = 0; $i <= $numIntentos; $i++) {
                            $validador->pagarArchivo($ftp);
                            if ($validador->get_pago() === true) {
                                $bitacora->agregar($input->id, "EXISTE EN VALIDADOR PAGO " . $validador->get_a());
                                usleep(500000);
                                if (!($rss->verificar($input->id, $validador->get_a()))) {
                                    $ap = $rss->agregar($input->id, $validador->get_a(), $validador->contenidoArchivoBase64($validador->get_a()), strtolower($file["usuario"]));
                                    if (isset($ap["id"])) {
                                        $sender->archivosDePago($ap["id"]);
                                    }
                                    $archVal->agregarArchivoValidacion($file["patente"], $file["aduana"], $folder, $validador->get_a(), $validador->contenidoArchivoBase64($validador->get_a()), "pagado", strtolower($file["usuario"]));
                                }
                            }
                            if ($validador->get_pago() === false) {
                                $bitacora->agregar($input->id, "INTENTO " . ($i + 1) . " de " . $numIntentos);
                                sleep(10);
                            }
                            if ($validador->get_pago() === true) {
                                $ftp->disconnect();
                                $bitacora->agregar($input->id, "SE CONCLUYO PAGO.");                                
                                $val->fuePagado($input->id);
                                break;
                            }
                        } // for
                        $ftp->disconnect();
                        $bitacora->agregar($input->id, "TIMEOUT.");
                    }
                }
            } catch (Exception $ex) {
                if (isset($ftp)) {
                    $ftp->disconnect();
                }
                $bitacora->agregar($input->id, $ex->getMessage());
            }
        }
    }

    public function servidorValidadorAction() {
        try {
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "patente" => array("Digits"),
                    "aduana" => array("Digits"),
                    "idUsuario" => array("Digits"),
                );
                $v = array(
                    "patente" => array("NotEmpty", new Zend_Validate_Int()),
                    "aduana" => array("NotEmpty", new Zend_Validate_Int()),
                    "idUsuario" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("patente") && $input->isValid("aduana")) {
                    $server = $this->_validador($input->patente, $input->aduana, $input->isValid("idUsuario") ? $input->idUsuario : null);
                    $ftp = new OAQ_Ftp(array(
                        "port" => $server["puerto"],
                        "host" => $server["host"],
                        "username" => $server["usuario"],
                        "password" => $server["password"],
                    ));
                    if (true !== ($conn = $ftp->connect())) {
                        $ftp->disconnect();
                        return;
                    } else {
                        $ftp->setTimeout();
                    }
                    if (!preg_match("/^\/$/", $server["carpeta"]) && !preg_match("/^\/ag3589$/", $server["carpeta"])) {
                        $ftp->changeRemoteDirectory($server["carpeta"]);
                    }
                    $rawlist = $ftp->rawList(".");
                    $a = 0;
                    $files = array();
                    if (count($rawlist)) {
                        foreach ($rawlist as $line) {
                            $out = explode(' ', preg_replace('/\s+/', ' ', $line));
                            $a++;
                            $files[$a]['rights'] = $out[0];
                            $files[$a]['owner'] = $out[2];
                            $files[$a]['group'] = $out[3];
                            $files[$a]['size'] = (int) $out[4];
                            $files[$a]['date'] = $out[7];
                            $files[$a]['name'] = $out[8];
                        }
                    }
                    $view = new Zend_View();
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/validador/");
                    $view->files = $files;
                    $view->patente = $input->patente;
                    $view->aduana = $input->aduana;
                    $ftp->disconnect();
                    $this->_helper->json(array("success" => true, "html" => $view->render("archivos-validador.phtml")));
                }
            }
            $this->_helper->json(array("success" => false));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function archivosValidadorAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "patente" => array("Digits"),
                    "aduana" => array("Digits"),
                    "idUsuario" => array("Digits"),
                );
                $v = array(
                    "patente" => array("NotEmpty", new Zend_Validate_Int()),
                    "aduana" => array("NotEmpty", new Zend_Validate_Int()),
                    "idUsuario" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("patente") && $input->isValid("aduana")) {
                    $server = $this->_validador($input->patente, $input->aduana, $input->isValid("idUsuario") ? $input->idUsuario : null);
                    $ftp = new OAQ_Ftp(array(
                        "port" => $server["puerto"],
                        "host" => $server["host"],
                        "username" => $server["usuario"],
                        "password" => $server["password"],
                    ));
                    if (true !== ($conn = $ftp->connect())) {
                        $ftp->disconnect();
                        return;
                    } else {
                        $ftp->setTimeout();
                    }
                    if (!preg_match("/^\/$/", $server["carpeta"]) && !preg_match("/^\/ag3589$/", $server["carpeta"])) {
                        $ftp->changeRemoteDirectory($server["carpeta"]);
                    }
                    $rawlist = $ftp->rawList(".");
                    $a = 0;
                    $files = array();
                    if (count($rawlist)) {
                        foreach ($rawlist as $line) {
                            $out = explode(' ', preg_replace('/\s+/', ' ', $line));
                            $a++;
                            $files[$a]['rights'] = $out[0];
                            $files[$a]['owner'] = $out[2];
                            $files[$a]['group'] = $out[3];
                            $files[$a]['size'] = (int) $out[4];
                            $files[$a]['date'] = $out[7];
                            $files[$a]['name'] = $out[8];
                        }
                    }
                    $view = new Zend_View();
                    $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/validador/");
                    $view->files = $files;
                    $view->patente = $input->patente;
                    $view->aduana = $input->aduana;
                    $ftp->disconnect();
                    $this->_helper->json(array("success" => true, "html" => $view->render("archivos-validador.phtml")));
                }
            }
            $this->_helper->json(array("success" => false));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function descargaUnArchivoAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "patente" => array("Digits"),
                    "aduana" => array("Digits"),
                );
                $v = array(
                    "patente" => array("NotEmpty", new Zend_Validate_Int()),
                    "aduana" => array("NotEmpty", new Zend_Validate_Int()),
                    "archivo" => array("NotEmpty"),
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("patente") && $i->isValid("aduana") && $i->isValid("archivo")) {
                    $mdl = new Operaciones_Model_Validador();
                    $model = new Application_Model_DirectoriosValidacion();
                    $folder = $model->obtener($i->patente, $i->aduana);
                    $server = $mdl->validador($i->patente, $i->aduana);
                    $ftp = new OAQ_Ftp(array(
                        "port" => $server["puerto"],
                        "host" => $server["host"],
                        "username" => $server["usuario"],
                        "password" => $server["password"],
                    ));
                    if (true !== ($conn = $ftp->connect())) {
                        $ftp->disconnect();
                        return;
                    } else {
                        $ftp->setTimeout();
                    }
                    $file = '/' . $i->archivo;
                    $res = $ftp->ftpSize($file);
                    if ($res > 1) {
                        $ftp->download($folder . DIRECTORY_SEPARATOR . $i->archivo, $i->archivo);
                        if (file_exists($folder . DIRECTORY_SEPARATOR . $i->archivo) && filesize($folder . DIRECTORY_SEPARATOR . $i->archivo) > 0) {
                            $download = true;
                        } else {
                            $this->_helper->json(array("success" => false, "message" => "El archivo no se pudo descargar, vuelva a intentar."));
                        }
                    }
                    $ftp->disconnect();
                    if (isset($download) && $download === true) {
                        $this->_helper->json(array("success" => true, "directorio" => $folder . DIRECTORY_SEPARATOR . $i->archivo));
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
    
    public function listenPagoAction() {
        if (APPLICATION_ENV === "development") {
            set_time_limit(30);
        }
        $listen = new OAQ_Workers_ArchivosValidacionReceiver();
        $listen->listenPagos();
    }

}
