<?php

class Trafico_Model_TraficoTipoFacturacionMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_TraficoTipoFacturacion();
    }
    
    public function obtenerTiposFacturacion($idCliente, $idAduana) {
        try {
            $sql = $this->_db_table->select()
                    ->where('idCliente = ?', $idCliente)
                    ->where('idAduana = ?', $idAduana);
            $stmt = $this->_db_table->fetchAll($sql);
            if($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " >> " . $e->getMessage());
        }
    }
    
    public function verificar($idAduana, $idCliente, $nombre) {
        try {
            $sql = $this->_db_table->select()
                    ->where('idAduana = ?', $idAduana)
                    ->where('idCliente = ?', $idCliente)
                    ->where('nombre = ?', $nombre);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " >> " . $e->getMessage());
        }
    }

    public function agregar($data) {
        try {
            $stmt = $this->_db_table->insert($data);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " >> " . $e->getMessage());
        }
    }

}
