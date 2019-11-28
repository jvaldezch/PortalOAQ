<?php

class Trafico_Model_TraficoUsuClientesMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_TraficoUsuClientes();
    }

    public function obtenerClientes($idUsuario) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->distinct()
                    ->from(array("u" => "trafico_usuclientes"), array("*"))
                    ->joinLeft(array("c" => "trafico_clientes"), "u.idCliente = c.id", array("*"))
                    ->where("u.idUsuario = ?", $idUsuario)
                    ->where("c.activo = 1")
                    ->order("c.nombre ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $data[""] = "---";
                foreach ($stmt as $item) {
                    $data[$item["idCliente"]] = $item["nombre"];
                }
                return $data;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerAduanas($idCliente, $aduanasUsuario = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->distinct()
                    ->from(array("u" => "trafico_usuclientes"), array("idAduana"))
                    ->joinLeft(array("a" => "trafico_aduanas"), "u.idAduana = a.id", array("patente", "aduana", "nombre"))
                    ->where("u.idCliente = ?", $idCliente);
            if (isset($aduanasUsuario)) {
                if (is_array($aduanasUsuario)) {
                    foreach ($aduanasUsuario as $item) {
                        $sql->orWhere("a.patente IN (?)", $item["patente"])
                                ->where("a.aduana IN (?)", $item["aduana"]);
                    }
                }
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $data[""] = "---";
                foreach ($stmt as $item) {
                    $data[$item["idAduana"]] = $item["patente"] . "-" . $item["aduana"] . " " . $item["nombre"];
                }
                return $data;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function obtenerMisAduanas($idCliente, $idUsuario) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->distinct()
                    ->from(array("u" => "trafico_usuclientes"), array("idAduana"))
                    ->joinLeft(array("a" => "trafico_aduanas"), "u.idAduana = a.id", array("patente", "aduana", "nombre"))
                    ->where("u.idCliente = ?", $idCliente)
                    ->where("u.idUsuario = ?", $idUsuario);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $data[""] = "---";
                foreach ($stmt as $item) {
                    $data[$item["idAduana"]] = $item["patente"] . "-" . $item["aduana"] . " " . $item["nombre"];
                }
                return $data;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerClientesUsuario($idUsuario) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->distinct()
                    ->from(array("u" => "trafico_usuclientes"), array("*"))
                    ->joinLeft(array("c" => "trafico_clientes"), "u.idCliente = c.id", array("*"))
                    ->where("u.idUsuario = ?", $idUsuario)
                    ->where("c.activo = 1");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                foreach ($stmt as $item) {
                    $data[$item["idCliente"]] = $item["nombre"];
                }
                return $data;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerClientesAduanaUsuario($idUsuario) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("s" => "trafico_usuaduanas"), array("idUsuario", "idCliente", "idAduana"))
                    ->joinLeft(array("a" => "trafico_aduanas"), "a.id = s.idAduana", array("patente", "aduana", "nombre AS nombreAduana"))
                    ->joinLeft(array("j" => "trafico_clientes"), "j.id = s.idCliente", array("nombre"))
                    ->where("s.idUsuario = ?", $idUsuario)
                    ->group(array("a.patente", "a.aduana", "s.idCliente", "s.idAduana", "s.idUsuario", "j.nombre"))
                    ->order(array("a.patente ASC", "a.aduana ASC", "a.nombre ASC"));
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerClientesPorAduana($idUsuario, $patente, $aduana) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->distinct()
                    ->from(array("u" => "trafico_usuclientes"), array("*"))
                    ->joinLeft(array("c" => "trafico_clientes"), "u.idCliente = c.id", array("*"))
                    ->joinLeft(array("a" => "trafico_cliaduanas"), "a.idCliente = u.idCliente", array())
                    ->joinLeft(array("r" => "trafico_aduanas"), "a.idAduana = r.id", array())
                    ->where("u.idUsuario = ?", $idUsuario)
                    ->where("r.patente = ?", $patente)
                    ->where("r.aduana = ?", $aduana)
                    ->where("c.activo = 1");
            $stmt = $this->_db_table->fetchAll($sql, array());
            if ($stmt) {
                $arr = $stmt->toArray();
                if (!empty($arr)) {
                    foreach ($arr as $item) {
                        $data[$item["rfc"]] = $item["nombre"];
                    }
                    return $data;
                }
                return false;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificar($idUsuario, $idCliente, $idAduana) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idUsuario = ?", $idUsuario)
                    ->where("idCliente = ?", $idCliente)
                    ->where("idAduana = ?", $idAduana);
            $stmt = $this->_db_table->fetchRow($sql, array());
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
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function remover($idUsuario, $idCliente, $idAduana) {
        try {
            $where = array(
                "idUsuario = ?" => $idUsuario,
                "idCliente = ?" => $idCliente,
                "idAduana = ?" => $idAduana,
            );
            $stmt = $this->_db_table->delete($where);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
