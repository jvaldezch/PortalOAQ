<?php

class Bodega_Model_Bodegas {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Bodega_Model_DbTable_Bodegas();
    }
    
    public function obtenerTodos() {
        try {
            $select = $this->_db_table->select();
            $stmt = $this->_db_table->fetchAll($select);
            if ($stmt) {
                $data[""] = "---";
                foreach ($stmt as $item) {
                    $data[$item["id"]] = $item["nombre"];
                }
                return $data;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function obtenerSiglas($idBodega) {
        try {
            $sql = $this->_db_table->select()
                    ->where('id = ?', $idBodega);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->siglas;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function obtener($idBodega) {
        try {
            $sql = $this->_db_table->select()
                    ->where('id = ?', $idBodega);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function obtenerDatos($idBodega) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("b" => "trafico_bodegas"), array("*"))
                    ->joinLeft(array("d" => "trafico_bodega_direccion"), "b.id = d.idBodega", array("*"))
                    ->where('b.id = ?', $idBodega);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
