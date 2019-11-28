<?php

class Operaciones_Model_Incidencias {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Operaciones_Model_DbTable_Incidencias();
    }

    public function incidenciasSelect() {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("i" => "incidencias"), array("*"))
                    ->joinLeft(array("c" => "trafico_clientes"), "i.idCliente = c.id", array("nombre"))
                    ->joinLeft(array("a" => "trafico_aduanas"), "i.idAduana = a.id", array("patente", "aduana"));
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
    

    public function verificar($idAduana, $idCliente, $acta) {
        try {           
            $sql = $this->_db_table->select()
                    ->where("idAduana = ?", $idAduana)
                    ->where("idCliente = ?", $idCliente)
                    ->where("acta = ?", $acta);
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

    public function borrar($id) {
        try {           
            $stmt = $this->_db_table->delete(array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
}
