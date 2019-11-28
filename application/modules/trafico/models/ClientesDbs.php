<?php

class Trafico_Model_ClientesDbs {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_ClientesDbs();
    }

    public function verificarSistema($idCliente, $sistema) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id"))
                    ->where("idCliente = ?", $idCliente)
                    ->where("sistema = ?", $sistema);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->id;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregar($idCliente, $identificador, $sistema, $usuario) {
        try {
            $arr = array(
                "idCliente" => $idCliente,
                "identificador" => $identificador,
                "sistema" => $sistema,
                "creado" => date("Y-m-d H:i:s"),
                "creadoPor" => $usuario,
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

    public function agregarPassword($idCliente, $password, $usuario) {
        try {
            $arr = array(
                "idCliente" => $idCliente,
                "password" => new Zend_Db_Expr("AES_ENCRYPT('{$password}','oaqlkjkj3asdjaksdjqweuiuyyASDQWEksald')"),
                "identificador" => 1,
                "sistema" => "portal",
                "creado" => date("Y-m-d H:i:s"),
                "creadoPor" => $usuario,
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

    public function actualizar($id, $identificador, $usuario) {
        try {
            $arr = array(
                "identificador" => $identificador,
                "modificado" => date("Y-m-d H:i:s"),
                "modificadoPor" => $usuario,
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

    public function noAcceso($id, $usuario) {
        try {
            $arr = array(
                "identificador" => 0,
                "modificado" => date("Y-m-d H:i:s"),
                "modificadoPor" => $usuario,
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

    public function actualizarPassword($id, $password, $usuario) {
        try {
            $arr = array(
                "password" => new Zend_Db_Expr("AES_ENCRYPT('{$password}','oaqlkjkj3asdjaksdjqweuiuyyASDQWEksald')"),
                "modificado" => date("Y-m-d H:i:s"),
                "modificadoPor" => $usuario,
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

}
