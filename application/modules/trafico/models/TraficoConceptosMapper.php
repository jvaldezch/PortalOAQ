<?php

class Trafico_Model_TraficoConceptosMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_TraficoConceptos();
    }

    /**
     * 
     * @param int $idAduana
     * @return boolean
     * @throws Exception
     */
    public function obtener($idAduana) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idAduana = ?", $idAduana)
                    ->order("concepto ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                foreach ($stmt as $item) {
                    $data[$item["id"]] = trim($item["concepto"]);
                }
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function verificarConceptos($idAduana) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idAduana = ?", $idAduana);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * Obtiene todos los conceptos de una aduana, y regresa valor de aquello capturados.
     * 
     * @param int $idAduana
     * @param int $idSolicitud
     * @return boolean
     * @throws Exception
     */
    public function obtenerConValor($idAduana, $idSolicitud = null) {
        try {
            $tbl = new Trafico_Model_TraficoSolConceptoMapper();
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("c" => "trafico_conceptos"), array("*"))
                    ->where("c.idAduana = ?", $idAduana)
                    ->order(array("c.orden", "c.concepto"));
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $data = array();
                foreach ($stmt as $item) {
                    $data[] = array(
                        "idConcepto" => (int) $item["id"],
                        "concepto" => $item["concepto"],
                        "importe" => (isset($idSolicitud)) ? $tbl->obtenerImporte($idSolicitud, $item["id"]) : 0,
                    );
                }
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerConCuentas($idAduana) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("c" => "trafico_conceptos"), array("*"))
                    ->joinLeft(array("a" => "trafico_cuentas"), "a.cuenta = c.idCuenta", array("cuenta as idCuenta", "concepto as nomContable"))
                    ->joinLeft(array("t" => "trafico_tipoconcepto"), "t.id = a.idTipoConcepto", array("tipoConcepto"))
                    ->where("c.idAduana = ?", $idAduana)
                    ->order(array("c.orden", "c.concepto"));
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                foreach ($stmt as $item) {
                    $data[] = array(
                        "idConcepto" => (int) $item["id"],
                        "orden" => (int) $item["orden"],
                        "concepto" => $item["concepto"],
                        "idCuenta" => $item["idCuenta"],
                        "nomContable" => $item["nomContable"],
                        "tipoConcepto" => strtoupper($item["tipoConcepto"]),
                    );
                }
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerGenerales() {
        try {
            $sql = $this->_db_table->select()
                    ->where("idAduana = 2")
                    ->order("concepto ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                foreach ($stmt as $item) {
                    $data[$item["id"]] = $item["concepto"];
                }
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificar($idAduana, $idCuenta) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idAduana = ?", $idAduana)
                    ->where("idCuenta = ?", $idCuenta);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function nombreConcepto($idAduana, $id) {
        try {
            $sql = $this->_db_table->select(array("concepto"))
                    ->where("id = ?", $id)
                    ->where("idAduana = ?", $idAduana);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt["concepto"];
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

    public function remover($idAduana, $idConcepto) {
        try {
            $stmt = $this->_db_table->delete(array(
                "idAduana = ?" => $idAduana,
                "id = ?" => $idConcepto,
            ));
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarOrden($idConcepto, $orden) {
        try {
            $stmt = $this->_db_table->update(array("orden" => $orden), array("id = ?" => $idConcepto));
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
