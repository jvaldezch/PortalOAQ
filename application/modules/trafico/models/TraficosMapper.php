<?php

class Trafico_Model_TraficosMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_Traficos();
    }

    /**
     * 
     * @param type $patente
     * @param type $aduana
     * @param type $pedimento
     * @return boolean
     * @throws Exception
     */
    public function verificar($patente, $aduana, $pedimento) {
        try {
            $sql = $this->_db_table->select()
                    ->from("traficos", array("id"))
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("pedimento = ?", $pedimento)
                    ->where("estatus NOT IN (4)");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt["id"];
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param type $idTrafico
     * @return boolean
     * @throws Exception
     */
    public function borrar($idTrafico) {
        try {
            $stmt = $this->_db_table->update(array("estatus" => 4), array("id = ?" => $idTrafico));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param type $patente
     * @param type $aduana
     * @param type $referencia
     * @return type
     * @throws Exception
     */
    public function buscar($patente, $aduana, $referencia) {
        try {
            $sql = $this->_db_table->select()
                    ->from("traficos", array("pedimento", "rfcCliente"))
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("referencia = ?", $referencia);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function search($patente, $aduana, $referencia) {
        try {
            $sql = $this->_db_table->select()
                    ->from("traficos", array("*"))
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("referencia = ?", $referencia);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function busquedaReferencia($idAduana, $referencia) {
        try {
            $sql = $this->_db_table->select()
                    ->from("traficos", array("id", "idAduana", "idCliente", "patente", "aduana", "pedimento", "estatus", "ie"))
                    ->where("idAduana = ?", $idAduana)
                    ->where("referencia = ?", $referencia);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function busquedaPedimento($idAduana, $pedimento, $referencia) {
        try {
            $sql = $this->_db_table->select()
                    ->from("traficos", array("id", "idAduana", "patente", "aduana", "estatus", "idCliente"))
                    ->where("idAduana = ?", $idAduana)
                    ->where("pedimento = ?", $pedimento)
                    ->where("referencia = ?", $referencia);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function busquedaCliente($idAduana, $idCliente, $pedimento, $referencia) {
        try {
            $sql = $this->_db_table->select()
                    ->from("traficos", array("id", "idAduana", "patente", "aduana", "estatus", "idCliente"))
                    ->where("idAduana = ?", $idAduana)
                    ->where("idCliente = ?", $idCliente)
                    ->where("pedimento = ?", $pedimento)
                    ->where("referencia = ?", $referencia);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function busqueda($idAduana, $idCliente, $tipoOperacion, $pedimento, $referencia) {
        try {
            $sql = $this->_db_table->select()
                    ->from("traficos", array("id", "idAduana", "patente", "aduana", "estatus", "idCliente"))
                    ->where("idAduana = ?", $idAduana)
                    ->where("idCliente = ?", $idCliente)
                    ->where("ie = ?", $tipoOperacion)
                    ->where("pedimento = ?", $pedimento)
                    ->where("referencia = ?", $referencia);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $patente
     * @param int $aduana
     * @param int $pedimento
     * @param string $referencia
     * @param string $tipoOperacion
     * @param string $cvePedimento
     * @return type
     * @throws Exception
     */
    public function buscarTrafico($patente, $aduana, $pedimento, $referencia, $tipoOperacion = null, $cvePedimento = null, $rfcCliente = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from("traficos", array("id", "estatus", "idCliente"))
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("pedimento = ?", $pedimento)
                    ->where("referencia = ?", $referencia)
                    ->where("estatus <> 4");
            if (isset($rfcCliente)) {
                $sql->where("rfcCliente = ?", $rfcCliente);
            }
            if (isset($tipoOperacion)) {
                $sql->where("ie = ?", $tipoOperacion);
            }
            if (isset($tipoOperacion)) {
                $sql->where("ie = ?", $tipoOperacion);
            }
            if (isset($cvePedimento)) {
                $sql->where("cvePedimento = ?", $cvePedimento);
            }
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $patente
     * @param int $aduana
     * @param string $referencia
     * @return type
     * @throws Exception
     */
    public function buscarReferencia($patente, $aduana, $referencia) {
        try {
            $sql = $this->_db_table->select()
                    ->from("traficos", array("id"))
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("referencia = ?", $referencia);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->id;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param type $idCliente
     * @param type $idAduana
     * @param type $patente
     * @param type $aduana
     * @param type $pedimento
     * @param type $referencia
     * @param type $ie
     * @param type $rfc
     * @param type $tipoCambio
     * @param type $idUsuario
     * @return boolean
     * @throws Exception
     */
    public function agregarNuevo($idCliente, $idAduana, $patente, $aduana, $pedimento, $referencia, $ie, $rfc, $tipoCambio, $idUsuario) {
        try {
            $data = array(
                "idCliente" => $idCliente,
                "idAduana" => $idAduana,
                "patente" => $patente,
                "aduana" => $aduana,
                "pedimento" => $pedimento,
                "referencia" => $referencia,
                "tipoCambio" => $tipoCambio,
                "ie" => $ie,
                "rfcCliente" => $rfc,
                "idUsuario" => $idUsuario,
                "estatus" => 1,
                "creado" => date("Y-m-d H:i:s")
            );
            $stmt = $this->_db_table->insert($data);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    public function agregar($arr) {
        try {
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return $stmt;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function nuevoTrafico($arr) {
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

    public function reciclarTrafico($idTrafico, $arr) {
        try {
            $stmt = $this->_db_table->update($arr, array("id = ?", $idTrafico));
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param type $idAduana
     * @param type $patente
     * @param type $aduana
     * @param type $pedimento
     * @param type $referencia
     * @param type $ie
     * @param type $consolidado
     * @param type $rectificacion
     * @param type $cvePed
     * @param type $regimen
     * @param type $idUsuario
     * @return boolean
     * @throws Exception
     */
    public function nuevaSolicitud($idAduana, $patente, $aduana, $pedimento, $referencia, $ie, $consolidado, $rectificacion, $cvePed, $regimen, $idUsuario) {
        try {
            $data = array(
                "idAduana" => $idAduana,
                "patente" => $patente,
                "aduana" => $aduana,
                "pedimento" => $pedimento,
                "referencia" => $referencia,
                "consolidado" => $consolidado,
                "rectificacion" => $rectificacion,
                "cvePedimento" => $cvePed,
                "regimen" => $regimen,
                "ie" => $ie,
                "idUsuario" => $idUsuario,
                "estatus" => 0,
                "creado" => date("Y-m-d H:i:s")
            );
            $inserted = $this->_db_table->insert($data);
            if ($inserted) {
                return $inserted;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param type $id
     * @param type $cvePedimento
     * @param type $regimen
     * @param type $consolidado
     * @param type $rectificacion
     * @return boolean
     * @throws Exception
     */
    public function actualizarBasePedimento($id, $cvePedimento, $regimen, $consolidado, $rectificacion) {
        try {
            $data = array(
                "cvePedimento" => $cvePedimento,
                "consolidado" => $consolidado,
                "rectificacion" => $rectificacion,
                "regimen" => $regimen,
                "actualizado" => date("Y-m-d H:i:s"),
            );
            $where = array(
                "id = ?" => $id,
            );
            $stmt = $this->_db_table->update($data, $where);
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
     * @param int $id
     * @param int $estatus
     * @return boolean
     * @throws Exception
     */
    public function actualizarEstatus($id, $estatus) {
        try {
            $stmt = $this->_db_table->update(array("estatus" => $estatus), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $id
     * @param string $fecha
     * @return boolean
     * @throws Exception
     */
    public function actualizarFechaPago($id, $fecha) {
        try {
            $stmt = $this->_db_table->update(array("actualizado" => date("Y-m-d H:i:s"), "fechaPago" => date("Y-m-d H:i:s", strtotime($fecha))), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $id
     * @param int $tipoFecha
     * @param string $fecha
     * @return boolean
     * @throws Exception
     */
    public function actualizarTipoFecha($id, $tipoFecha, $fecha) {
        try {
            $stmt = $this->_db_table->update(array("actualizado" => date("Y-m-d H:i:s"), $tipoFecha => date("Y-m-d H:i:s", strtotime($fecha))), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $id
     * @param string $guia
     * @return boolean
     * @throws Exception
     */
    public function actualizarGuia($id, $guia) {
        try {
            $stmt = $this->_db_table->update(array("blGuia" => $guia), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $id
     * @param string $fecha
     * @return boolean
     * @throws Exception
     */
    public function actualizarFechaFacturacion($id, $fecha) {
        try {
            $stmt = $this->_db_table->update(array("actualizado" => date("Y-m-d H:i:s"), "fechaFacturacion" => date("Y-m-d H:i:s", strtotime($fecha))), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $id
     * @param int $semaforo
     * @param string $observacion
     * @return boolean
     * @throws Exception
     */
    public function actualizarSemaforo($id, $semaforo, $observacion = null) {
        try {
            $stmt = $this->_db_table->update(
                    array("semaforo" => $semaforo, "observacionSemaforo" => $observacion), array("id = ?" => $id)
            );
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $id
     * @param string $fecha
     * @return boolean
     * @throws Exception
     */
    public function actualizarFechaLiberacion($id, $fecha) {
        try {
            $stmt = $this->_db_table->update(array("actualizado" => date("Y-m-d H:i:s"), "fechaLiberacion" => date("Y-m-d H:i:s", strtotime($fecha))), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $id
     * @param array $arr
     * @return boolean
     * @throws Exception
     * 
     */
    public function actualizarTrafico($id, $arr) {
        try {
            $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $id
     * @param int $idCliente
     * @return boolean
     * @throws Exception
     * 
     */
    public function actualizarClienteTrafico($id, $idCliente, $rfcCliente) {
        try {
            $stmt = $this->_db_table->update(array("idCliente" => $idCliente, 'rfcCliente' => $rfcCliente), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $id
     * @param array $arr
     * @return boolean
     * @throws Exception
     * 
     */
    public function actualizarDatosTrafico($id, $arr) {
        try {
            $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $id
     * @return boolean
     * @throws Exception
     */
    public function pagado($id) {
        try {
            $where = array(
                "id = ?" => $id,
            );
            $stmt = $this->_db_table->update(array("pagado" => 1), $where);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * Obtiene datos completos del trafico
     * 
     * @param int $id
     * @return array|boolean
     * @throws Zend_Application_Exception
     */
    public function obtenerPorId($id) {
        try {
            $trans = new Trafico_Model_Trans();
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("t" => "traficos"), array("*"))
                    ->joinLeft(array("n" => "trafico_clientes"), "t.idCliente = n.id", array("nombre AS nombreCliente", "rfcSociedad", "rfc", "peca", "peca_num", "inmex", "inmex_num"))
                    ->joinLeft(array("u" => "usuarios"), "t.idUsuario = u.id", array("nombre"))
                    ->joinLeft(array("a" => "trafico_aduanas"), "a.id = t.idAduana", array("tipoAduana", "nombre AS nombreAduana"))
                    ->where("t.id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                $arr = $stmt->toArray();
                $arr["transporte"] = $trans->obtener($id);
                return $arr;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerRegistroCompleto($id) {
        try {
            $sql = $this->_db_table->select()
                ->from($this->_db_table, array("*"))
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

    /**
     * Esta funcion regresa los datos principales del trafico.
     * 
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function encabezado($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("*"))
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param string $search
     * @param array $aduanas
     * @param int $idUsuario
     * @param string $pagadas
     * @param string $liberadas
     * @param array $rfcs
     * @return boolean
     * @throws Exception
     */
    public function obtenerTraficos($search = null, $aduanas = null, $idUsuario = null, $pagadas = null, $liberadas = null, $rfcs = null, $impos = false, $expos = false, $fechaIni = null, $fechaFin = null, $cvePedimento = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("t" => "traficos"), array("*"))
                    ->joinLeft(array("u" => "usuarios"), "t.idUsuario = u.id", array("nombre"))
                    ->joinLeft(array("c" => "trafico_clientes"), "c.id = idCliente", array("nombre AS nombreCliente"))
                    ->where("t.estatus NOT IN (4)")
                    ->order(array("t.fechaEta DESC"));
            if (isset($search) && $search != "") {
                $sql->where("(t.pedimento LIKE '%{$search}%' OR referencia LIKE '%{$search}%' OR c.nombre LIKE '%{$search}%')");
            }
            if (isset($cvePedimento)) {
                $sql->where("t.cvePedimento = ?", $cvePedimento);
            }
            if (!isset($search)) {
                if ($pagadas == true) {
                    $sql->where("t.pagado IS NOT NULL");
                }
                if ($liberadas == true) {
                    $sql->where("t.estatus = 3");
                } else {
                    $sql->where("t.estatus <> 3");
                }
                if (isset($aduanas) && !empty($aduanas)) {
                    $sql->where("t.idAduana IN (?)", $aduanas);
                }
                if (isset($idUsuario) && !empty($idUsuario)) {
                    $sql->where("t.idUsuario = {$idUsuario} OR idUsuarioModif = {$idUsuario}");
                }
                if (isset($rfcs) && is_array($rfcs) && !empty($rfcs)) {
                    $sql->where("rfcCliente IN (?)", $rfcs);
                }
                if (!($impos === true && $expos === true)) {
                    if (isset($impos) && $impos === true) {
                        $sql->where("ie = 'TOCE.IMP'");
                    }
                    if (isset($expos) && $expos === true) {
                        $sql->where("ie = 'TOCE.EXP'");
                    }
                }
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

    public function traficosClientes($idCliente, $rfcCliente, $fecha) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("t" => "traficos"), array("*"))
                    ->where("t.idCliente = ?", $idCliente)
                    ->where("t.rfcCliente = ?", $rfcCliente)
                    ->where("t.estatus NOT IN (4) AND t.estatus = 3")
                    ->where("t.fechaLiberacion >= ?", date("Y-m-d", strtotime($fecha)))
                    ->where("t.fechaLiberacion < ?", date("Y-m-d", strtotime($fecha . ' +1 day')));
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function traficosClientesFtp($idCliente, $rfcCliente, $fecha) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("t" => "traficos"), array("*"))
                    ->where("t.idCliente = ?", $idCliente)
                    ->where("t.rfcCliente = ?", $rfcCliente)
                    ->where("t.estatus NOT IN (4) AND t.estatus = 3")
                    ->where("t.fechaLiberacion >= ?", date("Y-m-d", strtotime($fecha)))
                    ->where("t.fechaLiberacion < ?", date("Y-m-d", strtotime($fecha . ' +1 day')));
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerTraficosClientes($search = null, $pagadas = null, $liberadas = null, $impos = false, $expos = false, $fechaIni = null, $fechaFin = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("t" => "traficos"), array("*"))
                    ->joinLeft(array("u" => "usuarios"), "t.idUsuario = u.id", array("nombre"))
                    ->where("t.estatus NOT IN (4)");
            if (isset($search) && $search != "") {
                $sql->where("(t.pedimento LIKE '%{$search}%' OR referencia LIKE '%{$search}%' OR c.nombre LIKE '%{$search}%')");
            }
            if (!isset($search)) {
                if (isset($pagadas) && $pagadas == "true") {
                    $sql->where("t.pagado IS NOT NULL");
                }
                if (isset($liberadas) && $liberadas == "true") {
                    $sql->where("t.estatus = 3");
                } else {
                    $sql->where("t.estatus <> 3");
                }
                if (!($impos === true && $expos === true)) {
                    if (isset($impos) && $impos === true) {
                        $sql->where("ie = 'TOCE.IMP'");
                    }
                    if (isset($expos) && $expos === true) {
                        $sql->where("ie = 'TOCE.EXP'");
                    }
                }
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

    public function traficosPagados($patente = 3589, $limit = 10, $fechaPago = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("t" => "traficos"), array("id", "patente", "aduana", "pedimento", "referencia"))
                    ->joinLeft(array("b" => "trafico_fechas"), "b.idTrafico = t.id AND b.tipo = 2", array("DATE_FORMAT(fecha,'%Y-%m-%d') as fechaPago"))
                    ->where("t.patente = ?", $patente)
                    ->where("t.estatus IN (2, 3)")
                    ->limit($limit);
            if (isset($fechaPago) && $fechaPago != "") {
                $sql->where("b.fecha LIKE ?", $fechaPago . "%");
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

    /**
     * 
     * @param type $patente
     * @param type $aduana
     * @param type $pedimento
     * @return boolean
     * @throws Exception
     */
    public function detalleTrafico($patente, $aduana, $pedimento) {
        try {
            $fechas = new Trafico_Model_TraficoFechasMapper();
            $guias = new Trafico_Model_TraficoGuiasMapper();
            $otros = new Trafico_Model_TraficoOtrosMapper();
            $facturas = new Trafico_Model_TraficoFacturasMapper();
            $sql = $this->_db_table->select()
                    ->from(array("t" => "traficos"), array("id", "estatus"))
                    ->where("t.patente = ?", $patente)
                    ->where("t.aduana = ?", $aduana)
                    ->where("t.pedimento = ?", $pedimento);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                $info = $stmt->toArray();
                $data = array();
                $data["estatus"] = $info["estatus"];
                $data["fechas"] = $fechas->obtenerFechas($info["id"]);
                $data["guias"] = $guias->obtenerGuias($info["id"]);
                $data["otros"] = $otros->obtenerWs($info["id"]);
                $data["facturas"] = $facturas->obtenerFacturasWs($info["id"]);
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param type $patente
     * @param type $aduana
     * @return boolean
     * @throws Exception
     */
    public function porDespachar($patente, $aduana) {
        try {
            $sql = $this->_db_table->select()
                    ->from(array("t" => "traficos"), array("*"))
                    ->where("t.patente = ?", $patente)
                    ->where("t.aduana = ?", $aduana)
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

    /**
     * 
     * @param type $idComentario
     * @return boolean
     * @throws Exception
     */
    public function infoTraficoComentario($idComentario) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("c" => "trafico_comentarios"), array())
                    ->joinLeft(array("t" => "traficos"), "t.id = c.idTrafico", array("patente", "aduana", "pedimento"))
                    ->where("c.id = ?", $idComentario);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param type $rfcCliente
     * @param type $fechaIni
     * @param type $fechaFin
     * @return boolean
     * @throws Exception
     */
    public function consultaPorRfc($rfcCliente, $fechaIni, $fechaFin) {
        try {
            $sql = $this->_db_table->select()
                    ->where("rfcCliente = ?", $rfcCliente)
                    ->where("creado >= ?", date("Y-m-d H:i:s", strtotime($fechaIni)))
                    ->where("creado <= ?", date("Y-m-d H:i:s", strtotime($fechaFin)));
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param Trafico_Model_Table_Traficos $tbl
     * @return type
     * @throws Exception
     */
    public function find(Trafico_Model_Table_Traficos $tbl) {
        try {
            $stmt = $this->_db_table->fetchRow(
                    $this->_db_table->select()
                            ->where("id = ?", $tbl->getId())
            );
            if (0 == count($stmt)) {
                return;
            }
            $tbl->setId($stmt->id);
            $tbl->setIdCliente($stmt->idCliente);
            $tbl->setIdAduana($stmt->idAduana);
            $tbl->setIdUsuario($stmt->idUsuario);
            $tbl->setIdRepositorio($stmt->idRepositorio);
            $tbl->setPatente($stmt->patente);
            $tbl->setAduana($stmt->aduana);
            $tbl->setPedimento($stmt->pedimento);
            $tbl->setPedimentoRectificar($stmt->pedimentoRectificar);
            $tbl->setReferencia($stmt->referencia);
            $tbl->setRfcCliente($stmt->rfcCliente);
            $tbl->setConsolidado($stmt->consolidado);
            $tbl->setRectificacion($stmt->rectificacion);
            $tbl->setTipoCambio($stmt->tipoCambio);
            $tbl->setPagado($stmt->pagado);
            $tbl->setRegimen($stmt->regimen);
            $tbl->setCvePedimento($stmt->cvePedimento);
            $tbl->setIe($stmt->ie);
            $tbl->setEstatus($stmt->estatus);
            $tbl->setFirmaValidacion($stmt->firmaValidacion);
            $tbl->setFirmaBanco($stmt->firmaBanco);
            $tbl->setCreado($stmt->creado);
            $tbl->setActualizado($stmt->actualizado);
            $tbl->setIdUsuarioModif($stmt->idUsuarioModif);
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param Trafico_Model_Table_Traficos $tbl
     * @throws Exception
     */
    public function save(Trafico_Model_Table_Traficos $tbl) {
        try {
            $arr = array(
                "id" => $tbl->getId(),
                "idCliente" => $tbl->getIdCliente(),
                "idAduana" => $tbl->getIdAduana(),
                "idUsuario" => $tbl->getIdUsuario(),
                "patente" => $tbl->getPatente(),
                "aduana" => $tbl->getAduana(),
                "pedimento" => $tbl->getPedimento(),
                "pedimentoRectificar" => $tbl->getPedimentoRectificar(),
                "referencia" => $tbl->getReferencia(),
                "rfcCliente" => $tbl->getRfcCliente(),
                "consolidado" => $tbl->getConsolidado(),
                "rectificacion" => $tbl->getRectificacion(),
                "tipoCambio" => $tbl->getTipoCambio(),
                "pagado" => $tbl->getPagado(),
                "regimen" => $tbl->getRegimen(),
                "cvePedimento" => $tbl->getCvePedimento(),
                "ie" => $tbl->getIe(),
                "estatus" => $tbl->getEstatus(),
                "firmaValidacion" => $tbl->getFirmaValidacion(),
                "firmaBanco" => $tbl->getFirmaBanco(),
                "creado" => $tbl->getCreado(),
                "actualizado" => $tbl->getActualizado(),
                "idUsuarioModif" => $tbl->getIdUsuarioModif(),
            );
            if (null === ($id = $tbl->getId())) {
                $id = $this->_db_table->insert($arr);
                $tbl->setId($id);
            } else {
                $this->_db_table->update($arr, array("id = ?" => $id));
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param Trafico_Model_Table_Traficos $tbl
     * @return type
     * @throws Exception
     */
    public function insert(Trafico_Model_Table_Traficos $tbl) {
        try {
            $arr = array(
                "idCliente" => $tbl->getIdCliente(),
                "idAduana" => $tbl->getIdAduana(),
                "idUsuario" => $tbl->getIdUsuario(),
                "patente" => $tbl->getPatente(),
                "aduana" => $tbl->getAduana(),
                "pedimento" => $tbl->getPedimento(),
                "pedimentoRectificar" => $tbl->getPedimentoRectificar(),
                "referencia" => $tbl->getReferencia(),
                "rfcCliente" => $tbl->getRfcCliente(),
                "consolidado" => $tbl->getConsolidado(),
                "rectificacion" => $tbl->getRectificacion(),
                "tipoCambio" => $tbl->getTipoCambio(),
                "pagado" => $tbl->getPagado(),
                "regimen" => $tbl->getRegimen(),
                "cvePedimento" => $tbl->getCvePedimento(),
                "ie" => $tbl->getIe(),
                "estatus" => $tbl->getEstatus(),
                "firmaValidacion" => $tbl->getFirmaValidacion(),
                "firmaBanco" => $tbl->getFirmaBanco(),
                "creado" => $tbl->getCreado(),
                "actualizado" => $tbl->getActualizado(),
                "idUsuarioModif" => $tbl->getIdUsuarioModif(),
            );
            $id = $this->_db_table->insert($arr);
            if (isset($id)) {
                return $id;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param Trafico_Model_Table_Traficos $tbl
     * @return type
     * @throws Exception
     */
    public function buscarPedimento(Trafico_Model_Table_Traficos $tbl) {
        try {
            $stmt = $this->_db_table->fetchRow(
                    $this->_db_table->select()
                            ->where("patente = ?", $tbl->getPatente())
                            ->where("aduana = ?", $tbl->getAduana())
                            ->where("pedimento = ?", $tbl->getPedimento())
                            ->where("referencia = ?", $tbl->getReferencia())
            );
            if (0 == count($stmt)) {
                return;
            }
            $tbl->setId($stmt->id);
            $tbl->setIdCliente($stmt->idCliente);
            $tbl->setIdAduana($stmt->idAduana);
            $tbl->setIdUsuario($stmt->idUsuario);
            $tbl->setPatente($stmt->patente);
            $tbl->setAduana($stmt->aduana);
            $tbl->setPedimento($stmt->pedimento);
            $tbl->setPedimentoRectificar($stmt->pedimentoRectificar);
            $tbl->setReferencia($stmt->referencia);
            $tbl->setRfcCliente($stmt->rfcCliente);
            $tbl->setConsolidado($stmt->consolidado);
            $tbl->setRectificacion($stmt->rectificacion);
            $tbl->setTipoCambio($stmt->tipoCambio);
            $tbl->setPagado($stmt->pagado);
            $tbl->setRegimen($stmt->regimen);
            $tbl->setCvePedimento($stmt->cvePedimento);
            $tbl->setIe($stmt->ie);
            $tbl->setEstatus($stmt->estatus);
            $tbl->setFirmaValidacion($stmt->firmaValidacion);
            $tbl->setFirmaBanco($stmt->firmaBanco);
            $tbl->setCreado($stmt->creado);
            $tbl->setActualizado($stmt->actualizado);
            $tbl->setIdUsuarioModif($stmt->idUsuarioModif);
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param Trafico_Model_Table_Traficos $tbl
     * @return boolean
     * @throws Exception
     */
    public function comprobar(Trafico_Model_Table_Traficos $tbl) {
        try {
            $stmt = $this->_db_table->fetchRow(
                    $this->_db_table->select()
                            ->where("patente = ?", $tbl->getPatente())
                            ->where("aduana = ?", $tbl->getAduana())
                            ->where("pedimento = ?", $tbl->getPedimento())
            );
            if (0 == count($stmt)) {
                return;
            }
            return true;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param type $idAduana
     * @param type $fechaInicio
     * @param type $fechaFin
     * @param type $porLiberar
     * @return type
     * @throws Exception
     */
    public function reporte($idAduana, $fechaInicio, $fechaFin, $porLiberar = false) {
        try {
            $fields = array(
                "t.aduana",
                "c.nombre AS nombreCliente",
                "s.descripcion AS esquemaPago",
                "t.referencia",
                "t.patente",
                "t.pedimento",
                "t.cvePedimento",
                new Zend_Db_Expr("CASE WHEN t.ie = 'TOCE.IMP' THEN 'IMPO' ELSE 'EXPO' END AS tipoOperacion"),
                new Zend_Db_Expr("CAST(' ' AS CHAR CHARACTER SET utf8) AS carga"),
                "t.fechaEntrada",
                "t.fechaNotificacion",
                "t.fechaEta",
                "t.fechaRevalidacion",
                "t.fechaPrevio",
                "t.fechaDeposito",
                "t.fechaPresentacion",
                "t.fechaCitaDespacho",
                "t.fechaPago",
                "t.fechaLiberacion",
                new Zend_Db_Expr("CAST(' ' AS CHAR CHARACTER SET utf8) AS diasEnProceso"),
            );
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("t" => "traficos"), $fields)
                    ->joinLeft(array("c" => "trafico_clientes"), "c.id = t.idCliente", array(""))
                    ->joinLeft(array("s" => "trafico_esquemafondos"), "s.id = c.esquema", array(""));
            if ($porLiberar == true) {
                $sql->where("t.creado >= ?", date("Y-m-d H:i:s", strtotime($fechaInicio)))
                        ->where("t.creado <= ?", date("Y-m-d H:i:s", strtotime($fechaFin)))
                        ->where("t.fechaLiberacion IS NULL");
            } else {
                $sql->where("t.fechaLiberacion >= ?", date("Y-m-d H:i:s", strtotime($fechaInicio)))
                        ->where("t.fechaLiberacion <= ?", date("Y-m-d H:i:s", strtotime($fechaFin)))
                        ->where("t.fechaLiberacion IS NOT NULL");
            }
            if (isset($idAduana) && (int) $idAduana != 0) {
                $sql->where("t.idAduana = ?", $idAduana);
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

    /**
     * 
     * @param type $idAduana
     * @param type $fechaInicio
     * @param type $fechaFin
     * @param type $pagados
     * @return type
     * @throws Exception
     */
    public function reporteCandados($idAduana, $fechaInicio, $fechaFin) {
        try {
            $fields = array(
                "t.aduana",
                "c.nombre AS nombreCliente",
                "t.referencia",
                "t.patente",
                "t.pedimento",
                "t.cvePedimento",
                "t.fechaPago",
            );
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("t" => "traficos"), $fields)
                    ->joinLeft(array("c" => "trafico_clientes"), "c.id = t.idCliente", array(""))
                    ->joinLeft(array("cc" => "trafico_candados"), "cc.idTrafico = t.id", array("numero"))
                    ->joinLeft(array("ct" => "trafico_trans"), "ct.idTrafico = t.id", array("placas"))
                    ->where("cc.numero IS NOT NULL")
                    ->where("t.fechaLiberacion >= ?", date("Y-m-d H:i:s", strtotime($fechaInicio)))
                    ->where("t.fechaLiberacion <= ?", date("Y-m-d H:i:s", strtotime($fechaFin)));
            if (isset($idAduana)) {
                $sql->where("t.idAduana = ?", $idAduana);
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

    public function asignarAUsuario($idTrafico, $idUsuario) {
        try {
            $stmt = $this->_db_table->update(array("idUsuarioModif" => $idUsuario, "actualizado" => date("Y-m-d H:i:s")), array("id = ?" => $idTrafico));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param string $fechaInicio
     * @param string $fechaFin
     * @param string $rfcSociedad
     * @return array|null
     * @throws Exception
     */
    public function pedimentosPagados($fechaInicio, $fechaFin, $rfcSociedad) {
        try {
            $fields = array(
                "t.aduana",
                "t.rfcCliente",
                "t.referencia",
                "t.patente",
                "t.pedimento",
                "t.cvePedimento",
                "t.ie AS tipoMovimiento",
            );
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("t" => "traficos"), $fields)
                    ->joinLeft(array("c" => "trafico_clientes"), "c.id = t.idCliente", array("rfcSociedad"))
                    ->joinLeft(array("f" => new Zend_Db_Expr("(select ff.idTrafico, ff.fecha AS fechaPago from trafico_fechas ff where ff.tipo = 2)")), "f.idTrafico = t.id", array("fechaPago"))
                    ->where("f.fechaPago >= ?", date("Y-m-d H:i:s", strtotime($fechaInicio)))
                    ->where("f.fechaPago <= ?", date("Y-m-d H:i:s", strtotime($fechaFin)))
                    ->where("c.rfcSociedad = ?", $rfcSociedad)
                    ->where("t.pagado IS NOT NULL AND t.estatus > 2");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $patente
     * @param int $aduana
     * @param int $pedimento
     * @return type
     * @throws Exception
     */
    public function pedimento($patente, $aduana, $pedimento) {
        try {
            $fields = array(
                "t.aduana",
                "t.rfcCliente",
                "c.nombre AS nombreCliente",
                "t.referencia",
                "t.patente",
                "t.pedimento",
                "t.regimen",
                "t.cvePedimento",
                "t.ie AS tipoMovimiento",
                new Zend_Db_Expr("(select DATE_FORMAT(f.fecha,'%d-%m-%Y') from trafico_fechas f where f.tipo = 2 and f.idTrafico = t.id limit 1) AS fechaPago")
            );
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("t" => "traficos"), $fields)
                    ->joinLeft(array("c" => "trafico_clientes"), "c.id = t.idCliente", array(""))
                    ->where("t.patente = ?", $patente)
                    ->where("t.aduana = ?", $aduana)
                    ->where("t.pedimento = ?", $pedimento);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function insertar($arr) {
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
    
    public function traficosDeCliente($rfc, $year) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("t" => "traficos"), array("count(*) AS total", "idAduana"))
                    ->joinLeft(array("c" => "trafico_clientes"), "t.idCliente = c.id", array())
                    ->joinLeft(array("a" => "trafico_aduanas"), "t.idAduana = a.id", array("patente", "aduana", "nombre"))
                    ->where("YEAR(fechaLiberacion) = ?", $year)
                    ->where("c.rfc = ?", $rfc)
                    ->group("t.idAduana");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function traficosDeClientePorMes($rfc, $year, $month, $idAduana) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("t" => "traficos"), array("count(*) AS total"))
                    ->joinLeft(array("c" => "trafico_clientes"), "t.idCliente = c.id", array())
                    ->where("YEAR(fechaLiberacion) = ?", $year)
                    ->where("MONTH(fechaLiberacion) = ?", $month)
                    ->where("c.rfc = ?", $rfc)
                    ->where("t.idAduana = ?", $idAduana);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->total;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function seleccionar($ids) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("t" => "traficos"), array("id", "patente", "aduana", "pedimento", "referencia"))
                    ->where("t.id IN (?)", $ids);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function actualizarOrdenCarga($idTrafico, $ordenCarga) {
        try {
            $stmt = $this->_db_table->update(array("ordenCarga" => $ordenCarga), array("id = ?" => $idTrafico));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function traficoMaster($idMaster, $id) {
        try {
            $stmt = $this->_db_table->update(array("idTrafico" => $idMaster), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function traficosConsolidados($idTrafico) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("t" => "traficos"), array("id", "patente", "aduana", "pedimento", "referencia"))
                    ->where("t.idTrafico = ?", $idTrafico);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
