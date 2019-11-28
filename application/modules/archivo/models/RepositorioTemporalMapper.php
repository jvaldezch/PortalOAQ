<?php

class Archivo_Model_RepositorioTemporalMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Archivo_Model_DbTable_RepositorioTemporal();
    }

    public function fetchAll() {
        try {
            $result = $this->_db_table->fetchAll(
                    $this->_db_table->select()
            );
            if ($result) {
                return $result->toArray();
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function save(Archivo_Model_RepositorioTemporal $table) {
        try {
            $data = array(
                "idTrafico" => $table->getIdTrafico(),
                "idMensaje" => $table->getIdMensaje(),
                "idComentario" => $table->getIdComentario(),
                "patente" => $table->getPatente(),
                "aduana" => $table->getAduana(),
                "pedimento" => $table->getPedimento(),
                "referencia" => $table->getReferencia(),
                "rfcCliente" => $table->getRfcCliente(),
                "tipoArchivo" => $table->getTipoArchivo(),
                "subTipoArchivo" => $table->getSubTipoArchivo(),
                "nombreArchivo" => $table->getNombreArchivo(),
                "archivo" => $table->getArchivo(),
                "ubicacion" => $table->getUbicacion(),
                "usuario" => $table->getUsuario(),
                "creado" => date("Y-m-d H:i:s"),
            );
            if (null === ($id = $table->getId())) {
                unset($data["id"]);
                $id = $this->_db_table->insert($data);
                $table->setId($id);
            } else {
                $this->_db_table->update($data, array("id = ?" => $id));
            }
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function find(Archivo_Model_RepositorioTemporal $table) {
        try {
            $result = $this->_db_table->fetchRow(
                    $this->_db_table->select()
                            ->where("patente = ?", $table->getPatente())
                            ->where("aduana = ?", $table->getAduana())
                            ->where("referencia = ?", $table->getReferencia())
                            ->where("archivo = ?", $table->getArchivo())
            );
            if ($result) {
                return true;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function findId(Archivo_Model_RepositorioTemporal $table) {
        try {
            $result = $this->_db_table->fetchRow(
                    $this->_db_table->select()
                            ->where("id = ?", $table->getId())
            );
            if ($result) {
                return true;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function getFile(Archivo_Model_RepositorioTemporal $table) {
        try {
            $result = $this->_db_table->fetchRow(
                    $this->_db_table->select()
                            ->where("id = ?", $table->getId())
            );
            if ($result) {
                $table->setIdTrafico($result->idTrafico);
                $table->setIdMensaje($result->idMensaje);
                $table->setIdComentario($result->idComentario);
                $table->setPatente($result->patente);
                $table->setAduana($result->aduana);
                $table->setPedimento($result->pedimento);
                $table->setTipoArchivo($result->tipoArchivo);
                $table->setSubTipoArchivo($result->subTipoArchivo);
                $table->setReferencia($result->referencia);
                $table->setRfcCliente($result->rfcCliente);
                $table->setNombreArchivo($result->nombreArchivo);
                $table->setArchivo($result->archivo);
                $table->setUbicacion($result->ubicacion);
                $table->setCreado($result->creado);
                $table->setUsuario($result->usuario);
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function get($id) {
        try {
            $result = $this->_db_table->fetchRow(
                    $this->_db_table->select()
                            ->where("id = ?", $id)
            );
            if ($result) {
                return $result->toArray();
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function delete($id) {
        try {
            $stmt = $this->_db_table->delete(array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function archivosTrafico($idTrafico) {
        try {
            $sql = $this->_db_table->select()
                    ->from("repositorio_temporal", array("*"))
                    ->where("idTrafico = ?", $idTrafico)
                    ->where("idMensaje IS NULL AND idComentario IS NULL");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function withNoInformation($patente) {
        try {
            $result = $this->_db_table->fetchAll(
                    $this->_db_table->select()
                            ->where("patente = ?", $patente)
            );
            if ($result) {
                return $result->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

}
