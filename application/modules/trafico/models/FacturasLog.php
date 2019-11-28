<?php

class Trafico_Model_FacturasLog {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_FacturasLog();
    }
    
    public function add($arr) {
        try {
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
}
