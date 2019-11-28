<?php

class Clientes_GetController extends Zend_Controller_Action {

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
    
    public function reporteIvaAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "mes" => "Digits",
                "year" => "Digits",
                "aduana" => "Digits",
                "download" => array("StringToLower"),
            );
            $v = array(
                "mes" => new Zend_Validate_Int(),
                "year" => new Zend_Validate_Int(),
                "aduana" => new Zend_Validate_Int(),
                "download" => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("mes") && $input->isValid("year") && $input->isValid("aduana")) {
                if ($input->aduana == 1) {
                    $patente = 3589;
                    $aduana = 640;
                }
                if ($input->aduana == 2) {
                    $patente = 3589;
                    $aduana = 240;
                }
                if ($input->aduana == 3) {
                    $patente = 3589;
                    $aduana = 800;
                }
                $misc = new OAQ_Misc();
                $db = $misc->sitawinTrafico($patente, $aduana);
                if (isset($db)) {
                    $result = $db->reporteIvaProveedores($this->_session->username, $input->year, $input->mes);
                    $download = filter_var($input->download, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                    if (isset($result) && !empty($result)) {
                        $view = new Zend_View();
                        $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                        if ($download === false) {
                            $view->aduana = $input->aduana;
                            $view->year = $input->year;
                            $view->mes =  $input->mes;
                            $view->result = $result;
                            echo $view->render("reporte-iva.phtml");
                        } else {
                            $reportes = new OAQ_ExcelReportes();
                            $reportes->reportesTrafico(100, $result);
                            return;
                        }
                    }
                } else {
                    throw new Exception("No database found!");
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Zend_Exception($ex->getMessage());
        }
    }
    
    public function descargaM3Action() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
            );
            $v = array(
                "fechaIni" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/"), "default" => date("Y-m-" . "01")),
                "fechaFin" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/"), "default" => date("Y-m-d")),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("fechaIni") && $input->isValid("fechaFin")) {
                $mapper = new Automatizacion_Model_ArchivosValidacionPedimentosMapper();
                $result = $mapper->pedimentosPagadosRango(trim(strtoupper($this->_session->username)), $input->fechaIni, $input->fechaFin);
                if (APPLICATION_ENV == "production" || APPLICATION_ENV == "staging") {
                    $zipfile = "/tmp" . DIRECTORY_SEPARATOR . sha1(time()) . ".zip";
                } else {
                    $zipfile = "C:\\wamp64\\tmp" . DIRECTORY_SEPARATOR . sha1(time()) . ".zip";
                }
                $zip = new ZipArchive;
                $res = $zip->open($zipfile, ZipArchive::CREATE);
                if ($res === true) {
                    foreach ($result as $item) {
                        if (isset($item["m3"]["contenido"])) {
                            $zip->addFromString($item["m3"]["archivoNombre"], base64_decode($item["m3"]["contenido"]));
                        }
                        if (isset($item["pago"]["contenido"])) {
                            $zip->addFromString($item["pago"]["archivoNombre"], base64_decode($item["pago"]["contenido"]));
                        }
                        if (isset($item["firma"]["contenido"])) {
                            $zip->addFromString($item["firma"]["archivoNombre"], base64_decode($item["firma"]["contenido"]));
                        }
                    }
                    $zip->close();
                    header("Cache-Control: public");
                    header("Content-Description: File Transfer");
                    header("Content-Disposition: attachment; filename=" . pathinfo($zipfile, PATHINFO_BASENAME) . "");
                    header("Content-length: " . filesize($zipfile));
                    header("Content-Transfer-Encoding: binary");
                    header("Content-Type: binary/octet-stream");
                    readfile($zipfile);
                    unlink($zipfile);
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Zend_Exception($ex->getMessage());
        }
    }
    
    public function reporteAction() {
        try {
            date_default_timezone_set('America/Mexico_City');
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(false);
            ini_set("soap.wsdl_cache_enabled", 0);
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idAduana" => "Digits",
                "idCliente" => "Digits",
                "year" => "Digits",
                "month" => "Digits",
            );
            $v = array(
                "idAduana" => array("Digits", new Zend_Validate_Int()),
                "idCliente" => array("Digits", new Zend_Validate_Int()),
                "year" => array("Digits", new Zend_Validate_Int(),
                    new Zend_Validate_Between(
                            array(
                        "min" => 2012,
                        "max" => 2025,
                        "inclusive" => true
                            )
                    )
                ),
                "month" => array("Digits", new Zend_Validate_Int(),
                    new Zend_Validate_Between(
                            array(
                        "min" => 1,
                        "max" => 12,
                        "inclusive" => true
                            )
                    )
                ),
                "rfc" => new Zend_Validate_StringLength(array("max" => 25)),
                "tipo" => new Zend_Validate_StringLength(array("max" => 25)),
                "fechaIni" => new Zend_Validate_Date(),
                "fechaFin" => new Zend_Validate_Date(),
            );
            
            $reportes = new OAQ_Reportes();
            
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("tipo")) {

                $viewsFolder = realpath(dirname(__FILE__)) . "/../views/scripts/get/";
                $view = new Zend_View();
                $view->setScriptPath($viewsFolder);
                
                $layout = $reportes->obtenerLayout($input->tipo);
                $view->titulos = $reportes->anexoHeaders($input->tipo);
                $view->tipo = $input->tipo;
                
                $rows = $reportes->obtenerDatos($input->tipo, $input->idAduana, $reportes->rfcCliente($input->idCliente), $input->fechaIni, $input->fechaFin);
                if (isset($rows)) {
                    $view->data = $rows;
                }
                $this->view->content = $view->render($layout);
                
                $this->view->type = $input->tipo;
                $this->view->excelLink = str_replace('/clientes/get/reporte?', '', $_SERVER["REQUEST_URI"]);
                
            } else {
                $this->view->error = "Los parámetros de consulta no son correctos.";
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("errorMsg" => $ex->getMessage()));
        }
    }

    public function excelAction() {
        try {
            date_default_timezone_set('America/Mexico_City');
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            ini_set("soap.wsdl_cache_enabled", 0);
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idAduana" => "Digits",
                "idCliente" => "Digits",
                "year" => "Digits",
                "month" => "Digits",
            );
            $v = array(
                "idAduana" => array("Digits", new Zend_Validate_Int()),
                "idCliente" => array("Digits", new Zend_Validate_Int()),
                "year" => array("Digits", new Zend_Validate_Int(),
                    new Zend_Validate_Between(
                            array(
                        "min" => 2012,
                        "max" => 2025,
                        "inclusive" => true
                            )
                    )
                ),
                "month" => array("Digits", new Zend_Validate_Int(),
                    new Zend_Validate_Between(
                            array(
                        "min" => 1,
                        "max" => 12,
                        "inclusive" => true
                            )
                    )
                ),
                "rfc" => new Zend_Validate_StringLength(array("max" => 25)),
                "tipo" => new Zend_Validate_StringLength(array("max" => 25)),
                "fechaIni" => new Zend_Validate_Date(),
                "fechaFin" => new Zend_Validate_Date(),
            );
            
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("tipo")) {

                $reportes = new OAQ_Reportes();
                $headers = $reportes->anexoHeaders($input->tipo);
                $rows = $reportes->obtenerDatos($input->tipo, $input->idAduana, $reportes->rfcCliente($input->idCliente), $input->fechaIni, $input->fechaFin);                
                $reports = new OAQ_ExcelReportes();
                $titles = array();
                foreach($headers["titulos"] as $k => $v) {
                    $titles[] = $k;
                }
                $reports->reportesOperaciones($input->tipo, $input->fechaIni, $input->fechaFin, $titles, $rows);
                
            } else {
                $this->view->error = "Los parámetros de consulta no son correctos.";
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("errorMsg" => $ex->getMessage()));
        }
    }    

    public function imageAction() {
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
            $this->_helper->json(array("errorMsg" => $ex->getMessage()));
        }
    }

    public function downloadPhotosAction() {
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
                $files = $mppr->obtenerTodas($input->id);
                $zipName = 'FOTOS_' . md5(microtime()) . '.zip';
                if (APPLICATION_ENV == "production") {
                    $zipFilename = '/tmp' . DIRECTORY_SEPARATOR . $zipName;
                } else {
                    $zipFilename = 'C:\\wamp64\\tmp' . DIRECTORY_SEPARATOR . $zipName;
                }
                $zip = new ZipArchive();
                if ($zip->open($zipFilename, ZIPARCHIVE::CREATE) !== TRUE) {
                    return null;
                }
                foreach ($files as $file) {
                    $image_file = $file["carpeta"] . DIRECTORY_SEPARATOR . $file["imagen"];
                    if (file_exists($image_file)) {
                        $zip->addFile($image_file, basename($file["nombre"]));
                    }
                }
                $zip->close();
                if (file_exists($zipFilename)) {
                    if (!is_file($zipFilename)) {
                        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
                        echo 'File not found';
                    } else if (!is_readable($zipFilename)) {
                        header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
                        echo 'File not readable';
                    }
                    header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
                    header("Content-Type: application/zip");
                    header("Content-Transfer-Encoding: Binary");
                    header("Content-Length: " . filesize($zipFilename));
                    header("Content-Disposition: attachment; filename=\"" . basename($zipFilename) . "\"");
                    readfile($zipFilename);
                    if (APPLICATION_ENV == "production") {
                        unlink($zipFilename);
                    }
                }
            } else {
                throw new Exception("Invalid input");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("errorMsg" => $ex->getMessage()));
        }
    }
    
    public function proveedoresAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {            
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "page" => array("Digits"),
                "rows" => array("Digits"),
                "excel" => array("StringToLower"),
            );            
            $v = array(
                "page" => array(new Zend_Validate_Int(), "default" => 1),
                "rows" => array(new Zend_Validate_Int(), "default" => 30),
                "filterRules" => "NotEmpty",
                "excel" => array("NotEmpty"),
            );
            
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            
            $mppr = new Clientes_Model_Clientes();
            $idCliente = $mppr->obtenerId($this->_session->username);
            
            if ($idCliente) {
                
                $dexcel = filter_var($input->excel, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);                
                $mapper = new Clientes_Model_FactPro();
                
                if ($dexcel == false) {
                    $arr = $mapper->obtenerPorCliente($idCliente, $input->page, $input->rows, $input->filterRules);
                    if (isset($arr)) {
                        $this->_helper->json($arr);
                    } else {
                        $this->_helper->json(array("total" => 0, "rows" => array()));
                    }
                } else {
                    $arr = $mapper->obtenerPorCliente($idCliente);                    
                    $reportes = new OAQ_ExcelReportes();
                    $reportes->reportesTrafico(83, $arr['rows']);
                }
            }            
        } catch (Exception $ex) {
            $this->_helper->json(array("errorMsg" => $ex->getMessage()));
        }
    }    
    
    public function traficoAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {            
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "page" => array("Digits"),
                "rows" => array("Digits"),
                "excel" => array("StringToLower"),
            );            
            $v = array(
                "page" => array(new Zend_Validate_Int(), "default" => 1),
                "rows" => array(new Zend_Validate_Int(), "default" => 30),
                "fechaIni" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}-\d{2}-\d{2}$/"), "default" => date('Y-m-d')),
                "fechaFin" => array("NotEmpty", new Zend_Validate_Regex("/^\d{4}-\d{2}-\d{2}$/"), "default" => date('Y-m-d')),
                "filterRules" => "NotEmpty",
                "excel" => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("fechaIni") && $input->isValid("fechaFin")) {
                $model = new Clientes_Model_Traficos();
                $rows = $model->obtenerTraficoCliente($this->_session->username, $input->fechaIni, $input->fechaFin);
                $arr = array(
                    "total" => $model->totalTraficoCliente($this->_session->username, $input->fechaIni, $input->fechaFin),
                    "rows" => empty($rows) ? array() : $rows,
                );
                $this->_helper->json($arr);
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerComentariosAction() {
        try {
            $f = array(
                "id_trafico" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags(), new Zend_Filter_Digits()),
            );
            $v = array(
                "id_trafico" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id_trafico")) {
                $trafico = new OAQ_Trafico(array("idTrafico" => $input->id_trafico, "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));
                $arr = array();
                $arr['bitacora'] = $trafico->obtenerBitacora();
                $arr['comentarios'] = $trafico->obtenerComentarios();
                $arr['archivos'] = $trafico->obtenerArchivosComentarios();
                $this->_helper->json(array("success" => true, "results" => $arr));
            } else {
                throw new Exception("Invalid input");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerArchivosAction() {
        try {
            $f = array(
                "id_trafico" => array(new Zend_Filter_StringTrim(), new Zend_Filter_StripTags(), new Zend_Filter_Digits()),
            );
            $v = array(
                "id_trafico" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id_trafico")) {
                
                $mppr = new Trafico_Model_TraficosMapper();
                $array = $mppr->obtenerPorId($input->id_trafico);

                $repo = new Archivo_Model_RepositorioMapper();
                $archivos = $repo->obtenerArchivosReferencia($array["referencia"]);
//                $view->archivos = $archivos;

                /*$trafico = new OAQ_Trafico(array("idTrafico" => $input->id_trafico, "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));
                $index = $trafico->verificarIndexRepositorios();

                if (in_array($this->_session->role, array("super", "gerente", "trafico_ejecutivo", "trafico"))) {
                    $view->canDelete = true;
                }
                $val = new OAQ_ArchivosValidacion();
                if (isset($array["pedimento"])) {
                    $view->validacion = $val->archivosDePedimento($array["patente"], $array["aduana"], $array["pedimento"]);
                }*/
                $arr = array();
                foreach ($archivos as $item) {
                    if (!in_array($item['tipo_archivo'], array(99, 29, 89, 2001, 9999))) {
                        $arr[] = array(
                            'id' => (int) $item['id'], 
                            'exists' => (file_exists($item["ubicacion"])) ? true : false, 
                            'nom_archivo' => $item['nom_archivo'],
                            'tipo_archivo' => (int) $item['tipo_archivo'],
                            'creado' => $item['creado'],
                            'usuario' => $item['usuario']
                        );
                    }
                }
                $this->_helper->json(array("success" => true, "results" => $arr));
            } else {
                throw new Exception("Invalid input");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerTiposArchivosAction() {
        try {
            $mppr = new Archivo_Model_DocumentosMapper();
            $rows = $mppr->obtenerTodos();
            $arr = array();
            foreach ($rows as $item) {
                if (!in_array($item['id'], array(99, 29, 89, 2001, 9999))) {
                    $arr[(int) $item['id']] = array('id' => (int) $item['id'], 'nombre' => $item['nombre']);
                }
            }
            $this->_helper->json(array("success" => true, "results" => $arr));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

}
