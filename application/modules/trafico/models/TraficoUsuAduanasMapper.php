<?php

class Trafico_Model_TraficoUsuAduanasMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_TraficoUsuAduanas();
    }

    public function obtenerPatentes($idUsuario) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->distinct()
                    ->from(array("a" => "trafico_usuaduanas"), array(""))
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
                    ->from(array("a" => "trafico_usuaduanas"), array(""))
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
    
    /**
     * 
     * @param int $idUsuario
     * @return type
     * @throws Exception
     */
    public function aduanasDeUsuario($idUsuario = null, $tipoAduana = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->distinct()
                    ->from(array("a" => "trafico_usuaduanas"), array(""))
                    ->joinLeft(array("p" => "trafico_aduanas"), "a.idAduana = p.id", array("id", "patente", "aduana", "nombre"))
                    ->where("p.activo = 1 AND p.visible = 1")
                    ->order(array("p.patente", "p.aduana"));
            if(isset($tipoAduana)) {
                $sql->where("p.tipoAduana = ?", $tipoAduana);
            }
            if(isset($idUsuario)) {
                $sql->where("a.idUsuario = ?", $idUsuario);
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
    
    public function todasAduanas() {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->joinLeft(array("p" => "trafico_aduanas"), array("id"))
                    ->where("p.activo = 1");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $arr = array();
                foreach ($stmt->toArray() as $item) {
                    $arr[$item["id"]] = $item["id"];
                }
                return $arr;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function misAduanas($idUsuario = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->distinct()
                    ->from(array("a" => "trafico_usuaduanas"), array(""))
                    ->joinLeft(array("p" => "trafico_aduanas"), "a.idAduana = p.id", array("id", "patente", "aduana", "nombre"))
                    ->where("p.activo = 1");
            if(isset($idUsuario)) {
                $sql->where("a.idUsuario = ?", $idUsuario);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $arr = array();
                foreach ($stmt->toArray() as $item) {
                    $arr[$item["id"]] = $item["id"];
                }
                return $arr;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerAduanas($patente, $idUsuario) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->distinct()
                    ->from(array("a" => "trafico_usuaduanas"), array(""))
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
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function existe($idUsuario, $idCliente, $idAduana) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idUsuario = ?", $idUsuario)
                    ->where("idCliente = ?", $idCliente)
                    ->where("idAduana = ?", $idAduana);
            $stmt = $this->_db_table->fetchRow($sql, array());
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregar($data) {
        try {
            $stmt = $this->_db_table->insert($data);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function remover($idUsuario, $idCliente, $idAduana) {
        try {
            $where = array(
                "idUsuario = ?" => $idUsuario,
                "idCliente = ?" => $idCliente,
                "idAduana = ?" => $idAduana
            );
            $stmt = $this->_db_table->delete($where);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
