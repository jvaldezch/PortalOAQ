<?php

class Webservice_Model_UsuariosSesionesMovil {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Webservice_Model_DbTable_UsuariosSesionesMovil();
    }

    public function verificar($usuario, $deviceId) {
        try {
            $sql = $this->_db_table->select()
                    ->where("usuario = ?", $usuario)
                    ->where("deviceId = ?", $deviceId);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->id;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function verificarSesion($usuario, $token) {
        try {
            $sql = $this->_db_table->select()
                    ->where("usuario = ?", $usuario)
                    ->where("token = ?", $token);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->id;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function actualizarToken($id, $token) {
        $stmt = $this->_db_table->update(array(
                "token" => $token,
                "fecha" => date("Y-m-d H:i:s"),
            ), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
    }
    
    public function agregar($usuario, $token, $deviceId) {
        try {
            $stmt = $this->_db_table->insert(array(
                "token" => $token,
                "usuario" => $usuario,
                "deviceId" => $deviceId,
                "fecha" => date("Y-m-d H:i:s"),
            ));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function actualizar($id, $usuario, $token, $deviceId) {
        try {
            $stmt = $this->_db_table->update(array(
                "token" => $token,
                "usuario" => $usuario,
                "deviceId" => $deviceId,
                "fecha" => date("Y-m-d H:i:s"),
            ), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /*public function challengeCustomer($username) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("c" => "trafico_clientes"), array("id"))
                    ->joinLeft(array("d" => "trafico_cliente_dbs"), "d.idCliente = c.id", array(""))
                    ->where("d.sistema = 'portal' AND d.identificador = 1")
                    ->where("c.rfc LIKE ?", $username);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->id;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function challengeCustomerPassword($id, $password) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("c" => "trafico_clientes"), array(""))
                    ->joinLeft(array("d" => "trafico_cliente_dbs"), "d.idCliente = c.id", array(new Zend_Db_Expr("AES_DECRYPT(`password`,'{$this->_custPassKey}') AS `password`")))
                    ->where("d.sistema = 'portal' AND d.identificador = 1")
                    ->where("c.id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                if ($stmt->password === $password) {
                    return true;
                }
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getCustomerIdentity($id) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("c" => "trafico_clientes"), array("id", "nombre", "rfc"))
                    ->where("c.id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return array(
                    "auth" => true,
                    "usuario" => $stmt->rfc,
                    "id" => $stmt->id,
                    "idRol" => 6,
                    "rfc" => $stmt->rfc,
                    "rol" => "cliente",
                    "nombre" => null,
                    "empresa" => NULL,
                    "email" => null,
                    "aduana" => NULL,
                    "patente" => NULL,
                );
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }*/

}
