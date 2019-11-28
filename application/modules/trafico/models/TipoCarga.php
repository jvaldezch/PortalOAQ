<?php

class Trafico_Model_TipoCarga {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_TipoCarga();
    }

    public function obtener($tipoAduana) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id", "tipoCarga AS descripcion"))
                    ->where("tipoAduana = ?", $tipoAduana)
                    ->where("activo = 1")
                    ->order("tipoCarga ASC");
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
