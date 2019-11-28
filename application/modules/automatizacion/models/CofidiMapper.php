<?php

class Automatizacion_Model_CofidiMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Automatizacion_Model_DbTable_Cofidi();
    }

    public function clientes($rfc = null) {
        try {
            $select = $this->_db_table->select()
                    ->from($this->_db_table);
            if (isset($rfc) && $rfc != "") {
                $select->where("rfc = ?", $rfc);
            }
            $result = $this->_db_table->fetchAll($select);
            if ($result) {
                return $result->toArray();
            }
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function obtenerCliente($rfc) {
        try {
            $select = $this->_db_table->select()
                    ->from($this->_db_table)
                    ->where("rfc = ?", $rfc);
            $result = $this->_db_table->fetchRow($select);
            if ($result) {
                return $result->toArray();
            }
            return null;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function obtenerCofidi($rfc) {
        try {
            $select = $this->_db_table->select()
                    ->from($this->_db_table)
                    ->where("rfc = ?", $rfc)
                    ->where("tipo = 'cofidi'");
            $result = $this->_db_table->fetchRow($select);
            if ($result) {
                return $result->toArray();
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

}
