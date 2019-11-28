<?php

class Default_AjaxController extends Zend_Controller_Action {

    protected $_session;
    protected $_appconfig;
    protected $_config;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $contextSwitch = $this->_helper->getHelper("contextSwitch");
        $contextSwitch->addActionContext("verify-session", "json")
                ->addActionContext("login", "json")
                ->addActionContext("recover-password", "json")
                ->initContext();
    }

    public function preDispatch() {
        $this->_session = new Zend_Session_Namespace($this->_config->app->namespace);
        if ($this->_session->authenticated == true) {
            $this->_session->setExpirationSeconds($this->_appconfig->getParam("session-exp"));
        } else {
            $this->_session = false;
        }
    }

    public function verifySessionAction() {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $sessCookie = $this->getRequest()->getCookie('portalSession');
                if ($this->_session === false) {
                    setcookie("logout", "true", null, "/");
                    $this->_helper->json(array("success" => false, "message" => "No session found."));
                }
                $session = new OAQ_Session($this->_session, $this->_appconfig);
                if (!($sess = $session->verificarDb())) {
                    $this->_helper->json(array("success" => false, "message" => "Session doesn't match."));
                }
                if ($sess !== $sessCookie) {
                    $this->_helper->json(array("success" => false, "message" => "Different session found."));
                }
                $session->actualizarSesion();
                $this->_helper->json(array("success" => true));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function recoverPasswordAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "username" => array("StringToLower"),
                );
                $v = array(
                    "username" => array("NotEmpty"),
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("username")) {
                    $model = new OAQ_Auth();
                    $auth = $model->recoverPassword($i->username);
                    if (isset($auth["username"])) {
                        $this->_helper->json(array("success" => false, "username" => $auth["username"]));
                    }
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function loginAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "username" => array("StringToLower"),
                );
                $v = array(
                    "username" => array("NotEmpty"),
                    "password" => array("NotEmpty"),
                );
                $i = new Zend_Filter_Input($f, $v, $r->getPost());
                if ($i->isValid("username") && $i->isValid("password")) {
                    /// -----
                    $sessCookie = $this->getRequest()->getCookie('portalSession');
                    if (isset($sessCookie) && $sessCookie !== "") {
                        $mppr = new Application_Model_UsuarioSesiones();
                        if ($mppr->verificarSesionAbierta($i->username, $sessCookie) === true) {
                            $session = new OAQ_Session(new Zend_Session_Namespace($this->_config->app->namespace), $this->_appconfig);
                            if ($session->forceLogin($i->username)) {
                                setcookie("logout", "false", null, "/");
                                $this->_helper->json(array("success" => true));
                                return;
                            }
                        }
                        setcookie("portalSession", "", null, "/");
                    }
                    /// -----
                    $model = new OAQ_Auth();
                    $auth = $model->challengeCredentials($i->username, $i->password);
                    if (isset($auth) && !isset($auth["auth"])) {
                        if (isset($auth["username"])) {
                            $this->_helper->json(array("success" => false, "username" => $auth["username"]));
                        }
                        if (isset($auth["password"])) {
                            $this->_helper->json(array("success" => false, "password" => $auth["password"]));
                        }
                    } elseif ($auth["auth"] === true) {
                        Zend_Session::regenerateId();
                        $this->_session = new Zend_Session_Namespace($this->_config->app->namespace);
                        if ($this->_session->isLocked()) {
                            $this->_session->unLock();
                            $this->_session->setExpirationSeconds($this->_appconfig->getParam("session-exp"));
                        }
                        $this->_session->authenticated = true;
                        $this->_session->id = $auth["id"];
                        $this->_session->idRol = isset($auth["idRol"]) ? $auth["idRol"] : 6;
                        $this->_session->username = $auth["usuario"];
                        $this->_session->nombre = $auth["nombre"];
                        $this->_session->rfc = $auth["rfc"];
                        $this->_session->empresa = $auth["empresa"];
                        $this->_session->role = $auth["rol"]; // returns array
                        $this->_session->email = $auth["email"];
                        $this->_session->patente = $auth["patente"];
                        $this->_session->aduana = $auth["aduana"];
                        $this->_session->winid = sha1(time() . $auth["usuario"]);
                        $this->_session->lock();
                        setcookie("logout", "false", null, "/");
                        $this->_helper->json(array("success" => true));
                    }
                    $this->_helper->json(array("success" => false, "password" => "Unknown error!"));
                } else {
                    $this->_helper->json(array("success" => false));
                }
            } else {
                throw new Exception("Error Processing Request", 1);
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
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
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }

}
