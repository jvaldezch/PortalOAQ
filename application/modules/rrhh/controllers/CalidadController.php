<?php

class Rrhh_CalidadController extends Zend_Controller_Action {

    protected $_session;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;

    public function init() {
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->view->headLink(array("rel" => "icon shortcut", "href" => "/favicon.png"));
        $this->view->headLink()
                ->appendStylesheet("/js/common/bootstrap/css/bootstrap.min.css")
                ->appendStylesheet("/js/common/bootstrap/datepicker/css/datepicker.css")
                ->appendStylesheet("/css/DT_bootstrap.css")
                ->appendStylesheet("/less/traffic-module.css?" . time());
        $this->view->headScript()
                ->appendFile("/js/common/jquery-1.9.1.min.js")
                ->appendFile("/js/common/bootstrap/js/bootstrap.min.js")
                ->appendFile("/js/common/bootstrap/datepicker/js/bootstrap-datepicker.js")
                ->appendFile("/js/common/jquery.form.min.js")
                ->appendFile("/js/common/jquery.validate.min.js")
                ->appendFile("/js/common/jquery.dataTables.min.js")
                ->appendFile("/js/common/js.cookie.js")
                ->appendFile("/js/common/mensajero.js?" . time())
                ->appendFile("/js/common/DT_bootstrap.js");
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
        $mapper = new Application_Model_MenuAccesos();
        $this->view->menu = $mapper->obtenerPorRol($this->_session->idRol);
        $this->view->rol = $this->_session->role;
        $this->view->username = $this->_session->username;
        $news = new Application_Model_NoticiasInternas();
        $this->view->noticias = $news->obtenerTodos();
    }

