<?php

class Clientes_ExpedienteController extends Zend_Controller_Action {

    protected $_soapClient;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;

    public function init() {
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper('Redirector');
        $this->view->headLink(array("rel" => "icon shortcut", "href" => "/favicon.png"));
        $this->view->headLink()
                ->appendStylesheet("/js/common/bootstrap/css/bootstrap.min.css")
                ->appendStylesheet("/js/common/bootstrap/datepicker/css/datepicker.css")
                ->appendStylesheet("/css/DT_bootstrap.css")
                ->appendStylesheet("/less/traffic-module.css?" . time());
        $this->view->headScript()
                ->appendFile("/js/common/js.cookie.js")
                ->appendFile("/js/common/jquery-1.9.1.min.js")
                ->appendFile("/js/common/bootstrap/js/bootstrap.min.js")
                ->appendFile("/js/common/bootstrap/datepicker/js/bootstrap-datepicker.js")
                ->appendFile("/js/common/jquery.form.min.js")
                ->appendFile("/js/common/jquery.validate.min.js")
                ->appendFile("/js/common/jquery.dataTables.min.js")
                ->appendFile("/js/common/DT_bootstrap.js")
                ->appendFile("/js/common/jquery.blockUI.js");
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
    }

    public function preDispatch() {
        $this->_helper->layout->setLayout("expediente/default");
        if (APPLICATION_ENV == "development") {
            $this->view->browser_sync = "<script async src='http://{$this->_config->app->browser_sync}/browser-sync/browser-sync-client.js?v=2.26.7'><\/script>";
        }
    }

    public function indexAction() {
        $this->view->title = $this->_appconfig->getParam('title') . ' ' . " Clientes";
        $this->view->headMeta()->appendName('description', '');
        $f = array(
            "*" => array("StringTrim", "StripTags"),
        );
        $v = array(
            "code" => array("NotEmpty"),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("code")) {
            $mppr = new Archivo_Model_RepositorioPermalinks();
            if (($arr = $mppr->buscarPermalink($input->code))) {
                $mapper = new Archivo_Model_RepositorioIndex();
                $arr = $mapper->datos($arr["idRepositorio"]);
                $model = new Archivo_Model_RepositorioMapper();
                $files = $model->getFilesByReferenceCustomers($arr["referencia"], $arr["patente"], $arr["aduana"]);
                $this->view->files = $files;
                $this->view->patente = $arr["patente"];
                $this->view->aduana = $arr["aduana"];
                $this->view->pedimento = $arr["pedimento"];
                $this->view->referencia = $arr["referencia"];
                $this->view->code = $input->code;
            }
        } else {
            throw new Exception("Invalid input!");
        }
    }

    public function archivosAction() {
        $this->view->title = $this->_appconfig->getParam('title') . ' ' . " Archivos";
        $this->view->headMeta()->appendName('description', '');$this->view->headScript()
                ->appendFile("/js/clientes/expediente/archivos.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
        );
        $v = array(
            "code" => array("NotEmpty"),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("code")) {
            
            $this->view->code = $input->code;
            
            $mppr = new Archivo_Model_RepositorioPermalinks();
            if (($arr = $mppr->buscarPermalink($input->code))) {
                
                $traffic = new OAQ_Trafico(array("idTrafico" => $arr['idTrafico']));
                
                $model = new Archivo_Model_RepositorioMapper();
                if ($traffic->getRfcCliente() == 'STE071214BE7') {
                    $file_type = array(2, 3, 40, 33, 34, 23, 438, 1010, 1020, 1030);
                    $files = $model->getFilesByReferenceCustomers($traffic->getReferencia(), $traffic->getPatente(), $traffic->getAduana(), $file_type);
                } else {
                    $files = $model->getFilesByReferenceCustomers($traffic->getReferencia(), $traffic->getPatente(), $traffic->getAduana());
                }
                $this->view->files = $files;
                $this->view->patente = $traffic->getPatente();
                $this->view->aduana = $traffic->getAduana();
                $this->view->pedimento = $traffic->getPedimento();
                $this->view->referencia = $traffic->getReferencia();
                
                $gallery = new Trafico_Model_Imagenes();
                $this->view->gallery = $gallery->miniaturas($arr['idTrafico']);

                $val = new OAQ_ArchivosValidacion();                
                $arr_val = $val->archivosDePedimento($traffic->getPatente(), $traffic->getAduana(), $traffic->getPedimento());
                $this->view->validacion = $arr_val;
                
            }
            
        } else {
            throw new Exception("Invalid input!");
        }
    }
    
