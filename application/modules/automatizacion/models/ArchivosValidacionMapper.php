<?php

class Automatizacion_Model_ArchivosValidacionMapper {

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
            $this->setDbTable("Automatizacion_Model_DbTable_ArchivosValidacion");
        }
        return $this->_dbTable;
    }

    public function save(Automatizacion_Model_Table_ArchivosValidacion $table) {
        try {
            $data = array(
                "id" => $table->getId(),
                "patente" => $table->getPatente(),
                "aduana" => $table->getAduana(),
                "archivo" => $table->getArchivo(),
                "archivoNombre" => $table->getArchivoNombre(),
                "diaJuliano" => $table->getDiaJuliano(),
                "archivoNum" => $table->getArchivoNum(),
                "tipo" => $table->getTipo(),
                "hash" => $table->getHash(),
                "contenido" => $table->getContenido(),
                "usuario" => $table->getUsuario(),
                "analizado" => $table->getAnalizado(),
                "error" => $table->getError(),
                "creado" => $table->getCreado(),
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

    public function find(Automatizacion_Model_Table_ArchivosValidacion $table) {
        try {
            $stmt = $this->getDbTable()->fetchRow(
                    $this->getDbTable()->select()
                            ->where("patente = ?", $table->getPatente())
                            ->where("aduana = ?", $table->getAduana())
                            ->where("archivoNombre = ?", $table->getArchivoNombre())
                            ->where("hash = ?", $table->getHash())
            );
            if (0 == count($stmt)) {
                return;
            }
            $table->setId($stmt->id);
            $table->setPatente($stmt->patente);
            $table->setAduana($stmt->aduana);
            $table->setArchivo($stmt->archivo);
            $table->setArchivoNombre($stmt->archivoNombre);
            $table->setDiaJuliano($stmt->diaJuliano);
            $table->setArchivoNum($stmt->archivoNum);
            $table->setTipo($stmt->tipo);
            $table->setHash($stmt->hash);
            $table->setContenido($stmt->contenido);
            $table->setUsuario($stmt->usuario);
            $table->setAnalizado($stmt->analizado);
            $table->setError($stmt->error);
            $table->setCreado($stmt->creado);
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function notAnalized($tipo) {
        try {
            $result = $this->getDbTable()->fetchAll(
                    $this->getDbTable()->select()
                            ->where("tipo = ?", $tipo)
                            ->where("analizado = 0")
                            ->where("contenido IS NOT NULL AND contenido <> ''")
            );
            if (0 == count($result)) {
                return;
            }
            return $result->toArray();
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function findFile($archivoNombre, $aduana = null, $year = null) {
        try {
            $sql = $this->getDbTable()->select()
                    ->where("archivoNombre = ?", $archivoNombre)
                    ->where("analizado = 1");
            if (isset($aduana)) {
                $sql->where("aduana = ?", $aduana);
            }
            if (isset($year)) {
                $sql->where("YEAR(creado) = ?", $year);
            }
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

    public function fileContent($idArchivo) {
        try {
            $select = $this->getDbTable()->select()
                    ->where("id = ?", $idArchivo);
            $result = $this->getDbTable()->fetchRow($select);
            if (0 == count($result)) {
                return;
            }
            return $result->toArray();
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function filesContent($arr) {
        try {
            $select = $this->getDbTable()->select()
                    ->where("id IN (?)", $arr);
            $result = $this->getDbTable()->fetchAll($select);
            if (0 == count($result)) {
                return;
            }
            return $result->toArray();
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function setAnalized($id) {
        try {
            $this->getDbTable()->update(array("analizado" => 1), array("id = ?" => $id));
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    /**
     * 
     * @param string $tipo
     * @param string $fecha
     * @param int $patente
     * @param int $aduana
     * @return type
     * @throws Exception
     */
    public function archivosValidacion($tipo, $fecha, $patente = null, $aduana = null) {
        try {
            $select = $this->getDbTable()->select()
                    ->from($this->getDbTable(), array("id", "patente", "aduana", "archivoNombre", "creado"))
                    ->where("tipo = ?", $tipo)
                    ->where("creado LIKE ?", $fecha . "%")
                    ->where("contenido IS NOT NULL AND contenido <> ''")
                    ->order("archivoNombre ASC");
            if (isset($patente) && isset($aduana)) {
                $select->where("(patente = {$patente} AND aduana = {$aduana}) ");
            }
            $result = $this->getDbTable()->fetchAll($select);
            if (0 == count($result)) {
                return;
            }
            $data = array();
            foreach ($result->toArray() as $item) {
                $data[] = $this->_complemento($item, $fecha);
            }
            return $data;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    protected function _complemento($item, $fecha) {
        try {
            $mapper = new Automatizacion_Model_ArchivosValidacionPedimentosMapper();
            $map = new Automatizacion_Model_ArchivosValidacionPagosMapper();

            $item["firma"] = $this->_buscar($item["patente"], $item["aduana"], "validacion", $fecha, substr($item["archivoNombre"], 1, 7));
            $item["validacion"] = $this->_buscar($item["patente"], $item["aduana"], "resultado", $fecha, substr($item["archivoNombre"], 1, 7));
            $item["pedimentos"] = $mapper->pedimentos($item["id"]);
            if (isset($item["pedimentos"][0])) {
                $item["pagos"] = $map->findFile($item["patente"], $item["aduana"], $item["pedimentos"][0]["pedimento"]);
            }
            return $item;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    protected function _buscar($patente, $aduana, $tipo, $fecha, $archivo) {
        try {
            $result = $this->getDbTable()->fetchRow(
                    $this->getDbTable()->select()
                            ->from($this->getDbTable(), array("id", "patente", "aduana", "archivoNombre"))
                            ->where("patente = ?", $patente)
                            ->where("aduana = ?", $aduana)
                            ->where("creado > ?", $fecha)
                            ->where("archivoNombre LIKE ?", "%" . $archivo . "%")
                            ->where("tipo = ?", $tipo)
            );
            if (0 == count($result)) {
                return;
            }
            return $result->toArray();
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function obtenerPorFecha($patente = null, $aduana = null, $fecha = null) {
        try {
            if (!isset($fecha)) {
                $fecha = date("Y-m-d");
            }
            $sql = $this->getDbTable()->select()
                    ->from(array("a" => "archivos_validacion"), array("id", "patente", "aduana", "usuario", "creado", "archivoNombre"))
                    ->where("a.creado LIKE ?", $fecha . "%");
            if (isset($patente)) {
                $sql->where("a.patente = ?", $patente);
            }
            if (isset($aduana)) {
                
            }
            $stmt = $this->getDbTable()->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
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
     * @param int $pedimento
     * @return type
     * @throws Exception
     */
    public function buscarPedimento($pedimento) {
        try {
            $this->_db = Zend_Registry::get("oaqintranet");
            $sql = "SELECT m.idArchivoValidacion AS id, r.patente, r.aduana, r.usuario, r.creado, r.archivoNombre FROM (
(SELECT p.idArchivoValidacion FROM archivos_validacion_pedimentos AS p WHERE p.pedimento = '$pedimento')
UNION
(SELECT a.idArchivoValidacion FROM archivos_validacion_pagos AS a WHERE a.pedimento = '$pedimento')
UNION
(SELECT f.idArchivoValidacion FROM archivos_validacion_firmas AS f WHERE f.pedimento = '$pedimento')) AS m
LEFT JOIN archivos_validacion AS r ON m.idArchivoValidacion = r.id";
            $stmt = $this->_db->query($sql);
            if ($stmt) {
                return $stmt;
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
     * @param string $nombreArchivo
     * @return type
     * @throws Exception
     */
    public function buscarArchivo($nombreArchivo) {
        try {
            $sql = $this->getDbTable()->select()
                    ->from(array("a" => "archivos_validacion"), array("id", "patente", "aduana", "usuario", "creado", "archivoNombre"))
                    ->where("a.archivoNombre LIKE ?", $nombreArchivo . "%");            
            $stmt = $this->getDbTable()->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
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
     * @return array|null
     * @throws Exception
     */
    public function pedimentosNoCerrados($fecha) {
        try {
            $this->_db = Zend_Registry::get("oaqintranet");
            $sql = "SELECT
  m.idArchivoValidacion AS id,
	r.patente,
	r.aduana,
	r.usuario,
	r.creado,
	r.archivoNombre
FROM (SELECT
	p.idArchivoValidacion
FROM
	archivos_validacion_pedimentos AS p
LEFT JOIN archivos_validacion_firmas AS f ON f.pedimento = p.pedimento
LEFT JOIN archivos_validacion_pagos AS a ON a.pedimento = p.pedimento
WHERE
	cveDoc = 'V1'
AND YEAR (p.creado) = 2017 AND a.firmaBanco IS NULL) AS m
LEFT JOIN archivos_validacion AS r ON m.idArchivoValidacion = r.id WHERE creado >= '{$fecha};'";
            $stmt = $this->_db->query($sql);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getFile($id) {
        try {
            $sql = $this->getDbTable()->select()
                    ->from($this->getDbTable(), array("*"))
                    ->where("id = ?", $id);
            $stmt = $this->getDbTable()->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getEFile($archivoNombre) {
        try {
            $sql = $this->getDbTable()->select()
                    ->from($this->getDbTable(), array("*"))
                    ->where("archivoNombre = ?", $archivoNombre);
            $stmt = $this->getDbTable()->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
