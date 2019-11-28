<?php

class Bodega_Model_Proveedores {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Bodega_Model_DbTable_Proveedores();
    }
    
    public function obtenerProveedores($idCliente, $idBodega) {
        try {
            $select = $this->_db_table->select()
                    ->where("idCliente = ?", $idCliente)
                    ->where("idBodega = ?", $idBodega)
                    ->order("nombre ASC");
            $stmt = $this->_db_table->fetchAll($select);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtener($idPro) {
        try {
            $sql = $this->_db_table->select()
                    ->where("id = ?", $idPro);
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
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function buscar($idCliente, $idBodega, $identificador, $nombre) {
        try {
            $select = $this->_db_table->select()
                    ->where("idCliente = ?", $idCliente)
                    ->where("idBodega = ?", $idBodega)
                    ->where("identificador = ?", $identificador)
                    ->where("nombre = ?", $nombre);
            $stmt = $this->_db_table->fetchRow($select);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
