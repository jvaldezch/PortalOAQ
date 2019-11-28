<?php

class Rrhh_Model_Empresas {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Rrhh_Model_DbTable_Empresas();
    }
    
    /**
     * 
     * @return boolean|array
     * @throws Exception
     */
    public function obtenerTodas() {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("*"));
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
