<?php

class Trafico_Model_TipoConceptoMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_TraficoTipoConcepto();
    }
    
    public function obtener() {
        try {
            $sql = $this->_db_table->select();
            $stmt = $this->_db_table->fetchAll($sql);
            if($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage());
        }
    }
    

}
