<?php

/**
 * Description of EmailNotifications
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class OAQ_Session {

    protected $config;
    protected $session;

    function __construct(Zend_Session_Namespace $session, Application_Model_ConfigMapper $config) {
        $this->config = $config;
        $this->session = $session;
    }

    public function actualizar() {
        $mppr = new Webservice_Model_UsuariosMapper();
        $arr = $mppr->getUserConfig($this->session->id);
        if ($this->session->role != "cliente") {
            $this->session->unLock();
            $this->session->setExpirationSeconds($this->config->getParam("session-exp"));
            $this->session->idRol = $arr["idRol"];
            $this->session->role = $arr["rol"];
            $this->session->lock();
        }
        return true;
    }

    protected function _hash($value) {
        return base64_encode(sha1($value));
        /* $bcrypt = password_hash($value, PASSWORD_BCRYPT, array('cost' => 11));
          return base64_encode($bcrypt); */
    }

    public function actualizarSesion($requestedUri = null) {
        $mppr = new Application_Model_UsuarioSesiones();
        $token = $this->_hash(Zend_Session::getId());
        if (!($id = $mppr->verificar($this->session->username, $this->_getUserIP()))) {
            $mppr->agregar($this->session->username, $this->_getUserIP(), $token, $this->_getAgent(), $requestedUri);
        } else {
            $mppr->actualizar($id, $token, $requestedUri);
        }
        setcookie('portalUsername', $this->session->username, time() + (3600 * 24 * 5), '/');
        setcookie('portalSession', $token, time() + (3600 * 24 * 5), '/');
    }

    protected function _cleanSessions() {
        $cobranza = new Zend_Session_Namespace("OAQCobranza");
        $ctagastos = new Zend_Session_Namespace("OAQCtaGastos");
        $trafico = new Zend_Session_Namespace("TraficoOAQ");
        $coves = new Zend_Session_Namespace("OAQCoves");
        $validacion = new Zend_Session_Namespace("OAQAvalidacion");
        $xml = new Zend_Session_Namespace("OAQXml");
        $controlTrafico = new Zend_Session_Namespace("ControlTraficoOAQ");
        $nav = new Zend_Session_Namespace("Navigation");
        $svucem = new Zend_Session_Namespace("OAQVucem");
        $ctagastos->unsetAll();
        $cobranza->unsetAll();
        $trafico->unsetAll();
        $coves->unsetAll();
        $xml->unsetAll();
        $validacion->unsetAll();
        $controlTrafico->unsetAll();
        $nav->unsetAll();
        $svucem->unsetAll();
    }

    public function logout($username) {
        $mppr = new Application_Model_UsuarioSesiones();
        $user = isset($this->session->username) ? $this->session->username : $username;
        if (isset($user)) {
            if (($id = $mppr->verificarSesion($user))) {
                setcookie("portalSession", "", null, "/");
                $this->_cleanSessions();
                //$mppr->borrar($id);  // necesario para no eliminar todas las sesiones
            }
        }
        return;
    }

    public function verificarDb() {
        $mppr = new Application_Model_UsuarioSesiones();
        if (($id = $mppr->verificarSesion($this->session->username))) {
            $session = $mppr->obtenerSesion($id);
            if ($session) {
                return $session;
            }
            return;
        }
        return;
    }

    public function forceLogin($username) {
        $mppr = new Usuarios_Model_UsuariosMapper();
        $arr = $mppr->obtenerUsuario(null, $username);
        if (!empty($arr)) {
            $this->session->authenticated = true;
            $this->session->id = $arr["id"];
            $this->session->idRol = isset($arr["idRol"]) ? $arr["idRol"] : 6;
            $this->session->username = $username;
            $this->session->nombre = $arr["nombre"];
            $this->session->rfc = $arr["rfc"];
            $this->session->empresa = $arr["empresa"];
            $this->session->role = $arr["rol"]; // returns array
            $this->session->email = $arr["email"];
            $this->session->patente = $arr["patenteUsuario"];
            $this->session->aduana = $arr["aduanaUsuario"];
            $this->session->winid = sha1(time() . $arr["usuario"]);
            $this->session->lock();
            return true;
        }
        return;
    }

    protected function _getAgent() {
        return $_SERVER['HTTP_USER_AGENT'];
    }

    protected function _getUserIP() {
        $client = @$_SERVER["HTTP_CLIENT_IP"];
        $forward = @$_SERVER["HTTP_X_FORWARDED_FOR"];
        $remote = $_SERVER["REMOTE_ADDR"];
        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } else {
            $ip = $remote;
        }
        return $ip;
    }

    public function revisarUri($uri) {
        $mppr = new Application_Model_UsuarioSesiones();
        if (($res = $mppr->verificarUri($uri))) {
            return $res;
        }
        return;
    }

}
