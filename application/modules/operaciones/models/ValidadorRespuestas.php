<?php

class Operaciones_Model_ValidadorRespuestas {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Operaciones_Model_DbTable_ValidadorRespuestas();
    }

    public function verificar($idArchivo, $archivo) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id", "enviado", "agotado", "pagado", "validado", "error"))
                    ->where("idArchivo = ? ", $idArchivo)
                    ->where("archivo = ? ", $archivo);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $e->getMessage());
        }
    }

    public function agregar($idArchivo, $archivo, $contenido, $usuario, $error = 0) {
        try {
            $arr = array(
                "idArchivo" => $idArchivo,
                "archivo" => $archivo,
                "contenido" => $contenido,
                "usuario" => $usuario,
                "error" => $error,
                "creado" => date("Y-m-d H:i:s"),
            );
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return array("id" => $stmt);
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $e->getMessage());
        }
    }

    public function actualizarContenido($idArchivo, $contenido) {
        try {
            $stmt = $this->_db_table->update(array("contenido" => $contenido), array("idArchivo = ?" => $idArchivo));
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $e->getMessage());
        }
    }

    public function tieneError($idArchivo) {
        try {
            $stmt = $this->_db_table->update(array("error" => 1), array("idArchivo = ?" => $idArchivo));
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $e->getMessage());
        }
    }

    public function obtener($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id", "archivo", "error", new Zend_Db_Expr("OCTET_LENGTH(contenido) as size")))
                    ->where("idArchivo = ?", $id);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $e->getMessage());
        }
    }

    public function obtenerContenido($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id", "archivo", "contenido"))
                    ->where("id = ? ", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $e->getMessage());
        }
    }

    public function patenteAduana($id) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("r" => "validador_respuestas"), array("archivo", "contenido"))
                    ->joinLeft(array("a" => "validador_archivos"), "r.idArchivo = a.id", array("patente", "aduana"))
                    ->where("r.id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $e->getMessage());
        }
    }

}
