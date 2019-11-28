<?php

class Trafico_Model_ClientesPlantas {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_ClientesPlantas();
    }

    public function verificar($idCliente, $clave) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id"))
                    ->where("idCliente = ?", $idCliente)
                    ->where("clave = ?", $clave);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return (int) $stmt->id;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function actualizar($idCliente, $idPlanta, $arr) {
        try {
            $stmt = $this->_db_table->update($arr, array("idCliente = ?" => $idCliente, "id = ?" => $idPlanta));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " >> " . $e->getMessage());
        }
    }

    public function agregar($data) {
        try {
            $stmt = $this->_db_table->insert($data);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " >> " . $e->getMessage());
        }
    }
    
    public function borrar($idCliente, $idPlanta) {
        try {
            $stmt = $this->_db_table->delete(array("idCliente = ?" => $idCliente, "id = ?" => $idPlanta));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " >> " . $e->getMessage());
        }
    }

    public function obtener($idCliente, $id = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("*"))
                    ->where("idCliente = ?", $idCliente);
            if (!isset($id)) {
                $stmt = $this->_db_table->fetchAll($sql);
            } else {
                $sql->where("id = ?", $id);
                $stmt = $this->_db_table->fetchRow($sql);
            }
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
