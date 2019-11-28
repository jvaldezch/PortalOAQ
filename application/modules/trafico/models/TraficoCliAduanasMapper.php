<?php

class Trafico_Model_TraficoCliAduanasMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_TraficoCliAduanas();
    }

    public function obtenerAduanas($idCliente, $patente = null, $aduana = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->distinct()
                    ->from(array("c" => "trafico_cliaduanas"), array("*"))
                    ->joinLeft(array("p" => "trafico_aduanas"), "c.idAduana = p.id", array("nombre", "id as aduana", "aduana as adu", "patente as pat"))
                    ->where("c.idCliente = ?", $idCliente)
                    ->where("p.activo = 1");
            if (isset($patente) && isset($aduana)) {
                if (!is_array($patente) && !is_array($aduana)) {
                    $sql->where("p.patente = ?", $patente)
                            ->where("p.aduana LIKE ?", substr($aduana, 0, 2) . "%");
                } else {
                    $sql->where("p.patente IN (?)", $patente)
                            ->where("p.aduana IN (?)", $aduana);
                }
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if (count($stmt) > 0) {
                $data[""] = "---";
                foreach ($stmt as $item) {
                    $data[$item["aduana"]] = array("nombre" => $item["nombre"], "aduana" => $item["adu"], "patente" => $item["pat"]);
                }
                return $data;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function clienteAduanas($idCliente) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->distinct()
                    ->from(array("c" => "trafico_cliaduanas"), array("*"))
                    ->joinLeft(array("p" => "trafico_aduanas"), "c.idAduana = p.id", array("nombre", "id as aduana", "aduana as adu", "patente as pat"))
                    ->where("c.idCliente = ?", $idCliente)
                    ->where("p.activo = 1")
                    ->order("patente ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if (count($stmt) > 0) {
                foreach ($stmt as $item) {
                    $data[$item["aduana"]] = array("id" => $item["id"],"nombre" => $item["nombre"], "aduana" => $item["adu"], "patente" => $item["pat"]);
                }
                return $data;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function clientesAduana($idAduana) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("c" => "trafico_cliaduanas"), array("*"))
                    ->joinLeft(array("p" => "trafico_clientes"), "c.idCliente = p.id", array("id AS idCliente", "nombre"))
                    ->where("c.idAduana = ?", $idAduana)
                    ->order("nombre ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function reporteOficinaClientes($idAduana) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("c" => "trafico_cliaduanas"), array())
                    ->joinLeft(array("p" => "trafico_clientes"), "c.idCliente = p.id", array("id AS idCliente", "nombre", "rfc"))
                    ->where("c.idAduana = ?", $idAduana)
                    ->order("nombre ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function clientesAduanas($idCliente) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->distinct()
                    ->from(array("c" => "trafico_cliaduanas"), array("*"))
                    ->joinLeft(array("a" => "trafico_aduanas"), "c.idAduana = a.id", array("patente", "aduana", "nombre"))
                    ->where("c.idCliente = ?", $idCliente);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $data[""] = "---";
                foreach ($stmt as $item) {
                    $data[$item["idAduana"]] = $item["patente"] . "-" . $item["aduana"] . " " . $item["nombre"];
                }
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificar($idAduana, $idCliente) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idAduana = ?", $idAduana)
                    ->where("idCliente = ?", $idCliente);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregar($data) {
        try {
            $added = $this->_db_table->insert($data);
            if ($added) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function remover($idAduana, $idCliente) {
        try {
            $stmt = $this->_db_table->delete(array(
                "idAduana = ?" => $idAduana,
                "idCliente = ?" => $idCliente,
            ));
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function clientesPorAduana($patente, $aduana) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("c" => "trafico_cliaduanas"), array("idCliente"))
                    ->joinLeft(array("a" => "trafico_aduanas"), "a.id = c.idAduana", array("patente", "aduana", "nombre"))
                    ->joinLeft(array("t" => "trafico_clientes"), "t.id = c.idCliente", array("nombre as razonSocial"))
                    ->where("a.patente IN (?)", $patente)
                    ->where("a.aduana IN (?)", $aduana)
                    ->order("t.nombre ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $data = array("" => "---");
                foreach ($stmt->toArray() as $item) {
                    $data[$item["idCliente"]] = $item["razonSocial"];
                }
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
