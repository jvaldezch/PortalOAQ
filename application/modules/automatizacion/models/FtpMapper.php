<?php

class Automatizacion_Model_FtpMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Automatizacion_Model_DbTable_Ftp();
    }

    public function getByRfc($rfc, $type) {
        try {
            $select = $this->_db_table->select()
                    ->where("rfc = ?", $rfc)
                    ->where("type = ?", $type)
                    ->where("active = 1");
            $stmt = $this->_db_table->fetchRow($select);
            if ($stmt) {
                return $stmt->toArray();
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $e->getMessage());
        }
    }

    public function getByType($type, $rfc = null) {
        try {
            $select = $this->_db_table->select()
                    ->where("type = ?", $type)
                    ->where("active = 1");
            if (isset($rfc) && !is_array($rfc)) {
                $select->where("rfc = ?", $rfc);
            } elseif (isset($rfc) && is_array($rfc)) {
                $select->where("rfc IN (?)", $rfc);
            }
            $stmt = $this->_db_table->fetchAll($select);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $e->getMessage());
        }
    }

    public function obtenerDatosFtp($rfcCliente) {
        try {
            $select = $this->_db_table->select()
                    ->where("rfc = ?", $rfcCliente)
                    ->where("type = 'expedientes'")
                    ->where("active = 1");
            $stmt = $this->_db_table->fetchRow($select);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $e->getMessage());
        }
    }

}
