<?php

class Trafico_Model_CatAduanas {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_CatAduanas();
    }

    public function todas() {
        try {
            $sql = $this->_db_table->select()
                    ->order("clave ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function obtenerNombre($clave) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("nombre"))
                    ->where("clave = ?", $clave);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->nombre;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

}
