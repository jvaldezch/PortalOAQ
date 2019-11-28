<?php

class Clientes_Model_Repositorio {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Clientes_Model_DbTable_Repositorio();
    }

    public function datos($id, $rfc = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("r" => "repositorio_index"), array("*"))
                    ->where("r.id = ?", $id);
            if (isset($rfc)) {
                $sql->where("rfcCliente = ?", $rfc);
            }
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function archivos($reference, $patente = null, $aduana = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("a" => "repositorio"), array("id", "tipo_archivo", "nom_archivo", "creado", "usuario", "ubicacion_pdf", "ubicacion", "folio"))
                    ->joinLeft(array("d" => "documentos"), "d.id = a.tipo_archivo", array("d.nombre"))
                    ->where("a.referencia = ?", $reference)
                    //->where("a.tipo_archivo NOT IN (?)", array(29, 31, 89, 2001, 9999))
                    ->where("a.tipo_archivo NOT IN (?)", array(31, 89, 2001, 9999))
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

    public function archivosCliente($reference, $patente = null, $aduana = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("a" => "repositorio"), array("id", "tipo_archivo", "nom_archivo", "creado", "usuario", "ubicacion_pdf", "ubicacion", "folio"))
                    ->joinLeft(array("d" => "documentos"), "d.id = a.tipo_archivo", array("d.nombre"))
                    ->where("a.referencia = ?", $reference)
                    ->where("a.tipo_archivo NOT IN (?)", array(29, 31, 89, 2001, 99, 9999))
                    //->where("a.tipo_archivo NOT IN (?)", array(31, 89, 2001, 9999))
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

    public function complementosReferencia($referencia) {
        try {
            $sql = $this->_db_table->select()
                    ->distinct()
                    ->from($this->_db_table, array("id", "uuid", "nom_archivo", "ubicacion", "tipo_archivo", "creado", "usuario", "edocument"))
                    ->where("ftp IS NULL")
                    ->where("tipo_archivo NOT IN (?)", array(29, 31, 89, 2001, 9999))
                    ->where("referencia = ?", $referencia . '-C')
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

    public function referencias($rfCliente, $pedimento = null, $referencia = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("r" => "repositorio_index"), array("*"))
                    ->where("r.rfcCliente = ?", $rfCliente)
                    ->order("creado DESC");
            if (isset($pedimento)) {
                $sql->where("r.pedimento = ?", $pedimento);
            }
            if (isset($referencia)) {
                $sql->where("r.referencia = ?", $referencia);
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

    public function buscarReferencia($referencia, $rfcCliente) {
        try {
            $sql = $this->_db_table->select()
                    ->from("repositorio", array("referencia", "patente", "pedimento", "rfc_cliente", "aduana", "creado"))
                    ->where("referencia = ?", $referencia)
                    ->where("rfc_cliente = '{$rfcCliente}' OR receptor_rfc = '{$rfcCliente}'")
                    ->group("referencia")
                    ->group("patente")
                    ->group("aduana");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $arr = array();
                foreach ($stmt as $item) {
                    $arr[] = array(
                        "referencia" => $item["referencia"],
                        "patente" => $item["patente"],
                        "aduana" => $item["aduana"],
                        "pedimento" => $item["pedimento"],
                        "rfc_cliente" => $item["rfc_cliente"],
                        "creado" => date("Y-m-d", strtotime($item["creado"])),
                    );
                }
                return $arr;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function buscarPedimento($pedimento, $rfcCliente) {
        try {
            $sql = $this->_db_table->select()
                    ->from("repositorio", array("referencia", "patente", "pedimento", "rfc_cliente", "aduana", "creado"))
                    ->where("pedimento = ?", $pedimento)
                    ->where("rfc_cliente = '{$rfcCliente}' OR receptor_rfc = '{$rfcCliente}'")
                    ->group("referencia")
                    ->group("patente")
                    ->group("aduana");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $arr = array();
                foreach ($stmt as $item) {
                    $arr[] = array(
                        "referencia" => $item["referencia"],
                        "patente" => $item["patente"],
                        "aduana" => $item["aduana"],
                        "pedimento" => $item["pedimento"],
                        "rfc_cliente" => $item["rfc_cliente"],
                        "creado" => date("Y-m-d", strtotime($item["creado"])),
                    );
                }
                return $arr;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function obtenerExpedientes($rfcCliente) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("patente", "aduana", "pedimento", "referencia", "creado", "rfc_cliente"))
                    ->group(array("referencia", "patente", "aduana"))
                    ->where("rfc_cliente = ?", $rfcCliente)
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

    public function archivosDeReferencia($patente, $aduana, $referencia) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("a" => "repositorio"), array("id", "tipo_archivo", "nom_archivo", "creado", "usuario", "ubicacion_pdf", "ubicacion"))
                    ->joinLeft(array("d" => "documentos"), "d.id = a.tipo_archivo", array("d.nombre"))
                    ->where("a.referencia = ?", $referencia)
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

}
