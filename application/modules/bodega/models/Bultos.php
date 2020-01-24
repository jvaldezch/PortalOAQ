<?php

class Bodega_Model_Bultos {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Bodega_Model_DbTable_Bultos();
    }

    public function verificar($numBulto, $idTrafico) {
        try {
            $sql = $this->_db_table->select()
                    ->where("numBulto = ?", $numBulto)
                    ->where("idTrafico = ?", $idTrafico);
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

    public function actualizar($idBulto, $arr) {
        try {
            $stmt = $this->_db_table->update($arr, array("id = ?" => $idBulto));
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function borrar($idBulto) {
        try {
            $stmt = $this->_db_table->delete(array("id = ?" => $idBulto));
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function ultimoBulto($idTrafico) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("numBulto"))
                    ->where("idTrafico = ?", $idTrafico)
                    ->order("numBulto DESC")
                    ->limit(1);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->numBulto;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function totalBultos($idTrafico) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("COUNT(*) AS total"))
                    ->where("idTrafico = ?", $idTrafico);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->total;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function obtenerBultos($idTrafico) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("b" => "trafico_bultos"), array("*"))
                    ->joinLeft(array("t" => "trafico_bulto_tipos"), "t.id = b.tipoBulto", array("descripcion AS nombreBulto"))
                    ->where("b.idTrafico = ?", $idTrafico)
                    ->order("b.numBulto ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function obtenerBultosByIds($ids) {
        try {
            $sql = $this->_db_table->select()
                    ->where("id IN (?)", $ids);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function obtenerBulto($id) {
        try {
            $sql = $this->_db_table->select()
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
}
