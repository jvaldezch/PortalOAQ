<?php

class Application_Model_CustomsMapper {

    protected $_db;

    public function __construct() {
        $this->_db = Zend_Registry::get("oaqintranet");
    }

    public function getAllCustoms() {
        try {
            $select = $this->_db->select()
                    ->from("aduanas")
                    ->order("aduana ASC");
            $result = $this->_db->fetchAll($select, array());
            if ($result) {
                return $result;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getAllCustomsByPatent($patente) {
        try {
            $select = $this->_db->select()
                    ->from("aduanas", array("aduana", "ubicacion"))
                    ->where("patente = ?", $patente)
                    ->order("aduana ASC");
            $result = $this->_db->fetchAll($select, array());
            if ($result) {
                return $result;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getCustomsByPatent($patente) {
        try {
            $select = $this->_db->select()
                    ->from("aduanas", array("aduana"))
                    ->where("patente = ?", $patente)
                    ->order("aduana ASC");
            $result = $this->_db->fetchAll($select, array());
            if ($result) {
                $data = array();
                foreach ($result as $item) {
                    $data[] = $item["aduana"];
                }
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getAllPatents() {
        try {
            $select = $this->_db->select()
                    ->distinct()
                    ->from("aduanas", array("patente"))
                    ->order("patente ASC");
            $result = $this->_db->fetchAll($select, array());
            if ($result) {
                return $result;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getAllCompanies() {
        try {
            $select = $this->_db->select()
                    ->distinct()
                    ->from("corresponsales", array("nombre", "rfc"))
                    ->order("nombre ASC");
            $result = $this->_db->fetchAll($select, array());
            if ($result) {
                return $result;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getCompanyName($rfc) {
        try {
            $select = $this->_db->select()
                    ->from("corresponsales", array("nombre"))
                    ->where("rfc = ?", $rfc);
            $result = $this->_db->fetchRow($select);
            if ($result) {
                return $result["nombre"];
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
