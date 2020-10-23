<?php

class Application_Model_UsuariosAduanasMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Application_Model_DbTable_UsuariosAduanas();
    }

    public function aduanasDeUsuario($idUsuario = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("a" => "usuarios_aduanas"), array("patente", "aduana"))
                    ->joinLeft(array("t" => "trafico_aduanas"), "t.patente = a.patente AND t.aduana = a.aduana", array("id as idAduana", "nombre"))
                    ->where("a.estatus = 1")
                    ->where("t.activo = 1");
            if (isset($idUsuario)) {
                $sql->where("a.idUsuario = ?", $idUsuario);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if (count($stmt)) {
                if (count($stmt) > 1) {
                    $data = array("-" => "---");
                    foreach ($stmt->toArray() as $item) {
                        $data[$item["idAduana"]] = $item["patente"] . "-" . $item["aduana"] . " " . $item["nombre"];
                    }
                } else {
                    $item = $stmt->toArray();
                    $data = array("-" => "---");
                    $data[$item[0]["idAduana"]] = $item[0]["patente"] . "-" . $item[0]["aduana"] . " " . $item[0]["nombre"];
                }
                return $data;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function traficoAduanasUsuario($id) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->distinct()
                    ->from(array("a" => "trafico_usuaduanas"), array("idAduana"))
                    ->where("a.idUsuario = ?", $id);
            $stmt = $this->_db_table->fetchAll($sql);
            if (0 !== count($stmt)) {
                $data = array();
                foreach ($stmt as $item) {
                    $data[] = $item["idAduana"];
                }
                return $data;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function misAduanas($id) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("a" => "usuarios_aduanas"), array("patente", "aduana"))
                    ->joinLeft(array("t" => "trafico_aduanas"), "t.patente = a.patente AND t.aduana = a.aduana", array("id AS idAduana"))
                    ->where("a.idUsuario = ?", $id)
                    ->where("a.estatus = 1");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function aduanasUsuario($id, $patente = null, $aduana = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("patente", "aduana"))
                    ->where("idUsuario = ?", $id)
                    ->where("estatus = 1");
            $stmt = $this->_db_table->fetchAll($sql);
            $rows = $stmt->toArray();
            if (!empty($rows)) {
                $data["patente"] = array();
                $data["aduana"] = array();
                foreach ($rows as $item) {
                    if (!in_array($item["patente"], $data["patente"])) {
                        array_push($data["patente"], $item["patente"]);
                    }
                    if (!in_array($item["aduana"], $data["aduana"])) {
                        array_push($data["aduana"], $item["aduana"]);
                    }
                }
                if (isset($patente) && $patente != "" || isset($aduana) && $aduana != "") {
                    if ($data["patente"][0] == "0" && isset($patente)) {
                        $data["patente"][0] = $patente;
                    } elseif ($data["patente"][0] != "0" && isset($patente)) {
                        $data["patente"][0] = $patente;
                    }
                    if ($data["aduana"][0] == "0" && isset($aduana)) {
                        $data["aduana"][0] = $aduana;
                    } elseif (isset($data["aduana"]) && $data["aduana"][0] != "0" && isset($aduana)) {
                        $data["aduana"][0] = $aduana;
                    }
                }
                return $data;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getCustoms($id, $patente) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("aduana"))
                    ->where("idUsuario = ?", $id)
                    ->where("patente = ?", $patente)
                    ->where("estatus = 1");
            $stmt = $this->_db_table->fetchAll($sql);
            $rows = $stmt->toArray();
            if (!empty($rows)) {
                $arr = array();
                foreach ($rows as $item) {
                    $arr[] = $item["aduana"];
                }
                return $arr;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function patentesUsuario($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("patente"))
                    ->where("idUsuario = ?", $id)
                    ->where("estatus = 1");
            $stmt = $this->_db_table->fetchAll($sql);
            $rows = $stmt->toArray();
            if (!empty($rows)) {
                foreach ($rows as $item) {
                    if ($item["patente"] != 0) {
                        $data[$item["patente"]] = $item["patente"];
                    }
                }
                if (isset($data)) {
                    return $data;
                } else {
                    return false;
                }
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function aduanasAsignadas($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id", "patente", "aduana"))
                    ->where("idUsuario = ?", $id)
                    ->where("estatus = 1");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificar($idUsuario, $patente, $aduana) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id", "patente", "aduana"))
                    ->where("idUsuario = ?", $idUsuario)
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("estatus = 1");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregar($idUsuario, $patente, $aduana) {
        try {
            $data = array(
                "idUsuario" => $idUsuario,
                "patente" => $patente,
                "aduana" => $aduana,
            );
            $added = $this->_db_table->insert($data);
            if ($added) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function borrarAduana($id) {
        try {
            $where = array(
                "id = ?" => $id,
            );
            if (($stmt = $this->_db_table->delete($where))) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function borrarTodas($idUsuario) {
        try {
            $stmt = $this->_db_table->delete(array("idUsuario = ?" => $idUsuario));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
