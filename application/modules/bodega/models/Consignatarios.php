<?php

class Bodega_Model_Consignatarios {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Bodega_Model_DbTable_Consignatarios();
    }

}
