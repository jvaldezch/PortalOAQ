<?php

class Pedimento_Model_Monedas {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Pedimento_Model_DbTable_Monedas();
    }

    public function obtenerTodos() {
        $sql = $this->_db_table->select()
                ->order("clave ASC");
        $stmt = $this->_db_table->fetchAll($sql);
        if ($stmt) {
            return $stmt->toArray();
        }
        return null;
    }

}
