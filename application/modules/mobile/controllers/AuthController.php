<?php

class Mobile_AuthController extends Zend_Controller_Action {

    protected $_session;
    protected $_config;
    protected $_appconfig;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
    }

    public function preDispatch() {
        
    }

    public function loginAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "username" => array("StringToLower"),
                );
                $v = array(
                    "username" => array("NotEmpty"),
                    "password" => array("NotEmpty"),
                );
                $i = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($i->isValid("username") && $i->isValid("password")) {
                    $model = new OAQ_Auth();
                    $auth = $model->challengeCredentials($i->username, $i->password);
                    if (isset($auth) && !isset($auth["auth"])) {
                        if (isset($auth["username"])) {
                            $this->_helper->json(array("success" => false, "message" => $auth["username"]));
                        }
                        if (isset($auth["password"])) {
                            $this->_helper->json(array("success" => false, "message" => $auth["password"]));
                        }
                    } elseif ($auth["auth"] === true) {
                        Zend_Session::regenerateId();
                        $this->_session = new Zend_Session_Namespace("OAQmobile");
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
    
}
