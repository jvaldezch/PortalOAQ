<?php

class Trafico_Model_VucemMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_Vucem();
    }

    /**
     * 
     * @param array $data
     * @return boolean
     * @throws Exception
     */
    public function agregar($data) {
        try {
            $stmt = $this->_db_table->insert($data);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function actualizar($id, $arr) {
        try {
            $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    /**
     * 
     * @param int $idTrafico
     * @param int $idArchivo
     * @return boolean
     * @throws Exception
     */
    public function verificarEdocument($idTrafico, $idArchivo) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idTrafico = ?", $idTrafico)
                    ->where("idArchivo = ?", $idArchivo);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    /**
     * 
     * @param int $idTrafico
     * @param int $idFactura
     * @return boolean
     * @throws Exception
     */
    public function verificarFactura($idTrafico, $idFactura) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idTrafico = ?", $idTrafico)
                    ->where("idFactura = ?", $idFactura);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    /**
     * 
     * @param int $idTrafico
     * @return boolean
     * @throws Exception
     */
    public function obtener($idTrafico) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("v" => "trafico_vucem"), "*")
                    ->joinLeft(array("f" => "trafico_factdetalle"), "f.idFactura = v.idFactura", array("archivoCove AS archivoXml"))
                    ->where("v.idTrafico = ?", $idTrafico)
                    ->order("v.creado DESC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $mppr = new Trafico_Model_TraficoVucemLog();
                $arr = array();
                foreach ($stmt->toArray() as $item) {
                    $rows = $mppr->obtenerTodos($item['id']);
                    if (!empty($rows)) {
                        $item['log'] = $rows;
                    }
                    $arr[] = $item;
                }
                return $arr;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    /**
     * 
     * @param int $idTrafico
     * @return boolean
     * @throws Exception
     */
    public function obtenerConfig($idTrafico) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idTrafico = ?", $idTrafico)
                    ->order("creado DESC");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    /**
     * 
     * @param int $idFactura
     * @return boolean
     * @throws Exception
     */
    public function obtenerPorFactura($idFactura, $cove = null) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idFactura = ?", $idFactura);
            if (isset($cove)) {
                $sql->where("edocument = ?", $cove);
            }
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    /**
     * 
     * @param int $idVucem
     * @return boolean
     * @throws Exception
     */
    public function obtenerVucem($idVucem) {
        try {
            $sql = $this->_db_table->select()
                    ->where("id = ?", $idVucem);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function establecerSelloAgente($idTrafico, $idSello) {
        try {
            $stmt = $this->_db_table->update(array("idSelloAgente" => $idSello, "idSelloCliente" => null), array("idTrafico = ?" => $idTrafico));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function establecerSelloCliente($idTrafico, $idSello) {
        try {
            $stmt = $this->_db_table->update(array("idSelloCliente" => $idSello, "idSelloAgente" => null), array("idTrafico = ?" => $idTrafico));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function borrar($id) {
        try {
            $stmt = $this->_db_table->delete(array("id = ?" => $id));
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function borrarIdFactura($idFactura) {
        try {
            $stmt = $this->_db_table->delete(array("idFactura = ?" => $idFactura));
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

}
