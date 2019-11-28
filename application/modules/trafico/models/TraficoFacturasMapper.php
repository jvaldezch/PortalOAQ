<?php

class Trafico_Model_TraficoFacturasMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_TraficoFacturas();
    }

    public function verificar($idTrafico, $numFactura) {
        try {
            $sql = $this->_db_table->select();
            $sql->where("idTrafico = ?", $idTrafico)
                    ->where("numFactura = ?", $numFactura);
            $found = $this->_db_table->fetchRow($sql);
            if ($found) {
                return true;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificarFactura($idTrafico, $numFactura) {
        try {
            $sql = $this->_db_table->select();
            $sql->where("idTrafico = ?", $idTrafico)
                    ->where("numFactura = ?", $numFactura);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->id;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregarFacturaSimple($idTrafico, $numFactura, $idUsuario) {
        try {
            $stmt = $this->_db_table->insert(array(
                "idTrafico" => $idTrafico,
                "idUsuario" => $idUsuario,
                "numFactura" => $numFactura,
                "creado" => date("Y-m-d H:i:s")
            ));
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function agregar($idTrafico, $arr, $idUsuario) {
        try {
            $stmt = $this->_db_table->insert(array(
                "idTrafico" => $idTrafico,
                "idUsuario" => $idUsuario,
                "cvePro" => $arr["cvePro"],
                "identificador" => $arr["identificador"],
                "nombreProveedor" => $arr["nombreProveedor"],
                "incoterm" => $arr["incoterm"],
                "numFactura" => $arr["numFactura"],
                "fechaFactura" => isset($arr["fechaFactura"]) ? date("Y-m-d", strtotime($arr["fechaFactura"])) : null,
                "valorMonExt" => $arr["valorMonExt"],
                "valorDolares" => $arr["valorDolares"],
                "factorMonExt" => $arr["factorMonExt"],
                "cove" => isset($arr["cove"]) ? $arr["cove"] : null,
                "divisa" => $arr["divisa"],
                "paisFactura" => isset($arr["paisFactura"]) ? $arr["paisFactura"] : null,
                "creado" => date("Y-m-d H:i:s")
            ));
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function agregarFactura($idTrafico, $arr, $idUsuario) {
        try {
            $stmt = $this->_db_table->insert(array(
                "idTrafico" => $idTrafico,
                "idUsuario" => $idUsuario,
                "cvePro" => $arr["cvePro"],
                "identificador" => $arr["identificador"],
                "nombreProveedor" => $arr["nombreProveedor"],
                "incoterm" => $arr["incoterm"],
                "numFactura" => $arr["numFactura"],
                "fechaFactura" => isset($arr["fechaFactura"]) ? date("Y-m-d", strtotime($arr["fechaFactura"])) : null,
                "valorMonExt" => $arr["valorMonExt"],
                "valorDolares" => $arr["valorDolares"],
                "factorMonExt" => $arr["factorMonExt"],
                "cove" => $arr["cove"],
                "divisa" => $arr["divisa"],
                "paisFactura" => isset($arr["paisFactura"]) ? $arr["paisFactura"] : null,
                "sistema" => isset($arr["sistema"]) ? $arr["sistema"] : null,
                "creado" => date("Y-m-d H:i:s")
            ));
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
     * @param int $idTrafico
     * @param int $idUsuario
     * @return type
     * @throws Exception
     */
    public function obtenerFacturas($idTrafico, $idUsuario = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("f" => "trafico_facturas"), array("*"))
                    ->joinLeft(array("d" => "trafico_factdetalle"), "d.idFactura = f.id", array("idFactura"))
                    ->where("f.idTrafico = ?", $idTrafico)
                    ->where("f.estatus = 1")
                    ->order("f.numFactura ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function obtener($idFactura) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("f" => "trafico_facturas"), array("*"))
                    ->where("f.id = ?", $idFactura);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    /**
     * 
     * @param int $idTrafico
     * @param int $idUsuario
     * @return type
     * @throws Exception
     */
    public function obtenerDetalleFacturas($idTrafico) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("f" => "trafico_facturas"), array("cvePro"))
                    ->joinLeft(array("d" => "trafico_factdetalle"), "d.idFactura = f.id", array("*"))
                    ->where("f.idTrafico = ?", $idTrafico)
                    ->where("f.estatus = 1")
                    ->order("f.numFactura ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerFacturasWs($idTrafico) {
        try {
            $sql = $this->_db_table->select();
            $sql->where("idTrafico = ?", $idTrafico)
                    ->where("estatus = 1");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function delete($idFactura) {
        try {
            $stmt = $this->_db_table->delete(array("id = ?" => $idFactura));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function actualizar($idFactura, $arr) {
        try {
            $stmt = $this->_db_table->update($arr, array("id = ?" => $idFactura));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function informacionFactura($idFactura) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("f" => "trafico_facturas"), array("idTrafico", "numFactura", "fechaFactura", "divisa", "paisFactura", "sistema"))
                    ->joinLeft(array("t" => "traficos"), "f.idTrafico = t.id", array("patente", "aduana", "pedimento", "CONCAT(aduana, '-', patente, '-', pedimento) AS operacion", "referencia", "ie", "idCliente"))
                    ->joinLeft(array("c" => "trafico_clientes"), "c.id = t.idCliente", array("rfc AS rfcCliente", "nombre AS razonSocial"))
                    ->joinLeft(array("d" => "trafico_factdetalle"), "d.idFactura = f.id", array("idPro"))
                    ->where("f.id = ?", $idFactura);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getRow($idFactura) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("*"))
                    ->where("id = ?", $idFactura);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function detalleFactura($idFactura) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("f" => "trafico_facturas"), array("idTrafico", "id AS idFactura", "numFactura AS numeroFactura", "cove", "coveAdenda", "adenda", "numFactura"))
                    ->joinLeft(array("d" => "trafico_factdetalle"), "d.idFactura = f.id", array("id", "idPro", "fechaFactura", "incoterm", "observaciones", "subdivision", "ordenFactura", "valorFacturaUsd", "valorFacturaMonExt", "divisa", "paisFactura", "factorMonExt", "certificadoOrigen", "numExportador", "archivoCove"))
                    ->joinLeft(array("p" => "trafico_factpro"), "d.idPro = p.id", array("nombre AS nombreProveedor"))
                    ->where("f.id = ?", $idFactura);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function factura($idFactura) {
        try {
            $mapper = new Trafico_Model_FactProd();
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("f" => "trafico_facturas"), array("idTrafico", "numFactura"))
                    ->joinLeft(array("d" => "trafico_factdetalle"), "d.idFactura = f.id", array("*"))
                    ->where("f.id = ?", $idFactura);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                $arr = $stmt->toArray();
                $arr["productos"] = $mapper->obtener($idFactura);
                return $arr;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function insert($arr) {
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
