<?php

class Trafico_Model_SellosAgentes {

    protected $_db_table;
    protected $_key = "5203bfec0c3db@!b2295";

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_SellosAgentes();
    }

    public function verificar($idAgente) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from($this->_db_table, array("id"))
                    ->where("idAgente = ?", $idAgente)
                    ->where("activo = 1");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function obtener($patente = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("s" => "trafico_sellos_agentes"), array("id", "certificado_nom", "valido_desde", "valido_hasta", "sha", "actualizado", new Zend_Db_Expr("CAST('agente' AS CHAR CHARACTER SET utf8) AS tipo")))
                    ->joinLeft(array("a" => "trafico_agentes"), "a.id = s.idAgente", array("rfc", "patente", "nombre AS razon"));
            if (isset($patente)) {
                $sql->where("s.patente = ?", $patente);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function obtenerPorId($id) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("s" => "trafico_sellos_agentes"), array(
                        "key_nom",
                        "certificado_nom",
                        new Zend_Db_Expr("AES_DECRYPT(`key`,'{$this->_key}') AS `key`"),
                        new Zend_Db_Expr("AES_DECRYPT(`spem`,'{$this->_key}') AS `spem`"),
                        new Zend_Db_Expr("AES_DECRYPT(`certificado`,'{$this->_key}') AS `certificado`"),
                        new Zend_Db_Expr("AES_DECRYPT(`password_spem`,'{$this->_key}') AS `password_spem`"),
                        new Zend_Db_Expr("AES_DECRYPT(`password_ws`,'{$this->_key}') AS `password_ws`"),
                        "sha",
                    ))
                    ->joinLeft(array("a" => "trafico_agentes"), "a.id = s.idAgente", array("rfc", "patente", "nombre AS razon"))
                    ->where("s.id = ?", $id)
                    ->limit(1);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return array(
                    "figura" => 1,
                    "rfc" => $stmt["rfc"],
                    "patente" => $stmt["patente"],
                    "razon" => $stmt["razon"],
                    "key" => $stmt["key"],
                    "cer" => $stmt["certificado"],
                    "spem" => $stmt["spem"],
                    "spem_pswd" => $stmt["password_spem"],
                    "ws_pswd" => $stmt["password_ws"],
                    "sha" => $stmt["sha"],
                    "key_nom" => $stmt["key_nom"],
                    "cer_nom" => $stmt["certificado_nom"],
                );
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function obtenerSellos($id) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("s" => "trafico_sellos_agentes"), array(
                        "id",
                        "key_nom",
                        "certificado_nom",
                        new Zend_Db_Expr("AES_DECRYPT(`key`,'{$this->_key}') AS `key`"),
                        new Zend_Db_Expr("AES_DECRYPT(`spem`,'{$this->_key}') AS `spem`"),
                        new Zend_Db_Expr("AES_DECRYPT(`certificado`,'{$this->_key}') AS `certificado`"),
                        new Zend_Db_Expr("AES_DECRYPT(`password_spem`,'{$this->_key}') AS `password_spem`"),
                        new Zend_Db_Expr("AES_DECRYPT(`password_ws`,'{$this->_key}') AS `password_ws`"),
                        "sha",
                        "valido_desde",
                        "valido_hasta",
                        "activo",
                        new Zend_Db_Expr('CASE WHEN s.creado IS NOT NULL THEN DATE_FORMAT(s.creado, "%d/%m/%Y") ELSE NULL END AS creado'),
                        "creadoPor",
                        new Zend_Db_Expr('CASE WHEN s.actualizado IS NOT NULL THEN DATE_FORMAT(s.actualizado, "%d/%m/%Y") ELSE NULL END AS actualizado'),
                        "actualizadoPor",
                    ))
                    ->joinLeft(array("a" => "trafico_agentes"), "a.id = s.idAgente", array("rfc", "patente", "nombre AS razon"))
                    ->where("s.idAgente = ?", $id)
                    ->limit(1);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
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
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function actualizar($id, $arr) {
        try {
            $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;            
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function reporte($select = false) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("s" => "trafico_sellos_agentes"), array("patente", "valido_desde", "valido_hasta"))
                    ->joinInner(array("a" => "trafico_agentes"), "s.idAgente= a.id", array("nombre"))
                    ->order("s.patente");
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
