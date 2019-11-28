<?php

class Dashboard_Model_Clima {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Dashboard_Model_DbTable_Clima();
    }
    
    public function obtener() {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("*"))
                    ->order("fecha")
                    ->limit(1);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Zend_Exception $ex) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

}
