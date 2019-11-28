<?php

class Archivo_Model_RepositorioContaMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Archivo_Model_DbTable_RepositorioConta();
    }
    
    public function buscarPoliza($numPoliza, $tipoPoliza) {
         try {
            $select = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("a" => "repositorio_conta"), array("id"))
                    ->where("a.poliza = ?", $numPoliza)
                    ->where("a.tipo_poliza = ?", $tipoPoliza);
            $result = $this->_db_table->fetchRow($select);
            if ($result) {
                return true;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function archivosDePoliza($poliza) {
        try {
            $select = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("a" => "repositorio_conta"), array("id", "tipo_archivo", "nom_archivo", "creado", "usuario","uuid", "ubicacion","cfdi_valido"))
                    ->joinLeft(array("d" => "documentos"), "d.id = a.tipo_archivo", array("d.nombre"))
                    ->where("a.poliza = ?", $poliza);
            $result = $this->_db_table->fetchAll($select);
            if ($result) {
                return $result->toArray();
            }
            return null;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function agregarArchivo($tipoArch, $poliza, $tipo, $ubi, $username, $uuid = null, $folio = null, $fecha = null, $emiRfc = null, $emiNom = null, $recRfc = null, $recNom = null, $version = null, $total = null) {
        try {
            $data = array(
                "tipo_archivo" => $tipoArch,
                "poliza" => $poliza,
                "tipo_poliza" => $tipo,
                "nom_archivo" => basename($ubi),
                "ubicacion" => $ubi,
                "uuid" => isset($uuid) ? $uuid : null,
                "folio" => isset($folio) ? $folio : null,
                "fecha" => isset($fecha) ? $fecha : null,
                "emisor_rfc" => isset($emiRfc) ? $emiRfc : null,
                "emisor_nombre" => isset($emiNom) ? $emiNom : null,
                "receptor_rfc" => isset($recRfc) ? $recRfc : null,
                "receptor_nombre" => isset($recNom) ? $recNom : null,
                "total" => isset($total) ? $total : null,
                "version" => isset($version) ? $version : null,
                "creado" => date("Y-m-d H:i:s"),
                "usuario" => $username,
            );
            $added = $this->_db_table->insert($data);
            if ($added) {
                return true;
            }
            return null;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function ultimasPolizas() {
        try {
            $select = $this->_db_table->select()
                    ->from($this->_db_table, array("poliza", "tipo_poliza", "creado", "rfc_cliente"))
                    ->group(array("poliza"))
                    ->order("creado DESC");
            $result = $this->_db_table->fetchAll($select);
            if ($result) {
                return $result->toArray();
            }
            return null;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function rutaArchivo($id) {
        try {
            $select = $this->_db_table->select()
                    ->from($this->_db_table, array("ubicacion"))
                    ->where("id = ?", $id);
            $result = $this->_db_table->fetchRow($select);
            if ($result) {
                return $result->toArray();
            }
            return null;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function obtenerCdfi($id) {
        try {
            $select = $this->_db_table->select()
                    ->from($this->_db_table, array("emisor_rfc","receptor_rfc","version","total","uuid"))
                    ->where("id = ?", $id);
            $result = $this->_db_table->fetchRow($select);
            if ($result) {
                return $result->toArray();
            }
            return null;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function actualizarFolio($id,$estatus) {
        try {
            $data = array(
                "cfdi_valido" => $estatus
            );
            $where = array(
                "id = ?" => $id,
            );
            $updated = $this->_db_table->update($data, $where);
            if($updated) {
                return true;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

}
