<?php

class Trafico_Model_CliSello {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_CliSello();
    }

    public function verificar($idCliente) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idCliente = ?", $idCliente);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizar($idCliente, $idSello, $tipo) {
        try {
            $stmt = $this->_db_table->update(array("idSello" => $idSello, "tipo" => $tipo, "modificado" => date("Y-m-d H:i:s")), array("idCliente = ?" => $idCliente));
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregar($idCliente, $idSello, $tipo) {
        try {
            $stmt = $this->_db_table->insert(array(
                "idCliente" => $idCliente,
                "idSello" => $idSello,
                "tipo" => $tipo,
                "creado" => date("Y-m-d H:i:s")
            ));
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerDefault($idCliente) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("s" => "trafico_clisello"), array("idSello"))
                    ->where("s.idCliente = ?", $idCliente);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return (int) $stmt->idSello;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtener($idCliente) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("s" => "trafico_clisello"), array("*"))
                    ->joinLeft(array("f" => "vucem_firmante"), "s.idSello = f.id", array("rfc", "razon"))
                    ->where("s.idCliente = ?", $idCliente);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