    public function indexAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Archivos ISO (Manager)";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
                ->appendStylesheet("/js/common/contentxmenu/jquery.contextMenu.min.css");
        $this->view->headScript()
                ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
                ->appendFile("/js/common/contentxmenu/jquery.contextMenu.min.js")
                ->appendFile("/js/rrhh/calidad/index.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
        );
        $v = array(
            "directorio" => "NotEmpty",
        );
        $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        $mapper = new Rrhh_Model_IsoRelCarpetas();
        $arr = $mapper->obtener($i->directorio);
        $this->view->carpetas = $arr;
        $parent = $mapper->obtenerParent($i->directorio);
        if (isset($parent) && $parent["previo"] !== "") {
            $this->view->parent = $parent;
        }
        $mappera = new Rrhh_Model_IsoArchivos();
        $arra = $mappera->obtenerTodos($i->directorio);
        $this->view->archivos = $arra;
        $this->view->directorio = $i->directorio;
        if ($i->isValid("directorio")) {
            $dr = $this->_buscarParentArray($i->directorio);
            $this->view->navigator = $dr;
        }
    }

    public function descargarArchivoAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => "Digits",
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int()),
            );
            $i = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($i->isValid("id")) {
                $folder = realpath(APPLICATION_PATH . "/../public/sgc2015/");
                $mapper = new Rrhh_Model_IsoArchivos();
                $arr = $mapper->obtener($i->id);
                if (($d = $this->_buscarParent($arr["carpeta"]))) {
                    $folder .= $d . DIRECTORY_SEPARATOR . $arr["carpeta"];
                }
                if (!isset($d) && $arr["carpeta"] !== "") {
                    $folder .= DIRECTORY_SEPARATOR . $arr["carpeta"];
                }
                if (isset($arr)) {
                    $filename = $folder . DIRECTORY_SEPARATOR . $arr["archivo"];
                    if (file_exists($filename)) {
                        header("Cache-Control: public");
                        header("Content-Description: File Transfer");
                        header("Content-Disposition: attachment; filename=\"" . $this->_convert($arr["nombreArchivo"]) . "\"");
                        header("Content-length: " . filesize($filename));
                        header("Content-Transfer-Encoding: binary");
                        header("Content-Type: binary/octet-stream");
                        readfile($filename);
                    }
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function crearDirectorioAction() {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                );
                $v = array(
                    "folderName" => array("NotEmpty"),
                    "directorio" => array("NotEmpty", "default" => ""),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                $rels = new Rrhh_Model_IsoRelCarpetas();
                $folders = new Rrhh_Model_IsoCarpetas();
                $folder = realpath(APPLICATION_PATH . "/../public/sgc2015/");
                if (!$input->isValid("directorio")) {
                    $dir = 0;
                } else {
                    $folders = new Rrhh_Model_IsoCarpetas();
                    $dir = $folders->buscarId($input->directorio);
                    if (($d = $this->_buscarParent($input->directorio))) {
                        $folder .= $d . DIRECTORY_SEPARATOR . $input->directorio;
                    }
                    if (!isset($d) && $input->directorio !== "") {
                        $folder .= DIRECTORY_SEPARATOR . $input->directorio;
                    }
                }
                if ($input->isValid("folderName")) {
                    $newFolder = sha1($input->folderName . $dir);
                    $folder .= DIRECTORY_SEPARATOR . $newFolder;
                    if (!file_exists($folder)) {
                        mkdir($folder, 0777, true);
                    }
                    if (!($id = $folders->verificar($newFolder))) {
                        $id = $folders->agregar($newFolder, $input->folderName);
                    }
                    if (isset($id)) {
                        $childOf = $rels->isChildOf($dir);
                        if (!($rels->verificar($id, $dir))) {
                            $rels->agregar($id, $dir);
                        }
                        if (isset($childOf)) {
                            if (($u = $rels->verificarNoChild($dir, $childOf))) {
                                $rels->actualizarIdChild($u, $id);
                            }
                        }
                        if (!($rels->verificar($dir, $childOf, $id))) {
                            $rels->agregar($dir, $childOf, $id);
                        }
                    }
                    $this->_helper->json(array("success" => true));
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function editarArchivoAction() {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                );
                $v = array(
                    "id" => array("NotEmpty"),
                    "nombre" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("id") && $input->isValid("nombre")) {
                    $mapper = new Rrhh_Model_IsoArchivos();
                    if (($mapper->actualizarNombre($input->id, html_entity_decode($input->nombre)))) {
                        $this->_helper->json(array("success" => true, "nombre" => $input->nombre));
                    }
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function eliminarArchivosAction() {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                );
                $v = array(
                    "ids" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("ids")) {
                    $folder = realpath(APPLICATION_PATH . "/../public/sgc2015/");
                    if (is_array($input->ids)) {
                        $mapper = new Rrhh_Model_IsoArchivos();
                        foreach ($input->ids as $id) {
                            if (($f = $mapper->obtener($id))) {
                                if (($d = $this->_buscarParent($f["carpeta"]))) {
                                    $folder = $folder . $d . DIRECTORY_SEPARATOR . $f["carpeta"];
                                }
                                if (!isset($d) && $f["carpeta"] !== "") {
                                    $folder .= DIRECTORY_SEPARATOR . $f["carpeta"];
                                }
                                if (file_exists($folder . DIRECTORY_SEPARATOR . $f["archivo"])) {
                                    unlink($folder . DIRECTORY_SEPARATOR . $f["archivo"]);
                                }
                                $mapper->eliminarArchivo($id);
                            }
                        }
                    }
                    $this->_helper->json(array("success" => true));
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function editarDirectorioAction() {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                );
                $v = array(
                    "id" => array("NotEmpty"),
                    "nombre" => array("NotEmpty"),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("id") && $input->isValid("nombre")) {
                    $mapper = new Rrhh_Model_IsoCarpetas();
                    if (($mapper->actualizarNombre($input->id, html_entity_decode($input->nombre)))) {
                        $this->_helper->json(array("success" => true, "nombre" => $input->nombre));
                    }
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function subirArchivosAction() {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                );
                $v = array(
                    "directorio" => array("NotEmpty", "default" => ""),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                $folder = realpath(APPLICATION_PATH . "/../public/sgc2015/");
                if (!$input->isValid("directorio")) {
                    $dir = 0;
                } else {
                    $folders = new Rrhh_Model_IsoCarpetas();
                    $dir = $folders->buscarId($input->directorio);
                    if (($d = $this->_buscarParent($input->directorio))) {
                        $folder .= $d . DIRECTORY_SEPARATOR . $input->directorio;
                    }
                    if (!isset($d) && $input->directorio !== "") {
                        $folder .= DIRECTORY_SEPARATOR . $input->directorio;
                    }
                }
                $archivos = new Rrhh_Model_IsoArchivos();
                $upload = new Zend_File_Transfer_Adapter_Http();
                $upload->addValidator("Count", false, array("min" => 1, "max" => 15))
                        ->addValidator("Size", false, array("min" => "1", "max" => "20MB"))
                        ->addValidator("Extension", false, array("extension" => "pdf,xml,xls,xlsx,doc,docx,zip,bmp,tif,jpg,jpeg", "case" => false));
                $upload->setDestination($folder);
                $files = $upload->getFileInfo();
                foreach ($files as $fieldname => $fileinfo) {
                    if (($upload->isUploaded($fieldname)) && ($upload->isValid($fieldname))) {
                        $sha = sha1_file($fileinfo["tmp_name"]);
                        $origName = $fileinfo["name"];
                        $filename = sha1($sha . $dir) . "." . strtolower(pathinfo($fileinfo["name"], PATHINFO_EXTENSION));
                        $upload->addFilter("Rename", $filename, $fieldname);
                        $upload->receive($fieldname);
                        if (file_exists($folder . DIRECTORY_SEPARATOR . $filename)) {
                            if (!($archivos->verificar($dir, $filename))) {
                                $archivos->agregar($dir, $filename, $origName);
                            }
                        }
                    }
                }
                $this->_helper->json(array("success" => true));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function eliminarDirectorioAction() {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("id")) {
                    $folder = realpath(APPLICATION_PATH . "/../public/sgc2015/");
                    $r = new Rrhh_Model_IsoRelCarpetas();
                    $c = new Rrhh_Model_IsoCarpetas();
                    $d = $c->obtenerDirectorio($input->id);
                    if (isset($d) && !empty($d)) {
                        $f = $this->_buscarParent($d["carpeta"]);
                        if (!isset($f)) {
                            $f = $folder . DIRECTORY_SEPARATOR . $d["carpeta"];
                        } else {
                            $f = $folder . $f . DIRECTORY_SEPARATOR . $d["carpeta"];
                        }
                        $r->eliminarDirectorio($input->id);
                        $c->eliminarDirectorio($input->id);
                        if (file_exists($f) && $f != realpath(APPLICATION_PATH . "/../public/sgc2015/")) {
                            unlink($f);
                        }
                        $this->_helper->json(array("success" => true));
                    }
                    $this->_helper->json(array("success" => false));
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function comprobarContenidoAction() {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($input->isValid("id")) {
                    $r = new Rrhh_Model_IsoRelCarpetas();
                    $a = new Rrhh_Model_IsoArchivos();
                    if ($a->contarElementos($input->id) || $r->contarElementos($input->id)) {
                        $this->_helper->json(array("success" => true));
                    }
                    $this->_helper->json(array("success" => false));
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    protected function _convert($value) {
        return mb_convert_encoding(preg_replace(array("/\s+/"), "_", utf8_decode($value)), "UTF-8", "ISO-8859-1");
    }

    protected function _buscarParent($directorio) {
        $rel = new Rrhh_Model_IsoRelCarpetas();
        $p = $rel->obtenerParent($directorio);
        if ($p["previo"]) {
            return $this->_buscarParent($p["previo"]) . DIRECTORY_SEPARATOR . $p["previo"];
        }
        return;
    }

    protected function _buscarParentArray($directorio) {
        $rel = new Rrhh_Model_IsoRelCarpetas();
        $p = $rel->obtenerParentArray($directorio);
        if ($p["previo"]) {
            return array_merge_recursive($this->_buscarParentArray($p["previo"]), array("directorio" => $directorio, "nombreCarpeta" => $p["nombreCarpeta"]));
        }
        $folders = new Rrhh_Model_IsoCarpetas();
        $p = $folders->obtener($directorio);
        return array("directorio" => $p["carpeta"], "nombreCarpeta" => $p["nombreCarpeta"]);
    }

}
