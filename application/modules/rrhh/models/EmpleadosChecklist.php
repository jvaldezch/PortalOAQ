<?php

class Rrhh_Model_EmpleadosChecklist {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Rrhh_Model_DbTable_EmpleadosChecklist();
    }

}
