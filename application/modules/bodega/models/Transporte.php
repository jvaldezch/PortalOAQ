<?php

class Bodega_Model_Transporte {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Bodega_Model_DbTable_Transporte();
    }
    
    public function obtenerPorBodega($idBodega) {
        try {
            $select = $this->_db_table->select()
                    ->where("idBodega = ?", $idBodega)
                    ->order("nombre ASC");
            $stmt = $this->_db_table->fetchAll($select);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
