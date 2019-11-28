<?php

class Trafico_Model_TarifaVigencias {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_TarifaVigencias();
    }

    public function obtenerTodos() {
        try {
            $sql = $this->_db_table->select();
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
