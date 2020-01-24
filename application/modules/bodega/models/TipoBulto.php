<?php

class Bodega_Model_TipoBulto {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Bodega_Model_DbTable_TipoBulto();
    }
    
    public function obtenerTodos() {
        $sql = $this->_db_table->select()
                ->order("descripcion ASC");
        $stmt = $this->_db_table->fetchAll($sql);
        if ($stmt) {
            return $stmt->toArray();
        }
        return null;
    }
}
