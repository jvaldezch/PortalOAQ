<?php

class Principal_Model_UsuariosActividades {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Principal_Model_DbTable_UsuariosActividades();
    }

    public function obtenerActividades($fecha) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("a" => "usuarios_actividades"), array("*"))
                    ->joinLeft(array("u" => "usuarios"), "u.id = a.idUsuario", array("nombre AS nombreUsuario"))
                    ->joinLeft(array("t" => "empresa_depto_actividades"), "t.id = a.idActividad", array("descripcion AS descripcionActividad"))
                    ->joinLeft(array("c" => "trafico_clientes"), "c.id = a.idCliente", array("nombre AS nombreCliente"))
                    ->joinLeft(array("d" => "empresa_departamentos"), "d.id = a.idDepto", array("descripcion AS nombreDepartamento"))
                    ->where("a.fecha LIKE ?", $fecha . "%");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerTodasPorFecha($fecha) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("a" => "usuarios_actividades"), array("*"))
                    ->joinLeft(array("u" => "usuarios"), "u.id = a.idUsuario", array("nombre AS nombreUsuario"))
                    ->where("a.fecha LIKE ?", $fecha . "%")
                    ->order("nombre ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function obtenerPorFecha($idUsuario, $fecha) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idUsuario = ?", $idUsuario)
                    ->where("fecha LIKE ?", $fecha . "%");
            $stmt = $this->_db_table->fetchAll($sql);
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
