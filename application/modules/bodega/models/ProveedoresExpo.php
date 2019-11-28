<?php

class Bodega_Model_ProveedoresExpo {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Bodega_Model_DbTable_ProveedoresExpo();
    }

}
