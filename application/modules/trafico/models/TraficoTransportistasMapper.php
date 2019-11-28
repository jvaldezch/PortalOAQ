<?php

class Trafico_Model_TraficoTransportistasMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_TraficoTransportistas();
    }

    public function obtenerTransportistas($aduana, $tipo) {
        try {
            $sql = $this->_db_table->select()
                    ->where('idAduana = ?', $aduana)
                    ->where('tipo = ?', $tipo);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " >> " . $e->getMessage());
        }
    }

}
