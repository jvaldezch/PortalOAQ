<?php

class Trafico_Model_ClientesMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_Clientes();
    }

    public function obtenerId($rfcCliente) {
        try {
            $sql = $this->_db_table->select()
                    ->from(array("c" => "trafico_clientes"), array("id"))
                    ->where("c.rfc = ?", $rfcCliente);
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

    /**
     * 
     * @param string $rfc
     * @return boolean
     * @throws Exception
     */
    public function buscarRfc($rfc) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("c" => "trafico_clientes"), array("*"))
                    ->joinLeft(array("f" => "trafico_clisello"), "f.idCliente = c.id", array("idSello"))
                    ->where("c.rfc = ?", $rfc);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function datosCliente($id) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("c" => "trafico_clientes"), array("*"))
                    ->joinLeft(array("f" => "trafico_clisello"), "f.idCliente = c.id", array("idSello"))
                    ->where("c.id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function datosClientes($rfcs) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("c" => "trafico_clientes"), array("*"))
                    ->joinLeft(array("f" => "trafico_clisello"), "f.idCliente = c.id", array("idSello"))
                    ->where("c.rfc IN (?)", $rfcs);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function datosClienteDomicilio($id) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("c" => "trafico_clientes"), array("*", "nombre AS razonSocial"))
                    ->joinLeft(array("f" => "trafico_clisello"), "f.idCliente = c.id", array("idSello"))
                    ->joinLeft(array("d" => "trafico_clidom"), "d.idCliente = c.id", array("*"))
                    ->where("c.id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtener($todos = true, $filter = null) {
        try {
            $vucem = new Zend_Db_Expr("(SELECT f.figura FROM vucem_firmante f WHERE f.rfc = c.rfc LIMIT 1) as vucem");
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("c" => "trafico_clientes"), array("id", "nombre", "rfc", "activo", $vucem))
                    ->joinLeft(array("l" => "checklist_clientes"), "l.idCliente = c.id", array("completo AS expedienteCompleto"))
                    ->joinLeft(array("t" => "tarifas"), "t.idCliente = c.id AND t.estatus = 2", array("estatus AS estatusTarifa"))
                    ->order("c.nombre ASC");
            if (isset($filter)) {
                if ((int) $filter == 1) {
                    $sql->where("activo = 0");
                } elseif ((int) $filter == 3) {
                    $sql->where("c.activo = 1");
                } elseif ((int) $filter == 4) {
                    $sql->where("t.estatus = 2");
                } elseif ((int) $filter == 5) {
                    $sql->where("l.completo = 1");
                } elseif ((int) $filter == 6) {
                    $sql->where("l.completo = 0");
                } elseif ((int) $filter != 0) {
                    
                }
            }
            if ($todos == true) {
                $sql->where("c.activo = 1");
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerPorEmpresa($idEmpresa) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idEmpresa = ?", $idEmpresa)
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
    
    public function obtenerClientes($rfcs = null, $idClientes = null) {
        try {
            $sql = $this->_db_table->select()
                    ->where("activo = 1")
                    ->order("nombre ASC");
            if (isset($rfcs) && !empty($rfcs)) {
                $sql->where("rfc IN (?)", $rfcs);
            }
            if (isset($idClientes) && !empty($idClientes)) {
                $sql->where("id IN (?)", $idClientes);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function obtenerTodos() {
        try {
            $sql = $this->_db_table->select()
                    ->where("activo = 1")
                    ->order("nombre ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $data[""] = "---";
                foreach ($stmt as $item) {
                    $data[$item["id"]] = $item["nombre"];
                }
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function buscar($rfc) {
        try {
            $sql = $this->_db_table->select()
                    ->where("rfc = ?", $rfc);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function busqueda($string) {
        try {
            $vucem = new Zend_Db_Expr("(SELECT f.figura FROM vucem_firmante f WHERE f.rfc = c.rfc LIMIT 1) as vucem");
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("c" => "trafico_clientes"), array("id", "nombre", "rfc", "activo", $vucem))
                    ->joinLeft(array("l" => "checklist_clientes"), "l.idCliente = c.id", array("completo AS expedienteCompleto"))
                    ->joinLeft(array("t" => "tarifas"), "t.idCliente = c.id AND t.estatus = 2", array("estatus AS estatusTarifa"))
                    ->where("c.rfc LIKE ?", "%" . $string . "%")
                    ->orWhere("c.nombre LIKE ?", "%" . $string . "%")
                    ->order("c.nombre ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function nuevoCliente($data) {
        try {
            $stmt = $this->_db_table->insert($data);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function pecaDefault($idCliente, $peca) {
        try {
            $data = array(
                "peca" => $peca,
            );
            $where = array(
                "id = ?" => $idCliente,
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

    public function esquemaDefault($idCliente, $esquema) {
        try {
            $data = array(
                "esquema" => $esquema,
            );
            $where = array(
                "id = ?" => $idCliente,
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

    public function tipoCliente($idCliente, $esquema) {
        try {
            $data = array(
                "tipoCliente" => $esquema,
            );
            $where = array(
                "id = ?" => $idCliente,
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

    public function actualizarNombre($idCliente, $nombre, $rfcSociedad) {
        try {
            $data = array(
                "nombre" => $nombre,
                "rfcSociedad" => $rfcSociedad,
            );
            $where = array(
                "id = ?" => $idCliente,
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

    public function cambiarEstatus($activo, $rfc) {
        try {
            $data = array(
                "activo" => (isset($activo) && $activo == 1) ? 1 : 0,
            );
            $where = array(
                "rfc = ?" => $rfc,
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

    /**
     * 
     * @param string $name
     * @return array|null
     * @throws Exception
     */
    public function buscarCliente($name) {
        try {
            $sql = $this->_db_table->select()
                    ->where("nombre LIKE ?", "%" . $name . "%")
                    ->order("nombre ASC")
                    ->limit(10);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $arr = array();
                foreach ($stmt->toArray() as $item) {
                    $arr[] = $item["nombre"];
                }
                return $arr;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function buscarPorNombre($nombre) {
        try {
            $sql = $this->_db_table->select()
                    ->where("nombre LIKE ?", "%" . $nombre . "%");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param string $name
     * @return array|null
     * @throws Exception
     */
    public function rfcDeCliente($name) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("rfc"))
                    ->where("nombre LIKE ?", "%" . $name . "%")
                    ->limit(1);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param string $rfc
     * @param string $nombre
     * @param string $sistema
     * @return type
     * @throws Exception
     */
    public function sistema($rfc, $nombre, $sistema) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("c" => "trafico_clientes"), array("id"))
                    ->joinLeft(array("s" => "trafico_cliente_dbs"), "s.idCliente = c.id", array("identificador"))
                    ->where("c.rfc = ?", $rfc)
                    ->where("c.nombre = ?", $nombre)
                    ->where("s.sistema = ?", $sistema);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $idCliente
     * @return type
     * @throws Exception
     */
    public function sica($idCliente) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("c" => "trafico_clientes"), array("id"))
                    ->joinLeft(array("s" => "trafico_cliente_dbs"), "s.idCliente = c.id", array("identificador"))
                    ->where("c.id = ?", $idCliente)
                    ->where("s.sistema = 'sica'");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->identificador;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $idCliente
     * @return type
     * @throws Exception
     */
    public function accesoPortal($idCliente) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("c" => "trafico_clientes"), array("id"))
                    ->joinLeft(array("s" => "trafico_cliente_dbs"), "s.idCliente = c.id", array(new Zend_Db_Expr("AES_DECRYPT(`password`,'oaqlkjkj3asdjaksdjqweuiuyyASDQWEksald') AS `password`")))
                    ->where("c.id = ?", $idCliente)
                    ->where("s.sistema = 'portal'");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->password;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $idCliente
     * @return type
     * @throws Exception
     */
    public function accesoDashboard($idCliente) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("c" => "trafico_clientes"), array("id"))
                    ->joinLeft(array("s" => "trafico_cliente_dbs"), "s.idCliente = c.id", array("identificador"))
                    ->where("c.id = ?", $idCliente)
                    ->where("s.sistema = 'dashboard'");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->identificador;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function obtenerRfcCliente($idCliente) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("rfc"))
                    ->where("id = ?", $idCliente);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->rfc;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
