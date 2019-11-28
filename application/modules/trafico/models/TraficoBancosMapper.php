<?php

class Trafico_Model_TraficoBancosMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_TraficoBancos();
    }

    public function obtener($idAduana) {
        try {
            $sql = $this->_db_table->select(array("*"))
                    ->where("idAduana = ?", $idAduana);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerBanco($id) {
        try {
            $sql = $this->_db_table->select(array("*"))
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function obtenerBancoDefault($idAduana) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idAduana = ?", $idAduana)
                    ->where("`default` = 1");            
            $stmt = $this->_db_table->fetchRow($sql);            
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerTodos($idAduana) {
        try {
            $sql = $this->_db_table->select(array("*"))
                    ->where("idAduana = ?", $idAduana)
                    ->where("activo = 1");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificar($idAduana, $nombre, $cuenta) {
        try {
            $s = $this->_db_table->select()
                    ->where("idAduana = ?", $idAduana)
                    ->where("nombre = ?", $nombre)
                    ->where("cuenta = ?", $cuenta);
            $stmt = $this->_db_table->fetchRow($s);
            if ($stmt) {
                return true;
            }
            return false;
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
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function actualizar($id, $data) {
        try {
            $stmt = $this->_db_table->update($data, array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function removerDefault($idAduana) {
        try {
            $data = array(
                "default" => 0
            );
            $where = array(
                "idAduana = ?" => $idAduana
            );
            $stmt = $this->_db_table->update($data, $where);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function establecerDefault($id, $idAduana) {
        try {
            $data = array(
                "default" => 1
            );
            $where = array(
                "id = ?" => $id,
                "idAduana = ?" => $idAduana
            );
            $stmt = $this->_db_table->update($data, $where);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
