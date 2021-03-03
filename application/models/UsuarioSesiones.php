<?php

class Application_Model_UsuarioSesiones {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Application_Model_DbTable_UsuarioSesiones();
    }

    /**
     * 
     * @return type
     * @throws Exception
     */
    public function obtenerTodos() {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("s" => "usuario_sesiones"), array("*"))
                    ->joinLeft(array("u" => "usuarios"), "u.usuario = s.usuario", array("nombre AS nombreUsuario"));
            $stmt = $this->_db_table->fetchAll($sql);
            if (count($stmt)) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param type $usuario
     * @return type
     * @throws Exception
     */
    public function verificarSesion($usuario) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id"))
                    ->where("usuario = ?", $usuario);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return (int) $stmt->id;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    /**
     * 
     * @param type $usuario
     * @param type $token
     * @return boolean
     * @throws Exception
     */
    public function verificarSesionAbierta($usuario, $token) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id"))
                    ->where("usuario = ?", $usuario)
                    ->where("session = ?", $token);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $id
     * @return type
     * @throws Exception
     */
    public function obtenerSesion($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("session"))
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->session;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param type $usuario
     * @param type $ip
     * @return type
     * @throws Exception
     */
    public function verificar($usuario, $ip) {
        try {
            $sql = $this->_db_table->select()
                    ->where("usuario = ?", $usuario)
                    ->where("ip = ?", $ip);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->id;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param string $usuario
     * @param string $ip
     * @param string $session
     * @param string $agent
     * @param string $requestUri
     * @return boolean
     * @throws Exception
     */
    public function agregar($usuario, $ip, $session, $agent, $requestUri = null) {
        try {
            $arr = array(
                "usuario" => $usuario,
                "session" => $session,
                "token" => $session,
                "ip" => $ip,
                "agent" => $agent,
                "fecha" => date("Y-m-d H:i:s"),
            );
            if (isset($requestUri)) {
                $arr["uri"] = $requestUri;
            }
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $id
     * @param string $session
     * @param string $requestUri
     * @return boolean
     * @throws Exception
     */
    public function actualizar($id, $session, $requestUri = null) {
        try {
            $arr = array(
                "fecha" => date("Y-m-d H:i:s"),
                "session" => $session,
                "token" => $session,
            );
            if (isset($requestUri)) {
                $arr["uri"] = $requestUri;
            }
            $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $time
     * @return type
     * @throws Exception
     */
    public function usuariosNoActivos($time = 6800) {
        try {
            $sql = $this->_db_table->select()
                    ->where("TIMESTAMPDIFF(SECOND, fecha, NOW()) > {$time}");
            $stmt = $this->_db_table->fetchAll($sql);
            if (count($stmt)) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param type $id
     * @return boolean
     * @throws Exception
     */
    public function borrar($id) {
        try {
            $stmt = $this->_db_table->delete(array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    /**
     * 
     * @param string $uri
     * @param string $usuario
     * @return boolean
     * @throws Exception
     */
    public function verificarUri($uri) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("usuario"))
                    ->where("uri = ?", $uri);
            $stmt = $this->_db_table->fetchRow($sql);
            if (count($stmt)) {
                return $stmt->usuario;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
