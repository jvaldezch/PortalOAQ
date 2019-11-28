<?php

class V2_Model_Trafico_Clientes {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new V2_Model_Trafico_DbTable_Clientes();
    }

    public function obtenerTodos() {
        try {
            $sql = $this->_db_table->select()
                    ->order("nombre ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $arr[""] = "---";
                foreach ($stmt as $item) {
                    $arr[$item["id"]] = $item["nombre"];
                }
                return $arr;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function nombreCliente($idCliente) {
        try {
            $sql = $this->_db_table->select()
                    ->where("id = ?", $idCliente);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                $arr = $stmt->toArray();
                return $arr["nombre"];
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function rfcCliente($idCliente) {
        try {
            $sql = $this->_db_table->select()
                    ->where("id = ?", $idCliente);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                $arr = $stmt->toArray();
                return $arr["rfc"];
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
