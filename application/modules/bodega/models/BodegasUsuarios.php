<?php

class Bodega_Model_BodegasUsuarios {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Bodega_Model_DbTable_BodegasUsuarios();
    }
    
    public function obtenerTodos($idUsuario) {
        try {
            $select = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("u" => "trafico_usubodegas"), array("id"))
                    ->joinLeft(array("b" => "trafico_bodegas"), "b.id = u.idBodega", array("nombre AS nombreBodega"))
                    ->where("idUsuario = ?", $idUsuario);
            $stmt = $this->_db_table->fetchAll($select);
            if ($stmt) {                
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function obtenerBodegas($idUsuario) {
        try {
            $select = $this->_db_table->select()
                    ->from($this->_db_table, array("idBodega"))
                    ->where("idUsuario = ?", $idUsuario);
            $stmt = $this->_db_table->fetchAll($select);
            if ($stmt) {                
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function verificar($idUsuario, $idBodega) {
        try {
            $select = $this->_db_table->select()
                    ->where("idUsuario = ?", $idUsuario)
                    ->where("idBodega = ?", $idBodega);
            $stmt = $this->_db_table->fetchRow($select);
            if ($stmt) {                
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function agregar($idUsuario, $idBodega) {
        try {
            $stmt = $this->_db_table->insert(array(
                "idUsuario" => $idUsuario,
                "idBodega" => $idBodega,
                "creado" => date("Y-m-d H:i:s"),
                "activo" => 1,
            ));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function borrar($idUsuario, $id) {
        try {
            
            
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
