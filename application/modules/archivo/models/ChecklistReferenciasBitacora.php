<?php

class Archivo_Model_ChecklistReferenciasBitacora {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Archivo_Model_DbTable_ChecklistReferenciasBitacora();
    }

    public function agregar($data) {
        try {
            $stmt = $this->_db_table->insert($data);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function obtener($patente, $aduana, $pedimento, $referencia) {
        try {
            $sql = $this->_db_table->select()
                    ->where("patente = ?", $patente)
                    ->where("aduana LIKE ?", substr($aduana, 0, 2) . '%')
                    ->where("pedimento = ?", $pedimento)
                    ->where("referencia = ?", $referencia)
                    ->where("tipo IS NULL");
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
