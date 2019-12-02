<?php

class Trafico_Model_TraficoAduanasMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_TraficoAduanas();
    }

    /**
     * 
     * @param int $patente
     * @param int $aduana
     * @return boolean
     * @throws Exception
     */
    public function idAduana($patente, $aduana) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id"))
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->id;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $tipoAduana
     * @return boolean
     * @throws Exception
     */
    public function obtener($tipoAduana = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id", "patente", "aduana", "nombre", "corresponsal", "tipoAduana"))
                    ->where("activo = 1")
                    ->where("visible = 1")
                    ->order(array("patente", "aduana"));
            if (isset($tipoAduana)) {
                $sql->where("tipoAduana = ?", $tipoAduana)
                        ->where("patente <> 9999");
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }
    
    public function obtenerReporteo() {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id", "patente", "aduana", "nombre", "corresponsal", "tipoAduana"))
                    ->where("activo = 1")
                    ->where("visible = 1")
                    ->where("reportes = 1")
                    ->order(array("patente", "aduana"));
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function aduana($id) {
        try {
            $sql = $this->_db_table->select()
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function aduanas() {
        try {
            $sql = $this->_db_table->select()
                    ->where("visible = 1")
                    ->where("activo = 1")
                    ->where("aduana NOT IN (645, 646)")
                    ->order(array("patente", "aduana"));
            $stmt = $this->_db_table->fetchAll($sql);
            if (count($stmt)) {
                $arr = array("-" => "---");
                foreach ($stmt->toArray() as $item) {
                    $arr[$item["id"]] = $item["patente"] . "-" . $item["aduana"] . " " . $item["nombre"];
                }
                return $arr;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function aduanasDashboard() {
        try {
            $sql = $this->_db_table->select()
                    ->where("visible = 1")
                    ->where("activo = 1")
                    ->where("aduana NOT IN (645, 646)")
                    ->order(array("patente", "aduana"));
            $stmt = $this->_db_table->fetchAll($sql);
            if (count($stmt)) {
                foreach ($stmt->toArray() as $item) {
                    if ((int) $item["patente"] == 0) {
                        $arr[$item["id"]] = $item["nombre"];
                    } else {
                        $arr[$item["id"]] = $item["patente"] . "-" . $item["aduana"] . " " . $item["nombre"];                        
                    }
                }
                return $arr;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param array $ids
     * @return boolean
     * @throws Exception
     */
    public function obtenerTodas($ids = null) {
        try {
            $sql = $this->_db_table->select()
                    ->where("(activo = 1 AND visible = 1)")
                    ->order("patente");
            if (isset($ids) && is_array($ids)) {
                $sql->where("id IN (?)", $ids);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }
    
    public function obtenerPatentes() {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("patente"))
                    ->group("patente")
                    ->where("visible = 1")
                    ->where("activo = 1");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }
    
    public function obtenerAduanas($patente) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("aduana", "nombre"))
                    ->order("aduana ASC")
                    ->where("visible = 1")
                    ->where("activo = 1")
                    ->where("patente = ?", $patente);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }
    
    /**
     * 
     * @param array $ids
     * @return boolean
     * @throws Exception
     */
    public function obtenerActivas($ids = null, $tipoAduana = null) {
        try {
            $sql = $this->_db_table->select()
                    ->where("activo = 1")
                    ->where("visible = 1")
                    ->order(array("patente", "aduana"));
            if (isset($tipoAduana)) {
                $sql->where("tipoAduana = ?", $tipoAduana);
            }
            if (isset($ids) && is_array($ids)) {
                $sql->where("id IN (?)", $ids);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $patente
     * @param int $aduana
     * @return boolean
     * @throws Exception
     */
    public function infoAduana($patente, $aduana) {
        try {
            $sql = $this->_db_table->select()
                    ->from(array("a" => "trafico_aduanas"), array("*"))
                    ->where("a.patente = ?", $patente)
                    ->where("a.aduana = ?", $aduana);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function obtenerAduana($idAduana) {
        try {
            $sql = $this->_db_table->select()
                    ->from(array("a" => "trafico_aduanas"), array("*"))
                    ->where("a.id = ?", $idAduana);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function find(Trafico_Model_Table_Aduanas $tbl) {
        try {
            $stmt = $this->_db_table->fetchRow(
                    $this->_db_table->select()
                            ->where("patente = ?", $tbl->getPatente())
                            ->where("aduana = ?", $tbl->getAduana())
            );
            if (0 == count($stmt)) {
                return;
            }
            $tbl->setId($stmt->id);
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function save(Trafico_Model_Table_Aduanas $tbl) {
        try {
            $arr = array(
                "patente" => $tbl->getPatente(),
                "aduana" => $tbl->getAduana(),
                "nombre" => html_entity_decode($tbl->getNombre()),
                "tipoAduana" => $tbl->getTipoAduana(),
                "corresponsal" => $tbl->getCorresponsal(),
                "activo" => $tbl->getActivo(),
            );
            if (null === ($id = $tbl->getId())) {
                unset($arr["id"]);
                $id = $this->_db_table->insert($arr);
                $tbl->setId($id);
            } else {
                $this->_db_table->update($arr, array("id = ?" => $id));
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function tipoAduana($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("tipoAduana"))
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->tipoAduana;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

}
