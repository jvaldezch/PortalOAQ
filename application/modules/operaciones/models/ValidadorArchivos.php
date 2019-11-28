<?php

class Operaciones_Model_ValidadorArchivos {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Operaciones_Model_DbTable_ValidadorArchivos();
    }

    public function verificar($patente, $aduana, $archivo) {
        try {
            $sql = $this->_db_table->select()
                    ->from("validador_archivos", array("id", "enviado", "agotado", "pagado", "validado", "error"))
                    ->where("patente = ? ", $patente)
                    ->where("aduana = ? ", $aduana)
                    ->where("archivo = ? ", $archivo)
                    ->where("YEAR(creado) = ?", date("Y"));
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function sinPedimento($patente, $aduana) {
        try {
            $sql = $this->_db_table->select()
                    ->from("validador_archivos", array("id"))
                    ->where("patente = ? ", $patente)
                    ->where("aduana = ? ", $aduana)
                    ->where("pedimento IS NULL")
                    ->where("archivo LIKE 'M%'")
                    ->limit(1000)
                    ->order("creado DESC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregar($patente, $aduana, $archivo, $contenido, $usuario) {
        try {
            $data = array(
                "patente" => $patente,
                "aduana" => $aduana,
                "archivo" => $archivo,
                "contenido" => $contenido,
                "usuario" => $usuario,
                "creado" => date("Y-m-d H:i:s"),
            );
            $stmt = $this->_db_table->insert($data);
            if ($stmt) {
                return array("id" => $stmt);
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtener($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("contenido", "patente", "aduana", "pedimento", "archivo", "contenido", "usuario", new Zend_Db_Expr("OCTET_LENGTH(contenido) as size")))
                    ->where("id = ? ", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerEstatus($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("*", new Zend_Db_Expr("OCTET_LENGTH(contenido) as size")))
                    ->where("id = ? ", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarMensaje($id, $mensaje) {
        try {
            $stmt = $this->_db_table->update(array("mensaje" => $mensaje), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerMensaje($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("mensaje"))
                    ->where("id = ? ", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function fueEnviado($id) {
        try {
            $stmt = $this->_db_table->update(array("enviado" => 1), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function fueValidado($id) {
        try {
            $stmt = $this->_db_table->update(array("validado" => 1), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function fuePagado($id) {
        try {
            $stmt = $this->_db_table->update(array("pagado" => 1), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function tieneError($id) {
        try {
            $stmt = $this->_db_table->update(array("error" => 1), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function seAgoto($id) {
        try {
            $stmt = $this->_db_table->update(array("agotado" => 1), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function enviado($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("enviado"))
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                $data = $stmt->toArray();
                return $data["enviado"];
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function validado($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("enviado"))
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                $data = $stmt->toArray();
                return $data["enviado"];
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function buscarPorNombre($archivo) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id"))
                    ->where("archivo LIKE ?", "%" . $archivo . "%");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                $data = $stmt->toArray();
                return $data["id"];
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarPedimento($id, $pedimento) {
        try {
            $stmt = $this->_db_table->update(array("pedimento" => $pedimento), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerPorPedimento($patente, $aduana, $pedimento) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id", "archivo"))
                    ->where("patente = ? ", $patente)
                    ->where("aduana = ? ", $aduana)
                    ->where("pedimento = ?", $pedimento);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerPorFecha($patente = null, $aduana = null, $fecha = null) {
        try {
            if (!isset($fecha)) {
                $fecha = date("Y-m-d");
            }
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id", "patente", "aduana", "pedimento", "usuario", "creado", "archivo"))
                    ->where("creado LIKE ?", $fecha . "%");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function buscarSimilar($nombre) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id", "patente", "aduana", "pedimento", "referencia", "archivo", "usuario", "enviado"))
                    ->where("archivo LIKE ?", "%" . $nombre . "%");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
