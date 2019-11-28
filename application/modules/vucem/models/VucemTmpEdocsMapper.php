<?php

class Vucem_Model_VucemTmpEdocsMapper {

    protected $_db_table;

    function __construct() {
        $this->_db_table = new Vucem_Model_DbTable_VucemTmpEdocs();
    }

    public function verify($patente, $referencia, $nomArchivo) {
        try {
            $select = $this->_db_table->select()
                    ->where("patente = ?", $patente)
                    ->where("referencia = ?", $referencia)
                    ->where("nomArchivo = ?", $nomArchivo);
            $stmt = $this->_db_table->fetchRow($select);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $e->getMessage());
        }
    }

    public function existe($nomArchivo, $hash) {
        try {
            $select = $this->_db_table->select()
                    ->where("nomArchivo = ?", $nomArchivo)
                    ->where("hash = ?", $hash);
            $stmt = $this->_db_table->fetchRow($select);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $e->getMessage());
        }
    }

    public function agregar($type, $subType, $firmante, $patente, $aduana, $pedimento, $referencia, $filename, $size, $hash, $rfcConsulta, $username) {
        try {
            $arr = array(
                "firmante" => $firmante,
                "tipoArchivo" => $type,
                "subTipoArchivo" => $subType,
                "referencia" => $referencia,
                "patente" => $patente,
                "aduana" => $aduana,
                "pedimento" => $pedimento,
                "nomArchivo" => $filename,
                "size" => $size,
                "hash" => $hash,
                "rfcConsulta" => $rfcConsulta,
                "usuario" => $username,
                "creado" => date("Y-m-d H:i:s"),
            );
            if ($size > 3670016) {
                $arr["error"] = 1;
                $arr["mensajeError"] = "El tamaño del archivo es mayor a 3 MB.";
            }
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $e->getMessage());
        }
    }

    public function obtener($usuario = null) {
        try {
            $sql = $this->_db_table->select()
                    ->where("estatus IS NULL OR estatus = 4");
            if (isset($usuario)) {
                $sql->where("usuario = ?", $usuario);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $e->getMessage());
        }
    }

    public function obtenerArchivo($id, $usuario = null) {
        try {
            $sql = $this->_db_table->select()
                    ->where("id = ?", $id);
            if (!isset($usuario) && $usuario != null) {
                $sql->where("usuario = ?", $usuario);
            }
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $e->getMessage());
        }
    }

    public function obtenerNomArchivo($id) {
        try {
            $select = $this->_db_table->select()
                    ->from($this->_db_table, array("nomArchivo"))
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($select);
            if ($stmt) {
                return $stmt->toArray();
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $e->getMessage());
        }
    }

    public function marcarEnviado($id) {
        try {
            $updated = $this->_db_table->update(array("enviado" => 1), array("id = ?" => $id));
            if ($updated) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $e->getMessage());
        }
    }

    public function borrar($id) {
        try {
            $stmt = $this->_db_table->update(array("borrado" => 1), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $e->getMessage());
        }
    }

    public function eliminar($id) {
        try {
            $stmt = $this->_db_table->delete(array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $e->getMessage());
        }
    }

    public function actualizarSolicitud($id, $solicitud) {
        try {
            $stmt = $this->_db_table->update(array("solicitud" => $solicitud), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $e->getMessage());
        }
    }

    public function ultimaRespuesta($id, $ultimaRespuesta) {
        try {
            $stmt = $this->_db_table->update(array("ultimaRespuesta" => $ultimaRespuesta, "actualizado" => date("Y-m-d H:i:s")), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $e->getMessage());
        }
    }

    public function numOperacion($id, $numOperacion) {
        try {
            $stmt = $this->_db_table->update(array("numOperacion" => $numOperacion), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $e->getMessage());
        }
    }

    public function edocument($id, $edocument) {
        try {
            $stmt = $this->_db_table->update(array("edocument" => $edocument), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $e->getMessage());
        }
    }

    public function enviado($id) {
        try {
            $stmt = $this->_db_table->update(array("enviado" => date("Y-m-d H:i:s")), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $e->getMessage());
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
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $e->getMessage());
        }
    }

    public function error($id) {
        try {
            $stmt = $this->_db_table->update(array("error" => 1), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $e->getMessage());
        }
    }
    
    public function estatus($id, $estatus) {
        try {
            $stmt = $this->_db_table->update(array("estatus" => $estatus), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $e->getMessage());
        }
    }

    public function cambiarTipo($id, $type) {
        try {
            $stmt = $this->_db_table->update(array("tipoArchivo" => $type), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $e->getMessage());
        }
    }

}
