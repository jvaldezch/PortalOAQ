<?php

class Vucem_Model_VucemPaisesMapper {

    protected $_db_table;

    function __construct() {
        $this->_db_table = new Vucem_Model_DbTable_VucemPaises();
    }

    public function getAllCountries() {
        try {
            $sql = $this->_db_table->select()
                    ->order("nombre ASC");
            if (($stmt = $this->_db_table->fetchAll($sql))) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getAllCve() {
        try {
            $sql = $this->_db_table->select()
                    ->order("cve_pais ASC");
            if (($stmt = $this->_db_table->fetchAll($sql))) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function getName($cvePais) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("nombre"))
                    ->where("cve_pais = ?", $cvePais);
            if (($stmt = $this->_db_table->fetchRow($sql))) {
                return $stmt->nombre;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
