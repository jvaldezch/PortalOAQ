<?php

class Trafico_Model_TraficoObservacionesMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_Observaciones();
    }

    public function obtenerObservaciones($aduana) {
        try {
            $sql = $this->_db_table->select();
            $sql->where('idAduana = ?', $aduana);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
