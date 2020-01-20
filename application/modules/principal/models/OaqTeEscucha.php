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

}
