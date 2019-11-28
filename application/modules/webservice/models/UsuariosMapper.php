<?php

class Webservice_Model_UsuariosMapper {

    protected $_db_table;
    protected $_db_cust;
    protected $_db_sess;
    protected $_passKey = "asdlkjkj34729384172312%!";

    public function __construct() {
        $this->_db_table = new Webservice_Model_DbTable_Usuarios();
        $this->_db_cust = new Webservice_Model_ClientesMapper();
        $this->_db_sess = new Application_Model_UsuarioSesiones();
    }

    /**
     * 
     * @param type $username
     * @param type $password
     * @return array
     * @throws Exception
     */
    public function challengeCredentials($username, $password) {
        try {
            $userChallenge = $this->challengeUser($username);
            if ($userChallenge) {
                if (($id = $this->_db_sess->verificarSesion($username))) {
                    $this->_db_sess->borrar($id);
//                    return array(
//                        "username" => "El usuario ya tiene sesión abierta.",
//                    );
                }
                $challenge = $this->challengeUserPassword($userChallenge["id"], $password);
                if ($challenge) {
                    $this->doLogin($userChallenge["id"]);
                    return $this->getUserIdentity($userChallenge["id"]);
                } else {
                    return array(
                        "password" => "Su contraseña es inválida.",
                    );
                }
            }
            $custChallenge = $this->_db_cust->challengeCustomer($username);
            if ($custChallenge) {
                $challenge = $this->_db_cust->challengeCustomerPassword($custChallenge, $password);
                if ($challenge) {
                    $this->doLogin($custChallenge);
                    return $this->_db_cust->getCustomerIdentity($custChallenge);
                } else {
                    return array(
                        "password" => "Su contraseña de cliente es inválida.",
                    );
                }
            }
            if (!$userChallenge) {
                return array(
                    "username" => "El usuario no existe en nuestro sistema.",
                );
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param string $username
     * @return type
     * @throws Exception
     */
    protected function challengeUser($username) {
        try {
            $user = $this->_db_table->select()
                    ->from("usuarios", array("id", "intentos"))
                    ->where("usuario LIKE ?", $username)
                    ->where("estatus = 1");
            $userChallenge = $this->_db_table->fetchRow($user, array());
            if ($userChallenge) {
                return $userChallenge;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function challengeUserPassword($id, $password) {
        try {
            $pass = $this->_db_table->select()
                    ->from("usuarios", array("AES_DECRYPT(password,'{$this->_passKey}') AS password"))
                    ->where("id = ?", $id)
                    ->where("estatus = 1");
            $passChallenge = $this->_db_table->fetchRow($pass, array());
            if ($passChallenge) {
                if ($passChallenge["password"] === $password) {
                    return true;
                } else {
                    return;
                }
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function getUserIdentity($id) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("u" => "usuarios"), array("id", "nombre", "usuario", "aduana", "patente", "rfc", "email", "rol", "empresa"))
                    ->joinLeft(array("r" => "usuarios_roles"), "r.nombre = u.rol", array("id as idRol"))
                    ->where("u.id = ?", $id)
                    ->where("u.estatus = 1");
            $stmt = $this->_db_table->fetchRow($sql, array());
            if ($stmt) {
                return array(
                    "auth" => true,
                    "id" => $stmt["id"],
                    "idRol" => $stmt["idRol"],
                    "usuario" => $stmt["usuario"],
                    "rfc" => $stmt["rfc"],
                    "rol" => $stmt["rol"],
                    "nombre" => $stmt["nombre"],
                    "empresa" => $stmt["empresa"],
                    "email" => $stmt["email"],
                    "aduana" => $stmt["aduana"],
                    "patente" => $stmt["patente"],
                );
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getUserConfig($id) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("u" => "usuarios"), array("id", "nombre", "usuario", "aduana", "patente", "rfc", "email", "rol", "empresa"))
                    ->joinLeft(array("r" => "usuarios_roles"), "r.nombre = u.rol", array("id as idRol"))
                    ->where("u.id = ?", $id)
                    ->where("u.estatus = 1");
            $stmt = $this->_db_table->fetchRow($sql, array());
            if ($stmt) {
                return array(
                    "idRol" => $stmt["idRol"],
                    "rol" => $stmt["rol"],
                );
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function doLogin($id) {
        try {
            $arr = array(
                "acceso" => date("Y-m-d H:i:s"),
                "intentos" => 0,
            );
            $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function updateTry($id, $num) {
        try {
            $this->_db_table->update(array("intentos" => $num), array("id = ?" => $id));
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function recoverPassword($username) {
        try {
            $user = $this->_db_table->select()
                    ->from("usuarios", array("email", "AES_DECRYPT(password,'{$this->_passKey}') AS password"))
                    ->where("usuario LIKE ?", $username)
                    ->where("estatus = 1");
            $stmt = $this->_db_table->fetchRow($user, array());
            if ($stmt) {
                $mail = new OAQ_EmailNotifications();
                $sent = $mail->sendForgotPasswordEmail($username, $stmt["password"], $stmt["email"]);
                if ($sent) {
                    return array(
                        "sent" => "<b>Info:</b> Un email ha sido enviado con su contraseña.",
                    );
                }
            } else {
                return array(
                    "username" => "El usuario no existe en nuestro sistema.",
                );
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
