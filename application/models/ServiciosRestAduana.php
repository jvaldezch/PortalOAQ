<?php

class Application_Model_ServiciosRestAduana {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Application_Model_DbTable_ServiciosRestAduana();
    }
    
    public function obtenerSistema($idAduana) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("a" => "servicios_rest_aduana"), array("*"))
                    ->joinLeft(array("s" => "servicios_rest"), "s.id = a.idServicio", array(""))
                    ->where("a.idAduana = ?", $idAduana)
                    ->where("s.activo = 1");
            $stmt = $this->_db_table->fetchRow($sql, array());
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function obtenerServicio($idAduana, $sistema) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("a" => "servicios_rest_aduana"), array("*"))
                    ->joinLeft(array("s" => "servicios_rest"), "s.id = a.idServicio", array(""))
                    ->where("a.idAduana = ?", $idAduana)
                    ->where("s.sistema = ?", $sistema);
            $stmt = $this->_db_table->fetchRow($sql, array());
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function obtenerSistemas($idAduana = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("a" => "servicios_rest_aduana"), array("*"))
                    ->joinLeft(array("s" => "servicios_rest"), "s.id = a.idServicio", array(""))                    
                    ->where("s.activo = 1");
            if (isset($idAduana)) {
               $sql->where("a.idAduana = ?", $idAduana);
            }
            $stmt = $this->_db_table->fetchAll($sql, array());
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
