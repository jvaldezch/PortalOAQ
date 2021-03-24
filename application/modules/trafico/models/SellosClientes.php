<?php

class Trafico_Model_SellosClientes
{

    protected $_db_table;
    protected $_key = "5203bfec0c3db@!b2295";
    protected $_firephp;

    public function __construct()
    {
        $this->_db_table = new Trafico_Model_DbTable_SellosClientes();
        $this->_firephp = Zend_Registry::get("firephp");
    }

    public function verificar($idCliente)
    {
        try {
            $sql = $this->_db_table->select()
                ->setIntegrityCheck(false)
                ->from($this->_db_table, array("id"))
                ->where("idCliente = ?", $idCliente);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function obtener($idCliente)
    {
        try {
            $sql = $this->_db_table->select()
                ->setIntegrityCheck(false)
                ->from(array("s" => "trafico_sellos_clientes"), array("id", "sha", "valido_desde", "certificado_nom", "valido_hasta", "actualizado", new Zend_Db_Expr("CAST('cliente' AS CHAR CHARACTER SET utf8) AS tipo")))
                ->joinLeft(array("c" => "trafico_clientes"), "c.id = s.idCliente", array("rfc", "nombre AS razon"))
                ->where("s.idCliente = ?", $idCliente);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function obtenerSello($id)
    {
        try {
            $fields = array(
                "id",
                "sha",
                "valido_desde",
                "valido_hasta",
                new Zend_Db_Expr("CAST('cliente' AS CHAR CHARACTER SET utf8) AS tipo"),
                new Zend_Db_Expr("AES_DECRYPT(`key`,'{$this->_key}') AS `key`"),
                new Zend_Db_Expr("AES_DECRYPT(spem,'{$this->_key}') AS spem"),
                new Zend_Db_Expr("AES_DECRYPT(certificado,'{$this->_key}') AS certificado"),
                new Zend_Db_Expr("AES_DECRYPT(password_spem,'{$this->_key}') AS password_spem"),
                new Zend_Db_Expr("AES_DECRYPT(password_ws,'{$this->_key}') AS password_ws"),
            );
            $sql = $this->_db_table->select()
                ->setIntegrityCheck(false)
                ->from($this->_db_table, $fields)
                ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
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

    public function obtenerPorId($id)
    {
        try {
            $sql = $this->_db_table->select()
                ->setIntegrityCheck(false)
                ->from(array("s" => "trafico_sellos_clientes"), array(
                    "key_nom",
                    "certificado_nom",
                    new Zend_Db_Expr("AES_DECRYPT(`key`,'{$this->_key}') AS `key`"),
                    new Zend_Db_Expr("AES_DECRYPT(`spem`,'{$this->_key}') AS `spem`"),
                    new Zend_Db_Expr("AES_DECRYPT(`certificado`,'{$this->_key}') AS `certificado`"),
                    new Zend_Db_Expr("AES_DECRYPT(`password_spem`,'{$this->_key}') AS `password_spem`"),
                    new Zend_Db_Expr("AES_DECRYPT(`password_ws`,'{$this->_key}') AS `password_ws`"),
                    "sha",
                ))
                ->joinLeft(array("a" => "trafico_clientes"), "a.id = s.idCliente", array("rfc", "nombre AS razon"))
                ->where("s.id = ?", $id)
                ->limit(1);
            $stmt = $this->_db_table->fetchRow($sql);            
            if ($stmt) {
                return array(
                    "figura" => 5,
                    "rfc" => $stmt["rfc"],
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

    public function obtenerVencimientoPorId($id)
    {
        try {
            $sql = $this->_db_table->select()
                ->setIntegrityCheck(false)
                ->from(array("s" => "trafico_sellos_clientes"), array(
                    "valido_desde",
                    "valido_hasta",
                ))
                ->joinLeft(array("a" => "trafico_clientes"), "a.id = s.idCliente", array("rfc", "nombre AS razon"))
                ->where("s.id = ?", $id)
                ->limit(1);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return array(
                    "valido_desde" => $stmt["valido_desde"],
                    "valido_hasta" => $stmt["valido_hasta"],
                );
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function obtenerPorIdCliente($id)
    {
        try {
            $sql = $this->_db_table->select()
                ->setIntegrityCheck(false)
                ->from(array("s" => "trafico_sellos_clientes"), array(
                    "key_nom",
                    "certificado_nom",
                    new Zend_Db_Expr("AES_DECRYPT(`key`,'{$this->_key}') AS `key`"),
                    new Zend_Db_Expr("AES_DECRYPT(`spem`,'{$this->_key}') AS `spem`"),
                    new Zend_Db_Expr("AES_DECRYPT(`certificado`,'{$this->_key}') AS `certificado`"),
                    new Zend_Db_Expr("AES_DECRYPT(`password_spem`,'{$this->_key}') AS `password_spem`"),
                    new Zend_Db_Expr("AES_DECRYPT(`password_ws`,'{$this->_key}') AS `password_ws`"),
                    "sha",
                ))
                ->joinLeft(array("a" => "trafico_clientes"), "a.id = s.idCliente", array("rfc", "nombre AS razon"))
                ->where("s.idCliente = ?", $id)
                ->limit(1);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return array(
                    "figura" => 5,
                    "rfc" => $stmt["rfc"],
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

    public function agregar($arr)
    {
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

    public function actualizar($id, $arr)
    {
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

    public function reporte($select = false)
    {
        try {
            $sql = $this->_db_table->select()
                ->setIntegrityCheck(false)
                ->from(array("a" => "trafico_sellos_clientes"), array("rfc", "razon", "valido_desde", "valido_hasta"))
                ->order("a.razon");
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
