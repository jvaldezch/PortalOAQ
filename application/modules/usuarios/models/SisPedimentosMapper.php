<?php

class Usuarios_Model_SisPedimentosMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Usuarios_Model_DbTable_SisPedimentos();
    }

    public function getSystems() {
        try {
            $sql = $this->_db_table->select()
                    ->where("env = 'prod'")
                    ->order("id ASC");
            $stmt = $this->_db_table->fetchAll($sql, array());
            if ($stmt) {
                return $stmt->toArray();
            }
            return NULL;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function search($patente, $aduana) {
        try {
            $sql = $this->_db_table->select()
                    ->where("patente = ?", $patente)
                    ->where("aduana LIKE ?", substr($aduana, 0, 2) . "%")
                    ->where("env = 'prod'");
            $stmt = $this->_db_table->fetchRow($sql, array());
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getMySystemData($idSys) {
        try {
            $sql = $this->_db_table->select()
                    ->where("id = ?", $idSys)
                    ->where("env = 'prod'");
            $stmt = $this->_db_table->fetchRow($sql, array());
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
