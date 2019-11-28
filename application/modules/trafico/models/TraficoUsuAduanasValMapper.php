<?php

class Trafico_Model_TraficoUsuAduanasValMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_TraficoUsuAduanasVal();
    }

    public function obtenerPatentes($idUsuario) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->distinct()
                    ->from(array("a" => "trafico_usuaduanasval"), array(""))
                    ->joinLeft(array("p" => "trafico_aduanas"), "a.idAduana = p.id", array("patente"))
                    ->where("a.idUsuario = ?", $idUsuario);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $data[""] = "---";
                foreach ($stmt as $item) {
                    $data[$item["patente"]] = $item["patente"];
                }
                return $data;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerAduanasUsuario($idUsuario) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->distinct()
                    ->from(array("a" => "trafico_usuaduanasval"), array(""))
                    ->joinLeft(array("p" => "trafico_aduanas"), "a.idAduana = p.id", array("patente", "aduana"))
                    ->where("a.idUsuario = ?", $idUsuario);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerAduanasUsuarioDirectorio($idUsuario) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->distinct()
                    ->from(array("a" => "trafico_usuaduanasval"), array(""))
                    ->joinLeft(array("p" => "trafico_aduanas"), "a.idAduana = p.id", array("patente", "aduana"))
                    ->joinLeft(array("d" => "validador_directorio"), "d.patente = p.patente AND d.aduana = p.aduana", array("directorio"))
                    ->where("a.idUsuario = ?", $idUsuario);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerAduanas($patente, $idUsuario) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->distinct()
                    ->from(array("a" => "trafico_usuaduanasval"), array(""))
                    ->joinLeft(array("p" => "trafico_aduanas"), "a.idAduana = p.id", array("aduana"))
                    ->where("a.idUsuario = ?", $idUsuario)
                    ->where("p.patente = ?", $patente);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $data = array();
                foreach ($stmt as $item) {
                    $data[$item["aduana"]] = $item["aduana"];
                }
                return $data;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerOperaciones($patente, $aduana, $idUsuario) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->distinct()
                    ->from(array("a" => "trafico_usuaduanas"), array("tipo", "descripcion"))
                    ->joinLeft(array("p" => "trafico_aduanas"), "a.idAduana = p.id", array(""))
                    ->where("a.idUsuario = ?", $idUsuario)
                    ->where("p.patente = ?", $patente)
                    ->where("p.aduana = ?", $aduana);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $data = array();
                foreach ($stmt as $item) {
                    $data[$item["tipo"]] = $item["descripcion"];
                }
                return $data;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerMisOperaciones($idUsuario, $idAduana) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->distinct()
                    ->from(array("a" => "trafico_usuaduanas"), array("*"))
                    ->where("a.idAduana = ?", $idAduana)
                    ->where("a.idUsuario = ?", $idUsuario);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $data[""] = "---";
                foreach ($stmt as $item) {
                    $data[$item["tipo"]] = $item["descripcion"];
                }
                return $data;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificar($idUsuario, $idCliente, $idAduana, $tipo) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idUsuario = ?", $idUsuario)
                    ->where("idCliente = ?", $idCliente)
                    ->where("idAduana = ?", $idAduana)
                    ->where("tipo = ?", $tipo);
            $stmt = $this->_db_table->fetchRow($sql, array());
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
            $stmt = $this->_db_table->insert($data);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function removerValidador($idAduana, $idUsuario) {
        try {
            $where = array(
                "idAduana = ?" => $idAduana,
                "idUsuario = ?" => $idUsuario,
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
