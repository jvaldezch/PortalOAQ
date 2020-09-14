<?php

class Archivo_Model_RepositorioIndex
{

    protected $_db_table;

    public function __construct()
    {
        $this->_db_table = new Archivo_Model_DbTable_RepositorioIndex();
    }

    public function paginatorSelectCorresponsales($filtro = false, $search = null, $aduanas = null)
    {
        $sql = $this->_db_table->select()
            ->setIntegrityCheck(false)
            ->from(array("r" => "repositorio_index"), array("id", "patente", "aduana", "pedimento", "referencia", "rfcCliente", "nombreCliente", "completo", "revisionOperaciones", "revisionAdministracion", "modificado", "modificadoPor", "creado", "creadoPor"))
            ->order("r.creado DESC")
            ->limit(250);
        if ($filtro !== false) {
            if ((int) $filtro == 1) {
                $sql->where("completo = 1");
            } else if ((int) $filtro == 2) {
                $sql->where("revisionOperaciones = 1 AND (completo IS NULL OR completo = 0) AND revisionAdministracion IS NULL");
            } else if ((int) $filtro == 3) {
                $sql->where("revisionAdministracion = 1 AND (completo IS NULL OR completo = 0) AND revisionOperaciones IS NULL");
            } else if ((int) $filtro == 4) {
                $sql->where("(revisionOperaciones = 1 AND revisionAdministracion = 1) AND (completo IS NULL OR completo = 0)");
            }
        }
        if (isset($search[0])) {
            $sql->where("patente LIKE ?", $search[0]);
        }
        if (isset($search[1])) {
            $sql->where("aduana LIKE ?", $search[1]);
        }
        if (isset($search[2])) {
            $sql->where("pedimento LIKE ?", $search[2]);
        }
        if (isset($search[3])) {
            $sql->where("referencia LIKE ?", $search[3]);
        }
        if (isset($aduanas) && !empty($aduanas)) {
            foreach ($aduanas as $adu) {
                $pats[] = $adu["patente"];
                $adus[] = $adu["aduana"];
            }
            if (!empty($pats) && !empty($adus)) {
                $sql->where("patente IN (" . implode(',', $pats) . ") AND aduana IN (" . implode(',', $adus) . ")");
            }
        }
        return $sql;
    }

