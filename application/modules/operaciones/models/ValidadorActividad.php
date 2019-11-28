<?php

class Operaciones_Model_ValidadorActividad {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Operaciones_Model_DbTable_ValidadorActividad();
    }

    public function obtener($patente, $aduana, $pedimento) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array('mensaje', 'creado'))
                    ->where('patente =? ', $patente)
                    ->where('aduana =? ', $aduana)
                    ->where('pedimento =? ', $pedimento)
                    ->order('creado DESC');
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
