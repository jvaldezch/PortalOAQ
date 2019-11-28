<?php

class Trafico_Model_TraficoServiciosMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_TraficoServicios();
    }

    public function obtenerServicios($aduana) {
        try {
            $sql = $this->_db_table->select()
                    ->where('idAduana = ?', $aduana);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $ex) {
            throw new Exception($this->_throwException("Db Exception found on", __METHOD__, $ex));
        }
    }

}