    public function descargarArchivoAction() {        
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $f = array(
                "id" => array("Digits", "StringTrim", "StripTags"),
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $mppr = new Archivo_Model_RepositorioMapper();
                $arr = $mppr->getFileById($input->id);
                $tmpDir = $this->_appconfig->getParam("tmpDir");
                if (is_readable($arr["ubicacion"]) && file_exists($arr["ubicacion"])) {
                    $sha = sha1_file($arr["ubicacion"]);
                    copy($arr["ubicacion"], $tmpDir . DIRECTORY_SEPARATOR . $sha);
                    $basename = basename($arr["ubicacion"]);
                }
                if ((int) $arr["tipo_archivo"] == 22 && preg_match("/.xml$/i", $arr["nom_archivo"])) {
                    $misc = new OAQ_Misc();
                    $xml = file_get_contents($arr["ubicacion"], $tmpDir . DIRECTORY_SEPARATOR . $sha);
                    $cleanXml = $misc->removeSecurityHeaders($xml);
                    header("Content-Type: application/octet-stream");
                    header("Content-Transfer-Encoding: Binary");
                    header("Content-Length: " . strlen($cleanXml));
                    header("Content-disposition: attachment; filename=\"" . $basename . "\"");
                    echo $cleanXml;
                } elseif ((int) $arr["tipo_archivo"] == 56 && preg_match("/.xml$/i", $arr["nom_archivo"])) {
                    header("Content-Type: application/octet-stream");
                    header("Content-Transfer-Encoding: Binary");
                    header("Content-Length: " . filesize($tmpDir . DIRECTORY_SEPARATOR . $sha));
                    header("Content-disposition: attachment; filename=\"" . $basename . "\"");
                    readfile($tmpDir . DIRECTORY_SEPARATOR . $sha);
                } else {
                    header("Content-Type: application/octet-stream");
                    header("Content-Transfer-Encoding: Binary");
                    header("Content-Length: " . filesize($tmpDir . DIRECTORY_SEPARATOR . $sha));
                    header("Content-disposition: attachment; filename=\"" . $basename . "\"");
                    readfile($tmpDir . DIRECTORY_SEPARATOR . $sha);
                }
                unlink($tmpDir . DIRECTORY_SEPARATOR . $sha);
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function descargarCarpetaAction() {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $f = array(
                "*" => array("StringTrim", "StripTags"),
            );
            $v = array(
                "code" => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("code")) {
                
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Zend_Exception $e) {
            throw new Zend_Exception($e->getMessage());
        }
    }

    public function descargarArchivosTraficoAction() {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $f = array(
                "*" => array("StringTrim", "StripTags"),
            );
            $v = array(
                "code" => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("code")) {
            
                $mppr = new Archivo_Model_RepositorioPermalinks();
                if (($arr = $mppr->buscarPermalink($input->code))) {

                    $traffic = new OAQ_Trafico(array("idTrafico" => $arr['idTrafico']));
                    
                    $model = new Archivo_Model_RepositorioMapper();
                    if ($traffic->getRfcCliente() == 'STE071214BE7') {
                        $file_type = array(2, 3, 40, 33, 34, 23, 438, 1010, 1020, 1030);
                        $files = $model->getFilesByReferenceCustomers($traffic->getReferencia(), $traffic->getPatente(), $traffic->getAduana(), $file_type);
                    } else {
                        $files = $model->getFilesByReferenceCustomers($traffic->getReferencia(), $traffic->getPatente(), $traffic->getAduana());
                    }
                    
                    $gallery = new Trafico_Model_Imagenes();
                    $images = $gallery->obtenerTodas($arr['idTrafico']);
                    
                    $misc = new OAQ_Misc();
                    $exp = new OAQ_Expediente_Descarga();
                    
                    if (count($files)) {
                        $zipName = $exp->zipFilename($traffic->getPatente(), $traffic->getAduana(), $traffic->getPedimento(), $misc->limpiarNombreReferencia($traffic->getReferencia()), $traffic->getRfcCliente());
                        $zipDir = "D:\\xampp\\tmp\\zips";
                        if (APPLICATION_ENV === "production" || APPLICATION_ENV === "staging") {
                            $zipDir = "/tmp/zips";
                        }
                        if (!file_exists($zipDir)) {
                            mkdir($zipDir, 0777, true);
                        }
                        $zipFilename = $zipDir . DIRECTORY_SEPARATOR . $zipName;
                        if (file_exists($zipFilename)) {
                            unlink($zipFilename);
                        }
                        $zip = new ZipArchive();
                        if ($zip->open($zipFilename, ZIPARCHIVE::CREATE) !== TRUE) {
                            return null;
                        }
                        foreach ($files as $file) {
                            if (file_exists($file["ubicacion"])) {
                                $tmpfile = $zipDir . DIRECTORY_SEPARATOR . sha1($file["ubicacion"]);
                                copy($file["ubicacion"], $tmpfile);
                                if (($zip->addFile($tmpfile, $exp->filename($traffic->getPatente(), $traffic->getAduana(), $traffic->getPedimento(), basename($file["ubicacion"]), $file["tipo_archivo"]))) === true) {
                                    $added[] = $tmpfile;
                                }
                                unset($tmpfile);
                            }
                        }

                        $val = new OAQ_ArchivosValidacion();                
                        $arch_val = $val->archivosDePedimento($traffic->getPatente(), $traffic->getAduana(), $traffic->getPedimento());
                        if (!empty($arch_val)) {
                            $mppr_val = new Automatizacion_Model_ArchivosValidacionMapper();
                            foreach ($arch_val as $a_val) {
                                if ($a_val['idArchivoValidacion']) {
                                    $file_val = $mppr_val->fileContent($a_val['idArchivoValidacion']);
                                    if ($file_val) {
                                        $zip->addFromString($a_val['archivoNombre'], base64_decode(base64_decode($file_val["contenido"])));
                                    }
                                }
                            }
                        }
                        
                        if (($zip->close()) === TRUE) {
                            $closed = true;
                        }
                        if ($closed === true) {
                            foreach ($added as $tmp) {
                                unlink($tmp);
                            }
                        }
                        if (file_exists($zipFilename)) {
                            if (!is_file($zipFilename)) {
                                header($this->getRequest()->getServer('SERVER_PROTOCOL') . " 404 Not Found");
                                echo "File not found";
                            } else if (!is_readable($zipFilename)) {
                                header($this->getRequest()->getServer('SERVER_PROTOCOL') . " 403 Forbidden");
                                echo "File not readable";
                            }
                            header($this->getRequest()->getServer('SERVER_PROTOCOL') . " 200 OK");
                            header("Content-Type: application/zip");
                            header("Content-Transfer-Encoding: Binary");
                            header("Content-Length: " . filesize($zipFilename));
                            header("Content-Disposition: attachment; filename=\"" . basename($zipFilename) . "\"");
                            readfile($zipFilename);
                            unlink($zipFilename);
                            return false;
                        }
                    }
                }
                
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Zend_Exception $e) {
            throw new Zend_Exception($e->getMessage());
        }
    }

    public function readImageAction() {
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
            throw new Zend_Exception($ex->getMessage());
        }
    }   

    public function readThumbnailAction() {
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
                $miniatura = $mppr->obtenerMiniatura($input->id);
                header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
                header("Cache-Control: post-check=0, pre-check=0", false);
                header("Pragma: no-cache");
                header("Content-Type: image/jpeg");
                header("Content-Length: " . filesize($miniatura));
                echo file_get_contents($miniatura);
            } else {
                throw new Exception("Invalid input");
            }
        } catch (Exception $ex) {
            throw new Zend_Exception($ex->getMessage());
        }
    }

}
