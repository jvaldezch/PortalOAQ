<?php

class Operaciones_Model_Validador {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Operaciones_Model_DbTable_Validador();
    }

    public function validador($patente, $aduana) {
        try {
            $sql = $this->_db_table->select()
                    ->from("validador")
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("habilitado = 1");
            if (($stmt = $this->_db_table->fetchRow($sql))) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $e->getMessage());
        }
    }

}
