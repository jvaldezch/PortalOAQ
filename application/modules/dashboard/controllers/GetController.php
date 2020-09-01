<?php

class Dashboard_GetController extends Zend_Controller_Action {

    protected $_session;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;
    protected $_firephp;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->_firephp = Zend_Registry::get("firephp");
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $this->_logger = Zend_Registry::get("logDb");
    }

    public function preDispatch() {
        $this->_session = null ? $this->_session = new Zend_Session_Namespace("") : $this->_session = new Zend_Session_Namespace("Dashboard");
        if (isset($this->_session->rfcCliente)) {
            $this->_session->setExpirationSeconds($this->_appconfig->getParam("session-exp"));
        }
    }

    public function traficosAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "page" => array("Digits"),
                "limit" => array("Digits"),
                "year" => array("Digits"),
                "month" => array("Digits"),
                "idAduana" => array("Digits"),
            );
            $v = array(
                "page" => array(new Zend_Validate_Int(), "default" => 1),
                "limit" => array(new Zend_Validate_Int(), "default" => 10),
                "year" => array("NotEmpty", new Zend_Validate_Int()),
                "month" => array("NotEmpty", new Zend_Validate_Int()),
                "idAduana" => array("NotEmpty", new Zend_Validate_Int()),
                "code" => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("code")) {
                $custs = new Dashboard_Model_ClientesDbs();
                $cust = $custs->buscarIdentificador($input->code);
                if (count($cust)) {
                    $mapper = new Dashboard_Model_Traficos();
                    //$arr = $mapper->obtenerTraficos($input->page, $input->limit, date("Y-m-d"), $cust["rfc"]);
                    $arr = $mapper->obtenerTraficos($input->page, $input->limit, date("Y-m-d"));
                    if (count($arr)) {
                        //$this->_helper->json(array("success" => true, "total" => $mapper->total(date("Y-m-d"), $cust["rfc"]), "page" => $input->page, "data" => $arr));
                        $this->_helper->json(array("success" => true, "total" => $mapper->total(date("Y-m-d")), "page" => $input->page, "data" => $arr));
                    } else {
                        throw new Exception("No data found!");
                    }
                } else {
                    throw new Exception("Invalid code!");
                }
            } else {
                throw new Exception("Code not set!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function porAduanaAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "year" => array("Digits"),
                "month" => array("Digits"),
            );
            $v = array(
                "year" => array("NotEmpty", new Zend_Validate_Int()),
                "month" => array("NotEmpty", new Zend_Validate_Int()),
                "code" => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            $mapper = new Dashboard_Model_Traficos();
            $year = (int) date("Y");
            $month = (int) date("m");

            if ($input->isValid("year")) {
                $year = $input->year;
            }
            if ($input->isValid("month")) {
                $month = $input->month;
            }
            if ($input->isValid("code")) {
                $custs = new Dashboard_Model_ClientesDbs();
                $cust = $custs->buscarIdentificador($input->code);
                if (empty($cust)) {
                    throw new Exception("Invalid code!!");
                }
            } else {
                throw new Exception("Code not set!");
            }
            if ($cust["rfc"] == "ACM080307L15") {
                $array = array(
                    'QUERETARO, QRO' => $mapper->porAduana($cust["rfc"], 3589, 640, date("Y-m-d")),
                    'NUEVO LAREDO' => $mapper->porAduana($cust["rfc"], null, 240, date("Y-m-d")),
                    'COLOMBIA, N.L.' => $mapper->porAduana($cust["rfc"], null, 800, date("Y-m-d")),
                    'MANZANILLO, COL.' => $mapper->porAduana($cust["rfc"], 3574, 160, date("Y-m-d")),
                    'CD. DE MEXICO' => $mapper->porAduana($cust["rfc"], 3574, 470, date("Y-m-d")),
                    'ALTAMIRA, TAMPS.' => $mapper->porAduana($cust["rfc"], 3933, 810, date("Y-m-d")),
                    'TOLUCA' => $mapper->porAduana($cust["rfc"], 3920, 650, date("Y-m-d")),
                );                
            } else if ($cust["rfc"] == "GCO980828GY0") {
                $array = array(
                    'QUERETARO, QRO' => $mapper->porAduana($cust["rfc"], 3589, 640, date("Y-m-d")),
                    'NUEVO LAREDO' => $mapper->porAduana($cust["rfc"], 3589, 240, date("Y-m-d")),
                    'CD. DE MEXICO' => $mapper->porAduana($cust["rfc"], 3574, 470, date("Y-m-d")),
                );                                
            }

            $totalMes = $mapper->totalMes($cust["rfc"], date("Y-m-d"));
            $totalPorLiberar = $mapper->totalPorLiberar($cust["rfc"], date("Y-m-d"));
            $totalAnterior = $mapper->totalMesAnterior($cust["rfc"], date("Y-m-d"));
            $data = array();
            foreach ($array as $k => $v) {
                $data[] = array(
                    "label" => $k,
                    "value" => $v,
                );
            }
            if (count($data)) {
                $this->_helper->json(array("success" => true, "data" => $data, "totalMes" => $totalMes, "totalAnterior" => $totalAnterior, "totalLiberar" => $totalPorLiberar));
            } else {
                throw new Exception("No data found!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function uploadFormAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $v = array(
                "id" => array(new Zend_Validate_Int()),
                "type" => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id") && $input->isValid("type")) {
                
                $traficos = new OAQ_Trafico(array("idTrafico" => $input->id));
                $pedimento = $traficos->getAduana() . '-' . $traficos->getPatente() . '-' . $traficos->getPedimento();
                
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->id = $input->id;
                $view->type = $input->type;
                if ($input->type == 'fechaPrevio') {
                    $this->_helper->json(array("success" => true, "referencia" => $traficos->getReferencia(), "pedimento" => $pedimento, "html" => $view->render("view-photos.phtml")));
                } elseif ($input->type == 'fechaRevalidacion') {
                    $this->_helper->json(array("success" => true, "referencia" => $traficos->getReferencia(), "pedimento" => $pedimento, "html" => $view->render("upload-form-rev.phtml")));
                } else {
                    $this->_helper->json(array("success" => true, "referencia" => $traficos->getReferencia(), "pedimento" => $pedimento, "html" => $view->render("upload-form.phtml")));
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function viewFilesAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $v = array(
                "id" => array(new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                
                $traficos = new OAQ_Trafico(array("idTrafico" => $input->id));
                $pedimento = $traficos->getAduana() . '-' . $traficos->getPatente() . '-' . $traficos->getPedimento();
                
                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");
                
                $mppr = new Trafico_Model_TraficosMapper();
                $array = $mppr->obtenerPorId($input->id);
                $repo = new Archivo_Model_RepositorioMapper();
                $archivos = $repo->obtenerArchivosReferencia($array["referencia"], true);
                $view->archivos = $archivos;
                
                $this->_helper->json(array("success" => true, "referencia" => $traficos->getReferencia(), "pedimento" => $pedimento, "html" => $view->render("view-files.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function viewUploadedFilesAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $v = array(
                "id" => array(new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                
                $mppr = new Trafico_Model_TraficosMapper();
                $array = $mppr->obtenerPorId($input->id);
                $repo = new Archivo_Model_RepositorioMapper();
                $archivos = $repo->obtenerArchivosReferencia($array["referencia"], true, array(18, 34, 38, 55, 66));
                if (!empty($archivos)) {
                    $this->_helper->json(array("success" => true, "results" => $archivos));
                } else {
                    $this->_helper->json(array("success" => false));
                }
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function viewPhotosAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $v = array(
                "id" => array(new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {

                $traficos = new OAQ_Trafico(array("idTrafico" => $input->id));
                $pedimento = $traficos->getAduana() . '-' . $traficos->getPatente() . '-' . $traficos->getPedimento();

                $gallery = new Trafico_Model_Imagenes();
                $arr = $gallery->miniaturas($input->id);

                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");
                if (!empty($arr)) {
                    $view->gallery = $arr;
                }
                $this->_helper->json(array("success" => true, "referencia" => $traficos->getReferencia(), "pedimento" => $pedimento, "html" => $view->render("view-photos.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function commentsAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idTrafico" => array("Digits"),
            );
            $v = array(
                "idTrafico" => array(new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("idTrafico")) {
                
                $traficos = new OAQ_Trafico(array("idTrafico" => $input->idTrafico));

                $arr = $traficos->obtenerComentarios();
                $this->_helper->json(array("success" => true, "comments" => $arr));
                
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function viewCommentsAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => array("Digits"),
            );
            $v = array(
                "id" => array(new Zend_Validate_Int()),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                
                $traficos = new OAQ_Trafico(array("idTrafico" => $input->id));
                $pedimento = $traficos->getAduana() . '-' . $traficos->getPatente() . '-' . $traficos->getPedimento();

                $view = new Zend_View();
                $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/get/");
                $view->setHelperPath(realpath(dirname(__FILE__)) . "/../views/helpers/");

                $arr = $traficos->obtenerComentarios();
                $view->id = $input->id;
                if (!empty($arr)) {
                    $view->comments = $arr;
                }

                $this->_helper->json(array("success" => true, "referencia" => $traficos->getReferencia(), "pedimento" => $pedimento, "html" => $view->render("view-comments.phtml")));
            } else {
                throw new Exception("Invalid input!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function climaAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $mapper = new Dashboard_Model_Clima();
            $arr = $mapper->obtener();
            if (isset($arr["json"])) {
                if (count($arr["json"])) {
                    $this->_helper->json(array("success" => true, "data" => $arr["json"]));
                } else {
                    throw new Exception("No data found!");
                }
            }
            throw new Exception("No data found!");
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function documentsTypeAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception("Not an AJAX request detected");
            }
            $mppr = new Archivo_Model_DocumentosMapper();
            $rows = $mppr->getAll();
            if (count($rows)) {
                $arr = array();
                foreach ($rows as $item) {
                    $arr[$item['id']] = array('name' => $item['nombre']);
                }
                $this->_helper->json(array("success" => true, "results" => $arr));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }   

    public function viewThumbnailAction() {
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
            Zend_Debug::Dump($ex->getMessage());
        }
    }

    public function viewImageAction() {
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
            Zend_Debug::Dump($ex->getMessage());
        }
    }

}
