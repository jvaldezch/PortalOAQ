<?php

class Vucem_EdocumentController extends Zend_Controller_Action {

    protected $_session;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;

    public function init() {
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->view->headLink(array("rel" => "icon shortcut", "href" => "/favicon.png"));
        $this->view->headLink()
                ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css")
                ->appendStylesheet("/css/jquery.qtip.min.css")
                ->appendStylesheet("/js/common/bootstrap/css/bootstrap.min.css")
                ->appendStylesheet("/js/common/bootstrap/datepicker/css/datepicker.css")
                ->appendStylesheet("/css/DT_bootstrap.css")
                ->appendStylesheet("/less/traffic-module.css?" . time());
        $this->view->headScript()
                ->appendFile("/js/common/jquery-1.9.1.min.js")
                ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
                ->appendFile("/js/common/jquery.qtip.min.js")
                ->appendFile("/js/common/bootstrap/js/bootstrap.min.js")
                ->appendFile("/js/common/bootstrap/datepicker/js/bootstrap-datepicker.js")
                ->appendFile("/js/common/jquery.form.min.js")
                ->appendFile("/js/common/jquery.validate.min.js")
                ->appendFile("/js/common/jquery.dataTables.min.js")
                ->appendFile("/js/common/js.cookie.js")
                ->appendFile("/js/common/jquery.blockUI.js")
                ->appendFile("/js/common/DT_bootstrap.js")
                ->appendFile("/js/common/mensajero.js?" . time())
                ->appendFile("/js/common/principal.js?" . time());
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
        $this->view->username = $this->_session->username;
        $this->view->rol = $this->_session->role;
        $news = new Application_Model_NoticiasInternas();
        $this->view->noticias = $news->obtenerTodos();
        if (APPLICATION_ENV == "development") {
            $this->view->browser_sync = "<script async src='http://{$this->_config->app->browser_sync}/browser-sync/browser-sync-client.js?v=2.26.7'><\/script>";
        }
    }

    public function consultarAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " EDocument";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/js/vucem/edocument/consultar.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits"),
        );
        $v = array(
            "id" => array("NotEmpty", new Zend_Validate_Int()),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id")) {
            $mapper = new Vucem_Model_VucemEdocIndex();
            $arr = $mapper->consultar($input->id);
            if (isset($arr)) {
                $edocs = new Vucem_Model_VucemEdocMapper();
                $arr = $edocs->obtener($input->id);
                if (isset($arr["solicitud"]) && $arr["solicitud"] != '') {
                    if ($this->_session->role == "super" || $this->_session->role == "trafico_operaciones") {
                        $data = $edocs->obtenerEdocPorUuid($arr["uuid"], $arr["solicitud"]);
                        $this->view->data = $data;
                        $this->view->id = $arr["uuid"];
                        $this->view->solicitud = $arr["solicitud"];
                    } else {
                        $data = $edocs->obtenerEdocPorUuid($arr["uuid"], $arr["solicitud"]);
                        $this->view->data = $data;
                        $this->view->id = $arr["uuid"];
                        $this->view->solicitud = $arr["solicitud"];
                    }
                } else {
                    $data = $edocs->obtenerEdocPorUuid($arr["uuid"]);
                    $this->view->data = $data;
                    $this->view->id = $arr["uuid"];
                    $this->view->solicitud = $arr["solicitud"];
                }
            }
        } else {
            throw new Exception("Id not set!");
        }
    }

    public function descargarArchivoAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits"),
        );
        $v = array(
            "id" => array("NotEmpty", new Zend_Validate_Int()),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id")) {
            $mapper = new Vucem_Model_VucemEdocIndex();
            $arr = $mapper->consultar($input->id);
            if (isset($arr)) {
                $edocs = new Vucem_Model_VucemEdocMapper();
                $arr = $edocs->obtener($input->id);
                $file = $edocs->obtenerEdocDigitalizado($arr["uuid"]);
                if ($file) {
                    header('Content-Type: application/pdf');
                    header("Content-Transfer-Encoding: Binary");
                    header("Content-disposition: attachment; filename=" . $arr["nomArchivo"]);
                    echo base64_decode($arr["archivo"]);
                }
            }
        } else {
            throw new Exception("Id not set!");
        }
    }

    public function indexAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " EDocuments";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/js/vucem/edocument/index.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "pedimento" => array("Digits"),
            "referencia" => array("StringToUpper"),
            "edoc" => array("StringToUpper"),
        );
        $v = array(
            "pedimento" => array("NotEmpty", new Zend_Validate_Int()),
            "referencia" => "NotEmpty",
            "edoc" => "NotEmpty",
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        $mapper = new Vucem_Model_VucemEdocIndex();        
        $usuario = null;
        if (!in_array($this->_session->role, array("super", "trafico_operaciones", "gerente"))) {
            $usuario = $this->_session->username;
        }
        $arr = $mapper->obtenerSolicitudes($usuario, $input->pedimento, $input->referencia, $input->edocument);
        if(isset($arr) && !empty($arr)) {
            $this->view->result = $arr;
        }
        $this->view->edocument = $input->edocument;
        $this->view->referencia = $input->referencia;
        $this->view->pedimento = $input->pedimento;
    }
    
    public function digitalizarAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Digitalizar";
        $this->view->headMeta()
                ->appendName("description", "");
        $this->view->headLink()
                ->appendStylesheet("/css/jquery.selectBoxIt.css")
                ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css");
        $this->view->headScript()
                ->appendFile("/js/common/jquery-ui-1.9.2.min.js")
                ->appendFile("/js/common/jquery.selectBoxIt.min.js")
                ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
                ->appendFile("/js/vucem/edocument/digitalizar.js?" . time());
        $request = new Zend_Controller_Request_Http();
        $directory = $request->getCookie("edocsDirectory");
        if (!isset($directory)) {
            $directory = $this->_appconfig->getParam("tmpDir") . DIRECTORY_SEPARATOR . 'ed_' . md5(time());
            echo "<script>Cookies.set('edocsDirectory', '" . urlencode($directory) . "');</script>";
            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }
        }
        $this->view->directory = urldecode($directory);
        $form = new Vucem_Form_MultiplesEDocuments();
        $this->view->form = $form;
    }
    
    public function testscriptAction() {
        
    }

}
