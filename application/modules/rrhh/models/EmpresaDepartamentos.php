<?php

class Rrhh_Model_EmpresaDepartamentos {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Rrhh_Model_DbTable_EmpresaDepartamentos();
    }

    public function obtener($idEmpresa) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("*"))
                    ->where("idEmpresa = ?", $idEmpresa)
                    ->order("descripcion ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function update($id, $arr) {
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

    public function verificar($idEmpresa, $nombreDepto) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("*"))
                    ->where("idEmpresa = ?", $idEmpresa)
                    ->where("descripcion LIKE ?", "%" . $nombreDepto . "%");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
