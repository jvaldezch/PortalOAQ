<?php

class Default_IndexController extends Zend_Controller_Action {

    protected $_config;
    protected $_appconfig;
    protected $_soapClient;

    public function init() {
        $this->_helper->layout->setLayout("default");
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->view->headLink()
                ->appendStylesheet("/css/login.css");
        $this->view->headLink(array("rel" => "icon shortcut", "href" => "/favicon.png"));
        $this->view->headScript()
                ->appendFile("/js/common/js.cookie.js")
                ->appendFile("/js/common/jquery-1.9.1.min.js")
                ->appendFile("/js/common/jquery.form.min.js");
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $context = stream_context_create(array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
                "allow_self_signed" => true
            )
        ));
        $this->_soapClient = new Zend_Soap_Client($this->_config->app->endpoint, array("stream_context" => $context));
    }
    
    protected function _checkUserAgent($type = NULL) {
        $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        if ($type == 'bot') {
            // matches popular bots
            if (preg_match("/googlebot|adsbot|yahooseeker|yahoobot|msnbot|watchmouse|pingdom\.com|feedfetcher-google/", $user_agent)) {
                return true;
                // watchmouse|pingdom\.com are "uptime services"
            }
        } else if ($type == 'browser') {
            // matches core browser types
            if (preg_match("/mozilla\/|opera\//", $user_agent)) {
                return true;
            }
        } else if ($type == 'mobile') {
            // matches popular mobile devices that have small screens and/or touch inputs
            // mobile devices have regional trends; some of these will have varying popularity in Europe, Asia, and America
            // detailed demographics are unknown, and South America, the Pacific Islands, and Africa trends might not be represented, here
            if (preg_match("/phone|iphone|itouch|ipod|symbian|android|htc_|htc-|palmos|blackberry|opera mini|iemobile|windows ce|nokia|fennec|hiptop|kindle|mot |mot-|webos\/|samsung|sonyericsson|^sie-|nintendo/", $user_agent)) {
                // these are the most common
                return true;
            } else if (preg_match("/mobile|pda;|avantgo|eudoraweb|minimo|netfront|brew|teleca|lg;|lge |wap;| wap /", $user_agent)) {
                // these are less common, and might not be worth checking
                return true;
            }
        }
        return false;
    }

    public function indexAction() {
        $this->_helper->layout->setLayout("login/index");
        $this->view->title = $this->_appconfig->getParam("title") . " " . $this->_appconfig->getParam("bienvenida");
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/js/common/jquery.validate.min.js")
                ->appendFile("/js/default/index/index.js");
        $mapper = new Rrhh_Model_Empresas();
        $arr = $mapper->obtenerTodas();
        $this->view->empresas = $arr;
        $username = $this->getRequest()->getCookie('portalUsername');
        if (isset($username)) {
            $this->view->username = $username;
        }
        if (APPLICATION_ENV == "development") {
            $this->view->browser_sync = "<script async src='http://{$this->_config->app->browser_sync}/browser-sync/browser-sync-client.js?v=2.26.7'><\/script>";
        }
    }

    public function olvidePasswordAction() {
        $this->_helper->layout->setLayout("login/index");
        $this->view->title = $this->_appconfig->getParam("title") . " " . "Olvide contraseña";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/js/common/jquery.validate.min.js")
                ->appendFile("/js/default/index/recover.js");
    }

    public function terminosAction() {
        $this->_helper->layout->setLayout("login/index");
        $this->view->title = $this->_appconfig->getParam("title") . " " . "Términos y condiciones de uso";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/js/common/jquery.validate.min.js");
    }

    public function privacidadAction() {
        $this->_helper->layout->setLayout("login/index");
        $this->view->title = $this->_appconfig->getParam("title") . " " . "Aviso de privacidad";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/js/common/jquery.validate.min.js");
    }

    public function contactoAction() {
        $this->_helper->layout->setLayout("login/index");
        $this->view->title = $this->_appconfig->getParam("title") . " " . "Contacto";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/js/common/jquery.validate.min.js");
        $form = new Default_Form_Contacto();
        $this->view->form = $form;
    }

    public function logoutAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $session = new Zend_Session_Namespace($this->_config->app->namespace);
        try {
            $sess = new OAQ_Session($session, $this->_appconfig);
            $sess->logout($this->getRequest()->getCookie('portalUsername'));
            $session->unsetAll();
            Zend_Session::destroy(true);
            $this->_helper->redirector->gotoUrl($this->_appconfig->getParam("link-index") . "?session=Sesión finalizada");
        } catch (Zend_Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

}
