<?php

class Usuarios_Model_UsuarioInhouse {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Usuarios_Model_DbTable_UsuarioInhouse();
    }

    /**
     * 
     * @param int $idUsuario
     * @param int $idCliente
     * @return boolean
     * @throws Exception
     */
    public function verificar($idUsuario, $idCliente) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("*"))
                    ->where("idUsuario = ?", $idUsuario)
                    ->where("idCliente = ?", $idCliente);
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
     * @param int $idUsuario
     * @param int $idCliente
     * @return boolean
     * @throws Exception
     */
    public function agregar($idUsuario, $idCliente) {
        try {
            $arr = array(
                "idUsuario" => $idUsuario,
                "idCliente" => $idCliente,
                "creado" => date("Y-m-d H:i:s")
            );
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
     * @param int $idUsuario
     * @return type
     * @throws Exception
     */
    public function obtenerRfcClientes($idUsuario) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("i" => "usuario_inhouse"), array(""))
                    ->joinLeft(array("c" => "trafico_clientes"), "c.id = i.idCliente", array("rfc"))
                    ->where("i.idUsuario = ?", $idUsuario);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $arr = [];
                foreach ($stmt->toArray() as $item) {
                    $arr[] = $item["rfc"];
                }
                return $arr;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function obtenerTodosClientes() {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("c" => "trafico_clientes"), array("id"));
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $arr = [];
                foreach ($stmt->toArray() as $item) {
                    $arr[] = $item["id"];
                }
                return $arr;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function obtenerIdClientes($idUsuario) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("i" => "usuario_inhouse"), array(""))
                    ->joinLeft(array("c" => "trafico_clientes"), "c.id = i.idCliente", array("id"))
                    ->where("i.idUsuario = ?", $idUsuario);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $arr = [];
                foreach ($stmt->toArray() as $item) {
                    $arr[] = $item["id"];
                }
                return $arr;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $idUsuario
     * @return type
     * @throws Exception
     */
    public function obtenerClientes($idUsuario) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("i" => "usuario_inhouse"), array("id"))
                    ->joinLeft(array("c" => "trafico_clientes"), "c.id = i.idCliente", array("rfc", "nombre"))
                    ->where("i.idUsuario = ?", $idUsuario)
                    ->order("c.nombre ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
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

}
