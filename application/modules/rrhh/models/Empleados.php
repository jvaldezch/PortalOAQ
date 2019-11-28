<?php

class Rrhh_Model_Empleados {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Rrhh_Model_DbTable_Empleados();
    }

    public function obtenerTodos($empresas, $idEmpresa = null, $estatus = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("e" => "empleados"), array("*"))
                    ->joinInner(array("p" => "empresas"), "p.id = e.idEmpresa", array("razonSocial"))
                    ->where("e.idEmpresa IN (?)", $empresas)
                    ->order("e.nombre ASC");
            if (isset($estatus)) {
                if ($estatus == 0) {
                    $sql->where("estatus = 1");
                }
                if ($estatus == 1) {
                    $sql->where("estatus = 0");
                }
            } else {
                $sql->where("estatus = 1");
            }
            if (isset($idEmpresa)) {
                $sql->where("e.idEmpresa = ?", $idEmpresa);
            }
            $stmt = $this->_db_table->fetchAll($sql, array());
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerPorUsuario($idUsuario) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("*"))
                    ->where("idUsuario = ?", $idUsuario);
            $stmt = $this->_db_table->fetchRow($sql, array());
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtener($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("*"))
                    ->where("id = ?", $id)
                    ->order("nombre ASC");
            $stmt = $this->_db_table->fetchRow($sql, array());
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizar($idEmpleado, $arr) {
        try {
            $stmt = $this->_db_table->update($arr, array("id = ?" => $idEmpleado));
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

}
