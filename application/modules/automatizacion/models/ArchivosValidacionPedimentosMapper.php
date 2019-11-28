<?php

class Automatizacion_Model_ArchivosValidacionPedimentosMapper {

    protected $_dbTable;

    public function setDbTable($dbTable) {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception("Invalid table data gateway provided");
        }
        $this->_dbTable = $dbTable;
        return $this;
    }

    public function getDbTable() {
        if (null === $this->_dbTable) {
            $this->setDbTable("Automatizacion_Model_DbTable_ArchivosValidacionPedimentos");
        }
        return $this->_dbTable;
    }

    /**
     * 
     * @param Automatizacion_Model_Table_ArchivosValidacionPedimentos $table
     * @throws Exception
     */
    public function save(Automatizacion_Model_Table_ArchivosValidacionPedimentos $table) {
        try {
            $data = array(
                "id" => $table->getId(),
                "idArchivoValidacion" => $table->getIdArchivoValidacion(),
                "archivoNombre" => $table->getArchivoNombre(),
                "patente" => $table->getPatente(),
                "aduana" => $table->getAduana(),
                "pedimento" => $table->getPedimento(),
                "tipoMovimiento" => $table->getTipoMovimiento(),
                "pedimentoDesistir" => $table->getPedimentoDesistir(),
                "cveDoc" => $table->getCveDoc(),
                "rfcCliente" => $table->getRfcCliente(),
                "rfcSociedad" => $table->getRfcSociedad(),
                "curpAgente" => $table->getCurpAgente(),
                "fechaEntrada" => $table->getFechaEntrada(),
                "fechaPago" => $table->getFechaPago(),
                "fechaExtraccion" => $table->getFechaExtraccion(),
                "fechaPresentacion" => $table->getFechaPresentacion(),
                "fechaUsaCan" => $table->getFechaUsaCan(),
                "fechaOriginal" => $table->getFechaOriginal(),
                "firmaDigital" => $table->getFirmaDigital(),
                "consolidado" => $table->getConsolidado(),
                "remesa" => $table->getRemesa(),
                "creado" => date("Y-m-d H:i:s"),
            );
            if (null === ($id = $table->getId())) {
                unset($data["id"]);
                $id = $this->getDbTable()->insert($data);
                $table->setId($id);
            } else {
                $this->getDbTable()->update($data, array("id = ?" => $id));
            }
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    /**
     * 
     * @param Automatizacion_Model_Table_ArchivosValidacionPedimentos $table
     * @throws Exception
     */
    public function update(Automatizacion_Model_Table_ArchivosValidacionPedimentos $table) {
        try {
            $data = array(
                "archivoNombre" => $table->getArchivoNombre(),
                "patente" => $table->getPatente(),
                "aduana" => $table->getAduana(),
                "pedimento" => $table->getPedimento(),
                "tipoMovimiento" => $table->getTipoMovimiento(),
                "pedimentoDesistir" => $table->getPedimentoDesistir(),
                "cveDoc" => $table->getCveDoc(),
                "rfcCliente" => $table->getRfcCliente(),
                "rfcSociedad" => $table->getRfcSociedad(),
                "curpAgente" => $table->getCurpAgente(),
                "fechaEntrada" => $table->getFechaEntrada(),
                "fechaPago" => $table->getFechaPago(),
                "fechaExtraccion" => $table->getFechaExtraccion(),
                "fechaPresentacion" => $table->getFechaPresentacion(),
                "fechaUsaCan" => $table->getFechaUsaCan(),
                "fechaOriginal" => $table->getFechaOriginal(),
                "firmaDigital" => $table->getFirmaDigital(),
                "consolidado" => $table->getConsolidado(),
                "remesa" => $table->getRemesa(),
            );
            $this->getDbTable()->update($data, array("id = ?" => $table->getId()));
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    /**
     * 
     * @param Automatizacion_Model_Table_ArchivosValidacionPedimentos $table
     * @return type
     * @throws Exception
     */
    public function find(Automatizacion_Model_Table_ArchivosValidacionPedimentos $table) {
        try {
            $result = $this->getDbTable()->fetchRow(
                    $this->getDbTable()->select()
                            ->where("idArchivoValidacion = ?", $table->getIdArchivoValidacion())
                            ->where("archivoNombre = ?", $table->getArchivoNombre())
                            ->where("patente = ?", $table->getPatente())
                            ->where("aduana = ?", $table->getAduana())
                            ->where("pedimento = ?", $table->getPedimento())
            );
            if (0 == count($result)) {
                return;
            }
            $table->setId($result->id);
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    /**
     * 
     * @param int $idArchivoValidacion
     * @return type
     * @throws Exception
     */
    public function informacionArchivo($idArchivoValidacion) {
        try {
            $stmt = $this->getDbTable()->fetchAll(
                    $this->getDbTable()->select()
                            ->where("idArchivoValidacion = ?", $idArchivoValidacion)
            );
            if (0 == count($stmt)) {
                return;
            }
            return $stmt;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    /**
     * 
     * @param int $patente
     * @param int $pedimento
     * @param int $aduana
     * @return type
     * @throws Exception
     */
    public function pedimento($patente, $pedimento, $aduana = null) {
        try {
            $sql = $this->getDbTable()->select()
                    ->from(array("p" => "archivos_validacion_pedimentos"), array("archivoNombre", "tipoMovimiento", "cveDoc", "rfcCliente", "rfcSociedad", "curpAgente", "firmaDigital"))
                    ->where("patente = ?", $patente)
                    ->where("pedimento = ?", $pedimento)
                    ->where("cveDoc <> '' AND cveDoc IS NOT NULL")
                    ->order("creado DESC");
            if (isset($aduana)) {
                $sql->where("aduana = ?", $aduana);
            }
            $result = $this->getDbTable()->fetchRow($sql);
            if (0 == count($result)) {
                return;
            }
            return $result->toArray();
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    /**
     * 
     * @param int $patente
     * @param int $pedimento
     * @param int $aduana
     * @return type
     * @throws Exception
     */
    public function obtenerTodos($patente, $pedimento, $aduana = null) {
        try {
            $select = $this->getDbTable()->select()
                    ->setIntegrityCheck(false)
                    ->from(array("p" => "archivos_validacion_pedimentos"), array("archivoNombre", "tipoMovimiento", "cveDoc", "rfcCliente", "rfcSociedad", "curpAgente", "firmaDigital", "idArchivoValidacion"))
                    ->where("p.patente = ?", $patente)
                    ->where("p.pedimento = ?", $pedimento)
                    ->where("p.cveDoc <> '' AND p.cveDoc IS NOT NULL")
                    ->order("p.creado DESC");
            if (isset($aduana)) {
                $select->where("p.aduana LIKE ?", substr($aduana, 0, 2) . "%");
            }
            $result = $this->getDbTable()->fetchAll($select);
            if (0 == count($result)) {
                return;
            }
            return $result->toArray();
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    /**
     * 
     * @param int $patente
     * @param int $pedimento
     * @param int $aduana
     * @return type
     * @throws Exception
     */
    public function obtenerUltimo($patente, $pedimento, $aduana = null) {
        try {
            $select = $this->getDbTable()->select()
                    ->setIntegrityCheck(false)
                    ->from(array("p" => "archivos_validacion_pedimentos"), array("archivoNombre", "tipoMovimiento", "cveDoc", "rfcCliente", "rfcSociedad", "curpAgente", "firmaDigital", "idArchivoValidacion"))
                    ->where("p.patente = ?", $patente)
                    ->where("p.pedimento = ?", $pedimento)
                    ->where("p.cveDoc <> '' AND p.cveDoc IS NOT NULL")
                    ->order("p.creado DESC");
            if (isset($aduana)) {
                $select->where("p.aduana LIKE ?", substr($aduana, 0, 2) . "%");
            }
            $result = $this->getDbTable()->fetchRow($select);
            if (0 == count($result)) {
                return;
            }
            return $result->toArray();
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    /**
     * 
     * @param string $rfcCliente
     * @param int $year
     * @param int $mes
     * @param string $fecha
     * @param int $patente
     * @return array
     * @throws Exception
     */
    public function pedimentosPagados($rfcCliente, $year = null, $mes = null, $fecha = null, $patente = null) {
        try {
            $m3 = new Automatizacion_Model_ArchivosValidacionMapper();
            $pg = new Automatizacion_Model_ArchivosValidacionPagosMapper();
            $vl = new Automatizacion_Model_ArchivosValidacionFirmasMapper();
            $select = $this->getDbTable()->select()
                    ->from(array("p" => "archivos_validacion_pedimentos"), array("archivoNombre", "patente", "pedimento", "aduana", "creado"))
                    ->where("p.cveDoc <> '' AND p.cveDoc IS NOT NULL")
                    ->where("p.rfcCliente = ?", $rfcCliente)
                    ->order("p.creado DESC");
            if (isset($patente)) {
                $select->where("p.patente = ?", $patente);
            }
            if (isset($fecha)) {
                $select->where("p.fechaPago LIKE ?", date("Y-m-d", strtotime($fecha)) . "%");
            }
            if (isset($year) && !isset($fecha)) {
                $select->where("YEAR(p.creado) = ?", $year);
            }
            if (isset($mes) && !isset($fecha)) {
                $select->where("MONTH(p.creado) = ?", $mes);
            }
            $result = $this->getDbTable()->fetchAll($select);
            if (0 != count($result)) {
                $data = array();
                foreach ($result->toArray() as $item) {
                    $firmas = $vl->findFile($item["patente"], $item["aduana"], $item["pedimento"], $item["archivoNombre"]);
                    $pago = $pg->findFile($item["patente"], $item["aduana"], $item["pedimento"]);
                    if (isset($pago)) {
                        $pagoe = $m3->getEFile(str_replace('A', 'E', $pago["archivoNombre"]));
                    }
                    if (isset($firmas) && isset($pago)) {
                        $item["m3"] = $m3->findFile($item["archivoNombre"], $item["aduana"]);
                        $item["pago"] = $pago;
                        if (isset($pagoe)) {
                            $item["pagoe"] = $pagoe;
                        }
                        $item["firma"] = $firmas;
                        $data[] = $item;
                    }
                }
                return $data;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function pedimentosPagadosRango($rfcCliente, $fechaIni, $fechaFin) {
        try {
            $m3 = new Automatizacion_Model_ArchivosValidacionMapper();
            $pg = new Automatizacion_Model_ArchivosValidacionPagosMapper();
            $vl = new Automatizacion_Model_ArchivosValidacionFirmasMapper();
            $select = $this->getDbTable()->select()
                    ->from(array("p" => "archivos_validacion_pedimentos"), array("archivoNombre", "patente", "pedimento", "aduana", "creado"))
                    ->where("p.cveDoc <> '' AND p.cveDoc IS NOT NULL")
                    ->where("p.rfcCliente = ?", $rfcCliente)
                    ->order("p.creado DESC");
            if (isset($patente)) {
                $select->where("p.patente = ?", $patente);
            }
            if (isset($fechaIni)) {
                $select->where("p.fechaPago >= ?", date("Y-m-d", strtotime($fechaIni)) . "%")
                        ->where("p.fechaPago <= ?", date("Y-m-d", strtotime($fechaFin)) . "%");
            }
            $result = $this->getDbTable()->fetchAll($select);
            if (0 != count($result)) {
                $data = array();
                foreach ($result->toArray() as $item) {
                    $firmas = $vl->findFile($item["patente"], $item["aduana"], $item["pedimento"], $item["archivoNombre"]);
                    $pago = $pg->findFile($item["patente"], $item["aduana"], $item["pedimento"]);
                    if (isset($firmas) && isset($pago)) {
                        $item["m3"] = $m3->findFile($item["archivoNombre"], $item["aduana"]);
                        $item["pago"] = $pago;
                        $item["firma"] = $firmas;
                        $data[] = $item;
                    }
                }
                return $data;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    /**
     *      
     * @param int $idArchivoValidacion
     * @return type
     * @throws Exception
     */
    public function pedimentos($idArchivoValidacion) {
        try {
            $select = $this->getDbTable()->select()
                    ->from(array("p" => "archivos_validacion_pedimentos"), array("patente", "pedimento", "aduana", "tipoMovimiento", "cveDoc", "rfcCliente"))
                    ->where("p.idArchivoValidacion = ?", $idArchivoValidacion);
            $result = $this->getDbTable()->fetchAll($select);
            if (0 != count($result)) {
                return $result->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

}
