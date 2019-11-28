<?php

class Archivo_Model_ChecklistClientesCampos {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Archivo_Model_DbTable_ChecklistClientesCampos();
    }

    public function getGeneric($tipo = null) {
        try {
            $sql = $this->_db_table->select()
                    ->where("activo = 1")
                    ->order("orden ASC");
            if (isset($tipo)) {
                $sql->where("tipo IN (?)", $tipo);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

}
