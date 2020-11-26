<?php

class Mobile_PostController extends Zend_Controller_Action
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
    }

    public function preDispatch()
    {
        $this->_session = NULL ? $this->_session = new Zend_Session_Namespace("") : $this->_session = new Zend_Session_Namespace("OAQmobile");
        if ($this->_session->authenticated == true) {
            $session = new OAQ_Session($this->_session, $this->_appconfig);
            $session->actualizar();
            $session->actualizarSesion(Zend_Controller_Front::getInstance()->getRequest()->getRequestUri());
        } else {
            $this->getResponse()->setRedirect("/mobile/main/logout");
        }
    }

    public function subirFotosBodegaAction()
    {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                    "idBodega" => array("Digits"),
                    "referencia" => array("StringToUpper"),
                    "rfcCliente" => array("StringToUpper"),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "idBodega" => array("NotEmpty", new Zend_Validate_Int()),
                    "referencia" => array(new Zend_Validate_Regex("/^[-_a-zA-Z0-9.]+$/"), "presence" => "required"),
                    "rfcCliente" => array(new Zend_Validate_Regex("/^[-_a-zA-Z0-9.]+$/"), "presence" => "required"),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if (!$input->isValid("id") || !$input->isValid("idBodega") || !$input->isValid("referencia")) {
                    throw new Exception("Invalid input!" . Zend_Debug::dump($input->getErrors(), true) . Zend_Debug::dump($input->getMessages(), true));
                }
            }

            $misc = new OAQ_Misc();
            if (APPLICATION_ENV == "production") {
                $misc->set_baseDir($this->_appconfig->getParam("expdest"));
            } else {
                $misc->set_baseDir("/tmp/expedientes");
            }

            $traffic = new OAQ_Bodega(array("idTrafico" => $input->id));
            $t = $traffic->obtenerDatos();

            $mpr = new Bodega_Model_Bodegas();
            $b = $mpr->obtener($input->idBodega);

            $mdl = new Trafico_Model_Imagenes();

            $upload = new Zend_File_Transfer_Adapter_Http();
            $upload->addValidator("Count", false, array("min" => 1, "max" => 15))
                ->addValidator("Size", false, array("min" => "1kB", "max" => "20MB"))
                ->addValidator("Extension", false, array("extension" => "jpg,png,jpeg", "case" => false));

            if (($path = $misc->directorioExpedienteDigitalBodega($b['siglas'], $t["fechaEta"], $input->referencia))) {
                $upload->setDestination($path);
            }

            $files = $upload->getFileInfo();
            foreach ($files as $fieldname => $fileinfo) {
                if (($upload->isUploaded($fieldname)) && ($upload->isValid($fieldname))) {

                    $ext = strtolower(pathinfo($fileinfo["name"], PATHINFO_EXTENSION));
                    $filename = sha1(time() . $fileinfo["name"]) . "." . $ext;
                    $upload->addFilter("Rename", $filename, $fieldname);
                    $upload->receive($fieldname);

                    $thumb = $path . DIRECTORY_SEPARATOR . pathinfo($filename, PATHINFO_FILENAME) . "_thumb." . pathinfo($filename, PATHINFO_EXTENSION);
                    if (file_exists($path . DIRECTORY_SEPARATOR . $filename)) {
                        if (extension_loaded("imagick")) {
                            $im = new Imagick();
                            $im->pingImage($path . DIRECTORY_SEPARATOR . $filename);
                            $im->readImage($path . DIRECTORY_SEPARATOR . $filename);

                            if ($im->getimagewidth() > 1024) {
                                $im->resizeimage(1024, round($im->getimageheight() / ($im->getimagewidth() / 1024), 0), Imagick::FILTER_LANCZOS, 1);
                                $im->writeImage($path . DIRECTORY_SEPARATOR . $filename);
                            }
                            $im->thumbnailImage(150, round($im->getimageheight() / ($im->getimagewidth() / 150), 0));
                            $im->writeImage($thumb);
                            $im->destroy();
                            if (isset($thumb) && file_exists($thumb)) {
                                $mdl->agregar($input->id, 1, $path, pathinfo($filename, PATHINFO_BASENAME), pathinfo($thumb, PATHINFO_BASENAME), $fileinfo["name"]);
                            }
                        }
                        if (!isset($thumb) || !file_exists($thumb)) {
                            $mdl->agregar($input->id, 1, $path, pathinfo($filename, PATHINFO_BASENAME), null, $fileinfo["name"]);
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

    public function subirFotosTraficoAction()
    {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if (!$input->isValid("id")) {
                    throw new Exception("Invalid input!" . Zend_Debug::dump($input->getErrors(), true) . Zend_Debug::dump($input->getMessages(), true));
                }
            }
            $misc = new OAQ_Misc();
            if (APPLICATION_ENV == "production") {
                $misc->set_baseDir($this->_appconfig->getParam("expdest"));
            } else {
                $misc->set_baseDir("/tmp/expedientes");
            }

            $bodega = new OAQ_Bodega(array("idTrafico" => $input->id));
            $arr = $bodega->obtenerDatos();

            $model = new Archivo_Model_RepositorioMapper();

            $dir = $misc->crearNuevoDirectorio($this->_appconfig->getParam("expdest"), $arr["patente"] . "/" . $arr["aduana"] . "/" . $arr["referencia"]);

            $upload = new Zend_File_Transfer_Adapter_Http();
            $upload->addValidator("Count", false, array("min" => 1, "max" => 15))
                ->addValidator("Size", false, array("min" => "1", "max" => "20MB"))
                ->addValidator("Extension", false, array("extension" => "png,jpg,jpeg", "case" => false));
            $upload->setDestination($dir);

            $mdl = new Trafico_Model_Imagenes();

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
                                $mdl->agregar($input->id, 1, $dir, pathinfo($filename, PATHINFO_BASENAME), pathinfo($thumb, PATHINFO_BASENAME), $fileinfo["name"]);
                            }
                        }
                        if (!isset($thumb) || !file_exists($thumb)) {
                            $mdl->agregar($input->id, 1, $dir, pathinfo($filename, PATHINFO_BASENAME), null, $fileinfo["name"]);
                        }
                    }
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

    public function subirArchivosTraficoAction()
    {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "patente" => array("Digits"),
                    "aduana" => array("Digits"),
                    "pedimento" => array("Digits"),
                    "referencia" => array("StringToUpper"),
                    "rfcCliente" => array("StringToUpper"),
                );
                $v = array(
                    "patente" => new Zend_Validate_Int(),
                    "aduana" => new Zend_Validate_Int(),
                    "pedimento" => new Zend_Validate_Int(),
                    "referencia" => array(new Zend_Validate_Regex("/^[-_a-zA-Z0-9.]+$/"), "presence" => "required"),
                    "rfcCliente" => array(new Zend_Validate_Regex("/^[-_a-zA-Z0-9.]+$/"), "presence" => "required"),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if (!$input->isValid()) {
                    throw new Exception("Invalid input!" . Zend_Debug::dump($input->getErrors(), true) . Zend_Debug::dump($input->getMessages(), true));
                }
            }
            $errors = [];

            if (!is_writable($this->_appconfig->getParam("expdest"))) {
                throw new Exception("Directory [" . $this->_appconfig->getParam("expdest") . "] is not writable.");
            }
            $misc = new OAQ_Misc();
            if (APPLICATION_ENV == "production") {
                $misc->set_baseDir($this->_appconfig->getParam("expdest"));
            } else {
                $misc->set_baseDir("/tmp/expedientes");
            }
            $model = new Archivo_Model_RepositorioMapper();
            $upload = new Zend_File_Transfer_Adapter_Http();
            $upload->addValidator("Count", false, array("min" => 1, "max" => 15))
                ->addValidator("Size", false, array("min" => "1", "max" => "25MB"));
            
            if (($path = $misc->directorioExpedienteDigital($input->patente, $input->aduana, $input->referencia))) {
                $upload->setDestination($path);
            }
            $files = $upload->getFileInfo();
            $up = array();
            $nup = array();
            foreach ($files as $fieldname => $fileinfo) {
                if (($upload->isUploaded($fieldname))) {
                    if (!preg_match('/\.(pdf|xml|xls|xlsx|doc|docx|zip|bmp|tif|jpe?g|bmp|png|msg|([0-9]{3})|err)(?:[\?\#].*)?$/i', $fileinfo["name"])) {
                        continue;
                    }
                    $tipoArchivo = $misc->tipoArchivo(basename($fileinfo["name"]));
                    if (preg_match('/^A[0-9]{7}.([0-9]{3})$/i', basename($fileinfo["name"]))) {
                        $tipoArchivo = 1030;
                    }
                    if (preg_match('/^E[0-9]{7}.([0-9]{3})$/i', basename($fileinfo["name"]))) {
                        $tipoArchivo = 1030;
                    }
                    if (preg_match('/^M[0-9]{7}.([0-9]{3})$/i', basename($fileinfo["name"]))) {
                        $tipoArchivo = 1010;
                    }
                    if (preg_match('/^m[0-9]{7}.err$/i', basename($fileinfo["name"]))) {
                        $tipoArchivo = 1020;
                    }

                    $ext = pathinfo($fileinfo["name"], PATHINFO_EXTENSION);
                    if (preg_match('/msg/i', $ext)) {
                        $tipoArchivo = 2001;
                    }

                    if ($tipoArchivo == 99) {
                        $nup[] = $fileinfo["name"];
                        unlink($fileinfo["name"]);
                        continue;
                    }

                    $filename = $misc->formatFilename($fileinfo["name"], false);
                    $verificar = $model->verificarArchivo($input->patente, $input->referencia, $filename);
                    if ($verificar == false) {
                        $upload->receive($fieldname);
                        if (in_array($tipoArchivo, array(1010, 1020, 1030))) {
                            $up[] = $fileinfo["name"];
                            $model->nuevoArchivo($tipoArchivo, null, $input->patente, $input->aduana, $input->pedimento, $input->referencia, $filename, $path . DIRECTORY_SEPARATOR . $filename, $this->_session->username, $input->rfcCliente);
                        }
                        if (($misc->renombrarArchivo($path, $fileinfo["name"], $filename))) {
                            $up[] = $fileinfo["name"];
                            $model->nuevoArchivo($tipoArchivo, null, $input->patente, $input->aduana, $input->pedimento, $input->referencia, $filename, $path . DIRECTORY_SEPARATOR . $filename, $this->_session->username, $input->rfcCliente);
                        }
                    } else {
                        $errors[] = array(
                            "filename" => $fileinfo["name"],
                            "errors" => array("errors" => "El archivo ya existe."),
                        );
                    }
                } else {
                    $error = $upload->getErrors();
                    $errors[] = array(
                        "filename" => $fileinfo["name"],
                        "errors" => $error,
                    );
                }
            }
            if (!empty($errors)) {
                $this->_helper->json(array("success" => false, "errors" => $errors));
            } else {
                $this->_helper->json(array("success" => true, "uploaded" => $up, "not_uploaded" => $nup, "errors" => $errors));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function subirArchivosBodegaAction()
    {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "id" => array("Digits"),
                    "idBodega" => array("Digits"),
                    "referencia" => array("StringToUpper"),
                    "rfcCliente" => array("StringToUpper"),
                );
                $v = array(
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "idBodega" => array("NotEmpty", new Zend_Validate_Int()),
                    "referencia" => array(new Zend_Validate_Regex("/^[-_a-zA-Z0-9.]+$/"), "presence" => "required"),
                    "rfcCliente" => array(new Zend_Validate_Regex("/^[-_a-zA-Z0-9.]+$/"), "presence" => "required"),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if (!$input->isValid("id") || !$input->isValid("idBodega") || !$input->isValid("referencia")) {
                    throw new Exception("Invalid input!" . Zend_Debug::dump($input->getErrors(), true) . Zend_Debug::dump($input->getMessages(), true));
                }
            }

            $misc = new OAQ_Misc();
            $misc->set_baseDir($this->_appconfig->getParam("expdest"));

            if (APPLICATION_ENV == "development") {
                $misc->set_baseDir("/tmp/expedientes");
            }

            $traffic = new OAQ_Bodega(array("idTrafico" => $input->id));
            $t = $traffic->obtenerDatos();

            $mpr = new Bodega_Model_Bodegas();
            $b = $mpr->obtener($input->idBodega);

            $model = new Archivo_Model_RepositorioMapper();
            $upload = new Zend_File_Transfer_Adapter_Http();
            $upload->addValidator("Count", false, array("min" => 1, "max" => 15))
                ->addValidator("Size", false, array("min" => "1", "max" => "20MB"))
                ->addValidator("Extension", false, array("extension" => "pdf,xml,xls,xlsx,doc,docx,zip,bmp,tif,jpg,msg", "case" => false));

            $path = $misc->directorioExpedienteDigitalBodega($b['siglas'], $t["fechaEta"], $input->referencia);

            if (($path = $misc->directorioExpedienteDigitalBodega($b['siglas'], $t["fechaEta"], $input->referencia))) {
                $upload->setDestination($path);
            } else {
                throw new Exception("Could not set base directory.");
            }

            $files = $upload->getFileInfo();
            foreach ($files as $fieldname => $fileinfo) {
                if (($upload->isUploaded($fieldname)) && ($upload->isValid($fieldname))) {
                    $tipoArchivo = $misc->tipoArchivo(basename($fileinfo["name"]));
                    $ext = pathinfo($fileinfo["name"], PATHINFO_EXTENSION);
                    if (preg_match('/msg/i', $ext)) {
                        $tipoArchivo = 2001;
                    }
                    $filename = $misc->formatFilename($fileinfo["name"], false);
                    $verificar = $model->verificarArchivo(null, $input->referencia, $filename);
                    if ($verificar == false) {
                        $upload->receive($fieldname);
                        if (($misc->renombrarArchivo($path, $fileinfo["name"], $filename))) {
                            $model->nuevoArchivo($tipoArchivo, null, null, null, null, $input->referencia, $filename, $path . DIRECTORY_SEPARATOR . $filename, $this->_session->username, $input->rfcCliente);
                        }
                    } else {
                        $errors[] = array(
                            "filename" => $fileinfo["name"],
                            "errors" => array("errors" => "El archivo ya existe."),
                        );
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
}
