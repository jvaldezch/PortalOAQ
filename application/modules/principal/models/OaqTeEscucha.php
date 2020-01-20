<?php

class Principal_Model_OaqTeEscucha {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Principal_Model_DbTable_OaqTeEscucha();
    }

    public function agregar($arr) {
        $stmt = $this->_db_table->insert($arr);
        if ($stmt) {
            return true;
        }
        return null;
    }

    public function obtenerTodos() {
        $sql = $this->_db_table->select();
        $stmt = $this->_db_table->fetchAll($sql);
        if ($stmt) {
            return $stmt->toArray();
        }
        return null;
    }

}
