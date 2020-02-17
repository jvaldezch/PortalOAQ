<?php

class Administracion_Model_RepositorioContaMapper {

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
            $this->setDbTable("Administracion_Model_DbTable_RepositorioConta");
        }
        return $this->_dbTable;
    }

    public function find(Administracion_Model_Table_RepositorioConta $table) {
        try {
            $result = $this->getDbTable()->fetchRow(
                    $this->getDbTable()->select()
                            ->where("hash = ?", $table->getHash())
            );
            if (0 == count($result)) {
                return;
            }
            $table->setId($result->id);
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function buscarId(Administracion_Model_Table_RepositorioConta $table) {
        try {
            $result = $this->getDbTable()->fetchRow(
                    $this->getDbTable()->select()
                            ->where("id = ?", $table->getId())
            );
            if (0 == count($result)) {
                return;
            }
            $table->setId($result->id);            
            $table->setUbicacion($result->ubicacion);
            $table->setNombreArchivo($result->nombreArchivo);
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function archivoSolicitud(Administracion_Model_Table_RepositorioConta $table) {
        try {
            $result = $this->getDbTable()->fetchRow(
                    $this->getDbTable()->select()
                            ->where("idSolicitud = ?", $table->getIdSolicitud())
                            ->where("hash = ?", $table->getHash())
            );
            if (0 == count($result)) {
                return;
            }
            $table->setId($result->id);
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function buscar($id) {
        try {
            $result = $this->getDbTable()->fetchRow(
                    $this->getDbTable()->select()
                            ->where("id = ?", $id)
            );
            if (0 == count($result)) {
                return;
            }
            return $result->toArray();
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function getAll($id) {
        try {
            $result = $this->getDbTable()->fetchAll(
                    $this->getDbTable()->select()
                            ->where("id = ?", $id)
            );
            if (0 == count($result)) {
                return;
            }
            return $result->toArray();
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function archivosSolicitud($idSolicitud) {
        try {
            $result = $this->getDbTable()->fetchAll(
                    $this->getDbTable()->select()
                            ->where("idSolicitud = ?", $idSolicitud)
            );
            if (0 == count($result)) {
                return;
            }
            return $result->toArray();
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function save(Administracion_Model_Table_RepositorioConta $table) {
        try {
            $data = array(
                "id" => $table->getId(),
                "idSolicitud" => $table->getIdSolicitud(),
                "rfcCliente" => $table->getRfcCliente(),
                "nombreCliente" => $table->getNombreCliente(),
                "tipoPoliza" => $table->getTipoPoliza(),
                "tipoArchivo" => $table->getTipoArchivo(),
                "poliza" => $table->getPoliza(),
                "folio" => $table->getFolio(),
                "fecha" => $table->getFecha(),
                "factura" => $table->getFactura(),
                "transferencia" => $table->getTransferencia(),
                "referencia" => $table->getReferencia(),
                "patente" => $table->getPatente(),
                "aduana" => $table->getAduana(),
                "pedimento" => str_pad($table->getPedimento(), 7, '0', STR_PAD_LEFT),
                "hash" => $table->getHash(),
                "uuid" => $table->getUuid(),
                "rfcEmisor" => $table->getRfcEmisor(),
                "nombreEmisor" => $table->getNombreEmisor(),
                "rfcReceptor" => $table->getRfcReceptor(),
                "nombreReceptor" => $table->getNombreReceptor(),
                "total" => $table->getTotal(),
                "nombreArchivo" => $table->getNombreArchivo(),
                "ubicacion" => $table->getUbicacion(),
                "observaciones" => $table->getObservaciones(),
                "cfdiValido" => $table->getCfdiValido(),
                "version" => $table->getVersion(),
                "borrado" => $table->getBorrado(),
                "usuario" => $table->getUsuario(),
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
        }
    }

    public function fetchAll() {
        try {
            $result = $this->getDbTable()->fetchAll(
                    $this->getDbTable()->select()
            );
            if (0 == count($result)) {
                return;
            }
            return $result->toArray();
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function agregar($arr) {
        try {
            $stmt = $this->getDbTable()->insert($arr);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function buscarArchivo($nombreArchivo, $hash) {
        try {
            $sql = $this->getDbTable()->select()
                            ->where("nombreArchivo = ?", $nombreArchivo)
                            ->where("hash = ?", $hash);
            $stmt = $this->getDbTable()->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function borrar($id) {
        try { 
            $stmt = $this->getDbTable()->delete(array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

}
