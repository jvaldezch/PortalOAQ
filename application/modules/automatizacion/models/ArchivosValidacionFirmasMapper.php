<?php

class Automatizacion_Model_ArchivosValidacionFirmasMapper {

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
            $this->setDbTable("Automatizacion_Model_DbTable_ArchivosValidacionFirmas");
        }
        return $this->_dbTable;
    }

    public function save(Automatizacion_Model_Table_ArchivosValidacionFirmas $table) {
        try {
            $data = array(
                "id" => $table->getId(),
                "idArchivoValidacion" => $table->getIdArchivoValidacion(),
                "patente" => $table->getPatente(),
                "pedimento" => $table->getPedimento(),
                "firma" => $table->getFirma(),
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
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function find(Automatizacion_Model_Table_ArchivosValidacionFirmas $table) {
        try {
            $stmt = $this->getDbTable()->fetchRow(
                    $this->getDbTable()->select()
                            ->where("patente = ?", $table->getPatente())
                            ->where("pedimento = ?", $table->getPedimento())
                            ->where("firma = ?", $table->getFirma())
            );
            if (0 == count($stmt)) {
                return;
            }
            $table->setId($stmt->id);
            $table->setIdArchivoValidacion($stmt->idArchivoValidacion);
            $table->setPatente($stmt->patente);
            $table->setPedimento($stmt->pedimento);
            $table->setFirma($stmt->firma);
            $table->setCreado($stmt->creado);
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function ultimaFirma($patente, $pedimento) {
        try {
            $sql = $this->getDbTable()->select()
                    ->from(array("f" => "archivos_validacion_firmas"), array("firma"))
                    ->where("patente = ?", $patente)
                    ->where("pedimento = ?", $pedimento)
                    ->order("creado DESC");
            $stmt = $this->getDbTable()->fetchRow($sql);
            if (0 == count($stmt)) {
                return;
            }
            return $stmt->toArray();
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function obtenerTodos($patente, $pedimento) {
        try {
            $sql = $this->getDbTable()->select()
                    ->setIntegrityCheck(false)
                    ->from(array("f" => "archivos_validacion_firmas"), array("firma", "idArchivoValidacion"))
                    ->joinLeft(array("a" => "archivos_validacion"), "f.idArchivoValidacion = a.id", array("archivo", "archivoNombre"))
                    ->where("f.patente = ?", $patente)
                    ->where("f.pedimento = ?", $pedimento)
                    ->order("f.creado DESC");
            $stmt = $this->getDbTable()->fetchAll($sql);
            if (0 == count($stmt)) {
                return;
            }
            return $stmt->toArray();
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function obtenerUltimo($patente, $pedimento) {
        try {
            $sql = $this->getDbTable()->select()
                    ->setIntegrityCheck(false)
                    ->from(array("f" => "archivos_validacion_firmas"), array("firma", "idArchivoValidacion"))
                    ->joinLeft(array("a" => "archivos_validacion"), "f.idArchivoValidacion = a.id", array("archivo", "archivoNombre"))
                    ->where("f.patente = ?", $patente)
                    ->where("f.pedimento = ?", $pedimento)
                    ->order("f.creado DESC");
            $stmt = $this->getDbTable()->fetchRow($sql);
            if (0 == count($stmt)) {
                return;
            }
            return $stmt->toArray();
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function findFile($patente, $aduana, $pedimento, $archivoNombre) {
        try {
            $sql = $this->getDbTable()->select()
                    ->setIntegrityCheck(false)
                    ->from(array("p" => "archivos_validacion_firmas"), array("patente", "pedimento", "firma"))
                    ->joinLeft(array("a" => "archivos_validacion"), "p.idArchivoValidacion = a.id", array("archivo", "archivoNombre", "contenido"))
                    ->where("p.patente = ?", $patente)
                    ->where("p.pedimento = ?", $pedimento)
                    ->where("a.aduana LIKE ?", substr($aduana, 0, 2) . "%")
                    ->order("a.creado DESC");
            if (isset($archivoNombre)) {
                $sql->where("a.archivoNombre LIKE ?", "%" . substr(preg_replace("/\D/", "", $archivoNombre), 0, 7) . "%");
            }
            $stmt = $this->getDbTable()->fetchRow($sql);
            if (0 != count($stmt)) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
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
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

}
