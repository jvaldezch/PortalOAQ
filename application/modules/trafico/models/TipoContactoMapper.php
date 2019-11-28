<?php

class Trafico_Model_TipoContactoMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_TipoContacto();
    }

    public function obtenerTodos() {
        try {
            $sql = $this->_db_table->select()
                    ->order('tipo ASC');
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

}
