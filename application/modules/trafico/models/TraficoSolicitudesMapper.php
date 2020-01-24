<?php

class Trafico_Model_TraficoSolicitudesMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_TraficoSolicitudes();
    }

    public function verificar($idCliente, $idAduana, $tipoOperacion, $pedimento, $referencia) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("s" => "trafico_solicitudes"), array("id", "creado", new Zend_Db_Expr("CASE WHEN activa IS NULL THEN 'INACTIVA' ELSE 'ACTIVA' END AS activa"), new Zend_Db_Expr("CASE WHEN generada IS NULL THEN 'NO GENERADA' ELSE 'GENERADA' END AS generada"), new Zend_Db_Expr("CASE WHEN enviada IS NULL THEN 'NO ENVIADA' ELSE 'ENVIADA' END AS enviada"), new Zend_Db_Expr("CASE WHEN borrada IS NULL THEN 'NO BORRADA' ELSE 'BORRADA' END AS borrada")))
                    ->joinLeft(array("u" => "usuarios"), "u.id = s.idUsuario", array("usuario"))
                    ->where("s.idCliente = ?", $idCliente)
                    ->where("s.idAduana = ?", $idAduana)
                    ->where("s.tipoOperacion = ?", $tipoOperacion)
                    ->where("s.pedimento = ?", $pedimento)
                    ->where("s.referencia = ?", $referencia)
                    ->where("s.borrada IS NULL")
                    ->where("s.complemento IS NULL");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function buscar($patente, $aduana, $referencia) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(["s" => "trafico_solicitudes"], ["pedimento"])
                    ->joinLeft(["c" => "trafico_clientes"], "s.idCliente = c.id", array("rfc as rfcCliente"))
                    ->joinLeft(["a" => "trafico_aduanas"], "s.idAduana = a.id", array(""))
                    ->where("a.patente = ?", $patente)
                    ->where("a.aduana = ?", $aduana)
                    ->where("s.referencia = ?", $referencia);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function buscarReferencia($referencia) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(["s" => "trafico_solicitudes"], ["id", "referencia", "pedimento", "enviada"])
                    ->joinLeft(["c" => "trafico_clientes"], "s.idCliente = c.id", array("rfc as rfcCliente"))
                    ->joinLeft(["a" => "trafico_aduanas"], "s.idAduana = a.id", array("patente", "aduana"))
                    ->where("s.referencia = ?", $referencia);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregar($idCliente, $idAduana, $tipoOperacion, $pedimento, $referencia, $idUsuario, $planta = null) {
        try {
            $data = array(
                "idCliente" => $idCliente,
                "idAduana" => $idAduana,
                "idPlanta" => isset($planta) ? $planta : null,
                "tipoOperacion" => $tipoOperacion,
                "pedimento" => $pedimento,
                "referencia" => $referencia,
                "idUsuario" => $idUsuario,
                "creado" => date("Y-m-d H:i:s"),
            );
            $stmt = $this->_db_table->insert($data);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function guardar($id, $idCliente, $idAduana, $tipoOperacion, $pedimento, $referencia) {
        try {
            $arr = array(
                "idCliente" => $idCliente,
                "idAduana" => $idAduana,
                "tipoOperacion" => $tipoOperacion,
                "pedimento" => $pedimento,
                "referencia" => $referencia,
            );
            $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregarComplemento($idCliente, $idAduana, $tipoOperacion, $pedimento, $referencia, $complemento, $idUsuario) {
        try {
            $data = array(
                "idCliente" => $idCliente,
                "idAduana" => $idAduana,
                "tipoOperacion" => $tipoOperacion,
                "pedimento" => $pedimento,
                "referencia" => $referencia,
                "idUsuario" => $idUsuario,
                "complemento" => $complemento,
                "creado" => date("Y-m-d H:i:s"),
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

    public function obtenerMisSolicitudes($idUsuario) {
        try {
            $det = new Trafico_Model_TraficoSolDetalleMapper();
            $con = new Trafico_Model_TraficoSolConceptoMapper();
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("s" => "trafico_solicitudes"), array("*"))
                    ->joinLeft(array("c" => "trafico_clientes"), "s.idCliente = c.id", array("nombre as nombreCliente"))
                    ->joinLeft(array("a" => "trafico_aduanas"), "s.idAduana = a.id", array("aduana", "patente"))
                    ->where("s.enviada IS NULL")
                    ->where("s.idUsuario = ?", $idUsuario)
                    ->order("creado DESC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $data = array();
                foreach ($stmt->toArray() as $item) {
                    $item["detalle"] = $det->tieneDetalle($item["id"]);
                    $item["conceptos"] = $con->tieneConceptos($item["id"]);
                    $data[] = $item;
                }
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $id
     * @param int $patente
     * @param int $aduana
     * @return boolean
     * @throws Exception
     */
    public function obtener($id, $patente = null, $aduana = null) {
        try {
            $model = new Trafico_Model_TraficoSolConceptoMapper();
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("s" => "trafico_solicitudes"), array("*"))
                    ->joinLeft(array("c" => "trafico_clientes"), "s.idCliente = c.id", array("nombre as nombreCliente", "rfc as rfcCliente"))
                    ->joinLeft(array("a" => "trafico_aduanas"), "s.idAduana = a.id", array("aduana", "patente"))
                    ->joinLeft(array("d" => "trafico_soldetalle"), "d.idSolicitud = s.id", array("fechaEta", "bl", "numFactura", "cvePed"))
                    ->joinLeft(array("u" => "usuarios"), "u.id = s.idUsuario", array("nombre", "usuario as nomUsuario", "empresa"))
                    ->where("s.id = ?", $id);
            if (isset($patente) && isset($aduana)) {
                if (!is_array($patente) && !is_array($aduana)) {
                    $sql->where("a.patente = ?", $patente)
                            ->where("a.aduana LIKE ?", substr($aduana, 0, 2) . "%");
                } else {
                    $sql->where("a.patente IN (?)", $patente)
                            ->where("a.aduana IN (?)", $aduana);
                }
            }
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                $data = $stmt->toArray();
                $data["subtotal"] = $model->subtotal($data["id"]);
                $data["anticipo"] = $model->anticipo($data["id"]);
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function exists($id) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("s" => "trafico_solicitudes"), array())
                    ->where("s.id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception($this->_throwException("Db Exception found on", __METHOD__, $ex));
        }
    }

    /**
     * 
     * @param int $patente
     * @param int $aduana
     * @param string $buscar
     * @param boolean $complementos
     * @param int $idAduana
     * @param boolean $pend
     * @param boolean $dep
     * @param boolean $war
     * @return boolean
     * @throws Exception
     */
    public function obtenerSolicitudes($patente = null, $aduana = null, $buscar = null, $complementos = null, $idAduana = null, $pend = null, $dep = null, $war = null) {
        try {
            $model = new Trafico_Model_TraficoSolConceptoMapper();
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("s" => "trafico_solicitudes"), array("*"))
                    ->joinLeft(array("c" => "trafico_clientes"), "s.idCliente = c.id", array("nombre as nombreCliente"))
                    ->joinLeft(array("a" => "trafico_aduanas"), "s.idAduana = a.id", array("aduana", "patente"))
                    ->joinLeft(array("d" => "trafico_soldetalle"), "d.idSolicitud = s.id", array("fechaEta", "peca", "bl", "numFactura"))
                    ->where("s.generada IS NOT NULL")
                    ->where("s.borrada IS NULL")
                    ->order(array("d.fechaEta DESC", "s.creado DESC"))
                    ->limit(200);
            if (isset($buscar) && $buscar != "") {
                $sql->where("d.bl LIKE ?", "%{$buscar}%")
                        ->orWhere("d.numFactura LIKE ?", "%{$buscar}%")
                        ->orWhere("s.referencia LIKE ?", "%{$buscar}%")
                        ->where("s.generada = 1");
            }
            if (isset($patente) && isset($aduana)) {
                if (!is_array($patente) && !is_array($aduana)) {
                    $sql->where("a.patente = ?", $patente)
                            ->where("a.aduana LIKE ?", substr($aduana, 0, 2) . "%");
                } else {
                    $sql->where("a.patente IN (?)", $patente)
                            ->where("a.aduana IN (?)", $aduana);
                }
            }
            if (isset($complementos) && $complementos == true) {
                $sql->where("s.complemento = 1");
            }
            if (isset($idAduana) && $idAduana !== false) {
                $sql->where("s.idAduana = ?", $idAduana);
            }
            if (isset($pend) && $pend !== false) {
                $sql->where("s.aprobada IS NULL");
            }
            if (isset($dep) && $dep !== false) {
                $sql->where("s.depositado IS NULL");
            }
            if (isset($war) && $war !== false) {
                $sql->where("(time_to_sec(timediff(d.fechaEta, s.enviada)) / 3600) <= 48");
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $data = array();
                foreach ($stmt->toArray() as $item) {
                    if ($item["borrada"] == 1 && $item["generada"] !== 1) {
                        continue;
                    }
                    $item["subtotal"] = $model->subtotal($item["id"]);
                    $item["anticipo"] = $model->anticipo($item["id"]);
                    $data[] = $item;
                }
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function obtenerEstatus($pedimento, $referencia) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("s" => "trafico_solicitudes"), array("*"))
                    ->where("s.generada IS NOT NULL")
                    ->where("s.borrada IS NULL")
                    ->where("s.pedimento = ?", $pedimento)
                    ->where("s.referencia = ?", $referencia);
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
     * @param string $buscar
     * @param boolean $complementos
     * @param int $idAduana
     * @param boolean $pend
     * @param boolean $dep
     * @param boolean $war
     * @param int $idCliente
     * @return boolean
     * @throws Exception
     */
    public function obtenerSolicitudesTrafico($aduanas, $buscar = null, $complementos = null, $idAduana = null, $pend = null, $dep = null, $war = null, $idCliente = null) {
        try {
            $model = new Trafico_Model_TraficoSolConceptoMapper();
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("s" => "trafico_solicitudes"), array("*"))
                    ->joinLeft(array("c" => "trafico_clientes"), "s.idCliente = c.id", array("nombre as nombreCliente"))
                    ->joinLeft(array("a" => "trafico_aduanas"), "s.idAduana = a.id", array("aduana", "patente"))
                    ->joinLeft(array("d" => "trafico_soldetalle"), "d.idSolicitud = s.id", array("fechaEta", "peca", "bl", "numFactura"))
                    ->joinLeft(array("q" => "trafico_esquemafondos"), "q.id = s.esquema", array("descripcion as esquemaFondo"))
                    ->where("s.generada IS NOT NULL")
                    ->where("s.borrada IS NULL")
                    ->order(array("d.fechaEta DESC", "s.creado DESC"))
                    ->limit(200);
            if (isset($buscar) && $buscar != "") {
                $sql->where("(d.bl LIKE '%{$buscar}%' OR d.numFactura LIKE '%{$buscar}%' OR s.referencia LIKE '%{$buscar}%' OR s.pedimento LIKE '%{$buscar}%') AND s.generada = 1");
            }
            if (isset($aduanas)) {
                $sql->where("s.idAduana IN (?)", $aduanas);
            }
            if (isset($idCliente) && $idCliente != '') {
                $sql->where("s.idCliente = ?", $idCliente);
            }
            if (isset($complementos) && $complementos == true && !$buscar) {
                $sql->where("s.complemento = 1");
            }
            if (isset($idAduana) && $idAduana !== false && $idAduana !== "") {
                $sql->where("s.idAduana = ?", $idAduana);
            }
            if (isset($pend) && $pend !== false && !$buscar) {
                $sql->where("s.aprobada IS NULL");
            }
            if (isset($dep) && $dep !== false && !$buscar) {
                $sql->where("s.depositado IS NULL")
                        ->where("s.autorizadaHsbc IS NULL")
                        ->where("s.autorizadaBanamex IS NULL");
            }
            if (isset($war) && $war !== false && !$buscar) {
                $sql->where("(time_to_sec(timediff(d.fechaEta, s.enviada)) / 3600) <= 48");
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $data = array();
                foreach ($stmt->toArray() as $item) {
                    if ($item["borrada"] == 1 && $item["generada"] !== 1) {
                        continue;
                    }
                    $item["subtotal"] = $model->subtotal($item["id"]);
                    $item["anticipo"] = $model->anticipo($item["id"]);
                    $data[] = $item;
                }
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param string $buscar
     * @param type $complementos
     * @param type $dep
     * @param type $war
     * @return boolean
     * @throws Exception
     */
    public function solicitudesAnticipo($buscar = null, $complementos = null, $dep = null, $war = null, $idAduana = null) {
        try {
            $model = new Trafico_Model_TraficoSolConceptoMapper();
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("s" => "trafico_solicitudes"), array("*"))
                    ->joinLeft(array("c" => "trafico_clientes"), "s.idCliente = c.id", array("nombre as nombreCliente"))
                    ->joinLeft(array("a" => "trafico_aduanas"), "s.idAduana = a.id", array("aduana", "patente"))
                    ->joinLeft(array("d" => "trafico_soldetalle"), "d.idSolicitud = s.id", array("fechaEta", "peca", "bl", "numFactura"))
                    ->joinLeft(array("q" => "trafico_esquemafondos"), "q.id = s.esquema", array("descripcion as esquemaFondo"))
                    ->where("s.generada IS NOT NULL")
                    ->where("s.autorizada IS NOT NULL")
                    ->where("s.borrada IS NULL")
                    ->order(array("d.fechaEta DESC", "s.creado DESC"))
                    ->limit(200);
            if (isset($buscar) && $buscar != "") {
                $sql->where("(d.bl LIKE '%{$buscar}%' OR d.numFactura LIKE '%{$buscar}%' OR s.referencia LIKE '%{$buscar}%') AND s.generada = 1");
            }
            if (isset($complementos) && $complementos == true) {
                $sql->where("s.complemento = 1");
            }
            if (isset($dep) && $dep !== false) {
                $sql->where("s.depositado IS NULL");
            }
            if (isset($war) && $war !== false) {
                $sql->where("(time_to_sec(timediff(d.fechaEta, s.enviada)) / 3600) <= 48");
            }
            if (isset($idAduana) && $idAduana !== false) {
                $sql->where("s.idAduana = ?", $idAduana);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $data = array();
                foreach ($stmt->toArray() as $item) {
                    if ($item["borrada"] == 1 && $item["generada"] !== 1) {
                        continue;
                    }
                    $item["subtotal"] = $model->subtotal($item["id"]);
                    $item["anticipo"] = $model->anticipo($item["id"]);
                    $data[] = $item;
                }
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param string $buscar
     * @param type $complementos
     * @param type $dep
     * @param type $war
     * @return boolean
     * @throws Exception
     */
    public function solicitudesEnTramite($buscar = null, $complementos = null, $dep = null, $war = null, $idAduana = null) {
        try {
            $model = new Trafico_Model_TraficoSolConceptoMapper();
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("s" => "trafico_solicitudes"), array("*"))
                    ->joinLeft(array("c" => "trafico_clientes"), "s.idCliente = c.id", array("nombre as nombreCliente"))
                    ->joinLeft(array("a" => "trafico_aduanas"), "s.idAduana = a.id", array("aduana", "patente"))
                    ->joinLeft(array("d" => "trafico_soldetalle"), "d.idSolicitud = s.id", array("fechaEta", "peca", "bl", "numFactura"))
                    ->joinLeft(array("q" => "trafico_esquemafondos"), "q.id = s.esquema", array("descripcion as esquemaFondo"))
                    ->where("s.tramite IS NOT NULL")
                    ->where("s.deposito IS NULL")
                    ->where("s.borrada IS NULL")
                    ->order(array("d.fechaEta DESC", "s.creado DESC"))
                    ->limit(200);
            if (isset($buscar) && $buscar != "") {
                $sql->where("(d.bl LIKE '%{$buscar}%' OR d.numFactura LIKE '%{$buscar}%' OR s.referencia LIKE '%{$buscar}%') AND s.generada = 1");
            }
            if (isset($complementos) && $complementos == true) {
                $sql->where("s.complemento = 1");
            }
            if (isset($dep) && $dep !== false) {
                $sql->where("s.depositado IS NULL");
            }
            if (isset($war) && $war !== false) {
                $sql->where("(time_to_sec(timediff(d.fechaEta, s.enviada)) / 3600) <= 48");
            }
            if (isset($idAduana) && $idAduana !== false) {
                $sql->where("s.idAduana = ?", $idAduana);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $data = array();
                $total = 0;
                foreach ($stmt->toArray() as $item) {
                    if ($item["borrada"] == 1 && $item["generada"] !== 1) {
                        continue;
                    }
                    $item["subtotal"] = $model->subtotal($item["id"]);
                    $item["anticipo"] = $model->anticipo($item["id"]);
                    $total = $total + ($item["subtotal"] - $item["anticipo"]);
                    $data[] = $item;
                }
                $data["total"] = $total;
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param string $buscar
     * @param type $complementos
     * @param type $war
     * @return boolean
     * @throws Exception
     */
    public function solicitudesSupervision($buscar = null, $complementos = null, $war = null, $idAduana = null) {
        try {
            $model = new Trafico_Model_TraficoSolConceptoMapper();
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("s" => "trafico_solicitudes"), array("*"))
                    ->joinLeft(array("c" => "trafico_clientes"), "s.idCliente = c.id", array("nombre as nombreCliente"))
                    ->joinLeft(array("a" => "trafico_aduanas"), "s.idAduana = a.id", array("aduana", "patente"))
                    ->joinLeft(array("d" => "trafico_soldetalle"), "d.idSolicitud = s.id", array("fechaEta", "peca", "bl", "numFactura"))
                    ->joinLeft(array("q" => "trafico_esquemafondos"), "q.id = s.esquema", array("descripcion as esquemaFondo"))
                    ->where("s.generada IS NOT NULL")
                    ->where("s.autorizada IS NOT NULL")
                    ->where("s.depositado IS NULL AND s.deposito IS NULL")
                    ->where("s.borrada IS NULL")
                    ->order(array("d.fechaEta DESC", "s.creado DESC"))
                    ->limit(200);
            if (isset($buscar) && $buscar != "") {
                $sql->where("(d.bl LIKE '%{$buscar}%' OR d.numFactura LIKE '%{$buscar}%' OR s.referencia LIKE '%{$buscar}%') AND s.generada = 1");
            }
            if (isset($complementos) && $complementos == true) {
                $sql->where("s.complemento = 1");
            }
            if (isset($war) && $war !== false) {
                $sql->where("(time_to_sec(timediff(d.fechaEta, s.enviada)) / 3600) <= 48");
            }
            if (isset($idAduana) && $idAduana !== false) {
                $sql->where("s.idAduana = ?", $idAduana);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $data = array();
                $total = 0;
                foreach ($stmt->toArray() as $item) {
                    if ($item["borrada"] == 1 && $item["generada"] !== 1) {
                        continue;
                    }
                    $item["subtotal"] = $model->subtotal($item["id"]);
                    $item["anticipo"] = $model->anticipo($item["id"]);
                    $total = $total + ($item["subtotal"] - $item["anticipo"]);
                    $data[] = $item;
                }
                $data["total"] = $total;
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
     * @param string $fechaIni
     * @param string $fechaFin
     * @return boolean
     * @throws Exception
     */
    public function obtenerSolicitudesEtaVencido($patente = null, $aduana = null, $fechaIni = null, $fechaFin = null) {
        try {
            $model = new Trafico_Model_TraficoSolConceptoMapper();
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("s" => "trafico_solicitudes"), array("*"))
                    ->joinLeft(array("c" => "trafico_clientes"), "s.idCliente = c.id", array("nombre as nombreCliente"))
                    ->joinLeft(array("a" => "trafico_aduanas"), "s.idAduana = a.id", array("aduana", "patente"))
                    ->joinLeft(array("d" => "trafico_soldetalle"), "d.idSolicitud = s.id", array("fechaEta", "peca", "bl", "numFactura"))
                    ->where("s.generada = 1")
                    ->where("s.deposito IS NULL")
                    ->where("s.borrada IS NULL")
                    ->where("(time_to_sec(timediff(d.fechaEta, s.enviada)) / 3600) >= 24")
                    ->order("d.fechaEta DESC");
            if (isset($patente) && isset($aduana)) {
                if (!is_array($patente) && !is_array($aduana)) {
                    $sql->where("a.patente = ?", $patente)
                            ->where("a.aduana LIKE ?", substr($aduana, 0, 2) . "%");
                } else {
                    $sql->where("a.patente IN (?)", $patente)
                            ->where("a.aduana IN (?)", $aduana);
                }
            }
            if (isset($fechaIni) && isset($fechaFin)) {
                $sql->where("s.enviada >= ?", date("Y-m-d", strtotime($fechaIni)) . " 00:00:00")
                        ->where("s.enviada <= ?", date("Y-m-d", strtotime($fechaFin)) . " 23:59:59");
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $data = array();
                foreach ($stmt->toArray() as $item) {
                    $item["subtotal"] = $model->subtotal($item["id"]);
                    $item["anticipo"] = $model->anticipo($item["id"]);
                    $data[] = $item;
                }
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $patente
     * @param int $aduana
     * @param string $fechaIni
     * @param string $fechaFin
     * @return boolean
     * @throws Exception
     */
    public function obtenerSolicitudesDepositadas($patente = null, $aduana = null, $fechaIni = null, $fechaFin = null) {
        try {
            $model = new Trafico_Model_TraficoSolConceptoMapper();
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("s" => "trafico_solicitudes"), array("*"))
                    ->joinLeft(array("c" => "trafico_clientes"), "s.idCliente = c.id", array("nombre as nombreCliente"))
                    ->joinLeft(array("a" => "trafico_aduanas"), "s.idAduana = a.id", array("aduana", "patente"))
                    ->joinLeft(array("d" => "trafico_soldetalle"), "d.idSolicitud = s.id", array("fechaEta", "peca", "bl", "numFactura"))
                    ->where("s.generada = 1")
                    ->where("s.deposito = 1")
                    ->where("s.borrada IS NULL")
                    ->order("d.fechaEta DESC");
            if (isset($patente) && isset($aduana)) {
                if (!is_array($patente) && !is_array($aduana)) {
                    $sql->where("a.patente = ?", $patente)
                            ->where("a.aduana LIKE ?", substr($aduana, 0, 2) . "%");
                } else {
                    $sql->where("a.patente IN (?)", $patente)
                            ->where("a.aduana IN (?)", $aduana);
                }
            }
            if (isset($fechaIni) && isset($fechaFin)) {
                $sql->where("s.depositado >= ?", date("Y-m-d", strtotime($fechaIni)) . " 00:00:00")
                        ->where("s.depositado <= ?", date("Y-m-d", strtotime($fechaFin)) . " 23:59:59");
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $data = array();
                foreach ($stmt->toArray() as $item) {
                    $item["subtotal"] = $model->subtotal($item["id"]);
                    $item["anticipo"] = $model->anticipo($item["id"]);
                    $data[] = $item;
                }
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    /**
     * 
     * @param int $patente
     * @param int $aduana
     * @param string $fechaIni
     * @param string $fechaFin
     * @return boolean
     * @throws Exception
     */
    public function reporteSolicitudes($idAduana = null, $fechaIni = null, $fechaFin = null, $noDepositado = null, $complemento = null) {
        try {
            $fields = array(
                "a.aduana",
                "c.nombre AS nombreCliente",
                new Zend_Db_Expr("CAST('' as CHAR(1)) AS esquemaPago"),
                "s.referencia",
                new Zend_Db_Expr("(CASE WHEN s.complemento IS NULL THEN '' ELSE 'SI' END) AS complemento"),
                "a.patente",
                "s.pedimento",
                "d.cvePed AS cvePedimento",
                "s.tipoOperacion",
                "d.tipoCarga",
                new Zend_Db_Expr("DATE_FORMAT(d.fechaAlmacenaje,'%d-%m-%Y') AS fechaAlmacenaje"),
                new Zend_Db_Expr("DATE_FORMAT(d.fechaEta,'%d-%m-%Y') AS fechaEta"),
                new Zend_Db_Expr("(select SUM(l.importe) as importe from trafico_solconcepto l where l.idSolicitud = s.id and l.concepto <> 'ANTICIPO') AS subTotal"),
                new Zend_Db_Expr("(select l.importe from trafico_solconcepto l where l.idSolicitud = s.id and l.concepto = 'ANTICIPO') AS anticipo"),
                new Zend_Db_Expr("((select SUM(l.importe) as importe from trafico_solconcepto l where l.idSolicitud = s.id and l.concepto <> 'ANTICIPO') - (select l.importe from trafico_solconcepto l where l.idSolicitud = s.id and l.concepto = 'ANTICIPO')) AS total"),
            );
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("s" => "trafico_solicitudes"), $fields)
                    ->joinLeft(array("c" => "trafico_clientes"), "s.idCliente = c.id", array(""))
                    ->joinLeft(array("a" => "trafico_aduanas"), "s.idAduana = a.id", array(""))
                    ->joinLeft(array("d" => "trafico_soldetalle"), "d.idSolicitud = s.id", array(""))
                    ->where("s.generada = 1")
                    ->where("s.borrada IS NULL")
                    ->order("d.fechaEta DESC");
            if (isset($idAduana)) {
                $sql->where("s.idAduana = ?", $idAduana);                
            }
            if (isset($complemento) && $complemento == 1) {
                $sql->where("s.complemento IS NOT NULL");
            }
            if (!isset($noDepositado)) {
                $sql->where("s.deposito = 1");
            } elseif (isset($noDepositado) && $noDepositado == 1) {
                $sql->where("s.deposito IS NULL");
            }
            if (isset($fechaIni) && isset($fechaFin)) {
                $sql->where("s.enviada >= ?", date("Y-m-d", strtotime($fechaIni)) . " 00:00:00")
                        ->where("s.enviada <= ?", date("Y-m-d", strtotime($fechaFin)) . " 23:59:59");
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function buscarId(Trafico_Model_Table_TraficoSolicitudes $tbl) {
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
            $tbl->setTipoOperacion($stmt->tipoOperacion);
            $tbl->setPedimento($stmt->pedimento);
            $tbl->setReferencia($stmt->referencia);
            $tbl->setCreado($stmt->creado);
            $tbl->setGenerada($stmt->generada);
            $tbl->setEnviada($stmt->enviada);
            $tbl->setAutorizada($stmt->autorizada);
            $tbl->setAprobada($stmt->aprobada);
            $tbl->setTramite($stmt->tramite);
            $tbl->setTramitada($stmt->tramitada);
            $tbl->setDeposito($stmt->deposito);
            $tbl->setDepositado($stmt->depositado);
            $tbl->setActualizada($stmt->actualizada);
            $tbl->setComplemento($stmt->complemento);
            $tbl->setActiva($stmt->activa);
            $tbl->setBorrada($stmt->borrada);
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function buscarPorBlFactura($patente = null, $aduana = null) {
        try {
            $model = new Trafico_Model_TraficoSolConceptoMapper();
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("s" => "trafico_solicitudes"), array("*"))
                    ->joinLeft(array("c" => "trafico_clientes"), "s.idCliente = c.id", array("nombre as nombreCliente"))
                    ->joinLeft(array("a" => "trafico_aduanas"), "s.idAduana = a.id", array("aduana", "patente"))
                    ->joinLeft(array("d" => "trafico_soldetalle"), "d.idSolicitud = s.id", array("fechaEta", "peca", "bl", "numFactura"))
                    ->where("s.generada = 1")
                    ->where("s.borrada IS NULL")
                    ->order("d.fechaEta DESC");
            if (isset($patente) && isset($aduana)) {
                if (!is_array($patente) && !is_array($aduana)) {
                    $sql->where("a.patente = ?", $patente)
                            ->where("a.aduana LIKE ?", substr($aduana, 0, 2) . "%");
                } else {
                    $sql->where("a.patente IN (?)", $patente)
                            ->where("a.aduana IN (?)", $aduana);
                }
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $data = array();
                foreach ($stmt->toArray() as $item) {
                    $item["subtotal"] = $model->subtotal($item["id"]);
                    $item["anticipo"] = $model->anticipo($item["id"]);
                    $data[] = $item;
                }
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function establecerIdTrafico($id, $idTrafico) {
        try {
            $stmt = $this->_db_table->update(array("idTrafico" => $idTrafico), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function activarSolicitud($id) {
        try {
            $stmt = $this->_db_table->update(array("enviada" => date("Y-m-d H:i:s"), "actualizada" => date("Y-m-d H:i:s"), "generada" => 1), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function borrarSolicitud($id) {
        try {
            $stmt = $this->_db_table->update(array("actualizada" => date("Y-m-d H:i:s"), "borrada" => 1), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function delete($id) {
        try {
            $stmt = $this->_db_table->delete(array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function aprobarSolicitud($id) {
        try {
            $stmt = $this->_db_table->update(array("aprobada" => date("Y-m-d H:i:s"), "autorizada" => 1), array("id = ?" => $id));
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

    public function esquemaSolicitud($id, $esquema) {
        try {
            $stmt = $this->_db_table->update(array("aprobada" => date("Y-m-d H:i:s"), "autorizada" => $esquema), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function tramiteSolicitud($id) {
        try {
            $stmt = $this->_db_table->update(array("tramitada" => date("Y-m-d H:i:s"), "tramite" => 1), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function depositarSolicitud($id) {
        try {
            $stmt = $this->_db_table->update(array("depositado" => date("Y-m-d H:i:s"), "deposito" => 1), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function propietario($idSolicitud) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("s" => "trafico_solicitudes", array("idUsuario", "idAduana", "pedimento", "referencia")))
                    ->joinLeft(array("a" => "trafico_aduanas"), "s.idAduana = a.id", array("patente", "aduana"))
                    ->joinLeft(array("u" => "usuarios"), "u.id = s.idUsuario", array("email", "nombre"))
                    ->where("s.id = ?", $idSolicitud);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function updateRequest($id, $arr) {
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

    public function decripcionEsquemaFondos($id) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("s" => "trafico_esquemafondos", array("descripcion")))
                    ->where("s.id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                $arr = $stmt->toArray();
                return $arr["descripcion"];
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function find(Trafico_Model_Table_TraficoSolicitudes $t) {
        try {
            $stmt = $this->_db_table->fetchRow(
                    $this->_db_table->select()
                            ->where("idCliente = ?", $t->getIdCliente())
                            ->where("idAduana = ?", $t->getIdAduana())
                            ->where("tipoOperacion = ?", $t->getTipoOperacion())
                            ->where("pedimento = ?", $t->getPedimento())
                            ->where("referencia = ?", $t->getReferencia())
                            ->where("complemento IS NULL")
            );
            if (0 == count($stmt)) {
                return;
            }
            $t->setId($stmt->id);
            $t->setIdCliente($stmt->idCliente);
            $t->setIdAduana($stmt->idAduana);
            $t->setIdUsuario($stmt->idUsuario);
            $t->setTipoOperacion($stmt->tipoOperacion);
            $t->setPedimento($stmt->pedimento);
            $t->setReferencia($stmt->referencia);
            $t->setCreado($stmt->creado);
            $t->setEsquema($stmt->esquema);
            $t->setGenerada($stmt->generada);
            $t->setEnviada($stmt->enviada);
            $t->setAutorizada($stmt->autorizada);
            $t->setAprobada($stmt->aprobada);
            $t->setTramite($stmt->tramite);
            $t->setTramitada($stmt->tramitada);
            $t->setDeposito($stmt->deposito);
            $t->setDepositado($stmt->depositado);
            $t->setActualizada($stmt->actualizada);
            $t->setComplemento($stmt->complemento);
            $t->setActiva($stmt->activa);
            $t->setBorrada($stmt->borrada);
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function save(Trafico_Model_Table_TraficoSolicitudes $t) {
        try {
            $arr = array(
                "id" => $t->getId(),
                "idCliente" => $t->getIdCliente(),
                "idAduana" => $t->getIdAduana(),
                "idUsuario" => $t->getIdUsuario(),
                "tipoOperacion" => $t->getTipoOperacion(),
                "pedimento" => $t->getPedimento(),
                "referencia" => $t->getReferencia(),
                "creado" => $t->getCreado(),
                "esquema" => $t->getEsquema(),
                "generada" => $t->getGenerada(),
                "enviada" => $t->getEnviada(),
                "autorizada" => $t->getAutorizada(),
                "aprobada" => $t->getAprobada(),
                "tramite" => $t->getTramite(),
                "tramitada" => $t->getTramitada(),
                "deposito" => $t->getDeposito(),
                "depositado" => $t->getDepositado(),
                "actualizada" => $t->getActualizada(),
                "complemento" => $t->getComplemento(),
                "activa" => $t->getActiva(),
                "borrada" => $t->getBorrada(),
            );
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizar($idSolicitud, $arr) {
        try {
            $stmt = $this->_db_table->update($arr, array("id = ?" => $idSolicitud));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function update(Trafico_Model_Table_TraficoSolicitudes $t) {
        try {
            $arr = array(
                "idCliente" => $t->getIdCliente(),
                "idAduana" => $t->getIdAduana(),
                "idUsuario" => $t->getIdUsuario(),
                "tipoOperacion" => $t->getTipoOperacion(),
                "pedimento" => $t->getPedimento(),
                "referencia" => $t->getReferencia(),
                "creado" => $t->getCreado(),
                "esquema" => $t->getEsquema(),
                "generada" => $t->getGenerada(),
                "enviada" => $t->getEnviada(),
                "autorizada" => $t->getAutorizada(),
                "aprobada" => $t->getAprobada(),
                "tramite" => $t->getTramite(),
                "tramitada" => $t->getTramitada(),
                "deposito" => $t->getDeposito(),
                "depositado" => $t->getDepositado(),
                "actualizada" => $t->getActualizada(),
                "complemento" => $t->getComplemento(),
                "activa" => $t->getActiva(),
                "borrada" => $t->getBorrada(),
            );
            $stmt = $this->_db_table->update($arr, array("id = ?" => $t->getId()));
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

}
