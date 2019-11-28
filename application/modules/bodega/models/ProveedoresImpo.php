<?php

class Bodega_Model_ProveedoresImpo {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Bodega_Model_DbTable_ProveedoresImpo();
    }
    
    public function obtener($idCliente) {
        try {
            $select = $this->_db_table->select()
                    ->where("idCliente = ?", $idCliente);
            $stmt = $this->_db_table->fetchAll($select);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {  
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
