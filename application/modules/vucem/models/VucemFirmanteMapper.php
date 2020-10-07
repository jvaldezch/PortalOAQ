<?php

class Vucem_Model_VucemFirmanteMapper
{

    protected $_db_table;
    protected $_key = "5203bfec0c3db@!b2295";

    function __construct()
    {
        $this->_db_table = new Vucem_Model_DbTable_VucemFirmante();
    }

    public function obtenerFirmantes($env = "prod", $username = null)
    {
        try {
            $sql = $this->_db_table->select();
            $sql->setIntegrityCheck(false)
                ->from(array("p" => "vucem_permisos"), array("p.idfirmante"))
                ->joinLeft(array("u" => "usuarios"), "u.id = p.idusuario")
                ->joinLeft(array("f" => "vucem_firmante"), "f.id = p.idfirmante", array("f.razon", "f.rfc"))
                ->where("f.tipo = ?", "prod")
                ->order("f.razon ASC");
            if (isset($username)) {
                $sql->where("u.usuario = ?", $username);
            }

            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function obtenerDetalleFirmanteId($id)
    {
        try {
            $sql = $this->_db_table->select();
            $sql->from("vucem_firmante", array(
                "razon",
                "rfc",
                "figura",
                "patente",
                "aduana",
                "key_nom",
                "certificado_nom",
                new Zend_Db_Expr("AES_DECRYPT(`key`,'{$this->_key}') AS `key`"),
                new Zend_Db_Expr("AES_DECRYPT(spem,'{$this->_key}') AS spem"),
                new Zend_Db_Expr("AES_DECRYPT(certificado,'{$this->_key}') AS certificado"),
                new Zend_Db_Expr("AES_DECRYPT(password_spem,'{$this->_key}') AS password_spem"),
                new Zend_Db_Expr("AES_DECRYPT(password_ws,'{$this->_key}') AS password_ws"),
                "sha",
            ))
                ->where("id = ?", $id)
                ->limit(1);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                $data = array(
                    "razon" => $stmt["razon"],
                    "figura" => $stmt["figura"],
                    "patente" => $stmt["patente"],
                    "aduana" => $stmt["aduana"],
                    "rfc" => $stmt["rfc"],
                    "key" => $stmt["key"],
                    "cer" => $stmt["certificado"],
                    "spem" => $stmt["spem"],
                    "spem_pswd" => $stmt["password_spem"],
                    "ws_pswd" => $stmt["password_ws"],
                    "sha" => $stmt["sha"],
                    "key_nom" => $stmt["key_nom"],
                    "cer_nom" => $stmt["certificado_nom"],
                );
                return $data;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function actualizarWs($id, $ws)
    {
        try {
            $arr = array(
                "password_ws" => new Zend_Db_Expr("AES_ENCRYPT('{$ws}','{$this->_key}')"),
            );
            $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function obtenerDetalleFirmante($rfc, $env = null, $patente = null, $aduana = null)
    {
        try {
            $sql = $this->_db_table->select();
            $sql->from("vucem_firmante", array(
                "id",
                "razon",
                "rfc",
                "figura",
                "patente",
                new Zend_Db_Expr("AES_DECRYPT(`key`,'{$this->_key}') AS llave"),
                new Zend_Db_Expr("AES_DECRYPT(spem,'{$this->_key}') AS spem"),
                new Zend_Db_Expr("AES_DECRYPT(certificado,'{$this->_key}') AS certificado"),
                new Zend_Db_Expr("AES_DECRYPT(password_spem,'{$this->_key}') AS password_spem"),
                new Zend_Db_Expr("AES_DECRYPT(password_ws,'{$this->_key}') AS password_ws"),
                "sha",
                "valido_hasta",
            ))
                ->where("rfc = ?", $rfc)
                ->where("tipo = 'prod'")
                ->limit(1);
            if (isset($patente)) {
                $sql->where("patente = ?", $patente);
            }
            if (isset($aduana)) {
                $sql->where("aduana = ?", $aduana);
            }
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                $data = array(
                    "id" => $stmt["id"],
                    "razon" => $stmt["razon"],
                    "figura" => $stmt["figura"],
                    "patente" => $stmt["patente"],
                    "rfc" => $stmt["rfc"],
                    "cer" => $stmt["certificado"],
                    "llave" => $stmt["llave"],
                    "spem" => $stmt["spem"],
                    "spem_pswd" => $stmt["password_spem"],
                    "ws_pswd" => $stmt["password_ws"],
                    "sha" => $stmt["sha"],
                    "validoHasta" => $stmt["valido_hasta"],
                );
                return $data;
            }
            return null;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function listadoFirmantes()
    {
        try {
            $sql = $this->_db_table->select()
                ->from("vucem_firmante", array("id", "patente", "aduana", "razon", "rfc", "sha", "valido_desde", "valido_hasta"))
                ->order("razon ASC");
            if (($stmt = $this->_db_table->fetchAll($sql))) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function certificados()
    {
        try {
            $sql = $this->_db_table->select()
                ->from("vucem_firmante", array("id", "certificado_nom as nombre", new Zend_Db_Expr("AES_DECRYPT(certificado,'{$this->_key}') AS contenido")))
                ->where("valido_desde IS NULL AND valido_hasta IS NULL")
                ->limit(10);
            if (($stmt = $this->_db_table->fetchAll($sql))) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function firmanteId($rfc, $patente = null, $aduana = null)
    {
        try {
            $sql = $this->_db_table->select()
                ->from("vucem_firmante", array("id"))
                ->where("rfc = ?", $rfc)
                ->where("tipo = 'prod'");
            if (isset($patente) && isset($aduana)) {
                $sql->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana);
            }
            if (($stmt = $this->_db_table->fetchRow($sql))) {
                return $stmt->toArray();
            }
            return null;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function tipoFigura($rfc)
    {
        try {
            $sql = $this->_db_table->select()
                ->from("vucem_firmante", array("figura"))
                ->where("rfc = ?", $rfc);
            if (($stmt = $this->_db_table->fetchRow($sql))) {
                return $stmt["figura"];
            }
            return null;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function archivosFirmante($rfc, $env = null, $patente = null, $aduana = null)
    {
        try {
            $sql = $this->_db_table->select();
            $sql->from("vucem_firmante", array(
                new Zend_Db_Expr("AES_DECRYPT(`key`,'{$this->_key}') AS `key`"),
                "key_nom",
                new Zend_Db_Expr("AES_DECRYPT(certificado,'{$this->_key}') AS certificado"),
                "certificado_nom",
                new Zend_Db_Expr("AES_DECRYPT(pem,'{$this->_key}') AS pem"),
                "pem_nom",
                new Zend_Db_Expr("AES_DECRYPT(spem,'{$this->_key}') AS spem"),
                "spem_nom",
                new Zend_Db_Expr("AES_DECRYPT(req,'{$this->_key}') AS req"),
                "req_nom",
            ))
                ->where("rfc = ?", $rfc)
                ->where("tipo = ?", "prod")
                ->limit(1);
            if (isset($patente)) {
                $sql->where("patente = ?", $patente);
            }
            if (isset($aduana)) {
                $sql->where("aduana = ?", $aduana);
            }
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                $data = array(
                    "key" => $stmt["key"],
                    "key_nom" => $stmt["key_nom"],
                    "cer" => $stmt["certificado"],
                    "certificado_nom" => $stmt["certificado_nom"],
                    "pem" => $stmt["pem"],
                    "pem_nom" => $stmt["pem_nom"],
                    "spem" => $stmt["spem"],
                    "spem_nom" => $stmt["spem_nom"],
                    "req" => $stmt["req"],
                    "req_nom" => $stmt["req_nom"],
                );
                return $data;
            }
            return null;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function verificarFirmante($tipo, $figura, $rfc, $patente, $aduana)
    {
        try {
            $sql = $this->_db_table->select()
                ->where("tipo = ?", $tipo)
                ->where("figura = ?", $figura)
                ->where("rfc = ?", $rfc)
                ->where("patente = ?", $patente)
                ->where("aduana = ?", $aduana);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return null;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function agregarNuevoFirmante($tipo, $figura, $razonSocial, $rfc, $patente, $aduana)
    {
        try {
            $added = $this->_db_table->insert(array(
                "tipo" => $tipo,
                "figura" => $figura,
                "patente" => $patente,
                "aduana" => $aduana,
                "razon" => $razonSocial,
                "rfc" => $rfc,
            ));
            if ($added) {
                return $added;
            }
            return null;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function queryBlob($query)
    {
        try {
            $db = Zend_Registry::get("oaqintranet");
            $added = $db->query($query);
            if ($added) {
                return $added;
            }
            return null;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function fielDisponible()
    {
        try {
            $sql = $this->_db_table->select()
                ->from("vucem_firmante", array("id", "tipo", "figura", "patente", "aduana", "razon", "rfc"))
                ->order(array("razon ASC", "patente", "aduana"));

            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return null;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function addNew($figura, $patente, $aduana, $razonSocial, $rfc, $cerPath, $keyPath, $reqPath, $pemPath, $spemPath, $passVu, $passFiel, $passWs, $passSpem, $username)
    {
        try {
            $data = array(
                "tipo" => "prod",
                "figura" => $figura,
                "patente" => $patente,
                "aduana" => $aduana,
                "razon" => $razonSocial,
                "rfc" => $rfc,
                "certificado_nom" => isset($cerPath) ? basename($cerPath) : null,
                "certificado" => isset($cerPath) ? new Zend_Db_Expr("AES_ENCRYPT('" . base64_encode(file_get_contents($cerPath, FILE_USE_INCLUDE_PATH)) . "', '{$this->_key}')") : null,
                "key_nom" => isset($keyPath) ? basename($keyPath) : null,
                "key" => isset($keyPath) ? new Zend_Db_Expr("AES_ENCRYPT('" . base64_encode(file_get_contents($keyPath, FILE_USE_INCLUDE_PATH)) . "', '{$this->_key}')") : null,
                "req_nom" => isset($reqPath) ? basename($reqPath) : null,
                "req" => isset($reqPath) ? new Zend_Db_Expr("AES_ENCRYPT('" . base64_encode(file_get_contents($reqPath, FILE_USE_INCLUDE_PATH)) . "', '{$this->_key}')") : null,
                "pem_nom" => isset($pemPath) ? basename($pemPath) : null,
                "pem" => isset($pemPath) ? new Zend_Db_Expr("AES_ENCRYPT('" . base64_encode(file_get_contents($pemPath, FILE_USE_INCLUDE_PATH)) . "', '{$this->_key}')") : null,
                "spem_nom" => isset($spemPath) ? basename($spemPath) : null,
                "spem" => isset($spemPath) ? new Zend_Db_Expr("AES_ENCRYPT('" . base64_encode(file_get_contents($spemPath, FILE_USE_INCLUDE_PATH)) . "', '{$this->_key}')") : null,
                "password_vu" => new Zend_Db_Expr("AES_ENCRYPT('{$passVu}', '{$this->_key}')"),
                "password_fiel" => new Zend_Db_Expr("AES_ENCRYPT('{$passFiel}', '{$this->_key}')"),
                "password_ws" => new Zend_Db_Expr("AES_ENCRYPT('{$passWs}', '{$this->_key}')"),
                "password_spem" => new Zend_Db_Expr("AES_ENCRYPT('{$passSpem}', '{$this->_key}')"),
                "creado" => date("Y-m-d H:i:s"),
                "creadoPor" => $username,
            );
            $added = $this->_db_table->insert($data);
            if ($added) {
                return true;
            }
            return null;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function obtenerSellosDisponibles()
    {
        try {
            $sql = $this->_db_table->select()
                ->setIntegrityCheck(false)
                ->from(array("f" => "vucem_firmante"), array("f.rfc", "f.razon"))
                ->where("tipo <> 'dev'")
                ->group(array("f.razon", "f.rfc"))
                ->order("f.razon ASC");
            if (($stmt = $this->_db_table->fetchAll($sql))) {
                return $stmt->toArray();
            }
            return null;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function patentesPorSello($rfc)
    {
        try {
            $sql = $this->_db_table->select()
                ->setIntegrityCheck(false)
                ->from(array("f" => "vucem_firmante"), array("f.patente"))
                ->where("f.tipo <> 'dev'")
                ->where("f.rfc = ?", $rfc)
                ->group("f.patente");
            if (($stmt = $this->_db_table->fetchAll($sql))) {
                return $stmt->toArray();
            }
            return null;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function aduanasPorPatente($rfc, $patente)
    {
        try {
            $sql = $this->_db_table->select()
                ->setIntegrityCheck(false)
                ->from(array("f" => "vucem_firmante"), array("f.aduana"))
                ->where("f.tipo <> 'dev'")
                ->where("f.rfc = ?", $rfc)
                ->where("f.patente = ?", $patente)
                ->group("f.aduana");
            if (($stmt = $this->_db_table->fetchAll($sql))) {
                return $stmt->toArray();
            }
            return null;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function nombreFirmante($rfc)
    {
        try {
            $sql = $this->_db_table->select()
                ->setIntegrityCheck(false)
                ->from(array("f" => "vucem_firmante"), array("f.razon"))
                ->where("f.rfc = ?", $rfc);
            if (($stmt = $this->_db_table->fetchRow($sql))) {
                return $stmt->toArray();
            }
            return null;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function sellosPorRfc($rfc)
    {
        try {
            $sql = $this->_db_table->select()
                ->from($this->_db_table, array("*"))
                ->where("rfc = ?", $rfc);
            if (($stmt = $this->_db_table->fetchAll($sql))) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function sellosDeCliente($rfcCte)
    {
        try {
            $sql = $this->_db_table->select()
                ->distinct()
                ->from($this->_db_table, array("rfc", "id", "sha", "razon"))
                ->where("rfc = ?", $rfcCte)
                ->orWhere("figura = 1")
                ->where("rfc NOT IN ('GWT921026L97')")
                ->group(array("id", "rfc"));
            if (($stmt = $this->_db_table->fetchAll($sql))) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function update($id, $data)
    {
        try {
            $where = array(
                "id = ?" => $id
            );
            $stmt = $this->_db_table->update($data, $where);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $stmt = $this->_db_table->delete(array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function datosFirmante($rfc)
    {
        try {
            $sql = $this->_db_table->select()
                ->setIntegrityCheck(false)
                ->from(array("f" => "vucem_firmante"), array("*"))
                ->where("f.rfc = ?", $rfc);
            if (($stmt = $this->_db_table->fetchRow($sql))) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function fechasVencimiento($id)
    {
        try {
            $sql = $this->_db_table->select()
                ->setIntegrityCheck(false)
                ->from(array("f" => "vucem_firmante"), array("*"))
                ->where("f.id = ?", $id)
                ->where("(valido_desde IS NULL OR valido_hasta IS NULL)");
            if (($stmt = $this->_db_table->fetchRow($sql))) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function actualizarFechasVencimiento($id, $validoDesde, $validoHasta)
    {
        try {
            $arr = array(
                "valido_desde" => date("Y-m-d H:i:s", strtotime($validoDesde)),
                "valido_hasta" => date("Y-m-d H:i:s", strtotime($validoHasta))
            );
            $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
}
