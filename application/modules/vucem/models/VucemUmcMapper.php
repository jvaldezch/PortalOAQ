<?php

class Vucem_Model_VucemUmcMapper {

    protected $_db_table;

    function __construct() {
        $this->_db_table = new Vucem_Model_DbTable_VucemUmc();
    }

    public function getAllUnits() {
        try {
            $sql = $this->_db_table->select();
            $sql->order('clave ASC');
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getUmcDesc($cve) {
        try {
            $sql = $this->_db_table->select();
            $sql->where('clave = ?', $cve);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt["desc"];
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
