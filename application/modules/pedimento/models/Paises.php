<?php

class Pedimento_Model_Paises {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Pedimento_Model_DbTable_Paises();
    }

    public function obtenerTodos() {
        $sql = $this->_db_table->select()
                ->order("clave_m3 ASC");
        $stmt = $this->_db_table->fetchAll($sql);
        if ($stmt) {
            return $stmt->toArray();
        }
        return null;
    }

}
