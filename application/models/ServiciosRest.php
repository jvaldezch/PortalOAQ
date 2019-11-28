<?php

class Application_Model_ServiciosRest {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Application_Model_DbTable_ServiciosRest();
    }
    
    public function buscar($patente, $aduana, $tipo = 'rest', $orden = 1) {
        try {
            $sql = $this->_db_table->select()
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("tipo = ?", $tipo)
                    ->where("orden = ?", $orden);
            $stmt = $this->_db_table->fetchRow($sql, array());
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function obtener($id) {
        try {
            $sql = $this->_db_table->select()
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql, array());
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
