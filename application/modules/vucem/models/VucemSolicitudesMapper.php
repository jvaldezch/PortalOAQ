<?php

class Vucem_Model_VucemSolicitudesMapper {

    protected $_db_table;

    function __construct() {
        $this->_db_table = new Vucem_Model_DbTable_VucemSolicitudes();
    }

    public function nuevaSolicitud($relFact, $numRelFact, $cert, $cadena, $firma, $xml, $rfc, $uuid, $imp_exp, $tipo, $patente, $aduana, $pedimento, $referencia, $factura, $usuario, $email, $manual = null) {
        try {
            $data = array(
                "relfact" => $relFact,
                "relfact_num" => $numRelFact,
                "certificado" => $cert,
                "cadena" => utf8_encode($cadena),
                "firma" => $firma,
                "xml" => utf8_encode($xml),
                "rfc" => $rfc,
                "uuid" => $uuid,
                "imp_exp" => $imp_exp,
                "tipo" => $tipo,
                "patente" => $patente,
                "aduana" => $aduana,
                "pedimento" => $pedimento,
                "referencia" => $referencia,
                "factura" => $factura,
                "usuario" => $usuario,
                "email" => $email,
                "estatus" => 3, // no enviado
                "creado" => date("Y-m-d H:i:s"),
                "manual" => isset($manual) ? 1 : 0,
            );

            $stmt = $this->_db_table->insert($data);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("Db Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarSolicitudNueva($id, $numSolicitud, $responseXml) {
        try {
            $data = array(
                "solicitud" => $numSolicitud,
                "respuesta" => $responseXml,
                "enviado" => date("Y-m-d H:i:s"),
                "estatus" => 1, // estatus de enviado
            );
            $stmt = $this->_db_table->update($data, array("id = ?" => $id));
            if ($stmt) {
                return $stmt;
            }
            return null;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("Db Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerFacturaSolicitudPorId($id, $username = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("cove", "factura", "estatus", "pedimento", "referencia", "patente", "aduana"))
                    ->where("id = ?", $id);
            if ($username) {
                $sql->where("usuario = ?", $username);
            }
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                $data = $stmt->toArray();
                return $data;
            }
            return null;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("Db Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerSolicitudPorId($id, $username = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("xml", "cove", "factura", "solicitud", "estatus", "enviado", "actualizado", "pedimento", "referencia", "patente", "aduana", "creado", "respuesta_vu"))
                    ->where("id = ?", $id);
            if ($username) {
                $sql->where("usuario = ?", $username);
            }
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("Db Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerSolicitud($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("solicitud", "rfc", "patente", "aduana", "factura"))
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("Db Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerSolicitudPorCove($cove) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("xml", "cove", "factura", "solicitud", "estatus", "enviado", "actualizado", "pedimento", "referencia", "patente", "aduana", "creado"))
                    ->where("cove = ?", $cove);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                $data = $stmt->toArray();
                return $data;
            }
            return;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("Db Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerDetalleSolicitudPorId($id, $username = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("patente", "aduana", "pedimento", "solicitud", "referencia", "rfc", "tipo", "cove", "uuid"))
                    ->where("id = ?", $id);
            if ($username) {
                $sql->where("usuario = ?", $username);
            }
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("Db Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerRespuestaVU($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("respuesta_vu", "cove", "solicitud", "factura"))
                    ->where("id= ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("Db Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerNombreCove($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("cove", "solicitud"))
                    ->where("id= ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("Db Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificarEstatus($uuid) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("estatus", "solicitud"))
                    ->where("uuid = ?", $uuid);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("Db Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificarSolicitud($uuid) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("uuid"))
                    ->where("uuid = ?", $uuid)
                    ->where("estatus > 0")
                    ->where("active = 1");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("Db Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function buscarPeticion($numPet) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("estatus", "solicitud"))
                    ->where("solicitud = ?", $numPet)
                    ->where("estatus = 1");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("Db Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarSolicitud($numop, $xml, $factura, $cove, $estatus) {
        try {
            $data = array(
                "respuesta_vu" => $xml,
                "cove" => $cove,
                "estatus" => $estatus,
                "actualizado" => date("Y-m-d H:i:s"),
            );
            $stmt = $this->_db_table->update($data, array("solicitud = ?" => $numop, "factura = ?" => $factura));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("Db Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarRespuestaCove($id, $numop, $xml, $cove, $estatus) {
        try {
            $data = array(
                "respuesta_vu" => $xml,
                "cove" => $cove,
                "estatus" => $estatus,
                "actualizado" => date("Y-m-d H:i:s"),
            );
            $stmt = $this->_db_table->update($data, array("solicitud = ?" => $numop, "id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("Db Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarSolicitudVucem($id, $estatus, $xml, $cove = null, $adenda = null) {
        try {
            $data = array(
                "respuesta_vu" => $xml,
                "cove" => isset($cove) ? $cove : null,
                "adenda" => isset($adenda) ? $adenda : null,
                "estatus" => $estatus,
                "actualizado" => date("Y-m-d H:i:s"),
            );
            $stmt = $this->_db_table->update($data, array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("Db Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerCove($uuid) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("cove"))
                    ->where("uuid = ?", $uuid)
                    ->where("estatus = 2");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("Db Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerCoveRespuesta($uuid) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("cove", "xml"))
                    ->where("uuid = ?", $uuid)
                    ->where("estatus = 2");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("Db Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerSolicitudesCorresponsal($aduanas = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id", "relfact", "tipo", "rfc", "factura", "solicitud", "patente", "aduana", "pedimento", "referencia", "usuario", "cove", "estatus", "respuesta_vu", "enviado", "actualizado", "usuario", "expediente", "enPedimento"))
                    ->order("id DESC")
                    ->where("active = 1")
                    ->limit(150);
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
        } catch (Zend_Db_Exception $e) {
            throw new Exception("Db Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function obtenerSolicitudes($usuario = null, $page = null, $limit = null, $aduana = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id", "relfact", "tipo", "rfc", "factura", "solicitud", "patente", "aduana", "pedimento", "referencia", "usuario", "cove", "estatus", "respuesta_vu", "enviado", "actualizado", "usuario", "expediente", "enPedimento"))
                    ->order("id DESC")
                    ->where("active = 1")
                    ->limit(150);
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
        } catch (Zend_Db_Exception $e) {
            throw new Exception("Db Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function buscarSolicitudes($usuario = null, $cove = null, $referencia = null, $pedimento = null, $factura = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id", "tipo", "relfact", "rfc", "factura", "solicitud", "patente", "aduana", "pedimento", "referencia", "usuario", "cove", "estatus", "respuesta_vu", "enviado", "actualizado", "usuario", "enPedimento", "expediente"))
                    ->order("id DESC")
                    ->where("active = 1");
            if (isset($cove)) {
                $sql->where("cove LIKE '%{$cove}%'");
            }
            if (isset($referencia)) {
                $sql->where("referencia LIKE '%{$referencia}%'");
            }
            if (isset($pedimento)) {
                $sql->where("pedimento LIKE '%{$pedimento}%'");
            }
            if (isset($factura)) {
                $sql->where("pedimento LIKE '%{$factura}%'");
            }
            if (isset($usuario)) {
                $sql->where("usuario  = ?", $usuario);
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

    public function contarSolicitudes($usuario = null, $aduana = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("count(*) as total"));
            if (isset($aduana)) {
                $sql->where("aduana  = ?", $aduana);
            }
            if (isset($usuario)) {
                $sql->where("usuario  = ?", $usuario);
            }
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt["total"];
            }
            return;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("Db Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerSinRespuestaCove($username = null, $aduana = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id", "solicitud", "patente", "aduana", "pedimento", "rfc", "referencia", "factura", "consolidado"))
                    ->where("(estatus = 1 AND relfact = '0' AND active = 1)")
                    ->order("enviado DESC")
                    ->limit(20);
            if (isset($aduana)) {
                $sql->where("aduana = ?", $aduana);
            }
            if (isset($username)) {
                $sql->where("usuario = ?", $username);
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

    public function obtenerSinRespuestaRelacion($username = null, $aduana = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id", "solicitud", "rfc"))
                    ->where("estatus = 1")
                    ->where("active = 1")
                    ->where("relfact = '1'");
            if (isset($username)) {
                $sql->where("usuario = ?", $username);
            }
            if (isset($aduana)) {
                $sql->where("aduana  = ?", $aduana);
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

    public function borrarSolicitud($id, $username = null) {
        try {
            $where = array(
                "id = ?" => $id,
            );
            if (isset($username)) {
                $where["usuario = ?"] = $username;
            }
            $stmt = $this->_db_table->delete($where);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("Db Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function removerSolicitud($id, $username = null) {
        try {
            $where = array(
                "id = ?" => $id,
            );
            if (isset($username)) {
                $where["usuario = ?"] = $username;
            }
            $stmt = $this->_db_table->update(array("active" => 0), $where);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("Db Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function reporteCoves($fechaIni = null, $fechaFin = null, $referencia = null, $pedimento = null, $usuario = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("rfc", "patente", "aduana", "pedimento", "referencia", "factura", "cove"))
                    ->where("cove IS NOT NULL");
            if (!isset($referencia) || !isset($pedimento)) {
                $sql->where("enviado >= ?", $fechaIni)
                        ->where("enviado <= ?", $fechaFin);
            }
            if (isset($referencia) && $referencia != "") {
                $sql->where("referencia = ?", $referencia);
            }
            if (isset($pedimento) && $pedimento != "") {
                $sql->where("pedimento = ?", $pedimento);
            }
            if (isset($usuario) && $usuario != "") {
                $sql->where("usuario = ?", $usuario);
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

    public function obtenerFacturaDeSolicitudId($id, $solId, $rfc) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("patente", "aduana", "pedimento", "factura", "referencia", "consolidado"))
                    ->where("id = ?", $id)
                    ->where("solicitud = ?", $solId)
                    ->where("rfc = ?", $rfc);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("Db Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function searchCove($cove) {
        try {
            $sql = $this->_db_table->select()
                    ->where("cove = ?", $cove);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("Db Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function ultimoCoveFecha($referencia, $patente, $pedimento) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array(new Zend_Db_Expr("MAX(actualizado) AS fecha_cove")))
                    ->where("patente = ?", $patente)
                    ->where("pedimento = ?", $pedimento)
                    ->where("referencia = ?", $referencia)
                    ->order("actualizado DESC");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt["fecha_cove"];
            }
            return;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("Db Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerXmlSolicitud($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("xml"))
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt["xml"];
            }
            return;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("Db Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarEstatusSolicitud($id, $solicitud) {
        try {
            $data = array(
                "solicitud" => $solicitud,
                "estatus" => 1,
                "enviado" => date("Y-m-d H:i:s"),
            );
            $stmt = $this->_db_table->update($data, array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("Db Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getXmlForFtp($rfc, $fecha) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("s" => "vucem_solicitudes"), array("s.xml", "s.cove"))
                    ->joinLeft(array("f" => "vucem_facturas"), "f.idSolicitud = s.id", array("f.CteRfc"))
                    ->where("f.CteRfc = ?", $rfc)
                    ->where("s.creado LIKE '{$fecha}%'")
                    ->where("s.cove IS NOT NULL")
                    ->where("s.active = 1");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("Db Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function enExpediente($id) {
        try {
            $stmt = $this->_db_table->update(array("expediente" => 1), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("Db Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function enPedimento($id) {
        try {
            $stmt = $this->_db_table->update(array("enPedimento" => 1), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("Db Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function noExpediente() {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id", "patente", "aduana", "referencia", "cove"))
                    ->where("expediente = 0")
                    ->where("cove IS NOT NULL")
                    ->order("creado DESC")
                    ->limit(1000);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("Db Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerSinExpediente($username = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id", "cove", "solicitud"))
                    ->where("estatus = 2")
                    ->where("expediente = 0")
                    ->where("enviado LIKE ?", date("Y-m-d") . "%")
                    ->limit(30);
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
        }
    }
    
    public function reportePorUsuario($fechaIni, $fechaFin, $select = false) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("s" => "vucem_solicitudes"), array(
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