    public function paginatorSelect($filtro = false, $search = null, $idsAduana = null, $rfcs = null)
    {
        try {
            $sql = $this->_db_table->select()
                ->setIntegrityCheck(false)
                ->from(array("r" => "repositorio_index"), array("id", "patente", "aduana", "pedimento", "referencia", "rfcCliente", "nombreCliente", "completo", "revisionOperaciones", "revisionAdministracion", "modificado", "modificadoPor", "creado", "creadoPor"))
                ->order("r.creado DESC")
                ->limit(250);
            if ($filtro !== false) {
                if ((int) $filtro == 1) {
                    $sql->where("completo = 1");
                } else if ((int) $filtro == 2) {
                    $sql->where("revisionOperaciones = 1 AND (completo IS NULL OR completo = 0) AND revisionAdministracion IS NULL");
                } else if ((int) $filtro == 3) {
                    $sql->where("revisionAdministracion = 1 AND (completo IS NULL OR completo = 0) AND revisionOperaciones IS NULL");
                } else if ((int) $filtro == 4) {
                    $sql->where("(revisionOperaciones = 1 AND revisionAdministracion = 1) AND (completo IS NULL OR completo = 0)");
                }
            }
            if (isset($search[0])) {
                $sql->where("patente LIKE ?", $search[0]);
            }
            if (isset($search[1])) {
                $sql->where("aduana LIKE ?", $search[1]);
            }
            if (isset($search[2])) {
                $sql->where("pedimento LIKE ?", $search[2]);
            }
            if (isset($search[3])) {
                $sql->where("referencia LIKE ?", $search[3]);
            }
            if (isset($search[4])) {
                $sql->where("rfcCliente LIKE ?", $search[4]);
            }
            if (isset($search[5])) { // fecha-inicio
                $sql->where("creado >= ?", $search[5]);
            }
            if (isset($search[6])) { // fecha-fin
                $sql->where("creado <= ?", $search[6] . ' 23:59:59');
            }
            if (isset($idsAduana) && !empty($idsAduana)) {
                $sql->where("idAduana IN (?)", $idsAduana);
            }
            if (isset($rfcs)) {
                $sql->where("rfcCliente IN (?)", $rfcs);
            }
            return $sql;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function datos($id, $idsAduana = null, $rfcs = null)
    {
        try {
            $sql = $this->_db_table->select()
                ->from($this->_db_table, array("*"))
                ->where("id = ?", $id);
            if (isset($idsAduana) && !empty($idsAduana)) {
                $sql->where("idAduana IN (?)", $idsAduana);
            }
            if (isset($rfcs)) {
                $sql->where("rfcCliente IN (?)", $rfcs);
            }
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function buscarPorTrafico($idTrafico)
    {
        try {
            $sql = $this->_db_table->select()
                ->from($this->_db_table, array("id"))
                ->where("idTrafico = ?", $idTrafico);
            $stmt = $this->_db_table->fetchRow($sql);
            if (isset($stmt) && !empty($stmt)) {
                return $stmt->id;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function obtenerPorTrafico($idTrafico)
    {
        try {
            $sql = $this->_db_table->select()
                ->from($this->_db_table, array("id"))
                ->where("idTrafico = ?", $idTrafico);
            $stmt = $this->_db_table->fetchRow($sql);
            if (isset($stmt) && !empty($stmt)) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function buscar($patente, $aduana, $referencia, $pedimento = null)
    {
        try {
            $sql = $this->_db_table->select()
                ->from($this->_db_table, array("id"))
                ->where("referencia = ?", $referencia);
            if (isset($pedimento)) {
                $sql->where("pedimento = ?", $pedimento);
            }
            if (isset($patente)) {
                $sql->where("patente = ?", $patente);
            }
            if (isset($aduana)) {
                $sql->where("aduana = ?", $aduana);
            }
            $stmt = $this->_db_table->fetchRow($sql);
            if (isset($stmt) && !empty($stmt)) {
                return $stmt->id;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function buscarIndex($patente, $aduana, $pedimento, $referencia)
    {
        try {
            $sql = $this->_db_table->select()
                ->from($this->_db_table, array("id", "idTrafico"))
                ->where("patente = ?", $patente)
                ->where("aduana = ?", $aduana)
                ->where("pedimento = ?", $pedimento)
                ->where("referencia = ?", $referencia);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function buscarDatos($patente, $aduana, $pedimento, $referencia)
    {
        try {
            $sql = $this->_db_table->select()
                ->from($this->_db_table, array("*"))
                ->where("patente = ?", $patente)
                ->where("aduana = ?", $aduana)
                ->where("pedimento = ?", $pedimento)
                ->where("referencia = ?", $referencia);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function agregarDesdeTrafico($idTrafico, $idAduana, $rfcCliente, $patente, $aduana, $pedimento, $referencia, $usuario, $nombreCliente = null)
    {
        try {
            $arr = array(
                "idTrafico" => $idTrafico,
                "idAduana" => $idAduana,
                "rfcCliente" => $rfcCliente,
                "nombreCliente" => $nombreCliente,
                "patente" => $patente,
                "aduana" => $aduana,
                "pedimento" => $pedimento,
                "referencia" => $referencia,
                "creado" => date("Y-m-d H:i:s"),
                "creadoPor" => $usuario,
            );
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function agregarDesdeBodega($idTrafico, $rfcCliente, $referencia, $usuario)
    {
        try {
            $arr = array(
                "idTrafico" => $idTrafico,
                "rfcCliente" => $rfcCliente,
                "referencia" => $referencia,
                "creado" => date("Y-m-d H:i:s"),
                "creadoPor" => $usuario,
            );
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function agregar($idAduana, $rfcCliente, $patente, $aduana, $pedimento, $referencia, $usuario, $nombreCliente = null, $idTrafico = null)
    {
        try {
            $arr = array(
                "idAduana" => $idAduana,
                "idTrafico" => isset($idTrafico) ? $idTrafico : null,
                "rfcCliente" => $rfcCliente,
                "nombreCliente" => $nombreCliente,
                "patente" => $patente,
                "aduana" => $aduana,
                "pedimento" => $pedimento,
                "referencia" => $referencia,
                "creado" => date("Y-m-d H:i:s"),
                "creadoPor" => $usuario,
            );
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function modificacion($id, $modificadoPor)
    {
        try {
            $arr = array(
                "modificado" => date("Y-m-d H:i:s"),
                "modificadoPor" => $modificadoPor,
            );
            $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function actualizarChecklist($id, $arr)
    {
        try {
            $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function actualizar($id, $patente, $aduana, $pedimento, $referencia, $modificadoPor)
    {
        try {
            $arr = array(
                "patente" => $patente,
                "aduana" => $aduana,
                "pedimento" => $pedimento,
                "referencia" => $referencia,
                "modificado" => date("Y-m-d H:i:s"),
                "modificadoPor" => $modificadoPor,
            );
            $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function borrar($id)
    {
        try {
            $stmt = $this->_db_table->delete(array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function update($id, $arr)
    {
        try {
            $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function archivosDeRepositorio($patente, $aduana, $referencia)
    {
        try {
            $sql = $this->_db_table->select()
                ->setIntegrityCheck(false)
                ->from("repositorio", array("id", "tipo_archivo", "sub_tipo_archivo"))
                ->where("referencia = ?", $referencia)
                ->where("patente = ?", $patente)
                ->where("aduana = ?", $aduana);
            $stmt = $this->_db_table->fetchAll($sql);
            if (isset($stmt) && !empty($stmt)) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function actualizarRegistroArchivo($id, $arr)
    {
        try {
            $this->_db = Zend_Registry::get("oaqintranet");
            $stmt = $this->_db->update("repositorio", $arr, "id = {$id}");
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function actualizarArchivo($id, $arr)
    {
        try {
            $this->_db = Zend_Registry::get("oaqintranet");
            $stmt = $this->_db->update("repositorio", $arr, "id = {$id} AND tipo_archivo <> 9999");
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function borrarEnRepositorio($id)
    {
        try {
            $this->_db = Zend_Registry::get("oaqintranet");
            $stmt = $this->_db->delete("repositorio", "id = {$id} AND tipo_archivo = 9999");
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function verificarFacturaTerminal()
    {
        try {
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function obtenerNoCompletos($fecha, $revOp = null, $revAdm = null)
    {
        try {
            if (!isset($fecha)) {
                $fecha = date("Y-m-d");
            }
            $sql = $this->_db_table->select()
                ->setIntegrityCheck(false)
                ->from(array("r" => "repositorio_index"), array("id", "patente", "aduana", "pedimento", "referencia", "modificadoPor", "creadoPor"))
                ->joinLeft(array("t" => "traficos"), "r.patente = t.patente AND r.aduana = t.aduana AND r.referencia = t.referencia", array("fechaPago", "fechaLiberacion"))
                ->where("r.completo IS NULL")
                ->where("t.fechaLiberacion IS  NOT NULL")
                ->where("r.creado >= ?", $fecha)
                ->order(array("r.patente", "r.aduana"));
            if (isset($revOp)) {
                $sql->where("revisionOperaciones IS NULL");
            }
            if (isset($revAdm)) {
                $sql->where("revisionAdministracion IS NULL");
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if (isset($stmt) && !empty($stmt)) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
}
