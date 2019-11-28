<?php

class Operaciones_Model_PlantillaPedimentos {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Operaciones_Model_DbTable_PlantillaPedimentos();
    }

    public function pedimentos() {
        try {
            $sql = $this->_db_table->select()
                    ->limit(20);
            if (($stmt = $this->_db_table->fetchAll($sql))) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
