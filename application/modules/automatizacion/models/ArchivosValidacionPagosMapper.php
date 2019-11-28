<?php

class Automatizacion_Model_ArchivosValidacionPagosMapper {

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
            $this->setDbTable("Automatizacion_Model_DbTable_ArchivosValidacionPagos");
        }
        return $this->_dbTable;
    }

    public function save(Automatizacion_Model_Table_ArchivosValidacionPagos $table) {
        try {
            $data = array(
                "id" => $table->getId(),
                "idArchivoValidacion" => $table->getIdArchivoValidacion(),
                "patente" => $table->getPatente(),
                "aduana" => $table->getAduana(),
                "pedimento" => $table->getPedimento(),
                "rfcImportador" => $table->getRfcImportador(),
                "caja" => $table->getCaja(),
                "numOperacion" => $table->getNumOperacion(),
                "firmaBanco" => $table->getFirmaBanco(),
                "error" => $table->getError(),
                "fecha" => $table->getFecha(),
                "hora" => $table->getHora(),
                "fechaPago" => $table->getFechaPago(),
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

    public function update(Automatizacion_Model_Table_ArchivosValidacionPagos $table) {
        try {
            $data = array(
                "patente" => $table->getPatente(),
                "aduana" => $table->getAduana(),
                "pedimento" => $table->getPedimento(),
                "rfcImportador" => $table->getRfcImportador(),
                "caja" => $table->getCaja(),
                "numOperacion" => $table->getNumOperacion(),
                "firmaBanco" => $table->getFirmaBanco(),
                "error" => $table->getError(),
                "fecha" => $table->getFecha(),
                "hora" => $table->getHora(),
                "fechaPago" => $table->getFechaPago(),
            );
            $this->getDbTable()->update($data, array("id = ?" => $table->getId()));
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function find(Automatizacion_Model_Table_ArchivosValidacionPagos $table) {
        try {
            $result = $this->getDbTable()->fetchRow(
                    $this->getDbTable()->select()
                            ->where("patente = ?", $table->getPatente())
                            ->where("aduana = ?", $table->getAduana())
                            ->where("pedimento = ?", $table->getPedimento())
                            ->where("rfcImportador = ?", $table->getRfcImportador())
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

    public function pagados($rfcImportador, $fechaIni, $fechaFin) {
        try {
            $select = $this->getDbTable()->select()
                    ->from(array("p" => "archivos_validacion_pagos"), array("patente", "aduana", "pedimento", "rfcImportador", "firmaBanco", "numOperacion"))
                    ->where("rfcImportador = ?", $rfcImportador)
                    ->where("fecha > ?", $fechaIni)
                    ->where("fecha < ?", $fechaFin);
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

    public function findFile($patente, $aduana, $pedimento) {
        try {
            $select = $this->getDbTable()->select()
                    ->setIntegrityCheck(false)
                    ->from(array("p" => "archivos_validacion_pagos"), array("patente", "aduana", "pedimento", "rfcImportador", "firmaBanco", "numOperacion", "idArchivoValidacion"))
                    ->joinLeft(array("a" => "archivos_validacion"), "p.idArchivoValidacion = a.id", array("archivo", "archivoNombre", "contenido"))
                    ->where("p.patente = ?", $patente)
                    ->where("p.aduana LIKE ?", substr($aduana, 0, 2) . "%")
                    ->where("p.pedimento = ?", $pedimento);
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

}
