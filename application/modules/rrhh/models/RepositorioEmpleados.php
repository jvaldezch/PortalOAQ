<?php

class Rrhh_Model_RepositorioEmpleados {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Rrhh_Model_DbTable_RepositorioEmpleados();
    }

    public function archivosEmpleado($idEmpleado) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("a" => "repositorio_empleados"), array("*"))
                    ->joinLeft(array("d" => "documentos_empleados"), "d.id = a.tipoArchivo", array("nombre as descripcionArchivo"))
                    ->where("a.idEmpleado = ?", $idEmpleado)
                    ->order("a.nombreArchivo ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function buscar($id) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("a" => "repositorio_empleados"), array("*"))
                    ->where("a.id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function tipo($id) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("a" => "repositorio_empleados"), array("tipoArchivo"))
                    ->where("a.id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->tipoArchivo;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function buscarArchivo($idEmpleado, $nombreArchivo) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("a" => "repositorio_empleados"), array("id"))
                    ->where("a.idEmpleado = ?", $idEmpleado)
                    ->where("a.nombreArchivo = ?", $nombreArchivo);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function nuevoArchivo($idEmpleado, $tipoArchivo, $nombreArchivo, $ubicacion, $observaciones, $usuario) {
        try {
            $data = array(
                "idEmpleado" => $idEmpleado,
                "tipoArchivo" => $tipoArchivo,
                "hash" => sha1_file($ubicacion . DIRECTORY_SEPARATOR . $nombreArchivo),
                "nombreArchivo" => $nombreArchivo,
                "ubicacion" => $ubicacion,
                "observaciones" => $observaciones,
                "creado" => date("Y-m-d H:i:s"),
                "usuario" => $usuario,
            );
            $added = $this->_db_table->insert($data);
            if ($added) {
                return true;
            }
            return null;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function tipoArchivo($id) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("a" => "repositorio_empleados"), array("id"))
                    ->joinLeft(array("d" => "documentos_empleados"), "d.id = a.tipoArchivo", array("nombre"))
                    ->where("a.id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->nombre;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function idTipoArchivo($id) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("a" => "repositorio_empleados"), array("tipoArchivo"))
                    ->where("a.id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->tipoArchivo;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function fechaVencimiento($id) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("a" => "repositorio_empleados"), array("fechaVencimiento"))
                    ->where("a.id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->fechaVencimiento;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function actualizarTipoArchivo($id, $tipoArchivo) {
        try {
            $arr = array(
                "tipoArchivo" => $tipoArchivo
            );
            $added = $this->_db_table->update($arr, array("id = ?" => $id));
            if ($added) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function borrarArchivo($id) {
        try {
            $stmt = $this->_db_table->delete(array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
