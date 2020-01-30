<?php

class Archivo_Model_RepositorioMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Archivo_Model_DbTable_Repositorio();
    }

    /**
     * 
     * @param string $reference
     * @param int $patente
     * @param int $aduana
     * @return type
     * @throws Exception
     */
    public function getFilesByReferenceCustomers($reference, $patente = null, $aduana = null, $file_type = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("a" => "repositorio"), array("id", "tipo_archivo", "sub_tipo_archivo", "folio", "emisor_nombre", "receptor_nombre", "nom_archivo", "creado", "usuario", "ubicacion_pdf", "ubicacion"))
                    ->joinLeft(array("d" => "documentos"), "d.id = a.tipo_archivo", array("d.nombre"))
                    ->where("a.referencia = ?", $reference)
                    ->order("d.orden ASC");
            if (!$file_type) {
                $sql->where("a.tipo_archivo NOT IN (29, 31, 58, 89, 2001, 9999)");
            } else {
                $sql->where("a.tipo_archivo IN (?)", $file_type);
            }
            if (isset($patente)) {
                $sql->where("a.patente = ?", $patente);
            }
            if (isset($aduana)) {
                $sql->where("a.aduana LIKE ?", substr($aduana, 0, 2) . "%");
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    public function obtener($reference, $patente = null, $aduana = null, $array = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("a" => "repositorio"), array("id", "tipo_archivo", "sub_tipo_archivo", "nom_archivo", "creado", "usuario", "ubicacion_pdf", "ubicacion"))
                    ->joinLeft(array("d" => "documentos"), "d.id = a.tipo_archivo", array("d.nombre"))
                    ->where("a.referencia = ?", $reference)
                    ->where("a.tipo_archivo IN (?)", $array)
                    ->where("a.tipo_archivo NOT IN (29, 31, 58, 89, 9999)")
                    ->order("d.orden ASC");
            if (isset($patente)) {
                $sql->where("a.patente = ?", $patente);
            }
            if (isset($aduana)) {
                $sql->where("a.aduana LIKE ?", substr($aduana, 0, 2) . "%");
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    public function countFilesByReferenceCustomers($reference, $patente = null, $aduana = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("a" => "repositorio"), array("count(*) AS total"))
                    ->joinLeft(array("d" => "documentos"), "d.id = a.tipo_archivo", array("d.nombre"))
                    ->where("a.referencia = ?", $reference)
                    ->where("a.tipo_archivo NOT IN (29, 31, 89, 2001, 9999)")
                    ->order("nom_archivo ASC");
            if (isset($patente)) {
                $sql->where("a.patente = ?", $patente);
            }
            if (isset($aduana)) {
                $sql->where("a.aduana LIKE ?", substr($aduana, 0, 2) . "%");
            }
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->total;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    /**
     * 
     * @param string $reference
     * @param int $patente
     * @param int $aduana
     * @return type
     * @throws Exception
     */
    public function getFilesByReferenceUsers($reference, $patente = null, $aduana = null, $filetype = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("a" => "repositorio"), array("id", "tipo_archivo", "sub_tipo_archivo", "nom_archivo", "edocument", "creado", "usuario", "ubicacion_pdf", "ubicacion"))
                    ->joinLeft(array("d" => "documentos"), "d.id = a.tipo_archivo", array("d.nombre"))
                    ->where("a.referencia = ?", $reference)
                    ->where("a.tipo_archivo NOT IN (9999)")
                    ->order("d.orden ASC");
            if (isset($patente)) {
                $sql->where("a.patente = ?", $patente);
            }
            if (isset($aduana)) {
                $sql->where("a.aduana LIKE ?", substr($aduana, 0, 2) . "%");
            }
            if (isset($filetype) && !empty($filetype)) {
                $sql->where("a.tipo_archivo IN (?)", $filetype);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    /**
     * 
     * @param string $referencia
     * @param int $patente
     * @param int $aduana
     * @param array $tiposArchivos
     * @return type
     * @throws Exception
     */
    public function obtenerTiposDeArchivos($referencia, $patente = null, $aduana = null, $tiposArchivos = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("a" => "repositorio"), array("id", "tipo_archivo", "sub_tipo_archivo", "nom_archivo", "creado", "usuario", "ubicacion_pdf", "ubicacion"))
                    ->joinLeft(array("d" => "documentos"), "d.id = a.tipo_archivo", array("d.nombre"))
                    ->where("a.referencia = ?", $referencia)
                    ->where("a.tipo_archivo NOT IN (9999)")
                    ->where("a.tipo_archivo IN (?)", $tiposArchivos)
                    ->order("nom_archivo ASC");
            if (isset($patente)) {
                $sql->where("a.patente = ?", $patente);
            }
            if (isset($aduana)) {
                $sql->where("a.aduana LIKE ?", substr($aduana, 0, 2) . "%");
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    public function countFilesByReferenceUsers($reference, $patente = null, $aduana = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("a" => "repositorio"), array("count(*) AS total"))
                    ->joinLeft(array("d" => "documentos"), "d.id = a.tipo_archivo", array("d.nombre"))
                    ->where("a.referencia = ?", $reference)
                    ->where("a.tipo_archivo NOT IN (9999)")
                    ->order("nom_archivo ASC");
            if (isset($patente)) {
                $sql->where("a.patente = ?", $patente);
            }
            if (isset($aduana)) {
                $sql->where("a.aduana LIKE ?", substr($aduana, 0, 2) . "%");
            }
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->total;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    /**
     * 
     * @param array $array
     * @return type
     * @throws Exception
     */
    public function getFilesEdocuments($array) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("a" => "repositorio"), array("id", "ubicacion"))
                    ->where("a.edocument IN (?)", $array)
                    ->where("a.tipo_archivo = 27")
                    ->order("nom_archivo ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    /**
     * 
     * @param string $string
     * @return type
     * @throws Exception
     */
    public function getFilesCoves($string) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("a" => "repositorio"), array("id", "ubicacion"))
                    ->where("a.nom_archivo REGEXP '{$string}'")
                    ->where("a.tipo_archivo IN (21, 22)")
                    ->order("nom_archivo ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function actualizarPedimento($refrencia, $pedimento, $rfcCliente) {
        try {
            $data = array(
                "pedimento" => $pedimento,
                "rfc_cliente" => $rfcCliente,
            );
            $where = array(
                "referencia = ?" => $refrencia,
            );
            $updated = $this->_db_table->update($data, $where);
            if ($updated) {
                return true;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function sinPedimentoORfcCliente($patente, $referencia) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("*"))
                    ->where("patente = ?", $patente)
                    ->where("referencia = ?", $referencia)
                    ->where("rfc_cliente IS NULL OR pedimento IS NULL");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function getInfo($patente, $referencia) {
        try {
            if (isset($patente) && isset($referencia)) {
                $sql = $this->_db_table->select()
                        ->from($this->_db_table, array("pedimento", "rfc_cliente"))
                        ->where("patente = ?", $patente)
                        ->where("referencia = ?", $referencia);
                $stmt = $this->_db_table->fetchRow($sql);
                if ($stmt) {
                    return $stmt->toArray();
                }
                return false;
            } else {
                return;
            }
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    public function obtenerInformacionReferencia($patente, $referencia) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("pedimento", "rfc_cliente"))
                    ->where("patente = ?", $patente)
                    ->where("referencia = ?", $referencia)
                    ->where("rfc_cliente IS NOT NULL")
                    ->where("rfc_cliente REGEXP '^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}'");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;            
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function getFileInfo($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("ubicacion", "tipo_archivo", "sub_tipo_archivo"))
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function getInvoice($ctaGastos, $emisor) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from($this->_db_table, array("*"))
                    ->where("folio = ?", $ctaGastos)
                    ->where("tipo_archivo = 2");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function getCustomerFilesByReference($reference, $rfc) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("a" => "repositorio"), array("id", "tipo_archivo", "sub_tipo_archivo", "nom_archivo", "creado", "usuario", "ubicacion_pdf", "ubicacion"))
                    ->joinLeft(array("d" => "documentos"), "d.id = a.tipo_archivo", array("d.nombre"))
                    ->where("a.referencia = ?", $reference)
                    ->where("a.rfc_cliente = '{$rfc}' OR a.receptor_rfc = '{$rfc}'")
                    ->where("a.tipo_archivo NOT IN (29, 89, 2001, 9999)")
                    ->order("a.nom_archivo ASC");
            $stmt = $this->_db_table->fetchAll($sql);

            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function getAllFilesByReference($patente, $aduana, $reference) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("a" => "repositorio"), array("id", "tipo_archivo", "sub_tipo_archivo", "nom_archivo", "creado", "usuario", "ubicacion_pdf", "ubicacion"))
                    ->joinLeft(array("d" => "documentos"), "d.id = a.tipo_archivo", array("d.nombre"))
                    ->where("a.referencia = ?", $reference)
                    ->where("a.patente = ?", $patente)
                    ->where("a.tipo_archivo NOT IN (29, 89, 9999)")
                    ->order("a.nom_archivo ASC");
            $stmt = $this->_db_table->fetchAll($sql);

            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function getFileById($id) {
        try {
            $sql = $this->_db_table->select()
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function getCustomerFileById($id, $rfc) {
        try {
            $sql = $this->_db_table->select()
                    ->where("id = ?", $id)
                    ->where("rfc_cliente = '{$rfc}' OR receptor_rfc = '{$rfc}'")
                    ->where("tipo_archivo NOT IN (29, 89, 2001, 9999)");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function getFilePathById($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("ubicacion"))
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                $data = $stmt->toArray();
                return $data["ubicacion"];
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    /**
     * 
     * @param int $tipoDoc
     * @param int $subTipoArchivo
     * @param string $ref
     * @param int $patente
     * @param int $aduana
     * @param string $nom
     * @param string $ubi
     * @param string $username
     * @param string $edoc
     * @param string $rfc
     * @param string $pedimento
     * @return boolean
     * @throws Exception
     */
    public function addNewFile($tipoDoc, $subTipoArchivo, $ref, $patente, $aduana, $nom, $ubi, $username, $edoc = null, $rfc = null, $pedimento = null) {
        try {
            $data = array(
                "tipo_archivo" => $tipoDoc,
                "sub_tipo_archivo" => $subTipoArchivo,
                "referencia" => $ref,
                "patente" => $patente,
                "aduana" => $aduana,
                "nom_archivo" => $nom,
                "ubicacion" => $ubi,
                "edocument" => $edoc,
                "pedimento" => $pedimento,
                "rfc_cliente" => isset($rfc) ? $rfc : null,
                "creado" => date("Y-m-d H:i:s"),
                "usuario" => $username,
            );
            $added = $this->_db_table->insert($data);
            if ($added) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    /**
     * 
     * @param type $patente
     * @param type $aduana
     * @param type $pedimento
     * @param type $referencia
     * @param type $rfcCliente
     * @param type $usuario
     * @return boolean
     * @throws Exception
     */
    public function nuevoRepositorio($patente, $aduana, $pedimento, $referencia, $rfcCliente, $usuario  = null, $id_trafico = null) {
        try {
            $data = array(
                "tipo_archivo" => 9999,
                "id_trafico" => $id_trafico,
                "patente" => $patente,
                "aduana" => $aduana,
                "pedimento" => $pedimento,
                "referencia" => $referencia,
                "rfc_cliente" => $rfcCliente,
                "creado" => date("Y-m-d H:i:s"),
                "usuario" => $usuario,
            );
            $added = $this->_db_table->insert($data);
            if ($added) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    /**
     * 
     * @param int $tipoArchivo
     * @param int $subTipoArchivo
     * @param string $referencia
     * @param int $patente
     * @param int $aduana
     * @param string $nombre
     * @param string $ubicacion
     * @param string $username
     * @param string $rfcCliente
     * @param int $pedimento
     * @param string $edocument
     * @return boolean
     * @throws Exception
     */
    public function addFile($tipoArchivo, $subTipoArchivo, $referencia, $patente, $aduana, $nombre, $ubicacion, $username, $rfcCliente = null, $pedimento = null, $edocument = null) {
        try {
            $data = array(
                "tipo_archivo" => $tipoArchivo,
                "sub_tipo_archivo" => $subTipoArchivo,
                "referencia" => $referencia,
                "patente" => $patente,
                "aduana" => $aduana,
                "nom_archivo" => $nombre,
                "ubicacion" => $ubicacion,
                "edocument" => $edocument,
                "pedimento" => $pedimento,
                "rfc_cliente" => isset($rfcCliente) ? $rfcCliente : null,
                "creado" => date("Y-m-d H:i:s"),
                "usuario" => $username,
            );
            $added = $this->_db_table->insert($data);
            if ($added) {
                return $added;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    /**
     * 
     * @param int $tipoArchivo
     * @param string $referencia
     * @param int $patente
     * @param int $aduana
     * @param string $nombre
     * @return boolean
     * @throws Exception
     */
    public function searchFile($tipoArchivo, $referencia, $patente, $aduana, $nombre) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id"))
                    ->where("tipo_archivo = ?", $tipoArchivo)
                    ->where("referencia = ?", $referencia)
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("nom_archivo = ?", $nombre);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->id;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    public function buscarArchivo($tipoArchivo, $patente, $aduana, $referencia, $nombre) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id"))
                    ->where("tipo_archivo = ?", $tipoArchivo)
                    ->where("referencia = ?", $referencia)
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("nom_archivo = ?", $nombre);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->id;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function agregar($arr) {
        try {
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    public function nuevoArchivo($tipoArchivo, $subTipoArchivo, $patente, $aduana, $pedimento, $referencia, $filename, $path, $username, $rfcCliente) {
        try {
            $data = array(
                "tipo_archivo" => $tipoArchivo,
                "sub_tipo_archivo" => $subTipoArchivo,
                "referencia" => $referencia,
                "patente" => $patente,
                "aduana" => $aduana,
                "pedimento" => $pedimento,
                "nom_archivo" => $filename,
                "ubicacion" => $path,
                "rfc_cliente" => $rfcCliente,
                "creado" => date("Y-m-d H:i:s"),
                "usuario" => $username,
            );
            $stmt = $this->_db_table->insert($data);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    public function nuevaFacturaOriginal($idTrafico, $idFactura, $tipoArchivo, $subTipoArchivo, $patente, $aduana, $pedimento, $referencia, $filename, $path, $username, $rfcCliente, $folio, $emisorNombre) {
        try {
            $data = array(
                "id_trafico" => $idTrafico,
                "id_factura" => $idFactura,
                "tipo_archivo" => $tipoArchivo,
                "sub_tipo_archivo" => $subTipoArchivo,
                "referencia" => $referencia,
                "patente" => $patente,
                "aduana" => $aduana,
                "pedimento" => $pedimento,
                "nom_archivo" => $filename,
                "ubicacion" => $path,
                "rfc_cliente" => $rfcCliente,
                "folio" => $folio,
                "emisor_nombre" => $emisorNombre,
                "creado" => date("Y-m-d H:i:s"),
                "usuario" => $username,
            );
            $stmt = $this->_db_table->insert($data);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function addNewInvoice($tipoArchivo, $subTipoArchivo, $folio, $fecha, $rfcEmisor, $nomEmisor, $rfcReceptor, $nomReceptor, $nom, $ubi, $username) {
        try {
            $data = array(
                "folio" => $folio,
                "fecha" => $fecha,
                "tipo_archivo" => $tipoArchivo,
                "sub_tipo_archivo" => $subTipoArchivo,
                "emisor_rfc" => $rfcEmisor,
                "emisor_nombre" => $nomEmisor,
                "receptor_rfc" => $rfcReceptor,
                "receptor_nombre" => $nomReceptor,
                "nom_archivo" => $nom,
                "ubicacion" => $ubi,
                "creado" => date("Y-m-d H:i:s"),
                "usuario" => $username,
            );
            $added = $this->_db_table->insert($data);
            if ($added) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function addNewTerminalInvoice($data) {
        try {
            $added = $this->_db_table->insert($data);
            if ($added) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function verifyInvoice($folio, $rfcEmisor, $rfcReceptor, $nom) {
        try {
            $sql = $this->_db_table->select();
            $sql->where("folio = ?", $folio)
                    ->where("emisor_rfc = ?", $rfcEmisor)
                    ->where("receptor_rfc = ?", $rfcReceptor)
                    ->where("nom_archivo = ?", $nom);
            if (($stmt = $this->_db_table->fetchRow($sql))) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function checkIfFileExists($ref, $patente, $aduana, $nom) {
        try {
            $sql = $this->_db_table->select()
                    ->where("referencia = ?", $ref)
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("nom_archivo = ?", $nom);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function searchReference($ref, $patente = null, $aduana = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from("repositorio", array("referencia", "patente", "aduana", "creado"))
                    ->where("referencia = ?", $ref)
                    ->group("referencia")
                    ->group("patente")
                    ->group("aduana");
            if (isset($patente)) {
                $sql->where("patente = ?", $patente);
            }
            if (isset($aduana)) {
                $sql->where("aduana = ?", $aduana);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $data = array();
                foreach ($stmt as $item) {
                    $data[] = array(
                        "referencia" => $item["referencia"],
                        "patente" => $item["patente"],
                        "aduana" => $item["aduana"],
                        "year" => date("Y", strtotime($item["creado"])),
                        "fecha" => date("Y-m-d", strtotime($item["creado"])),
                    );
                }
                return $data;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function verificarEDoc($id) {
        try {
            $sql = $this->_db_table->select()
                    ->where("id = ?", $id)
                    ->where("edocument IS NOT NULL");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt["edocument"];
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function obtenerArchivosProveedor($rfc, $fechaIni = null, $fechaFin = null, $folio = null, $rfcCliente = null) {
        try {
            $sql = $this->_db_table->select()
                    ->where("emisor_rfc = ?", $rfc);
            if (!isset($folio)) {
                if (isset($fechaIni)) {
                    $sql->where("fecha >= ?", $fechaIni);
                }
                if (isset($fechaFin)) {
                    $sql->where("fecha <= ?", $fechaFin);
                }
            } elseif (isset($folio)) {
                $sql->where("folio = ?", $folio);
            }
            if (isset($rfcCliente)) {
                $sql->where("receptor_rfc LIKE ?", $rfcCliente);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function obtenerInformacion($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from("repositorio", array("tipo_archivo", "edocument"))
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function informacionVucem($id) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("r" => "repositorio"), array("nom_archivo", "ubicacion", "tipo_archivo"))
                    ->joinLeft(array("d" => "documentos"), "d.id = r.tipo_archivo", array("nombre as descripcion"))
                    ->where("r.id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function obtenerInfo($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from("repositorio", array("patente", "aduana", "referencia", "nom_archivo"))
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function verificarFacturaProveedor($rfc, $folio) {
        try {
            $sql = $this->_db_table->select()
                    ->where("emisor_rfc = ?", $rfc)
                    ->where("folio = ?", $folio);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function verificarArchivo($patente, $referencia, $nomArchivo) {
        try {
            $sql = $this->_db_table->select()
                    ->from("repositorio")                    
                    ->where("referencia = ?", $referencia)
                    ->where("nom_archivo = ?", $nomArchivo);
            if (isset($patente)) {
                $sql->where("patente = ?", $patente);
            }
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function verificar($patente, $referencia, $nomArchivo) {
        try {
            $sql = $this->_db_table->select()
                    ->from("repositorio")                    
                    ->where("referencia = ?", $referencia)
                    ->where("nom_archivo = ?", $nomArchivo);
            if (isset($patente)) {
                $sql->where("patente = ?", $patente);
            }
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->id;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function getXmlPaths($fechaIni = null, $fechaFin = null, $rfc = null, $rfcCliente = null, $ids = null) {
        try {
            $sql = $this->_db_table->select();
            if (!isset($ids)) {
                $sql->where("tipo_archivo = 3")
                        ->where("fecha >= ?", $fechaIni)
                        ->where("DATE_FORMAT(fecha, '%Y-%m-%d') <= ?", $fechaFin);
                if ($rfc) {
                    $sql->where("emisor_rfc = ?", $rfc);
                }
                if ($rfcCliente) {
                    $sql->where("receptor_rfc LIKE ?", $rfcCliente);
                }
            } else {
                $sql->where("tipo_archivo = 3")
                        ->where("id IN (?)", $ids);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $data = array();
                foreach ($stmt as $item) {
                    $data[] = $item["ubicacion"];
                }
                return $data;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function getXmlPathsLinux($fechaIni = null, $fechaFin = null, $rfc = null, $rfcCliente = null, $ids = null) {
        try {
            $sql = $this->_db_table->select();
            if (!isset($ids)) {
                $sql->where("tipo_archivo = 3")
                        ->where("fecha >= ?", $fechaIni)
                        ->where("DATE_FORMAT(fecha, '%Y-%m-%d') <= ?", $fechaFin);
                if ($rfc) {
                    $sql->where("emisor_rfc = ?", $rfc);
                }
                if ($rfcCliente) {
                    $sql->where("receptor_rfc LIKE ?", $rfcCliente);
                }
            } else {
                $sql->where("tipo_archivo = 3")
                        ->where("id IN (?)", $ids);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $data = array();
                foreach ($stmt as $item) {
                    $data[] = $item["ubicacion"];
                }
                return $data;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function getFilesByRfcAndDate($rfc, $date) {
        try {
            $sql = $this->_db_table->select();
            $sql->from("repositorio", array("ubicacion", "nom_archivo"))
                    ->where("emisor_rfc = ?", $rfc)
                    ->where("fecha LIKE ?", $date . "%");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function getInvoicesByRfcAndDate($rfcEmi, $rfc, $date) {
        try {
            $sql = $this->_db_table->select();
            $sql->from("repositorio", array("id", "nom_archivo", "ubicacion", "folio"))
                    ->where("emisor_rfc = ?", $rfcEmi)
                    ->where("receptor_rfc = ?", $rfc)
                    ->where("fecha LIKE ?", $date . "%")
                    ->order("folio ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $data = array();
                foreach ($stmt->toArray() as $item) {
                    if (preg_match("/.xml$/i", $item["nom_archivo"])) {
                        $data[$item["folio"]]["xml"] = array(
                            "id" => $item["id"],
                            "nom_archivo" => $item["nom_archivo"],
                            "ubicacion" => $item["ubicacion"],
                        );
                    } elseif (preg_match("/.pdf$/i", $item["nom_archivo"])) {
                        $data[$item["folio"]]["pdf"] = array(
                            "id" => $item["id"],
                            "nom_archivo" => $item["nom_archivo"],
                            "ubicacion" => $item["ubicacion"],
                        );
                    }
                }
                return $data;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function cofidiSent($id) {
        try {
            $data = array(
                "cofidi" => 1,
                "email" => 1,
            );
            $where = array(
                "id = ?" => $id,
            );
            $updated = $this->_db_table->update($data, $where);
            if ($updated) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function emailSent($id) {
        try {
            $data = array(
                "email" => 1,
            );
            $where = array(
                "id = ?" => $id,
            );
            $updated = $this->_db_table->update($data, $where);
            if ($updated) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    public function ftpSent($id) {
        try {
            $stmt = $this->_db_table->update(array("ftp" => 1), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function getInvoicesByRfc($rfcEmi, $rfc, $date = null, $limit = null) {
        try {
            $sql = $this->_db_table->select();
            $sql->from("repositorio", array("ubicacion_pdf", "ubicacion_xml", "ubicacion", "nom_archivo"))
                    ->where("emisor_rfc = ?", $rfcEmi)
                    ->where("receptor_rfc = ?", $rfc)
                    ->where("fecha >= '2014-01-01'")
                    ->where("email IS NULL")
                    ->order("folio ASC");
            if (isset($limit)) {
                $sql->limit($limit);
            } else {
                $sql->limit(250);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function getInvoicesByRfcTerminal($rfcEmi, $rfc, $limit = null) {
        try {
            $sql = $this->_db_table->select();
            $sql->from("repositorio", array("folio", "ubicacion_pdf", "ubicacion_xml", "ubicacion", "nom_archivo", "rfc_cliente", "referencia", "creado"))
                    ->where("emisor_rfc = ?", $rfcEmi)
                    ->where("receptor_rfc = ?", $rfc)
                    ->where("rfc_cliente IS NULL")
                    ->where("referencia IS NULL")
                    ->order("folio DESC");
            if (isset($limit)) {
                $sql->limit($limit);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function searchCovePdf($cove) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("ubicacion"))
                    ->where("nom_archivo LIKE '{$cove}%.pdf'");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function searchCove($cove) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("ubicacion"))
                    ->where("nom_archivo LIKE '{$cove}%.pdf'");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function searchEdoc($edoc) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("ubicacion"))
                    ->where("nom_archivo LIKE 'EDOC{$edoc}%'");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function searchFileByName($patente, $aduana, $nomArchivo) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id"))
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("nom_archivo LIKE '{$nomArchivo}'");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                $data = $stmt->toArray();
                return $data["id"];
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function referenciasCliente($fechaInicio, $fechaFin, $rfc) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("patente", "aduana", "pedimento", "referencia", "creado", "rfc_cliente"))
                    ->group(array("referencia", "patente", "aduana", "pedimento", "rfc_cliente"))
                    ->where("creado >= '{$fechaInicio} 00:00:00'")
                    ->where("creado <= '{$fechaFin} 23:59:59'")
                    ->where("rfc_cliente = '{$rfc}'")
                    ->order("creado DESC")
                    ->limit(250);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    /**
     * 
     * @param int $patente
     * @param int $aduana
     * @param string $revisados
     * @param string $revisadosOp
     * @param string $revisadosAdm
     * @param string $completos
     * @return type
     * @throws Exception
     */
    public function paginatorSelect($patente = null, $aduana = null, $revisados = null, $revisadosOp = null, $revisadosAdm = null, $completos = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("r" => "repositorio"), array("patente", "aduana", "pedimento", "referencia", "rfc_cliente"))
                    ->joinLeft(array("c" => "checklist_referencias"), "c.patente = r.patente AND c.aduana = r.aduana AND c.referencia = r.referencia", array("completo", "revisionOperaciones", "revisionAdministracion"))
                    ->where("r.tipo_archivo = 9999")
                    ->order("r.creado DESC")
                    ->limit(250);
            if(isset($revisados) && $revisados == "true") {
                $sql->where("c.revisionOperaciones = 1 AND c.revisionAdministracion = 1 AND c.completo IS NULL");
            }
            if(isset($revisadosOp) && $revisadosOp == "true") {
                $sql->where("c.revisionOperaciones = 1 AND c.completo IS NULL");
            }
            if(isset($revisadosAdm) && $revisadosAdm == "true") {
                $sql->where("c.revisionAdministracion = 1 AND c.completo IS NULL");
            }
            if(isset($completos) && $completos == "true") {
                $sql->where("c.completo = 1");                
            }
            if (isset($patente) && isset($aduana)) {
                if (!is_array($patente) && !is_array($aduana)) {
                    $sql->where("r.patente = ?", $patente)
                            ->where("r.aduana LIKE ?", substr($aduana, 0, 2) . "%");
                } else {
                    $sql->where("r.patente IN (?)", $patente)
                            ->where("r.aduana IN (?)", $aduana);
                }
            }
            return $sql;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    /**
     * 
     * @param array $rfcs
     * @param string $revisados
     * @param string $completos
     * @return type
     * @throws Exception
     */
    public function paginatorSelectInhouse($rfcs, $revisados = null, $completos = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("r" => "repositorio"), array("patente", "aduana", "pedimento", "referencia", "rfc_cliente"))
                    ->joinLeft(array("c" => "checklist_referencias"), "c.patente = r.patente AND c.aduana = r.aduana AND c.referencia = r.referencia", array("completo", "revisionOperaciones", "revisionAdministracion"))
                    ->where("r.rfc_cliente IN (?)", $rfcs)
                    ->where("r.tipo_archivo = 9999")
                    ->order("r.creado DESC")
                    ->limit(250);
            if(isset($revisados) && $revisados == "true") {
                $sql->where("c.revisionOperaciones IS NOT NULL OR c.revisionAdministracion IS NOT NULL");
            }
            if(isset($completos) && $completos == "true") {
                $sql->where("c.completo IS NOT NULL");                
            }
            return $sql;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function getLastReferences($patente = null, $aduana = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("patente", "aduana", "pedimento", "referencia", "rfc_cliente"))
                    ->group(array("referencia", "patente", "aduana", "pedimento", "rfc_cliente"))
                    ->order("creado DESC")
                    ->limit(250);
            if (isset($patente) && isset($aduana)) {
                if (!is_array($patente) && !is_array($aduana)) {
                    $sql->where("patente = ?", $patente)
                            ->where("aduana LIKE ?", substr($aduana, 0, 2) . "%");
                } else {
                    $sql->where("patente IN (?)", $patente)
                            ->where("aduana IN (?)", $aduana);
                }
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function getLastCustomerReferences($rfc) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("patente", "aduana", "pedimento", "referencia", "creado", "rfc_cliente"))
                    ->group(array("referencia", "patente", "aduana"))
                    ->where("rfc_cliente = ?", $rfc)
                    ->where("tipo_archivo = 9999")
                    ->order("creado DESC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function search($referencia, $patentes = null, $aduanas = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("r" => "repositorio"), array("patente", "aduana", "pedimento", "referencia", "rfc_cliente"))
                    ->joinLeft(array("c" => "checklist_referencias"), "c.patente = r.patente AND c.aduana = r.aduana AND c.referencia = r.referencia", array("completo", "revisionOperaciones", "revisionAdministracion"))
                    ->where("r.referencia = ?", $referencia)
                    ->group("r.referencia")
                    ->group("r.patente")
                    ->group("r.aduana");
            if (isset($patentes)) {
                if (!is_array($patentes)) {
                    $sql->where("r.patente = ?", $patentes);
                } else {
                    $sql->where("r.patente IN (?)", $patentes);
                }
            }
            if (isset($aduanas)) {
                if (!is_array($aduanas)) {
                    $sql->where("r.aduana LIKE ?", substr($aduanas, 0, 2) . "%");
                } else {
                    $sql->where("r.aduana IN (?)", $aduanas);
                }
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    public function searchInhouse($referencia, $rfcs) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("r" => "repositorio"), array("patente", "aduana", "pedimento", "referencia", "rfc_cliente"))
                    ->joinLeft(array("c" => "checklist_referencias"), "c.patente = r.patente AND c.aduana = r.aduana AND c.referencia = r.referencia", array("completo", "revisionOperaciones", "revisionAdministracion"))
                    ->where("r.rfc_cliente IN (?)", $rfcs)
                    ->where("r.referencia = ?", $referencia)
                    ->group("r.referencia")
                    ->group("r.patente")
                    ->group("r.aduana");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function searchByDocument($pedimento, $patente = null, $aduana = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("r" => "repositorio"), array("patente", "aduana", "pedimento", "referencia", "rfc_cliente"))
                    ->joinLeft(array("c" => "checklist_referencias"), "c.patente = r.patente AND c.aduana = r.aduana AND c.referencia = r.referencia", array("completo", "revisionOperaciones", "revisionAdministracion"))
                    ->where("r.pedimento = ?", $pedimento)
                    ->group("r.referencia")
                    ->group("r.patente")
                    ->group("r.aduana");
            if (isset($patente)) {
                if (!is_array($patente)) {
                    $sql->where("r.patente = ?", $patente);
                } else {
                    $sql->where("r.patente IN (?)", $patente);
                }
            }
            if (isset($aduana)) {
                if (!is_array($patente)) {
                    $sql->where("r.aduana LIKE ?", substr($aduana, 0, 2) . "%");
                } else {
                    $sql->where("r.aduana IN (?)", $aduana);
                }
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    public function searchByDocumentInhouse($pedimento, $rfcs) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("r" => "repositorio"), array("patente", "aduana", "pedimento", "referencia", "rfc_cliente"))
                    ->joinLeft(array("c" => "checklist_referencias"), "c.patente = r.patente AND c.aduana = r.aduana AND c.referencia = r.referencia", array("completo", "revisionOperaciones", "revisionAdministracion"))
                    ->where("rfc_cliente IN (?)", $rfcs)
                    ->where("r.pedimento = ?", $pedimento)
                    ->group("r.referencia")
                    ->group("r.patente")
                    ->group("r.aduana");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function cutomerSearch($ref, $rfc) {
        try {
            $sql = $this->_db_table->select()
                    ->from("repositorio", array("referencia", "patente", "pedimento", "rfc_cliente", "aduana", "creado"))
                    ->where("referencia = ?", $ref)
                    ->where("rfc_cliente = '{$rfc}' OR receptor_rfc = '{$rfc}'")
                    ->group("referencia")
                    ->group("patente")
                    ->group("aduana");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $data = array();
                foreach ($stmt as $item) {
                    $data[] = array(
                        "referencia" => $item["referencia"],
                        "patente" => $item["patente"],
                        "aduana" => $item["aduana"],
                        "pedimento" => $item["pedimento"],
                        "rfc_cliente" => $item["rfc_cliente"],
                        "creado" => date("Y-m-d", strtotime($item["creado"])),
                    );
                }
                return $data;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function cutomerSearchPedimento($ped, $rfc) {
        try {
            $sql = $this->_db_table->select()
                    ->from("repositorio", array("referencia", "patente", "pedimento", "rfc_cliente", "aduana", "creado"))
                    ->where("pedimento = ?", $ped)
                    ->where("rfc_cliente = '{$rfc}' OR receptor_rfc = '{$rfc}'")
                    ->group("referencia")
                    ->group("patente")
                    ->group("aduana");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $data = array();
                foreach ($stmt as $item) {
                    $data[] = array(
                        "referencia" => $item["referencia"],
                        "patente" => $item["patente"],
                        "aduana" => $item["aduana"],
                        "pedimento" => $item["pedimento"],
                        "rfc_cliente" => $item["rfc_cliente"],
                        "creado" => date("Y-m-d", strtotime($item["creado"])),
                    );
                }
                return $data;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    /**
     * 
     * @param type $patente
     * @param type $aduana
     * @param type $referencia
     * @return boolean
     * @throws Exception
     */
    public function buscarReferencia($patente, $aduana, $referencia) {
        try {
            $sql = $this->_db_table->select()
                    ->from("repositorio", array("referencia", "patente", "aduana", "creado"))
                    ->where("referencia = ?", $referencia)
                    ->group("referencia")
                    ->group("patente")
                    ->group("aduana");
            if (isset($patente)) {
                $sql->where("patente = ?", $patente);
            }
            if (isset($aduana)) {
                $sql->where("aduana = ?", $aduana);
            }
            $stmt = $this->_db_table->fetchRow($sql);
            if (isset($stmt) && !empty($stmt)) {
                return true;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    /**
     * 
     * @param type $patente
     * @param type $aduana
     * @param type $referencia
     * @return boolean
     * @throws Exception
     */
    public function buscarRepositorio($patente, $aduana, $referencia, $id_trafico = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from("repositorio", array("id"))
                    ->where("referencia = ?", $referencia)
                    ->where("tipo_archivo = 9999");
            if (isset($patente) && isset($aduana)) {
                $sql->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana);
            }
            if (isset($id_trafico)) {
                $sql->where("id_trafico = ?", $id_trafico);
            }
            $stmt = $this->_db_table->fetchRow($sql);
            if (isset($stmt) && !empty($stmt)) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    /**
     * URL : http://www.jveweb.net/archivo/2011/07/algunas-expresiones-regulares-y-como-usarlas-en-php.html
     * 
     * @param type $referencia
     * @return type
     * @throws Exception
     */
    public function buscarReferenciaRfc($referencia) {
        try {
            $sql = $this->_db_table->select()
                    ->from("repositorio", array("rfc_cliente as rfcCliente", "aduana", "creado", "count(*) as archivos"))
                    ->where("referencia = ?", $referencia)
                    ->where("patente = 3589")
                    ->where("tipo_archivo NOT IN (?)", array(29, 2, 31, 2001, 89, 9999))
                    ->where("rfc_cliente IS NOT NULL")
                    ->where("rfc_cliente REGEXP '^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}'");
            $stmt = $this->_db_table->fetchRow($sql);
            if (isset($stmt)) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function referenciasSinRfc($patente = null, $aduana = null, $limit = null, $referencia = null, $order = 1) {
        try {
            $sql = $this->_db_table->select()
                    ->from("repositorio", array("id", "referencia", "patente", "aduana", "pedimento"))
                    ->where("rfc_cliente IS NULL")
                    ->where("referencia IS NOT NULL AND referencia <> '' AND emisor_rfc IS NULL AND receptor_rfc IS NULL AND pedimento IS NULL")
                    ->where("referencia NOT IN ('Q1499999','4004855','4004141')");
            if (isset($limit)) {
                $sql->limit($limit);
            }
            if (isset($patente)) {
                $sql->where("patente = ?", $patente);
            }
            if (isset($aduana)) {
                $sql->where("aduana = ?", $aduana);
            }
            if (isset($referencia)) {
                $sql->where("referencia = ?", $referencia);
            }
            if ($order == 1) {
                $sql->order("creado ASC");
            } elseif ($order == 2) {
                $sql->order("creado DESC");
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function referenciasNoRfc($patente = null, $aduana = null, $limit = null, $referencia = null, $order = 1) {
        try {
            $sql = $this->_db_table->select()
                    ->distinct()
                    ->from("repositorio", array("referencia", "patente", "aduana", "pedimento"))
                    ->where("rfc_cliente IS NULL")
                    ->where("referencia IS NOT NULL AND referencia <> '' AND emisor_rfc IS NULL AND receptor_rfc IS NULL AND pedimento IS NULL")
                    ->where("referencia NOT IN ('Q1499999','4004855','4004141')");
            if (isset($limit)) {
                $sql->limit($limit);
            }
            if (isset($patente)) {
                $sql->where("patente = ?", $patente);
            }
            if (isset($aduana)) {
                $sql->where("aduana = ?", $aduana);
            }
            if (isset($referencia)) {
                $sql->where("referencia = ?", $referencia);
            }
            if ($order == 1) {
                $sql->order("creado ASC");
            } elseif ($order == 2) {
                $sql->order("creado DESC");
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function facturasSinRfc($patente = null, $aduana = null, $limit = null, $referencia = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from("repositorio", array("id", "referencia", "patente", "aduana", "pedimento"))
                    ->where("rfc_cliente IS NULL")
                    ->where("receptor_rfc IS NOT NULL");
            if (isset($limit)) {
                $sql->limit($limit);
            }
            if (isset($patente)) {
                $sql->where("patente = ?", $patente);
            }
            if (isset($aduana)) {
                $sql->where("aduana = ?", $aduana);
            }
            if (isset($referencia)) {
                $sql->where("referencia = ?", $referencia);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    /**
     * Actualizar RFC y pedimento en expediente digital.
     * 
     * @param int $id Id de la tabla
     * @param string $rfcCliente String del RFC del cliente
     * @param int $pedimento Numero de pedimento del archivo
     * @return boolean
     */
    public function actualizarRfcCliente($id, $rfcCliente, $pedimento) {
        try {
            $data = array(
                "rfc_cliente" => $rfcCliente,
                "pedimento" => $pedimento,
            );
            $where = array(
                "id = ?" => $id,
            );
            $updated = $this->_db_table->update($data, $where);
            if ($updated) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    /**
     * 
     * @param type $patente
     * @param type $referencia
     * @param type $rfcCliente
     * @param type $pedimento
     * @param type $username
     * @return boolean
     * @throws Exception
     */
    public function actualizarDatosReferencia($patente, $referencia, $rfcCliente, $pedimento, $username) {
        try {
            $data = array(
                "rfc_cliente" => $rfcCliente,
                "pedimento" => $pedimento,
                "modificado" => date("Y-m-d H:i:s"),
                "modificadoPor" => $username,
            );
            $where = array(
                "patente = ?" => $patente,
                "referencia = ?" => $referencia,
            );
            $updated = $this->_db_table->update($data, $where);
            if ($updated) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function actualizarIdTrafico($idTrafico, $arr) {
        $stmt = $this->_db_table->update($arr, array("id_trafico = ?" => $idTrafico));
        if ($stmt) {
            return true;
        }
        return null;
    }

    public function actualizarArchivos($patente, $aduana, $pedimento, $rfcCliente, $referencia, $anterior, $username) {
        try {
            $arr = array(
                "patente" => $patente,
                "aduana" => $aduana,
                "referencia" => $referencia,
                "rfc_cliente" => $rfcCliente,
                "pedimento" => $pedimento,
                "modificado" => date("Y-m-d H:i:s"),
                "modificadoPor" => $username,
            );
            $stmt = $this->_db_table->update($arr, array("referencia = ?" => $anterior));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function actualizarFolioTerminal($folio, $rfcCliente, $referencia, $rfcEmisor = null, $rfcReceptor = null, $pedimento = null) {
        try {
            if (!isset($pedimento)) {
                $data = array(
                    "emisor_rfc" => $rfcEmisor,
                    "receptor_rfc" => $rfcReceptor,
                    "rfc_cliente" => $rfcCliente,
                    "referencia" => $referencia,
                );
            } else {
                $data = array(
                    "emisor_rfc" => $rfcEmisor,
                    "receptor_rfc" => $rfcReceptor,
                    "rfc_cliente" => $rfcCliente,
                    "referencia" => $referencia,
                    "pedimento" => $pedimento,
                );
            }
            $where = array(
                "folio = ?" => $folio,
            );
            $updated = $this->_db_table->update($data, $where);
            if ($updated) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function actualizarRfcClienteReferencia($id, $rfcCliente, $pedimento, $referencia) {
        try {
            $data = array(
                "rfc_cliente" => $rfcCliente,
                "referencia" => $referencia,
                "pedimento" => $pedimento,
            );
            $where = array(
                "id = ?" => $id,
            );
            $updated = $this->_db_table->update($data, $where);
            if ($updated) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function archivosNoEnviadosFtp($patente, $aduana, $referencia, $rfcCliente = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from("repositorio", array("*"))
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("referencia = ?", $referencia)
                    ->where("tipo_archivo NOT IN (?)", array(29, 31, 89, 99, 2001, 9999))
                    ->where("ftp IS NULL")
                    ->where("borrado IS NULL");
            if (isset($rfcCliente)) {
                $sql->where("rfc_cliente = ?", $rfcCliente);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    public function noEnviadoFtp($rfcCliente, $referencia = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from("repositorio", array("id"))
                    ->where("rfc_cliente = ?", $rfcCliente)
                    ->where("ftp IS NULL")
                    ->where("borrado IS NULL")
                    ->limit(250);
            if (isset($referencia)) {
                $sql->where("referencia = ?", $referencia);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function referenciasNoEnviadas($rfcCliente, $referencia = null) {
        try {
            $sql = $this->_db_table->select()
                    ->distinct()
                    ->from("repositorio", array("referencia", "pedimento", "rfc_cliente"))
                    ->where("rfc_cliente = ?", $rfcCliente)
                    ->where("ftp IS NULL")
                    ->where("referencia IS NOT NULL")
                    ->where("pedimento IS NOT NULL")
                    ->where("borrado IS NULL")
                    ->limit(250);
            if (isset($referencia)) {
                $sql->where("referencia = ?", $referencia);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function referenciasParaEnviar($rfcCliente, $referencia) {
        try {
            $sql = $this->_db_table->select()
                    ->distinct()
                    ->from($this->_db_table, array("patente", "aduana", "pedimento", "referencia", "rfc_cliente"))
                    ->where("rfc_cliente = ?", $rfcCliente)
                    ->where("referencia IS NOT NULL")
                    ->where("borrado IS NULL");
            if (isset($referencia)) {
                $sql->where("referencia = ?", $referencia);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $data = array();
                foreach ($stmt as $item) {
                    $data[] = array(
                        "patente" => $item["patente"],
                        "aduana" => $item["aduana"],
                        "pedimento" => $item["pedimento"],
                        "referencia" => $item["referencia"],
                        "rfc_cliente" => $item["rfc_cliente"],
                    );
                }
                return $data;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function referenciasPorPatente($patente, $regex) {
        try {
            $sql = $this->_db_table->select()
                    ->distinct()
                    ->from($this->_db_table, array("patente", "referencia", "rfc_cliente"))
                    ->where("patente = ?", $patente)
                    ->where("referencia IS NOT NULL")
                    ->where("rfc_cliente REGEXP '^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}'")
                    ->where("rfc_cliente IS NOT NULL")
                    ->where("referencia REGEXP '{$regex}'")
                    ->where("borrado IS NULL")
                    ->where("tipo_archivo NOT IN (?)", array(9999))
                    ->group(array("patente", "referencia", "rfc_cliente"));
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function obtenerArchivosReferencia($referencia, $customer = null, $tiposArchivo = null, $ftp = null) {
        try {
            $sql = $this->_db_table->select()
                    ->distinct()
                    ->setIntegrityCheck(false)
                    ->from(array("r" => "repositorio"), array("id", "id_trafico", "patente", "aduana", "pedimento", "uuid", "nom_archivo", "ubicacion", "tipo_archivo", "creado", "usuario", "edocument"))
                    ->joinLeft(array("d" => "documentos"), "r.tipo_archivo = d.id", array(""))
                    ->where("r.tipo_archivo NOT IN (?)", array(9999))
                    ->order("d.orden ASC");
            if ($ftp) {
                $sql->where("r.ftp IS NULL");
            }
            if (!is_array($referencia)) {
                $sql->where("r.referencia = ?", $referencia);
            } else {
                $sql->where("r.referencia IN (?)", $referencia);
            }
            if (isset($customer)) {
                $sql->where("r.tipo_archivo NOT IN (29, 89, 2001, 9999)");
            }
            if (isset($tiposArchivo)) {
                $sql->where("r.tipo_archivo IN (?)", $tiposArchivo);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    public function obtenerTiposArchivosReferencia($referencia) {
        try {
            $sql = $this->_db_table->select()
                    ->distinct()
                    ->from($this->_db_table, array("tipo_archivo"))
                    ->where("referencia = ?", $referencia)
                    ->where("tipo_archivo NOT IN (?)", array(9999));
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $arr = array();
                foreach ($stmt->toArray() as $item) {
                    $arr[$item["tipo_archivo"]] = $item["tipo_archivo"];
                }
                return $arr;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function actualizarFacturaTerminal($folio, $tipoArchivo, $rfcEmisor, $patente, $referencia) {
        try {
            $data = array(
                "patente" => $patente,
                "referencia" => $referencia,
                "tipo_archivo" => $tipoArchivo,
            );
            $where = array(
                "folio = ?" => $folio,
                "emisor_rfc = ?" => $rfcEmisor,
            );
            $updated = $this->_db_table->update($data, $where);
            if ($updated) {
                return true;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function actualizarFactura($id, $data) {
        try {
            $where = array(
                "id = ?" => $id,
            );
            $updated = $this->_db_table->update($data, $where);
            if ($updated) {
                return true;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    public function archivosDeReferencia($referencia, $patente = null, $noCorresponsal = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from("repositorio", array("id"))
                    ->where("referencia = ?", $referencia)
                    ->where("ftp IS NULL")
                    ->where("borrado IS NULL");
            if (isset($patente)) {
                $sql->where("patente = ?", $patente);
            }
            if (isset($noCorresponsal)) {
                $sql->where("tipo_archivo NOT IN (29, 9999)");
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function archivosReferenciaAgente($referencia, $patente = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from("repositorio", array("nom_archivo", "ubicacion"))
                    ->where("tipo_archivo NOT IN (?)", array(29, 2, 31, 89, 2001, 9999))
                    ->where("referencia = ?", $referencia);
            if (isset($patente)) {
                $sql->where("patente = ?", $patente);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function removeFileById($id) {
        try {
            $where = array(
                "id = ?" => $id
            );
            $stmt = $this->_db_table->delete($where);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function getFileType($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from("repositorio", array("tipo_archivo"))
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                $data = $stmt->toArray();
                return $data["tipo_archivo"];
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function getEdocFileType($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from("repositorio", array("tipo_archivo"))
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                $data = $stmt->toArray();
                return $data["tipo_archivo"];
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function changeFileType($id, $type) {
        try {
            $data = array(
                "tipo_archivo" => $type,
            );
            $where = array(
                "id = ?" => $id,
            );
            $updated = $this->_db_table->update($data, $where);
            if ($updated) {
                return true;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function getInvoicesByRfcAndInvoiceByUuid($rfcEmi, $uuid) {
        try {
            $sql = $this->_db_table->select();
            $sql->from("repositorio", array("id", "folio", "uuid", "ubicacion", "nom_archivo"))
                    ->where("emisor_rfc = ?", $rfcEmi)
                    ->where("uuid = ?", $uuid);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function getInvoicesByRfcAndInvoice($rfcEmi, $folio) {
        try {
            $sql = $this->_db_table->select();
            $sql->from("repositorio", array("id", "folio", "uuid", "ubicacion", "nom_archivo"))
                    ->where("emisor_rfc = ?", $rfcEmi)
                    ->where("folio = ?", $folio);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function buscarFactTerceroPorReferencia($referencia) {
        try {
            $sql = $this->_db_table->select();
            $sql->from("repositorio", array("id", "ubicacion", "nom_archivo"))
                    ->where("referencia = ?", $referencia)
                    ->where("tipo_archivo = 40");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function obtenerCuentas($rfcEmisor, $patente = null, $aduana = null, $year = null, $mes = null, $dia = null, $folio = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from("repositorio", array("rfc_cliente", "patente", "aduana", "pedimento", "referencia", "folio"))
                    ->where("emisor_rfc = ?", $rfcEmisor)
                    ->where("rfc_cliente IS NULL")
                    ->where("tipo_archivo = 2")
                    ->group(array("patente", "patente", "aduana", "pedimento", "referencia", "folio"));
            if (isset($folio) && $folio != "") {
                $sql->where("folio = ?", $folio);
            } else {
                $sql->where("patente = ?", $patente)
                        ->where("aduana = ?", $aduana)
                        ->where("YEAR(fecha) = ?", $year)
                        ->where("MONTH(fecha) = ?", $mes)
                        ->where("DAY(fecha) = ?", $dia);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function archivosPatente($patente) {
        try {
            $sql = $this->_db_table->select()
                    ->from("repositorio", array("rfc_cliente", "patente", "pedimento", "aduana", "referencia", "referencia", "ubicacion"))
                    ->where("patente = ?", $patente)
                    ->where("DATE(creado) = CURDATE()");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function buscarRfcPorReferencia($patente, $referencia) {
        try {
            $sql = $this->_db_table->select()
                    ->from("repositorio", array("rfc_cliente"))
                    ->where("patente = ?", $patente)
                    ->where("referencia = ?", $referencia)
                    ->where("rfc_cliente IS NOT NULL");
            $stmt = $this->_db_table->fetchRow($sql, array());
            if ($stmt) {
                return $stmt["rfc_cliente"];
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    /**
     * 
     * @param int $patente
     * @param int $aduana
     * @param string $referencia
     * @param int $tipoArchivo
     * @return boolean
     * @throws Exception
     */
    public function buscarTipoArchivo($patente, $aduana, $referencia, $tipoArchivo) {
        try {
            $sql = $this->_db_table->select()
                    ->from("repositorio", array("*"))
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("referencia = ?", $referencia)
                    ->where("tipo_archivo = ?", $tipoArchivo);
            $stmt = $this->_db_table->fetchRow($sql, array());
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    /**
     * 
     * @param int $patente
     * @param string $referencia
     * @param array $tipo
     * @return boolean
     * @throws Exception
     */
    public function tipoDocumentoExiste($patente, $referencia, $tipo) {
        try {
            $sql = $this->_db_table->select()
                    ->from("repositorio", array("rfc_cliente"))
                    ->where("patente = ?", $patente)
                    ->where("referencia = ?", $referencia)
                    ->where("tipo_archivo IN (?)", $tipo);
            $stmt = $this->_db_table->fetchRow($sql, array());
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    /**
     * 
     * @param int $patente
     * @param string $referencia
     * @return boolean
     * @throws Exception
     */
    public function ulitmaMoficacion($patente, $referencia) {
        try {
            $sql = $this->_db_table->select()
                    ->from("repositorio", array("creado", "usuario"))
                    ->where("patente = ?", $patente)
                    ->where("referencia = ?", $referencia)
                    ->order("creado DESC");
            $stmt = $this->_db_table->fetchRow($sql, array());
            if ($stmt) {
                return date("d/m/y h:i a", strtotime($stmt["creado"])) . ", " . strtoupper($stmt["usuario"]);
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    /**
     * 
     * @param int $patente
     * @param string $referencia
     * @param int $min
     * @param int $max
     * @return boolean
     * @throws Exception
     */
    public function tipoDocumentoRango($patente, $referencia, $min, $max) {
        try {
            $sql = $this->_db_table->select()
                    ->from("repositorio", array("rfc_cliente"))
                    ->where("patente = ?", $patente)
                    ->where("referencia = ?", $referencia)
                    ->where("tipo_archivo >= ?", $min)
                    ->where("tipo_archivo <= ?", $max);
            $stmt = $this->_db_table->fetchRow($sql, array());
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function referenciasDeClientes($patente, $aduana, $fechaIni, $fechaFin) {
        try {
            $sql = $this->_db_table->select()
                    ->distinct()
                    ->from($this->_db_table, array("patente", "aduana", "pedimento", "referencia", "rfc_cliente"))
                    ->where("patente IN (?)", $patente)
                    ->where("aduana IN (?)", $aduana)
                    ->where("creado >= ?", $fechaIni)
                    ->where("creado <= ?", $fechaFin)
                    ->where("rfc_cliente IS NOT NULL");
            $stmt = $this->_db_table->fetchAll($sql, array());
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function obtenerFacturasTerminal($fecha, $receptor) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id", "referencia", "patente", "aduana", "pedimento", "folio", "nom_archivo", "ubicacion", "observaciones", "creado"))
                    ->where("emisor_rfc = 'TLO050804QY7'")
                    ->where("receptor_rfc = ?", $receptor)
                    ->where("nom_archivo LIKE '%.xml'")
                    ->where("creado >= '{$fecha}'");
            $stmt = $this->_db_table->fetchAll($sql, array());
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function obtenerFacturaTerminalFolio($folio) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id", "referencia", "patente", "aduana", "pedimento", "folio", "nom_archivo", "ubicacion", "observaciones", "creado"))
                    ->where("emisor_rfc = 'TLO050804QY7'")
                    ->where("folio = ?", $folio)
                    ->where("nom_archivo LIKE '%.xml'");
            $stmt = $this->_db_table->fetchRow($sql, array());
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function facturasTerminalPedimento($pedimento) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id", "referencia", "patente", "aduana", "pedimento", "folio", "nom_archivo", "ubicacion", "observaciones", "creado"))
                    ->where("emisor_rfc = 'TLO050804QY7'")
                    ->where("pedimento = ?", $pedimento)
                    ->where("nom_archivo LIKE '%.xml'");
            $stmt = $this->_db_table->fetchRow($sql, array());
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function modificado($id, $username) {
        try {
            $data = array(
                "modificado" => date("Y-m-d H:i:s"),
                "modificadoPor" => $username,
            );
            $where = array(
                "id = ?" => $id,
            );
            $updated = $this->_db_table->update($data, $where);
            if ($updated) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    public function buscarEnRepositorio($patente, $aduana, $referencia) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("pedimento", "rfc_cliente AS rfcCliente"))
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("referencia = ?", $referencia)
                    ->where("rfc_cliente IS NOT NULL AND pedimento IS NOT NULL");
            $stmt = $this->_db_table->fetchRow($sql, array());
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    public function update($id, $arr) {
        try {
            $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    public function obtenerComplementos($referencia, $patente = null, $aduana = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("a" => "repositorio"), array("id", "tipo_archivo", "sub_tipo_archivo", "nom_archivo", "creado", "usuario", "ubicacion_pdf", "ubicacion"))
                    ->joinLeft(array("d" => "documentos"), "d.id = a.tipo_archivo", array("d.nombre"))
                    ->where("a.referencia = ?", $referencia . "-C")
                    ->where("a.tipo_archivo NOT IN (29, 31, 58, 89, 2001, 9999)")
                    ->order("nom_archivo ASC");
            if (isset($patente)) {
                $sql->where("a.patente = ?", $patente);
            }
            if (isset($aduana)) {
                $sql->where("a.aduana LIKE ?", substr($aduana, 0, 2) . "%");
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }    

    public function complementosReferencia($referencia, $customer = null, $tiposArchivo = null) {
        try {
            $sql = $this->_db_table->select()
                    ->distinct()
                    ->from($this->_db_table, array("id", "uuid", "nom_archivo", "ubicacion", "tipo_archivo", "creado", "usuario", "edocument"))
                    ->where("ftp IS NULL")
                    ->where("tipo_archivo NOT IN (?)", array(9999))
                    ->order("nom_archivo ASC");
            if (!is_array($referencia)) {
                $sql->where("referencia = ?", $referencia . '-C');
            } else {
                $sql->where("referencia IN (?)", $referencia);
            }
            if (isset($customer)) {
                $sql->where("tipo_archivo NOT IN (29, 89, 2001, 9999)");
            }
            if (isset($tiposArchivo)) {
                $sql->where("tipo_archivo IN (?)", $tiposArchivo);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

}
