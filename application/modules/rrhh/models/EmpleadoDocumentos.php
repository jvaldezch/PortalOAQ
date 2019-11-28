<?php

class Rrhh_Model_EmpleadoDocumentos {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Rrhh_Model_DbTable_EmpleadoDocumentos();
    }

    public function init() {
        try {
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
