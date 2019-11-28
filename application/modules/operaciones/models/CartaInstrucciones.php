<?php

class Operaciones_Model_CartaInstrucciones {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Operaciones_Model_DbTable_CartaInstrucciones();
    }

    public function cartasSelect($idsClientes = null) {
        try {
            $sql = $this->_db_table->select();
            if (isset($idsClientes) && !empty($idsClientes)) {
                $sql->where("idCliente IN (?)", $idsClientes);
            }
            return $sql;
        } catch (Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function obtener($id) {
        try {            
            $sql = $this->_db_table->select()
                    ->where("id = ?", $id);            
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizar($id, $arr) {
        try {           
            $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    

    public function verificar($idCliente, $numCarta) {
        try {           
            $sql = $this->_db_table->select()
                    ->where("idCliente = ?", $idCliente)
                    ->where("numCarta = ?", $numCarta);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregar($arr) {
        try {           
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
}
