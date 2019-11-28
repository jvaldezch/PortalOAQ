<?php

class Archivo_Model_Repositorio {

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
            $this->setDbTable("Archivo_Model_DbTable_Repositorio");
        }
        return $this->_dbTable;
    }

    public function agregar($arr) {
        try {
            $stmt = $this->getDbTable()->insert($arr);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function get(Archivo_Model_Table_Repositorio $tbl) {
        try {
            $stmt = $this->getDbTable()->fetchRow(
                    $this->getDbTable()->select()
                            ->where("id = ?", $tbl->getId())
            );
            if (0 == count($stmt)) {
                return;
            }
            $this->_getData($tbl, $stmt);
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function getPdf($basename) {
        try {
            $stmt = $this->getDbTable()->fetchRow(
                    $this->getDbTable()->select()
                            ->where("nom_archivo REGEXP '{$basename}.pdf$'")
            );
            if (0 == count($stmt)) {
                return;
            }
            return $stmt->toArray();
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function find(Archivo_Model_Table_Repositorio $tbl) {
        try {
            $stmt = $this->getDbTable()->fetchRow(
                    $this->getDbTable()->select()
                            ->where("emisor_rfc = ?", $tbl->getEmisor_rfc())
                            ->where("folio = ?", $tbl->getFolio())
            );
            if (0 == count($stmt)) {
                return;
            }
            $this->_getData($tbl, $stmt);
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function findEdocument(Archivo_Model_Table_Repositorio $tbl) {
        try {
            $stmt = $this->getDbTable()->fetchRow(
                    $this->getDbTable()->select()
                            ->where("referencia = ?", $tbl->getReferencia())
                            ->where("nom_archivo = ?", $tbl->getNom_archivo())
                            ->where("edocument = ?", $tbl->getEdocument())
            );
            if (0 == count($stmt)) {
                return;
            }
            $this->_getData($tbl, $stmt);
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function findFile(Archivo_Model_Table_Repositorio $tbl) {
        try {
            $stmt = $this->getDbTable()->fetchRow(
                    $this->getDbTable()->select()
                            ->where("referencia = ?", $tbl->getReferencia())
                            ->where("nom_archivo = ?", $tbl->getNom_archivo())
            );
            if (0 == count($stmt)) {
                return;
            }
            $this->_getData($tbl, $stmt);
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    protected function _getData(Archivo_Model_Table_Repositorio $tbl, $stmt) {
        $tbl->setId($stmt->id);
        $tbl->setRfc_cliente($stmt->rfc_cliente);
        $tbl->setTipo_archivo($stmt->tipo_archivo);
        $tbl->setSub_tipo_archivo($stmt->sub_tipo_archivo);
        $tbl->setReferencia($stmt->referencia);
        $tbl->setPatente($stmt->patente);
        $tbl->setAduana($stmt->aduana);
        $tbl->setPedimento($stmt->pedimento);
        $tbl->setUuid($stmt->uuid);
        $tbl->setFolio($stmt->folio);
        $tbl->setFecha($stmt->fecha);
        $tbl->setEmisor_rfc($stmt->emisor_rfc);
        $tbl->setEmisor_nombre($stmt->emisor_nombre);
        $tbl->setReceptor_rfc($stmt->receptor_rfc);
        $tbl->setReceptor_nombre($stmt->receptor_nombre);
        $tbl->setNom_archivo($stmt->nom_archivo);
        $tbl->setUbicacion($stmt->ubicacion);
        $tbl->setUbicacion_xml($stmt->ubicacion_xml);
        $tbl->setUbicacion_pdf($stmt->ubicacion_pdf);
        $tbl->setEdocument($stmt->edocument);
        $tbl->setObservaciones($stmt->observaciones);
        $tbl->setEmail($stmt->email);
        $tbl->setCofidi($stmt->cofidi);
        $tbl->setFtp($stmt->ftp);
        $tbl->setBorrado($stmt->borrado);
        $tbl->setCreado($stmt->creado);
        $tbl->setUsuario($stmt->usuario);
        $tbl->setModificado($stmt->modificado);
        $tbl->setModificadoPor($stmt->modificadoPor);
    }

    public function findUuid(Archivo_Model_Table_Repositorio $tbl) {
        try {
            $stmt = $this->getDbTable()->fetchRow(
                    $this->getDbTable()->select()
                            ->where("uuid = ?", $tbl->getUuid())
                            ->where("nom_archivo REGEXP '.xml$'")
            );
            if (0 == count($stmt)) {
                return;
            }
            $this->_getData($tbl, $stmt);
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function save(Archivo_Model_Table_Repositorio $tbl) {
        try {
            $data = array(
                "id" => $tbl->getId(),
                "rfc_cliente" => $tbl->getRfc_cliente(),
                "tipo_archivo" => $tbl->getTipo_archivo(),
                "sub_tipo_archivo" => $tbl->getSub_tipo_archivo(),
                "referencia" => $tbl->getReferencia(),
                "patente" => $tbl->getPatente(),
                "aduana" => $tbl->getAduana(),
                "pedimento" => $tbl->getPedimento(),
                "uuid" => $tbl->getUuid(),
                "folio" => $tbl->getFolio(),
                "fecha" => $tbl->getFecha(),
                "emisor_rfc" => $tbl->getEmisor_rfc(),
                "emisor_nombre" => $tbl->getEmisor_nombre(),
                "receptor_rfc" => $tbl->getReceptor_rfc(),
                "receptor_nombre" => $tbl->getReceptor_nombre(),
                "nom_archivo" => $tbl->getNom_archivo(),
                "ubicacion" => $tbl->getUbicacion(),
                "ubicacion_xml" => $tbl->getUbicacion_xml(),
                "ubicacion_pdf" => $tbl->getUbicacion_pdf(),
                "edocument" => $tbl->getEdocument(),
                "observaciones" => $tbl->getObservaciones(),
                "email" => $tbl->getEmail(),
                "cofidi" => $tbl->getCofidi(),
                "ftp" => $tbl->getFtp(),
                "borrado" => $tbl->getBorrado(),
                "creado" => $tbl->getCreado(),
                "usuario" => $tbl->getUsuario(),
                "modificado" => $tbl->getModificado(),
                "modificadoPor" => $tbl->getModificadoPor(),
            );
            if (null === ($id = $tbl->getId())) {
                unset($data["id"]);
                $id = $this->getDbTable()->insert($data);
                $tbl->setId($id);
            } else {
                $this->getDbTable()->update($data, array("id = ?" => $id));
            }
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function facturasTerminalSinRfc($fecha) {
        try {
            $stmt = $this->getDbTable()->fetchAll(
                    $this->getDbTable()->select()
                            ->distinct()
                            ->from("repositorio", array("uuid"))
                            ->where("emisor_rfc = 'TLO050804QY7'")
                            ->where("rfc_cliente IS NULL")
                            ->where("creado LIKE ?", $fecha . "%")
                            ->order("creado DESC")
                            ->limit(50)
            );
            if (0 == count($stmt)) {
                return;
            }
            return $stmt->toArray();
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function facturasTerminalSinDatos($fecha) {
        try {
            $stmt = $this->getDbTable()->fetchAll(
                    $this->getDbTable()->select()
                            ->from("repositorio", array("id", "nom_archivo", "ubicacion"))
                            ->where("emisor_rfc IS NULL")
                            ->where("receptor_rfc IS NULL")
                            ->where("nom_archivo REGEXP '^TLO050804QY7'")
                            ->where("nom_archivo REGEXP '.xml$'")
                            ->where("creado LIKE ?", $fecha . "%")
                            ->order("creado DESC")
                            ->limit(50)
            );
            if (0 == count($stmt)) {
                return;
            }
            return $stmt->toArray();
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function update($uuid, $data) {
        try {
            $this->getDbTable()->update($data, array("uuid = ?" => $uuid));
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function borrarExpediente($arr) {
        try {
            if (is_array($arr) && !empty($arr)) {
                $tbl = new Archivo_Model_DbTable_Repositorio();
                $stmt = $tbl->delete(array("id IN (?)", $arr));
                if ($stmt) {
                    return true;
                }
                return;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function borrarVacio($patente, $aduana, $referencia) {
        try {
            $tbl = new Archivo_Model_DbTable_Repositorio();
            $stmt = $tbl->delete(array(
                "patente = ?" => $patente,
                "aduana = ?" => $aduana,
                "referencia = ?" => $referencia,
                "tipo_archivo = ?" => 9999
            ));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function buscarRepositorio($patente, $aduana, $referencia) {
        try {
            $stmt = $this->getDbTable()->fetchAll(
                    $this->getDbTable()->select()
                            ->from("repositorio", array("pedimento", "rfc_cliente AS rfcCliente"))
                            ->where("patente = ?", $patente)
                            ->where("aduana = ?", $aduana)
                            ->where("referencia = ?", $referencia)
                            ->where("tipo_archivo = 9999")
            );
            if (0 == count($stmt)) {
                return;
            }
            return $stmt->toArray();
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function buscarArchivo($patente, $aduana, $pedimento, $referencia, $nombreArchivo) {
        try {
            $stmt = $this->getDbTable()->fetchRow(
                    $this->getDbTable()->select()
                            ->from("repositorio", array("id"))
                            ->where("patente = ?", $patente)
                            ->where("aduana = ?", $aduana)
                            ->where("pedimento = ?", $pedimento)
                            ->where("referencia = ?", $referencia)
                            ->where("nom_archivo = ?", $nombreArchivo)
            );
            if (0 == count($stmt)) {
                return;
            }
            return $stmt->id;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function buscarMal() {
        try {
            $stmt = $this->getDbTable()->fetchAll(
                    $this->getDbTable()->select()
                            ->from("repositorio", array("id", "patente", "aduana", "referencia", "rfc_cliente", "pedimento", "creado"))
                            ->where("pedimento = 0")
                            ->where("YEAR(creado) = 2017")
                            ->where("tipo_archivo = 9999")
            );
            if (0 == count($stmt)) {
                return;
            }
            return $stmt->toArray();
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function actualizarRepositorio($id, $pedimento, $rfcCliente) {
        try {
            $arr = array(
                "pedimento" => $pedimento,
                "rfc_cliente" => $rfcCliente
            );
            $this->getDbTable()->update($arr, array("id = ?" => $id));
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function archivos($referencia, $patente = null) {
        try {
            $sql = $this->getDbTable()->select()
                    ->from("repositorio", array("id", "tipo_archivo", "sub_tipo_archivo"))
                    ->where("referencia = ?", $referencia);
            if (isset($patente)) {
                $sql->where("patente = ?", $patente);
            }
            $stmt = $this->getDbTable()->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function obtenerPorArregloId($arr) {
        try {
            $sql = $this->getDbTable()->select()
                    ->from("repositorio", array("nom_archivo", "ubicacion", "tipo_archivo"))
                    ->where("id IN (?)", $arr);
            $stmt = $this->getDbTable()->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

}
