<?php

class Trafico_Model_TraficoSolConceptoMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_TraficoSolConcepto();
    }
    
    /**
     * 
     * @param int $idAduana
     * @param int $idSolicitud
     * @param int $idConcepto
     * @return boolean
     * @throws Exception
     */
    public function verificar($idAduana, $idSolicitud, $idConcepto) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idAduana = ?", $idAduana)
                    ->where("idSolicitud = ?", $idSolicitud)
                    ->where("idConcepto = ?", $idConcepto);
            $found = $this->_db_table->fetchrow($sql);
            if ($found) {
                return true;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    /**
     * 
     * @param int $idAduana
     * @param int $idSolicitud
     * @param int $idConcepto
     * @param string $concepto
     * @param float $importe
     * @return boolean
     * @throws Exception
     */
    public function agregar($idAduana, $idSolicitud, $idConcepto, $concepto, $importe) {
        try {
            $data = array(
                "idAduana" => $idAduana,
                "idSolicitud" => $idSolicitud,
                "idConcepto" => $idConcepto,
                "concepto" => $concepto,
                "importe" => $importe,
                "creado" => date("Y-m-d H:i:s")
            );
            $stmt = $this->_db_table->insert($data);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    /**
     * Obtener los conceptos de la solicitud.
     * 
     * @param int $idSolicitud
     * @return array|boolean
     * @throws Exception
     */
    public function obtenerTodos($idSolicitud) {
        try {
            $sql = $this->_db_table->select()
                    ->from(array("s" => "trafico_solconcepto"), array("*"))
                    ->where("s.idSolicitud = ?", $idSolicitud);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {                
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    /**
     * Obtener los conceptos de la solicitud.
     * 
     * @param int $idSolicitud
     * @return array|boolean
     * @throws Exception
     */
    public function obtener($idSolicitud) {
        try {
            $sql = $this->_db_table->select()
                    ->from(array("s" => "trafico_solconcepto"), array("concepto", "importe"))
                    ->where("s.idSolicitud = ?", $idSolicitud);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                foreach ($stmt->toArray() as $item) {
                    $data[trim($item["concepto"])] = $item["importe"];
                }
                return $data;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    /**
     * Obtener los conceptos de la solicitud.
     * 
     * @param int $idSolicitud
     * @return array|boolean
     * @throws Exception
     */
    public function obtenerImpresion($idSolicitud) {
        try {
            $sql = $this->_db_table->select()
                    ->from(array("s" => "trafico_solconcepto"), array("idConcepto", "importe"))
                    ->where("s.idSolicitud = ?", $idSolicitud)
                    ->where("s.idConcepto IS NOT NULL");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                foreach ($stmt->toArray() as $item) {
                    $data[$item["idConcepto"]] = $item["importe"];
                }
                return $data;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    /**
     * Obtener los conceptos de la solicitud.
     * 
     * @param int $idSolicitud
     * @return array|boolean
     * @throws Exception
     */
    public function obtenerAnticipo($idSolicitud) {
        try {
            $sql = $this->_db_table->select()
                    ->from(array("s" => "trafico_solconcepto"), array("importe"))
                    ->where("s.idSolicitud = ?", $idSolicitud)
                    ->where("s.concepto = 'ANTICIPO'");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->importe;
            }
            return 0;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    /**
     * Verifica si tiene conceptos
     * 
     * @param int $idSolicitud
     * @return array|boolean
     * @throws Exception
     */
    public function tieneConceptos($idSolicitud) {
        try {
            $sql = $this->_db_table->select()
                    ->from(array("s" => "trafico_solconcepto"), array("id"))
                    ->where("s.idSolicitud = ?", $idSolicitud);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    /**
     * 
     * @param int $idSolicitud
     * @return boolean
     * @throws Exception
     */
    public function borrarAnterior($idSolicitud) {
        try {
            $stmt = $this->_db_table->delete(array("idSolicitud = ?" => $idSolicitud));
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    /**
     * Obtiene el subtotal de la solicitud
     * 
     * @param int $idSolicitud
     * @return boolean
     * @throws Exception
     */
    public function subtotal($idSolicitud) {
        try {
            $sql = $this->_db_table->select()
                    ->from(array("s" => "trafico_solconcepto"), array("SUM(importe) as importe"))
                    ->where("s.idSolicitud = ?", $idSolicitud)
                    ->where("s.concepto <> 'ANTICIPO'");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                $data = $stmt->toArray();
                return $data["importe"];
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    /**
     * Obtiene el anticipo de la solicitud
     * 
     * @param int $idSolicitud
     * @return boolean
     * @throws Exception
     */
    public function anticipo($idSolicitud) {
        try {
            $sql = $this->_db_table->select()
                    ->from(array("s" => "trafico_solconcepto"), array("importe"))
                    ->where("s.idSolicitud = ?", $idSolicitud)
                    ->where("s.concepto = 'ANTICIPO'");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                $data = $stmt->toArray();
                return $data["importe"];
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    /**
     * Obtener el importe de una solicitud de un concepto dado
     * 
     * @param int $idSolicitud
     * @param int $idConcepto
     * @return boolean
     * @throws Exception
     */
    public function obtenerImporte($idSolicitud, $idConcepto) {
        try {
            $sql = $this->_db_table->select()
                    ->from(array("s" => "trafico_solconcepto"), array("importe"))
                    ->where("s.idSolicitud = ?", $idSolicitud)
                    ->where("s.idConcepto = ?", $idConcepto);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                $data = $stmt->toArray();
                return $data["importe"];
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    /**
     * Borra todos los conceptos de la solicitud
     * 
     * @param int $id
     * @return boolean
     * @throws Exception
     */
    public function delete($id) {
        try {
            $stmt = $this->_db_table->delete(array("idSolicitud = ?" => $id));
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

}
