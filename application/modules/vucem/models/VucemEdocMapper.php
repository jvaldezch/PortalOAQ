<?php

class Vucem_Model_VucemEdocMapper {

    protected $_db_table;

    function __construct() {
        $this->_db_table = new Vucem_Model_DbTable_VucemEdoc();
    }

    public function verificar($patente, $aduana, $nomArchivo, $hash) {
        try {
            $sql = $this->_db_table->select()
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("nomArchivo = ?", $nomArchivo)
                    ->where("hash = ?", $hash);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function nuevaSolicitud($rfc, $patente, $aduana, $pedimento, $referencia, $uuid, $solicitud, $certificado, $firma, $cadena, $base64, $tipoDoc, $subTipoArchivo, $nomArchivo, $hash, $usuario, $email, $respuestaEnvio = null, $rfcConsulta = null) {
        try {
            $arr = array(
                "rfc" => $rfc,
                "patente" => $patente,
                "aduana" => $aduana,
                "pedimento" => $pedimento,
                "referencia" => $referencia,
                "uuid" => $uuid,
                "solicitud" => $solicitud,
                "certificado" => $certificado,
                "cadena" => $cadena,
                "firma" => $firma,
                "rfcConsulta" => isset($rfcConsulta) ? $rfcConsulta : null,
                "tipoDoc" => $tipoDoc,
                "subTipoArchivo" => $subTipoArchivo,
                "nomArchivo" => $nomArchivo,
                "hash" => $hash,
                "usuario" => $usuario,
                "email" => $email,
                "estatus" => 1,
                "edoc" => null,
                "archivo" => $base64,
                "respuesta" => null,
                "enviado" => date("Y-m-d H:i:s"),
            );
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function nuevaSolicitudEdoc($rfc, $patente, $aduana, $pedimento, $referencia, $uuid, $solicitud, $certificado, $firma, $cadena, $base64, $tipoDoc, $subTipoArchivo, $nomArchivo, $hash, $usuario, $email, $rfcConsulta) {
        try {
            $arr = array(
                "rfc" => $rfc,
                "patente" => $patente,
                "aduana" => $aduana,
                "pedimento" => $pedimento,
                "referencia" => $referencia,
                "uuid" => $uuid,
                "solicitud" => $solicitud,
                "certificado" => $certificado,
                "cadena" => $cadena,
                "firma" => $firma,
                "rfcConsulta" => isset($rfcConsulta) ? $rfcConsulta : null,
                "tipoDoc" => $tipoDoc,
                "subTipoArchivo" => $subTipoArchivo,
                "nomArchivo" => $nomArchivo,
                "hash" => $hash,
                "usuario" => $usuario,
                "email" => $email,
                "estatus" => 1,
                "edoc" => null,
                "archivo" => $base64,
                "respuesta" => null,
                "enviado" => date("Y-m-d H:i:s"),
            );
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarEdocRes($id, $xmlRespuesta) {
        try {
            $arr = array(
                "respuesta" => $xmlRespuesta,
            );
            $where = array(
                "id = ?" => $id,
            );
            $stmt = $this->_db_table->update($arr, $where);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarEdocSolicitud($id, $solicitud) {
        try {
            $arr = array(
                "solicitud" => $solicitud,
            );
            $where = array(
                "id = ?" => $id,
            );
            $stmt = $this->_db_table->update($arr, $where);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarEdocEstatus($id, $estatus) {
        try {
            $arr = array(
                "estatus" => $estatus,
            );
            $where = array(
                "id = ?" => $id,
            );
            $stmt = $this->_db_table->update($arr, $where);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerRespuestaVU($id) {
        try {
            $sql = $this->_db_table->select();
            $sql->from("vucem_edoc", array("respuesta_vu", "respuesta", "solicitud"))
                    ->where("id= ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerEdocumentsCorresponsal($aduanas = null) {
        try {
            $sql = $this->_db_table->select();
            $sql->from("vucem_edoc", array("id", "uuid", "rfc", "solicitud", "patente", "aduana", "pedimento", "referencia", "tipoDoc", "subTipoArchivo", "nomArchivo", "usuario", "edoc", "estatus", "respuesta_vu", "enviado", "actualizado", "usuario", "LENGTH(archivo) AS size", "expediente"))
                    ->order("enviado DESC")
                    ->limit(100);
            if (isset($aduanas)) {
                foreach ($aduanas as $adu) {
                    $sql->where("aduana = ?", $adu["aduana"])
                            ->where("patente = ?", $adu["patente"]);
                }
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function obtenerSolicitudes($usuario = null, $page = null, $limit = null, $aduana = null) {
        try {
            $sql = $this->_db_table->select();
            $sql->from("vucem_edoc", array("id", "uuid", "rfc", "solicitud", "patente", "aduana", "pedimento", "referencia", "tipoDoc", "subTipoArchivo", "nomArchivo", "usuario", "edoc", "estatus", "respuesta_vu", "enviado", "actualizado", "usuario", "LENGTH(archivo) AS size", "expediente"))
                    ->order("enviado DESC")
                    ->limit(100);
            if ($limit && $page) {
                $sql->limitPage($page, $limit);
            }
            if (isset($aduana)) {
                $sql->where("aduana = ?", $aduana);
            }
            if (isset($usuario)) {
                $sql->where("usuario  = ?", $usuario);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function buscarSolicitudes($usuario = null, $edoc = null, $referencia = null, $pedimento = null) {
        try {
            $sql = $this->_db_table->select();
            $sql->from("vucem_edoc", array("id", "uuid", "rfc", "solicitud", "patente", "aduana", "pedimento", "referencia", "tipoDoc", "subTipoArchivo", "nomArchivo", "usuario", "edoc", "estatus", "respuesta_vu", "enviado", "actualizado", "usuario", "LENGTH(archivo) AS size", "expediente"))
                    ->order("enviado DESC");
            if (isset($edoc)) {
                $sql->where("edoc LIKE '%{$edoc}%'");
            }
            if (isset($referencia)) {
                $sql->where("referencia LIKE '%{$referencia}%'");
            }
            if (isset($pedimento)) {
                $sql->where("pedimento LIKE '%{$pedimento}%'");
            }
            if (isset($usuario)) {
                $sql->where("usuario  = ?", $usuario);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerSinRespuestaEdoc($username = null, $aduana = null, $solicitud = null, $limit = null) {
        try {
            $sql = $this->_db_table->select();
            $sql->from("vucem_edoc", array("id", "uuid", "solicitud", "pedimento", "patente", "aduana", "referencia", "rfc", "nomArchivo"))
                    ->where("estatus = 1")
                    ->where("solicitud IS NOT NULL");
            if (isset($username)) {
                $sql->where("usuario  = ?", $username);
            }
            if (isset($aduana)) {
                $sql->where("aduana  = ?", $aduana);
            }
            if (isset($solicitud)) {
                $sql->where("solicitud = ?", $solicitud);
            }
            if (isset($limit)) {
                $sql->limit($limit);
                $sql->order("enviado DESC");
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerSinExpediente($username = null) {
        try {
            $sql = $this->_db_table->select();
            $sql->from("vucem_edoc", array("id", "uuid", "solicitud"))
                    ->where("estatus = 2")
                    ->where("expediente = 0")
                    ->where("enviado LIKE ?", date("Y-m-d") . "%");
            if (isset($username)) {
                $sql->where("usuario  = ?", $username);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("Db Exception on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarEdoc($id, $solicitud, $estatus, $respuestaVu, $edoc = null, $numTramite = null) {
        try {
            $arr = array(
                "edoc" => $edoc,
                "numTramite" => $numTramite,
                "estatus" => $estatus,
                "respuesta_vu" => $respuestaVu,
                "actualizado" => date("Y-m-d H:i:s"),
            );
            $where = array(
                "id = ?" => $id,
                "solicitud = ?" => $solicitud,
            );
            $stmt = $this->_db_table->update($arr, $where);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerArchivoEdocument($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from("vucem_edoc", array("id", "nomArchivo", "archivo"));
            $sql->where("id = ?", $id);
            if (($stmt = $this->_db_table->fetchRow($sql))) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtener($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from("vucem_edoc", array("id", "uuid", "rfc", "patente", "aduana", "pedimento", "referencia", "solicitud", "cadena", "firma", "tipoDoc", "subTipoArchivo", "nomArchivo", "rfcConsulta", "hash", "edoc", "numTramite", "enviado", "actualizado", "usuario", "email"));
            $sql->where("id = ?", $id);
            if (($stmt = $this->_db_table->fetchRow($sql))) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerEdocPorUuid($uuid, $solicitud = null, $usuario = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from("vucem_edoc", array("id", "rfc", "patente", "aduana", "pedimento", "referencia", "solicitud", "cadena AS firma", "firma AS cadena", "tipoDoc", "subTipoArchivo", "nomArchivo", "rfcConsulta", "hash", "edoc", "numTramite", "enviado", "actualizado", "usuario", "respuesta"));
            if (isset($uuid)) {
                $sql->where("uuid = ?", $uuid);
            }
            if (isset($solicitud)) {
                $sql->where("solicitud = ?", $solicitud);
            }
            if (isset($usuario)) {
                $sql->where("usuario = ?", $usuario);
            }
            if (($stmt = $this->_db_table->fetchRow($sql))) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerEdocument($id, $solicitud) {
        try {
            $sql = $this->_db_table->select()
                    ->from("vucem_edoc", array("id", "rfc", "patente", "aduana", "pedimento", "referencia", "solicitud", "cadena", "firma", "tipoDoc", "subTipoArchivo", "nomArchivo", "rfcConsulta", "hash", "edoc", "numTramite", "enviado", "actualizado", "usuario", "respuesta", "email"))
                    ->where("id = ?", $id)
                    ->where("solicitud = ?", $solicitud);
            if (($stmt = $this->_db_table->fetchRow($sql))) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerEdocDigitalizado($uuid, $usuario = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from("vucem_edoc", array("nomArchivo", "archivo", "tipoDoc", "subTipoArchivo"));
            $sql->where("uuid = ?", $uuid);
            if (isset($usuario)) {
                $sql->where("usuario = ?", $usuario);
            }
            if (($stmt = $this->_db_table->fetchRow($sql))) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function archivoDigitalizado($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from("vucem_edoc", array("nomArchivo", "archivo", "tipoDoc", "subTipoArchivo", "hash"));
            $sql->where("id = ?", $id);
            if (($stmt = $this->_db_table->fetchRow($sql))) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function borrarEdoc($uuid, $username = null) {
        try {
            $where = array(
                "uuid = ?" => $uuid,
            );
            if (isset($username)) {
                $where["usuario = ?"] = $username;
            }
            $deleted = $this->_db_table->delete($where);
            if ($deleted) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function borrar($id) {
        try {
            $stmt = $this->_db_table->delete(array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function enExpediente($uuid) {
        try {
            $stmt = $this->_db_table->update(array("expediente" => 1), array("uuid = ?" => $uuid));
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function saved($id) {
        try {
            $stmt = $this->_db_table->update(array("expediente" => 1), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function noExpediente() {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id", "uuid", "patente", "aduana", "referencia", "edoc"))
                    ->where("expediente = 0")
                    ->where("edoc IS NOT NULL")
                    ->order("enviado DESC")
                    ->limit(200);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function update($id, $arr) {
        try {
            $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function add($arr) {
        try {
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function reportePorUsuario($fechaIni, $fechaFin, $select = false) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("s" => "vucem_edoc"), array(
                        "u.nombre",
                        new Zend_Db_Expr("sum(CASE s.estatus = 2 WHEN 1 THEN 1 ELSE 0 END) as sinError"),
                        new Zend_Db_Expr("sum(CASE s.estatus = 0 WHEN 1 THEN 1 ELSE 0 END) as conError"),
                        new Zend_Db_Expr("count(s.id) AS total"),
                    ))
                    ->joinInner(array("u" => "usuarios"), "u.usuario = s.usuario", array(""))
                    ->where("enviado > ?", date("Y-m-d", strtotime($fechaIni)))
                    ->where("enviado <= ?", date("Y-m-d", strtotime($fechaFin)))
                    ->group("u.nombre");
            if ($select == true) {
                return $sql;
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("Db Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
